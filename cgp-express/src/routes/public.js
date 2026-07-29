const express = require('express');
const { v4: uuidv4 } = require('uuid');
const db = require('../db');

const router = express.Router();

router.get('/', (_req, res) => res.render('public/home'));
router.get('/contraloria-escolar', (_req, res) => res.render('public/escolar'));
router.get('/organigrama', (_req, res) => res.render('public/organigrama'));
router.get('/denuncia', (_req, res) => res.render('public/denuncia'));

/** Generates the next OAC-<year>-NNNNN case_number using annual_sequence, serialized per year. */
async function nextCaseNumber(trx) {
  const year = new Date().getFullYear();
  const existing = await trx('annual_sequence').where({ year }).first();
  const nextNumber = existing ? existing.last_number + 1 : 1;
  if (existing) {
    await trx('annual_sequence').where({ year }).update({ last_number: nextNumber });
  } else {
    await trx('annual_sequence').insert({ year, last_number: nextNumber });
  }
  return `OAC-${year}-${String(nextNumber).padStart(5, '0')}`;
}

/**
 * Receives the denuncia wizard payload (see public/assets/js/wizard.js
 * enviarSolicitud) and persists citizen + case_file + respondents.
 * Body shape:
 * {
 *   idDocType, idDocNumber, firstName, lastName, sex, birthDate, email, mobilePhone,
 *   address, parish, municipality, city,
 *   claimTypeCode, narrative, incidentDate, incidentLocation,
 *   otherInstance, otherInstanceName, isPopularConsultation,
 *   popularConsultation: { projectName, approvalDate, projectAmount, fundingEntity },
 *   respondents: [{ typeCode, location, attributes }]
 * }
 */
router.post('/denuncias', express.json(), async (req, res) => {
  const b = req.body;
  try {
    const result = await db.transaction(async (trx) => {
      const docType = await trx('id_document_type').where({ code: b.idDocType }).first();
      if (!docType) throw new Error(`Tipo de documento inválido: ${b.idDocType}`);

      let person = await trx('person')
        .where({ id_doc_type_id: docType.id, id_doc_number: b.idDocNumber })
        .first();

      if (!person) {
        const personId = uuidv4();
        await trx('person').insert({
          id: personId,
          id_doc_type_id: docType.id,
          id_doc_number: b.idDocNumber,
          first_name: b.firstName,
          last_name: b.lastName,
          sex: b.sex,
          birth_date: b.birthDate || null,
          email: b.email,
          mobile_phone: b.mobilePhone,
        });
        person = { id: personId };
      }

      let citizen = await trx('citizen').where({ id: person.id }).first();
      if (!citizen) {
        await trx('citizen').insert({
          id: person.id,
          address: b.address,
          parish: b.parish || null,
          municipality: b.municipality || 'Páez',
          city: b.city || null,
        });
        const citizenRole = await trx('person_role_type').where({ code: 'CITIZEN' }).first();
        await trx('person_role').insert({ person_id: person.id, role_type_id: citizenRole.id });
      }

      const claimType = await trx('claim_type').where({ code: b.claimTypeCode }).first();
      if (!claimType) throw new Error(`Tipo de trámite inválido: ${b.claimTypeCode}`);
      const receivedStatus = await trx('case_status').where({ code: 'RECEIVED' }).first();

      const caseId = uuidv4();
      const trackingCode = uuidv4();
      const caseNumber = await nextCaseNumber(trx);

      await trx('case_file').insert({
        id: caseId,
        case_number: caseNumber,
        tracking_code: trackingCode,
        claim_type_id: claimType.id,
        status_id: receivedStatus.id,
        citizen_id: person.id,
        narrative: b.narrative,
        incident_date: b.incidentDate,
        incident_location: b.incidentLocation || null,
        other_instance: !!b.otherInstance,
        other_instance_name: b.otherInstanceName || null,
        is_popular_consultation: !!b.isPopularConsultation,
        sworn_declaration: true,
        declaration_date: db.fn.now(),
      });

      await trx('case_status_log').insert({
        case_file_id: caseId,
        previous_status: null,
        new_status: receivedStatus.id,
        changed_by: null,
      });

      if (b.isPopularConsultation && b.popularConsultation) {
        const pc = b.popularConsultation;
        await trx('popular_consultation').insert({
          case_file_id: caseId,
          project_name: pc.projectName,
          approval_date: pc.approvalDate,
          project_amount: pc.projectAmount,
          funding_entity: pc.fundingEntity,
        });
      }

      const respondents = Array.isArray(b.respondents) ? b.respondents : [];
      for (const r of respondents) {
        const rType = await trx('respondent_type').where({ code: r.typeCode }).first();
        if (!rType) continue;
        await trx('respondent').insert({
          case_file_id: caseId,
          respondent_type_id: rType.id,
          location: r.location,
          attributes: JSON.stringify(r.attributes || {}),
        });
      }

      return { caseNumber, trackingCode };
    });

    res.status(201).json(result);
  } catch (err) {
    res.status(400).json({ error: err.message });
  }
});

/** Public tracking lookup (RQF-07): citizen checks status with the opaque tracking_code. */
router.get('/seguimiento/:trackingCode', async (req, res) => {
  const caseFile = await db('case_file')
    .join('case_status', 'case_file.status_id', 'case_status.id')
    .join('claim_type', 'case_file.claim_type_id', 'claim_type.id')
    .where('case_file.tracking_code', req.params.trackingCode)
    .select('case_file.case_number', 'case_file.created_at', 'case_status.name as status_name', 'claim_type.name as claim_type_name')
    .first();

  if (!caseFile) return res.status(404).json({ error: 'No encontrado' });

  const history = await db('case_status_log')
    .join('case_status', 'case_status_log.new_status', 'case_status.id')
    .where('case_status_log.case_file_id', db('case_file').where('tracking_code', req.params.trackingCode).select('id'))
    .select('case_status.name as status_name', 'case_status_log.changed_at')
    .orderBy('case_status_log.changed_at', 'asc');

  res.json({ ...caseFile, history });
});

module.exports = router;
