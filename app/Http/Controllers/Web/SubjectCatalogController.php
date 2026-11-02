<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\CatalogAuditLog;
use App\Models\Subject;
use App\Services\SubjectCatalogService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SubjectCatalogController extends Controller
{
    public function __construct(protected SubjectCatalogService $catalogo) {}

    public function index()
    {
        return view('catalogo.index', ['plan' => $this->catalogo->plan()]);
    }

    public function list(): JsonResponse
    {
        $materias = Subject::with('prerequisites')->withCount('academicHistories as history_count')
            ->orderBy('semester_order')->orderBy('code')->get();

        return response()->json([
            'success' => true,
            'data' => $materias->map(fn ($s) => $this->catalogo->serializar($s))->values(),
            'bitacoraTotal' => CatalogAuditLog::count(),
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $d = $this->catalogo->normalizar($request->all());
        if ($errores = $this->catalogo->validar($d)) {
            return $this->fallo($errores);
        }
        if ($r = $this->requiereConfirmacion($request, $d, null)) {
            return $r;
        }
        $s = $this->catalogo->crear($d, $request->user());

        return response()->json([
            'success' => true,
            'message' => "Materia {$s->code} («{$s->name}») dada de alta en el semestre {$s->semester_order}. Se recalculó el total de créditos y las recomendaciones del plan.",
            'data' => $this->catalogo->serializar($s),
        ], 201);
    }

    public function update(Request $request, Subject $subject): JsonResponse
    {
        $d = $this->catalogo->normalizar($request->all());
        $d['clave'] = $subject->code; // la clave no es editable
        if ($errores = $this->catalogo->validar($d, $subject)) {
            return $this->fallo($errores);
        }
        if ($r = $this->requiereConfirmacion($request, $d, $subject)) {
            return $r;
        }
        $cambios = $this->catalogo->actualizar($subject, $d, $request->user());

        return response()->json([
            'success' => true,
            'message' => $cambios
                ? "Cambios guardados en «{$subject->name}» ({$subject->code}). El ajuste quedó registrado en la bitácora."
                : "No hubo cambios por guardar en la materia {$subject->code}.",
            'data' => $this->catalogo->serializar($subject->fresh()),
        ]);
    }

    public function impacto(Subject $subject): JsonResponse
    {
        return response()->json(['success' => true, 'data' => $this->catalogo->impactoEliminacion($subject)]);
    }

    public function destroy(Request $request, Subject $subject): JsonResponse
    {
        $impacto = $this->catalogo->impactoEliminacion($subject);

        if ($impacto['modo'] === 'bloqueada') {
            $lista = collect($impacto['dependientes'])->map(fn ($d) => "{$d['clave']} ({$d['nombre']})")->implode(', ');

            return response()->json([
                'success' => false,
                'message' => "No se puede eliminar: es prerrequisito de {$lista}.",
            ], 422);
        }
        if ($impacto['modo'] === 'ya_inactiva') {
            return response()->json(['success' => false, 'message' => "La materia {$subject->code} ya se encuentra inactiva."], 422);
        }
        if (! $request->boolean('confirmado')) {
            return response()->json([
                'success' => false,
                'requiereConfirmacion' => true,
                'message' => 'Se requiere confirmación explícita antes de eliminar.',
                'data' => $impacto,
            ], 409);
        }

        $modo = $this->catalogo->eliminar($subject, $request->user());

        return response()->json([
            'success' => true,
            'modo' => $modo,
            'message' => $modo === 'baja_logica'
                ? 'Materia dada de baja. El historial no se modificó.'
                : 'Materia eliminada correctamente.',
        ]);
    }

    public function reactivar(Request $request, Subject $subject): JsonResponse
    {
        if ($error = $this->catalogo->reactivar($subject, $request->user())) {
            return response()->json(['success' => false, 'message' => $error], 422);
        }

        return response()->json([
            'success' => true,
            'message' => "Materia {$subject->code} («{$subject->name}») reactivada exitosamente en la retícula.",
        ]);
    }

    public function bitacora(): JsonResponse
    {
        $filas = CatalogAuditLog::orderByDesc('id')->limit(200)->get()->map(fn ($b) => [
            'fecha' => $b->created_at->format('Y-m-d H:i'),
            'usuario' => "{$b->user_name} ({$b->user_role})",
            'accion' => $b->action,
            'detalle' => $b->detail,
        ]);

        return response()->json(['success' => true, 'data' => $filas]);
    }

    private function fallo(array $errores): JsonResponse
    {
        return response()->json([
            'success' => false,
            'message' => 'Los datos enviados no son válidos.',
            'errors' => $errores,
        ], 422);
    }

    private function requiereConfirmacion(Request $request, array $d, ?Subject $actual): ?JsonResponse
    {
        $avisos = $this->catalogo->avisosDeConfirmacion($d, $actual);
        if ($avisos && ! $request->boolean('confirmado')) {
            return response()->json([
                'success' => false,
                'requiereConfirmacion' => true,
                'message' => 'Confirmación requerida antes de guardar.',
                'avisos' => $avisos,
            ], 409);
        }

        return null;
    }
}
