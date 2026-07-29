require('dotenv').config();
const path = require('path');
const express = require('express');
const session = require('express-session');
const KnexSessionStore = require('connect-session-knex')(session);
const db = require('./db');

const publicRoutes = require('./routes/public');
const authRoutes = require('./routes/auth');
const adminRoutes = require('./routes/admin');
const { timeAgo, initials, avatarColor } = require('./utils/format');

const app = express();

app.set('view engine', 'ejs');
app.set('views', path.join(__dirname, 'views'));

// Available in every EJS template — used by the Odoo-style chatter partial.
app.locals.timeAgo = timeAgo;
app.locals.initials = initials;
app.locals.avatarColor = avatarColor;

app.use(express.urlencoded({ extended: true }));
app.use(express.json());
app.use(express.static(path.join(__dirname, '..', 'public')));

app.use(
  session({
    store: new KnexSessionStore({ knex: db, createtable: true, tablename: 'sessions' }),
    secret: process.env.SESSION_SECRET || 'change-me-in-production',
    resave: false,
    saveUninitialized: false,
    cookie: { maxAge: 1000 * 60 * 60 * 8 },
  })
);

app.use((req, res, next) => {
  res.locals.staffUser = req.session.staffUser || null;
  res.locals.isHtmx = req.get('HX-Request') === 'true';
  next();
});

app.use('/', publicRoutes);
app.use('/', authRoutes);
app.use('/admin', adminRoutes);

app.use((req, res) => {
  res.status(404).render('public/404', { title: 'Página no encontrada' });
});

// eslint-disable-next-line no-unused-vars
app.use((err, req, res, next) => {
  console.error(err);
  res.status(500).send(`<pre>${err.stack || err.message}</pre>`);
});

const PORT = process.env.PORT || 3000;
app.listen(PORT, () => {
  console.log(`Contraloría Municipio Páez — http://localhost:${PORT} (DB_CLIENT=${process.env.DB_CLIENT || 'sqlite3'})`);
});
