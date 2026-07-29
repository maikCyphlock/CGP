/**
 * Catalog seed data ported from contraloria.sql.
 * Idempotent by design: inserts only rows whose key doesn't exist yet, never
 * deletes. A del()+insert() here would break once real data exists (case_file
 * etc. hold FKs into these catalogs), and would also wipe any edits made
 * later through the admin CRUD.
 */
async function insertMissing(knex, table, keyField, rows) {
  const existing = await knex(table).pluck(keyField);
  const existingSet = new Set(existing);
  const toInsert = rows.filter((r) => !existingSet.has(r[keyField]));
  if (toInsert.length > 0) await knex(table).insert(toInsert);
}

exports.seed = async function (knex) {
  await insertMissing(knex, 'claim_type', 'code', [
    { code: 'COMPLAINT', name: 'Denuncia', validation_level: 'STRICT', sort_order: 1 },
    { code: 'GRIEVANCE', name: 'Queja', validation_level: 'BASIC', sort_order: 2 },
    { code: 'CLAIM', name: 'Reclamo', validation_level: 'BASIC', sort_order: 3 },
    { code: 'PETITION', name: 'Petición', validation_level: 'AUTOMATED', sort_order: 4 },
    { code: 'SUGGESTION', name: 'Sugerencia', validation_level: 'AUTOMATED', sort_order: 5 },
  ]);

  await insertMissing(knex, 'irregularity_type', 'code', [
    { code: 'MISUSE_OF_FUNDS', name: 'Uso indebido de fondos públicos', sort_order: 1 },
    { code: 'ABUSE_OF_AUTHORITY', name: 'Abuso de autoridad', sort_order: 2 },
    { code: 'PROCUREMENT_IRREGULARITY', name: 'Irregularidad en contrataciones', sort_order: 3 },
    { code: 'NEGLIGENCE', name: 'Negligencia administrativa', sort_order: 4 },
    { code: 'OTHER', name: 'Otro', sort_order: 5 },
  ]);

  await insertMissing(knex, 'case_status', 'code', [
    { code: 'RECEIVED', name: 'Recibido', sort_order: 1, is_terminal: false },
    { code: 'IN_REVIEW', name: 'En Revisión', sort_order: 2, is_terminal: false },
    { code: 'PROCESSED', name: 'Procesado', sort_order: 3, is_terminal: false },
    { code: 'REFERRED', name: 'Derivado', sort_order: 4, is_terminal: false },
    { code: 'ARCHIVED', name: 'Archivado', sort_order: 5, is_terminal: true },
    { code: 'INVALIDATED', name: 'Invalidado', sort_order: 6, is_terminal: true },
  ]);

  await insertMissing(knex, 'referral_unit', 'code', [
    { code: 'SUBSEQUENT_CONTROL', name: 'Control Posterior' },
    { code: 'INVESTIGATIVE_POWER', name: 'Potestad Investigativa' },
  ]);

  await insertMissing(knex, 'respondent_type', 'code', [
    {
      code: 'NATURAL_PERSON', name: 'Persona Natural',
      field_schema: JSON.stringify([
        { key: 'id_number', label: 'Cédula', type: 'string', required: true },
        { key: 'full_name', label: 'Nombre completo', type: 'string', required: true },
      ]),
    },
    {
      code: 'LEGAL_ENTITY', name: 'Persona Jurídica',
      field_schema: JSON.stringify([
        { key: 'rif', label: 'RIF', type: 'string', required: true },
        { key: 'full_name', label: 'Razón social', type: 'string', required: true },
      ]),
    },
    {
      code: 'GOV_AGENCY', name: 'Órgano o Ente',
      field_schema: JSON.stringify([
        { key: 'rif', label: 'RIF', type: 'string', required: true },
        { key: 'full_name', label: 'Denominación', type: 'string', required: true },
      ]),
    },
    {
      code: 'COMMUNE', name: 'Comuna',
      field_schema: JSON.stringify([
        { key: 'commune_name', label: 'Nombre de la comuna', type: 'string', required: true },
        { key: 'situr_code', label: 'Código SITUR', type: 'string', required: true },
      ]),
    },
    {
      code: 'COMMUNAL_COUNCIL', name: 'Consejo Comunal',
      field_schema: JSON.stringify([
        { key: 'commune_name', label: 'Nombre del consejo comunal', type: 'string', required: true },
        { key: 'situr_code', label: 'Código SITUR', type: 'string', required: true },
      ]),
    },
    {
      code: 'JUSTICE_OF_PEACE', name: 'Juez de Paz',
      field_schema: JSON.stringify([
        { key: 'id_number', label: 'Cédula', type: 'string', required: true },
        { key: 'full_name', label: 'Nombre completo', type: 'string', required: true },
      ]),
    },
    {
      code: 'OTHER', name: 'Otro',
      field_schema: JSON.stringify([
        { key: 'description', label: 'Descripción', type: 'string', required: true },
      ]),
    },
  ]);

  await insertMissing(knex, 'id_document_type', 'code', [
    { code: 'V', name: 'Cédula Venezolana', sort_order: 1 },
    { code: 'E', name: 'Cédula Extranjera', sort_order: 2 },
    { code: 'PASSPORT', name: 'Pasaporte', sort_order: 3 },
  ]);

  await insertMissing(knex, 'person_role_type', 'code', [
    { code: 'CITIZEN', name: 'Ciudadano / Denunciante', sort_order: 1 },
    { code: 'RESPONDENT', name: 'Señalado', sort_order: 2 },
    { code: 'STAFF', name: 'Personal Interno', sort_order: 3 },
  ]);

  await insertMissing(knex, 'physical_doc_type', 'code', [
    { code: 'WITNESS_ID', name: 'Copia C.I. del testigo', sort_order: 1 },
    { code: 'CLAIMANT_ID', name: 'Copia C.I. del denunciante', sort_order: 2 },
    { code: 'COVER_LETTER', name: 'Carta de exposición de motivos', sort_order: 3 },
    { code: 'PHOTOS', name: 'Fotografías', sort_order: 4 },
    { code: 'VIDEO', name: 'Video', sort_order: 5 },
    { code: 'AUDIO', name: 'Grabación de voz', sort_order: 6 },
    { code: 'WRITTEN_TESTIMONY', name: 'Testimonio escrito', sort_order: 7 },
    { code: 'OTHER', name: 'Otros', sort_order: 8 },
  ]);

  await insertMissing(knex, 'job_position', 'title', [
    { title: 'Contralor Municipal', description: 'Máxima autoridad de la Contraloría' },
    { title: 'Auditor', description: 'Personal de auditoría y control posterior' },
    { title: 'Analista de Atención Ciudadana', description: 'Recepción y análisis de expedientes' },
    { title: 'Administrador de Sistema', description: 'Gestión de usuarios, accesos y CMS' },
  ]);

  await insertMissing(knex, 'app_module', 'code', [
    { code: 'CASES', name: 'Gestión de Expedientes', sort_order: 1 },
    { code: 'CLASSIFY', name: 'Clasificación y Derivación', sort_order: 2 },
    { code: 'USERS', name: 'Gestión de Usuarios', sort_order: 3 },
    { code: 'ACCESS', name: 'Gestión de Accesos', sort_order: 4 },
    { code: 'STATS', name: 'Criterios Estadísticos', sort_order: 5 },
    { code: 'CMS', name: 'Gestión de Página Web', sort_order: 6 },
    { code: 'CATALOGS', name: 'Gestión de Catálogos', sort_order: 7 },
    { code: 'REPORTS', name: 'Reportes e Informes', sort_order: 8 },
  ]);

  await insertMissing(knex, 'cms_content_type', 'code', [
    { code: 'NEWS', name: 'Noticia institucional', sort_order: 1 },
    { code: 'MISSION', name: 'Misión', sort_order: 2 },
    { code: 'VISION', name: 'Visión', sort_order: 3 },
    { code: 'ORG_CHART', name: 'Organigrama', sort_order: 4 },
    { code: 'BULLETIN', name: 'Cartelera digital', sort_order: 5 },
    { code: 'CITIZEN_GUIDE', name: 'Guía ciudadana', sort_order: 6 },
  ]);
};
