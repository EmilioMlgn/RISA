<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Core\AcademicLoad\LoadRecommenderEngine;
use App\Models\Subject;
use App\Models\User;
use Database\Seeders\RisaCurriculumSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PlaneacionTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RisaCurriculumSeeder::class);
    }

    // ---- Simulador de escenarios ----

    public function test_simulador_usa_los_limites_institucionales_por_omision(): void
    {
        $this->get('/simulador-escenarios')
            ->assertOk()
            ->assertSee('Simulador de escenarios')
            ->assertSee('Ana Sofía Ramírez Torres')
            ->assertViewHas('limite', LoadRecommenderEngine::CREDITOS_MAXIMOS);
    }

    public function test_simulador_respeta_el_limite_de_creditos_elegido(): void
    {
        $respuesta = $this->get('/simulador-escenarios?creditos=12&ritmo=20')->assertOk();

        $escenario = $respuesta->viewData('escenario');
        $this->assertLessThanOrEqual(12, $escenario['recomendacionCarga']['creditosTotales']);
        $this->assertSame(20, $respuesta->viewData('ritmo'));
    }

    public function test_simulador_acota_valores_fuera_de_rango(): void
    {
        $this->get('/simulador-escenarios?creditos=999&ritmo=1')
            ->assertOk()
            ->assertViewHas('limite', LoadRecommenderEngine::CREDITOS_MAXIMOS)
            ->assertViewHas('ritmo', 12);

        $this->get('/simulador-escenarios?creditos=abc')
            ->assertOk()
            ->assertViewHas('limite', LoadRecommenderEngine::CREDITOS_MAXIMOS);
    }

    // ---- Reglas de recomendación ----

    public function test_reglas_requiere_sesion_de_administrador(): void
    {
        $this->get('/configuracion-reglas')->assertRedirect('/login');

        $alumno = User::where('role', 'estudiante')->firstOrFail();
        $this->actingAs($alumno)->get('/configuracion-reglas')->assertForbidden();
    }

    public function test_reglas_muestra_limites_del_motor_y_materias_reales(): void
    {
        $admin = User::where('role', 'admin')->firstOrFail();
        $materia = Subject::active()->has('prerequisites')->with('prerequisites')->firstOrFail();

        $this->actingAs($admin)->get('/configuracion-reglas')
            ->assertOk()
            ->assertSee('Reglas de recomendación')
            ->assertSee((string) LoadRecommenderEngine::CREDITOS_MAXIMOS)
            ->assertSee($materia->code)
            ->assertSee($materia->prerequisites->first()->name);
    }

    public function test_reglas_no_muestra_materias_dadas_de_baja(): void
    {
        $admin = User::where('role', 'admin')->firstOrFail();
        $baja = Subject::active()->firstOrFail();
        $baja->update(['status' => 'Inactiva']);

        $this->actingAs($admin)->get('/configuracion-reglas')
            ->assertOk()
            ->assertDontSee('data-q="'.mb_strtolower($baja->code.' '.$baja->name).'"', false);
    }
}
