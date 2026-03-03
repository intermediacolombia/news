<?php 
require_once __DIR__ . '/../login/session.php';  // Inicia la sesión y carga la información del usuario
$permisopage = 'Crear Entrada';
require_once __DIR__ . '/../login/restriction.php';


require_once __DIR__ . '/blog_controller.php'; // <- controlador de blog (similar al de productos)

// Recoger y limpiar errores/old de sesión
$errors = $_SESSION['errors'] ?? [];
$old    = $_SESSION['old']    ?? [];
unset($_SESSION['errors'], $_SESSION['old']);

function oldv($key, $default=''){
  global $old;
  return htmlspecialchars($old[$key] ?? $default, ENT_QUOTES, 'UTF-8');
}
$oldCats   = array_map('intval', $old['categories'] ?? []);
$oldStatus = $old['status'] ?? 'draft';
?>
<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <title>Nueva entrada de blog</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">  
  <?php require_once __DIR__ . '/../inc/header.php'; ?>
</head>
<body>
<div class="container" style="padding: 0px; background:rgba(0,0,0,0.00)">
  <div class="portada">
    <h1 class="mb-4"><i class="bi bi-layout-text-window-reverse"></i> Nueva Entrada</h1>
  </div>
</div>
<?php require_once __DIR__ . '/../inc/menu.php'; ?>

<div class="wrap">
  <div class="card">
    <div class="card-header d-flex align-items-center justify-content-between">
      <h5 class="mb-0">Nueva entrada</h5>
      <span class="badge badge-brand">Blog</span>
    </div>

    <div class="card-body">

      <?php if(isset($errors['__global'])): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($errors['__global']) ?></div>
      <?php endif; ?>

      <form method="post" enctype="multipart/form-data" novalidate>
        <div class="row g-4">
          <div class="col-lg-8">
            <div class="mb-3">
              <label class="form-label">Título *</label>
              <input type="text" class="form-control<?= isset($errors['title'])?' is-invalid':'' ?>" name="title" id="title" required placeholder="Mi artículo" value="<?= oldv('title') ?>">
              <?php if(isset($errors['title'])): ?><div class="invalid-feedback"><?= htmlspecialchars($errors['title']) ?></div><?php endif; ?>
              <div class="hint mt-1">El <em>slug</em> se genera automáticamente (puedes editarlo).</div>
            </div>

            <div class="mb-3">
              <label class="form-label">Slug</label>
              <input type="text" class="form-control<?= isset($errors['slug'])?' is-invalid':'' ?>" name="slug" id="slug" placeholder="mi-articulo-ejemplo" value="<?= oldv('slug') ?>">
              <?php if(isset($errors['slug'])): ?><div class="invalid-feedback"><?= htmlspecialchars($errors['slug']) ?></div><?php endif; ?>
            </div>

            <!-- Categorías -->
            <div class="mb-3">
              <label class="form-label">Categorías</label>
              <select name="categories[]" class="form-select" multiple>
                <?php foreach(($cats ?? []) as $c): ?>
                  <option value="<?= (int)$c['id'] ?>" <?= in_array((int)$c['id'],$oldCats,true) ? 'selected' : '' ?>>
                    <?= htmlspecialchars($c['name']) ?>
                  </option>
                <?php endforeach; ?>
              </select>
              <div class="hint mt-1">Mantén CTRL/⌘ para seleccionar varias</div>
            </div>

            <div class="mb-3">
  <label class="form-label">Contenido *</label>
  <textarea class="form-control<?= isset($errors['content'])?' is-invalid':'' ?> summernote" 
            name="content" 
             
            rows="10"><?= oldv('content') ?></textarea>
  <?php if(isset($errors['content'])): ?>
    <div class="invalid-feedback"><?= htmlspecialchars($errors['content']) ?></div>
  <?php endif; ?>
</div>
			  
	<!-- SEO -->
<hr class="my-4">
<h5 class="mb-3 text-primary">Configuración SEO</h5>
<p class="text-muted">Optimiza cómo aparecerá esta entrada en buscadores (Google, Bing...).</p>

<div class="mb-3">
  <label class="form-label">SEO Title</label>
  <input type="text"
         class="form-control<?= isset($errors['seo_title'])?' is-invalid':'' ?>"
         name="seo_title"
         id="seo_title"
         maxlength="180"
         placeholder="Título SEO (aparece en Google)"
         value="<?= oldv('seo_title') ?>">
  <div class="hint mt-1">
    Máx 60–70 caracteres recomendados.
    <span id="seo_title_counter" class="badge bg-secondary">0</span>
  </div>
  <?php if(isset($errors['seo_title'])): ?><div class="invalid-feedback"><?= htmlspecialchars($errors['seo_title']) ?></div><?php endif; ?>
</div>

<div class="mb-3">
  <label class="form-label">SEO Descripción</label>
  <textarea class="form-control<?= isset($errors['seo_description'])?' is-invalid':'' ?>"
            name="seo_description"
            id="seo_description"
            maxlength="300"
            rows="2"
            placeholder="Meta descripción para buscadores"><?= oldv('seo_description') ?></textarea>
  <div class="hint mt-1">
    Máx 160 caracteres recomendados.
    <span id="seo_description_counter" class="badge bg-secondary">0</span>
  </div>
  <?php if(isset($errors['seo_description'])): ?><div class="invalid-feedback"><?= htmlspecialchars($errors['seo_description']) ?></div><?php endif; ?>
</div>

<div class="mb-3">
  <label class="form-label">SEO Keywords</label>
  <input type="text"
         class="form-control<?= isset($errors['seo_keywords'])?' is-invalid':'' ?>"
         name="seo_keywords"
         id="seo_keywords"
         maxlength="300"
         placeholder="palabra1, palabra2, palabra3"
         value="<?= oldv('seo_keywords') ?>">
  <div class="hint mt-1">
    Opcional. Separa por comas.
    <span id="seo_keywords_counter" class="badge bg-secondary">0</span>
  </div>
  <?php if(isset($errors['seo_keywords'])): ?><div class="invalid-feedback"><?= htmlspecialchars($errors['seo_keywords']) ?></div><?php endif; ?>
</div>
<!-- FIN SEO -->


          </div>

          <div class="col-lg-4">
            <div class="mt-3">
              <label class="form-label">Estado</label>
              <select class="form-select<?= isset($errors['status'])?' is-invalid':'' ?>" name="status">
                <option value="draft"    <?= $oldStatus==='draft'?'selected':''; ?>>Borrador</option>
                <option value="published" <?= $oldStatus==='published'?'selected':''; ?>>Publicado</option>
              </select>
              <?php if(isset($errors['status'])): ?><div class="invalid-feedback"><?= htmlspecialchars($errors['status']) ?></div><?php endif; ?>
            </div>

            <br>

            <div class="mb-4">
  <label class="form-label">Imagen destacada</label>

  <!-- Preview imagen seleccionada -->
  <div id="featured-preview" class="mb-2 d-none">
    <img id="featured-preview-img" src="" alt=""
         style="max-width:100%; max-height:150px; border-radius:6px; border:1px solid #dee2e6;">
    <div class="mt-1">
      <button type="button" class="btn btn-xs btn-outline-danger" id="btn-remove-featured">
        <i class="bi bi-x-circle me-1"></i> Quitar imagen
      </button>
    </div>
  </div>

  <!-- Input oculto que guarda la ruta relativa -->
  <input type="hidden" name="image_path" id="image_path" value="">

  <!-- Input file para subir nueva (oculto, se activa desde el modal) -->
  <input type="file" id="media-upload-input" name="image" accept="image/*" class="d-none">

  <div class="d-flex gap-2">
    <button type="button" class="btn btn-outline-primary btn-sm" id="btn-open-media">
      <i class="bi bi-images me-1"></i> Seleccionar imagen
    </button>
  </div>

  <?php if(isset($errors['image'])): ?>
    <div class="text-danger small mt-1"><?= htmlspecialchars($errors['image']) ?></div>
  <?php endif; ?>
</div>

            <div class="divider"></div>
            <div class="d-grid gap-2">
              <button class="btn btn-success btn-lg" type="submit"><i class="fas fa-save"></i> Guardar Entrada</button>
              <a class="btn btn-secondary" href="<?= htmlspecialchars($url) ?>/admin/blog/index.php"><i class="fa-solid fa-arrow-left"></i> Volver al listado</a>
            </div>
          </div>
        </div>
      </form>
    </div>
  </div>
</div>

<?php require_once __DIR__ . '/../inc/menu-footer.php'; ?>
<script>
/* Slug auto */
const titleInput = document.getElementById('title');
const slugInput  = document.getElementById('slug');
function slugify(s){
  return s.toString()
    .normalize('NFD').replace(/[\u0300-\u036f]/g,'')
    .toLowerCase().replace(/[^a-z0-9]+/g,'-')
    .replace(/^-+|-+$/g,'').substring(0,180);
}
titleInput?.addEventListener('input', () => {
  if(!slugInput.value || slugInput.dataset.touched!=='1'){
    slugInput.value = slugify(titleInput.value);
  }
});
slugInput?.addEventListener('input', ()=> slugInput.dataset.touched='1');

/* Preview */
document.getElementById('image')?.addEventListener('change', function(){
  const cont = document.getElementById('imagePreview');
  cont.innerHTML = '';
  Array.from(this.files || []).forEach(file=>{
    const reader = new FileReader();
    reader.onload = e=>{
      const img = document.createElement('img');
      img.src = e.target.result;
      cont.appendChild(img);
    };
    reader.readAsDataURL(file);
  });
});
</script>
	
<script>
function updateCounter(inputId, counterId, min, max){
  const input = document.getElementById(inputId);
  const counter = document.getElementById(counterId);
  if(!input || !counter) return;

  if(input.value.length > max){
    input.value = input.value.substring(0, max);
  }

  const len = input.value.length;
  counter.textContent = len;

  if(len === 0){
    counter.className = "badge bg-danger";
  } else if(len < min){
    counter.className = "badge bg-warning";
  } else if(len > max){
    counter.className = "badge bg-danger";
  } else {
    counter.className = "badge bg-success";
  }
}

document.addEventListener("DOMContentLoaded", function(){
  updateCounter("seo_title", "seo_title_counter", 50, 70);
  updateCounter("seo_description", "seo_description_counter", 120, 160);
  updateCounter("seo_keywords", "seo_keywords_counter", 5, 250);

  ["seo_title","seo_description","seo_keywords"].forEach(id=>{
    const input = document.getElementById(id);
    input?.addEventListener("input", ()=>{
      if(id==="seo_title") updateCounter(id, id+"_counter", 50, 70);
      if(id==="seo_description") updateCounter(id, id+"_counter", 120, 160);
      if(id==="seo_keywords") updateCounter(id, id+"_counter", 5, 250);
    });
  });
});
</script>

	
<!-- Modal Biblioteca de Medios -->
<div class="modal fade" id="modalMediaLibrary" tabindex="-1" data-bs-backdrop="static">
  <div class="modal-dialog modal-xl modal-dialog-scrollable">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title"><i class="bi bi-images me-2"></i>Biblioteca de medios</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body p-0">

        <!-- Tabs: Biblioteca / Subir nueva -->
        <ul class="nav nav-tabs px-3 pt-2" id="mediaTabs">
          <li class="nav-item">
            <a class="nav-link active" data-bs-toggle="tab" href="#tab-library">
              <i class="bi bi-grid me-1"></i> Biblioteca
            </a>
          </li>
          <li class="nav-item">
            <a class="nav-link" data-bs-toggle="tab" href="#tab-upload">
              <i class="bi bi-cloud-upload me-1"></i> Subir nueva
            </a>
          </li>
        </ul>

        <div class="tab-content">

          <!-- Tab Biblioteca -->
          <div class="tab-pane fade show active p-3" id="tab-library">

            <!-- Filtros -->
            <div class="d-flex gap-2 mb-3 flex-wrap align-items-center">
              <input type="text" id="media-search" class="form-control form-control-sm"
                     style="max-width:200px" placeholder="Buscar...">
              <select id="media-filter-type" class="form-select form-select-sm" style="max-width:140px">
                <option value="image">Imágenes</option>
                <option value="">Todos</option>
              </select>
              <button type="button" class="btn btn-sm btn-outline-secondary" id="btn-media-search">
                <i class="bi bi-search"></i>
              </button>
              <span class="ms-auto text-muted small" id="media-total"></span>
            </div>

            <!-- Grid de imágenes -->
            <div id="media-library-grid" class="row g-2" style="min-height:200px">
              <div class="col-12 text-center py-5 text-muted" id="media-loading">
                <div class="spinner-border spinner-border-sm me-2"></div> Cargando...
              </div>
            </div>

            <!-- Paginación biblioteca -->
            <div id="media-pagination" class="d-flex justify-content-center mt-3 gap-2"></div>

          </div>

          <!-- Tab Subir nueva -->
          <div class="tab-pane fade p-3" id="tab-upload">
            <div class="upload-zone-modal border rounded p-4 text-center mb-3"
                 id="modal-upload-zone" style="cursor:pointer; border-style:dashed !important;">
              <i class="bi bi-cloud-arrow-up fs-1 text-muted"></i>
              <div class="mt-2 text-muted">Arrastra o haz clic para subir</div>
              <small class="text-muted">JPG, PNG, WebP, GIF — Máx 20MB</small>
            </div>
            <input type="file" id="modal-file-input" accept="image/*" class="d-none">

            <div id="modal-upload-preview" class="d-none mb-3 text-center">
              <img id="modal-preview-img" src="" class="img-thumbnail" style="max-height:120px">
              <div class="small text-muted mt-1" id="modal-preview-name"></div>
            </div>

            <div id="modal-upload-progress" class="d-none mb-3">
              <div class="progress">
                <div class="progress-bar progress-bar-striped progress-bar-animated w-100"></div>
              </div>
              <div class="text-muted small text-center mt-1">Subiendo...</div>
            </div>

            <button type="button" class="btn btn-primary w-100" id="btn-modal-upload" disabled>
              <i class="bi bi-upload me-1"></i> Subir e insertar
            </button>
          </div>

        </div>
      </div>
      <div class="modal-footer">
        <div class="me-auto small text-muted" id="selected-file-info"></div>
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
        <button type="button" class="btn btn-primary" id="btn-insert-media" disabled>
          <i class="bi bi-check-circle me-1"></i> Insertar imagen
        </button>
      </div>
    </div>
  </div>
</div>
	<script>
(function () {
  const BASE = '<?= URLBASE ?>';
  let mediaPage     = 1;
  let selectedMedia = null; // { path, url, name }
  let uploadedFile  = null;

  const modal       = document.getElementById('modalMediaLibrary');
  const grid        = document.getElementById('media-library-grid');
  const pagination  = document.getElementById('media-pagination');
  const btnInsert   = document.getElementById('btn-insert-media');
  const btnOpen     = document.getElementById('btn-open-media');
  const preview     = document.getElementById('featured-preview');
  const previewImg  = document.getElementById('featured-preview-img');
  const imagePath   = document.getElementById('image_path');
  const fileInput   = document.getElementById('media-upload-input');

  /* — Abrir modal — */
  btnOpen?.addEventListener('click', () => {
    selectedMedia = null;
    btnInsert.disabled = true;
    document.getElementById('selected-file-info').textContent = '';
    loadLibrary(1);
    new bootstrap.Modal(modal).show();
  });

  /* — Quitar imagen — */
  document.getElementById('btn-remove-featured')?.addEventListener('click', () => {
    imagePath.value     = '';
    previewImg.src      = '';
    fileInput.value     = '';
    preview.classList.add('d-none');
  });

  /* — Cargar biblioteca vía AJAX — */
  function loadLibrary(page) {
    const q    = document.getElementById('media-search').value.trim();
    const type = document.getElementById('media-filter-type').value;
    mediaPage  = page;

    grid.innerHTML = '<div class="col-12 text-center py-5 text-muted"><div class="spinner-border spinner-border-sm me-2"></div> Cargando...</div>';
    pagination.innerHTML = '';

    fetch(`${BASE}/admin/multimedia/media_picker.php?p=${page}&q=${encodeURIComponent(q)}&type=${encodeURIComponent(type)}`)
      .then(r => r.json())
      .then(data => {
        grid.innerHTML = '';
        document.getElementById('media-total').textContent = data.total + ' archivos';

        if (!data.files.length) {
          grid.innerHTML = '<div class="col-12 text-center py-4 text-muted">No hay imágenes</div>';
          return;
        }

        data.files.forEach(f => {
          const col = document.createElement('div');
          col.className = 'col-6 col-sm-4 col-md-3 col-lg-2';
          col.innerHTML = `
            <div class="media-pick-card border rounded overflow-hidden"
                 style="cursor:pointer; transition:.2s;"
                 data-path="${f.path}" data-url="${f.url}" data-name="${f.name}">
              <div style="height:80px; overflow:hidden; background:#f8f9fa; display:flex; align-items:center; justify-content:center;">
                <img src="${f.url}" alt="${f.name}"
                     style="width:100%; height:80px; object-fit:cover;" loading="lazy">
              </div>
              <div class="p-1" style="font-size:10px; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;">
                ${f.name}
              </div>
            </div>`;

          col.querySelector('.media-pick-card').addEventListener('click', function () {
            // Deseleccionar anterior
            document.querySelectorAll('.media-pick-card.selected').forEach(el => {
              el.classList.remove('selected');
              el.style.outline = '';
            });
            // Seleccionar este
            this.classList.add('selected');
            this.style.outline = '3px solid #0d6efd';
            selectedMedia = { path: this.dataset.path, url: this.dataset.url, name: this.dataset.name };
            btnInsert.disabled = false;
            document.getElementById('selected-file-info').textContent = this.dataset.name;
          });

          grid.appendChild(col);
        });

        // Paginación
        renderPagination(data.page, data.total_pages);
      })
      .catch(() => {
        grid.innerHTML = '<div class="col-12 text-center py-4 text-danger">Error al cargar</div>';
      });
  }

  function renderPagination(current, total) {
    if (total <= 1) return;
    const qs = `&q=${encodeURIComponent(document.getElementById('media-search').value)}&type=${encodeURIComponent(document.getElementById('media-filter-type').value)}`;
    let html = '';
    if (current > 1)  html += `<button class="btn btn-sm btn-outline-secondary media-pg" data-p="${current-1}">‹</button>`;
    html += `<span class="btn btn-sm btn-primary disabled">${current} / ${total}</span>`;
    if (current < total) html += `<button class="btn btn-sm btn-outline-secondary media-pg" data-p="${current+1}">›</button>`;
    pagination.innerHTML = html;
    pagination.querySelectorAll('.media-pg').forEach(b => {
      b.addEventListener('click', () => loadLibrary(parseInt(b.dataset.p)));
    });
  }

  /* — Buscar — */
  document.getElementById('btn-media-search')?.addEventListener('click', () => loadLibrary(1));
  document.getElementById('media-search')?.addEventListener('keydown', e => {
    if (e.key === 'Enter') { e.preventDefault(); loadLibrary(1); }
  });
  document.getElementById('media-filter-type')?.addEventListener('change', () => loadLibrary(1));

  /* — Insertar imagen seleccionada de biblioteca — */
  btnInsert?.addEventListener('click', () => {
    if (!selectedMedia) return;
    imagePath.value    = selectedMedia.path;
    previewImg.src     = selectedMedia.url;
    fileInput.value    = ''; // limpiar file input
    preview.classList.remove('d-none');
    bootstrap.Modal.getInstance(modal).hide();
  });

  /* — Upload nueva imagen desde modal — */
  const modalUploadZone  = document.getElementById('modal-upload-zone');
  const modalFileInput   = document.getElementById('modal-file-input');
  const modalPreview     = document.getElementById('modal-upload-preview');
  const modalPreviewImg  = document.getElementById('modal-preview-img');
  const modalPreviewName = document.getElementById('modal-preview-name');
  const btnModalUpload   = document.getElementById('btn-modal-upload');
  const uploadProgress   = document.getElementById('modal-upload-progress');

  modalUploadZone?.addEventListener('click', () => modalFileInput.click());
  ['dragenter','dragover'].forEach(e => modalUploadZone?.addEventListener(e, ev => { ev.preventDefault(); modalUploadZone.style.background = '#f0f4ff'; }));
  ['dragleave','drop'].forEach(e => modalUploadZone?.addEventListener(e, ev => { ev.preventDefault(); modalUploadZone.style.background = ''; }));
  modalUploadZone?.addEventListener('drop', ev => { modalFileInput.files = ev.dataTransfer.files; handleModalFile(); });
  modalFileInput?.addEventListener('change', handleModalFile);

  function handleModalFile() {
    if (!modalFileInput.files.length) return;
    uploadedFile = modalFileInput.files[0];
    modalPreviewImg.src      = URL.createObjectURL(uploadedFile);
    modalPreviewName.textContent = uploadedFile.name + ' (' + (uploadedFile.size/1024).toFixed(0) + 'KB)';
    modalPreview.classList.remove('d-none');
    btnModalUpload.disabled = false;
  }

  btnModalUpload?.addEventListener('click', () => {
    if (!uploadedFile) return;

    const formData = new FormData();
    formData.append('file', uploadedFile);

    btnModalUpload.disabled = true;
    uploadProgress.classList.remove('d-none');

    fetch(`${BASE}/admin/multimedia/upload_ajax.php`, {
      method: 'POST',
      body  : formData,
    })
    .then(r => r.json())
    .then(d => {
      uploadProgress.classList.add('d-none');
      btnModalUpload.disabled = false;

      if (d.success) {
        // Insertar directamente y cerrar modal
        imagePath.value = d.path;
        previewImg.src  = d.url;
        fileInput.value = '';
        preview.classList.remove('d-none');
        bootstrap.Modal.getInstance(modal).hide();
      } else {
        alert('Error: ' + d.message);
      }
    })
    .catch(() => {
      uploadProgress.classList.add('d-none');
      btnModalUpload.disabled = false;
      alert('Error al subir el archivo.');
    });
  });

})();
</script>
<?php require_once __DIR__ . '/../inc/summernote.php'; ?>
<?php require_once __DIR__ . '/../inc/flash_simple.php'; ?>
	<!-- Summernote JS -->


</body>
</html>
