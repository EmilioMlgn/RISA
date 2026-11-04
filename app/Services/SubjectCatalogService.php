<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\CatalogAuditLog;
use App\Models\Prerequisite;
use App\Models\StudyPlan;
use App\Models\Subject;
use App\Models\User;
use Illuminate\Support\Facades\DB;

/**
 * Reglas de negocio del catálogo de materias: validación, duplicados,
 * prerrequisitos, baja lógica / eliminación física y bitácora de auditoría.
 */
class SubjectCatalogService
{
    public const ACTIVA = 'Activa';

    public const INACTIVA = 'Inactiva';

    public function plan(): StudyPlan
    {
        return StudyPlan::where('is_active', true)->first() ?? StudyPlan::firstOrFail();
    }

    /** Normaliza espacios y mayúsculas de la clave. */
    public function normalizar(array $in): array
    {
        $limpio = fn ($v) => trim((string) preg_replace('/\s+/u', ' ', (string) ($v ?? '')));

        return [
            'clave' => mb_strtoupper($limpio($in['clave'] ?? '')),
            'nombre' => $limpio($in['nombre'] ?? ''),
            'creditos' => $limpio($in['creditos'] ?? ''),
            'semestre' => $limpio($in['semestre'] ?? ''),
            'prerrequisitos' => array_values(array_map(
                fn ($v) => $limpio($v),
                is_array($in['prerrequisitos'] ?? null) ? $in['prerrequisitos'] : []
            )),
        ];
    }

    /** Clave comparable: sin guiones ni espacios, en mayúsculas (SCD-1008 == SCD1008). */
    public function claveCanonica(string $clave): string
    {
        return mb_strtoupper(str_replace(['-', ' '], '', $clave));
    }

    /**
     * Valida los datos. Devuelve [campo => mensaje].
     *
     * @return array<string,string>
     */
    public function validar(array $d, ?Subject $actual = null): array
    {
        $e = [];
        $maxSem = $this->plan()->total_semesters ?: 9;

        // Clave (solo se valida en alta; en edición no es modificable)
        if ($actual === null) {
            if ($d['clave'] === '') {
                $e['clave'] = 'El campo Clave es obligatorio.';
            } elseif (! preg_match('/^[A-Z0-9]{5,10}$/', $d['clave'])) {
                $e['clave'] = 'La clave debe tener de 5 a 10 caracteres entre letras mayúsculas y números, sin espacios.';
            } elseif ($this->claveExiste($d['clave'])) {
                $e['clave'] = 'Ya existe una materia con la clave '.$d['clave'].'.';
            }
        }

        // Nombre
        $len = mb_strlen($d['nombre']);
        if ($d['nombre'] === '') {
            $e['nombre'] = 'El campo Nombre es obligatorio.';
        } elseif ($len < 5 || $len > 100
            || ! preg_match('/^[\p{L}0-9 .,()\-]+$/u', $d['nombre'])
            || preg_match('/^[0-9 .,()\-]+$/', $d['nombre'])) {
            $e['nombre'] = 'El nombre debe tener entre 5 y 100 caracteres válidos y no puede contener solo números.';
        }

        // Créditos
        $creditosOk = false;
        if ($d['creditos'] === '') {
            $e['creditos'] = 'El campo Créditos es obligatorio.';
        } elseif (! preg_match('/^\d+$/', $d['creditos']) || (int) $d['creditos'] < 1 || (int) $d['creditos'] > 10) {
            $e['creditos'] = 'Los créditos deben ser un número entero entre 1 y 10.';
        } else {
            $creditosOk = true;
        }

        // Semestre
        $semestreOk = false;
        if ($d['semestre'] === '') {
            $e['semestre'] = 'El campo Semestre es obligatorio.';
        } elseif (! preg_match('/^\d+$/', $d['semestre']) || (int) $d['semestre'] < 1 || (int) $d['semestre'] > $maxSem) {
            $e['semestre'] = 'El semestre debe ser un número entero entre 1 y '.$maxSem.'.';
        } else {
            $semestreOk = true;
        }

        // Prerrequisitos
        $errPre = $this->validarPrerrequisitos($d, $actual, $semestreOk ? (int) $d['semestre'] : null);
        if ($errPre !== null) {
            $e['prerrequisitos'] = $errPre;
        }

        return $e;
    }

    private function claveExiste(string $clave): bool
    {
        $canon = $this->claveCanonica($clave);

        return Subject::pluck('code')->contains(fn ($c) => $this->claveCanonica($c) === $canon);
    }

    private function validarPrerrequisitos(array $d, ?Subject $actual, ?int $semestre): ?string
    {
        $generico = 'El prerrequisito seleccionado genera una dependencia circular o no es válido';
        $ids = $d['prerrequisitos'];
        $vistos = [];

        foreach ($ids as $id) {
            if ($id === '' || ! ctype_digit($id)) {
                return $generico.'.';
            }
            $id = (int) $id;
            $pre = Subject::find($id);
            if (! $pre || $pre->status !== self::ACTIVA || ($actual && $pre->id === $actual->id) || isset($vistos[$id])) {
                return $generico.'.';
            }
            if ($semestre !== null && $pre->semester_order >= $semestre) {
                return $generico." (debe pertenecer a un semestre menor al semestre {$semestre}).";
            }
            $vistos[$id] = true;
        }

        if ($actual && $semestre !== null) {
            $dep = Subject::whereIn('id', Prerequisite::where('prerequisite_subject_id', $actual->id)->pluck('subject_id'))
                ->where('semester_order', '<=', $semestre)->first();
            if ($dep) {
                return $generico." (la materia dependiente {$dep->code} está en semestre {$dep->semester_order}).";
            }
        }

        if ($actual && $ids && $this->hayCiclo($actual->id, array_map('intval', $ids))) {
            return $generico.'.';
        }

        return null;
    }

    /** Detecta si, tras asignar $nuevos como prerrequisitos de $origen, se vuelve alcanzable $origen. */
    private function hayCiclo(int $origen, array $nuevos): bool
    {
        $mapa = Prerequisite::whereNotNull('prerequisite_subject_id')->get()
            ->groupBy('subject_id')->map(fn ($g) => $g->pluck('prerequisite_subject_id')->all())->all();
        $mapa[$origen] = $nuevos;

        $pila = $nuevos;
        $visitados = [];
        while ($pila) {
            $n = array_pop($pila);
            if ($n === $origen) {
                return true;
            }
            if (isset($visitados[$n])) {
                continue;
            }
            $visitados[$n] = true;
            foreach ($mapa[$n] ?? [] as $s) {
                $pila[] = $s;
            }
        }

        return false;
    }

    /**
     * Avisos que requieren confirmación explícita antes de guardar.
     *
     * @return list<string>
     */
    public function avisosDeConfirmacion(array $d, ?Subject $actual): array
    {
        $avisos = [];
        $homonima = Subject::whereRaw('lower(name) = ?', [mb_strtolower($d['nombre'])])
            ->where('semester_order', (int) $d['semestre'])
            ->when($actual, fn ($q) => $q->where('id', '!=', $actual->id))
            ->first();
        if ($homonima) {
            $avisos[] = "Ya existe la materia «{$homonima->name}» ({$homonima->code}) en el semestre {$d['semestre']}.";
        }
        if ($actual) {
            if ((int) $d['creditos'] !== $actual->credits) {
                $avisos[] = "Cambiar los créditos ({$actual->credits} → {$d['creditos']}) recalculará proyecciones y recomendaciones futuras; el historial ya cursado conservará sus créditos originales.";
            }
            if ((int) $d['semestre'] !== $actual->semester_order) {
                $avisos[] = "Cambiar el semestre ({$actual->semester_order} → {$d['semestre']}) reubicará la materia en la retícula oficial y recalculará la proyección de egreso.";
            }
        }

        return $avisos;
    }

    public function crear(array $d, User $user): Subject
    {
        return DB::transaction(function () use ($d, $user) {
            $subject = Subject::create([
                'study_plan_id' => $this->plan()->id,
                'code' => $d['clave'],
                'name' => $d['nombre'],
                'credits' => (int) $d['creditos'],
                'semester_order' => (int) $d['semestre'],
                'status' => self::ACTIVA,
            ]);
            $this->sincronizarPrerrequisitos($subject, $d['prerrequisitos']);
            $this->bitacora($user, 'Alta', "{$subject->code}: {$subject->name} (Sem. {$subject->semester_order}, {$subject->credits} cr.)");

            return $subject;
        });
    }

    /** @return list<string> cambios aplicados (vacío si no hubo). */
    public function actualizar(Subject $s, array $d, User $user): array
    {
        return DB::transaction(function () use ($s, $d, $user) {
            $antes = $s->prerequisites()->pluck('subjects.code')->sort()->values()->all();
            $cambios = [];
            if ($s->name !== $d['nombre']) {
                $cambios[] = "Nombre: «{$s->name}» → «{$d['nombre']}»";
            }
            if ($s->credits !== (int) $d['creditos']) {
                $cambios[] = "Créditos: {$s->credits} → {$d['creditos']}";
            }
            if ($s->semester_order !== (int) $d['semestre']) {
                $cambios[] = "Semestre: {$s->semester_order} → {$d['semestre']}";
            }

            $s->update([
                'name' => $d['nombre'],
                'credits' => (int) $d['creditos'],
                'semester_order' => (int) $d['semestre'],
            ]);
            $this->sincronizarPrerrequisitos($s, $d['prerrequisitos']);

            $despues = $s->prerequisites()->pluck('subjects.code')->sort()->values()->all();
            if ($antes !== $despues) {
                $cambios[] = 'Prerrequisitos: ['.(implode(', ', $antes) ?: 'ninguno').'] → ['.(implode(', ', $despues) ?: 'ninguno').']';
            }
            if ($cambios) {
                $this->bitacora($user, "Edición ({$s->code})", implode(' | ', $cambios));
            }

            return $cambios;
        });
    }

    private function sincronizarPrerrequisitos(Subject $s, array $ids): void
    {
        Prerequisite::where('subject_id', $s->id)->where('type', 'obligatorio')
            ->whereNotNull('prerequisite_subject_id')->delete();
        foreach ($ids as $id) {
            Prerequisite::create([
                'subject_id' => $s->id,
                'prerequisite_subject_id' => (int) $id,
                'type' => 'obligatorio',
            ]);
        }
    }

    /** Materias que usan a $s como prerrequisito. */
    public function dependientes(Subject $s)
    {
        return Subject::whereIn('id', Prerequisite::where('prerequisite_subject_id', $s->id)->pluck('subject_id'))
            ->orderBy('code')->get();
    }

    /** Resultado esperado de eliminar: bloqueada | baja_logica | fisica | ya_inactiva. */
    public function impactoEliminacion(Subject $s): array
    {
        $deps = $this->dependientes($s);
        $historial = $s->academicHistories()->count();

        $modo = match (true) {
            $deps->isNotEmpty() => 'bloqueada',
            $historial > 0 && $s->status === self::INACTIVA => 'ya_inactiva',
            $historial > 0 => 'baja_logica',
            default => 'fisica',
        };

        return [
            'modo' => $modo,
            'dependientes' => $deps->map(fn ($d) => ['clave' => $d->code, 'nombre' => $d->name])->all(),
            'alumnosConHistorial' => $historial,
        ];
    }

    public function eliminar(Subject $s, User $user): string
    {
        $impacto = $this->impactoEliminacion($s);

        return DB::transaction(function () use ($s, $user, $impacto) {
            if ($impacto['modo'] === 'baja_logica') {
                $s->update(['status' => self::INACTIVA]);
                $this->bitacora($user, 'Baja lógica', "{$s->code}: Estatus Activa → Inactiva (con {$impacto['alumnosConHistorial']} registros en historial)");
            } else {
                $this->bitacora($user, 'Eliminación física', "{$s->code}: «{$s->name}» eliminada del catálogo");
                $s->delete();
            }

            return $impacto['modo'];
        });
    }

    /** Devuelve null si se reactivó, o mensaje de error. */
    public function reactivar(Subject $s, User $user): ?string
    {
        $inactivo = $s->prerequisites()->where('subjects.status', '!=', self::ACTIVA)->first();
        if ($inactivo) {
            return "No se puede reactivar {$s->code}: su prerrequisito {$inactivo->code} se encuentra inactivo.";
        }
        $s->update(['status' => self::ACTIVA]);
        $this->bitacora($user, 'Reactivación', "{$s->code}: Estatus Inactiva → Activa");

        return null;
    }

    public function bitacora(User $user, string $accion, string $detalle): void
    {
        CatalogAuditLog::create([
            'user_id' => $user->id,
            'user_name' => $user->name,
            'user_role' => $user->role,
            'action' => $accion,
            'detail' => $detalle,
        ]);
    }

    public function serializar(Subject $s): array
    {
        $s->loadMissing('prerequisites');

        return [
            'id' => $s->id,
            'clave' => $s->code,
            'nombre' => $s->name,
            'creditos' => $s->credits,
            'semestre' => $s->semester_order,
            'estatus' => $s->status,
            'prerrequisitos' => $s->prerequisites->map(fn ($p) => ['id' => $p->id, 'clave' => $p->code, 'nombre' => $p->name])->values()->all(),
            'alumnosConHistorial' => (int) ($s->history_count ?? $s->academicHistories()->count()),
        ];
    }
}
