<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Retícula | RISA — Retícula Interactiva de Seguimiento Académico</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Public+Sans:wght@400;500;600;700&display=swap">
    <style>
        :root {
            --bg: #F3F5F8;
            --surface: #FFFFFF;
            --ink: #1B2430;
            --muted: #566273;
            --line: #D5DCE5;
            --navy: #1B396A;
            --navy-light: #2A5298;
            --a: #28A745;
            --c: #007BFF;
            --r: #DC3545;
            --d: #FFC107;
            --p: #6C757D;
        }
        @media (prefers-color-scheme: dark) {
            :root {
                --bg: #12161C;
                --surface: #1B212A;
                --ink: #E8ECF1;
                --muted: #9AA6B5;
                --line: #2C3541;
                --navy: #9DBBEF;
                --navy-light: #1B396A;
            }
        }
        * { box-sizing: border-box; }
        body {
            margin: 0;
            background: var(--bg);
            color: var(--ink);
            font: 15px/1.45 "Public Sans", system-ui, -apple-system, "Segoe UI", sans-serif;
        }
        [data-e="A"] { --s: var(--a); }
        [data-e="C"] { --s: var(--c); }
        [data-e="R"] { --s: var(--r); }
        [data-e="D"] { --s: var(--d); }
        [data-e="P"] { --s: var(--p); }

        .appbar { background: #1B396A; color: #fff; position: sticky; top: 0; z-index: 40; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
        .appbar-in { max-width: 1440px; margin: 0 auto; padding: 10px 20px; display: flex; align-items: center; justify-content: space-between; gap: 16px; }
        .marca { display: flex; align-items: baseline; gap: 12px; }
        .marca strong { font-size: 1.25rem; letter-spacing: 0.03em; font-weight: 700; }
        .marca span { font-size: 0.8125rem; opacity: 0.85; }
        .usuario { position: relative; display: flex; align-items: center; gap: 12px; }
        .alumno { font-weight: 600; font-size: 0.875rem; }
        .control-pill { background: rgba(255,255,255,0.2); padding: 2px 8px; border-radius: 999px; font-size: 0.75rem; }

        .btn-menu {
            display: inline-flex; align-items: center; justify-content: center;
            width: 40px; height: 40px; border: 0; border-radius: 8px;
            background: transparent; color: #fff; cursor: pointer;
        }
        .btn-menu:hover { background: rgba(255, 255, 255, 0.15); }
        .menu {
            position: absolute; top: calc(100% + 8px); right: 0; z-index: 50;
            min-width: 250px; display: flex; flex-direction: column; padding: 6px;
            background: var(--surface); color: var(--ink); border: 1px solid var(--line);
            border-radius: 8px; box-shadow: 0 10px 25px rgba(0,0,0,0.15);
        }
        .menu[hidden] { display: none; }
        .menu a, .menu button {
            padding: 10px 14px; border-radius: 6px; color: inherit;
            text-decoration: none; text-align: left; background: none; border: 0;
            font: inherit; cursor: pointer; display: flex; align-items: center; gap: 10px;
        }
        .menu a:hover, .menu button:hover { background: color-mix(in srgb, var(--navy) 10%, var(--surface)); }
        .menu a[aria-current="page"] { font-weight: 600; color: var(--navy); }

        .wrap { max-width: 1440px; margin: 0 auto; padding: 24px 20px 60px; }
        .top { display: flex; flex-wrap: wrap; gap: 16px 24px; justify-content: space-between; align-items: flex-start; margin-bottom: 20px; }
        h1 { margin: 0 0 4px; font-size: 1.6rem; font-weight: 700; color: var(--navy); letter-spacing: -0.01em; }
        .sub { margin: 0; color: var(--muted); font-size: 0.9rem; }

        .tabs-nav {
            display: flex; gap: 8px; margin-bottom: 20px; border-bottom: 1px solid var(--line);
        }
        .tab-btn {
            padding: 10px 18px; border: 0; background: none; font: inherit; font-weight: 600;
            color: var(--muted); cursor: pointer; border-bottom: 3px solid transparent;
            transition: all 0.2s ease;
        }
        .tab-btn:hover { color: var(--navy); }
        .tab-btn.active { color: var(--navy); border-bottom-color: var(--navy); }

        .actions { display: flex; gap: 10px; flex-wrap: wrap; }
        .btn-action {
            display: inline-flex; align-items: center; gap: 8px; padding: 8px 14px;
            border: 1px solid var(--navy); border-radius: 6px; background: var(--surface);
            color: var(--navy); font: inherit; font-size: 0.875rem; font-weight: 600; cursor: pointer;
            transition: background 0.15s ease;
        }
        .btn-action:hover { background: color-mix(in srgb, var(--navy) 10%, var(--surface)); }
        .btn-action.primary { background: #1B396A; color: #fff; border-color: #1B396A; }
        .btn-action.primary:hover { background: #2A5298; }

        .progress-box {
            background: var(--surface); border: 1px solid var(--line); border-radius: 8px;
            padding: 16px 20px; margin-bottom: 24px; box-shadow: 0 1px 3px rgba(0,0,0,0.03);
        }
        .progress-box p { margin: 0 0 10px; font-size: 0.95rem; }
        .bar { display: flex; height: 14px; border-radius: 7px; overflow: hidden; background: var(--line); }
        .bar span { background: var(--s); transition: width 0.4s ease; }
        .progress-meta { display: flex; justify-content: space-between; margin-top: 8px; font-size: 0.8125rem; color: var(--muted); }

        .legend {
            display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
            gap: 12px 16px; margin-bottom: 24px; padding: 14px 18px;
            background: var(--surface); border: 1px solid var(--line); border-radius: 8px;
        }
        .lg { display: grid; grid-template-columns: 16px 1fr; gap: 0 10px; align-items: start; }
        .sw { width: 16px; height: 16px; margin-top: 2px; border-radius: 4px; background: var(--s); }
        .lg b { font-weight: 600; font-size: 0.875rem; }
        .lg small { display: block; color: var(--muted); font-size: 0.75rem; }

        .board {
            display: grid; grid-auto-flow: column; grid-auto-columns: minmax(195px, 1fr);
            gap: 14px; align-items: start; overflow-x: auto; padding-bottom: 20px;
        }
        .col { display: flex; flex-direction: column; gap: 10px; min-width: 195px; }
        .col-h {
            display: flex; flex-wrap: wrap; align-items: baseline; justify-content: space-between;
            gap: 4px 8px; padding-bottom: 8px; border-bottom: 2px solid var(--line);
        }
        .col-h h2 { margin: 0; font-size: 0.95rem; font-weight: 700; color: var(--navy); }
        .col-h span { color: var(--muted); font-size: 0.75rem; font-weight: 500; }
        .col-h .now {
            width: 100%; display: inline-block; color: #007BFF; font-weight: 700;
            font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.04em;
        }

        .card {
            padding: 12px; border: 1px solid var(--line); border-left: 6px solid var(--s);
            border-radius: 8px; background: color-mix(in srgb, var(--s) 12%, var(--surface));
            cursor: pointer; transition: transform 0.15s ease, box-shadow 0.15s ease;
            position: relative;
        }
        .card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 10px rgba(0,0,0,0.08);
            border-color: color-mix(in srgb, var(--s) 40%, var(--line));
        }
        .card:focus-visible { outline: 2px solid var(--navy); outline-offset: 2px; }
        .card h3 { margin: 0 0 6px; font-size: 0.875rem; line-height: 1.3; font-weight: 600; color: var(--ink); }
        .meta { display: flex; justify-content: space-between; align-items: center; gap: 8px; font-size: 0.75rem; color: var(--muted); }
        .st { display: inline-flex; align-items: center; gap: 6px; font-weight: 600; color: var(--ink); }
        .st::before { content: ""; width: 8px; height: 8px; border-radius: 50%; background: var(--s); }
        .badges { display: flex; gap: 6px; flex-wrap: wrap; margin-top: 6px; }
        .badge {
            display: inline-flex; align-items: center; gap: 4px; padding: 1px 7px;
            border-radius: 999px; background: var(--surface); border: 1px solid var(--line);
            font-size: 0.6875rem; font-weight: 500; color: var(--muted);
        }
        .badge.comment { color: #007BFF; border-color: rgba(0, 123, 255, 0.4); }

        /* Panel de Recomendación */
        .recom-panel {
            background: var(--surface); border: 1px solid var(--line); border-radius: 10px;
            padding: 24px; box-shadow: 0 2px 6px rgba(0,0,0,0.04); margin-bottom: 24px;
        }
        .recom-grid { display: grid; grid-template-columns: 2fr 1fr; gap: 24px; }
        @media (max-width: 900px) { .recom-grid { grid-template-columns: 1fr; } }
        .stat-card {
            background: color-mix(in srgb, var(--navy) 6%, var(--surface));
            border: 1px solid color-mix(in srgb, var(--navy) 20%, var(--surface));
            border-radius: 8px; padding: 18px; margin-bottom: 16px;
        }
        .stat-card h3 { margin: 0 0 6px; font-size: 0.95rem; color: var(--navy); }
        .stat-big { font-size: 2.2rem; font-weight: 700; color: var(--navy); line-height: 1.1; margin: 6px 0; }
        .recom-table { width: 100%; border-collapse: collapse; margin-top: 14px; font-size: 0.875rem; }
        .recom-table th, .recom-table td { padding: 10px 12px; text-align: left; border-bottom: 1px solid var(--line); }
        .recom-table th { background: color-mix(in srgb, var(--navy) 8%, var(--surface)); font-weight: 600; color: var(--navy); }
        .tag-recom {
            display: inline-block; padding: 2px 8px; border-radius: 4px; font-size: 0.75rem; font-weight: 600;
        }
        .tag-recom.rep { background: rgba(220, 53, 69, 0.15); color: #DC3545; }
        .tag-recom.crit { background: rgba(0, 123, 255, 0.15); color: #007BFF; }
        .tag-recom.ord { background: rgba(40, 167, 69, 0.15); color: #28A745; }

        /* Modal */
        .modal-backdrop {
            position: fixed; inset: 0; background: rgba(0, 0, 0, 0.55);
            display: flex; align-items: center; justify-content: center;
            z-index: 100; padding: 16px; backdrop-filter: blur(2px);
        }
        .modal-backdrop[hidden] { display: none; }
        .modal {
            background: var(--surface); color: var(--ink); border-radius: 12px;
            width: 100%; max-width: 680px; max-height: 90vh; overflow-y: auto;
            border: 1px solid var(--line); box-shadow: 0 20px 40px rgba(0,0,0,0.25);
            padding: 24px;
        }
        .modal-header { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 16px; border-bottom: 1px solid var(--line); padding-bottom: 12px; }
        .modal-title { margin: 0; font-size: 1.25rem; font-weight: 700; color: var(--navy); }
        .modal-close { background: none; border: 0; font-size: 1.5rem; cursor: pointer; color: var(--muted); }
        .comment-item {
            background: color-mix(in srgb, var(--navy) 5%, var(--surface));
            border-left: 4px solid var(--navy); padding: 10px 14px;
            border-radius: 0 6px 6px 0; margin-bottom: 10px; font-size: 0.875rem;
        }
        .comment-item .author { font-weight: 600; color: var(--navy); font-size: 0.8125rem; display: flex; justify-content: space-between; }
        .input-text, .select-input {
            width: 100%; padding: 8px 12px; border: 1px solid var(--line);
            border-radius: 6px; font: inherit; font-size: 0.875rem; background: var(--surface); color: var(--ink);
        }
        .form-group { margin-bottom: 12px; }
        .form-group label { display: block; font-weight: 600; font-size: 0.8125rem; margin-bottom: 4px; color: var(--muted); }

        .solo-impresion { display: none; }
        @page { size: landscape; margin: 10mm; }
        @media print {
            :root { --bg: #fff; --surface: #fff; --ink: #000; --muted: #333; --line: #aaa; --navy: #1B396A; }
            * { -webkit-print-color-adjust: exact; print-color-adjust: exact; }
            body { background: #fff; font-size: 11px; }
            .appbar, .actions, .tabs-nav, #estado, .modal-backdrop { display: none !important; }
            .wrap { max-width: none; padding: 0; }
            .solo-impresion { display: block; margin: 0 0 10px; color: var(--muted); font-size: 0.75rem; }
            h1 { font-size: 1.2rem; }
            .board { grid-auto-columns: minmax(0, 1fr); gap: 6px; overflow: visible; padding-bottom: 0; }
            .col { min-width: 0; gap: 4px; }
            .card { padding: 4px 6px; border-left-width: 4px; break-inside: avoid; }
            .card h3 { font-size: 0.65rem; margin-bottom: 2px; }
            .meta { font-size: 0.55rem; }
        }
    </style>
</head>
<body>

<header class="appbar">
    <div class="appbar-in">
        <div class="marca">
            <strong>RISA</strong>
            <span>Retícula Interactiva de Seguimiento Académico</span>
        </div>
        <div class="usuario">
            <span class="control-pill">{{ $student->control_number ?? '20030582' }}</span>
            <span class="alumno" id="alumno">{{ $student->name }}</span>
            <button type="button" class="btn-menu" id="btn-menu" aria-label="Abrir menú" aria-expanded="false" aria-controls="menu">
                <svg viewBox="0 0 24 24" width="22" height="22" aria-hidden="true"><path d="M4 7h16M4 12h16M4 17h16" stroke="currentColor" stroke-width="2" stroke-linecap="round" fill="none"/></svg>
            </button>
            <nav class="menu" id="menu" aria-label="Menú principal" hidden>
                <button type="button" onclick="mostrarSeccion('reticula')">
                    <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 3h7v7H3zM14 3h7v7h-7zM14 14h7v7h-7zM3 14h7v7H3z"/></svg>
                    Retícula Académica
                </button>
                <button type="button" onclick="mostrarSeccion('recomendacion')">
                    <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 2v4M12 18v4M4.93 4.93l2.83 2.83M16.24 16.24l2.83 2.83M2 12h4M18 12h4M4.93 19.07l2.83-2.83M16.24 7.76l2.83-2.83"/></svg>
                    Recomendación de Carga
                </button>
                <a href="{{ route('reglas.configuracion') }}">
                    <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 15a3 3 0 100-6 3 3 0 000 6z"/><path d="M19.4 15a1.65 1.65 0 00.33 1.82l.06.06a2 2 0 010 2.83 2 2 0 01-2.83 0l-.06-.06a1.65 1.65 0 00-1.82-.33 1.65 1.65 0 00-1 1.51V21a2 2 0 01-2 2 2 2 0 01-2-2v-.09A1.65 1.65 0 009 19.4a1.65 1.65 0 00-1.82.33l-.06.06a2 2 0 01-2.83 0 2 2 0 010-2.83l.06-.06a1.65 1.65 0 00.33-1.82 1.65 1.65 0 00-1.51-1H3a2 2 0 01-2-2 2 2 0 012-2h.09A1.65 1.65 0 004.6 9a1.65 1.65 0 00-.33-1.82l-.06-.06a2 2 0 010-2.83 2 2 0 012.83 0l.06.06a1.65 1.65 0 001.82.33H9a1.65 1.65 0 001-1.51V3a2 2 0 012-2 2 2 0 012 2v.09a1.65 1.65 0 001 1.51 1.65 1.65 0 001.82-.33l.06-.06a2 2 0 012.83 0 2 2 0 010 2.83l-.06.06a1.65 1.65 0 00-.33 1.82V9a1.65 1.65 0 001.51 1H21a2 2 0 012 2 2 2 0 01-2 2h-.09a1.65 1.65 0 00-1.51 1z"/></svg>
                    Configuración de Reglas
                </a>
                <a href="{{ route('recomendacion.simulador') }}">
                    <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z"/><path d="M14 2v6h6"/><path d="M16 13H8"/><path d="M16 17H8"/><path d="M10 9H8"/></svg>
                    Simulador de Escenarios
                </a>
                <button type="button" onclick="window.print()">
                    <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2"><path d="M6 9V3h12v6M6 18H4v-7h16v7h-2M7 14h10v7H7z"/></svg>
                    Imprimir Retícula
                </button>
            </nav>
        </div>
    </div>
</header>

<div class="wrap">
    <div class="top">
        <div>
            <h1>Retícula de {{ $student->career ?? 'Ingeniería en Sistemas Computacionales' }}</h1>
            <p class="sub">Instituto Tecnológico de Celaya • Trayectoria curricular en tiempo real impulsada por autómata.</p>
        </div>
        <div class="actions">
            <button type="button" class="btn-action primary" onclick="mostrarSeccion('recomendacion')">
                <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2"><path d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                Ver Recomendación de Carga
            </button>
            <button type="button" class="btn-action" id="imprimir">
                <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2"><path d="M6 9V3h12v6M6 18H4v-7h16v7h-2M7 14h10v7H7z"/></svg>
                Imprimir retícula
            </button>
        </div>
    </div>

    <p class="solo-impresion" id="sello">Estudiante: {{ $student->name }} ({{ $student->control_number }}). Generado oficialmente el {{ date('d/m/Y') }}.</p>

    <!-- Pestañas de Navegación -->
    <nav class="tabs-nav">
        <button class="tab-btn active" id="tab-reticula" onclick="mostrarSeccion('reticula')">Retícula Curricular</button>
        <button class="tab-btn" id="tab-recomendacion" onclick="mostrarSeccion('recomendacion')">Motor de Recomendación y Proyección</button>
    </nav>

    <!-- SECCIÓN 1: RETÍCULA ACADÉMICA -->
    <section id="seccion-reticula">
        <!-- Barra de Progreso Global -->
        <div class="progress-box">
            <p id="resumen">
                Has aprobado <strong>{{ $grid['resumen']['creditosAprobados'] }} de {{ $grid['resumen']['totalCreditos'] }} créditos</strong> ({{ $grid['resumen']['porcentajeAvance'] }} %)
            </p>
            <div class="bar" id="barra" role="img" aria-label="{{ $grid['resumen']['creditosAprobados'] }} créditos aprobados">
                @php
                    $total = max(1, $grid['resumen']['totalCreditos']);
                    $aprobados = $grid['resumen']['creditosAprobados'];
                    $pctA = ($aprobados / $total) * 100;
                @endphp
                <span data-e="A" style="width: {{ $pctA }}%" title="Aprobadas: {{ $aprobados }} créditos"></span>
            </div>
            <div class="progress-meta">
                <span>Total de asignaturas evaluadas: {{ $grid['resumen']['totalMaterias'] }}</span>
                <span id="estado">Estado actualizado en tiempo real</span>
            </div>
        </div>

        <!-- Leyenda de Estatus -->
        <section class="legend" aria-label="Leyenda de estatus">
            <div class="lg" data-e="A"><span class="sw"></span><div><b>Aprobada</b><small>Calificación de 70 o más</small></div></div>
            <div class="lg" data-e="C"><span class="sw"></span><div><b>Cursando</b><small>Inscrita en el periodo activo</small></div></div>
            <div class="lg" data-e="R"><span class="sw"></span><div><b>Reprobada</b><small>Calificación menor a 70, por repetir</small></div></div>
            <div class="lg" data-e="D"><span class="sw"></span><div><b>Disponible</b><small>Cumples todos sus prerrequisitos</small></div></div>
            <div class="lg" data-e="P"><span class="sw"></span><div><b>Pendiente</b><small>Faltan prerrequisitos en la cadena</small></div></div>
        </section>

        <!-- Tablero Semestral -->
        <main class="board" id="tablero-reticula">
            @foreach($grid['semestres'] as $sem)
                <section class="col" aria-label="Semestre {{ $sem['semestre'] }}">
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
                            onclick="abrirModalMateria({{ $m['id'] }})"
                            onkeydown="if(event.key === 'Enter') abrirModalMateria({{ $m['id'] }})"
                            aria-label="{{ $m['nombre'] }}, {{ $m['creditos'] }} créditos, {{ $m['estatusLabel'] }}"
                        >
                            <h3>{{ $m['nombre'] }}</h3>
                            <div class="meta">
                                <span>{{ $m['creditos'] }} créditos</span>
                                <span class="st">{{ $m['estatusLabel'] }}</span>
                            </div>
                            <div class="badges">
                                @if($m['intento'] > 1)
                                    <span class="badge">Intento {{ $m['intento'] }}</span>
                                @endif
                                @if($m['calificacion'])
                                    <span class="badge">{{ $m['calificacion'] }} pts</span>
                                @endif
                                @if($m['tieneComentarios'])
                                    <span class="badge comment">
                                        <svg viewBox="0 0 24 24" width="12" height="12" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
                                        {{ $m['totalComentarios'] }}
                                    </span>
                                @endif
                            </div>
                        </article>
                    @endforeach
                </section>
            @endforeach
        </main>
    </section>

    <!-- SECCIÓN 2: RECOMENDACIÓN DE CARGA Y PROYECCIÓN -->
    <section id="seccion-recomendacion" hidden>
        <div class="recom-panel">
            <div class="recom-grid">
                <div>
                    <h2 style="margin-top:0; color:var(--navy);">Propuesta de Carga Académica para el Siguiente Periodo</h2>
                    <p class="sub" style="margin-bottom:16px;">
                        Generada por el motor determinista de autómata curricular sin dependencias externas. Prioriza materias en repetición y ruta crítica.
                    </p>

                    @foreach($recommendation['recomendacionCarga']['diagnostico'] as $diag)
                        <div style="background: rgba(0,123,255,0.08); border-left: 4px solid #007BFF; padding: 10px 14px; border-radius: 4px; margin-bottom: 12px; font-size: 0.875rem;">
                            {{ $diag }}
                        </div>
                    @endforeach

                    <table class="recom-table">
                        <thead>
                            <tr>
                                <th>Clave</th>
                                <th>Asignatura Sugerida</th>
                                <th>Créditos</th>
                                <th>Sem.</th>
                                <th>Motivo / Regla de Autómata</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($recommendation['recomendacionCarga']['materiasRecomendadas'] as $rec)
                                <tr>
                                    <td><code>{{ $rec['code'] }}</code></td>
                                    <td><strong>{{ $rec['name'] }}</strong></td>
                                    <td>{{ $rec['credits'] }}</td>
                                    <td>Sem. {{ $rec['semester_order'] }}</td>
                                    <td>
                                        @if($rec['esRepeticion'])
                                            <span class="tag-recom rep">Repetición Urgente</span>
                                        @elseif($rec['materiasQueDesbloquea'] > 0)
                                            <span class="tag-recom crit">Ruta Crítica (+{{ $rec['materiasQueDesbloquea'] }})</span>
                                        @else
                                            <span class="tag-recom ord">Avance Regular</span>
                                        @endif
                                        <div style="font-size:0.75rem; color:var(--muted); margin-top:3px;">{{ $rec['motivo'] }}</div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" style="text-align:center; color:var(--muted); padding:20px;">
                                        No hay materias disponibles para cursar en este momento.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>

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
                    <div class="stat-card">
                        <h3>Estimación de Semestres Restantes</h3>
                        <div class="stat-big">
                            {{ $recommendation['proyeccionEgreso']['semestresRestantesEstimados'] }} <span style="font-size:1.1rem; font-weight:500;">semestres</span>
                        </div>
                        <p style="font-size:0.8125rem; margin:0; color:var(--muted);">
                            {{ $recommendation['proyeccionEgreso']['diagnostico'] }}
                        </p>
                    </div>

                    <div class="stat-card" style="background:var(--surface);">
                        <h4 style="margin:0 0 10px; font-size:0.875rem; color:var(--navy);">Métricas Curriculares</h4>
                        <div style="font-size:0.8125rem; display:flex; flex-direction:column; gap:8px;">
                            <div style="display:flex; justify-content:space-between;">
                                <span>Créditos de la Carga Sugerida:</span>
                                <strong>{{ $recommendation['recomendacionCarga']['creditosTotales'] }} créditos</strong>
                            </div>
                            <div style="display:flex; justify-content:space-between;">
                                <span>Cota Mínima por Seriación:</span>
                                <strong>{{ $recommendation['proyeccionEgreso']['cotaPorSeriacion'] }} semestres</strong>
                            </div>
                            <div style="display:flex; justify-content:space-between;">
                                <span>Cota Mínima por Créditos:</span>
                                <strong>{{ $recommendation['proyeccionEgreso']['cotaPorCreditos'] }} semestres</strong>
                            </div>
                        </div>

                        @if(!empty($recommendation['proyeccionEgreso']['cadenaCriticaEjemplo']))
                            <div style="margin-top:14px; border-top:1px solid var(--line); padding-top:10px;">
                                <strong style="font-size:0.75rem; color:var(--navy); display:block; margin-bottom:4px;">Cadena Crítica de Seriación:</strong>
                                <div style="font-size:0.75rem; color:var(--muted); line-height:1.4;">
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
    <div class="modal" role="dialog" aria-modal="true" aria-labelledby="modal-materia-titulo">
        <div class="modal-header">
            <div>
                <span id="modal-clave" style="font-size:0.75rem; color:var(--muted); font-weight:600;"></span>
                <h2 class="modal-title" id="modal-materia-titulo">Detalle de Materia</h2>
            </div>
            <button type="button" class="modal-close" onclick="cerrarModal()" aria-label="Cerrar ventana">&times;</button>
        </div>

        <div id="modal-cargando" style="text-align:center; padding:20px; color:var(--muted);">
            Cargando información curricular...
        </div>

        <div id="modal-contenido" hidden>
            <div style="display:flex; gap:16px; flex-wrap:wrap; margin-bottom:16px; font-size:0.875rem;">
                <div>Créditos: <strong id="modal-creditos"></strong></div>
                <div>Horas: <span id="modal-horas"></span></div>
                <div>Semestre oficial: <strong id="modal-semestre"></strong></div>
                <div>Estatus: <strong id="modal-estatus"></strong></div>
            </div>

            <!-- Prerrequisitos y materias que desbloquea -->
            <div style="display:grid; grid-template-columns:1fr 1fr; gap:14px; margin-bottom:18px; font-size:0.8125rem;">
                <div style="background:var(--bg); padding:10px 12px; border-radius:6px;">
                    <strong style="color:var(--navy); display:block; margin-bottom:6px;">Prerrequisitos obligatorios:</strong>
                    <ul id="modal-prerrequisitos" style="margin:0; padding-left:18px; color:var(--muted);"></ul>
                </div>
                <div style="background:var(--bg); padding:10px 12px; border-radius:6px;">
                    <strong style="color:var(--navy); display:block; margin-bottom:6px;">Desbloquea en semestres futuros:</strong>
                    <ul id="modal-subsecuentes" style="margin:0; padding-left:18px; color:var(--muted);"></ul>
                </div>
            </div>

            <!-- Observaciones de Docentes y Asesores -->
            <div style="margin-top:20px; border-top:1px solid var(--line); padding-top:16px;">
                <h4 style="margin:0 0 10px; color:var(--navy); font-size:0.95rem;">Observaciones y Retroalimentación de Docentes</h4>
                <div id="modal-comentarios-lista"></div>

                <!-- Formulario para agregar observación -->
                <form id="form-nuevo-comentario" onsubmit="guardarComentario(event)" style="margin-top:14px; background:var(--bg); padding:12px; border-radius:8px;">
                    <strong style="display:block; margin-bottom:8px; font-size:0.8125rem; color:var(--navy);">Registrar nueva nota de asesoría o desempeño:</strong>
                    <div style="display:grid; grid-template-columns:1fr 1fr; gap:10px; margin-bottom:8px;">
                        <div>
                            <input type="text" id="comentario-autor" class="input-text" placeholder="Nombre del docente/asesor" required>
                        </div>
                        <div>
                            <select id="comentario-rol" class="select-input" required>
                                <option value="Docente Titular">Docente Titular</option>
                                <option value="Tutor Académico">Tutor Académico</option>
                                <option value="Asesor de Proyecto">Asesor de Proyecto</option>
                                <option value="Coordinación">Coordinación</option>
                            </select>
                        </div>
                    </div>
                    <div class="form-group" style="margin-bottom:8px;">
                        <textarea id="comentario-texto" class="input-text" rows="2" placeholder="Escribe aquí las observaciones sobre el desempeño del estudiante en esta asignatura..." required></textarea>
                    </div>
                    <button type="submit" class="btn-action primary" style="font-size:0.8125rem; padding:6px 12px;">Guardar Observación</button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
let materiaActualId = null;

function mostrarSeccion(seccion) {
    const secReticula = document.getElementById('seccion-reticula');
    const secRecom = document.getElementById('seccion-recomendacion');
    const tabReticula = document.getElementById('tab-reticula');
    const tabRecom = document.getElementById('tab-recomendacion');

    if (seccion === 'reticula') {
        secReticula.hidden = false;
        secRecom.hidden = true;
        tabReticula.classList.add('active');
        tabRecom.classList.remove('active');
    } else {
        secReticula.hidden = true;
        secRecom.hidden = false;
        tabReticula.classList.remove('active');
        tabRecom.classList.add('active');
    }
    cerrarMenu();
}

function abrirModalMateria(id) {
    materiaActualId = id;
    const modal = document.getElementById('modal-materia');
    const cargando = document.getElementById('modal-cargando');
    const contenido = document.getElementById('modal-contenido');

    modal.hidden = false;
    cargando.hidden = false;
    contenido.hidden = true;

    fetch(`/api/materias/${id}`, {
        headers: { 'Accept': 'application/json' }
    })
    .then(r => r.json())
    .then(resp => {
        if (!resp.success) return;
        const data = resp.data;
        document.getElementById('modal-clave').textContent = data.clave;
        document.getElementById('modal-materia-titulo').textContent = data.nombre;
        document.getElementById('modal-creditos').textContent = data.creditos;
        document.getElementById('modal-horas').textContent = `${data.horasTeoria}T / ${data.horasPractica}P`;
        document.getElementById('modal-semestre').textContent = data.semestreSugerido;
        document.getElementById('modal-estatus').textContent = `${data.estatus} ${data.calificacion ? '(' + data.calificacion + ' pts)' : ''}`;

        // Prerrequisitos
        const reqUl = document.getElementById('modal-prerrequisitos');
        reqUl.innerHTML = data.prerrequisitos.length
            ? data.prerrequisitos.map(p => `<li>${p.nombre} (<code>${p.clave}</code>)</li>`).join('')
            : '<li>Sin prerrequisitos (primer semestre o libre)</li>';

        // Subsecuentes
        const subUl = document.getElementById('modal-subsecuentes');
        subUl.innerHTML = data.materiasSubsecuentes.length
            ? data.materiasSubsecuentes.map(u => `<li>${u.nombre} (<code>${u.clave}</code>)</li>`).join('')
            : '<li>Asignatura terminal o sin dependientes</li>';

        // Comentarios
        const comLista = document.getElementById('modal-comentarios-lista');
        comLista.innerHTML = data.comentariosDocentes.length
            ? data.comentariosDocentes.map(c => `
                <div class="comment-item">
                    <div class="author">
                        <span>${c.autor} (${c.rol})</span>
                        <span style="font-weight:normal; color:var(--muted);">${c.fecha}</span>
                    </div>
                    <div style="margin-top:4px;">${c.comentario}</div>
                </div>
            `).join('')
            : '<p style="color:var(--muted); font-size:0.8125rem;">No hay notas u observaciones registradas aún para esta materia.</p>';

        cargando.hidden = true;
        contenido.hidden = false;
    })
    .catch(err => {
        cargando.textContent = 'Error al cargar los datos de la asignatura.';
    });
}

function cerrarModal() {
    document.getElementById('modal-materia').hidden = true;
    materiaActualId = null;
}

function guardarComentario(e) {
    e.preventDefault();
    if (!materiaActualId) return;

    const autor = document.getElementById('comentario-autor').value;
    const rol = document.getElementById('comentario-rol').value;
    const texto = document.getElementById('comentario-texto').value;
    const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

    fetch(`/api/materias/${materiaActualId}/comentarios`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': token
        },
        body: JSON.stringify({
            author_name: autor,
            author_role: rol,
            comment: texto
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

// Menú desplegable
const btnMenu = document.getElementById("btn-menu");
const menu = document.getElementById("menu");
const cerrarMenu = () => {
    menu.hidden = true;
    btnMenu.setAttribute("aria-expanded", "false");
};
btnMenu.addEventListener("click", () => {
    const abrir = menu.hidden;
    menu.hidden = !abrir;
    btnMenu.setAttribute("aria-expanded", String(abrir));
});
document.addEventListener("click", e => {
    if (!menu.hidden && !e.target.closest(".usuario")) cerrarMenu();
});
document.addEventListener("keydown", e => {
    if (e.key === "Escape") {
        cerrarMenu();
        cerrarModal();
    }
});
document.getElementById("imprimir").addEventListener("click", () => window.print());
</script>
</body>
</html>
