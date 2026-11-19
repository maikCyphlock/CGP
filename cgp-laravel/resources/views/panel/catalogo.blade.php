@extends(request()->is('admin', 'admin/*') ? 'layouts.web' : 'layouts.oac')

@section('titulo', $c['titulo'])

@push('estilos')
<style>
  /* Switch deslizador modernizado */
  .switch-moderno {
    position: relative;
    display: inline-block;
    width: 44px;
    height: 24px;
    vertical-align: middle;
    margin: 0;
  }
  .switch-moderno input {
    opacity: 0;
    width: 0;
    height: 0;
    position: absolute;
  }
  .switch-slider-round {
    position: absolute;
    cursor: pointer;
    top: 0; left: 0; right: 0; bottom: 0;
    background-color: #cbd5e1;
    transition: all .25s ease;
    border-radius: 24px;
    border: 1px solid #94a3b8;
  }
  .switch-slider-round:before {
    position: absolute;
    content: "";
    height: 16px;
    width: 16px;
    left: 3px;
    bottom: 3px;
    background-color: #ffffff;
    transition: all .25s ease;
    border-radius: 50%;
    box-shadow: 0 1px 3px rgba(0,0,0,.25);
  }
  .switch-moderno input:checked + .switch-slider-round {
    background-color: #15803d;
    border-color: #15803d;
  }
  .switch-moderno input:checked + .switch-slider-round:before {
    transform: translateX(20px);
  }
  .switch-moderno input:disabled + .switch-slider-round {
    opacity: 0.5;
    cursor: not-allowed;
  }

  /* Filas inactivas / borrado lógico */
  tr.fila-inactiva {
    background-color: #f8fafc !important;
    opacity: 0.75;
  }

  /* Paginación modernizada */
  .pagination {
    margin-bottom: 0;
    gap: 4px;
  }
  .pagination .page-item .page-link {
    border-radius: 8px !important;
    border: 1px solid #e2e8f0;
    color: #334155;
    font-weight: 600;
    font-size: .85rem;
    padding: 6px 12px;
    background: #ffffff;
    transition: all .2s ease;
  }
  .pagination .page-item.active .page-link {
    background-color: var(--primary-color, #0d1b3e) !important;
    border-color: var(--primary-color, #0d1b3e) !important;
    color: #ffffff !important;
    box-shadow: 0 4px 8px rgba(13, 27, 62, 0.25);
  }
  .pagination .page-item .page-link:hover {
    background-color: #f1f5f9;
    color: var(--primary-color, #0d1b3e);
    border-color: #cbd5e1;
    transform: translateY(-1px);
  }
  .pagination .page-item.disabled .page-link {
    background-color: #f8fafc;
    border-color: #f1f5f9;
    color: #94a3b8;
    cursor: not-allowed;
  }

  /* Toast flotante */
  .toast-flotante {
    position: fixed;
    bottom: 24px;
    right: 24px;
    z-index: 1090;
  }
</style>
@endpush

@section('contenido')
<?php
  $mod = request()->is('admin', 'admin/*') ? 'admin' : 'oac';
  $puedeEscribir = $mod === 'admin' ? auth()->user()->puede('USERS', 'write') : auth()->user()->puede('CATALOGS', 'write');
?>

{{-- Barra superior con título y botón nuevo --}}
<div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
  <div class="d-flex align-items-center gap-3">
    <a href="{{ route($mod.'.catalogos.index') }}" class="btn btn-sm btn-outline-secondary" title="Volver a la lista de catálogos">
      <i class="fa-solid fa-arrow-left me-1"></i> Catálogos
    </a>
    <div>
      <h4 class="fw-bold text-primary-cgp mb-0 d-flex align-items-center gap-2">
        <span>{{ $c['titulo'] }}</span>
        <span class="badge bg-light text-secondary border font-monospace small" style="font-size: .75rem;">
          {{ $filas->total() }} registros
        </span>
      </h4>
    </div>
  </div>

  @if ($puedeEscribir)
    <button type="button" class="btn btn-sm btn-cgp fw-bold px-3 shadow-sm" data-bs-toggle="modal" data-bs-target="#modalCrear">
      <i class="fa-solid fa-plus me-1"></i> Nuevo registro
    </button>
  @endif
</div>

{{-- Barra de herramientas: Búsqueda en tiempo real y Filtros instantáneos --}}
<div class="card border-0 shadow-sm p-3 mb-3 bg-white rounded-3">
  <div class="row g-2 align-items-center justify-content-between">
    {{-- Pestañas de estado interactivo --}}
    <div class="col-lg-5 col-md-12 d-flex flex-wrap gap-2">
      <button type="button" class="btn btn-sm px-3 btn-filtro-estado btn-dark" data-filtro="todos">
        Todos <span class="badge bg-secondary ms-1">{{ $conteo['total'] }}</span>
      </button>
      <button type="button" class="btn btn-sm px-3 btn-filtro-estado btn-outline-secondary" data-filtro="activos">
        Activos <span class="badge bg-success ms-1">{{ $conteo['activos'] }}</span>
      </button>
      <button type="button" class="btn btn-sm px-3 btn-filtro-estado btn-outline-secondary" data-filtro="inactivos">
        Inactivos <span class="badge bg-secondary ms-1">{{ $conteo['inactivos'] }}</span>
      </button>
    </div>

    {{-- Buscador en tiempo real --}}
    <div class="col-lg-7 col-md-12">
      <div class="input-group input-group-sm">
        <span class="input-group-text bg-white border-end-0 text-muted"><i class="fa-solid fa-magnifying-glass"></i></span>
        <input type="text" id="buscadorTiempoReal" class="form-control border-start-0 border-end-0 ps-0" placeholder="Búsqueda en tiempo real en todo (código, nombre, descripción, estado)..." autocomplete="off">
        <button type="button" id="btnLimpiarBuscador" class="btn btn-outline-secondary border-start-0" title="Limpiar búsqueda" style="display: none;">
          <i class="fa-solid fa-xmark"></i>
        </button>
        <span id="badgeCoincidencias" class="input-group-text bg-light text-muted small" style="display: none;"></span>
      </div>
    </div>
  </div>
</div>

{{-- Tabla principal --}}
<div class="card border-0 shadow-sm rounded-3 overflow-hidden bg-white mb-3">
  <div class="table-responsive">
    <table class="table table-hover align-middle mb-0" style="font-size: .9rem;">
      <thead class="table-light">
        <tr>
          <?php $esPrimero = true; ?>
          @foreach ($c['campos'] as [$campo, $etiqueta, $tipo])
            @if (! in_array($tipo, ['json', 'textarea']))
              <th class="{{ $esPrimero ? 'ps-4' : '' }} text-muted small text-uppercase fw-bold">{{ $etiqueta }}</th>
              <?php $esPrimero = false; ?>
            @endif
          @endforeach

          @if ($slug === 'irregularidades')
            <th class="text-muted small text-uppercase fw-bold">Base legal</th>
          @elseif ($slug === 'cargos')
            <th class="text-muted small text-uppercase fw-bold">Descripción</th>
          @elseif ($slug === 'tipos-senalado')
            <th class="text-muted small text-uppercase fw-bold">Campos</th>
          @endif

          <th class="text-center text-muted small text-uppercase fw-bold" style="width: 140px;">Estado</th>
          <th class="text-end pe-4 text-muted small text-uppercase fw-bold" style="width: 150px;">Acciones</th>
        </tr>
      </thead>
      <tbody id="tbodyCatalogos">
        @forelse ($filas as $f)
          <?php
            $activo = (bool) $f->active;
            
            // Recolectar dinámicamente los términos de búsqueda
            $terminos = [];
            foreach ($c['campos'] as [$col]) {
                if (isset($f->$col)) {
                    $terminos[] = is_string($f->$col) ? $f->$col : json_encode($f->$col);
                }
            }
            $terminos[] = $activo ? 'activo' : 'inactivo borrado logico';
            $textoBusqueda = mb_strtolower(implode(' ', $terminos));
            $esPrimero = true;
          ?>
          <tr id="fila-{{ $f->id }}" 
              class="fila-registro {{ $activo ? '' : 'fila-inactiva' }}"
              data-id="{{ $f->id }}"
              data-activo="{{ $activo ? '1' : '0' }}"
              data-busqueda="{{ $textoBusqueda }}">
            
            @foreach ($c['campos'] as [$campo, $etiqueta, $tipo, $extra])
              @if (! in_array($tipo, ['json', 'textarea']))
                <td class="{{ $esPrimero ? 'ps-4 fw-bold text-primary-cgp' : '' }}">
                  @if ($campo === 'code')
                    <span class="badge bg-light text-dark font-monospace border px-2 py-1">{{ $f->$campo }}</span>
                  @elseif ($tipo === 'select')
                    <span class="badge bg-light text-dark border">{{ $extra[$f->$campo] ?? $f->$campo }}</span>
                  @else
                    {{ $f->$campo }}
                  @endif
                </td>
                <?php $esPrimero = false; ?>
              @endif
            @endforeach

            @if ($slug === 'irregularidades')
              <td class="text-muted small text-truncate" style="max-width: 240px;" title="{{ $f->legal_basis ?? '' }}">
                {{ Str::limit($f->legal_basis ?? '—', 45) }}
              </td>
            @elseif ($slug === 'cargos')
              <td class="text-muted small text-truncate" style="max-width: 240px;" title="{{ $f->description ?? '' }}">
                {{ Str::limit($f->description ?? '—', 45) }}
              </td>
            @elseif ($slug === 'tipos-senalado')
              <td class="small">
                <?php
                  $camposObj = is_string($f->field_schema ?? null) ? json_decode($f->field_schema, true) : ($f->field_schema ?? []);
                  $cant = is_array($camposObj) ? count($camposObj) : 0;
                ?>
                <span class="badge bg-light text-dark border">{{ $cant }} {{ $cant === 1 ? 'campo' : 'campos' }}</span>
              </td>
            @endif

            {{-- Switch deslizador modernizado --}}
            <td class="text-center text-nowrap">
              <div class="d-inline-flex align-items-center gap-2">
                <span id="badge-{{ $f->id }}" class="badge {{ $activo ? 'bg-success' : 'bg-secondary' }}" style="min-width: 58px;">
                  {{ $activo ? 'Activo' : 'Inactivo' }}
                </span>
                <label class="switch-moderno" title="{{ $activo ? 'Desactivar registro (Borrado lógico)' : 'Activar registro' }}">
                  <input type="checkbox"
                         class="switch-slider-input"
                         data-id="{{ $f->id }}"
                         data-url="{{ route($mod.'.catalogos.toggle', [$slug, $f->id]) }}"
                         @checked($activo)
                         @disabled(! $puedeEscribir)>
                  <span class="switch-slider-round"></span>
                </label>
              </div>
            </td>

            {{-- Botones de acción directos con datos precargados --}}
            <td class="text-end pe-4 text-nowrap">
              <div class="d-inline-flex align-items-center gap-1">
                {{-- Ver --}}
                <button type="button" class="btn btn-sm btn-outline-secondary px-2 py-1 btn-ver-modal"
                        title="Ver detalle"
                        data-bs-toggle="modal"
                        data-bs-target="#modalVer"
                        data-id="{{ $f->id }}"
                        data-val-id="{{ $f->id }}"
                        data-val-active="{{ $activo ? '1' : '0' }}"
                        @foreach ($c['campos'] as [$col])
                          @if (isset($f->$col))
                            data-val-{{ $col }}="{{ is_string($f->$col) ? $f->$col : json_encode($f->$col) }}"
                          @endif
                        @endforeach>
                  <i class="fa-solid fa-eye text-primary-cgp"></i>
                </button>

                @if ($puedeEscribir)
                  {{-- Editar --}}
                  <button type="button" class="btn btn-sm btn-outline-dark px-2 py-1 btn-editar-modal"
                          title="Editar registro"
                          data-bs-toggle="modal"
                          data-bs-target="#modalEditar"
                          data-id="{{ $f->id }}"
                          data-url="{{ route($mod.'.catalogos.update', [$slug, $f->id]) }}"
                          data-val-id="{{ $f->id }}"
                          data-val-active="{{ $activo ? '1' : '0' }}"
                          @foreach ($c['campos'] as [$col])
                            @if (isset($f->$col))
                              data-val-{{ $col }}="{{ is_string($f->$col) ? $f->$col : json_encode($f->$col) }}"
                            @endif
                          @endforeach>
                    <i class="fa-solid fa-pen-to-square"></i>
                  </button>

                  {{-- Desactivar (Borrado Lógico) / Reactivar --}}
                  @if ($activo)
                    <button type="button" class="btn btn-sm btn-outline-danger px-2 py-1 btn-eliminar-trigger"
                            title="Desactivar (Borrado lógico)"
                            data-bs-toggle="modal"
                            data-bs-target="#modalBorrado"
                            data-id="{{ $f->id }}"
                            data-nombre="{{ $f->name ?? $f->title ?? $f->code ?? '' }}"
                            data-url="{{ route($mod.'.catalogos.destroy', [$slug, $f->id]) }}">
                      <i class="fa-solid fa-trash-can"></i>
                    </button>
                  @else
                    <form method="POST" action="{{ route($mod.'.catalogos.toggle', [$slug, $f->id]) }}" class="d-inline m-0">
                      @csrf
                      <input type="hidden" name="active" value="1">
                      <button type="submit" class="btn btn-sm btn-outline-success px-2 py-1" title="Reactivar registro">
                        <i class="fa-solid fa-rotate-left"></i>
                      </button>
                    </form>
                  @endif
                @endif
              </div>
            </td>
          </tr>
        @empty
          <tr>
            <td colspan="6" class="text-center py-4 text-muted">
              No hay registros disponibles en este catálogo.
            </td>
          </tr>
        @endforelse

        {{-- Fila para cuando la búsqueda en tiempo real no arroja resultados --}}
        <tr id="filaSinCoincidencias" style="display: none;">
          <td colspan="6" class="text-center py-4 text-muted">
            <i class="fa-solid fa-magnifying-glass me-1 opacity-50"></i> No se encontraron coincidencias en la búsqueda.
          </td>
        </tr>
      </tbody>
    </table>
  </div>

  {{-- Paginación modernizada --}}
  <div class="card-footer bg-white border-top py-3 px-3 d-flex flex-wrap align-items-center justify-content-between gap-3">
    <div class="d-flex align-items-center gap-2">
      <span class="badge bg-light text-dark border font-monospace">
        Pág. {{ $filas->currentPage() }} de {{ $filas->lastPage() }}
      </span>
      <span class="text-muted small">
        Mostrando {{ $filas->firstItem() ?? 0 }}-{{ $filas->lastItem() ?? 0 }} de {{ $filas->total() }} registros
      </span>
    </div>
    <div>
      {{ $filas->links('pagination::bootstrap-5') }}
    </div>
  </div>
</div>

{{-- MODAL CREAR --}}
@if ($puedeEscribir)
<div class="modal fade" id="modalCrear" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content border-0 shadow">
      <div class="modal-header border-bottom py-3">
        <h6 class="modal-title fw-bold text-primary-cgp mb-0">Nuevo {{ Str::singular($c['titulo']) }}</h6>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
      </div>
      <form method="POST" action="{{ route($mod.'.catalogos.store', $slug) }}">
        @csrf
        <div class="modal-body p-4 row g-3">
          @foreach ($c['campos'] as [$campo, $etiqueta, $tipo, $extra])
            <div class="{{ in_array($tipo, ['textarea', 'json']) ? 'col-12' : 'col-md-6' }}">
              <label class="form-label fw-semibold small mb-1">{{ $etiqueta }}</label>
              @if ($tipo === 'select')
                <select name="{{ $campo }}" class="form-select form-select-sm" required>
                  <option value="" disabled selected>Seleccione...</option>
                  @foreach ($extra as $k => $t)
                    <option value="{{ $k }}">{{ $t }}</option>
                  @endforeach
                </select>
              @elseif ($tipo === 'textarea')
                <textarea name="{{ $campo }}" class="form-control form-control-sm" rows="3" maxlength="{{ $extra }}"></textarea>
              @elseif ($tipo === 'json')
                <textarea name="{{ $campo }}" class="form-control form-control-sm font-monospace" rows="3" required>[]</textarea>
              @else
                <input name="{{ $campo }}" class="form-control form-control-sm {{ $campo === 'code' ? 'text-uppercase font-monospace' : '' }}" maxlength="{{ $extra }}" required>
                @if ($campo === 'code')
                  <div class="form-text small">MAYÚSCULAS y guión bajo. Inmutable.</div>
                @endif
              @endif
            </div>
          @endforeach

          <div class="col-12 border-top pt-3 d-flex align-items-center justify-content-between">
            <span class="small fw-semibold text-dark">Activo en el sistema</span>
            <label class="switch-moderno">
              <input type="checkbox" name="active" value="1" checked>
              <span class="switch-slider-round"></span>
            </label>
          </div>
        </div>
        <div class="modal-footer border-top py-2 px-3">
          <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
          <button type="submit" class="btn btn-sm btn-cgp fw-bold px-3">Guardar</button>
        </div>
      </form>
    </div>
  </div>
</div>

{{-- MODAL EDITAR --}}
<div class="modal fade" id="modalEditar" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content border-0 shadow">
      <div class="modal-header border-bottom py-3">
        <h6 class="modal-title fw-bold text-primary-cgp mb-0">Editar registro</h6>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
      </div>
      <form id="formEditar" method="POST" action="">
        @csrf
        <div class="modal-body p-4 row g-3">
          @foreach ($c['campos'] as [$campo, $etiqueta, $tipo, $extra])
            <div class="{{ in_array($tipo, ['textarea', 'json']) ? 'col-12' : 'col-md-6' }}">
              <label class="form-label fw-semibold small mb-1">{{ $etiqueta }}</label>
              @if ($campo === 'code')
                <input id="edit_{{ $campo }}" class="form-control form-control-sm bg-light font-monospace" disabled>
                <div class="form-text small">El código es inmutable para proteger el histórico.</div>
              @elseif ($tipo === 'select')
                <select id="edit_{{ $campo }}" name="{{ $campo }}" class="form-select form-select-sm" required>
                  @foreach ($extra as $k => $t)
                    <option value="{{ $k }}">{{ $t }}</option>
                  @endforeach
                </select>
              @elseif ($tipo === 'textarea')
                <textarea id="edit_{{ $campo }}" name="{{ $campo }}" class="form-control form-control-sm" rows="3" maxlength="{{ $extra }}"></textarea>
              @elseif ($tipo === 'json')
                <textarea id="edit_{{ $campo }}" name="{{ $campo }}" class="form-control form-control-sm font-monospace" rows="3" required></textarea>
              @else
                <input id="edit_{{ $campo }}" name="{{ $campo }}" class="form-control form-control-sm" maxlength="{{ $extra }}" required>
              @endif
            </div>
          @endforeach

          <div class="col-12 border-top pt-3 d-flex align-items-center justify-content-between">
            <span class="small fw-semibold text-dark">Activo en el sistema</span>
            <input type="hidden" name="active" value="0">
            <label class="switch-moderno">
              <input type="checkbox" id="edit_active" name="active" value="1">
              <span class="switch-slider-round"></span>
            </label>
          </div>
        </div>
        <div class="modal-footer border-top py-2 px-3">
          <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
          <button type="submit" class="btn btn-sm btn-cgp fw-bold px-3">Actualizar</button>
        </div>
      </form>
    </div>
  </div>
</div>
@endif

{{-- MODAL VER DETALLE --}}
<div class="modal fade" id="modalVer" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content border-0 shadow">
      <div class="modal-header border-bottom py-3">
        <h6 class="modal-title fw-bold text-primary-cgp mb-0">Detalle del Registro</h6>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
      </div>
      <div class="modal-body p-4">
        <div class="d-flex align-items-center justify-content-between p-3 rounded-2 mb-3 bg-light border">
          <div>
            <span class="badge bg-light text-secondary border font-monospace small mb-1" id="verId">#0</span>
            <h6 id="verNombrePrincipal" class="fw-bold text-primary-cgp mb-0">-</h6>
          </div>
          <span id="verBadgeEstado" class="badge bg-success">Activo</span>
        </div>

        <div class="row g-2" id="verContenedorCampos">
          {{-- Se inyecta dinámicamente con JavaScript con la información completa --}}
        </div>
      </div>
      <div class="modal-footer border-top py-2 px-3">
        <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-dismiss="modal">Cerrar</button>
      </div>
    </div>
  </div>
</div>

{{-- MODAL CONFIRMAR BORRADO LÓGICO --}}
@if ($puedeEscribir)
<div class="modal fade" id="modalBorrado" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-sm">
    <div class="modal-content border-0 shadow">
      <div class="modal-header border-bottom py-2">
        <h6 class="modal-title fw-bold text-danger mb-0"><i class="fa-solid fa-triangle-exclamation me-1"></i> Desactivar</h6>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
      </div>
      <form id="formBorrado" method="POST" action="">
        @csrf
        <div class="modal-body p-3">
          <p class="small text-muted mb-0">
            ¿Desactivar <strong id="nombreEliminar" class="text-dark"></strong>? 
            Se aplicará borrado lógico para preservar el histórico.
          </p>
        </div>
        <div class="modal-footer border-top py-2 px-3">
          <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
          <button type="submit" class="btn btn-sm btn-danger fw-bold">Desactivar</button>
        </div>
      </form>
    </div>
  </div>
</div>
@endif

{{-- Toast flotante --}}
<div class="toast-flotante">
  <div id="toastApp" class="toast align-items-center text-white bg-dark border-0 shadow" role="alert" aria-live="assertive" aria-atomic="true">
    <div class="d-flex">
      <div class="toast-body py-2 px-3 small d-flex align-items-center gap-2">
        <i id="toastIcono" class="fa-solid fa-circle-check text-success"></i>
        <span id="toastTexto">Operación completada</span>
      </div>
      <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Cerrar"></button>
    </div>
  </div>
</div>

@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {
  const tokenCsrf = '{{ csrf_token() }}';
  const camposCatalogo = @json($c['campos']);

  // Toast flotante
  const toastEl = document.getElementById('toastApp');
  const toastBs = toastEl ? bootstrap.Toast.getOrCreateInstance(toastEl, { delay: 3000 }) : null;
  function avisar(msg, error = false) {
    if (!toastBs) return;
    document.getElementById('toastTexto').textContent = msg;
    document.getElementById('toastIcono').className = error ? 'fa-solid fa-circle-exclamation text-danger' : 'fa-solid fa-circle-check text-success';
    toastBs.show();
  }

  // Búsqueda en tiempo real y filtrado instantáneo
  const inputBusqueda = document.getElementById('buscadorTiempoReal');
  const btnLimpiar = document.getElementById('btnLimpiarBuscador');
  const badgeCoincidencias = document.getElementById('badgeCoincidencias');
  const filas = document.querySelectorAll('.fila-registro');
  const filaSinCoincidencias = document.getElementById('filaSinCoincidencias');
  const botonesFiltro = document.querySelectorAll('.btn-filtro-estado');

  let estadoSeleccionado = 'todos';

  function filtrarTabla() {
    const q = (inputBusqueda?.value || '').toLowerCase().trim();
    let contador = 0;

    filas.forEach(f => {
      const activo = f.getAttribute('data-activo');
      const texto = f.getAttribute('data-busqueda') || '';

      const coincideEstado = (estadoSeleccionado === 'todos') ||
                             (estadoSeleccionado === 'activos' && activo === '1') ||
                             (estadoSeleccionado === 'inactivos' && activo === '0');

      const coincideTexto = q === '' || texto.includes(q);

      if (coincideEstado && coincideTexto) {
        f.style.display = '';
        contador++;
      } else {
        f.style.display = 'none';
      }
    });

    if (filaSinCoincidencias) {
      filaSinCoincidencias.style.display = (contador === 0 && (q !== '' || estadoSeleccionado !== 'todos')) ? '' : 'none';
    }

    if (btnLimpiar) {
      btnLimpiar.style.display = q !== '' ? '' : 'none';
    }

    if (badgeCoincidencias) {
      if (q !== '') {
        badgeCoincidencias.textContent = `${contador} encontrados`;
        badgeCoincidencias.style.display = '';
      } else {
        badgeCoincidencias.style.display = 'none';
      }
    }
  }

  if (inputBusqueda) {
    inputBusqueda.addEventListener('input', filtrarTabla);
  }

  if (btnLimpiar) {
    btnLimpiar.addEventListener('click', () => {
      inputBusqueda.value = '';
      filtrarTabla();
      inputBusqueda.focus();
    });
  }

  botonesFiltro.forEach(btn => {
    btn.addEventListener('click', function () {
      botonesFiltro.forEach(b => {
        b.classList.remove('btn-dark');
        b.classList.add('btn-outline-secondary');
      });
      this.classList.remove('btn-outline-secondary');
      this.classList.add('btn-dark');

      estadoSeleccionado = this.getAttribute('data-filtro') || 'todos';
      filtrarTabla();
    });
  });

  // Switch deslizador modernizado (AJAX)
  document.querySelectorAll('.switch-slider-input').forEach(sw => {
    sw.addEventListener('change', async function () {
      const id = this.dataset.id;
      const url = this.dataset.url;
      const nuevoActivo = this.checked;
      const fila = document.getElementById(`fila-${id}`);
      const badge = document.getElementById(`badge-${id}`);

      this.disabled = true;
      try {
        const fd = new FormData();
        fd.append('_token', tokenCsrf);
        fd.append('active', nuevoActivo ? '1' : '0');

        const res = await fetch(url, {
          method: 'POST',
          headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' },
          body: fd
        });
        const data = await res.json();

        if (res.ok && data.ok) {
          if (fila) {
            fila.setAttribute('data-activo', nuevoActivo ? '1' : '0');
            if (nuevoActivo) {
              fila.classList.remove('fila-inactiva');
            } else {
              fila.classList.add('fila-inactiva');
            }
          }
          if (badge) {
            badge.className = nuevoActivo ? 'badge bg-success' : 'badge bg-secondary';
            badge.textContent = nuevoActivo ? 'Activo' : 'Inactivo';
          }
          avisar(data.mensaje || 'Estado actualizado.');
        } else {
          this.checked = !nuevoActivo;
          avisar(data.error || 'No se pudo actualizar.', true);
        }
      } catch (e) {
        this.checked = !nuevoActivo;
        avisar('Error de comunicación.', true);
      } finally {
        this.disabled = false;
      }
    });
  });

  // Modal Ver Detalle (Bootstrap event) - Precarga garantizada campo por campo
  const modalVer = document.getElementById('modalVer');
  if (modalVer) {
    modalVer.addEventListener('show.bs.modal', function (e) {
      const btn = e.relatedTarget?.closest('button') || e.relatedTarget;
      if (!btn) return;

      const id = btn.getAttribute('data-val-id') || btn.dataset.id || '0';
      const esActivo = btn.getAttribute('data-val-active') === '1';

      const nombreVal = btn.getAttribute('data-val-name') || btn.getAttribute('data-val-title') || btn.getAttribute('data-val-code') || ('Registro #' + id);

      const verId = document.getElementById('verId');
      if (verId) verId.textContent = '#' + id;

      const verNombrePrincipal = document.getElementById('verNombrePrincipal');
      if (verNombrePrincipal) verNombrePrincipal.textContent = nombreVal;

      const badgeEstado = document.getElementById('verBadgeEstado');
      if (badgeEstado) {
        badgeEstado.className = esActivo ? 'badge bg-success' : 'badge bg-secondary';
        badgeEstado.textContent = esActivo ? 'Activo' : 'Inactivo (Borrado lógico)';
      }

      const contenedor = document.getElementById('verContenedorCampos');
      if (contenedor) {
        contenedor.innerHTML = '';
        camposCatalogo.forEach(([campo, etiqueta, tipo, extra]) => {
          let val = btn.getAttribute(`data-val-${campo}`) || '';
          let valHtml = '';

          if (tipo === 'select' && extra && extra[val]) {
            valHtml = `<span class="badge bg-light text-dark border">${extra[val]}</span>`;
          } else if (tipo === 'textarea') {
            valHtml = `<div class="p-2 rounded bg-light border small text-muted mt-1" style="white-space: pre-wrap;">${val || '—'}</div>`;
          } else if (tipo === 'json') {
            let jsonStr = val;
            try {
              jsonStr = JSON.stringify(JSON.parse(val), null, 2);
            } catch(e) {}
            valHtml = `<pre class="p-2 rounded bg-light border font-monospace small mb-0 mt-1" style="max-height: 180px; overflow-y: auto;">${jsonStr || '[]'}</pre>`;
          } else if (campo === 'code') {
            valHtml = `<span class="badge bg-light text-dark font-monospace border px-2 py-1">${val || '—'}</span>`;
          } else {
            valHtml = `<span class="fw-semibold text-dark">${val || '—'}</span>`;
          }

          const col = (tipo === 'textarea' || tipo === 'json') ? 'col-12' : 'col-6';
          contenedor.innerHTML += `
            <div class="${col} mb-2">
              <span class="text-muted small d-block mb-1">${etiqueta}</span>
              ${valHtml}
            </div>
          `;
        });
      }
    });
  }

  // Modal Editar (Bootstrap event) - Precarga garantizada de inputs
  const modalEditar = document.getElementById('modalEditar');
  if (modalEditar) {
    modalEditar.addEventListener('show.bs.modal', function (e) {
      const btn = e.relatedTarget?.closest('button') || e.relatedTarget;
      if (!btn) return;

      const form = document.getElementById('formEditar');
      if (form && btn.dataset.url) {
        form.action = btn.dataset.url;
      }

      camposCatalogo.forEach(([campo, , tipo]) => {
        const inp = document.getElementById(`edit_${campo}`);
        if (!inp) return;
        let v = btn.getAttribute(`data-val-${campo}`) || '';
        if (tipo === 'json' && v) {
          try {
            v = JSON.stringify(JSON.parse(v), null, 2);
          } catch(err) {}
        }
        inp.value = v;
      });

      const swActive = document.getElementById('edit_active');
      if (swActive) {
        const esActivo = btn.getAttribute('data-val-active') === '1';
        swActive.checked = esActivo;
      }
    });
  }

  // Modal Borrado Lógico (Bootstrap event)
  const modalBorrado = document.getElementById('modalBorrado');
  if (modalBorrado) {
    modalBorrado.addEventListener('show.bs.modal', function (e) {
      const btn = e.relatedTarget?.closest('button') || e.relatedTarget;
      if (!btn) return;

      const form = document.getElementById('formBorrado');
      if (form && btn.dataset.url) {
        form.action = btn.dataset.url;
      }

      const nombreEl = document.getElementById('nombreEliminar');
      if (nombreEl) {
        nombreEl.textContent = btn.dataset.nombre || ('Registro #' + (btn.dataset.id || ''));
      }
    });
  }
});
</script>
@endpush
