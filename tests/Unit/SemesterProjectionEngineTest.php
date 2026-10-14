<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Core\AcademicLoad\SemesterProjectionEngine;
use PHPUnit\Framework\TestCase;

class SemesterProjectionEngineTest extends TestCase
{
    protected SemesterProjectionEngine $engine;

    protected function setUp(): void
    {
        parent::setUp();
        $this->engine = new SemesterProjectionEngine;
    }

    /**
     * Prueba que el motor estima los semestres considerando la longitud del camino
     * más largo en la cadena de prerrequisitos (Topological DAG longest path).
     */
    public function test_proyeccion_por_cadena_critica_de_seriacion(): void
    {
        // 3 materias en serie estricta: M1 -> M2 -> M3 (cada una requiere la anterior)
        $catalogo = [
            1 => ['id' => 1, 'code' => 'M1', 'name' => 'Materia 1', 'credits' => 5, 'semester_order' => 1],
            2 => ['id' => 2, 'code' => 'M2', 'name' => 'Materia 2', 'credits' => 5, 'semester_order' => 2],
            3 => ['id' => 3, 'code' => 'M3', 'name' => 'Materia 3', 'credits' => 5, 'semester_order' => 3],
        ];

        $prereqs = [
            2 => [1], // M2 requiere M1
            3 => [2], // M3 requiere M2
        ];

        $historial = [
            1 => ['status' => 'P', 'attempts' => 1, 'grade' => null],
            2 => ['status' => 'P', 'attempts' => 1, 'grade' => null],
            3 => ['status' => 'P', 'attempts' => 1, 'grade' => null],
        ];

        $res = $this->engine->proyectarSemestres(
            catalogoMaterias: $catalogo,
            historialEstudiante: $historial,
            prerrequisitosPorMateria: $prereqs,
            promedioCreditosPorSemestre: 30
        );

        // Como están en serie, requieren al menos 3 semestres a pesar de tener solo 15 créditos
        $this->assertSame(3, $res['cotaPorSeriacion']);
        $this->assertSame(3, $res['semestresRestantesEstimados']);
        $this->assertCount(3, $res['cadenaCriticaEjemplo']);
    }

    /**
     * Prueba que si no hay materias pendientes el estimado es 0 semestres.
     */
    public function test_proyeccion_cero_si_todo_esta_aprobado(): void
    {
        $catalogo = [
            1 => ['id' => 1, 'code' => 'M1', 'name' => 'Materia 1', 'credits' => 5, 'semester_order' => 1],
        ];

        $historial = [
            1 => ['status' => 'A', 'attempts' => 1, 'grade' => 90.0],
        ];

        $res = $this->engine->proyectarSemestres(
            catalogoMaterias: $catalogo,
            historialEstudiante: $historial,
            prerrequisitosPorMateria: []
        );

        $this->assertSame(0, $res['semestresRestantesEstimados']);
        $this->assertEquals(100.0, $res['porcentajeAvance']);
    }
}
