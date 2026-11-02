<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Control de acceso por rol en el servidor.
 * Invitado: 401 (JSON) o redirección a /login. Rol no autorizado: 403.
 */
class EnsureUserHasRole
{
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();

        if (! $user) {
            if ($request->expectsJson()) {
                return response()->json(['success' => false, 'message' => 'Autenticación requerida.'], 401);
            }

            return redirect()->guest(route('login'));
        }

        if (! in_array($user->role, $roles, true)) {
            \App\Models\CatalogAuditLog::create([
                'user_id' => $user->id,
                'user_name' => $user->name,
                'user_role' => $user->role,
                'action' => 'Acceso denegado (403)',
                'detail' => 'Intento de '.$request->method().' /'.$request->path().' rechazado por rol sin privilegios.',
            ]);
            $mensaje = 'No cuentas con permisos para modificar el catálogo académico.';
            if ($request->expectsJson()) {
                return response()->json(['success' => false, 'message' => $mensaje], 403);
            }
            abort(403, $mensaje);
        }

        return $next($request);
    }
}
