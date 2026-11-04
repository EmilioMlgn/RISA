<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="color-scheme" content="light">
    <title>Retícula | RISA — Retícula Interactiva de Seguimiento Académico</title>
    <link rel="icon" href="{{ asset('images/tecnm-emblema.png') }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Public+Sans:wght@400;500;600;700;800&display=swap">
    @php
        $resumen = $grid['resumen'];
        $semActual = collect($grid['semestres'])->firstWhere('esSemestreActual', true);
        $iniciales = collect(preg_split('/\s+/', trim($student->name)))->take(2)->map(fn ($p) => mb_substr($p, 0, 1))->implode('');
        $carrera = $student->career ?? 'Ingeniería en Sistemas Computacionales';
    @endphp
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

        /* Progreso */
        .panel {
            background: var(--surface); backdrop-filter: blur(6px);
            border: 1px solid var(--line); border-radius: 14px; box-shadow: var(--shadow-sm);
        }
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

        @media (prefers-reduced-motion: reduce) {
            *, *::before, *::after { animation-duration: 0.01ms !important; animation-iteration-count: 1 !important; transition-duration: 0.01ms !important; transition-delay: 0s !important; }
        }

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
            <span class="control-pill">{{ $student->control_number ?? '20030582' }}</span>
            <span class="alumno" id="alumno">{{ $student->name }}</span>
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
                <strong>{{ $student->name }}</strong>
                <small>No. de control {{ $student->control_number ?? '20030582' }}</small>
                <small>{{ $carrera }}</small>
            </div>
        </div>
        <div class="mini-avance">
            <div><span>Avance de la carrera</span><strong>{{ $resumen['porcentajeAvance'] }} %</strong></div>
            <div class="mini-bar"><span style="width: {{ min(100, $resumen['porcentajeAvance']) }}%"></span></div>
        </div>
    </div>

    <nav class="drawer-nav">
        <h3>Navegación</h3>
        <button type="button" class="nav-item" data-sec="reticula" aria-current="page" onclick="mostrarSeccion('reticula')">
            <span class="nav-ico"><svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" stroke-linejoin="round"><path d="M3 3h7v7H3zM14 3h7v7h-7zM14 14h7v7h-7zM3 14h7v7H3z"/></svg></span>
            <span><b>Retícula académica</b><small>Tu avance semestre por semestre</small></span>
            <svg class="chev" viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M9 18l6-6-6-6"/></svg>
        </button>
        <button type="button" class="nav-item" data-sec="recomendacion" onclick="mostrarSeccion('recomendacion')">
            <span class="nav-ico"><svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" stroke-linejoin="round"><path d="M13 3L4 14h7l-1 7 9-11h-7l1-7z"/></svg></span>
            <span><b>Recomendación de carga</b><small>Materias sugeridas y egreso</small></span>
            <svg class="chev" viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M9 18l6-6-6-6"/></svg>
        </button>
        <h3>Módulos</h3>
        <a class="nav-item" href="{{ route('catalogo.materias') }}">
            <span class="nav-ico"><svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" stroke-linejoin="round"><path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"/><path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"/></svg></span>
            <span><b>Catálogo de materias</b><small>Administración de asignaturas</small></span>
            <svg class="chev" viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M9 18l6-6-6-6"/></svg>
        </a>
        <a class="nav-item" href="{{ route('reglas.configuracion') }}">
            <span class="nav-ico"><svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 21v-7M4 10V3M12 21v-9M12 8V3M20 21v-5M20 12V3M1 14h6M9 8h6M17 16h6"/></svg></span>
            <span><b>Configuración de reglas</b><small>Reglas institucionales de carga</small></span>
            <svg class="chev" viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M9 18l6-6-6-6"/></svg>
        </a>
        <a class="nav-item" href="{{ route('recomendacion.simulador') }}">
            <span class="nav-ico"><svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" stroke-linejoin="round"><path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z"/><path d="M14 2v6h6M16 13H8M16 17H8M10 9H8"/></svg></span>
            <span><b>Simulador de escenarios</b><small>Prueba distintas cargas</small></span>
            <svg class="chev" viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M9 18l6-6-6-6"/></svg>
        </a>
        <h3>Herramientas</h3>
        <button type="button" class="nav-item" onclick="cerrarMenu(); setTimeout(() => window.print(), 350)">
            <span class="nav-ico"><svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" stroke-linejoin="round"><path d="M6 9V3h12v6M6 18H4v-7h16v7h-2M7 14h10v7H7z"/></svg></span>
            <span><b>Imprimir retícula</b><small>Versión horizontal para entregar</small></span>
            <svg class="chev" viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M9 18l6-6-6-6"/></svg>
        </button>
    </nav>

    <div class="drawer-f">
        <img src="{{ asset('images/tecnm-emblema.png') }}" alt="" aria-hidden="true">
        <span>TecNM · Instituto Tecnológico de Celaya<br>RISA v1</span>
    </div>
</aside>

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
    const f = [...drawer.querySelectorAll('button')];
    const primero = f[0], ultimo = f[f.length - 1];
    if (e.shiftKey && document.activeElement === primero) { e.preventDefault(); ultimo.focus(); }
    else if (!e.shiftKey && document.activeElement === ultimo) { e.preventDefault(); primero.focus(); }
});

document.addEventListener('keydown', e => {
    if (e.key !== 'Escape') return;
    if (document.body.classList.contains('menu-abierto')) cerrarMenu();
    else cerrarModal();
});
document.getElementById('imprimir').addEventListener('click', () => window.print());

// Animación de la barra de avance al cargar
requestAnimationFrame(() => {
    document.querySelectorAll('[data-ancho]').forEach(el => { el.style.width = el.dataset.ancho + '%'; });
});
</script>
</body>
</html>
