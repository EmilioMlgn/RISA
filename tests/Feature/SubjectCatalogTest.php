<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\CatalogAuditLog;
use App\Models\Prerequisite;
use App\Models\StudentAcademicHistory;
use App\Models\Subject;
use App\Models\User;
use Database\Seeders\RisaCurriculumSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SubjectCatalogTest extends TestCase
{
    use RefreshDatabase;

    private const BASE = '/admin/catalogo/materias';

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RisaCurriculumSeeder::class);
        $this->admin = User::where('role', 'admin')->firstOrFail();
    }

    private function alta(array $extra = []): array
    {
        return array_merge([
            'clave' => 'TST1001',
            'nombre' => 'Materia de Prueba',
            'creditos' => '4',
            'semestre' => '5',
            'prerrequisitos' => [],
        ], $extra);
    }

    private function conHistorialSinDependientes(): Subject
    {
        return Subject::active()->get()->first(fn ($s) => StudentAcademicHistory::where('subject_id', $s->id)->exists()
            && ! Prerequisite::where('prerequisite_subject_id', $s->id)->exists());
    }

    private function sinHistorialNiDependientes(): Subject
    {
        return Subject::create([
            'study_plan_id' => Subject::first()->study_plan_id,
            'code' => 'LIB1001', 'name' => 'Materia Libre', 'credits' => 3, 'semester_order' => 8, 'status' => 'Activa',
        ]);
    }

    // ---- Control de acceso por rol (servidor) ----

    public function test_invitado_es_redirigido_al_login_en_la_pantalla(): void
    {
        $this->get('/catalogo-materias')->assertRedirect('/login');
    }

    public function test_invitado_recibe_401_en_la_api(): void
    {
        $this->postJson(self::BASE, $this->alta())->assertStatus(401);
        $this->assertSame(0, Subject::where('code', 'TST1001')->count());
    }

    public function test_alumno_recibe_403_en_pantalla_y_en_todas_las_operaciones_de_escritura(): void
    {
        $alumno = User::where('role', 'estudiante')->firstOrFail();
        $materia = Subject::first();

        $this->actingAs($alumno)->get('/catalogo-materias')->assertForbidden();
        $this->actingAs($alumno)->postJson(self::BASE, $this->alta())->assertForbidden();
        $this->actingAs($alumno)->putJson(self::BASE.'/'.$materia->id, $this->alta())->assertForbidden();
        $this->actingAs($alumno)->deleteJson(self::BASE.'/'.$materia->id, ['confirmado' => true])->assertForbidden();
        $this->actingAs($alumno)->postJson(self::BASE.'/'.$materia->id.'/reactivar')->assertForbidden();

        $this->assertSame(0, Subject::where('code', 'TST1001')->count());
        $this->assertTrue(Subject::whereKey($materia->id)->exists());
        $this->assertGreaterThanOrEqual(5, CatalogAuditLog::where('action', 'Acceso denegado (403)')->count());
    }

    public function test_docente_tampoco_puede_modificar_el_catalogo(): void
    {
        $docente = User::where('role', 'docente')->firstOrFail();
        $this->actingAs($docente)->postJson(self::BASE, $this->alta())->assertForbidden();
    }

    public function test_admin_accede_a_la_pantalla_y_a_la_lista(): void
    {
        $this->actingAs($this->admin)->get('/catalogo-materias')->assertOk()->assertSee('Administración del catálogo de materias');
        $this->actingAs($this->admin)->getJson(self::BASE)->assertOk()
            ->assertJsonStructure(['data' => [['id', 'clave', 'nombre', 'creditos', 'semestre', 'estatus', 'prerrequisitos']]]);
    }

    public function test_login_con_credenciales_correctas_e_incorrectas(): void
    {
        $this->post('/login', ['email' => $this->admin->email, 'password' => 'mala'])->assertSessionHasErrors('email');
        $this->assertGuest();
        $this->post('/login', ['email' => $this->admin->email, 'password' => 'secreto123'])->assertRedirect('/catalogo-materias');
        $this->assertAuthenticatedAs($this->admin);
    }

    // ---- Alta y validaciones ----

    public function test_alta_valida_crea_la_materia_y_registra_bitacora(): void
    {
        $r = $this->actingAs($this->admin)->postJson(self::BASE, $this->alta(['clave' => ' tst1001 ']));
        $r->assertStatus(201)->assertJsonPath('data.clave', 'TST1001')->assertJsonPath('data.estatus', 'Activa');
        $this->assertDatabaseHas('subjects', ['code' => 'TST1001', 'credits' => 4, 'semester_order' => 5, 'status' => 'Activa']);
        $this->assertDatabaseHas('catalog_audit_logs', ['action' => 'Alta', 'user_id' => $this->admin->id]);
    }

    public function test_alta_con_campos_vacios_devuelve_mensajes_obligatorios(): void
    {
        $r = $this->actingAs($this->admin)->postJson(self::BASE, ['clave' => '', 'nombre' => '', 'creditos' => '', 'semestre' => '']);
        $r->assertStatus(422)->assertJsonPath('errors.clave', 'El campo Clave es obligatorio.')
            ->assertJsonPath('errors.nombre', 'El campo Nombre es obligatorio.')
            ->assertJsonPath('errors.creditos', 'El campo Créditos es obligatorio.')
            ->assertJsonPath('errors.semestre', 'El campo Semestre es obligatorio.');
    }

    public function test_alta_con_formatos_y_rangos_invalidos(): void
    {
        $r = $this->actingAs($this->admin)->postJson(self::BASE, ['clave' => 'AB-1', 'nombre' => '12345', 'creditos' => '15', 'semestre' => '12']);
        $r->assertStatus(422)->assertJsonStructure(['errors' => ['clave', 'nombre', 'creditos', 'semestre']]);
        $this->assertSame('Los créditos deben ser un número entero entre 1 y 10.', $r->json('errors.creditos'));
        $this->assertSame('El semestre debe ser un número entero entre 1 y 9.', $r->json('errors.semestre'));
    }

    public function test_limites_de_creditos_y_semestre(): void
    {
        foreach ([['0', '1'], ['10', '9']] as $i => [$cr, $sem]) {
            $malo = $i === 0;
            $r = $this->actingAs($this->admin)->postJson(self::BASE, $this->alta(['clave' => 'LIM100'.$i, 'creditos' => $cr, 'semestre' => $sem]));
            $malo ? $r->assertStatus(422) : $r->assertStatus(201);
        }
        $this->actingAs($this->admin)->postJson(self::BASE, $this->alta(['clave' => 'LIM1009', 'creditos' => '4.5']))->assertStatus(422);
    }

    public function test_clave_duplicada_se_rechaza_sin_importar_mayusculas_ni_guiones(): void
    {
        $existente = Subject::where('code', 'SCD-1008')->firstOrFail();
        $this->actingAs($this->admin)->postJson(self::BASE, $this->alta(['clave' => 'SCD1008']))
            ->assertStatus(422)->assertJsonPath('errors.clave', 'Ya existe una materia con la clave SCD1008.');
        $this->actingAs($this->admin)->postJson(self::BASE, $this->alta(['clave' => 'TST1001']))->assertStatus(201);
        $this->actingAs($this->admin)->postJson(self::BASE, $this->alta(['clave' => 'tst1001']))->assertStatus(422);
        $this->assertSame(1, Subject::where('code', 'TST1001')->count());
        $this->assertTrue($existente->exists);
    }

    public function test_nombre_homonimo_en_mismo_semestre_exige_confirmacion(): void
    {
        $base = Subject::where('code', 'SCD-1008')->firstOrFail();
        $datos = $this->alta(['nombre' => $base->name, 'semestre' => (string) $base->semester_order]);

        $this->actingAs($this->admin)->postJson(self::BASE, $datos)
            ->assertStatus(409)->assertJsonPath('requiereConfirmacion', true);
        $this->assertSame(0, Subject::where('code', 'TST1001')->count());

        $this->actingAs($this->admin)->postJson(self::BASE, $datos + ['confirmado' => true])->assertStatus(201);
    }

    public function test_prerrequisito_debe_ser_de_semestre_menor(): void
    {
        $pre = Subject::where('semester_order', 6)->firstOrFail();
        $this->actingAs($this->admin)->postJson(self::BASE, $this->alta(['semestre' => '6', 'prerrequisitos' => [(string) $pre->id]]))
            ->assertStatus(422)->assertJsonStructure(['errors' => ['prerrequisitos']]);

        $ok = Subject::where('semester_order', 3)->firstOrFail();
        $this->actingAs($this->admin)->postJson(self::BASE, $this->alta(['semestre' => '6', 'prerrequisitos' => [(string) $ok->id]]))->assertStatus(201);
        $this->assertDatabaseHas('prerequisites', ['prerequisite_subject_id' => $ok->id, 'type' => 'obligatorio']);
    }

    public function test_prerrequisito_inactivo_o_repetido_se_rechaza(): void
    {
        $a = Subject::where('semester_order', 2)->first();
        $this->actingAs($this->admin)->postJson(self::BASE, $this->alta(['prerrequisitos' => [(string) $a->id, (string) $a->id]]))->assertStatus(422);

        $a->update(['status' => 'Inactiva']);
        $this->actingAs($this->admin)->postJson(self::BASE, $this->alta(['prerrequisitos' => [(string) $a->id]]))->assertStatus(422);
    }

    public function test_dependencia_circular_se_rechaza_en_edicion(): void
    {
        $a = Subject::create(['study_plan_id' => Subject::first()->study_plan_id, 'code' => 'CIC1001', 'name' => 'Ciclo Uno', 'credits' => 3, 'semester_order' => 1, 'status' => 'Activa']);
        $b = Subject::create(['study_plan_id' => $a->study_plan_id, 'code' => 'CIC1002', 'name' => 'Ciclo Dos', 'credits' => 3, 'semester_order' => 2, 'status' => 'Activa']);
        Prerequisite::create(['subject_id' => $b->id, 'prerequisite_subject_id' => $a->id, 'type' => 'obligatorio']);

        // A (sem 1) no puede depender de B (sem 2): semestre mayor.
        $this->actingAs($this->admin)->putJson(self::BASE.'/'.$a->id, [
            'nombre' => 'Ciclo Uno', 'creditos' => '3', 'semestre' => '3', 'prerrequisitos' => [(string) $b->id],
        ])->assertStatus(422);
    }

    // ---- Edición ----

    public function test_edicion_de_nombre_no_modifica_la_clave(): void
    {
        $this->actingAs($this->admin)->postJson(self::BASE, $this->alta());
        $s = Subject::where('code', 'TST1001')->firstOrFail();

        $this->actingAs($this->admin)->putJson(self::BASE.'/'.$s->id, [
            'clave' => 'HACK9999', 'nombre' => 'Nombre Actualizado', 'creditos' => '4', 'semestre' => '5', 'prerrequisitos' => [],
        ])->assertOk();

        $s->refresh();
        $this->assertSame('TST1001', $s->code);
        $this->assertSame('Nombre Actualizado', $s->name);
        $this->assertDatabaseHas('catalog_audit_logs', ['action' => 'Edición (TST1001)']);
    }

    public function test_cambio_de_creditos_o_semestre_exige_confirmacion(): void
    {
        $this->actingAs($this->admin)->postJson(self::BASE, $this->alta());
        $s = Subject::where('code', 'TST1001')->firstOrFail();
        $datos = ['nombre' => 'Materia de Prueba', 'creditos' => '5', 'semestre' => '6', 'prerrequisitos' => []];

        $this->actingAs($this->admin)->putJson(self::BASE.'/'.$s->id, $datos)
            ->assertStatus(409)->assertJsonCount(2, 'avisos');
        $this->assertSame(4, $s->fresh()->credits);

        $this->actingAs($this->admin)->putJson(self::BASE.'/'.$s->id, $datos + ['confirmado' => true])->assertOk();
        $this->assertSame(5, $s->fresh()->credits);
        $this->assertSame(6, $s->fresh()->semester_order);
    }

    public function test_bajar_semestre_por_debajo_de_un_dependiente_se_rechaza(): void
    {
        $fila = Prerequisite::whereNotNull('prerequisite_subject_id')->firstOrFail();
        $pre = $fila->prerequisiteSubject;
        $dep = $fila->subject;

        $this->actingAs($this->admin)->putJson(self::BASE.'/'.$pre->id, [
            'nombre' => $pre->name, 'creditos' => (string) $pre->credits, 'semestre' => (string) $dep->semester_order, 'prerrequisitos' => [],
            'confirmado' => true,
        ])->assertStatus(422)->assertJsonStructure(['errors' => ['prerrequisitos']]);
    }

    // ---- Eliminación ----

    public function test_eliminar_sin_confirmacion_devuelve_409_y_no_borra(): void
    {
        $s = $this->sinHistorialNiDependientes();
        $this->actingAs($this->admin)->deleteJson(self::BASE.'/'.$s->id)->assertStatus(409)->assertJsonPath('requiereConfirmacion', true);
        $this->assertTrue(Subject::whereKey($s->id)->exists());
    }

    public function test_eliminacion_fisica_sin_historial_ni_dependientes(): void
    {
        $s = $this->sinHistorialNiDependientes();
        $this->actingAs($this->admin)->deleteJson(self::BASE.'/'.$s->id, ['confirmado' => true])
            ->assertOk()->assertJsonPath('modo', 'fisica');
        $this->assertDatabaseMissing('subjects', ['id' => $s->id]);
        $this->assertDatabaseHas('catalog_audit_logs', ['action' => 'Eliminación física']);
    }

    public function test_eliminar_materia_que_es_prerrequisito_queda_bloqueado(): void
    {
        $pre = Prerequisite::whereNotNull('prerequisite_subject_id')->firstOrFail()->prerequisiteSubject;
        $r = $this->actingAs($this->admin)->deleteJson(self::BASE.'/'.$pre->id, ['confirmado' => true]);
        $r->assertStatus(422);
        $this->assertStringContainsString('No se puede eliminar: es prerrequisito de', $r->json('message'));
        $this->assertTrue(Subject::whereKey($pre->id)->exists());
    }

    public function test_materia_con_historial_se_da_de_baja_logica_y_conserva_el_historial(): void
    {
        $s = $this->conHistorialSinDependientes();
        $this->assertNotNull($s, 'El seeder debe contener una materia con historial y sin dependientes.');
        $historiales = StudentAcademicHistory::where('subject_id', $s->id)->count();

        $this->actingAs($this->admin)->deleteJson(self::BASE.'/'.$s->id, ['confirmado' => true])
            ->assertOk()->assertJsonPath('modo', 'baja_logica');

        $this->assertSame('Inactiva', $s->fresh()->status);
        $this->assertSame($historiales, StudentAcademicHistory::where('subject_id', $s->id)->count());
        $this->assertDatabaseHas('catalog_audit_logs', ['action' => 'Baja lógica']);
    }

    public function test_reactivar_materia_inactiva(): void
    {
        $s = $this->conHistorialSinDependientes();
        $this->actingAs($this->admin)->deleteJson(self::BASE.'/'.$s->id, ['confirmado' => true]);

        $this->actingAs($this->admin)->postJson(self::BASE.'/'.$s->id.'/reactivar')->assertOk();
        $this->assertSame('Activa', $s->fresh()->status);
    }

    // ---- Reflejo en la retícula ----

    private function clavesEnReticula(): array
    {
        return collect($this->getJson('/api/reticula')->json('data.semestres'))
            ->flatMap(fn ($sem) => collect($sem['materias'])->pluck('clave'))->all();
    }

    public function test_alta_baja_y_reactivacion_se_reflejan_en_la_reticula(): void
    {
        $this->assertNotContains('TST1001', $this->clavesEnReticula());

        $this->actingAs($this->admin)->postJson(self::BASE, $this->alta())->assertStatus(201);
        $this->assertContains('TST1001', $this->clavesEnReticula());

        $s = $this->conHistorialSinDependientes();
        $this->actingAs($this->admin)->deleteJson(self::BASE.'/'.$s->id, ['confirmado' => true])->assertOk();
        $this->assertNotContains($s->code, $this->clavesEnReticula());

        $this->actingAs($this->admin)->postJson(self::BASE.'/'.$s->id.'/reactivar')->assertOk();
        $this->assertContains($s->code, $this->clavesEnReticula());
    }

    public function test_la_edicion_reubica_la_materia_en_la_reticula(): void
    {
        $this->actingAs($this->admin)->postJson(self::BASE, $this->alta());
        $s = Subject::where('code', 'TST1001')->firstOrFail();
        $this->actingAs($this->admin)->putJson(self::BASE.'/'.$s->id, [
            'nombre' => 'Materia de Prueba', 'creditos' => '4', 'semestre' => '7', 'prerrequisitos' => [], 'confirmado' => true,
        ])->assertOk();

        $sem7 = collect($this->getJson('/api/reticula')->json('data.semestres'))->firstWhere('semestre', 7);
        $this->assertContains('TST1001', collect($sem7['materias'])->pluck('clave')->all());
    }

    public function test_bitacora_solo_para_admin(): void
    {
        $this->actingAs($this->admin)->getJson('/admin/catalogo/bitacora')->assertOk();
        $this->actingAs(User::where('role', 'estudiante')->first())->getJson('/admin/catalogo/bitacora')->assertForbidden();
    }
}
