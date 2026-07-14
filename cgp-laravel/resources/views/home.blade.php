@extends('layouts.app')

@section('title', 'Contraloría del Municipio Páez – Estado Portuguesa')

@section('content')
  <!-- seccion de inicio como portada, el declarar puse el original de la contraloria general, hay que preguntar si es ese-->
  <section id="hero" style="background-image: url('{{ asset('assets/img/portadita.jpeg') }}'); background-size: cover; background-position: center;">
    <!-- el primer texto q se vera como portada (hero) -->
    <div class="hero-content">
      <h1>Contraloría Del <br>Municipio Páez</h1>
      <h2>Órgano de Control Fiscal Municipal</h2>
      <p>42 años de servicio, vigilando el patrimonio público de Acarigua<br>y sus parroquias · Estado Portuguesa</p>
      <a href="https://www.cgr.gob.ve/contenido/047" class="btn-declare">Declare Aquí</a>
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
          <a href="#" class="servicio-card h-100">
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
          <a href="#" class="servicio-card h-100">
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
        <div class="sub">Línea de atención · Asistente de Despacho </div>
        <div class="num">0414-3506418</div>
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
            <p>"Nuestro compromiso es apalancar el desarrollo continuado y sostenible del municipio Páez del estado Portuguesa potenciando una función de gobierno óptima, transparente y de calidad en el uso de los recursos financieros y bienes del municipio, fomentando sinergias positivas creadoras de valor que propendan a la total observancia del marco jurídico positivo Venezolano."</p>
            <h3 style="font-size:1.3rem; margin-top:24px;">Visión</h3>
            <p>"A cinco años ejerceremos la rectoría total de la función contralora en el municipio Páez del estado Portuguesa, siendo referentes a nivel estadal y nacional. Visualizamos en cinco años una estructura funcional y normativa eficaz, eficiente, efectiva, pertinente y oportuna, que interactúa exitosamente a lo interno de la Alcaldía y Cámara del municipio Páez del estado Portuguesa y a lo externo con los diferentes estamentos del poder comunal en la búsqueda de la prestación de un servicio contralor sostenible y de calidad."</p>
          </div>
        </div>
        <div class="caja-ubicacion col-md-6">
          <h2>Ubicación</h2>
          <p><strong>Dirección:</strong> Avenida 33 con calle 35, Edificio Doña Cecilia, Casco Central. Acarigua, Portuguesa.</p>
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
            <a href="{{ url('/denuncias') }}" class="ver-mas">Ver más →</a>
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
            <a href="{{ url('/contraloria-escolar') }}" class="ver-mas">Ver más →</a>
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
            <a href="#" class="ver-mas">Ver más →</a>
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
            <div class="ig-stats">
              <div class="ig-stat"><span class="n">595</span><span class="l">Publicaciones</span></div>
              <div class="ig-stat"><span class="n">756</span><span class="l">Seguidores</span></div>
              <div class="ig-stat"><span class="n">366</span><span class="l">Siguiendo</span></div>
            </div>
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

      <!-- fotos publicas de manera manual dentro del codigo -->
      <div class="ig-posts-grid">
        <div class="ig-post">
          <img src="{{ asset('assets/img/mesadetrabajo1.jpeg') }}" alt="Mesa de trabajo con estudiantes de la UPTP" loading="lazy">
          <div class="ig-post-overlay">
            <p>Mesa de trabajo con estudiantes de la casa de estudio J.J. Montilla con fines de brindar apoyo para el desarrollo de su proyecto de Grado.</p>
            <span class="ig-post-date">Abril 2026</span>
          </div>
        </div>
        <div class="ig-post">
          <img src="{{ asset('assets/img/mesadetrabajo2.jpeg') }}" alt="Mesa de trabajo con estudiantes de la UPTP" loading="lazy">
          <div class="ig-post-overlay">
            <p>Mesa de trabajo con estudiantes de la casa de estudio J.J. Montilla con fines de brindar apoyo para el desarrollo de su proyecto de Grado.</p>
            <span class="ig-post-date">Abril 2026</span>
          </div>
        </div>
        <div class="ig-post">
          <img src="{{ asset('assets/img/juramentacion.jpeg') }}" alt="Juramentacion del nuevo contralor" loading="lazy">
          <div class="ig-post-overlay">
            <p>Juramento del nuevo Contralor del Municipio Páez, el Abg. Leonel Lucena. </p>
            <span class="ig-post-date">Junio 2025</span>
          </div>
        </div>
        <div class="ig-post">
          <img src="{{ asset('assets/img/reunion.jpeg') }}" alt="Reunion de Contralores." loading="lazy">
          <div class="ig-post-overlay">
            <p>El Abg. Leonel Lucena, Contralor del Municipio Páez, junto a la Contralora del Estado Portuguesa, Dra. Nayke Tovar, y los Contralores Municipales de la Región Llanera, acudieron a la Contraloría General de la República para optimizar la gestión fiscal en la región.</p>
            <span class="ig-post-date">Enero 2026</span>
          </div>
        </div>
        <div class="ig-post">
          <img src="{{ asset('assets/img/clasecontralor.jpeg') }}" loading="lazy">
          <div class="ig-post-overlay">
            <p> Taller organizado junto a la Contraloría del municipio Páez, convocó a contralores y técnicos del Eje Sur del estado. </p>
            <span class="ig-post-date">Marzo 2026</span>
          </div>
        </div>
        <div class="ig-post">
          <img src="{{ asset('assets/img/ninos.jpeg') }}" loading="lazy">
          <div class="ig-post-overlay">
            <p>La Contraloría del Municipio Páez, dando continuidad al programa escolar en la fase 4.</p>
            <span class="ig-post-date">Marzo 2026</span>
          </div>
        </div>
        <div class="ig-post">
          <img src="{{ asset('assets/img/rrhh.jpeg') }}" alt="Recursos Humano" loading="lazy">
          <div class="ig-post-overlay">
            <p>Entre las direcciones de Talento Humano de la Contraloría Municipal de Páez y Contraloría Municipal de Araure, se llevó a cabo una Mesa de Trabajo con el fin de optimizar los procesos en beneficio de los trabajadores del Sistema Nacional de Control Fiscal.</p>
            <span class="ig-post-date">Febrero 2026</span>
          </div>
        </div>
        <div class="ig-post">
          <img src="{{ asset('assets/img/contralores.jpeg') }}" alt="Reunion de contralores" loading="lazy">
          <div class="ig-post-overlay">
            <p>El Contralor Municipal de Páez, Abg. Leonel Lucena, junto a los Directores de Administración y Talento Humano sostuvieron reunión con el Contralor del Municipio Araure, M.Sc. Julio Camacaro, con el objetivo de asegurar estándares para el cumplimiento de los principios de legalidad.</p>
            <span class="ig-post-date">Marzo 2026</span>
          </div>
        </div>
      </div>

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

      <div class="row g-4 mt-2">
        <div class="col-md-6">
          <ul class="legal-list">
            <li>
              <span class="icon"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#1565c0" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                  <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z" />
                  <polyline points="14 2 14 8 20 8" />
                  <line x1="16" y1="13" x2="8" y2="13" />
                  <line x1="16" y1="17" x2="8" y2="17" />
                </svg></span>
              Constitución de la República Bolivariana de Venezuela (1999)
            </li>
            <li>
              <span class="icon"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#1565c0" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                  <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z" />
                  <polyline points="14 2 14 8 20 8" />
                  <line x1="16" y1="13" x2="8" y2="13" />
                  <line x1="16" y1="17" x2="8" y2="17" />
                </svg></span>
              Ley Orgánica de la Contraloría General de la República y del Sistema Nacional de Control Fiscal
            </li>
            <li>
              <span class="icon"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#1565c0" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                  <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z" />
                  <polyline points="14 2 14 8 20 8" />
                  <line x1="16" y1="13" x2="8" y2="13" />
                  <line x1="16" y1="17" x2="8" y2="17" />
                </svg></span>
              Ley Orgánica del Poder Público Municipal (LOPPM – G.O. N° 6.015)
            </li>
            <li>
              <span class="icon"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#1565c0" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                  <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z" />
                  <polyline points="14 2 14 8 20 8" />
                  <line x1="16" y1="13" x2="8" y2="13" />
                  <line x1="16" y1="17" x2="8" y2="17" />
                </svg></span>
              Ley de Reforma del Decreto con Rango, Valor y Fuerza de Ley Contra la Corrupción
            </li>
          </ul>
        </div>
        <div class="col-md-6">
          <ul class="legal-list">
            <li>
              <span class="icon"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#1565c0" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                  <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z" />
                  <polyline points="14 2 14 8 20 8" />
                  <line x1="16" y1="13" x2="8" y2="13" />
                  <line x1="16" y1="17" x2="8" y2="17" />
                </svg></span>
              Reglamento sobre Concursos Públicos para Designación de Contralores Municipales (G.O. N° 38.311)
            </li>
            <li>
              <span class="icon"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#1565c0" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                  <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z" />
                  <polyline points="14 2 14 8 20 8" />
                  <line x1="16" y1="13" x2="8" y2="13" />
                  <line x1="16" y1="17" x2="8" y2="17" />
                </svg></span>
              Resolución N° 01-00-122 del 19/06/2009 (G.O. N° 39.205)
            </li>
            <li>
              <span class="icon"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#1565c0" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                  <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z" />
                  <polyline points="14 2 14 8 20 8" />
                  <line x1="16" y1="13" x2="8" y2="13" />
                  <line x1="16" y1="17" x2="8" y2="17" />
                </svg></span>
              Ley de Consejos Comunales – Art. 29 (Unidades de Gestión Financiera)
            </li>
            <li>
              <span class="icon"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#1565c0" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                  <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z" />
                  <polyline points="14 2 14 8 20 8" />
                  <line x1="16" y1="13" x2="8" y2="13" />
                  <line x1="16" y1="17" x2="8" y2="17" />
                </svg></span>
              Ordenanza Municipal de Control Fiscal del Municipio Páez, Estado Portuguesa
            </li>
          </ul>
        </div>
      </div>
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
@endpush
