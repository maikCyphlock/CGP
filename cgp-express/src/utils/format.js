/** Odoo-chatter-style relative time: "hace 5 minutos", "hace 2 horas", "ayer", etc. */
function timeAgo(value) {
  const date = value instanceof Date ? value : new Date(value);
  const diffMs = Date.now() - date.getTime();
  const minutes = Math.floor(diffMs / 60000);

  if (minutes < 1) return 'justo ahora';
  if (minutes < 60) return `hace ${minutes} min`;
  const hours = Math.floor(minutes / 60);
  if (hours < 24) return `hace ${hours} h`;
  const days = Math.floor(hours / 24);
  if (days === 1) return 'ayer';
  if (days < 7) return `hace ${days} días`;
  return date.toLocaleDateString('es-VE', { day: '2-digit', month: 'short', year: 'numeric' });
}

/** First letter of the first two words — the initials shown in the chatter avatar circle. */
function initials(name) {
  if (!name) return '?';
  return name.trim().split(/\s+/).slice(0, 2).map((w) => w[0].toUpperCase()).join('');
}

/** Deterministic HSL color per name, so the same person always gets the same avatar color. */
function avatarColor(name) {
  const str = name || 'Sistema';
  let hash = 0;
  for (let i = 0; i < str.length; i++) hash = str.charCodeAt(i) + ((hash << 5) - hash);
  const hue = Math.abs(hash) % 360;
  return `hsl(${hue}, 55%, 45%)`;
}

module.exports = { timeAgo, initials, avatarColor };
