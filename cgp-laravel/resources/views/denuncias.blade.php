@extends('layouts.app')

@section('title', 'Recepción de Atención al Cliente – Contraloría del Municipio Páez')

@push('styles')
  <link rel="stylesheet" href="{{ asset('assets/css/denuncias.css') }}">
@endpush

@section('content')
  <!-- ════════════════════════════════════════════
     BANDA SUPERIOR / TÍTULO DE PÁGINA
     ════════════════════════════════════════════ -->
  <div class="page-header-strip" style="margin-top:78px;">
    <div class="page-header-inner">
      <div class="breadcrumb-row">
        <a href="{{ url('/') }}">Inicio</a>
        <span>›</span>
        <a href="{{ url('/#participacion') }}">Participación Ciudadana</a>
        <span>›</span>
        <span style="color:rgba(255,255,255,0.85);">Recepción de Denuncias</span>
      </div>
      <div class="page-header-accent"></div>
      <h1>Recepción de Atención al Cliente</h1>
      <p>Dirección de Atención al Ciudadano · Contraloría del Municipio Páez, Estado Portuguesa</p>
    </div>
  </div>


  <!-- ════════════════════════════════════════════
     SECCIÓN PRINCIPAL
     ════════════════════════════════════════════ -->
  <section id="denuncias">
    <div class="denuncias-inner">

      <!-- AVISO DE NO ANONIMATO — siempre visible -->
      <div class="aviso-anonimato">
        <span class="av-icon">
          <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
            <circle cx="12" cy="12" r="10" />
            <line x1="12" y1="8" x2="12" y2="12" />
            <line x1="12" y1="16" x2="12.01" y2="16" />
          </svg>
        </span>
        <div>
          <strong>Este sistema no admite denuncias anónimas.</strong>
          De conformidad con la Ley Orgánica de la Contraloría General de la República y del Sistema Nacional de Control Fiscal, la identificación del denunciante es obligatoria. Sus datos personales serán tratados con estricta confidencialidad y utilizados únicamente para los fines del proceso de investigación.
        </div>
      </div>


      <!-- ══════════════════════════════════════
         BARRA DE PROGRESO (Pasos 1–5)
         Oculta en la vista de selección; se activa al elegir tipo
         ══════════════════════════════════════ -->
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
            <span class="paso-etiqueta">REVISIÓN</span>
          </div>
        </div>
      </div>


      <!-- ══════════════════════════════════════
         VISTA DE SELECCIÓN (Paso 0)
         Se oculta al elegir un tipo de formulario
         ══════════════════════════════════════ -->
      <div id="vista-seleccion">
        <h2 class="section-title-center">Seleccione el Tipo de Denuncia</h2>
        <p class="section-subtitle-center">
          Elija la categoría que mejor describa su caso para habilitar el formulario correspondiente.
        </p>
        <div class="title-underline"></div>

        <div class="tipo-cards">

          <!-- Tarjeta A — Poder Popular -->
          <div class="tipo-card poder-popular" onclick="iniciarFlujo('poder-popular')" tabindex="0" role="button" aria-label="Denuncia de Proyectos del Poder Popular">
            <div class="tc-icon">
              <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                <path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z" />
                <polyline points="9 22 9 12 15 12 15 22" />
              </svg>
            </div>
            <div class="tc-label">Tipo A</div>
            <div class="tc-title">Denuncia de Proyectos del<br>Poder Popular</div>
            <div class="tc-desc">
              Para denuncias relacionadas con Consejos Comunales, Comunas y Proyectos Financiados a través de la Consulta Popular Nacional. Incluye campos para Código CITUR, monto y denominación del proyecto.
            </div>
            <span class="tc-btn">
              Iniciar denuncia
              <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                <line x1="5" y1="12" x2="19" y2="12" />
                <polyline points="12 5 19 12 12 19" />
              </svg>
            </span>
          </div>

          <!-- Tarjeta B — Denuncia General -->
          <div class="tipo-card general" onclick="iniciarFlujo('general')" tabindex="0" role="button" aria-label="Denuncia General">
            <div class="tc-icon">
              <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                <rect x="2" y="3" width="20" height="14" rx="2" ry="2" />
                <line x1="8" y1="21" x2="16" y2="21" />
                <line x1="12" y1="17" x2="12" y2="21" />
              </svg>
            </div>
            <div class="tc-label" style="color:#c10000;">Tipo B</div>
            <div class="tc-title">Denuncia General<br>(Organismos e Institutos)</div>
            <div class="tc-desc">
              Para denuncias dirigidas contra órganos públicos, Instituciones, entes Municipales, Funcionarios o Personas Naturales y Jurídicas sujetas al Control Fiscal del Municipio.
            </div>
            <span class="tc-btn" style="color:#c10000;">
              Iniciar denuncia
              <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                <line x1="5" y1="12" x2="19" y2="12" />
                <polyline points="12 5 19 12 12 19" />
              </svg>
            </span>
          </div>

        </div>
      </div><!-- /vista-seleccion -->


      <!-- ══════════════════════════════════════
         CONTENEDOR DEL FORMULARIO WIZARD
         Gestiona los pasos 1–5 para ambos tipos
         ══════════════════════════════════════ -->
      <div id="vista-wizard" style="display:none;">

        <!-- Botón para volver a la selección de tipo -->
        <button class="form-back-btn" onclick="volverSeleccion()">
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
            <line x1="19" y1="12" x2="5" y2="12" />
            <polyline points="12 19 5 12 12 5" />
          </svg>
          Volver a la selección
        </button>

        <!-- ─────────────────────────────────────────────────
           PASO 1 — TIPO DE TRÁMITE / CONTEXTO
           Diferente contenido según el tipo seleccionado
           ───────────────────────────────────────────────── -->
        <div class="wizard-paso" id="paso-1">

          <!-- Cabecera dinámica del paso (se rellena con JS) -->
          <div class="form-header" id="paso1-cabecera">
            <div class="form-header-icon" id="paso1-icono"></div>
            <div>
              <h2 id="paso1-titulo"></h2>
              <p id="paso1-subtitulo">Paso 1 de 5 · Tipo de Trámite</p>
            </div>
          </div>

          <div class="form-body">

            <!-- Banner de error del paso -->
            <div class="banner-error" id="err-paso1" style="display:none;">
              <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z" />
                <line x1="12" y1="9" x2="12" y2="13" />
                <line x1="12" y1="17" x2="12.01" y2="17" />
              </svg>
              <div><strong>Por favor corrija los siguientes campos:</strong>
                <ul id="err-paso1-lista"></ul>
              </div>
            </div>

            <!-- ── TIPO A: Selección de tipo de trámite ── -->
            <div id="bloque-tipo-tramite">
              <div class="form-section-title">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                  <circle cx="12" cy="12" r="10" />
                  <line x1="12" y1="8" x2="12" y2="12" />
                  <line x1="12" y1="16" x2="12.01" y2="16" />
                </svg>
                Seleccione el Tipo de Trámite <span style="color:#e63946;margin-left:3px;">*</span>
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
              </div>
              <span class="error-msg" id="tipo-tramite-err">Debe seleccionar un tipo de trámite.</span>

              <!-- Pregunta de Consulta Popular -->
              <div id="bloque-consulta-popular" style="display:none;margin-top:24px;">
                <div class="form-section" style="padding-top:20px;border-top:1px solid #eef1f7;">
                  <div class="form-section-title" id="consulta-title-color">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                      <circle cx="12" cy="12" r="10" />
                      <line x1="12" y1="8" x2="12" y2="12" />
                      <line x1="12" y1="16" x2="12.01" y2="16" />
                    </svg>
                    Contexto de la Denuncia <span style="color:#e63946;margin-left:3px;">*</span>
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
                    <span class="error-msg" id="es-consulta-err">
                      Debe indicar si la denuncia es sobre un proyecto de consulta popular.
                    </span>
                  </div>
                </div>
              </div>
            </div><!-- /bloque-tipo-tramite -->

          </div><!-- /form-body -->

          <!-- Navegación del paso -->
          <div class="wizard-nav">
            <span style="font-size:0.78rem;color:#888;">
              Los campos <span style="color:#e63946;font-weight:700;">*</span> son obligatorios.
            </span>
            <button class="btn-submit" id="btn-sig-paso1" onclick="siguientePaso(1)">
              Continuar
              <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                <line x1="5" y1="12" x2="19" y2="12" />
                <polyline points="12 5 19 12 12 19" />
              </svg>
            </button>
          </div>
        </div><!-- /paso-1 -->


        <!-- ─────────────────────────────────────────────────
           PASO 2 — DATOS DEL CIUDADANO (DENUNCIANTE)
           ───────────────────────────────────────────────── -->
        <div class="wizard-paso" id="paso-2" style="display:none;">

          <div class="form-header" id="paso2-cabecera">
            <div class="form-header-icon" id="paso2-icono"></div>
            <div>
              <h2 id="paso2-titulo"></h2>
              <p>Paso 2 de 5 · Datos del Ciudadano Solicitante</p>
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

            <!-- Sección: Identificación -->
            <div class="form-section">
              <div class="form-section-title" id="ciudadano-title-color">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                  <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2" />
                  <circle cx="12" cy="7" r="4" />
                </svg>
                Identificación del Denunciante
              </div>
              <div class="row g-3">
                <div class="col-md-3">
                  <div class="form-group">
                    <label class="form-label">Tipo de Documento <span class="required">*</span></label>
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
                    <label class="form-label">Número de Documento <span class="required">*</span></label>
                    <input type="text" id="cit-nro-doc" class="form-control" placeholder="Ej. 12345678" maxlength="10">
                    <span class="error-msg" id="cit-nro-doc-err">Ingrese un número de documento válido (5–10 dígitos).</span>
                  </div>
                </div>
                <div class="col-md-6">
                  <div class="form-group">
                    <label class="form-label">Apellidos y Nombres <span class="required">*</span></label>
                    <input type="text" id="cit-nombres" class="form-control" placeholder="Apellido Apellido, Nombre Nombre">
                    <span class="error-msg" id="cit-nombres-err">Ingrese sus apellidos y nombres completos.</span>
                  </div>
                </div>
                <div class="col-md-2">
                  <div class="form-group">
                    <label class="form-label">Sexo <span class="required">*</span></label>
                    <select id="cit-sexo" class="form-select">
                      <option value="">--</option>
                      <option value="M">Masculino</option>
                      <option value="F">Femenino</option>
                    </select>
                    <span class="error-msg" id="cit-sexo-err">Seleccione.</span>
                  </div>
                </div>
                <div class="col-md-2">
                  <div class="form-group">
                    <label class="form-label">Edad <span class="required">*</span></label>
                    <input type="number" id="cit-edad" class="form-control" placeholder="Años" min="1" max="120">
                    <span class="error-msg" id="cit-edad-err">Ingrese una edad válida.</span>
                  </div>
                </div>
                <div class="col-md-3">
                  <div class="form-group">
                    <label class="form-label">Fecha de Nacimiento <span class="required">*</span></label>
                    <input type="date" id="cit-fecha-nac" class="form-control">
                    <span class="error-msg" id="cit-fecha-nac-err">Seleccione su fecha de nacimiento.</span>
                  </div>
                </div>
                <div class="col-md-3">
                  <div class="form-group">
                    <label class="form-label">Estado Civil</label>
                    <select id="cit-ecivil" class="form-select">
                      <option value="">-- Seleccione --</option>
                      <option>Soltero(a)</option>
                      <option>Casado(a)</option>
                      <option>Divorciado(a)</option>
                      <option>Viudo(a)</option>
                      <option>Unión estable</option>
                    </select>
                  </div>
                </div>
                <div class="col-md-2">
                  <div class="form-group">
                    <label class="form-label">Nivel Educativo</label>
                    <select id="cit-edu" class="form-select">
                      <option value="">-- Seleccione --</option>
                      <option>Primaria</option>
                      <option>Secundaria</option>
                      <option>Técnico</option>
                      <option>Universitario</option>
                      <option>Postgrado</option>
                    </select>
                  </div>
                </div>
                <div class="col-md-4">
                  <div class="form-group">
                    <label class="form-label">Profesión u Ocupación</label>
                    <input type="text" id="cit-profesion" class="form-control" placeholder="Ej. Docente, Comerciante...">
                  </div>
                </div>
              </div>
            </div>

            <!-- Sección: Dirección y Contacto -->
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
                    <label class="form-label">Correo Electrónico <span class="required">*</span></label>
                    <input type="email" id="cit-correo" class="form-control" placeholder="correo@ejemplo.com">
                    <span class="error-msg" id="cit-correo-err">Ingrese un correo válido.</span>
                  </div>
                </div>
                <div class="col-md-4">
                  <div class="form-group">
                    <label class="form-label">Confirmar Correo <span class="required">*</span></label>
                    <input type="email" id="cit-correo2" class="form-control" placeholder="Repita su correo">
                    <span class="error-msg" id="cit-correo2-err">Los correos no coinciden.</span>
                  </div>
                </div>
                <div class="col-md-2">
                  <div class="form-group">
                    <label class="form-label">Teléfono Celular <span class="required">*</span></label>
                    <input type="tel" id="cit-telf-cel" class="form-control" placeholder="04XX-XXXXXXX">
                    <span class="error-msg" id="cit-telf-cel-err">Formato: 04XX-XXXXXXX.</span>
                  </div>
                </div>
                <div class="col-md-2">
                  <div class="form-group">
                    <label class="form-label">Teléfono Habitación</label>
                    <input type="tel" id="cit-telf-hab" class="form-control" placeholder="0255-XXXXXXX">
                  </div>
                </div>
                <div class="col-md-3">
                  <div class="form-group">
                    <label class="form-label">Parroquia</label>
                    <input type="text" id="cit-parroquia" class="form-control" placeholder="Parroquia de residencia">
                  </div>
                </div>
                <div class="col-md-3">
                  <div class="form-group">
                    <label class="form-label">Municipio <span class="required">*</span></label>
                    <input type="text" id="cit-municipio" class="form-control" placeholder="Municipio" value="Páez">
                    <span class="error-msg" id="cit-municipio-err">Este campo es obligatorio.</span>
                  </div>
                </div>
                <div class="col-md-3">
                  <div class="form-group">
                    <label class="form-label">Ciudad / Estado</label>
                    <input type="text" id="cit-ciudad" class="form-control" placeholder="Acarigua, Portuguesa">
                  </div>
                </div>
                <div class="col-12">
                  <div class="form-group">
                    <label class="form-label">Dirección de Habitación <span class="required">*</span></label>
                    <input type="text" id="cit-direccion" class="form-control" placeholder="Calle, Avenida, Urbanización, Casa/Apto...">
                    <span class="error-msg" id="cit-direccion-err">Ingrese su dirección completa (mínimo 10 caracteres).</span>
                  </div>
                </div>
              </div>
            </div>

          </div><!-- /form-body -->

          <div class="wizard-nav">
            <button class="btn-submit btn-outline-inst" onclick="anteriorPaso(2)">
              <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                <line x1="19" y1="12" x2="5" y2="12" />
                <polyline points="12 19 5 12 12 5" />
              </svg>
              Anterior
            </button>
            <button class="btn-submit" id="btn-sig-paso2" onclick="siguientePaso(2)">
              Continuar
              <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                <line x1="5" y1="12" x2="19" y2="12" />
                <polyline points="12 5 19 12 12 19" />
              </svg>
            </button>
          </div>
        </div><!-- /paso-2 -->


        <!-- ─────────────────────────────────────────────────
           PASO 3 — DATOS DEL SEÑALADO
           ───────────────────────────────────────────────── -->
        <div class="wizard-paso" id="paso-3" style="display:none;">

          <div class="form-header" id="paso3-cabecera">
            <div class="form-header-icon" id="paso3-icono"></div>
            <div>
              <h2 id="paso3-titulo"></h2>
              <p>Paso 3 de 5 · Identificación del Señalado</p>
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

            <!-- Tipo de señalado -->
            <div class="form-section">
              <div class="form-section-title" id="senalado-title-color">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                  <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2" />
                  <circle cx="9" cy="7" r="4" />
                  <path d="M23 21v-2a4 4 0 0 0-3-3.87" />
                  <path d="M16 3.13a4 4 0 0 1 0 7.75" />
                </svg>
                Tipo del Señalado <span style="color:#e63946;margin-left:3px;">*</span>
              </div>
              <div class="form-group">
                <label class="form-label">¿Contra quién o qué instancia va dirigida?</label>
                <div class="check-group" id="tipo-senalado-grupo">
                  <label class="check-item">
                    <input type="checkbox" name="tipo_senalado" value="persona_natural">
                    <label>Persona Natural</label>
                  </label>
                  <label class="check-item">
                    <input type="checkbox" name="tipo_senalado" value="persona_juridica">
                    <label>Persona Jurídica</label>
                  </label>
                  <label class="check-item">
                    <input type="checkbox" name="tipo_senalado" value="organo_ente">
                    <label>Órgano o Ente</label>
                  </label>
                  <label class="check-item">
                    <input type="checkbox" name="tipo_senalado" value="comuna">
                    <label>Comuna</label>
                  </label>
                  <label class="check-item">
                    <input type="checkbox" name="tipo_senalado" value="consejo_comunal">
                    <label>Consejo Comunal</label>
                  </label>
                  <label class="check-item">
                    <input type="checkbox" name="tipo_senalado" value="juez_paz">
                    <label>Juez de Paz</label>
                  </label>
                </div>
                <div style="display:flex;align-items:center;gap:10px;margin-top:10px;">
                  <label class="check-item" style="flex-shrink:0;">
                    <input type="checkbox" id="senalado-otro-chk" onchange="toggleSenaladoOtro(this)">
                    <label>Otro:</label>
                  </label>
                  <input type="text" id="senalado-otro-txt" class="form-control" placeholder="Especifique..." disabled style="opacity:0.5;cursor:not-allowed;">
                </div>
                <span class="error-msg" id="tipo-senalado-err">Debe seleccionar al menos un tipo de señalado.</span>
              </div>

              <div class="form-group" style="margin-top:16px;">
                <label class="form-label">
                  Ubicación Geográfica del Señalado <span class="required">*</span>
                </label>
                <input type="text" id="sen-ubicacion" class="form-control" placeholder="Dirección, Parroquia, Consejo Comunal, Órgano o Ente...">
                <span class="error-msg" id="sen-ubicacion-err">Ingrese la ubicación del señalado (mínimo 5 caracteres).</span>
              </div>
            </div>

            <!-- Tabla de señalados -->
            <div class="form-section">
              <div class="form-section-title" id="tabla-sen-title-color">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                  <rect x="3" y="3" width="18" height="18" rx="2" ry="2" />
                  <line x1="3" y1="9" x2="21" y2="9" />
                  <line x1="3" y1="15" x2="21" y2="15" />
                  <line x1="9" y1="3" x2="9" y2="21" />
                </svg>
                Datos del Señalado(s) <span style="color:#e63946;margin-left:3px;">*</span>
              </div>
              <p style="font-size:0.78rem;color:#888;margin-bottom:12px;">
                Al menos un señalado debe tener Cédula/RIF o Nombre completo.
              </p>
              <div style="overflow-x:auto;margin-bottom:12px;">
                <table class="tabla-senalados" id="tabla-senalados">
                  <thead>
                    <tr>
                      <th>Cédula (V/E)</th>
                      <th>Apellidos y Nombres</th>
                      <th>Nombre del Consejo Comunal / Comuna</th>
                      <th>Código SITUR</th>
                      <th>R.I.F.</th>
                      <th></th>
                    </tr>
                  </thead>
                  <tbody id="tbody-senalados">
                    <tr>
                      <td><input type="text" placeholder="V-00000000" oninput="validarCeldaCedula(this)" onblur="validarCeldaCedula(this)"></td>
                      <td><input type="text" placeholder="Apellido, Nombre" oninput="validarCeldaNombre(this)" onblur="validarCeldaNombre(this)"></td>
                      <td><input type="text" placeholder="Nombre de la instancia"></td>
                      <td><input type="text" placeholder="Código SITUR" oninput="validarCeldaSitur(this)" onblur="validarCeldaSitur(this)"></td>
                      <td><input type="text" placeholder="J-XXXXXXXX" oninput="validarCeldaRif(this)" onblur="validarCeldaRif(this)"></td>
                      <td>
                        <button type="button" onclick="eliminarFilaSenalado(this)" title="Eliminar fila" class="btn-tabla-eliminar">
                          <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                            <line x1="18" y1="6" x2="6" y2="18" />
                            <line x1="6" y1="6" x2="18" y2="18" />
                          </svg>
                        </button>
                      </td>
                    </tr>
                  </tbody>
                </table>
              </div>
              <span class="error-msg" id="tabla-senalados-err">Al menos el primer señalado debe tener cédula o nombre completo.</span>
              <button type="button" class="btn-agregar-fila" onclick="agregarFilaSenalado()">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                  <line x1="12" y1="5" x2="12" y2="19" />
                  <line x1="5" y1="12" x2="19" y2="12" />
                </svg>
                Agregar otro señalado
              </button>
            </div>

            <!-- Bloque de Proyecto de Consulta Popular -->
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
                        Denominación del Proyecto <span class="required">*</span>
                      </label>
                      <input type="text" id="proy-nombre" class="form-control" placeholder="Nombre oficial del proyecto">
                      <span class="error-msg" id="proy-nombre-err">Ingrese el nombre del proyecto.</span>
                    </div>
                  </div>
                  <div class="col-md-3">
                    <div class="form-group">
                      <label class="form-label">
                        Fecha de Aprobación <span class="required">*</span>
                      </label>
                      <input type="date" id="proy-fecha" class="form-control">
                      <span class="error-msg" id="proy-fecha-err">Seleccione la fecha de aprobación.</span>
                    </div>
                  </div>
                  <div class="col-md-3">
                    <div class="form-group">
                      <label class="form-label">
                        Monto Estimado (Bs) <span class="required">*</span>
                      </label>
                      <input type="text" id="proy-monto" class="form-control" placeholder="Ej. 500000">
                      <span class="error-msg" id="proy-monto-err">Ingrese un monto numérico válido.</span>
                    </div>
                  </div>
                  <div class="col-md-6">
                    <div class="form-group">
                      <label class="form-label">Ente Financiador <span class="required">*</span></label>
                      <input type="text" id="proy-financiador" class="form-control" placeholder="Nombre del ente financiador">
                      <span class="error-msg" id="proy-financiador-err">Ingrese el ente financiador.</span>
                    </div>
                  </div>
                  <div class="col-md-3">
                    <div class="form-group">
                      <label class="form-label">Código CITUR</label>
                      <input type="text" id="proy-citur" class="form-control mayusculas" placeholder="CÓDIGO CITUR" oninput="this.value=this.value.toUpperCase()">
                    </div>
                  </div>
                  <div class="col-md-3">
                    <div class="form-group">
                      <label class="form-label">Código SITUR</label>
                      <input type="text" id="proy-situr" class="form-control mayusculas" placeholder="CÓDIGO SITUR" oninput="this.value=this.value.toUpperCase()">
                    </div>
                  </div>
                </div>
              </div>
            </div><!-- /bloque-proyecto-consulta -->

          </div><!-- /form-body -->

          <div class="wizard-nav">
            <button class="btn-submit btn-outline-inst" onclick="anteriorPaso(3)">
              <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                <line x1="19" y1="12" x2="5" y2="12" />
                <polyline points="12 19 5 12 12 5" />
              </svg>
              Anterior
            </button>
            <button class="btn-submit" id="btn-sig-paso3" onclick="siguientePaso(3)">
              Continuar
              <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                <line x1="5" y1="12" x2="19" y2="12" />
                <polyline points="12 5 19 12 12 19" />
              </svg>
            </button>
          </div>
        </div><!-- /paso-3 -->


        <!-- ─────────────────────────────────────────────────
           PASO 4 — DESCRIPCIÓN Y HECHOS
           ───────────────────────────────────────────────── -->
        <div class="wizard-paso" id="paso-4" style="display:none;">

          <div class="form-header" id="paso4-cabecera">
            <div class="form-header-icon" id="paso4-icono"></div>
            <div>
              <h2 id="paso4-titulo"></h2>
              <p>Paso 4 de 5 · Descripción de los Hechos</p>
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
                Descripción y Señalamiento
              </div>

              <div class="form-group">
                <label class="form-label">
                  Narración Circunstanciada de los Hechos <span class="required">*</span>
                </label>
                <textarea id="narracion" class="form-textarea" placeholder="Describa detalladamente qué ocurrió, cuándo, cómo y quiénes están involucrados. Incluya cualquier dato relevante..." oninput="actualizarContadorNarracion(this)" style="min-height:140px;"></textarea>
                <div style="display:flex;justify-content:space-between;align-items:center;margin-top:4px;">
                  <span class="error-msg" id="narracion-err" style="display:inline;">La narración debe tener al menos 50 caracteres.</span>
                  <span class="contador-chars" id="narracion-contador">0 / 3000 caracteres</span>
                </div>
              </div>

              <div class="form-group" style="margin-top:20px;">
                <label class="form-label">
                  ¿Esta situación ha sido presentada ante otra instancia anteriormente?
                  <span class="required">*</span>
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
                  ¿Ante cuál instancia? <span class="required">*</span>
                </label>
                <input type="text" id="cual-instancia" class="form-control" placeholder="Nombre de la institución o instancia">
                <span class="error-msg" id="cual-instancia-err">Especifique la instancia ante la cual fue presentada.</span>
              </div>
            </div>

            <div class="form-section">
              <div class="form-section-title" id="evidencias-title-color">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                  <polyline points="21 8 21 21 3 21 3 8" />
                  <rect x="1" y="3" width="22" height="5" />
                  <line x1="10" y1="12" x2="14" y2="12" />
                </svg>
                Documentos y Evidencias que Anexa
              </div>
              <div class="check-group" style="flex-wrap:wrap;gap:10px;">
                <label class="check-item">
                  <input type="checkbox" name="evidencia" value="ci_testigo">
                  <label>Copia C.I. del testigo</label>
                </label>
                <label class="check-item">
                  <input type="checkbox" name="evidencia" value="ci_denunciante">
                  <label>Copia C.I. del denunciante</label>
                </label>
                <label class="check-item">
                  <input type="checkbox" name="evidencia" value="carta_exposicion">
                  <label>Carta de exposición de motivo</label>
                </label>
                <label class="check-item">
                  <input type="checkbox" name="evidencia" value="fotografias">
                  <label>Fotografías</label>
                </label>
                <label class="check-item">
                  <input type="checkbox" name="evidencia" value="video">
                  <label>Video</label>
                </label>
                <label class="check-item">
                  <input type="checkbox" name="evidencia" value="grabacion_voz">
                  <label>Grabación de voz</label>
                </label>
                <label class="check-item">
                  <input type="checkbox" name="evidencia" value="testimonio_escrito">
                  <label>Testimonio escrito con firma y huella</label>
                </label>
                <label class="check-item">
                  <input type="checkbox" name="evidencia" value="otros_docs">
                  <label>Otros documentos</label>
                </label>
              </div>

              <div style="margin-top:20px;">
                <label class="form-label">Adjuntar Archivos</label>
                <div class="file-drop" onclick="document.getElementById('archivo-input').click()" ondragover="event.preventDefault();this.style.borderColor='#1565c0'" ondragleave="this.style.borderColor=''" ondrop="manejarDropArchivos(event)">
                  <div class="file-drop-icon">
                    <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                      <polyline points="16 16 12 12 8 16" />
                      <line x1="12" y1="12" x2="12" y2="21" />
                      <path d="M20.39 18.39A5 5 0 0 0 18 9h-1.26A8 8 0 1 0 3 16.3" />
                    </svg>
                  </div>
                  <p>
                    <strong>Haga clic o arrastre archivos aquí</strong><br>
                    Imágenes, videos, audios, PDF — máx. 10 MB por archivo
                  </p>
                  <input type="file" id="archivo-input" multiple accept="image/*,video/*,audio/*,.pdf,.doc,.docx" onchange="agregarArchivos(this.files)" style="display:none;">
                </div>
                <span class="error-msg" id="archivo-err" style="display:none;margin-top:6px;"></span>
                <div class="file-list" id="archivos-lista"></div>
              </div>
            </div>

          </div><!-- /form-body -->

          <div class="wizard-nav">
            <button class="btn-submit btn-outline-inst" onclick="anteriorPaso(4)">
              <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                <line x1="19" y1="12" x2="5" y2="12" />
                <polyline points="12 19 5 12 12 5" />
              </svg>
              Anterior
            </button>
            <button class="btn-submit" id="btn-sig-paso4" onclick="siguientePaso(4)">
              Revisar solicitud
              <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                <line x1="5" y1="12" x2="19" y2="12" />
                <polyline points="12 5 19 12 12 19" />
              </svg>
            </button>
          </div>
        </div><!-- /paso-4 -->


        <!-- ─────────────────────────────────────────────────
           PASO 5 — REVISIÓN Y CONFIRMACIÓN
           ───────────────────────────────────────────────── -->
        <div class="wizard-paso" id="paso-5" style="display:none;">

          <div class="form-header" id="paso5-cabecera">
            <div class="form-header-icon" id="paso5-icono"></div>
            <div>
              <h2 id="paso5-titulo"></h2>
              <p>Paso 5 de 5 · Revisión y Confirmación</p>
            </div>
          </div>

          <div class="form-body">

            <!-- Resumen de la solicitud -->
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
                </div>
              </div>
            </div>

            <!-- Declaración jurada -->
            <div class="form-section">
              <div style="background:#fffbf2;border:1px solid #f0d080;border-radius:6px;
                        padding:16px 20px;font-size:0.82rem;color:#555;line-height:1.75;margin-bottom:18px;">
                <strong style="color:#1a2340;">Declaración Jurada del Denunciante:</strong><br>
                Declaro que los datos suministrados son fidedignos, y estoy en conocimiento de que cualquier falta o falsedad en los mismos involucra sanciones o la no aceptación de la solicitud, conforme a la normativa legal vigente.
              </div>
              <div class="form-group">
                <label style="display:flex;align-items:flex-start;gap:10px;cursor:pointer;">
                  <input type="checkbox" id="acepta-declaracion" style="margin-top:2px;width:16px;height:16px;accent-color:#1565c0;flex-shrink:0;">
                  <span style="font-size:0.82rem;color:#1a2340;">
                    Acepto la declaración anterior y confirmo que la información proporcionada es veraz.
                  </span>
                </label>
                <span class="error-msg" id="acepta-decl-err">Debe aceptar la declaración para enviar su solicitud.</span>
              </div>
            </div>

          </div><!-- /form-body -->

          <div class="wizard-nav" style="justify-content:space-between;">
            <button class="btn-submit btn-outline-inst" onclick="anteriorPaso(5)">
              <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                <line x1="19" y1="12" x2="5" y2="12" />
                <polyline points="12 19 5 12 12 5" />
              </svg>
              Anterior
            </button>
            <div style="display:flex;gap:12px;flex-wrap:wrap;">
              <button class="btn-submit btn-pdf" id="btn-guardar-pdf" onclick="generarPDF()" style="display:none;">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                  <polyline points="8 17 12 21 16 17" />
                  <line x1="12" y1="12" x2="12" y2="21" />
                  <path d="M20.88 18.09A5 5 0 0 0 18 9h-1.26A8 8 0 1 0 3 16.29" />
                </svg>
                Guardar como PDF
              </button>
              <button class="btn-submit" id="btn-enviar-solicitud" onclick="enviarSolicitud()">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                  <line x1="22" y1="2" x2="11" y2="13" />
                  <polygon points="22 2 15 22 11 13 2 9 22 2" />
                </svg>
                Enviar Solicitud
              </button>
            </div>
          </div>
        </div><!-- /paso-5 -->


        <!-- ─────────────────────────────────────────────────
           PANTALLA DE CONFIRMACIÓN FINAL
           ───────────────────────────────────────────────── -->
        <div id="vista-confirmacion" style="display:none;">
          <div class="form-header" style="background:#2e7d32;">
            <div class="form-header-icon">
              <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <polyline points="20 6 9 17 4 12" />
              </svg>
            </div>
            <div>
              <h2>Solicitud Recibida Exitosamente</h2>
              <p>Su trámite ha sido registrado en el sistema de la Contraloría del Municipio Páez.</p>
            </div>
          </div>
          <div class="form-body" style="text-align:center;padding:40px 36px;">
            <p style="font-size:0.88rem;color:#666;margin-bottom:12px;">
              Su número de expediente es:
            </p>
            <div id="nro-expediente-display" style="background:#1a2340;color:#fff;display:inline-block;padding:12px 36px;
                      border-radius:6px;font-size:1.4rem;font-weight:700;letter-spacing:3px;
                      font-family:monospace;margin-bottom:20px;">
              OAC-2026-0001
            </div>
            <p style="font-size:0.8rem;color:#888;margin-bottom:28px;">
              Conserve este número para hacer seguimiento de su caso.
            </p>
            <div class="row g-3 text-start" style="max-width:600px;margin:0 auto 32px;">
              <div class="col-md-6">
                <div class="resumen-item">
                  <div class="resumen-etiqueta">Estado Actual</div>
                  <div class="resumen-valor" style="color:#e6a000;">En proceso de revisión</div>
                </div>
              </div>
              <div class="col-md-6">
                <div class="resumen-item">
                  <div class="resumen-etiqueta">Plazo de Respuesta</div>
                  <div class="resumen-valor">5 días hábiles</div>
                </div>
              </div>
              <div class="col-md-6">
                <div class="resumen-item">
                  <div class="resumen-etiqueta">Notificación enviada a</div>
                  <div class="resumen-valor" id="conf-correo">—</div>
                </div>
              </div>
              <div class="col-md-6">
                <div class="resumen-item">
                  <div class="resumen-etiqueta">Fecha de Registro</div>
                  <div class="resumen-valor" id="conf-fecha">—</div>
                </div>
              </div>
            </div>
            <div style="display:flex;gap:12px;justify-content:center;flex-wrap:wrap;">
              <button class="btn-submit btn-outline-inst" onclick="imprimirComprobante()">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                  <polyline points="6 9 6 2 18 2 18 9" />
                  <path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2" />
                  <rect x="6" y="14" width="12" height="8" />
                </svg>
                Imprimir Comprobante
              </button>
              <button class="btn-submit" onclick="nuevaSolicitud()">
                Registrar Nueva Solicitud
              </button>
            </div>
          </div>
        </div><!-- /vista-confirmacion -->

      </div><!-- /vista-wizard -->

    </div><!-- /denuncias-inner -->
  </section>
@endsection

@push('scripts')
  <script src="{{ asset('assets/js/wizard.js') }}"></script>
@endpush
