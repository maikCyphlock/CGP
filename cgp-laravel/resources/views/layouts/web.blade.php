<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>@yield('titulo') · Portal Web CMP · Contraloría del Municipio Páez</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&family=Open+Sans:wght@400;600;700&display=swap" rel="stylesheet">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.2/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" rel="stylesheet">
  <link href="{{ asset('assets/css/web.css') }}?v={{ filemtime(public_path('assets/css/web.css')) }}" rel="stylesheet">
  <style>
    .page p, .card p, .card-body p, .welcome-banner p, .main-content p, .activity-item p, .kpi-desc {
      text-align: justify !important;
    }
  </style>
  @stack('estilos')
</head>
<body>
@php
  $u = auth()->user();
  $mod = 'admin';
  $iniciales = mb_strtoupper(mb_substr($u->first_name, 0, 1).mb_substr($u->last_name, 0, 1));
@endphp
<div class="admin-wrapper">
  <div class="sidebar-backdrop" id="sidebarBackdrop" onclick="toggleSidebar()"></div>

  <aside class="sidebar" id="appSidebar">
    <div class="sidebar-header">
      <img src="{{ asset('assets/img/logonuevo.jpeg') }}" alt="">
      <span>Portal Web CMP</span>
      <button class="btn-close-sidebar ms-auto d-lg-none" onclick="toggleSidebar()" aria-label="Cerrar menú"><i class="fa-solid fa-xmark"></i></button>
    </div>

    <ul class="sidebar-menu">
      <li class="menu-category">Gestión Web</li>
      <li><a href="{{ route('admin.inicio') }}" class="{{ request()->routeIs('admin.inicio') ? 'active' : '' }}"><i class="fa-solid fa-chart-pie"></i> Inicio</a></li>
      @if ($u->puede('CMS'))
        <li><a href="{{ route('admin.contenidos.index') }}" class="{{ request()->routeIs('admin.contenidos.*') ? 'active' : '' }}"><i class="fa-solid fa-newspaper"></i> Noticias y contenido</a></li>
        <li><a href="{{ route('admin.informes.index') }}" class="{{ request()->routeIs('admin.informes.*') ? 'active' : '' }}"><i class="fa-solid fa-file-pdf"></i> Informes</a></li>
      @endif

      @if ($u->puede('STATS') || $u->puede('USERS'))
        <li class="menu-category mt-3">Sistema</li>
        @if ($u->puede('STATS'))
          <li><a href="{{ route('admin.monitoreo') }}" class="{{ request()->routeIs('admin.monitoreo*') ? 'active' : '' }}"><i class="fa-solid fa-chart-line"></i> Monitoreo</a></li>
        @endif
        @if ($u->puede('USERS'))
          <li><a href="{{ route('admin.usuarios.index') }}" class="{{ request()->routeIs('admin.usuarios.*') ? 'active' : '' }}"><i class="fa-solid fa-user-shield"></i> Usuarios y accesos</a></li>
          <li><a href="{{ route('admin.catalogos.show', 'cargos') }}" class="{{ request()->routeIs('admin.catalogos.*') ? 'active' : '' }}"><i class="fa-solid fa-id-badge"></i> Cargos</a></li>
        @endif
      @endif

      @if ($u->accede('oac'))
        <li class="menu-category mt-3">Cambiar de módulo</li>
        <li><a href="{{ route('oac.inicio') }}"><i class="fa-solid fa-right-left"></i> Atención al Ciudadano</a></li>
      @endif
    </ul>

    <form method="POST" action="{{ route('admin.logout') }}" class="sidebar-footer">
      @csrf
      <button type="submit" class="logout-btn"><i class="fa-solid fa-arrow-right-from-bracket"></i> Cerrar Sesión</button>
    </form>
  </aside>

  <main class="main-content">
    <header class="topbar">
      <div class="d-flex align-items-center gap-3">
        <button class="menu-toggle-btn" onclick="toggleSidebar()" aria-label="Abrir menú"><i class="fa-solid fa-bars"></i></button>
        <h4>@yield('titulo')</h4>
      </div>

      <div class="dropdown ms-auto">
        <div class="user-avatar shadow-sm" role="button" data-bs-toggle="dropdown" aria-expanded="false" aria-label="Menú de usuario">{{ $iniciales }}</div>
        <ul class="dropdown-menu dropdown-menu-end shadow-lg border-0 user-menu">
          <li class="text-center p-3 border-bottom">
            <div class="user-avatar avatar-lg">{{ $iniciales }}</div>
            <h6 class="mb-1">{{ $u->nombre }}</h6>
            @if ($u->cargo())<span class="badge bg-light">{{ $u->cargo() }}</span>@endif
          </li>
          <li>
            <form method="POST" action="{{ route('admin.logout') }}" class="m-0 mt-2">
              @csrf
              <button type="submit" class="dropdown-item py-2 px-3 text-danger fw-bold"><i class="fa-solid fa-power-off me-2"></i> Cerrar Sesión</button>
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
    document.getElementById('sidebarBackdrop').classList.toggle('show');
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
