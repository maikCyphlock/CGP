@extends(request()->is('admin', 'admin/*') ? 'layouts.web' : 'layouts.oac')

@section('titulo', 'Catálogos')

@push('estilos')
<style>
  .card-catalogo-item {
    background: #ffffff;
    border: 1px solid #e2e8f0 !important;
    border-radius: 12px;
    transition: transform .2s ease, box-shadow .2s ease, border-color .2s ease;
  }
  .card-catalogo-item:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 20px rgba(13, 27, 62, 0.08) !important;
    border-color: #cbd5e1 !important;
  }
</style>
@endpush

@section('contenido')
@php
  $mod = request()->is('admin', 'admin/*') ? 'admin' : 'oac';
@endphp

<div class="d-flex align-items-center justify-content-between mb-4">
  <div>
    <h4 class="fw-bold text-primary-cgp mb-1">Catálogos Maestros</h4>
    <p class="text-muted small mb-0">Tablas maestras con borrado lógico y control de integridad.</p>
  </div>
  <span class="badge bg-light text-secondary border px-3 py-2 font-monospace">
    {{ $catalogos->count() }} tablas
  </span>
</div>

<div class="row g-3">
  @foreach ($catalogos as $slug => $c)
    <div class="col-md-6 col-xl-4">
      <div class="card card-catalogo-item shadow-sm h-100">
        <div class="card-body p-4 d-flex flex-column justify-content-between">
          <div>
            <div class="d-flex align-items-center justify-content-between mb-3">
              <h6 class="fw-bold text-primary-cgp mb-0 fs-5">{{ $c['titulo'] }}</h6>
              <span class="badge bg-light text-muted border font-monospace small">
                {{ $c['tabla'] }}
              </span>
            </div>

            <div class="d-flex align-items-center gap-2 mb-3">
              <span class="badge bg-light text-dark border">
                {{ $c['total'] }} {{ $c['total'] === 1 ? 'registro' : 'registros' }}
              </span>
              <span class="badge bg-success-subtle text-success border border-success-subtle">
                {{ $c['activos'] }} activos
              </span>
              @if ($c['inactivos'] > 0)
                <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle">
                  {{ $c['inactivos'] }} inactivos
                </span>
              @endif
            </div>
          </div>

          <div class="pt-2 border-top">
            <a href="{{ route($mod.'.catalogos.show', $slug) }}" class="btn btn-sm btn-cgp fw-bold w-100 d-flex align-items-center justify-content-center gap-2">
              <span>Gestionar</span>
              <i class="fa-solid fa-arrow-right"></i>
            </a>
          </div>
        </div>
      </div>
    </div>
  @endforeach
</div>
@endsection
