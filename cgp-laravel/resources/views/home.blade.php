@extends('layouts.app')

@section('title', 'Contraloría del Municipio Páez – Estado Portuguesa')

@section('content')
  <style>
    .mv-texto, .mv-texto p, .mv-texto * { font-size: 0.9rem; line-height: 1.75; opacity: 0.9; text-align: justify !important; }
    .mv-texto p { margin-bottom: 0.6rem; text-align: justify !important; }
    .mv-intro { text-align: justify !important; }
    .noticia-texto { font-size: 0.82rem; color: #666; line-height: 1.6; }
    .noticia-texto p { margin-bottom: 0.6rem; }
    .noticia-texto ul, .noticia-texto ol { padding-left: 1.2rem; }
    .hero-content p { text-align: justify; }

    /* ── Carrusel Dinámico del Hero ── */
    .hero-photo.hero-carousel {
      position: relative;
      overflow: hidden;
      aspect-ratio: 16 / 10 !important;
      width: 100%;
      background: #0d1b3e;
    }
    .carousel-track {
      display: flex;
      width: 100%;
      height: 100%;
      transition: transform 0.7s cubic-bezier(0.22, 1, 0.36, 1);
      will-change: transform;
    }
    .carousel-slide {
      min-width: 100%;
      width: 100%;
      height: 100%;
      flex-shrink: 0;
      position: relative;
      overflow: hidden;
    }
    /* Adapta las fotos originales sin cortar rostros ni hacer zooms excesivos */
    .hero-photo.hero-carousel .carousel-slide img {
      display: block !important;
      width: 100% !important;
      height: 100% !important;
      object-fit: cover !important;
      object-position: center 25% !important;
      transform: none !important;
      transition: transform 3.5s ease-out;
    }
    .carousel-slide.active img {
      transform: scale(1.05) !important;
    }
    /* Barra de progreso de tiempo */
    .carousel-progress {
      position: absolute;
      top: 0;
      left: 0;
      width: 100%;
      height: 3px;
      background: rgba(255, 255, 255, 0.25);
      z-index: 8;
    }
    .carousel-progress-bar {
      height: 100%;
      width: 0%;
      background: #2563eb;
      box-shadow: 0 0 8px #3b82f6;
    }
    /* Puntos de navegación */
    .carousel-dots {
      position: absolute;
      bottom: 12px;
      left: 50%;
      transform: translateX(-50%);
      display: flex;
      gap: 7px;
      z-index: 7;
      background: rgba(13, 27, 62, 0.45);
      backdrop-filter: blur(4px);
      padding: 5px 12px;
      border-radius: 20px;
    }
    .carousel-dots .dot {
      width: 8px;
      height: 8px;
      border-radius: 50%;
      background: rgba(255, 255, 255, 0.5);
      border: none;
      padding: 0;
      cursor: pointer;
      transition: all 0.3s ease;
    }
    .carousel-dots .dot.active {
      background: #ffffff;
      width: 22px;
      border-radius: 10px;
    }
    /* Flechas de navegación */
    .carousel-arrow {
      position: absolute;
      top: 50%;
      transform: translateY(-50%);
      background: rgba(13, 27, 62, 0.65);
      color: #fff;
      border: 1px solid rgba(255, 255, 255, 0.2);
      width: 36px;
      height: 36px;
      border-radius: 50%;
      font-size: 22px;
      line-height: 1;
      display: flex;
      align-items: center;
      justify-content: center;
      cursor: pointer;
      opacity: 0.8;
      transition: all 0.25s ease;
      z-index: 6;
    }
    .hero-carousel:hover .carousel-arrow { opacity: 1; }
    .carousel-arrow:hover { background: #1565c0; border-color: transparent; transform: translateY(-50%) scale(1.08); }
    .carousel-arrow.prev { left: 10px; }
    .carousel-arrow.next { right: 10px; }
  </style>
  <!-- seccion de inicio como portada, el declarar puse el original de la contraloria general, hay que preguntar si es ese-->
  <section id="hero">
    <div class="hero-inner">
      <!-- el primer texto q se vera como portada (hero) -->
      <div class="hero-content">
        <h1>Contraloría del <span>Municipio Páez</span></h1>
        <h2>Órgano de Control Fiscal Municipal</h2>
        <p style="text-align: justify;">Vigilamos el patrimonio público de Acarigua y sus parroquias con transparencia y cercanía al ciudadano.</p>
        <div class="hero-actions">
          <a href="https://djp.cgr.gob.ve/authentication/sign-in" class="btn-declare" target="_blank" rel="noopener">Declare Aquí</a>
        </div>
      </div>

      <figure class="hero-figure">
        <div class="hero-photo hero-carousel" id="heroCarousel">
          <div class="carousel-progress"><div class="carousel-progress-bar" id="carouselProgressBar"></div></div>
          <div class="carousel-track">
            <div class="carousel-slide active">
              <img src="{{ asset('assets/img/portadasinrayas.jpg') }}" alt="Equipo de la Contraloría del Municipio Páez">
            </div>
            <div class="carousel-slide">
              <img src="{{ asset('assets/img/reunion.jpeg') }}" alt="Reunión institucional">
            </div>
            <div class="carousel-slide">
              <img src="{{ asset('assets/img/mesadetrabajo1.jpeg') }}" alt="Mesa de trabajo">
            </div>
            <div class="carousel-slide">
              <img src="{{ asset('assets/img/juramentacion.jpeg') }}" alt="Actividades institucionales">
            </div>
          </div>
          <!-- Indicadores de puntos -->
          <div class="carousel-dots" id="heroCarouselDots"></div>
          <!-- Flechas de navegación -->
          <button type="button" class="carousel-arrow prev" id="heroPrevBtn" aria-label="Foto anterior">‹</button>
          <button type="button" class="carousel-arrow next" id="heroNextBtn" aria-label="Siguiente foto">›</button>
        </div>
      </figure>
    </div>

    <!-- para las flechas animadas de scroll -->
    <div class="scroll-arrows">
      <span></span>
      <span></span>
      <span></span>
    </div>
  </section>

  <!-- servicio en linea -->
  <section id="servicios">
    <div class="container">
      <h2 class="section-title-center">Servicios en Línea</h2>
      <div class="title-underline"></div>

      <div class="row g-0 border rounded overflow-hidden">
        <div class="col-md-3 col-sm-6">
          <a href="#djp" class="servicio-card active h-100">
            <span class="serv-icon">
              <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2" />
                <circle cx="9" cy="7" r="4" />
                <path d="M23 21v-2a4 4 0 0 0-3-3.87" />
                <path d="M16 3.13a4 4 0 0 1 0 7.75" />
              </svg>
            </span>
            <span class="serv-text">Declaración Jurada de Patrimonio</span>
          </a>
        </div>
        <div class="col-md-3 col-sm-6">
          <a href="https://www.cgr.gob.ve/pc/registration" class="servicio-card h-100" target="_blank" rel="noopener">
            <span class="serv-icon">
              <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                <line x1="3" y1="22" x2="21" y2="22" />
                <line x1="6" y1="18" x2="6" y2="11" />
                <line x1="10" y1="18" x2="10" y2="11" />
                <line x1="14" y1="18" x2="14" y2="11" />
                <line x1="18" y1="18" x2="18" y2="11" />
                <polygon points="12 2 20 7 4 7" />
              </svg>
            </span>
            <span class="serv-text">Registro de Órganos y Entes del Sector Público</span>
          </a>
        </div>
        <div class="col-md-3 col-sm-6">
          <a href="https://www.cgr.gob.ve/contenido/073" class="servicio-card h-100" target="_blank" rel="noopener">
            <span class="serv-icon">
              <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                <circle cx="11" cy="11" r="8" />
                <line x1="21" y1="21" x2="16.65" y2="16.65" />
              </svg>
            </span>
            <span class="serv-text">Registro de Auditores, Consultores y Profesionales</span>
          </a>
        </div>
        <div class="col-md-3 col-sm-6">
          <a href="#participacion" class="servicio-card h-100">
            <span class="serv-icon">
              <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z" />
              </svg>
            </span>
            <span class="serv-text">Dirección de Atención al Ciudadano</span>
          </a>
        </div>
      </div>
    </div>
  </section>

  <!-- apartado de la declaracion jurada -->
  <section id="djp">
    <div class="container">
      <h2 class="page-title">Declaración Jurada de Patrimonio</h2>

      <div class="djp-aviso">
        <p>Antes de realizar su declaración <strong>disponga de toda la información patrimonial (activos y pasivos)</strong>, incluyendo los bienes y obligaciones de su grupo familiar: cónyuge, concubino o persona con quien mantiene una unión estable de hecho y los hijos menores de edad sometidos a su patria potestad.</p>
        <br>
        <p><strong>No olvide</strong> que para acceder al sistema <strong>su cuenta de usuario es el correo electrónico</strong> con el cual usted se registró; asimismo, recuerde <strong>cambiar la clave</strong> periódicamente.</p>
      </div>

      <div class="djp-accesos">
        <a href="https://djp.cgr.gob.ve/USU_DECLA/login.php?error=0&s=1&td=AP" target="_blank" class="djp-btn-acceso">
          <span class="btn-icon">
            <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round">
              <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2" />
              <circle cx="12" cy="7" r="4" />
            </svg>
          </span>
          <span>Funcionario Público</span>
        </a>
        <a href="https://djp.cgr.gob.ve/USU_DECLA/login.php?error=0&s=1&td=CC" target="_blank" class="djp-btn-acceso">
          <span class="btn-icon">
            <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round">
              <path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z" />
              <polyline points="9 22 9 12 15 12 15 22" />
            </svg>
          </span>
          <span>Consejo Comunal</span>
        </a>
      </div>

      <div class="telefono-box">
        <div class="sub">Línea de atención al ciudadano · Asistente de Despacho </div>
        <div class="num">0424-2502266</div>
        <div class="sub" style="margin-top:4px;">Llama · Escribe</div>
      </div>
    </div>
  </section>

  <!-- apartado de preguntas -->
  <section id="faq">
    <div class="container">
      <h2 class="section-title-center">Preguntas Frecuentes</h2>
      <div class="title-underline"></div>

      <div class="faq-inner">
        <div class="faq-item">
          <button class="faq-q" onclick="toggleFaq(this)">
            ¿Quiénes están exceptuados de presentar la declaración jurada de patrimonio?
            <span class="faq-arrow">▼</span>
          </button>
          <div class="faq-a">
            <p>No hay excepción alguna en la presentación de la declaración jurada de patrimonio; el trámite es de obligatorio cumplimiento para todas las personas señaladas en el ordenamiento jurídico vigente.</p>
          </div>
        </div>

        <div class="faq-item">
          <button class="faq-q" onclick="toggleFaq(this)">
            ¿En qué oportunidades debo presentar la declaración jurada de patrimonio?
            <span class="faq-arrow">▼</span>
          </button>
          <div class="faq-a">
            <p>Debe ser presentada dentro de los treinta (30) días siguientes a la toma de posesión de funciones públicas y dentro de los treinta (30) días posteriores al cese en el cargo, de conformidad con lo establecido en el artículo 28 de la Ley Contra la Corrupción. </p>
          </div>
        </div>

        <div class="faq-item">
          <button class="faq-q" onclick="toggleFaq(this)">
            ¿Quiénes están obligados a presentar declaración jurada de patrimonio?
            <span class="faq-arrow">▼</span>
          </button>
          <div class="faq-a">
            <p>Todo funcionario o empleado público, de conformidad con lo establecido en el artículo 3 de la Ley de Reforma del Decreto con Rango, Valor y Fuerza de Ley Contra la Corrupción. Igualmente los miembros de unidad de gestión financiera de los consejos comunales, integrantes de directivas de organizaciones sindicales y gremiales, y los obreros al servicio del Estado. </p>
          </div>
        </div>

        <div class="faq-item">
          <button class="faq-q" onclick="toggleFaq(this)">
            ¿Cuáles son las consecuencias jurídicas de no presentarla?
            <span class="faq-arrow">▼</span>
          </button>
          <div class="faq-a">
            El incumplimiento será sancionado administrativamente con multas de 100 a 1.000 unidades del tipo de cambio de mayor valor publicado por el BCV. El Contralor General podrá acordar suspensión sin goce de sueldo hasta por doce meses e inhabilitación para el ejercicio de la función pública.
          </div>
        </div>

        <div class="faq-item">
          <button class="faq-q" onclick="toggleFaq(this)">
            ¿Cómo debo expresar los montos en mi declaración?
            <span class="faq-arrow">▼</span>
          </button>
          <div class="faq-a">
            <p> A partir del 1 de octubre de 2021, todos los montos deben expresarse en bolívares (Bs), según el Decreto de Nueva Expresión Monetaria publicado en la Gaceta Oficial N° 42.185 del 06 de agosto de 2021.</p>
          </div>
        </div>

        <div class="faq-item">
          <button class="faq-q" onclick="toggleFaq(this)">
            ¿Puede un apoderado presentar la declaración en mi nombre?
            <span class="faq-arrow">▼</span>
          </button>
          <div class="faq-a">
            <p> No. La presentación de la declaración jurada de patrimonio es una obligación personalísima del funcionario, dado que la responsabilidad de lo declarado no es transferible. </p>
          </div>
        </div>

        <div class="faq-item">
          <button class="faq-q" onclick="toggleFaq(this)">
            ¿Qué constancia se emite al cumplir con la obligación?
            <span class="faq-arrow">▼</span>
          </button>
          <div class="faq-a">
            <p> El sistema permitirá imprimir el certificado electrónico, el cual sirve como constancia de haber cumplido con la obligación. Este documento no requiere de firma o sello. </p>
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- Historia -->
  <section id="Historia">
    <div class="container">
      <h2 class="section-title-center">Historia</h2>
      <div class="title-underline"></div>
      <div class="historia-texto">
        <p>La Contraloría del Municipio Páez consolidó su estructura formal hace aproximadamente <strong>42 años</strong>, siguiendo el impulso de la reforma del régimen municipal de los<strong> años 80 </strong>, con el fin de garantizar la transparencia en una Acarigua en pleno crecimiento comercial y agrícola. Durante este periodo, la institución dejó de ser una simple dependencia administrativa para convertirse en un órgano con autonomía funcional y organizativa, cuyo hito histórico principal fue la transición de la vigilancia básica a la implementación de sistemas modernos de fiscalización externa, alineados hoy con las directrices de la Contraloría General de la República para supervisar el patrimonio público local y fomentar la participación ciudadana.</p>
      </div>
    </div>
  </section>

  <!-- apartado de mision y vision -->
  <section id="mision">
    <div class="container">
      <h2 class="page-title">Misión y Visión Institucional</h2>

      <p class="mv-intro">
        La Contraloría del Municipio Páez es el órgano encargado del control, vigilancia y fiscalización de los ingresos, gastos e integridad del patrimonio municipal. Ejerce sus funciones de manera autónoma, en el marco del Sistema Nacional de Control Fiscal, con apego a la Constitución de la República Bolivariana de Venezuela y demás leyes que rigen la materia.
      </p>

      <div class="row g-4">
        <div class="col-md-6">
          <div class="mv-card-blue h-100">
            <h3>Misión</h3>
            <div class="mv-texto" style="text-align: justify;">@if ($mision){!! $mision->html !!}@else"Nuestro compromiso es apalancar el desarrollo continuado y sostenible del municipio Páez del estado Portuguesa potenciando una función de gobierno óptima, transparente y de calidad en el uso de los recursos financieros y bienes del municipio, fomentando sinergias positivas creadoras de valor que propendan a la total observancia del marco jurídico positivo Venezolano."@endif</div>
            <h3 style="font-size:1.3rem; margin-top:24px;">Visión</h3>
            <div class="mv-texto" style="text-align: justify;">@if ($vision){!! $vision->html !!}@else"A cinco años ejerceremos la rectoría total de la función contralora en el municipio Páez del estado Portuguesa, siendo referentes a nivel estadal y nacional. Visualizamos en cinco años una estructura funcional y normativa eficaz, eficiente, efectiva, pertinente y oportuna, que interactúa exitosamente a lo interno de la Alcaldía y Cámara del municipio Páez del estado Portuguesa y a lo externo con los diferentes estamentos del poder comunal en la búsqueda de la prestación de un servicio contralor sostenible y de calidad."@endif</div>
          </div>
        </div>
        <div class="caja-ubicacion col-md-6">
          <h2>Ubicación</h2>
          <p><strong>Dirección:</strong> Av. 33 con calle 35. Edif. Doña Cecilia. Frente a la Plaza Andrés Eloy Blanco. Acarigua, Municipio Páez, Estado Portuguesa.</p>
          <p><strong>Horario:</strong> Lunes a Viernes: 8:00 a.m. a 3:00 p.m.</p>
          <div class="contenedor-mapa">
            <iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3934.341484157!2d-69.2014!3d9.5597!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x0%3A0x0!2zOcKwMzMnMzQuOSJOIDY5wrAxMicwNS4wIlc!5e0!3m2!1ses!2sve!4v1650460000000!5m2!1ses!2sve" allowfullscreen="" loading="lazy"></iframe>
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- apartado de participacion ciudadana -->
  <section id="participacion">
    <div class="container">
      <h2 class="section-title-center">Participación Ciudadana</h2>
      <div class="title-underline"></div>

      <p class="text-justify" style="color:#555; font-size:0.93rem; max-width:700px; margin:0 auto 10px;">
        La Dirección de Atención al Ciudadano promueve la participación a través de programas pedagógicos y actitudes formativas dirigidas a la ciudadanía, atendiendo y tramitando denuncias, quejas y peticiones.
      </p>

      <div class="row g-4 mt-2">
        <div class="col-md-4">
          <div class="pc-card h-100">
            <span class="icon">
              <svg width="44" height="44" viewBox="0 0 24 24" fill="none" stroke="#1565c0" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round">
                <path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z" />
                <line x1="12" y1="9" x2="12" y2="13" />
                <line x1="12" y1="17" x2="12.01" y2="17" />
              </svg>
            </span>
            <h4>Denuncias Ciudadanas</h4>
            <p>Reporte irregularidades en el manejo de fondos públicos municipales. Su denuncia es confidencial y contribuye a la transparencia de la gestión pública.</p>
            <a href="{{ url('/denuncias') }}" class="ver-mas">Ver más</a>
          </div>
        </div>
        <div class="col-md-4">
          <div class="pc-card h-100">
            <span class="icon">
              <svg width="44" height="44" viewBox="0 0 24 24" fill="none" stroke="#1565c0" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round">
                <path d="M2 3h6a4 4 0 0 1 4 4v14a3 3 0 0 0-3-3H2z" />
                <path d="M22 3h-6a4 4 0 0 0-4 4v14a3 3 0 0 1 3-3h7z" />
              </svg>
            </span>
            <h4>Contraloría va a la Escuela</h4>
            <p>Programa educativo que promueve los valores de transparencia, legalidad y ética pública en instituciones educativas del Municipio Páez.</p>
            <a href="{{ url('/contraloria-escolar') }}" class="ver-mas">Ver más</a>
          </div>
        </div>
        <div class="col-md-4">
          <div class="pc-card h-100">
            <span class="icon">
              <svg width="44" height="44" viewBox="0 0 24 24" fill="none" stroke="#1565c0" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round">
                <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2" />
                <circle cx="9" cy="7" r="4" />
                <path d="M23 21v-2a4 4 0 0 0-3-3.87" />
                <path d="M16 3.13a4 4 0 0 1 0 7.75" />
              </svg>
            </span>
            <h4>Gestión Comunitaria</h4>
            <p>Acompañamiento a las unidades de gestión financiera de los consejos comunales en el correcto cumplimiento de sus obligaciones de control fiscal.</p>
            <a href="{{ url('/gestion-comunitaria') }}" class="ver-mas">Ver más</a>
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- Apartado de noticias -->
  <section id="noticias">
    <div class="container">
      <h2 class="section-title-center">Noticias</h2>
      <div class="title-underline"></div>

      @if ($noticias->isNotEmpty())
      <div class="row g-4 mt-2">
        @foreach ($noticias as $n)
        <div class="col-md-4">
          <div class="noticia-card h-100">
            <div class="noticia-top"></div>
            @if ($n->image_path)<img src="{{ route('media.cms', $n->image_path) }}" alt="" loading="lazy" style="width:100%;aspect-ratio:16/9;object-fit:cover;display:block">@endif
            <div class="noticia-body">
              <div class="noticia-fecha">{{ \Illuminate\Support\Carbon::parse($n->published_at)->locale('es')->translatedFormat('j \\d\\e F, Y') }}</div>
              <h5>{{ $n->title }}</h5>
              <div class="noticia-texto">{!! $n->html !!}</div>
            </div>
          </div>
        </div>
        @endforeach
      </div>
      @else
      <div class="row g-4 mt-2">
        <div class="col-md-4">
          <div class="noticia-card h-100">
            <div class="noticia-top"></div>
            <div class="noticia-body">
              <div class="noticia-fecha">18 de Junio, 2025</div>
              <h5>Juramentación del nuevo Contralor del Municipio Páez</h5>
              <p>En un acto protocolar celebrado en la sede de la Contraloría General de la República, el Abg. Leonel Lucena fue juramentado oficialmente como el nuevo Contralor del municipio Páez, estado Portuguesa. La ceremonia, fue presidida por el Contralor General de la República, Dr. Gustavo Vizcaíno Gil. Durante el evento, las autoridades nacionales destacaron que esta designación forma parte del plan de fortalecimiento del Sistema Nacional de Control Fiscal en todo el territorio venezolano.</p>
            </div>
          </div>
        </div>
        <div class="col-md-4">
          <div class="noticia-card h-100">
            <div class="noticia-top"></div>
            <div class="noticia-body">
              <div class="noticia-fecha">13 de Abril, 2026</div>
              <h5>Entrega de Informe de Gestión</h5>
              <p>El pasado 13 de abril de 2026, la Contraloría del estado Portuguesa, en conjunto con las contralorías municipales (incluyendo la de Páez), consignó formalmente su Informe de Gestión correspondiente al año 2025 ante la Contraloría General de la República. Este paso es fundamental para certificar la legalidad de los ingresos y gastos ejecutados en la jurisdicción durante el año anterior.</p>
            </div>
          </div>
        </div>
        <div class="col-md-4">
          <div class="noticia-card h-100">
            <div class="noticia-top" style="background:#e63946;"></div>
            <div class="noticia-body">
              <div class="noticia-fecha" style="color:#e63946;">Aviso Importante</div>
              <h5>Obligación de presentar Declaración Jurada de Patrimonio</h5>
              <p>Recuerde que el incumplimiento de esta obligación acarrea sanciones administrativas y posible inhabilitación para el ejercicio de la función pública.</p>
            </div>
          </div>
        </div>
      </div>
      @endif
    </div>
  </section>

  <!-- Apartado de las redes sociales, especificamente ig -->
  <section id="redes">
    <div class="redes-inner">
      <div class="redes-header">
        <div class="redes-title-block">
          <center>
            <h2>Redes Sociales e Institucional</h2>
            <p>Actividades, eventos y comunicados oficiales de la Contraloría del Municipio Páez</p>
          </center>
        </div>

        <div class="ig-profile-badge">
          <div class="ig-avatar">
            <img src="{{ asset('assets/img/logo.jpeg') }}" alt="Logo" style="width:56px;height:56px; border-radius: 25px;">
          </div>
          <div class="ig-info">
            <a href="https://www.instagram.com/contraloria_mpaez/" target="_blank" class="ig-handle">@contraloria_mpaez</a>
            <a href="https://www.instagram.com/contraloria_mpaez/" target="_blank" class="ig-follow-btn">
              <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                <rect x="2" y="2" width="20" height="20" rx="5" ry="5" />
                <circle cx="12" cy="12" r="4" />
                <circle cx="17.5" cy="6.5" r="0.5" fill="currentColor" />
              </svg>
              Seguir en Instagram
            </a>
          </div>
        </div>
      </div>

      <!-- Embed oficial del perfil: muestra solas las últimas publicaciones, sin token ni acceso a la cuenta. -->
      <iframe class="ig-embed" src="https://www.instagram.com/contraloria_mpaez/embed/" title="Últimas publicaciones de @contraloria_mpaez en Instagram" loading="lazy" scrolling="no"></iframe>

      <div class="redes-cta">
        <a href="https://www.instagram.com/contraloria_mpaez/" target="_blank">
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <rect x="2" y="2" width="20" height="20" rx="5" ry="5" />
            <circle cx="12" cy="12" r="4" />
            <circle cx="17.5" cy="6.5" r="0.5" fill="currentColor" />
          </svg>
          Ver todas las publicaciones en Instagram
        </a>
      </div>
    </div>
  </section>

  <!-- apartado de base normativa -->
  <section id="legal">
    <div class="container">
      <h2 class="section-title-center">Base Normativa</h2>
      <div class="title-underline"></div>
      <p class="text-center text-muted mt-2">Marco legal, reglamentos e instrumentos jurídicos del control fiscal.</p>

      @php
        $pdf = fn ($f) => asset('assets/pdf/'.$f);
        $leyes = [
          ['Constitución de la República Bolivariana de Venezuela', 'Ext. 5.908 (18/2/2009)', 'https://www.asambleanacional.gob.ve/storage/documentos/botones/constitucion-nacional-20191205135853.PDF'],
          ['Ley Orgánica de la Contraloría General de la República y del Sistema Nacional de Control Fiscal', 'Ext. 6.013 (23/12/2010)', 'https://www.oas.org/juridico/spanish/cr_res9.htm'],
          ['Ley Contra la Corrupción', 'Ext. 6.699 (2/5/2022)', 'https://www.saren.gob.ve/wp-content/uploads/2025/01/15.-Ley-Contra-la-Corrupcion.pdf'],
          ['Ley Orgánica del Poder Ciudadano', 'G.O. 37.310 (25/10/2011)', $pdf('ley-organica-del-poder-ciudadano.pdf')],
          ['Ley Orgánica de Procedimientos Administrativos', 'Ext. 2.818 (1/7/1981)', $pdf('ley_org_procedimientos_administrativos.pdf')],
          ['Decreto Ley Orgánica de la Administración Pública', 'Ext. 6.147 (17/11/2010)', $pdf('Ley-Organica-de-Administracion-Publica.pdf')],
          ['Ley Orgánica de los Consejos Comunales', 'Ext. 6.759 (15/7/2022)', 'https://mppp.gob.ve/wp-content/uploads/2023/07/Ley-organica-de-los-Consejos-Comunales.pdf'],
          ['Ley Orgánica del Poder Popular', 'Ext. 6.812 (6/6/2024)', $pdf('ley_organica_del_poder_popular.pdf')],
          ['Ley Orgánica de las Comunas', 'Ext. 6.872 (30/12/2024)', $pdf('ley_organica_de_las_comunas.pdf')],
          ['Ley Orgánica de Planificación Pública y Popular', 'Ext. 6.148 (18/11/2014)', $pdf('Ley-Orgánica-de-Planificación-Pública-y-Popular.pdf')],
          ['Ley Orgánica de la Contraloría Social', 'Ext. 6.748 (9/6/2023)', $pdf('ley_organica_de_contraloria_social.pdf')],
          ['Normas para Fomentar la Participación Ciudadana', 'G.O. 38.750 (20/8/2007)', $pdf('GO_38750_NORMAS_PARA_FOMENTAR_LA_PARTICIPACION_CIUDADANA.pdf')],
          ['Ley Orgánica del Poder Público Municipal (LOPPM)', 'Poder Público Municipal', 'https://www.cmldc.gob.ve/wp-content/uploads/2024/04/Ley-Organica-del-Poder-Publico-Municipal.pdf'],
          ['Ley de Infogobierno', 'G.O. N° 40.274', $pdf('LEY-INFOGOBIERNO.pdf')],
          ['Ley para el Respeto de los Derechos Humanos en el Ejercicio de la Función Pública', 'G.O. N° 6.658', $pdf('ley-para-el-respeto-de-los-derechos-humanos-en-el-ejercicio-de-la-funcion-publica.pdf')],
          ['Ordenanza Municipal de Control Fiscal del Municipio Páez, Estado Portuguesa', 'Municipio Páez', $pdf('ordenanza_municipal_de_control_fiscal_del_municipio_páez_estado_portuguesa.pdf')],
        ];
      @endphp

      <div class="mx-auto mt-4 mb-5 position-relative" style="max-width: 550px;">
        <span class="position-absolute top-50 translate-middle-y text-muted" style="left: 20px;">
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8" /><line x1="21" y1="21" x2="16.65" y2="16.65" /></svg>
        </span>
        <input type="search" id="legalSearch" class="form-control rounded-pill shadow-sm ps-5 py-3 border-0 bg-white" placeholder="Buscar ley, reglamento o gaceta..." aria-label="Buscar en la base normativa">
      </div>

      <div class="row g-4" id="legalGrid">
        @foreach ($leyes as [$titulo, $gaceta, $url])
          <div class="col-lg-4 col-md-6 legal-item">
            <div class="card h-100 border-0 shadow-sm rounded-4 p-3 bg-white">
              <div class="card-body d-flex flex-column justify-content-between">
                <h5 class="card-title fw-bold text-dark fs-6 mb-3">{{ $titulo }}</h5>
                <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 pt-3 border-top border-light">
                  <span class="badge bg-primary bg-opacity-10 text-primary rounded-pill px-3 py-2 fw-semibold" style="font-size: 0.75rem;">{{ $gaceta }}</span>
                  <a href="{{ $url }}" target="_blank" rel="noopener" class="btn btn-outline-primary btn-sm rounded-pill px-3 fw-semibold text-nowrap flex-shrink-0">
                    {{ str_ends_with(strtolower($url), '.pdf') ? 'Ver PDF' : 'Ver página' }}
                  </a>
                </div>
              </div>
            </div>
          </div>
        @endforeach
      </div>

      <nav class="mt-5" aria-label="Paginación de la base normativa">
        <ul class="pagination justify-content-center" id="paginationControls"></ul>
      </nav>

      <p id="noResults" class="text-center text-muted fs-5 py-5" style="display: none;">No se encontraron normativas que coincidan con tu búsqueda.</p>
    </div>
  </section>

  <!-- boton para volver arriba o hero -->
  <a href="#" class="back-top" title="Volver arriba">▲</a>
@endsection

@push('scripts')
  <script>
    // abrir y cerrar preguntas frecuentes (esta parte es netamente ia porque no entendi hacerlo y bueno, es logica)
    function toggleFaq(btn) {
      var answer = btn.nextElementSibling;
      var isOpen = answer.classList.contains('show');
      document.querySelectorAll('.faq-a').forEach(function (a) { a.classList.remove('show'); });
      document.querySelectorAll('.faq-q').forEach(function (b) { b.classList.remove('open'); });
      if (!isOpen) {
        answer.classList.add('show');
        btn.classList.add('open');
      }
    }

    // Scroll suave para los enlaces internos (igual aca)
    document.querySelectorAll('a[href^="#"]').forEach(function (a) {
      a.addEventListener('click', function (e) {
        var target = a.getAttribute('href');
        if (target && target !== '#' && document.querySelector(target)) {
          e.preventDefault();
          document.querySelector(target).scrollIntoView({ behavior: 'smooth' });
        }
      });
    });
  </script>
  <!-- Animaciones de scroll y entrada -->
  <script src="{{ asset('assets/js/animations.js') }}"></script>
  <script>
    // Buscador y paginación de la base normativa (3 por página)
    (function () {
      const input = document.getElementById('legalSearch');
      const items = Array.from(document.querySelectorAll('.legal-item'));
      const controls = document.getElementById('paginationControls');
      const noResults = document.getElementById('noResults');
      const porPagina = 3;
      const norm = t => t.normalize('NFD').replace(/[\u0300-\u036f]/g, '').toLowerCase();
      let filtrados = items;
      let pagina = 1;

      function render() {
        const total = Math.ceil(filtrados.length / porPagina);
        pagina = Math.min(Math.max(pagina, 1), Math.max(total, 1));
        items.forEach(i => i.style.display = 'none');
        filtrados.slice((pagina - 1) * porPagina, pagina * porPagina).forEach(i => i.style.display = '');
        noResults.style.display = filtrados.length ? 'none' : 'block';
        controls.innerHTML = total <= 1 ? '' : [
          `<li class="page-item ${pagina === 1 ? 'disabled' : ''}"><a class="page-link rounded-pill px-3 mx-1" href="#" data-p="${pagina - 1}">Anterior</a></li>`,
          ...Array.from({ length: total }, (_, k) => `<li class="page-item ${pagina === k + 1 ? 'active' : ''}"><a class="page-link rounded-circle mx-1 d-flex align-items-center justify-content-center" style="width:38px;height:38px" href="#" data-p="${k + 1}">${k + 1}</a></li>`),
          `<li class="page-item ${pagina === total ? 'disabled' : ''}"><a class="page-link rounded-pill px-3 mx-1" href="#" data-p="${pagina + 1}">Siguiente</a></li>`,
        ].join('');
      }

      controls.addEventListener('click', e => {
        const a = e.target.closest('a[data-p]');
        if (!a) return;
        e.preventDefault();
        pagina = Number(a.dataset.p);
        render();
        document.getElementById('legal').scrollIntoView({ behavior: 'smooth' });
      });

      input.addEventListener('input', () => {
        const q = norm(input.value.trim());
        filtrados = items.filter(i => norm(i.textContent).includes(q));
        pagina = 1;
        render();
      });

      render();
    })();

    // Carrusel dinámico y automático del Hero
    (function () {
      const track = document.querySelector('.carousel-track');
      if (!track) return;

      const slides = Array.from(track.children);
      const dotsContainer = document.getElementById('heroCarouselDots');
      const prevBtn = document.getElementById('heroPrevBtn');
      const nextBtn = document.getElementById('heroNextBtn');
      const progressBar = document.getElementById('carouselProgressBar');
      const INTERVAL = 3200; // 3.2 segundos por foto para movimiento continuo
      let currentIndex = 0;
      let timer = null;

      // Generar puntos indicadores
      slides.forEach((_, i) => {
        const dot = document.createElement('button');
        dot.type = 'button';
        dot.className = 'dot' + (i === 0 ? ' active' : '');
        dot.setAttribute('aria-label', `Foto ${i + 1}`);
        dot.addEventListener('click', () => {
          goTo(i);
        });
        dotsContainer.appendChild(dot);
      });

      const dots = Array.from(dotsContainer.children);

      function updateProgressBar() {
        if (!progressBar) return;
        progressBar.style.transition = 'none';
        progressBar.style.width = '0%';
        requestAnimationFrame(() => {
          requestAnimationFrame(() => {
            progressBar.style.transition = `width ${INTERVAL}ms linear`;
            progressBar.style.width = '100%';
          });
        });
      }

      function goTo(index) {
        currentIndex = (index + slides.length) % slides.length;
        track.style.transform = `translateX(-${currentIndex * 100}%)`;
        dots.forEach((d, i) => d.classList.toggle('active', i === currentIndex));
        slides.forEach((s, i) => s.classList.toggle('active', i === currentIndex));
        resetTimer();
      }

      function startTimer() {
        updateProgressBar();
        timer = setInterval(() => {
          currentIndex = (currentIndex + 1) % slides.length;
          track.style.transform = `translateX(-${currentIndex * 100}%)`;
          dots.forEach((d, i) => d.classList.toggle('active', i === currentIndex));
          slides.forEach((s, i) => s.classList.toggle('active', i === currentIndex));
          updateProgressBar();
        }, INTERVAL);
      }

      function resetTimer() {
        clearInterval(timer);
        startTimer();
      }

      if (prevBtn) prevBtn.addEventListener('click', () => goTo(currentIndex - 1));
      if (nextBtn) nextBtn.addEventListener('click', () => goTo(currentIndex + 1));

      startTimer();
    })();
  </script>
@endpush
