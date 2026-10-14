<?php

declare(strict_types=1);

namespace App\Services;

use App\Core\AcademicLoad\LoadRecommenderEngine;
use App\Core\AcademicLoad\SemesterProjectionEngine;
use App\Core\Automaton\PrerequisiteAutomaton;
use App\Core\Automaton\SubjectStatus;
use App\Models\Prerequisite;
use App\Models\StudentAcademicHistory;
use App\Models\StudyPlan;
use App\Models\Subject;
use App\Models\User;

class CurriculumService
{
    public function __construct(
        protected PrerequisiteAutomaton $automaton = new PrerequisiteAutomaton,
        protected LoadRecommenderEngine $recommender = new LoadRecommenderEngine,
        protected SemesterProjectionEngine $projectionEngine = new SemesterProjectionEngine
    ) {}

    /**
     * Obtiene o resuelve el estudiante predeterminado para el sistema.
     */
    public function getActiveStudent(?int $userId = null): User
    {
        if ($userId) {
            return User::findOrFail($userId);
        }

        return User::where('role', 'estudiante')->first()
            ?? User::firstOrCreate(
                ['email' => 'ana.ramirez@itcelaya.edu.mx'],
                [
                    'name' => 'Ana Sofía Ramírez Torres',
                    'role' => 'estudiante',
                    'control_number' => '20030582',
                    'career' => 'Ingeniería en Sistemas Computacionales',
                    'current_semester' => 7,
                    'password' => bcrypt('secreto123'),
                ]
            );
    }

    /**
     * Retorna la retícula completa estructurada por semestre para un estudiante.
     *
     * @return array{
     *     estudiante: array{id: int, nombre: string, numeroControl: string, carrera: string, semestreActual: int},
     *     resumen: array{totalCreditos: int, creditosAprobados: int, porcentajeAvance: float, totalMaterias: int},
     *     semestres: list<array{
     *         semestre: int,
     *         totalCreditos: int,
     *         esSemestreActual: bool,
     *         materias: list<array{
     *             id: int,
     *             clave: string,
     *             nombre: string,
     *             creditos: int,
     *             estatus: string,
     *             estatusLabel: string,
     *             colorHex: string,
     *             intento: int,
     *             calificacion: float|null,
     *             tieneComentarios: bool,
     *             totalComentarios: int,
     *             prerrequisitos: list<string>
     *         }>
     *     }>
     * }
     */
    public function getInteractiveGrid(User $student): array
    {
        $plan = StudyPlan::where('is_active', true)->first()
            ?? StudyPlan::first();

        $subjects = Subject::where('study_plan_id', $plan?->id ?? 1)
            ->with(['prerequisites', 'comments'])
            ->orderBy('semester_order')
            ->orderBy('id')
            ->get();

        $histories = StudentAcademicHistory::where('user_id', $student->id)
            ->get()
            ->keyBy('subject_id');

        // Mapa de prerrequisitos [subject_id => [prereq_id, ...]]
        $allPrerequisites = Prerequisite::all();
        $prereqsMap = [];
        foreach ($allPrerequisites as $pr) {
            $prereqsMap[$pr->subject_id][] = $pr->prerequisite_subject_id;
        }

        // Mapear catálogo simple para el autómata
        $catalogoMaterias = [];
        $historialMap = [];
        $estadosActuales = [];

        foreach ($subjects as $s) {
            $catalogoMaterias[$s->id] = [
                'id' => $s->id,
                'code' => $s->code,
                'name' => $s->name,
                'credits' => $s->credits,
                'semester_order' => $s->semester_order,
            ];

            $hist = $histories->get($s->id);
            $historialMap[$s->id] = [
                'status' => $hist?->status,
                'attempts' => $hist?->attempts ?? 1,
                'grade' => $hist?->grade,
            ];

            if ($hist && $hist->status) {
                $estadosActuales[$s->id] = $hist->status;
            }
        }

        // Calcular créditos aprobados
        $creditosAprobados = 0;
        $totalCreditos = 0;
        foreach ($subjects as $s) {
            $totalCreditos += $s->credits;
            $hist = $histories->get($s->id);
            if ($hist && $hist->status === SubjectStatus::Aprobada->value) {
                $creditosAprobados += $s->credits;
            }
        }

        // Agrupar materias por semestre
        $semestresMap = [];
        for ($i = 1; $i <= ($plan?->total_semesters ?? 9); $i++) {
            $semestresMap[$i] = [
                'semestre' => $i,
                'totalCreditos' => 0,
                'esSemestreActual' => false,
                'materias' => [],
            ];
        }

        foreach ($subjects as $s) {
            $hist = $histories->get($s->id);
            $estatusHistorial = $hist?->status;

            // Resolver con el autómata formal
            $estadoResuelto = $this->automaton->resolverEstadoParaMateria(
                materiaId: $s->id,
                estadosPorMateria: $estadosActuales,
                prerrequisitosPorMateria: $prereqsMap,
                creditosAprobadosAcumulados: $creditosAprobados,
                creditosMinimosRequeridos: 0,
                estatusRegistradoEnHistorial: $estatusHistorial
            );

            if ($estadoResuelto === SubjectStatus::Cursando) {
                $semestresMap[$s->semester_order]['esSemestreActual'] = true;
            }

            $semestresMap[$s->semester_order]['totalCreditos'] += $s->credits;

            $prereqNombres = $s->prerequisites->pluck('name')->all();

            $semestresMap[$s->semester_order]['materias'][] = [
                'id' => $s->id,
                'clave' => $s->code,
                'nombre' => $s->name,
                'creditos' => $s->credits,
                'estatus' => $estadoResuelto->value,
                'estatusLabel' => $estadoResuelto->label(),
                'colorHex' => $estadoResuelto->colorHex(),
                'intento' => $hist?->attempts ?? 1,
                'calificacion' => $hist?->grade,
                'tieneComentarios' => $s->comments->count() > 0,
                'totalComentarios' => $s->comments->count(),
                'prerrequisitos' => $prereqNombres,
            ];
        }

        $pct = $totalCreditos > 0 ? round(($creditosAprobados / $totalCreditos) * 100, 1) : 0;

        return [
            'estudiante' => [
                'id' => $student->id,
                'nombre' => $student->name,
                'numeroControl' => $student->control_number ?? '20030582',
                'carrera' => $student->career ?? 'Ingeniería en Sistemas Computacionales',
                'semestreActual' => $student->current_semester,
            ],
            'resumen' => [
                'totalCreditos' => $totalCreditos,
                'creditosAprobados' => $creditosAprobados,
                'porcentajeAvance' => $pct,
                'totalMaterias' => $subjects->count(),
            ],
            'semestres' => array_values($semestresMap),
        ];
    }

    /**
     * Calcula la recomendación de carga y la proyección de egreso.
     */
    public function getRecommendation(User $student): array
    {
        $plan = StudyPlan::where('is_active', true)->first() ?? StudyPlan::first();

        $subjects = Subject::where('study_plan_id', $plan?->id ?? 1)
            ->orderBy('semester_order')
            ->get();

        $histories = StudentAcademicHistory::where('user_id', $student->id)
            ->get()
            ->keyBy('subject_id');

        $allPrerequisites = Prerequisite::all();
        $prereqsMap = [];
        foreach ($allPrerequisites as $pr) {
            $prereqsMap[$pr->subject_id][] = $pr->prerequisite_subject_id;
        }

        $catalogoMaterias = [];
        $historialMap = [];

        foreach ($subjects as $s) {
            $catalogoMaterias[$s->id] = [
                'id' => $s->id,
                'code' => $s->code,
                'name' => $s->name,
                'credits' => $s->credits,
                'semester_order' => $s->semester_order,
            ];

            $hist = $histories->get($s->id);
            $historialMap[$s->id] = [
                'status' => $hist?->status ?? SubjectStatus::Pendiente->value,
                'attempts' => $hist?->attempts ?? 1,
                'grade' => $hist?->grade,
            ];
        }

        $recomendacion = $this->recommender->generarRecomendacion(
            catalogoMaterias: $catalogoMaterias,
            historialEstudiante: $historialMap,
            prerrequisitosPorMateria: $prereqsMap,
            limiteCreditosMaximo: 36,
            limiteCreditosMinimo: 20
        );

        $proyeccion = $this->projectionEngine->proyectarSemestres(
            catalogoMaterias: $catalogoMaterias,
            historialEstudiante: $historialMap,
            prerrequisitosPorMateria: $prereqsMap,
            promedioCreditosPorSemestre: 30
        );

        return [
            'estudiante' => [
                'id' => $student->id,
                'nombre' => $student->name,
                'numeroControl' => $student->control_number,
            ],
            'recomendacionCarga' => $recomendacion,
            'proyeccionEgreso' => $proyeccion,
        ];
    }
}
