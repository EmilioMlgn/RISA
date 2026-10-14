<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Core\Automaton\CurricularEvent;
use App\Core\Automaton\PrerequisiteAutomaton;
use App\Core\Automaton\SubjectStatus;
use PHPUnit\Framework\TestCase;

class PrerequisiteAutomatonTest extends TestCase
{
    protected PrerequisiteAutomaton $automaton;

    protected function setUp(): void
    {
        parent::setUp();
        $this->automaton = new PrerequisiteAutomaton;
    }

    /**
     * Prueba que una materia aprobada ('A') es un estado terminal e inmutable.
     */
    public function test_materia_aprobada_es_inmutable(): void
    {
        $estado = $this->automaton->transicion(
            estadoActual: SubjectStatus::Aprobada,
            evento: CurricularEvent::NoAcreditar,
            calificacion: 50.0
        );

        $this->assertSame(SubjectStatus::Aprobada, $estado);
    }

    /**
     * Prueba que una materia pendiente ('P') transita a disponible ('D')
     * cuando se satisfacen todos sus prerrequisitos y créditos mínimos.
     */
    public function test_materia_pendiente_transita_a_disponible_si_cumple_requisitos(): void
    {
        $estado = $this->automaton->transicion(
            estadoActual: SubjectStatus::Pendiente,
            evento: CurricularEvent::EvaluarRequisitos,
            cumplePrerrequisitos: true,
            cumpleCreditosMinimos: true
        );

        $this->assertSame(SubjectStatus::Disponible, $estado);
    }

    /**
     * Prueba que una materia pendiente ('P') permanece pendiente si falta algún prerrequisito.
     */
    public function test_materia_pendiente_permanece_si_falta_requisito(): void
    {
        $estado = $this->automaton->transicion(
            estadoActual: SubjectStatus::Pendiente,
            evento: CurricularEvent::EvaluarRequisitos,
            cumplePrerrequisitos: false,
            cumpleCreditosMinimos: true
        );

        $this->assertSame(SubjectStatus::Pendiente, $estado);
    }

    /**
     * Prueba que una materia cursando ('C') pasa a aprobada ('A') con calificación >= 70.
     */
    public function test_materia_cursando_acredita_con_calificacion_aprobatoria(): void
    {
        $estado = $this->automaton->transicion(
            estadoActual: SubjectStatus::Cursando,
            evento: CurricularEvent::Acreditar,
            calificacion: 85.0
        );

        $this->assertSame(SubjectStatus::Aprobada, $estado);
    }

    /**
     * Prueba que una materia cursando ('C') pasa a reprobada ('R') si no acredita.
     */
    public function test_materia_cursando_pasa_a_reprobada_si_no_acredita(): void
    {
        $estado = $this->automaton->transicion(
            estadoActual: SubjectStatus::Cursando,
            evento: CurricularEvent::NoAcreditar,
            calificacion: 58.0
        );

        $this->assertSame(SubjectStatus::Reprobada, $estado);
    }

    /**
     * Prueba la resolución completa de una materia cruzando el grafo de prerrequisitos.
     */
    public function test_resolver_estado_con_prerrequisitos(): void
    {
        // Supongamos que Cálculo Integral (ID 7) depende de Cálculo Diferencial (ID 2)
        $materiaId = 7;
        $prerrequisitos = [7 => [2]];

        // Caso 1: Cálculo Diferencial aún no aprobado -> Pendiente
        $estadosHistorial = [2 => 'C'];
        $res = $this->automaton->resolverEstadoParaMateria(
            materiaId: $materiaId,
            estadosPorMateria: $estadosHistorial,
            prerrequisitosPorMateria: $prerrequisitos,
            creditosAprobadosAcumulados: 10
        );
        $this->assertSame(SubjectStatus::Pendiente, $res);

        // Caso 2: Cálculo Diferencial acreditado -> Disponible
        $estadosHistorial = [2 => 'A'];
        $res = $this->automaton->resolverEstadoParaMateria(
            materiaId: $materiaId,
            estadosPorMateria: $estadosHistorial,
            prerrequisitosPorMateria: $prerrequisitos,
            creditosAprobadosAcumulados: 15
        );
        $this->assertSame(SubjectStatus::Disponible, $res);
    }
}
