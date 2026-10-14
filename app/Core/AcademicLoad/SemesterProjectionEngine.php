<?php

declare(strict_types=1);

namespace App\Core\AcademicLoad;

use App\Core\Automaton\SubjectStatus;

/**
 * Motor de Proyección de Semestres Restantes para Egreso.
 *
 * Emplea ordenamiento topológico y cálculo de caminos máximos en Grafos Acíclicos Dirigidos (DAG)
 * para calcular cuántos periodos escolares requiere el estudiante como cota mínima teórica
 * y proyectada, cruzando la longitud de las cadenas de seriación con el saldo de créditos restantes.
 */
class SemesterProjectionEngine
{
    /**
     * Proyecta el número de semestres restantes y analiza la ruta crítica del estudiante.
     *
     * @param  array<int, array{id: int, code: string, name: string, credits: int, semester_order: int}>  $catalogoMaterias
     * @param  array<int, array{status: string, attempts: int, grade: float|null}>  $historialEstudiante
     * @param  array<int, list<int>>  $prerrequisitosPorMateria  [materia_id => [prereq_id, ...]]
     * @param  int  $promedioCreditosPorSemestre  Créditos típicos por semestre (estándar institucional: 30 a 32).
     * @return array{
     *     semestresRestantesEstimados: int,
     *     cotaPorSeriacion: int,
     *     cotaPorCreditos: int,
     *     creditosAprobados: int,
     *     creditosCursando: int,
     *     creditosPendientes: int,
     *     creditosTotales: int,
     *     porcentajeAvance: float,
     *     cadenaCriticaEjemplo: list<string>,
     *     diagnostico: string
     * }
     */
    public function proyectarSemestres(
        array $catalogoMaterias,
        array $historialEstudiante,
        array $prerrequisitosPorMateria,
        int $promedioCreditosPorSemestre = 30
    ): array {
        $creditosAprobados = 0;
        $creditosCursando = 0;
        $creditosPendientes = 0;
        $creditosTotales = 0;

        $materiasNoAprobadas = [];

        foreach ($catalogoMaterias as $materiaId => $materia) {
            $creditosTotales += $materia['credits'];
            $hist = $historialEstudiante[$materiaId] ?? null;
            $status = $hist['status'] ?? SubjectStatus::Pendiente->value;

            if ($status === SubjectStatus::Aprobada->value) {
                $creditosAprobados += $materia['credits'];
            } elseif ($status === SubjectStatus::Cursando->value) {
                $creditosCursando += $materia['credits'];
                // La materia cursando cuenta en la cadena actual pero con avance en curso
                $materiasNoAprobadas[$materiaId] = $materia;
            } else {
                $creditosPendientes += $materia['credits'];
                $materiasNoAprobadas[$materiaId] = $materia;
            }
        }

        // Si ya aprobó todo el plan
        if (empty($materiasNoAprobadas)) {
            return [
                'semestresRestantesEstimados' => 0,
                'cotaPorSeriacion' => 0,
                'cotaPorCreditos' => 0,
                'creditosAprobados' => $creditosAprobados,
                'creditosCursando' => $creditosCursando,
                'creditosPendientes' => 0,
                'creditosTotales' => $creditosTotales,
                'porcentajeAvance' => 100.0,
                'cadenaCriticaEjemplo' => [],
                'diagnostico' => '¡Felicidades! Has cubierto la totalidad de los créditos del plan de estudios.',
            ];
        }

        // Cota 1: Por volumen de créditos pendientes (asumiendo que las cursando se acreditan)
        $saldoCreditos = $creditosPendientes;
        $cotaPorCreditos = (int) ceil(max(0, $saldoCreditos) / max(1, $promedioCreditosPorSemestre));

        // Cota 2: Por longitud del camino crítico en el subgrafo de materias no aprobadas
        $profundidades = [];
        $predecesores = [];

        foreach (array_keys($materiasNoAprobadas) as $mId) {
            $this->calcularLongitudCadena(
                $mId,
                $materiasNoAprobadas,
                $prerrequisitosPorMateria,
                $profundidades,
                $predecesores
            );
        }

        $cotaPorSeriacion = 0;
        $materiaFinalCadena = null;

        foreach ($profundidades as $mId => $depth) {
            if ($depth > $cotaPorSeriacion) {
                $cotaPorSeriacion = $depth;
                $materiaFinalCadena = $mId;
            }
        }

        // Reconstruir la cadena crítica
        $cadenaNombres = [];
        if ($materiaFinalCadena !== null) {
            $curr = $materiaFinalCadena;
            while ($curr !== null) {
                $cadenaNombres[] = $catalogoMaterias[$curr]['name'] ?? "Materia #{$curr}";
                $curr = $predecesores[$curr] ?? null;
            }
            $cadenaNombres = array_reverse($cadenaNombres);
        }

        // La estimación es el máximo entre la seriación obligatoria y los créditos pendientes
        $semestresEstimados = max($cotaPorSeriacion, $cotaPorCreditos, 1);
        $porcentajeAvance = $creditosTotales > 0
            ? round(($creditosAprobados / $creditosTotales) * 100, 1)
            : 0.0;

        $diagnostico = "Con tu avance actual del {$porcentajeAvance}%, se estima que te restan {$semestresEstimados} semestre(s) para egresar.";
        if ($cotaPorSeriacion > $cotaPorCreditos) {
            $diagnostico .= ' El factor limitante es la cadena de seriación de prerrequisitos ('.implode(' → ', $cadenaNombres).').';
        } else {
            $diagnostico .= ' El factor limitante es la cantidad de créditos acumulados por cubrir.';
        }

        return [
            'semestresRestantesEstimados' => $semestresEstimados,
            'cotaPorSeriacion' => $cotaPorSeriacion,
            'cotaPorCreditos' => $cotaPorCreditos,
            'creditosAprobados' => $creditosAprobados,
            'creditosCursando' => $creditosCursando,
            'creditosPendientes' => $creditosPendientes,
            'creditosTotales' => $creditosTotales,
            'porcentajeAvance' => $porcentajeAvance,
            'cadenaCriticaEjemplo' => $cadenaNombres,
            'diagnostico' => $diagnostico,
        ];
    }

    /**
     * Calcula recursivamente la longitud de la cadena de prerrequisitos más larga
     * que termina en una asignatura dada dentro del subgrafo pendiente.
     *
     * @param  array<int, array<string, mixed>>  $materiasNoAprobadas
     * @param  array<int, list<int>>  $prerrequisitosPorMateria
     * @param  array<int, int>  $profundidades
     * @param  array<int, int|null>  $predecesores
     */
    protected function calcularLongitudCadena(
        int $materiaId,
        array &$materiasNoAprobadas,
        array &$prerrequisitosPorMateria,
        array &$profundidades,
        array &$predecesores
    ): int {
        if (isset($profundidades[$materiaId])) {
            return $profundidades[$materiaId];
        }

        $prereqs = $prerrequisitosPorMateria[$materiaId] ?? [];
        $maxPrereqDepth = 0;
        $mejorPredecesor = null;

        foreach ($prereqs as $prereqId) {
            // Solo nos interesan los prerrequisitos que el estudiante aún NO ha aprobado
            if (isset($materiasNoAprobadas[$prereqId])) {
                $d = $this->calcularLongitudCadena(
                    $prereqId,
                    $materiasNoAprobadas,
                    $prerrequisitosPorMateria,
                    $profundidades,
                    $predecesores
                );
                if ($d > $maxPrereqDepth) {
                    $maxPrereqDepth = $d;
                    $mejorPredecesor = $prereqId;
                }
            }
        }

        $profundidades[$materiaId] = $maxPrereqDepth + 1;
        $predecesores[$materiaId] = $mejorPredecesor;

        return $profundidades[$materiaId];
    }
}
