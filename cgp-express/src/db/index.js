const knex = require('knex');
const config = require('./knexfile');

// Single shared connection instance. Swapping DB_CLIENT in .env (sqlite3 <-> pg)
// is the only change needed anywhere in the app — every query goes through
// this same `db` object using the knex query builder.
const db = knex(config);

module.exports = db;
