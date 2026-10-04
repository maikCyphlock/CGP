@extends('layouts.admin')

@section('titulo', $u->first_name.' '.$u->last_name)

@section('contenido')
@php
  $puedeEstado = auth()->user()->puede('USERS', 'write');
  $puedePermisos = auth()->user()->puede('ACCESS', 'write') && ! $esYo;
@endphp
<div class="row g-4">
  <div class="col-lg-4">
    <div class="card border-0 shadow-sm mb-4">
      <div class="card-body">
        <div class="fw-bold fs-5 text-primary-cgp">{{ $u->first_name }} {{ $u->last_name }}</div>
        <div class="text-muted">{{ $cargo }}</div>
        <hr>
        <div class="small text-muted">Correo</div><div class="fw-semibold mb-2">{{ $u->email }}</div>
        <div class="small text-muted">Último acceso</div><div class="fw-semibold mb-3">{{ $u->last_login?->timezone(config('app.timezone'))->format('d/m/Y h:i a') ?? 'Nunca' }}</div>
        <span class="badge {{ $u->active ? 'bg-success' : 'bg-secondary' }}">{{ $u->active ? 'Activo' : 'Desactivado' }}</span>
        @if ($u->locked_until?->isFuture()) <span class="badge bg-warning text-dark">Bloqueado hasta {{ $u->locked_until->timezone(config('app.timezone'))->format('h:i a') }}</span> @endif
        @if ($puedeEstado && ! $esYo)
          <form method="POST" action="{{ route('admin.usuarios.estado', $u->id) }}" class="mt-3"
                @if ($u->active) onsubmit="return confirm('Este usuario dejará de poder entrar al panel. ¿Continuar?')" @endif>
            @csrf
            <input type="hidden" name="active" value="{{ $u->active ? 0 : 1 }}">
            <button class="btn btn-sm w-100 {{ $u->active ? 'btn-outline-danger' : 'btn-outline-success' }}">{{ $u->active ? 'Desactivar usuario' : 'Activar usuario' }}</button>
          </form>
        @endif
        @if ($esYo) <div class="form-text mt-3">Es su propia cuenta: no puede desactivarla ni cambiar sus permisos.</div> @endif
      </div>
    </div>
  </div>

  <div class="col-lg-8">
    <div class="card border-0 shadow-sm mb-4">
      <div class="card-header bg-white fw-bold text-primary-cgp">Permisos por módulo</div>
      <form method="POST" action="{{ route('admin.usuarios.permisos', $u->id) }}">
        @csrf
        <div class="table-responsive">
          <table class="table align-middle mb-0" style="font-size: .9rem;">
            <thead class="table-light"><tr><th class="ps-4">Módulo</th><th class="text-center">Ver</th><th class="text-center">Editar</th><th class="text-center">Eliminar</th></tr></thead>
            <tbody>
              @foreach ($modulos as $m)
                @php($p = $permisos[$m->id] ?? null)
                <tr>
                  <td class="ps-4">{{ $m->name }}</td>
                  @foreach (['read' => 'can_read', 'write' => 'can_write', 'delete' => 'can_delete'] as $accion => $col)
                    <td class="text-center"><input type="checkbox" class="form-check-input perm" data-nivel="{{ $accion }}" name="p[{{ $m->id }}][{{ $accion }}]" value="1" @checked($p?->$col) @disabled(! $puedePermisos)></td>
                  @endforeach
                </tr>
              @endforeach
            </tbody>
          </table>
        </div>
        @if ($puedePermisos)
          <div class="card-body border-top d-flex justify-content-between align-items-center">
            <span class="small text-muted">Editar incluye ver; eliminar incluye editar. Cada cambio queda registrado.</span>
            <button class="btn btn-cgp fw-bold">Guardar permisos</button>
          </div>
        @endif
      </form>
    </div>

    <div class="card border-0 shadow-sm">
      <div class="card-header bg-white fw-bold text-primary-cgp">Cambios de permisos</div>
      <ul class="list-group list-group-flush small">
        @forelse ($cambios as $c)
          @php($a = json_decode($c->after_state, true))
          <li class="list-group-item d-flex justify-content-between">
            <span><strong>{{ ['GRANT' => 'Otorgó', 'MODIFY' => 'Modificó', 'REVOKE' => 'Retiró'][$c->action] }}</strong> {{ $c->modulo }}
              @if ($c->action !== 'REVOKE') — {{ $a['can_delete'] ? 'ver, editar y eliminar' : ($a['can_write'] ? 'ver y editar' : 'solo ver') }} @endif
              <span class="text-muted">· {{ $c->admin }}</span></span>
            <span class="text-muted">{{ \Illuminate\Support\Carbon::parse($c->logged_at)->timezone(config('app.timezone'))->format('d/m/Y h:i a') }}</span>
          </li>
        @empty
          <li class="list-group-item text-muted">Sin cambios registrados.</li>
        @endforelse
      </ul>
    </div>
  </div>
</div>
@endsection

@push('scripts')
<script>
  // Marcar "eliminar"/"editar" marca también los niveles menores; desmarcar "ver" desmarca los mayores.
  document.querySelectorAll('tr').forEach(tr => {
    const [r, w, d] = ['read', 'write', 'delete'].map(n => tr.querySelector(`.perm[data-nivel=${n}]`));
    if (!r) return;
    d.addEventListener('change', () => { if (d.checked) w.checked = r.checked = true; });
    w.addEventListener('change', () => { if (w.checked) r.checked = true; else d.checked = false; });
    r.addEventListener('change', () => { if (!r.checked) w.checked = d.checked = false; });
  });
</script>
@endpush
