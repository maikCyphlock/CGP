<!DOCTYPE html>
<html lang="es">

<head>
  <meta charset="UTF-8">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Recepción de Atención al Ciudadano – Contraloría del Municipio Páez</title>

  <!-- Fuentes institucionales -->
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link
    href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700;900&family=Open+Sans:wght@400;600;700&display=swap"
    rel="stylesheet">

  <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.2/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="{{ asset('assets') }}/css/denuncias.css">
  <link rel="stylesheet" href="{{ asset('assets') }}/css/contraloria.css">

  <style>
    /* Estilos adicionales para justificar textos institucionales */
    p, .aviso-anonimato div, .tramite-card-desc, .resumen-valor {
      text-align: justify;
    }
    #header {
      position: relative !important;
      top: auto !important;
      left: auto !important;
      width: 100% !important;
      z-index: 1000 !important;
      visibility: visible !important;
      opacity: 1 !important;
      box-sizing: border-box;
      background: #0f2340;
      padding: 15px 30px;
      color: white;
    }

    #header .header-container {
      position: relative !important;
      display: flex !important;
      flex-direction: row !important;
      align-items: center !important;
      justify-content: space-between !important;
      width: 100% !important;
      max-width: 100% !important;
      visibility: visible !important;
      opacity: 1 !important;
    }

    #header .logo-link {
      position: static !important;
      flex: 0 1 auto !important;
      margin: 0 !important;
      display: flex;
      align-items: center;
      gap: 15px;
      text-decoration: none;
      color: white;
    }

    #header .logo-texts .l1 {
      font-weight: bold;
      font-size: 0.95rem;
      line-height: 1.2;
    }

    #header #navbar {
      position: static !important;
      display: flex !important;
      align-items: center !important;
      justify-content: flex-end !important;
      width: auto !important;
      height: auto !important;
      margin: 0 0 0 auto !important;
      padding: 0 !important;
      flex: 0 0 auto !important;
      z-index: 10 !important;
    }

    .btn-inicio {
      display: inline-flex !important;
      align-items: center;
      justify-content: center;
      padding: 8px 16px;
      border: 1px solid rgba(255, 255, 255, 0.35);
      border-radius: 20px;
      background: transparent !important;
      color: #fff !important;
      text-decoration: none !important;
      font-size: 0.9rem;
      font-weight: 600 !important;
      line-height: 1;
      white-space: nowrap;
    }

    .btn-inicio:hover {
      background: rgba(255, 255, 255, 0.10) !important;
      border-color: rgba(255, 255, 255, 0.55) !important;
    }

    @media (max-width: 768px) {
      #header {
        padding: 10px 14px !important;
      }
      #header .header-container {
        min-height: 42px !important;
        flex-direction: row !important;
        flex-wrap: nowrap !important;
        align-items: center !important;
        gap: 10px;
      }
      #header .logo-link {
        flex: 1 1 auto !important;
        max-width: calc(100% - 75px) !important;
        gap: 9px !important;
      }
      #header .logo-link img {
        width: 42px !important;
        height: 42px !important;
      }
      #header .logo-texts .l1 {
        font-size: 0.72rem !important;
        line-height: 1.15 !important;
      }
      #header #navbar .btn-inicio {
        padding: 7px 13px;
        font-size: 0.8rem;
      }
    }

    /* ESTILOS DE IMPRESIÓN Y PLANILLA RESPONSIVA */
    .print-card-box {
      border: 1px solid #dce3ec;
      padding: 15px;
      border-radius: 8px;
      background: #fff;
      text-align: left;
      word-break: break-word;
      margin-bottom: 20px;
    }
    .print-row {
      display: flex;
      flex-wrap: wrap;
      margin-bottom: 8px;
      border-bottom: 1px solid #f0f4f8;
      padding-bottom: 6px;
      font-size: 0.85rem;
    }
    .print-label {
      font-weight: bold;
      color: #1a2340;
      width: 40%;
    }
    .print-val {
      color: #4a5568;
      width: 60%;
    }
    .print-section-title {
      color: #1565c0; 
      border-bottom: 1px solid #1565c0; 
      padding-bottom: 5px; 
      margin: 15px 0 12px 0; 
      font-size: 0.95rem; 
      font-weight: bold;
      text-transform: uppercase;
    }

    @media (max-width: 576px) {
      .print-row {
        flex-direction: column;
      }
      .print-label, .print-val {
        width: 100%;
      }
      .print-val {
        margin-top: 2px;
      }
    }

    @media print {
      body * {
        visibility: hidden;
      }
      #vista-confirmacion, #vista-confirmacion * {
        visibility: visible;
      }
      #vista-confirmacion {
        position: absolute;
        left: 0;
        top: 0;
        width: 100%;
        margin: 0;
        padding: 10px;
        background: #fff !important;
        font-size: 11px !important;
      }
      .btn-submit, .form-back-btn, header, footer, #barra-progreso {
        display: none !important;
      }
      .print-card-box {
        border: 1px solid #333;
      }
      .print-row {
        border-bottom: 1px solid #ccc;
      }
    }
  </style>
</head>

<body>
<header id="header">
  <div class="header-container">
    <a href="{{ url('/') }}" class="logo-link">
      <div style="width:56px;height:56px;flex-shrink:0;display:flex;align-items:center;justify-content:center;">
        <img src="{{ asset('assets') }}/img/logonuevo.jpeg" alt="Logo" style="width:56px;height:56px;max-width:100%;border-radius:25px;">
      </div>
      <div class="logo-texts">
        <div class="l1">CONTRALORÍA DEL <br>MUNICIPIO PÁEZ</div>
      </div>
    </a>
    <nav id="navbar">
      <a href="{{ url('/') }}" class="btn-inicio" aria-label="Ir al inicio">Inicio</a>
    </nav>
  </div>
</header>

  <div class="page-header-strip" style="margin-top:0px;">
    <div class="page-header-inner">
      <div class="breadcrumb-row">
        <a href="{{ url('/') }}">Inicio</a>
        <span>›</span>
        <a href="">Participación Ciudadana</a>
        <span>›</span>
        <span style="color:rgba(255,255,255,0.85);">Atención al Ciudadano</span>
      </div>
      <div class="page-header-accent"></div>
      <h1>Recepción de Atención al Ciudadano</h1>
      <p>Dirección de Atención al Ciudadano · Contraloría del Municipio Páez, Estado Portuguesa</p>
    </div>
  </div>

  <section id="denuncias">
    <div class="denuncias-inner">

      <!--  BARRA DE PROGRESO (Pasos 1–5) -->
      <div id="barra-progreso" style="display:none;" class="barra-pasos-wrap">
        <div class="barra-pasos">
          <div class="paso-item activo" data-paso="0">
            <div class="paso-num">1</div>
            <span class="paso-etiqueta">TIPO</span>
          </div>
          <div class="paso-item" data-paso="1">
            <div class="paso-num">2</div>
            <span class="paso-etiqueta">CIUDADANO</span>
          </div>
          <div class="paso-item" data-paso="2">
            <div class="paso-num">3</div>
            <span class="paso-etiqueta">SEÑALADO</span>
          </div>
          <div class="paso-item" data-paso="3">
            <div class="paso-num">4</div>
            <span class="paso-etiqueta">HECHOS</span>
          </div>
          <div class="paso-item" data-paso="4">
            <div class="paso-num">5</div>
            <span class="paso-etiqueta">EVIDENCIAS</span>
          </div>
          <div class="paso-item" data-paso="5">
            <div class="paso-num">6</div>
            <span class="paso-etiqueta">REVISIÓN</span>
          </div>
        </div>
      </div>

      <!-- VISTA DE INICIO (Avisos previos)-->
      <div id="vista-seleccion">
        <h2 class="section-title-center">Consideraciones Previas</h2>
        <p class="section-subtitle-center">
          Por favor, lea atentamente la siguiente información antes de iniciar su trámite.
        </p>
        <div class="title-underline"></div>

        <div class="aviso-anonimato" style="background-color: #e3f2fd; border-left: 4px solid #1565c0; color: #0d47a1; max-width: 800px; margin: 0 auto 20px auto;">
          <span class="av-icon" style="color: #1565c0;">
            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
              <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z" />
              <polyline points="14 2 14 8 20 8" />
              <line x1="16" y1="13" x2="8" y2="13" />
              <line x1="16" y1="17" x2="8" y2="17" />
            </svg>
          </span>
          <div>
            <strong>Aviso Legal sobre Consignación de Documentos:</strong>
            De manera legal y formal, se le notifica que una vez culminado el proceso digital de su trámite, <strong>deberá consignar físicamente el documento final impreso</strong> generado por este sistema ante nuestras oficinas. En el caso exclusivo de las <strong>Denuncias</strong>, es de carácter obligatorio consignar también todas las evidencias físicas probatorias ante la sede de la Contraloría.
          </div>
        </div>

          <div class="aviso-anonimato" style="background-color: #e3f2fd; border-left: 4px solid #1565c0; color: #0d47a1; max-width: 800px; margin: 0 auto 20px auto;">
          <span class="av-icon" style="color: #1565c0;">
            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" style="vertical-align: middle; margin-right: 8px;">
    <circle cx="12" cy="12" r="10"></circle>
    <line x1="4.93" y1="4.93" x2="19.07" y2="19.07"></line>
  </svg>
          </span>
          <div>
            <strong>Este sistema no admite denuncias anónimas.</strong>
            De conformidad con la Ley Orgánica de la Contraloría General de la República y del Sistema Nacional de Control
            Fiscal, la identificación del denunciante es obligatoria. Sus datos personales serán tratados con estricta
            confidencialidad y utilizados únicamente para los fines del proceso de investigación.
        </div>
        </div>
         
       <div class="aviso-anonimato" style="background-color: #e3f2fd; border-left: 4px solid #1565c0; color: #0d47a1; max-width: 800px; margin: 0 auto 20px auto;">
          <span class="av-icon" style="color: #1565c0;">
            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"
              stroke-linecap="round" stroke-linejoin="round">
              <path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z" />
              <line x1="12" y1="9" x2="12" y2="13" />
              <line x1="12" y1="17" x2="12.01" y2="17" />
            </svg>
          </span>
          <div>
            <strong>Atención para menores de edad:</strong>
            Si usted es menor de edad, puede realizar una denuncia, queja, reclamo, petición o sugerencia siempre y cuando el trámite sea presentado y usted esté debidamente representado por su representante legal o un adulto responsable.
          </div>
        </div>

        <div style="text-align: center; margin-bottom: 20px; display: flex; justify-content: center; gap: 15px; flex-wrap: wrap;">
          <button class="btn-submit" style="font-size: 1.1rem; padding: 12px 32px; background: #022139; border: 1px solid #4d7f99;" onclick="mostrarConsultaTramite()">
            Estado de Trámite
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"
              stroke-linecap="round" stroke-linejoin="round" style="margin-left: 8px;">
              <circle cx="11" cy="11" r="8"></circle>
              <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
            </svg>
          </button>
          
          <button class="btn-submit" style="font-size: 1.1rem; padding: 12px 32px; background: #1565c0;" onclick="iniciarFlujo('general')">
            Continuar
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"
              stroke-linecap="round" stroke-linejoin="round" style="margin-left: 8px;">
              <line x1="5" y1="12" x2="19" y2="12" />
              <polyline points="12 5 19 12 12 19" />
            </svg>
          </button>
        </div>
      </div><!-- /vista-seleccion -->


      <!-- VISTA DE CONSULTA DE ESTADO DE TRÁMITE -->
      <div id="vista-estado-tramite" style="display:none; max-width: 600px; margin: 0 auto; background: #fff; padding: 30px; border-radius: 8px; border: 1px solid #dce3ec; box-shadow: 0 4px 12px rgba(0,0,0,0.05);">
        <button class="form-back-btn" onclick="volverSeleccionDesdeEstado()" style="margin-bottom: 20px;">
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
            <line x1="19" y1="12" x2="5" y2="12" />
            <polyline points="12 19 5 12 12 5" />
          </svg>
          Volver al inicio
        </button>

        <h3 style="color: #1565c0; font-weight: 700; margin-bottom: 10px; text-align: center;">Consultar Estado de Trámite</h3>
        <p style="text-align: center; color: #666; font-size: 0.9rem; margin-bottom: 20px;">Ingrese el código de expediente o código arrojado por el sistema para conocer el estatus actual de su solicitud.</p>

        <div class="form-group" style="margin-bottom: 15px;">
          <label class="form-label" style="font-weight: bold; color: #1a2340;">Código de Expediente / Solicitud</label>
          <div style="display: flex; gap: 10px;">
            <input type="text" id="input-codigo-consulta" class="form-control mayusculas" placeholder="Ej. OAC-2026-0001" style="font-family: monospace; text-transform: uppercase;" oninput="this.value=this.value.toUpperCase()">
            <button class="btn-submit" style="background: #01579b; padding: 8px 20px; white-space: nowrap;" onclick="consultarEstadoTramite()">Consultar</button>
          </div>
          <span class="error-msg" id="err-codigo-consulta">Por favor ingrese un código válido.</span>
        </div>

        <div id="resultado-estado-container" style="margin-top: 25px; display: none;">
          <div style="background: #f0f4f8; border: 1px solid #dce3ec; padding: 20px; border-radius: 6px;">
            <h5 style="color: #1565c0; font-size: 1rem; font-weight: bold; margin-bottom: 15px; border-bottom: 1px solid #dce3ec; padding-bottom: 8px;">Resultado de la Consulta</h5>
            <div class="print-row"><div class="print-label">Expediente:</div><div class="print-val" id="res-est-codigo">-</div></div>
            <div class="print-row"><div class="print-label">Tipo de Trámite:</div><div class="print-val" id="res-est-tipo">-</div></div>
            <div class="print-row"><div class="print-label">Fecha de Registro:</div><div class="print-val" id="res-est-fecha">-</div></div>
            <div class="print-row" style="border-bottom: none;"><div class="print-label">Estado Actual:</div><div class="print-val" id="res-est-estado" style="font-weight: bold; color: #2e7d32;">-</div></div>
          </div>
        </div>
      </div>


      <!-- CONTENEDOR DEL FORMULARIO WIZARD -->
      <div id="vista-wizard" style="display:none;">

        <button class="form-back-btn" onclick="volverSeleccion()">
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"
            stroke-linecap="round" stroke-linejoin="round">
            <line x1="19" y1="12" x2="5" y2="12" />
            <polyline points="12 19 5 12 12 5" />
          </svg>
          Volver a las consideraciones
        </button>

        <!-- PASO 1 -->
        <div class="wizard-paso" id="paso-1">
          <div class="form-header" id="paso1-cabecera" style="background: #1565c0 !important;">
            <div class="form-header-icon" id="paso1-icono"></div>
            <div>
              <h2 id="paso1-titulo"></h2>
              <p id="paso1-subtitulo">Paso 1 de 6 · Tipo de Trámite</p>
            </div>
          </div>
          <div class="form-body">
            <div class="banner-error" id="err-paso1" style="display:none;">
              <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"
                stroke-linecap="round" stroke-linejoin="round">
                <path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z" />
                <line x1="12" y1="9" x2="12" y2="13" />
                <line x1="12" y1="17" x2="12.01" y2="17" />
              </svg>
              <div><strong>Por favor corrija los siguientes campos:</strong>
                <ul id="err-paso1-lista"></ul>
              </div>
            </div>
            <div id="bloque-tipo-tramite">
              <div class="form-section-title">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"
                  stroke-linecap="round" stroke-linejoin="round">
                  <circle cx="12" cy="12" r="10" />
                  <line x1="12" y1="8" x2="12" y2="12" />
                  <line x1="12" y1="16" x2="12.01" y2="16" />
                </svg>
                Seleccione el Tipo de Trámite <span style="color:#1565c0;margin-left:3px;">*</span>
              </div>
              <div class="tipo-tramite-grid" id="tipo-tramite-grid">
                <label class="tramite-card" id="card-denuncia">
                  <input type="radio" name="tipo_tramite" value="denuncia" onchange="onTipoTramiteChange(this)">
                  <div class="tramite-card-body">
                    <div class="tramite-card-icon">
                      <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z" />
                        <line x1="12" y1="9" x2="12" y2="13" />
                        <line x1="12" y1="17" x2="12.01" y2="17" />
                      </svg>
                    </div>
                    <div class="tramite-card-label">Denuncia</div>
                    <div class="tramite-card-desc">Irregularidades en el uso de recursos públicos</div>
                  </div>
                </label>
                <label class="tramite-card" id="card-queja">
                  <input type="radio" name="tipo_tramite" value="queja" onchange="onTipoTramiteChange(this)">
                  <div class="tramite-card-body">
                    <div class="tramite-card-icon">
                      <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z" />
                      </svg>
                    </div>
                    <div class="tramite-card-label">Queja</div>
                    <div class="tramite-card-desc">Mala prestación de un servicio público</div>
                  </div>
                </label>
                <label class="tramite-card" id="card-reclamo">
                  <input type="radio" name="tipo_tramite" value="reclamo" onchange="onTipoTramiteChange(this)">
                  <div class="tramite-card-body">
                    <div class="tramite-card-icon">
                      <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z" />
                        <polyline points="14 2 14 8 20 8" />
                        <line x1="16" y1="13" x2="8" y2="13" />
                        <line x1="16" y1="17" x2="8" y2="17" />
                      </svg>
                    </div>
                    <div class="tramite-card-label">Reclamo</div>
                    <div class="tramite-card-desc">Incumplimiento de una obligación institucional</div>
                  </div>
                </label>
                <label class="tramite-card" id="card-peticion">
                  <input type="radio" name="tipo_tramite" value="peticion" onchange="onTipoTramiteChange(this)">
                  <div class="tramite-card-body">
                    <div class="tramite-card-icon">
                      <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                        <line x1="22" y1="2" x2="11" y2="13" />
                        <polygon points="22 2 15 22 11 13 2 9 22 2" />
                      </svg>
                    </div>
                    <div class="tramite-card-label">Petición</div>
                    <div class="tramite-card-desc">Solicitud de información o actuación</div>
                  </div>
                </label>
                <label class="tramite-card" id="card-sugerencia">
                  <input type="radio" name="tipo_tramite" value="sugerencia" onchange="onTipoTramiteChange(this)">
                  <div class="tramite-card-body">
                    <div class="tramite-card-icon">
                      <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="12" cy="12" r="4"/>
                        <path d="M12 2v2"/><path d="M12 20v2"/>
                        <path d="M4.93 4.93l1.41 1.41"/><path d="M17.66 17.66l1.41 1.41"/>
                        <path d="M2 12h2"/><path d="M20 12h2"/>
                        <path d="M4.93 19.07l1.41-1.41"/><path d="M17.66 6.34l1.41-1.41"/>
                      </svg>
                    </div>
                    <div class="tramite-card-label">Sugerencia</div>
                    <div class="tramite-card-desc">Propuesta para mejorar la institución</div>
                  </div>
                </label>
              </div>
              <span class="error-msg" id="tipo-tramite-err">Debe seleccionar un tipo de trámite.</span>

              <div id="bloque-consulta-popular" style="display:none;margin-top:24px;">
                <div class="form-section" style="padding-top:20px;border-top:1px solid #eef1f7;">
                  <div class="form-section-title" id="consulta-title-color">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                      <circle cx="12" cy="12" r="10" />
                      <line x1="12" y1="8" x2="12" y2="12" />
                      <line x1="12" y1="16" x2="12.01" y2="16" />
                    </svg>
                    Contexto de la Denuncia <span style="color:#1565c0;margin-left:3px;">*</span>
                  </div>
                  <div class="form-group">
                    <label class="form-label">
                      ¿La denuncia está relacionada con un proyecto de la Consulta Popular Nacional?
                    </label>
                    <div class="check-group" id="radio-consulta-group" style="margin-top:8px;">
                      <label class="check-item">
                        <input type="radio" name="es_consulta" value="si" onchange="document.getElementById('es-consulta-err').classList.remove('visible')">
                        <label>Sí, es sobre un proyecto de consulta popular</label>
                      </label>
                      <label class="check-item">
                        <input type="radio" name="es_consulta" value="no" onchange="document.getElementById('es-consulta-err').classList.remove('visible')">
                        <label>No, es sobre otro organismo o situación</label>
                      </label>
                    </div>
                    <span class="error-msg" id="es-consulta-err">Debe indicar si la denuncia es sobre un proyecto de consulta popular.</span>
                  </div>
                </div>
              </div>
            </div>
          </div>
          <div class="wizard-nav">
            <span style="font-size:0.78rem;color:#888;">
              Los campos <span style="color:#1565c0;font-weight:700;">*</span> son obligatorios.
            </span>
            <button class="btn-submit" id="btn-sig-paso1" style="background: #1565c0;" onclick="siguientePaso(1)">
              Continuar
              <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                <line x1="5" y1="12" x2="19" y2="12" />
                <polyline points="12 5 19 12 12 19" />
              </svg>
            </button>
          </div>
        </div><!-- /paso-1 -->


        <!-- PASO 2 -->
        <div class="wizard-paso" id="paso-2" style="display:none;">
          <div class="form-header" id="paso2-cabecera" style="background: #1565c0 !important;">
            <div class="form-header-icon" id="paso2-icono"></div>
            <div>
              <h2 id="paso2-titulo"></h2>
              <p>Paso 2 de 6 · Datos del Ciudadano Solicitante</p>
            </div>
          </div>
          <div class="form-body">
            <div class="banner-error" id="err-paso2" style="display:none;">
              <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z" />
                <line x1="12" y1="9" x2="12" y2="13" />
                <line x1="12" y1="17" x2="12.01" y2="17" />
              </svg>
              <div><strong>Por favor corrija los siguientes campos:</strong>
                <ul id="err-paso2-lista"></ul>
              </div>
            </div>

            <div class="form-section">
              <div class="form-section-title" id="ciudadano-title-color">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                  <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2" />
                  <circle cx="12" cy="7" r="4" />
                </svg>
                Identificación del Solicitante
              </div>
              <div class="row g-3">
                <div class="col-md-3">
                  <div class="form-group">
                    <label class="form-label">Tipo de Documento <span class="required" style="color:#1565c0;">*</span></label>
                    <select id="cit-tipo-doc" class="form-select">
                      <option value="">Seleccionar</option>
                      <option value="V">V — Venezolano</option>
                      <option value="E">E — Extranjero</option>
                      <option value="P">Pasaporte</option>
                    </select>
                    <span class="error-msg" id="cit-tipo-doc-err">Seleccione el tipo de documento.</span>
                  </div>
                </div>
                <div class="col-md-3">
                  <div class="form-group">
                    <label class="form-label">Número de Documento <span class="required" style="color:#1565c0;">*</span></label>
                    <input type="text" id="cit-nro-doc" class="form-control" placeholder="Ej. 12345678" maxlength="10">
                    <span class="error-msg" id="cit-nro-doc-err">Ingrese un número de documento válido.</span>
                  </div>
                </div>
                <div class="col-md-3">
                  <div class="form-group">
                    <label class="form-label">Primer Nombre <span class="required" style="color:#1565c0;">*</span></label>
                    <input type="text" id="cit-primer-nombre" class="form-control" placeholder="Ej. José">
                    <span class="error-msg" id="cit-primer-nombre-err">Ingrese su primer nombre.</span>
                  </div>
                </div>
                <div class="col-md-3">
                  <div class="form-group">
                    <label class="form-label">Segundo Nombre</label>
                    <input type="text" id="cit-segundo-nombre" class="form-control" placeholder="Ej. Antonio">
                  </div>
                </div>
                <div class="col-md-3">
                  <div class="form-group">
                    <label class="form-label">Primer Apellido <span class="required" style="color:#1565c0;">*</span></label>
                    <input type="text" id="cit-primer-apellido" class="form-control" placeholder="Ej. Pérez">
                    <span class="error-msg" id="cit-primer-apellido-err">Ingrese su primer apellido.</span>
                  </div>
                </div>
                <div class="col-md-3">
                  <div class="form-group">
                    <label class="form-label">Segundo Apellido</label>
                    <input type="text" id="cit-segundo-apellido" class="form-control" placeholder="Ej. Rodríguez">
                  </div>
                </div>
                <div class="col-md-2">
                  <div class="form-group">
                    <label class="form-label">Sexo <span class="required" style="color:#1565c0;">*</span></label>
                    <select id="cit-sexo" class="form-select">
                      <option value="">--</option>
                      <option value="M">Masculino</option>
                      <option value="F">Femenino</option>
                    </select>
                    <span class="error-msg" id="cit-sexo-err">Seleccione.</span>
                  </div>
                </div>
                <div class="col-md-3">
                  <div class="form-group">
                    <label class="form-label">Fecha de Nacimiento <span class="required" style="color:#1565c0;">*</span></label>
                    <input type="date" id="cit-fecha-nac" class="form-control" onchange="actualizarEdadComputada(this)">
                    <span class="error-msg" id="cit-fecha-nac-err">Seleccione su fecha de nacimiento.</span>
                  </div>
                </div>
                <div class="col-md-2">
                  <div class="form-group">
                    <label class="form-label">Edad <span class="required" style="color:#1565c0;">*</span></label>
                    <input type="text" id="cit-edad" class="form-control" placeholder="Automática" readonly tabindex="-1" style="background:#f2f4f8;color:#5a6474;cursor:not-allowed;">
                    <span class="error-msg" id="cit-edad-err">Requerido.</span>
                  </div>
                </div>
                <div class="col-md-3">
                  <div class="form-group">
                    <label class="form-label">Estado Civil <span class="required" style="color:#1565c0;">*</span></label>
                    <select id="cit-ecivil" class="form-select">
                      <option value="">-- Seleccione --</option>
                      <option>Soltero(a)</option>
                      <option>Casado(a)</option>
                      <option>Divorciado(a)</option>
                      <option>Viudo(a)</option>
                      <option>Unión estable</option>
                    </select>
                    <span class="error-msg" id="cit-ecivil-err">Seleccione su estado civil.</span>
                  </div>
                </div>
                <div class="col-12">
                  <div class="aviso-menor-edad" id="aviso-menor-edad" style="display:none;">
                    <span class="av-icon">
                      <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="12" cy="12" r="10" />
                        <line x1="12" y1="8" x2="12" y2="12" />
                        <line x1="12" y1="16" x2="12.01" y2="16" />
                      </svg>
                    </span>
                    <div>
                      <strong>No puede continuar: es menor de edad.</strong>
                      Únicamente las personas mayores de 18 años pueden interponer denuncias o solicitudes ante la Contraloría Municipal.
                    </div>
                  </div>
                </div>
              </div>

              <div class="form-group" style="margin-top:8px;">
                <label class="form-label">Datos Demográficos Complementarios</label>
                <div style="overflow-x:auto;">
                  <table class="tabla-demografica">
                    <thead>
                      <tr>
                        <th>Lugar de Nacimiento</th>
                        <th>Nivel Educativo</th>
                        <th>Profesión</th>
                        <th>Ocupación</th>
                      </tr>
                    </thead>
                    <tbody>
                      <tr>
                        <td>
                          <input type="text" id="cit-lugar-nac" class="form-control" placeholder="Ej. Acarigua, Portuguesa">
                        </td>
                        <td>
                          <select id="cit-edu" class="form-select">
                            <option value="">-- Seleccione --</option>
                            <option>Primaria</option>
                            <option>Secundaria</option>
                            <option>Técnico</option>
                            <option>Universitario</option>
                            <option>Postgrado</option>
                          </select>
                        </td>
                        <td>
                          <input type="text" id="cit-profesion" class="form-control" placeholder="Ej. Docente">
                        </td>
                        <td>
                          <input type="text" id="cit-ocupacion" class="form-control" placeholder="Ej. Comerciante">
                        </td>
                      </tr>
                    </tbody>
                  </table>
                </div>
              </div>
            </div>

            <div class="form-section">
              <div class="form-section-title" id="contacto-title-color">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                  <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z" />
                  <circle cx="12" cy="10" r="3" />
                </svg>
                Dirección y Contacto
              </div>
              <div class="row g-3">
                <div class="col-md-4">
                  <div class="form-group">
                    <label class="form-label">Correo Electrónico <span class="required" style="color:#1565c0;">*</span></label>
                    <input type="email" id="cit-correo" class="form-control" placeholder="ejemplo@gmail.com">
                    <span class="error-msg" id="cit-correo-err">Ingrese un correo válido.</span>
                  </div>
                </div>
                <div class="col-md-4">
                  <div class="form-group">
                    <label class="form-label">Confirmar Correo <span class="required" style="color:#1565c0;">*</span></label>
                    <input type="email" id="cit-correo2" class="form-control" placeholder="ejemplo@gmail.com">
                    <span class="error-msg" id="cit-correo2-err">Los correos no coinciden.</span>
                  </div>
                </div>
                <div class="col-md-4">
                  <div class="form-group">
                    <label class="form-label">Teléfono Celular <span class="required" style="color:#1565c0;">*</span></label>
                    <div style="display:flex;gap:6px;">
                      <select id="cit-telf-cel-cod" class="form-select" style="max-width:110px;flex-shrink:0;">
                        <option value="">Cód.</option>
                        <option value="0412">0412</option>
                        <option value="0414">0414</option>
                        <option value="0416">0416</option>
                        <option value="0424">0424</option>
                        <option value="0426">0426</option>
                      </select>
                      <input type="tel" id="cit-telf-cel-num" class="form-control" placeholder="1234567" maxlength="7">
                    </div>
                    <span class="error-msg" id="cit-telf-cel-err">Complete el número celular.</span>
                  </div>
                </div>
                <div class="col-md-4">
                  <div class="form-group">
                    <label class="form-label">Teléfono Habitación</label>
                    <div style="display:flex;gap:6px;">
                      <select id="cit-telf-hab-cod" class="form-select" style="max-width:130px;flex-shrink:0;">
                        <option value="">Cód.</option>
                        <option value="0255">0255</option>
                        <option value="0257">0257</option>
                      </select>
                      <input type="tel" id="cit-telf-hab-num" class="form-control" placeholder="1234567" maxlength="7">
                    </div>
                  </div>
                </div>
                <div class="col-md-4">
                  <div class="form-group">
                    <label class="form-label">Teléfono del Trabajo</label>
                    <div style="display:flex;gap:6px;">
                      <input type="tel" id="cit-telf-trab-cod" class="form-control" placeholder="Cód. área" maxlength="4" style="max-width:110px;flex-shrink:0;">
                      <input type="tel" id="cit-telf-trab-num" class="form-control" placeholder="1234567" maxlength="7">
                    </div>
                  </div>
                </div>
                <div class="col-md-3">
                  <div class="form-group">
                    <label class="form-label">Parroquia <span class="required" style="color:#1565c0;">*</span></label>
                    <select id="cit-parroquia" class="form-select">
                      <option value="">-- Seleccione --</option>
                      <option>Acarigua</option>
                      <option>Payara</option>
                      <option>Pimpinela</option>
                      <option>Ramón Peraza</option>
                    </select>
                    <span class="error-msg" id="cit-parroquia-err">Seleccione una parroquia.</span>
                  </div>
                </div>
                <div class="col-md-3">
                  <div class="form-group">
                    <label class="form-label" style="visibility:hidden;">Nota</label>
                    <div style="background:#f2f4f8;color:#5a6474;border:1.5px solid #e1e6ee;border-radius:5px;
                      padding:8px 10px;font-size:0.72rem;line-height:1.35;display:flex;gap:7px;align-items:flex-start;
                      box-sizing:border-box;height:100%;">
                      <span>Si el caso no se tramita en Páez, compete a otra contraloría.</span>
                    </div>
                  </div>
                </div>
                <div class="col-md-3">
                  <div class="form-group">
                    <label class="form-label">Municipio <span class="required" style="color:#1565c0;">*</span></label>
                    <input type="text" id="cit-municipio" class="form-control" value="Páez" readonly tabindex="-1"
                      style="background:#f2f4f8;color:#5a6474;cursor:not-allowed;">
                  </div>
                </div>
                <div class="col-md-3">
                  <div class="form-group">
                    <label class="form-label">Ciudad / Estado</label>
                    <input type="text" id="cit-ciudad" class="form-control" value="Acarigua/Portuguesa" readonly tabindex="-1"
                      style="background:#f2f4f8;color:#5a6474;cursor:not-allowed;">
                  </div>
                </div>
                <div class="col-12">
                  <div class="form-group">
                    <label class="form-label">Dirección de Habitación <span class="required" style="color:#1565c0;">*</span></label>
                    <input type="text" id="cit-direccion" class="form-control"
                      placeholder="Calle, Avenida, Urbanización...">
                    <span class="error-msg" id="cit-direccion-err">Ingrese su dirección completa.</span>
                  </div>
                </div>
              </div>
            </div>
          </div>
          <div class="wizard-nav">
            <button class="btn-submit btn-outline-inst" onclick="anteriorPaso(2)">
              <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                <line x1="19" y1="12" x2="5" y2="12" />
                <polyline points="12 19 5 12 12 5" />
              </svg>
              Anterior
            </button>
            <button class="btn-submit" id="btn-sig-paso2" style="background: #1565c0;" onclick="siguientePaso(2)">
              Continuar
              <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                <line x1="5" y1="12" x2="19" y2="12" />
                <polyline points="12 5 19 12 12 19" />
              </svg>
            </button>
          </div>
        </div><!-- /paso-2 -->


        <!-- PASO 3 -->
        <div class="wizard-paso" id="paso-3" style="display:none;">
          <div class="form-header" id="paso3-cabecera" style="background: #1565c0 !important;">
            <div class="form-header-icon" id="paso3-icono"></div>
            <div>
              <h2 id="paso3-titulo"></h2>
              <p>Paso 3 de 6 · Identificación del Señalado</p>
            </div>
          </div>
          <div class="form-body">
            <div class="banner-error" id="err-paso3" style="display:none;">
              <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z" />
                <line x1="12" y1="9" x2="12" y2="13" />
                <line x1="12" y1="17" x2="12.01" y2="17" />
              </svg>
              <div><strong>Por favor corrija los siguientes campos:</strong>
                <ul id="err-paso3-lista"></ul>
              </div>
            </div>

            <div id="contenido-senalado-wrapper">
              <div class="form-section">
                <div class="form-section-title" id="senalado-title-color">
                  <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2" />
                    <circle cx="9" cy="7" r="4" />
                    <path d="M23 21v-2a4 4 0 0 0-3-3.87" />
                    <path d="M16 3.13a4 4 0 0 1 0 7.75" />
                  </svg>
                  Datos del Señalado(s) <span style="color:#1565c0;margin-left:3px;">*</span>
                </div>
                <p style="font-size:0.78rem;color:#888;margin-bottom:12px;">
                  Agregue uno o más señalados. Seleccione el tipo de cada uno para completar sus datos.
                </p>

                <div id="lista-senalados"></div>
                <span class="error-msg" id="senalados-err">Debe agregar al menos un señalado.</span>
                <button type="button" class="btn-agregar-fila" onclick="agregarSenalado()">
                  <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                    <line x1="12" y1="5" x2="12" y2="19" />
                    <line x1="5" y1="12" x2="19" y2="12" />
                  </svg>
                  Agregar otro señalado
                </button>

                <div class="form-group" style="margin-top:20px;">
                  <label class="form-label">
                    Ubicación Geográfica del Señalado <span class="required" style="color:#1565c0;">*</span>
                  </label>
                  <input type="text" id="sen-ubicacion" class="form-control"
                    placeholder="Dirección, Parroquia, Consejo Comunal...">
                  <span class="error-msg" id="sen-ubicacion-err">Ingrese la ubicación del señalado.</span>
                </div>
              </div>

              <div id="bloque-proyecto-consulta" style="display:none;">
                <div class="form-section">
                  <div class="form-section-title">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                      <path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z" />
                      <polyline points="9 22 9 12 15 12 15 22" />
                    </svg>
                    Datos del Proyecto de la Consulta Popular Nacional
                  </div>
                  <div class="row g-3">
                    <div class="col-md-6">
                      <div class="form-group">
                        <label class="form-label">
                          Denominación del Proyecto <span class="required" style="color:#1565c0;">*</span>
                        </label>
                        <input type="text" id="proy-nombre" class="form-control" placeholder="Nombre oficial">
                        <span class="error-msg" id="proy-nombre-err">Ingrese el nombre del proyecto.</span>
                      </div>
                    </div>
                    <div class="col-md-3">
                      <div class="form-group">
                        <label class="form-label">
                          Fecha de Aprobación <span class="required" style="color:#1565c0;">*</span>
                        </label>
                        <input type="date" id="proy-fecha" class="form-control">
                        <span class="error-msg" id="proy-fecha-err">Seleccione la fecha.</span>
                      </div>
                    </div>
                    <div class="col-md-3">
                      <div class="form-group">
                        <label class="form-label">
                          Monto Estimado (Bs) <span class="required" style="color:#1565c0;">*</span>
                        </label>
                        <input type="text" id="proy-monto" class="form-control" placeholder="Ej. 500000">
                        <span class="error-msg" id="proy-monto-err">Ingrese un monto válido.</span>
                      </div>
                    </div>
                    <div class="col-md-6">
                      <div class="form-group">
                        <label class="form-label">Ente Financiador <span class="required" style="color:#1565c0;">*</span></label>
                        <input type="text" id="proy-financiador" class="form-control" placeholder="Nombre del ente">
                        <span class="error-msg" id="proy-financiador-err">Ingrese el ente financiador.</span>
                      </div>
                    </div>
                    <div class="col-md-3">
                      <div class="form-group">
                        <label class="form-label">Código SITUR</label>
                        <input type="text" id="proy-situr" class="form-control mayusculas" placeholder="SITUR"
                          oninput="this.value=this.value.toUpperCase()">
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
            
            <div id="senalado-no-aplica" style="display:none; text-align:center; padding: 40px 20px; background:#f7f9fd; border:1px solid #dde3ee; border-radius:6px; margin: 20px 0;">
              <h3 style="color:#1565c0; font-size:1.1rem; font-weight:700;">Este paso no aplica para el trámite seleccionado</h3>
              <p style="color:#555; font-size:0.9rem;">Haga clic en Continuar para seguir con el proceso.</p>
            </div>

          </div>

          <div class="wizard-nav">
            <button class="btn-submit btn-outline-inst" onclick="anteriorPaso(3)">
              <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                <line x1="19" y1="12" x2="5" y2="12" />
                <polyline points="12 19 5 12 12 5" />
              </svg>
              Anterior
            </button>
            <button class="btn-submit" id="btn-sig-paso3" style="background: #1565c0;" onclick="siguientePaso(3)">
              Continuar
              <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                <line x1="5" y1="12" x2="19" y2="12" />
                <polyline points="12 5 19 12 12 19" />
              </svg>
            </button>
          </div>
        </div><!-- /paso-3 -->


        <!-- PASO 4 -->
        <div class="wizard-paso" id="paso-4" style="display:none;">
          <div class="form-header" id="paso4-cabecera" style="background: #1565c0 !important;">
            <div class="form-header-icon" id="paso4-icono"></div>
            <div>
              <h2 id="paso4-titulo"></h2>
              <p>Paso 4 de 6 · Descripción de los Hechos / Solicitud</p>
            </div>
          </div>
          <div class="form-body">
            <div class="banner-error" id="err-paso4" style="display:none;">
              <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z" />
                <line x1="12" y1="9" x2="12" y2="13" />
                <line x1="12" y1="17" x2="12.01" y2="17" />
              </svg>
              <div><strong>Por favor corrija los siguientes campos:</strong>
                <ul id="err-paso4-lista"></ul>
              </div>
            </div>

            <div class="form-section">
              <div class="form-section-title" id="narracion-title-color">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                  <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z" />
                  <polyline points="14 2 14 8 20 8" />
                </svg>
                Descripción Detallada
              </div>

              <div id="banner-contexto-aviso" style="display:none; background:#e3f2fd; border-left:4px solid #1565c0; color:#0d47a1; padding:10px 14px; border-radius:4px; font-size:0.85rem; margin-bottom:16px;">
                <strong>Contexto del Trámite:</strong> <span id="texto-contexto-badge">—</span>
              </div>

              <div class="form-group">
                <label class="form-label">
                  Narración Circunstanciada / Descripción del Requerimiento <span class="required" style="color:#1565c0;">*</span>
                </label>
                <textarea id="narracion" class="form-textarea"
                  placeholder="Describa detalladamente su trámite..."
                  oninput="actualizarContadorNarracion(this)" style="min-height:140px;"></textarea>
                <div style="display:flex;justify-content:space-between;align-items:center;margin-top:4px;">
                  <span class="error-msg" id="narracion-err" style="display:inline;">La narración debe tener al menos 50 caracteres.</span>
                  <span class="contador-chars" id="narracion-contador">0 / 3000 caracteres</span>
                </div>
              </div>

              <div class="form-group" style="margin-top:20px;">
                <label class="form-label">
                  ¿Esta situación ha sido presentada ante otra instancia anteriormente?
                  <span class="required" style="color:#1565c0;">*</span>
                </label>
                <div class="check-group" style="margin-top:8px;">
                  <label class="check-item">
                    <input type="radio" name="otra_instancia" value="si" onchange="toggleOtraInstancia('si')">
                    <label>Sí</label>
                  </label>
                  <label class="check-item">
                    <input type="radio" name="otra_instancia" value="no" onchange="toggleOtraInstancia('no')">
                    <label>No</label>
                  </label>
                </div>
                <span class="error-msg" id="otra-inst-err">Debe indicar si fue presentada ante otra instancia.</span>
              </div>

              <div class="form-group" id="campo-cual-instancia" style="display:none;">
                <label class="form-label">
                  ¿Ante cuál instancia? <span class="required" style="color:#1565c0;">*</span>
                </label>
                <input type="text" id="cual-instancia" class="form-control"
                  placeholder="Nombre de la institución">
                <span class="error-msg" id="cual-instancia-err">Especifique la instancia.</span>
              </div>
            </div>

          </div>

          <div class="wizard-nav">
            <button class="btn-submit btn-outline-inst" onclick="anteriorPaso(4)">
              <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                <line x1="19" y1="12" x2="5" y2="12" />
                <polyline points="12 19 5 12 12 5" />
              </svg>
              Anterior
            </button>
            <button class="btn-submit" id="btn-sig-paso4" style="background: #1565c0;" onclick="siguientePaso(4)">
              Continuar
              <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                <line x1="5" y1="12" x2="19" y2="12" />
                <polyline points="12 5 19 12 12 19" />
              </svg>
            </button>
          </div>
        </div><!-- /paso-4 -->


        <!-- PASO 5 -->
        <div class="wizard-paso" id="paso-5" style="display:none;">
          <div class="form-header" id="paso5-cabecera" style="background: #1565c0 !important;">
            <div class="form-header-icon" id="paso5-icono"></div>
            <div>
              <h2 id="paso5-titulo"></h2>
              <p>Paso 5 de 6 · Evidencias</p>
            </div>
          </div>
          <div class="form-body">
            <div class="banner-error" id="err-paso5" style="display:none;">
              <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z" />
                <line x1="12" y1="9" x2="12" y2="13" />
                <line x1="12" y1="17" x2="12.01" y2="17" />
              </svg>
              <div><strong>Por favor corrija los siguientes campos:</strong>
                <ul id="err-paso5-lista"></ul>
              </div>
            </div>

            <div id="contenido-evidencia-wrapper">
              <div class="form-section">
                <div class="form-section-title" id="evidencias-title-color">
                  <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                    <polyline points="21 8 21 21 3 21 3 8" />
                    <rect x="1" y="3" width="22" height="5" />
                    <line x1="10" y1="12" x2="14" y2="12" />
                  </svg>
                  Documentos y Evidencias que Anexa
                </div>
                <p id="texto-nota-evidencia" style="font-size:0.78rem;color:#888;margin-bottom:16px;">
                  Cargue los documentos uno por uno, en el orden que se muestra a continuación.
                </p>

                <div id="evidencias-secuencia"></div>
              </div>
            </div>

            <div id="evidencia-no-aplica" style="display:none; text-align:center; padding: 40px 20px; background:#f7f9fd; border:1px solid #dde3ee; border-radius:6px; margin: 20px 0;">
              <h3 style="color:#1565c0; font-size:1.1rem; font-weight:700;">Este paso no aplica para Sugerencias</h3>
              <p style="color:#555; font-size:0.9rem;">Haga clic en Revisar solicitud para finalizar el proceso.</p>
            </div>

          </div>

          <div class="wizard-nav">
            <button class="btn-submit btn-outline-inst" onclick="anteriorPaso(5)">
              <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                <line x1="19" y1="12" x2="5" y2="12" />
                <polyline points="12 19 5 12 12 5" />
              </svg>
              Anterior
            </button>
            <button class="btn-submit" id="btn-sig-paso5" style="background: #1565c0;" onclick="siguientePaso(5)">
              Revisar solicitud
              <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                <line x1="5" y1="12" x2="19" y2="12" />
                <polyline points="12 5 19 12 12 19" />
              </svg>
            </button>
          </div>
        </div><!-- /paso-5 -->


        <!-- PASO 6 -->
        <div class="wizard-paso" id="paso-6" style="display:none;">
          <div class="form-header" id="paso6-cabecera" style="background: #1565c0 !important;">
            <div class="form-header-icon" id="paso6-icono"></div>
            <div>
              <h2 id="paso6-titulo"></h2>
              <p>Paso 6 de 6 · Revisión y Confirmación</p>
            </div>
          </div>
          <div class="form-body">

            <div class="form-section">
              <div class="form-section-title" id="revision-title-color">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                  <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z" />
                  <circle cx="12" cy="12" r="3" />
                </svg>
                Resumen de su Solicitud
              </div>
              <div style="background:#f7f9fd;border:1px solid #dde3ee;border-radius:6px;padding:8px;">
                <div class="row g-2">
                  <div class="col-md-6">
                    <div class="resumen-item">
                      <div class="resumen-etiqueta">Tipo de Trámite</div>
                      <div class="resumen-valor" id="res-tipo-tramite">—</div>
                    </div>
                  </div>
                  <div class="col-md-6">
                    <div class="resumen-item">
                      <div class="resumen-etiqueta">Fecha de Presentación</div>
                      <div class="resumen-valor" id="res-fecha">—</div>
                    </div>
                  </div>
                  <div class="col-12" id="resumen-bloque-contexto" style="display:none;">
                    <div class="resumen-item">
                      <div class="resumen-etiqueta">Contexto</div>
                      <div class="resumen-valor" id="res-contexto" style="font-weight:600; color:#1565c0;">—</div>
                    </div>
                  </div>
                  <div class="col-md-6">
                    <div class="resumen-item">
                      <div class="resumen-etiqueta">Solicitante</div>
                      <div class="resumen-valor" id="res-nombres">—</div>
                    </div>
                  </div>
                  <div class="col-md-6">
                    <div class="resumen-item">
                      <div class="resumen-etiqueta">Cédula / Documento</div>
                      <div class="resumen-valor" id="res-cedula">—</div>
                    </div>
                  </div>
                  <div class="col-md-6">
                    <div class="resumen-item">
                      <div class="resumen-etiqueta">Correo Electrónico</div>
                      <div class="resumen-valor" id="res-correo">—</div>
                    </div>
                  </div>
                  <div class="col-md-6">
                    <div class="resumen-item">
                      <div class="resumen-etiqueta">Teléfono Celular</div>
                      <div class="resumen-valor" id="res-telf">—</div>
                    </div>
                  </div>
                  <div class="col-12">
                    <div class="resumen-item">
                      <div class="resumen-etiqueta">Señalado / Instancia</div>
                      <div class="resumen-valor" id="res-senalado">—</div>
                    </div>
                  </div>
                  <div class="col-12">
                    <div class="resumen-item">
                      <div class="resumen-etiqueta">Resumen de los Hechos</div>
                      <div class="resumen-valor" id="res-hechos" style="font-size:0.8rem;line-height:1.6;">—</div>
                    </div>
                  </div>
                  <!-- NUEVOS CAMPOS: Ubicación Exacta y Evidencias Visuales -->
                  <div class="col-12">
                    <div class="resumen-item">
                      <div class="resumen-etiqueta">Ubicación Completa (Estado, Municipio, Parroquia)</div>
                      <div class="resumen-valor" id="res-ubicacion" style="font-weight:600; color:#1a2340;">—</div>
                    </div>
                  </div>
                  <div class="col-12">
                    <div class="resumen-item">
                      <div class="resumen-etiqueta">Documentos y Evidencias Cargadas</div>
                      <div class="resumen-valor" id="res-evidencias" style="font-size:0.85rem;line-height:1.6; background:#fff; padding:10px; border-radius:4px; border:1px solid #dce3ec;">—</div>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <div class="form-section">
              <div style="background:#cdcdcd;border:1px solid #a1a09e;border-radius:6px;
                        padding:16px 20px;font-size:0.82rem;color:#555;line-height:1.75;margin-bottom:18px;">
                <strong style="color:#1a2340;">Declaración Jurada:</strong><br>
                Declaro que los datos suministrados son fidedignos, y estoy en conocimiento de que cualquier
                falta o falsedad en los mismos involucra sanciones o la no aceptación de la solicitud, conforme
                a la normativa legal vigente.
              </div>
              <div class="form-group">
                <label style="display:flex;align-items:flex-start;gap:10px;cursor:pointer;">
                  <input type="checkbox" id="acepta-declaracion"
                    style="margin-top:2px;width:16px;height:16px;accent-color:#1565c0;flex-shrink:0;">
                  <span style="font-size:0.82rem;color:#1a2340;">
                    Acepto la declaración anterior y confirmo que la información proporcionada es veraz.
                  </span>
                </label>
                <span class="error-msg" id="acepta-decl-err">Debe aceptar la declaración.</span>
              </div>
            </div>

          </div>

          <div class="wizard-nav" style="justify-content:space-between;">
            <button class="btn-submit btn-outline-inst" onclick="anteriorPaso(6)">
              <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                <line x1="19" y1="12" x2="5" y2="12" />
                <polyline points="12 19 5 12 12 5" />
              </svg>
              Anterior
            </button>
            <div style="display:flex;gap:12px;flex-wrap:wrap;">
              <button class="btn-submit btn-pdf" id="btn-guardar-pdf" onclick="generarPDF()" style="display:none;background: #1565c0;">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                  <polyline points="8 17 12 21 16 17" />
                  <line x1="12" y1="12" x2="12" y2="21" />
                  <path d="M20.88 18.09A5 5 0 0 0 18 9h-1.26A8 8 0 1 0 3 16.29" />
                </svg>
                Guardar como PDF
              </button>
              <button class="btn-submit" id="btn-enviar-solicitud" style="background: #1565c0;" onclick="enviarSolicitud()">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                  <line x1="22" y1="2" x2="11" y2="13" />
                  <polygon points="22 2 15 22 11 13 2 9 22 2" />
                </svg>
                Enviar Solicitud
              </button>
            </div>
          </div>
        </div><!-- /paso-6 -->


        <!-- PANTALLA DE CONFIRMACIÓN / PLANILLA RESPONSIVA -->
        <div id="vista-confirmacion" style="display:none;">
          <div class="form-header" style="background:#e3f2fd; color:#0d47a1; border-bottom: 2px solid #1565c0;">
            <div class="form-header-icon" style="color:#1565c0;">
              <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                <polyline points="20 6 9 17 4 12" />
              </svg>
            </div>
            <div>
              <h2 style="color:#0d47a1; font-weight:700;">Planilla de Solicitud Registrada</h2>
            </div>
          </div>
          <div class="form-body" style="padding:20px 10px;">
            
            <div style="text-align:center; margin-bottom:25px; display:flex; flex-direction:column; align-items:center; justify-content:center;">
              <p style="font-size:0.88rem;color:#666;margin-bottom:5px; font-weight:bold;">Número de Expediente:</p>
              <div id="nro-expediente-display" style="background:#1a2340;color:#fff;display:inline-block;padding:8px 24px;
                        border-radius:6px;font-size:1.2rem;font-weight:700;letter-spacing:2px;font-family:monospace;">
                OAC-2026-0001
              </div>
            </div>

            <!-- PLANILLA DETALLADA -->
            <div class="print-card-box">
              <h5 class="print-section-title">1. Datos del Trámite</h5>
              <div class="print-row">
                <div class="print-label">Tipo de Trámite:</div>
                <div class="print-val" id="print-tipo-tramite">—</div>
              </div>
              <div class="print-row" id="print-fila-contexto" style="display:none;">
                <div class="print-label">Contexto del Trámite:</div>
                <div class="print-val" id="print-contexto" style="font-weight:600; color:#1565c0;">—</div>
              </div>
              <div class="print-row">
                <div class="print-label">Fecha de Registro:</div>
                <div class="print-val" id="print-fecha">—</div>
              </div>
            </div>

            <div class="print-card-box">
              <h5 class="print-section-title">2. Datos del Ciudadano Solicitante</h5>
              <div class="print-row">
                <div class="print-label">Apellidos y Nombres:</div>
                <div class="print-val" id="print-nombres">—</div>
              </div>
              <div class="print-row">
                <div class="print-label">Cédula / Documento de Identidad:</div>
                <div class="print-val" id="print-cedula">—</div>
              </div>
              <div class="print-row">
                <div class="print-label">Sexo:</div>
                <div class="print-val" id="print-sexo">—</div>
              </div>
              <div class="print-row">
                <div class="print-label">Fecha de Nacimiento y Edad:</div>
                <div class="print-val"><span id="print-fecha-nac"></span> (<span id="print-edad"></span> años)</div>
              </div>
              <div class="print-row">
                <div class="print-label">Estado Civil:</div>
                <div class="print-val" id="print-ecivil">—</div>
              </div>
              <div class="print-row">
                <div class="print-label">Nivel Educativo:</div>
                <div class="print-val" id="print-edu">—</div>
              </div>
              <div class="print-row">
                <div class="print-label">Profesión y Ocupación:</div>
                <div class="print-val"><span id="print-profesion"></span> / <span id="print-ocupacion"></span></div>
              </div>
              <div class="print-row">
                <div class="print-label">Correo Electrónico:</div>
                <div class="print-val" id="print-correo">—</div>
              </div>
              <div class="print-row">
                <div class="print-label">Teléfono Celular:</div>
                <div class="print-val" id="print-telf-cel">—</div>
              </div>
              <div class="print-row">
                <div class="print-label">Teléfono Habitación:</div>
                <div class="print-val" id="print-telf-hab">—</div>
              </div>
              <div class="print-row">
                <div class="print-label">Dirección Completa (Habitación):</div>
                <div class="print-val" id="print-direccion">—</div>
              </div>
            </div>

            <div class="print-card-box" id="print-seccion-senalado" style="display:none;">
              <h5 class="print-section-title">3. Identificación del Señalado</h5>
              <div class="print-row">
                <div class="print-label">Nombre(s) del Señalado(s) o Instancia:</div>
                <div class="print-val" id="print-senalados-lista">—</div>
              </div>
              <div class="print-row">
                <div class="print-label">Ubicación Geográfica del Señalado:</div>
                <div class="print-val" id="print-sen-ubicacion">—</div>
              </div>
              
              <!-- Sub-sección dinámica Proyecto -->
              <div id="print-seccion-proyecto" style="display:none; margin-top: 15px; border-top: 1px dashed #dce3ec; padding-top:10px;">
                 <h6 style="font-weight:bold; color:#1a2340; margin-bottom:10px;">Datos del Proyecto (Consulta Popular)</h6>
                 <div class="print-row">
                   <div class="print-label">Denominación del Proyecto:</div>
                   <div class="print-val" id="print-proy-nombre">—</div>
                 </div>
                 <div class="print-row">
                   <div class="print-label">Fecha de Aprobación:</div>
                   <div class="print-val" id="print-proy-fecha">—</div>
                 </div>
                 <div class="print-row">
                   <div class="print-label">Monto Estimado:</div>
                   <div class="print-val">Bs. <span id="print-proy-monto">—</span></div>
                 </div>
                 <div class="print-row">
                   <div class="print-label">Ente Financiador:</div>
                   <div class="print-val" id="print-proy-financiador">—</div>
                 </div>
                 <div class="print-row">
                   <div class="print-label">Código SITUR:</div>
                   <div class="print-val" id="print-proy-situr">—</div>
                 </div>
              </div>
            </div>

            <div class="print-card-box">
              <h5 class="print-section-title">4. Información de los Hechos</h5>
              <div class="print-row">
                <div class="print-label">Narración Circunstanciada:</div>
                <div class="print-val" id="print-hechos" style="text-align:justify; white-space: pre-wrap;">—</div>
              </div>
              <div class="print-row">
                <div class="print-label">¿Presentado ante otra instancia?</div>
                <div class="print-val" id="print-otra-instancia">—</div>
              </div>
            </div>

            <div class="print-card-box">
              <h5 class="print-section-title">5. Documentos y Evidencias Anexas</h5>
              <div class="print-row" style="border-bottom:none;">
                <div class="print-val" id="print-evidencias" style="width:100%; text-align:left;">—</div>
              </div>
            </div>

            <div style="display:flex;gap:12px;justify-content:center;flex-wrap:wrap; margin-top: 25px;">
              <a class="btn-submit btn-outline-inst" id="btn-descargar-planilla" href="#" target="_blank" rel="noopener">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                  <polyline points="8 17 12 21 16 17" />
                  <line x1="12" y1="12" x2="12" y2="21" />
                  <path d="M20.88 18.09A5 5 0 0 0 18 9h-1.26A8 8 0 1 0 3 16.29" />
                </svg>
                Descargar Planilla (PDF)
              </a>
              <button class="btn-submit" style="background: #1565c0;" onclick="location.reload()">
                Registrar Nueva Solicitud
              </button>
            </div>
          </div>
        </div><!-- /vista-confirmacion -->

        <datalist id="dl-instancias"></datalist>

      </div><!-- /vista-wizard -->

    </div><!-- /denuncias-inner -->
  </section>

  <footer>
    <div class="footer-inner">
      <div class="row g-4 pb-4" style="border-bottom: 1px solid rgba(255,255,255,0.1); margin-bottom: 24px;">
        <div class="col-lg-4 col-md-6 footer-brand">
          <img src="{{ asset('assets') }}/img/logo.jpeg" alt="Logo" style="width:56px;height:56px; border-radius: 25px;">
          <p style="margin-top:12px;">Contraloría del Municipio Páez — Estado Portuguesa, República Bolivariana de
            Venezuela. Órgano de Control Fiscal Municipal.</p>
          <div class="rif">RIF: G-20001628-0 · Acarigua, Estado Portuguesa</div>
        </div>
      </div>
      <div class="footer-bottom">
        <span>© 2026 Contraloría del Municipio Páez · Dirección General de Tecnología de Información</span>
        <a href="https://www.cgr.gob.ve" target="_blank">cgr.gob.ve</a>
      </div>
    </div>
  </footer>

  <script src="{{ asset('assets') }}/js/wizard.js"></script>
  
  <script>
    function mostrarConsultaTramite() {
      document.getElementById('vista-seleccion').style.display = 'none';
      document.getElementById('vista-estado-tramite').style.display = 'block';
    }

    function volverSeleccionDesdeEstado() {
      document.getElementById('vista-estado-tramite').style.display = 'none';
      document.getElementById('vista-seleccion').style.display = 'block';
      document.getElementById('resultado-estado-container').style.display = 'none';
      document.getElementById('input-codigo-consulta').value = '';
    }

    function consultarEstadoTramite() {
      const codigo = document.getElementById('input-codigo-consulta').value.trim().toUpperCase();
      const containerRes = document.getElementById('resultado-estado-container');
      
      if (!codigo) {
        alert('Por favor ingrese un código para consultar.');
        return;
      }

      containerRes.style.display = 'block';
      
      // Simulación de respuesta según el código ingresado o general
      document.getElementById('res-est-codigo').textContent = codigo;
      
      if (codigo === 'OAC-2026-0001' || codigo.includes('0001')) {
        document.getElementById('res-est-tipo').textContent = 'Denuncia';
        document.getElementById('res-est-fecha').textContent = '26/03/2026';
        document.getElementById('res-est-estado').textContent = 'En Fase de Investigación Preliminar';
        document.getElementById('res-est-estado').style.color = '#1565c0';
      } else {
        document.getElementById('res-est-tipo').textContent = 'Solicitud Ciudadana General';
        document.getElementById('res-est-fecha').textContent = new Date().toLocaleDateString();
        document.getElementById('res-est-estado').textContent = 'Recibido en taquilla / Pendiente de Revisión';
        document.getElementById('res-est-estado').style.color = '#2e7d32';
      }
    }

    function onTipoTramiteChange(radio) {
      const tipo = radio.value;
      const bloqueConsulta = document.getElementById('bloque-consulta-popular');
      const contSenaladoWrapper = document.getElementById('contenido-senalado-wrapper');
      const contSenaladoNoAplica = document.getElementById('senalado-no-aplica');
      const contEvidenciaWrapper = document.getElementById('contenido-evidencia-wrapper');
      const contEvidenciaNoAplica = document.getElementById('evidencia-no-aplica');
      const textoNotaEvidencia = document.getElementById('texto-nota-evidencia');
      const camposRequeridosCiudadano = document.querySelectorAll('#paso-2 .required');

      bloqueConsulta.style.display = 'none';
      contSenaladoWrapper.style.display = 'block';
      if(contSenaladoNoAplica) contSenaladoNoAplica.style.display = 'none';
      contEvidenciaWrapper.style.display = 'block';
      if(contEvidenciaNoAplica) contEvidenciaNoAplica.style.display = 'none';
      camposRequeridosCiudadano.forEach(el => el.style.display = 'inline');

      if (tipo === 'denuncia') {
        bloqueConsulta.style.display = 'block';
        textoNotaEvidencia.innerHTML = 'Cargue los documentos uno por uno, en el orden que se muestra a continuación. <strong>Para las denuncias, adjuntar evidencias probatorias es de carácter obligatorio.</strong>';
      } 
      else if (tipo === 'queja') {
        textoNotaEvidencia.innerHTML = 'Cargue los documentos que considere pertinentes. Para las quejas, adjuntar evidencia es <strong>opcional</strong>.';
      } 
      else if (tipo === 'reclamo' || tipo === 'peticion') {
        contSenaladoWrapper.style.display = 'none';
        if(contSenaladoNoAplica) contSenaladoNoAplica.style.display = 'block';
        textoNotaEvidencia.innerHTML = 'Cargue los anexos, identificaciones o documentos que acompañen su solicitud. (Opcional)';
      } 
      else if (tipo === 'sugerencia') {
        contSenaladoWrapper.style.display = 'none';
        if(contSenaladoNoAplica) contSenaladoNoAplica.style.display = 'block';
        contEvidenciaWrapper.style.display = 'none';
        if(contEvidenciaNoAplica) contEvidenciaNoAplica.style.display = 'none';
        camposRequeridosCiudadano.forEach(el => el.style.display = 'none');
      }
    }

    const originalSiguientePaso = window.siguientePaso;
    window.siguientePaso = function(pasoActual) {
      if (pasoActual === 1) {
        const radioSeleccionado = document.querySelector('input[name="tipo_tramite"]:checked');
        if (radioSeleccionado && radioSeleccionado.value === 'denuncia') {
          const consultaRadio = document.querySelector('input[name="es_consulta"]:checked');
          if (!consultaRadio) {
            document.getElementById('es-consulta-err').classList.add('visible');
            return;
          } else {
            document.getElementById('es-consulta-err').classList.remove('visible');
          }
        }
      }

      const radioSeleccionado = document.querySelector('input[name="tipo_tramite"]:checked');
      const tipo = radioSeleccionado ? radioSeleccionado.value : '';

      if (pasoActual === 3 && (tipo === 'reclamo' || tipo === 'peticion' || typeMatch(tipo))) {
        document.getElementById('err-paso3').style.display = 'none';
        document.getElementById('paso-3').style.display = 'none';
        document.getElementById('paso-4').style.display = 'block';
        actualizarBarraProgreso(4);
        actualizarAvisoContexto();
        return;
      }

      if (pasoActual === 5 && tipo === 'sugerencia') {
        document.getElementById('err-paso5').style.display = 'none';
        document.getElementById('paso-5').style.display = 'none';
        document.getElementById('paso-6').style.display = 'block';
        actualizarBarraProgreso(6);
        actualizarResumenFinal();
        return;
      }

      if (typeof originalSiguientePaso === 'function') {
        originalSiguientePaso(pasoActual);
      }
      
      if (pasoActual === 3) {
        actualizarAvisoContexto();
      }
      if (pasoActual === 5) {
        actualizarResumenFinal();
      }
    };

    function actualizarAvisoContexto() {
      const radioSeleccionado = document.querySelector('input[name="tipo_tramite"]:checked');
      const banner = document.getElementById('banner-contexto-aviso');
      const textoBadge = document.getElementById('texto-contexto-badge');
      
      if (radioSeleccionado && radioSeleccionado.value === 'denuncia') {
        const consultaRadio = document.querySelector('input[name="es_consulta"]:checked');
        if (consultaRadio) {
          banner.style.display = 'block';
          if (consultaRadio.value === 'si') {
            textoBadge.textContent = 'Relacionado con Proyecto de Consulta Popular Nacional';
          } else {
            textoBadge.textContent = 'Relacionado con Organismos e Institutos';
          }
        }
      } else {
        banner.style.display = 'none';
      }
    }

    function actualizarResumenFinal() {
      const radioSeleccionado = document.querySelector('input[name="tipo_tramite"]:checked');
      const bloqueResumenContexto = document.getElementById('resumen-bloque-contexto');
      const textoResumenContexto = document.getElementById('res-contexto');

      // 1. Validar Contexto de Consulta
      if (radioSeleccionado && radioSeleccionado.value === 'denuncia') {
        const consultaRadio = document.querySelector('input[name="es_consulta"]:checked');
        if (consultaRadio) {
          bloqueResumenContexto.style.display = 'block';
          textoResumenContexto.textContent = consultaRadio.value === 'si' ? 'Proyecto de Consulta Popular Nacional' : 'Organismo / Otra Institución';
        }
      } else {
        bloqueResumenContexto.style.display = 'none';
      }

      // 2. Poblar datos básicos para que no queden en blanco en el Paso 6
      const tipoTramite = radioSeleccionado ? radioSeleccionado.value.toUpperCase() : 'NO DEFINIDO';
      document.getElementById('res-tipo-tramite').textContent = tipoTramite;
      document.getElementById('res-fecha').textContent = new Date().toLocaleDateString();
      document.getElementById('res-nombres').textContent = `${document.getElementById('cit-primer-nombre').value} ${document.getElementById('cit-primer-apellido').value}`;
      document.getElementById('res-cedula').textContent = document.getElementById('cit-nro-doc').value;
      document.getElementById('res-correo').textContent = document.getElementById('cit-correo').value;
      document.getElementById('res-telf').textContent = document.getElementById('cit-telf-cel-num').value;
      document.getElementById('res-hechos').textContent = document.getElementById('narracion').value || 'Sin descripción';

      // 3. Poblar Ubicación Absoluta (Ciudad/Estado, Municipio, Parroquia)
      const parr = document.getElementById('cit-parroquia').value || 'No especificó';
      const mun = document.getElementById('cit-municipio').value || 'Páez';
      const ciu = document.getElementById('cit-ciudad').value || 'Acarigua/Portuguesa';
      const dir = document.getElementById('cit-direccion').value || '';
      
      const contenedorUbicacion = document.getElementById('res-ubicacion');
      if (contenedorUbicacion) {
         contenedorUbicacion.innerHTML = `<strong>Ciudad/Estado:</strong> ${ciu} <br><strong>Municipio:</strong> ${mun} <br><strong>Parroquia:</strong> ${parr} <br><strong>Dirección:</strong> ${dir}`;
      }

      // 4. Mapeo y Maqueta Visual de Evidencias Cargadas
      const inputsEvidencias = document.querySelectorAll('input[type="file"]');
      let listaArchivosHtml = '';
      let totalArchivos = 0;

      inputsEvidencias.forEach(input => {
        if (input.files && input.files.length > 0) {
          let tipoDocumento = "Anexo";
          const parentGroup = input.closest('.form-group') || input.parentElement;
          if (parentGroup) {
              const labelElem = parentGroup.querySelector('label') || parentGroup.querySelector('.form-label');
              if (labelElem && labelElem.innerText.trim() !== '') {
                  tipoDocumento = labelElem.innerText.replace(/\*/g, '').trim();
              }
          }
          for (let i = 0; i < input.files.length; i++) {
            totalArchivos++;
            listaArchivosHtml += `<div style="margin-bottom:4px;">✅ <strong>${tipoDocumento}:</strong> <span style="color:#555;">${input.files[i].name}</span></div>`;
          }
        }
      });

      const contenedorResEvidencias = document.getElementById('res-evidencias');
      if (contenedorResEvidencias) {
          if (totalArchivos > 0) {
            contenedorResEvidencias.innerHTML = `<div style="color:#2e7d32;font-weight:bold;margin-bottom:8px;">Se anexaron ${totalArchivos} documento(s) listos para enviar:</div>` + listaArchivosHtml;
          } else {
            contenedorResEvidencias.innerHTML = '<div style="color:#c62828; font-weight:600;">⚠️ No se anexaron evidencias ni documentos en el sistema.</div>';
          }
      }
    }

    // Función que recoge ABSOLUTAMENTE TODOS los datos ingresados
    // Envía la solicitud al servidor; si se guarda, muestra la confirmación
    // con el número de expediente real y el enlace a la planilla en PDF.
    window.enviarSolicitud = async function() {
      const acepta = document.getElementById('acepta-declaracion');
      if (acepta && !acepta.checked) {
        document.getElementById('acepta-decl-err').classList.add('visible');
        acepta.scrollIntoView({ behavior: 'smooth', block: 'center' });
        return;
      }

      const val = id => (document.getElementById(id)?.value || '').trim();
      const radio = name => document.querySelector(`input[name="${name}"]:checked`)?.value || '';

      const senalados = [];
      if (!document.getElementById('senalado-no-aplica')?.checked) {
        document.querySelectorAll('#lista-senalados .senalado-card').forEach(card => {
          const tipo = card.querySelector('[data-campo="tipo-senalado"]:checked');
          if (!tipo) return;
          const campos = {};
          card.querySelectorAll('[data-campo]').forEach(el => {
            if (el.dataset.campo !== 'tipo-senalado' && el.value.trim()) campos[el.dataset.campo] = el.value.trim();
          });
          senalados.push({ tipo: tipo.value, campos });
        });
      }

      const datos = {
        tipo_tramite: radio('tipo_tramite'),
        es_consulta: radio('tipo_tramite') === 'denuncia' && radio('es_consulta') === 'si',
        ciudadano: {
          tipo_doc: val('cit-tipo-doc'), nro_doc: val('cit-nro-doc'),
          primer_nombre: val('cit-primer-nombre'), segundo_nombre: val('cit-segundo-nombre'),
          primer_apellido: val('cit-primer-apellido'), segundo_apellido: val('cit-segundo-apellido'),
          sexo: val('cit-sexo'), fecha_nac: val('cit-fecha-nac'), lugar_nac: val('cit-lugar-nac'),
          estado_civil: val('cit-ecivil'), nivel_educativo: val('cit-edu'),
          profesion: val('cit-profesion'), ocupacion: val('cit-ocupacion'),
          correo: val('cit-correo'),
          telf_cel: val('cit-telf-cel-cod') && val('cit-telf-cel-num') ? val('cit-telf-cel-cod') + '-' + val('cit-telf-cel-num') : '',
          telf_hab: val('cit-telf-hab-cod') && val('cit-telf-hab-num') ? val('cit-telf-hab-cod') + '-' + val('cit-telf-hab-num') : '',
          telf_trab: val('cit-telf-trab-cod') && val('cit-telf-trab-num') ? val('cit-telf-trab-cod') + '-' + val('cit-telf-trab-num') : '',
          direccion: val('cit-direccion'), parroquia: val('cit-parroquia'),
          municipio: val('cit-municipio') || 'Páez', ciudad: val('cit-ciudad'),
        },
        senalados,
        ubicacion_senalado: val('sen-ubicacion'),
        proyecto: {
          nombre: val('proy-nombre'), fecha: val('proy-fecha'), monto: val('proy-monto'),
          financiador: val('proy-financiador'), situr: val('proy-situr'),
        },
        narracion: val('narracion'),
        otra_instancia: radio('otra_instancia') === 'si',
        cual_instancia: val('cual-instancia'),
        acepta_declaracion: true,
      };

      const form = new FormData();
      form.append('datos', JSON.stringify(datos));
      Object.entries(window.archivosPorDocumento || {}).forEach(([tipo, archivos]) => {
        archivos.forEach(archivo => form.append(`evidencias[${tipo}][]`, archivo));
      });

      const boton = document.getElementById('btn-enviar-solicitud');
      if (boton) boton.disabled = true;
      try {
        const resp = await fetch(@json(route('denuncias.store')), {
          method: 'POST',
          headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Accept': 'application/json',
          },
          body: form,
        });
        const json = await resp.json();
        if (!resp.ok) {
          const errores = json.errors ? Object.values(json.errors).flat() : [json.message || 'Error desconocido'];
          alert('No se pudo registrar la solicitud:\n\n• ' + errores.join('\n• '));
          return;
        }
        document.getElementById('nro-expediente-display').textContent = json.case_number;
        document.getElementById('btn-descargar-planilla').href = json.planilla_url;
        mostrarConfirmacion();
      } catch (e) {
        alert('No se pudo conectar con el servidor. Intente de nuevo.');
      } finally {
        if (boton) boton.disabled = false;
      }
    };

    function mostrarConfirmacion() {
      // 1. Datos del Trámite y Contexto
      const radioSeleccionado = document.querySelector('input[name="tipo_tramite"]:checked');
      const tipoTramite = radioSeleccionado ? radioSeleccionado.value : '';
      if (radioSeleccionado) {
        document.getElementById('print-tipo-tramite').textContent = tipoTramite.toUpperCase();
        
        if (tipoTramite === 'denuncia') {
          const consultaRadio = document.querySelector('input[name="es_consulta"]:checked');
          if (consultaRadio) {
            document.getElementById('print-fila-contexto').style.display = 'flex';
            document.getElementById('print-contexto').textContent = consultaRadio.value === 'si' ? 'Proyecto de Consulta Popular Nacional' : 'Organismo u otra institución (No aplica a Consulta)';
          }
        } else {
          document.getElementById('print-fila-contexto').style.display = 'none';
        }
      }
      
      // 2. Datos del Solicitante Completos
      const pNombre = document.getElementById('cit-primer-nombre').value || '';
      const sNombre = document.getElementById('cit-segundo-nombre').value || '';
      const pApellido = document.getElementById('cit-primer-apellido').value || '';
      const sApellido = document.getElementById('cit-segundo-apellido').value || '';
      document.getElementById('print-nombres').textContent = `${pNombre} ${sNombre} ${pApellido} ${sApellido}`.replace(/\s+/g, ' ').trim();

      const tDoc = document.getElementById('cit-tipo-doc').options[document.getElementById('cit-tipo-doc').selectedIndex]?.text || '';
      const nDoc = document.getElementById('cit-nro-doc').value || '';
      document.getElementById('print-cedula').textContent = `${tDoc} - ${nDoc}`;

      const sexoSelect = document.getElementById('cit-sexo');
      document.getElementById('print-sexo').textContent = sexoSelect.options[sexoSelect.selectedIndex]?.text || 'No indicó';
      
      document.getElementById('print-fecha-nac').textContent = document.getElementById('cit-fecha-nac').value || 'No indicó';
      document.getElementById('print-edad').textContent = document.getElementById('cit-edad').value || '--';
      document.getElementById('print-ecivil').textContent = document.getElementById('cit-ecivil').value || 'No indicó';

      document.getElementById('print-edu').textContent = document.getElementById('cit-edu').value || 'No especificó';
      document.getElementById('print-profesion').textContent = document.getElementById('cit-profesion').value || 'No indicó';
      document.getElementById('print-ocupacion').textContent = document.getElementById('cit-ocupacion').value || 'No indicó';

      document.getElementById('print-correo').textContent = document.getElementById('cit-correo').value || '';
      
      const codTelf = document.getElementById('cit-telf-cel-cod').value || '';
      const numTelf = document.getElementById('cit-telf-cel-num').value || '';
      document.getElementById('print-telf-cel').textContent = codTelf && numTelf ? `${codTelf}-${numTelf}` : 'No posee';

      const codHab = document.getElementById('cit-telf-hab-cod').value || '';
      const numHab = document.getElementById('cit-telf-hab-num').value || '';
      document.getElementById('print-telf-hab').textContent = codHab && numHab ? `${codHab}-${numHab}` : 'No posee';

      const parr = document.getElementById('cit-parroquia').value || '';
      const dir = document.getElementById('cit-direccion').value || '';
      const mun = document.getElementById('cit-municipio').value || 'Páez';
      const ciu = document.getElementById('cit-ciudad').value || 'Acarigua/Portuguesa';
      document.getElementById('print-direccion').textContent = `${dir}, Parroquia ${parr}. Municipio ${mun} (${ciu})`;

      // 3. Señalados / Proyecto
      const seccionSenalado = document.getElementById('print-seccion-senalado');
      if (tipoTramite === 'denuncia' || tipoTramite === 'queja') {
        seccionSenalado.style.display = 'block';
        
        let textoSenalados = '';
        const inputsSenalados = document.querySelectorAll('#lista-senalados input[type="text"], #lista-senalados select');
        if(inputsSenalados.length > 0) {
            inputsSenalados.forEach(inp => { if(inp.value) textoSenalados += `${inp.value} | `; });
        } else {
            textoSenalados = "Registro dinámico guardado en sistema.";
        }
        document.getElementById('print-senalados-lista').textContent = textoSenalados.replace(/\|\s*$/, '');
        document.getElementById('print-sen-ubicacion').textContent = document.getElementById('sen-ubicacion').value || 'No indicó';

        const consultaRadio = document.querySelector('input[name="es_consulta"]:checked');
        const seccionProyecto = document.getElementById('print-seccion-proyecto');
        if (tipoTramite === 'denuncia' && consultaRadio && consultaRadio.value === 'si') {
          seccionProyecto.style.display = 'block';
          document.getElementById('print-proy-nombre').textContent = document.getElementById('proy-nombre').value || 'No indicó';
          document.getElementById('print-proy-fecha').textContent = document.getElementById('proy-fecha').value || 'No indicó';
          document.getElementById('print-proy-monto').textContent = document.getElementById('proy-monto').value || '0.00';
          document.getElementById('print-proy-financiador').textContent = document.getElementById('proy-financiador').value || 'No indicó';
          document.getElementById('print-proy-situr').textContent = document.getElementById('proy-situr').value || 'No indicó';
        } else {
          seccionProyecto.style.display = 'none';
        }
      } else {
        seccionSenalado.style.display = 'none';
      }

      // 4. Información de los Hechos
      document.getElementById('print-hechos').textContent = document.getElementById('narracion').value || '';
      
      const instaRadio = document.querySelector('input[name="otra_instancia"]:checked');
      if (instaRadio) {
        if (instaRadio.value === 'si') {
            const instNombre = document.getElementById('cual-instancia').value || 'Instancia no especificada';
            document.getElementById('print-otra-instancia').textContent = `Presentado previamente en: ${instNombre}`;
        } else {
            document.getElementById('print-otra-instancia').textContent = 'Trámite originario (No se ha presentado en otra instancia)';
        }
      } else {
        document.getElementById('print-otra-instancia').textContent = 'No indicó';
      }

      document.getElementById('print-fecha').textContent = new Date().toLocaleDateString();

      // 5. Corrección de Evidencias (Buscando la etiqueta lógica)
      const inputsEvidencias = document.querySelectorAll('input[type="file"]');
      let listaArchivosHtml = '';
      let totalArchivos = 0;

      inputsEvidencias.forEach(input => {
        if (input.files && input.files.length > 0) {
          let tipoDocumento = "Documento/Anexo";
          const parentGroup = input.closest('.form-group') || input.parentElement;
          if (parentGroup) {
              const labelElem = parentGroup.querySelector('label') || parentGroup.querySelector('.form-label');
              if (labelElem && labelElem.innerText.trim() !== '') {
                  tipoDocumento = labelElem.innerText.replace(/\*/g, '').trim();
              }
          }

          for (let i = 0; i < input.files.length; i++) {
            totalArchivos++;
            listaArchivosHtml += `• <strong>${tipoDocumento}:</strong> ${input.files[i].name}<br>`;
          }
        }
      });

      const contenedorEvidenciasPrint = document.getElementById('print-evidencias');
      if (totalArchivos > 0) {
        contenedorEvidenciasPrint.innerHTML = `<p style="margin-bottom:8px;">Se cargaron ${totalArchivos} evidencia(s) exitosamente:</p> ${listaArchivosHtml}`;
      } else {
        contenedorEvidenciasPrint.textContent = 'El ciudadano no cargó documentos probatorios o anexos para este trámite.';
      }

      const paso6 = document.getElementById('paso-6');
      const vistaConfirmacion = document.getElementById('vista-confirmacion');
      const barraProgreso = document.getElementById('barra-progreso');

      if (paso6) paso6.style.display = 'none';
      if (vistaConfirmacion) vistaConfirmacion.style.display = 'block';
      if (barraProgreso) barraProgreso.style.display = 'none';

      window.scrollTo({ top: 0, behavior: 'smooth' });
    };

    function typeMatch(t) {
      return t === 'reclamo' || t === 'peticion' || t === 'sugerencia';
    }

    function actualizarBarraProgreso(pasoDestino) {
      const items = document.querySelectorAll('.paso-item');
      items.forEach((item, index) => {
        if (index < pasoDestino - 1) {
          item.classList.add('completado');
          item.classList.remove('activo');
        } else if (index === pasoDestino - 1) {
          item.classList.add('activo');
          item.classList.remove('completado');
        } else {
          item.classList.remove('activo', 'completado');
        }
      });
    }
  </script>
</body>

</html>