{{-- Modal de confirmación para Eliminado Lógico de Expedientes --}}
<div class="modal fade" id="modalEliminarExpediente" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content border-0 shadow">
      <form id="formEliminarExpediente" method="POST" action="">
        @csrf
        <div class="modal-header border-0 pb-0">
          <h5 class="modal-title text-danger fw-bold"><i class="fa-solid fa-trash-can me-2"></i>Eliminado Lógico de Expediente</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
        </div>
        <div class="modal-body py-3">
          <p class="mb-2">¿Está seguro de que desea eliminar lógicamente el expediente <strong id="numeroExpedienteEliminar" class="text-primary-cgp"></strong>?</p>
          <div class="alert alert-light border small text-muted mb-3">
            <i class="fa-solid fa-circle-info me-1 text-primary"></i> <strong>Nota:</strong> Esta acción ocultará el expediente de las listas activas de la OAC y de las consultas públicas, pero la información y sus documentos se conservarán resguardados en el sistema para fines de auditoría y respaldo institucional.
          </div>
          <div>
            <label class="form-label small fw-semibold">Motivo de la eliminación <span class="text-muted">(opcional)</span>:</label>
            <input type="text" name="motivo" class="form-control form-control-sm" placeholder="Ej. Registro duplicado, desistimiento, error en carga original...">
          </div>
        </div>
        <div class="modal-footer border-0 pt-0">
          <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
          <button type="submit" class="btn btn-sm btn-danger fw-bold"><i class="fa-solid fa-trash-can me-1"></i> Confirmar Eliminación</button>
        </div>
      </form>
    </div>
  </div>
</div>

@push('scripts')
<script>
  document.addEventListener('DOMContentLoaded', () => {
    const modalEl = document.getElementById('modalEliminarExpediente');
    if (!modalEl) return;
    const formEliminar = document.getElementById('formEliminarExpediente');
    const spanNumero = document.getElementById('numeroExpedienteEliminar');

    document.querySelectorAll('.btn-eliminar-expediente').forEach(btn => {
      btn.addEventListener('click', ev => {
        ev.preventDefault();
        ev.stopPropagation();
        formEliminar.action = btn.dataset.action;
        spanNumero.textContent = btn.dataset.numero;
        bootstrap.Modal.getOrCreateInstance(modalEl).show();
      });
    });
  });
</script>
@endpush
