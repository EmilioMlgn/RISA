<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Subject;
use App\Models\User;
use Database\Seeders\RisaCurriculumSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReticulaApiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RisaCurriculumSeeder::class);
    }

    /**
     * Prueba que la ruta principal carga la interfaz web de la retícula.
     */
    public function test_carga_vista_principal_de_reticula(): void
    {
        $response = $this->get('/');

        $response->assertStatus(200);
        $response->assertSee('RISA');
        $response->assertSee('Ana Sofía Ramírez Torres');
        $response->assertSee('Instituto Tecnológico de Celaya');
    }

    /**
     * Prueba que el endpoint /api/reticula devuelve los datos completos.
     */
    public function test_endpoint_api_reticula_retorna_estructura_correcta(): void
    {
        $response = $this->getJson('/api/reticula');

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'success',
            'data' => [
                'estudiante' => ['id', 'nombre', 'numeroControl'],
                'resumen' => ['totalCreditos', 'creditosAprobados', 'porcentajeAvance'],
                'semestres' => [
                    '*' => [
                        'semestre',
                        'totalCreditos',
                        'materias' => [
                            '*' => ['id', 'clave', 'nombre', 'creditos', 'estatus'],
                        ],
                    ],
                ],
            ],
        ]);
    }

    /**
     * Prueba que el motor de recomendación devuelve la sugerencia de carga y proyección.
     */
    public function test_endpoint_api_recomendacion_calcula_carga_y_proyeccion(): void
    {
        $response = $this->getJson('/api/recomendacion');

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'success',
            'data' => [
                'recomendacionCarga' => [
                    'materiasRecomendadas',
                    'creditosTotales',
                    'diagnostico',
                ],
                'proyeccionEgreso' => [
                    'semestresRestantesEstimados',
                    'cotaPorSeriacion',
                    'cotaPorCreditos',
                    'porcentajeAvance',
                ],
            ],
        ]);
    }

    /**
     * Prueba el registro de una observación de docente ligada a una materia y alumno.
     */
    public function test_registro_de_observacion_docente(): void
    {
        $materia = Subject::first();
        $estudiante = User::where('role', 'estudiante')->first();

        $payload = [
            'author_name' => 'Dr. Fernando Arreola',
            'author_role' => 'Docente Titular',
            'comment' => 'El estudiante demostró alto compromiso y dominio en la resolución de problemas algorítmicos.',
            'student_id' => $estudiante->id,
        ];

        $response = $this->postJson("/api/materias/{$materia->id}/comentarios", $payload);

        $response->assertStatus(201);
        $response->assertJsonPath('success', true);
        $this->assertDatabaseHas('subject_comments', [
            'subject_id' => $materia->id,
            'user_id' => $estudiante->id,
            'author_name' => 'Dr. Fernando Arreola',
        ]);
    }
}
