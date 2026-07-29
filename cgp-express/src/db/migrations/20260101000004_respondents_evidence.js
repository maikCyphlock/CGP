/**
 * Bloque 5/6 — Señalados (respondent) y evidencias/documentos.
 */
exports.up = async function (knex) {
  await knex.schema
    .createTable('respondent', (t) => {
      t.increments('id').unsigned().primary();
      t.string('case_file_id', 36).notNullable().references('id').inTable('case_file').onDelete('CASCADE');
      t.integer('respondent_type_id').unsigned().notNullable().references('id').inTable('respondent_type');
      t.string('person_id', 36).references('id').inTable('person');
      t.string('location', 250).notNullable();
      t.json('attributes').notNullable().defaultTo(JSON.stringify({}));
      t.timestamp('created_at').notNullable().defaultTo(knex.fn.now());
    })
    .createTable('evidence_file', (t) => {
      t.string('id', 36).primary();
      t.string('case_file_id', 36).notNullable().references('id').inTable('case_file').onDelete('CASCADE');
      t.string('original_name', 255).notNullable();
      t.string('mime_type', 100).notNullable();
      t.integer('size_bytes').notNullable();
      t.text('storage_url').notNullable();
      t.string('uploaded_by', 36).references('id').inTable('staff_user');
      t.timestamp('created_at').notNullable().defaultTo(knex.fn.now());
    })
    .createTable('case_physical_doc', (t) => {
      t.string('case_file_id', 36).notNullable().references('id').inTable('case_file').onDelete('CASCADE');
      t.integer('physical_doc_type_id').unsigned().notNullable().references('id').inTable('physical_doc_type');
      t.string('other_description', 200);
      t.timestamp('checked_at').notNullable().defaultTo(knex.fn.now());
      t.primary(['case_file_id', 'physical_doc_type_id']);
    })
    .createTable('scanned_minute', (t) => {
      t.string('id', 36).primary();
      t.string('case_file_id', 36).notNullable().references('id').inTable('case_file').onDelete('CASCADE');
      t.string('file_name', 255).notNullable();
      t.text('storage_url').notNullable();
      t.string('uploaded_by', 36).notNullable().references('id').inTable('staff_user');
      t.timestamp('created_at').notNullable().defaultTo(knex.fn.now());
    });
};

exports.down = async function (knex) {
  await knex.schema
    .dropTableIfExists('scanned_minute')
    .dropTableIfExists('case_physical_doc')
    .dropTableIfExists('evidence_file')
    .dropTableIfExists('respondent');
};
