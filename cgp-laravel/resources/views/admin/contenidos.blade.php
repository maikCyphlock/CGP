@extends('layouts.admin')

@section('titulo', 'Página Web')

@section('contenido')
<div class="d-flex justify-content-between align-items-center mb-3">
  <p class="text-muted mb-0">Noticias, misión, visión y demás contenido institucional. Cada edición conserva la versión anterior.</p>
  @if (auth()->user()->puede('CMS', 'write'))
    <a href="{{ route('admin.contenidos.create') }}" class="btn btn-cgp fw-bold"><i class="fa-solid fa-plus me-1"></i> Nuevo contenido</a>
  @endif
</div>
<div class="card border-0 shadow-sm overflow-hidden">
  <div class="table-responsive">
    <table class="table table-hover align-middle mb-0" style="font-size: .9rem;">
      <thead class="table-light"><tr><th class="ps-4">Título</th><th>Tipo</th><th>Autor</th><th>Actualizado</th><th>Estado</th></tr></thead>
      <tbody>
        @forelse ($items as $i)
          <tr class="fila-link" onclick="location.href='{{ route('admin.contenidos.edit', $i->id) }}'">
            <td class="ps-4 fw-bold text-primary-cgp">{{ $i->title }}</td>
            <td>{{ $i->tipo }}</td>
            <td>{{ $i->autor }}</td>
            <td class="text-secondary">{{ \Illuminate\Support\Carbon::parse($i->updated_at)->timezone(config('app.timezone'))->format('d/m/Y h:i a') }}</td>
            <td><span class="badge {{ $i->published ? 'bg-success' : 'bg-secondary' }}">{{ $i->published ? 'Publicado' : 'Borrador' }}</span></td>
          </tr>
        @empty
          <tr><td colspan="5" class="text-center text-muted py-5">Aún no hay contenido.</td></tr>
        @endforelse
      </tbody>
    </table>
  </div>
</div>
@endsection
