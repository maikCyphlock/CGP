/**
 * Config-driven definitions for the simple lookup-table catalogs (Bloque 1
 * of contraloria.sql). One generic CRUD (src/routes/adminCatalogs.js) reads
 * this to render/list/create/update/delete any of them without per-table code.
 */
module.exports = {
  'claim-type': {
    table: 'claim_type', label: 'Tipos de Trámite', module: 'CATALOGS',
    fields: [
      { name: 'code', label: 'Código', type: 'text', required: true },
      { name: 'name', label: 'Nombre', type: 'text', required: true },
      { name: 'validation_level', label: 'Nivel de validación', type: 'select', options: ['STRICT', 'BASIC', 'AUTOMATED'], required: true },
      { name: 'sort_order', label: 'Orden', type: 'number' },
      { name: 'active', label: 'Activo', type: 'checkbox' },
    ],
  },
  'irregularity-type': {
    table: 'irregularity_type', label: 'Tipos de Irregularidad', module: 'CATALOGS',
    fields: [
      { name: 'code', label: 'Código', type: 'text', required: true },
      { name: 'name', label: 'Nombre', type: 'text', required: true },
      { name: 'legal_basis', label: 'Base legal', type: 'textarea' },
      { name: 'sort_order', label: 'Orden', type: 'number' },
      { name: 'active', label: 'Activo', type: 'checkbox' },
    ],
  },
  'case-status': {
    table: 'case_status', label: 'Estados de Expediente', module: 'CATALOGS',
    fields: [
      { name: 'code', label: 'Código', type: 'text', required: true },
      { name: 'name', label: 'Nombre', type: 'text', required: true },
      { name: 'sort_order', label: 'Orden', type: 'number', required: true },
      { name: 'is_terminal', label: 'Estado terminal', type: 'checkbox' },
      { name: 'active', label: 'Activo', type: 'checkbox' },
    ],
  },
  'referral-unit': {
    table: 'referral_unit', label: 'Unidades de Derivación', module: 'CATALOGS',
    fields: [
      { name: 'code', label: 'Código', type: 'text', required: true },
      { name: 'name', label: 'Nombre', type: 'text', required: true },
      { name: 'sort_order', label: 'Orden', type: 'number' },
      { name: 'active', label: 'Activo', type: 'checkbox' },
    ],
  },
  'respondent-type': {
    table: 'respondent_type', label: 'Tipos de Señalado', module: 'CATALOGS',
    fields: [
      { name: 'code', label: 'Código', type: 'text', required: true },
      { name: 'name', label: 'Nombre', type: 'text', required: true },
      { name: 'field_schema', label: 'Esquema de campos (JSON)', type: 'json' },
      { name: 'sort_order', label: 'Orden', type: 'number' },
      { name: 'active', label: 'Activo', type: 'checkbox' },
    ],
  },
  'id-document-type': {
    table: 'id_document_type', label: 'Tipos de Documento de Identidad', module: 'CATALOGS',
    fields: [
      { name: 'code', label: 'Código', type: 'text', required: true },
      { name: 'name', label: 'Nombre', type: 'text', required: true },
      { name: 'sort_order', label: 'Orden', type: 'number' },
      { name: 'active', label: 'Activo', type: 'checkbox' },
    ],
  },
  'physical-doc-type': {
    table: 'physical_doc_type', label: 'Documentos Físicos', module: 'CATALOGS',
    fields: [
      { name: 'code', label: 'Código', type: 'text', required: true },
      { name: 'name', label: 'Nombre', type: 'text', required: true },
      { name: 'sort_order', label: 'Orden', type: 'number' },
      { name: 'active', label: 'Activo', type: 'checkbox' },
    ],
  },
  'job-position': {
    table: 'job_position', label: 'Cargos del Personal', module: 'CATALOGS',
    fields: [
      { name: 'title', label: 'Título', type: 'text', required: true },
      { name: 'description', label: 'Descripción', type: 'textarea' },
      { name: 'sort_order', label: 'Orden', type: 'number' },
      { name: 'active', label: 'Activo', type: 'checkbox' },
    ],
  },
  'app-module': {
    table: 'app_module', label: 'Módulos del Sistema', module: 'CATALOGS',
    fields: [
      { name: 'code', label: 'Código', type: 'text', required: true },
      { name: 'name', label: 'Nombre', type: 'text', required: true },
      { name: 'sort_order', label: 'Orden', type: 'number' },
      { name: 'active', label: 'Activo', type: 'checkbox' },
    ],
  },
  'cms-content-type': {
    table: 'cms_content_type', label: 'Tipos de Contenido CMS', module: 'CATALOGS',
    fields: [
      { name: 'code', label: 'Código', type: 'text', required: true },
      { name: 'name', label: 'Nombre', type: 'text', required: true },
      { name: 'sort_order', label: 'Orden', type: 'number' },
      { name: 'active', label: 'Activo', type: 'checkbox' },
    ],
  },
};
