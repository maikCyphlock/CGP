@extends('layouts.admin')

@section('titulo', 'Acceso denegado')

@section('contenido')
<div class="card border-0 shadow-sm text-center p-5">
  <div class="display-4 text-danger mb-3"><i class="fa-solid fa-lock"></i></div>
  <h4 class="fw-bold text-primary-cgp">No tiene permiso para esta sección</h4>
  <p class="text-muted mb-4">Este intento quedó registrado. Si lo necesita para su trabajo, solicite el acceso al administrador del sistema.</p>
  <div><a href="{{ route('admin.inicio') }}" class="btn btn-cgp">Volver al panel</a></div>
</div>
@endsection
