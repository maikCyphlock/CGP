@extends('layouts.panel')

@section('titulo', 'Monitoreo del sistema')

@section('contenido')
@php
  $u = auth()->user();
  $puedeGestionar = $u->puede('ACCESS', 'write');
  $zona = config('app.timezone');
  $fecha = fn ($f) => \Illuminate\Support\Carbon::parse($f)->timezone($zona)->format('d/m/Y h:i a');
  $navegador = function (?string $ua) {
    $ua = (string) $ua;
    $nav = match (true) { str_contains($ua, 'Edg/') => 'Edge', str_contains($ua, 'OPR/') => 'Opera', str_contains($ua, 'Firefox/') => 'Firefox', str_contains($ua, 'Chrome/') => 'Chrome', str_contains($ua, 'Safari/') => 'Safari', default => 'Otro' };
    $so = match (true) { str_contains($ua, 'Android') => 'Android', str_contains($ua, 'iPhone'), str_contains($ua, 'iPad') => 'iOS', str_contains($ua, 'Windows') => 'Windows', str_contains($ua, 'Mac OS') => 'macOS', str_contains($ua, 'Linux') => 'Linux', default => '' };
    return trim("$nav $so") ?: 'Desconocido';
  };
  $nivel = fn ($e) => ! $e ? 'Sin acceso' : (($e['can_delete'] ?? false) ? 'Total' : (($e['can_write'] ?? false) ? 'Lectura y escritura' : (($e['can_read'] ?? false) ? 'Solo lectura' : 'Sin acceso')));
  $pestanas = ['sesiones' => ['Sesiones abiertas', $r['sesiones']], 'bloqueos' => ['Cuentas bloqueadas', $r['bloqueadas']], 'denegados' => ['Accesos denegados y fallidos', null], 'permisos' => ['Cambios de permisos', null], 'historial' => ['Historial de ingresos', null]];
@endphp

<div class="stat-strip">
  <a href="{{ route('admin.monitoreo', ['ver' => 'sesiones']) }}"><div class="stat-label">Sesiones abiertas</div><div class="stat-value">{{ $r['sesiones'] }}</div></a>
  <a href="{{ route('admin.monitoreo', ['ver' => 'bloqueos']) }}"><div class="stat-label">Cuentas bloqueadas</div><div class="stat-value {{ $r['bloqueadas'] ? 'accent' : '' }}">{{ $r['bloqueadas'] }}</div></a>
  <a href="{{ route('admin.monitoreo', ['ver' => 'denegados']) }}"><div class="stat-label">Accesos denegados (24 h)</div><div class="stat-value {{ $r['denegados'] ? 'accent' : '' }}">{{ $r['denegados'] }}</div></a>
  <a href="{{ route('admin.monitoreo', ['ver' => 'permisos']) }}"><div class="stat-label">Cambios de permisos (7 días)</div><div class="stat-value">{{ $r['permisos'] }}</div></a>
</div>

<div class="d-flex flex-wrap gap-4 small text-muted mb-3">
  <span><i class="fa-solid fa-users me-1"></i>{{ $r['usuarios'] }} usuarios activos @if ($r['inactivos']) · {{ $r['inactivos'] }} desactivados @endif</span>
  @if ($disco)<span><i class="fa-solid fa-hard-drive me-1"></i>Disco: {{ $disco['usado'] }}% usado · {{ number_format($disco['libre'] / 1073741824, 1) }} GB libres</span>@endif
  <span><i class="fa-regular fa-clock me-1"></i>Hora del servidor: {{ now()->timezone($zona)->format('d/m/Y h:i a') }}</span>
</div>

<div class="d-flex flex-wrap gap-2 mb-3">
  @foreach ($pestanas as $clave => [$nombre, $n])
    <a href="{{ route('admin.monitoreo', ['ver' => $clave]) }}" class="btn btn-sm rounded-pill px-3 {{ $ver === $clave ? 'btn-dark' : 'btn-outline-secondary' }}">{{ $nombre }}@if ($n) <span class="badge bg-secondary ms-1">{{ $n }}</span>@endif</a>
  @endforeach
</div>

<div class="card border-0 shadow-sm rounded-3 overflow-hidden">
  <div class="table-responsive">
    <table class="table table-hover align-middle mb-0" style="font-size: .9rem;">
      @if ($ver === 'sesiones')
        <thead class="table-light"><tr><th class="ps-4">Usuario</th><th>Equipo</th><th>IP</th><th>Última actividad</th><th></th></tr></thead>
        <tbody>
          @forelse ($filas as $f)
            <tr>
              <td class="ps-4"><strong>{{ $f->first_name }} {{ $f->last_name }}</strong><div class="text-muted small">{{ $f->email }}</div></td>
              <td>{{ $navegador($f->user_agent) }}</td>
              <td class="text-nowrap">{{ $f->ip_address }}</td>
              <td class="text-nowrap">{{ \Illuminate\Support\Carbon::createFromTimestamp($f->last_activity)->timezone($zona)->format('d/m/Y h:i a') }}</td>
              <td class="text-end pe-4">
                @if ($f->id === $miSesion) <span class="badge bg-success">Esta sesión</span>
                @elseif ($puedeGestionar)
                  <form method="POST" action="{{ route('admin.monitoreo.cerrar', $f->id) }}" onsubmit="return confirm('¿Cerrar la sesión de {{ $f->first_name }} {{ $f->last_name }}? Tendrá que volver a iniciar sesión.')">@csrf<button class="btn btn-sm btn-outline-danger">Cerrar sesión</button></form>
                @endif
              </td>
            </tr>
          @empty
            <tr><td colspan="5" class="text-center text-muted py-5">No hay sesiones abiertas.</td></tr>
          @endforelse
        </tbody>

      @elseif ($ver === 'bloqueos')
        <thead class="table-light"><tr><th class="ps-4">Usuario</th><th>Bloqueada hasta</th><th></th></tr></thead>
        <tbody>
          @forelse ($filas as $f)
            <tr>
              <td class="ps-4"><a href="{{ route('admin.usuarios.show', $f->id) }}" class="fw-bold text-decoration-none">{{ $f->first_name }} {{ $f->last_name }}</a><div class="text-muted small">{{ $f->email }}</div></td>
              <td>{{ $fecha($f->locked_until) }} <span class="text-muted small">(por intentos fallidos)</span></td>
              <td class="text-end pe-4">
                @if ($puedeGestionar)
                  <form method="POST" action="{{ route('admin.monitoreo.desbloquear', $f->id) }}">@csrf<button class="btn btn-sm btn-cgp">Desbloquear</button></form>
                @endif
              </td>
            </tr>
          @empty
            <tr><td colspan="3" class="text-center text-muted py-5">No hay cuentas bloqueadas.</td></tr>
          @endforelse
        </tbody>

      @elseif ($ver === 'denegados')
        <thead class="table-light"><tr><th class="ps-4">Fecha</th><th>Usuario</th><th>Qué se intentó</th><th>Módulo</th><th>IP</th></tr></thead>
        <tbody>
          @forelse ($filas as $f)
            @php($meta = json_decode($f->client_meta, true) ?: [])
            <tr>
              <td class="ps-4 text-nowrap text-secondary">{{ $fecha($f->logged_at) }}</td>
              <td>{{ $f->usuario ?? 'Desconocido' }}</td>
              <td><code class="small">{{ $f->endpoint }}</code></td>
              <td>{{ $f->modulo ?? '—' }}</td>
              <td class="text-nowrap">{{ $meta['ip'] ?? '—' }}</td>
            </tr>
          @empty
            <tr><td colspan="5" class="text-center text-muted py-5">Sin registros.</td></tr>
          @endforelse
        </tbody>

      @elseif ($ver === 'permisos')
        <thead class="table-light"><tr><th class="ps-4">Fecha</th><th>Quién lo hizo</th><th>A quién</th><th>Módulo</th><th>Cambio</th></tr></thead>
        <tbody>
          @forelse ($filas as $f)
            @php($antes = json_decode($f->before_state, true))
            @php($despues = json_decode($f->after_state, true))
            <tr>
              <td class="ps-4 text-nowrap text-secondary">{{ $fecha($f->logged_at) }}</td>
              <td>{{ $f->admin }}</td>
              <td><a href="{{ route('admin.usuarios.show', $f->afectado_id) }}" class="text-decoration-none">{{ $f->afectado }}</a></td>
              <td>{{ $f->modulo }}</td>
              <td>
                @if (isset($despues['can_read']))
                  {{ $nivel($antes) }} <i class="fa-solid fa-arrow-right mx-1 text-muted small"></i> <strong>{{ $nivel($despues) }}</strong>
                @elseif (isset($despues['sesion_abierta'])) Sesión cerrada por un administrador
                @elseif (isset($despues['bloqueada'])) Cuenta desbloqueada
                @else {{ $f->action }} @endif
              </td>
            </tr>
          @empty
            <tr><td colspan="5" class="text-center text-muted py-5">Sin cambios registrados.</td></tr>
          @endforelse
        </tbody>

      @else
        <thead class="table-light"><tr><th class="ps-4">Ingreso</th><th>Usuario</th><th>Módulo</th><th>Equipo / IP</th><th>Salida</th></tr></thead>
        <tbody>
          @forelse ($filas as $f)
            @php($meta = json_decode($f->client_meta, true) ?: [])
            <tr>
              <td class="ps-4 text-nowrap text-secondary">{{ $fecha($f->created_at) }}</td>
              <td><strong>{{ $f->first_name }} {{ $f->last_name }}</strong><div class="text-muted small">{{ $f->email }}</div></td>
              <td>{{ ($meta['modulo'] ?? '') === 'oac' ? 'Atención al Ciudadano' : (($meta['modulo'] ?? '') === 'admin' ? 'Administración' : '—') }}</td>
              <td>{{ $navegador($meta['ua'] ?? '') }} · {{ $meta['ip'] ?? '—' }}</td>
              <td class="text-nowrap">
                @if ($f->closed_at) {{ $fecha($f->closed_at) }}
                @elseif (\Illuminate\Support\Carbon::parse($f->expires_at)->isPast()) <span class="text-muted">Expiró</span>
                @else <span class="badge bg-success">Abierta</span> @endif
              </td>
            </tr>
          @empty
            <tr><td colspan="5" class="text-center text-muted py-5">Sin ingresos registrados.</td></tr>
          @endforelse
        </tbody>
      @endif
    </table>
  </div>
</div>
<p class="small text-muted mt-2">Se muestran los últimos 100 registros. Los eventos de seguridad no se pueden borrar.</p>
@endsection
