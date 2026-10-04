@extends('layouts.admin')

@section('titulo', $item ? 'Editar contenido' : 'Nuevo contenido')

@section('contenido')
@php($puedeEscribir = auth()->user()->puede('CMS', 'write'))
<a href="{{ route('admin.contenidos.index') }}" class="btn btn-sm btn-outline-secondary mb-3"><i class="fa-solid fa-arrow-left me-1"></i> Volver</a>
<div class="row g-4">
  <div class="col-lg-8">
    <form method="POST" class="card border-0 shadow-sm" action="{{ $item ? route('admin.contenidos.update', $item->id) : route('admin.contenidos.store') }}">
      @csrf
      <fieldset class="card-body row g-3" @disabled(! $puedeEscribir)>
        <div class="col-md-4">
          <label class="form-label fw-semibold small">Tipo</label>
          <select name="content_type_id" class="form-select" required>
            @foreach ($tipos as $t) <option value="{{ $t->id }}" @selected(old('content_type_id', $item->content_type_id ?? null) == $t->id)>{{ $t->name }}</option> @endforeach
          </select>
        </div>
        <div class="col-md-8">
          <label class="form-label fw-semibold small">Título</label>
          <input name="title" class="form-control" maxlength="200" value="{{ old('title', $item->title ?? '') }}" required>
        </div>
        <div class="col-12">
          <label class="form-label fw-semibold small">Contenido</label>
          <textarea name="body" class="form-control" rows="12" maxlength="50000" required>{{ old('body', $item->body ?? '') }}</textarea>
        </div>
        <div class="col-12 form-check ms-2">
          <input class="form-check-input" type="checkbox" name="published" value="1" id="pub" @checked(old('published', $item->published ?? false))>
          <label class="form-check-label" for="pub">Publicado (visible en el portal)</label>
        </div>
        @if ($puedeEscribir) <div class="col-12"><button type="submit" class="btn btn-cgp fw-bold">Guardar</button></div> @endif
      </fieldset>
    </form>
  </div>
  <div class="col-lg-4">
    <div class="card border-0 shadow-sm">
      <div class="card-header bg-white fw-bold text-primary-cgp">Versiones anteriores</div>
      <ul class="list-group list-group-flush small">
        @forelse ($versiones as $v)
          <li class="list-group-item"><div class="fw-semibold">{{ $v->title }}</div><div class="text-muted">{{ $v->autor }} · {{ \Illuminate\Support\Carbon::parse($v->modified_at)->timezone(config('app.timezone'))->format('d/m/Y h:i a') }}</div></li>
        @empty
          <li class="list-group-item text-muted">Todavía no se ha editado.</li>
        @endforelse
      </ul>
    </div>
  </div>
</div>
@endsection
