<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web;

use App\Core\AcademicLoad\LoadRecommenderEngine;
use App\Core\AcademicLoad\SemesterProjectionEngine;
use App\Http\Controllers\Controller;
use App\Models\StudyPlan;
use App\Models\Subject;
use App\Services\CurriculumService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class PlaneacionController extends Controller
{
    /** Rango que el simulador permite explorar, en créditos por semestre. */
    public const SIMULADOR_MIN = 12;

    public const SIMULADOR_MAX = LoadRecommenderEngine::CREDITOS_MAXIMOS;

    public function __construct(
        protected CurriculumService $curriculumService
    ) {}

    /**
     * Simulador de escenarios: recalcula la recomendación y la proyección de egreso
     * con un límite de créditos distinto al institucional.
     */
    public function simulador(Request $request): View
    {
        $userId = $request->query('student_id') ? (int) $request->query('student_id') : null;
        $student = $this->curriculumService->getActiveStudent($userId);

        $limite = $this->entreLimites($request->query('creditos'), LoadRecommenderEngine::CREDITOS_MAXIMOS);
        $ritmo = $this->entreLimites($request->query('ritmo'), SemesterProjectionEngine::CREDITOS_PROMEDIO);

        return view('planeacion.simulador', [
            'student' => $student,
            'grid' => $this->curriculumService->getInteractiveGrid($student),
            'base' => $this->curriculumService->getRecommendation($student),
            'escenario' => $this->curriculumService->getRecommendation($student, $limite, $ritmo),
            'limite' => $limite,
            'ritmo' => $ritmo,
            'rango' => [self::SIMULADOR_MIN, self::SIMULADOR_MAX],
            'limitesInstitucionales' => [
                'maximo' => LoadRecommenderEngine::CREDITOS_MAXIMOS,
                'minimo' => LoadRecommenderEngine::CREDITOS_MINIMOS,
                'promedio' => SemesterProjectionEngine::CREDITOS_PROMEDIO,
            ],
        ]);
    }

    /**
     * Consulta de las reglas que aplica el motor de recomendación, con los
     * prerrequisitos vigentes del plan de estudios activo.
     */
    public function reglas(): View
    {
        $plan = StudyPlan::where('is_active', true)->first() ?? StudyPlan::first();

        $materias = Subject::active()
            ->where('study_plan_id', $plan?->id)
            ->with(['prerequisites', 'unlockedSubjects'])
            ->orderBy('semester_order')
            ->orderBy('code')
            ->get();

        return view('planeacion.reglas', [
            'plan' => $plan,
            'materias' => $materias,
            'limites' => [
                'maximo' => LoadRecommenderEngine::CREDITOS_MAXIMOS,
                'minimo' => LoadRecommenderEngine::CREDITOS_MINIMOS,
                'promedio' => SemesterProjectionEngine::CREDITOS_PROMEDIO,
                'aprobatoria' => 70,
            ],
        ]);
    }

    private function entreLimites(mixed $valor, int $porDefecto): int
    {
        if (! is_numeric($valor)) {
            return $porDefecto;
        }

        return max(self::SIMULADOR_MIN, min(self::SIMULADOR_MAX, (int) $valor));
    }
}
