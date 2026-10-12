@extends('layouts.web')

@section('titulo', 'Panel de Control')

@section('contenido')
@php
  $u = auth()->user();
  $hora = now()->timezone(config('app.timezone'))->hour;
  $saludo = $hora < 12 ? 'Buenos días' : ($hora < 18 ? 'Buenas tardes' : 'Buenas noches');
  $alerta = $r && ($r['bloqueadas'] || $r['denegados']);
  $accesos = array_filter([
    $u->puede('CMS', 'write') ? ['Nuevo contenido', 'Redacte una noticia o actualice misión y visión.', route('admin.contenidos.create'), 'fa-pen-nib text-primary'] : null,
    $u->puede('CMS') ? ['Noticias y contenido', 'Revise lo publicado y los borradores.', route('admin.contenidos.index'), 'fa-newspaper text-danger'] : null,
    $u->puede('STATS') ? ['Monitoreo', 'Sesiones, accesos denegados y bloqueos.', route('admin.monitoreo'), 'fa-chart-line text-success'] : null,
    $u->puede('USERS') ? ['Usuarios y accesos', 'Personal interno y permisos por módulo.', route('admin.usuarios.index'), 'fa-user-shield text-secondary'] : null,
  ]);
@endphp

<div class="welcome-banner">
  <div class="system-status-badge">
    <span class="pulse-dot {{ $alerta ? 'alerta' : '' }}"></span>
    {{ $alerta ? 'Hay accesos que revisar' : 'Sistema operando con normalidad' }}
  </div>
  <h1>{{ $saludo }}, {{ $u->nombre }}</h1>
  <p>Bienvenido al centro de gestión de contenidos del portal institucional. Desde este panel puede coordinar las publicaciones del sitio y supervisar su actividad.</p>
  <div class="d-flex gap-2 flex-wrap">
    @if ($u->puede('CMS', 'write'))
      <a href="{{ route('admin.contenidos.create') }}" class="btn btn-light btn-sm fw-bold px-3 py-2 text-dark"><i class="fa-solid fa-pen-nib me-1 text-primary"></i> Redactar contenido</a>
    @endif
    <a href="{{ url('/') }}" target="_blank" class="btn btn-danger btn-sm fw-bold px-3 py-2"><i class="fa-solid fa-arrow-up-right-from-square me-1"></i> Ver el portal</a>
  </div>
</div>

<div class="row g-3 mb-4">
  @if ($publicados !== null)
    <div class="col-md-4">
      <a href="{{ route('admin.contenidos.index') }}" class="kpi-card">
        <div class="kpi-header-top"><span class="kpi-category">Portal / Publicado</span><span class="kpi-status-badge on">Activo</span></div>
        <div class="kpi-title">Contenidos publicados</div>
        <div class="kpi-value">{{ $publicados }}</div>
        <div class="kpi-bar"><div style="width: {{ $publicados + $borradores ? round($publicados * 100 / ($publicados + $borradores)) : 0 }}%"></div></div>
        <p class="kpi-desc">Noticias y textos institucionales visibles hoy en el portal web.</p>
      </a>
    </div>
    <div class="col-md-4">
      <a href="{{ route('admin.contenidos.index') }}" class="kpi-card">
        <div class="kpi-header-top"><span class="kpi-category">Redacción / Borrador</span><span class="kpi-status-badge">En curso</span></div>
        <div class="kpi-title">Borradores</div>
        <div class="kpi-value">{{ $borradores }}</div>
        <div class="kpi-bar"><div style="width: {{ $publicados + $borradores ? round($borradores * 100 / ($publicados + $borradores)) : 0 }}%"></div></div>
        <p class="kpi-desc">Contenidos guardados que aún no se publican en el sitio.</p>
      </a>
    </div>
  @endif
  @if ($r)
    <div class="col-md-4">
      <a href="{{ route('admin.monitoreo', ['ver' => 'sesiones']) }}" class="kpi-card">
        <div class="kpi-header-top"><span class="kpi-category">Sistema / Sesiones</span><span class="kpi-status-badge {{ $alerta ? 'alerta' : 'on' }}">{{ $alerta ? 'Revisar' : 'Normal' }}</span></div>
        <div class="kpi-title">Sesiones abiertas</div>
        <div class="kpi-value">{{ $r['sesiones'] }}</div>
        <div class="kpi-bar"><div style="width: 100%"></div></div>
        <p class="kpi-desc">{{ $r['bloqueadas'] }} {{ $r['bloqueadas'] === 1 ? 'cuenta bloqueada' : 'cuentas bloqueadas' }} y {{ $r['denegados'] }} {{ $r['denegados'] === 1 ? 'acceso denegado' : 'accesos denegados' }} en las últimas 24 horas.</p>
      </a>
    </div>
  @endif
</div>

<div class="row">
  <div class="col-lg-7 mb-4">
    <div class="card h-100">
      <div class="card-header">Accesos directos del sistema</div>
      <div class="card-body">
        <div class="row g-3">
          @foreach ($accesos as [$nombre, $texto, $link, $icono])
            <div class="col-sm-6">
              <a href="{{ $link }}" class="shortcut">
                <i class="fa-solid {{ $icono }}"></i>
                <h6>{{ $nombre }}</h6>
                <p>{{ $texto }}</p>
              </a>
            </div>
          @endforeach
        </div>
      </div>
    </div>
  </div>

  <div class="col-lg-5 mb-4">
    <div class="card h-100">
      <div class="card-header">Bitácora de actividad</div>
      <div class="card-body">
        @if ($actividad->isEmpty())
          <p class="text-muted mb-0">Aún no hay actividad en el contenido web.</p>
        @else
          <div class="activity-timeline">
            @foreach ($actividad as $a)
              <div class="activity-item">
                <div class="activity-dot {{ $loop->first ? 'accent' : '' }}"></div>
                <p><strong>{{ $a->autor }}</strong> {{ $a->published ? 'publicó' : 'guardó como borrador' }} <a href="{{ route('admin.contenidos.edit', $a->id) }}" class="fw-semibold text-decoration-none">{{ $a->title }}</a>.</p>
                <span class="activity-time">{{ \Illuminate\Support\Carbon::parse($a->updated_at)->locale('es')->diffForHumans() }}</span>
              </div>
            @endforeach
          </div>
        @endif
      </div>
    </div>
  </div>
</div>
@endsection
