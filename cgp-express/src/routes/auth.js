const express = require('express');
const bcrypt = require('bcryptjs');
const db = require('../db');

const router = express.Router();

router.get('/admin/login', (req, res) => {
  if (req.session.staffUser) return res.redirect('/admin');
  res.render('admin/login', { title: 'Acceso Administrativo', error: null });
});

router.post('/admin/login', async (req, res) => {
  const { email, password } = req.body;
  const user = await db('staff_user').where({ email }).first();

  const fail = (msg) => res.status(401).render('admin/login', { title: 'Acceso Administrativo', error: msg });

  if (!user || !user.active) return fail('Credenciales inválidas.');
  if (user.locked_until && new Date(user.locked_until) > new Date()) {
    return fail('Cuenta bloqueada temporalmente por intentos fallidos.');
  }

  const ok = bcrypt.compareSync(password, user.password_hash);
  if (!ok) {
    const attempts = user.failed_attempts + 1;
    const lockedUntil = attempts >= 5 ? new Date(Date.now() + 15 * 60 * 1000) : null;
    await db('staff_user').where({ id: user.id }).update({
      failed_attempts: attempts,
      locked_until: lockedUntil,
    });
    return fail('Credenciales inválidas.');
  }

  await db('staff_user').where({ id: user.id }).update({
    failed_attempts: 0,
    locked_until: null,
    last_login: db.fn.now(),
  });

  const person = await db('person').where({ id: user.id }).first();
  req.session.staffUser = { id: user.id, email: user.email, name: `${person.first_name} ${person.last_name}` };
  res.redirect('/admin');
});

router.post('/admin/logout', (req, res) => {
  req.session.destroy(() => res.redirect('/admin/login'));
});

module.exports = router;
