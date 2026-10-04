@extends('layouts.panel')

@section('titulo', 'Usuarios y Accesos')

@section('contenido')
<div class="d-flex justify-content-between align-items-center mb-3">
  <p class="text-muted mb-0">Personal con acceso al panel. Cada persona solo ve los módulos que se le asignen.</p>
  @if (auth()->user()->puede('USERS', 'write'))
    <a href="{{ route('admin.usuarios.create') }}" class="btn btn-cgp fw-bold"><i class="fa-solid fa-user-plus me-1"></i> Nuevo usuario</a>
  @endif
</div>
<div class="card border-0 shadow-sm overflow-hidden">
  <div class="table-responsive">
    <table class="table table-hover align-middle mb-0" style="font-size: .9rem;">
      <thead class="table-light"><tr><th class="ps-4">Nombre</th><th>Correo</th><th>Cargo</th><th>Último acceso</th><th>Estado</th></tr></thead>
      <tbody>
        @foreach ($usuarios as $u)
          <tr class="fila-link" onclick="location.href='{{ route('admin.usuarios.show', $u->id) }}'">
            <td class="ps-4 fw-bold text-primary-cgp">{{ $u->first_name }} {{ $u->last_name }}</td>
            <td>{{ $u->email }}</td>
            <td>{{ $u->cargo }}</td>
            <td class="text-secondary">{{ $u->last_login?->timezone(config('app.timezone'))->format('d/m/Y h:i a') ?? 'Nunca' }}</td>
            <td>
              @if (! $u->active) <span class="badge bg-secondary">Desactivado</span>
              @elseif ($u->locked_until?->isFuture()) <span class="badge bg-warning text-dark">Bloqueado</span>
              @else <span class="badge bg-success">Activo</span> @endif
            </td>
          </tr>
        @endforeach
      </tbody>
    </table>
  </div>
</div>
@endsection
