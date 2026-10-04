@extends('layouts.panel')

@section('titulo', 'Expediente '.$e->case_number)

@section('contenido')
@php
  $tz = config('app.timezone');
  $cd = $c->contact_data ?? [];
  $fecha = fn ($f) => $f ? \Illuminate\Support\Carbon::parse($f)->timezone($tz)->format('d/m/Y h:i a') : '—';
  $dato = fn ($label, $valor) => '<div class="col-sm-6 col-lg-4"><div class="text-muted small">'.e($label).'</div><div class="fw-semibold">'.(filled($valor) ? e($valor) : '—').'</div></div>';
@endphp

<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
  <a href="{{ url()->previous() !== url()->current() ? url()->previous() : route('oac.expedientes.index') }}" class="btn btn-sm btn-outline-secondary"><i class="fa-solid fa-arrow-left me-1"></i> Volver</a>
  <div class="d-flex gap-2 align-items-center">
    <span class="badge badge-estatus fs-6 st-{{ $actual->code }}">{{ $actual->name }}</span>
    <a href="{{ route('denuncias.planilla', $e->tracking_code) }}" target="_blank" class="btn btn-sm btn-outline-dark"><i class="fa-solid fa-file-pdf me-1"></i> Planilla PDF</a>
  </div>
</div>

<div class="row g-4">
  <div class="col-xl-8">

    <div class="card border-0 shadow-sm mb-4">
      <div class="card-header bg-white fw-bold text-primary-cgp">Solicitud</div>
      <div class="card-body row g-3">
        {!! $dato('Tipo de trámite', $e->claimType->name) !!}
        {!! $dato('Fecha de ingreso', $fecha($e->created_at)) !!}
        {!! $dato('Protocolizado', $e->protocolized_at ? $fecha($e->protocolized_at) : 'Pendiente') !!}
        @if ($unidad) {!! $dato('Derivado a', $unidad.' ('.$fecha($e->referred_at).')') !!} @endif
        {!! $dato('Irregularidad', $irregularidad ?? 'Sin clasificar') !!}
        @if ($e->referral_letter_url) <div class="col-sm-6 col-lg-4"><div class="text-muted small">Oficio de derivación</div><a class="fw-semibold" href="{{ $e->referral_letter_url }}" target="_blank" rel="noopener noreferrer">Ver oficio</a></div> @endif
        @if ($e->analyst_notes) <div class="col-12"><div class="text-muted small">Notas del analista</div><div style="white-space: pre-wrap;">{{ $e->analyst_notes }}</div></div> @endif
        {!! $dato('Otra instancia', $e->other_instance ? ($e->other_instance_name ?: 'Sí') : 'No') !!}
        <div class="col-12">
          <div class="text-muted small">Narración de los hechos</div>
          <div class="border rounded p-3 bg-light" style="white-space: pre-wrap;">{{ $e->narrative }}</div>
        </div>
      </div>
    </div>

    <div class="card border-0 shadow-sm mb-4">
      <div class="card-header bg-white fw-bold text-primary-cgp">Datos del ciudadano</div>
      <div class="card-body row g-3">
        {!! $dato('Nombre', $c->first_name.' '.$c->last_name) !!}
        {!! $dato('Cédula', ($docTipo === 'PASSPORT' ? 'P' : $docTipo).'-'.$c->id_doc_number) !!}
        {!! $dato('Sexo', ['M' => 'Masculino', 'F' => 'Femenino'][$c->sex] ?? $c->sex) !!}
        {!! $dato('Nacimiento', trim(($cd['birth_place'] ?? '').' '.($c->birth_date?->format('d/m/Y') ?? ''))) !!}
        {!! $dato('Correo', $c->email) !!}
        {!! $dato('Teléfono celular', $c->mobile_phone) !!}
        {!! $dato('Teléfono habitación', $cd['home_phone'] ?? null) !!}
        {!! $dato('Teléfono trabajo', $cd['work_phone'] ?? null) !!}
        {!! $dato('Profesión / Ocupación', collect([$cd['profession'] ?? null, $cd['occupation'] ?? null])->filter()->join(' / ')) !!}
        <div class="col-12"><div class="text-muted small">Dirección</div><div class="fw-semibold">{{ collect([$c->address, $c->parish, $c->municipality, $c->city])->filter()->join(', ') }}</div></div>
      </div>
    </div>

    @if ($e->respondents->isNotEmpty())
      <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white fw-bold text-primary-cgp">Señalados <span class="text-muted fw-normal small">— {{ $e->incident_location }}</span></div>
        <ul class="list-group list-group-flush">
          @foreach ($e->respondents as $r)
            <li class="list-group-item">
              <div class="fw-bold">{{ $r->respondentType->name }}</div>
              @foreach ($r->attributes ?? [] as $k => $v)
                @if (filled($v))
                  <span class="me-3 small"><span class="text-muted">{{ ucfirst(str_replace('-', ' ', preg_replace('/^[a-z]{2}-/', '', $k))) }}:</span> {{ $v }}</span>
                @endif
              @endforeach
            </li>
          @endforeach
        </ul>
      </div>
    @endif

    @if ($consulta)
      <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white fw-bold text-primary-cgp">Proyecto de Consulta Popular</div>
        <div class="card-body row g-3">
          {!! $dato('Proyecto', $consulta->project_name) !!}
          {!! $dato('Fecha de aprobación', \Illuminate\Support\Carbon::parse($consulta->approval_date)->format('d/m/Y')) !!}
          {!! $dato('Monto', number_format($consulta->project_amount, 2, ',', '.')) !!}
          {!! $dato('Ente financiador', $consulta->funding_entity) !!}
          {!! $dato('Código SITUR', $consulta->situr_code) !!}
        </div>
      </div>
    @endif

    <div class="card border-0 shadow-sm mb-4">
      <div class="card-header bg-white fw-bold text-primary-cgp">Soportes consignados</div>
      <div class="card-body">
        @if ($documentos->isNotEmpty())
          <div class="mb-2">@foreach ($documentos as $d) <span class="badge bg-light text-dark border me-1">{{ $d }}</span> @endforeach</div>
        @endif
        @forelse ($archivos as $a)
          <a href="{{ route('oac.expedientes.archivo', [$e, $a->id]) }}" class="d-block"><i class="fa-solid fa-paperclip me-1"></i>{{ $a->original_name }} <span class="text-muted small">({{ number_format($a->size_bytes / 1024, 0, ',', '.') }} KB)</span></a>
        @empty
          <span class="text-muted">El ciudadano no adjuntó archivos.</span>
        @endforelse
      </div>
    </div>
  </div>

  <div class="col-xl-4">
    @if ($puedeEscribir)
    <div class="card border-0 shadow-sm mb-4" style="border-top: 4px solid var(--brand) !important;">
      <div class="card-header bg-white fw-bold text-primary-cgp">Registrar actuación</div>
      <div class="card-body">
        <form method="POST" action="{{ route('oac.expedientes.actuar', $e) }}">
          @csrf
          <input type="hidden" name="desde" value="{{ $actual->code }}">
          <label class="form-label fw-semibold small">¿Qué desea hacer?</label>
          <select name="estatus" id="accion" class="form-select mb-3">
            <option value="">Solo agregar una observación</option>
            @foreach ($opciones as $code => $texto)
              <option value="{{ $code }}" @selected(old('estatus') === $code)>{{ $texto }}</option>
            @endforeach
          </select>
          <div id="bloque-unidad" class="mb-3" hidden>
            <label class="form-label fw-semibold small">Unidad a la que se deriva</label>
            <select name="referral_unit_id" class="form-select">
              <option value="">Seleccione…</option>
              @foreach ($unidades as $un)
                <option value="{{ $un->id }}" @selected(old('referral_unit_id') == $un->id)>{{ $un->name }}</option>
              @endforeach
            </select>
          </div>
          <div id="bloque-oficio" class="mb-3" hidden>
            <label class="form-label fw-semibold small">Enlace al oficio de derivación <span class="text-muted">(opcional)</span></label>
            <input type="url" name="referral_letter_url" class="form-control" maxlength="300" placeholder="https://…" value="{{ old('referral_letter_url') }}">
          </div>
          <label class="form-label fw-semibold small">Observación <span class="text-danger">*</span></label>
          <textarea name="nota" class="form-control mb-3" rows="4" minlength="5" maxlength="2000" required placeholder="Describa lo realizado o el motivo del cambio">{{ old('nota') }}</textarea>
          <button type="submit" class="btn btn-cgp w-100 fw-bold">Guardar</button>
          @if ($opciones->isEmpty())
            <div class="form-text">Este expediente está cerrado; solo se pueden agregar observaciones.</div>
          @endif
        </form>
      </div>
    </div>
    @endif

    @if ($puedeClasificar)
    <div class="card border-0 shadow-sm mb-4">
      <div class="card-header bg-white fw-bold text-primary-cgp">Clasificación</div>
      <div class="card-body">
        <form method="POST" action="{{ route('oac.expedientes.clasificar', $e) }}">
          @csrf
          <label class="form-label fw-semibold small">Tipo de irregularidad</label>
          <select name="irregularity_type_id" class="form-select mb-3">
            <option value="">Sin clasificar</option>
            @foreach ($irregularidades as $i)
              <option value="{{ $i->id }}" @selected((old('irregularity_type_id', $e->irregularity_type_id)) == $i->id)>{{ $i->name }}</option>
            @endforeach
          </select>
          <label class="form-label fw-semibold small">Notas del analista</label>
          <textarea name="analyst_notes" class="form-control mb-3" rows="3" maxlength="3000">{{ old('analyst_notes', $e->analyst_notes) }}</textarea>
          <button type="submit" class="btn btn-outline-dark w-100 fw-bold">Guardar clasificación</button>
        </form>
      </div>
    </div>
    @endif

    <div class="card border-0 shadow-sm">
      <div class="card-header bg-white fw-bold text-primary-cgp">Historial</div>
      <ul class="list-group list-group-flush small">
        @foreach ($historial as $h)
          <li class="list-group-item">
            <div class="d-flex justify-content-between"><span class="fw-bold">{{ match ($h->action_type) { 'STATUS_CHANGE' => 'Pasó a '.$estatus->firstWhere('code', $h->payload['a'])?->name, 'CLASSIFICATION' => 'Clasificó: '.($h->payload['irregularidad'] ?? 'sin tipo'), default => 'Observación' } }}</span><span class="text-muted">{{ $fecha($h->performed_at) }}</span></div>
            @isset($h->payload['unidad']) <div>Unidad: {{ $h->payload['unidad'] }}</div> @endisset
            @isset($h->payload['oficio']) <div><a href="{{ $h->payload['oficio'] }}" target="_blank" rel="noopener noreferrer">Ver oficio</a></div> @endisset
            <div style="white-space: pre-wrap;">{{ $h->payload['nota'] ?? '' }}</div>
            <div class="text-muted">{{ $h->usuario }}</div>
          </li>
        @endforeach
        <li class="list-group-item">
          <div class="d-flex justify-content-between"><span class="fw-bold">Solicitud recibida</span><span class="text-muted">{{ $fecha($e->created_at) }}</span></div>
          <div class="text-muted">Portal público</div>
        </li>
      </ul>
    </div>
  </div>
</div>
@endsection

@push('scripts')
<script>
  const accion = document.getElementById('accion');
  if (accion) {
  const mostrarUnidad = () => {
    const derivar = accion.value === 'REFERRED';
    document.getElementById('bloque-unidad').hidden = !derivar;
    document.getElementById('bloque-oficio').hidden = !derivar;
    document.querySelector('[name=referral_unit_id]').required = derivar;
  };
  accion.addEventListener('change', mostrarUnidad);
  mostrarUnidad();
  // Pide confirmación antes de cerrar un expediente (no tiene vuelta atrás).
  accion.form.addEventListener('submit', ev => {
    if (['ARCHIVED', 'INVALIDATED'].includes(accion.value) && !confirm('Esta acción cierra el expediente y no se puede deshacer. ¿Continuar?')) {
      ev.preventDefault();
      ev.stopImmediatePropagation();
    }
  }, true);
  }
</script>
@endpush
