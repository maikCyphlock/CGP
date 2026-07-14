/* ═══════════════════════════════════════════════════════════
   ESTADO GLOBAL DEL WIZARD
   ═══════════════════════════════════════════════════════════ */

/** Tipo de formulario activo: 'poder-popular' | 'general' */
var tipoFormulario = '';

/** Paso actual (1–5) */
var pasoActual = 1;

/** Total de pasos del proceso */
var TOTAL_PASOS = 5;


/* ═══════════════════════════════════════════════════════════
   CONFIGURACIÓN VISUAL POR TIPO
   Define los colores y textos de cabeceras según el tipo
   ═══════════════════════════════════════════════════════════ */
var configTipo = {
    'poder-popular': {
        claseHeader: '',           // azul institucional (default)
        claseBoton: '',
        claseSeccion: '',
        titulo: 'Denuncia de Proyectos del Poder Popular',
        iconoSVG: '<svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>'
    },
    'general': {
        claseHeader: 'rojo',
        claseBoton: 'rojo',
        claseSeccion: 'rojo',
        titulo: 'Denuncia General — Organismos e Institutos',
        iconoSVG: '<svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="3" width="20" height="14" rx="2" ry="2"/><line x1="8" y1="21" x2="16" y2="21"/><line x1="12" y1="17" x2="12" y2="21"/></svg>'
    }
};


/* ═══════════════════════════════════════════════════════════
   DOMINIOS DE CORREO ACEPTADOS
   Se valida todo lo que sigue al "@". Se permiten los
   proveedores más comunes en Venezuela, además de cualquier
   dominio institucional que termine en .gob.ve
   ═══════════════════════════════════════════════════════════ */
var DOMINIOS_CORREO_VALIDOS = [
    'gmail.com', 'hotmail.com', 'outlook.com', 'yahoo.com',
    'yahoo.es', 'live.com', 'icloud.com', 'aol.com', 'gob.ve',
    'cantv.net'
];

/**
 * Valida que el dominio del correo (lo que sigue al @) esté en la
 * lista de dominios aceptados, o que sea un subdominio .gob.ve
 * @param {string} correo
 * @returns {boolean}
 */
function validarDominioCorreo(correo) {
    var partes = correo.split('@');
    if (partes.length !== 2) return false;
    var dominio = partes[1].toLowerCase().trim();
    if (dominio.endsWith('.gob.ve')) return true;
    return DOMINIOS_CORREO_VALIDOS.indexOf(dominio) !== -1;
}


/* ═══════════════════════════════════════════════════════════
   TIPOS DE DOCUMENTO (Venezuela)
   Se reutilizan en todo el formulario donde amerite:
   personas naturales (V/E/P) y personas/entes colectivos (J/G/C)
   ═══════════════════════════════════════════════════════════ */
var TIPOS_DOCUMENTO_PERSONA = [
    { valor: 'V', etiqueta: 'V — Venezolano' },
    { valor: 'E', etiqueta: 'E — Extranjero' },
    { valor: 'P', etiqueta: 'P — Pasaporte' }
];

var TIPOS_DOCUMENTO_ENTIDAD = [
    { valor: 'J', etiqueta: 'J — Jurídico' },
    { valor: 'G', etiqueta: 'G — Gubernamental' },
    { valor: 'C', etiqueta: 'C — Comunal' }
];

var TIPOS_DOCUMENTO_TODOS = TIPOS_DOCUMENTO_PERSONA.concat(TIPOS_DOCUMENTO_ENTIDAD);

/**
 * Construye las <option> de un <select> de tipo de documento.
 * @param {Array} lista - TIPOS_DOCUMENTO_PERSONA | TIPOS_DOCUMENTO_ENTIDAD | TIPOS_DOCUMENTO_TODOS
 * @param {boolean} conPlaceholder - si incluye la opción "Seleccionar"
 */
function construirOpcionesTipoDoc(lista, conPlaceholder) {
    var html = conPlaceholder ? '<option value="">Seleccionar</option>' : '';
    lista.forEach(function (t) {
        html += '<option value="' + t.valor + '">' + t.etiqueta + '</option>';
    });
    return html;
}


/* ═══════════════════════════════════════════════════════════
   CÓDIGOS TELEFÓNICOS DE VENEZUELA
   ═══════════════════════════════════════════════════════════ */
var CODIGOS_MOVILES = ['0412', '0414', '0416', '0424', '0426'];

/** Códigos de área de telefonía fija, Portuguesa primero (Municipio Páez) */
var CODIGOS_FIJOS = [
    { cod: '0255', zona: 'Acarigua-Araure, Portuguesa' },
    { cod: '0257', zona: 'Guanare, Portuguesa' },
    { cod: '0212', zona: 'Caracas, Distrito Capital' },
    { cod: '0241', zona: 'Valencia, Carabobo' },
    { cod: '0251', zona: 'Barquisimeto, Lara' },
    { cod: '0261', zona: 'Maracaibo, Zulia' },
    { cod: '0271', zona: 'San Cristóbal, Táchira' },
    { cod: '0281', zona: 'Barcelona-Pto. La Cruz, Anzoátegui' },
    { cod: '0285', zona: 'Porlamar, Nueva Esparta' },
    { cod: '0234', zona: 'Maracay, Aragua' },
    { cod: '0243', zona: 'Guacara-San Joaquín, Carabobo' },
    { cod: '0295', zona: 'Cumaná, Sucre' }
];


/* ═══════════════════════════════════════════════════════════
   PLANTILLAS DE CAMPOS SEGÚN TIPO DE SEÑALADO
   Cada tipo de señalado tiene un mini-formulario propio con
   los datos necesarios para verificar su identidad ante la
   Contraloría Municipal.
   ═══════════════════════════════════════════════════════════ */
var PLANTILLAS_SENALADO = {
    persona_natural: {
        titulo: 'Datos de la Persona Natural Señalada',
        html:
            '<div class="row g-3">' +
            '  <div class="col-md-3"><div class="form-group">' +
            '    <label class="form-label">Tipo de Documento <span class="required">*</span></label>' +
            '    <select class="form-select campo-dinamico-req" data-campo="pn-tipo-doc">' +
            construirOpcionesTipoDoc(TIPOS_DOCUMENTO_PERSONA, true) +
            '    </select></div></div>' +
            '  <div class="col-md-3"><div class="form-group">' +
            '    <label class="form-label">Número de Documento <span class="required">*</span></label>' +
            '    <input type="text" class="form-control campo-dinamico-req" data-campo="pn-nro-doc" placeholder="Ej. 12345678"></div></div>' +
            '  <div class="col-md-6"><div class="form-group">' +
            '    <label class="form-label">Apellidos y Nombres <span class="required">*</span></label>' +
            '    <input type="text" class="form-control campo-dinamico-req" data-campo="pn-nombres" placeholder="Apellido Apellido, Nombre Nombre"></div></div>' +
            '  <div class="col-12"><div class="form-group">' +
            '    <label class="form-label">Dirección o Lugar donde Ejerce la Función</label>' +
            '    <input type="text" class="form-control" data-campo="pn-direccion" placeholder="Dirección, cargo u oficina que ocupa (si aplica)"></div></div>' +
            '</div>'
    },
    persona_juridica: {
        titulo: 'Datos de la Persona Jurídica Señalada',
        html:
            '<div class="row g-3">' +
            '  <div class="col-md-3"><div class="form-group">' +
            '    <label class="form-label">Tipo de Documento</label>' +
            '    <select class="form-select" data-campo="pj-tipo-doc" disabled>' +
            construirOpcionesTipoDoc(TIPOS_DOCUMENTO_ENTIDAD, false) +
            '    </select></div></div>' +
            '  <div class="col-md-3"><div class="form-group">' +
            '    <label class="form-label">R.I.F. <span class="required">*</span></label>' +
            '    <input type="text" class="form-control campo-dinamico-req" data-campo="pj-rif" placeholder="J-XXXXXXXX-X"></div></div>' +
            '  <div class="col-md-6"><div class="form-group">' +
            '    <label class="form-label">Razón Social / Denominación <span class="required">*</span></label>' +
            '    <input type="text" class="form-control campo-dinamico-req" data-campo="pj-razon" placeholder="Nombre de la empresa"></div></div>' +
            '  <div class="col-md-6"><div class="form-group">' +
            '    <label class="form-label">Representante Legal</label>' +
            '    <input type="text" class="form-control" data-campo="pj-representante" placeholder="Nombre y cédula del representante"></div></div>' +
            '  <div class="col-md-6"><div class="form-group">' +
            '    <label class="form-label">Dirección Fiscal</label>' +
            '    <input type="text" class="form-control" data-campo="pj-direccion" placeholder="Dirección fiscal de la empresa"></div></div>' +
            '</div>'
    },
    organo_ente: {
        titulo: 'Datos del Órgano o Ente Público Señalado',
        html:
            '<div class="row g-3">' +
            '  <div class="col-md-3"><div class="form-group">' +
            '    <label class="form-label">Tipo de Documento</label>' +
            '    <select class="form-select" data-campo="oe-tipo-doc" disabled>' +
            construirOpcionesTipoDoc(TIPOS_DOCUMENTO_ENTIDAD, false) +
            '    </select></div></div>' +
            '  <div class="col-md-3"><div class="form-group">' +
            '    <label class="form-label">R.I.F. del Ente</label>' +
            '    <input type="text" class="form-control" data-campo="oe-rif" placeholder="G-XXXXXXXX-X"></div></div>' +
            '  <div class="col-md-6"><div class="form-group">' +
            '    <label class="form-label">Nombre del Órgano o Ente <span class="required">*</span></label>' +
            '    <input type="text" class="form-control campo-dinamico-req" data-campo="oe-nombre" placeholder="Ej. Alcaldía del Municipio Páez"></div></div>' +
            '  <div class="col-12"><div class="form-group">' +
            '    <label class="form-label">Dirección o Sede</label>' +
            '    <input type="text" class="form-control" data-campo="oe-direccion" placeholder="Dirección de la sede"></div></div>' +
            '</div>'
    },
    comuna: {
        titulo: 'Datos de la Comuna Señalada',
        html:
            '<div class="row g-3">' +
            '  <div class="col-md-3"><div class="form-group">' +
            '    <label class="form-label">Tipo de Documento</label>' +
            '    <select class="form-select" data-campo="cm-tipo-doc" disabled>' +
            construirOpcionesTipoDoc(TIPOS_DOCUMENTO_ENTIDAD, false) +
            '    </select></div></div>' +
            '  <div class="col-md-3"><div class="form-group">' +
            '    <label class="form-label">Código SITUR</label>' +
            '    <input type="text" class="form-control mayusculas" data-campo="cm-situr" placeholder="CÓDIGO SITUR" oninput="this.value=this.value.toUpperCase()"></div></div>' +
            '  <div class="col-md-6"><div class="form-group">' +
            '    <label class="form-label">Nombre de la Comuna <span class="required">*</span></label>' +
            '    <input type="text" class="form-control campo-dinamico-req" data-campo="cm-nombre" placeholder="Nombre oficial de la comuna"></div></div>' +
            '  <div class="col-md-6"><div class="form-group">' +
            '    <label class="form-label">Parroquia</label>' +
            '    <input type="text" class="form-control" data-campo="cm-parroquia" placeholder="Parroquia a la que pertenece"></div></div>' +
            '  <div class="col-md-6"><div class="form-group">' +
            '    <label class="form-label">Municipio</label>' +
            '    <input type="text" class="form-control" data-campo="cm-municipio" placeholder="Municipio" value="Páez"></div></div>' +
            '</div>'
    },
    consejo_comunal: {
        titulo: 'Datos del Consejo Comunal Señalado',
        html:
            '<div class="row g-3">' +
            '  <div class="col-md-3"><div class="form-group">' +
            '    <label class="form-label">Tipo de Documento</label>' +
            '    <select class="form-select" data-campo="cc-tipo-doc" disabled>' +
            construirOpcionesTipoDoc(TIPOS_DOCUMENTO_ENTIDAD, false) +
            '    </select></div></div>' +
            '  <div class="col-md-3"><div class="form-group">' +
            '    <label class="form-label">Código SITUR</label>' +
            '    <input type="text" class="form-control mayusculas" data-campo="cc-situr" placeholder="CÓDIGO SITUR" oninput="this.value=this.value.toUpperCase()"></div></div>' +
            '  <div class="col-md-6"><div class="form-group">' +
            '    <label class="form-label">Nombre del Consejo Comunal <span class="required">*</span></label>' +
            '    <input type="text" class="form-control campo-dinamico-req" data-campo="cc-nombre" placeholder="Nombre oficial del consejo comunal"></div></div>' +
            '  <div class="col-md-6"><div class="form-group">' +
            '    <label class="form-label">R.I.F. (si posee)</label>' +
            '    <input type="text" class="form-control" data-campo="cc-rif" placeholder="J-XXXXXXXX-X"></div></div>' +
            '  <div class="col-md-6"><div class="form-group">' +
            '    <label class="form-label">Parroquia</label>' +
            '    <input type="text" class="form-control" data-campo="cc-parroquia" placeholder="Parroquia a la que pertenece"></div></div>' +
            '</div>'
    },
    juez_paz: {
        titulo: 'Datos del Juez o Jueza de Paz Señalado(a)',
        html:
            '<div class="row g-3">' +
            '  <div class="col-md-3"><div class="form-group">' +
            '    <label class="form-label">Tipo de Documento <span class="required">*</span></label>' +
            '    <select class="form-select campo-dinamico-req" data-campo="jp-tipo-doc">' +
            construirOpcionesTipoDoc(TIPOS_DOCUMENTO_PERSONA, true) +
            '    </select></div></div>' +
            '  <div class="col-md-3"><div class="form-group">' +
            '    <label class="form-label">Número de Documento <span class="required">*</span></label>' +
            '    <input type="text" class="form-control campo-dinamico-req" data-campo="jp-nro-doc" placeholder="Ej. 12345678"></div></div>' +
            '  <div class="col-md-6"><div class="form-group">' +
            '    <label class="form-label">Nombres y Apellidos <span class="required">*</span></label>' +
            '    <input type="text" class="form-control campo-dinamico-req" data-campo="jp-nombres" placeholder="Nombre completo"></div></div>' +
            '  <div class="col-md-6"><div class="form-group">' +
            '    <label class="form-label">Circuito Judicial de Paz</label>' +
            '    <input type="text" class="form-control" data-campo="jp-circuito" placeholder="Circuito al que pertenece"></div></div>' +
            '  <div class="col-md-6"><div class="form-group">' +
            '    <label class="form-label">Parroquia de Actuación</label>' +
            '    <input type="text" class="form-control" data-campo="jp-parroquia" placeholder="Parroquia donde ejerce funciones"></div></div>' +
            '</div>'
    }
};


/* ═══════════════════════════════════════════════════════════
   INICIO DEL FLUJO
   Se llama al hacer clic en una tarjeta de tipo
   ═══════════════════════════════════════════════════════════ */

/**
 * Inicia el wizard con el tipo seleccionado.
 * @param {string} tipo - 'poder-popular' o 'general'
 */
function iniciarFlujo(tipo) {
    tipoFormulario = tipo;
    pasoActual = 1;

    // Ocultar selección, mostrar barra y wizard
    document.getElementById('vista-seleccion').style.display = 'none';
    document.getElementById('barra-progreso').style.display = 'block';
    document.getElementById('vista-wizard').style.display = 'block';

    // Aplicar configuración visual al wizard
    aplicarEstiloTipo();

    // Mostrar/ocultar bloque de consulta popular (solo en Tipo A)
    var bloqueConsulta = document.getElementById('bloque-consulta-popular');
    bloqueConsulta.style.display = 'none';

    // Mostrar el primer paso
    mostrarPaso(1);

    // Scroll suave a la sección
    document.getElementById('denuncias').scrollIntoView({ behavior: 'smooth', block: 'start' });
}


/**
 * Aplica los estilos de color (azul / rojo) a todos los
 * elementos dependientes del tipo de formulario.
 */
function aplicarEstiloTipo() {
    var cfg = configTipo[tipoFormulario];

    // Actualizar cabeceras de todos los pasos
    for (var i = 1; i <= TOTAL_PASOS; i++) {
        var cabecera = document.getElementById('paso' + i + '-cabecera');
        var icono = document.getElementById('paso' + i + '-icono');
        var titEl = document.getElementById('paso' + i + '-titulo');
        if (!cabecera) continue;
        cabecera.className = 'form-header' + (cfg.claseHeader ? ' ' + cfg.claseHeader : '');
        if (icono) icono.innerHTML = cfg.iconoSVG;
        if (titEl) titEl.textContent = cfg.titulo;
    }

    // Colorear secciones según tipo
    var secciones = [
        'ciudadano-title-color', 'contacto-title-color', 'senalado-title-color',
        'tabla-sen-title-color', 'narracion-title-color', 'evidencias-title-color',
        'revision-title-color', 'consulta-title-color'
    ];
    secciones.forEach(function (id) {
        var el = document.getElementById(id);
        if (el) el.className = 'form-section-title' + (cfg.claseSeccion ? ' ' + cfg.claseSeccion : '');
    });

    // Colorear botones principales
    ['btn-sig-paso1', 'btn-sig-paso2', 'btn-sig-paso3', 'btn-sig-paso4', 'btn-enviar-solicitud'].forEach(function (id) {
        var el = document.getElementById(id);
        if (!el) return;
        el.className = 'btn-submit' + (cfg.claseBoton ? ' ' + cfg.claseBoton : '');
    });
}


/* ═══════════════════════════════════════════════════════════
   NAVEGACIÓN ENTRE PASOS
   ═══════════════════════════════════════════════════════════ */

/**
 * Avanza al siguiente paso tras validar el actual.
 * @param {number} pasoOrigen - Número del paso actual
 */
function siguientePaso(pasoOrigen) {
    if (!validarPaso(pasoOrigen)) return;

    // Antes del paso 5 se puebla el resumen
    if (pasoOrigen === 4) poblarResumen();

    pasoActual = pasoOrigen + 1;
    mostrarPaso(pasoActual);
}


/**
 * Retrocede al paso anterior sin validar.
 * @param {number} pasoOrigen - Número del paso actual
 */
function anteriorPaso(pasoOrigen) {
    pasoActual = pasoOrigen - 1;
    mostrarPaso(pasoActual);
}


/**
 * Muestra el paso indicado y actualiza la barra de progreso.
 * @param {number} numeroPaso - 1..5
 */
function mostrarPaso(numeroPaso) {
    // Ocultar todos los pasos
    for (var i = 1; i <= TOTAL_PASOS; i++) {
        var el = document.getElementById('paso-' + i);
        if (el) el.style.display = 'none';
    }

    // Mostrar el paso solicitado
    var pasoEl = document.getElementById('paso-' + numeroPaso);
    if (pasoEl) pasoEl.style.display = 'block';

    // Actualizar barra de progreso
    document.querySelectorAll('.paso-item').forEach(function (item) {
        var n = parseInt(item.dataset.paso, 10);
        item.classList.remove('activo', 'completado');
        if (n === numeroPaso - 1) item.classList.add('activo');
        else if (n < numeroPaso - 1) item.classList.add('completado');
    });

    // Scroll al inicio del wizard
    var wizard = document.getElementById('vista-wizard');
    if (wizard) wizard.scrollIntoView({ behavior: 'smooth', block: 'start' });
}


/**
 * Regresa a la pantalla de selección de tipo.
 * Limpia el estado del wizard.
 */
function volverSeleccion() {
    document.getElementById('vista-seleccion').style.display = 'block';
    document.getElementById('barra-progreso').style.display = 'none';
    document.getElementById('vista-wizard').style.display = 'none';
    document.getElementById('vista-confirmacion').style.display = 'none';

    tipoFormulario = '';
    pasoActual = 1;
    limpiarTodosLosErrores();

    document.getElementById('denuncias').scrollIntoView({ behavior: 'smooth', block: 'start' });
}


/* ═══════════════════════════════════════════════════════════
   VALIDACIÓN POR PASO
   ═══════════════════════════════════════════════════════════ */

/**
 * Valida los campos del paso indicado.
 * Retorna true si todo es válido; false si hay errores.
 * @param {number} numeroPaso
 * @returns {boolean}
 */
function validarPaso(numeroPaso) {
    limpiarErroresPaso(numeroPaso);
    var errores = [];

    if (numeroPaso === 1) {
        // Validar tipo de trámite seleccionado
        var tipoTramite = document.querySelector('input[name="tipo_tramite"]:checked');
        if (!tipoTramite) {
            errores.push('Debe seleccionar un tipo de trámite.');
            document.getElementById('tipo-tramite-err').classList.add('visible');
            document.querySelectorAll('.tramite-card').forEach(function (c) {
                c.style.borderColor = '#e63946';
            });
        } else {
            document.querySelectorAll('.tramite-card').forEach(function (c) {
                c.style.borderColor = '';
            });

            // Si es denuncia, verificar si fue contestada la pregunta de consulta popular
            if (tipoTramite.value === 'denuncia') {
                document.getElementById('bloque-consulta-popular').style.display = 'block';
                var esConsulta = document.querySelector('input[name="es_consulta"]:checked');
                if (!esConsulta) {
                    errores.push('Indique si la denuncia es sobre un proyecto de consulta popular.');
                    document.getElementById('es-consulta-err').classList.add('visible');
                }
            }
        }
    }

    if (numeroPaso === 2) {
        // Tipo de documento
        if (!document.getElementById('cit-tipo-doc').value) {
            errores.push('Tipo de documento');
            marcarError('cit-tipo-doc', 'cit-tipo-doc-err');
        }
        // Número de documento
        var nroDoc = document.getElementById('cit-nro-doc').value.trim();
        if (!/^\d{5,10}$/.test(nroDoc)) {
            errores.push('Número de documento');
            marcarError('cit-nro-doc', 'cit-nro-doc-err');
        }
        // Nombres
        if (document.getElementById('cit-nombres').value.trim().length < 5) {
            errores.push('Apellidos y Nombres');
            marcarError('cit-nombres', 'cit-nombres-err');
        }
        // Sexo
        if (!document.getElementById('cit-sexo').value) {
            errores.push('Sexo');
            marcarError('cit-sexo', 'cit-sexo-err');
        }
        // Fecha de nacimiento (la edad se calcula automáticamente a partir de ella)
        if (!document.getElementById('cit-fecha-nac').value) {
            errores.push('Fecha de Nacimiento');
            marcarError('cit-fecha-nac', 'cit-fecha-nac-err');
        } else {
            var edadCalculada = calcularEdad(document.getElementById('cit-fecha-nac').value);
            if (edadCalculada === null || edadCalculada < 1 || edadCalculada > 120) {
                errores.push('Fecha de Nacimiento (edad inválida)');
                marcarError('cit-fecha-nac', 'cit-fecha-nac-err');
            }
        }
        // Correo
        var correo = document.getElementById('cit-correo').value.trim();
        document.getElementById('cit-correo-err').textContent = 'Ingrese un correo válido.';
        if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(correo)) {
            errores.push('Correo Electrónico');
            marcarError('cit-correo', 'cit-correo-err');
        } else if (!validarDominioCorreo(correo)) {
            errores.push('Dominio de correo no reconocido');
            marcarError('cit-correo', 'cit-correo-err');
            document.getElementById('cit-correo-err').textContent =
                'Dominio no reconocido. Use gmail.com, hotmail.com, outlook.com, yahoo.com u otro dominio institucional (.gob.ve).';
        }
        // Confirmar correo
        var correo2 = document.getElementById('cit-correo2').value.trim();
        if (correo !== correo2) {
            errores.push('Confirmar Correo');
            marcarError('cit-correo2', 'cit-correo2-err');
        }
        // Teléfono celular (código de operadora + número)
        var telfCelCod = document.getElementById('cit-telf-cel-cod').value;
        var telfCelNum = document.getElementById('cit-telf-cel-num').value.trim();
        if (!telfCelCod || !/^\d{7}$/.test(telfCelNum)) {
            errores.push('Teléfono Celular');
            marcarError('cit-telf-cel-num', 'cit-telf-cel-err');
            document.getElementById('cit-telf-cel-cod').classList.toggle('invalid', !telfCelCod);
        }
        // Teléfono de habitación (opcional, pero si se llena el número debe tener código)
        var telfHabCod = document.getElementById('cit-telf-hab-cod').value;
        var telfHabNum = document.getElementById('cit-telf-hab-num').value.trim();
        if (telfHabNum && (!telfHabCod || !/^\d{7}$/.test(telfHabNum))) {
            errores.push('Teléfono de Habitación');
            marcarError('cit-telf-hab-num', 'cit-telf-hab-err');
        }
        // Municipio
        if (!document.getElementById('cit-municipio').value.trim()) {
            errores.push('Municipio');
            marcarError('cit-municipio', 'cit-municipio-err');
        }
        // Dirección
        if (document.getElementById('cit-direccion').value.trim().length < 10) {
            errores.push('Dirección de Habitación');
            marcarError('cit-direccion', 'cit-direccion-err');
        }
    }

    if (numeroPaso === 3) {
        // Al menos un tipo de señalado seleccionado
        var tiposSenalados = document.querySelectorAll('input[name="tipo_senalado"]:checked');
        var otroChk = document.getElementById('senalado-otro-chk');
        var otroTxt = document.getElementById('senalado-otro-txt').value.trim();
        if (tiposSenalados.length === 0 && !(otroChk.checked && otroTxt)) {
            errores.push('Tipo del señalado');
            document.getElementById('tipo-senalado-err').classList.add('visible');
        } else {
            var erroresDinamicos = validarBloquesSenaladoDinamicos();
            erroresDinamicos.forEach(function (nombreCampo) {
                errores.push('Datos del señalado: ' + nombreCampo);
            });
        }

        // Ubicación del señalado
        if (document.getElementById('sen-ubicacion').value.trim().length < 5) {
            errores.push('Ubicación del señalado');
            marcarError('sen-ubicacion', 'sen-ubicacion-err');
        }

        // Validar que la primera fila de la tabla tenga al menos cédula o nombre
        var primeraFila = document.querySelector('#tbody-senalados tr:first-child');
        if (primeraFila) {
            var cedula = primeraFila.cells[1].querySelector('input').value.trim();
            var nombre = primeraFila.cells[2].querySelector('input').value.trim();
            if (!cedula && !nombre) {
                errores.push('Datos del primer señalado (cédula o nombre)');
                document.getElementById('tabla-senalados-err').classList.add('visible');
            }
        }

        // Bloque de proyecto consulta popular (si aplica)
        if (document.getElementById('bloque-proyecto-consulta').style.display !== 'none') {
            if (document.getElementById('proy-nombre').value.trim().length < 4) {
                errores.push('Denominación del Proyecto');
                marcarError('proy-nombre', 'proy-nombre-err');
            }
            if (!document.getElementById('proy-fecha').value) {
                errores.push('Fecha de Aprobación del Proyecto');
                marcarError('proy-fecha', 'proy-fecha-err');
            }
            if (!/^\d+([.,]\d{1,2})?$/.test(document.getElementById('proy-monto').value.trim())) {
                errores.push('Monto del Proyecto');
                marcarError('proy-monto', 'proy-monto-err');
            }
            if (document.getElementById('proy-financiador').value.trim().length < 3) {
                errores.push('Ente Financiador');
                marcarError('proy-financiador', 'proy-financiador-err');
            }
        }
    }

    if (numeroPaso === 4) {
        // Narración (mínimo 50 caracteres)
        if (document.getElementById('narracion').value.trim().length < 50) {
            errores.push('Narración de los Hechos (mínimo 50 caracteres)');
            marcarError('narracion', 'narracion-err');
        }
        // ¿Presentada ante otra instancia?
        var otraInst = document.querySelector('input[name="otra_instancia"]:checked');
        if (!otraInst) {
            errores.push('Indicar si fue presentada ante otra instancia');
            document.getElementById('otra-inst-err').classList.add('visible');
        } else if (otraInst.value === 'si') {
            if (document.getElementById('cual-instancia').value.trim().length < 3) {
                errores.push('Nombre de la instancia anterior');
                marcarError('cual-instancia', 'cual-instancia-err');
            }
        }
    }

    // Si hay errores, mostrar el banner y hacer scroll al primer error
    if (errores.length > 0) {
        mostrarBannerError(numeroPaso, errores);
        var primerError = document.querySelector(
            '#paso-' + numeroPaso + ' .form-control.invalid, ' +
            '#paso-' + numeroPaso + ' .form-select.invalid, ' +
            '#paso-' + numeroPaso + ' .form-textarea.invalid, ' +
            '#paso-' + numeroPaso + ' .error-msg.visible'
        );
        if (primerError) primerError.scrollIntoView({ behavior: 'smooth', block: 'center' });
        return false;
    }

    return true;
}


/* ═══════════════════════════════════════════════════════════
   UTILIDADES DE ERRORES
   ═══════════════════════════════════════════════════════════ */

/**
 * Marca un campo como inválido y muestra su mensaje de error.
 * @param {string} campoId - ID del campo de entrada
 * @param {string} errId   - ID del span de error
 */
function marcarError(campoId, errId) {
    var campo = document.getElementById(campoId);
    var err = document.getElementById(errId);
    if (campo) campo.classList.add('invalid');
    if (err) err.classList.add('visible');
}


/**
 * Muestra el banner de errores de un paso con la lista de campos.
 * @param {number} numeroPaso
 * @param {string[]} errores - Lista de nombres de campos con error
 */
function mostrarBannerError(numeroPaso, errores) {
    var banner = document.getElementById('err-paso' + numeroPaso);
    var lista = document.getElementById('err-paso' + numeroPaso + '-lista');
    if (!banner) return;
    lista.innerHTML = errores.map(function (e) {
        return '<li>' + e + '</li>';
    }).join('');
    banner.style.display = 'flex';
}


/**
 * Limpia los errores visuales del paso indicado.
 * @param {number} numeroPaso
 */
function limpiarErroresPaso(numeroPaso) {
    var contenedor = document.getElementById('paso-' + numeroPaso);
    if (!contenedor) return;
    contenedor.querySelectorAll('.form-control, .form-select, .form-textarea').forEach(function (el) {
        el.classList.remove('invalid');
    });
    contenedor.querySelectorAll('.error-msg').forEach(function (el) {
        el.classList.remove('visible');
    });
    var banner = document.getElementById('err-paso' + numeroPaso);
    if (banner) banner.style.display = 'none';
}


/** Limpia todos los errores de todos los pasos */
function limpiarTodosLosErrores() {
    for (var i = 1; i <= TOTAL_PASOS; i++) {
        limpiarErroresPaso(i);
    }
}


/* ═══════════════════════════════════════════════════════════
   EDAD COMPUTADA A PARTIR DE LA FECHA DE NACIMIENTO
   ═══════════════════════════════════════════════════════════ */

/**
 * Calcula la edad en años completos a partir de una fecha (YYYY-MM-DD).
 * @param {string} fechaStr
 * @returns {number|null}
 */
function calcularEdad(fechaStr) {
    if (!fechaStr) return null;
    var nacimiento = new Date(fechaStr + 'T00:00:00');
    if (isNaN(nacimiento.getTime())) return null;
    var hoy = new Date();
    var edad = hoy.getFullYear() - nacimiento.getFullYear();
    var mesDiff = hoy.getMonth() - nacimiento.getMonth();
    if (mesDiff < 0 || (mesDiff === 0 && hoy.getDate() < nacimiento.getDate())) {
        edad--;
    }
    return edad;
}

/**
 * Actualiza el campo (de solo lectura) de edad cuando cambia la
 * fecha de nacimiento.
 * @param {HTMLInputElement} inputFecha
 */
function actualizarEdadComputada(inputFecha) {
    var campoEdad = document.getElementById('cit-edad');
    var edad = calcularEdad(inputFecha.value);
    if (edad === null || edad < 0) {
        campoEdad.value = '';
        return;
    }
    campoEdad.value = edad + ' años';
    inputFecha.classList.remove('invalid');
    document.getElementById('cit-fecha-nac-err').classList.remove('visible');
}


/* ═══════════════════════════════════════════════════════════
   LÓGICA CONDICIONAL DEL FORMULARIO
   ═══════════════════════════════════════════════════════════ */

/**
 * Reacciona al cambio del tipo de trámite (radio).
 * Muestra u oculta la pregunta de consulta popular.
 * @param {HTMLInputElement} radio
 */
function onTipoTramiteChange(radio) {
    var bloque = document.getElementById('bloque-consulta-popular');
    bloque.style.display = (radio.value === 'denuncia') ? 'block' : 'none';
    document.getElementById('tipo-tramite-err').classList.remove('visible');
    radio.closest('.tramite-card').parentElement.querySelectorAll('.tramite-card').forEach(function (c) {
        c.style.borderColor = '';
    });
}


/**
 * Muestra u oculta el campo "¿Ante cuál instancia?".
 * @param {string} valor - 'si' | 'no'
 */
function toggleOtraInstancia(valor) {
    var campoInstancia = document.getElementById('campo-cual-instancia');
    campoInstancia.style.display = (valor === 'si') ? 'block' : 'none';
    document.getElementById('otra-inst-err').classList.remove('visible');
}


/**
 * Habilita o deshabilita el campo de texto para "Otro" tipo de señalado.
 * @param {HTMLInputElement} checkbox
 */
function toggleSenaladoOtro(checkbox) {
    var campo = document.getElementById('senalado-otro-txt');
    campo.disabled = !checkbox.checked;
    campo.style.opacity = checkbox.checked ? '1' : '0.5';
    campo.style.cursor = checkbox.checked ? 'text' : 'not-allowed';
}


/* ═══════════════════════════════════════════════════════════
   FORMULARIOS DINÁMICOS SEGÚN TIPO DE SEÑALADO
   Al marcar/desmarcar un checkbox de "Tipo del Señalado" se
   agrega o elimina el bloque de campos correspondiente, para
   poder verificar la identidad de la persona o ente señalado.
   ═══════════════════════════════════════════════════════════ */

/**
 * Reacciona al marcar/desmarcar un checkbox de tipo de señalado.
 * Muestra u oculta el mini-formulario correspondiente a ese tipo.
 * @param {HTMLInputElement} checkbox
 */
function onTipoSenaladoChange(checkbox) {
    var contenedor = document.getElementById('detalle-senalados-dinamico');
    var tipo = checkbox.value;
    var idBloque = 'detalle-' + tipo;
    document.getElementById('tipo-senalado-err').classList.remove('visible');

    if (checkbox.checked) {
        // Evitar duplicados
        if (document.getElementById(idBloque)) return;
        var plantilla = PLANTILLAS_SENALADO[tipo];
        if (!plantilla) return;

        var bloque = document.createElement('div');
        bloque.className = 'form-section bloque-senalado-dinamico';
        bloque.id = idBloque;
        bloque.innerHTML =
            '<div class="form-section-title">' +
            '<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">' +
            '<path d="M9 12l2 2 4-4"/><circle cx="12" cy="12" r="10"/></svg>' +
            plantilla.titulo +
            '</div>' + plantilla.html;

        contenedor.appendChild(bloque);
    } else {
        var existente = document.getElementById(idBloque);
        if (existente) existente.remove();
    }
}

/**
 * Valida los campos requeridos de los bloques dinámicos de
 * señalado actualmente visibles (uno por cada tipo marcado).
 * @returns {string[]} lista de etiquetas de campos con error
 */
function validarBloquesSenaladoDinamicos() {
    var erroresCampos = [];
    document.querySelectorAll('#detalle-senalados-dinamico .campo-dinamico-req').forEach(function (campo) {
        var valor = (campo.value || '').trim();
        if (!valor) {
            campo.classList.add('invalid');
            erroresCampos.push(campo.previousElementSibling ? campo.closest('.form-group').querySelector('.form-label').textContent.replace('*', '').trim() : campo.dataset.campo);
        } else {
            campo.classList.remove('invalid');
        }
    });
    return erroresCampos;
}


/* ═══════════════════════════════════════════════════════════
   TABLA DE SEÑALADOS
   ═══════════════════════════════════════════════════════════ */

/** Agrega una nueva fila vacía a la tabla de señalados */
function agregarFilaSenalado() {
    var tbody = document.getElementById('tbody-senalados');
    var fila = document.createElement('tr');
    fila.innerHTML = [
        '<td><select class="celda-tipo-doc">' + construirOpcionesTipoDoc(TIPOS_DOCUMENTO_TODOS, true) + '</select></td>',
        '<td><input type="text" placeholder="00000000"',
        '     oninput="validarCeldaCedula(this)" onblur="validarCeldaCedula(this)"></td>',
        '<td><input type="text" placeholder="Apellido, Nombre"',
        '     oninput="validarCeldaNombre(this)" onblur="validarCeldaNombre(this)"></td>',
        '<td><input type="text" placeholder="Nombre de la instancia"></td>',
        '<td><input type="text" placeholder="Código SITUR"',
        '     oninput="validarCeldaSitur(this)" onblur="validarCeldaSitur(this)"></td>',
        '<td><input type="text" placeholder="J-XXXXXXXX"',
        '     oninput="validarCeldaRif(this)" onblur="validarCeldaRif(this)"></td>',
        '<td><button type="button" onclick="eliminarFilaSenalado(this)"',
        '     title="Eliminar fila" class="btn-tabla-eliminar">',
        '<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor"',
        '     stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">',
        '<line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>',
        '</button></td>'
    ].join('');
    tbody.appendChild(fila);
}


/**
 * Elimina una fila de la tabla de señalados (mínimo 1 fila).
 * @param {HTMLButtonElement} boton
 */
function eliminarFilaSenalado(boton) {
    var tbody = document.getElementById('tbody-senalados');
    if (tbody.rows.length > 1) {
        boton.closest('tr').remove();
    }
}


/* ─── Validaciones inline para las celdas de la tabla ─── */

function validarCeldaCedula(input) {
    var v = input.value.trim();
    if (!v) { input.classList.remove('valid', 'invalid'); return; }
    var ok = /^\d{5,10}$/.test(v);
    input.classList.toggle('valid', ok);
    input.classList.toggle('invalid', !ok);
}

function validarCeldaNombre(input) {
    var v = input.value.trim();
    if (!v) { input.classList.remove('valid', 'invalid'); return; }
    input.classList.toggle('valid', v.length >= 3);
    input.classList.toggle('invalid', v.length < 3);
}

function validarCeldaSitur(input) {
    var v = input.value.trim();
    if (!v) { input.classList.remove('valid', 'invalid'); return; }
    input.classList.toggle('valid', v.length >= 3);
    input.classList.toggle('invalid', v.length < 3);
}

function validarCeldaRif(input) {
    var v = input.value.trim();
    if (!v) { input.classList.remove('valid', 'invalid'); return; }
    var ok = /^[JGVECjgvec]-?\d{8,9}-?\d?$/.test(v);
    input.classList.toggle('valid', ok);
    input.classList.toggle('invalid', !ok);
}


/* ═══════════════════════════════════════════════════════════
   GESTIÓN DE ARCHIVOS
   ═══════════════════════════════════════════════════════════ */

/**
 * Valida y agrega chips de archivo a la lista de adjuntos.
 * @param {FileList} archivos
 */
function agregarArchivos(archivos) {
    var errEl = document.getElementById('archivo-err');
    var lista = document.getElementById('archivos-lista');
    var MAX_MB = 10 * 1024 * 1024; // 10 MB
    var grandes = [];

    Array.from(archivos).forEach(function (archivo) {
        if (archivo.size > MAX_MB) {
            grandes.push(archivo.name);
            return;
        }
        var chip = document.createElement('div');
        chip.className = 'file-chip';
        chip.innerHTML = [
            '<svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor"',
            '     stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">',
            '<path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>',
            '<polyline points="14 2 14 8 20 8"/></svg>',
            archivo.name,
            ' <span style="color:#8da4c2">(' + (archivo.size / 1024).toFixed(0) + ' KB)</span>',
            '<button type="button" onclick="this.parentElement.remove()" title="Quitar">',
            '<svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor"',
            '     stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">',
            '<line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>',
            '</button>'
        ].join('');
        lista.appendChild(chip);
    });

    if (grandes.length) {
        errEl.textContent = 'Archivo(s) demasiado grande(s) (máx. 10 MB): ' + grandes.join(', ');
        errEl.style.display = 'block';
    } else {
        errEl.style.display = 'none';
    }
}


/** Maneja el drop de archivos sobre la zona de carga */
function manejarDropArchivos(evento) {
    evento.preventDefault();
    evento.currentTarget.style.borderColor = '';
    if (evento.dataTransfer && evento.dataTransfer.files) {
        agregarArchivos(evento.dataTransfer.files);
    }
}


/* ═══════════════════════════════════════════════════════════
   CONTADOR DE CARACTERES — NARRACIÓN
   ═══════════════════════════════════════════════════════════ */

/**
 * Actualiza el contador de caracteres de la narración.
 * Aplica clases de advertencia al acercarse o superar el límite.
 * @param {HTMLTextAreaElement} textarea
 */
function actualizarContadorNarracion(textarea) {
    var n = textarea.value.length;
    var MAX = 3000;
    var contador = document.getElementById('narracion-contador');
    contador.textContent = n + ' / ' + MAX + ' caracteres';
    contador.className = 'contador-chars' + (n > MAX ? ' excedido' : n > MAX * 0.85 ? ' advertencia' : '');

    // Limpiar error si se alcanza el mínimo
    if (n >= 50) {
        textarea.classList.remove('invalid');
        document.getElementById('narracion-err').classList.remove('visible');
    }
}


/* ═══════════════════════════════════════════════════════════
   RESUMEN — PASO 5
   Puebla los campos de revisión con los datos ingresados
   ═══════════════════════════════════════════════════════════ */

/**
 * Recoge los datos de todos los pasos y los muestra
 * en la pantalla de revisión (Paso 5).
 */
function poblarResumen() {
    var tipoTramiteEl = document.querySelector('input[name="tipo_tramite"]:checked');
    var tipoTramiteVal = tipoTramiteEl
        ? tipoTramiteEl.value.charAt(0).toUpperCase() + tipoTramiteEl.value.slice(1)
        : '—';

    var labelTipo = tipoFormulario === 'poder-popular'
        ? 'Poder Popular'
        : 'General';

    document.getElementById('res-tipo-tramite').textContent =
        tipoTramiteVal + ' (' + labelTipo + ')';

    document.getElementById('res-fecha').textContent =
        new Date().toLocaleDateString('es-VE', { day: '2-digit', month: 'long', year: 'numeric' });

    document.getElementById('res-nombres').textContent =
        document.getElementById('cit-nombres').value || '—';

    document.getElementById('res-cedula').textContent =
        (document.getElementById('cit-tipo-doc').value || '') + ' ' +
        (document.getElementById('cit-nro-doc').value || '');

    document.getElementById('res-correo').textContent =
        document.getElementById('cit-correo').value || '—';

    var telfCod = document.getElementById('cit-telf-cel-cod').value;
    var telfNum = document.getElementById('cit-telf-cel-num').value;
    document.getElementById('res-telf').textContent =
        (telfCod && telfNum) ? (telfCod + '-' + telfNum) : '—';

    // Señalado: primera fila de la tabla
    var primeraFila = document.querySelector('#tbody-senalados tr:first-child');
    var resSenalado = '—';
    if (primeraFila) {
        var nom = primeraFila.cells[2].querySelector('input').value.trim();
        var ced = primeraFila.cells[1].querySelector('input').value.trim();
        resSenalado = nom || ced || '—';
    }
    document.getElementById('res-senalado').textContent = resSenalado;

    // Narración (primeros 200 caracteres con elipsis)
    var narracion = document.getElementById('narracion').value.trim();
    document.getElementById('res-hechos').textContent =
        narracion.length > 200 ? narracion.substring(0, 200) + '...' : narracion || '—';

    // Mostrar botón de PDF
    document.getElementById('btn-guardar-pdf').style.display = 'inline-flex';
}


/* ═══════════════════════════════════════════════════════════
   ENVÍO FINAL
   ═══════════════════════════════════════════════════════════ */

/**
 * Valida la declaración jurada y procesa el envío de la solicitud.
 * Genera el número de expediente y muestra la confirmación.
 */
function enviarSolicitud() {
    var acepta = document.getElementById('acepta-declaracion');
    var errDecl = document.getElementById('acepta-decl-err');

    if (!acepta.checked) {
        errDecl.classList.add('visible');
        acepta.scrollIntoView({ behavior: 'smooth', block: 'center' });
        return;
    }
    errDecl.classList.remove('visible');

    // Generar número de expediente único (formato: OAC-AÑO-XXXX)
    var anio = new Date().getFullYear();
    var correlativo = String(Math.floor(Math.random() * 9000) + 1000);
    var nroExpediente = 'OAC-' + anio + '-' + correlativo;

    // Mostrar en pantalla de confirmación
    document.getElementById('nro-expediente-display').textContent = nroExpediente;
    document.getElementById('conf-correo').textContent =
        document.getElementById('cit-correo').value || '—';
    document.getElementById('conf-fecha').textContent =
        new Date().toLocaleString('es-VE');

    // Ocultar wizard, mostrar confirmación
    document.getElementById('barra-progreso').style.display = 'none';
    for (var i = 1; i <= TOTAL_PASOS; i++) {
        var paso = document.getElementById('paso-' + i);
        if (paso) paso.style.display = 'none';
    }
    document.querySelector('.form-back-btn').style.display = 'none';
    document.getElementById('vista-confirmacion').style.display = 'block';

    document.getElementById('denuncias').scrollIntoView({ behavior: 'smooth', block: 'start' });
}


/* ═══════════════════════════════════════════════════════════
   PDF / IMPRESIÓN
   Usa window.print() con los estilos @media print del CSS
   ═══════════════════════════════════════════════════════════ */

/**
 * Abre el diálogo de impresión del navegador.
 * Los estilos @media print de denuncias1.css se encargan
 * del formato institucional del documento impreso.
 */
function generarPDF() {
    window.print();
}


/** Imprime el comprobante de confirmación */
function imprimirComprobante() {
    window.print();
}


/* ═══════════════════════════════════════════════════════════
   NUEVA SOLICITUD — RESETEAR EL FORMULARIO
   ═══════════════════════════════════════════════════════════ */

/**
 * Reinicia completamente el wizard para registrar una nueva solicitud.
 * Limpia todos los campos y vuelve a la vista de selección.
 */
function nuevaSolicitud() {
    // Limpiar todos los campos de texto, select y textarea
    document.querySelectorAll(
        '#vista-wizard input[type="text"], #vista-wizard input[type="email"],' +
        '#vista-wizard input[type="tel"], #vista-wizard input[type="number"],' +
        '#vista-wizard input[type="date"], #vista-wizard textarea'
    ).forEach(function (el) {
        el.value = '';
        el.classList.remove('valid', 'invalid');
    });

    document.querySelectorAll('#vista-wizard select').forEach(function (el) {
        el.selectedIndex = 0;
        el.classList.remove('valid', 'invalid');
    });

    document.querySelectorAll('#vista-wizard input[type="checkbox"], #vista-wizard input[type="radio"]').forEach(function (el) {
        el.checked = false;
    });

    // Resetear el campo de municipio a su valor por defecto
    var municipio = document.getElementById('cit-municipio');
    if (municipio) municipio.value = 'Páez';

    // Restaurar visibilidad de bloques condicionales
    document.getElementById('bloque-consulta-popular').style.display = 'none';
    document.getElementById('campo-cual-instancia').style.display = 'none';
    document.getElementById('senalado-otro-txt').disabled = true;
    document.getElementById('senalado-otro-txt').style.opacity = '0.5';
    document.getElementById('senalado-otro-txt').style.cursor = 'not-allowed';
    document.getElementById('bloque-proyecto-consulta').style.display = 'none';

    // Limpiar bloques dinámicos de señalado
    document.getElementById('detalle-senalados-dinamico').innerHTML = '';

    // Limpiar tabla de señalados (dejar solo la primera fila vacía)
    var tbody = document.getElementById('tbody-senalados');
    while (tbody.rows.length > 1) tbody.deleteRow(1);
    tbody.rows[0].querySelectorAll('input').forEach(function (inp) {
        inp.value = '';
        inp.classList.remove('valid', 'invalid');
    });

    // Limpiar lista de archivos
    document.getElementById('archivos-lista').innerHTML = '';
    document.getElementById('archivo-err').style.display = 'none';

    // Resetear contador de narración
    document.getElementById('narracion-contador').textContent = '0 / 3000 caracteres';
    document.getElementById('narracion-contador').className = 'contador-chars';

    // Mostrar de nuevo el botón volver
    document.querySelector('.form-back-btn').style.display = '';

    // Ocultar confirmación
    document.getElementById('vista-confirmacion').style.display = 'none';

    // Volver a la selección
    volverSeleccion();
}


/* ═══════════════════════════════════════════════════════════
   ACCESIBILIDAD — ACTIVAR CARDS CON TECLADO
   ═══════════════════════════════════════════════════════════ */
document.querySelectorAll('.tipo-card').forEach(function (tarjeta) {
    tarjeta.addEventListener('keydown', function (evento) {
        if (evento.key === 'Enter' || evento.key === ' ') {
            evento.preventDefault();
            tarjeta.click();
        }
    });
});


/* ═══════════════════════════════════════════════════════════
   SCROLL SUAVE PARA ANCLAS INTERNAS
   ═══════════════════════════════════════════════════════════ */
document.querySelectorAll('a[href^="#"]').forEach(function (enlace) {
    enlace.addEventListener('click', function (evento) {
        var destino = enlace.getAttribute('href');
        if (destino && destino !== '#' && document.querySelector(destino)) {
            evento.preventDefault();
            document.querySelector(destino).scrollIntoView({ behavior: 'smooth' });
        }
    });
});