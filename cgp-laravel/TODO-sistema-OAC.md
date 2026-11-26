# TODO: Sistema OAC (Contraloría del Municipio Páez)

> Hecho a partir de las notas de voz de Laura (21/11 y 25/11/2026) y de dos capturas de errores.
> No es una lista rígida: el orden es una sugerencia y cada punto lleva su contexto.
> Los nombres y detalles salen de una transcripción automática; si algo no cuadra, revísalo con Laura.

---

## Contexto general

- Laura cambió la **interfaz** (maqueta visual) del seguimiento de expedientes, pero **no la base de datos ni la lógica**. No quiso tocar los controladores para no romper nada, así que ahora la vista y el backend no coinciden.
- Contraloría le entregó a Carlos el **manual digital** de la Oficina de Atención al Ciudadano. Ahí los estados del trámite tienen otros nombres que los del sistema (hoy usa "en revisión", "procesado", etc.). La idea es **usar exactamente los nombres del manual**.
- Hay una rama `Programming` en GitHub que es **solo para la nota de Programación**. No va al proyecto real, así que no hay que mezclarla.
- **Fecha clave:** el lunes posiblemente se reúnen con Contraloría. Lo ideal es mostrar el flujo de estados funcionando.
- **Evaluación:** la profesora va a escoger a una persona del equipo para defender **todo** el sistema, y el informe tiene que decir exactamente lo que hace el sistema. Laura dijo: *"quienes defienden el sistema somos tú y yo"*.

### Estados del trámite según el manual

| Estado | Cuándo se usa |
|---|---|
| **En tramitación / valoración** | Se recibió y se revisa admisibilidad, requisitos y competencia |
| **En subsanación** | Faltan recaudos (Oficio Circular N° 01-00-499); el solicitante tiene **5 días hábiles** para completarlos |
| **Remitido / derivado** | Se pasa a una unidad interna o a otro órgano o ente competente (p. ej., cuando no es competencia fiscal de la Contraloría del Municipio Páez) |
| **Improcedente / desestimado y archivado** | No procede, o no se subsanó a tiempo: inadmisión y archivo |
| **Cerrado / archivado** | Auto de cierre **motivado**, con visto bueno de la **DAC** y aprobación del **Contralor/a Municipal** |

---

## 🔴 Urgente (antes del lunes)

- [x] **Cambiar los estados en la base de datos**
  - ✅ Lo hizo Bill (commit `6e612bd`, 22/11): migración `2026_11_22_000000_update_case_status_to_dac_workflow`. Ya corre en local.
  - ⚠️ Laura y Bill tienen que hacer `git pull` + `docker exec cgp-laravel-app php artisan migrate`.
  - Una migración que reemplace los estados viejos por los 5 del manual (enum, tabla catálogo o columna, según cómo esté hecho).
  - Convertir los expedientes que ya existen (p. ej., "en revisión" pasa a "en tramitación").
  - Actualizar el modelo y las constantes o enum de PHP si los hay.

- [x] **Arreglar "La acción seleccionada no es válida"** (Historial y Actuaciones, Registrar Actuación)
  - Pasa al guardar cualquier actuación con los estados nuevos.
  - Lo más probable es que el FormRequest o el `validate()` tenga una regla `in:` con los valores viejos, o que el `value` del `<select>` no coincida con lo que espera el backend.
  - Registrar una actuación = asignarle el estado al expediente, y también debe quedar en el historial (fecha, hora y funcionario).
  - ✅ Lo corrigió Bill; fallaba porque faltaban migraciones. Probado en vivo: Recibido → Tramitación → Subsanación → Cerrado.

- [x] **Arreglar "Consultar Estado de Trámite"** (vista pública)
  - Con `OAC-2026-00004` sale *"No se pudo conectar con el servidor"*.
  - ✅ Causa: faltaban las migraciones del 20 y 21/11 (columnas de ubicación y confirmación). Probado: responde 200, y 404 si el código no existe.
  - Seguramente el endpoint revienta (500) al mapear un estado que ya no existe, o cambió la ruta. Revisar en Network qué responde, y `storage/logs/laravel.log`.

- [x] **Probar el flujo completo** de principio a fin: crear denuncia → tramitación → subsanación/remisión → cierre → consulta pública.
  - ✅ Los 24 tests pasan (creación, derivación, consulta y cambios de estado). Arreglé los datos de prueba (commit `738c12d`, ya en GitHub).
  - Pendiente: hacer una vuelta a mano en el navegador antes del lunes.

---

## 🟠 Importante (lo nuevo que pidió Laura)

- [ ] **Mensaje al ciudadano para cada estado** (notificación sin costo)
  - Nada de SMS ni correo de pago: es un **mensaje dentro del sistema** que el ciudadano ve al consultar su trámite.
  - El responsable (posiblemente el Ing. Juan Romero) puede **personalizar** el texto de cada estado.
  - Propuesta: una tabla `mensajes_estado` (estado, texto) con una pantalla sencilla para editarla.

- [ ] **Valoración y Asignación: tabla de funcionarios**
  - Aquí se hace el análisis de admisibilidad y se asigna o reasigna el responsable (lo que pidió Franklin).
  - Datos: área o dirección, cargo, figura y funcionario. Laura sugiere una **tabla de funcionarios** en la base de datos en vez de texto libre.
  - Ver qué tan factible es y presentárselo a ellos.

- [ ] **Remisión y Notificación: guardar en la base de datos**
  - La remisión (idea de la profesora) registra a qué ente se envía: Contraloría del Municipio Páez, Unidad de Auditoría Interna, Contraloría General de la República u otro órgano competente.
  - Abajo va la **notificación formal al ciudadano**.
  - Hoy es solo interfaz; falta la tabla, la relación con el expediente y el controlador.

- [ ] **Revisar lo que ya cambió Laura** y confirmar que sigue funcionando: Google Maps en el formulario y evidencias con formato digitalizado.

---

## 🟡 Entender y revisar (no es código)

- [ ] Escuchar todas las notas del grupo **desde el principio** para tener el contexto.
- [ ] Pasar el **manual de la OAC (Word)** a Claude y hacerle preguntas concretas en vez de leerlo entero.
- [ ] Pedirle a **Carlos** acceso al link de **Gemini** con los audios de la información que recolectaron.
- [ ] **Entender tú mismo** el proceso de remisión o derivación al ente competente y compararlo con lo que hizo Laura.
  - Ella cree que está bien, pero no está segura de haberlo entendido como es.
  - Bill no lo ha revisado (o no ha dicho nada).
- [ ] Revisar el **Word de correcciones de Bill**:
  - 🌸 Rosado: ya hecho
  - 🔵 Azul: falta información de Contraloría
  - 🟡 Amarillo: Laura no lo entiende (ayudarla con esto)
  - 🟠 Naranja: evaluar cómo encaja con los estados nuevos

---

## 🔵 Para la defensa / evaluación

- [ ] Revisar que el **informe** describa exactamente lo que hace el sistema; quien lo lea lo va a comparar con el sistema.
- [ ] Prepararte para defender el sistema **completo** tú solo.
- [ ] (Opcional) Ayudar al equipo a entender el sistema; vienen evaluaciones individuales.

---

## Notas técnicas sueltas

- No mezclar nada con la rama `Programming`.
- Antes de migrar, **respaldar la base de datos** o probar primero con datos de prueba.
- Si los estados se repiten en varios lugares (vista, request, controlador, consulta pública), centralizarlos en un solo enum o constante para que no vuelva a pasar esto.
