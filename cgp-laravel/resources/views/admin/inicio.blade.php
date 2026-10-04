@extends('layouts.admin')

@section('titulo', 'Panel administrativo')

@section('contenido')
@php
  $u = auth()->user();
  $hora = now()->timezone(config('app.timezone'))->hour;
  $saludo = $hora < 12 ? 'Buen día' : ($hora < 19 ? 'Buenas tardes' : 'Buenas noches');
  $icono = fn ($d) => '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round">'.$d.'</svg>';
  $modulos = array_filter([
    $u->puede('CASES') ? ['Gestión de expedientes', 'Recibir, clasificar y derivar denuncias, quejas y reclamos', route('admin.expedientes.index'), '<path d="M3 7a1 1 0 0 1 1-1h4.5l1.5 2H20a1 1 0 0 1 1 1v9a1 1 0 0 1-1 1H4a1 1 0 0 1-1-1V7Z"/>'] : null,
    $u->puede('CATALOGS') ? ['Gestión de catálogos', 'Tipos de trámite, irregularidades, unidades de derivación y demás listas', route('admin.catalogos.index'), '<path d="m12 3 8.5 4.5L12 12 3.5 7.5 12 3Z"/><path d="m3.5 12.5 8.5 4.5 8.5-4.5"/><path d="m3.5 16.5 8.5 4.5 8.5-4.5"/>'] : null,
    $u->puede('USERS') ? ['Usuarios y accesos', 'Personal interno y permisos por módulo', route('admin.usuarios.index'), '<path d="M12 3 5 5.5V11c0 4.5 3 8 7 9.5 4-1.5 7-5 7-9.5V5.5L12 3Z"/><path d="m9.5 12 1.8 1.8 3.2-3.6"/>'] : null,
    $u->puede('CMS') ? ['Página web', 'Noticias, misión, visión y contenido del portal', route('admin.contenidos.index'), '<path d="M7 3h7l4 4v14a1 1 0 0 1-1 1H7a1 1 0 0 1-1-1V4a1 1 0 0 1 1-1Z"/><path d="M14 3v4h4"/><path d="M9 13h6M9 17h6"/>'] : null,
  ]);
@endphp

<p class="text-muted" style="margin-top: -12px;">{{ $saludo }}, {{ $u->nombre }}.
  @if ($kpi && $kpi['recibidos'])
    Hay <strong>{{ $kpi['recibidos'] }}</strong> {{ $kpi['recibidos'] === 1 ? 'solicitud pendiente' : 'solicitudes pendientes' }} de revisar.
  @elseif ($kpi)
    No hay solicitudes pendientes.
  @endif
</p>

@if ($kpi)
<div class="stat-strip">
  <a href="{{ route('admin.expedientes.index') }}"><div class="stat-label">Recibidas este mes</div><div class="stat-value">{{ $kpi['mes'] }}</div></a>
  <a href="{{ route('admin.expedientes.index', ['estado' => 'RECEIVED']) }}"><div class="stat-label">Por protocolizar</div><div class="stat-value {{ $kpi['recibidos'] ? 'accent' : '' }}">{{ $kpi['recibidos'] }}</div></a>
  <a href="{{ route('admin.expedientes.index') }}"><div class="stat-label">En trámite</div><div class="stat-value">{{ $kpi['revision'] }}</div></a>
  <a href="{{ route('admin.expedientes.index') }}"><div class="stat-label">Cerrados</div><div class="stat-value">{{ $kpi['cerrados'] }}</div></a>
</div>
@endif

<div class="card mb-4">
  <div class="card-body py-2">
    <div class="module-nav">
      @foreach ($modulos as [$nombre, $texto, $link, $svg])
        <a href="{{ $link }}">
          <div class="module-nav-icon">{!! $icono($svg) !!}</div>
          <div class="module-nav-text"><strong>{{ $nombre }}</strong><span>{{ $texto }}</span></div>
          <i class="fa-solid fa-chevron-right module-nav-chevron"></i>
        </a>
      @endforeach
    </div>
  </div>
</div>

@if ($kpi)
<div class="card overflow-hidden">
  <div class="card-header fw-bold text-primary-cgp d-flex justify-content-between align-items-center">
    Últimos movimientos
    <a href="{{ route('admin.expedientes.index') }}" class="small fw-semibold text-decoration-none text-primary-cgp">Ver todos <i class="fa-solid fa-arrow-right ms-1"></i></a>
  </div>
  <div class="table-responsive">
    <table class="table table-hover align-middle mb-0">
      <thead><tr><th class="ps-4">Fecha / hora</th><th>Movimiento</th><th>Expediente</th><th>Usuario / origen</th></tr></thead>
      <tbody>
        @forelse ($movimientos as $m)
          <tr class="fila-link" onclick="location.href='{{ route('admin.expedientes.show', $m->id) }}'">
            <td class="ps-4 text-secondary text-nowrap">{{ \Illuminate\Support\Carbon::parse($m->changed_at)->timezone(config('app.timezone'))->format('d/m/Y h:i a') }}</td>
            <td class="fw-semibold">{{ $m->previous_status ? 'Pasó a '.$m->estatus : 'Solicitud recibida' }}</td>
            <td><span class="badge bg-light">{{ $m->case_number }}</span></td>
            <td class="text-muted">{{ $m->usuario }}</td>
          </tr>
        @empty
          <tr><td colspan="4" class="text-center text-muted py-4">Todavía no hay movimientos.</td></tr>
        @endforelse
      </tbody>
    </table>
  </div>
</div>
@endif
@endsection
