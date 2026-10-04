<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  @php
    $esOac = $mod === 'oac';
    $titulo = $esOac ? 'Oficina de Atención al Ciudadano' : 'Administración del Sistema';
    $texto = $esOac
      ? 'Acceso para el personal de la OAC: recepción, clasificación y seguimiento de denuncias, quejas y reclamos.'
      : 'Acceso para contralores y recursos humanos: control de accesos, usuarios, contenido del portal y monitoreo del sistema.';
  @endphp
  <title>{{ $titulo }} · Contraloría del Municipio Páez</title>
  <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.2/css/bootstrap.min.css" rel="stylesheet">
  <style>
    body { font-family: 'Segoe UI', Roboto, Arial, sans-serif; background-color: #0d1b3e; margin: 0; min-height: 100vh; }
    .login-wrapper { display: flex; min-height: 100vh; }
    .login-info { background: linear-gradient(135deg, rgba(13,27,62,0.93), rgba(13,27,62,0.93)), url('{{ asset('assets/img/portadahero.jpg') }}'); background-size: cover; background-position: center; width: 50%; color: white; display: flex; align-items: center; justify-content: center; padding: 40px; }
    .info-content { max-width: 450px; }
    .info-content h1 { font-weight: 900; font-size: 2.5rem; text-transform: uppercase; background: linear-gradient(to right, #fff, #b3e5fc); -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text; }
    .info-content p { opacity: .8; line-height: 1.7; text-align: justify; }
    .login-form-container { width: 50%; display: flex; align-items: center; justify-content: center; background-color: ghostwhite; padding: 16px; }
    @media (max-width: 768px) { .login-info { display: none; } .login-form-container { width: 100%; } }
    .form-box { background: #fff; padding: 40px; border-radius: 20px; box-shadow: 0 20px 50px rgba(0,0,0,0.1); width: 100%; max-width: 450px; }
    .form-label { font-weight: 600; color: #1a2340; font-size: 0.85rem; }
    .btn-login { background-color: #0d1b3e; color: white; font-weight: 700; padding: 12px; letter-spacing: .05em; border: none; }
    .btn-login:hover { background-color: #1a2340; color: white; }
  </style>
</head>
<body>
<div class="login-wrapper">
  <div class="login-info">
    <div class="info-content">
      <h1>{{ $titulo }}</h1>
      <p class="mt-4">{{ $texto }} Acceso restringido a personal autorizado de la Contraloría del Municipio Páez. Toda actividad es monitoreada para garantizar la transparencia y seguridad de los datos.</p>
    </div>
  </div>

  <div class="login-form-container">
    <div class="form-box">
      <div class="d-flex align-items-center justify-content-center mb-4 p-3 rounded" style="background-color: #0d1b3e;">
        <img src="{{ asset('assets/img/logo.jpeg') }}" alt="Logo" style="width: 40px; height: 40px; margin-right: 12px; border-radius: 8px; object-fit: cover;">
        <h3 class="mb-0 text-white fw-bold">{{ $esOac ? 'OAC · INICIAR SESIÓN' : 'ADMINISTRACIÓN' }}</h3>
      </div>

      @if ($errors->any())
        <div class="alert alert-danger py-2" role="alert">{{ $errors->first() }}</div>
      @endif

      <form method="POST" action="{{ route($mod.'.login') }}" onsubmit="this.querySelector('button').disabled = true">
        @csrf
        <div class="mb-3">
          <label for="email" class="form-label">Correo electrónico</label>
          <input type="email" class="form-control" id="email" name="email" value="{{ old('email') }}" placeholder="usuario@cgr.gob.ve" required autofocus autocomplete="username">
        </div>
        <div class="mb-4">
          <label for="password" class="form-label">Contraseña</label>
          <input type="password" class="form-control" id="password" name="password" placeholder="••••••••" required autocomplete="current-password">
          <div class="form-text">¿Olvidó su clave? Solicite el restablecimiento al administrador del sistema.</div>
        </div>
        <button type="submit" class="btn btn-login w-100">ACCEDER</button>
      </form>
      <p class="text-center small text-muted mt-4 mb-0">
        @if ($esOac) ¿Es administrador del sistema? <a href="{{ route('admin.login') }}">Ir a Administración</a>
        @else ¿Trabaja en la OAC? <a href="{{ route('oac.login') }}">Ir a la Oficina de Atención al Ciudadano</a> @endif
      </p>
    </div>
  </div>
</div>
</body>
</html>
