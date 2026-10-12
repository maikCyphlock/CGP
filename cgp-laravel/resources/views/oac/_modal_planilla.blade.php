{{-- Visor rápido de la planilla PDF: cualquier <a class="ver-planilla" data-titulo> lo abre. --}}
<div class="modal fade" id="modalPlanilla" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-xl modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header py-2">
        <h6 class="modal-title">Planilla <span id="planillaTitulo"></span></h6>
        <a id="planillaNueva" href="#" target="_blank" class="btn btn-sm btn-outline-dark ms-auto me-2"><i class="fa-solid fa-up-right-from-square me-1"></i> Abrir</a>
        <button type="button" class="btn-close m-0" data-bs-dismiss="modal" aria-label="Cerrar"></button>
      </div>
      <iframe id="planillaFrame" style="width:100%;height:80vh;border:0"></iframe>
    </div>
  </div>
</div>


@push('scripts')
<script>
  const modalPlanilla = document.getElementById('modalPlanilla');
  document.querySelectorAll('.ver-planilla').forEach(a => a.addEventListener('click', ev => {
    ev.preventDefault(); ev.stopPropagation();
    document.getElementById('planillaFrame').src = a.href;
    document.getElementById('planillaNueva').href = a.href;
    document.getElementById('planillaTitulo').textContent = a.dataset.titulo;
    bootstrap.Modal.getOrCreateInstance(modalPlanilla).show();
  }));
  modalPlanilla.addEventListener('hidden.bs.modal', () => document.getElementById('planillaFrame').src = 'about:blank');
</script>
@endpush
