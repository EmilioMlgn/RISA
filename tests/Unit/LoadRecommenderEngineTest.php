<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Core\AcademicLoad\LoadRecommenderEngine;
use PHPUnit\Framework\TestCase;

class LoadRecommenderEngineTest extends TestCase
{
    protected LoadRecommenderEngine $engine;

    protected function setUp(): void
    {
        parent::setUp();
        $this->engine = new LoadRecommenderEngine;
    }

    /**
     * Prueba que las asignaturas en estatus Reprobada ('R') obtienen la prioridad máxima.
     */
    public function test_prioriza_materias_reprobadas(): void
    {
        $catalogo = [
            1 => ['id' => 1, 'code' => 'M1', 'name' => 'Materia 1 Normal', 'credits' => 5, 'semester_order' => 2],
            2 => ['id' => 2, 'code' => 'M2', 'name' => 'Materia 2 Reprobada', 'credits' => 5, 'semester_order' => 1],
        ];

        $historial = [
            1 => ['status' => 'D', 'attempts' => 1, 'grade' => null],
            2 => ['status' => 'R', 'attempts' => 2, 'grade' => 50.0],
        ];

        $prereqs = [1 => [], 2 => []];

        $res = $this->engine->generarRecomendacion(
            catalogoMaterias: $catalogo,
            historialEstudiante: $historial,
            prerrequisitosPorMateria: $prereqs,
            limiteCreditosMaximo: 30
        );

        $this->assertNotEmpty($res['materiasRecomendadas']);
        // La primera recomendada debe ser la reprobada
        $this->assertSame(2, $res['materiasRecomendadas'][0]['id']);
        $this->assertTrue($res['materiasRecomendadas'][0]['esRepeticion']);
    }

    /**
     * Prueba que se respeta estrictamente el límite máximo de créditos configurado.
     */
    public function test_respeta_limite_de_creditos_maximo(): void
    {
        $catalogo = [
            1 => ['id' => 1, 'code' => 'M1', 'name' => 'Materia 1', 'credits' => 10, 'semester_order' => 1],
            2 => ['id' => 2, 'code' => 'M2', 'name' => 'Materia 2', 'credits' => 10, 'semester_order' => 1],
            3 => ['id' => 3, 'code' => 'M3', 'name' => 'Materia 3', 'credits' => 10, 'semester_order' => 1],
        ];

        $historial = [
            1 => ['status' => 'D', 'attempts' => 1, 'grade' => null],
            2 => ['status' => 'D', 'attempts' => 1, 'grade' => null],
            3 => ['status' => 'D', 'attempts' => 1, 'grade' => null],
        ];

        // Límite máximo de 20 créditos (deben entrar exactamente 2 materias, 1 excluida)
        $res = $this->engine->generarRecomendacion(
            catalogoMaterias: $catalogo,
            historialEstudiante: $historial,
            prerrequisitosPorMateria: [],
            limiteCreditosMaximo: 20
        );

        $this->assertSame(20, $res['creditosTotales']);
        $this->assertCount(2, $res['materiasRecomendadas']);
        $this->assertCount(1, $res['materiasDisponiblesExcluidasPorCupo']);
    }
}
