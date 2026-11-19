@extends('layouts.oac')

@section('titulo', !empty($esPapelera) ? 'Papelera de Expedientes' : (($filtros['estado'] ?? null) === 'RECEIVED' ? 'Revisión y Protocolización' : 'Expedientes'))

@section('contenido')
@php($link = fn (array $cambios) => route('oac.expedientes.index', array_filter(array_merge($filtros, $cambios))))

{{-- Pestañas por estatus con su cantidad + Papelera --}}
<div class="d-flex flex-wrap gap-2 mb-3">
  <a href="{{ $link(['estado' => null, 'papelera' => null]) }}" class="btn btn-sm px-3 {{ empty($filtros['estado']) && empty($esPapelera) ? 'btn-dark' : 'btn-outline-secondary' }}">
    Todos <span class="badge bg-secondary ms-1">{{ $conteo->sum() }}</span>
  </a>
  @foreach ($estatus as $s)
    <a href="{{ $link(['estado' => $s->code, 'papelera' => null]) }}" class="btn btn-sm px-3 {{ ($filtros['estado'] ?? null) === $s->code && empty($esPapelera) ? 'btn-dark' : 'btn-outline-secondary' }}">
      {{ $s->name }} <span class="badge bg-secondary ms-1">{{ $conteo[$s->id] ?? 0 }}</span>
    </a>
  @endforeach
  <a href="{{ $link(['estado' => null, 'tipo' => null, 'papelera' => '1']) }}" class="btn btn-sm px-3 ms-auto {{ !empty($esPapelera) ? 'btn-danger text-white' : 'btn-outline-danger' }}" title="Expedientes eliminados lógicamente">
    <i class="fa-solid fa-trash-can me-1"></i> Papelera <span class="badge {{ !empty($esPapelera) ? 'bg-light text-danger' : 'bg-danger' }} ms-1">{{ $conteoPapelera }}</span>
  </a>
</div>

<form method="GET" class="card border-0 shadow-sm p-3 mb-3 bg-white rounded-3">
  @if (!empty($esPapelera))
    <input type="hidden" name="papelera" value="1">
  @else
    <input type="hidden" name="estado" value="{{ $filtros['estado'] ?? '' }}">
  @endif
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
        <tr>
          <th class="ps-4">Expediente</th>
          <th>Fecha de ingreso</th>
          <th>Solicitante</th>
          <th>Tipo</th>
          <th>Estatus</th>
          <th class="text-end pe-4">Acciones</th>
        </tr>
      </thead>
      <tbody>
        @forelse ($expedientes as $e)
          @php($s = $estatus[$e->status_id])
          <tr class="fila-link" onclick="location.href='{{ route('oac.expedientes.show', $e) }}'">
            <td class="ps-4 fw-bold text-primary-cgp text-nowrap">
              {{ $e->case_number }}
              @if ($e->trashed())
                <span class="badge bg-danger ms-1" style="font-size: .7rem;">Eliminado</span>
              @endif
            </td>
            <td class="text-secondary text-nowrap">{{ $e->created_at->timezone(config('app.timezone'))->format('d/m/Y') }}</td>
            <td>{{ $e->citizen->first_name }} {{ $e->citizen->last_name }} <span class="text-muted">({{ $e->citizen->id_doc_number }})</span></td>
            <td>{{ $e->claimType->name }}</td>
            <td><span class="badge badge-estatus st-{{ $s->code }}">{{ $s->name }}</span></td>
            <td class="text-end pe-4 text-nowrap" onclick="event.stopPropagation();">
              <div class="d-inline-flex gap-1 align-items-center">
                @if ($e->trashed())
                  {{-- Visualizar expediente eliminado --}}
                  <a href="{{ route('oac.expedientes.show', $e) }}" class="btn btn-sm btn-outline-primary" title="Visualizar contenido">
                    <i class="fa-solid fa-eye"></i>
                  </a>
                  @if ($puedeEscribir)
                    {{-- Restaurar expediente eliminado lógicamente --}}
                    <form method="POST" action="{{ route('oac.expedientes.restore', $e->id) }}" class="d-inline">
                      @csrf
                      <button type="submit" class="btn btn-sm btn-success" title="Restaurar expediente a las listas activas">
                        <i class="fa-solid fa-rotate-left me-1"></i> Restaurar
                      </button>
                    </form>
                  @endif
                @else
                  {{-- Visualizar contenido del expediente --}}
                  <a href="{{ route('oac.expedientes.show', $e) }}" class="btn btn-sm btn-outline-primary" title="Visualizar contenido completo">
                    <i class="fa-solid fa-eye"></i>
                  </a>

                  {{-- Planilla PDF --}}
                  <a href="{{ route('denuncias.planilla', $e->tracking_code) }}" class="btn btn-sm btn-outline-secondary ver-planilla" title="Ver planilla PDF" data-titulo="{{ $e->case_number }}">
                    <i class="fa-solid fa-file-pdf"></i>
                  </a>

                  @if ($puedeEscribir)
                    {{-- Editar datos de la denuncia --}}
                    <a href="{{ route('oac.expedientes.edit', $e) }}" class="btn btn-sm btn-outline-warning text-dark" title="Editar datos de la denuncia">
                      <i class="fa-solid fa-pen-to-square"></i>
                    </a>

                    {{-- Eliminado lógico --}}
                    <button type="button" class="btn btn-sm btn-outline-danger btn-eliminar-expediente" data-numero="{{ $e->case_number }}" data-action="{{ route('oac.expedientes.destroy', $e) }}" title="Eliminado lógico">
                      <i class="fa-solid fa-trash-can"></i>
                    </button>
                  @endif

                  @if ($s->code === 'RECEIVED')
                    <a href="{{ route('oac.expedientes.show', $e) }}" class="btn btn-sm btn-cgp ms-1" title="Protocolizar expediente">
                      Protocolizar
                    </a>
                  @endif
                @endif
              </div>
            </td>
          </tr>
        @empty
          <tr>
            <td colspan="6" class="text-center text-muted py-5">
              @if (!empty($esPapelera))
                <i class="fa-solid fa-trash-can-arrow-up fs-3 text-muted d-block mb-2"></i>
                No hay expedientes en la papelera.
              @else
                No hay expedientes con estos filtros.
              @endif
            </td>
          </tr>
        @endforelse
      </tbody>
    </table>
  </div>
</div>

@include('oac._modal_planilla')
@include('oac._modal_eliminar')

<div class="mt-3">{{ $expedientes->links('pagination::bootstrap-5') }}</div>
@endsection
