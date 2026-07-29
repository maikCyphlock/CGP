const db = require('../db');

async function requireAuth(req, res, next) {
  if (!req.session.staffUser) {
    return res.redirect('/admin/login');
  }
  next();
}

/** Loads staff_privilege for the module code and blocks if the required action isn't granted. */
function requirePrivilege(moduleCode, action = 'can_read') {
  return async (req, res, next) => {
    if (!req.session.staffUser) return res.redirect('/admin/login');

    const mod = await db('app_module').where({ code: moduleCode }).first();
    const priv = mod
      ? await db('staff_privilege')
          .where({ user_id: req.session.staffUser.id, module_id: mod.id })
          .first()
      : null;

    if (!priv || !priv[action]) {
      await db('unauthorized_access_log').insert({
        user_id: req.session.staffUser.id,
        module_id: mod ? mod.id : null,
        endpoint: req.originalUrl,
        client_meta: JSON.stringify({ ip: req.ip, ua: req.get('user-agent') }),
      });
      return res.status(403).render('admin/403', { title: 'Acceso denegado', moduleCode });
    }
    next();
  };
}

module.exports = { requireAuth, requirePrivilege };
