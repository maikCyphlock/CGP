const express = require('express');
const { v4: uuidv4 } = require('uuid');
const db = require('../db');
const { requirePrivilege } = require('../middleware/auth');

const router = express.Router();

router.get('/', requirePrivilege('CMS', 'can_read'), async (req, res) => {
  const items = await db('cms_content')
    .join('cms_content_type', 'cms_content.content_type_id', 'cms_content_type.id')
    .select('cms_content.*', 'cms_content_type.name as type_name')
    .orderBy('cms_content.updated_at', 'desc');
  res.render('admin/cms_list', { title: 'Página Web (CMS)', active: 'cms', items });
});

router.get('/new', requirePrivilege('CMS', 'can_write'), async (req, res) => {
  const types = await db('cms_content_type').where({ active: true });
  res.render('admin/cms_form', { title: 'Nuevo contenido', active: 'cms', types, item: null });
});

router.post('/', requirePrivilege('CMS', 'can_write'), async (req, res) => {
  const id = uuidv4();
  await db('cms_content').insert({
    id,
    content_type_id: req.body.content_type_id,
    title: req.body.title,
    body: req.body.body,
    published: req.body.published === 'on',
    author_id: req.session.staffUser.id,
    published_at: req.body.published === 'on' ? db.fn.now() : null,
  });
  res.redirect('/admin/cms');
});

router.get('/:id/edit', requirePrivilege('CMS', 'can_write'), async (req, res) => {
  const item = await db('cms_content').where({ id: req.params.id }).first();
  if (!item) return res.status(404).send('No encontrado');
  const types = await db('cms_content_type').where({ active: true });
  const versions = await db('cms_content_version').where({ content_id: req.params.id }).orderBy('modified_at', 'desc');
  res.render('admin/cms_form', { title: 'Editar contenido', active: 'cms', types, item, versions });
});

router.post('/:id', requirePrivilege('CMS', 'can_write'), async (req, res) => {
  const current = await db('cms_content').where({ id: req.params.id }).first();
  if (!current) return res.status(404).send('No encontrado');

  await db.transaction(async (trx) => {
    await trx('cms_content_version').insert({
      content_id: req.params.id,
      title: current.title,
      body: current.body,
      modified_by: req.session.staffUser.id,
    });
    const wasPublished = current.published;
    const nowPublished = req.body.published === 'on';
    await trx('cms_content').where({ id: req.params.id }).update({
      content_type_id: req.body.content_type_id,
      title: req.body.title,
      body: req.body.body,
      published: nowPublished,
      published_at: !wasPublished && nowPublished ? trx.fn.now() : current.published_at,
      unpublished_at: wasPublished && !nowPublished ? trx.fn.now() : current.unpublished_at,
      updated_at: trx.fn.now(),
    });
  });

  res.redirect('/admin/cms');
});

module.exports = router;
