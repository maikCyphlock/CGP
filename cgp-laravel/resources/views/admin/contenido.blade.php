@extends('layouts.admin')

@section('titulo', $item ? 'Editar contenido' : 'Nuevo contenido')

@push('estilos')
<link rel="stylesheet" href="https://uicdn.toast.com/editor/3.2.2/toastui-editor.min.css">
<style>
  .toastui-editor-defaultUI { border-color: var(--border-strong); border-radius: var(--radius-md); overflow: hidden; font-family: var(--font); }
  .ed-title { font-size: 1.35rem; font-weight: 700; border: 0; border-bottom: 2px solid var(--border); border-radius: 0; padding: 6px 0; box-shadow: none !important; background: transparent; }
  .ed-title:focus { border-bottom-color: var(--brand); }
  .ed-estado { display: flex; align-items: center; gap: 10px; padding: 10px 14px; border-radius: var(--radius-md); background: var(--surface); border: 1px solid var(--border); }
  .ed-estado.on { background: var(--success-tint); border-color: #b9e3cc; }
  .pv-card { background: #fff; border: 1px solid #e0e0e0; border-radius: 6px; overflow: hidden; }
  .pv-card .top { background: #1a4b8c; height: 6px; }
  .pv-card .cuerpo { padding: 20px; }
  .pv-card .fecha { font-size: .75rem; color: #1565c0; font-weight: 700; text-transform: uppercase; letter-spacing: .08em; margin-bottom: 8px; }
  .pv-card h5 { font-weight: 700; font-size: .95rem; color: #1a2340; margin-bottom: 10px; }
  .pv-card .texto { font-size: .85rem; color: #555; line-height: 1.6; }
  .pv-card .texto p { margin-bottom: .6rem; }
  .pv-card .texto ul, .pv-card .texto ol { padding-left: 1.2rem; }
</style>
@endpush

@section('contenido')
@php
  $puedeEscribir = auth()->user()->puede('CMS', 'write');
  $dondeSale = ['NEWS' => 'Sale en «Noticias» de la portada (las 6 más recientes).', 'MISSION' => 'Reemplaza el texto de la Misión en la portada.', 'VISION' => 'Reemplaza el texto de la Visión en la portada.'];
@endphp
<a href="{{ route('admin.contenidos.index') }}" class="btn btn-sm btn-outline-secondary mb-3"><i class="fa-solid fa-arrow-left me-1"></i> Volver</a>

<form method="POST" id="ed-form" enctype="multipart/form-data" action="{{ $item ? route('admin.contenidos.update', $item->id) : route('admin.contenidos.store') }}">
  @csrf
  <fieldset @disabled(! $puedeEscribir)>
  <div class="row g-4">
    <div class="col-lg-7">
      <div class="card border-0 shadow-sm">
        <div class="card-body p-4">
          <div class="row g-3 mb-3 align-items-end">
            <div class="col-md-6">
              <label class="form-label fw-semibold small">Tipo de contenido</label>
              <select name="content_type_id" id="ed-tipo" class="form-select" required>
                @foreach ($tipos as $t) <option value="{{ $t->id }}" data-code="{{ $t->code }}" @selected(old('content_type_id', $item->content_type_id ?? $tipos->firstWhere('code', 'NEWS')?->id) == $t->id)>{{ $t->name }}</option> @endforeach
              </select>
            </div>
            <div class="col-md-6">
              <label class="ed-estado mb-0" id="ed-estado" for="pub">
                <input class="form-check-input m-0" type="checkbox" name="published" value="1" id="pub" @checked(old('published', $item->published ?? false))>
                <span><strong id="ed-estado-txt"></strong><br><small class="text-muted" id="ed-donde"></small></span>
              </label>
            </div>
          </div>

          <input name="title" id="ed-titulo" class="form-control ed-title mb-4" maxlength="200" placeholder="Título" value="{{ old('title', $item->title ?? '') }}" required>

          <div id="ed-editor"></div>
          <textarea name="body" id="ed-cuerpo" hidden>{{ old('body', $item->body ?? '') }}</textarea>
          <div class="d-flex justify-content-between small mt-1"><span class="text-danger" id="ed-cuerpo-error" hidden>Escribe el contenido.</span><span class="text-muted ms-auto" id="ed-cuenta"></span></div>

          <div class="mt-4">
            <label class="form-label fw-semibold small">Imagen <span class="text-muted fw-normal">(opcional · JPG, PNG o WebP · máx. 4 MB · se muestra en las noticias)</span></label>
            @if ($item?->image_path)
              <div class="d-flex align-items-center gap-3 mb-2" id="ed-img-actual">
                <img src="{{ route('admin.contenidos.imagen', $item->image_path) }}" alt="" style="height:64px;border-radius:6px;border:1px solid var(--border)">
                <label class="small text-danger mb-0"><input type="checkbox" name="quitar_imagen" value="1" class="form-check-input me-1">Quitar imagen</label>
              </div>
            @endif
            <input type="file" name="imagen" id="ed-img" class="form-control" accept="image/jpeg,image/png,image/webp">
            <div class="small text-danger mt-1" id="ed-img-error" hidden></div>
          </div>

          @if ($puedeEscribir)
            <div class="mt-4 d-flex align-items-center gap-3">
              <button type="submit" class="btn btn-cgp fw-bold px-4">Guardar</button>
              <span class="small text-muted" id="ed-sin-guardar" hidden><i class="fa-solid fa-circle text-warning me-1" style="font-size:.5rem;vertical-align:middle"></i>Cambios sin guardar</span>
            </div>
          @endif
        </div>
      </div>
    </div>

    <div class="col-lg-5">
      <div class="fw-semibold small text-muted mb-2"><i class="fa-regular fa-eye me-1"></i> Así se verá en el portal</div>
      <div class="pv-card mb-4">
        <div class="top"></div>
        <img id="pv-img" alt="" style="width:100%;aspect-ratio:16/9;object-fit:cover;display:block" @if ($item?->image_path) src="{{ route('admin.contenidos.imagen', $item->image_path) }}" @else hidden @endif>
        <div class="cuerpo">
          <div class="fecha">{{ now()->timezone(config('app.timezone'))->locale('es')->translatedFormat('j \d\e F, Y') }}</div>
          <h5 id="pv-titulo"></h5>
          <div class="texto" id="pv-texto"></div>
        </div>
      </div>

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
  </fieldset>
</form>
@endsection

@push('scripts')
<script src="https://uicdn.toast.com/editor/3.2.2/toastui-editor-all.min.js"></script>
<script src="https://uicdn.toast.com/editor/3.2.2/i18n/es-es.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/marked/12.0.2/marked.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/dompurify/3.1.6/purify.min.js"></script>
<script>
(() => {
  const $ = id => document.getElementById(id);
  const cuerpo = $('ed-cuerpo'), titulo = $('ed-titulo'), pub = $('pub'), tipo = $('ed-tipo');
  const donde = @json($dondeSale);
  const puedeEscribir = @json($puedeEscribir);
  let sucio = false, editor = null;

  function render() {
    const md = cuerpo.value;
    $('pv-titulo').textContent = titulo.value || 'Título de la noticia';
    $('pv-texto').innerHTML = DOMPurify.sanitize(md.trim() ? marked.parse(md, { breaks: true }) : '<p class="text-muted">El texto aparecerá aquí.</p>');
    $('ed-cuenta').textContent = md.length.toLocaleString('es') + ' / 50.000';
    $('ed-estado').classList.toggle('on', pub.checked);
    $('ed-estado-txt').textContent = pub.checked ? 'Publicado' : 'Borrador';
    const code = tipo.selectedOptions[0]?.dataset.code;
    $('ed-donde').textContent = pub.checked ? (donde[code] || 'Visible en el panel; este tipo aún no se muestra en el sitio.') : 'Nadie lo ve en el portal hasta que lo publiques.';
  }

  const marcar = () => { sucio = true; $('ed-sin-guardar')?.removeAttribute('hidden'); render(); };
  [titulo, pub, tipo].forEach(e => e.addEventListener('input', marcar));
  tipo.addEventListener('change', marcar);

  // Editor visual: el texto se guarda como Markdown en el campo oculto.
  if (puedeEscribir) {
    editor = new toastui.Editor({
      el: $('ed-editor'), height: '380px', initialEditType: 'wysiwyg', previewStyle: 'tab', language: 'es-ES',
      initialValue: cuerpo.value, usageStatistics: false, hideModeSwitch: false,
      placeholder: 'Escribe aquí el contenido…',
      toolbarItems: [['heading', 'bold', 'italic', 'strike'], ['ul', 'ol', 'quote', 'hr'], ['link']],
      events: { change: () => { cuerpo.value = editor.getMarkdown(); marcar(); $('ed-cuerpo-error').hidden = true; } },
    });
  } else {
    toastui.Editor.factory({ el: $('ed-editor'), viewer: true, initialValue: cuerpo.value });
  }

  // Imagen: valida el peso antes de enviar y la muestra en la vista previa.
  $('ed-img').addEventListener('change', e => {
    const f = e.target.files[0], err = $('ed-img-error');
    err.hidden = true;
    if (!f) return;
    if (!/^image\/(jpeg|png|webp)$/.test(f.type)) { err.textContent = 'Debe ser JPG, PNG o WebP.'; err.hidden = false; e.target.value = ''; return; }
    if (f.size > 4 * 1024 * 1024) { err.textContent = 'La imagen pesa ' + (f.size / 1048576).toFixed(1) + ' MB; el máximo es 4 MB.'; err.hidden = false; e.target.value = ''; return; }
    $('pv-img').src = URL.createObjectURL(f); $('pv-img').hidden = false; marcar();
  });

  $('ed-form').addEventListener('submit', e => {
    if (editor) cuerpo.value = editor.getMarkdown();
    if (puedeEscribir && !cuerpo.value.trim()) { e.preventDefault(); e.stopImmediatePropagation(); $('ed-cuerpo-error').hidden = false; editor.focus(); return; }
    sucio = false;
  }, true);
  window.addEventListener('beforeunload', e => { if (sucio) { e.preventDefault(); e.returnValue = ''; } });
  render();
})();
</script>
@endpush
