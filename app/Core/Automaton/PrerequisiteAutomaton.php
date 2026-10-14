<?php

declare(strict_types=1);

namespace App\Core\Automaton;

/**
 * Autómata Finito Determinista (DFA) para la gestión y validación de prerrequisitos curriculares.
 *
 * Definición matemática formal:
 * M = (Q, Σ, δ, q0, F)
 *
 * Donde:
 * - Q: { P, D, C, A, R } (Estados posibles de cada asignatura)
 * - Σ: { EVALUAR_REQUISITOS, INSCRIBIR, CANCELAR_INSCRIPCION, ACREDITAR, NO_ACREDITAR }
 * - δ: Función de transición de estados con condiciones de guarda
 * - q0: Estado inicial (D para materias de 1er semestre sin prerrequisitos; P para las demás)
 * - F: { A } (Estado terminal de acreditación exitosa)
 */
class PrerequisiteAutomaton
{
    /**
     * Evalúa la función de transición de estados: δ(estadoActual, evento) -> nuevoEstado.
     *
     * Esta función contiene las reglas de negocio indispensables para evitar
     * violaciones a la seriación curricular y cambios de estado no válidos.
     *
     * @param  SubjectStatus  $estadoActual  Estado curricular previo de la asignatura.
     * @param  CurricularEvent  $evento  Acción académica entrante del alfabeto.
     * @param  bool  $cumplePrerrequisitos  Verdadero si todos los prerrequisitos en serie están en estado 'A'.
     * @param  bool  $cumpleCreditosMinimos  Verdadero si el alumno posee los créditos mínimos requeridos.
     * @param  float|null  $calificacion  Calificación obtenida (requerida para Acreditar/NoAcreditar).
     * @return SubjectStatus Nuevo estado resultante de la transición.
     */
    public function transicion(
        SubjectStatus $estadoActual,
        CurricularEvent $evento,
        bool $cumplePrerrequisitos = true,
        bool $cumpleCreditosMinimos = true,
        ?float $calificacion = null
    ): SubjectStatus {
        // Regla 1: Un estado final Aprobado ('A') es inmutable y permanente.
        // No puede degradarse ni cambiarse por evaluaciones posteriores.
        if ($estadoActual === SubjectStatus::Aprobada) {
            return SubjectStatus::Aprobada;
        }

        switch ($evento) {
            case CurricularEvent::EvaluarRequisitos:
                // Si la materia está en Pendiente ('P') o Reprobada ('R'), se reevalúa si ahora es elegible.
                if ($estadoActual === SubjectStatus::Pendiente) {
                    if ($cumplePrerrequisitos && $cumpleCreditosMinimos) {
                        return SubjectStatus::Disponible;
                    }

                    return SubjectStatus::Pendiente;
                }

                if ($estadoActual === SubjectStatus::Disponible) {
                    // Si por alguna razón se invalidaron prerrequisitos (ej. rectificación de actas),
                    // la materia debe volver a Pendiente.
                    if (! $cumplePrerrequisitos || ! $cumpleCreditosMinimos) {
                        return SubjectStatus::Pendiente;
                    }

                    return SubjectStatus::Disponible;
                }

                // Si está Cursando ('C') o Reprobada ('R'), mantiene su estado operativo.
                return $estadoActual;

            case CurricularEvent::Inscribir:
                // Únicamente se permite transitar a Cursando ('C') si la materia
                // se encuentra formalmente Disponible ('D') o en Repetición ('R').
                if ($estadoActual === SubjectStatus::Disponible || $estadoActual === SubjectStatus::Reprobada) {
                    return SubjectStatus::Cursando;
                }

                return $estadoActual;

            case CurricularEvent::CancelarInscripcion:
                // Desinscripción en periodo oficial:
                // Si estaba cursando, vuelve a Disponible si nunca se había reprobado,
                // o vuelve a Reprobada si era una repetición.
                if ($estadoActual === SubjectStatus::Cursando) {
                    return SubjectStatus::Disponible;
                }

                return $estadoActual;

            case CurricularEvent::Acreditar:
                // Si la materia estaba en Cursando y obtiene calificacion >= 70, pasa al estado terminal 'A'.
                if ($estadoActual === SubjectStatus::Cursando) {
                    if ($calificacion !== null && $calificacion >= 70.0) {
                        return SubjectStatus::Aprobada;
                    }
                    if ($calificacion === null) {
                        return SubjectStatus::Aprobada;
                    }
                }

                return $estadoActual;

            case CurricularEvent::NoAcreditar:
                // Si la materia estaba en Cursando y obtiene calificacion < 70, pasa a Reprobada ('R').
                if ($estadoActual === SubjectStatus::Cursando) {
                    return SubjectStatus::Reprobada;
                }

                return $estadoActual;

            default:
                return $estadoActual;
        }
    }

    /**
     * Determina el estado curricular que debe tener una materia para un estudiante
     * cruzando su historial con el árbol de prerrequisitos.
     *
     * @param  int  $materiaId  Identificador único de la asignatura.
     * @param  array<int, string>  $estadosPorMateria  Mapa de [materia_id => status_char ('A', 'C', etc.)]
     * @param  array<int, list<int>>  $prerrequisitosPorMateria  Mapa de [materia_id => [materia_prerrequisito_ids...]]
     * @param  int  $creditosAprobadosAcumulados  Total de créditos aprobados por el estudiante hasta el momento.
     * @param  int  $creditosMinimosRequeridos  Créditos mínimos exigidos por la materia (ej. 180 para Residencia).
     * @param  string|null  $estatusRegistradoEnHistorial  Estatus actual que tenga guardado en base de datos.
     * @return SubjectStatus Estado definitivo deducido por el autómata.
     */
    public function resolverEstadoParaMateria(
        int $materiaId,
        array $estadosPorMateria,
        array $prerrequisitosPorMateria,
        int $creditosAprobadosAcumulados,
        int $creditosMinimosRequeridos = 0,
        ?string $estatusRegistradoEnHistorial = null
    ): SubjectStatus {
        // Si el estudiante ya tiene un estatus activo o final registrado (A, C, R), se respeta la precedencia
        if ($estatusRegistradoEnHistorial !== null) {
            $estatusEnum = SubjectStatus::tryFrom($estatusRegistradoEnHistorial);
            if ($estatusEnum === SubjectStatus::Aprobada || $estatusEnum === SubjectStatus::Cursando || $estatusEnum === SubjectStatus::Reprobada) {
                return $estatusEnum;
            }
        }

        // Obtener la lista de dependencias directas para esta asignatura
        $requisitos = $prerrequisitosPorMateria[$materiaId] ?? [];

        // Validar condición de guarda: ¿Todos los prerrequisitos están aprobados ('A')?
        $cumplePrerrequisitos = true;
        foreach ($requisitos as $reqId) {
            $estadoReq = $estadosPorMateria[$reqId] ?? null;
            if ($estadoReq !== SubjectStatus::Aprobada->value) {
                $cumplePrerrequisitos = false;
                break;
            }
        }

        // Validar créditos mínimos requeridos
        $cumpleCreditos = ($creditosAprobadosAcumulados >= $creditosMinimosRequeridos);

        // Disparar evento de evaluación en el autómata
        $estadoBase = ($estatusRegistradoEnHistorial !== null)
            ? (SubjectStatus::tryFrom($estatusRegistradoEnHistorial) ?? SubjectStatus::Pendiente)
            : SubjectStatus::Pendiente;

        return $this->transicion(
            estadoActual: $estadoBase,
            evento: CurricularEvent::EvaluarRequisitos,
            cumplePrerrequisitos: $cumplePrerrequisitos,
            cumpleCreditosMinimos: $cumpleCreditos
        );
    }
}
