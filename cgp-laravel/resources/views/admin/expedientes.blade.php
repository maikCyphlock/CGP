@extends('layouts.admin')

@section('titulo', ($filtros['estado'] ?? null) === 'RECEIVED' ? 'Por Protocolizar' : 'Expedientes')

@section('contenido')
@php($link = fn (array $cambios) => route('admin.expedientes.index', array_filter(array_merge($filtros, $cambios))))

{{-- Pestañas por estatus con su cantidad --}}
<div class="d-flex flex-wrap gap-2 mb-3">
  <a href="{{ $link(['estado' => null]) }}" class="btn btn-sm rounded-pill px-3 {{ empty($filtros['estado']) ? 'btn-dark' : 'btn-outline-secondary' }}">
    Todos <span class="badge bg-secondary ms-1">{{ $conteo->sum() }}</span>
  </a>
  @foreach ($estatus as $s)
    <a href="{{ $link(['estado' => $s->code]) }}" class="btn btn-sm rounded-pill px-3 {{ ($filtros['estado'] ?? null) === $s->code ? 'btn-dark' : 'btn-outline-secondary' }}">
      {{ $s->name }} <span class="badge bg-secondary ms-1">{{ $conteo[$s->id] ?? 0 }}</span>
    </a>
  @endforeach
</div>

<form method="GET" class="card border-0 shadow-sm p-3 mb-3 bg-white rounded-3">
  <input type="hidden" name="estado" value="{{ $filtros['estado'] ?? '' }}">
  <div class="row g-2 align-items-center">
    <div class="col-md-6">
      <div class="input-group input-group-sm">
        <span class="input-group-text bg-light"><i class="fa-solid fa-magnifying-glass"></i></span>
        <input type="search" name="q" value="{{ $filtros['q'] ?? '' }}" class="form-control bg-light" placeholder="N° de expediente, cédula o nombre del solicitante">
      </div>
    </div>
    <div class="col-md-4">
      <select name="tipo" class="form-select form-select-sm" onchange="this.form.submit()">
        <option value="">Todos los tipos</option>
        @foreach ($tipos as $t)
          <option value="{{ $t->code }}" @selected(($filtros['tipo'] ?? null) === $t->code)>{{ $t->name }}</option>
        @endforeach
      </select>
    </div>
    <div class="col-md-2 d-grid">
      <button class="btn btn-sm btn-cgp fw-bold">Buscar</button>
    </div>
  </div>
</form>

<div class="card border-0 shadow-sm rounded-3 overflow-hidden">
  <div class="table-responsive">
    <table class="table table-hover align-middle mb-0" style="font-size: .9rem;">
      <thead class="table-light">
        <tr><th class="ps-4">Expediente</th><th>Fecha de ingreso</th><th>Solicitante</th><th>Tipo</th><th>Estatus</th><th></th></tr>
      </thead>
      <tbody>
        @forelse ($expedientes as $e)
          @php($s = $estatus[$e->status_id])
          <tr class="fila-link" onclick="location.href='{{ route('admin.expedientes.show', $e) }}'">
            <td class="ps-4 fw-bold text-primary-cgp text-nowrap">{{ $e->case_number }}</td>
            <td class="text-secondary text-nowrap">{{ $e->created_at->timezone(config('app.timezone'))->format('d/m/Y') }}</td>
            <td>{{ $e->citizen->first_name }} {{ $e->citizen->last_name }} <span class="text-muted">({{ $e->citizen->id_doc_number }})</span></td>
            <td>{{ $e->claimType->name }}</td>
            <td><span class="badge badge-estatus st-{{ $s->code }}">{{ $s->name }}</span></td>
            <td class="text-end pe-4"><a href="{{ route('admin.expedientes.show', $e) }}" class="btn btn-sm {{ $s->code === 'RECEIVED' ? 'btn-cgp' : 'btn-outline-dark' }}">{{ $s->code === 'RECEIVED' ? 'Revisar' : 'Abrir' }}</a></td>
          </tr>
        @empty
          <tr><td colspan="6" class="text-center text-muted py-5">No hay expedientes con estos filtros.</td></tr>
        @endforelse
      </tbody>
    </table>
  </div>
</div>

<div class="mt-3">{{ $expedientes->links('pagination::bootstrap-5') }}</div>
@endsection
