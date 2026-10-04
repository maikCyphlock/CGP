@extends('layouts.panel')

@section('titulo', 'Administración del sistema')

@section('contenido')
@php
  $u = auth()->user();
  $hora = now()->timezone(config('app.timezone'))->hour;
  $saludo = $hora < 12 ? 'Buen día' : ($hora < 19 ? 'Buenas tardes' : 'Buenas noches');
  $icono = fn ($d) => '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round">'.$d.'</svg>';
  $modulos = array_filter([
    $u->puede('STATS') ? ['Monitoreo del sistema', 'Sesiones abiertas, accesos denegados, cambios de permisos y bloqueos', route('admin.monitoreo'), '<path d="M3 12h4l2-6 4 12 2-6h6"/>'] : null,
    $u->puede('USERS') ? ['Usuarios y accesos', 'Personal interno y permisos por módulo', route('admin.usuarios.index'), '<path d="M12 3 5 5.5V11c0 4.5 3 8 7 9.5 4-1.5 7-5 7-9.5V5.5L12 3Z"/><path d="m9.5 12 1.8 1.8 3.2-3.6"/>'] : null,
    $u->puede('USERS') ? ['Cargos del personal', 'Lista de cargos que se asignan a cada usuario', route('admin.catalogos.show', 'cargos'), '<rect x="4" y="5" width="16" height="14" rx="2"/><path d="M8 10h8M8 14h5"/>'] : null,
    $u->puede('CMS') ? ['Página web', 'Noticias, misión, visión y contenido del portal', route('admin.contenidos.index'), '<path d="M7 3h7l4 4v14a1 1 0 0 1-1 1H7a1 1 0 0 1-1-1V4a1 1 0 0 1 1-1Z"/><path d="M14 3v4h4"/><path d="M9 13h6M9 17h6"/>'] : null,
  ]);
@endphp

<p class="text-muted" style="margin-top: -12px;">{{ $saludo }}, {{ $u->nombre }}.
  @if ($r && ($r['bloqueadas'] || $r['denegados']))
    Hay <strong>{{ $r['bloqueadas'] }}</strong> {{ $r['bloqueadas'] === 1 ? 'cuenta bloqueada' : 'cuentas bloqueadas' }} y <strong>{{ $r['denegados'] }}</strong> {{ $r['denegados'] === 1 ? 'acceso denegado' : 'accesos denegados' }} en las últimas 24 horas.
  @elseif ($r)
    Todo en orden: sin bloqueos ni accesos denegados en las últimas 24 horas.
  @endif
</p>

@if ($r)
<div class="stat-strip">
  <a href="{{ route('admin.monitoreo', ['ver' => 'sesiones']) }}"><div class="stat-label">Sesiones abiertas</div><div class="stat-value">{{ $r['sesiones'] }}</div></a>
  <a href="{{ route('admin.monitoreo', ['ver' => 'bloqueos']) }}"><div class="stat-label">Cuentas bloqueadas</div><div class="stat-value {{ $r['bloqueadas'] ? 'accent' : '' }}">{{ $r['bloqueadas'] }}</div></a>
  <a href="{{ route('admin.monitoreo', ['ver' => 'denegados']) }}"><div class="stat-label">Accesos denegados (24 h)</div><div class="stat-value {{ $r['denegados'] ? 'accent' : '' }}">{{ $r['denegados'] }}</div></a>
  <a href="{{ route('admin.monitoreo', ['ver' => 'permisos']) }}"><div class="stat-label">Cambios de permisos (7 días)</div><div class="stat-value">{{ $r['permisos'] }}</div></a>
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
@endsection
