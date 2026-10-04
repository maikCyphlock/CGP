@extends('layouts.panel')

@section('titulo', 'Catálogos')

@section('contenido')
<p class="text-muted">Listas que alimentan el formulario público y el panel. Los registros no se borran: se desactivan, para no afectar expedientes anteriores.</p>
<div class="row g-3">
  @foreach ($catalogos as $slug => $c)
    <div class="col-md-6 col-xl-4">
      <a href="{{ route($mod.'.catalogos.show', $slug) }}" class="card card-kpi border-0 shadow-sm h-100 text-decoration-none">
        <div class="card-body p-4">
          <h6 class="fw-bold text-primary-cgp mb-1">{{ $c['titulo'] }}</h6>
          <span class="text-muted small">{{ $c['total'] }} registros</span>
        </div>
      </a>
    </div>
  @endforeach
</div>
@endsection
