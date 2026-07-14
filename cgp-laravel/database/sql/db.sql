-- =============================================================================
-- OAC CONTRALORÍA MUNICIPIO PÁEZ — Schema v2
-- PostgreSQL 15+
--
-- FILOSOFÍA DE DISEÑO
-- ─────────────────────────────────────────────────────────────────────────────
-- Lo que NUNCA cambia          → columnas normalizadas + constraints en DB
-- Lo que cambia OCASIONALMENTE → columna JSONB con validación en app
-- Lo que el admin CONFIGURA    → tabla de catálogo editable (cat_*)
--
-- Separación concreta:
--   citizen          → núcleo fijo (doc, nombre, sexo) + contact_data JSONB
--   respondent_type  → catálogo configurable con field_schema JSONB
--   respondent       → columnas fijas mínimas + attributes JSONB
--   case_action      → columnas normalizadas (actor, tipo, fecha) + payload JSONB
-- =============================================================================

CREATE EXTENSION IF NOT EXISTS "pgcrypto";
CREATE EXTENSION IF NOT EXISTS "unaccent";


-- =============================================================================
-- BLOQUE 1 — CATÁLOGOS
-- Editables por el administrador. Constraints fuertes porque son referenciados
-- por FK y su corrupción rompe integridad referencial.
-- =============================================================================

-- 1.1 Tipos de trámite  (Denuncia, Queja, Reclamo, Petición, Sugerencia)
CREATE TABLE claim_type (
    id                  SMALLSERIAL  PRIMARY KEY,
    code                VARCHAR(30)  NOT NULL UNIQUE,       -- 'COMPLAINT','GRIEVANCE'…
    name                VARCHAR(80)  NOT NULL,
    validation_level    VARCHAR(20)  NOT NULL
                            CHECK (validation_level IN ('STRICT','BASIC','AUTOMATED')),
    active              BOOLEAN      NOT NULL DEFAULT TRUE,
    created_at          TIMESTAMPTZ  NOT NULL DEFAULT now()
);
COMMENT ON TABLE claim_type IS
    'RQF-02.1 / RQF-12 — Catálogo de tipos de trámite.
     Tipos inactivos no aparecen en el formulario pero se preservan en expedientes históricos.';


-- 1.2 Tipos de irregularidad / falta (catálogo legal LOCGRSNCF — pendiente de desglose)
CREATE TABLE irregularity_type (
    id          SMALLSERIAL  PRIMARY KEY,
    code        VARCHAR(40)  NOT NULL UNIQUE,
    name        VARCHAR(120) NOT NULL,
    legal_basis TEXT,                                       -- artículo de ley aplicable
    active      BOOLEAN      NOT NULL DEFAULT TRUE,
    created_at  TIMESTAMPTZ  NOT NULL DEFAULT now()
);
COMMENT ON TABLE irregularity_type IS
    'RQF-06.1 / RQF-12.2 — Tipificación según LOCGRSNCF.';


-- 1.3 Estados del expediente  (orden importa para la línea de tiempo)
CREATE TABLE case_status (
    id          SMALLSERIAL  PRIMARY KEY,
    code        VARCHAR(30)  NOT NULL UNIQUE,
    name        VARCHAR(60)  NOT NULL,
    sort_order  SMALLINT     NOT NULL,
    is_terminal BOOLEAN      NOT NULL DEFAULT FALSE         -- TRUE = no hay transición posible
);
COMMENT ON TABLE case_status IS
    'RQF-07.2 — Estados visibles al ciudadano. is_terminal bloquea transiciones desde estados finales.';

INSERT INTO case_status (code, name, sort_order, is_terminal) VALUES
    ('RECEIVED',    'Recibido',     1, FALSE),
    ('IN_REVIEW',   'En Revisión',  2, FALSE),
    ('PROCESSED',   'Procesado',    3, FALSE),
    ('REFERRED',    'Derivado',     4, FALSE),
    ('ARCHIVED',    'Archivado',    5, TRUE),
    ('INVALIDATED', 'Invalidado',   6, TRUE);


-- 1.4 Unidades de derivación
CREATE TABLE referral_unit (
    id      SMALLSERIAL  PRIMARY KEY,
    code    VARCHAR(30)  NOT NULL UNIQUE,
    name    VARCHAR(100) NOT NULL
);
INSERT INTO referral_unit (code, name) VALUES
    ('SUBSEQUENT_CONTROL', 'Control Posterior'),
    ('INVESTIGATIVE_POWER', 'Potestad Investigativa');


-- 1.5 Tipos de señalado  ← configurable
-- field_schema define qué campos extra lleva cada tipo.
-- La app lee este JSON para renderizar el formulario y validar attributes en respondent.
--
-- Estructura de field_schema:
-- [
--   { "key": "id_number",  "label": "Cédula",  "type": "string",  "required": true,  "pattern": "^[VE]\\d{6,10}$" },
--   { "key": "full_name",  "label": "Nombre",  "type": "string",  "required": false },
--   { "key": "rif",        "label": "RIF",     "type": "string",  "required": true,  "pattern": "^[JGVEC]\\d{8,9}$" }
-- ]
CREATE TABLE respondent_type (
    id              SMALLSERIAL  PRIMARY KEY,
    code            VARCHAR(30)  NOT NULL UNIQUE,
    name            VARCHAR(80)  NOT NULL,
    field_schema    JSONB        NOT NULL DEFAULT '[]',      -- define campos dinámicos
    active          BOOLEAN      NOT NULL DEFAULT TRUE,
    created_at      TIMESTAMPTZ  NOT NULL DEFAULT now(),
    updated_at      TIMESTAMPTZ  NOT NULL DEFAULT now()
);
COMMENT ON TABLE respondent_type IS
    'RQF-03.2 — Catálogo editable por el admin. field_schema define qué campos
     de identificación lleva cada tipo (cedula, rif, situr, nombre_comuna, etc.).
     La app valida respondent.attributes contra este schema en cada inserción/actualización.';

INSERT INTO respondent_type (code, name, field_schema) VALUES
    ('NATURAL_PERSON', 'Persona Natural',
     '[{"key":"id_number","label":"Cédula","type":"string","required":true,"pattern":"^[VE]\\d{5,10}$"},
       {"key":"full_name","label":"Nombre completo","type":"string","required":true}]'),

    ('LEGAL_ENTITY', 'Persona Jurídica',
     '[{"key":"rif","label":"RIF","type":"string","required":true,"pattern":"^[JGVEC]\\d{8,9}$"},
       {"key":"full_name","label":"Razón social","type":"string","required":true}]'),

    ('GOV_AGENCY', 'Órgano o Ente',
     '[{"key":"rif","label":"RIF","type":"string","required":true,"pattern":"^[JGVEC]\\d{8,9}$"},
       {"key":"full_name","label":"Denominación","type":"string","required":true}]'),

    ('COMMUNE', 'Comuna',
     '[{"key":"commune_name","label":"Nombre de la comuna","type":"string","required":true},
       {"key":"situr_code","label":"Código SITUR","type":"string","required":true}]'),

    ('COMMUNAL_COUNCIL', 'Consejo Comunal',
     '[{"key":"commune_name","label":"Nombre del consejo comunal","type":"string","required":true},
       {"key":"situr_code","label":"Código SITUR","type":"string","required":true}]'),

    ('JUSTICE_OF_PEACE', 'Juez de Paz',
     '[{"key":"id_number","label":"Cédula","type":"string","required":true,"pattern":"^[VE]\\d{5,10}$"},
       {"key":"full_name","label":"Nombre completo","type":"string","required":true}]'),

    ('OTHER', 'Otro',
     '[{"key":"description","label":"Descripción","type":"string","required":true}]');


-- 1.6 Tipos de documento de identidad del ciudadano
CREATE TABLE id_document_type (
    id      SMALLSERIAL  PRIMARY KEY,
    code    VARCHAR(10)  NOT NULL UNIQUE,    -- 'V','E','PASSPORT'
    name    VARCHAR(40)  NOT NULL
);
INSERT INTO id_document_type (code, name) VALUES
    ('V',        'Cédula Venezolana'),
    ('E',        'Cédula Extranjera'),
    ('PASSPORT', 'Pasaporte');


-- 1.7 Tipos de checklist de documentos físicos consignados
CREATE TABLE physical_doc_type (
    id      SMALLSERIAL  PRIMARY KEY,
    code    VARCHAR(40)  NOT NULL UNIQUE,
    name    VARCHAR(80)  NOT NULL,
    active  BOOLEAN      NOT NULL DEFAULT TRUE
);
INSERT INTO physical_doc_type (code, name) VALUES
    ('WITNESS_ID',      'Copia C.I. del testigo'),
    ('CLAIMANT_ID',     'Copia C.I. del denunciante'),
    ('COVER_LETTER',    'Carta de exposición de motivos'),
    ('PHOTOS',          'Fotografías'),
    ('VIDEO',           'Video'),
    ('AUDIO',           'Grabación de voz'),
    ('WRITTEN_TESTIMONY','Testimonio escrito'),
    ('OTHER',           'Otros');


-- 1.8 Catálogo de cargos del personal  (RQF-14)
CREATE TABLE job_position (
    id          SERIAL       PRIMARY KEY,
    title       VARCHAR(100) NOT NULL,
    description TEXT,
    active      BOOLEAN      NOT NULL DEFAULT TRUE,
    created_at  TIMESTAMPTZ  NOT NULL DEFAULT now(),
    updated_at  TIMESTAMPTZ  NOT NULL DEFAULT now()
);
COMMENT ON TABLE job_position IS
    'RQF-14 — No eliminable si tiene personal activo asociado (enforced por trigger).
     Se desactiva lógicamente para preservar integridad referencial.';


-- 1.9 Módulos del sistema (para RBAC)
CREATE TABLE app_module (
    id      SMALLSERIAL  PRIMARY KEY,
    code    VARCHAR(40)  NOT NULL UNIQUE,
    name    VARCHAR(80)  NOT NULL
);
INSERT INTO app_module (code, name) VALUES
    ('CASES',       'Gestión de Expedientes'),
    ('CLASSIFY',    'Clasificación y Derivación'),
    ('USERS',       'Gestión de Usuarios'),
    ('ACCESS',      'Gestión de Accesos'),
    ('STATS',       'Criterios Estadísticos'),
    ('CMS',         'Gestión de Página Web'),
    ('CATALOGS',    'Gestión de Catálogos'),
    ('REPORTS',     'Reportes e Informes');


-- 1.10 Tipos de contenido CMS
CREATE TABLE cms_content_type (
    id      SMALLSERIAL  PRIMARY KEY,
    code    VARCHAR(30)  NOT NULL UNIQUE,
    name    VARCHAR(60)  NOT NULL
);

INSERT INTO cms_content_type (code, name) VALUES
    ('NEWS',        'Noticia institucional'),
    ('MISSION',     'Misión'),
    ('VISION',      'Visión'),
    ('ORG_CHART',   'Organigrama'),
    ('BULLETIN',    'Cartelera digital'),
    ('CITIZEN_GUIDE','Guía ciudadana');


-- =============================================================================
-- BLOQUE 2 — USUARIOS INTERNOS
-- Núcleo normalizado. Solo session_log usa JSONB para metadata variable del cliente.
-- =============================================================================

CREATE TABLE staff_user (
    id                  UUID         PRIMARY KEY DEFAULT gen_random_uuid(),
    job_position_id     INT          NOT NULL REFERENCES job_position(id),
    id_doc_type_id      SMALLINT     NOT NULL REFERENCES id_document_type(id),
    id_doc_number       VARCHAR(12)  NOT NULL,
    first_name          VARCHAR(80)  NOT NULL,
    last_name           VARCHAR(80)  NOT NULL,
    email               VARCHAR(150) NOT NULL UNIQUE,
    password_hash       TEXT         NOT NULL,
    active              BOOLEAN      NOT NULL DEFAULT TRUE,
    failed_attempts     SMALLINT     NOT NULL DEFAULT 0,
    locked_until        TIMESTAMPTZ,                        -- NULL = no bloqueado
    last_login          TIMESTAMPTZ,
    created_at          TIMESTAMPTZ  NOT NULL DEFAULT now(),
    updated_at          TIMESTAMPTZ  NOT NULL DEFAULT now(),

    UNIQUE (id_doc_type_id, id_doc_number)
);
COMMENT ON TABLE staff_user IS
    'RQF-01 / RQF-08 — Personal administrativo interno.
     Desactivación lógica (active=FALSE). Nunca se elimina el registro.';


CREATE TABLE staff_privilege (
    id              SERIAL       PRIMARY KEY,
    user_id         UUID         NOT NULL REFERENCES staff_user(id),
    module_id       SMALLINT     NOT NULL REFERENCES app_module(id),
    can_read        BOOLEAN      NOT NULL DEFAULT FALSE,
    can_write       BOOLEAN      NOT NULL DEFAULT FALSE,
    can_delete      BOOLEAN      NOT NULL DEFAULT FALSE,
    granted_by      UUID         REFERENCES staff_user(id),
    granted_at      TIMESTAMPTZ  NOT NULL DEFAULT now(),

    UNIQUE (user_id, module_id)
);
COMMENT ON TABLE staff_privilege IS
    'RQF-09.2 — RBAC granular por módulo. Cambios efectivos desde la siguiente sesión.';


-- session_log: columnas normalizadas para las partes consultables (usuario, horario),
-- client_meta JSONB para todo lo que varía según el cliente (IP, user-agent, device…)
CREATE TABLE session_log (
    id              UUID         PRIMARY KEY DEFAULT gen_random_uuid(),
    user_id         UUID         NOT NULL REFERENCES staff_user(id),
    token_hash      TEXT         NOT NULL UNIQUE,
    expires_at      TIMESTAMPTZ  NOT NULL,
    closed_at       TIMESTAMPTZ,                            -- NULL = activa
    client_meta     JSONB        NOT NULL DEFAULT '{}',     -- ip, user_agent, device, etc.
    created_at      TIMESTAMPTZ  NOT NULL DEFAULT now()
);
COMMENT ON TABLE session_log IS
    'RQF-01 — Token invalidado al cerrar sesión (closed_at). Expiración por inactividad
     controlada comparando expires_at con now().
     client_meta es JSONB porque los campos varían (mobile/desktop/proxy/IPv6…).';


-- =============================================================================
-- BLOQUE 3 — CIUDADANOS (denunciantes)
-- Núcleo fijo → columnas normalizadas con constraints.
-- Datos de contacto extendidos → contact_data JSONB.
--
-- Separación:
--   Columnas normalizadas: todo lo que identifica legalmente al ciudadano
--                          y que la DB necesita para integridad o consultas.
--   contact_data JSONB:    campos que pueden variar por decreto o necesidad futura
--                          (tel_trabajo, redes sociales, campos de accesibilidad…)
--                          La app valida el schema, la DB solo garantiza que sea JSON.
-- =============================================================================

CREATE TABLE citizen (
    id              UUID         PRIMARY KEY DEFAULT gen_random_uuid(),

    -- Identidad legal — raramente cambia, constraints fuertes
    id_doc_type_id  SMALLINT     NOT NULL REFERENCES id_document_type(id),
    id_doc_number   VARCHAR(12)  NOT NULL,
    first_name      VARCHAR(80)  NOT NULL,
    last_name       VARCHAR(80)  NOT NULL,
    sex             CHAR(1)      NOT NULL CHECK (sex IN ('M','F')),
    birth_date      DATE         CHECK (birth_date < CURRENT_DATE),

    -- Contacto primario — obligatorio en todos los trámites
    email           VARCHAR(150) NOT NULL,
    mobile_phone    VARCHAR(15)  NOT NULL,       -- formato venezolano 04XX-XXXXXXX

    -- Dirección — obligatoria, raramente cambia
    address         VARCHAR(250) NOT NULL        CHECK (char_length(address) >= 10),
    parish          VARCHAR(80),
    municipality    VARCHAR(80)  NOT NULL DEFAULT 'Páez',
    city            VARCHAR(80),

    -- Contacto extendido y datos demográficos opcionales → JSONB
    -- Schema esperado por la app (no enforced en DB):
    -- {
    --   "home_phone":   "0257-...",
    --   "work_phone":   "0212-...",
    --   "birth_place":  "Acarigua, Portuguesa",
    --   "education_level": "TSU",           -- cat: PRIMARY/SECONDARY/TSU/UNIVERSITY/POSTGRAD
    --   "marital_status":  "SINGLE",        -- cat: SINGLE/MARRIED/DIVORCED/WIDOWED/COMMON_LAW
    --   "occupation":   "Contador"
    -- }
    contact_data    JSONB        NOT NULL DEFAULT '{}',

    created_at      TIMESTAMPTZ  NOT NULL DEFAULT now(),
    updated_at      TIMESTAMPTZ  NOT NULL DEFAULT now(),

    UNIQUE (id_doc_type_id, id_doc_number)
);
COMMENT ON TABLE citizen IS
    'RQF-03.1 — Denunciante identificado. No se permiten registros anónimos.
     contact_data almacena campos demográficos y de contacto opcionales que pueden
     crecer sin migraciones. La app valida el schema antes de insertar.';


-- =============================================================================
-- BLOQUE 4 — EXPEDIENTES (núcleo del sistema)
-- =============================================================================

-- Control de correlativo por año (unicidad concurrente sin gaps)
CREATE TABLE annual_sequence (
    year        SMALLINT  PRIMARY KEY,
    last_number INT       NOT NULL DEFAULT 0
);

COMMENT ON TABLE annual_sequence IS
    'RQF-05.1 — Contador por año fiscal. UPDATE ... RETURNING con FOR UPDATE
     serializa la concurrencia. El trigger lo usa para generar OAC-AÑO-NNNNN.';


CREATE TABLE case_file (
    id                  UUID         PRIMARY KEY DEFAULT gen_random_uuid(),

    -- Código institucional: OAC-2026-00001 (generado por trigger)
    case_number         VARCHAR(20)  NOT NULL UNIQUE,

    -- Token público para consulta ciudadana — opaco, no secuencial
    tracking_code       VARCHAR(36)  NOT NULL UNIQUE DEFAULT gen_random_uuid()::TEXT,

    -- Tipo y estado
    claim_type_id       SMALLINT     NOT NULL REFERENCES claim_type(id),
    status_id           SMALLINT     NOT NULL REFERENCES case_status(id),

    -- Denunciante
    citizen_id          UUID         NOT NULL REFERENCES citizen(id),

    -- Narración principal
    narrative           TEXT         NOT NULL
                            CHECK (char_length(narrative) BETWEEN 50 AND 3000),
    incident_date       DATE         NOT NULL CHECK (incident_date <= CURRENT_DATE),
    incident_location   VARCHAR(250),

    -- Presentado ante otra instancia
    other_instance      BOOLEAN      NOT NULL DEFAULT FALSE,
    other_instance_name VARCHAR(200),

    -- Consulta Popular Nacional (solo si tipo = COMPLAINT)
    is_popular_consultation BOOLEAN  NOT NULL DEFAULT FALSE,

    -- Declaración jurada
    sworn_declaration   BOOLEAN      NOT NULL DEFAULT FALSE,
    declaration_date    TIMESTAMPTZ,

    -- Protocolización (RQF-05)
    protocolized_at     TIMESTAMPTZ,
    qr_payload          TEXT,
    receipt_pdf_url     TEXT,

    -- Soportes físicos (RQF-06)
    physical_docs_deadline   DATE,                          -- protocolized_at + 5 días hábiles
    physical_docs_submitted  BOOLEAN  NOT NULL DEFAULT FALSE,
    physical_docs_date       DATE,
    physical_file_number     VARCHAR(50),
    analyst_notes            TEXT,

    -- Clasificación y derivación (RQF-06)
    irregularity_type_id     SMALLINT REFERENCES irregularity_type(id),
    referral_unit_id         SMALLINT REFERENCES referral_unit(id),
    referred_at              TIMESTAMPTZ,
    referred_by              UUID     REFERENCES staff_user(id),
    referral_letter_url      TEXT,

    -- Integración SIGECE
    sigece_reference         VARCHAR(80),

    -- Recibido por (NULL = registro público en portal)
    received_by              UUID     REFERENCES staff_user(id),

    created_at          TIMESTAMPTZ  NOT NULL DEFAULT now(),
    updated_at          TIMESTAMPTZ  NOT NULL DEFAULT now(),

    CONSTRAINT ck_other_instance_name
        CHECK (other_instance = FALSE OR other_instance_name IS NOT NULL),
    CONSTRAINT ck_sworn_declaration
        CHECK (sworn_declaration = TRUE OR status_id = 1)
);
COMMENT ON TABLE case_file IS
    'Núcleo del sistema. tracking_code es el token público opaco del ciudadano (RQF-07).
     case_number se genera con trigger usando annual_sequence.';


-- Bloque condicional Consulta Popular (1:0..1)
CREATE TABLE popular_consultation (
    case_file_id        UUID         PRIMARY KEY REFERENCES case_file(id) ON DELETE CASCADE,
    project_name        VARCHAR(200) NOT NULL CHECK (char_length(project_name) >= 4),
    approval_date       DATE         NOT NULL CHECK (approval_date <= CURRENT_DATE),
    project_amount      NUMERIC(18,2) NOT NULL CHECK (project_amount > 0),
    funding_entity      VARCHAR(200) NOT NULL CHECK (char_length(funding_entity) >= 3),
    created_at          TIMESTAMPTZ  NOT NULL DEFAULT now()
);
COMMENT ON TABLE popular_consultation IS
    'RQF bloque 4 — Solo existe cuando case_file.is_popular_consultation = TRUE
     y claim_type = COMPLAINT. Relación 1:0..1 evita columnas nullable en case_file.';


-- =============================================================================
-- BLOQUE 5 — SEÑALADOS
-- Columnas fijas: las mínimas para integridad referencial y ubicación.
-- attributes JSONB: campos de identificación definidos por respondent_type.field_schema.
-- La app valida attributes contra el schema del tipo antes de insertar.
-- =============================================================================

CREATE TABLE respondent (
    id                  SERIAL       PRIMARY KEY,
    case_file_id        UUID         NOT NULL REFERENCES case_file(id) ON DELETE CASCADE,
    respondent_type_id  SMALLINT     NOT NULL REFERENCES respondent_type(id),

    -- Mínimo normalizado: ubicación (siempre obligatoria y consultable)
    location            VARCHAR(250) NOT NULL CHECK (char_length(location) >= 5),

    -- Campos de identificación variables según el tipo
    -- Validados en app contra respondent_type.field_schema
    -- Ejemplo para NATURAL_PERSON: {"id_number": "V-12345678", "full_name": "Juan Pérez"}
    -- Ejemplo para COMMUNE:        {"commune_name": "El Palito", "situr_code": "P-1234"}
    attributes          JSONB        NOT NULL DEFAULT '{}',

    created_at          TIMESTAMPTZ  NOT NULL DEFAULT now()
);


COMMENT ON TABLE respondent IS
    'RQF-03.2 — Al menos 1 señalado por expediente (enforced en app).
     attributes contiene los campos definidos por respondent_type.field_schema.
     La DB no valida el schema interno del JSONB; eso es responsabilidad de la app
     (y opcionalmente de un JSON Schema en check constraint si se desea).';

-- Índice GIN para búsquedas dentro de attributes (cédula, RIF, SITUR…)
CREATE INDEX idx_respondent_attributes ON respondent USING GIN (attributes);


-- =============================================================================
-- BLOQUE 6 — EVIDENCIAS Y DOCUMENTOS
-- =============================================================================

-- Archivos digitales adjuntos (RQF-04.1 / RQF-04.3)
CREATE TABLE evidence_file (
    id              UUID         PRIMARY KEY DEFAULT gen_random_uuid(),
    case_file_id    UUID         NOT NULL REFERENCES case_file(id) ON DELETE CASCADE,
    original_name   VARCHAR(255) NOT NULL,
    mime_type       VARCHAR(100) NOT NULL,
    size_bytes      INT          NOT NULL CHECK (size_bytes <= 10485760),    -- 10 MB
    storage_url     TEXT         NOT NULL,
    uploaded_by     UUID         REFERENCES staff_user(id),                  -- NULL = ciudadano
    created_at      TIMESTAMPTZ  NOT NULL DEFAULT now()
);
COMMENT ON TABLE evidence_file IS
    'RQF-04.1 / RQF-04.3 — Archivos de soporte. 10 MB enforced en CHECK y en app.
     storage_url apunta al sistema de almacenamiento externo (S3, MinIO, etc.).';


-- Checklist de documentos físicos consignados (RQF-04.2)
CREATE TABLE case_physical_doc (
    case_file_id        UUID         NOT NULL REFERENCES case_file(id) ON DELETE CASCADE,
    physical_doc_type_id SMALLINT    NOT NULL REFERENCES physical_doc_type(id),
    other_description   VARCHAR(200),                       -- solo para tipo OTHER
    checked_at          TIMESTAMPTZ  NOT NULL DEFAULT now(),

    PRIMARY KEY (case_file_id, physical_doc_type_id)
);


-- Minutas físicas digitalizadas (RQF-06.3)
CREATE TABLE scanned_minute (
    id              UUID         PRIMARY KEY DEFAULT gen_random_uuid(),
    case_file_id    UUID         NOT NULL REFERENCES case_file(id) ON DELETE CASCADE,
    file_name       VARCHAR(255) NOT NULL,
    storage_url     TEXT         NOT NULL,
    uploaded_by     UUID         NOT NULL REFERENCES staff_user(id),
    created_at      TIMESTAMPTZ  NOT NULL DEFAULT now()
);


-- =============================================================================
-- BLOQUE 7 — AUDITORÍA Y TRAZABILIDAD
-- =============================================================================

-- Historial de cambios de estado — fuente pública (RQF-07.2) e interna (RQF-13.2)
CREATE TABLE case_status_log (
    id              BIGSERIAL    PRIMARY KEY,
    case_file_id    UUID         NOT NULL REFERENCES case_file(id),
    previous_status SMALLINT     REFERENCES case_status(id),       -- NULL en el primero
    new_status      SMALLINT     NOT NULL REFERENCES case_status(id),
    changed_by      UUID         REFERENCES staff_user(id),         -- NULL = sistema
    changed_at      TIMESTAMPTZ  NOT NULL DEFAULT now()
);
COMMENT ON TABLE case_status_log IS
    'RQF-07.2 / RQF-13.2 — La vista pública solo muestra new_status + changed_at.
     La vista interna incluye changed_by y el historial completo.';

CREATE INDEX idx_status_log_case ON case_status_log (case_file_id, changed_at DESC);


-- Acciones de analistas — consultable/filtrable por tipo, fecha y actor (RQF-13.2)
-- action_type normalizado porque es el eje principal de filtrado en SQL.
-- payload JSONB para el contenido variable de cada tipo de acción.
--
-- Estructura de payload por action_type:
--   CLASSIFICATION  → {"irregularity_type_id": 3, "notes": "..."}
--   REFERRAL        → {"unit": "INVESTIGATIVE_POWER", "letter_url": "..."}
--   STATUS_CHANGE   → {"from": "IN_REVIEW", "to": "PROCESSED", "reason": "..."}
--   NOTE            → {"text": "..."}
--   DOCUMENT_CHECK  → {"docs_submitted": true, "submission_date": "2026-06-24"}
CREATE TABLE case_action (
    id              BIGSERIAL    PRIMARY KEY,
    case_file_id    UUID         NOT NULL REFERENCES case_file(id),
    user_id         UUID         NOT NULL REFERENCES staff_user(id),
    action_type     VARCHAR(30)  NOT NULL
                        CHECK (action_type IN (
                            'CLASSIFICATION','REFERRAL','STATUS_CHANGE',
                            'NOTE','DOCUMENT_CHECK','OTHER'
                        )),
    payload         JSONB        NOT NULL DEFAULT '{}',
    performed_at    TIMESTAMPTZ  NOT NULL DEFAULT now()
);
COMMENT ON TABLE case_action IS
    'RQF-13.2 — Consultable por action_type, performed_at y user_id.
     payload contiene el detalle variable según el tipo de acción.
     Nunca expuesto en la vista pública del ciudadano.';

CREATE INDEX idx_case_action_case    ON case_action (case_file_id, performed_at DESC);
CREATE INDEX idx_case_action_type    ON case_action (action_type, performed_at DESC);
CREATE INDEX idx_case_action_user    ON case_action (user_id, performed_at DESC);
CREATE INDEX idx_case_action_payload ON case_action USING GIN (payload);


-- Bitácora de cambios de privilegios (RQF-09.3) — inmutable
CREATE TABLE access_audit_log (
    id                  BIGSERIAL    PRIMARY KEY,
    affected_user_id    UUID         NOT NULL REFERENCES staff_user(id),
    module_id           SMALLINT     NOT NULL REFERENCES app_module(id),
    admin_id            UUID         NOT NULL REFERENCES staff_user(id),
    action              VARCHAR(20)  NOT NULL CHECK (action IN ('GRANT','REVOKE','MODIFY')),
    before_state        JSONB,
    after_state         JSONB,
    logged_at           TIMESTAMPTZ  NOT NULL DEFAULT now()
);
COMMENT ON TABLE access_audit_log IS
    'RQF-09.3 — Solo INSERT. Nunca UPDATE ni DELETE. before_state/after_state son
     JSONB porque son snapshots de staff_privilege, que puede agregar columnas.';


-- Intentos de acceso no autorizado (RQF-09)
CREATE TABLE unauthorized_access_log (
    id              BIGSERIAL    PRIMARY KEY,
    user_id         UUID         REFERENCES staff_user(id),
    module_id       SMALLINT     REFERENCES app_module(id),
    endpoint        VARCHAR(200),
    client_meta     JSONB        NOT NULL DEFAULT '{}',     -- ip, user_agent…
    logged_at       TIMESTAMPTZ  NOT NULL DEFAULT now()
);


-- =============================================================================
-- BLOQUE 8 — CMS  (RQF-11)
-- =============================================================================

CREATE TABLE cms_content (
    id              UUID         PRIMARY KEY DEFAULT gen_random_uuid(),
    content_type_id SMALLINT     NOT NULL REFERENCES cms_content_type(id),
    title           VARCHAR(200) NOT NULL,
    body            TEXT         NOT NULL,
    published       BOOLEAN      NOT NULL DEFAULT FALSE,
    author_id       UUID         NOT NULL REFERENCES staff_user(id),
    published_at    TIMESTAMPTZ,
    unpublished_at  TIMESTAMPTZ,
    created_at      TIMESTAMPTZ  NOT NULL DEFAULT now(),
    updated_at      TIMESTAMPTZ  NOT NULL DEFAULT now()
);

-- Historial de versiones para previsualización y auditoría de autoría (RQF-11.3 / RQF-11.4)
CREATE TABLE cms_content_version (
    id              BIGSERIAL    PRIMARY KEY,
    content_id      UUID         NOT NULL REFERENCES cms_content(id),
    title           VARCHAR(200) NOT NULL,
    body            TEXT         NOT NULL,
    modified_by     UUID         NOT NULL REFERENCES staff_user(id),
    modified_at     TIMESTAMPTZ  NOT NULL DEFAULT now()
);


-- =============================================================================
-- FUNCIONES Y TRIGGERS
-- =============================================================================

-- F1: Generar case_number = OAC-AÑO-NNNNN con serialización concurrente
CREATE OR REPLACE FUNCTION fn_generate_case_number()
RETURNS TRIGGER LANGUAGE plpgsql AS $$
DECLARE
    v_year  SMALLINT := EXTRACT(YEAR FROM now())::SMALLINT;
    v_seq   INT;
BEGIN
    INSERT INTO annual_sequence (year, last_number) VALUES (v_year, 1)
    ON CONFLICT (year) DO UPDATE
        SET last_number = annual_sequence.last_number + 1
    RETURNING last_number INTO v_seq;

    NEW.case_number := 'OAC-' || v_year || '-' || LPAD(v_seq::TEXT, 5, '0');
    RETURN NEW;
END;
$$;

CREATE TRIGGER trg_generate_case_number
    BEFORE INSERT ON case_file
    FOR EACH ROW
    WHEN (NEW.case_number IS NULL)
    EXECUTE FUNCTION fn_generate_case_number();


-- F2: Registrar cambio de estado en case_status_log automáticamente
CREATE OR REPLACE FUNCTION fn_log_status_change()
RETURNS TRIGGER LANGUAGE plpgsql AS $$
BEGIN
    IF OLD.status_id IS DISTINCT FROM NEW.status_id THEN
        INSERT INTO case_status_log (case_file_id, previous_status, new_status)
        VALUES (NEW.id, OLD.status_id, NEW.status_id);
    END IF;
    RETURN NEW;
END;
$$;

CREATE TRIGGER trg_log_status_change
    AFTER UPDATE OF status_id ON case_file
    FOR EACH ROW EXECUTE FUNCTION fn_log_status_change();


-- F3: Insertar estado inicial al crear el expediente
CREATE OR REPLACE FUNCTION fn_initial_status()
RETURNS TRIGGER LANGUAGE plpgsql AS $$
BEGIN
    INSERT INTO case_status_log (case_file_id, previous_status, new_status)
    VALUES (NEW.id, NULL, NEW.status_id);
    RETURN NEW;
END;
$$;

CREATE TRIGGER trg_initial_status
    AFTER INSERT ON case_file
    FOR EACH ROW EXECUTE FUNCTION fn_initial_status();


-- F4: Calcular physical_docs_deadline al protocolizar
-- Nota: +7 días calendario como proxy de 5 días hábiles.
-- Reemplazar con tabla de feriados en producción.
CREATE OR REPLACE FUNCTION fn_set_docs_deadline()
RETURNS TRIGGER LANGUAGE plpgsql AS $$
BEGIN
    IF NEW.protocolized_at IS NOT NULL AND OLD.protocolized_at IS NULL THEN
        NEW.physical_docs_deadline :=
            (NEW.protocolized_at::DATE + INTERVAL '7 days')::DATE;
    END IF;
    RETURN NEW;
END;
$$;

CREATE TRIGGER trg_set_docs_deadline
    BEFORE UPDATE OF protocolized_at ON case_file
    FOR EACH ROW EXECUTE FUNCTION fn_set_docs_deadline();


-- F5: Proteger job_position de eliminación si tiene personal activo (RQF-14)
CREATE OR REPLACE FUNCTION fn_protect_active_position()
RETURNS TRIGGER LANGUAGE plpgsql AS $$
BEGIN
    IF EXISTS (SELECT 1 FROM staff_user WHERE job_position_id = OLD.id AND active = TRUE) THEN
        RAISE EXCEPTION
            'Cannot delete job position "%": it has active staff members assigned.',
            OLD.title USING ERRCODE = '23000';
    END IF;
    RETURN OLD;
END;
$$;

CREATE TRIGGER trg_protect_active_position
    BEFORE DELETE ON job_position
    FOR EACH ROW EXECUTE FUNCTION fn_protect_active_position();


-- F6: updated_at automático
CREATE OR REPLACE FUNCTION fn_updated_at()
RETURNS TRIGGER LANGUAGE plpgsql AS $$
BEGIN NEW.updated_at := now(); RETURN NEW; END;
$$;

CREATE TRIGGER trg_upd_case_file    BEFORE UPDATE ON case_file    FOR EACH ROW EXECUTE FUNCTION fn_updated_at();
CREATE TRIGGER trg_upd_citizen      BEFORE UPDATE ON citizen       FOR EACH ROW EXECUTE FUNCTION fn_updated_at();
CREATE TRIGGER trg_upd_staff_user   BEFORE UPDATE ON staff_user    FOR EACH ROW EXECUTE FUNCTION fn_updated_at();
CREATE TRIGGER trg_upd_cms_content  BEFORE UPDATE ON cms_content   FOR EACH ROW EXECUTE FUNCTION fn_updated_at();
CREATE TRIGGER trg_upd_job_position BEFORE UPDATE ON job_position  FOR EACH ROW EXECUTE FUNCTION fn_updated_at();
CREATE TRIGGER trg_upd_respondent_type BEFORE UPDATE ON respondent_type FOR EACH ROW EXECUTE FUNCTION fn_updated_at();


-- =============================================================================
-- VISTAS
-- =============================================================================

-- Vista pública — solo tracking_code + estatus + timeline (RQF-07)
-- No expone datos del ciudadano, señalados, narración ni acciones internas.
CREATE VIEW v_public_case_tracking AS
SELECT
    cf.tracking_code,
    ct.name                         AS claim_type,
    cs.name                         AS current_status,
    cf.created_at                   AS received_at,
    cf.protocolized_at,
    (
        SELECT json_agg(
            json_build_object('status', s.name, 'date', sl.changed_at)
            ORDER BY sl.changed_at
        )
        FROM case_status_log sl
        JOIN case_status s ON s.id = sl.new_status
        WHERE sl.case_file_id = cf.id
    )                               AS timeline
FROM case_file cf
JOIN claim_type  ct ON ct.id = cf.claim_type_id
JOIN case_status cs ON cs.id = cf.status_id;

COMMENT ON VIEW v_public_case_tracking IS
    'RQF-07 — Acceso público por tracking_code. Sin datos sensibles.';


-- Vista interna completa para analistas (RQF-13)
CREATE VIEW v_case_detail AS
SELECT
    cf.id,
    cf.case_number,
    cf.tracking_code,
    ct.name                         AS claim_type,
    cs.name                         AS status,
    -- Ciudadano
    c.first_name || ' ' || c.last_name AS claimant_name,
    c.email                         AS claimant_email,
    c.mobile_phone,
    c.contact_data                  AS claimant_extended_data,
    -- Narración
    cf.narrative,
    cf.incident_date,
    cf.incident_location,
    cf.other_instance,
    cf.other_instance_name,
    -- Protocolización
    cf.protocolized_at,
    -- Clasificación
    it.name                         AS irregularity_type,
    ru.name                         AS referral_unit,
    cf.referred_at,
    cf.physical_file_number,
    cf.analyst_notes,
    -- Soportes físicos
    cf.physical_docs_submitted,
    cf.physical_docs_deadline,
    cf.physical_docs_date,
    -- Integración
    cf.sigece_reference,
    cf.created_at,
    cf.updated_at
FROM case_file cf
JOIN claim_type          ct ON ct.id = cf.claim_type_id
JOIN case_status         cs ON cs.id = cf.status_id
JOIN citizen             c  ON c.id  = cf.citizen_id
LEFT JOIN irregularity_type it ON it.id = cf.irregularity_type_id
LEFT JOIN referral_unit     ru ON ru.id = cf.referral_unit_id;

COMMENT ON VIEW v_case_detail IS
    'RQF-13 — Vista interna completa. El control de acceso se aplica en la capa
     de aplicación verificando staff_privilege antes de exponer esta vista.';


-- =============================================================================
-- ÍNDICES COMPLEMENTARIOS
-- =============================================================================

-- Hot paths de consulta
CREATE INDEX idx_case_tracking_code   ON case_file (tracking_code);          -- RQF-07 (crítico)
CREATE INDEX idx_case_number          ON case_file (case_number);
CREATE INDEX idx_case_citizen         ON case_file (citizen_id);
CREATE INDEX idx_case_status          ON case_file (status_id);
CREATE INDEX idx_case_claim_type      ON case_file (claim_type_id);

-- Vencimiento de soportes (job nocturno de notificación — RQF-06)
CREATE INDEX idx_case_docs_deadline
    ON case_file (physical_docs_deadline)
    WHERE physical_docs_submitted = FALSE
      AND protocolized_at IS NOT NULL;

-- Sesiones activas (RQF-01)
CREATE INDEX idx_session_active
    ON session_log (user_id)
    WHERE closed_at IS NULL;

-- Privilegios (hot path de autorización en cada request)
CREATE INDEX idx_privilege_lookup
    ON staff_privilege (user_id, module_id);

-- Ciudadano por documento (deduplicación en registro)
CREATE UNIQUE INDEX idx_citizen_doc
    ON citizen (id_doc_type_id, id_doc_number);

-- Respondent: búsqueda dentro de attributes (cédula, RIF, SITUR)
-- Ya creado arriba con GIN

-- CMS: contenido publicado por tipo (portal público)
CREATE INDEX idx_cms_published
    ON cms_content (content_type_id, published_at DESC)
    WHERE published = TRUE;

-- Access audit por usuario (RQF-09.3)
CREATE INDEX idx_access_audit_user
    ON access_audit_log (affected_user_id, logged_at DESC);