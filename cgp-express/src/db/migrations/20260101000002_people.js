/**
 * Bloque 2/3 — person (shared identity), staff_user, citizen.
 * UUID PKs are generated in the app layer (uuid.v4()) rather than via a DB
 * default, since sqlite has no UUID() function — this keeps id generation
 * identical across both connectors.
 */
exports.up = async function (knex) {
  await knex.schema
    .createTable('person', (t) => {
      t.string('id', 36).primary();
      t.integer('id_doc_type_id').unsigned().notNullable().references('id').inTable('id_document_type');
      t.string('id_doc_number', 12).notNullable();
      t.string('first_name', 80).notNullable();
      t.string('last_name', 80).notNullable();
      t.string('sex', 1).notNullable();
      t.date('birth_date');
      t.string('email', 150).notNullable();
      t.string('mobile_phone', 15).notNullable();
      t.json('extended_data').notNullable().defaultTo(JSON.stringify({}));
      t.timestamp('created_at').notNullable().defaultTo(knex.fn.now());
      t.timestamp('updated_at').notNullable().defaultTo(knex.fn.now());
      t.unique(['id_doc_type_id', 'id_doc_number']);
    })
    .createTable('person_role', (t) => {
      t.string('person_id', 36).notNullable().references('id').inTable('person').onDelete('CASCADE');
      t.integer('role_type_id').unsigned().notNullable().references('id').inTable('person_role_type');
      t.boolean('active').notNullable().defaultTo(true);
      t.timestamp('granted_at').notNullable().defaultTo(knex.fn.now());
      t.timestamp('revoked_at');
      t.primary(['person_id', 'role_type_id']);
    })
    .createTable('staff_user', (t) => {
      t.string('id', 36).primary().references('id').inTable('person');
      t.integer('job_position_id').unsigned().notNullable().references('id').inTable('job_position');
      t.string('email', 150).notNullable().unique();
      t.text('password_hash').notNullable();
      t.boolean('active').notNullable().defaultTo(true);
      t.integer('failed_attempts').notNullable().defaultTo(0);
      t.timestamp('locked_until');
      t.timestamp('last_login');
      t.timestamp('created_at').notNullable().defaultTo(knex.fn.now());
      t.timestamp('updated_at').notNullable().defaultTo(knex.fn.now());
    })
    .createTable('staff_privilege', (t) => {
      t.increments('id').unsigned().primary();
      t.string('user_id', 36).notNullable().references('id').inTable('staff_user');
      t.integer('module_id').unsigned().notNullable().references('id').inTable('app_module');
      t.boolean('can_read').notNullable().defaultTo(false);
      t.boolean('can_write').notNullable().defaultTo(false);
      t.boolean('can_delete').notNullable().defaultTo(false);
      t.string('granted_by', 36).references('id').inTable('staff_user');
      t.timestamp('granted_at').notNullable().defaultTo(knex.fn.now());
      t.unique(['user_id', 'module_id']);
    })
    .createTable('session_log', (t) => {
      t.string('id', 36).primary();
      t.string('user_id', 36).notNullable().references('id').inTable('staff_user');
      t.string('token_hash', 255).notNullable().unique();
      t.timestamp('expires_at').notNullable();
      t.timestamp('closed_at');
      t.json('client_meta').notNullable().defaultTo(JSON.stringify({}));
      t.timestamp('created_at').notNullable().defaultTo(knex.fn.now());
    })
    .createTable('citizen', (t) => {
      t.string('id', 36).primary().references('id').inTable('person');
      t.string('address', 250).notNullable();
      t.string('parish', 80);
      t.string('municipality', 80).notNullable().defaultTo('Páez');
      t.string('city', 80);
      t.json('contact_data').notNullable().defaultTo(JSON.stringify({}));
      t.timestamp('created_at').notNullable().defaultTo(knex.fn.now());
      t.timestamp('updated_at').notNullable().defaultTo(knex.fn.now());
    });

  // Deferred FKs from Bloque 1 catalogs -> staff_user (mirrors the original .sql)
  const deferred = [
    'claim_type', 'irregularity_type', 'case_status', 'referral_unit',
    'respondent_type', 'id_document_type', 'physical_doc_type', 'job_position',
    'app_module', 'cms_content_type',
  ];
  for (const table of deferred) {
    await knex.schema.alterTable(table, (t) => {
      t.foreign('updated_by').references('id').inTable('staff_user');
    });
  }
};

exports.down = async function (knex) {
  const deferred = [
    'claim_type', 'irregularity_type', 'case_status', 'referral_unit',
    'respondent_type', 'id_document_type', 'physical_doc_type', 'job_position',
    'app_module', 'cms_content_type',
  ];
  for (const table of deferred) {
    await knex.schema.alterTable(table, (t) => {
      t.dropForeign('updated_by');
    });
  }
  await knex.schema
    .dropTableIfExists('citizen')
    .dropTableIfExists('session_log')
    .dropTableIfExists('staff_privilege')
    .dropTableIfExists('staff_user')
    .dropTableIfExists('person_role')
    .dropTableIfExists('person');
};
