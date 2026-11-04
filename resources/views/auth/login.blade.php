<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Iniciar sesión | RISA</title>
<style>
  body { margin: 0; min-height: 100vh; display: grid; place-items: center; background: #F3F5F8; color: #1B2430; font: 15px/1.45 "Public Sans", system-ui, "Segoe UI", sans-serif; }
  form { background: #fff; border: 1px solid #D5DCE5; border-radius: 10px; padding: 28px; width: min(380px, 92vw); }
  h1 { margin: 0 0 4px; font-size: 1.3rem; color: #1B396A; }
  p.sub { margin: 0 0 18px; color: #566273; font-size: .875rem; }
  label { display: block; font-weight: 600; margin: 12px 0 4px; }
  input { width: 100%; padding: 9px 10px; border: 1px solid #D5DCE5; border-radius: 6px; font: inherit; box-sizing: border-box; }
  .err { color: #B02A37; font-size: .8125rem; margin: 6px 0 0; }
  button { margin-top: 18px; width: 100%; padding: 10px; background: #1B396A; color: #fff; border: 0; border-radius: 6px; font: inherit; font-weight: 600; cursor: pointer; }
</style>
</head>
<body>
<form method="post" action="{{ route('login.attempt') }}" novalidate>
  @csrf
  <h1>RISA</h1>
  <p class="sub">Inicia sesión para administrar el catálogo de materias.</p>
  <label for="email">Correo institucional</label>
  <input id="email" name="email" type="email" value="{{ old('email') }}" autocomplete="username" required>
  <label for="password">Contraseña</label>
  <input id="password" name="password" type="password" autocomplete="current-password" required>
  @foreach ($errors->all() as $error)
    <p class="err" role="alert">{{ $error }}</p>
  @endforeach
  <button type="submit">Entrar</button>
</form>
</body>
</html>
