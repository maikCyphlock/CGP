const express = require('express');
const db = require('../db');
const CATALOGS = require('../config/catalogs');
const { requirePrivilege } = require('../middleware/auth');

const router = express.Router();

function getCatalogOr404(req, res) {
  const catalog = CATALOGS[req.params.slug];
  if (!catalog) {
    res.status(404).send('Catálogo no encontrado');
    return null;
  }
  return catalog;
}

/** Coerces raw form-encoded strings into the right JS type per field.type before an insert/update. */
function coerceBody(catalog, body) {
  const row = {};
  for (const f of catalog.fields) {
    if (f.type === 'checkbox') {
      row[f.name] = body[f.name] === 'on' || body[f.name] === 'true' || body[f.name] === true;
    } else if (f.type === 'number') {
      row[f.name] = body[f.name] === '' || body[f.name] == null ? null : Number(body[f.name]);
    } else if (f.type === 'json') {
      try {
        row[f.name] = JSON.stringify(JSON.parse(body[f.name] || '[]'));
      } catch {
        row[f.name] = '[]';
      }
    } else {
      row[f.name] = body[f.name] ?? null;
    }
  }
  return row;
}

router.get('/', requirePrivilege('CATALOGS', 'can_read'), (req, res) => {
  res.render('admin/catalogs_index', { title: 'Catálogos', catalogs: CATALOGS });
});

router.get('/:slug', requirePrivilege('CATALOGS', 'can_read'), async (req, res) => {
  const catalog = getCatalogOr404(req, res);
  if (!catalog) return;
  const rows = await db(catalog.table).orderBy('sort_order', 'asc');
  res.render('admin/catalog_detail', { title: catalog.label, slug: req.params.slug, catalog, rows });
});

router.get('/:slug/rows', requirePrivilege('CATALOGS', 'can_read'), async (req, res) => {
  const catalog = getCatalogOr404(req, res);
  if (!catalog) return;
  const rows = await db(catalog.table).orderBy('sort_order', 'asc');
  res.render('admin/partials/catalog_table', { slug: req.params.slug, catalog, rows });
});

router.get('/:slug/new', requirePrivilege('CATALOGS', 'can_write'), (req, res) => {
  const catalog = getCatalogOr404(req, res);
  if (!catalog) return;
  res.render('admin/partials/catalog_form', { slug: req.params.slug, catalog, row: null });
});

router.get('/:slug/:id/edit', requirePrivilege('CATALOGS', 'can_write'), async (req, res) => {
  const catalog = getCatalogOr404(req, res);
  if (!catalog) return;
  const row = await db(catalog.table).where({ id: req.params.id }).first();
  if (!row) return res.status(404).send('No encontrado');
  res.render('admin/partials/catalog_form', { slug: req.params.slug, catalog, row });
});

router.post('/:slug', requirePrivilege('CATALOGS', 'can_write'), async (req, res) => {
  const catalog = getCatalogOr404(req, res);
  if (!catalog) return;
  await db(catalog.table).insert(coerceBody(catalog, req.body));
  const rows = await db(catalog.table).orderBy('sort_order', 'asc');
  res.render('admin/partials/catalog_table', { slug: req.params.slug, catalog, rows });
});

router.post('/:slug/:id', requirePrivilege('CATALOGS', 'can_write'), async (req, res) => {
  const catalog = getCatalogOr404(req, res);
  if (!catalog) return;
  const update = coerceBody(catalog, req.body);
  update.updated_at = db.fn.now();
  await db(catalog.table).where({ id: req.params.id }).update(update);
  const rows = await db(catalog.table).orderBy('sort_order', 'asc');
  res.render('admin/partials/catalog_table', { slug: req.params.slug, catalog, rows });
});

router.delete('/:slug/:id', requirePrivilege('CATALOGS', 'can_delete'), async (req, res) => {
  const catalog = getCatalogOr404(req, res);
  if (!catalog) return;
  await db(catalog.table).where({ id: req.params.id }).del();
  const rows = await db(catalog.table).orderBy('sort_order', 'asc');
  res.render('admin/partials/catalog_table', { slug: req.params.slug, catalog, rows });
});

module.exports = router;
