/**
 * Bloque 4 — Expedientes (case_file) + popular_consultation.
 * CHECK constraints from the original .sql (narrative length, incident_date
 * <= today, sworn_declaration, etc.) are not portable across sqlite/pg via
 * knex, so they are enforced in the app layer (src/services) instead.
 */
exports.up = async function (knex) {
  await knex.schema
    .createTable('annual_sequence', (t) => {
      t.integer('year').primary();
      t.integer('last_number').notNullable().defaultTo(0);
    })
    .createTable('case_file', (t) => {
      t.string('id', 36).primary();
      t.string('case_number', 20).notNullable().unique();
      t.string('tracking_code', 36).notNullable().unique();
      t.integer('claim_type_id').unsigned().notNullable().references('id').inTable('claim_type');
      t.integer('status_id').unsigned().notNullable().references('id').inTable('case_status');
      t.string('citizen_id', 36).notNullable().references('id').inTable('citizen');
      t.text('narrative').notNullable();
      t.date('incident_date').notNullable();
      t.string('incident_location', 250);
      t.boolean('other_instance').notNullable().defaultTo(false);
      t.string('other_instance_name', 200);
      t.boolean('is_popular_consultation').notNullable().defaultTo(false);
      t.boolean('sworn_declaration').notNullable().defaultTo(false);
      t.timestamp('declaration_date');
      t.timestamp('protocolized_at');
      t.text('qr_payload');
      t.text('receipt_pdf_url');
      t.date('physical_docs_deadline');
      t.boolean('physical_docs_submitted').notNullable().defaultTo(false);
      t.date('physical_docs_date');
      t.string('physical_file_number', 50);
      t.text('analyst_notes');
      t.integer('irregularity_type_id').unsigned().references('id').inTable('irregularity_type');
      t.integer('referral_unit_id').unsigned().references('id').inTable('referral_unit');
      t.timestamp('referred_at');
      t.string('referred_by', 36).references('id').inTable('staff_user');
      t.text('referral_letter_url');
      t.string('sigece_reference', 80);
      t.string('received_by', 36).references('id').inTable('staff_user');
      t.timestamp('created_at').notNullable().defaultTo(knex.fn.now());
      t.timestamp('updated_at').notNullable().defaultTo(knex.fn.now());
    })
    .createTable('popular_consultation', (t) => {
      t.string('case_file_id', 36).primary().references('id').inTable('case_file').onDelete('CASCADE');
      t.string('project_name', 200).notNullable();
      t.date('approval_date').notNullable();
      t.decimal('project_amount', 18, 2).notNullable();
      t.string('funding_entity', 200).notNullable();
      t.timestamp('created_at').notNullable().defaultTo(knex.fn.now());
    });
};

exports.down = async function (knex) {
  await knex.schema
    .dropTableIfExists('popular_consultation')
    .dropTableIfExists('case_file')
    .dropTableIfExists('annual_sequence');
};
