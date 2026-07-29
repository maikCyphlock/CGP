const bcrypt = require('bcryptjs');
const { v4: uuidv4 } = require('uuid');

/** Default super-admin so the admin panel is usable right after seeding. */
exports.seed = async function (knex) {
  const existing = await knex('staff_user').where({ email: 'admin@contraloriapaez.gob.ve' }).first();
  if (existing) return;

  const docType = await knex('id_document_type').where({ code: 'V' }).first();
  const jobPosition = await knex('job_position').where({ title: 'Administrador de Sistema' }).first();
  const roleType = await knex('person_role_type').where({ code: 'STAFF' }).first();

  const personId = uuidv4();
  await knex('person').insert({
    id: personId,
    id_doc_type_id: docType.id,
    id_doc_number: '00000000',
    first_name: 'Administrador',
    last_name: 'Sistema',
    sex: 'M',
    email: 'admin@contraloriapaez.gob.ve',
    mobile_phone: '0000000000',
  });

  await knex('person_role').insert({ person_id: personId, role_type_id: roleType.id });

  const passwordHash = bcrypt.hashSync('Admin123!', 10);
  await knex('staff_user').insert({
    id: personId,
    job_position_id: jobPosition.id,
    email: 'admin@contraloriapaez.gob.ve',
    password_hash: passwordHash,
    active: true,
  });

  const modules = await knex('app_module').select('id');
  await knex('staff_privilege').insert(
    modules.map((m) => ({
      user_id: personId,
      module_id: m.id,
      can_read: true,
      can_write: true,
      can_delete: true,
    }))
  );

  // eslint-disable-next-line no-console
  console.log('Seeded admin user -> admin@contraloriapaez.gob.ve / Admin123!');
};
