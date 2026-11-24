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

  <!-- Leaflet para Geolocalización y Google Maps Widget -->
  <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="" />
  <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>

  <style>
    /* Estilos adicionales para justificar textos institucionales */
    p, .aviso-anonimato div, .tramite-card-desc, .resumen-valor,
    .nota-info, .aviso-menor-edad, #banner-contexto-aviso, #banner-tramite-seleccionado,
    .modal-body p, .modal-body div, .modal-body ul, .modal-body li {
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
      background: #0b253e !important;
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
      background: transparent !important;
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

    /* Adaptación responsiva para la tabla de Datos Demográficos Complementarios */
@media (max-width: 768px) {
  /* Quita el scroll contenedor en dispositivos móviles */
  .form-group div[style*="overflow-x:auto"] {
    overflow-x: visible !important;
  }

  /* Transforma la tabla y sus filas en contenedores flexibles/bloque */
  .tabla-demografica,
  .tabla-demografica tbody,
  .tabla-demografica tr {
    display: block !important;
    width: 100% !important;
  }

  /* Oculta los encabezados tradicionales de la tabla */
  .tabla-demografica thead {
    display: none !important;
  }
  .tabla-demografica td {
    display: block !important;
    width: 100% !important;
    padding: 6px 0 !important;
    box-sizing: border-box;
  }

  .tabla-demografica td:nth-child(1)::before {
    content: "Lugar de Nacimiento";
    display: block;
    font-weight: 600;
    font-size: 0.82rem;
    color: #1a2340;
    margin-bottom: 4px;
  }

  .tabla-demografica td:nth-child(2)::before {
    content: "Nivel Educativo";
    display: block;
    font-weight: 600;
    font-size: 0.82rem;
    color: #1a2340;
    margin-bottom: 4px;
  }

  .tabla-demografica td:nth-child(3)::before {
    content: "Profesión";
    display: block;
    font-weight: 600;
    font-size: 0.82rem;
    color: #1a2340;
    margin-bottom: 4px;
  }

  .tabla-demografica td:nth-child(4)::before {
    content: "Ocupación";
    display: block;
    font-weight: 600;
    font-size: 0.82rem;
    color: #1a2340;
    margin-bottom: 4px;
  }
}

    @keyframes fadeInPopover {
      from { opacity: 0; transform: translateY(-4px); }
      to { opacity: 1; transform: translateY(0); }
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
        <div style="text-align: center; margin-bottom: 20px; display: flex; justify-content: center; gap: 15px; flex-wrap: wrap;">
          <button class="btn-submit" style="font-size: 1.1rem; padding: 12px 32px; background: #022139; border: 1px solid #4d7f99;" onclick="mostrarConsultaTramite()">
            Estado de Trámite
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
            <div class="print-row"><div class="print-label">Estado Actual:</div><div class="print-val" id="res-est-estado" style="font-weight: bold; color: #2e7d32;">-</div></div>
            <div class="print-row" id="fila-est-observacion"><div class="print-label">Observación:</div><div class="print-val" id="res-est-observacion" style="white-space: pre-wrap;">-</div></div>

            <!-- Fila Geolocalización Señalado -->
            <div class="print-row" id="fila-est-ubicacion" style="display:none;">
              <div class="print-label">Ubicación Señalado:</div>
              <div class="print-val" id="res-est-ubicacion">
                <span id="res-est-ubicacion-texto">-</span>
                <a id="res-est-gmaps-link" href="#" target="_blank" rel="noopener noreferrer" style="margin-left:8px; color:#1565c0; font-weight:600; text-decoration:none; display:none;">Ver en Google Maps ↗</a>
              </div>
            </div>

            <!-- Módulo Regla de Negocio: Lapso de Anticipación para Confirmar Datos -->
            <div id="bloque-confirmacion-denunciante" style="margin-top:16px; display:none; background:#fff; border:1px solid #bfdbfe; border-left:4px solid #2563eb; border-radius:6px; padding:14px; text-align:justify;">
              <div style="font-weight:700; color:#1e40af; font-size:0.92rem; margin-bottom:4px;" id="titulo-confirmacion-lapso">
                Lapso de Anticipación para Confirmar Datos
              </div>
              <div style="font-size:0.86rem; color:#1e3a8a; margin-bottom:10px;" id="texto-confirmacion-lapso">
                —
              </div>
              <div id="acciones-confirmacion-datos" style="display:none;">
                <div style="margin-bottom:8px;">
                  <textarea id="input-rectificacion-denunciante" class="form-control" rows="2" style="font-size:0.84rem;" placeholder="Si desea rectificar algún dato antes de validar la conformidad, especifíquelo aquí (opcional)..."></textarea>
                </div>
                <button type="button" class="btn btn-sm btn-primary" onclick="enviarConfirmacionDatos()" id="btn-confirmar-datos" style="background:#1565c0; font-weight:600;">
                  Confirmar conformidad de mis datos
                </button>
              </div>
              <div id="feedback-confirmacion-datos" style="font-size:0.84rem; font-weight:600; color:#16a34a; margin-top:8px; display:none;"></div>
            </div>

            <!-- Respuestas Institucionales de la Contraloría -->
            <div id="fila-respuesta-ciudadano" style="margin-top:16px; display:none; text-align:justify;">
              <div style="font-weight:bold; color:#1565c0; border-top:1px solid #dce3ec; padding-top:12px; margin-bottom:8px; font-size:0.95rem;">Respuesta Institucional de la Contraloría</div>
              <div id="res-respuestas-ciudadano"></div>
            </div>
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

        <!-- BANNER DE TRÁMITE SELECCIONADO (Visible en pasos 2 a 6) -->
        <div id="banner-tramite-seleccionado" style="display:none; width: 100%; box-sizing: border-box; margin: 0 0 16px 0; background: #e8f0fe; border-left: 4px solid #1565c0; border-radius: 6px; padding: 10px 16px; box-shadow: 0 1px 3px rgba(0,0,0,0.05); text-align: justify;">
          <div style="font-size: 0.9rem; color: #1a365d; text-align: justify;">
            Está realizando una solicitud de tipo: <strong id="texto-tramite-seleccionado" style="color: #1565c0; text-transform: uppercase;">-</strong>
          </div>
        </div>

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
            <button class="btn-submit" id="btn-sig-paso1" style="background: #1565c0;" onclick="mostrarModalConfirmacion(1)">
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
                    <span class="error-msg" id="cit-segundo-nombre-err">Solo se permiten letras.</span>
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
                    <span class="error-msg" id="cit-segundo-apellido-err">Solo se permiten letras.</span>
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
                    <div style="position: relative; display: flex; align-items: center; gap: 8px;">
                      <input type="date" id="cit-fecha-nac" class="form-control" onchange="actualizarEdadComputada(this)" style="flex: 1; min-width: 0;">
                      <button type="button" id="btn-info-calendario" 
                        onclick="toggleTooltipCalendario(event)" 
                        title="Indicación sobre el calendario"
                        aria-label="Ayuda para seleccionar fecha"
                        style="width: 34px; height: 34px; min-width: 34px; border-radius: 50%; border: 1.5px solid #1565c0; background: #e8f0fe; color: #1565c0; display: inline-flex; align-items: center; justify-content: center; cursor: pointer; padding: 0; transition: all 0.2s ease; box-shadow: 0 1px 3px rgba(0,0,0,0.08);"
                        onmouseover="this.style.background='#1565c0'; this.style.color='#ffffff';"
                        onmouseout="if(!document.getElementById('popover-info-calendario') || document.getElementById('popover-info-calendario').style.display !== 'block'){ this.style.background='#e8f0fe'; this.style.color='#1565c0'; }">
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                          <circle cx="12" cy="12" r="10"></circle>
                          <line x1="12" y1="16" x2="12" y2="12"></line>
                          <line x1="12" y1="8" x2="12.01" y2="8"></line>
                        </svg>
                      </button>

                      <!-- Popover flotante informativo del calendario -->
                      <div id="popover-info-calendario" style="display: none; position: absolute; top: calc(100% + 8px); right: 0; width: 245px; background: #ffffff; border: 1px solid #bfdbfe; border-radius: 8px; padding: 10px 12px; box-shadow: 0 6px 18px rgba(15, 23, 42, 0.15); z-index: 100; text-align: left; animation: fadeInPopover 0.2s ease-out;">
                        <div style="display: flex; align-items: flex-start; gap: 8px;">
                          <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#1565c0" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" style="flex-shrink: 0; margin-top: 2px;">
                            <circle cx="12" cy="12" r="10"></circle>
                            <line x1="12" y1="16" x2="12" y2="12"></line>
                            <line x1="12" y1="8" x2="12.01" y2="8"></line>
                          </svg>
                          <div style="font-size: 0.78rem; color: #1e293b; line-height: 1.35;">
                            Seleccione primero el <strong>Año</strong>, luego el <strong>Mes</strong> y el <strong>Día</strong> en el desplegable.
                          </div>
                          <button type="button" onclick="cerrarTooltipCalendario(event)" style="background: none; border: none; padding: 0; cursor: pointer; color: #94a3b8; font-size: 16px; line-height: 1; margin-left: auto;" title="Cerrar">&times;</button>
                        </div>
                      </div>
                    </div>

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
                      <option value="Otro">Otro</option>
                    </select>
                    <input type="text" id="cit-ecivil-otro" class="form-control mt-1" placeholder="Especifique estado civil..." style="display:none;" maxlength="40">
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
                           {{-- Selectores en cascada: Estado → Municipio → Parroquia (Lugar de Nacimiento) --}}
                           <select id="cit-lugar-nac-estado" class="form-select mb-1" onchange="geoActualizarMunicipio()">
                             <option value="">-- Estado --</option>
                             <option value="Extranjero">Extranjero (nacido en el exterior)</option>
                             {{-- Los estados de Venezuela se inyectan dinámicamente por JS --}}
                           </select>
                           <select id="cit-lugar-nac-municipio" class="form-select mb-1" disabled onchange="geoActualizarParroquia()">
                             <option value="">-- Municipio --</option>
                           </select>
                           <select id="cit-lugar-nac-parroquia" class="form-select" disabled onchange="geoActualizarHiddenNac()">
                             <option value="">-- Parroquia --</option>
                           </select>
                           {{-- Campo oculto que agrupa el valor final enviado al backend --}}
                           <input type="hidden" id="cit-lugar-nac" value="">
                           <span class="error-msg" id="cit-lugar-nac-err">Seleccione el lugar de nacimiento.</span>
                         </td>
                        <td>
                          <select id="cit-edu" class="form-select">
                            <option value="">-- Seleccione --</option>
                            <option>Sin instrucción / Ninguno</option>
                            <option>Educación Inicial / Preescolar</option>
                            <option>Primaria</option>
                            <option>Secundaria / Bachillerato</option>
                            <option>Técnico Medio</option>
                            <option>Técnico Superior Universitario (TSU)</option>
                            <option>Universitario</option>
                            <option>Postgrado</option>
                            <option>Especialización</option>
                            <option>Maestría</option>
                            <option>Doctorado</option>
                            <option>Formación Profesional / Oficios</option>
                          </select>
                        </td>
                        <td>
                          <select id="cit-profesion" class="form-select">
                            @foreach(\App\Models\Profesion::nombresParaSelect() as $profesion)
                              <option value="{{ $profesion }}" @selected($profesion === \App\Models\Profesion::NINGUNA)>{{ $profesion }}</option>
                            @endforeach
                            <option value="Otra">Otra</option>
                          </select>
                          <input type="text" id="cit-profesion-otra" class="form-control mt-1" placeholder="Especifique su profesión..." style="display:none;" maxlength="80">
                          <span class="error-msg" id="cit-profesion-err">Seleccione una profesión.</span>
                        </td>
                        <td>
                          <input type="text" id="cit-ocupacion" class="form-control" placeholder="Ej. Comerciante">
                          <span class="error-msg" id="cit-ocupacion-err">Solo se permiten letras.</span>
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
                {{-- 1. Dirección de Habitación con Mapa Interactivo y Autocompletado --}}
                <div class="col-12">
                  <div class="form-group">
                    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:4px; flex-wrap:wrap; gap:6px;">
                      <label class="form-label" style="margin-bottom:0;">Dirección de Habitación <span class="required" style="color:#1565c0;">*</span></label>
                      <div style="display:flex; gap:6px;">
                        <button type="button" class="btn btn-sm btn-outline-secondary" style="white-space:nowrap; font-size:0.8rem; padding:3px 10px; border-radius:12px;" onclick="buscarDireccionCiudadanoEnMapa()" title="Ubicar dirección escrita en el mapa">
                          Buscar en mapa
                        </button>
                        <button type="button" class="btn btn-sm btn-outline-primary" style="white-space:nowrap; font-size:0.8rem; padding:3px 10px; border-radius:12px;" onclick="obtenerUbicacionCiudadanoGPS()" title="Usar GPS actual del dispositivo">
                          Mi ubicación
                        </button>
                      </div>
                    </div>
                    <input type="text" id="cit-direccion" class="form-control"
                      placeholder="Calle, Avenida, Urbanización, Casa/Edificio...">
                    <span class="error-msg" id="cit-direccion-err">Ingrese su dirección completa.</span>

                    <!-- Componente de Mapa Interactivo para la Dirección del Ciudadano -->
                    <div id="contenedor-mapa-ciudadano" style="margin-top:10px; background:#fff; border:1px solid #dce3ec; border-radius:8px; overflow:hidden; box-shadow:0 1px 4px rgba(0,0,0,0.06);">
                      <div style="background:#f1f5f9; padding:8px 14px; font-size:0.82rem; color:#475569; display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:6px; border-bottom:1px solid #e2e8f0;">
                        <span><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#1565c0" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" style="vertical-align:middle;margin-right:4px;"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg> Haga clic en el mapa o arrastre el marcador para autocompletar Ciudad, Municipio y Parroquia.</span>
                        <a id="btn-abrir-google-maps-ciudadano" href="https://maps.google.com" target="_blank" rel="noopener noreferrer" style="color:#1565c0; font-weight:600; text-decoration:none; font-size:0.8rem; display:none;">
                          Abrir en Google Maps 
                        </a>
                      </div>
                      <div id="mapa-ciudadano" style="height:250px; width:100%; background:#e5e3df;"></div>
                      <div style="background:#f8fafc; padding:6px 14px; font-size:0.8rem; color:#64748b; display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:6px;">
                        <div>
                          Coordenadas: Lat <strong id="cit-lat-texto" style="color:#0f172a;">—</strong>, Lng <strong id="cit-lng-texto" style="color:#0f172a;">—</strong>
                        </div>
                        <span id="cit-geo-status" style="font-size:0.78rem; color:#0284c7; font-weight:500;">Haga clic en el mapa para fijar su ubicación</span>
                      </div>
                    </div>
                    <span class="error-msg" id="cit-mapa-err" style="margin-top:6px;">Debe seleccionar una ubicación válida dentro del territorio nacional de Venezuela.</span>
                    <input type="hidden" id="cit-lat" name="cit_lat" value="">
                    <input type="hidden" id="cit-lng" name="cit_lng" value="">
                  </div>
                </div>

                {{-- 2. Ciudad (primero de la ubicación geográfica) --}}
                <div class="col-md-4">
                  <div class="form-group">
                    <label class="form-label">Ciudad <span class="required" style="color:#1565c0;">*</span></label>
                    <select id="cit-ciudad" class="form-select">
                      <option value="">-- Seleccione Ciudad --</option>
                    </select>
                    <input type="text" id="cit-ciudad-otra" class="form-control mt-1" placeholder="Especifique su ciudad..." style="display:none;" maxlength="80">
                    <span class="error-msg" id="cit-ciudad-err">Seleccione o indique su ciudad.</span>
                  </div>
                </div>

                {{-- 3. Municipio (luego) --}}
                <div class="col-md-4">
                  <div class="form-group">
                    <label class="form-label">Municipio <span class="required" style="color:#1565c0;">*</span></label>
                    <select id="cit-municipio" class="form-select">
                      <option value="">-- Seleccione Municipio --</option>
                    </select>
                    <input type="text" id="cit-municipio-otro" class="form-control mt-1" placeholder="Especifique municipio..." style="display:none;" maxlength="80">
                    <span class="error-msg" id="cit-municipio-err">Seleccione un municipio.</span>
                  </div>
                </div>

                {{-- 4. Parroquia (después) --}}
                <div class="col-md-4">
                  <div class="form-group">
                    <label class="form-label">Parroquia <span class="required" style="color:#1565c0;">*</span></label>
                    <select id="cit-parroquia" class="form-select">
                      <option value="">-- Seleccione Parroquia --</option>
                    </select>
                    <input type="text" id="cit-parroquia-otra" class="form-control mt-1" placeholder="Especifique parroquia..." style="display:none;" maxlength="80">
                    <span class="error-msg" id="cit-parroquia-err">Seleccione una parroquia.</span>
                  </div>
                </div>

                {{-- 5. Correo electrónico y en la confirmación el autocompletado --}}
                <div class="col-md-6">
                  <div class="form-group">
                    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:4px;">
                      <label class="form-label" style="margin-bottom:0;">
                        Correo Electrónico <span class="required" style="color:#1565c0;">*</span>
                      </label>
                      <button type="button" class="btn btn-link p-0 text-decoration-none" style="font-size:0.75rem; color:#1565c0; display:inline-flex; align-items:center; gap:4px; font-weight:500;" data-bs-toggle="modal" data-bs-target="#modalAvisoGmail" title="Ver motivo legal por el cual debe ser Gmail">
                        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                          <circle cx="12" cy="12" r="10"></circle>
                          <line x1="12" y1="16" x2="12" y2="12"></line>
                          <line x1="12" y1="8" x2="12.01" y2="8"></line>
                        </svg>
                        <span>Debe ser Gmail</span>
                      </button>
                    </div>
                    <div class="input-group">
                      <input type="email" id="cit-correo" class="form-control" placeholder="ejemplo@gmail.com">
                      <button type="button" class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#modalAvisoGmail" title="Abrir ventanita informativa: El correo debe ser Gmail por ley" style="border-color:#1565c0; color:#1565c0; background:#f0f7ff; display:inline-flex; align-items:center; justify-content:center; padding: 0 12px;">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-label="Ventanita informativa">
                          <rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect>
                          <line x1="3" y1="9" x2="21" y2="9"></line>
                          <line x1="9" y1="21" x2="9" y2="9"></line>
                        </svg>
                      </button>
                    </div>
                    <span class="error-msg" id="cit-correo-err">El correo electrónico a utilizar debe ser exclusivamente Gmail (@gmail.com).</span>
                  </div>
                </div>
                <div class="col-md-6">
                  <div class="form-group">
                    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:4px;">
                      <label class="form-label" style="margin-bottom:0;">Confirmar Correo <span class="required" style="color:#1565c0;">*</span></label>
                      <button type="button" class="btn btn-link p-0 text-decoration-none" style="font-size:0.75rem; color:#1565c0;" onclick="autocompletarConfirmacionCorreo()" title="Copiar correo para confirmar">
                        Autocompletar
                      </button>
                    </div>
                    <input type="email" id="cit-correo2" class="form-control" placeholder="ejemplo@gmail.com">
                    <span class="error-msg" id="cit-correo2-err">Los correos no coinciden.</span>
                  </div>
                </div>

                {{-- 6. Teléfono Habitación --}}
                <div class="col-md-4">
                  <div class="form-group">
                    <label class="form-label">Teléfono Habitación</label>
                    <div style="display:flex;gap:6px;">
                      <select id="cit-telf-hab-cod" class="form-select" style="max-width:130px;flex-shrink:0;">
                        <option value="">Cód.</option>
                        <option value="0255" selected>0255</option>
                        <option value="0257">0257</option>
                        <option value="0212">0212</option>
                        <option value="0241">0241</option>
                        <option value="0251">0251</option>
                        <option value="0273">0273</option>
                        <option value="0261">0261</option>
                        <option value="0243">0243</option>
                        <option value="0274">0274</option>
                        <option value="0276">0276</option>
                        <option value="0281">0281</option>
                        <option value="0286">0286</option>
                      </select>
                      <input type="tel" id="cit-telf-hab-num" class="form-control" placeholder="1234567" maxlength="7">
                    </div>
                    <span class="error-msg" id="cit-telf-hab-err">Complete el código de área y los 7 dígitos.</span>
                  </div>
                </div>

                {{-- 7. Teléfono Celular --}}
                <div class="col-md-4">
                  <div class="form-group">
                    <label class="form-label">Teléfono Celular</label>
                    <div style="display:flex;gap:6px;">
                      <select id="cit-telf-cel-cod" class="form-select" style="max-width:110px;flex-shrink:0;">
                        <option value="">Cód.</option>
                        <option value="0412">0412</option>
                        <option value="0414">0414</option>
                        <option value="0416">0416</option>
                        <option value="0422">0422</option>
                        <option value="0424">0424</option>
                        <option value="0426">0426</option>
                      </select>
                      <input type="tel" id="cit-telf-cel-num" class="form-control" placeholder="1234567" maxlength="7">
                    </div>
                    <span class="error-msg" id="cit-telf-cel-err">Complete el número celular.</span>
                  </div>
                </div>

                {{-- 8. Teléfono del Trabajo --}}
                <div class="col-md-4">
                  <div class="form-group">
                    <label class="form-label">Teléfono del Trabajo</label>
                    <div style="display:flex;gap:6px;">
                      <select id="cit-telf-trab-cod" class="form-select" style="max-width:130px;flex-shrink:0;">
                        <option value="">Cód.</option>
                        <option value="0255" selected>0255</option>
                        <option value="0257">0257</option>
                        <option value="0212">0212</option>
                        <option value="0241">0241</option>
                        <option value="0251">0251</option>
                        <option value="0273">0273</option>
                        <option value="0261">0261</option>
                        <option value="0243">0243</option>
                        <option value="3301">3301</option>
                      </select>
                      <input type="tel" id="cit-telf-trab-num" class="form-control" placeholder="1234567" maxlength="7">
                    </div>
                    <span class="error-msg" id="cit-telf-trab-err">Complete el código de área y los 7 dígitos.</span>
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
            <button class="btn-submit" id="btn-sig-paso2" style="background: #1565c0;" onclick="mostrarModalConfirmacion(2)">
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
                  <label class="form-label" style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:8px;">
                    <span>Ubicación Geográfica del Señalado <span class="required" style="color:#1565c0;">*</span></span>
                    <span style="font-size:0.8rem; font-weight:normal; color:#546e7a;">
                      Geolocalización interactiva vía Google Maps / Satélite
                    </span>
                  </label>
                  <div style="display:flex; gap:8px; margin-bottom:8px;">
                    <input type="text" id="sen-ubicacion" class="form-control"
                      placeholder="Dirección, Parroquia, Consejo Comunal, punto de referencia...">
                    <button type="button" class="btn btn-sm btn-outline-secondary" style="white-space:nowrap; font-size:0.82rem; padding:6px 12px;" onclick="buscarDireccionEnMapa()" title="Ubicar dirección en el mapa">
                      Buscar en mapa
                    </button>
                    <button type="button" class="btn btn-sm btn-outline-primary" style="white-space:nowrap; font-size:0.82rem; padding:6px 12px;" onclick="obtenerUbicacionActualGPS()" title="Usar GPS del dispositivo">
                      Mi ubicación
                    </button>
                  </div>
                  <span class="error-msg" id="sen-ubicacion-err">Ingrese la ubicación del señalado.</span>

                  <!-- Componente de Mapa Interactivo Google Maps -->
                  <div id="contenedor-mapa-senalado" style="margin-top:10px; background:#fff; border:1px solid #dce3ec; border-radius:8px; overflow:hidden; box-shadow:0 1px 4px rgba(0,0,0,0.06);">
                    <div style="background:#f1f5f9; padding:8px 14px; font-size:0.82rem; color:#475569; display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:6px; border-bottom:1px solid #e2e8f0;">
                      <span>Haga clic en el mapa o arrastre el marcador para fijar las coordenadas exactas.</span>
                      <a id="btn-abrir-google-maps" href="https://maps.google.com" target="_blank" rel="noopener noreferrer" style="color:#1565c0; font-weight:600; text-decoration:none; font-size:0.8rem; display:none;">
                        Abrir en Google Maps 
                      </a>
                    </div>
                    <div id="mapa-senalado" style="height:260px; width:100%; background:#e5e3df;"></div>
                    <div style="background:#f8fafc; padding:6px 14px; font-size:0.8rem; color:#64748b; display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:6px;">
                      <div>
                        Coordenadas: Lat <strong id="sen-lat-texto" style="color:#0f172a;">—</strong>, Lng <strong id="sen-lng-texto" style="color:#0f172a;">—</strong>
                      </div>
                      <span id="sen-geo-status" style="font-size:0.78rem; color:#0284c7;"></span>
                    </div>
                  </div>
                  <span class="error-msg" id="sen-mapa-err" style="margin-top:6px;">Debe seleccionar una ubicación válida dentro del territorio nacional de Venezuela.</span>
                  <input type="hidden" id="sen-lat" name="sen_lat" value="">
                  <input type="hidden" id="sen-lng" name="sen_lng" value="">
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
                          Nombre del Proyecto <span class="required" style="color:#1565c0;">*</span>
                        </label>
                        <input type="text" id="proy-nombre" class="form-control" placeholder="Nombre oficial del proyecto">
                        <span class="error-msg" id="proy-nombre-err">Ingrese el nombre del proyecto.</span>
                      </div>
                    </div>
                    <div class="col-md-3">
                      <div class="form-group">
                        <label class="form-label">
                          Fecha estimada de aprobación <span class="required" style="color:#1565c0;">*</span>
                        </label>
                        <input type="date" id="proy-fecha" class="form-control" min="{{ date('Y-m-d', strtotime('-5 years')) }}" max="{{ date('Y-m-d') }}" onchange="validarFechaProyecto(this)" oninput="validarFechaProyecto(this)">
                        <span class="error-msg" id="proy-fecha-err">Seleccione la fecha estimada de aprobación.</span>
                      </div>
                    </div>
                    <div class="col-md-3">
                      <div class="form-group">
                        <label class="form-label">
                          Monto Estimado del Proyecto (Bs.) <span class="required" style="color:#1565c0;">*</span>
                        </label>
                        <div style="display:flex;align-items:stretch;">
                          <span style="display:inline-flex;align-items:center;padding:0 12px;background:#f1f5f9;color:#0d47a1;font-weight:700;font-size:0.85rem;border:1.5px solid #cdd5e0;border-right:none;border-radius:5px 0 0 5px;user-select:none;">Bs.</span>
                          <input type="text" id="proy-monto" class="form-control" style="border-top-left-radius:0;border-bottom-left-radius:0;" placeholder="0,00" inputmode="decimal" oninput="formatearMonto(this)" onblur="formatearMontoAlSalir(this)">
                        </div>
                        <span class="error-msg" id="proy-monto-err">Ingrese un monto estimado válido en Bolívares.</span>
                      </div>
                    </div>
                    <div class="col-md-6">
                      <div class="form-group">
                        <label class="form-label">Ente Financiador <span class="required" style="color:#1565c0;">*</span></label>
                        <input type="text" id="proy-financiador" class="form-control" placeholder="Nombre del ente financiador">
                        <span class="error-msg" id="proy-financiador-err">Ingrese el ente financiador.</span>
                      </div>
                    </div>
                    <div class="col-md-6">
                      <div class="form-group">
                        <label class="form-label">Código SITUR <span class="text-muted" style="font-weight:normal;font-size:0.8rem;">(Opcional)</span></label>
                        <div class="input-group">
                          <select id="proy-situr-tipo" class="form-select situr-tipo-selector" style="max-width: 145px; font-weight: 600; font-size: 0.82rem; background-color: #f8fafc; border-color: #cdd5e0;" onchange="actualizarPrefijoSitur('proy-situr')">
                            <option value="">Formato...</option>
                            <option value="C-URB-">C-URB- (Comuna Urbana)</option>
                            <option value="C-RUR-">C-RUR- (Comuna Rural)</option>
                            <option value="C-MIX-">C-MIX- (Comuna Mixta)</option>
                            <option value="CEC-POR-">CEC-POR- (Circuito Páez)</option>
                            <option value="CEC-">CEC- (Circuito Nacional)</option>
                            <option value="18-04-">18-04- (Territorial Páez)</option>
                            <option value="OTRO">Otro / Completo</option>
                          </select>
                          <input type="text" id="proy-situr-num" class="form-control mayusculas" placeholder="Seleccione formato o escriba..." oninput="manejarEntradaSitur('proy-situr')" onblur="validarSiturAlSalir('proy-situr')">
                        </div>
                        <input type="hidden" id="proy-situr" value="">
                        <small class="form-text text-muted" id="proy-situr-guia" style="display:block; font-size:0.75rem; margin-top:3px; color:#555;">
                          Elija el formato e ingrese solo los números correspondientes.
                        </small>
                        <span class="error-msg" id="proy-situr-err">Código SITUR no válido. Ej.: C-URB-2014-11-0001 o CEC-ZU-205</span>
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
            <button class="btn-submit" id="btn-sig-paso3" style="background: #1565c0;" onclick="mostrarModalConfirmacion(3)">
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
                  maxlength="3500" oninput="actualizarContadorNarracion(this)" style="min-height:140px;"></textarea>
                <div style="display:flex;justify-content:space-between;align-items:center;margin-top:4px;">
                  <span class="error-msg" id="narracion-err">La narración debe tener al menos 50 caracteres.</span>
                  <span class="contador-chars" id="narracion-contador">0 / 3500 caracteres</span>
                </div>
              </div>

              <div class="form-group" style="margin-top:20px;">
                <label class="form-label">
                  ¿Esta situación irregular ya fue denunciada ante otro organismo?
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
                  <label class="check-item">
                    <input type="radio" name="otra_instancia" value="no_se" onchange="toggleOtraInstancia('no_se')">
                    <label>No sé</label>
                  </label>
                </div>
                <span class="error-msg" id="otra-inst-err">Debe indicar si la situación irregular ya fue denunciada ante otro organismo.</span>
              </div>

              <div class="form-group" id="campo-cual-instancia" style="display:none;">
                <label class="form-label">
                  ¿Ante cuál organismo o instancia? <span class="required" style="color:#1565c0;">*</span>
                </label>
                <input type="text" id="cual-instancia" class="form-control"
                  placeholder="Nombre de la institución u organismo">
                <span class="error-msg" id="cual-instancia-err">Especifique el organismo o instancia.</span>
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
            <button class="btn-submit" id="btn-sig-paso4" style="background: #1565c0;" onclick="mostrarModalConfirmacion(4)">
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
                <p id="texto-nota-evidencia" style="font-size:0.78rem;color:#888;margin-bottom:16px;text-align:justify;">
                  Cargue los documentos uno por uno, en el orden que se muestra a continuación.
                </p>

                <div class="nota-info" style="margin-bottom:16px;">
                  Formatos admitidos: imágenes, video, audio, PDF y Word, con un máximo de 10 MB por archivo.
                  Los documentos marcados como <strong>Obligatorio</strong> deben adjuntarse para continuar.
                </div>
                <div class="nota-info" style="margin-bottom:16px;">
                  Recuerde consignar físicamente las evidencias originales ante la sede de la Contraloría.
                </div>

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
            <button class="btn-submit" id="btn-sig-paso5" style="background: #1565c0;" onclick="mostrarModalConfirmacion(5)">
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
                Resumen de su Solicitud
              </div>
              <div class="revision-card">
                <div class="revision-card-header">
                  <span class="revision-card-titulo">1. Tipo de Trámite</span>
                  <button type="button" class="btn-revision-editar" onclick="editarDesdeRevision(1)"><svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><path d="M12 20h9"/><path d="M16.5 3.5a2.1 2.1 0 0 1 3 3L7 19l-4 1 1-4z"/></svg> Editar</button>
                </div>
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
                </div>
              </div>
              <div class="revision-card">
                <div class="revision-card-header">
                  <span class="revision-card-titulo">2. Datos del Solicitante</span>
                  <button type="button" class="btn-revision-editar" onclick="editarDesdeRevision(2)">Editar</button>
                </div>
                <div class="row g-2">
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
                      <div class="resumen-etiqueta">Ubicación (Dirección, Ciudad/Estado, Municipio, Parroquia)</div>
                      <div class="resumen-valor" id="res-ubicacion" style="font-weight:600; color:#1a2340;">—</div>
                    </div>
                  </div>
                </div>
              </div>
              <div class="revision-card" id="rev-card-senalado">
                <div class="revision-card-header">
                  <span class="revision-card-titulo">3. Identificación del Señalado</span>
                  <button type="button" class="btn-revision-editar" onclick="editarDesdeRevision(3)"><svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><path d="M12 20h9"/><path d="M16.5 3.5a2.1 2.1 0 0 1 3 3L7 19l-4 1 1-4z"/></svg> Editar</button>
                </div>
                <div class="row g-2">
                  <div class="col-12">
                    <div class="resumen-item">
                      <div class="resumen-etiqueta">Señalado(s) / Instancia</div>
                      <div class="resumen-valor" id="res-senalado" style="white-space:pre-line;">—</div>
                    </div>
                  </div>
                  <div class="col-12">
                    <div class="resumen-item">
                      <div class="resumen-etiqueta">Ubicación del Señalado</div>
                      <div class="resumen-valor" id="res-sen-ubicacion">—</div>
                    </div>
                  </div>
                  <div class="col-12" id="res-bloque-proyecto" style="display:none;">
                    <div class="resumen-item">
                      <div class="resumen-etiqueta">Proyecto de la Consulta Popular</div>
                      <div class="resumen-valor" id="res-proyecto" style="white-space:pre-line;">—</div>
                    </div>
                  </div>
                </div>
              </div>
              <div class="revision-card">
                <div class="revision-card-header">
                  <span class="revision-card-titulo">4. Descripción de los Hechos</span>
                  <button type="button" class="btn-revision-editar" onclick="editarDesdeRevision(4)"><svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><path d="M12 20h9"/><path d="M16.5 3.5a2.1 2.1 0 0 1 3 3L7 19l-4 1 1-4z"/></svg> Editar</button>
                </div>
                <div class="row g-2">
                  <div class="col-12">
                    <div class="resumen-item">
                      <div class="resumen-etiqueta">Resumen de los Hechos</div>
                      <div class="resumen-valor" id="res-hechos" style="font-size:0.8rem;line-height:1.6;">—</div>
                    </div>
                  </div>
                  <div class="col-12">
                    <div class="resumen-item">
                      <div class="resumen-etiqueta">¿Presentado ante otra instancia?</div>
                      <div class="resumen-valor" id="res-otra-instancia">—</div>
                    </div>
                  </div>
                </div>
              </div>
              <div class="revision-card" id="rev-card-evidencias">
                <div class="revision-card-header">
                  <span class="revision-card-titulo">5. Documentos y Evidencias</span>
                  <button type="button" class="btn-revision-editar" onclick="editarDesdeRevision(5)"><svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><path d="M12 20h9"/><path d="M16.5 3.5a2.1 2.1 0 0 1 3 3L7 19l-4 1 1-4z"/></svg> Editar</button>
                </div>
                <div class="row g-2">
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
                     padding:16px 20px;font-size:0.82rem;color:#555;line-height:1.75;margin-bottom:18px; text-align: justify;">
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
              <button class="btn-submit" id="btn-enviar-solicitud" style="background: #1565c0;" onclick="enviarSolicitud()">
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

            <!-- BANNER REGLA DE NEGOCIO: DÍAS DE ANTICIPACIÓN PARA CONFIRMAR DATOS -->
            <div id="banner-lapso-confirmacion-exito" style="background:#f0f7ff; border:1px solid #bfdbfe; border-left:4px solid #1d4ed8; border-radius:8px; padding:16px 20px; margin-bottom:24px; text-align:justify;">
              <div style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:8px; margin-bottom:6px;">
                <strong style="color:#1e40af; font-size:0.96rem;">Lapso de Anticipación para Confirmación de Datos</strong>
                <span class="badge bg-primary" id="badge-lapso-dias" style="font-size:0.82rem; padding:5px 10px;">5 Días Hábiles</span>
              </div>
              <p style="font-size:0.88rem; color:#1e3a8a; margin:0 0 8px 0; text-align:justify;">
                De acuerdo con el procedimiento de la Contraloría, se le ha otorgado un lapso de <strong><span id="lapso-dias-display">5</span> días de anticipación</strong> (hasta el <strong id="lapso-fecha-display">—</strong>) para que pueda revisar minuciosamente su planilla, verificar los datos aportados y validar o rectificar cualquier inconsistencia antes de la formalización legal de su expediente.
              </p>
              <div style="font-size:0.82rem; color:#475569;">
                Código de seguimiento: <code id="lapso-tracking-display" style="font-weight:bold; color:#1e40af; font-size:0.88rem;">—</code>. Podrá consultar su estatus y validar la conformidad de sus datos en la sección <em>Consultar Estado de Trámite</em>.
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
                 <h6 style="font-weight:bold; color:#1a2340; margin-bottom:10px;">Datos del Proyecto (Consulta Popular Nacional)</h6>
                 <div class="print-row">
                   <div class="print-label">Nombre del Proyecto:</div>
                   <div class="print-val" id="print-proy-nombre">—</div>
                 </div>
                 <div class="print-row">
                   <div class="print-label">Fecha estimada de aprobación:</div>
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
                Imprimir Planilla Completa
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

  <!-- MODAL FLOTANTE: AVISO LEGAL SOBRE EL USO EXCLUSIVO DE CORREO GMAIL -->
  <div class="modal fade" id="modalAvisoGmail" tabindex="-1" aria-labelledby="modalAvisoGmailLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content" style="border: none; border-radius: 10px; box-shadow: 0 12px 30px rgba(0,0,0,0.2); overflow: hidden;">
        <div class="modal-header" style="background: #0f2340; color: #fff; padding: 16px 20px; border-bottom: 3px solid #1565c0;">
          <h5 class="modal-title" id="modalAvisoGmailLabel" style="font-size: 1.05rem; font-weight: bold; display: flex; align-items: center; gap: 9px; margin: 0;">
            Uso Obligatorio de Correo Gmail
          </h5>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
        </div>
        <div class="modal-body" style="padding: 22px 20px; background: #ffffff; color: #333;">
          <div style="background: #e8f4fd; border-radius: 6px; padding: 12px 16px; margin-bottom: 16px;">
            <strong style="color: #0d47a1; display: block; margin-bottom: 4px; font-size: 0.95rem;">
              Notificación Oficial
            </strong>
            <p style="margin: 0; font-size: 0.9rem; line-height: 1.5; text-align: justify; color: #1e3a5f;">
             <strong>El correo electrónico a utilizar en este formulario debe pertenecer obligatoriamente al servicio Gmail (@gmail.com)</strong>.
            </p>
          </div>

          <h6 style="font-size: 0.92rem; font-weight: 700; color: #0f2340; margin-bottom: 8px;">
            ¿Por qué se exige una cuenta de Gmail?
          </h6>
          <ul style="font-size: 0.88rem; line-height: 1.6; color: #495057; padding-left: 20px; margin-bottom: 16px; text-align: justify;">
            <li style="margin-bottom: 6px;">
              <strong>Garantía de Entrega y Celeridad Procesal:</strong> Asegura la recepción inmediata y sin bloqueos de los comprobantes oficiales con código QR, números de folio y seguimiento de su trámite.
            </li>
            <li style="margin-bottom: 6px;">
              <strong>Seguridad y Compatibilidad Tecnológica:</strong> Otros proveedores presentan filtros de correo no deseado (SPAM) que impiden o retrasan la entrega de las resoluciones y respuestas emitidas por la Contraloría.
            </li>
            <li>
              <strong>Validez del Expediente Electrónico:</strong> Permite autenticar de manera fidedigna los canales de contacto legalmente vinculantes con el ciudadano solicitante.
            </li>
          </ul>

          <div style="background: #fff8e1; border: 1px solid #ffe082; border-radius: 6px; padding: 10px 14px; font-size: 0.85rem; color: #795548; display: flex; align-items: center; gap: 10px;">
            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="#f57c00" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="flex-shrink:0;">
              <path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/>
              <line x1="12" y1="9" x2="12" y2="13"/>
              <line x1="12" y1="17" x2="12.01" y2="17"/>
            </svg>
            <div style="text-align: justify;">
              Si actualmente no cuenta con una dirección de Gmail, puede registrarla de forma gratuita en <strong>www.gmail.com</strong> para completar su trámite sin inconvenientes.
            </div>
          </div>
        </div>
        <div class="modal-footer" style="background: #f7f9fd; border-top: 1px solid #eef1f7; padding: 12px 20px; justify-content: flex-end;">
          <button type="button" class="btn btn-primary" data-bs-dismiss="modal" style="background: #1565c0; border-color: #1565c0; font-size: 0.88rem; font-weight: 600; padding: 7px 22px; border-radius: 6px;">
            Entendido
          </button>
        </div>
      </div>
    </div>
  </div>

  <!-- MODAL: EJEMPLOS DE FORMATOS DIGITALIZADOS (C.I. Y TESTIMONIO CON FIRMA Y HUELLA) -->
  <div class="modal fade" id="modalEjemploDocumento" tabindex="-1" aria-labelledby="modalEjemploTitulo" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
      <div class="modal-content" style="border: none; border-radius: 12px; box-shadow: 0 16px 36px rgba(0,0,0,0.22); overflow: hidden;">
        <div class="modal-header" style="background: #0f2340; color: #fff; padding: 15px 20px; border-bottom: 3px solid #1565c0;">
          <h5 class="modal-title" id="modalEjemploTitulo" style="font-size: 1.05rem; font-weight: bold; display: flex; align-items: center; gap: 9px; margin: 0;">
            Ejemplo de Formato Digitalizado
          </h5>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
        </div>
        <div class="modal-body" style="padding: 22px 20px; background: #f8fafc; color: #333;">

          <!-- SECCIÓN 1: EJEMPLO DE CÉDULA DE IDENTIDAD -->
          <div id="ejemplo-doc-ci" style="display: none;">
            <div style="background: #e8f4fd; border-radius: 8px; padding: 12px 16px; margin-bottom: 18px; display: flex; align-items: flex-start; gap: 12px;">
              <div style="font-size: 0.88rem; color: #1e3a5f; line-height: 1.45; text-align: justify;">
                <strong>¿Cómo debe ser la imagen o escaneo de la C.I.?</strong><br>
                Debe ser una copia legible en color o blanco y negro, donde se aprecien claramente todos los datos de identidad (anverso y reverso).
              </div>
            </div>

            <!-- MOCKUP VISUAL DE CÉDULA DIGITALIZADA -->
            <div style="background: #ffffff; border: 2px dashed #b0bec5; border-radius: 10px; padding: 20px; margin-bottom: 18px; text-align: center; box-shadow: 0 2px 8px rgba(0,0,0,0.04);">
              <div style="display: inline-block; font-size: 0.75rem; font-weight: 700; color: #2e7d32; background: #e8f5e9; border: 1px solid #c8e6c9; border-radius: 20px; padding: 3px 12px; margin-bottom: 12px;">
               FORMATO DE MUESTRA CORRECTO (ANVERSO Y REVERSO)
              </div>

              <div style="display: flex; justify-content: center; align-items: center; flex-wrap: wrap; gap: 20px;">
                <!-- ANVERSO C.I. REAL -->
                <div style="max-width: 330px; background: #fff; border: 1.5px solid #1565c0; border-radius: 10px; overflow: hidden; box-shadow: 0 4px 12px rgba(0,0,0,0.1); text-align: center;">
                  <div style="background: #0f2340; color: #fff; font-size: 0.72rem; font-weight: 700; padding: 6px 10px; letter-spacing: 0.5px;">
                    ANVERSO (FRENTE)
                  </div>
                  <img src="{{ asset('assets/img/ejemplo-ci-frente.jpg') }}" alt="Ejemplo de Cédula de Identidad Frente" style="width: 100%; height: auto; display: block; object-fit: contain;">
                </div>

                <!-- REVERSO C.I. REAL -->
                <div style="max-width: 330px; background: #fff; border: 1.5px solid #1565c0; border-radius: 10px; overflow: hidden; box-shadow: 0 4px 12px rgba(0,0,0,0.1); text-align: center;">
                  <div style="background: #0f2340; color: #fff; font-size: 0.72rem; font-weight: 700; padding: 6px 10px; letter-spacing: 0.5px;">
                    REVERSO (POSTERIOR)
                  </div>
                  <img src="{{ asset('assets/img/ejemplo-ci-reverso.png') }}" alt="Ejemplo de Cédula de Identidad Reverso" style="width: 100%; height: auto; display: block; object-fit: contain;">
                </div>
              </div>
            </div>

            <!-- CRITERIOS CLAVE -->
            <div style="background: #ffffff; border: 1px solid #e2e8f0; border-radius: 8px; padding: 14px 16px;">
              <h6 style="font-size: 0.88rem; font-weight: 700; color: #0f2340; margin-bottom: 8px;">Requisitos indispensables para su C.I.:</h6>
              <ul style="font-size: 0.83rem; line-height: 1.6; color: #475569; margin: 0; padding-left: 18px; text-align: justify;">
                <li><strong>Claridad total:</strong> El número de documento y los nombres deben ser 100% legibles.</li>
                <li><strong>Sin recortes:</strong> Todo el marco de la cédula debe quedar dentro de la fotografía.</li>
                <li><strong>Sin reflejos ni brillos:</strong> Evite utilizar flash directo que ciegue el número de cédula o la foto.</li>
                <li><strong>Archivos admitidos:</strong> Imagen nítida (JPG, PNG) o documento escaneado en PDF (máximo 10 MB).</li>
              </ul>
            </div>
          </div>

          <!-- SECCIÓN 2: EJEMPLO DE TESTIMONIO / CARTA CON FIRMA Y HUELLA -->
          <div id="ejemplo-doc-testimonio" style="display: none;">
            <div style="background: #e8f4fd; border-radius: 8px; padding: 12px 16px; margin-bottom: 18px; display: flex; align-items: flex-start; gap: 12px;">
              <div style="font-size: 0.88rem; color: #1e3a5f; line-height: 1.45; text-align: justify;">
                <strong>¿Cómo debe consignarse el Testimonio Escrito o Carta de Exposición?</strong><br>
                Debe ser un documento escrito (a mano con letra clara o en computadora impreso), donde se expongan los motivos o hechos, y que contenga obligatoriamente la <strong>firma autógrafa</strong> y la <strong>huella dactilar</strong> del declarante.
              </div>
            </div>

            <!-- MOCKUP VISUAL DE CARTA CON FIRMA Y HUELLA -->
            <div style="background: #ffffff; border: 2px dashed #b0bec5; border-radius: 10px; padding: 20px; margin-bottom: 18px; text-align: center; box-shadow: 0 2px 8px rgba(0,0,0,0.04);">
              <div style="display: inline-block; font-size: 0.75rem; font-weight: 700; color: #2e7d32; background: #e8f5e9; border: 1px solid #c8e6c9; border-radius: 20px; padding: 3px 12px; margin-bottom: 12px;">
                FORMATO DE MUESTRA CORRECTO (CON FIRMA Y HUELLA)
              </div>

              <!-- HOJA DE CARTA DIGITALIZADA -->
              <div style="max-width: 480px; margin: 0 auto; background: #ffffff; border: 1px solid #cbd5e1; border-radius: 6px; padding: 22px 24px; box-shadow: 0 6px 14px rgba(0,0,0,0.08); text-align: left; font-size: 0.75rem; color: #334155; line-height: 1.5;">
                <div style="text-align: right; color: #64748b; font-size: 0.7rem; margin-bottom: 12px;">
                  Acarigua, Portuguesa, 24 de Noviembre de 2026
                </div>
                
                <div style="font-weight: 700; color: #0f2340; margin-bottom: 10px;">
                  Ciudadano(a):<br>
                  Contralor(a) del Municipio Páez<br>
                  Oficina de Atención al Ciudadano (OAC)<br>
                  <span style="font-size: 0.7rem; color: #64748b; font-weight: normal;">Su Despacho. —</span>
                </div>

                <div style="text-align: justify; margin-bottom: 10px;">
                  Yo, <strong>Juan Carlos Pérez Pérez</strong>, titular de la Cédula de Identidad N° <strong>V-12.345.678</strong>, domiciliado en el Municipio Páez, mediante la presente expongo de manera voluntaria los hechos ocurridos con fecha [dd/mm/aaaa], en la comunidad [Nombre de la Comunidad]...
                </div>

                <div style="height: 1px; background: #f1f5f9; margin: 15px 0;"></div>

                <!-- SECCIÓN DE FIRMA Y HUELLA -->
                <div style="display: flex; justify-content: space-between; align-items: flex-end; padding: 10px 14px; background: #f8fafc; border: 1.5px solid #cbd5e1; border-radius: 6px;">
                  <!-- FIRMA -->
                  <div style="flex: 1; text-align: center; padding-right: 15px;">
                    <div style="height: 45px; display: flex; align-items: flex-end; justify-content: center;">
                      <span style="font-family: cursive; font-size: 1.15rem; color: #0d47a1; font-weight: 600;">Juan C. Pérez</span>
                    </div>
                    <div style="border-top: 1.5px solid #334155; margin-top: 4px; padding-top: 4px; font-size: 0.68rem; line-height: 1.3;">
                      <strong>FIRMA DEL DECLARANTE</strong><br>
                      C.I.: V-12.345.678<br>
                      Teléfono: 0414-1234567
                    </div>
                  </div>

                  <!-- HUELLA DACTILAR -->
                  <div style="width: 110px; text-align: center; border-left: 1px dashed #cbd5e1; padding-left: 15px;">
                    <div style="width: 58px; height: 68px; margin: 0 auto; background: #fff; border: 1.5px solid #0d47a1; border-radius: 6px; display: flex; flex-direction: column; align-items: center; justify-content: center; position: relative;">
                      <!-- SVG HUELLA DACTILAR -->
                      <svg width="40" height="52" viewBox="0 0 24 24" fill="none" stroke="#0d47a1" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" style="opacity: 0.85;">
                        <path d="M12 10a2 2 0 0 0-2 2c0 1.02-.1 2.51-.26 4"/>
                        <path d="M14 13.12c0 2.38 0 6.38-1 8.88"/>
                        <path d="M17.29 21.02c.12-.6.43-2.3.5-3.02"/>
                        <path d="M2 12a10 10 0 0 1 18-6"/>
                        <path d="M2 16h.01"/>
                        <path d="M21.8 16c.2-2 .131-5.354 0-6"/>
                        <path d="M5 19.5C5.5 18 6 15 6 12a6 6 0 0 1 .34-2"/>
                        <path d="M8.65 22c.21-.66.45-1.32.57-2"/>
                        <path d="M9 6.8a6 6 0 0 1 9 5.2v2"/>
                      </svg>
                    </div>
                    <div style="margin-top: 4px; font-size: 0.65rem; font-weight: 700; color: #0d47a1; text-transform: uppercase;">
                      HUELLA DACTILAR<br>
                      <span style="font-size: 0.58rem; color: #64748b; font-weight: normal;">(Pulgar Derecho)</span>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <!-- CRITERIOS CLAVE -->
            <div style="background: #ffffff; border: 1px solid #e2e8f0; border-radius: 8px; padding: 14px 16px;">
              <h6 style="font-size: 0.88rem; font-weight: 700; color: #0f2340; margin-bottom: 8px;">Requisitos indispensables para la Carta o Testimonio:</h6>
              <ul style="font-size: 0.83rem; line-height: 1.6; color: #475569; margin: 0; padding-left: 18px; text-align: justify;">
                <li><strong>Firma manuscrita original:</strong> Realizada con bolígrafo de tinta negra o azul.</li>
                <li><strong>Huella dactilar nítida:</strong> Estampar la huella (preferiblemente del pulgar derecho) con tinta para huellas; no debe verse borrosa ni manchada.</li>
                <li><strong>Página completa y vertical:</strong> Asegúrese de fotografiar o escanear toda la hoja, sin doblar esquinas ni recortar los márgenes.</li>
                <li><strong>Archivos admitidos:</strong> Puede subir el archivo en formato PDF, imagen (JPG, PNG) o documento de Word escaneado.</li>
              </ul>
            </div>
          </div>

        </div>
        <div class="modal-footer" style="background: #f7f9fd; border-top: 1px solid #eef1f7; padding: 12px 20px; justify-content: flex-end;">
          <button type="button" class="btn btn-primary" data-bs-dismiss="modal" style="background: #1565c0; border-color: #1565c0; font-size: 0.88rem; font-weight: 600; padding: 7px 22px; border-radius: 6px;">
            Entendido, volver al formulario
          </button>
        </div>
      </div>
    </div>
  </div>

  <!-- MODAL FLOTANTE DE CONFIRMACIÓN DE DATOS (Bootstrap Custom) -->
  <div class="modal fade" id="modalConfirmacionPaso" tabindex="-1" aria-labelledby="modalConfirmacionLabel" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content" style="border: none; border-radius: 8px; box-shadow: 0 10px 25px rgba(0,0,0,0.15);">
        <div class="modal-header" style="background: #0f2340; color: #fff; border-top-left-radius: 8px; border-top-right-radius: 8px; padding: 15px 20px;">
          <h5 class="modal-title" id="modalConfirmacionLabel" style="font-size: 1.05rem; font-weight: bold; display: flex; align-items: center; gap: 8px;">
            Confirmación de Datos Ingresados
          </h5>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body" style="padding: 25px 20px; background: #fdfdfd; color: #333;">
          <p id="modal-texto-pregunta" style="font-size: 0.95rem; margin-bottom: 0; text-align: justify; line-height: 1.5;">
            ¿Verificó correctamente los datos ingresados en este paso antes de continuar con el proceso?
          </p>
        </div>
        <div class="modal-footer" style="justify-content: center; flex-wrap: nowrap; gap: 10px; background: #f7f9fd; border-bottom-left-radius: 8px; border-bottom-right-radius: 8px; padding: 12px 20px; border-top: 1px solid #eef1f7;">
          <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal" style="font-size: 0.88rem; padding: 6px 16px; border-color: #b0bec5; color: #455a64;">Revisar nuevamente</button>
          <button type="button" class="btn btn-primary" id="btn-confirmar-modal" onclick="ejecutarPasoSiguiente()" style="background: #1565c0; border-color: #1565c0; font-size: 0.88rem; padding: 6px 20px; font-weight: 600;">Sí, todo es correcto</button>
        </div>
      </div>
    </div>
  </div>

  <footer>
    <div class="footer-inner">
      <div class="row g-4 pb-4" style="border-bottom: 1px solid rgba(255,255,255,0.1); margin-bottom: 24px;">
        <div class="col-lg-4 col-md-6 footer-brand">
          <img src="{{ asset('assets/img/logonuevo.jpeg') }}" alt="Logo" style="width:56px;height:56px; border-radius: 25px;">
          <p style="margin-top:12px;">Contraloría del Municipio Páez — Estado Portuguesa, República Bolivariana de Venezuela. Órgano de Control Fiscal Municipal.</p>
          <div class="rif">RIF: G-20001628-0 · Acarigua, Estado Portuguesa</div>
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
            <li><a href="{{ url('/organigrama') }}">Organigrama</a></li>
            <li><a href="{{ url('/#Historia') }}">Historia</a></li>
            <li><a href="{{ url('/#mision') }}">Ubicación</a></li>
            <li><a href="{{ url('/#legal') }}">Base Normativa</a></li>
          </ul>
        </div>

        <div class="col-lg-2 col-md-6 footer-col">
          <h6>Ciudadanía</h6>
          <ul>
            <li><a href="{{ url('/#participacion') }}">Participación Ciudadana</a></li>
            <li><a href="{{ url('/#redes') }}">Redes Sociales</a></li>
            <li><a href="{{ url('/denuncias') }}">Denuncias</a></li>
            <li><a href="{{ url('/contraloria-escolar') }}">Contraloría Escolar</a></li>
            <li><a href="{{ url('/gestion-comunitaria') }}">Gestión Comunitaria</a></li>
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

  <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.2/js/bootstrap.bundle.min.js"></script>
  {{-- Datos geográficos de Venezuela para los selectores de Lugar de Nacimiento --}}
  <script src="{{ asset('assets/js/venezuela_geo.js') }}?v={{ filemtime(public_path('assets/js/venezuela_geo.js')) }}"></script>
  <script src="{{ asset('assets/js/wizard.js') }}?v={{ filemtime(public_path('assets/js/wizard.js')) }}"></script>
  
  <script>
    let pasoPendienteValidacion = 1;
    let instanciaModal = null;

    const NOMBRES_PASOS = ["Tipo de Trámite", "Datos del Ciudadano", "Datos del Señalado", "Descripción de los Hechos", "Documentos y Evidencias"];

    /* ══════════════════════════════════════════════════════════════
       Selectores en cascada para Lugar de Nacimiento
       Usa window.VENEZUELA_GEO cargado desde venezuela_geo.js
       ══════════════════════════════════════════════════════════════ */

    /** Rellena el select de Estados al cargar la página */
    function geoInicializar() {
      var selEst = document.getElementById('cit-lugar-nac-estado');
      if (!selEst || !window.VENEZUELA_GEO) return;
      var estados = Object.keys(window.VENEZUELA_GEO).sort(function(a, b) {
        return a.localeCompare(b, 'es');
      });
      estados.forEach(function(est) {
        var opt = document.createElement('option');
        opt.value = est;
        opt.textContent = est;
        selEst.appendChild(opt);
      });
    }

    /** Cuando cambia el Estado: recarga municipios y limpia parroquias */
    function geoActualizarMunicipio() {
      var estado  = document.getElementById('cit-lugar-nac-estado').value;
      var selMun  = document.getElementById('cit-lugar-nac-municipio');
      var selPar  = document.getElementById('cit-lugar-nac-parroquia');
      var hidden  = document.getElementById('cit-lugar-nac');

      selMun.innerHTML = '<option value="">-- Municipio --</option>';
      selPar.innerHTML = '<option value="">-- Parroquia --</option>';
      selMun.disabled  = true;
      selPar.disabled  = true;
      hidden.value     = '';

      if (!estado) return;

      if (estado === 'Extranjero') {
        hidden.value = 'Extranjero';
        return;
      }

      var municipios = Object.keys(window.VENEZUELA_GEO[estado] || {}).sort(function(a, b) {
        return a.localeCompare(b, 'es');
      });
      municipios.forEach(function(mun) {
        var opt = document.createElement('option');
        opt.value = mun;
        opt.textContent = mun;
        selMun.appendChild(opt);
      });
      selMun.disabled = false;
    }

    /** Cuando cambia el Municipio: recarga parroquias */
    function geoActualizarParroquia() {
      var estado    = document.getElementById('cit-lugar-nac-estado').value;
      var municipio = document.getElementById('cit-lugar-nac-municipio').value;
      var selPar    = document.getElementById('cit-lugar-nac-parroquia');
      var hidden    = document.getElementById('cit-lugar-nac');

      selPar.innerHTML = '<option value="">-- Parroquia --</option>';
      selPar.disabled  = true;
      // Valor mínimo aunque no seleccione parroquia
      hidden.value = municipio ? (municipio + ', ' + estado) : (estado || '');

      if (!estado || !municipio) return;

      var parroquias = (window.VENEZUELA_GEO[estado] || {})[municipio] || [];
      parroquias.forEach(function(par) {
        var opt = document.createElement('option');
        opt.value = par;
        opt.textContent = par;
        selPar.appendChild(opt);
      });
      if (parroquias.length > 0) selPar.disabled = false;
    }

    /** Cuando cambia la Parroquia: actualiza el campo oculto con el valor completo */
    function geoActualizarHiddenNac() {
      var estado    = document.getElementById('cit-lugar-nac-estado').value;
      var municipio = document.getElementById('cit-lugar-nac-municipio').value;
      var parroquia = document.getElementById('cit-lugar-nac-parroquia').value;
      var hidden    = document.getElementById('cit-lugar-nac');

      if (parroquia && municipio && estado) {
        hidden.value = parroquia + ', ' + municipio + ', ' + estado;
      } else if (municipio && estado) {
        hidden.value = municipio + ', ' + estado;
      } else {
        hidden.value = estado || '';
      }
    }

    /**
     * Restaura los tres selectores de lugar de nacimiento a partir de
     * los valores guardados en el padrón (registro de autocompletado).
     * Debe llamarse en el orden Estado → Municipio → Parroquia para que
     * cada nivel cargue sus opciones antes de asignar el valor.
     */
    function geoRestaurarSelectores(estado, municipio, parroquia) {
      var selEst = document.getElementById('cit-lugar-nac-estado');
      var selMun = document.getElementById('cit-lugar-nac-municipio');
      var selPar = document.getElementById('cit-lugar-nac-parroquia');
      if (!selEst) return;

      if (estado) {
        selEst.value = estado;
        geoActualizarMunicipio();           // recarga municipios
        if (municipio && selMun) {
          selMun.value = municipio;
          geoActualizarParroquia();         // recarga parroquias
          if (parroquia && selPar) {
            selPar.value = parroquia;
            geoActualizarHiddenNac();       // actualiza campo oculto
          }
        }
      }
    }

    /* ── Monto en bolívares: 1.250.000,50 mientras se escribe y al perder el foco ── */
    function formatearMonto(el) {
      let v = el.value.replace(/[^\d,]/g, '');
      let [ent, dec] = v.split(',');
      ent = (ent || '').replace(/^0+(?=\d)/, '').replace(/\B(?=(\d{3})+(?!\d))/g, '.');
      el.value = dec === undefined ? ent : (ent || '0') + ',' + dec.slice(0, 2);
    }

    function formatearMontoAlSalir(el) {
      formatearMonto(el);
      let v = (el.value || '').trim();
      if (!v) return;
      if (!v.includes(',')) {
        el.value = v + ',00';
      } else {
        let [ent, dec] = v.split(',');
        dec = (dec || '').padEnd(2, '0').slice(0, 2);
        el.value = (ent || '0') + ',' + dec;
      }
    }

    /* ── Borrador en localStorage: sobrevive a recargas, caduca a las 2 horas ── */
    const BORRADOR_KEY = 'borrador-denuncia', BORRADOR_TTL = 2 * 60 * 60 * 1000;
    const SEL_CAMPOS = 'input:not([type=file]):not([type=hidden]), select, textarea';
    const camposBorrador = () => [...document.querySelectorAll(SEL_CAMPOS)].filter(el => !el.closest('#lista-senalados'));
    // Tarjetas de señalado: se crean dinámicamente y sus campos no tienen id; se guardan por posición
    const leerSenalados = () => [...document.querySelectorAll('#lista-senalados .senalado-card')].map(t => ({
      tipo: t.querySelector('[data-campo="tipo-senalado"]:checked')?.value || '',
      valores: [...t.querySelectorAll('.senalado-card-campos ' + SEL_CAMPOS.split(', ').join(', .senalado-card-campos '))]
        .map(el => (el.type === 'checkbox' || el.type === 'radio') ? el.checked : el.value),
    }));
    function restaurarSenalados(lista) {
      (lista || []).forEach((s, i) => {
        const cont = document.getElementById('lista-senalados');
        if (!cont.children[i]) agregarSenalado();
        const t = cont.children[i];
        const cb = s.tipo && t.querySelector('[data-campo="tipo-senalado"][value="' + CSS.escape(s.tipo) + '"]');
        if (!cb) return;
        cb.checked = true;
        onTipoSenaladoCheckboxChange(cb); // dibuja los campos del tipo elegido
        t.querySelectorAll('.senalado-card-campos ' + SEL_CAMPOS.split(', ').join(', .senalado-card-campos ')).forEach((el, j) => {
          if (!(j in s.valores)) return;
          if (el.type === 'checkbox' || el.type === 'radio') el.checked = s.valores[j]; else el.value = s.valores[j];
        });
      });
    }
    function guardarBorrador() {
      if (!window.tipoFormulario) return; // el asistente aún no se ha iniciado
      const campos = {};
      camposBorrador().forEach(el => {
        if (el.type === 'radio') { if (el.checked) campos['r:' + el.name] = el.value; }
        else if (el.type === 'checkbox') { if (el.id) campos[el.id] = el.checked; }
        else if (el.id && el.id !== 'input-codigo-consulta') campos[el.id] = el.value;
      });
      try { localStorage.setItem(BORRADOR_KEY, JSON.stringify({ t: Date.now(), tipo: window.tipoFormulario, paso: window.pasoActual, campos, senalados: leerSenalados() })); } catch (e) {}
    }
    function restaurarBorrador() {
      let b;
      try { b = JSON.parse(localStorage.getItem(BORRADOR_KEY)); } catch (e) {}
      if (!b) return;
      if (Date.now() - b.t > BORRADOR_TTL || !b.tipo) { localStorage.removeItem(BORRADOR_KEY); return; }
      iniciarFlujo(b.tipo); // muestra el asistente y crea la primera tarjeta de señalado
      camposBorrador().forEach(el => {
        let cambio = false;
        if (el.type === 'radio') { if (b.campos['r:' + el.name] === el.value) { el.checked = cambio = true; } }
        else if (el.id in b.campos) {
          if (el.type === 'checkbox') el.checked = b.campos[el.id]; else el.value = b.campos[el.id];
          cambio = true;
        }
        if (cambio) el.dispatchEvent(new Event('change', { bubbles: true }));
      });
      restaurarSenalados(b.senalados);
      if (b.paso > 1 && b.paso < 6 && typeof mostrarPaso === 'function') { window.pasoActual = b.paso; mostrarPaso(b.paso); }
    }
    document.addEventListener('input', guardarBorrador);
    document.addEventListener('change', guardarBorrador);
    window.addEventListener('beforeunload', guardarBorrador);

    document.addEventListener('DOMContentLoaded', function () {
      // Inicializar selectores geográficos de Lugar de Nacimiento
      geoInicializar();
      restaurarBorrador();
      const elementoModal = document.getElementById('modalConfirmacionPaso');
      if (elementoModal && window.bootstrap) {
        instanciaModal = new bootstrap.Modal(elementoModal);
      }
      // Validación en tiempo real: nombres, apellidos, profesión y ocupación solo letras
      const camposSoloTexto = [
        'cit-primer-nombre',
        'cit-segundo-nombre',
        'cit-primer-apellido',
        'cit-segundo-apellido',
        'cit-ocupacion'
      ];

      camposSoloTexto.forEach(id => {
        const campo = document.getElementById(id);
        if (!campo) return;
        const errSpan = document.getElementById(id + '-err');

        campo.addEventListener('input', function () {
          const val = this.value;
          // Detectar si ingresó algún caracter no permitido (números, símbolos, etc.)
          const tieneCaracteresInvalidos = /[^a-zA-ZáéíóúÁÉÍÓÚñÑüÜ\s]/.test(val);

          if (tieneCaracteresInvalidos) {
            // Filtrar y eliminar números y caracteres no permitidos
            this.value = val.replace(/[^a-zA-ZáéíóúÁÉÍÓÚñÑüÜ\s]/g, '');
            this.dataset.errorLetras = '1';
            if (errSpan) {
              errSpan.textContent = 'No se aceptan números ni signos especiales. Solo se permiten letras.';
              errSpan.classList.add('visible');
              errSpan.style.display = 'block';
            }
            this.classList.add('invalid');

            if (this._timerErrorLetras) clearTimeout(this._timerErrorLetras);
            this._timerErrorLetras = setTimeout(() => {
              delete this.dataset.errorLetras;
              const req = (this.id === 'cit-primer-nombre' || this.id === 'cit-primer-apellido');
              if (this.value.trim().length >= (req ? 2 : 0)) {
                if (errSpan && errSpan.textContent.includes('No se aceptan')) {
                  errSpan.classList.remove('visible');
                  errSpan.style.display = 'none';
                  this.classList.remove('invalid');
                }
              }
            }, 3500);
          } else {
            // Si el campo tiene contenido válido suficiente, se quita la alerta
            if (this.id === 'cit-primer-nombre' || this.id === 'cit-primer-apellido') {
              if (this.value.trim().length >= 2) {
                delete this.dataset.errorLetras;
                if (errSpan) {
                  errSpan.classList.remove('visible');
                  errSpan.style.display = 'none';
                }
                this.classList.remove('invalid');
              }
            } else {
              delete this.dataset.errorLetras;
              if (errSpan) {
                errSpan.classList.remove('visible');
                errSpan.style.display = 'none';
              }
              this.classList.remove('invalid');
            }
          }
        });
      });

      // Validación y sanitización reactiva en tiempo real: nombres y apellidos del señalado (Paso 3)
      document.addEventListener('input', function (e) {
        const el = e.target;
        if (!el || !el.matches) return;
        if (el.matches('[data-campo="pn-nombres"], [data-campo="jp-nombres"], [data-campo="pj-representante"]')) {
          const val = el.value;
          const tieneCaracteresInvalidos = /[^a-zA-ZáéíóúÁÉÍÓÚñÑüÜ\s,]/.test(val);
          const grupo = el.closest('.form-group');
          const errSpan = grupo ? (grupo.querySelector('.error-msg[data-campo-err]') || grupo.querySelector('.error-msg')) : null;

          if (tieneCaracteresInvalidos) {
            el.value = val.replace(/[^a-zA-ZáéíóúÁÉÍÓÚñÑüÜ\s,]/g, '');
            el.dataset.errorLetras = '1';
            if (errSpan) {
              errSpan.textContent = 'No se aceptan números ni signos especiales. Solo se permiten letras.';
              errSpan.classList.add('visible');
              errSpan.style.display = 'block';
            }
            el.classList.add('invalid');

            if (el._timerErrorLetras) clearTimeout(el._timerErrorLetras);
            el._timerErrorLetras = setTimeout(() => {
              delete el.dataset.errorLetras;
              if (el.value.trim().length >= 3 || !el.classList.contains('campo-dinamico-req')) {
                if (errSpan && errSpan.textContent.includes('No se aceptan')) {
                  errSpan.classList.remove('visible');
                  errSpan.style.display = 'none';
                  el.classList.remove('invalid');
                }
              }
            }, 3500);
          } else {
            if (el.value.trim().length >= 3 || (!el.classList.contains('campo-dinamico-req') && el.value.trim().length === 0)) {
              delete el.dataset.errorLetras;
              if (errSpan && errSpan.textContent.includes('No se aceptan')) {
                errSpan.classList.remove('visible');
                errSpan.style.display = 'none';
                el.classList.remove('invalid');
              }
            }
          }
        }
      });

      // Primera letra de cada palabra en mayúscula en los campos de texto del registro
      document.addEventListener('input', function (e) {
        const el = e.target;
        if (!el.matches('input[type="text"]:not(.mayusculas), textarea') || el.id === 'narracion') return;
        const pos = el.selectionStart;
        el.value = el.value.replace(/(^|\s)(\p{Ll})/gu, (m, sp, c) => sp + c.toUpperCase());
        el.setSelectionRange(pos, pos);
      });

      // Configuración de fecha máxima (hoy) y mínima (hasta 5 años atrás) para la aprobación del proyecto
      const proyFecha = document.getElementById('proy-fecha');
      if (proyFecha) {
        const dHoy = new Date();
        const hoy = dHoy.getFullYear() + '-' + String(dHoy.getMonth() + 1).padStart(2, '0') + '-' + String(dHoy.getDate()).padStart(2, '0');
        const dMin = new Date(dHoy);
        dMin.setFullYear(dMin.getFullYear() - 5);
        const minFecha = dMin.getFullYear() + '-' + String(dMin.getMonth() + 1).padStart(2, '0') + '-' + String(dMin.getDate()).padStart(2, '0');
        proyFecha.max = hoy;
        proyFecha.min = minFecha;
      }

      // Inicialización de la geografía nacional en cascada y autocompletado de confirmación de correo
      inicializarGeografiaVenezuela();
      configurarOpcionesOtras();
      configurarAutocompletadoCorreo();
      setTimeout(function() {
        if (typeof inicializarMapaCiudadano === 'function') inicializarMapaCiudadano();
      }, 250);
    });

    /* ── Geografía de Venezuela (Ciudades de Venezuela, Municipios y Parroquias) ── */
    window.venezuelaGeoData = @json($venezuelaGeo ?? []);

    window.CIUDADES_VENEZUELA = [
      // Portuguesa (Ámbito principal)
      { ciudad: 'Acarigua', estado: 'Portuguesa', municipio: 'Páez', parroquia: 'Acarigua' },
      { ciudad: 'Araure', estado: 'Portuguesa', municipio: 'Araure', parroquia: 'Araure' },
      { ciudad: 'Guanare', estado: 'Portuguesa', municipio: 'Guanare', parroquia: 'Guanare' },
      { ciudad: 'Agua Blanca', estado: 'Portuguesa', municipio: 'Agua Blanca', parroquia: 'Agua Blanca' },
      { ciudad: 'Biscucuy', estado: 'Portuguesa', municipio: 'Sucre', parroquia: 'Biscucuy' },
      { ciudad: 'Chabasquén', estado: 'Portuguesa', municipio: 'Monseñor José Vicente de Unda', parroquia: 'Chabasquén' },
      { ciudad: 'Guanarito', estado: 'Portuguesa', municipio: 'Guanarito', parroquia: 'Guanarito' },
      { ciudad: 'Ospino', estado: 'Portuguesa', municipio: 'Ospino', parroquia: 'Ospino' },
      { ciudad: 'Papelón', estado: 'Portuguesa', municipio: 'Papelón', parroquia: 'Papelón' },
      { ciudad: 'Píritu', estado: 'Portuguesa', municipio: 'Esteller', parroquia: 'Píritu' },
      { ciudad: 'San Genaro de Boconoíto', estado: 'Portuguesa', municipio: 'San Genaro de Boconoíto', parroquia: 'Boconoíto' },
      { ciudad: 'San Rafael de Onoto', estado: 'Portuguesa', municipio: 'San Rafael de Onoto', parroquia: 'San Rafael de Onoto' },
      { ciudad: 'Santa Rosalía (El Playón)', estado: 'Portuguesa', municipio: 'Santa Rosalía', parroquia: 'El Playón' },
      { ciudad: 'Turén (Villa Bruzual)', estado: 'Portuguesa', municipio: 'Turén', parroquia: 'Villa Bruzual' },

      // Principales Ciudades y Capitales de Venezuela
      { ciudad: 'Caracas', estado: 'Distrito Capital', municipio: 'Libertador', parroquia: 'Catedral' },
      { ciudad: 'Maracaibo', estado: 'Zulia', municipio: 'Maracaibo', parroquia: 'Chiquinquirá' },
      { ciudad: 'Valencia', estado: 'Carabobo', municipio: 'Valencia', parroquia: 'San José' },
      { ciudad: 'Barquisimeto', estado: 'Lara', municipio: 'Iribarren', parroquia: 'Catedral' },
      { ciudad: 'Maracay', estado: 'Aragua', municipio: 'Girardot', parroquia: 'Madre María de San José' },
      { ciudad: 'Ciudad Guayana (Puerto Ordaz)', estado: 'Bolívar', municipio: 'Caroní', parroquia: 'Cachamay' },
      { ciudad: 'San Cristóbal', estado: 'Táchira', municipio: 'San Cristóbal', parroquia: 'San Juan Bautista' },
      { ciudad: 'Barcelona', estado: 'Anzoátegui', municipio: 'Simón Bolívar', parroquia: 'El Carmen' },
      { ciudad: 'Puerto La Cruz', estado: 'Anzoátegui', municipio: 'Juan Antonio Sotillo', parroquia: 'Puerto La Cruz' },
      { ciudad: 'Lechería', estado: 'Anzoátegui', municipio: 'Diego Bautista Urbaneja', parroquia: 'Lechería' },
      { ciudad: 'Maturín', estado: 'Monagas', municipio: 'Maturín', parroquia: 'San Simón' },
      { ciudad: 'Ciudad Bolívar', estado: 'Bolívar', municipio: 'Angostura del Orinoco', parroquia: 'Catedral' },
      { ciudad: 'Cumaná', estado: 'Sucre', municipio: 'Sucre', parroquia: 'Santa Inés' },
      { ciudad: 'Barinas', estado: 'Barinas', municipio: 'Barinas', parroquia: 'Barinas' },
      { ciudad: 'Cabimas', estado: 'Zulia', municipio: 'Cabimas', parroquia: 'Carmen Herrera' },
      { ciudad: 'Punto Fijo', estado: 'Falcón', municipio: 'Carirubana', parroquia: 'Carirubana' },
      { ciudad: 'Coro', estado: 'Falcón', municipio: 'Miranda', parroquia: 'San Gabriel' },
      { ciudad: 'Los Teques', estado: 'Miranda', municipio: 'Guaicaipuro', parroquia: 'Los Teques' },
      { ciudad: 'Mérida', estado: 'Mérida', municipio: 'Libertador', parroquia: 'Sagrario' },
      { ciudad: 'Guarenas', estado: 'Miranda', municipio: 'Plaza', parroquia: 'Guarenas' },
      { ciudad: 'Guatire', estado: 'Miranda', municipio: 'Zamora', parroquia: 'Guatire' },
      { ciudad: 'Carúpano', estado: 'Sucre', municipio: 'Bermúdez', parroquia: 'Santa Rosa' },
      { ciudad: 'El Tigre', estado: 'Anzoátegui', municipio: 'Simón Rodríguez', parroquia: 'Edmundo Barrios' },
      { ciudad: 'Anaco', estado: 'Anzoátegui', municipio: 'Anaco', parroquia: 'Anaco' },
      { ciudad: 'San Fernando de Apure', estado: 'Apure', municipio: 'San Fernando', parroquia: 'San Fernando' },
      { ciudad: 'San Felipe', estado: 'Yaracuy', municipio: 'San Felipe', parroquia: 'San Felipe' },
      { ciudad: 'Cabudare', estado: 'Lara', municipio: 'Palavecino', parroquia: 'Cabudare' },
      { ciudad: 'Carora', estado: 'Lara', municipio: 'Torres', parroquia: 'Trinidad Samuel' },
      { ciudad: 'El Tocuyo', estado: 'Lara', municipio: 'Morán', parroquia: 'Bolívar' },
      { ciudad: 'Quíbor', estado: 'Lara', municipio: 'Jiménez', parroquia: 'Juan Bautista Rodríguez' },
      { ciudad: 'San Juan de los Morros', estado: 'Guárico', municipio: 'Juan Germán Roscio', parroquia: 'San Juan de los Morros' },
      { ciudad: 'Calabozo', estado: 'Guárico', municipio: 'Francisco de Miranda', parroquia: 'Calabozo' },
      { ciudad: 'Valle de la Pascua', estado: 'Guárico', municipio: 'Leonardo Infante', parroquia: 'Valle de la Pascua' },
      { ciudad: 'Zaraza', estado: 'Guárico', municipio: 'Pedro Zaraza', parroquia: 'Zaraza' },
      { ciudad: 'Valera', estado: 'Trujillo', municipio: 'Valera', parroquia: 'Mercedes Díaz' },
      { ciudad: 'Trujillo', estado: 'Trujillo', municipio: 'Trujillo', parroquia: 'Chiquinquirá' },
      { ciudad: 'Boconó', estado: 'Trujillo', municipio: 'Boconó', parroquia: 'Boconó' },
      { ciudad: 'Ocumare del Tuy', estado: 'Miranda', municipio: 'Tomás Lander', parroquia: 'Ocumare del Tuy' },
      { ciudad: 'Cúa', estado: 'Miranda', municipio: 'Urdaneta', parroquia: 'Cúa' },
      { ciudad: 'Charallave', estado: 'Miranda', municipio: 'Cristóbal Rojas', parroquia: 'Charallave' },
      { ciudad: 'Santa Teresa del Tuy', estado: 'Miranda', municipio: 'Independencia', parroquia: 'Santa Teresa del Tuy' },
      { ciudad: 'Higuerote', estado: 'Miranda', municipio: 'Brión', parroquia: 'Higuerote' },
      { ciudad: 'San Antonio de los Altos', estado: 'Miranda', municipio: 'Los Salias', parroquia: 'San Antonio de los Altos' },
      { ciudad: 'Puerto Ayacucho', estado: 'Amazonas', municipio: 'Atures', parroquia: 'Fernando Girón Tovar' },
      { ciudad: 'Tucupita', estado: 'Delta Amacuro', municipio: 'Tucupita', parroquia: 'San José' },
      { ciudad: 'San Carlos', estado: 'Cojedes', municipio: 'Ezequiel Zamora', parroquia: 'San Carlos de Austria' },
      { ciudad: 'Tinaquillo', estado: 'Cojedes', municipio: 'Tinaquillo', parroquia: 'Tinaquillo' },
      { ciudad: 'Ciudad Ojeda', estado: 'Zulia', municipio: 'Lagunillas', parroquia: 'Alonso de Ojeda' },
      { ciudad: 'Machiques', estado: 'Zulia', municipio: 'Machiques de Perijá', parroquia: 'Libertad' },
      { ciudad: 'Santa Rita', estado: 'Zulia', municipio: 'Santa Rita', parroquia: 'Santa Rita' },
      { ciudad: 'Villa del Rosario', estado: 'Zulia', municipio: 'Rosario de Perijá', parroquia: 'El Rosario' },
      { ciudad: 'El Vigía', estado: 'Mérida', municipio: 'Alberto Adriani', parroquia: 'Presidente Betancourt' },
      { ciudad: 'Tovar', estado: 'Mérida', municipio: 'Tovar', parroquia: 'Tovar' },
      { ciudad: 'Ejido', estado: 'Mérida', municipio: 'Campo Elías', parroquia: 'Matriz' },
      { ciudad: 'La Grita', estado: 'Táchira', municipio: 'Jáuregui', parroquia: 'La Grita' },
      { ciudad: 'Rubio', estado: 'Táchira', municipio: 'Junín', parroquia: 'Rubio' },
      { ciudad: 'San Antonio del Táchira', estado: 'Táchira', municipio: 'Bolívar', parroquia: 'Bolívar' },
      { ciudad: 'Táriba', estado: 'Táchira', municipio: 'Cárdenas', parroquia: 'Táriba' },
      { ciudad: 'Cagua', estado: 'Aragua', municipio: 'Sucre', parroquia: 'Cagua' },
      { ciudad: 'La Victoria', estado: 'Aragua', municipio: 'José Félix Ribas', parroquia: 'La Victoria' },
      { ciudad: 'Turmero', estado: 'Aragua', municipio: 'Santiago Mariño', parroquia: 'Turmero' },
      { ciudad: 'Villa de Cura', estado: 'Aragua', municipio: 'Zamora', parroquia: 'Villa de Cura' },
      { ciudad: 'Palo Negro', estado: 'Aragua', municipio: 'Libertador', parroquia: 'Palo Negro' },
      { ciudad: 'Santa Cruz de Aragua', estado: 'Aragua', municipio: 'José Ángel Lamas', parroquia: 'Santa Cruz' },
      { ciudad: 'El Limón', estado: 'Aragua', municipio: 'Mario Briceño Iragorry', parroquia: 'El Limón' },
      { ciudad: 'Puerto Cabello', estado: 'Carabobo', municipio: 'Puerto Cabello', parroquia: 'Fraternidad' },
      { ciudad: 'Guacara', estado: 'Carabobo', municipio: 'Guacara', parroquia: 'Guacara' },
      { ciudad: 'Mariara', estado: 'Carabobo', municipio: 'Diego Ibarra', parroquia: 'Mariara' },
      { ciudad: 'San Joaquín', estado: 'Carabobo', municipio: 'San Joaquín', parroquia: 'San Joaquín' },
      { ciudad: 'Bejuma', estado: 'Carabobo', municipio: 'Bejuma', parroquia: 'Bejuma' },
      { ciudad: 'Morón', estado: 'Carabobo', municipio: 'Juan José Mora', parroquia: 'Morón' },
      { ciudad: 'Upata', estado: 'Bolívar', municipio: 'Piar', parroquia: 'Upata' },
      { ciudad: 'Caicara del Orinoco', estado: 'Bolívar', municipio: 'Cedeño', parroquia: 'Caicara del Orinoco' },
      { ciudad: 'Guasipati', estado: 'Bolívar', municipio: 'Roscio', parroquia: 'Guasipati' },
      { ciudad: 'Tumeremo', estado: 'Bolívar', municipio: 'Sifontes', parroquia: 'Tumeremo' },
      { ciudad: 'Santa Elena de Uairén', estado: 'Bolívar', municipio: 'Gran Sabana', parroquia: 'Santa Elena de Uairén' },
      { ciudad: 'Chivacoa', estado: 'Yaracuy', municipio: 'Bruzual', parroquia: 'Chivacoa' },
      { ciudad: 'Yaritagua', estado: 'Yaracuy', municipio: 'Peña', parroquia: 'Yaritagua' },
      { ciudad: 'Nirgua', estado: 'Yaracuy', municipio: 'Nirgua', parroquia: 'Nirgua' },
      { ciudad: 'Guasdualito', estado: 'Apure', municipio: 'Páez', parroquia: 'Guasdualito' },
      { ciudad: 'Achaguas', estado: 'Apure', municipio: 'Achaguas', parroquia: 'Achaguas' },
      { ciudad: 'Santa Bárbara de Barinas', estado: 'Barinas', municipio: 'Ezequiel Zamora', parroquia: 'Santa Bárbara' },
      { ciudad: 'Socopó', estado: 'Barinas', municipio: 'Antonio José de Sucre', parroquia: 'Ticoporo' },
      { ciudad: 'Ciudad Bolivia (Pedraza)', estado: 'Barinas', municipio: 'Pedraza', parroquia: 'Ciudad Bolivia' },
      { ciudad: 'Barinitas', estado: 'Barinas', municipio: 'Bolívar', parroquia: 'Barinitas' },
      { ciudad: 'Churuguara', estado: 'Falcón', municipio: 'Federación', parroquia: 'Churuguara' },
      { ciudad: 'Dabajuro', estado: 'Falcón', municipio: 'Dabajuro', parroquia: 'Dabajuro' },
      { ciudad: 'Chichiriviche', estado: 'Falcón', municipio: 'Monseñor Iturriza', parroquia: 'Chichiriviche' },
      { ciudad: 'Tucacas', estado: 'Falcón', municipio: 'Silva', parroquia: 'Tucacas' },
      { ciudad: 'Puerto Píritu', estado: 'Anzoátegui', municipio: 'Fernando de Peñalver', parroquia: 'Puerto Píritu' },
      { ciudad: 'Pariaguán', estado: 'Anzoátegui', municipio: 'Francisco de Miranda', parroquia: 'Pariaguán' },
      { ciudad: 'Cantaura', estado: 'Anzoátegui', municipio: 'Pedro María Freites', parroquia: 'Cantaura' },
      { ciudad: 'Punta de Mata', estado: 'Monagas', municipio: 'Ezequiel Zamora', parroquia: 'Punta de Mata' },
      { ciudad: 'Caripe', estado: 'Monagas', municipio: 'Caripe', parroquia: 'Caripe' },
      { ciudad: 'Caripito', estado: 'Monagas', municipio: 'Bolívar', parroquia: 'Caripito' },
      { ciudad: 'Güiria', estado: 'Sucre', municipio: 'Valdez', parroquia: 'Güiria' },
      { ciudad: 'Cariaco', estado: 'Sucre', municipio: 'Ribero', parroquia: 'Cariaco' },
      { ciudad: 'Porlamar', estado: 'Nueva Esparta', municipio: 'Mariño', parroquia: 'Porlamar' },
      { ciudad: 'Pampatar', estado: 'Nueva Esparta', municipio: 'Maneiro', parroquia: 'Pampatar' },
      { ciudad: 'La Asunción', estado: 'Nueva Esparta', municipio: 'Arismendi', parroquia: 'La Asunción' },
      { ciudad: 'Juan Griego', estado: 'Nueva Esparta', municipio: 'Marcano', parroquia: 'Juan Griego' },
      { ciudad: 'La Guaira', estado: 'La Guaira', municipio: 'Vargas', parroquia: 'La Guaira' },
      { ciudad: 'Catia La Mar', estado: 'La Guaira', municipio: 'Vargas', parroquia: 'Catia La Mar' },
      { ciudad: 'Maiquetía', estado: 'La Guaira', municipio: 'Vargas', parroquia: 'Maiquetía' },
      { ciudad: 'Caraballeda', estado: 'La Guaira', municipio: 'Vargas', parroquia: 'Caraballeda' }
    ];

    function inicializarGeografiaVenezuela() {
      const selCiudad = document.getElementById('cit-ciudad');
      const selMun = document.getElementById('cit-municipio');
      const selParr = document.getElementById('cit-parroquia');
      if (!selCiudad || !selMun || !selParr) return;

      const geo = (window.venezuelaGeoData && Object.keys(window.venezuelaGeoData).length) ? window.venezuelaGeoData : (window.VENEZUELA_GEO || {});
      const listaCiudades = [...window.CIUDADES_VENEZUELA].sort((a, b) => a.ciudad.localeCompare(b.ciudad));

      selCiudad.innerHTML = '<option value="">-- Seleccione Ciudad --</option>';
      listaCiudades.forEach(c => {
        const opt = document.createElement('option');
        opt.value = c.ciudad;
        opt.textContent = c.ciudad;
        if (c.ciudad === 'Acarigua') opt.selected = true;
        selCiudad.appendChild(opt);
      });
      // Opción Otra al final
      const optOtraCiudad = document.createElement('option');
      optOtraCiudad.value = 'Otra';
      optOtraCiudad.textContent = 'Otra Ciudad';
      selCiudad.appendChild(optOtraCiudad);

      function actualizarMunicipios(estadoSel, munPreseleccionar = null, parrPreseleccionar = null) {
        selMun.innerHTML = '<option value="">-- Seleccione Municipio --</option>';
        selParr.innerHTML = '<option value="">-- Seleccione Parroquia --</option>';

        const g = (window.venezuelaGeoData && Object.keys(window.venezuelaGeoData).length) ? window.venezuelaGeoData : (window.VENEZUELA_GEO || {});
        if (!estadoSel || !g[estadoSel]) {
          // Si el estado no existe en el catálogo, permitir Páez por defecto y opción Otro
          const optDef = document.createElement('option');
          optDef.value = 'Páez';
          optDef.textContent = 'Páez';
          optDef.selected = true;
          selMun.appendChild(optDef);
          const optOtroM = document.createElement('option');
          optOtroM.value = 'Otro';
          optOtroM.textContent = 'Otro Municipio';
          selMun.appendChild(optOtroM);
          actualizarParroquias('Portuguesa', 'Páez', parrPreseleccionar);
          return;
        }

        const municipios = Object.keys(g[estadoSel]).sort();
        municipios.forEach(m => {
          const opt = document.createElement('option');
          opt.value = m;
          opt.textContent = m;
          if (munPreseleccionar && m === munPreseleccionar) {
            opt.selected = true;
          } else if (!munPreseleccionar && estadoSel === 'Portuguesa' && m === 'Páez') {
            opt.selected = true;
          }
          selMun.appendChild(opt);
        });

        // Opción Otro Municipio
        const optOtroMun = document.createElement('option');
        optOtroMun.value = 'Otro';
        optOtroMun.textContent = 'Otro Municipio';
        selMun.appendChild(optOtroMun);

        actualizarParroquias(estadoSel, selMun.value, parrPreseleccionar);
      }

      function actualizarParroquias(estadoSel, munSel, parrPreseleccionar = null) {
        selParr.innerHTML = '<option value="">-- Seleccione Parroquia --</option>';
        const g = (window.venezuelaGeoData && Object.keys(window.venezuelaGeoData).length) ? window.venezuelaGeoData : (window.VENEZUELA_GEO || {});
        
        if (munSel && munSel !== 'Otro' && g[estadoSel] && g[estadoSel][munSel]) {
          const parroquias = g[estadoSel][munSel];
          parroquias.forEach(p => {
            const opt = document.createElement('option');
            opt.value = p;
            opt.textContent = p;
            if (parrPreseleccionar && p === parrPreseleccionar) {
              opt.selected = true;
            } else if (!parrPreseleccionar && munSel === 'Páez' && p === 'Acarigua') {
              opt.selected = true;
            }
            selParr.appendChild(opt);
          });
        } else {
          // Si es Páez o desconocido
          const optDefP = document.createElement('option');
          optDefP.value = 'Acarigua';
          optDefP.textContent = 'Acarigua';
          optDefP.selected = true;
          selParr.appendChild(optDefP);
        }

        // Opción Otra Parroquia
        const optOtraParr = document.createElement('option');
        optOtraParr.value = 'Otra';
        optOtraParr.textContent = 'Otra Parroquia';
        selParr.appendChild(optOtraParr);
      }

      function actualizarPorCiudad(ciudadNombre, munDeseado = null, parrDeseado = null) {
        const item = window.CIUDADES_VENEZUELA.find(c => c.ciudad.toLowerCase() === (ciudadNombre || '').toLowerCase());
        const estadoDestino = item ? item.estado : 'Portuguesa';
        const munDestino = munDeseado || (item ? item.municipio : 'Páez');
        const parrDestino = parrDeseado || (item ? item.parroquia : 'Acarigua');
        actualizarMunicipios(estadoDestino, munDestino, parrDestino);
      }

      window.actualizarMunicipiosCiudadano = actualizarMunicipios;
      window.actualizarParroquiasCiudadano = actualizarParroquias;
      window.actualizarPorCiudadCiudadano = actualizarPorCiudad;

      selCiudad.addEventListener('change', function() {
        actualizarPorCiudad(this.value);
        this.classList.remove('invalid');
        const err = document.getElementById('cit-ciudad-err');
        if (err) err.classList.remove('visible');
      });

      selMun.addEventListener('change', function() {
        const item = window.CIUDADES_VENEZUELA.find(c => c.ciudad.toLowerCase() === (selCiudad.value || '').toLowerCase());
        const est = item ? item.estado : 'Portuguesa';
        actualizarParroquias(est, this.value);
        this.classList.remove('invalid');
        const err = document.getElementById('cit-municipio-err');
        if (err) err.classList.remove('visible');
      });

      selParr.addEventListener('change', function() {
        this.classList.remove('invalid');
        const err = document.getElementById('cit-parroquia-err');
        if (err) err.classList.remove('visible');
      });

      // Inicialización por defecto: Acarigua -> Páez -> Acarigua
      actualizarPorCiudad('Acarigua', 'Páez', 'Acarigua');
    }

    /* ── Configuración de visibilidad para campos condicionales "Otra" / "Otro" ── */
    function configurarOpcionesOtras() {
      const mapeo = [
        { selectId: 'cit-ecivil', inputId: 'cit-ecivil-otro', triggerVal: 'Otro' },
        { selectId: 'cit-profesion', inputId: 'cit-profesion-otra', triggerVal: 'Otra' },
        { selectId: 'cit-ciudad', inputId: 'cit-ciudad-otra', triggerVal: 'Otra' },
        { selectId: 'cit-municipio', inputId: 'cit-municipio-otro', triggerVal: 'Otro' },
        { selectId: 'cit-parroquia', inputId: 'cit-parroquia-otra', triggerVal: 'Otra' },
      ];

      mapeo.forEach(item => {
        const sel = document.getElementById(item.selectId);
        const inp = document.getElementById(item.inputId);
        if (!sel || !inp) return;

        sel.addEventListener('change', function() {
          if (this.value === item.triggerVal) {
            inp.style.display = 'block';
            inp.focus();
          } else {
            inp.style.display = 'none';
            inp.value = '';
          }
        });
      });
    }

    /* ── Autocompletado de confirmación de correo electrónico ── */
    function autocompletarConfirmacionCorreo() {
      const c1 = document.getElementById('cit-correo');
      const c2 = document.getElementById('cit-correo2');
      if (c1 && c2) {
        c2.value = c1.value.trim();
        const err = document.getElementById('cit-correo2-err');
        if (err) err.classList.remove('visible');
        c2.classList.remove('invalid');
        c2.classList.add('valid');
      }
    }

    function configurarAutocompletadoCorreo() {
      const c1 = document.getElementById('cit-correo');
      const c2 = document.getElementById('cit-correo2');
      if (!c1 || !c2) return;

      c1.addEventListener('input', function() {
        if (!c2.dataset.manual || c2.dataset.manual === '0') {
          c2.value = c1.value;
          const err = document.getElementById('cit-correo2-err');
          if (err && c2.value.trim() === c1.value.trim()) {
            err.classList.remove('visible');
            c2.classList.remove('invalid');
          }
        }
      });

      c2.addEventListener('input', function() {
        c2.dataset.manual = '1';
      });

      c1.addEventListener('blur', function() {
        if (!c2.value.trim() || c2.dataset.manual !== '1') {
          c2.value = c1.value.trim();
          c2.dataset.manual = '0';
          const err = document.getElementById('cit-correo2-err');
          if (err && c2.value.trim() === c1.value.trim()) {
            err.classList.remove('visible');
            c2.classList.remove('invalid');
          }
        }
      });
    }

    /* ── Validación en tiempo real para la fecha estimada de aprobación del proyecto (no futura y máx. 5 años atrás) ── */
    function validarFechaProyecto(input) {
      if (!input) return;
      const err = document.getElementById('proy-fecha-err');
      const val = input.value;
      if (!val) {
        if (err) {
          err.textContent = 'Seleccione la fecha estimada de aprobación.';
        }
        return;
      }
      const dHoy = new Date();
      const hoy = dHoy.getFullYear() + '-' + String(dHoy.getMonth() + 1).padStart(2, '0') + '-' + String(dHoy.getDate()).padStart(2, '0');
      const dMin = new Date(dHoy);
      dMin.setFullYear(dMin.getFullYear() - 5);
      const minFecha = dMin.getFullYear() + '-' + String(dMin.getMonth() + 1).padStart(2, '0') + '-' + String(dMin.getDate()).padStart(2, '0');

      if (val > hoy) {
        if (err) {
          err.textContent = 'La fecha estimada de aprobación no puede ser una fecha futura.';
          err.classList.add('visible');
          err.style.display = 'block';
        }
        input.classList.add('invalid');
      } else if (val < minFecha) {
        if (err) {
          err.textContent = 'La fecha estimada de aprobación no puede exceder los 5 años de antigüedad.';
          err.classList.add('visible');
          err.style.display = 'block';
        }
        input.classList.add('invalid');
      } else {
        if (err) {
          err.textContent = 'Seleccione la fecha estimada de aprobación.';
          err.classList.remove('visible');
          err.style.display = 'none';
        }
        input.classList.remove('invalid');
      }
    }

    /* ── Indicación informativa del calendario de fecha de nacimiento ── */
    function toggleTooltipCalendario(e) {
      if (e) {
        e.preventDefault();
        e.stopPropagation();
      }
      const popover = document.getElementById('popover-info-calendario');
      const btn = document.getElementById('btn-info-calendario');
      if (!popover) return;
      const isVisible = popover.style.display === 'block';
      if (isVisible) {
        popover.style.display = 'none';
        if (btn) {
          btn.style.background = '#e8f0fe';
          btn.style.color = '#1565c0';
        }
      } else {
        popover.style.display = 'block';
        if (btn) {
          btn.style.background = '#1565c0';
          btn.style.color = '#ffffff';
        }
      }
    }

    function cerrarTooltipCalendario(e) {
      if (e) {
        e.preventDefault();
        e.stopPropagation();
      }
      const popover = document.getElementById('popover-info-calendario');
      const btn = document.getElementById('btn-info-calendario');
      if (popover) popover.style.display = 'none';
      if (btn) {
        btn.style.background = '#e8f0fe';
        btn.style.color = '#1565c0';
      }
    }

    document.addEventListener('click', function (e) {
      const popover = document.getElementById('popover-info-calendario');
      const btn = document.getElementById('btn-info-calendario');
      if (popover && popover.style.display === 'block') {
        if (!popover.contains(e.target) && (!btn || !btn.contains(e.target))) {
          popover.style.display = 'none';
          if (btn) {
            btn.style.background = '#e8f0fe';
            btn.style.color = '#1565c0';
          }
        }
      }
    });

    /* ── Confirmación al avanzar: primero se valida el paso y, si está correcto, se pide confirmar ── */
    function mostrarModalConfirmacion(paso) {
      // Los pasos que no aplican al tipo de trámite se saltan sin validar
      const aplica = (paso === 3 || paso === 5) ? pasoAplica(paso) : true;
      if (aplica && !validarPaso(paso)) return;

      pasoPendienteValidacion = paso;

      // Personalización del texto del modal
      const radioTramite = document.querySelector('input[name="tipo_tramite"]:checked');
      const esSugerencia = radioTramite && radioTramite.value === 'sugerencia';

      if (esSugerencia && paso === 5) {
        document.getElementById('modal-texto-pregunta').innerHTML =
          `¿Confirma que ha revisado correctamente los datos correspondientes al <strong>Paso 5</strong> y desea continuar con el proceso?`;
      } else if (!pasoAplica(3) && (paso === 3 || paso === 4)) {
        document.getElementById('modal-texto-pregunta').innerHTML =
          `¿Confirma que ha revisado correctamente los datos correspondientes al <strong>Paso ${paso}</strong> y desea continuar con el proceso?`;
      } else {
        document.getElementById('modal-texto-pregunta').innerHTML =
          `¿Confirma que ha revisado correctamente los datos correspondientes al <strong>Paso ${paso} (${NOMBRES_PASOS[paso - 1]})</strong> y desea continuar con el proceso?`;
      }

      if (instanciaModal) instanciaModal.show();
      else ejecutarPasoSiguiente();   // si el modal no cargó, no se bloquea el avance
    }

    function ejecutarPasoSiguiente() {
      if (instanciaModal) instanciaModal.hide();
      const paso = pasoPendienteValidacion;

      // Paso 3 no aplica (reclamo, petición, sugerencia): se pasa directo a los hechos
      if (paso === 3 && !pasoAplica(3)) {
        limpiarErroresPaso(3);
        pasoActual = 4;
        mostrarPaso(4);
        actualizarAvisoContexto();
        return;
      }
      // Paso 5 no aplica (sugerencia): se pasa directo a la revisión
      if (paso === 5 && !pasoAplica(5)) {
        limpiarErroresPaso(5);
        poblarResumen();
        actualizarResumenFinal();
        pasoActual = 6;
        mostrarPaso(6);
        return;
      }

      siguientePaso(paso);   // valida y avanza (wizard.js)
      if (pasoActual === paso + 1) {
        if (pasoActual === 2) setTimeout(inicializarMapaCiudadano, 150);
        if (pasoActual === 3) setTimeout(inicializarMapaSenalado, 150);
        if (paso === 3) actualizarAvisoContexto();
        if (paso === 5) actualizarResumenFinal();
      }
    }

    /* ── Consulta del estado de un trámite ── */
    function mostrarConsultaTramite() {
      document.getElementById('vista-seleccion').style.display = 'none';
      document.getElementById('vista-estado-tramite').style.display = 'block';
    }

    function volverSeleccionDesdeEstado() {
      document.getElementById('vista-estado-tramite').style.display = 'none';
      document.getElementById('vista-seleccion').style.display = 'block';
      document.getElementById('resultado-estado-container').style.display = 'none';
      document.getElementById('input-codigo-consulta').value = '';
      document.getElementById('err-codigo-consulta').classList.remove('visible');
      const elObs = document.getElementById('res-est-observacion');
      if (elObs) elObs.textContent = '-';
    }

    async function consultarEstadoTramite() {
      const codigo = document.getElementById('input-codigo-consulta').value.trim().toUpperCase();
      const containerRes = document.getElementById('resultado-estado-container');
      const err = document.getElementById('err-codigo-consulta');

      if (!codigo) {
        err.textContent = 'Por favor ingrese un código válido.';
        err.classList.add('visible');
        containerRes.style.display = 'none';
        return;
      }
      err.classList.remove('visible');
      containerRes.style.display = 'none';

      const resp = await fetch('{{ route('denuncias.estado') }}?codigo=' + encodeURIComponent(codigo), { headers: { 'Accept': 'application/json' } })
        .catch(() => null);
      if (!resp || !resp.ok) {
        err.textContent = resp && resp.status === 404 ? 'No existe un trámite con ese código.'
          : resp && resp.status === 429 ? 'Demasiadas consultas. Espere un minuto.'
          : 'No se pudo conectar con el servidor. Intente de nuevo.';
        err.classList.add('visible');
        return;
      }
      const r = await resp.json();
      document.getElementById('res-est-codigo').textContent = r.case_number;
      document.getElementById('res-est-tipo').textContent = r.tipo;
      document.getElementById('res-est-fecha').textContent = r.fecha;

      // Verificar si está en tramitación o revisión (por código PROCESSING, IN_REVIEW o por texto)
      const estatusNorm = (r.estatus || '').toLowerCase().normalize('NFD').replace(/[\u0300-\u036f]/g, '');
      const esEnRevision = ['IN_REVIEW', 'PROCESSING'].includes(r.estatus_codigo) || estatusNorm.includes('revision') || estatusNorm.includes('tramitacion');

      // Tipos de trámites que requieren o admiten evidencias/documentos (todos excepto la sugerencia)
      const tipoNorm = (r.tipo || '').toLowerCase().normalize('NFD').replace(/[\u0300-\u036f]/g, '');
      const tipoCod = (r.tipo_codigo || '').toUpperCase();
      const requiereEvidencias = (tipoCod !== 'SUGGESTION') && !tipoNorm.includes('sugerencia');

      const elEstado = document.getElementById('res-est-estado');
      if (esEnRevision && requiereEvidencias) {
        elEstado.innerHTML = `${r.estatus} <span style="font-size:0.82rem; font-weight:normal; color:#c62828; display:inline-block; margin-top:2px;">— Debe asistir a la sede de la Contraloría a consignar las evidencias</span>`;
        elEstado.style.color = '#1565c0';
      } else {
        elEstado.textContent = r.estatus;
        elEstado.style.color = r.cerrado ? '#2e7d32' : '#1565c0';
      }

      const elObs = document.getElementById('res-est-observacion');
      if (elObs) {
        if (r.observaciones && r.observaciones.length > 1) {
          elObs.innerHTML = r.observaciones.map(o => `• <span style="font-weight:600; color:#1a2340;">[${escaparHtml(o.fecha)}]:</span> ${escaparHtml(o.texto)}`).join('<br><br>');
          elObs.style.fontStyle = 'normal';
          elObs.style.color = '#333';
        } else if (r.observacion) {
          elObs.textContent = r.observacion;
          elObs.style.fontStyle = 'normal';
          elObs.style.color = '#333';
        } else {
          elObs.textContent = 'Sin observaciones registradas a la fecha.';
          elObs.style.fontStyle = 'italic';
          elObs.style.color = '#666';
        }
      }

      // 1. Mostrar Ubicación Geográfica del Señalado y enlace a Google Maps
      const filaUbicacion = document.getElementById('fila-est-ubicacion');
      const textoUbicacion = document.getElementById('res-est-ubicacion-texto');
      const linkGmaps = document.getElementById('res-est-gmaps-link');
      if (r.incident_location || (r.incident_lat && r.incident_lng)) {
        let txt = r.incident_location || 'Coordenadas registradas';
        if (r.incident_lat && r.incident_lng) {
          txt += ` (Lat: ${Number(r.incident_lat).toFixed(5)}, Lng: ${Number(r.incident_lng).toFixed(5)})`;
        }
        textoUbicacion.textContent = txt;
        if (r.google_maps_url) {
          linkGmaps.href = r.google_maps_url;
          linkGmaps.style.display = 'inline-block';
        } else {
          linkGmaps.style.display = 'none';
        }
        filaUbicacion.style.display = 'flex';
      } else {
        filaUbicacion.style.display = 'none';
      }

      // 2. Módulo de Regla de Negocio: Lapso de Anticipación para Confirmar Datos
      window.currentTrackingCode = r.tracking_code || codigo;
      const bloqueLapso = document.getElementById('bloque-confirmacion-denunciante');
      const titLapso = document.getElementById('titulo-confirmacion-lapso');
      const txtLapso = document.getElementById('texto-confirmacion-lapso');
      const accLapso = document.getElementById('acciones-confirmacion-datos');
      const fdbLapso = document.getElementById('feedback-confirmacion-datos');
      fdbLapso.style.display = 'none';

      if (r.data_confirmation_deadline) {
        bloqueLapso.style.display = 'block';
        if (r.confirmacion_completada) {
          bloqueLapso.style.borderLeftColor = '#16a34a';
          titLapso.innerHTML = '<span style="color:#16a34a;">✓ Conformidad de Datos Validada</span>';
          txtLapso.innerHTML = `El denunciante revisó y validó la conformidad de sus datos registrados el <strong>${escaparHtml(r.data_confirmed_at)}</strong>.`;
          accLapso.style.display = 'none';
        } else if (r.confirmacion_vigente) {
          bloqueLapso.style.borderLeftColor = '#2563eb';
          titLapso.innerHTML = `Lapso de Anticipación para Confirmar Datos <span style="background:#dbeafe; color:#1d4ed8; font-size:0.8rem; padding:2px 8px; border-radius:4px; font-weight:600;">${r.dias_restantes_confirmacion} día(s) restante(s)</span>`;
          txtLapso.innerHTML = `De acuerdo con la normativa de la Contraloría, dispone de un lapso hasta el <strong>${escaparHtml(r.data_confirmation_deadline)}</strong> para revisar la información de su solicitud y confirmar su conformidad o registrar rectificaciones antes de que el expediente sea formalizado formalmente.`;
          accLapso.style.display = 'block';
        } else {
          bloqueLapso.style.borderLeftColor = '#64748b';
          titLapso.innerHTML = '<span style="color:#475569;">Lapso de Anticipación Concluido</span>';
          txtLapso.innerHTML = `El lapso inicial otorgado al denunciante concluyó el <strong>${escaparHtml(r.data_confirmation_deadline)}</strong>. El trámite continúa en su curso legal de gestión.`;
          accLapso.style.display = 'none';
        }
      } else {
        bloqueLapso.style.display = 'none';
      }

      // 3. Respuestas Institucionales de la Contraloría
      const filaResp = document.getElementById('fila-respuesta-ciudadano');
      const divResp = document.getElementById('res-respuestas-ciudadano');
      if (r.respuestas_ciudadano && r.respuestas_ciudadano.length > 0) {
        divResp.innerHTML = r.respuestas_ciudadano.map(rc => `
          <div style="background:#f8fafc; border:1px solid #dce3ec; border-radius:6px; padding:12px; margin-bottom:10px;">
            <div style="font-size:0.82rem; color:#64748b; margin-bottom:4px; font-weight:600;">${escaparHtml(rc.fecha)} · ${escaparHtml(rc.tipo)}</div>
            <div style="color:#0f172a; white-space:pre-wrap; font-size:0.92rem; line-height:1.4;">${escaparHtml(rc.mensaje)}</div>
            <div style="font-size:0.8rem; color:#64748b; margin-top:6px; border-top:1px solid #e8edf2; padding-top:4px;">${escaparHtml(rc.firmante)}</div>
          </div>
        `).join('');
        filaResp.style.display = 'block';
      } else {
        filaResp.style.display = 'none';
      }

      containerRes.style.display = 'block';
    }

    async function enviarConfirmacionDatos() {
      const tracking = window.currentTrackingCode;
      if (!tracking) return;
      const btn = document.getElementById('btn-confirmar-datos');
      const txtRectif = document.getElementById('input-rectificacion-denunciante').value.trim();
      const feedback = document.getElementById('feedback-confirmacion-datos');

      if (btn) btn.disabled = true;
      feedback.style.display = 'none';

      try {
        const url = '{{ route('denuncias.confirmar-datos', ':tracking') }}'.replace(':tracking', encodeURIComponent(tracking));
        const res = await fetch(url, {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Accept': 'application/json',
          },
          body: JSON.stringify({ observacion_rectificacion: txtRectif })
        });
        const j = await res.json();
        if (res.ok) {
          document.getElementById('acciones-confirmacion-datos').style.display = 'none';
          document.getElementById('titulo-confirmacion-lapso').innerHTML = '<span style="color:#16a34a;">✓ Conformidad de Datos Validada</span>';
          document.getElementById('texto-confirmacion-lapso').innerHTML = `El denunciante revisó y confirmó la conformidad de sus datos registrados el <strong>${j.data_confirmed_at || 'hoy'}</strong>.`;
          document.getElementById('bloque-confirmacion-denunciante').style.borderLeftColor = '#16a34a';
          feedback.textContent = j.mensaje || '¡Conformidad registrada con éxito!';
          feedback.style.display = 'block';
        } else {
          alert(j.mensaje || 'No se pudo registrar la confirmación.');
          if (btn) btn.disabled = false;
        }
      } catch (e) {
        alert('Error al conectar con el servidor.');
        if (btn) btn.disabled = false;
      }
    }

    /* ── Componente de Geolocalización Interactiva para el Ciudadano (Paso 2) ── */
    let mapaCiudadano = null;
    let marcadorCiudadano = null;

    function inicializarMapaCiudadano() {
      const contenedor = document.getElementById('mapa-ciudadano');
      if (!contenedor || typeof L === 'undefined') return;

      if (mapaCiudadano) {
        setTimeout(() => mapaCiudadano.invalidateSize(), 100);
        setTimeout(() => mapaCiudadano.invalidateSize(), 400);
        return;
      }

      // Coordenadas iniciales: Municipio Páez (Acarigua), Portuguesa
      const defaultLat = 9.5558;
      const defaultLng = -69.1994;

      mapaCiudadano = L.map('mapa-ciudadano', {
        maxBounds: [[0.3, -74.0], [16.2, -58.0]],
        maxBoundsViscosity: 0.8
      }).setView([defaultLat, defaultLng], 14);

      L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        maxZoom: 19,
        attribution: '© OpenStreetMap | Integración Google Maps'
      }).addTo(mapaCiudadano);

      setTimeout(() => { if (mapaCiudadano) mapaCiudadano.invalidateSize(); }, 200);
      setTimeout(() => { if (mapaCiudadano) mapaCiudadano.invalidateSize(); }, 600);

      marcadorCiudadano = L.marker([defaultLat, defaultLng], {
        draggable: true,
        title: 'Arrastre el marcador para fijar su ubicación y autocompletar Estado, Municipio y Parroquia'
      }).addTo(mapaCiudadano);

      function actualizarCoordenadasCiudadano(lat, lng, consultarGeocodificacion = false) {
        const inputLat = document.getElementById('cit-lat');
        const inputLng = document.getElementById('cit-lng');
        const textoLat = document.getElementById('cit-lat-texto');
        const textoLng = document.getElementById('cit-lng-texto');
        const contenedorMapa = document.getElementById('contenedor-mapa-ciudadano');
        const errMapa = document.getElementById('cit-mapa-err');
        const st = document.getElementById('cit-geo-status');

        if (inputLat) inputLat.value = lat.toFixed(7);
        if (inputLng) inputLng.value = lng.toFixed(7);
        if (textoLat) textoLat.textContent = lat.toFixed(5);
        if (textoLng) textoLng.textContent = lng.toFixed(5);

        const btnGmaps = document.getElementById('btn-abrir-google-maps-ciudadano');
        if (btnGmaps) {
          btnGmaps.href = `https://www.google.com/maps?q=${lat},${lng}`;
          btnGmaps.style.display = 'inline-block';
        }

        // Validación inmediata de límites geográficos de Venezuela
        const fnVal = typeof esCoordenadaEnVenezuela === 'function' ? esCoordenadaEnVenezuela : (typeof window.esCoordenadaEnVenezuela === 'function' ? window.esCoordenadaEnVenezuela : null);
        const enVzla = fnVal ? fnVal(lat, lng) : (lat >= 0.5 && lat <= 16.0 && lng >= -73.6 && lng <= -58.0);

        if (!enVzla) {
          if (inputLat) inputLat.dataset.fueraDeVenezuela = '1';
          if (contenedorMapa) contenedorMapa.style.border = '2px solid #ef4444';
          if (errMapa) {
            errMapa.textContent = 'La ubicación seleccionada está fuera de Venezuela. No se permite continuar hasta ubicar un punto dentro del país.';
            errMapa.classList.add('visible');
          }
          if (st) {
            st.innerHTML = '<span style="color:#dc2626; font-weight:bold;">Ubicación fuera de Venezuela. Seleccione un punto dentro del territorio nacional.</span>';
          }
          return;
        }

        // Ubicación válida preliminarmente
        if (inputLat) inputLat.dataset.fueraDeVenezuela = '0';
        if (contenedorMapa) contenedorMapa.style.border = '1px solid #dce3ec';
        if (errMapa) errMapa.classList.remove('visible');

        if (consultarGeocodificacion) {
          if (st) st.textContent = 'Detectando Ciudad, Municipio y Parroquia...';

          // Detección preliminar inmediata por proximidad de coordenadas en Portuguesa
          if (lat >= 9.48 && lat <= 9.62 && lng >= -69.25 && lng <= -69.15) {
            const sc = document.getElementById('cit-ciudad');
            if (sc) {
              sc.value = 'Acarigua';
              sc.classList.remove('invalid');
              document.getElementById('cit-ciudad-err')?.classList.remove('visible');
            }
          }

          fetch(`https://nominatim.openstreetmap.org/reverse?format=json&lat=${lat}&lon=${lng}&zoom=18&addressdetails=1`)
            .then(res => res.json())
            .then(data => {
              const addr = data.address || {};
              const cCode = (addr.country_code || '').toLowerCase();
              if (cCode && cCode !== 've') {
                if (inputLat) inputLat.dataset.fueraDeVenezuela = '1';
                if (contenedorMapa) contenedorMapa.style.border = '2px solid #ef4444';
                if (errMapa) {
                  errMapa.textContent = `La ubicación seleccionada pertenece a ${addr.country || 'otro país'}. Debe seleccionar un punto dentro de Venezuela.`;
                  errMapa.classList.add('visible');
                }
                if (st) {
                  st.innerHTML = `<span style="color:#dc2626; font-weight:bold;">⚠️ Ubicación fuera de Venezuela (${addr.country || 'otro país'}). Seleccione un punto nacional.</span>`;
                }
                return;
              }
              if (inputLat) inputLat.dataset.fueraDeVenezuela = '0';
              if (contenedorMapa) contenedorMapa.style.border = '1px solid #dce3ec';
              if (errMapa) errMapa.classList.remove('visible');
              autocompletarUbicacionCiudadanoDesdeGeo(data, lat, lng);
            })
            .catch(() => {
              if (st) st.textContent = 'Coordenadas fijadas';
            });
        }
      }

      marcadorCiudadano.on('dragend', function(e) {
        const pos = e.target.getLatLng();
        actualizarCoordenadasCiudadano(pos.lat, pos.lng, true);
      });

      mapaCiudadano.on('click', function(e) {
        marcadorCiudadano.setLatLng(e.latlng);
        actualizarCoordenadasCiudadano(e.latlng.lat, e.latlng.lng, true);
      });

      actualizarCoordenadasCiudadano(defaultLat, defaultLng, false);
    }

    function autocompletarUbicacionCiudadanoDesdeGeo(data, lat, lng) {
      if (!data) return;
      const addr = data.address || {};
      const geo = (window.venezuelaGeoData && Object.keys(window.venezuelaGeoData).length) ? window.venezuelaGeoData : (window.VENEZUELA_GEO || {});
      const estados = Object.keys(geo);
      const ciudadesLista = window.CIUDADES_VENEZUELA || [];

      function normalizar(str) {
        if (!str) return '';
        return str.toString()
          .normalize('NFD')
          .replace(/[\u0300-\u036f]/g, '')
          .toLowerCase()
          .replace(/\b(ciudad|estado|municipio|parroquia|de|del|la|las|los|el)\b/gi, '')
          .replace(/[^a-z0-9]/g, '')
          .trim();
      }

      const rawCity = addr.city || addr.town || addr.village || addr.city_district || addr.suburb || addr.municipality || '';
      const rawMun = addr.county || addr.municipality || addr.city || addr.town || '';
      const rawParr = addr.suburb || addr.quarter || addr.neighbourhood || addr.village || addr.city_district || addr.town || addr.city || '';
      const rawState = addr.state || '';

      const normCity = normalizar(rawCity);
      const normMun = normalizar(rawMun);
      const normParr = normalizar(rawParr);
      const normState = normalizar(rawState);

      // 1. Detección de Ciudad
      let ciudadEncontrada = null;
      let municipioEncontrado = null;
      let parroquiaEncontrada = null;
      let estadoEncontrado = null;

      // Zonas de alta exactitud geográfica en Portuguesa (Acarigua, Araure, Guanare)
      if (lat >= 9.48 && lat <= 9.62 && lng >= -69.25 && lng <= -69.15) {
        ciudadEncontrada = 'Acarigua';
        municipioEncontrado = 'Páez';
        estadoEncontrado = 'Portuguesa';
      } else if (lat >= 9.53 && lat <= 9.65 && lng >= -69.32 && lng <= -69.21) {
        ciudadEncontrada = 'Araure';
        municipioEncontrado = 'Araure';
        estadoEncontrado = 'Portuguesa';
      } else if (lat >= 8.95 && lat <= 9.15 && lng >= -69.85 && lng <= -69.65) {
        ciudadEncontrada = 'Guanare';
        municipioEncontrado = 'Guanare';
        estadoEncontrado = 'Portuguesa';
      } else if (lat >= 10.45 && lat <= 10.55 && lng >= -67.00 && lng <= -66.80) {
        ciudadEncontrada = 'Caracas';
        municipioEncontrado = 'Libertador';
        estadoEncontrado = 'Distrito Capital';
      } else if (lat >= 10.00 && lat <= 10.15 && lng >= -69.45 && lng <= -69.25) {
        ciudadEncontrada = 'Barquisimeto';
        municipioEncontrado = 'Iribarren';
        estadoEncontrado = 'Lara';
      } else if (lat >= 10.12 && lat <= 10.28 && lng >= -68.08 && lng <= -67.92) {
        ciudadEncontrada = 'Valencia';
        municipioEncontrado = 'Valencia';
        estadoEncontrado = 'Carabobo';
      } else if (lat >= 10.20 && lat <= 10.30 && lng >= -67.65 && lng <= -67.55) {
        ciudadEncontrada = 'Maracay';
        municipioEncontrado = 'Girardot';
        estadoEncontrado = 'Aragua';
      } else if (lat >= 10.58 && lat <= 10.75 && lng >= -71.72 && lng <= -71.55) {
        ciudadEncontrada = 'Maracaibo';
        municipioEncontrado = 'Maracaibo';
        estadoEncontrado = 'Zulia';
      }

      // Si no coincidió por límites exactos, buscar en el catálogo de ciudades por nombre devuelto
      if (!ciudadEncontrada && normCity) {
        const itemC = ciudadesLista.find(c => {
          const nc = normalizar(c.ciudad);
          return nc === normCity || normCity.includes(nc) || nc.includes(normCity);
        });
        if (itemC) {
          ciudadEncontrada = itemC.ciudad;
          municipioEncontrado = itemC.municipio;
          estadoEncontrado = itemC.estado;
          parroquiaEncontrada = itemC.parroquia;
        }
      }

      // Búsqueda por municipio devuelto
      if (!ciudadEncontrada && normMun) {
        const itemM = ciudadesLista.find(c => {
          const nm = normalizar(c.municipio);
          const nc = normalizar(c.ciudad);
          return nm === normMun || normMun.includes(nm) || nc === normMun;
        });
        if (itemM) {
          ciudadEncontrada = itemM.ciudad;
          municipioEncontrado = itemM.municipio;
          estadoEncontrado = itemM.estado;
        }
      }

      // Si aún no se encontró, detectar estado y asignar ciudad principal
      if (!estadoEncontrado) {
        estadoEncontrado = estados.find(e => {
          const ne = normalizar(e);
          return ne === normState || normState.includes(ne) || ne.includes(normState);
        });
        if (!estadoEncontrado) {
          estadoEncontrado = (lat >= 8.5 && lat <= 10.0 && lng >= -70.5 && lng <= -68.5) ? 'Portuguesa' : 'Portuguesa';
        }
      }

      if (!ciudadEncontrada) {
        const itemEst = ciudadesLista.find(c => c.estado === estadoEncontrado);
        ciudadEncontrada = itemEst ? itemEst.ciudad : (rawCity || 'Acarigua');
      }

      // 2. Detectar Municipio si no se tiene
      if (!municipioEncontrado && estadoEncontrado && geo[estadoEncontrado]) {
        const municipios = Object.keys(geo[estadoEncontrado]);
        municipioEncontrado = municipios.find(m => {
          const nm = normalizar(m);
          return nm === normMun || normMun.includes(nm) || nm.includes(normMun);
        });

        if (!municipioEncontrado && estadoEncontrado === 'Portuguesa') {
          municipioEncontrado = (lat >= 9.50 && lat <= 9.65 && lng >= -69.35 && lng <= -69.20) ? 'Araure' : 'Páez';
        }
      }

      // 3. Detectar Parroquia
      if (!parroquiaEncontrada && estadoEncontrado && municipioEncontrado && geo[estadoEncontrado] && geo[estadoEncontrado][municipioEncontrado]) {
        const parroquias = geo[estadoEncontrado][municipioEncontrado];
        parroquiaEncontrada = parroquias.find(p => {
          const np = normalizar(p);
          return np === normParr || normParr.includes(np) || np.includes(normParr);
        });

        if (!parroquiaEncontrada) {
          if (municipioEncontrado === 'Páez') parroquiaEncontrada = 'Acarigua';
          else if (parroquias.length > 0) parroquiaEncontrada = parroquias[0];
        }
      }

      // Asignar a selectores
      const selCiudad = document.getElementById('cit-ciudad');
      const selMun = document.getElementById('cit-municipio');
      const selParr = document.getElementById('cit-parroquia');

      if (selCiudad && ciudadEncontrada) {
        // Verificar si existe la opción en el select
        let optExiste = Array.from(selCiudad.options).some(o => o.value === ciudadEncontrada);
        if (!optExiste && ciudadEncontrada) {
          const optNueva = document.createElement('option');
          optNueva.value = ciudadEncontrada;
          optNueva.textContent = ciudadEncontrada;
          const optOtra = selCiudad.querySelector('option[value="Otra"]');
          if (optOtra) selCiudad.insertBefore(optNueva, optOtra);
          else selCiudad.appendChild(optNueva);
        }

        selCiudad.value = ciudadEncontrada;
        selCiudad.classList.remove('invalid');
        const errC = document.getElementById('cit-ciudad-err');
        if (errC) errC.classList.remove('visible');

        const inputOtra = document.getElementById('cit-ciudad-otra');
        if (inputOtra) {
          inputOtra.style.display = 'none';
          inputOtra.value = '';
        }

        // Actualizar listas de municipios y parroquias en cascada
        if (typeof window.actualizarPorCiudadCiudadano === 'function') {
          window.actualizarPorCiudadCiudadano(ciudadEncontrada, municipioEncontrado, parroquiaEncontrada);
        } else if (typeof window.actualizarMunicipiosCiudadano === 'function') {
          window.actualizarMunicipiosCiudadano(estadoEncontrado || 'Portuguesa', municipioEncontrado, parroquiaEncontrada);
        }

        if (selMun && municipioEncontrado) {
          selMun.value = municipioEncontrado;
          selMun.classList.remove('invalid');
          const errM = document.getElementById('cit-municipio-err');
          if (errM) errM.classList.remove('visible');
        }

        if (selParr && parroquiaEncontrada) {
          selParr.value = parroquiaEncontrada;
          selParr.classList.remove('invalid');
          const errP = document.getElementById('cit-parroquia-err');
          if (errP) errP.classList.remove('visible');
        }
      }

      const st = document.getElementById('cit-geo-status');
      if (st) {
        st.textContent = `✓ Ubicado: ${ciudadEncontrada}, Municipio ${selMun ? selMun.value : (municipioEncontrado || 'Páez')}, Parroquia ${selParr ? selParr.value : (parroquiaEncontrada || 'Acarigua')}`;
      }

      // Sugerir dirección en campo si está vacío o autollenado
      const inputDir = document.getElementById('cit-direccion');
      if (inputDir && (!inputDir.value.trim() || inputDir.dataset.autofill === '1')) {
        const partes = [];
        if (addr.road) partes.push(addr.road);
        if (addr.neighbourhood || addr.suburb || addr.quarter) partes.push(addr.neighbourhood || addr.suburb || addr.quarter);
        if (addr.residential) partes.push(addr.residential);

        const resDir = partes.length > 0 ? partes.join(', ') : (data.display_name ? data.display_name.split(',').slice(0, 3).join(',') : '');
        if (resDir) {
          inputDir.value = resDir.trim();
          inputDir.dataset.autofill = '1';
          inputDir.classList.remove('invalid');
          const errD = document.getElementById('cit-direccion-err');
          if (errD) errD.classList.remove('visible');
        }
      }
    }

    function buscarDireccionCiudadanoEnMapa() {
      const inputDir = document.getElementById('cit-direccion');
      const query = inputDir ? inputDir.value.trim() : '';
      const st = document.getElementById('cit-geo-status');

      if (!query) {
        alert('Escriba una dirección o referencia para buscar en el mapa.');
        if (inputDir) inputDir.focus();
        return;
      }

      if (st) st.textContent = 'Buscando ubicación en Venezuela...';

      const selCiudad = document.getElementById('cit-ciudad');
      const selMun = document.getElementById('cit-municipio');
      const ciudadVal = selCiudad && selCiudad.value ? (selCiudad.value === 'Otra' ? (document.getElementById('cit-ciudad-otra')?.value || 'Acarigua') : selCiudad.value) : 'Acarigua';
      const munVal = selMun && selMun.value ? (selMun.value === 'Otro' ? (document.getElementById('cit-municipio-otro')?.value || '') : selMun.value) : '';
      const searchQuery = `${query}, ${munVal ? munVal + ', ' : ''}${ciudadVal}, Venezuela`;

      fetch(`https://nominatim.openstreetmap.org/search?format=json&countrycodes=ve&q=${encodeURIComponent(searchQuery)}&limit=1&addressdetails=1`)
        .then(res => res.json())
        .then(data => {
          if (data && data.length > 0) {
            const item = data[0];
            const lat = parseFloat(item.lat);
            const lng = parseFloat(item.lon);

            if (mapaCiudadano && marcadorCiudadano) {
              mapaCiudadano.setView([lat, lng], 15);
              marcadorCiudadano.setLatLng([lat, lng]);
              setTimeout(() => mapaCiudadano.invalidateSize(), 150);
            }
            actualizarCoordenadasCiudadano(lat, lng, true);
            if (st) st.textContent = '✓ Ubicación localizada en el mapa';
          } else {
            // Reintentar con solo Venezuela
            fetch(`https://nominatim.openstreetmap.org/search?format=json&countrycodes=ve&q=${encodeURIComponent(query + ', Venezuela')}&limit=1&addressdetails=1`)
              .then(r => r.json())
              .then(data2 => {
                if (data2 && data2.length > 0) {
                  const item = data2[0];
                  const lat = parseFloat(item.lat);
                  const lng = parseFloat(item.lon);
                  if (mapaCiudadano && marcadorCiudadano) {
                    mapaCiudadano.setView([lat, lng], 15);
                    marcadorCiudadano.setLatLng([lat, lng]);
                    setTimeout(() => mapaCiudadano.invalidateSize(), 150);
                  }
                  actualizarCoordenadasCiudadano(lat, lng, true);
                  if (st) st.textContent = '✓ Ubicación localizada en el mapa';
                } else if (st) {
                  st.textContent = 'No se encontró la dirección en Venezuela. Haga clic directo en el mapa.';
                }
              });
          }
        })
        .catch(() => {
          if (st) st.textContent = 'Error al buscar dirección. Haga clic en el mapa.';
        });
    }

    function obtenerUbicacionCiudadanoGPS() {
      const st = document.getElementById('cit-geo-status');
      if (!navigator.geolocation) {
        if (st) st.textContent = 'Su navegador no soporta geolocalización.';
        return;
      }

      if (st) st.textContent = 'Obteniendo coordenadas GPS...';

      navigator.geolocation.getCurrentPosition(
        function(position) {
          const lat = position.coords.latitude;
          const lng = position.coords.longitude;

          if (mapaCiudadano && marcadorCiudadano) {
            mapaCiudadano.setView([lat, lng], 16);
            marcadorCiudadano.setLatLng([lat, lng]);
            setTimeout(() => mapaCiudadano.invalidateSize(), 150);
          }

          actualizarCoordenadasCiudadano(lat, lng, true);
        },
        function(err) {
          if (st) {
            if (err.code === err.PERMISSION_DENIED) {
              st.textContent = 'Permiso GPS denegado. Puede hacer clic en el mapa.';
            } else {
              st.textContent = 'No se pudo obtener señal GPS. Haga clic en el mapa.';
            }
          }
        },
        { enableHighAccuracy: true, timeout: 10000, maximumAge: 0 }
      );
    }

    /* ── Componente de Geolocalización Interactiva y Google Maps ── */
    let mapaSenalado = null;
    let marcadorSenalado = null;

    function inicializarMapaSenalado() {
      const contenedor = document.getElementById('mapa-senalado');
      if (!contenedor || typeof L === 'undefined') return;

      if (mapaSenalado) {
        setTimeout(() => mapaSenalado.invalidateSize(), 100);
        setTimeout(() => mapaSenalado.invalidateSize(), 400);
        return;
      }

      // Coordenadas iniciales: Municipio Páez (Acarigua), Portuguesa
      const defaultLat = 9.5558;
      const defaultLng = -69.1994;

      mapaSenalado = L.map('mapa-senalado', {
        maxBounds: [[0.3, -74.0], [16.2, -58.0]],
        maxBoundsViscosity: 0.8
      }).setView([defaultLat, defaultLng], 14);

      L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        maxZoom: 19,
        attribution: '© OpenStreetMap | Integración Google Maps'
      }).addTo(mapaSenalado);

      setTimeout(() => { if (mapaSenalado) mapaSenalado.invalidateSize(); }, 200);
      setTimeout(() => { if (mapaSenalado) mapaSenalado.invalidateSize(); }, 600);

      marcadorSenalado = L.marker([defaultLat, defaultLng], {
        draggable: true,
        title: 'Arrastre para ajustar la ubicación del señalado'
      }).addTo(mapaSenalado);

      function actualizarCoordenadas(lat, lng, consultarDireccion = false) {
        const inputLat = document.getElementById('sen-lat');
        const inputLng = document.getElementById('sen-lng');
        const textoLat = document.getElementById('sen-lat-texto');
        const textoLng = document.getElementById('sen-lng-texto');
        const contenedorMapa = document.getElementById('contenedor-mapa-senalado');
        const errMapa = document.getElementById('sen-mapa-err');
        const st = document.getElementById('sen-geo-status');

        if (inputLat) inputLat.value = lat.toFixed(7);
        if (inputLng) inputLng.value = lng.toFixed(7);
        if (textoLat) textoLat.textContent = lat.toFixed(5);
        if (textoLng) textoLng.textContent = lng.toFixed(5);

        const btnGmaps = document.getElementById('btn-abrir-google-maps');
        if (btnGmaps) {
          btnGmaps.href = `https://www.google.com/maps?q=${lat},${lng}`;
          btnGmaps.style.display = 'inline-block';
        }

        // Validación inmediata de límites geográficos de Venezuela
        const fnVal = typeof esCoordenadaEnVenezuela === 'function' ? esCoordenadaEnVenezuela : (typeof window.esCoordenadaEnVenezuela === 'function' ? window.esCoordenadaEnVenezuela : null);
        const enVzla = fnVal ? fnVal(lat, lng) : (lat >= 0.5 && lat <= 16.0 && lng >= -73.6 && lng <= -58.0);

        if (!enVzla) {
          if (inputLat) inputLat.dataset.fueraDeVenezuela = '1';
          if (contenedorMapa) contenedorMapa.style.border = '2px solid #ef4444';
          if (errMapa) {
            errMapa.textContent = 'La ubicación seleccionada está fuera de Venezuela. No se permite continuar hasta ubicar un punto dentro del país.';
            errMapa.classList.add('visible');
          }
          if (st) {
            st.innerHTML = '<span style="color:#dc2626; font-weight:bold;">Ubicación fuera de Venezuela. Seleccione un punto dentro del territorio nacional.</span>';
          }
          return;
        }

        // Si las coordenadas son válidas preliminarmente
        if (inputLat) inputLat.dataset.fueraDeVenezuela = '0';
        if (contenedorMapa) contenedorMapa.style.border = '1px solid #dce3ec';
        if (errMapa) errMapa.classList.remove('visible');

        if (consultarDireccion) {
          if (st) st.textContent = 'Obteniendo dirección aproximada...';
          fetch(`https://nominatim.openstreetmap.org/reverse?format=json&lat=${lat}&lon=${lng}&zoom=18&addressdetails=1`)
            .then(res => res.json())
            .then(data => {
              const addr = data.address || {};
              const cCode = (addr.country_code || '').toLowerCase();
              if (cCode && cCode !== 've') {
                if (inputLat) inputLat.dataset.fueraDeVenezuela = '1';
                if (contenedorMapa) contenedorMapa.style.border = '2px solid #ef4444';
                if (errMapa) {
                  errMapa.textContent = `La ubicación seleccionada pertenece a ${addr.country || 'otro país'}. Debe seleccionar un punto dentro de Venezuela.`;
                  errMapa.classList.add('visible');
                }
                if (st) {
                  st.innerHTML = `<span style="color:#dc2626; font-weight:bold;">⚠️ Ubicación fuera de Venezuela (${addr.country || 'otro país'}). Seleccione un punto nacional.</span>`;
                }
                return;
              }
              if (inputLat) inputLat.dataset.fueraDeVenezuela = '0';
              if (contenedorMapa) contenedorMapa.style.border = '1px solid #dce3ec';
              if (errMapa) errMapa.classList.remove('visible');

              if (data && data.display_name) {
                const inputUbic = document.getElementById('sen-ubicacion');
                if (inputUbic && (!inputUbic.value.trim() || inputUbic.dataset.autofill === '1')) {
                  inputUbic.value = data.display_name;
                  inputUbic.dataset.autofill = '1';
                }
              }
              if (st) st.textContent = 'Coordenadas fijadas correctamente';
            })
            .catch(() => {
              if (st) st.textContent = 'Coordenadas fijadas';
            });
        }
      }

      actualizarCoordenadas(defaultLat, defaultLng, false);

      marcadorSenalado.on('dragend', function(e) {
        const pos = e.target.getLatLng();
        actualizarCoordenadas(pos.lat, pos.lng, true);
      });

      mapaSenalado.on('click', function(e) {
        marcadorSenalado.setLatLng(e.latlng);
        actualizarCoordenadas(e.latlng.lat, e.latlng.lng, true);
      });
    }

    function buscarDireccionEnMapa() {
      const query = document.getElementById('sen-ubicacion').value.trim();
      if (!query) {
        alert('Escriba una dirección o referencia para buscar en el mapa.');
        return;
      }
      const st = document.getElementById('sen-geo-status');
      if (st) st.textContent = 'Buscando en mapa de Venezuela...';
      fetch(`https://nominatim.openstreetmap.org/search?format=json&countrycodes=ve&q=${encodeURIComponent(query + ', Portuguesa, Venezuela')}`)
        .then(r => r.json())
        .then(data => {
          if (!data || !data.length) {
            return fetch(`https://nominatim.openstreetmap.org/search?format=json&countrycodes=ve&q=${encodeURIComponent(query + ', Venezuela')}`).then(r => r.json());
          }
          return data;
        })
        .then(data => {
          if (data && data.length) {
            const item = data[0];
            const lat = parseFloat(item.lat);
            const lng = parseFloat(item.lon);
            if (mapaSenalado && marcadorSenalado) {
              mapaSenalado.setView([lat, lng], 16);
              marcadorSenalado.setLatLng([lat, lng]);
            }
            actualizarCoordenadas(lat, lng, true);
            if (st) st.textContent = 'Ubicación localizada en el mapa';
          } else {
            if (st) st.textContent = 'No se encontró la dirección en Venezuela. Puede arrastrar el marcador manualmente.';
          }
        })
        .catch(() => {
          if (st) st.textContent = 'Error al consultar geocodificación.';
        });
    }

    function obtenerUbicacionActualGPS() {
      if (!navigator.geolocation) {
        alert('La geolocalización no está disponible en su navegador.');
        return;
      }
      const st = document.getElementById('sen-geo-status');
      if (st) st.textContent = 'Obteniendo GPS...';
      navigator.geolocation.getCurrentPosition(
        pos => {
          const lat = pos.coords.latitude;
          const lng = pos.coords.longitude;
          if (mapaSenalado && marcadorSenalado) {
            mapaSenalado.setView([lat, lng], 16);
            marcadorSenalado.setLatLng([lat, lng]);
          }
          actualizarCoordenadas(lat, lng, true);
        },
        () => {
          if (st) st.textContent = 'No se pudo acceder al GPS.';
        },
        { enableHighAccuracy: true, timeout: 8000 }
      );
    }

    /* ── Cambio del tipo de trámite ── */
    function onTipoTramiteChange(radio) {
      const tipo = radio.value;
      const bloqueConsulta = document.getElementById('bloque-consulta-popular');
      const bloqueProyecto = document.getElementById('bloque-proyecto-consulta');
      const contSenaladoWrapper = document.getElementById('contenido-senalado-wrapper');
      const contSenaladoNoAplica = document.getElementById('senalado-no-aplica');
      const contEvidenciaWrapper = document.getElementById('contenido-evidencia-wrapper');
      const contEvidenciaNoAplica = document.getElementById('evidencia-no-aplica');
      const textoNotaEvidencia = document.getElementById('texto-nota-evidencia');

      // Limpia el aviso y el borde rojo de la selección
      document.getElementById('tipo-tramite-err').classList.remove('visible');
      document.querySelectorAll('.tramite-card').forEach(c => c.style.borderColor = '');

      bloqueConsulta.style.display = 'none';
      bloqueProyecto.style.display = 'none';
      contSenaladoWrapper.style.display = 'block';
      if (contSenaladoNoAplica) contSenaladoNoAplica.style.display = 'none';
      contEvidenciaWrapper.style.display = 'block';
      if (contEvidenciaNoAplica) contEvidenciaNoAplica.style.display = 'none';

      if (tipo === 'denuncia') {
        bloqueConsulta.style.display = 'block';
        const consulta = document.querySelector('input[name="es_consulta"]:checked');
        if (consulta && consulta.value === 'si') bloqueProyecto.style.display = 'block';
        textoNotaEvidencia.innerHTML = 'Cargue los documentos uno por uno, en el orden que se muestra a continuación. <strong>Para las denuncias, adjuntar evidencias probatorias es de carácter obligatorio.</strong>';
      }
      else if (tipo === 'queja') {
        textoNotaEvidencia.innerHTML = 'Cargue los documentos uno por uno, en el orden que se muestra a continuación. <strong>Para las quejas, la copia de la C.I. del denunciante y la carta de exposición de motivo son obligatorias;</strong> los demás documentos puede omitirlos según su criterio.';
      }
      else if (tipo === 'reclamo') {
        contSenaladoWrapper.style.display = 'none';
        if (contSenaladoNoAplica) contSenaladoNoAplica.style.display = 'block';
        textoNotaEvidencia.innerHTML = 'Cargue los anexos, identificaciones o documentos que acompañen su reclamo.';
      }
      else if (tipo === 'peticion') {
        contSenaladoWrapper.style.display = 'none';
        if (contSenaladoNoAplica) contSenaladoNoAplica.style.display = 'block';
        textoNotaEvidencia.innerHTML = 'Cargue los anexos, identificaciones o documentos que acompañen su petición.';
      }
      else if (tipo === 'sugerencia') {
        contSenaladoWrapper.style.display = 'none';
        if (contSenaladoNoAplica) contSenaladoNoAplica.style.display = 'block';
        contEvidenciaWrapper.style.display = 'none';
        if (contEvidenciaNoAplica) contEvidenciaNoAplica.style.display = 'block';
      }

      const tipoNombres = {
        'denuncia': 'Denuncia',
        'queja': 'Queja',
        'reclamo': 'Reclamo',
        'peticion': 'Petición',
        'sugerencia': 'Sugerencia'
      };
      const bannerTramite = document.getElementById('banner-tramite-seleccionado');
      const textoTramite = document.getElementById('texto-tramite-seleccionado');
      if (bannerTramite && textoTramite && tipoNombres[tipo]) {
        textoTramite.textContent = tipoNombres[tipo];
        if (typeof pasoActual !== 'undefined' && pasoActual > 1) {
          bannerTramite.style.display = 'block';
        }
      }

      if (typeof renderSecuenciaEvidencias === 'function') {
        renderSecuenciaEvidencias();
      }
    }

    function actualizarAvisoContexto() {
      const radioSeleccionado = document.querySelector('input[name="tipo_tramite"]:checked');
      const banner = document.getElementById('banner-contexto-aviso');
      const textoBadge = document.getElementById('texto-contexto-badge');

      if (radioSeleccionado && radioSeleccionado.value === 'denuncia') {
        const consultaRadio = document.querySelector('input[name="es_consulta"]:checked');
        if (consultaRadio) {
          banner.style.display = 'block';
          textoBadge.textContent = consultaRadio.value === 'si'
            ? 'Relacionado con Proyecto de Consulta Popular Nacional'
            : 'Relacionado con Organismos e Institutos';
        }
      } else {
        banner.style.display = 'none';
      }
    }

    /* ── Resumen de la revisión (los señalados y el proyecto los llena poblarResumen en wizard.js) ── */
    function actualizarResumenFinal() {
      const radioSeleccionado = document.querySelector('input[name="tipo_tramite"]:checked');
      const bloqueResumenContexto = document.getElementById('resumen-bloque-contexto');
      const textoResumenContexto = document.getElementById('res-contexto');

      if (radioSeleccionado && radioSeleccionado.value === 'denuncia') {
        const consultaRadio = document.querySelector('input[name="es_consulta"]:checked');
        if (consultaRadio) {
          bloqueResumenContexto.style.display = 'block';
          textoResumenContexto.textContent = consultaRadio.value === 'si' ? 'Proyecto de Consulta Popular Nacional' : 'Organismo / Otra Institución';
        }
      } else {
        bloqueResumenContexto.style.display = 'none';
      }

      const tipoTramite = radioSeleccionado ? radioSeleccionado.value.toUpperCase() : 'NO DEFINIDO';
      document.getElementById('res-tipo-tramite').textContent = tipoTramite;
      document.getElementById('res-fecha').textContent = new Date().toLocaleDateString();
      document.getElementById('res-nombres').textContent = obtenerNombreCompletoCiudadano();
      document.getElementById('res-cedula').textContent = `${document.getElementById('cit-tipo-doc').value} ${document.getElementById('cit-nro-doc').value}`.trim();
      document.getElementById('res-correo').textContent = document.getElementById('cit-correo').value;
      const codTel = document.getElementById('cit-telf-cel-cod').value;
      const numTel = document.getElementById('cit-telf-cel-num').value;
      document.getElementById('res-hechos').textContent = document.getElementById('narracion').value || 'Sin descripción';

      const parr = (document.getElementById('cit-parroquia')?.value === 'Otra' ? document.getElementById('cit-parroquia-otra')?.value : document.getElementById('cit-parroquia')?.value) || 'No especificó';
      const mun = (document.getElementById('cit-municipio')?.value === 'Otro' ? document.getElementById('cit-municipio-otro')?.value : document.getElementById('cit-municipio')?.value) || 'Páez';
      const ciu = (document.getElementById('cit-ciudad')?.value === 'Otra' ? document.getElementById('cit-ciudad-otra')?.value : document.getElementById('cit-ciudad')?.value) || 'Acarigua';
      const dir = escaparHtml(document.getElementById('cit-direccion').value || '');
      const contenedorUbicacion = document.getElementById('res-ubicacion');
      if (contenedorUbicacion) {
        contenedorUbicacion.innerHTML = `<strong>Dirección:</strong> ${dir} <br><strong>Ciudad:</strong> ${ciu} <br><strong>Municipio:</strong> ${mun} <br><strong>Parroquia:</strong> ${parr}`;
      }

      // Evidencias cargadas (desde la secuencia por documentos)
      const evCargadas = obtenerEvidenciasCargadas();
      const contenedorResEvidencias = document.getElementById('res-evidencias');
      if (contenedorResEvidencias) {
        if (evCargadas.total > 0) {
          contenedorResEvidencias.innerHTML = `<div style="color:#1565c0;font-weight:bold;margin-bottom:8px;">Se anexaron ${evCargadas.total} archivo(s):</div>` +
            evCargadas.items.map(it => `<div style="margin-bottom:4px;"> <strong>${escaparHtml(it.doc)}:</strong> <span style="color:#555;">${escaparHtml(it.nombre)}</span></div>`).join('');
        } else {
          contenedorResEvidencias.innerHTML = '<div style="color:#c62828; font-weight:600;"> No se anexaron evidencias ni documentos.</div>';
        }
      }
    }

    let enviandoSolicitud = false;

    // Función que recoge ABSOLUTAMENTE TODOS los datos ingresados
    // Envía la solicitud al servidor; si se guarda, muestra la confirmación
    // con el número de expediente real y el enlace a la planilla en PDF.
    window.enviarSolicitud = async function() {
      if (enviandoSolicitud) return;

      const acepta = document.getElementById('acepta-declaracion');
      if (acepta && !acepta.checked) {
        document.getElementById('acepta-decl-err').classList.add('visible');
        acepta.scrollIntoView({ behavior: 'smooth', block: 'center' });
        return;
      }
      document.getElementById('acepta-decl-err')?.classList.remove('visible');

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

      const citDocVal = val('cit-nro-doc').replace(/\D/g, '');
      const citTipoVal = val('cit-tipo-doc');
      for (const s of senalados) {
        const c = s.campos || {};
        const pnDoc = (c['pn-nro-doc'] || '').replace(/\D/g, '');
        const pnTipo = (c['pn-tipo-doc'] || '').trim();
        const jpDoc = (c['jp-nro-doc'] || '').replace(/\D/g, '');
        const jpTipo = (c['jp-tipo-doc'] || '').trim();
        const otDoc = (c['ot-documento'] || '').trim();
        const otNum = otDoc.replace(/\D/g, '');

        if (citDocVal && (
          (pnDoc && pnDoc === citDocVal && (!pnTipo || pnTipo === citTipoVal)) ||
          (jpDoc && jpDoc === citDocVal && (!jpTipo || jpTipo === citTipoVal)) ||
          (otNum && otNum === citDocVal && !/^[JjGgCc]/.test(otDoc))
        )) {
          alert('Error en el formulario:\n\n• La persona o funcionario señalado no puede tener la misma cédula que el denunciante.');
          return;
        }
      }

      // Validar que entre señalado y señalado no puede existir el mismo RIF, código SITUR, número de documento
      const normRif = r => (r || '').toUpperCase().replace(/[^A-Z0-9]/g, '');
      const normSitur = s => (s || '').trim().toUpperCase();
      const normDoc = d => (d || '').replace(/\D/g, '');

      for (let i = 0; i < senalados.length; i++) {
        const cA = senalados[i].campos || {};

        let rifA = cA['pj-rif'] || cA['oe-rif'] || cA['cc-rif'] || '';
        if (!rifA && cA['ot-documento'] && /^[JGVECjgvec]-?\d{8,9}-?\d?$/.test(cA['ot-documento'].trim())) {
          rifA = cA['ot-documento'].trim();
        }
        const nRifA = normRif(rifA);

        const siturA = (cA['cm-situr'] || cA['cc-situr'] || '').trim();
        const nSiturA = normSitur(siturA);

        let docA = cA['pn-nro-doc'] || cA['jp-nro-doc'] || '';
        let tipoA = (cA['pn-tipo-doc'] || cA['jp-tipo-doc'] || '').trim();
        if (!docA && cA['ot-documento'] && !/^[JjGgCc]/.test(cA['ot-documento'].trim())) {
          docA = cA['ot-documento'].trim();
          tipoA = '';
        }
        const nDocA = normDoc(docA);

        for (let j = i + 1; j < senalados.length; j++) {
          const cB = senalados[j].campos || {};

          let rifB = cB['pj-rif'] || cB['oe-rif'] || cB['cc-rif'] || '';
          if (!rifB && cB['ot-documento'] && /^[JGVECjgvec]-?\d{8,9}-?\d?$/.test(cB['ot-documento'].trim())) {
            rifB = cB['ot-documento'].trim();
          }
          const nRifB = normRif(rifB);

          if (nRifA && nRifB && nRifA === nRifB) {
            alert(`Error en el formulario:\n\n• No puede existir el mismo R.I.F. (${rifB}) entre señalados.`);
            return;
          }

          const siturB = (cB['cm-situr'] || cB['cc-situr'] || '').trim();
          const nSiturB = normSitur(siturB);

          if (nSiturA && nSiturB && nSiturA === nSiturB) {
            alert(`Error en el formulario:\n\n• No puede existir el mismo código SITUR (${siturB}) entre señalados.`);
            return;
          }

          let docB = cB['pn-nro-doc'] || cB['jp-nro-doc'] || '';
          let tipoB = (cB['pn-tipo-doc'] || cB['jp-tipo-doc'] || '').trim();
          if (!docB && cB['ot-documento'] && !/^[JjGgCc]/.test(cB['ot-documento'].trim())) {
            docB = cB['ot-documento'].trim();
            tipoB = '';
          }
          const nDocB = normDoc(docB);

          if (nDocA && nDocB && nDocA.length >= 5 && nDocA === nDocB && (!tipoA || !tipoB || tipoA === tipoB)) {
            alert(`Error en el formulario:\n\n• No puede existir el mismo número de documento (${docB}) entre señalados.`);
            return;
          }
        }
      }

      const resolverCampoOtra = (idSelect, idOtra) => {
        const selVal = val(idSelect);
        if (selVal === 'Otra' || selVal === 'Otro') {
          return val(idOtra) || selVal;
        }
        return selVal;
      };

      const datos = {
        tipo_tramite: radio('tipo_tramite'),
        es_consulta: radio('tipo_tramite') === 'denuncia' && radio('es_consulta') === 'si',
        ciudadano: {
          tipo_doc: val('cit-tipo-doc'), nro_doc: val('cit-nro-doc'),
          primer_nombre: val('cit-primer-nombre'), segundo_nombre: val('cit-segundo-nombre'),
          primer_apellido: val('cit-primer-apellido'), segundo_apellido: val('cit-segundo-apellido'),
          sexo: val('cit-sexo'), fecha_nac: val('cit-fecha-nac'), lugar_nac: val('cit-lugar-nac'),
          estado_civil: resolverCampoOtra('cit-ecivil', 'cit-ecivil-otro'),
          nivel_educativo: val('cit-edu'),
          profesion: resolverCampoOtra('cit-profesion', 'cit-profesion-otra'),
          ocupacion: val('cit-ocupacion'),
          correo: val('cit-correo'),
          telf_cel: val('cit-telf-cel-cod') && val('cit-telf-cel-num') ? val('cit-telf-cel-cod') + '-' + val('cit-telf-cel-num') : '',
          telf_hab: val('cit-telf-hab-cod') && val('cit-telf-hab-num') ? val('cit-telf-hab-cod') + '-' + val('cit-telf-hab-num') : '',
          telf_trab: val('cit-telf-trab-cod') && val('cit-telf-trab-num') ? val('cit-telf-trab-cod') + '-' + val('cit-telf-trab-num') : '',
          direccion: val('cit-direccion'),
          parroquia: resolverCampoOtra('cit-parroquia', 'cit-parroquia-otra'),
          municipio: resolverCampoOtra('cit-municipio', 'cit-municipio-otro') || 'Páez',
          ciudad: resolverCampoOtra('cit-ciudad', 'cit-ciudad-otra') || 'Acarigua',
        },
        senalados,
        ubicacion_senalado: val('sen-ubicacion'),
        ubicacion_lat: val('sen-lat'),
        ubicacion_lng: val('sen-lng'),
        proyecto: {
          nombre: val('proy-nombre'), fecha: val('proy-fecha'), monto: val('proy-monto').replace(/[^\d,.]/g, '').replace(/\./g, '').replace(',', '.'),
          financiador: val('proy-financiador'), situr: val('proy-situr'),
        },
        narracion: val('narracion'),
        otra_instancia: radio('otra_instancia'),
        cual_instancia: radio('otra_instancia') === 'si' ? val('cual-instancia') : null,
        acepta_declaracion: true,
      };

      const form = new FormData();
      form.append('datos', JSON.stringify(datos));
      Object.entries(window.archivosPorDocumento || {}).forEach(([tipo, archivos]) => {
        archivos.forEach(archivo => form.append(`evidencias[${tipo}][]`, archivo));
      });

      const boton = document.getElementById('btn-enviar-solicitud');
      const textoOriginal = boton ? boton.innerHTML : 'Enviar Solicitud';

      enviandoSolicitud = true;
      if (boton) {
        boton.disabled = true;
        boton.innerHTML = `
          <span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true" style="vertical-align: -0.15em;"></span>
          Enviando solicitud, espere...
        `;
      }

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
          if (boton) {
            boton.disabled = false;
            boton.innerHTML = textoOriginal;
          }
          enviandoSolicitud = false;
          return;
        }
        document.getElementById('nro-expediente-display').textContent = json.case_number;
        document.getElementById('btn-descargar-planilla').href = json.planilla_url;
        if (document.getElementById('lapso-dias-display')) {
          document.getElementById('lapso-dias-display').textContent = json.dias_confirmacion || 5;
        }
        if (document.getElementById('lapso-fecha-display')) {
          document.getElementById('lapso-fecha-display').textContent = json.fecha_limite_confirmacion || '5 días hábiles';
        }
        if (document.getElementById('lapso-tracking-display')) {
          document.getElementById('lapso-tracking-display').textContent = json.tracking_code || json.case_number;
        }
        if (typeof guardarSenaladosEnPadron === 'function') guardarSenaladosEnPadron();
        localStorage.removeItem(BORRADOR_KEY);
        mostrarConfirmacion();
      } catch (e) {
        alert('No se pudo conectar con el servidor. Intente de nuevo.');
        if (boton) {
          boton.disabled = false;
          boton.innerHTML = textoOriginal;
        }
        enviandoSolicitud = false;
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

      document.getElementById('print-nombres').textContent = obtenerNombreCompletoCiudadano();

      const tDoc = document.getElementById('cit-tipo-doc').options[document.getElementById('cit-tipo-doc').selectedIndex]?.text || '';
      const nDoc = document.getElementById('cit-nro-doc').value || '';
      document.getElementById('print-cedula').textContent = `${tDoc} - ${nDoc}`;

      const sexoSelect = document.getElementById('cit-sexo');
      document.getElementById('print-sexo').textContent = sexoSelect.options[sexoSelect.selectedIndex]?.text || 'No indicó';
      document.getElementById('print-fecha-nac').textContent = document.getElementById('cit-fecha-nac').value || 'No indicó';
      const resolverCampoOtra = (idSelect, idOtra, def = '') => {
        const selEl = document.getElementById(idSelect);
        const v = selEl ? selEl.value.trim() : '';
        if (v === 'Otra' || v === 'Otro') {
          const otraEl = document.getElementById(idOtra);
          return (otraEl && otraEl.value.trim()) ? otraEl.value.trim() : v;
        }
        return v || def;
      };

      document.getElementById('print-edad').textContent = (document.getElementById('cit-edad').value || '--').replace(' años', '');
      document.getElementById('print-ecivil').textContent = resolverCampoOtra('cit-ecivil', 'cit-ecivil-otro', 'No indicó');
      document.getElementById('print-edu').textContent = val('cit-edu') || 'No especificó';
      document.getElementById('print-profesion').textContent = resolverCampoOtra('cit-profesion', 'cit-profesion-otra', 'No indicó');
      document.getElementById('print-ocupacion').textContent = document.getElementById('cit-ocupacion').value || 'No indicó';
      document.getElementById('print-correo').textContent = document.getElementById('cit-correo').value || '';

      const codTelf = document.getElementById('cit-telf-cel-cod').value || '';
      const numTelf = document.getElementById('cit-telf-cel-num').value || '';
      document.getElementById('print-telf-cel').textContent = codTelf && numTelf ? `${codTelf}-${numTelf}` : 'No posee';
      const codHab = document.getElementById('cit-telf-hab-cod').value || '';
      const numHab = document.getElementById('cit-telf-hab-num').value || '';
      document.getElementById('print-telf-hab').textContent = codHab && numHab ? `${codHab}-${numHab}` : 'No posee';

      const parr = resolverCampoOtra('cit-parroquia', 'cit-parroquia-otra', 'Acarigua');
      const dir = document.getElementById('cit-direccion').value || '';
      const mun = resolverCampoOtra('cit-municipio', 'cit-municipio-otro', 'Páez');
      const ciu = resolverCampoOtra('cit-ciudad', 'cit-ciudad-otra', 'Acarigua');
      document.getElementById('print-direccion').textContent = `${dir}, Parroquia ${parr}. Municipio ${mun} (Ciudad: ${ciu})`;

      // Señalados / Proyecto
      const seccionSenalado = document.getElementById('print-seccion-senalado');
      if (pasoAplica(3)) {
        seccionSenalado.style.display = 'block';
        const lineas = obtenerLineasSenalados();
        const elSen = document.getElementById('print-senalados-lista');
        elSen.style.whiteSpace = 'pre-line';
        elSen.textContent = lineas.length ? lineas.join('\n') : 'No indicó';
        document.getElementById('print-sen-ubicacion').textContent = document.getElementById('sen-ubicacion').value || 'No indicó';

        const consultaRadio = document.querySelector('input[name="es_consulta"]:checked');
        const seccionProyecto = document.getElementById('print-seccion-proyecto');
        if (tipoTramite === 'denuncia' && consultaRadio && consultaRadio.value === 'si') {
          seccionProyecto.style.display = 'block';
          document.getElementById('print-proy-nombre').textContent = document.getElementById('proy-nombre').value || 'No indicó';
          document.getElementById('print-proy-fecha').textContent = document.getElementById('proy-fecha').value || 'No indicó';
          const proyMontoVal = document.getElementById('proy-monto').value.trim();
          document.getElementById('print-proy-monto').textContent = proyMontoVal ? (proyMontoVal.includes(',') ? proyMontoVal : proyMontoVal + ',00') : '0,00';
          document.getElementById('print-proy-financiador').textContent = document.getElementById('proy-financiador').value || 'No indicó';
          document.getElementById('print-proy-situr').textContent = document.getElementById('proy-situr').value || 'No indicó';
        } else {
          seccionProyecto.style.display = 'none';
        }
      } else {
        seccionSenalado.style.display = 'none';
      }

      // Hechos
      document.getElementById('print-hechos').textContent = document.getElementById('narracion').value || '';
      const instaRadio = document.querySelector('input[name="otra_instancia"]:checked');
      if (instaRadio) {
        if (instaRadio.value === 'si') {
          document.getElementById('print-otra-instancia').textContent = `Denunciada previamente en: ${document.getElementById('cual-instancia').value || 'Organismo no especificado'}`;
        } else if (instaRadio.value === 'no_se') {
          document.getElementById('print-otra-instancia').textContent = 'No sabe si ha sido denunciada ante otro organismo';
        } else {
          document.getElementById('print-otra-instancia').textContent = 'Trámite originario (No se ha presentado ante otro organismo)';
        }
      } else {
        document.getElementById('print-otra-instancia').textContent = 'No indicó';
      }
      document.getElementById('print-fecha').textContent = new Date().toLocaleDateString();

      // Evidencias
      const evCargadas = obtenerEvidenciasCargadas();
      const contenedorEvidenciasPrint = document.getElementById('print-evidencias');
      if (evCargadas.total > 0) {
        contenedorEvidenciasPrint.innerHTML = `<p style="margin-bottom:8px;">Se cargaron ${evCargadas.total} evidencia(s) exitosamente:</p>` +
          evCargadas.items.map(it => `• <strong>${escaparHtml(it.doc)}:</strong> ${escaparHtml(it.nombre)}<br>`).join('');
      } else {
        contenedorEvidenciasPrint.textContent = 'El ciudadano no cargó documentos probatorios o anexos para este trámite.';
      }

      document.getElementById('paso-6').style.display = 'none';
      document.getElementById('vista-confirmacion').style.display = 'block';
      document.getElementById('barra-progreso').style.display = 'none';
      const volver = document.querySelector('#vista-wizard > .form-back-btn');
      if (volver) volver.style.display = 'none';

      window.scrollTo({ top: 0, behavior: 'smooth' });
    };
  </script>
</body>

</html>