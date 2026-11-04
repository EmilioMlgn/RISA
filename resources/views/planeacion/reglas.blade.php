@php
    $admin = auth()->user();
    $conPre = $materias->filter(fn ($m) => $m->prerequisites->isNotEmpty())->count();
@endphp
@extends('layouts.risa', [
    'activo' => 'reglas',
    'perfil' => [
        'nombre' => $admin->name,
        'etiqueta' => 'Administrador',
        'lineas' => [$admin->email, 'Administración académica'],
        'avance' => null,
    ],
])

@section('titulo', 'Reglas de recomendación')

@push('styles')
<style>
    .bloque { padding: 22px; margin-bottom: 20px; }
    .prioridades { display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 12px; margin: 0; padding: 0; list-style: none; counter-reset: p; }
    .prioridades li { position: relative; padding: 14px 14px 14px 52px; border: 1px solid var(--line); border-radius: 12px; background: #fff; counter-increment: p; font-size: 0.85rem; color: var(--muted); }
    .prioridades li::before { content: counter(p); position: absolute; left: 14px; top: 14px; display: grid; place-items: center; width: 26px; height: 26px; border-radius: 50%; background: var(--navy); color: #fff; font-weight: 800; font-size: 0.8rem; }
    .prioridades b { display: block; color: var(--ink); font-size: 0.9rem; margin-bottom: 2px; }
    .filtros { display: grid; grid-template-columns: 1fr 220px; gap: 12px; margin-bottom: 14px; }
    @media (max-width: 640px) { .filtros { grid-template-columns: 1fr; } }
    .sin { color: var(--muted); font-size: 0.8125rem; }
    .desbloquea { display: inline-block; min-width: 28px; padding: 1px 8px; border-radius: 999px; background: rgba(0,123,255,0.08); color: #0058B8; font-weight: 700; font-size: 0.78rem; text-align: center; }
    .desbloquea.cero { background: var(--soft); color: var(--muted); }
    .tabla td.mat { min-width: 220px; }
    .banner a { color: var(--navy); font-weight: 700; }
</style>
@endpush

@section('content')
<div class="wrap">
    <div class="hero">
        <div>
            <span class="eyebrow"><img src="{{ asset('images/tecnm-emblema.png') }}" alt="" aria-hidden="true">Administración</span>
            <h1>Reglas de recomendación</h1>
            <p class="sub">Reglas que aplica el motor al sugerir la carga académica del plan {{ $plan?->name }} ({{ $plan?->code }}).</p>
        </div>
        <div class="actions">
            <a class="btn-action primary" href="{{ route('catalogo.materias') }}" style="text-decoration:none;">
                <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" stroke-linejoin="round"><path d="M12 20h9M16.5 3.5a2.1 2.1 0 0 1 3 3L7 19l-4 1 1-4z"/></svg>
                Editar prerrequisitos en el catálogo
            </a>
        </div>
    </div>

    <section class="stats" aria-label="Límites del motor">
        <div class="stat" style="--k: var(--navy)">
            <small>Carga máxima por semestre</small>
            <strong>{{ $limites['maximo'] }} <span>créditos</span></strong>
        </div>
        <div class="stat" style="--k: var(--d)">
            <small>Carga mínima institucional</small>
            <strong>{{ $limites['minimo'] }} <span>créditos</span></strong>
        </div>
        <div class="stat" style="--k: var(--a)">
            <small>Calificación mínima aprobatoria</small>
            <strong>{{ $limites['aprobatoria'] }}</strong>
        </div>
        <div class="stat" style="--k: var(--c)">
            <small>Materias con prerrequisitos</small>
            <strong>{{ $conPre }} <span>de {{ $materias->count() }} activas</span></strong>
        </div>
    </section>

    <p class="banner info">Los límites de créditos están definidos en el motor de recomendación y por ahora no se pueden cambiar desde esta pantalla. Los prerrequisitos se editan en el <a href="{{ route('catalogo.materias') }}">catálogo de materias</a> y se aplican de inmediato en la retícula y en las recomendaciones.</p>

    <section class="panel bloque" aria-labelledby="t-prior">
        <div class="seccion-h"><h2 id="t-prior">Cómo prioriza el motor</h2></div>
        <ol class="prioridades">
            <li><b>Materias reprobadas</b>Se recomiendan primero para evitar bajas reglamentarias y rezago.</li>
            <li><b>Ruta crítica</b>Después, las materias que desbloquean más asignaturas futuras.</li>
            <li><b>Semestre sugerido</b>Luego, el orden del plan de estudios.</li>
            <li><b>Límite de créditos</b>Se agregan materias hasta {{ $limites['maximo'] }} créditos; si la carga queda bajo {{ $limites['minimo'] }}, se avisa al estudiante.</li>
        </ol>
        <p class="sub" style="margin-top:14px; font-size:0.82rem;">Una materia solo se recomienda si todos sus prerrequisitos están aprobados. La proyección de egreso supone {{ $limites['promedio'] }} créditos por semestre.</p>
    </section>

    <section class="panel bloque" aria-labelledby="t-mat">
        <div class="seccion-h">
            <h2 id="t-mat">Prerrequisitos por materia</h2>
            <span class="sub" id="conteo">{{ $materias->count() }} materias</span>
        </div>
        <div class="filtros">
            <div class="campo">
                <label for="buscar">Buscar por clave o nombre</label>
                <input id="buscar" type="text" placeholder="Ejemplo: SCD-1015 o Autómatas" autocomplete="off">
            </div>
            <div class="campo">
                <label for="semestre">Semestre</label>
                <select id="semestre">
                    <option value="">Todos</option>
                    @foreach($materias->pluck('semester_order')->unique()->sort() as $s)
                        <option value="{{ $s }}">Semestre {{ $s }}</option>
                    @endforeach
                </select>
            </div>
        </div>
        <div class="tabla-w">
            <table class="tabla">
                <thead>
                    <tr><th>Clave</th><th>Materia</th><th class="num">Sem.</th><th class="num">Créditos</th><th>Prerrequisitos</th><th class="num">Desbloquea</th></tr>
                </thead>
                <tbody id="cuerpo">
                    @foreach($materias as $m)
                        <tr data-q="{{ mb_strtolower($m->code . ' ' . $m->name) }}" data-s="{{ $m->semester_order }}">
                            <td><code>{{ $m->code }}</code></td>
                            <td class="mat"><strong>{{ $m->name }}</strong></td>
                            <td class="num">{{ $m->semester_order }}</td>
                            <td class="num">{{ $m->credits }}</td>
                            <td>
                                @forelse($m->prerequisites as $p)
                                    <span class="chip" title="{{ $p->code }}">{{ $p->name }}</span>
                                @empty
                                    <span class="sin">Sin prerrequisitos</span>
                                @endforelse
                            </td>
                            <td class="num"><span class="desbloquea {{ $m->unlockedSubjects->isEmpty() ? 'cero' : '' }}">{{ $m->unlockedSubjects->count() }}</span></td>
                        </tr>
                    @endforeach
                    <tr id="sin-resultados" hidden><td colspan="6" class="sin">Ninguna materia coincide con la búsqueda.</td></tr>
                </tbody>
            </table>
        </div>
    </section>
</div>
@endsection

@push('scripts')
<script>
const buscar = document.getElementById('buscar');
const semestre = document.getElementById('semestre');
function filtrar() {
    const q = buscar.value.trim().toLowerCase();
    const s = semestre.value;
    let n = 0;
    document.querySelectorAll('#cuerpo tr[data-q]').forEach(tr => {
        const ver = (!q || tr.dataset.q.includes(q)) && (!s || tr.dataset.s === s);
        tr.hidden = !ver;
        if (ver) n++;
    });
    document.getElementById('sin-resultados').hidden = n > 0;
    document.getElementById('conteo').textContent = n + (n === 1 ? ' materia' : ' materias');
}
buscar.addEventListener('input', filtrar);
semestre.addEventListener('change', filtrar);
</script>
@endpush
