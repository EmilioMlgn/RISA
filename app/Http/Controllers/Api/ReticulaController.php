<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreSubjectCommentRequest;
use App\Http\Requests\UpdateAcademicStatusRequest;
use App\Models\StudentAcademicHistory;
use App\Models\Subject;
use App\Models\SubjectComment;
use App\Services\CurriculumService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ReticulaController extends Controller
{
    public function __construct(
        protected CurriculumService $curriculumService
    ) {}

    /**
     * Retorna la retícula completa y el resumen de avance en formato JSON.
     * Compatible con el consumo dinámico desde el frontend o clientes móviles.
     */
    public function index(Request $request): JsonResponse
    {
        $userId = $request->query('student_id') ? (int) $request->query('student_id') : null;
        $student = $this->curriculumService->getActiveStudent($userId);

        $data = $this->curriculumService->getInteractiveGrid($student);

        return response()->json([
            'success' => true,
            'message' => 'Retícula académica consultada exitosamente.',
            'data' => $data,
        ]);
    }

    /**
     * Retorna el detalle completo de una materia (prerrequisitos, subsecuentes, notas y comentarios).
     */
    public function show(Request $request, int $subjectId): JsonResponse
    {
        $userId = $request->query('student_id') ? (int) $request->query('student_id') : null;
        $student = $this->curriculumService->getActiveStudent($userId);

        $subject = Subject::with(['prerequisites', 'unlockedSubjects'])->findOrFail($subjectId);
        $history = StudentAcademicHistory::where('user_id', $student->id)
            ->where('subject_id', $subjectId)
            ->first();

        $comments = SubjectComment::where('user_id', $student->id)
            ->where('subject_id', $subjectId)
            ->orderByDesc('created_at')
            ->get();

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $subject->id,
                'clave' => $subject->code,
                'nombre' => $subject->name,
                'creditos' => $subject->credits,
                'horasTeoria' => $subject->theory_hours,
                'horasPractica' => $subject->practice_hours,
                'semestreSugerido' => $subject->semester_order,
                'estatus' => $history?->status ?? 'P',
                'calificacion' => $history?->grade,
                'intento' => $history?->attempts ?? 1,
                'prerrequisitos' => $subject->prerequisites->map(fn ($p) => [
                    'id' => $p->id,
                    'nombre' => $p->name,
                    'clave' => $p->code,
                ]),
                'materiasSubsecuentes' => $subject->unlockedSubjects->map(fn ($u) => [
                    'id' => $u->id,
                    'nombre' => $u->name,
                    'clave' => $u->code,
                ]),
                'comentariosDocentes' => $comments->map(fn ($c) => [
                    'id' => $c->id,
                    'autor' => $c->author_name,
                    'rol' => $c->author_role,
                    'comentario' => $c->comment,
                    'fecha' => $c->created_at->format('d/m/Y H:i'),
                ]),
            ],
        ]);
    }

    /**
     * Registra un comentario u observación permanente de un docente/asesor.
     */
    public function storeComment(StoreSubjectCommentRequest $request, int $subjectId): JsonResponse
    {
        $validated = $request->validated();
        $student = $this->curriculumService->getActiveStudent($validated['student_id'] ?? null);

        $subject = Subject::findOrFail($subjectId);

        $comment = SubjectComment::create([
            'user_id' => $student->id,
            'subject_id' => $subject->id,
            'author_id' => auth()->id(),
            'author_name' => $validated['author_name'],
            'author_role' => $validated['author_role'],
            'comment' => $validated['comment'],
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Comentario académico registrado exitosamente en el historial del estudiante.',
            'data' => [
                'id' => $comment->id,
                'autor' => $comment->author_name,
                'rol' => $comment->author_role,
                'comentario' => $comment->comment,
                'fecha' => $comment->created_at->format('d/m/Y H:i'),
            ],
        ], 201);
    }

    /**
     * Actualiza el estatus o calificación de una materia para el alumno (Simulador / Actualización).
     */
    public function updateStatus(UpdateAcademicStatusRequest $request): JsonResponse
    {
        $validated = $request->validated();
        $student = $this->curriculumService->getActiveStudent($validated['student_id'] ?? null);

        $history = StudentAcademicHistory::updateOrCreate(
            [
                'user_id' => $student->id,
                'subject_id' => $validated['subject_id'],
            ],
            [
                'status' => $validated['status'],
                'grade' => $validated['grade'] ?? null,
                'attempts' => $validated['attempts'] ?? 1,
            ]
        );

        // Retornar la retícula reevaluada en tiempo real
        $grid = $this->curriculumService->getInteractiveGrid($student);

        return response()->json([
            'success' => true,
            'message' => 'Historial actualizado y autómata de prerrequisitos recalculado.',
            'data' => $grid,
        ]);
    }
}
