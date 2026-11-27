@extends('layouts.oac')

@section('titulo', 'Mensajes al Ciudadano por Estado')

@section('contenido')
@php
  $puedeEscribir = auth()->user()->puede('CATALOGS', 'write');
  $tz = config('app.timezone');
@endphp

<div class="alert alert-info border-0 shadow-sm small mb-4">
  <i class="fa-solid fa-circle-info me-2"></i>
  Configure el texto informativo (sin costo de SMS ni correo) que verá el ciudadano en el portal público cuando consulte el estado de su trámite según la fase del Manual DAC.
</div>

<div class="row g-4">
  @foreach ($estados as $estado)
    @php
      $reg = $mensajes->get($estado->code);
      $def = $porDefecto[$estado->code] ?? ['titulo' => $estado->name, 'mensaje' => ''];
      $tituloActual = old('estado_actual') === $estado->code ? old('titulo') : ($reg->titulo ?? $def['titulo']);
      $mensajeActual = old('estado_actual') === $estado->code ? old('mensaje') : ($reg->mensaje ?? $def['mensaje']);
      $activoActual = old('estado_actual') === $estado->code ? (bool) old('activo') : ($reg ? $reg->activo : true);
    @endphp
    <div class="col-12 col-lg-6">
      <div class="card border-0 shadow-sm h-100">
        <div class="card-header bg-white d-flex flex-wrap justify-content-between align-items-center gap-2 py-3">
          <div>
            <span class="badge badge-estatus st-{{ $estado->code }} me-1">{{ $estado->name }}</span>
            <code class="text-muted small">{{ $estado->code }}</code>
          </div>
          @if ($reg && $reg->updated_at)
            <span class="text-muted small">
              Actualizado: {{ $reg->updated_at->timezone($tz)->format('d/m/Y h:i a') }}
              @if ($reg->actualizadoPor)
                · {{ $reg->actualizadoPor->nombre }}
              @endif
            </span>
          @endif
        </div>
        <div class="card-body">
          @if (!empty($descripciones[$estado->code]))
            <p class="text-muted small mb-3">{{ $descripciones[$estado->code] }}</p>
          @endif

          <form method="POST" action="{{ route('oac.mensajes_estado.update', $estado->code) }}">
            @csrf
            <input type="hidden" name="estado_actual" value="{{ $estado->code }}">

            <div class="mb-3">
              <label class="form-label small fw-semibold" for="titulo_{{ $estado->code }}">Título del aviso <span class="text-muted">(opcional)</span></label>
              <input type="text"
                     id="titulo_{{ $estado->code }}"
                     name="titulo"
                     class="form-control form-control-sm"
                     maxlength="120"
                     value="{{ $tituloActual }}"
                     @disabled(!$puedeEscribir)>
            </div>

            <div class="mb-3">
              <label class="form-label small fw-semibold" for="mensaje_{{ $estado->code }}">Mensaje visible al ciudadano <span class="text-danger">*</span></label>
              <textarea id="mensaje_{{ $estado->code }}"
                        name="mensaje"
                        class="form-control form-control-sm"
                        rows="4"
                        minlength="5"
                        maxlength="2000"
                        required
                        @disabled(!$puedeEscribir)>{{ $mensajeActual }}</textarea>
            </div>

            <div class="d-flex flex-wrap justify-content-between align-items-center gap-2">
              <div class="form-check m-0">
                <input type="hidden" name="activo" value="0">
                <input class="form-check-input"
                       type="checkbox"
                       name="activo"
                       value="1"
                       id="activo_{{ $estado->code }}"
                       @checked($activoActual)
                       @disabled(!$puedeEscribir)>
                <label class="form-check-label small" for="activo_{{ $estado->code }}">
                  Mostrar en consulta pública
                </label>
              </div>
              @if ($puedeEscribir)
                <button type="submit" class="btn btn-sm btn-cgp fw-bold px-3">Guardar mensaje</button>
              @endif
            </div>
          </form>
        </div>
      </div>
    </div>
  @endforeach
</div>
@endsection
