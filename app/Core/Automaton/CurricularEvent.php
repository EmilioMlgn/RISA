<?php

declare(strict_types=1);

namespace App\Core\Automaton;

/**
 * Alfabeto de entrada (Sigma) para el Autómata Finito Determinista (DFA).
 * Representa los eventos académicos y transiciones de trayectoria del estudiante.
 */
enum CurricularEvent: string
{
    case EvaluarRequisitos = 'EVALUAR_REQUISITOS';
    case Inscribir = 'INSCRIBIR';
    case CancelarInscripcion = 'CANCELAR_INSCRIPCION';
    case Acreditar = 'ACREDITAR';
    case NoAcreditar = 'NO_ACREDITAR';
}
