/**
 * Bloque 7/8 — Auditoría/trazabilidad y CMS.
 */
exports.up = async function (knex) {
  await knex.schema
    .createTable('case_status_log', (t) => {
      t.increments('id').primary();
      t.string('case_file_id', 36).notNullable().references('id').inTable('case_file');
      t.integer('previous_status').unsigned().references('id').inTable('case_status');
      t.integer('new_status').unsigned().notNullable().references('id').inTable('case_status');
      t.string('changed_by', 36).references('id').inTable('staff_user');
      t.timestamp('changed_at').notNullable().defaultTo(knex.fn.now());
      t.index(['case_file_id', 'changed_at'], 'idx_status_log_case');
    })
    .createTable('case_action', (t) => {
      t.increments('id').primary();
      t.string('case_file_id', 36).notNullable().references('id').inTable('case_file');
      t.string('user_id', 36).notNullable().references('id').inTable('staff_user');
      t.string('action_type', 30).notNullable();
      t.json('payload').notNullable().defaultTo(JSON.stringify({}));
      t.timestamp('performed_at').notNullable().defaultTo(knex.fn.now());
      t.index(['case_file_id', 'performed_at'], 'idx_case_action_case');
      t.index(['action_type', 'performed_at'], 'idx_case_action_type');
      t.index(['user_id', 'performed_at'], 'idx_case_action_user');
    })
    .createTable('access_audit_log', (t) => {
      t.increments('id').primary();
      t.string('affected_user_id', 36).notNullable().references('id').inTable('staff_user');
      t.integer('module_id').unsigned().notNullable().references('id').inTable('app_module');
      t.string('admin_id', 36).notNullable().references('id').inTable('staff_user');
      t.string('action', 20).notNullable();
      t.json('before_state');
      t.json('after_state');
      t.timestamp('logged_at').notNullable().defaultTo(knex.fn.now());
    })
    .createTable('unauthorized_access_log', (t) => {
      t.increments('id').primary();
      t.string('user_id', 36).references('id').inTable('staff_user');
      t.integer('module_id').unsigned().references('id').inTable('app_module');
      t.string('endpoint', 200);
      t.json('client_meta').notNullable().defaultTo(JSON.stringify({}));
      t.timestamp('logged_at').notNullable().defaultTo(knex.fn.now());
    })
    .createTable('cms_content', (t) => {
      t.string('id', 36).primary();
      t.integer('content_type_id').unsigned().notNullable().references('id').inTable('cms_content_type');
      t.string('title', 200).notNullable();
      t.text('body').notNullable();
      t.boolean('published').notNullable().defaultTo(false);
      t.string('author_id', 36).notNullable().references('id').inTable('staff_user');
      t.timestamp('published_at');
      t.timestamp('unpublished_at');
      t.timestamp('created_at').notNullable().defaultTo(knex.fn.now());
      t.timestamp('updated_at').notNullable().defaultTo(knex.fn.now());
    })
    .createTable('cms_content_version', (t) => {
      t.increments('id').primary();
      t.string('content_id', 36).notNullable().references('id').inTable('cms_content');
      t.string('title', 200).notNullable();
      t.text('body').notNullable();
      t.string('modified_by', 36).notNullable().references('id').inTable('staff_user');
      t.timestamp('modified_at').notNullable().defaultTo(knex.fn.now());
    });
};

exports.down = async function (knex) {
  await knex.schema
    .dropTableIfExists('cms_content_version')
    .dropTableIfExists('cms_content')
    .dropTableIfExists('unauthorized_access_log')
    .dropTableIfExists('access_audit_log')
    .dropTableIfExists('case_action')
    .dropTableIfExists('case_status_log');
};
