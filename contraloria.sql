-- =============================================================================
-- OAC CONTRALORÍA MUNICIPIO PÁEZ — Schema v2 (MySQL 8.0+)
-- Convertido desde PostgreSQL 15
--
-- NOTAS DE CONVERSIÓN (léelas antes de correr el script)
-- ─────────────────────────────────────────────────────────────────────────────
-- 1. UUID            -> CHAR(36), generado con DEFAULT (UUID())  (requiere MySQL 8.0.13+)
-- 2. SMALLSERIAL/SERIAL/BIGSERIAL -> int/INT/BIGINT UNSIGNED AUTO_INCREMENT
-- 3. JSONB           -> JSON  (MySQL no distingue binario/texto, JSON ya es binario internamente)
-- 4. TIMESTAMPTZ     -> DATETIME  (MySQL TIMESTAMP tiene rango 1970-2038 y hace
--                       conversión automática de zona horaria por sesión; DATETIME
--                       evita ambos problemas. Si necesitas comportamiento con TZ,
--                       maneja la conversión en la app o usa TIMESTAMP sabiendo la limitación)
-- 5. now()           -> CURRENT_TIMESTAMP
-- 6. NUMERIC(p,s)    -> DECIMAL(p,s)  (son sinónimos en MySQL)
-- 7. COMMENT ON TABLE ... -> cláusula COMMENT='...' embebida en cada CREATE TABLE
-- 8. CREATE EXTENSION pgcrypto/unaccent -> eliminado (no aplica en MySQL;
--    UUID() es nativo, y unaccent no tiene equivalente directo — si se necesita
--    búsqueda insensible a acentos, usar una collation utf8mb4_0900_ai_ci)
-- 9. Índices GIN sobre JSONB -> eliminados. MySQL no indexa JSON directamente;
--    si se requiere indexar claves específicas del JSON, se deben crear columnas
--    generadas (generated columns) sobre esas claves e indexarlas individualmente.
--    Ejemplo comentado al final del archivo.
-- 10. CHECK constraints -> se mantienen tal cual (soportadas y ENFORCED desde MySQL 8.0.16)
-- 11. Los patrones regex dentro de los JSON de ejemplo llevan backslashes duplicados
--     respecto al original, porque MySQL sí interpreta '\\' como carácter de escape
--     dentro de literales de cadena (Postgres, por defecto, no lo hace).
-- 12. Se usa InnoDB + utf8mb4 (utf8mb4_0900_ai_ci) en todas las tablas.
-- =============================================================================

SET NAMES utf8mb4;
SET default_storage_engine = InnoDB;


-- =============================================================================
-- BLOQUE 1 — CATÁLOGOS
-- =============================================================================

-- 1.1 Tipos de trámite (Denuncia, Queja, Reclamo, Petición, Sugerencia)
CREATE TABLE claim_type (
    id                  int UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    code                VARCHAR(30)  NOT NULL UNIQUE,
    name                VARCHAR(80)  NOT NULL,
    validation_level    VARCHAR(20)  NOT NULL,
    active              BOOLEAN      NOT NULL DEFAULT TRUE,
    sort_order          int          NOT NULL DEFAULT 0,
    created_at          DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at          DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    updated_by          CHAR(36),
    CONSTRAINT ck_claim_type_validation_level
        CHECK (validation_level IN ('STRICT','BASIC','AUTOMATED'))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci
  COMMENT='RQF-02.1 / RQF-12 - Catalogo de tipos de tramite. Tipos inactivos no aparecen en el formulario pero se preservan en expedientes historicos.';


-- 1.2 Tipos de irregularidad / falta (catálogo legal LOCGRSNCF)
CREATE TABLE irregularity_type (
    id          int UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    code        VARCHAR(40)  NOT NULL UNIQUE,
    name        VARCHAR(120) NOT NULL,
    legal_basis TEXT,
    active      BOOLEAN      NOT NULL DEFAULT TRUE,
    sort_order  int          NOT NULL DEFAULT 0,
    created_at  DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at  DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    updated_by  CHAR(36)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci
  COMMENT='RQF-06.1 / RQF-12.2 - Tipificacion segun LOCGRSNCF.';


-- 1.3 Estados del expediente (orden importa para la línea de tiempo)
CREATE TABLE case_status (
    id          int UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    code        VARCHAR(30)  NOT NULL UNIQUE,
    name        VARCHAR(60)  NOT NULL,
    sort_order  int     NOT NULL,
    is_terminal BOOLEAN      NOT NULL DEFAULT FALSE,
    active      BOOLEAN      NOT NULL DEFAULT TRUE,
    created_at  DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at  DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    updated_by  CHAR(36)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci
  COMMENT='RQF-07.2 - Estados visibles al ciudadano. is_terminal bloquea transiciones desde estados finales.';

INSERT INTO case_status (code, name, sort_order, is_terminal) VALUES
    ('RECEIVED',    'Recibido',     1, FALSE),
    ('IN_REVIEW',   'En Revisión',  2, FALSE),
    ('PROCESSED',   'Procesado',    3, FALSE),
    ('REFERRED',    'Derivado',     4, FALSE),
    ('ARCHIVED',    'Archivado',    5, TRUE),
    ('INVALIDATED', 'Invalidado',   6, TRUE);


-- 1.4 Unidades de derivación
CREATE TABLE referral_unit (
    id          int UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    code        VARCHAR(30)  NOT NULL UNIQUE,
    name        VARCHAR(100) NOT NULL,
    active      BOOLEAN      NOT NULL DEFAULT TRUE,
    sort_order  int          NOT NULL DEFAULT 0,
    created_at  DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at  DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    updated_by  CHAR(36)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

INSERT INTO referral_unit (code, name) VALUES
    ('SUBSEQUENT_CONTROL', 'Control Posterior'),
    ('INVESTIGATIVE_POWER', 'Potestad Investigativa');


-- 1.5 Tipos de señalado (configurable)
-- field_schema define los campos dinámicos que la app usa para renderizar el
-- formulario y validar respondent.attributes.
CREATE TABLE respondent_type (
    id              int UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    code            VARCHAR(30)  NOT NULL UNIQUE,
    name            VARCHAR(80)  NOT NULL,
    field_schema    JSON         NOT NULL DEFAULT ('[]'),
    active          BOOLEAN      NOT NULL DEFAULT TRUE,
    sort_order      int          NOT NULL DEFAULT 0,
    created_at      DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at      DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    updated_by      CHAR(36)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci
  COMMENT='RQF-03.2 - Catalogo editable por el admin. field_schema define que campos de identificacion lleva cada tipo. La app valida respondent.attributes contra este schema en cada insercion/actualizacion.';

INSERT INTO respondent_type (code, name, field_schema) VALUES
    ('NATURAL_PERSON', 'Persona Natural',
     '[{"key":"id_number","label":"Cédula","type":"string","required":true,"pattern":"^[VE]\\\\d{5,10}$"},
       {"key":"full_name","label":"Nombre completo","type":"string","required":true}]'),

    ('LEGAL_ENTITY', 'Persona Jurídica',
     '[{"key":"rif","label":"RIF","type":"string","required":true,"pattern":"^[JGVEC]\\\\d{8,9}$"},
       {"key":"full_name","label":"Razón social","type":"string","required":true}]'),

    ('GOV_AGENCY', 'Órgano o Ente',
     '[{"key":"rif","label":"RIF","type":"string","required":true,"pattern":"^[JGVEC]\\\\d{8,9}$"},
       {"key":"full_name","label":"Denominación","type":"string","required":true}]'),

    ('COMMUNE', 'Comuna',
     '[{"key":"commune_name","label":"Nombre de la comuna","type":"string","required":true},
       {"key":"situr_code","label":"Código SITUR","type":"string","required":true}]'),

    ('COMMUNAL_COUNCIL', 'Consejo Comunal',
     '[{"key":"commune_name","label":"Nombre del consejo comunal","type":"string","required":true},
       {"key":"situr_code","label":"Código SITUR","type":"string","required":true}]'),

    ('JUSTICE_OF_PEACE', 'Juez de Paz',
     '[{"key":"id_number","label":"Cédula","type":"string","required":true,"pattern":"^[VE]\\\\d{5,10}$"},
       {"key":"full_name","label":"Nombre completo","type":"string","required":true}]'),

    ('OTHER', 'Otro',
     '[{"key":"description","label":"Descripción","type":"string","required":true}]');


-- 1.6 Tipos de documento de identidad del ciudadano
CREATE TABLE id_document_type (
    id          int UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    code        VARCHAR(10)  NOT NULL UNIQUE,
    name        VARCHAR(40)  NOT NULL,
    active      BOOLEAN      NOT NULL DEFAULT TRUE,
    sort_order  int          NOT NULL DEFAULT 0,
    created_at  DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at  DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    updated_by  CHAR(36)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

INSERT INTO id_document_type (code, name) VALUES
    ('V',        'Cédula Venezolana'),
    ('E',        'Cédula Extranjera'),
    ('PASSPORT', 'Pasaporte');


-- Catálogo de roles que una persona puede tener en el sistema.
-- Una misma persona (misma cédula) puede acumular varios roles a la vez:
-- p. ej. ser CITIZEN (denuncia algo) y a la vez RESPONDENT (fue señalada
-- en otro expediente), o STAFF (trabaja en la contraloría). El rol NO se
-- guarda como columna única en person — se resuelve con la tabla puente
-- person_role, que es many-to-many.
CREATE TABLE person_role_type (
    id          int UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    code        VARCHAR(20)  NOT NULL UNIQUE,
    name        VARCHAR(50)  NOT NULL,
    active      BOOLEAN      NOT NULL DEFAULT TRUE,
    sort_order  int          NOT NULL DEFAULT 0,
    created_at  DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at  DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    updated_by  CHAR(36)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci
  COMMENT='Roles que puede ejercer una persona: CITIZEN, RESPONDENT, STAFF. El cargo de administrador se resuelve dentro de STAFF via job_position/staff_privilege, no como rol aparte.';

INSERT INTO person_role_type (code, name, sort_order) VALUES
    ('CITIZEN',    'Ciudadano / Denunciante', 1),
    ('RESPONDENT', 'Señalado',                2),
    ('STAFF',      'Personal Interno',        3);


-- 1.11 PERSON — identidad genérica única (RQF-03 / RQF-01)
-- Núcleo de identidad compartido por cualquier ser humano que interactúe con
-- el sistema, sin importar el/los rol(es) que juegue. citizen, staff_user y
-- respondent (cuando es persona natural) son extensiones 1:1 de esta tabla:
-- su PK es a la vez FK hacia person(id). Así una misma cédula nunca se
-- duplica aunque la persona sea denunciante, señalada y funcionario a la vez.
CREATE TABLE person (
    id              CHAR(36)     NOT NULL DEFAULT (UUID()) PRIMARY KEY,

    -- Datos comunes a TODO ser humano — viven aquí una sola vez
    id_doc_type_id  int UNSIGNED NOT NULL,
    id_doc_number   VARCHAR(12)  NOT NULL,
    first_name      VARCHAR(80)  NOT NULL,
    last_name       VARCHAR(80)  NOT NULL,
    sex             CHAR(1)      NOT NULL,
    birth_date      DATE,

    -- Datos de contacto base
    email           VARCHAR(150) NOT NULL,
    mobile_phone    VARCHAR(15)  NOT NULL,

    -- Atributos dinámicos en JSON para lo que no amerita columna propia
    extended_data   JSON         NOT NULL DEFAULT ('{}'),

    created_at      DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at      DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    UNIQUE (id_doc_type_id, id_doc_number),
    CONSTRAINT ck_person_sex        CHECK (sex IN ('M','F')),
    CONSTRAINT ck_person_birth_date CHECK (birth_date IS NULL OR birth_date < CURRENT_DATE),
    CONSTRAINT fk_person_doc_type  FOREIGN KEY (id_doc_type_id) REFERENCES id_document_type(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci
  COMMENT='RQF-03 / RQF-01 - Identidad genérica unica por cedula/RIF. citizen, staff_user y respondent (persona natural) son extensiones 1:1 via FK compartida en su PK.';


-- Tabla puente: qué roles tiene cada persona y desde cuándo.
-- Se inserta una fila aquí cada vez que se crea el registro correspondiente
-- en citizen / staff_user / respondent (persona natural). No se elimina al
-- perder el rol (p. ej. staff dado de baja): se marca active = FALSE para
-- conservar el historial de "quién fue qué" en el sistema.
CREATE TABLE person_role (
    person_id       CHAR(36)     NOT NULL,
    role_type_id    int UNSIGNED NOT NULL,
    active          BOOLEAN      NOT NULL DEFAULT TRUE,
    granted_at      DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    revoked_at      DATETIME,

    PRIMARY KEY (person_id, role_type_id),
    CONSTRAINT fk_person_role_person FOREIGN KEY (person_id) REFERENCES person(id) ON DELETE CASCADE,
    CONSTRAINT fk_person_role_type   FOREIGN KEY (role_type_id) REFERENCES person_role_type(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci
  COMMENT='Bitacora de que roles ha tenido/tiene cada persona. Permite que una misma persona sea CITIZEN y RESPONDENT y STAFF simultaneamente.';


-- 1.7 Tipos de checklist de documentos físicos consignados
CREATE TABLE physical_doc_type (
    id          int UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    code        VARCHAR(40)  NOT NULL UNIQUE,
    name        VARCHAR(80)  NOT NULL,
    active      BOOLEAN      NOT NULL DEFAULT TRUE,
    sort_order  int          NOT NULL DEFAULT 0,
    created_at  DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at  DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    updated_by  CHAR(36)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

INSERT INTO physical_doc_type (code, name) VALUES
    ('WITNESS_ID',      'Copia C.I. del testigo'),
    ('CLAIMANT_ID',     'Copia C.I. del denunciante'),
    ('COVER_LETTER',    'Carta de exposición de motivos'),
    ('PHOTOS',          'Fotografías'),
    ('VIDEO',           'Video'),
    ('AUDIO',           'Grabación de voz'),
    ('WRITTEN_TESTIMONY','Testimonio escrito'),
    ('OTHER',           'Otros');


-- 1.8 Catálogo de cargos del personal (RQF-14)
CREATE TABLE job_position (
    id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    title       VARCHAR(100) NOT NULL,
    description TEXT,
    active      BOOLEAN      NOT NULL DEFAULT TRUE,
    sort_order  int          NOT NULL DEFAULT 0,
    created_at  DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at  DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    updated_by  CHAR(36)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci
  COMMENT='RQF-14 - No eliminable si tiene personal activo asociado (enforced por trigger). Se desactiva logicamente para preservar integridad referencial.';


-- 1.9 Módulos del sistema (para RBAC)
CREATE TABLE app_module (
    id          int UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    code        VARCHAR(40)  NOT NULL UNIQUE,
    name        VARCHAR(80)  NOT NULL,
    active      BOOLEAN      NOT NULL DEFAULT TRUE,
    sort_order  int          NOT NULL DEFAULT 0,
    created_at  DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at  DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    updated_by  CHAR(36)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

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
    id          int UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    code        VARCHAR(30)  NOT NULL UNIQUE,
    name        VARCHAR(60)  NOT NULL,
    active      BOOLEAN      NOT NULL DEFAULT TRUE,
    sort_order  int          NOT NULL DEFAULT 0,
    created_at  DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at  DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    updated_by  CHAR(36)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

INSERT INTO cms_content_type (code, name) VALUES
    ('NEWS',        'Noticia institucional'),
    ('MISSION',     'Misión'),
    ('VISION',      'Visión'),
    ('ORG_CHART',   'Organigrama'),
    ('BULLETIN',    'Cartelera digital'),
    ('CITIZEN_GUIDE','Guía ciudadana');


-- =============================================================================
-- BLOQUE 2 — USUARIOS INTERNOS
-- =============================================================================

-- staff_user es una extensión 1:1 de person (rol STAFF). id = person.id;
-- la identidad (cédula, nombre, sexo, etc.) vive solo en person. email aquí
-- es el correo institucional de acceso (distinto del email de contacto
-- personal en person.email), por eso conserva su propio UNIQUE para login.
CREATE TABLE staff_user (
    id                  CHAR(36)     NOT NULL PRIMARY KEY,
    job_position_id     INT UNSIGNED NOT NULL,
    email               VARCHAR(150) NOT NULL UNIQUE,
    password_hash       TEXT         NOT NULL,
    active              BOOLEAN      NOT NULL DEFAULT TRUE,
    failed_attempts     int     NOT NULL DEFAULT 0,
    locked_until        DATETIME,
    last_login          DATETIME,
    created_at          DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at          DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    CONSTRAINT fk_staff_user_person       FOREIGN KEY (id) REFERENCES person(id),
    CONSTRAINT fk_staff_user_job_position FOREIGN KEY (job_position_id) REFERENCES job_position(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci
  COMMENT='RQF-01 / RQF-08 - Personal administrativo interno, extension 1:1 de person (rol STAFF). Desactivacion logica (active=FALSE). Nunca se elimina el registro.';


CREATE TABLE staff_privilege (
    id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id         CHAR(36)     NOT NULL,
    module_id       int UNSIGNED NOT NULL,
    can_read        BOOLEAN      NOT NULL DEFAULT FALSE,
    can_write       BOOLEAN      NOT NULL DEFAULT FALSE,
    can_delete      BOOLEAN      NOT NULL DEFAULT FALSE,
    granted_by      CHAR(36),
    granted_at      DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,

    UNIQUE (user_id, module_id),
    CONSTRAINT fk_privilege_user     FOREIGN KEY (user_id) REFERENCES staff_user(id),
    CONSTRAINT fk_privilege_module   FOREIGN KEY (module_id) REFERENCES app_module(id),
    CONSTRAINT fk_privilege_grantor  FOREIGN KEY (granted_by) REFERENCES staff_user(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci
  COMMENT='RQF-09.2 - RBAC granular por modulo. Cambios efectivos desde la siguiente sesion.';


-- session_log: columnas normalizadas para las partes consultables (usuario, horario),
-- client_meta JSON para todo lo que varía según el cliente (IP, user-agent, device…)
CREATE TABLE session_log (
    id              CHAR(36)     NOT NULL DEFAULT (UUID()) PRIMARY KEY,
    user_id         CHAR(36)     NOT NULL,
    token_hash      VARCHAR(255) NOT NULL UNIQUE,
    expires_at      DATETIME     NOT NULL,
    closed_at       DATETIME,
    client_meta     JSON         NOT NULL DEFAULT ('{}'),
    created_at      DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT fk_session_user FOREIGN KEY (user_id) REFERENCES staff_user(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci
  COMMENT='RQF-01 - Token invalidado al cerrar sesion (closed_at). Expiracion por inactividad comparando expires_at con NOW(). client_meta es JSON porque los campos varian (mobile/desktop/proxy/IPv6...).';
-- Nota: token_hash se cambió de TEXT a VARCHAR(255) porque MySQL no permite
-- UNIQUE sobre columnas TEXT sin especificar una longitud de índice (prefix length).


-- =============================================================================
-- BLOQUE 3 — CIUDADANOS (denunciantes)
-- =============================================================================

-- citizen es una extensión 1:1 de person (rol CITIZEN). id = person.id;
-- identidad y contacto base (cédula, nombre, sexo, email, teléfono) ya están
-- garantizados NOT NULL en person — aquí solo vive lo específico del rol
-- de denunciante: dirección y datos demográficos opcionales.
CREATE TABLE citizen (
    id              CHAR(36)     NOT NULL PRIMARY KEY,

    -- Dirección — obligatoria, raramente cambia
    address         VARCHAR(250) NOT NULL,
    parish          VARCHAR(80),
    municipality    VARCHAR(80)  NOT NULL DEFAULT 'Páez',
    city            VARCHAR(80),

    -- Contacto extendido y datos demográficos opcionales → JSON
    -- Schema esperado por la app (no enforced en DB):
    -- {
    --   "home_phone":   "0257-...",
    --   "work_phone":   "0212-...",
    --   "birth_place":  "Acarigua, Portuguesa",
    --   "education_level": "TSU",           -- cat: PRIMARY/SECONDARY/TSU/UNIVERSITY/POSTGRAD
    --   "marital_status":  "SINGLE",        -- cat: SINGLE/MARRIED/DIVORCED/WIDOWED/COMMON_LAW
    --   "occupation":   "Contador"
    -- }
    contact_data    JSON         NOT NULL DEFAULT ('{}'),

    created_at      DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at      DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    CONSTRAINT fk_citizen_person       FOREIGN KEY (id) REFERENCES person(id),
    CONSTRAINT ck_citizen_address_len  CHECK (CHAR_LENGTH(address) >= 10)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci
  COMMENT='RQF-03.1 - Denunciante identificado, extension 1:1 de person (rol CITIZEN). No se permiten registros anonimos. contact_data almacena campos demograficos y de contacto opcionales que pueden crecer sin migraciones.';


-- =============================================================================
-- BLOQUE 4 — EXPEDIENTES (núcleo del sistema)
-- =============================================================================

-- Control de correlativo por año (unicidad concurrente sin gaps)
CREATE TABLE annual_sequence (
    year        int  PRIMARY KEY,
    last_number INT       NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci
  COMMENT='RQF-05.1 - Contador por ano fiscal. UPDATE ... con SELECT ... FOR UPDATE serializa la concurrencia. El trigger/la app lo usa para generar OAC-ANO-NNNNN.';


CREATE TABLE case_file (
    id                  CHAR(36)     NOT NULL DEFAULT (UUID()) PRIMARY KEY,

    -- Código institucional: OAC-2026-00001 (generado por trigger o por la app)
    case_number         VARCHAR(20)  NOT NULL UNIQUE,

    -- Token público para consulta ciudadana — opaco, no secuencial
    tracking_code       VARCHAR(36)  NOT NULL DEFAULT (UUID()) UNIQUE,

    -- Tipo y estado
    claim_type_id       int UNSIGNED NOT NULL,
    status_id           int UNSIGNED NOT NULL,

    -- Denunciante
    citizen_id          CHAR(36)     NOT NULL,

    -- Narración principal
    narrative           TEXT         NOT NULL,
    incident_date       DATE         NOT NULL,
    incident_location   VARCHAR(250),

    -- Presentado ante otra instancia
    other_instance      BOOLEAN      NOT NULL DEFAULT FALSE,
    other_instance_name VARCHAR(200),

    -- Consulta Popular Nacional (solo si tipo = COMPLAINT)
    is_popular_consultation BOOLEAN  NOT NULL DEFAULT FALSE,

    -- Declaración jurada
    sworn_declaration   BOOLEAN      NOT NULL DEFAULT FALSE,
    declaration_date    DATETIME,

    -- Protocolización (RQF-05)
    protocolized_at     DATETIME,
    qr_payload          TEXT,
    receipt_pdf_url     TEXT,

    -- Soportes físicos (RQF-06)
    physical_docs_deadline   DATE,                          -- protocolized_at + 5 días hábiles
    physical_docs_submitted  BOOLEAN  NOT NULL DEFAULT FALSE,
    physical_docs_date       DATE,
    physical_file_number     VARCHAR(50),
    analyst_notes            TEXT,

    -- Clasificación y derivación (RQF-06)
    irregularity_type_id     int UNSIGNED,
    referral_unit_id         int UNSIGNED,
    referred_at               DATETIME,
    referred_by                CHAR(36),
    referral_letter_url        TEXT,

    -- Integración SIGECE
    sigece_reference         VARCHAR(80),

    -- Recibido por (NULL = registro público en portal)
    received_by              CHAR(36),

    created_at          DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at          DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    CONSTRAINT fk_case_claim_type       FOREIGN KEY (claim_type_id) REFERENCES claim_type(id),
    CONSTRAINT fk_case_status           FOREIGN KEY (status_id) REFERENCES case_status(id),
    CONSTRAINT fk_case_citizen          FOREIGN KEY (citizen_id) REFERENCES citizen(id),
    CONSTRAINT fk_case_irregularity     FOREIGN KEY (irregularity_type_id) REFERENCES irregularity_type(id),
    CONSTRAINT fk_case_referral_unit    FOREIGN KEY (referral_unit_id) REFERENCES referral_unit(id),
    CONSTRAINT fk_case_referred_by      FOREIGN KEY (referred_by) REFERENCES staff_user(id),
    CONSTRAINT fk_case_received_by      FOREIGN KEY (received_by) REFERENCES staff_user(id),

    CONSTRAINT ck_narrative_len
        CHECK (CHAR_LENGTH(narrative) BETWEEN 50 AND 3000),
    CONSTRAINT ck_incident_date
        CHECK (incident_date <= CURRENT_DATE),
    CONSTRAINT ck_other_instance_name
        CHECK (other_instance = FALSE OR other_instance_name IS NOT NULL),
    CONSTRAINT ck_sworn_declaration
        CHECK (sworn_declaration = TRUE OR status_id = 1)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci
  COMMENT='Nucleo del sistema. tracking_code es el token publico opaco del ciudadano (RQF-07). case_number se genera con trigger/app usando annual_sequence.';


-- Bloque condicional Consulta Popular (1:0..1)
CREATE TABLE popular_consultation (
    case_file_id        CHAR(36)      NOT NULL PRIMARY KEY,
    project_name        VARCHAR(200)  NOT NULL,
    approval_date        DATE          NOT NULL,
    project_amount       DECIMAL(18,2) NOT NULL,
    funding_entity        VARCHAR(200)  NOT NULL,
    created_at            DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT fk_popular_consultation_case
        FOREIGN KEY (case_file_id) REFERENCES case_file(id) ON DELETE CASCADE,
    CONSTRAINT ck_pc_project_name_len   CHECK (CHAR_LENGTH(project_name) >= 4),
    CONSTRAINT ck_pc_approval_date      CHECK (approval_date <= CURRENT_DATE),
    CONSTRAINT ck_pc_project_amount     CHECK (project_amount > 0),
    CONSTRAINT ck_pc_funding_entity_len CHECK (CHAR_LENGTH(funding_entity) >= 3)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci
  COMMENT='RQF bloque 4 - Solo existe cuando case_file.is_popular_consultation = TRUE y claim_type = COMPLAINT. Relacion 1:0..1 evita columnas nullable en case_file.';


-- =============================================================================
-- BLOQUE 5 — SEÑALADOS
-- =============================================================================

CREATE TABLE respondent (
    id                  INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    case_file_id        CHAR(36)     NOT NULL,
    respondent_type_id  int UNSIGNED NOT NULL,

    -- Enlace opcional a person cuando el tipo de señalado es una persona
    -- identificable (NATURAL_PERSON, JUSTICE_OF_PEACE). NULL para tipos que
    -- no son personas (LEGAL_ENTITY, GOV_AGENCY, COMMUNE, COMMUNAL_COUNCIL,
    -- OTHER), que siguen usando solo attributes. Cuando se enlaza, la app
    -- debe registrar el rol RESPONDENT en person_role para ese person_id.
    person_id           CHAR(36),

    -- Mínimo normalizado: ubicación (siempre obligatoria y consultable)
    location            VARCHAR(250) NOT NULL,

    -- Campos de identificación variables según el tipo
    -- Validados en app contra respondent_type.field_schema
    -- Ejemplo para NATURAL_PERSON: {"id_number": "V-12345678", "full_name": "Juan Pérez"}
    -- Ejemplo para COMMUNE:        {"commune_name": "El Palito", "situr_code": "P-1234"}
    attributes          JSON         NOT NULL DEFAULT ('{}'),

    created_at          DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT fk_respondent_case   FOREIGN KEY (case_file_id) REFERENCES case_file(id) ON DELETE CASCADE,
    CONSTRAINT fk_respondent_type   FOREIGN KEY (respondent_type_id) REFERENCES respondent_type(id),
    CONSTRAINT fk_respondent_person FOREIGN KEY (person_id) REFERENCES person(id),
    CONSTRAINT ck_respondent_location_len CHECK (CHAR_LENGTH(location) >= 5)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci
  COMMENT='RQF-03.2 - Al menos 1 senalado por expediente (enforced en app). attributes contiene los campos definidos por respondent_type.field_schema. La DB no valida el schema interno del JSON; eso es responsabilidad de la app.';

-- MySQL no soporta índices GIN sobre JSON. Si necesitas buscar por una clave
-- específica dentro de attributes (p. ej. id_number), crea una columna generada
-- e indícala, por ejemplo:
--
-- ALTER TABLE respondent
--   ADD COLUMN attr_id_number VARCHAR(20)
--     GENERATED ALWAYS AS (JSON_UNQUOTE(JSON_EXTRACT(attributes, '$.id_number'))) STORED;
-- CREATE INDEX idx_respondent_attr_id_number ON respondent (attr_id_number);
--
-- Repite el patrón para cada clave que necesites buscar frecuentemente (rif, situr_code, etc.).


-- =============================================================================
-- BLOQUE 6 — EVIDENCIAS Y DOCUMENTOS
-- =============================================================================

-- Archivos digitales adjuntos (RQF-04.1 / RQF-04.3)
CREATE TABLE evidence_file (
    id              CHAR(36)     NOT NULL DEFAULT (UUID()) PRIMARY KEY,
    case_file_id    CHAR(36)     NOT NULL,
    original_name   VARCHAR(255) NOT NULL,
    mime_type       VARCHAR(100) NOT NULL,
    size_bytes      INT          NOT NULL,
    storage_url     TEXT         NOT NULL,
    uploaded_by     CHAR(36),                                -- NULL = ciudadano
    created_at      DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT fk_evidence_case        FOREIGN KEY (case_file_id) REFERENCES case_file(id) ON DELETE CASCADE,
    CONSTRAINT fk_evidence_uploaded_by FOREIGN KEY (uploaded_by) REFERENCES staff_user(id),
    CONSTRAINT ck_evidence_size CHECK (size_bytes <= 10485760)    -- 10 MB
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci
  COMMENT='RQF-04.1 / RQF-04.3 - Archivos de soporte. 10 MB enforced en CHECK y en app. storage_url apunta al sistema de almacenamiento externo (S3, MinIO, etc.).';


-- Checklist de documentos físicos consignados (RQF-04.2)
CREATE TABLE case_physical_doc (
    case_file_id          CHAR(36)     NOT NULL,
    physical_doc_type_id  int UNSIGNED NOT NULL,
    other_description     VARCHAR(200),                       -- solo para tipo OTHER
    checked_at             DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,

    PRIMARY KEY (case_file_id, physical_doc_type_id),
    CONSTRAINT fk_case_physical_doc_case FOREIGN KEY (case_file_id) REFERENCES case_file(id) ON DELETE CASCADE,
    CONSTRAINT fk_case_physical_doc_type FOREIGN KEY (physical_doc_type_id) REFERENCES physical_doc_type(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;


-- Minutas físicas digitalizadas (RQF-06.3)
CREATE TABLE scanned_minute (
    id              CHAR(36)     NOT NULL DEFAULT (UUID()) PRIMARY KEY,
    case_file_id    CHAR(36)     NOT NULL,
    file_name       VARCHAR(255) NOT NULL,
    storage_url     TEXT         NOT NULL,
    uploaded_by     CHAR(36)     NOT NULL,
    created_at      DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT fk_scanned_minute_case        FOREIGN KEY (case_file_id) REFERENCES case_file(id) ON DELETE CASCADE,
    CONSTRAINT fk_scanned_minute_uploaded_by FOREIGN KEY (uploaded_by) REFERENCES staff_user(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;


-- =============================================================================
-- BLOQUE 7 — AUDITORÍA Y TRAZABILIDAD
-- =============================================================================

-- Historial de cambios de estado — fuente pública (RQF-07.2) e interna (RQF-13.2)
CREATE TABLE case_status_log (
    id              BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    case_file_id    CHAR(36)     NOT NULL,
    previous_status int UNSIGNED,                        -- NULL en el primero
    new_status      int UNSIGNED NOT NULL,
    changed_by      CHAR(36),                                 -- NULL = sistema
    changed_at      DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT fk_status_log_case      FOREIGN KEY (case_file_id) REFERENCES case_file(id),
    CONSTRAINT fk_status_log_prev      FOREIGN KEY (previous_status) REFERENCES case_status(id),
    CONSTRAINT fk_status_log_new       FOREIGN KEY (new_status) REFERENCES case_status(id),
    CONSTRAINT fk_status_log_changedby FOREIGN KEY (changed_by) REFERENCES staff_user(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci
  COMMENT='RQF-07.2 / RQF-13.2 - La vista publica solo muestra new_status + changed_at. La vista interna incluye changed_by y el historial completo.';

CREATE INDEX idx_status_log_case ON case_status_log (case_file_id, changed_at DESC);


-- Acciones de analistas — consultable/filtrable por tipo, fecha y actor (RQF-13.2)
-- action_type normalizado porque es el eje principal de filtrado en SQL.
-- payload JSON para el contenido variable de cada tipo de acción.
--
-- Estructura de payload por action_type:
--   CLASSIFICATION  → {"irregularity_type_id": 3, "notes": "..."}
--   REFERRAL        → {"unit": "INVESTIGATIVE_POWER", "letter_url": "..."}
--   STATUS_CHANGE   → {"from": "IN_REVIEW", "to": "PROCESSED", "reason": "..."}
--   NOTE            → {"text": "..."}
--   DOCUMENT_CHECK  → {"docs_submitted": true, "submission_date": "2026-06-24"}
CREATE TABLE case_action (
    id              BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    case_file_id    CHAR(36)     NOT NULL,
    user_id         CHAR(36)     NOT NULL,
    action_type     VARCHAR(30)  NOT NULL,
    payload         JSON         NOT NULL DEFAULT ('{}'),
    performed_at    DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT fk_case_action_case FOREIGN KEY (case_file_id) REFERENCES case_file(id),
    CONSTRAINT fk_case_action_user FOREIGN KEY (user_id) REFERENCES staff_user(id),
    CONSTRAINT ck_case_action_type CHECK (action_type IN (
        'CLASSIFICATION','REFERRAL','STATUS_CHANGE',
        'NOTE','DOCUMENT_CHECK','OTHER'
    ))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci
  COMMENT='RQF-13.2 - Consultable por action_type, performed_at y user_id. payload contiene el detalle variable segun el tipo de accion. Nunca expuesto en la vista publica del ciudadano.';

CREATE INDEX idx_case_action_case ON case_action (case_file_id, performed_at DESC);
CREATE INDEX idx_case_action_type ON case_action (action_type, performed_at DESC);
CREATE INDEX idx_case_action_user ON case_action (user_id, performed_at DESC);
-- Índice sobre payload (JSON): igual que en respondent.attributes, MySQL no
-- soporta índices GIN. Si necesitas filtrar por una clave concreta del payload,
-- crea una columna generada indexada sobre esa clave específica.


-- Bitácora de cambios de privilegios (RQF-09.3) — inmutable
CREATE TABLE access_audit_log (
    id                  BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    affected_user_id    CHAR(36)     NOT NULL,
    module_id           int UNSIGNED NOT NULL,
    admin_id            CHAR(36)     NOT NULL,
    action              VARCHAR(20)  NOT NULL,
    before_state        JSON,
    after_state         JSON,
    logged_at           DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT fk_access_audit_affected FOREIGN KEY (affected_user_id) REFERENCES staff_user(id),
    CONSTRAINT fk_access_audit_module   FOREIGN KEY (module_id) REFERENCES app_module(id),
    CONSTRAINT fk_access_audit_admin    FOREIGN KEY (admin_id) REFERENCES staff_user(id),
    CONSTRAINT ck_access_audit_action   CHECK (action IN ('GRANT','REVOKE','MODIFY'))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci
  COMMENT='RQF-09.3 - Solo INSERT. Nunca UPDATE ni DELETE. before_state/after_state son JSON porque son snapshots de staff_privilege, que puede agregar columnas. (Restringir en la app / con privilegios de usuario de DB, MySQL no tiene reglas nativas anti-UPDATE/DELETE como Postgres RULE.)';


-- Intentos de acceso no autorizado (RQF-09)
CREATE TABLE unauthorized_access_log (
    id              BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id         CHAR(36),
    module_id       int UNSIGNED,
    endpoint        VARCHAR(200),
    client_meta     JSON         NOT NULL DEFAULT ('{}'),     -- ip, user_agent…
    logged_at       DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT fk_unauth_access_user   FOREIGN KEY (user_id) REFERENCES staff_user(id),
    CONSTRAINT fk_unauth_access_module FOREIGN KEY (module_id) REFERENCES app_module(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;


-- =============================================================================
-- BLOQUE 8 — CMS (RQF-11)
-- =============================================================================

CREATE TABLE cms_content (
    id              CHAR(36)     NOT NULL DEFAULT (UUID()) PRIMARY KEY,
    content_type_id int UNSIGNED NOT NULL,
    title           VARCHAR(200) NOT NULL,
    body            TEXT         NOT NULL,
    published       BOOLEAN      NOT NULL DEFAULT FALSE,
    author_id       CHAR(36)     NOT NULL,
    published_at    DATETIME,
    unpublished_at  DATETIME,
    created_at      DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at      DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    CONSTRAINT fk_cms_content_type   FOREIGN KEY (content_type_id) REFERENCES cms_content_type(id),
    CONSTRAINT fk_cms_content_author FOREIGN KEY (author_id) REFERENCES staff_user(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Historial de versiones para previsualización y auditoría de autoría (RQF-11.3 / RQF-11.4)
CREATE TABLE cms_content_version (
    id              BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    content_id      CHAR(36)     NOT NULL,
    title           VARCHAR(200) NOT NULL,
    body            TEXT         NOT NULL,
    modified_by     CHAR(36)     NOT NULL,
    modified_at     DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT fk_cms_version_content FOREIGN KEY (content_id) REFERENCES cms_content(id),
    CONSTRAINT fk_cms_version_author  FOREIGN KEY (modified_by) REFERENCES staff_user(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;


-- =============================================================================
-- BLOQUE 9 — CONSTRAINTS DIFERIDOS
-- =============================================================================
-- Los catálogos del Bloque 1 llevan updated_by referenciando a staff_user,
-- pero staff_user se crea despues (Bloque 2) porque a su vez depende de
-- person y job_position. Se agregan aquí en vez de reordenar todo el script.

ALTER TABLE claim_type        ADD CONSTRAINT fk_claim_type_updated_by        FOREIGN KEY (updated_by) REFERENCES staff_user(id);
ALTER TABLE irregularity_type ADD CONSTRAINT fk_irregularity_type_updated_by FOREIGN KEY (updated_by) REFERENCES staff_user(id);
ALTER TABLE case_status       ADD CONSTRAINT fk_case_status_updated_by       FOREIGN KEY (updated_by) REFERENCES staff_user(id);
ALTER TABLE referral_unit     ADD CONSTRAINT fk_referral_unit_updated_by     FOREIGN KEY (updated_by) REFERENCES staff_user(id);
ALTER TABLE respondent_type   ADD CONSTRAINT fk_respondent_type_updated_by   FOREIGN KEY (updated_by) REFERENCES staff_user(id);
ALTER TABLE id_document_type  ADD CONSTRAINT fk_id_document_type_updated_by  FOREIGN KEY (updated_by) REFERENCES staff_user(id);
ALTER TABLE physical_doc_type ADD CONSTRAINT fk_physical_doc_type_updated_by FOREIGN KEY (updated_by) REFERENCES staff_user(id);
ALTER TABLE job_position      ADD CONSTRAINT fk_job_position_updated_by      FOREIGN KEY (updated_by) REFERENCES staff_user(id);
ALTER TABLE app_module        ADD CONSTRAINT fk_app_module_updated_by        FOREIGN KEY (updated_by) REFERENCES staff_user(id);
ALTER TABLE cms_content_type  ADD CONSTRAINT fk_cms_content_type_updated_by  FOREIGN KEY (updated_by) REFERENCES staff_user(id);
ALTER TABLE person_role_type  ADD CONSTRAINT fk_person_role_type_updated_by  FOREIGN KEY (updated_by) REFERENCES staff_user(id);