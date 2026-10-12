@extends('layouts.oac')

@section('titulo', 'Sistema de Control y Verificación')

@section('contenido')
@php
  $u = auth()->user();
  $hora = now()->timezone(config('app.timezone'))->hour;
  $saludo = $hora < 12 ? 'Buenos días' : ($hora < 18 ? 'Buenas tardes' : 'Buenas noches');
  $total = $kpi ? max(1, $kpi['recibidos'] + $kpi['revision'] + $kpi['cerrados']) : 1;
@endphp

<div class="welcome-banner p-4 p-md-5 mb-4">
  <div class="row align-items-center flex-column-reverse flex-lg-row">
    <div class="col-lg-8 text-center text-lg-start mt-4 mt-lg-0">
      <div class="banner-pill">Contraloría del Municipio Páez</div>
      <h1 class="fw-bold display-6 mb-2">{{ $saludo }}, {{ $u->first_name }}</h1>
      <p class="text-white-50 mb-4" style="max-width: 650px;">
        Sistema institucional para la recepción y el seguimiento de las denuncias, quejas y reclamos que llegan por el Portal Web.
        @if ($kpi && $kpi['recibidos'])
          Hay <strong class="text-white">{{ $kpi['recibidos'] }}</strong> {{ $kpi['recibidos'] === 1 ? 'solicitud pendiente' : 'solicitudes pendientes' }} de protocolizar.
        @endif
      </p>
      <div class="d-flex justify-content-center justify-content-lg-start gap-2 flex-wrap">
        @if ($kpi && $kpi['recibidos'])
          <a href="{{ route('oac.expedientes.index', ['estado' => 'RECEIVED']) }}" class="btn btn-update px-4 py-2 fw-bold"><i class="fa-solid fa-inbox me-2"></i> Revisar pendientes</a>
        @endif
        <button class="btn btn-update px-4 py-2 fw-bold" onclick="location.reload()"><i class="fa-solid fa-rotate me-2"></i> Actualizar bandeja</button>
      </div>
    </div>
    <div class="col-lg-4 text-center">
      <div class="banner-photo-container"><img src="{{ asset('assets/img/logonuevo.jpeg') }}" alt=""></div>
      <span class="small text-white-50 fw-semibold">Oficina de Atención al Ciudadano</span>
    </div>
  </div>
</div>

@if ($kpi)
<div class="row g-3 mb-4">
  <div class="col-xl-3 col-md-6">
    <a href="{{ route('oac.expedientes.index', ['estado' => 'RECEIVED']) }}" class="kpi-card">
      <div class="kpi-header-top"><span class="kpi-category">Portal / Ingresos</span><span class="kpi-status-badge {{ $kpi['recibidos'] ? 'alerta' : 'on' }}">{{ $kpi['recibidos'] ? 'Pendiente' : 'Al día' }}</span></div>
      <div class="kpi-title">Por protocolizar</div>
      <div class="kpi-value {{ $kpi['recibidos'] ? 'accent' : '' }}">{{ $kpi['recibidos'] }}</div>
      <div class="kpi-bar"><div style="width: {{ round(100 * $kpi['recibidos'] / $total) }}%"></div></div>
      <p class="kpi-desc">Solicitudes recibidas por el portal que esperan revisión.</p>
    </a>
  </div>
  <div class="col-xl-3 col-md-6">
    <a href="{{ route('oac.expedientes.index') }}" class="kpi-card">
      <div class="kpi-header-top"><span class="kpi-category">Fase / Análisis</span><span class="kpi-status-badge">En curso</span></div>
      <div class="kpi-title">En trámite</div>
      <div class="kpi-value">{{ $kpi['revision'] }}</div>
      <div class="kpi-bar"><div style="width: {{ round(100 * $kpi['revision'] / $total) }}%"></div></div>
      <p class="kpi-desc">Expedientes en revisión, procesados o derivados a otra unidad.</p>
    </a>
  </div>
  <div class="col-xl-3 col-md-6">
    <a href="{{ route('oac.expedientes.index') }}" class="kpi-card">
      <div class="kpi-header-top"><span class="kpi-category">Cierre / Legal</span><span class="kpi-status-badge">Concluido</span></div>
      <div class="kpi-title">Cerrados</div>
      <div class="kpi-value">{{ $kpi['cerrados'] }}</div>
      <div class="kpi-bar"><div style="width: {{ round(100 * $kpi['cerrados'] / $total) }}%"></div></div>
      <p class="kpi-desc">Casos archivados o anulados con estatus definitivo.</p>
    </a>
  </div>
  <div class="col-xl-3 col-md-6">
    <a href="{{ route('oac.expedientes.index') }}" class="kpi-card">
      <div class="kpi-header-top"><span class="kpi-category">Mes / Recepción</span><span class="kpi-status-badge on">Activo</span></div>
      <div class="kpi-title">Recibidas este mes</div>
      <div class="kpi-value">{{ $kpi['mes'] }}</div>
      <div class="kpi-bar"><div style="width: 100%"></div></div>
      <p class="kpi-desc">Total de casos ingresados por el Apartado de Denuncias desde el día 1.</p>
    </a>
  </div>
</div>

<div class="nota"><strong>Nota:</strong> haga clic en cualquier registro de la tabla para abrir el expediente con su historial completo.</div>

<div class="card overflow-hidden">
  <div class="card-header d-flex justify-content-between align-items-center">
    Últimos movimientos
    <a href="{{ route('oac.expedientes.index') }}" class="small fw-semibold text-decoration-none text-primary-cgp text-capitalize">Ver todos <i class="fa-solid fa-arrow-right ms-1"></i></a>
  </div>
  <div class="table-responsive">
    <table class="table table-hover align-middle mb-0">
      <thead><tr><th class="ps-4">Fecha / Hora</th><th>Acción registrada</th><th>Referencia</th><th>Usuario / Origen</th></tr></thead>
      <tbody>
        @forelse ($movimientos as $m)
          <tr class="fila-link" onclick="location.href='{{ route('oac.expedientes.show', $m->id) }}'">
            <td class="ps-4 text-secondary fw-semibold text-nowrap">{{ \Illuminate\Support\Carbon::parse($m->changed_at)->timezone(config('app.timezone'))->format('d/m/Y - h:i a') }}</td>
            <td class="fw-bold text-primary-cgp">{{ $m->previous_status ? 'Pasó a '.$m->estatus : 'Recepción de denuncia ciudadana' }}</td>
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
@else
<div class="card"><div class="card-body text-muted">Su cuenta no tiene acceso a los expedientes. Use el menú lateral para entrar a las secciones permitidas.</div></div>
@endif
@endsection
