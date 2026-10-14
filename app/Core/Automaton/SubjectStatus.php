<?php

declare(strict_types=1);

namespace App\Core\Automaton;

/**
 * Enumeración que representa los estados formales del autómata curricular para cada materia.
 *
 * Estados:
 * - A: Aprobada (Estado de aceptación final)
 * - C: Cursando (Inscrita en el periodo escolar activo)
 * - R: Reprobada (Calificación menor a 70, sujeta a repetición prioritaria)
 * - D: Disponible (Cumple todos los prerrequisitos y créditos mínimos requeridos)
 * - P: Pendiente (Falta acreditar uno o más prerrequisitos en la cadena)
 */
enum SubjectStatus: string
{
    case Aprobada = 'A';
    case Cursando = 'C';
    case Reprobada = 'R';
    case Disponible = 'D';
    case Pendiente = 'P';

    /**
     * Retorna la etiqueta legible del estado en español.
     */
    public function label(): string
    {
        return match ($this) {
            self::Aprobada => 'Aprobada',
            self::Cursando => 'Cursando',
            self::Reprobada => 'Reprobada',
            self::Disponible => 'Disponible',
            self::Pendiente => 'Pendiente',
        };
    }

    /**
     * Retorna la descripción institucional del estado.
     */
    public function description(): string
    {
        return match ($this) {
            self::Aprobada => 'Calificación de 70 o más en escala 0-100',
            self::Cursando => 'Inscrita en el ciclo escolar actual',
            self::Reprobada => 'Calificación menor a 70, por repetir en el siguiente periodo',
            self::Disponible => 'Cumple con el 100% de los prerrequisitos requeridos',
            self::Pendiente => 'Faltan prerrequisitos académicos o créditos mínimos requeridos',
        };
    }

    /**
     * Código de color hexadecimal alineado a la guía de diseño institucional de RISA.
     */
    public function colorHex(): string
    {
        return match ($this) {
            self::Aprobada => '#28A745',
            self::Cursando => '#007BFF',
            self::Reprobada => '#DC3545',
            self::Disponible => '#FFC107',
            self::Pendiente => '#6C757D',
        };
    }

    /**
     * Indica si el estado corresponde a una materia acreditada de forma definitiva.
     */
    public function isCompleted(): bool
    {
        return $this === self::Aprobada;
    }

    /**
     * Indica si el estado es candidato a ser inscrito en el periodo lectivo.
     */
    public function isEnrollable(): bool
    {
        return $this === self::Disponible || $this === self::Reprobada;
    }
}
