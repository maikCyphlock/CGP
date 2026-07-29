const express = require('express');
const db = require('../db');
const { requirePrivilege } = require('../middleware/auth');

const router = express.Router();

router.get('/', requirePrivilege('CASES', 'can_read'), async (req, res) => {
  const statuses = await db('case_status').orderBy('sort_order');
  let query = db('case_file')
    .join('case_status', 'case_file.status_id', 'case_status.id')
    .join('claim_type', 'case_file.claim_type_id', 'claim_type.id')
    .join('citizen', 'case_file.citizen_id', 'citizen.id')
    .join('person', 'citizen.id', 'person.id')
    .select(
      'case_file.id', 'case_file.case_number', 'case_file.created_at', 'case_file.incident_date',
      'case_status.name as status_name', 'case_status.code as status_code',
      'claim_type.name as claim_type_name',
      db.raw("person.first_name || ' ' || person.last_name as citizen_name")
    )
    .orderBy('case_file.created_at', 'desc');

  if (req.query.status) query = query.where('case_status.code', req.query.status);
  if (req.query.q) query = query.where('case_file.case_number', 'like', `%${req.query.q}%`);

  const cases = await query;
  res.render('admin/cases_list', { title: 'Expedientes', active: 'cases', cases, statuses, filters: req.query });
});

async function loadCaseDetail(id) {
  const caseFile = await db('case_file')
    .join('case_status', 'case_file.status_id', 'case_status.id')
    .join('claim_type', 'case_file.claim_type_id', 'claim_type.id')
    .join('citizen', 'case_file.citizen_id', 'citizen.id')
    .join('person', 'citizen.id', 'person.id')
    .leftJoin('irregularity_type', 'case_file.irregularity_type_id', 'irregularity_type.id')
    .leftJoin('referral_unit', 'case_file.referral_unit_id', 'referral_unit.id')
    .where('case_file.id', id)
    .select(
      'case_file.*',
      'case_status.name as status_name', 'case_status.code as status_code',
      'claim_type.name as claim_type_name',
      'irregularity_type.name as irregularity_type_name',
      'referral_unit.name as referral_unit_name',
      'person.first_name', 'person.last_name', 'person.email', 'person.mobile_phone',
      'citizen.address', 'citizen.parish', 'citizen.municipality'
    )
    .first();
  if (!caseFile) return null;

  caseFile.respondents = await db('respondent').where({ case_file_id: id });
  caseFile.respondents.forEach((r) => { r.attributes = JSON.parse(r.attributes || '{}'); });

  caseFile.history = await db('case_status_log')
    .join('case_status', 'case_status_log.new_status', 'case_status.id')
    .where('case_status_log.case_file_id', id)
    .select('case_status.name as status_name', 'case_status_log.changed_at')
    .orderBy('case_status_log.changed_at', 'asc');

  const rawActions = await db('case_action')
    .leftJoin('staff_user', 'case_action.user_id', 'staff_user.id')
    .leftJoin('person', 'staff_user.id', 'person.id')
    .where('case_action.case_file_id', id)
    .select('case_action.*', 'person.first_name', 'person.last_name')
    .orderBy('case_action.performed_at', 'desc');

  const irregularityTypes = await db('irregularity_type');
  const referralUnits = await db('referral_unit');
  const irregularityById = Object.fromEntries(irregularityTypes.map((t) => [t.id, t.name]));
  const referralUnitById = Object.fromEntries(referralUnits.map((u) => [u.id, u.name]));

  caseFile.actions = rawActions.map((a) => enrichAction(a, irregularityById, referralUnitById));

  caseFile.evidence = await db('evidence_file').where({ case_file_id: id });

  return caseFile;
}

/** Odoo-chatter-style: turns a raw case_action row into { authorName, icon, summary } for display. */
function enrichAction(action, irregularityById, referralUnitById) {
  const payload = JSON.parse(action.payload || '{}');
  const authorName = action.first_name ? `${action.first_name} ${action.last_name}` : 'Sistema';

  // icon keys map to inline SVG paths rendered by views/admin/partials/chatter.ejs
  const byType = {
    STATUS_CHANGE: {
      icon: 'status',
      summary: `cambió el estado a <strong>${payload.to || '—'}</strong>` + (payload.reason ? ` — ${payload.reason}` : ''),
    },
    CLASSIFICATION: {
      icon: 'tag',
      summary: payload.irregularity_type_id
        ? `clasificó el expediente como <strong>${irregularityById[payload.irregularity_type_id] || payload.irregularity_type_id}</strong>` + (payload.notes ? ` — ${payload.notes}` : '')
        : `actualizó las notas del analista${payload.notes ? ': ' + payload.notes : ''}`,
    },
    REFERRAL: {
      icon: 'send',
      summary: `derivó el expediente a <strong>${referralUnitById[payload.unit] || payload.unit || '—'}</strong>`,
    },
    NOTE: { icon: 'note', summary: payload.text || '' },
    DOCUMENT_CHECK: { icon: 'clip', summary: 'actualizó el checklist de documentos físicos' },
    OTHER: { icon: 'info', summary: payload.text || JSON.stringify(payload) },
  };

  const meta = byType[action.action_type] || { icon: 'info', summary: JSON.stringify(payload) };
  return { ...action, authorName, icon: meta.icon, summary: meta.summary };
}

router.get('/:id', requirePrivilege('CASES', 'can_read'), async (req, res) => {
  const caseFile = await loadCaseDetail(req.params.id);
  if (!caseFile) return res.status(404).send('Expediente no encontrado');
  const statuses = await db('case_status').orderBy('sort_order');
  const irregularityTypes = await db('irregularity_type').where({ active: true });
  const referralUnits = await db('referral_unit').where({ active: true });
  res.render('admin/cases_detail', {
    title: caseFile.case_number, active: 'cases', caseFile, statuses, irregularityTypes, referralUnits,
  });
});

router.get('/:id/panel', requirePrivilege('CASES', 'can_read'), async (req, res) => {
  const caseFile = await loadCaseDetail(req.params.id);
  if (!caseFile) return res.status(404).send('Expediente no encontrado');
  const statuses = await db('case_status').orderBy('sort_order');
  const irregularityTypes = await db('irregularity_type').where({ active: true });
  const referralUnits = await db('referral_unit').where({ active: true });
  res.render('admin/partials/case_panel', { caseFile, statuses, irregularityTypes, referralUnits });
});

router.post('/:id/status', requirePrivilege('CASES', 'can_write'), async (req, res) => {
  const caseFile = await db('case_file').where({ id: req.params.id }).first();
  if (!caseFile) return res.status(404).send('No encontrado');
  const newStatus = await db('case_status').where({ id: req.body.status_id }).first();
  if (!newStatus) return res.status(400).send('Estado inválido');

  await db.transaction(async (trx) => {
    await trx('case_file').where({ id: req.params.id }).update({ status_id: newStatus.id, updated_at: trx.fn.now() });
    await trx('case_status_log').insert({
      case_file_id: req.params.id,
      previous_status: caseFile.status_id,
      new_status: newStatus.id,
      changed_by: req.session.staffUser.id,
    });
    await trx('case_action').insert({
      case_file_id: req.params.id,
      user_id: req.session.staffUser.id,
      action_type: 'STATUS_CHANGE',
      payload: JSON.stringify({ to: newStatus.code, reason: req.body.reason || null }),
    });
  });

  const updated = await loadCaseDetail(req.params.id);
  const statuses = await db('case_status').orderBy('sort_order');
  const irregularityTypes = await db('irregularity_type').where({ active: true });
  const referralUnits = await db('referral_unit').where({ active: true });
  res.render('admin/partials/case_panel', { caseFile: updated, statuses, irregularityTypes, referralUnits });
});

router.post('/:id/classify', requirePrivilege('CLASSIFY', 'can_write'), async (req, res) => {
  await db.transaction(async (trx) => {
    await trx('case_file').where({ id: req.params.id }).update({
      irregularity_type_id: req.body.irregularity_type_id || null,
      analyst_notes: req.body.analyst_notes || null,
      updated_at: trx.fn.now(),
    });
    await trx('case_action').insert({
      case_file_id: req.params.id,
      user_id: req.session.staffUser.id,
      action_type: 'CLASSIFICATION',
      payload: JSON.stringify({ irregularity_type_id: req.body.irregularity_type_id, notes: req.body.analyst_notes }),
    });
  });

  const updated = await loadCaseDetail(req.params.id);
  const statuses = await db('case_status').orderBy('sort_order');
  const irregularityTypes = await db('irregularity_type').where({ active: true });
  const referralUnits = await db('referral_unit').where({ active: true });
  res.render('admin/partials/case_panel', { caseFile: updated, statuses, irregularityTypes, referralUnits });
});

router.post('/:id/refer', requirePrivilege('CLASSIFY', 'can_write'), async (req, res) => {
  const referredStatus = await db('case_status').where({ code: 'REFERRED' }).first();
  const caseFile = await db('case_file').where({ id: req.params.id }).first();

  await db.transaction(async (trx) => {
    await trx('case_file').where({ id: req.params.id }).update({
      referral_unit_id: req.body.referral_unit_id || null,
      referral_letter_url: req.body.referral_letter_url || null,
      referred_at: trx.fn.now(),
      referred_by: req.session.staffUser.id,
      status_id: referredStatus.id,
      updated_at: trx.fn.now(),
    });
    await trx('case_status_log').insert({
      case_file_id: req.params.id,
      previous_status: caseFile.status_id,
      new_status: referredStatus.id,
      changed_by: req.session.staffUser.id,
    });
    await trx('case_action').insert({
      case_file_id: req.params.id,
      user_id: req.session.staffUser.id,
      action_type: 'REFERRAL',
      payload: JSON.stringify({ unit: req.body.referral_unit_id, letter_url: req.body.referral_letter_url }),
    });
  });

  const updated = await loadCaseDetail(req.params.id);
  const statuses = await db('case_status').orderBy('sort_order');
  const irregularityTypes = await db('irregularity_type').where({ active: true });
  const referralUnits = await db('referral_unit').where({ active: true });
  res.render('admin/partials/case_panel', { caseFile: updated, statuses, irregularityTypes, referralUnits });
});

router.post('/:id/notes', requirePrivilege('CASES', 'can_write'), async (req, res) => {
  await db('case_action').insert({
    case_file_id: req.params.id,
    user_id: req.session.staffUser.id,
    action_type: 'NOTE',
    payload: JSON.stringify({ text: req.body.text }),
  });
  // Only the chatter feed needs to refresh here (Odoo-style: posting a note
  // doesn't reload the whole record), unlike status/classify/refer which
  // change data shown elsewhere on the page and re-render the full panel.
  const updated = await loadCaseDetail(req.params.id);
  res.render('admin/partials/chatter', { caseFile: updated });
});

module.exports = router;
