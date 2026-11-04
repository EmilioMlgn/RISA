<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="color-scheme" content="light">
<title>Iniciar sesión | RISA</title>
<link rel="icon" href="{{ asset('images/tecnm-emblema.png') }}">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Public+Sans:wght@400;500;600;700;800&display=swap">
<style>
  :root { --ink: #1B2430; --muted: #5B6778; --line: #E1E7EF; --soft: #F4F7FB; --navy: #1B396A; --navy-dark: #12284D; --navy-light: #2A5298; --gold: #C9A227; --err: #B02A37; --ease-out: cubic-bezier(0.22, 1, 0.36, 1); }
  * { box-sizing: border-box; }
  body { margin: 0; min-height: 100vh; display: grid; grid-template-columns: 1.1fr 1fr; background: #fff; color: var(--ink); font: 15px/1.45 "Public Sans", system-ui, "Segoe UI", sans-serif; -webkit-font-smoothing: antialiased; }
  .lado {
    position: relative; overflow: hidden; display: flex; flex-direction: column; justify-content: space-between; padding: 40px;
    background: linear-gradient(140deg, var(--navy-dark), var(--navy) 55%, var(--navy-light)); color: #fff;
  }
  .lado::after { content: ""; position: absolute; right: -80px; bottom: -90px; width: 420px; height: 608px; background: url("{{ asset('images/tecnm-emblema.png') }}") no-repeat center / contain; filter: brightness(0) invert(1); opacity: 0.08; }
  .lado img { height: 64px; filter: brightness(0) invert(1); position: relative; z-index: 1; align-self: flex-start; }
  .lado h1 { position: relative; z-index: 1; margin: 0 0 10px; font-size: clamp(1.8rem, 3vw, 2.6rem); line-height: 1.1; font-weight: 800; letter-spacing: -0.02em; }
  .lado p { position: relative; z-index: 1; margin: 0; max-width: 420px; opacity: 0.88; }
  .lado small { position: relative; z-index: 1; opacity: 0.7; font-size: 0.8rem; }
  .linea { width: 56px; height: 4px; border-radius: 2px; background: var(--gold); margin-bottom: 18px; }

  main { display: grid; place-items: center; padding: 32px 20px; position: relative; }
  main::before { content: ""; position: absolute; right: 4%; bottom: 4%; width: 240px; height: 348px; background: url("{{ asset('images/tecnm-emblema.png') }}") no-repeat center / contain; opacity: 0.05; pointer-events: none; }
  form { position: relative; width: min(400px, 100%); animation: entrar 0.45s var(--ease-out); }
  @keyframes entrar { from { opacity: 0; transform: translateY(10px); } }
  .marca { display: flex; align-items: center; gap: 10px; margin-bottom: 26px; }
  .marca img { height: 40px; }
  .marca strong { display: block; font-size: 1.2rem; letter-spacing: 0.06em; font-weight: 800; color: var(--navy); line-height: 1.1; }
  .marca span { font-size: 0.75rem; color: var(--muted); }
  h2 { margin: 0 0 4px; font-size: 1.5rem; font-weight: 800; color: var(--navy); letter-spacing: -0.01em; }
  .sub { margin: 0 0 22px; color: var(--muted); font-size: 0.9rem; }
  label { display: block; font-weight: 600; font-size: 0.875rem; margin: 14px 0 6px; }
  input { width: 100%; padding: 11px 13px; border: 1px solid var(--line); border-radius: 10px; font: inherit; background: #fff; color: var(--ink); transition: border-color 0.2s, box-shadow 0.2s; }
  input:focus { outline: none; border-color: var(--navy-light); box-shadow: 0 0 0 3px rgba(42, 82, 152, 0.15); }
  input[aria-invalid="true"] { border-color: var(--err); }
  .err { display: flex; gap: 8px; margin: 14px 0 0; padding: 10px 12px; border-radius: 10px; background: rgba(220,53,69,0.07); border: 1px solid rgba(220,53,69,0.25); color: var(--err); font-size: 0.85rem; font-weight: 600; }
  button { margin-top: 22px; width: 100%; min-height: 46px; padding: 0 16px; border: 0; border-radius: 10px; background: linear-gradient(135deg, var(--navy), var(--navy-light)); color: #fff; font: inherit; font-weight: 700; cursor: pointer; box-shadow: 0 6px 18px rgba(27, 57, 106, 0.22); transition: transform 0.15s ease, box-shadow 0.2s; }
  button:hover { transform: translateY(-1px); box-shadow: 0 10px 24px rgba(27, 57, 106, 0.28); }
  button:active { transform: scale(0.98); }
  .volver { display: inline-flex; align-items: center; gap: 6px; margin-top: 18px; color: var(--navy); font-weight: 600; font-size: 0.875rem; text-decoration: none; }
  .volver:hover { text-decoration: underline; }
  :focus-visible { outline: 2px solid var(--navy-light); outline-offset: 2px; }
  @media (max-width: 820px) {
    body { grid-template-columns: 1fr; grid-template-rows: auto 1fr; }
    .lado { padding: 22px 20px; flex-direction: row; align-items: center; gap: 16px; }
    .lado h1, .lado p, .lado small, .linea { display: none; }
    .lado img { height: 44px; }
    .lado::after { width: 200px; height: 290px; right: -40px; bottom: -120px; }
  }
  @media (prefers-reduced-motion: reduce) { * { animation: none !important; transition: none !important; } }
</style>
</head>
<body>
<aside class="lado">
  <img src="{{ asset('images/tecnm-logo.png') }}" alt="Tecnológico Nacional de México">
  <div>
    <div class="linea"></div>
    <h1>Retícula Interactiva de Seguimiento Académico</h1>
    <p>Administra el catálogo de materias y las reglas que usa el motor de recomendación del Instituto Tecnológico de Celaya.</p>
  </div>
  <small>TecNM · Instituto Tecnológico de Celaya</small>
</aside>

<main>
  <form method="post" action="{{ route('login.attempt') }}" novalidate>
    @csrf
    <div class="marca">
      <img src="{{ asset('images/tecnm-emblema.png') }}" alt="" aria-hidden="true">
      <div><strong>RISA</strong><span>Acceso de administración</span></div>
    </div>
    <h2>Iniciar sesión</h2>
    <p class="sub">Usa tu correo institucional para administrar el catálogo de materias.</p>

    <label for="email">Correo institucional</label>
    <input id="email" name="email" type="email" value="{{ old('email') }}" autocomplete="username" placeholder="nombre@itcelaya.edu.mx" required autofocus @error('email') aria-invalid="true" @enderror>

    <label for="password">Contraseña</label>
    <input id="password" name="password" type="password" autocomplete="current-password" required @error('password') aria-invalid="true" @enderror>

    @foreach ($errors->all() as $error)
      <p class="err" role="alert">{{ $error }}</p>
    @endforeach

    <button type="submit">Entrar</button>
    <a class="volver" href="{{ route('reticula.index') }}">
      <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M15 18l-6-6 6-6"/></svg>
      Volver a la retícula
    </a>
  </form>
</main>
</body>
</html>
