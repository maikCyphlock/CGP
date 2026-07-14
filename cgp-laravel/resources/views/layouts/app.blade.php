<!DOCTYPE html>
<html lang="es">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>@yield('title', 'Contraloría del Municipio Páez – Estado Portuguesa')</title>

  <!-- Google Fonts -->
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700;900&family=Open+Sans:wght@400;600;700&display=swap" rel="stylesheet">
  @stack('fonts')

  <!-- Bootstrap 5 -->
  <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.2/css/bootstrap.min.css" rel="stylesheet">
  
  <!-- Estilos base -->
  <link rel="stylesheet" href="{{ asset('assets/css/contraloria.css') }}">
  @stack('styles')
</head>

<body>
  <!-- Header / Cabecera -->
  <header id="header">
    <div class="header-container">
      <a href="{{ url('/') }}" class="logo-link">
        <div style="width:56px;height:56px;flex-shrink:0;display:flex;align-items:center;justify-content:center;">
          <img src="{{ asset('assets/img/logo.jpeg') }}" alt="Logo" style="width:56px;height:56px; border-radius: 25px;">
        </div>
        <div class="logo-texts">
          <div class="l1">Contraloría del <br>Municipio Páez</div>
        </div>
      </a>

      <!-- Navegación principal -->
      <nav id="navbar">
        @section('navigation')
        <a href="{{ url('/#') }}" class="active">Inicio</a>
        <div class="dropdown">
          <a href="#">Institución</a>
          <div class="dropdown-menu">
            <a href="{{ url('/#mision') }}">Misión y Visión</a>
            <a href="https://drive.google.com/file/d/1cobbu88SJoTykQXbhrDzVc9ymcYY8sPi/view?usp=drive_open" target="_blank">Organigrama</a>
            <a href="{{ url('/#Historia') }}">Historia</a>
            <a href="{{ url('/#legal') }}">Base Normativa</a>
          </div>
        </div>
        <div class="dropdown">
          <a href="#">Informes</a>
          <div class="dropdown-menu">
            <a href="#">De Gestión</a>
            <a href="#">De Actuación</a>
            <a href="#">Especiales</a>
          </div>
        </div>
        <div class="dropdown">
          <a href="#">Publicaciones</a>
          <div class="dropdown-menu">
            <a href="#">Dictámenes</a>
            <a href="#">Publicaciones Especiales</a>
            <a href="#">Concursos</a>
          </div>
        </div>
        <a href="{{ url('/#noticias') }}">Noticias</a>
        <a href="{{ url('/#redes') }}">Redes Sociales</a>
        <div class="dropdown">
          <a href="{{ url('/#participacion') }}">Participación Ciudadana</a>
          <div class="dropdown-menu">
            <a href="{{ url('/#participacion') }}">Información General</a>
            <a href="{{ url('/denuncias') }}">Denuncias</a>
            <a href="{{ url('/contraloria-escolar') }}">Contraloría Escolar</a>
          </div>
        </div>
        <a href="https://sistemas.cgr.gob.ve/webmail/" target="_blank" class="webmail-btn">Webmail</a>
        @show
      </nav>
    </div>
  </header>

  <!-- Contenido principal -->
  @yield('content')

  <!-- Footer / Pie de página -->
  <footer>
    <div class="footer-inner">
      <div class="row g-4 pb-4" style="border-bottom: 1px solid rgba(255,255,255,0.1); margin-bottom: 24px;">
        <div class="col-lg-4 col-md-6 footer-brand">
          <img src="{{ asset('assets/img/logo.jpeg') }}" alt="Logo" style="width:56px;height:56px; border-radius: 25px;">
          <p style="margin-top:12px;">Contraloría del Municipio Páez — Estado Portuguesa, República Bolivariana de Venezuela. Órgano de Control Fiscal Municipal.</p>
          <div class="rif">RIF: G-20002790-4 · Acarigua, Estado Portuguesa</div>
          <div class="social-row">
            <a href="https://www.instagram.com/contraloria_mpaez/" target="_blank" title="Instagram">
              <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <rect x="2" y="2" width="20" height="20" rx="5" ry="5" />
                <circle cx="12" cy="12" r="4" />
                <circle cx="17.5" cy="6.5" r="0.5" fill="currentColor" />
              </svg>
            </a>
            <a href="https://www.facebook.com/contraloriamunicipiopaez.estadoportuguesa?mibextid=wwXIfr" target="_blank" title="Facebook">
              <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M18 2h-3a5 5 0 0 0-5 5v3H7v4h3v8h4v-8h3l1-4h-4V7a1 1 0 0 1 1-1h3z" />
              </svg>
            </a>
            <a href="https://x.com/cmeppaez" target="_blank" title="Twitter / X">
              <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M4 4l16 16M4 20L20 4" />
                <path d="M20 4h-5l-11 16h5" />
              </svg>
            </a>
          </div>
        </div>

        <div class="col-lg-2 col-md-6 footer-col">
          <h6>Institución</h6>
          <ul>
            <li><a href="{{ url('/#mision') }}">Misión y Visión</a></li>
            <li><a href="https://drive.google.com/file/d/1cobbu88SJoTykQXbhrDzVc9ymcYY8sPi/view?usp=drive_open" target="_blank">Organigrama</a></li>
            <li><a href="{{ url('/#Historia') }}">Historia</a></li>
            <li><a href="#">Directorio</a></li>
            <li><a href="{{ url('/#legal') }}">Base Normativa</a></li>
          </ul>
        </div>

        <div class="col-lg-2 col-md-6 footer-col">
          <h6>Informes</h6>
          <ul>
            <li><a href="#">De Gestión</a></li>
            <li><a href="#">De Actuación</a></li>
            <li><a href="#">Especiales</a></li>
            <li><a href="#">Dictámenes</a></li>
            <li><a href="#">Sanciones</a></li>
          </ul>
        </div>

        <div class="col-lg-2 col-md-6 footer-col">
          <h6>Ciudadanía</h6>
          <ul>
            <li><a href="{{ url('/#participacion') }}">Participación Ciudadana</a></li>
            <li><a href="{{ url('/#redes') }}">Redes Sociales</a></li>
            <li><a href="{{ url('/denuncias') }}">Denuncias</a></li>
            <li><a href="{{ url('/contraloria-escolar') }}">Contraloría Escolar</a></li>
            <li><a href="{{ url('/#djp') }}">Declaración Jurada</a></li>
            <li><a href="https://www.cgr.gob.ve" target="_blank">CGR Venezuela ↗</a></li>
          </ul>
        </div>
      </div>

      <div class="footer-bottom">
        <span>© 2026 Contraloría del Municipio Páez · Dirección General de Tecnología de Información</span>
        <a href="https://www.cgr.gob.ve" target="_blank">cgr.gob.ve</a>
      </div>
    </div>
  </footer>

  @stack('scripts')
</body>

</html>
