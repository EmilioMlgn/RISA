@php
    $resumen = $grid['resumen'];
    $semActual = collect($grid['semestres'])->firstWhere('esSemestreActual', true);
    $carrera = $student->career ?? 'Ingeniería en Sistemas Computacionales';
@endphp
@extends('layouts.risa', [
    'activo' => 'reticula',
    'perfil' => [
        'nombre' => $student->name,
        'etiqueta' => $student->control_number ?? '20030582',
        'lineas' => ['No. de control ' . ($student->control_number ?? '20030582'), $carrera],
        'avance' => $resumen['porcentajeAvance'],
    ],
])

@section('titulo', 'Retícula')

@push('styles')
    <style>
        /* Pestañas */
        .tabs-nav {
            display: inline-flex; gap: 4px; margin-bottom: 22px; padding: 4px;
            background: var(--soft); border: 1px solid var(--line); border-radius: 12px;
        }
        .tab-btn {
            display: inline-flex; align-items: center; gap: 8px;
            padding: 9px 16px; border: 0; border-radius: 9px; background: none;
            font: inherit; font-size: 0.9rem; font-weight: 600; color: var(--muted); cursor: pointer;
            transition: background 0.2s ease, color 0.2s ease, box-shadow 0.2s ease;
        }
        .tab-btn:hover { color: var(--navy); }
        .tab-btn.active { background: #fff; color: var(--navy); box-shadow: var(--shadow-sm); }
        @media (max-width: 560px) { .tabs-nav { display: flex; } .tab-btn { flex: 1; justify-content: center; padding: 9px 8px; font-size: 0.82rem; } .tab-btn svg { display: none; } }

        .seccion { animation: aparecer 0.4s var(--ease-out); }
        @keyframes aparecer { from { opacity: 0; transform: translateY(8px); } to { opacity: 1; transform: none; } }


        .progress-box { padding: 18px 20px; margin-bottom: 18px; }
        .progress-box p { margin: 0 0 12px; font-size: 0.98rem; }
        .bar { height: 12px; border-radius: 6px; overflow: hidden; background: color-mix(in srgb, var(--navy) 8%, #fff); }
        .bar span {
            display: block; height: 100%; width: 0; border-radius: 6px;
            background: linear-gradient(90deg, #1E9E45, var(--a) 60%, #5FD17F);
            transition: width 1.1s var(--ease-out);
        }
        .progress-meta { display: flex; flex-wrap: wrap; gap: 4px 12px; justify-content: space-between; margin-top: 10px; font-size: 0.8125rem; color: var(--muted); }
        .vivo { display: inline-flex; align-items: center; gap: 6px; }
        .vivo::before { content: ""; width: 8px; height: 8px; border-radius: 50%; background: var(--a); box-shadow: 0 0 0 0 rgba(40,167,69,0.5); animation: pulso 2s infinite; }
        @keyframes pulso { 70% { box-shadow: 0 0 0 7px rgba(40,167,69,0); } 100% { box-shadow: 0 0 0 0 rgba(40,167,69,0); } }

        /* Leyenda */
        .legend { display: flex; flex-wrap: wrap; gap: 8px; margin-bottom: 22px; }
        .lg {
            display: inline-flex; align-items: center; gap: 8px; padding: 6px 12px 6px 8px;
            border: 1px solid color-mix(in srgb, var(--s) 30%, var(--line)); border-radius: 999px;
            background: color-mix(in srgb, var(--s) 7%, #fff); font-size: 0.8125rem;
        }
        .sw { width: 12px; height: 12px; border-radius: 50%; background: var(--s); }
        .lg b { font-weight: 600; }
        .lg small { color: var(--muted); }
        @media (max-width: 720px) { .lg small { display: none; } }

        /* Tablero */
        .board {
            display: grid; grid-auto-flow: column; grid-auto-columns: minmax(205px, 1fr);
            gap: 16px; align-items: start; overflow-x: auto; padding: 4px 2px 20px;
            scroll-snap-type: x proximity;
        }
        .col { display: flex; flex-direction: column; gap: 10px; min-width: 205px; scroll-snap-align: start; }
        .col-h {
            display: flex; flex-wrap: wrap; align-items: baseline; justify-content: space-between;
            gap: 2px 8px; padding: 10px 12px; border-radius: 12px;
            background: color-mix(in srgb, var(--navy) 6%, #fff); border: 1px solid var(--line);
        }
        .col-h h2 { margin: 0; font-size: 0.95rem; font-weight: 700; color: var(--navy); }
        .col-h span { color: var(--muted); font-size: 0.75rem; font-weight: 500; }
        .col.actual .col-h { background: linear-gradient(135deg, var(--navy), var(--navy-light)); border-color: transparent; }
        .col.actual .col-h h2, .col.actual .col-h span { color: #fff; }
        .col-h .now {
            width: 100%; display: inline-flex; align-items: center; gap: 6px; margin-top: 2px;
            font-weight: 700; font-size: 0.6875rem; text-transform: uppercase; letter-spacing: 0.06em;
        }
        .col-h .now::before { content: ""; width: 6px; height: 6px; border-radius: 50%; background: var(--gold); }

        .card {
            position: relative;
            padding: 12px 12px 12px 14px; border: 1px solid var(--line); border-radius: 12px;
            background: linear-gradient(180deg, color-mix(in srgb, var(--s) 6%, #fff), #fff);
            box-shadow: var(--shadow-sm); cursor: pointer; overflow: hidden;
            transition: transform 0.2s var(--ease-out), box-shadow 0.2s ease, border-color 0.2s ease;
        }
        .card::before { content: ""; position: absolute; left: 0; top: 0; bottom: 0; width: 5px; background: var(--s); }
        .card:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 22px color-mix(in srgb, var(--s) 18%, rgba(27,57,106,0.08));
            border-color: color-mix(in srgb, var(--s) 45%, var(--line));
        }
        .card:active { transform: translateY(-1px) scale(0.99); }
        .card h3 { margin: 0 0 8px; font-size: 0.875rem; line-height: 1.3; font-weight: 600; color: var(--ink); }
        .meta { display: flex; justify-content: space-between; align-items: center; gap: 8px; font-size: 0.75rem; color: var(--muted); }
        .st {
            display: inline-flex; align-items: center; gap: 5px; padding: 1px 8px; border-radius: 999px;
            background: color-mix(in srgb, var(--s) 14%, #fff); color: color-mix(in srgb, var(--s) 70%, #000);
            font-weight: 600; font-size: 0.7rem; white-space: nowrap;
        }
        .st::before { content: ""; width: 6px; height: 6px; border-radius: 50%; background: var(--s); }
        .badges { display: flex; gap: 6px; flex-wrap: wrap; margin-top: 8px; }
        .badges:empty { display: none; }
        .badge {
            display: inline-flex; align-items: center; gap: 4px; padding: 1px 8px;
            border-radius: 999px; background: #fff; border: 1px solid var(--line);
            font-size: 0.6875rem; font-weight: 600; color: var(--muted);
        }
        .badge.comment { color: var(--c); border-color: rgba(0, 123, 255, 0.35); background: rgba(0, 123, 255, 0.06); }
        .badge.rep { color: var(--r); border-color: rgba(220, 53, 69, 0.35); background: rgba(220, 53, 69, 0.06); }

        /* Recomendación */
        .recom-panel { padding: 24px; margin-bottom: 24px; }
        .recom-panel h2 { margin: 0 0 6px; color: var(--navy); font-size: 1.25rem; }
        .recom-grid { display: grid; grid-template-columns: 2fr 1fr; gap: 24px; }
        @media (max-width: 900px) { .recom-grid { grid-template-columns: 1fr; } }
        .diag { display: flex; gap: 10px; padding: 12px 14px; margin-bottom: 10px; border-radius: 10px; background: rgba(0,123,255,0.06); border: 1px solid rgba(0,123,255,0.18); font-size: 0.875rem; }
        .diag svg { flex: none; color: var(--c); margin-top: 1px; }
        .stat-card { border: 1px solid var(--line); border-radius: 14px; padding: 18px; margin-bottom: 16px; background: #fff; }
        .stat-card.hl { background: linear-gradient(135deg, var(--navy), var(--navy-light)); color: #fff; border-color: transparent; position: relative; overflow: hidden; }
        .stat-card.hl::after { content: ""; position: absolute; right: -20px; bottom: -30px; width: 110px; height: 160px; background: url("{{ asset('images/tecnm-emblema.png') }}") no-repeat center / contain; filter: brightness(0) invert(1); opacity: 0.07; }
        .stat-card h3 { margin: 0 0 6px; font-size: 0.95rem; color: var(--navy); }
        .stat-card.hl h3, .stat-card.hl p { color: #fff !important; }
        .stat-big { font-size: 2.6rem; font-weight: 800; line-height: 1.05; margin: 6px 0; }
        .metricas { font-size: 0.8125rem; display: flex; flex-direction: column; gap: 10px; }
        .metricas div { display: flex; justify-content: space-between; gap: 12px; padding-bottom: 8px; border-bottom: 1px dashed var(--line); }
        .metricas div:last-child { border-bottom: 0; padding-bottom: 0; }
        .table-wrap { overflow-x: auto; border: 1px solid var(--line); border-radius: 12px; margin-top: 14px; }
        .recom-table { width: 100%; border-collapse: collapse; font-size: 0.875rem; background: #fff; }
        .recom-table th, .recom-table td { padding: 11px 14px; text-align: left; border-bottom: 1px solid var(--line); }
        .recom-table tbody tr:last-child td { border-bottom: 0; }
        .recom-table tbody tr { transition: background 0.15s; }
        .recom-table tbody tr:hover { background: var(--soft); }
        .recom-table th { background: var(--soft); font-weight: 600; color: var(--navy); font-size: 0.8125rem; }
        .recom-table code { padding: 2px 6px; border-radius: 6px; background: var(--soft); font-size: 0.78rem; }
        .tag-recom { display: inline-block; padding: 2px 9px; border-radius: 999px; font-size: 0.75rem; font-weight: 600; }
        .tag-recom.rep { background: rgba(220, 53, 69, 0.1); color: #B02A37; }
        .tag-recom.crit { background: rgba(0, 123, 255, 0.1); color: #0058B8; }
        .tag-recom.ord { background: rgba(40, 167, 69, 0.12); color: #1E7B34; }

        /* ===== Modal ===== */
        .modal-backdrop {
            position: fixed; inset: 0; z-index: 100;
            display: flex; align-items: center; justify-content: center; padding: 16px;
            background: rgba(18, 40, 77, 0.5); backdrop-filter: blur(4px);
            animation: fundido 0.2s ease;
        }
        .modal-backdrop[hidden] { display: none; }
        @keyframes fundido { from { opacity: 0; } }
        .modal {
            width: 100%; max-width: 720px; max-height: 90vh; overflow-y: auto;
            background: #fff; color: var(--ink); border-radius: 18px;
            box-shadow: var(--shadow-lg);
            animation: emerger 0.32s var(--ease-out);
        }
        @keyframes emerger { from { opacity: 0; transform: translateY(14px) scale(0.97); } }
        .modal-header {
            position: sticky; top: 0; z-index: 2;
            display: flex; justify-content: space-between; align-items: flex-start; gap: 12px;
            padding: 20px 22px 16px; background: #fff; border-bottom: 1px solid var(--line);
            border-top: 6px solid var(--s, var(--navy));
        }
        .modal-clave { display: inline-block; margin-bottom: 4px; padding: 2px 8px; border-radius: 6px; background: var(--soft); font-size: 0.75rem; color: var(--muted); font-weight: 700; letter-spacing: 0.04em; }
        .modal-title { margin: 0; font-size: 1.35rem; font-weight: 800; color: var(--navy); line-height: 1.2; }
        .modal-close {
            flex: none; display: grid; place-items: center; width: 38px; height: 38px; border: 0; border-radius: 10px;
            background: var(--soft); color: var(--muted); cursor: pointer; transition: background 0.2s, color 0.2s, transform 0.3s var(--ease-out);
        }
        .modal-close:hover { background: color-mix(in srgb, var(--r) 12%, #fff); color: var(--r); transform: rotate(90deg); }
        .modal-body { padding: 18px 22px 22px; }
        .modal-datos { display: grid; grid-template-columns: repeat(4, 1fr); gap: 10px; margin-bottom: 18px; }
        @media (max-width: 560px) { .modal-datos { grid-template-columns: repeat(2, 1fr); } }
        .dato { padding: 10px 12px; border-radius: 12px; background: var(--soft); }
        .dato small { display: block; font-size: 0.72rem; color: var(--muted); }
        .dato strong { font-size: 1.05rem; }
        .modal-estatus { display: inline-flex; align-items: center; gap: 6px; }
        .modal-estatus::before { content: ""; width: 9px; height: 9px; border-radius: 50%; background: var(--s, var(--p)); }
        .relaciones { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; margin-bottom: 18px; font-size: 0.8125rem; }
        @media (max-width: 560px) { .relaciones { grid-template-columns: 1fr; } }
        .relaciones > div { padding: 12px 14px; border: 1px solid var(--line); border-radius: 12px; }
        .relaciones strong { color: var(--navy); display: block; margin-bottom: 6px; }
        .relaciones ul { margin: 0; padding-left: 18px; color: var(--muted); }
        .modal-sec { margin-top: 8px; border-top: 1px solid var(--line); padding-top: 16px; }
        .modal-sec h4 { margin: 0 0 10px; color: var(--navy); font-size: 0.98rem; }
        .comment-item {
            background: color-mix(in srgb, var(--navy) 4%, #fff);
            border: 1px solid var(--line); border-left: 4px solid var(--navy);
            padding: 10px 14px; border-radius: 10px; margin-bottom: 10px; font-size: 0.875rem;
        }
        .comment-item .author { font-weight: 600; color: var(--navy); font-size: 0.8125rem; display: flex; flex-wrap: wrap; gap: 4px 10px; justify-content: space-between; }
        .form-com { margin-top: 14px; background: var(--soft); padding: 14px; border-radius: 12px; }
        .form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 10px; margin-bottom: 10px; }
        @media (max-width: 560px) { .form-row { grid-template-columns: 1fr; } }
        .input-text, .select-input {
            width: 100%; padding: 9px 12px; border: 1px solid var(--line);
            border-radius: 9px; font: inherit; font-size: 0.875rem; background: #fff; color: var(--ink);
            transition: border-color 0.2s, box-shadow 0.2s;
        }
        .input-text:focus, .select-input:focus { outline: none; border-color: var(--navy-light); box-shadow: 0 0 0 3px rgba(42, 82, 152, 0.15); }
        .cargando { display: flex; align-items: center; justify-content: center; gap: 10px; padding: 36px 20px; color: var(--muted); }
        .cargando[hidden] { display: none; }
        .spinner { width: 20px; height: 20px; border: 2px solid var(--line); border-top-color: var(--navy); border-radius: 50%; animation: girar 0.7s linear infinite; }
        @keyframes girar { to { transform: rotate(360deg); } }


        .solo-impresion { display: none; }
        @page { size: landscape; margin: 10mm; }
        @media print {
            :root { --surface: #fff; --ink: #000; --muted: #333; --line: #aaa; }
            * { -webkit-print-color-adjust: exact; print-color-adjust: exact; box-shadow: none !important; }
            body { background: #fff; font-size: 11px; }
            body::before { opacity: 0.04; }
            .appbar, .actions, .tabs-nav, .stats, #estado, .modal-backdrop, .drawer, .drawer-overlay, .eyebrow { display: none !important; }
            .wrap { max-width: none; padding: 0; }
            .solo-impresion { display: block; margin: 0 0 10px; color: var(--muted); font-size: 0.75rem; }
            h1 { font-size: 1.2rem; }
            .board { grid-auto-columns: minmax(0, 1fr); gap: 6px; overflow: visible; padding-bottom: 0; }
            .col { min-width: 0; gap: 4px; }
            .card { padding: 4px 6px 4px 8px; break-inside: avoid; }
            .card h3 { font-size: 0.65rem; margin-bottom: 2px; }
            .meta { font-size: 0.55rem; }
        }
    </style>
@endpush

@section('content')
<div class="wrap">
    <div class="hero">
        <div>
            <span class="eyebrow"><img src="{{ asset('images/tecnm-emblema.png') }}" alt="" aria-hidden="true">TecNM · Campus Celaya</span>
            <h1>Retícula de {{ $carrera }}</h1>
            <p class="sub">Tu trayectoria curricular en tiempo real, validada por el autómata de prerrequisitos.</p>
        </div>
        <div class="actions">
            <button type="button" class="btn-action primary" onclick="mostrarSeccion('recomendacion')">
                <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" stroke-linejoin="round"><path d="M13 3L4 14h7l-1 7 9-11h-7l1-7z"/></svg>
                Ver recomendación de carga
            </button>
            <button type="button" class="btn-action" id="imprimir">
                <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" stroke-linejoin="round"><path d="M6 9V3h12v6M6 18H4v-7h16v7h-2M7 14h10v7H7z"/></svg>
                Imprimir
            </button>
        </div>
    </div>

    <p class="solo-impresion" id="sello">Estudiante: {{ $student->name }} ({{ $student->control_number }}). Generado oficialmente el {{ date('d/m/Y') }}.</p>

    <!-- Indicadores -->
    <section class="stats" aria-label="Resumen académico">
        <div class="stat" style="--k: var(--a)">
            <small>Créditos aprobados</small>
            <strong>{{ $resumen['creditosAprobados'] }} <span>de {{ $resumen['totalCreditos'] }}</span></strong>
        </div>
        <div class="stat" style="--k: var(--navy)">
            <small>Avance de la carrera</small>
            <strong>{{ $resumen['porcentajeAvance'] }} <span>%</span></strong>
            <svg class="ring" viewBox="0 0 36 36" width="46" height="46" aria-hidden="true">
                <circle cx="18" cy="18" r="15.5" fill="none" stroke="#E1E7EF" stroke-width="4"/>
                <circle cx="18" cy="18" r="15.5" fill="none" stroke="#1B396A" stroke-width="4" stroke-linecap="round"
                        stroke-dasharray="{{ round(min(100, $resumen['porcentajeAvance']) * 0.974, 1) }} 97.4" transform="rotate(-90 18 18)"/>
            </svg>
        </div>
        <div class="stat" style="--k: var(--c)">
            <small>Semestre actual</small>
            <strong>{{ $semActual['semestre'] ?? '—' }}<span>°</span></strong>
        </div>
        <div class="stat" style="--k: var(--gold)">
            <small>Materias evaluadas</small>
            <strong>{{ $resumen['totalMaterias'] }}</strong>
        </div>
    </section>

    <!-- Pestañas de Navegación -->
    <nav class="tabs-nav" aria-label="Secciones">
        <button class="tab-btn active" id="tab-reticula" onclick="mostrarSeccion('reticula')">
            <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" stroke-linejoin="round"><path d="M3 3h7v7H3zM14 3h7v7h-7zM14 14h7v7h-7zM3 14h7v7H3z"/></svg>
            Retícula curricular
        </button>
        <button class="tab-btn" id="tab-recomendacion" onclick="mostrarSeccion('recomendacion')">
            <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" stroke-linejoin="round"><path d="M13 3L4 14h7l-1 7 9-11h-7l1-7z"/></svg>
            Recomendación y proyección
        </button>
    </nav>

    <!-- SECCIÓN 1: RETÍCULA ACADÉMICA -->
    <section id="seccion-reticula" class="seccion">
        <!-- Barra de Progreso Global -->
        <div class="panel progress-box">
            <p id="resumen">
                Has aprobado <strong>{{ $resumen['creditosAprobados'] }} de {{ $resumen['totalCreditos'] }} créditos</strong> ({{ $resumen['porcentajeAvance'] }} %)
            </p>
            @php
                $total = max(1, $resumen['totalCreditos']);
                $pctA = ($resumen['creditosAprobados'] / $total) * 100;
            @endphp
            <div class="bar" id="barra" role="img" aria-label="{{ $resumen['creditosAprobados'] }} créditos aprobados">
                <span data-ancho="{{ $pctA }}" title="Aprobadas: {{ $resumen['creditosAprobados'] }} créditos"></span>
            </div>
            <div class="progress-meta">
                <span>Total de asignaturas evaluadas: {{ $resumen['totalMaterias'] }}</span>
                <span id="estado" class="vivo">Estado actualizado en tiempo real</span>
            </div>
        </div>

        <!-- Leyenda de Estatus -->
        <section class="legend" aria-label="Leyenda de estatus">
            <div class="lg" data-e="A"><span class="sw"></span><b>Aprobada</b><small>70 o más</small></div>
            <div class="lg" data-e="C"><span class="sw"></span><b>Cursando</b><small>Periodo activo</small></div>
            <div class="lg" data-e="R"><span class="sw"></span><b>Reprobada</b><small>Por repetir</small></div>
            <div class="lg" data-e="D"><span class="sw"></span><b>Disponible</b><small>Cumples prerrequisitos</small></div>
            <div class="lg" data-e="P"><span class="sw"></span><b>Pendiente</b><small>Faltan prerrequisitos</small></div>
        </section>

        <!-- Tablero Semestral -->
        <main class="board" id="tablero-reticula">
            @foreach($grid['semestres'] as $sem)
                <section class="col {{ $sem['esSemestreActual'] ? 'actual' : '' }}" aria-label="Semestre {{ $sem['semestre'] }}">
                    <div class="col-h">
                        <h2>Semestre {{ $sem['semestre'] }}</h2>
                        <span>{{ $sem['totalCreditos'] }} créditos</span>
                        @if($sem['esSemestreActual'])
                            <span class="now">Semestre actual</span>
                        @endif
                    </div>
                    @foreach($sem['materias'] as $m)
                        <article
                            class="card"
                            data-e="{{ $m['estatus'] }}"
                            tabindex="0"
                            role="button"
                            onclick="abrirModalMateria({{ $m['id'] }})"
                            onkeydown="if(event.key === 'Enter' || event.key === ' ') { event.preventDefault(); abrirModalMateria({{ $m['id'] }}); }"
                            aria-label="{{ $m['nombre'] }}, {{ $m['creditos'] }} créditos, {{ $m['estatusLabel'] }}"
                        >
                            <h3>{{ $m['nombre'] }}</h3>
                            <div class="meta">
                                <span>{{ $m['creditos'] }} créditos</span>
                                <span class="st">{{ $m['estatusLabel'] }}</span>
                            </div>
                            <div class="badges">@if($m['intento'] > 1)<span class="badge rep">Intento {{ $m['intento'] }}</span>@endif @if($m['calificacion'])<span class="badge">{{ $m['calificacion'] }} pts</span>@endif @if($m['tieneComentarios'])<span class="badge comment"><svg viewBox="0 0 24 24" width="12" height="12" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>{{ $m['totalComentarios'] }}</span>@endif</div>
                        </article>
                    @endforeach
                </section>
            @endforeach
        </main>
    </section>

    <!-- SECCIÓN 2: RECOMENDACIÓN DE CARGA Y PROYECCIÓN -->
    <section id="seccion-recomendacion" class="seccion" hidden>
        <div class="panel recom-panel">
            <div class="recom-grid">
                <div>
                    <h2>Propuesta de carga académica para el siguiente periodo</h2>
                    <p class="sub" style="margin-bottom:16px;">
                        Generada por el motor determinista del autómata curricular. Prioriza materias en repetición y la ruta crítica.
                    </p>

                    @foreach($recommendation['recomendacionCarga']['diagnostico'] as $diag)
                        <div class="diag">
                            <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><circle cx="12" cy="12" r="10"/><path d="M12 11v6M12 7.5v.01"/></svg>
                            <span>{{ $diag }}</span>
                        </div>
                    @endforeach

                    <div class="table-wrap">
                        <table class="recom-table">
                            <thead>
                                <tr>
                                    <th>Clave</th>
                                    <th>Asignatura sugerida</th>
                                    <th>Créditos</th>
                                    <th>Sem.</th>
                                    <th>Motivo / regla del autómata</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($recommendation['recomendacionCarga']['materiasRecomendadas'] as $rec)
                                    <tr>
                                        <td><code>{{ $rec['code'] }}</code></td>
                                        <td><strong>{{ $rec['name'] }}</strong></td>
                                        <td>{{ $rec['credits'] }}</td>
                                        <td>{{ $rec['semester_order'] }}°</td>
                                        <td>
                                            @if($rec['esRepeticion'])
                                                <span class="tag-recom rep">Repetición urgente</span>
                                            @elseif($rec['materiasQueDesbloquea'] > 0)
                                                <span class="tag-recom crit">Ruta crítica (+{{ $rec['materiasQueDesbloquea'] }})</span>
                                            @else
                                                <span class="tag-recom ord">Avance regular</span>
                                            @endif
                                            <div style="font-size:0.75rem; color:var(--muted); margin-top:3px;">{{ $rec['motivo'] }}</div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" style="text-align:center; color:var(--muted); padding:24px;">
                                            No hay materias disponibles para cursar en este momento.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    @if(!empty($recommendation['recomendacionCarga']['materiasDisponiblesExcluidasPorCupo']))
                        <div style="margin-top: 20px;">
                            <h4 style="margin: 0 0 8px; font-size: 0.875rem; color: var(--muted);">Materias disponibles diferidas por tope de carga crediticia:</h4>
                            <ul style="margin: 0; padding-left: 20px; font-size: 0.8125rem; color: var(--muted);">
                                @foreach($recommendation['recomendacionCarga']['materiasDisponiblesExcluidasPorCupo'] as $exc)
                                    <li><strong>{{ $exc['name'] }}</strong> ({{ $exc['credits'] }} créditos) — {{ $exc['motivo'] }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif
                </div>

                <!-- Panel de Proyección Topológica -->
                <div>
                    <div class="stat-card hl">
                        <h3>Estimación de semestres restantes</h3>
                        <div class="stat-big">
                            {{ $recommendation['proyeccionEgreso']['semestresRestantesEstimados'] }} <span style="font-size:1.1rem; font-weight:500;">semestres</span>
                        </div>
                        <p style="font-size:0.8125rem; margin:0; opacity:0.9;">
                            {{ $recommendation['proyeccionEgreso']['diagnostico'] }}
                        </p>
                    </div>

                    <div class="stat-card">
                        <h3>Métricas curriculares</h3>
                        <div class="metricas">
                            <div><span>Créditos de la carga sugerida</span><strong>{{ $recommendation['recomendacionCarga']['creditosTotales'] }}</strong></div>
                            <div><span>Cota mínima por seriación</span><strong>{{ $recommendation['proyeccionEgreso']['cotaPorSeriacion'] }} sem.</strong></div>
                            <div><span>Cota mínima por créditos</span><strong>{{ $recommendation['proyeccionEgreso']['cotaPorCreditos'] }} sem.</strong></div>
                        </div>

                        @if(!empty($recommendation['proyeccionEgreso']['cadenaCriticaEjemplo']))
                            <div style="margin-top:14px; border-top:1px solid var(--line); padding-top:12px;">
                                <strong style="font-size:0.75rem; color:var(--navy); display:block; margin-bottom:4px;">Cadena crítica de seriación</strong>
                                <div style="font-size:0.75rem; color:var(--muted); line-height:1.5;">
                                    {{ implode(' ➔ ', $recommendation['proyeccionEgreso']['cadenaCriticaEjemplo']) }}
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>

<!-- MODAL DE DETALLE DE MATERIA Y OBSERVACIONES DOCENTES -->
<div class="modal-backdrop" id="modal-materia" hidden onclick="if(event.target === this) cerrarModal()">
    <div class="modal" id="modal-caja" role="dialog" aria-modal="true" aria-labelledby="modal-materia-titulo">
        <div class="modal-header">
            <div>
                <span class="modal-clave" id="modal-clave"></span>
                <h2 class="modal-title" id="modal-materia-titulo">Detalle de materia</h2>
            </div>
            <button type="button" class="modal-close" id="modal-cerrar" onclick="cerrarModal()" aria-label="Cerrar ventana">
                <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round"><path d="M6 6l12 12M18 6L6 18"/></svg>
            </button>
        </div>

        <div class="modal-body">
            <div id="modal-cargando" class="cargando"><span class="spinner"></span><span>Cargando información curricular…</span></div>

            <div id="modal-contenido" hidden>
                <div class="modal-datos">
                    <div class="dato"><small>Créditos</small><strong id="modal-creditos"></strong></div>
                    <div class="dato"><small>Horas (T/P)</small><strong id="modal-horas"></strong></div>
                    <div class="dato"><small>Semestre</small><strong id="modal-semestre"></strong></div>
                    <div class="dato"><small>Estatus</small><strong id="modal-estatus" class="modal-estatus"></strong></div>
                </div>

                <!-- Prerrequisitos y materias que desbloquea -->
                <div class="relaciones">
                    <div>
                        <strong>Prerrequisitos obligatorios</strong>
                        <ul id="modal-prerrequisitos"></ul>
                    </div>
                    <div>
                        <strong>Desbloquea en semestres futuros</strong>
                        <ul id="modal-subsecuentes"></ul>
                    </div>
                </div>

                <!-- Observaciones de Docentes y Asesores -->
                <div class="modal-sec">
                    <h4>Observaciones y retroalimentación de docentes</h4>
                    <div id="modal-comentarios-lista"></div>

                    <!-- Formulario para agregar observación -->
                    <form id="form-nuevo-comentario" class="form-com" onsubmit="guardarComentario(event)">
                        <strong style="display:block; margin-bottom:10px; font-size:0.8125rem; color:var(--navy);">Registrar nueva nota de asesoría o desempeño</strong>
                        <div class="form-row">
                            <input type="text" id="comentario-autor" class="input-text" placeholder="Nombre del docente/asesor" aria-label="Nombre del docente o asesor" required>
                            <select id="comentario-rol" class="select-input" aria-label="Rol" required>
                                <option value="Docente Titular">Docente Titular</option>
                                <option value="Tutor Académico">Tutor Académico</option>
                                <option value="Asesor de Proyecto">Asesor de Proyecto</option>
                                <option value="Coordinación">Coordinación</option>
                            </select>
                        </div>
                        <div style="margin-bottom:10px;">
                            <textarea id="comentario-texto" class="input-text" rows="3" placeholder="Escribe aquí las observaciones sobre el desempeño del estudiante en esta asignatura..." aria-label="Observación" required></textarea>
                        </div>
                        <button type="submit" class="btn-action primary">Guardar observación</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
let materiaActualId = null;
let ultimoFoco = null;

const ESTATUS = { A: 'Aprobada', C: 'Cursando', R: 'Reprobada', D: 'Disponible', P: 'Pendiente' };
const esc = s => String(s ?? '').replace(/[&<>"']/g, c => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]));

function mostrarSeccion(seccion) {
    const esReticula = seccion === 'reticula';
    document.getElementById('seccion-reticula').hidden = !esReticula;
    document.getElementById('seccion-recomendacion').hidden = esReticula;
    document.getElementById('tab-reticula').classList.toggle('active', esReticula);
    document.getElementById('tab-recomendacion').classList.toggle('active', !esReticula);
    document.querySelectorAll('.nav-item[data-sec]').forEach(b => {
        if (b.dataset.sec === seccion) b.setAttribute('aria-current', 'page');
        else b.removeAttribute('aria-current');
    });
    cerrarMenu();
}

function abrirModalMateria(id) {
    materiaActualId = id;
    ultimoFoco = document.activeElement;
    const modal = document.getElementById('modal-materia');
    const caja = document.getElementById('modal-caja');
    const cargando = document.getElementById('modal-cargando');
    const contenido = document.getElementById('modal-contenido');

    modal.hidden = false;
    cargando.hidden = false;
    cargando.innerHTML = '<span class="spinner"></span><span>Cargando información curricular…</span>';
    contenido.hidden = true;
    document.body.style.overflow = 'hidden';
    document.getElementById('modal-cerrar').focus();

    fetch(`/api/materias/${id}`, { headers: { 'Accept': 'application/json' } })
    .then(r => {
        if (!r.ok) throw new Error(`Error ${r.status}: No se pudo consultar la materia`);
        return r.json();
    })
    .then(resp => {
        if (!resp || !resp.success) throw new Error(resp?.message || 'No se recibieron datos de la asignatura.');
        const data = resp.data;
        caja.dataset.e = data.estatus;
        document.getElementById('modal-clave').textContent = data.clave;
        document.getElementById('modal-materia-titulo').textContent = data.nombre;
        document.getElementById('modal-creditos').textContent = data.creditos;
        document.getElementById('modal-horas').textContent = `${data.horasTeoria} / ${data.horasPractica}`;
        document.getElementById('modal-semestre').textContent = `${data.semestreSugerido}°`;
        document.getElementById('modal-estatus').textContent =
            (ESTATUS[data.estatus] || data.estatus) + (data.calificacion ? ` · ${data.calificacion}` : '');

        document.getElementById('modal-prerrequisitos').innerHTML = data.prerrequisitos.length
            ? data.prerrequisitos.map(p => `<li>${esc(p.nombre)} (<code>${esc(p.clave)}</code>)</li>`).join('')
            : '<li>Sin prerrequisitos (primer semestre o libre)</li>';

        document.getElementById('modal-subsecuentes').innerHTML = data.materiasSubsecuentes.length
            ? data.materiasSubsecuentes.map(u => `<li>${esc(u.nombre)} (<code>${esc(u.clave)}</code>)</li>`).join('')
            : '<li>Asignatura terminal o sin dependientes</li>';

        document.getElementById('modal-comentarios-lista').innerHTML = data.comentariosDocentes.length
            ? data.comentariosDocentes.map(c => `
                <div class="comment-item">
                    <div class="author">
                        <span>${esc(c.autor)} (${esc(c.rol)})</span>
                        <span style="font-weight:normal; color:var(--muted);">${esc(c.fecha)}</span>
                    </div>
                    <div style="margin-top:4px;">${esc(c.comentario)}</div>
                </div>`).join('')
            : '<p style="color:var(--muted); font-size:0.8125rem; margin:0;">No hay notas u observaciones registradas aún para esta materia.</p>';

        cargando.hidden = true;
        contenido.hidden = false;
    })
    .catch(() => {
        cargando.textContent = 'Error al cargar los datos de la asignatura.';
    });
}

function cerrarModal() {
    const modal = document.getElementById('modal-materia');
    if (modal.hidden) return;
    modal.hidden = true;
    materiaActualId = null;
    document.body.style.overflow = '';
    if (ultimoFoco) ultimoFoco.focus();
}

function guardarComentario(e) {
    e.preventDefault();
    if (!materiaActualId) return;

    const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

    fetch(`/api/materias/${materiaActualId}/comentarios`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': token },
        body: JSON.stringify({
            author_name: document.getElementById('comentario-autor').value,
            author_role: document.getElementById('comentario-rol').value,
            comment: document.getElementById('comentario-texto').value
        })
    })
    .then(r => r.json())
    .then(resp => {
        if (resp.success) {
            document.getElementById('comentario-texto').value = '';
            abrirModalMateria(materiaActualId); // Recargar
        } else {
            alert('No se pudo guardar el comentario: ' + (resp.message || 'Error desconocido'));
        }
    })
    .catch(() => alert('Error de red al guardar el comentario.'));
}

document.getElementById('imprimir').addEventListener('click', () => window.print());

// Animación de la barra de avance al cargar
requestAnimationFrame(() => {
    document.querySelectorAll('[data-ancho]').forEach(el => { el.style.width = el.dataset.ancho + '%'; });
});

// Las opciones "Retícula" y "Recomendación" del menú cambian de pestaña sin recargar
document.querySelectorAll('.nav-item[data-sec]').forEach(a => a.addEventListener('click', e => {
    e.preventDefault();
    mostrarSeccion(a.dataset.sec);
    history.replaceState(null, '', a.dataset.sec === 'recomendacion' ? '#recomendacion' : location.pathname);
}));
if (location.hash === '#recomendacion') mostrarSeccion('recomendacion');
</script>
@endpush
