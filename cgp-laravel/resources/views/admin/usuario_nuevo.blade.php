@extends('layouts.panel')

@section('titulo', 'Nuevo usuario')

@section('contenido')
<div class="card border-0 shadow-sm" style="max-width: 760px;">
  <div class="card-body p-4">
    <form method="POST" action="{{ route('admin.usuarios.store') }}" class="row g-3">
      @csrf
      <div class="col-md-6"><label class="form-label fw-semibold small">Nombres</label><input name="first_name" class="form-control" value="{{ old('first_name') }}" maxlength="80" required></div>
      <div class="col-md-6"><label class="form-label fw-semibold small">Apellidos</label><input name="last_name" class="form-control" value="{{ old('last_name') }}" maxlength="80" required></div>
      <div class="col-md-4">
        <label class="form-label fw-semibold small">Tipo de cédula</label>
        <select name="id_doc_type_id" class="form-select" required>
          @foreach ($tiposDoc as $t) <option value="{{ $t->id }}" @selected(old('id_doc_type_id') == $t->id)>{{ $t->code }}</option> @endforeach
        </select>
      </div>
      <div class="col-md-8"><label class="form-label fw-semibold small">Cédula</label><input name="id_doc_number" class="form-control" value="{{ old('id_doc_number') }}" maxlength="12" inputmode="numeric" required></div>
      <div class="col-md-6"><label class="form-label fw-semibold small">Correo</label><input type="email" name="email" class="form-control" value="{{ old('email') }}" maxlength="150" required></div>
      <div class="col-md-6">
        <label class="form-label fw-semibold small">Cargo</label>
        <select name="job_position_id" class="form-select" required>
          @foreach ($cargos as $c) <option value="{{ $c->id }}" @selected(old('job_position_id') == $c->id)>{{ $c->title }}</option> @endforeach
        </select>
      </div>
      <div class="col-md-6"><label class="form-label fw-semibold small">Contraseña <span class="text-muted">(mínimo 8)</span></label><input type="password" name="password" class="form-control" minlength="8" autocomplete="new-password" required></div>
      <div class="col-md-6"><label class="form-label fw-semibold small">Repetir contraseña</label><input type="password" name="password_confirmation" class="form-control" minlength="8" autocomplete="new-password" required></div>
      <div class="col-12 d-flex gap-2">
        <button type="submit" class="btn btn-cgp fw-bold">Crear usuario</button>
        <a href="{{ route('admin.usuarios.index') }}" class="btn btn-outline-secondary">Cancelar</a>
      </div>
      <div class="form-text">El usuario se crea sin permisos. En el siguiente paso elegirá a qué módulos accede.</div>
    </form>
  </div>
</div>
@endsection
