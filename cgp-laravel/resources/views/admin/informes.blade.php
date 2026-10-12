@extends('layouts.web')

@section('titulo', 'Informes')

@php($S = \App\Http\Controllers\Admin\InformeController::SECCIONES)
@php($P = \App\Http\Controllers\Admin\InformeController::PERIODOS)

@section('contenido')
<p class="text-muted">Informes que se publican en las páginas de Contraloría Escolar y Gestión Comunitaria.</p>

@if (auth()->user()->puede('CMS', 'write'))
<div class="card border-0 shadow-sm mb-4">
  <div class="card-body">
    <h6 class="fw-bold mb-3"><i class="fa-solid fa-plus me-1"></i> Nuevo informe</h6>
    <form method="POST" action="{{ route('admin.informes.store') }}" enctype="multipart/form-data" class="row g-3">
      @csrf
      <div class="col-md-3">
        <label class="form-label">Sección</label>
        <select name="section" class="form-select" required>
          @foreach ($S as $k => $v) <option value="{{ $k }}" @selected(old('section') === $k)>{{ $v }}</option> @endforeach
        </select>
      </div>
      <div class="col-md-3">
        <label class="form-label">Período</label>
        <select name="period" class="form-select" required>
          @foreach ($P as $k => $v) <option value="{{ $k }}" @selected(old('period') === $k)>{{ $v }}</option> @endforeach
        </select>
      </div>
      <div class="col-md-2">
        <label class="form-label">Año</label>
        <input type="number" name="year" class="form-control" min="2000" max="2100" value="{{ old('year', now()->year) }}" required>
      </div>
      <div class="col-md-4">
        <label class="form-label">Descripción del período</label>
        <input type="text" name="label" class="form-control" maxlength="60" placeholder="Ej.: Enero, I Trimestre, Año Completo" value="{{ old('label') }}" required>
      </div>
      <div class="col-md-6">
        <label class="form-label">Informe (PDF, máx. 20 MB)</label>
        <input type="file" name="pdf" class="form-control" accept="application/pdf" required>
      </div>
      <div class="col-md-6">
        <label class="form-label">Imagen de vista previa (opcional)</label>
        <input type="file" name="imagen" class="form-control" accept="image/jpeg,image/png,image/webp">
      </div>
      <div class="col-12 text-end">
        <button class="btn btn-cgp fw-bold"><i class="fa-solid fa-upload me-1"></i> Publicar informe</button>
      </div>
    </form>
  </div>
</div>
@endif

<div class="card border-0 shadow-sm overflow-hidden">
  <div class="table-responsive">
    <table class="table table-hover align-middle mb-0" style="font-size: .9rem;">
      <thead class="table-light"><tr><th class="ps-4">Sección</th><th>Período</th><th>Año</th><th>Descripción</th><th>Archivos</th><th></th></tr></thead>
      <tbody>
        @forelse ($items as $i)
          <tr>
            <td class="ps-4 fw-bold text-primary-cgp">{{ $S[$i->section] }}</td>
            <td>{{ $P[$i->period] }}</td>
            <td>{{ $i->year }}</td>
            <td>{{ $i->label }}</td>
            <td>
              <a href="{{ route('media.informe', $i->pdf_path) }}" target="_blank" class="me-2"><i class="fa-solid fa-file-pdf"></i> PDF</a>
              @if ($i->image_path) <a href="{{ route('media.informe', $i->image_path) }}" target="_blank"><i class="fa-solid fa-image"></i> Imagen</a> @endif
            </td>
            <td class="text-end pe-4">
              @if (auth()->user()->puede('CMS', 'delete'))
                <form method="POST" action="{{ route('admin.informes.destroy', $i->id) }}" onsubmit="return confirm('¿Eliminar este informe?')">
                  @csrf
                  <button class="btn btn-sm btn-outline-danger"><i class="fa-solid fa-trash"></i></button>
                </form>
              @endif
            </td>
          </tr>
        @empty
          <tr><td colspan="6" class="text-center text-muted py-5">Aún no hay informes.</td></tr>
        @endforelse
      </tbody>
    </table>
  </div>
</div>
@endsection
