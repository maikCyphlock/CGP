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
                          <span class="error-msg" id="cit-profesion-err">Solo se permiten letras.</span>
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
                        <option value="0422">0422</option>
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
                    <span class="error-msg" id="cit-telf-hab-err">Complete el código de área y los 7 dígitos.</span>
                  </div>
                </div>
                <div class="col-md-4">
                  <div class="form-group">
                    <label class="form-label">Teléfono del Trabajo</label>
                    <div style="display:flex;gap:6px;">
                      <select id="cit-telf-trab-cod" class="form-select" style="max-width:130px;flex-shrink:0;">
                        <option value="">Cód.</option>
                        <option value="0255">0255</option>
                        <option value="3301">3301</option>
                      </select>
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
                    <label class="form-label">Municipio <span class="required" style="color:#1565c0;">*</span></label>
                    <input type="text" id="cit-municipio" class="form-control" value="Páez" readonly tabindex="-1"
                      style="background:#f2f4f8;color:#5a6474;cursor:not-allowed;">
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
                          Nombre del Proyecto <span class="required" style="color:#1565c0;">*</span>
                        </label>
                        <input type="text" id="proy-nombre" class="form-control" placeholder="Nombre oficial del proyecto">
                        <span class="error-msg" id="proy-nombre-err">Ingrese el nombre del proyecto.</span>
                      </div>
                    </div>
                    <div class="col-md-3">
                      <div class="form-group">
                        <label class="form-label">
                          Fecha de Aprobación <span class="required" style="color:#1565c0;">*</span>
                        </label>
                        <input type="date" id="proy-fecha" class="form-control" max="{{ date('Y-m-d') }}" onchange="validarFechaProyecto(this)" oninput="validarFechaProyecto(this)">
                        <span class="error-msg" id="proy-fecha-err">Seleccione la fecha de aprobación.</span>
                      </div>
                    </div>
                    <div class="col-md-3">
                      <div class="form-group">
                        <label class="form-label">
                          Monto Estimado del Proyecto <span class="required" style="color:#1565c0;">*</span>
                        </label>
                        <input type="text" id="proy-monto" class="form-control" placeholder="Ej. 500.000,00" inputmode="decimal" oninput="formatearMonto(this)">
                        <span class="error-msg" id="proy-monto-err">Ingrese un monto estimado válido.</span>
                      </div>
                    </div>
                    <div class="col-md-6">
                      <div class="form-group">
                        <label class="form-label">Ente Financiador <span class="required" style="color:#1565c0;">*</span></label>
                        <input type="text" id="proy-financiador" class="form-control" placeholder="Nombre del ente financiador">
                        <span class="error-msg" id="proy-financiador-err">Ingrese el ente financiador.</span>
                      </div>
                    </div>
                    <div class="col-md-3">
                      <div class="form-group">
                        <label class="form-label">Código SITUR</label>
                        <input type="text" id="proy-situr" class="form-control mayusculas" placeholder="SITUR"
                          oninput="this.value=this.value.toUpperCase()">
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
                      <div class="resumen-etiqueta">Ubicación Completa (Estado, Municipio, Parroquia)</div>
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
            ¿Verificó correctamente los datos ingresados en este paso antes de continuar al siguiente nivel?
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
  <script src="{{ asset('assets/js/wizard.js') }}?v={{ filemtime(public_path('assets/js/wizard.js')) }}"></script>
  
  <script>
    let pasoPendienteValidacion = 1;
    let instanciaModal = null;

    const NOMBRES_PASOS = ["Tipo de Trámite", "Datos del Ciudadano", "Datos del Señalado", "Descripción de los Hechos", "Documentos y Evidencias"];

    /* ── Monto en bolívares: 1.250.000,50 mientras se escribe ── */
    function formatearMonto(el) {
      let [ent, dec] = el.value.replace(/[^\d,]/g, '').split(',');
      ent = (ent || '').replace(/^0+(?=\d)/, '').replace(/\B(?=(\d{3})+(?!\d))/g, '.');
      el.value = dec === undefined ? ent : ent + ',' + dec.slice(0, 2);
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
        'cit-profesion',
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

      // Configuración de fecha máxima para la fecha del proyecto (no futura)
      const proyFecha = document.getElementById('proy-fecha');
      if (proyFecha) {
        const dHoy = new Date();
        const hoy = dHoy.getFullYear() + '-' + String(dHoy.getMonth() + 1).padStart(2, '0') + '-' + String(dHoy.getDate()).padStart(2, '0');
        proyFecha.max = hoy;
      }
    });

    /* ── Validación en tiempo real para la fecha de aprobación del proyecto (no futura) ── */
    function validarFechaProyecto(input) {
      if (!input) return;
      const err = document.getElementById('proy-fecha-err');
      const val = input.value;
      if (!val) {
        if (err) {
          err.textContent = 'Seleccione la fecha de aprobación.';
        }
        return;
      }
      const dHoy = new Date();
      const hoy = dHoy.getFullYear() + '-' + String(dHoy.getMonth() + 1).padStart(2, '0') + '-' + String(dHoy.getDate()).padStart(2, '0');
      if (val > hoy) {
        if (err) {
          err.textContent = 'La fecha de aprobación no puede ser una fecha futura.';
          err.classList.add('visible');
          err.style.display = 'block';
        }
        input.classList.add('invalid');
      } else {
        if (err) {
          err.textContent = 'Seleccione la fecha de aprobación.';
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
          `¿Confirma que ha revisado correctamente los datos correspondientes al <strong>Paso 5</strong> y desea avanzar al siguiente nivel?`;
      } else if (!pasoAplica(3) && (paso === 3 || paso === 4)) {
        document.getElementById('modal-texto-pregunta').innerHTML =
          `¿Confirma que ha revisado correctamente los datos correspondientes al <strong>Paso ${paso}</strong> y desea avanzar al siguiente nivel?`;
      } else {
        document.getElementById('modal-texto-pregunta').innerHTML =
          `¿Confirma que ha revisado correctamente los datos correspondientes al <strong>Paso ${paso} (${NOMBRES_PASOS[paso - 1]})</strong> y desea avanzar al siguiente nivel?`;
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

      // Verificar si está en revisión (por código IN_REVIEW o por texto)
      const estatusNorm = (r.estatus || '').toLowerCase().normalize('NFD').replace(/[\u0300-\u036f]/g, '');
      const esEnRevision = (r.estatus_codigo === 'IN_REVIEW') || estatusNorm.includes('revision');

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

      containerRes.style.display = 'block';
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
      const camposRequeridosCiudadano = document.querySelectorAll('#paso-2 .required');

      // Limpia el aviso y el borde rojo de la selección
      document.getElementById('tipo-tramite-err').classList.remove('visible');
      document.querySelectorAll('.tramite-card').forEach(c => c.style.borderColor = '');

      bloqueConsulta.style.display = 'none';
      bloqueProyecto.style.display = 'none';
      contSenaladoWrapper.style.display = 'block';
      if (contSenaladoNoAplica) contSenaladoNoAplica.style.display = 'none';
      contEvidenciaWrapper.style.display = 'block';
      if (contEvidenciaNoAplica) contEvidenciaNoAplica.style.display = 'none';
      camposRequeridosCiudadano.forEach(el => el.style.display = 'inline');

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
        camposRequeridosCiudadano.forEach(el => el.style.display = 'none');
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
      document.getElementById('res-telf').textContent = (codTel && numTel) ? `${codTel}-${numTel}` : numTel;
      document.getElementById('res-hechos').textContent = document.getElementById('narracion').value || 'Sin descripción';

      const parr = document.getElementById('cit-parroquia').value || 'No especificó';
      const mun = document.getElementById('cit-municipio').value || 'Páez';
      const ciu = document.getElementById('cit-ciudad').value || 'Acarigua/Portuguesa';
      const dir = escaparHtml(document.getElementById('cit-direccion').value || '');
      const contenedorUbicacion = document.getElementById('res-ubicacion');
      if (contenedorUbicacion) {
        contenedorUbicacion.innerHTML = `<strong>Ciudad/Estado:</strong> ${ciu} <br><strong>Municipio:</strong> ${mun} <br><strong>Parroquia:</strong> ${parr} <br><strong>Dirección:</strong> ${dir}`;
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
          nombre: val('proy-nombre'), fecha: val('proy-fecha'), monto: val('proy-monto').replace(/\./g, '').replace(',', '.'),
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
        if (typeof guardarSenaladosEnPadron === 'function') guardarSenaladosEnPadron();
        localStorage.removeItem(BORRADOR_KEY);
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

      document.getElementById('print-nombres').textContent = obtenerNombreCompletoCiudadano();

      const tDoc = document.getElementById('cit-tipo-doc').options[document.getElementById('cit-tipo-doc').selectedIndex]?.text || '';
      const nDoc = document.getElementById('cit-nro-doc').value || '';
      document.getElementById('print-cedula').textContent = `${tDoc} - ${nDoc}`;

      const sexoSelect = document.getElementById('cit-sexo');
      document.getElementById('print-sexo').textContent = sexoSelect.options[sexoSelect.selectedIndex]?.text || 'No indicó';
      document.getElementById('print-fecha-nac').textContent = document.getElementById('cit-fecha-nac').value || 'No indicó';
      document.getElementById('print-edad').textContent = (document.getElementById('cit-edad').value || '--').replace(' años', '');
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
          document.getElementById('print-proy-monto').textContent = document.getElementById('proy-monto').value || '0.00';
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
        document.getElementById('print-otra-instancia').textContent = instaRadio.value === 'si'
          ? `Presentado previamente en: ${document.getElementById('cual-instancia').value || 'Instancia no especificada'}`
          : 'Trámite originario (No se ha presentado en otra instancia)';
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