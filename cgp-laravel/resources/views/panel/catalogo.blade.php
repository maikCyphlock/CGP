@extends('layouts.panel')

@section('titulo', $c['titulo'])

@section('contenido')
@php($puedeEscribir = auth()->user()->puede('CATALOGS', 'write'))
<a href="{{ route($mod.'.catalogos.index') }}" class="btn btn-sm btn-outline-secondary mb-3"><i class="fa-solid fa-arrow-left me-1"></i> Catálogos</a>

@if ($puedeEscribir)
<div class="card border-0 shadow-sm mb-4">
  <div class="card-header bg-white fw-bold text-primary-cgp">{{ $fila ? 'Editar registro' : 'Nuevo registro' }}</div>
  <form method="POST" class="card-body row g-3"
        action="{{ $fila ? route($mod.'.catalogos.update', [$slug, $fila->id]) : route($mod.'.catalogos.store', $slug) }}">
    @csrf
    @foreach ($c['campos'] as [$campo, $etiqueta, $tipo, $extra])
      @php($valor = old($campo, $fila->$campo ?? ($tipo === 'json' ? '[]' : '')))
      <div class="{{ in_array($tipo, ['textarea', 'json']) ? 'col-12' : 'col-md-6' }}">
        <label class="form-label fw-semibold small">{{ $etiqueta }}</label>
        @if ($campo === 'code' && $fila)
          <input class="form-control" value="{{ $valor }}" disabled>
        @elseif ($tipo === 'select')
          <select name="{{ $campo }}" class="form-select" required>
            @foreach ($extra as $k => $t) <option value="{{ $k }}" @selected($valor === $k)>{{ $t }}</option> @endforeach
          </select>
        @elseif ($tipo === 'textarea' || $tipo === 'json')
          <textarea name="{{ $campo }}" class="form-control {{ $tipo === 'json' ? 'font-monospace' : '' }}" rows="{{ $tipo === 'json' ? 5 : 3 }}" @if ($tipo === 'textarea') maxlength="{{ $extra }}" @endif @required($tipo === 'json')>{{ is_string($valor) ? $valor : json_encode($valor) }}</textarea>
        @else
          <input name="{{ $campo }}" class="form-control" value="{{ $valor }}" maxlength="{{ $extra }}" required>
          @if ($campo === 'code') <div class="form-text">Mayúsculas, números y guion bajo. No se puede cambiar después.</div> @endif
        @endif
      </div>
    @endforeach
    @if ($fila)
      <div class="col-12 form-check ms-2">
        <input type="hidden" name="active" value="0">
        <input class="form-check-input" type="checkbox" name="active" value="1" id="activo" @checked(old('active', $fila->active))>
        <label class="form-check-label" for="activo">Activo (aparece en el formulario y el panel)</label>
      </div>
    @endif
    <div class="col-12 d-flex gap-2">
      <button type="submit" class="btn btn-cgp fw-bold">{{ $fila ? 'Guardar cambios' : 'Agregar' }}</button>
      @if ($fila) <a href="{{ route($mod.'.catalogos.show', $slug) }}" class="btn btn-outline-secondary">Cancelar</a> @endif
    </div>
  </form>
</div>
@endif

<div class="card border-0 shadow-sm overflow-hidden">
  <div class="table-responsive">
    <table class="table table-hover align-middle mb-0" style="font-size: .9rem;">
      <thead class="table-light"><tr>
        @foreach ($c['campos'] as [$campo, $etiqueta, $tipo]) @if (! in_array($tipo, ['json', 'textarea'])) <th class="{{ $loop->first ? 'ps-4' : '' }}">{{ $etiqueta }}</th> @endif @endforeach
        <th>Estado</th><th></th>
      </tr></thead>
      <tbody>
        @foreach ($filas as $f)
          <tr class="{{ $f->active ? '' : 'text-muted' }}">
            @foreach ($c['campos'] as [$campo, $etiqueta, $tipo, $extra])
              @if (! in_array($tipo, ['json', 'textarea']))
                <td class="{{ $loop->first ? 'ps-4 fw-semibold' : '' }}">{{ $tipo === 'select' ? ($extra[$f->$campo] ?? $f->$campo) : $f->$campo }}</td>
              @endif
            @endforeach
            <td><span class="badge {{ $f->active ? 'bg-success' : 'bg-secondary' }}">{{ $f->active ? 'Activo' : 'Inactivo' }}</span></td>
            <td class="text-end pe-4">@if ($puedeEscribir)<a href="{{ route($mod.'.catalogos.show', ['slug' => $slug, 'editar' => $f->id]) }}" class="btn btn-sm btn-outline-dark">Editar</a>@endif</td>
          </tr>
        @endforeach
      </tbody>
    </table>
  </div>
</div>
@endsection
