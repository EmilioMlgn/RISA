<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AcademicLoadRecommendation;
use App\Services\CurriculumService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class RecommendationController extends Controller
{
    public function __construct(
        protected CurriculumService $curriculumService
    ) {}

    /**
     * Ejecuta el motor de autómata y genera la recomendación de carga y proyección.
     */
    public function recommend(Request $request): JsonResponse
    {
        $userId = $request->query('student_id') ? (int) $request->query('student_id') : null;
        $student = $this->curriculumService->getActiveStudent($userId);

        $result = $this->curriculumService->getRecommendation($student);

        return response()->json([
            'success' => true,
            'message' => 'Recomendación de carga académica generada por el autómata determinista.',
            'data' => $result,
        ]);
    }

    /**
     * Almacena de forma persistente la recomendación para auditoría o planeación semestral.
     */
    public function save(Request $request): JsonResponse
    {
        $userId = $request->input('student_id') ? (int) $request->input('student_id') : null;
        $student = $this->curriculumService->getActiveStudent($userId);

        $result = $this->curriculumService->getRecommendation($student);

        $saved = AcademicLoadRecommendation::create([
            'user_id' => $student->id,
            'period_target' => $request->input('period_target', '2026-2'),
            'total_recommended_credits' => $result['recomendacionCarga']['creditosTotales'],
            'projected_semesters_remaining' => $result['proyeccionEgreso']['semestresRestantesEstimados'],
            'recommended_subjects_payload' => $result['recomendacionCarga']['materiasRecomendadas'],
            'diagnostic_notes' => implode("\n", $result['recomendacionCarga']['diagnostico']),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Propuesta de carga académica guardada exitosamente.',
            'data' => $saved,
        ], 201);
    }
}
