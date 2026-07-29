const express = require('express');
const db = require('../db');
const { requireAuth } = require('../middleware/auth');
const catalogsRouter = require('./adminCatalogs');
const casesRouter = require('./adminCases');
const usersRouter = require('./adminUsers');
const cmsRouter = require('./adminCms');

const router = express.Router();

router.use(requireAuth);

router.get('/', async (req, res) => {
  const [{ count: totalCases }] = await db('case_file').count({ count: '*' });
  const receivedStatus = await db('case_status').where({ code: 'RECEIVED' }).first();
  const [{ count: received }] = receivedStatus
    ? await db('case_file').where({ status_id: receivedStatus.id }).count({ count: '*' })
    : [{ count: 0 }];
  const [{ count: activeStaff }] = await db('staff_user').where({ active: true }).count({ count: '*' });

  res.render('admin/dashboard', {
    title: 'Panel',
    stats: { totalCases, received, activeStaff },
  });
});

router.use('/catalogs', catalogsRouter);
router.use('/cases', casesRouter);
router.use('/users', usersRouter);
router.use('/cms', cmsRouter);

module.exports = router;
