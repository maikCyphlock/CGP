require('dotenv').config();
const path = require('path');

// Swappable DB connector: change DB_CLIENT in .env to move from sqlite3 -> pg.
// Every migration/seed/query in this project is written with knex's query
// builder (no raw dialect-specific SQL) so it runs unmodified on both.
// knex expects the dialect name "better-sqlite3" for the better-sqlite3 driver;
// DB_CLIENT stays "sqlite3" in .env for readability and is mapped here.
const rawClient = process.env.DB_CLIENT || 'sqlite3';
const client = rawClient === 'sqlite3' ? 'better-sqlite3' : rawClient;

const connections = {
  sqlite3: {
    filename: process.env.SQLITE_FILE || path.join(__dirname, '..', '..', 'data', 'contraloria.sqlite3'),
  },
  pg: {
    host: process.env.PG_HOST || 'localhost',
    port: Number(process.env.PG_PORT) || 5432,
    database: process.env.PG_DATABASE || 'contraloria',
    user: process.env.PG_USER || 'postgres',
    password: process.env.PG_PASSWORD || 'postgres',
  },
};

module.exports = {
  client,
  useNullAsDefault: rawClient === 'sqlite3',
  connection: connections[rawClient],
  pool: rawClient === 'sqlite3' ? { min: 1, max: 1 } : { min: 2, max: 10 },
  migrations: {
    directory: path.join(__dirname, 'migrations'),
    tableName: 'knex_migrations',
  },
  seeds: {
    directory: path.join(__dirname, 'seeds'),
  },
};
