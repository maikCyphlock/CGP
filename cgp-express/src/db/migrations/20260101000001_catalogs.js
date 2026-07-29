/**
 * Bloque 1 — Catálogos. Ported from contraloria.sql. Uses knex's schema
 * builder only (no raw SQL) so it runs unchanged on sqlite3 and pg.
 * updated_by FKs to staff_user are added later (migration 20260101000006)
 * since staff_user does not exist yet, mirroring the deferred constraints
 * block at the bottom of the original .sql file.
 */
exports.up = async function (knex) {
  await knex.schema
    .createTable('claim_type', (t) => {
      t.increments('id').unsigned().primary();
      t.string('code', 30).notNullable().unique();
      t.string('name', 80).notNullable();
      t.string('validation_level', 20).notNullable();
      t.boolean('active').notNullable().defaultTo(true);
      t.integer('sort_order').notNullable().defaultTo(0);
      t.timestamp('created_at').notNullable().defaultTo(knex.fn.now());
      t.timestamp('updated_at').notNullable().defaultTo(knex.fn.now());
      t.string('updated_by', 36);
    })
    .createTable('irregularity_type', (t) => {
      t.increments('id').unsigned().primary();
      t.string('code', 40).notNullable().unique();
      t.string('name', 120).notNullable();
      t.text('legal_basis');
      t.boolean('active').notNullable().defaultTo(true);
      t.integer('sort_order').notNullable().defaultTo(0);
      t.timestamp('created_at').notNullable().defaultTo(knex.fn.now());
      t.timestamp('updated_at').notNullable().defaultTo(knex.fn.now());
      t.string('updated_by', 36);
    })
    .createTable('case_status', (t) => {
      t.increments('id').unsigned().primary();
      t.string('code', 30).notNullable().unique();
      t.string('name', 60).notNullable();
      t.integer('sort_order').notNullable();
      t.boolean('is_terminal').notNullable().defaultTo(false);
      t.boolean('active').notNullable().defaultTo(true);
      t.timestamp('created_at').notNullable().defaultTo(knex.fn.now());
      t.timestamp('updated_at').notNullable().defaultTo(knex.fn.now());
      t.string('updated_by', 36);
    })
    .createTable('referral_unit', (t) => {
      t.increments('id').unsigned().primary();
      t.string('code', 30).notNullable().unique();
      t.string('name', 100).notNullable();
      t.boolean('active').notNullable().defaultTo(true);
      t.integer('sort_order').notNullable().defaultTo(0);
      t.timestamp('created_at').notNullable().defaultTo(knex.fn.now());
      t.timestamp('updated_at').notNullable().defaultTo(knex.fn.now());
      t.string('updated_by', 36);
    })
    .createTable('respondent_type', (t) => {
      t.increments('id').unsigned().primary();
      t.string('code', 30).notNullable().unique();
      t.string('name', 80).notNullable();
      t.json('field_schema').notNullable().defaultTo(JSON.stringify([]));
      t.boolean('active').notNullable().defaultTo(true);
      t.integer('sort_order').notNullable().defaultTo(0);
      t.timestamp('created_at').notNullable().defaultTo(knex.fn.now());
      t.timestamp('updated_at').notNullable().defaultTo(knex.fn.now());
      t.string('updated_by', 36);
    })
    .createTable('id_document_type', (t) => {
      t.increments('id').unsigned().primary();
      t.string('code', 10).notNullable().unique();
      t.string('name', 40).notNullable();
      t.boolean('active').notNullable().defaultTo(true);
      t.integer('sort_order').notNullable().defaultTo(0);
      t.timestamp('created_at').notNullable().defaultTo(knex.fn.now());
      t.timestamp('updated_at').notNullable().defaultTo(knex.fn.now());
      t.string('updated_by', 36);
    })
    .createTable('person_role_type', (t) => {
      t.increments('id').unsigned().primary();
      t.string('code', 20).notNullable().unique();
      t.string('name', 50).notNullable();
      t.boolean('active').notNullable().defaultTo(true);
      t.integer('sort_order').notNullable().defaultTo(0);
      t.timestamp('created_at').notNullable().defaultTo(knex.fn.now());
      t.timestamp('updated_at').notNullable().defaultTo(knex.fn.now());
      t.string('updated_by', 36);
    })
    .createTable('physical_doc_type', (t) => {
      t.increments('id').unsigned().primary();
      t.string('code', 40).notNullable().unique();
      t.string('name', 80).notNullable();
      t.boolean('active').notNullable().defaultTo(true);
      t.integer('sort_order').notNullable().defaultTo(0);
      t.timestamp('created_at').notNullable().defaultTo(knex.fn.now());
      t.timestamp('updated_at').notNullable().defaultTo(knex.fn.now());
      t.string('updated_by', 36);
    })
    .createTable('job_position', (t) => {
      t.increments('id').unsigned().primary();
      t.string('title', 100).notNullable();
      t.text('description');
      t.boolean('active').notNullable().defaultTo(true);
      t.integer('sort_order').notNullable().defaultTo(0);
      t.timestamp('created_at').notNullable().defaultTo(knex.fn.now());
      t.timestamp('updated_at').notNullable().defaultTo(knex.fn.now());
      t.string('updated_by', 36);
    })
    .createTable('app_module', (t) => {
      t.increments('id').unsigned().primary();
      t.string('code', 40).notNullable().unique();
      t.string('name', 80).notNullable();
      t.boolean('active').notNullable().defaultTo(true);
      t.integer('sort_order').notNullable().defaultTo(0);
      t.timestamp('created_at').notNullable().defaultTo(knex.fn.now());
      t.timestamp('updated_at').notNullable().defaultTo(knex.fn.now());
      t.string('updated_by', 36);
    })
    .createTable('cms_content_type', (t) => {
      t.increments('id').unsigned().primary();
      t.string('code', 30).notNullable().unique();
      t.string('name', 60).notNullable();
      t.boolean('active').notNullable().defaultTo(true);
      t.integer('sort_order').notNullable().defaultTo(0);
      t.timestamp('created_at').notNullable().defaultTo(knex.fn.now());
      t.timestamp('updated_at').notNullable().defaultTo(knex.fn.now());
      t.string('updated_by', 36);
    });
};

exports.down = async function (knex) {
  await knex.schema
    .dropTableIfExists('cms_content_type')
    .dropTableIfExists('app_module')
    .dropTableIfExists('job_position')
    .dropTableIfExists('physical_doc_type')
    .dropTableIfExists('person_role_type')
    .dropTableIfExists('id_document_type')
    .dropTableIfExists('respondent_type')
    .dropTableIfExists('referral_unit')
    .dropTableIfExists('case_status')
    .dropTableIfExists('irregularity_type')
    .dropTableIfExists('claim_type');
};
