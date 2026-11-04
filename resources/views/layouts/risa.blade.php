{{--
    Layout común de RISA: barra superior, menú lateral, marca de agua del TecNM y estilos base.

    Variables que recibe desde @extends('layouts.risa', [...]):
    - $activo: sección del menú marcada como actual (reticula, recomendacion, simulador, catalogo, reglas).
    - $perfil: ['nombre' => ..., 'etiqueta' => ..., 'lineas' => [...], 'avance' => float|null]
--}}
@php
    $activo = $activo ?? '';
    $perfil = $perfil ?? [
        'nombre' => auth()->user()->name ?? 'Invitado',
        'etiqueta' => auth()->check() ? ucfirst(auth()->user()->role) : 'Sin sesión',
        'lineas' => [],
        'avance' => null,
    ];
    $iniciales = collect(preg_split('/\s+/', trim($perfil['nombre'])))->take(2)->map(fn ($p) => mb_substr($p, 0, 1))->implode('');
    $esAdmin = auth()->check() && auth()->user()->role === 'admin';
    $cur = fn (string $s) => $activo === $s ? 'aria-current=page' : '';
@endphp
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="color-scheme" content="light">
    <title>@yield('titulo', 'RISA') | RISA — Retícula Interactiva de Seguimiento Académico</title>
    <link rel="icon" href="{{ asset('images/tecnm-emblema.png') }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Public+Sans:wght@400;500;600;700;800&display=swap">
    <style>
        :root {
            --bg: #FFFFFF;
            --surface: rgba(255, 255, 255, 0.9);
            --surface-solid: #FFFFFF;
            --soft: #F4F7FB;
            --ink: #1B2430;
            --muted: #5B6778;
            --line: #E1E7EF;
            --navy: #1B396A;
            --navy-dark: #12284D;
            --navy-light: #2A5298;
            --gold: #C9A227;
            --a: #28A745;
            --c: #007BFF;
            --r: #DC3545;
            --d: #E0A800;
            --p: #6C757D;
            --shadow-sm: 0 1px 2px rgba(27, 57, 106, 0.06), 0 1px 3px rgba(27, 57, 106, 0.08);
            --shadow-md: 0 6px 18px rgba(27, 57, 106, 0.10);
            --shadow-lg: 0 24px 60px rgba(18, 40, 77, 0.25);
            --ease-out: cubic-bezier(0.22, 1, 0.36, 1);
        }
        * { box-sizing: border-box; }
        html { scroll-behavior: smooth; }
        body {
            margin: 0;
            min-height: 100vh;
            background: var(--bg);
            color: var(--ink);
            font: 15px/1.45 "Public Sans", system-ui, -apple-system, "Segoe UI", sans-serif;
            -webkit-font-smoothing: antialiased;
        }
        /* Marca de agua institucional */
        body::before {
            content: "";
            position: fixed;
            right: -4vw;
            bottom: -6vh;
            width: min(62vw, 720px);
            aspect-ratio: 483 / 700;
            background: url("{{ asset('images/tecnm-emblema.png') }}") no-repeat center / contain;
            opacity: 0.07;
            pointer-events: none;
            z-index: 0;
        }
        body > * { position: relative; z-index: 1; }

        [data-e="A"] { --s: var(--a); }
        [data-e="C"] { --s: var(--c); }
        [data-e="R"] { --s: var(--r); }
        [data-e="D"] { --s: var(--d); }
        [data-e="P"] { --s: var(--p); }

        :focus-visible { outline: 2px solid var(--navy-light); outline-offset: 2px; }

        /* ===== Barra superior ===== */
        .appbar {
            position: sticky; top: 0; z-index: 40;
            background: linear-gradient(100deg, var(--navy-dark) 0%, var(--navy) 55%, var(--navy-light) 100%);
            color: #fff;
            box-shadow: 0 2px 12px rgba(18, 40, 77, 0.25);
        }
        .appbar::after {
            content: ""; position: absolute; left: 0; right: 0; bottom: 0; height: 3px;
            background: linear-gradient(90deg, var(--gold), transparent 70%);
            opacity: 0.8;
        }
        .appbar-in { max-width: 1440px; margin: 0 auto; padding: 10px 20px; display: flex; align-items: center; justify-content: space-between; gap: 16px; }
        .lado-izq { display: flex; align-items: center; gap: 14px; min-width: 0; }
        .marca { display: flex; align-items: center; gap: 12px; min-width: 0; color: inherit; text-decoration: none; }
        .marca img { height: 38px; width: auto; filter: brightness(0) invert(1); }
        .marca-txt { display: flex; flex-direction: column; line-height: 1.15; min-width: 0; }
        .marca strong { font-size: 1.25rem; letter-spacing: 0.06em; font-weight: 800; }
        .marca-txt span { font-size: 0.75rem; opacity: 0.85; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
        .usuario { display: flex; align-items: center; gap: 12px; min-width: 0; }
        .alumno { font-weight: 600; font-size: 0.875rem; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
        .control-pill { background: rgba(255,255,255,0.16); border: 1px solid rgba(255,255,255,0.25); padding: 2px 10px; border-radius: 999px; font-size: 0.75rem; font-variant-numeric: tabular-nums; }
        @media (max-width: 720px) {
            .marca-txt span, .control-pill { display: none; }
            .alumno { max-width: 40vw; }
        }

        /* Botón hamburguesa animado */
        .btn-menu {
            position: relative; flex: none;
            display: inline-flex; flex-direction: column; align-items: center; justify-content: center; gap: 5px;
            width: 44px; height: 44px; border: 1px solid rgba(255,255,255,0.25); border-radius: 12px;
            background: rgba(255,255,255,0.08); color: #fff; cursor: pointer;
            transition: background 0.2s ease, transform 0.15s ease;
        }
        .btn-menu:hover { background: rgba(255,255,255,0.18); }
        .btn-menu:active { transform: scale(0.94); }
        .btn-menu span {
            display: block; width: 20px; height: 2px; border-radius: 2px; background: currentColor;
            transition: transform 0.35s var(--ease-out), opacity 0.2s ease, width 0.3s var(--ease-out);
        }
        .btn-menu span:nth-child(2) { width: 14px; align-self: flex-start; margin-left: 11px; }
        .btn-menu:hover span:nth-child(2) { width: 20px; }
        .btn-menu[aria-expanded="true"] span:nth-child(1) { transform: translateY(7px) rotate(45deg); }
        .btn-menu[aria-expanded="true"] span:nth-child(2) { opacity: 0; transform: scaleX(0); }
        .btn-menu[aria-expanded="true"] span:nth-child(3) { transform: translateY(-7px) rotate(-45deg); }

        /* ===== Menú lateral ===== */
        .drawer-overlay {
            position: fixed; inset: 0; z-index: 60;
            background: rgba(18, 40, 77, 0.45);
            backdrop-filter: blur(3px);
            opacity: 0; visibility: hidden;
            transition: opacity 0.3s ease, visibility 0.3s;
        }
        .drawer {
            position: fixed; top: 0; left: 0; bottom: 0; z-index: 70;
            width: min(360px, 88vw);
            display: flex; flex-direction: column;
            background: var(--surface-solid);
            box-shadow: var(--shadow-lg);
            transform: translateX(-105%);
            transition: transform 0.42s var(--ease-out);
            visibility: hidden;
        }
        body.menu-abierto .drawer-overlay { opacity: 1; visibility: visible; }
        body.menu-abierto .drawer { transform: none; visibility: visible; }
        body.menu-abierto { overflow: hidden; }

        .drawer-h {
            position: relative; overflow: hidden;
            padding: 20px 20px 18px;
            background: linear-gradient(135deg, var(--navy-dark), var(--navy) 60%, var(--navy-light));
            color: #fff;
        }
        .drawer-h::after {
            content: ""; position: absolute; right: -30px; bottom: -40px; width: 170px; height: 246px;
            background: url("{{ asset('images/tecnm-emblema.png') }}") no-repeat center / contain;
            filter: brightness(0) invert(1); opacity: 0.1; pointer-events: none;
        }
        .drawer-top { display: flex; justify-content: space-between; align-items: center; margin-bottom: 18px; }
        .drawer-top img { height: 46px; filter: brightness(0) invert(1); }
        .drawer-cerrar {
            display: grid; place-items: center; width: 36px; height: 36px; border: 0; border-radius: 10px;
            background: rgba(255,255,255,0.14); color: #fff; cursor: pointer; transition: background 0.2s, transform 0.3s var(--ease-out);
        }
        .drawer-cerrar:hover { background: rgba(255,255,255,0.26); transform: rotate(90deg); }
        .perfil { display: flex; align-items: center; gap: 12px; position: relative; z-index: 1; }
        .avatar {
            flex: none; display: grid; place-items: center; width: 52px; height: 52px; border-radius: 50%;
            background: #fff; color: var(--navy); font-weight: 800; font-size: 1.1rem;
            box-shadow: 0 0 0 3px rgba(255,255,255,0.3);
        }
        .perfil strong { display: block; font-size: 1rem; line-height: 1.25; }
        .perfil small { display: block; opacity: 0.85; font-size: 0.78rem; }
        .mini-avance { position: relative; z-index: 1; margin-top: 16px; font-size: 0.75rem; }
        .mini-avance div { display: flex; justify-content: space-between; margin-bottom: 6px; opacity: 0.9; }
        .mini-bar { height: 6px; border-radius: 3px; background: rgba(255,255,255,0.2); overflow: hidden; }
        .mini-bar span { display: block; height: 100%; border-radius: 3px; background: linear-gradient(90deg, #7CE495, #fff); }

        .drawer-nav { flex: 1; overflow-y: auto; padding: 14px 12px; display: flex; flex-direction: column; gap: 4px; }
        .drawer-nav h3 { margin: 10px 10px 6px; font-size: 0.6875rem; letter-spacing: 0.08em; text-transform: uppercase; color: var(--muted); }
        .nav-item {
            display: grid; grid-template-columns: 40px 1fr auto; align-items: center; gap: 12px;
            width: 100%; padding: 10px; border: 0; border-radius: 12px;
            background: none; color: var(--ink); font: inherit; text-align: left; cursor: pointer; text-decoration: none;
            transition: background 0.2s ease, transform 0.2s var(--ease-out);
            opacity: 0; transform: translateX(-16px);
        }
        body.menu-abierto .nav-item { opacity: 1; transform: none; transition: background 0.2s ease, opacity 0.35s ease, transform 0.45s var(--ease-out); }
        body.menu-abierto .nav-item:nth-of-type(1) { transition-delay: 0.08s; }
        body.menu-abierto .nav-item:nth-of-type(2) { transition-delay: 0.13s; }
        body.menu-abierto .nav-item:nth-of-type(3) { transition-delay: 0.18s; }
        body.menu-abierto .nav-item:nth-of-type(4) { transition-delay: 0.23s; }
        .nav-item:hover { background: var(--soft); }
        .nav-item:hover .nav-ico { transform: scale(1.06); }
        .nav-ico {
            display: grid; place-items: center; width: 40px; height: 40px; border-radius: 11px;
            background: color-mix(in srgb, var(--navy) 9%, #fff); color: var(--navy);
            transition: transform 0.2s var(--ease-out), background 0.2s, color 0.2s;
        }
        .nav-item b { display: block; font-weight: 600; font-size: 0.92rem; }
        .nav-item small { display: block; color: var(--muted); font-size: 0.76rem; }
        .nav-item .chev { color: var(--muted); transition: transform 0.2s var(--ease-out); }
        .nav-item:hover .chev { transform: translateX(3px); }
        .nav-item[aria-current="page"] { background: color-mix(in srgb, var(--navy) 8%, #fff); }
        .nav-item[aria-current="page"] .nav-ico { background: var(--navy); color: #fff; }
        .nav-item[aria-current="page"] b { color: var(--navy); }
        .drawer-f { padding: 14px 20px; border-top: 1px solid var(--line); font-size: 0.75rem; color: var(--muted); display: flex; align-items: center; gap: 10px; }
        .drawer-f img { height: 22px; }

        /* ===== Contenido ===== */
        .wrap { max-width: 1440px; margin: 0 auto; padding: 28px 20px 60px; }
        .hero {
            display: flex; flex-wrap: wrap; gap: 18px 24px; justify-content: space-between; align-items: flex-end;
            margin-bottom: 22px;
        }
        .eyebrow {
            display: inline-flex; align-items: center; gap: 8px; margin-bottom: 8px;
            padding: 4px 10px; border-radius: 999px;
            background: color-mix(in srgb, var(--navy) 8%, #fff); color: var(--navy);
            font-size: 0.75rem; font-weight: 700; letter-spacing: 0.04em; text-transform: uppercase;
        }
        .eyebrow img { height: 16px; }
        h1 { margin: 0 0 6px; font-size: clamp(1.45rem, 2.6vw, 2.1rem); line-height: 1.15; font-weight: 800; color: var(--navy); letter-spacing: -0.02em; }
        .sub { margin: 0; color: var(--muted); font-size: 0.92rem; }

        .actions { display: flex; gap: 10px; flex-wrap: wrap; }
        .btn-action {
            display: inline-flex; align-items: center; gap: 8px; min-height: 42px; padding: 0 16px;
            border: 1px solid var(--line); border-radius: 10px; background: var(--surface-solid);
            color: var(--navy); font: inherit; font-size: 0.875rem; font-weight: 600; cursor: pointer;
            box-shadow: var(--shadow-sm);
            transition: background 0.15s ease, transform 0.15s ease, box-shadow 0.2s ease, border-color 0.2s;
        }
        .btn-action:hover { border-color: color-mix(in srgb, var(--navy) 35%, var(--line)); box-shadow: var(--shadow-md); transform: translateY(-1px); }
        .btn-action:active { transform: translateY(0) scale(0.98); }
        .btn-action.primary { background: linear-gradient(135deg, var(--navy), var(--navy-light)); color: #fff; border-color: transparent; }

        /* Indicadores */
        .stats { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 14px; margin-bottom: 22px; }
        .stat {
            position: relative; overflow: hidden;
            padding: 16px 18px; border: 1px solid var(--line); border-radius: 14px;
            background: var(--surface); backdrop-filter: blur(6px); box-shadow: var(--shadow-sm);
        }
        .stat::before { content: ""; position: absolute; left: 0; top: 0; bottom: 0; width: 4px; background: var(--k, var(--navy)); }
        .stat small { display: block; color: var(--muted); font-size: 0.8rem; font-weight: 500; }
        .stat strong { display: block; margin-top: 2px; font-size: 1.65rem; line-height: 1.15; font-weight: 800; color: var(--ink); font-variant-numeric: tabular-nums; }
        .stat strong span { font-size: 0.9rem; font-weight: 500; color: var(--muted); }
        .stat .ring { position: absolute; right: 14px; top: 50%; transform: translateY(-50%); }
        @media (max-width: 560px) {
            .stats { grid-template-columns: 1fr 1fr; gap: 10px; }
            .stat { padding: 12px 14px; }
            .stat strong { font-size: 1.35rem; }
            .stat .ring { display: none; }
            .actions { width: 100%; }
            .actions .btn-action { flex: 1; justify-content: center; }
            .actions .btn-action.primary { flex-basis: 100%; }
        }


        /* Progreso */
        .panel {
            background: var(--surface); backdrop-filter: blur(6px);
            border: 1px solid var(--line); border-radius: 14px; box-shadow: var(--shadow-sm);
        }

        @media (prefers-reduced-motion: reduce) {
            *, *::before, *::after { animation-duration: 0.01ms !important; animation-iteration-count: 1 !important; transition-duration: 0.01ms !important; transition-delay: 0s !important; }
        }

        .vh { position: absolute; width: 1px; height: 1px; overflow: hidden; clip-path: inset(50%); white-space: nowrap; }
        .nav-lock { font-size: 0.68rem; font-weight: 700; color: var(--muted); background: var(--soft); border: 1px solid var(--line); border-radius: 999px; padding: 1px 7px; }
        .drawer-nav form { margin: 0; }

        /* Avisos y componentes compartidos */
        .banner { display: block; margin: 0 0 18px; padding: 12px 14px; border-radius: 12px; border: 1px solid color-mix(in srgb, var(--a) 40%, var(--line)); background: color-mix(in srgb, var(--a) 8%, #fff); font-weight: 600; font-size: 0.9rem; }
        .banner.error { border-color: color-mix(in srgb, var(--r) 40%, var(--line)); background: color-mix(in srgb, var(--r) 7%, #fff); color: #B02A37; }
        .banner.info { border-color: rgba(0,123,255,0.25); background: rgba(0,123,255,0.06); color: var(--ink); font-weight: 500; }
        .banner[hidden] { display: none; }
        .chip { display: inline-flex; align-items: center; gap: 4px; padding: 2px 10px; margin: 0 4px 4px 0; border: 1px solid var(--line); border-radius: 999px; background: #fff; font-size: 0.78rem; }
        .tabla-w { overflow-x: auto; border: 1px solid var(--line); border-radius: 12px; background: #fff; }
        .tabla { width: 100%; border-collapse: collapse; font-size: 0.875rem; }
        .tabla th, .tabla td { padding: 11px 14px; text-align: left; vertical-align: top; border-bottom: 1px solid var(--line); }
        .tabla th { background: var(--soft); color: var(--navy); font-size: 0.8125rem; font-weight: 600; white-space: nowrap; }
        .tabla tbody tr { transition: background 0.15s; }
        .tabla tbody tr:hover { background: color-mix(in srgb, var(--soft) 70%, #fff); }
        .tabla tbody tr:last-child td { border-bottom: 0; }
        .tabla .num { text-align: right; font-variant-numeric: tabular-nums; }
        .tabla code { padding: 2px 6px; border-radius: 6px; background: var(--soft); font-size: 0.78rem; }
        .campo label { display: block; margin-bottom: 5px; font-weight: 600; font-size: 0.85rem; }
        .campo input, .campo select {
            width: 100%; padding: 10px 12px; border: 1px solid var(--line); border-radius: 10px;
            background: #fff; color: var(--ink); font: inherit; font-size: 0.9rem;
            transition: border-color 0.2s, box-shadow 0.2s;
        }
        .campo input:focus, .campo select:focus { outline: none; border-color: var(--navy-light); box-shadow: 0 0 0 3px rgba(42, 82, 152, 0.15); }
        .campo .hint { margin: 5px 0 0; color: var(--muted); font-size: 0.78rem; }
        .seccion-h { display: flex; flex-wrap: wrap; justify-content: space-between; align-items: center; gap: 10px 16px; margin-bottom: 14px; }
        .seccion-h h2 { margin: 0; color: var(--navy); font-size: 1.1rem; }
        .vacio { display: grid; justify-items: center; gap: 8px; padding: 32px 20px; text-align: center; color: var(--muted); }
        .vacio svg { color: var(--navy); }
        .vacio h3 { margin: 0; color: var(--navy); font-size: 1rem; }

        @media print {
            .appbar, .drawer, .drawer-overlay, .eyebrow { display: none !important; }
            body::before { opacity: 0.04; }
        }
    </style>
    @stack('styles')
</head>
<body>

<header class="appbar">
    <div class="appbar-in">
        <div class="lado-izq">
            <button type="button" class="btn-menu" id="btn-menu" aria-label="Abrir menú" aria-expanded="false" aria-controls="drawer">
                <span></span><span></span><span></span>
            </button>
            <a class="marca" href="{{ route('reticula.index') }}">
                <img src="{{ asset('images/tecnm-emblema.png') }}" alt="TecNM">
                <span class="marca-txt">
                    <strong>RISA</strong>
                    <span>Retícula Interactiva de Seguimiento Académico</span>
                </span>
            </a>
        </div>
        <div class="usuario">
            @if(!empty($perfil['etiqueta']))
                <span class="control-pill">{{ $perfil['etiqueta'] }}</span>
            @endif
            <span class="alumno" id="alumno">{{ $perfil['nombre'] }}</span>
        </div>
    </div>
</header>

<!-- MENÚ LATERAL -->
<div class="drawer-overlay" id="drawer-overlay"></div>
<aside class="drawer" id="drawer" role="dialog" aria-modal="true" aria-label="Menú principal">
    <div class="drawer-h">
        <div class="drawer-top">
            <img src="{{ asset('images/tecnm-logo.png') }}" alt="Tecnológico Nacional de México">
            <button type="button" class="drawer-cerrar" id="drawer-cerrar" aria-label="Cerrar menú">
                <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round"><path d="M6 6l12 12M18 6L6 18"/></svg>
            </button>
        </div>
        <div class="perfil">
            <span class="avatar" aria-hidden="true">{{ $iniciales }}</span>
            <div>
                <strong>{{ $perfil['nombre'] }}</strong>
                @foreach($perfil['lineas'] ?? [] as $linea)
                    <small>{{ $linea }}</small>
                @endforeach
            </div>
        </div>
        @if(isset($perfil['avance']))
            <div class="mini-avance">
                <div><span>Avance de la carrera</span><strong>{{ $perfil['avance'] }} %</strong></div>
                <div class="mini-bar"><span style="width: {{ min(100, $perfil['avance']) }}%"></span></div>
            </div>
        @endif
    </div>

    <nav class="drawer-nav">
        <h3>Estudiante</h3>
        <a class="nav-item" href="{{ route('reticula.index') }}" data-sec="reticula" {{ $cur('reticula') }}>
            <span class="nav-ico"><svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" stroke-linejoin="round"><path d="M3 3h7v7H3zM14 3h7v7h-7zM14 14h7v7h-7zM3 14h7v7H3z"/></svg></span>
            <span><b>Retícula académica</b><small>Tu avance semestre por semestre</small></span>
            <svg class="chev" viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M9 18l6-6-6-6"/></svg>
        </a>
        <a class="nav-item" href="{{ route('reticula.index') }}#recomendacion" data-sec="recomendacion" {{ $cur('recomendacion') }}>
            <span class="nav-ico"><svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" stroke-linejoin="round"><path d="M13 3L4 14h7l-1 7 9-11h-7l1-7z"/></svg></span>
            <span><b>Recomendación de carga</b><small>Materias sugeridas y egreso</small></span>
            <svg class="chev" viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M9 18l6-6-6-6"/></svg>
        </a>
        <a class="nav-item" href="{{ route('recomendacion.simulador') }}" {{ $cur('simulador') }}>
            <span class="nav-ico"><svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 3v18h18"/><path d="M7 15l4-4 3 3 5-6"/></svg></span>
            <span><b>Simulador de escenarios</b><small>Prueba otra carga de créditos</small></span>
            <svg class="chev" viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M9 18l6-6-6-6"/></svg>
        </a>

        <h3>Administración</h3>
        <a class="nav-item" href="{{ route('catalogo.materias') }}" {{ $cur('catalogo') }}>
            <span class="nav-ico"><svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" stroke-linejoin="round"><path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"/><path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"/></svg></span>
            <span><b>Catálogo de materias</b><small>Alta, edición y bajas</small></span>
            @if($esAdmin)
                <svg class="chev" viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M9 18l6-6-6-6"/></svg>
            @else
                <span class="nav-lock">Admin</span>
            @endif
        </a>
        <a class="nav-item" href="{{ route('reglas.configuracion') }}" {{ $cur('reglas') }}>
            <span class="nav-ico"><svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 21v-7M4 10V3M12 21v-9M12 8V3M20 21v-5M20 12V3M1 14h6M9 8h6M17 16h6"/></svg></span>
            <span><b>Reglas de recomendación</b><small>Límites y prerrequisitos vigentes</small></span>
            @if($esAdmin)
                <svg class="chev" viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M9 18l6-6-6-6"/></svg>
            @else
                <span class="nav-lock">Admin</span>
            @endif
        </a>

        <h3>Sesión y herramientas</h3>
        <button type="button" class="nav-item" onclick="cerrarMenu(); setTimeout(() => window.print(), 350)">
            <span class="nav-ico"><svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" stroke-linejoin="round"><path d="M6 9V3h12v6M6 18H4v-7h16v7h-2M7 14h10v7H7z"/></svg></span>
            <span><b>Imprimir página</b><small>Versión para entregar</small></span>
            <svg class="chev" viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M9 18l6-6-6-6"/></svg>
        </button>
        @auth
            <form method="post" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="nav-item">
                    <span class="nav-ico"><svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4M16 17l5-5-5-5M21 12H9"/></svg></span>
                    <span><b>Cerrar sesión</b><small>{{ auth()->user()->email }}</small></span>
                    <svg class="chev" viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M9 18l6-6-6-6"/></svg>
                </button>
            </form>
        @else
            <a class="nav-item" href="{{ route('login') }}">
                <span class="nav-ico"><svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M15 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4M10 17l5-5-5-5M15 12H3"/></svg></span>
                <span><b>Iniciar sesión</b><small>Acceso de administración</small></span>
                <svg class="chev" viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M9 18l6-6-6-6"/></svg>
            </a>
        @endauth
    </nav>

    <div class="drawer-f">
        <img src="{{ asset('images/tecnm-emblema.png') }}" alt="" aria-hidden="true">
        <span>TecNM · Instituto Tecnológico de Celaya<br>RISA v1</span>
    </div>
</aside>

@yield('content')

<script>
// Menú lateral
const btnMenu = document.getElementById('btn-menu');
const drawer = document.getElementById('drawer');

function abrirMenu() {
    document.body.classList.add('menu-abierto');
    btnMenu.setAttribute('aria-expanded', 'true');
    btnMenu.setAttribute('aria-label', 'Cerrar menú');
    setTimeout(() => drawer.querySelector('.nav-item[aria-current="page"], .nav-item')?.focus(), 120);
}
function cerrarMenu() {
    if (!document.body.classList.contains('menu-abierto')) return;
    document.body.classList.remove('menu-abierto');
    btnMenu.setAttribute('aria-expanded', 'false');
    btnMenu.setAttribute('aria-label', 'Abrir menú');
    btnMenu.focus();
}
btnMenu.addEventListener('click', () => document.body.classList.contains('menu-abierto') ? cerrarMenu() : abrirMenu());
document.getElementById('drawer-overlay').addEventListener('click', cerrarMenu);
document.getElementById('drawer-cerrar').addEventListener('click', cerrarMenu);

// Mantener el foco dentro del menú mientras está abierto
drawer.addEventListener('keydown', e => {
    if (e.key !== 'Tab') return;
    const f = [...drawer.querySelectorAll('button, a[href]')];
    const primero = f[0], ultimo = f[f.length - 1];
    if (e.shiftKey && document.activeElement === primero) { e.preventDefault(); ultimo.focus(); }
    else if (!e.shiftKey && document.activeElement === ultimo) { e.preventDefault(); primero.focus(); }
});

document.addEventListener('keydown', e => {
    if (e.key !== 'Escape') return;
    if (document.body.classList.contains('menu-abierto')) cerrarMenu();
    else if (typeof cerrarModal === 'function') cerrarModal();
});
</script>
@stack('scripts')
</body>
</html>
