<?php

declare(strict_types=1);

namespace App\Core\AcademicLoad;

use App\Core\Automaton\PrerequisiteAutomaton;
use App\Core\Automaton\SubjectStatus;

/**
 * Motor de Recomendación de Carga Académica basado en reglas y optimización de ruta crítica.
 *
 * Implementado en PHP puro para garantizar máximo rendimiento, independencia
 * tecnológica y cumplimiento con la regla de no depender de IA generativa externa.
 *
 * Prioridades del algoritmo:
 * 1. Materias Reprobadas ('R'): Prevención de bajas reglamentarias y rezago acumulado.
 * 2. Grado de Salida Curricular (Ruta Crítica): Materias que desbloquean mayor número de materias futuras.
 * 3. Semestre Sugerido: Prioriza el avance ordenado del plan de estudios.
 * 4. Ajuste por límite crediticio (Mínimo: 20, Estándar: 28-32, Máximo Institucional: 36 créditos).
 */
class LoadRecommenderEngine
{
    public function __construct(
        protected PrerequisiteAutomaton $automaton = new PrerequisiteAutomaton
    ) {}

    /**
     * Genera la propuesta óptima de materias para el siguiente ciclo escolar.
     *
     * @param  array<int, array{id: int, code: string, name: string, credits: int, semester_order: int}>  $catalogoMaterias
     * @param  array<int, array{status: string, attempts: int, grade: float|null}>  $historialEstudiante  [materia_id => datos]
     * @param  array<int, list<int>>  $prerrequisitosPorMateria  [materia_id => [prereq_id, ...]]
     * @param  int  $limiteCreditosMaximo  Límite institucional superior de créditos por semestre.
     * @param  int  $limiteCreditosMinimo  Límite institucional inferior.
     * @return array{
     *     materiasRecomendadas: list<array{
     *         id: int,
     *         code: string,
     *         name: string,
     *         credits: int,
     *         semester_order: int,
     *         prioridad: int,
     *         motivo: string,
     *         esRepeticion: bool,
     *         materiasQueDesbloquea: int
     *     }>,
     *     creditosTotales: int,
     *     materiasDisponiblesExcluidasPorCupo: list<array{id: int, name: string, credits: int, motivo: string}>,
     *     diagnostico: list<string>
     * }
     */
    public function generarRecomendacion(
        array $catalogoMaterias,
        array $historialEstudiante,
        array $prerrequisitosPorMateria,
        int $limiteCreditosMaximo = 36,
        int $limiteCreditosMinimo = 20
    ): array {
        // 1. Calcular total de créditos ya aprobados
        $creditosAprobados = 0;
        $estadosActuales = [];

        foreach ($catalogoMaterias as $materiaId => $materia) {
            $reg = $historialEstudiante[$materiaId] ?? null;
            $estadoStr = $reg['status'] ?? null;
            if ($estadoStr === SubjectStatus::Aprobada->value) {
                $creditosAprobados += $materia['credits'];
            }
            if ($estadoStr !== null) {
                $estadosActuales[$materiaId] = $estadoStr;
            }
        }

        // 2. Resolver el estado de cada materia en la retícula usando el autómata
        $estadosResueltos = [];
        foreach ($catalogoMaterias as $materiaId => $materia) {
            $hist = $historialEstudiante[$materiaId] ?? null;
            $estatusHistorial = $hist['status'] ?? null;

            $estado = $this->automaton->resolverEstadoParaMateria(
                materiaId: $materiaId,
                estadosPorMateria: $estadosActuales,
                prerrequisitosPorMateria: $prerrequisitosPorMateria,
                creditosAprobadosAcumulados: $creditosAprobados,
                creditosMinimosRequeridos: 0,
                estatusRegistradoEnHistorial: $estatusHistorial
            );

            $estadosResueltos[$materiaId] = $estado;
        }

        // 3. Construir grafo inverso para saber cuántas materias desbloquea cada asignatura (Ruta Crítica)
        $materiasDesbloqueadas = $this->calcularGradoDeSalida($prerrequisitosPorMateria);

        // 4. Filtrar asignaturas elegibles (Disponibles 'D' o Reprobadas 'R')
        $candidatas = [];
        $diagnostico = [];
        $tieneReprobadas = false;

        foreach ($catalogoMaterias as $materiaId => $materia) {
            $estado = $estadosResueltos[$materiaId];

            if ($estado->isEnrollable()) {
                $hist = $historialEstudiante[$materiaId] ?? null;
                $intentos = (int) ($hist['attempts'] ?? 1);
                $esRepeticion = ($estado === SubjectStatus::Reprobada);

                if ($esRepeticion) {
                    $tieneReprobadas = true;
                }

                // Cálculo de puntuación de prioridad (mayor puntuación = mayor urgencia de inscripción)
                // - Reprobadas: Base de 100,000 + 10,000 por cada intento acumulado
                // - Ruta crítica: Multiplicador de 1,000 por cada materia subsecuente desbloqueada
                // - Semestre sugerido: Penalización leve para favorecer semestres tempranos
                $puntuacionPrioridad = 0;
                $motivo = '';

                if ($esRepeticion) {
                    $puntuacionPrioridad += 100000 + ($intentos * 10000);
                    $motivo = "Materia reprobada con máxima prioridad de repetición (Intento {$intentos}).";
                } else {
                    $desbloqueaCount = $materiasDesbloqueadas[$materiaId] ?? 0;
                    $puntuacionPrioridad += ($desbloqueaCount * 1000) + (100 - $materia['semester_order'] * 5);

                    if ($desbloqueaCount > 0) {
                        $motivo = "Ruta crítica: desbloquea {$desbloqueaCount} materia(s) en semestres posteriores.";
                    } else {
                        $motivo = "Materia de semestre {$materia['semester_order']} con prerrequisitos cumplidos.";
                    }
                }

                $candidatas[] = [
                    'id' => $materia['id'],
                    'code' => $materia['code'],
                    'name' => $materia['name'],
                    'credits' => $materia['credits'],
                    'semester_order' => $materia['semester_order'],
                    'prioridad' => $puntuacionPrioridad,
                    'motivo' => $motivo,
                    'esRepeticion' => $esRepeticion,
                    'materiasQueDesbloquea' => $materiasDesbloqueadas[$materiaId] ?? 0,
                ];
            }
        }

        // Ordenar candidatos descendentemente por prioridad
        usort($candidatas, fn ($a, $b) => $b['prioridad'] <=> $a['prioridad']);

        // 5. Selección voraz (Greedy packing) respetando la cota de créditos máxima
        $seleccionadas = [];
        $excluidas = [];
        $creditosAcumulados = 0;

        foreach ($candidatas as $candidata) {
            if ($creditosAcumulados + $candidata['credits'] <= $limiteCreditosMaximo) {
                $seleccionadas[] = $candidata;
                $creditosAcumulados += $candidata['credits'];
            } else {
                $excluidas[] = [
                    'id' => $candidata['id'],
                    'name' => $candidata['name'],
                    'credits' => $candidata['credits'],
                    'motivo' => "Excedería el límite máximo de créditos ({$limiteCreditosMaximo} créditos).",
                ];
            }
        }

        // 6. Diagnósticos y advertencias para el estudiante
        if ($tieneReprobadas) {
            $diagnostico[] = 'Atención: Tienes asignaturas en repetición. El reglamento institucional establece su inscripción prioritaria.';
        }
        if ($creditosAcumulados < $limiteCreditosMinimo) {
            $diagnostico[] = "Aviso: La carga sugerida ({$creditosAcumulados} créditos) está por debajo del mínimo institucional ({$limiteCreditosMinimo} créditos).";
        } else {
            $diagnostico[] = "Carga académica balanceada: {$creditosAcumulados} créditos sugeridos repartidos en ".count($seleccionadas).' asignaturas.';
        }

        return [
            'materiasRecomendadas' => $seleccionadas,
            'creditosTotales' => $creditosAcumulados,
            'materiasDisponiblesExcluidasPorCupo' => $excluidas,
            'diagnostico' => $diagnostico,
        ];
    }

    /**
     * Calcula para cada asignatura el número total de asignaturas dependientes
     * directas o indirectas (cadena de desbloqueo en la retícula).
     *
     * @param  array<int, list<int>>  $prerrequisitosPorMateria  [materia_id => [prereq_id, ...]]
     * @return array<int, int> [materia_id => conteo_desbloqueo]
     */
    protected function calcularGradoDeSalida(array $prerrequisitosPorMateria): array
    {
        // Invertir el grafo: [prereq_id => [materia_que_la_necesita, ...]]
        $grafoDescendientes = [];
        foreach ($prerrequisitosPorMateria as $materiaId => $prereqs) {
            foreach ($prereqs as $prereqId) {
                $grafoDescendientes[$prereqId][] = $materiaId;
            }
        }

        $conteoPorMateria = [];
        foreach (array_keys($grafoDescendientes) as $materiaId) {
            // Recorrido DFS / BFS para contar todos los descendientes alcanzables
            $visitados = [];
            $cola = $grafoDescendientes[$materiaId] ?? [];

            while (! empty($cola)) {
                $actual = array_shift($cola);
                if (! isset($visitados[$actual])) {
                    $visitados[$actual] = true;
                    if (isset($grafoDescendientes[$actual])) {
                        foreach ($grafoDescendientes[$actual] as $sig) {
                            $cola[] = $sig;
                        }
                    }
                }
            }
            $conteoPorMateria[$materiaId] = count($visitados);
        }

        return $conteoPorMateria;
    }
}
