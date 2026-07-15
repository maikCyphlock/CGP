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
   TABLAS RELACIONADAS (AUTORRELLENO)
   Mientras no exista una base de datos real, se usa localStorage
   como almacén local de "tablas relacionadas": un padrón de
   ciudadanos y de señalados ya registrados, y catálogos de
   valores repetidos (parroquias, ciudades, instancias, entes
   financiadores). Cuando el usuario reingresa un documento o
   una cédula/RIF ya usados, el resto de los campos relacionados
   se autocompletan.
   ═══════════════════════════════════════════════════════════ */
var CLAVE_PADRON_CIUDADANOS = 'cgp_padron_ciudadanos';
var CLAVE_PADRON_SENALADOS = 'cgp_padron_senalados';
var CLAVE_CATALOGO_PARROQUIAS = 'cgp_catalogo_parroquias';
var CLAVE_CATALOGO_CIUDADES = 'cgp_catalogo_ciudades';
var CLAVE_CATALOGO_INSTANCIAS = 'cgp_catalogo_instancias';
var CLAVE_CATALOGO_FINANCIADORES = 'cgp_catalogo_financiadores';

function leerTabla(clave) {
    try {
        var datos = JSON.parse(localStorage.getItem(clave));
        return Array.isArray(datos) ? datos : [];
    } catch (e) {
        return [];
    }
}

function guardarTabla(clave, valores) {
    try {
        localStorage.setItem(clave, JSON.stringify(valores));
    } catch (e) {
        // localStorage no disponible (modo privado, cuota excedida, etc.); se ignora silenciosamente.
    }
}

/**
 * Agrega un valor único (no vacío) a un catálogo relacionado.
 * @param {string} clave
 * @param {string} valor
 */
function registrarEnCatalogo(clave, valor) {
    valor = (valor || '').trim();
    if (!valor) return;
    var tabla = leerTabla(clave);
    if (tabla.indexOf(valor) === -1) {
        tabla.push(valor);
        guardarTabla(clave, tabla);
    }
}

/**
 * Puebla un <datalist> con los valores de un catálogo relacionado.
 * @param {string} idDatalist
 * @param {string} clave
 */
function poblarDatalist(idDatalist, clave) {
    var datalist = document.getElementById(idDatalist);
    if (!datalist) return;
    datalist.innerHTML = leerTabla(clave).map(function (valor) {
        return '<option value="' + valor.replace(/"/g, '&quot;') + '"></option>';
    }).join('');
}

/** Refresca todas las listas de autorrelleno con lo guardado en el navegador. */
function inicializarDatalists() {
    poblarDatalist('dl-parroquias', CLAVE_CATALOGO_PARROQUIAS);
    poblarDatalist('dl-ciudades', CLAVE_CATALOGO_CIUDADES);
    poblarDatalist('dl-instancias', CLAVE_CATALOGO_INSTANCIAS);
    poblarDatalist('dl-financiadores', CLAVE_CATALOGO_FINANCIADORES);
}

/**
 * Muestra un mensaje breve indicando que los datos fueron autocompletados.
 * @param {string} texto
 */
function mostrarMensajeAutorrelleno(texto) {
    var msg = document.getElementById('cit-autofill-msg');
    if (!msg) return;
    msg.textContent = texto;
    msg.style.display = 'inline';
    clearTimeout(mostrarMensajeAutorrelleno._t);
    mostrarMensajeAutorrelleno._t = setTimeout(function () {
        msg.style.display = 'none';
    }, 6000);
}

/**
 * Asigna un valor a un campo solo si está vacío, para no sobrescribir
 * lo que el usuario ya haya escrito manualmente.
 * @param {string} id
 * @param {string} valor
 */
function autorrellenarSiVacio(id, valor) {
    var campo = document.getElementById(id);
    if (campo && !campo.value && valor) campo.value = valor;
}

/**
 * Calcula la Edad (campo de solo lectura) a partir de la Fecha de
 * Nacimiento seleccionada.
 */
function calcularEdad() {
    var fechaInput = document.getElementById('cit-fecha-nac');
    var edadInput = document.getElementById('cit-edad');
    var valor = fechaInput.value;
    if (!valor) { edadInput.value = ''; return; }

    var nacimiento = new Date(valor + 'T00:00:00');
    var hoy = new Date();
    var edad = hoy.getFullYear() - nacimiento.getFullYear();
    var mesDiff = hoy.getMonth() - nacimiento.getMonth();
    if (mesDiff < 0 || (mesDiff === 0 && hoy.getDate() < nacimiento.getDate())) edad--;

    edadInput.value = (edad >= 0 && edad <= 120) ? edad : '';
    edadInput.classList.remove('invalid');
    document.getElementById('cit-edad-err').classList.remove('visible');
}

/**
 * Busca en el padrón de ciudadanos (tabla relacionada) un registro
 * previo con el mismo Tipo + Número de Documento y, si existe,
 * autocompleta el resto de los campos del Paso 2.
 */
function autocompletarCiudadano() {
    var tipoDoc = document.getElementById('cit-tipo-doc').value;
    var nroDoc = document.getElementById('cit-nro-doc').value.trim();
    if (!tipoDoc || !nroDoc) return;

    var padron = leerTabla(CLAVE_PADRON_CIUDADANOS);
    var registro = padron.find(function (r) {
        return r.tipoDoc === tipoDoc && r.nroDoc === nroDoc;
    });
    if (!registro) return;

    autorrellenarSiVacio('cit-nombres', registro.nombres);
    autorrellenarSiVacio('cit-profesion', registro.profesion);
    autorrellenarSiVacio('cit-correo', registro.correo);
    autorrellenarSiVacio('cit-correo2', registro.correo);
    autorrellenarSiVacio('cit-telf-cel', registro.telfCel);
    autorrellenarSiVacio('cit-telf-hab', registro.telfHab);
    autorrellenarSiVacio('cit-parroquia', registro.parroquia);
    autorrellenarSiVacio('cit-ciudad', registro.ciudad);
    autorrellenarSiVacio('cit-direccion', registro.direccion);

    var selSexo = document.getElementById('cit-sexo');
    if (selSexo && !selSexo.value && registro.sexo) selSexo.value = registro.sexo;
    var inputFecha = document.getElementById('cit-fecha-nac');
    if (inputFecha && !inputFecha.value && registro.fechaNac) inputFecha.value = registro.fechaNac;
    calcularEdad();
    var selEcivil = document.getElementById('cit-ecivil');
    if (selEcivil && !selEcivil.value && registro.ecivil) selEcivil.value = registro.ecivil;
    var selEdu = document.getElementById('cit-edu');
    if (selEdu && !selEdu.value && registro.edu) selEdu.value = registro.edu;

    mostrarMensajeAutorrelleno('Datos encontrados de una solicitud anterior — se autocompletaron los campos vacíos.');
}

/**
 * Guarda (o actualiza) el ciudadano del Paso 2 en el padrón local,
 * para que futuras denuncias con el mismo documento se autocompleten.
 */
function guardarCiudadanoEnPadron() {
    var tipoDoc = document.getElementById('cit-tipo-doc').value;
    var nroDoc = document.getElementById('cit-nro-doc').value.trim();
    if (!tipoDoc || !nroDoc) return;

    var registro = {
        tipoDoc: tipoDoc,
        nroDoc: nroDoc,
        nombres: document.getElementById('cit-nombres').value.trim(),
        sexo: document.getElementById('cit-sexo').value,
        edad: document.getElementById('cit-edad').value,
        fechaNac: document.getElementById('cit-fecha-nac').value,
        ecivil: document.getElementById('cit-ecivil').value,
        edu: document.getElementById('cit-edu').value,
        profesion: document.getElementById('cit-profesion').value.trim(),
        correo: document.getElementById('cit-correo').value.trim(),
        telfCel: document.getElementById('cit-telf-cel').value.trim(),
        telfHab: document.getElementById('cit-telf-hab').value.trim(),
        parroquia: document.getElementById('cit-parroquia').value.trim(),
        ciudad: document.getElementById('cit-ciudad').value.trim(),
        direccion: document.getElementById('cit-direccion').value.trim()
    };

    var padron = leerTabla(CLAVE_PADRON_CIUDADANOS);
    var indice = padron.findIndex(function (r) {
        return r.tipoDoc === tipoDoc && r.nroDoc === nroDoc;
    });
    if (indice === -1) padron.push(registro);
    else padron[indice] = registro;
    guardarTabla(CLAVE_PADRON_CIUDADANOS, padron);

    registrarEnCatalogo(CLAVE_CATALOGO_PARROQUIAS, registro.parroquia);
    registrarEnCatalogo(CLAVE_CATALOGO_CIUDADES, registro.ciudad);
}

/**
 * Lee el valor (trim) de un campo dinámico dentro de una tarjeta de
 * señalado, identificado por su atributo data-campo.
 * @param {HTMLElement} tarjeta - elemento .senalado-card
 * @param {string} nombreCampo - valor de data-campo a buscar
 * @returns {string}
 */
function valorCampoSenalado(tarjeta, nombreCampo) {
    var campo = tarjeta.querySelector('[data-campo="' + nombreCampo + '"]');
    return campo ? campo.value.trim() : '';
}

/**
 * Autocompleta, dentro de una tarjeta de señalado de tipo Persona
 * Jurídica, el resto de los campos si el R.I.F. ya existe en el
 * padrón local de señalados.
 * @param {HTMLInputElement} campoRif - input pj-rif que disparó la búsqueda
 */
function autocompletarPersonaJuridica(campoRif) {
    var tarjeta = campoRif.closest('.senalado-card');
    if (!tarjeta) return;
    var rif = campoRif.value.trim();
    if (!rif) return;

    var padron = leerTabla(CLAVE_PADRON_SENALADOS);
    var registro = padron.find(function (r) { return r.rif === rif; });
    if (!registro) return;

    var campoRazon = tarjeta.querySelector('[data-campo="pj-razon"]');
    var campoRepresentante = tarjeta.querySelector('[data-campo="pj-representante"]');
    var campoDireccion = tarjeta.querySelector('[data-campo="pj-direccion"]');
    if (campoRazon && !campoRazon.value) campoRazon.value = registro.razonSocial || registro.nombre || '';
    if (campoRepresentante && !campoRepresentante.value) campoRepresentante.value = registro.representante || '';
    if (campoDireccion && !campoDireccion.value) campoDireccion.value = registro.direccionFiscal || '';
}

/**
 * Guarda los datos de cada tarjeta de señalado en el padrón local,
 * para autocompletar denuncias futuras contra el mismo señalado.
 * Por ahora solo se indexa por R.I.F. (tipo Persona Jurídica); los
 * nombres de instancia se registran en el catálogo de instancias.
 */
function guardarSenaladosEnPadron() {
    var padron = leerTabla(CLAVE_PADRON_SENALADOS);

    document.querySelectorAll('#lista-senalados .senalado-card').forEach(function (tarjeta) {
        var tipo = valorCampoSenalado(tarjeta, 'tipo-senalado');

        if (tipo === 'persona_juridica') {
            var rif = valorCampoSenalado(tarjeta, 'pj-rif');
            if (rif) {
                var registroPJ = {
                    rif: rif,
                    razonSocial: valorCampoSenalado(tarjeta, 'pj-razon'),
                    representante: valorCampoSenalado(tarjeta, 'pj-representante'),
                    direccionFiscal: valorCampoSenalado(tarjeta, 'pj-direccion')
                };
                var indicePJ = padron.findIndex(function (r) { return r.rif === rif; });
                if (indicePJ === -1) padron.push(registroPJ);
                else padron[indicePJ] = Object.assign({}, padron[indicePJ], registroPJ);
            }
        }

        var nombreInstancia = valorCampoSenalado(tarjeta, 'cm-nombre') ||
            valorCampoSenalado(tarjeta, 'cc-nombre') ||
            valorCampoSenalado(tarjeta, 'oe-nombre');
        if (nombreInstancia) registrarEnCatalogo(CLAVE_CATALOGO_INSTANCIAS, nombreInstancia);
    });

    guardarTabla(CLAVE_PADRON_SENALADOS, padron);
}



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
            '    <input type="text" class="form-control campo-dinamico-req" data-campo="pj-rif" placeholder="J-XXXXXXXX-X"' +
            '     onblur="autocompletarPersonaJuridica(this)"></div></div>' +
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
            '    <input type="text" class="form-control campo-dinamico-req" data-campo="oe-nombre" list="dl-instancias" placeholder="Ej. Alcaldía del Municipio Páez"></div></div>' +
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
            '    <label class="form-label">Código SITUR / CITUR</label>' +
            '    <input type="text" class="form-control mayusculas" data-campo="cm-situr" placeholder="CÓDIGO SITUR" oninput="this.value=this.value.toUpperCase()"></div></div>' +
            '  <div class="col-md-6"><div class="form-group">' +
            '    <label class="form-label">Nombre de la Comuna <span class="required">*</span></label>' +
            '    <input type="text" class="form-control campo-dinamico-req" data-campo="cm-nombre" list="dl-instancias" placeholder="Nombre oficial de la comuna"></div></div>' +
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
            '    <input type="text" class="form-control campo-dinamico-req" data-campo="cc-nombre" list="dl-instancias" placeholder="Nombre oficial del consejo comunal"></div></div>' +
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
    },
    otro: {
        titulo: 'Datos del Señalado (Otro)',
        html:
            '<div class="row g-3">' +
            '  <div class="col-12"><div class="form-group">' +
            '    <label class="form-label">Especifique el Tipo</label>' +
            '    <input type="text" class="form-control" data-campo="ot-tipo" placeholder="Ej. Cooperativa, Sindicato, Fundación..."></div></div>' +
            '  <div class="col-12"><div class="form-group">' +
            '    <label class="form-label">Identificación (Nombre, Cédula o R.I.F.) <span class="required">*</span></label>' +
            '    <input type="text" class="form-control campo-dinamico-req" data-campo="ot-identificacion" placeholder="Nombre completo o razón social, con cédula o R.I.F. si aplica"></div></div>' +
            '</div>'
    }
};

/** Opciones del selector de tipo de señalado, en el orden en que se muestran. */
var TIPOS_SENALADO_OPCIONES = [
    { valor: 'persona_natural', etiqueta: 'Persona Natural' },
    { valor: 'persona_juridica', etiqueta: 'Persona Jurídica' },
    { valor: 'organo_ente', etiqueta: 'Órgano o Ente' },
    { valor: 'comuna', etiqueta: 'Comuna' },
    { valor: 'consejo_comunal', etiqueta: 'Consejo Comunal' },
    { valor: 'juez_paz', etiqueta: 'Juez de Paz' },
    { valor: 'otro', etiqueta: 'Otro' }
];


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

    // Iniciar la lista de señalados con una tarjeta vacía
    if (!document.getElementById('lista-senalados').children.length) {
        agregarSenalado();
    }

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
        'narracion-title-color', 'evidencias-title-color',
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
        // Primer Nombre
        if (document.getElementById('cit-primer-nombre').value.trim().length < 2) {
            errores.push('Primer Nombre');
            marcarError('cit-primer-nombre', 'cit-primer-nombre-err');
        }
        // Primer Apellido
        if (document.getElementById('cit-primer-apellido').value.trim().length < 2) {
            errores.push('Primer Apellido');
            marcarError('cit-primer-apellido', 'cit-primer-apellido-err');
        }
        // Segundo Nombre y Segundo Apellido son opcionales (sin validación)
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
        // Al menos un señalado agregado, con tipo elegido y datos completos
        var tarjetasSenalados = document.querySelectorAll('#lista-senalados .senalado-card');
        if (tarjetasSenalados.length === 0) {
            errores.push('Tipo del señalado');
            document.getElementById('senalados-err').classList.add('visible');
        } else {
            var erroresDinamicos = validarBloquesSenaladoDinamicos();
            erroresDinamicos.forEach(function (nombreCampo) {
                errores.push('Datos del señalado: ' + nombreCampo);
            });
            if (erroresDinamicos.length) {
                document.getElementById('senalados-err').classList.add('visible');
            }
        }

        // Ubicación del señalado
        if (document.getElementById('sen-ubicacion').value.trim().length < 5) {
            errores.push('Ubicación del señalado');
            marcarError('sen-ubicacion', 'sen-ubicacion-err');
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


/**
 * Compone el nombre completo del denunciante a partir de los
 * cuatro campos separados (primer/segundo nombre y apellido).
 * @returns {string}
 */
function obtenerNombreCompletoCiudadano() {
    var partes = [
        document.getElementById('cit-primer-nombre').value.trim(),
        document.getElementById('cit-segundo-nombre').value.trim(),
        document.getElementById('cit-primer-apellido').value.trim(),
        document.getElementById('cit-segundo-apellido').value.trim()
    ].filter(function (p) { return p.length > 0; });
    return partes.join(' ');
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


/* ═══════════════════════════════════════════════════════════
   LISTA DE SEÑALADOS
   Cada señalado es una tarjeta independiente con su propio
   selector de tipo; al elegir el tipo se renderiza el
   mini-formulario correspondiente (PLANTILLAS_SENALADO). Permite
   agregar tantos señalados como sea necesario.
   ═══════════════════════════════════════════════════════════ */

/** Contador incremental para generar ids únicos de tarjeta de señalado. */
var contadorSenalados = 0;

/** Construye las <option> del selector de tipo de señalado. */
function construirOpcionesTipoSenalado() {
    return TIPOS_SENALADO_OPCIONES.map(function (t) {
        return '<option value="' + t.valor + '">' + t.etiqueta + '</option>';
    }).join('');
}

/** Agrega una nueva tarjeta de señalado vacía a la lista. */
function agregarSenalado() {
    poblarDatalist('dl-instancias', CLAVE_CATALOGO_INSTANCIAS);

    contadorSenalados++;
    var idTarjeta = contadorSenalados;
    var lista = document.getElementById('lista-senalados');

    var tarjeta = document.createElement('div');
    tarjeta.className = 'senalado-card';
    tarjeta.id = 'senalado-card-' + idTarjeta;
    tarjeta.innerHTML =
        '<div class="senalado-card-header">' +
        '  <span class="senalado-card-numero">Señalado</span>' +
        '  <button type="button" onclick="eliminarSenalado(' + idTarjeta + ')" title="Eliminar señalado" class="btn-tabla-eliminar">' +
        '    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">' +
        '      <line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/>' +
        '    </svg>' +
        '  </button>' +
        '</div>' +
        '<div class="form-group" style="margin-bottom:0;">' +
        '  <label class="form-label">Tipo de Señalado <span class="required">*</span></label>' +
        '  <select class="form-select campo-dinamico-req" data-campo="tipo-senalado" onchange="onTipoSenaladoCardChange(this)">' +
        '    <option value="">Seleccionar</option>' +
        construirOpcionesTipoSenalado() +
        '  </select>' +
        '</div>' +
        '<div class="senalado-card-campos"></div>';

    lista.appendChild(tarjeta);
}

/**
 * Elimina una tarjeta de señalado (se exige mínimo una en la lista).
 * @param {number} idTarjeta
 */
function eliminarSenalado(idTarjeta) {
    var lista = document.getElementById('lista-senalados');
    if (lista.children.length <= 1) return;
    var tarjeta = document.getElementById('senalado-card-' + idTarjeta);
    if (tarjeta) tarjeta.remove();
}

/**
 * Renderiza, dentro de la tarjeta correspondiente, el mini-formulario
 * de PLANTILLAS_SENALADO asociado al tipo elegido.
 * @param {HTMLSelectElement} select - selector data-campo="tipo-senalado"
 */
function onTipoSenaladoCardChange(select) {
    var tarjeta = select.closest('.senalado-card');
    var contenedorCampos = tarjeta.querySelector('.senalado-card-campos');
    var plantilla = PLANTILLAS_SENALADO[select.value];
    contenedorCampos.innerHTML = plantilla ? plantilla.html : '';
}

/**
 * Valida los campos requeridos (.campo-dinamico-req) de todas las
 * tarjetas de señalado actualmente en #lista-senalados.
 * @returns {string[]} nombres de los campos inválidos o vacíos
 */
function validarBloquesSenaladoDinamicos() {
    var errores = [];
    document.querySelectorAll('#lista-senalados .campo-dinamico-req').forEach(function (campo) {
        var valor = (campo.value || '').trim();
        var nombreCampo = campo.dataset.campo || '';
        var valido;
        if (nombreCampo.slice(-4) === '-rif') {
            valido = /^[JGVECjgvec]-?\d{8,9}-?\d?$/.test(valor);
        } else if (nombreCampo.slice(-8) === '-nro-doc') {
            valido = /^\d{5,10}$/.test(valor);
        } else if (campo.tagName === 'SELECT') {
            valido = valor !== '';
        } else {
            valido = valor.length >= 3;
        }

        if (campo.tagName !== 'SELECT') campo.classList.toggle('valid', valido);
        campo.classList.toggle('invalid', !valido);

        if (!valido) {
            var grupo = campo.closest('.form-group');
            var etiqueta = grupo ? grupo.querySelector('.form-label') : null;
            errores.push(etiqueta ? etiqueta.textContent.replace('*', '').trim() : nombreCampo);
        }
    });
    return errores;
}

/**
 * Obtiene una etiqueta legible (nombre, razón social o identificación)
 * de la primera tarjeta de señalado, para mostrar en el resumen final.
 * @returns {string}
 */
function obtenerEtiquetaPrimerSenalado() {
    var tarjeta = document.querySelector('#lista-senalados .senalado-card');
    if (!tarjeta) return '—';

    var camposNombre = tarjeta.querySelectorAll(
        '[data-campo$="-nombres"], [data-campo$="-nombre"], [data-campo="pj-razon"], [data-campo="ot-identificacion"]'
    );
    for (var i = 0; i < camposNombre.length; i++) {
        var valor = camposNombre[i].value.trim();
        if (valor) return valor;
    }

    var camposIdent = tarjeta.querySelectorAll('[data-campo$="-nro-doc"], [data-campo$="-rif"]');
    for (var j = 0; j < camposIdent.length; j++) {
        var identificacion = camposIdent[j].value.trim();
        if (identificacion) return identificacion;
    }

    return '—';
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
        obtenerNombreCompletoCiudadano() || '—';

    document.getElementById('res-cedula').textContent =
        (document.getElementById('cit-tipo-doc').value || '') + ' ' +
        (document.getElementById('cit-nro-doc').value || '');

    document.getElementById('res-correo').textContent =
        document.getElementById('cit-correo').value || '—';

    var telfCod = document.getElementById('cit-telf-cel-cod').value;
    var telfNum = document.getElementById('cit-telf-cel-num').value;
    document.getElementById('res-telf').textContent =
        (telfCod && telfNum) ? (telfCod + '-' + telfNum) : '—';

    // Señalado: identificación principal de la primera tarjeta de la lista
    document.getElementById('res-senalado').textContent = obtenerEtiquetaPrimerSenalado();

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

    // Guardar los señalados en el padrón local, para autocompletar
    // denuncias futuras contra el mismo señalado (por R.I.F.).
    guardarSenaladosEnPadron();

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
    document.getElementById('bloque-proyecto-consulta').style.display = 'none';

    // Limpiar lista de señalados y dejar una tarjeta vacía
    document.getElementById('lista-senalados').innerHTML = '';
    contadorSenalados = 0;
    agregarSenalado();

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