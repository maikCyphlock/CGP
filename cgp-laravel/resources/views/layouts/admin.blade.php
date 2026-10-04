<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>@yield('titulo') · OAC - Contraloría del Municipio Páez</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.2/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
  <link href="{{ asset('assets/css/admin.css') }}" rel="stylesheet">
  @stack('estilos')
</head>
<body>
@php($u = auth()->user())
<div class="admin-overlay" id="adminOverlay" onclick="toggleSidebar()"></div>

<div class="admin-shell">
  <aside class="admin-sidebar" id="adminSidebar">
    <a href="{{ route('admin.inicio') }}" class="admin-brand">
      <img src="{{ asset('assets/img/logo.jpeg') }}" alt="">
      <div class="admin-brand-text"><strong>Contraloría</strong><span>Municipio Páez · OAC</span></div>
    </a>

    <nav>
      <a href="{{ route('admin.inicio') }}" class="{{ request()->routeIs('admin.inicio') ? 'active' : '' }}"><i class="fa-solid fa-house"></i> Inicio</a>
      @if ($u->puede('CASES'))
        <a href="{{ route('admin.expedientes.index', ['estado' => 'RECEIVED']) }}" class="{{ request('estado') === 'RECEIVED' ? 'active' : '' }}"><i class="fa-solid fa-inbox"></i> Por protocolizar</a>
        <a href="{{ route('admin.expedientes.index') }}" class="{{ request()->routeIs('admin.expedientes.*') && request('estado') !== 'RECEIVED' ? 'active' : '' }}"><i class="fa-solid fa-folder-open"></i> Expedientes</a>
      @endif
      @if ($u->puede('CMS') || $u->puede('CATALOGS') || $u->puede('USERS'))
        <div class="nav-group">Administración</div>
      @endif
      @if ($u->puede('CMS'))
        <a href="{{ route('admin.contenidos.index') }}" class="{{ request()->routeIs('admin.contenidos.*') ? 'active' : '' }}"><i class="fa-solid fa-file-lines"></i> Página web</a>
      @endif
      @if ($u->puede('CATALOGS'))
        <a href="{{ route('admin.catalogos.index') }}" class="{{ request()->routeIs('admin.catalogos.*') ? 'active' : '' }}"><i class="fa-solid fa-layer-group"></i> Catálogos</a>
      @endif
      @if ($u->puede('USERS'))
        <a href="{{ route('admin.usuarios.index') }}" class="{{ request()->routeIs('admin.usuarios.*') ? 'active' : '' }}"><i class="fa-solid fa-user-shield"></i> Usuarios y accesos</a>
      @endif
    </nav>

    <form method="POST" action="{{ route('admin.logout') }}" class="admin-logout">
      @csrf
      <div class="admin-logout-user">
        <div class="avatar">{{ mb_strtoupper(mb_substr($u->first_name, 0, 1).mb_substr($u->last_name, 0, 1)) }}</div>
        <div><strong>{{ $u->nombre }}</strong><small>{{ $u->cargo() }}</small></div>
      </div>
      <button type="submit" title="Cerrar sesión" aria-label="Cerrar sesión"><i class="fa-solid fa-arrow-right-from-bracket"></i></button>
    </form>
  </aside>

  <main class="admin-main">
    <div class="admin-topbar">
      <button class="admin-menu-btn" onclick="toggleSidebar()" aria-label="Abrir menú"><i class="fa-solid fa-bars"></i></button>
      <h1>@yield('titulo')</h1>
    </div>

    @if (session('ok'))
      <div class="alert alert-success"><i class="fa-solid fa-circle-check me-2"></i>{{ session('ok') }}</div>
    @endif
    @if (isset($errors) && $errors->any())
      <div class="alert alert-danger">
        <i class="fa-solid fa-triangle-exclamation me-2"></i>
        @foreach ($errors->all() as $error) <div class="d-inline">{{ $error }}</div> @endforeach
      </div>
    @endif
    @yield('contenido')
  </main>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.2/js/bootstrap.bundle.min.js"></script>
<script>
  function toggleSidebar() {
    document.getElementById('adminSidebar').classList.toggle('show');
    document.getElementById('adminOverlay').classList.toggle('show');
  }
  // Evita doble envío de formularios (doble clic).
  document.addEventListener('submit', e => {
    if (e.target.dataset.enviado) { e.preventDefault(); return; }
    e.target.dataset.enviado = '1';
    e.target.querySelectorAll('button[type=submit], button:not([type])').forEach(b => b.disabled = true);
  });
</script>
@stack('scripts')
</body>
</html>
