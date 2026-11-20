@extends('layouts.oac')

@section('titulo', 'Editar Denuncia · Expediente ' . $e->case_number)

@section('contenido')
@php
  $tz = config('app.timezone');
  $cd = $c->contact_data ?? [];
  $fecha = fn ($f) => $f ? \Illuminate\Support\Carbon::parse($f)->timezone($tz)->format('d/m/Y h:i a') : '—';
@endphp

<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
  <a href="{{ route('oac.expedientes.show', $e) }}" class="btn btn-sm btn-outline-secondary">
    <i class="fa-solid fa-arrow-left me-1"></i> Volver al expediente
  </a>
  <div class="d-flex gap-2 align-items-center">
    <span class="badge badge-estatus fs-6 st-{{ $actual->code }}">{{ $actual->name }}</span>
    <a href="{{ route('denuncias.planilla', $e->tracking_code) }}" class="btn btn-sm btn-outline-dark ver-planilla" data-titulo="{{ $e->case_number }}">
      <i class="fa-solid fa-file-pdf me-1"></i> Planilla PDF
    </a>
  </div>
</div>

{{-- Banner informativo de campos protegidos --}}
<div class="alert alert-info border-0 shadow-sm d-flex align-items-center mb-4 rounded-3 bg-white border-start border-4" style="border-color: #1565c0 !important;">
    <i class="fa-solid" style="color: #1565c0;"></i><div>
    <h6 class="mb-1 fw-bold text-dark">Campos Institucionales Protegidos</h6>
    <p class="mb-0 text-secondary small">
      Por normativas de auditoría y control de expedientes, el <strong>Número de expediente</strong> y la <strong>Fecha de ingreso</strong> son datos fijos generados por el sistema y <strong>no pueden ser modificados</strong>. Los datos de la denuncia, del ciudadano solicitante, proyecto de consulta y señalados sí pueden ser modificados a continuación.
    </p>
  </div>
</div>

{{-- Card de Identificación Institucional (Solo lectura) --}}
<div class="card border-0 shadow-sm mb-4 rounded-3">
  <div class="card-header bg-white py-3 fw-bold text-primary-cgp border-bottom d-flex align-items-center justify-content-between">
    <span></i>Identificación Institucional (No Modificable)</span>
    <span class="text-muted small fw-normal">Asignados en el registro</span>
  </div>
  <div class="card-body">
    <div class="row g-3">
      <div class="col-md-6">
        <label class="form-label small fw-semibold text-muted">Número de Expediente</label>
        <div class="input-group">
          <span class="input-group-text bg-light text-muted"><i class="fa-solid fa-folder-closed"></i></span>
          <input type="text" class="form-control bg-light fw-bold text-primary-cgp" value="{{ $e->case_number }}" readonly disabled aria-label="Número de Expediente">
          <span class="input-group-text bg-light text-muted" title="Campo bloqueado e inmutable"><i class="fa-solid fa-lock"></i></span>
        </div>
        <div class="form-text text-muted small">Identificador único generado por el correlativo anual de la OAC.</div>
      </div>
      <div class="col-md-6">
        <label class="form-label small fw-semibold text-muted">Fecha de Ingreso Oficial</label>
        <div class="input-group">
          <span class="input-group-text bg-light text-muted"><i class="fa-solid fa-calendar-check"></i></span>
          <input type="text" class="form-control bg-light text-secondary" value="{{ $fecha($e->created_at) }}" readonly disabled aria-label="Fecha de Ingreso Oficial">
          <span class="input-group-text bg-light text-muted" title="Campo bloqueado e inmutable"><i class="fa-solid fa-lock"></i></span>
        </div>
        <div class="form-text text-muted small">Fecha y hora oficial en que el trámite fue consignado en el sistema.</div>
      </div>
    </div>
  </div>
</div>

<form method="POST" action="{{ route('oac.expedientes.update', $e) }}">
  @csrf

  {{-- Card 1: Datos del Ciudadano Solicitante --}}
  <div class="card border-0 shadow-sm mb-4 rounded-3">
    <div class="card-header bg-white py-3 fw-bold text-primary-cgp border-bottom">
      <i class="fa-solid fa-user me-2"></i>Datos del Solicitante / Ciudadano
    </div>
    <div class="card-body">
      <div class="row g-3">
        <div class="col-md-2">
          <label class="form-label small fw-semibold">Tipo Doc. <span style="color: #1565c0;">*</span></label>
          <select name="ciudadano[id_doc_type_id]" class="form-select form-select-sm" required>
            @foreach ($tiposDoc as $td)
              <option value="{{ $td->id }}" @selected(old('ciudadano.id_doc_type_id', $c->id_doc_type_id) == $td->id)>
                {{ $td->code === 'PASSPORT' ? 'Pasaporte (P)' : $td->code }} - {{ $td->name }}
              </option>
            @endforeach
          </select>
        </div>
        <div class="col-md-4">
          <label class="form-label small fw-semibold">Número de Documento / Cédula <span style="color: #1565c0;">*</span></label>
          <input type="text" name="ciudadano[id_doc_number]" class="form-control form-control-sm" value="{{ old('ciudadano.id_doc_number', $c->id_doc_number) }}" required maxlength="10" placeholder="Ej. 12345678">
        </div>
        <div class="col-md-3">
          <label class="form-label small fw-semibold">Nombres <span style="color: #1565c0;">*</span></label>
          <input type="text" name="ciudadano[first_name]" class="form-control form-control-sm" value="{{ old('ciudadano.first_name', $c->first_name) }}" required maxlength="80">
        </div>
        <div class="col-md-3">
          <label class="form-label small fw-semibold">Apellidos <span style="color: #1565c0;">*</span></label>
          <input type="text" name="ciudadano[last_name]" class="form-control form-control-sm" value="{{ old('ciudadano.last_name', $c->last_name) }}" required maxlength="80">
        </div>

        <div class="col-md-3">
          <label class="form-label small fw-semibold">Sexo <span style="color: #1565c0;">*</span></label>
          <select name="ciudadano[sex]" class="form-select form-select-sm" required>
            <option value="M" @selected(old('ciudadano.sex', $c->sex) === 'M')>Masculino</option>
            <option value="F" @selected(old('ciudadano.sex', $c->sex) === 'F')>Femenino</option>
          </select>
        </div>
        <div class="col-md-3">
          <label class="form-label small fw-semibold">Fecha de Nacimiento</label>
          <input type="date" name="ciudadano[birth_date]" class="form-control form-control-sm" value="{{ old('ciudadano.birth_date', $c->birth_date?->format('Y-m-d')) }}" max="{{ date('Y-m-d') }}">
        </div>
        <div class="col-md-6">
          <label class="form-label small fw-semibold">Lugar de Nacimiento</label>
          <input type="text" name="ciudadano[birth_place]" class="form-control form-control-sm" value="{{ old('ciudadano.birth_place', $cd['birth_place'] ?? '') }}" maxlength="120">
        </div>

        <div class="col-md-4">
          <label class="form-label small fw-semibold">Correo Electrónico <span style="color: #1565c0;">*</span></label>
          <input type="email" name="ciudadano[email]" class="form-control form-control-sm" value="{{ old('ciudadano.email', $c->email) }}" required maxlength="150">
        </div>
        <div class="col-md-4">
          <label class="form-label small fw-semibold">Teléfono Celular</label>
          <input type="text" name="ciudadano[mobile_phone]" class="form-control form-control-sm" value="{{ old('ciudadano.mobile_phone', $c->mobile_phone) }}" maxlength="20" placeholder="Ej. 0414-1234567">
        </div>
        <div class="col-md-4">
          <label class="form-label small fw-semibold">Teléfono Habitación</label>
          <input type="text" name="ciudadano[home_phone]" class="form-control form-control-sm" value="{{ old('ciudadano.home_phone', $cd['home_phone'] ?? '') }}" maxlength="20">
        </div>

        <div class="col-md-3">
          <label class="form-label small fw-semibold">Teléfono Trabajo</label>
          <input type="text" name="ciudadano[work_phone]" class="form-control form-control-sm" value="{{ old('ciudadano.work_phone', $cd['work_phone'] ?? '') }}" maxlength="20">
        </div>
        <div class="col-md-3">
          <label class="form-label small fw-semibold">Estado Civil</label>
          <select name="ciudadano[marital_status]" class="form-select form-select-sm">
            <option value="">Seleccione...</option>
            @foreach (['Soltero(a)', 'Casado(a)', 'Divorciado(a)', 'Viudo(a)', 'Unión Estable de Hechos'] as $ec)
              <option value="{{ $ec }}" @selected(old('ciudadano.marital_status', $cd['marital_status'] ?? '') === $ec)>{{ $ec }}</option>
            @endforeach
          </select>
        </div>
        <div class="col-md-3">
          <label class="form-label small fw-semibold">Nivel Educativo</label>
          <select name="ciudadano[education_level]" class="form-select form-select-sm">
            <option value="">Seleccione...</option>
            @foreach (['Primaria', 'Secundaria', 'Técnico Medio', 'T.S.U.', 'Universitario', 'Postgrado / Especialización', 'Sin instrucción formal'] as $ne)
              <option value="{{ $ne }}" @selected(old('ciudadano.education_level', $cd['education_level'] ?? '') === $ne)>{{ $ne }}</option>
            @endforeach
          </select>
        </div>
        <div class="col-md-3">
          <label class="form-label small fw-semibold">Profesión</label>
          <input type="text" name="ciudadano[profession]" list="listaProfesiones" class="form-control form-control-sm" value="{{ old('ciudadano.profession', $cd['profession'] ?? '') }}" maxlength="80" placeholder="Escriba o seleccione...">
          <datalist id="listaProfesiones">
            @foreach ($profesiones as $p)
              <option value="{{ $p->nombre }}">
            @endforeach
          </datalist>
        </div>

        <div class="col-md-4">
          <label class="form-label small fw-semibold">Ocupación / Oficio</label>
          <input type="text" name="ciudadano[occupation]" class="form-control form-control-sm" value="{{ old('ciudadano.occupation', $cd['occupation'] ?? '') }}" maxlength="80">
        </div>
        <div class="col-md-4">
          <label class="form-label small fw-semibold">Parroquia</label>
          <select name="ciudadano[parish]" class="form-select form-select-sm">
            <option value="">Seleccione...</option>
            @foreach ($parroquias as $pr)
              <option value="{{ $pr->nombre }}" @selected(old('ciudadano.parish', $c->parish) === $pr->nombre)>{{ $pr->nombre }}</option>
            @endforeach
          </select>
        </div>
        <div class="col-md-4">
          <label class="form-label small fw-semibold">Municipio / Ciudad</label>
          <div class="row g-2">
            <div class="col-6">
              <input type="text" name="ciudadano[municipality]" class="form-control form-control-sm" value="{{ old('ciudadano.municipality', $c->municipality ?: 'Páez') }}" placeholder="Municipio">
            </div>
            <div class="col-6">
              <input type="text" name="ciudadano[city]" class="form-control form-control-sm" value="{{ old('ciudadano.city', $c->city) }}" placeholder="Ciudad">
            </div>
          </div>
        </div>

        <div class="col-12">
          <label class="form-label small fw-semibold">Dirección de Habitación <span style="color: #1565c0;">*</span></label>
          <textarea name="ciudadano[address]" class="form-control form-control-sm" rows="2" required minlength="5" maxlength="250">{{ old('ciudadano.address', $c->address) }}</textarea>
        </div>
      </div>
    </div>
  </div>

  {{-- Card 2: Datos de la Denuncia / Solicitud --}}
  <div class="card border-0 shadow-sm mb-4 rounded-3">
    <div class="card-header bg-white py-3 fw-bold text-primary-cgp border-bottom">
      <i class="fa-solid fa-file-lines me-2"></i>Datos de la Solicitud / Denuncia
    </div>
    <div class="card-body">
      <div class="row g-3">
        <div class="col-md-6">
          <label class="form-label small fw-semibold">Tipo de Trámite <span style="color: #1565c0;">*</span></label>
          <select name="tramite[claim_type_id]" class="form-select form-select-sm" required>
            @foreach ($tiposTramite as $tt)
              <option value="{{ $tt->id }}" @selected(old('tramite.claim_type_id', $e->claim_type_id) == $tt->id)>{{ $tt->name }}</option>
            @endforeach
          </select>
        </div>

        <div class="col-md-6">
          <label class="form-label small fw-semibold">Ubicación del Hecho / Señalado</label>
          <input type="text" name="tramite[incident_location]" class="form-control form-control-sm" value="{{ old('tramite.incident_location', $e->incident_location) }}" maxlength="250" placeholder="Ej. Calle 30 entre Av. 4 y 5, Acarigua">
        </div>

        <div class="col-12">
          @php
            $otraInstVal = 'no';
            if ($e->other_instance && $e->other_instance_name !== 'No sé') {
                $otraInstVal = 'si';
            } elseif ($e->other_instance_name === 'No sé') {
                $otraInstVal = 'no_se';
            }
            $otraInstSeleccionada = old('tramite.other_instance', $otraInstVal);
          @endphp
          <label class="form-label small fw-semibold d-block">¿Ha presentado este caso ante otra instancia u organismo? <span style="color: #1565c0;">*</span></label>
          <div class="d-flex gap-4 mb-2">
            <div class="form-check">
              <input class="form-check-input" type="radio" name="tramite[other_instance]" id="instanciaNo" value="no" @checked($otraInstSeleccionada === 'no') onchange="toggleInstancia(false)">
              <label class="form-check-label small" for="instanciaNo">No</label>
            </div>
            <div class="form-check">
              <input class="form-check-input" type="radio" name="tramite[other_instance]" id="instanciaSi" value="si" @checked($otraInstSeleccionada === 'si') onchange="toggleInstancia(true)">
              <label class="form-check-label small" for="instanciaSi">Sí</label>
            </div>
            <div class="form-check">
              <input class="form-check-input" type="radio" name="tramite[other_instance]" id="instanciaNoSe" value="no_se" @checked($otraInstSeleccionada === 'no_se') onchange="toggleInstancia(false)">
              <label class="form-check-label small" for="instanciaNoSe">No sé</label>
            </div>
          </div>
          <div id="bloqueOtraInstancia" class="{{ $otraInstSeleccionada === 'si' ? '' : 'd-none' }}">
            <label class="form-label small fw-semibold">Nombre del Organismo o Instancia</label>
            <input type="text" name="tramite[other_instance_name]" id="otraInstanciaNombre" class="form-control form-control-sm" value="{{ old('tramite.other_instance_name', $e->other_instance && $e->other_instance_name !== 'No sé' ? $e->other_instance_name : '') }}" maxlength="200" placeholder="Ej. Ministerio Público, Defensoría del Pueblo...">
          </div>
        </div>

        <div class="col-12">
          <label class="form-label small fw-semibold">Narración de los Hechos <span style="color: #1565c0;">*</span> <span class="text-muted fw-normal">(mínimo 50, máximo 3500 caracteres)</span></label>
          <textarea name="tramite[narrative]" id="narrativeText" class="form-control" rows="6" required minlength="50" maxlength="3500" placeholder="Describa con claridad los hechos ocurridos...">{{ old('tramite.narrative', $e->narrative) }}</textarea>
          <div class="d-flex justify-content-between form-text text-muted small mt-1">
            <span>Explique fecha, lugar, personas involucradas y detalles relevantes.</span>
            <span id="charCount">0 / 3500</span>
          </div>
        </div>
      </div>
    </div>
  </div>

  {{-- Card 3: Proyecto de Consulta Popular --}}
  <div class="card border-0 shadow-sm mb-4 rounded-3">
    <div class="card-header bg-white py-3 fw-bold text-primary-cgp border-bottom d-flex align-items-center justify-content-between">
      <span></i>Proyecto de Consulta Popular Nacional</span>
      <div class="form-check form-switch m-0">
        <input class="form-check-input" type="checkbox" name="consulta[es_consulta]" value="1" id="switchConsulta" @checked(old('consulta.es_consulta', $e->is_popular_consultation)) onchange="toggleConsulta(this.checked)">
        <label class="form-check-label small text-muted" for="switchConsulta">¿Aplica a Consulta Popular?</label>
      </div>
    </div>
    <div class="card-body {{ old('consulta.es_consulta', $e->is_popular_consultation) ? '' : 'd-none' }}" id="bloqueConsulta">
      <div class="row g-3">
        <div class="col-md-6">
          <label class="form-label small fw-semibold">Nombre del Proyecto</label>
          <input type="text" name="consulta[project_name]" class="form-control form-control-sm" value="{{ old('consulta.project_name', $consulta->project_name ?? '') }}" maxlength="200">
        </div>
        <div class="col-md-3">
          <label class="form-label small fw-semibold">Fecha Estimada de Aprobación</label>
          <input type="date" name="consulta[approval_date]" class="form-control form-control-sm" value="{{ old('consulta.approval_date', $consulta->approval_date ?? '') }}" max="{{ date('Y-m-d') }}">
        </div>
        <div class="col-md-3">
          <label class="form-label small fw-semibold">Monto del Proyecto (Bs.)</label>
          <input type="number" step="0.01" name="consulta[project_amount]" class="form-control form-control-sm" value="{{ old('consulta.project_amount', $consulta->project_amount ?? '') }}">
        </div>
        <div class="col-md-6">
          <label class="form-label small fw-semibold">Ente Financiador</label>
          <input type="text" name="consulta[funding_entity]" class="form-control form-control-sm" value="{{ old('consulta.funding_entity', $consulta->funding_entity ?? '') }}" maxlength="200">
        </div>
        <div class="col-md-6">
          <label class="form-label small fw-semibold">Código SITUR</label>
          <input type="text" name="consulta[situr_code]" class="form-control form-control-sm" value="{{ old('consulta.situr_code', $consulta->situr_code ?? '') }}" maxlength="40" placeholder="Ej. C-URB-2014-11-0001">
        </div>
      </div>
    </div>
  </div>

  {{-- Card 4: Personas u Órganos Señalados --}}
  <div class="card border-0 shadow-sm mb-4 rounded-3">
    <div class="card-header bg-white py-3 fw-bold text-primary-cgp border-bottom d-flex align-items-center justify-content-between">
      <span></i>Personas u Órganos Señalados</span>
      <button type="button" class="btn btn-sm btn-outline-primary fw-bold" onclick="agregarSenalado()">
        <i class="fa-solid fa-plus me-1"></i> Agregar Señalado
      </button>
    </div>
    <div class="card-body">
      <div id="contenedorSenalados">
        @forelse ($e->respondents as $idx => $r)
          <div class="card border rounded-3 p-3 mb-3 item-senalado bg-light">
            <input type="hidden" name="senalados[{{ $idx }}][id]" value="{{ $r->id }}">
            <div class="d-flex justify-content-between align-items-center mb-2">
              <span class="fw-bold text-primary-cgp small"><i class="fa-solid fa-user-tag me-1"></i> Señalado #{{ $idx + 1 }}</span>
              <button type="button" class="btn btn-sm btn-link text-danger p-0" onclick="eliminarSenalado(this)" title="Quitar este señalado">
                <i class="fa-solid fa-trash-can"></i> Quitar
              </button>
            </div>
            <div class="row g-2">
              <div class="col-md-4">
                <label class="form-label small fw-semibold">Tipo de Señalado</label>
                <select name="senalados[{{ $idx }}][respondent_type_id]" class="form-select form-select-sm" required>
                  @foreach ($tiposSenalado as $ts)
                    <option value="{{ $ts->id }}" @selected($r->respondent_type_id == $ts->id)>{{ $ts->name }}</option>
                  @endforeach
                </select>
              </div>
              <div class="col-md-8">
                <label class="form-label small fw-semibold">Ubicación del Señalado</label>
                <input type="text" name="senalados[{{ $idx }}][location]" class="form-control form-control-sm" value="{{ $r->location }}" placeholder="Ubicación específica">
              </div>
              <div class="col-12 mt-2">
                <label class="form-label small fw-semibold text-muted d-block">Datos / Atributos de Identificación</label>
                <div class="row g-2">
                  @php
                    $attrs = $r->attributes ?? [];
                  @endphp
                  <div class="col-md-4">
                    <input type="text" name="senalados[{{ $idx }}][attributes][full_name]" class="form-control form-control-sm" value="{{ $attrs['full_name'] ?? $attrs['pn-nombre'] ?? $attrs['pj-razon-social'] ?? $attrs['oe-denominacion'] ?? $attrs['commune_name'] ?? '' }}" placeholder="Nombre / Denominación / Razón Social">
                  </div>
                  <div class="col-md-4">
                    <input type="text" name="senalados[{{ $idx }}][attributes][id_number]" class="form-control form-control-sm" value="{{ $attrs['id_number'] ?? $attrs['pn-nro-doc'] ?? $attrs['rif'] ?? $attrs['pj-rif'] ?? $attrs['oe-rif'] ?? $attrs['situr_code'] ?? '' }}" placeholder="Cédula / RIF / SITUR">
                  </div>
                  <div class="col-md-4">
                    <input type="text" name="senalados[{{ $idx }}][attributes][cargo]" class="form-control form-control-sm" value="{{ $attrs['cargo'] ?? $attrs['pn-cargo'] ?? $attrs['oe-cargo'] ?? '' }}" placeholder="Cargo u observación">
                  </div>
                </div>
              </div>
            </div>
          </div>
        @empty
          <div id="sinSenaladosMsg" class="text-center text-muted py-3 small">
            No hay señalados registrados en este trámite. Puede presionar "Agregar Señalado" si desea incluir alguno.
          </div>
        @endforelse
      </div>
    </div>
  </div>

  {{-- Card 5: Nota u Observación de la Edición --}}
  <div class="card border-0 shadow-sm mb-4 rounded-3">
    <div class="card-header bg-white py-3 fw-bold text-primary-cgp border-bottom">
      <i class="fa-solid fa-clock-rotate-left me-2"></i>Observación para el Historial <span style="color: #1565c0;">*</span>
    </div>
    <div class="card-body">
      <label class="form-label small fw-semibold">Motivo o Resumen de la Corrección <span style="color: #1565c0;">*</span></label>
      <input type="text" name="nota_edicion" class="form-control form-control-sm" required minlength="5" maxlength="250" value="{{ old('nota_edicion') }}" placeholder="Ej. Corrección ortográfica en nombres y actualización de dirección a solicitud del ciudadano.">
      <div class="form-text text-muted small">Esta observación es obligatoria y quedará registrada en la bitácora de auditoría junto con su nombre de usuario.</div>
    </div>
  </div>

  {{-- Botones de Acción --}}
  <div class="d-flex justify-content-end gap-2 mb-5">
    <a href="{{ route('oac.expedientes.show', $e) }}" class="btn btn-outline-secondary px-4 fw-semibold">
      Cancelar
    </a>
    <button type="submit" class="btn btn-cgp px-4 fw-bold shadow-sm">
      <i class="fa-solid fa-floppy-disk me-1"></i> Guardar Cambios
    </button>
  </div>
</form>

@include('oac._modal_planilla')
@endsection

@push('scripts')
<script>
  // Contador de caracteres para la narración
  const narrativeText = document.getElementById('narrativeText');
  const charCount = document.getElementById('charCount');
  function updateCount() {
    if (!narrativeText || !charCount) return;
    const len = narrativeText.value.length;
    charCount.textContent = `${len} / 3500`;
    if (len < 50 || len > 3500) {
      charCount.classList.add('text-danger');
      charCount.classList.remove('text-muted');
    } else {
      charCount.classList.remove('text-danger');
      charCount.classList.add('text-muted');
    }
  }
  if (narrativeText) {
    narrativeText.addEventListener('input', updateCount);
    updateCount();
  }

  // Toggle de otra instancia
  function toggleInstancia(mostrar) {
    const b = document.getElementById('bloqueOtraInstancia');
    const input = document.getElementById('otraInstanciaNombre');
    if (mostrar) {
      b.classList.remove('d-none');
      input.focus();
    } else {
      b.classList.add('d-none');
    }
  }

  // Toggle de consulta popular
  function toggleConsulta(activo) {
    const b = document.getElementById('bloqueConsulta');
    if (activo) {
      b.classList.remove('d-none');
    } else {
      b.classList.add('d-none');
    }
  }

  // Repeater dinámico de señalados
  let senaladoIndex = {{ count($e->respondents) }};
  const tiposSenaladoJson = @json($tiposSenalado);

  function agregarSenalado() {
    const contenedor = document.getElementById('contenedorSenalados');
    const msg = document.getElementById('sinSenaladosMsg');
    if (msg) msg.remove();

    let opciones = '';
    tiposSenaladoJson.forEach(t => {
      opciones += `<option value="${t.id}">${t.name}</option>`;
    });

    const card = document.createElement('div');
    card.className = 'card border rounded-3 p-3 mb-3 item-senalado bg-light';
    card.innerHTML = `
      <div class="d-flex justify-content-between align-items-center mb-2">
        <span class="fw-bold text-primary-cgp small"><i class="fa-solid fa-user-tag me-1"></i> Nuevo Señalado</span>
        <button type="button" class="btn btn-sm btn-link text-danger p-0" onclick="eliminarSenalado(this)" title="Quitar este señalado">
          <i class="fa-solid fa-trash-can"></i> Quitar
        </button>
      </div>
      <div class="row g-2">
        <div class="col-md-4">
          <label class="form-label small fw-semibold">Tipo de Señalado</label>
          <select name="senalados[${senaladoIndex}][respondent_type_id]" class="form-select form-select-sm" required>
            ${opciones}
          </select>
        </div>
        <div class="col-md-8">
          <label class="form-label small fw-semibold">Ubicación del Señalado</label>
          <input type="text" name="senalados[${senaladoIndex}][location]" class="form-control form-control-sm" placeholder="Ubicación específica">
        </div>
        <div class="col-12 mt-2">
          <label class="form-label small fw-semibold text-muted d-block">Datos / Atributos de Identificación</label>
          <div class="row g-2">
            <div class="col-md-4">
              <input type="text" name="senalados[${senaladoIndex}][attributes][full_name]" class="form-control form-control-sm" placeholder="Nombre / Denominación / Razón Social">
            </div>
            <div class="col-md-4">
              <input type="text" name="senalados[${senaladoIndex}][attributes][id_number]" class="form-control form-control-sm" placeholder="Cédula / RIF / SITUR">
            </div>
            <div class="col-md-4">
              <input type="text" name="senalados[${senaladoIndex}][attributes][cargo]" class="form-control form-control-sm" placeholder="Cargo u observación">
            </div>
          </div>
        </div>
      </div>
    `;
    contenedor.appendChild(card);
    senaladoIndex++;
  }

  function eliminarSenalado(btn) {
    const card = btn.closest('.item-senalado');
    if (card) {
      card.remove();
    }
  }
</script>
@endpush
