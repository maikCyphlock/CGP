
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
    autorrellenarSiVacio('cit-edad', registro.edad);
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
 * Busca en el padrón de señalados (tabla relacionada) un registro
 * previo por cédula o RIF y, si existe, autocompleta el resto de
 * las celdas de esa fila de la tabla de señalados.
 * @param {HTMLInputElement} input - celda de cédula o de RIF que disparó la búsqueda
 */
function autocompletarSenalado(input) {
    var valor = input.value.trim();
    if (!valor) return;
    var fila = input.closest('tr');
    if (!fila) return;

    var padron = leerTabla(CLAVE_PADRON_SENALADOS);
    var registro = padron.find(function (r) {
        return r.cedula === valor || r.rif === valor;
    });
    if (!registro) return;

    var celdaCedula = fila.cells[0].querySelector('input');
    var celdaNombre = fila.cells[1].querySelector('input');
    var celdaInstancia = fila.cells[2].querySelector('input');
    var celdaSitur = fila.cells[3].querySelector('input');
    var celdaRif = fila.cells[4].querySelector('input');

    if (celdaCedula && !celdaCedula.value) celdaCedula.value = registro.cedula || '';
    if (celdaNombre && !celdaNombre.value) celdaNombre.value = registro.nombre || registro.razonSocial || '';
    if (celdaInstancia && !celdaInstancia.value) celdaInstancia.value = registro.instancia || '';
    if (celdaSitur && !celdaSitur.value) celdaSitur.value = registro.situr || '';
    if (celdaRif && !celdaRif.value) celdaRif.value = registro.rif || '';

    if (celdaNombre) validarCeldaNombre(celdaNombre);
    if (celdaSitur) validarCeldaSitur(celdaSitur);
    if (celdaCedula) validarCeldaCedula(celdaCedula);
    if (celdaRif) validarCeldaRif(celdaRif);
}

/**
 * Guarda cada fila con datos de la tabla de señalados en el padrón
 * local, para autocompletar denuncias futuras contra el mismo señalado.
 */
function guardarSenaladosEnPadron() {
    var padron = leerTabla(CLAVE_PADRON_SENALADOS);
    document.querySelectorAll('#tbody-senalados tr').forEach(function (fila) {
        var cedula = fila.cells[0].querySelector('input').value.trim();
        var nombre = fila.cells[1].querySelector('input').value.trim();
        var instancia = fila.cells[2].querySelector('input').value.trim();
        var situr = fila.cells[3].querySelector('input').value.trim();
        var rif = fila.cells[4].querySelector('input').value.trim();
        if (!cedula && !rif && !nombre) return;

        var registro = { cedula: cedula, nombre: nombre, instancia: instancia, situr: situr, rif: rif };
        var indice = padron.findIndex(function (r) {
            return (cedula && r.cedula === cedula) || (rif && r.rif === rif);
        });
        if (indice === -1) padron.push(registro);
        else padron[indice] = Object.assign({}, padron[indice], registro);

        registrarEnCatalogo(CLAVE_CATALOGO_INSTANCIAS, instancia);
    });

    // Datos de la Persona Jurídica Señalada (si el bloque está activo)
    var bloquePJ = document.getElementById('bloque-persona-juridica');
    if (bloquePJ && bloquePJ.style.display !== 'none') {
        var pjRif = document.getElementById('pj-rif').value.trim();
        if (pjRif) {
            var registroPJ = {
                rif: pjRif,
                razonSocial: document.getElementById('pj-razon-social').value.trim(),
                repNombre: document.getElementById('pj-rep-nombre').value.trim(),
                repCedula: document.getElementById('pj-rep-cedula').value.trim(),
                direccionFiscal: document.getElementById('pj-direccion-fiscal').value.trim()
            };
            var indicePJ = padron.findIndex(function (r) { return r.rif === pjRif; });
            if (indicePJ === -1) padron.push(registroPJ);
            else padron[indicePJ] = Object.assign({}, padron[indicePJ], registroPJ);
        }
    }

    guardarTabla(CLAVE_PADRON_SENALADOS, padron);
}

/**
 * Busca en el padrón de señalados un registro previo por R.I.F. y,
 * si existe, autocompleta los datos de la Persona Jurídica Señalada.
 */
function autocompletarPersonaJuridica() {
    var rif = document.getElementById('pj-rif').value.trim();
    if (!rif) return;

    var padron = leerTabla(CLAVE_PADRON_SENALADOS);
    var registro = padron.find(function (r) { return r.rif === rif; });
    if (!registro) return;

    autorrellenarSiVacio('pj-razon-social', registro.razonSocial || registro.nombre);
    autorrellenarSiVacio('pj-rep-nombre', registro.repNombre);
    autorrellenarSiVacio('pj-rep-cedula', registro.repCedula);
    autorrellenarSiVacio('pj-direccion-fiscal', registro.direccionFiscal);
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
        'revision-title-color', 'consulta-title-color', 'pj-title-color'
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
        // Edad
        var edad = parseInt(document.getElementById('cit-edad').value, 10);
        if (isNaN(edad) || edad < 1 || edad > 120) {
            errores.push('Edad');
            marcarError('cit-edad', 'cit-edad-err');
        }
        // Fecha de nacimiento
        if (!document.getElementById('cit-fecha-nac').value) {
            errores.push('Fecha de Nacimiento');
            marcarError('cit-fecha-nac', 'cit-fecha-nac-err');
        }
        // Correo
        var correo = document.getElementById('cit-correo').value.trim();
        if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(correo)) {
            errores.push('Correo Electrónico');
            marcarError('cit-correo', 'cit-correo-err');
        }
        // Confirmar correo
        var correo2 = document.getElementById('cit-correo2').value.trim();
        if (correo !== correo2) {
            errores.push('Confirmar Correo');
            marcarError('cit-correo2', 'cit-correo2-err');
        }
        // Teléfono celular
        var telf = document.getElementById('cit-telf-cel').value.trim();
        if (!/^(04\d{2}[-\s]?\d{7}|0\d{3}[-\s]?\d{7})$/.test(telf)) {
            errores.push('Teléfono Celular');
            marcarError('cit-telf-cel', 'cit-telf-cel-err');
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
        }

        // Ubicación del señalado
        if (document.getElementById('sen-ubicacion').value.trim().length < 5) {
            errores.push('Ubicación del señalado');
            marcarError('sen-ubicacion', 'sen-ubicacion-err');
        }

        // Validar que la primera fila de la tabla tenga al menos cédula o nombre
        // (no aplica si ya se identificó a la persona jurídica señalada arriba)
        var bloquePJVisible = document.getElementById('bloque-persona-juridica').style.display !== 'none'
            && document.getElementById('pj-razon-social').value.trim().length >= 3;
        var primeraFila = document.querySelector('#tbody-senalados tr:first-child');
        if (primeraFila && !bloquePJVisible) {
            var cedula = primeraFila.cells[0].querySelector('input').value.trim();
            var nombre = primeraFila.cells[1].querySelector('input').value.trim();
            var instancia = primeraFila.cells[2].querySelector('input').value.trim();
            if (!cedula && !nombre && !instancia) {
                errores.push('Datos del primer señalado (cédula, nombre o instancia)');
                document.getElementById('tabla-senalados-err').classList.add('visible');
            }
        }

        // Bloque de persona jurídica señalada (si aplica)
        if (document.getElementById('bloque-persona-juridica').style.display !== 'none') {
            var pjRif = document.getElementById('pj-rif').value.trim();
            if (!/^[JGVECjgvec]-?\d{8,9}-?\d?$/.test(pjRif)) {
                errores.push('R.I.F. de la persona jurídica señalada');
                marcarError('pj-rif', 'pj-rif-err');
            }
            if (document.getElementById('pj-razon-social').value.trim().length < 3) {
                errores.push('Razón Social / Denominación de la persona jurídica señalada');
                marcarError('pj-razon-social', 'pj-razon-social-err');
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


/**
 * Muestra u oculta el bloque de "Datos de la Persona Jurídica Señalada"
 * según el checkbox "Persona Jurídica" del Paso 3.
 */
function actualizarBloquePersonaJuridica() {
    var chk = document.querySelector('input[name="tipo_senalado"][value="persona_juridica"]');
    var bloque = document.getElementById('bloque-persona-juridica');
    bloque.style.display = chk.checked ? 'block' : 'none';
    document.getElementById('tipo-senalado-err').classList.remove('visible');
}


/** Valida el formato del R.I.F. de la persona jurídica señalada. */
function validarPjRif() {
    var campo = document.getElementById('pj-rif');
    var valor = campo.value.trim();
    var ok = /^[JGVECjgvec]-?\d{8,9}-?\d?$/.test(valor);
    campo.classList.toggle('invalid', valor.length > 0 && !ok);
    if (ok || !valor) document.getElementById('pj-rif-err').classList.remove('visible');
}


/* ═══════════════════════════════════════════════════════════
   TABLA DE SEÑALADOS
   ═══════════════════════════════════════════════════════════ */

/** Agrega una nueva fila vacía a la tabla de señalados */
function agregarFilaSenalado() {
    var tbody = document.getElementById('tbody-senalados');
    var fila = document.createElement('tr');
    fila.innerHTML = [
        '<td><input type="text" placeholder="V-00000000"',
        '     oninput="validarCeldaCedula(this)" onblur="validarCeldaCedula(this);autocompletarSenalado(this)"></td>',
        '<td><input type="text" placeholder="Apellido, Nombre"',
        '     oninput="validarCeldaNombre(this)" onblur="validarCeldaNombre(this)"></td>',
        '<td><input type="text" placeholder="Nombre de la instancia" list="dl-instancias"></td>',
        '<td><input type="text" placeholder="Código SITUR"',
        '     oninput="validarCeldaSitur(this)" onblur="validarCeldaSitur(this)"></td>',
        '<td><input type="text" placeholder="J-XXXXXXXX"',
        '     oninput="validarCeldaRif(this)" onblur="validarCeldaRif(this);autocompletarSenalado(this)"></td>',
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
    var ok = /^\d{5,10}$/.test(v) || /^[VEve]-?\d{5,10}$/.test(v);
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

    document.getElementById('res-telf').textContent =
        document.getElementById('cit-telf-cel').value || '—';

    // Señalado: Razón Social (si es persona jurídica) o primera fila de la tabla
    var resSenalado = '—';
    var bloquePJ = document.getElementById('bloque-persona-juridica');
    if (bloquePJ.style.display !== 'none') {
        var razonSocial = document.getElementById('pj-razon-social').value.trim();
        var pjRifVal = document.getElementById('pj-rif').value.trim();
        resSenalado = razonSocial || pjRifVal || '—';
    }
    var primeraFila = document.querySelector('#tbody-senalados tr:first-child');
    if (resSenalado === '—' && primeraFila) {
        var ced = primeraFila.cells[0].querySelector('input').value.trim();
        var nom = primeraFila.cells[1].querySelector('input').value.trim();
        var instancia = primeraFila.cells[2].querySelector('input').value.trim();
        var rif = primeraFila.cells[4].querySelector('input').value.trim();
        resSenalado = nom || instancia || ced || rif || '—';
    }
    // Si la tabla no aporta un identificador, usar la ubicación del señalado como respaldo
    if (resSenalado === '—') {
        var ubicacion = document.getElementById('sen-ubicacion').value.trim();
        if (ubicacion) resSenalado = ubicacion;
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

    // Actualizar las tablas relacionadas (padrón de ciudadanos y señalados)
    // para que futuras solicitudes con los mismos datos se autocompleten.
    guardarCiudadanoEnPadron();
    guardarSenaladosEnPadron();
    registrarEnCatalogo(CLAVE_CATALOGO_FINANCIADORES, document.getElementById('proy-financiador').value);
    inicializarDatalists();

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
    document.getElementById('bloque-persona-juridica').style.display = 'none';

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

    // Ocultar mensaje de autorrelleno
    document.getElementById('cit-autofill-msg').style.display = 'none';

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


/* ═══════════════════════════════════════════════════════════
   INICIALIZACIÓN DE TABLAS RELACIONADAS
   ═══════════════════════════════════════════════════════════ */
inicializarDatalists();
