<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>@yield('titulo') · OAC · Contraloría del Municipio Páez</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&family=Open+Sans:wght@400;600;700&display=swap" rel="stylesheet">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.2/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" rel="stylesheet">
  <link href="{{ asset('assets/css/oac.css') }}?v={{ filemtime(public_path('assets/css/oac.css')) }}" rel="stylesheet">
  <style>
    .page p, .card p, .card-body p, .welcome-banner p, .main-content p, .modal-body p, .kpi-desc {
      text-align: justify !important;
    }
  </style>
  @stack('estilos')
</head>
<body>
@php
  $u = auth()->user();
  $mod = 'oac';
  $iniciales = mb_strtoupper(mb_substr($u->first_name, 0, 1).mb_substr($u->last_name, 0, 1));
@endphp
<div class="sidebar-overlay" id="sidebarOverlay" onclick="toggleSidebar()"></div>

<div class="admin-wrapper">
  <aside class="sidebar" id="appSidebar">
    <div class="sidebar-brand">
      <img src="{{ asset('assets/img/logo.jpeg') }}" alt="">
      <span>OAC - Páez</span>
      <button class="btn btn-sm text-white d-lg-none border-0 ms-auto" onclick="toggleSidebar()" aria-label="Cerrar menú"><i class="fa-solid fa-xmark fs-5"></i></button>
    </div>

    <nav class="sidebar-content">
      <div class="nav-group">Gestión de Casos</div>
      <a href="{{ route('oac.inicio') }}" class="{{ request()->routeIs('oac.inicio') ? 'active-link' : '' }}"><i class="fa-solid fa-house-chimney"></i> Panel Principal</a>
      @if ($u->puede('CASES'))
        <a href="{{ route('oac.expedientes.index', ['estado' => 'RECEIVED']) }}" class="{{ request('estado') === 'RECEIVED' ? 'active-link' : '' }}"><i class="fa-solid fa-folder-open"></i> Revisión y Protocolización</a>
        <a href="{{ route('oac.expedientes.index') }}" class="{{ request()->routeIs('oac.expedientes.*') && request('estado') !== 'RECEIVED' ? 'active-link' : '' }}"><i class="fa-solid fa-timeline"></i> Seguimiento de Expedientes</a>
      @endif

      @if ($u->puede('REPORTS') || $u->puede('CATALOGS'))
        <div class="nav-group">Control y Estadísticas</div>
        @if ($u->puede('REPORTS'))
          <a href="{{ route('oac.reportes') }}" class="{{ request()->routeIs('oac.reportes') ? 'active-link' : '' }}"><i class="fa-solid fa-chart-pie"></i> Reportes Gerenciales</a>
        @endif
        @if ($u->puede('CATALOGS'))
          <a href="{{ route('oac.catalogos.index') }}" class="{{ request()->routeIs('oac.catalogos.*') ? 'active-link' : '' }}"><i class="fa-solid fa-layer-group"></i> Catálogos</a>
        @endif
      @endif

      @if ($u->accede('admin'))
        <div class="nav-group">Cambiar de módulo</div>
        <a href="{{ route('admin.inicio') }}"><i class="fa-solid fa-right-left"></i> Portal Web</a>
      @endif
    </nav>

    <form method="POST" action="{{ route('oac.logout') }}" class="sidebar-footer">
      @csrf
      <button type="submit" class="logout-btn"><i class="fa-solid fa-arrow-right-from-bracket"></i> Cerrar Sesión</button>
    </form>
  </aside>

  <main class="main-content">
    <header class="topbar">
      <div class="d-flex align-items-center gap-3 topbar-title">
        <button class="menu-toggle-btn" onclick="toggleSidebar()" aria-label="Abrir menú"><i class="fa-solid fa-bars"></i></button>
        <h5 class="text-truncate">@yield('titulo')</h5>
      </div>

      <div class="dropdown">
        <div class="topbar-user dropdown-toggle" role="button" data-bs-toggle="dropdown" aria-expanded="false">
          <div class="text-end me-2 d-none d-md-block">
            <strong>{{ $u->nombre }}</strong>
            <small>Oficina de Atención al Ciudadano</small>
          </div>
          <div class="avatar">{{ $iniciales }}</div>
        </div>
        <ul class="dropdown-menu dropdown-menu-end profile-dropdown-menu text-center">
          <li>
            <div class="profile-avatar-lg">{{ $iniciales }}</div>
            <h6 class="fw-bold text-dark mb-1">{{ $u->nombre }}</h6>
            @if ($u->cargo())<div class="profile-badge mb-2">{{ $u->cargo() }}</div>@endif
          </li>
          <li><hr class="dropdown-divider my-2"></li>
          <li>
            <form method="POST" action="{{ route('oac.logout') }}" class="m-0">
              @csrf
              <button type="submit" class="profile-menu-item"><i class="fa-solid fa-power-off me-3"></i>Cerrar Sesión</button>
            </form>
          </li>
        </ul>
      </div>
    </header>

    <div class="page">
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
    </div>
  </main>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.2/js/bootstrap.bundle.min.js"></script>
<script>
  function toggleSidebar() {
    document.getElementById('appSidebar').classList.toggle('show');
    document.getElementById('sidebarOverlay').classList.toggle('show');
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
