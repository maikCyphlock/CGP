const express = require('express');
const bcrypt = require('bcryptjs');
const { v4: uuidv4 } = require('uuid');
const db = require('../db');
const { requirePrivilege } = require('../middleware/auth');

const router = express.Router();

router.get('/', requirePrivilege('USERS', 'can_read'), async (req, res) => {
  const users = await db('staff_user')
    .join('person', 'staff_user.id', 'person.id')
    .join('job_position', 'staff_user.job_position_id', 'job_position.id')
    .select('staff_user.*', 'person.first_name', 'person.last_name', 'job_position.title as job_title');
  res.render('admin/users_list', { title: 'Usuarios', active: 'users', users });
});

router.get('/new', requirePrivilege('USERS', 'can_write'), async (req, res) => {
  const jobPositions = await db('job_position').where({ active: true });
  const docTypes = await db('id_document_type').where({ active: true });
  res.render('admin/users_new', { title: 'Nuevo Usuario', active: 'users', jobPositions, docTypes, error: null });
});

router.post('/', requirePrivilege('USERS', 'can_write'), async (req, res) => {
  const b = req.body;
  const jobPositions = await db('job_position').where({ active: true });
  const docTypes = await db('id_document_type').where({ active: true });
  const renderError = (msg) => res.status(400).render('admin/users_new', { title: 'Nuevo Usuario', active: 'users', jobPositions, docTypes, error: msg });

  const existing = await db('staff_user').where({ email: b.email }).first();
  if (existing) return renderError('Ya existe un usuario con ese correo.');

  const personId = uuidv4();
  await db.transaction(async (trx) => {
    await trx('person').insert({
      id: personId,
      id_doc_type_id: b.id_doc_type_id,
      id_doc_number: b.id_doc_number,
      first_name: b.first_name,
      last_name: b.last_name,
      sex: b.sex,
      email: b.email,
      mobile_phone: b.mobile_phone,
    });
    const staffRole = await trx('person_role_type').where({ code: 'STAFF' }).first();
    await trx('person_role').insert({ person_id: personId, role_type_id: staffRole.id });
    await trx('staff_user').insert({
      id: personId,
      job_position_id: b.job_position_id,
      email: b.email,
      password_hash: bcrypt.hashSync(b.password, 10),
      active: true,
    });
  });

  res.redirect(`/admin/users/${personId}`);
});

router.get('/:id', requirePrivilege('USERS', 'can_read'), async (req, res) => {
  const user = await db('staff_user')
    .join('person', 'staff_user.id', 'person.id')
    .join('job_position', 'staff_user.job_position_id', 'job_position.id')
    .where('staff_user.id', req.params.id)
    .select('staff_user.*', 'person.first_name', 'person.last_name', 'job_position.title as job_title')
    .first();
  if (!user) return res.status(404).send('No encontrado');

  const modules = await db('app_module').orderBy('sort_order');
  const privileges = await db('staff_privilege').where({ user_id: req.params.id });
  const privByModule = {};
  privileges.forEach((p) => { privByModule[p.module_id] = p; });

  res.render('admin/users_detail', { title: user.email, active: 'users', user, modules, privByModule });
});

router.post('/:id/toggle', requirePrivilege('USERS', 'can_write'), async (req, res) => {
  await db('staff_user').where({ id: req.params.id }).update({ active: req.body.active === 'true' });
  res.redirect(`/admin/users/${req.params.id}`);
});

router.post('/:id/privileges/:moduleId', requirePrivilege('ACCESS', 'can_write'), async (req, res) => {
  const existing = await db('staff_privilege').where({ user_id: req.params.id, module_id: req.params.moduleId }).first();
  const next = {
    can_read: req.body.can_read === 'on',
    can_write: req.body.can_write === 'on',
    can_delete: req.body.can_delete === 'on',
  };

  await db.transaction(async (trx) => {
    if (existing) {
      await trx('staff_privilege').where({ id: existing.id }).update(next);
    } else {
      await trx('staff_privilege').insert({ user_id: req.params.id, module_id: req.params.moduleId, granted_by: req.session.staffUser.id, ...next });
    }
    await trx('access_audit_log').insert({
      affected_user_id: req.params.id,
      module_id: req.params.moduleId,
      admin_id: req.session.staffUser.id,
      action: existing ? 'MODIFY' : 'GRANT',
      before_state: existing ? JSON.stringify(existing) : null,
      after_state: JSON.stringify(next),
    });
  });

  const user = await db('staff_user').where({ id: req.params.id }).first();
  const modules = await db('app_module').orderBy('sort_order');
  const privileges = await db('staff_privilege').where({ user_id: req.params.id });
  const privByModule = {};
  privileges.forEach((p) => { privByModule[p.module_id] = p; });
  res.render('admin/partials/privileges_table', { user, modules, privByModule });
});

module.exports = router;
