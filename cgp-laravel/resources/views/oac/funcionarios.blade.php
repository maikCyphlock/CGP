@extends('layouts.oac')

@section('titulo', 'Directorio de Funcionarios')

@section('contenido')
@php
  $puedeEscribir = auth()->user()->puede('CATALOGS', 'write');
@endphp

<div class="alert alert-light border small mb-4">
  <i class="fa-solid fa-users-gear me-2 text-primary"></i>
  Este directorio alimenta la asignación y reasignación de responsables en la pestaña <strong>Valoración y Asignación</strong> de cada expediente, evitando errores de texto libre.
</div>

@if ($puedeEscribir)
<div class="card border-0 shadow-sm mb-4">
  <div class="card-header bg-white fw-bold text-primary-cgp">
    {{ $fila ? 'Editar funcionario responsable' : 'Registrar funcionario en el directorio' }}
  </div>
  <form method="POST"
        action="{{ $fila ? route('oac.funcionarios.update', $fila) : route('oac.funcionarios.store') }}"
        class="card-body row g-3">
    @csrf
    <div class="col-md-6">
      <label class="form-label small fw-semibold" for="area_direccion">Área / Dirección <span class="text-danger">*</span></label>
      <input type="text"
             id="area_direccion"
             name="area_direccion"
             class="form-control form-control-sm @error('area_direccion') is-invalid @enderror"
             maxlength="180"
             required
             list="dl-areas"
             placeholder="Ej. Dirección de Atención al Ciudadano (DAC)"
             value="{{ old('area_direccion', $fila->area_direccion ?? '') }}">
      <datalist id="dl-areas">
        <option value="Dirección de Atención al Ciudadano (DAC)">
        <option value="Área de Atención de Denuncia, Quejas, Reclamos, Asesorías, Sugerencias y Peticiones (AAD)">
        <option value="Área de Información y Promoción a la Participación Ciudadana (AIP)">
        <option value="Despacho del Contralor(a) Municipal">
        <option value="Dirección de Control Posterior">
        <option value="Dirección de Determinación de Responsabilidades / Potestad Investigativa">
        <option value="Unidad de Auditoría Interna (UAI)">
      </datalist>
      @error('area_direccion') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    <div class="col-md-6">
      <label class="form-label small fw-semibold" for="cargo">Cargo <span class="text-danger">*</span></label>
      <input type="text"
             id="cargo"
             name="cargo"
             class="form-control form-control-sm @error('cargo') is-invalid @enderror"
             maxlength="150"
             required
             placeholder="Ej. Director(a), Analista de OAC, Promotor(a)"
             value="{{ old('cargo', $fila->cargo ?? '') }}">
      @error('cargo') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    <div class="col-md-6">
      <label class="form-label small fw-semibold" for="figura">Figura (Manual DAC) <span class="text-danger">*</span></label>
      <input type="text"
             id="figura"
             name="figura"
             class="form-control form-control-sm @error('figura') is-invalid @enderror"
             maxlength="150"
             required
             list="dl-figuras"
             placeholder="Ej. Funcionario(a) / Responsable de Mecanismo"
             value="{{ old('figura', $fila->figura ?? '') }}">
      <datalist id="dl-figuras">
        <option value="Director(a) de la Dirección de Atención al Ciudadano (DAC)">
        <option value="Funcionario(a) / Responsable de Mecanismo">
        <option value="Promotor(a) Adscrito a la DAC">
        <option value="Contralor(a) Municipal">
        <option value="Analista de OAC">
      </datalist>
      @error('figura') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    <div class="col-md-4">
      <label class="form-label small fw-semibold" for="nombre_completo">Funcionario (Nombre completo) <span class="text-danger">*</span></label>
      <input type="text"
             id="nombre_completo"
             name="nombre_completo"
             class="form-control form-control-sm @error('nombre_completo') is-invalid @enderror"
             maxlength="160"
             required
             placeholder="Ej. Ing. Juan Romero"
             value="{{ old('nombre_completo', $fila->nombre_completo ?? '') }}">
      @error('nombre_completo') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    <div class="col-md-2">
      <label class="form-label small fw-semibold" for="cedula">Cédula <span class="text-muted">(opc.)</span></label>
      <input type="text"
             id="cedula"
             name="cedula"
             class="form-control form-control-sm"
             maxlength="20"
             placeholder="V-12345678"
             value="{{ old('cedula', $fila->cedula ?? '') }}">
    </div>

    <div class="col-md-4">
      <label class="form-label small fw-semibold" for="referral_unit_id">Unidad del sistema <span class="text-muted">(opcional)</span></label>
      <select id="referral_unit_id" name="referral_unit_id" class="form-select form-select-sm">
        <option value="">Sin vínculo</option>
        @foreach ($unidades as $un)
          <option value="{{ $un->id }}" @selected(old('referral_unit_id', $fila->referral_unit_id ?? '') == $un->id)>{{ $un->name }}</option>
        @endforeach
      </select>
    </div>

    <div class="col-md-4">
      <label class="form-label small fw-semibold" for="job_position_id">Cargo en catálogo <span class="text-muted">(opcional)</span></label>
      <select id="job_position_id" name="job_position_id" class="form-select form-select-sm">
        <option value="">Sin vínculo</option>
        @foreach ($cargos as $cg)
          <option value="{{ $cg->id }}" @selected(old('job_position_id', $fila->job_position_id ?? '') == $cg->id)>{{ $cg->title }}</option>
        @endforeach
      </select>
    </div>

    <div class="col-md-4">
      <label class="form-label small fw-semibold" for="staff_user_id">Usuario del sistema <span class="text-muted">(opcional)</span></label>
      <select id="staff_user_id" name="staff_user_id" class="form-select form-select-sm">
        <option value="">Sin cuenta de acceso vinculada</option>
        @foreach ($usuarios as $us)
          <option value="{{ $us->id }}" @selected(old('staff_user_id', $fila->staff_user_id ?? '') == $us->id)>{{ $us->first_name }} {{ $us->last_name }}</option>
        @endforeach
      </select>
    </div>

    @if ($fila)
      <div class="col-12 form-check ms-2">
        <input type="hidden" name="activo" value="0">
        <input class="form-check-input" type="checkbox" name="activo" value="1" id="activo_func" @checked(old('activo', $fila->activo))>
        <label class="form-check-label small" for="activo_func">Activo (disponible para asignar expedientes)</label>
      </div>
    @endif

    <div class="col-12 d-flex gap-2">
      <button type="submit" class="btn btn-sm btn-cgp fw-bold px-3">{{ $fila ? 'Guardar cambios' : 'Agregar funcionario' }}</button>
      @if ($fila)
        <a href="{{ route('oac.funcionarios.index') }}" class="btn btn-sm btn-outline-secondary">Cancelar</a>
      @endif
    </div>
  </form>
</div>
@endif

<div class="card border-0 shadow-sm overflow-hidden">
  <div class="table-responsive">
    <table class="table table-hover align-middle mb-0" style="font-size: .88rem;">
      <thead class="table-light">
        <tr>
          <th class="ps-4">Funcionario</th>
          <th>Área / Dirección</th>
          <th>Cargo</th>
          <th>Figura</th>
          <th>Estado</th>
          <th></th>
        </tr>
      </thead>
      <tbody>
        @forelse ($funcionarios as $f)
          <tr class="{{ $f->activo ? '' : 'text-muted' }}">
            <td class="ps-4 fw-semibold">
              {{ $f->nombre_completo }}
              @if ($f->cedula)<div class="text-muted small">{{ $f->cedula }}</div>@endif
            </td>
            <td>{{ $f->area_direccion }}</td>
            <td>{{ $f->cargo }}</td>
            <td>{{ $f->figura }}</td>
            <td>
              <span class="badge {{ $f->activo ? 'bg-success' : 'bg-secondary' }}">{{ $f->activo ? 'Activo' : 'Inactivo' }}</span>
            </td>
            <td class="text-end pe-4">
              @if ($puedeEscribir)
                <a href="{{ route('oac.funcionarios.index', ['editar' => $f->id]) }}" class="btn btn-sm btn-outline-dark">Editar</a>
              @endif
            </td>
          </tr>
        @empty
          <tr><td colspan="6" class="text-center text-muted py-4">No hay funcionarios registrados.</td></tr>
        @endforelse
      </tbody>
    </table>
  </div>
</div>
@endsection
