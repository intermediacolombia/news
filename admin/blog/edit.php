<?php
require_once __DIR__ . '/../../inc/config.php';

require_once __DIR__ . '/../login/session.php';  // Inicia la sesión y carga la información del usuario
$permisopage = 'Editar Entrada';
require_once __DIR__ . '/../login/restriction.php';
require_once __DIR__ . '/../inc/flash_helpers.php';
require_once __DIR__ . '/blog_controller.php'; // donde cargamos categorías de blog

/* ========= Forzar UTF-8 en la salida ========= */
if (!headers_sent()) {
    header('Content-Type: text/html; charset=UTF-8');
}
ini_set('default_charset', 'UTF-8');
if (function_exists('mb_internal_encoding')) {
  mb_internal_encoding('UTF-8');
}

/* ========= Forzar UTF-8 en PDO ========= */
db()->exec("SET NAMES utf8mb4");
db()->exec("SET CHARACTER SET utf8mb4");
db()->exec("SET SESSION collation_connection = utf8mb4_unicode_ci");

/* ========= ID ========= */
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($id <= 0) {
  die("ID inválido.");
}

/* ========= Traer post ========= */
$stmt = db()->prepare("SELECT * FROM blog_posts WHERE id=? AND deleted=0 LIMIT 1");
$stmt->execute([$id]);
$post = $stmt->fetch();
if (!$post) {
  die("Entrada no encontrada.");
}

/* ========= Categorías del post (normalizadas a int) ========= */
$stc = db()->prepare("SELECT category_id FROM blog_post_category WHERE post_id=?");
$stc->execute([$id]);
$postCats = array_map('intval', $stc->fetchAll(PDO::FETCH_COLUMN));

/* ========= Errores / old ========= */
$errors = $_SESSION['errors'] ?? [];
$old    = $_SESSION['old']    ?? [];
unset($_SESSION['errors'], $_SESSION['old']);

/* ========= Set seleccionado final (respeta old si hubo POST fallido) ========= */
$selectedCats = isset($old['categories'])
  ? array_map('intval', (array)$old['categories'])
  : $postCats;

/* ========= Helpers de valores (safe para título/slug, raw para lo demás) ========= */
function oldv_safe($key, $default = ''){
  global $post, $old;
  return htmlspecialchars($old[$key] ?? ($post[$key] ?? $default), ENT_QUOTES, 'UTF-8');
}
function oldv_raw($key, $default = ''){
  global $post, $old;
  return $old[$key] ?? ($post[$key] ?? $default);
}
?>
<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <title>Editar entrada de blog</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">  
  <?php require_once __DIR__ . '/../inc/header.php'; ?>
</head>
<body>
<div class="container" style="padding: 0px; background:rgba(0,0,0,0.00)">
  <div class="portada">
    <h1 class="mb-4"><i class="bi bi-layout-text-window-reverse"></i> Editar Entrada</h1>
  </div>
</div>
<?php require_once __DIR__ . '/../inc/menu.php'; ?>

<div class="wrap">
  <div class="card">
    <div class="card-header d-flex align-items-center justify-content-between">
      <h5 class="mb-0">Editar entrada</h5>
      <span class="badge bg-info">Blog</span>
    </div>

    <div class="card-body">

      <?php if(isset($errors['__global'])): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($errors['__global']) ?></div>
      <?php endif; ?>

      <form method="post" enctype="multipart/form-data" action="update.php">
        <input type="hidden" name="id" value="<?= (int)$post['id'] ?>">

        <div class="row g-4">
          <div class="col-lg-8">
            <div class="mb-3">
              <label class="form-label">Título *</label>
              <input type="text" class="form-control<?= isset($errors['title'])?' is-invalid':'' ?>" 
                     name="title" id="title" required 
                     value="<?= oldv_safe('title') ?>">
              <?php if(isset($errors['title'])): ?><div class="invalid-feedback"><?= htmlspecialchars($errors['title']) ?></div><?php endif; ?>
            </div>

            <div class="mb-3">
              <label class="form-label">Slug</label>
              <input type="text" class="form-control<?= isset($errors['slug'])?' is-invalid':'' ?>" 
                     name="slug" id="slug" 
                     value="<?= oldv_safe('slug') ?>">
              <?php if(isset($errors['slug'])): ?><div class="invalid-feedback"><?= htmlspecialchars($errors['slug']) ?></div><?php endif; ?>
            </div>

            <!-- Categorías -->
            <div class="mb-3">
              <label class="form-label">Categorías</label>
              <select name="categories[]" class="form-select" multiple>
                <?php foreach(($cats ?? []) as $c): ?>
                  <option value="<?= (int)$c['id'] ?>" <?= in_array((int)$c['id'], $postCats, true) ? 'selected' : '' ?>>
                    <?= htmlspecialchars($c['name']) ?>
                  </option>
                <?php endforeach; ?>
              </select>
				<div class="hint mt-1">Mantén CTRL/⌘ para seleccionar varias</div>
            </div>

            <div class="mb-3">
              <label class="form-label">Contenido *</label>
              <textarea name="content"  class="form-control<?= isset($errors['content'])?' is-invalid':'' ?> summernote" rows="10"><?= oldv_raw('content') ?></textarea>
              <?php if(isset($errors['content'])): ?><div class="invalid-feedback"><?= htmlspecialchars($errors['content']) ?></div><?php endif; ?>
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
                     value="<?= oldv_raw('seo_title') ?>">
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
                        placeholder="Meta descripción para buscadores"><?= oldv_raw('seo_description') ?></textarea>
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
                     value="<?= oldv_raw('seo_keywords') ?>">
              <div class="hint mt-1">
                Opcional. Separa por comas.
                <span id="seo_keywords_counter" class="badge bg-secondary">0</span>
              </div>
              <?php if(isset($errors['seo_keywords'])): ?><div class="invalid-feedback"><?= htmlspecialchars($errors['seo_keywords']) ?></div><?php endif; ?>
            </div>
            <!-- FIN SEO -->

          </div>

          <div class="col-lg-4">
            <div class="mb-3">
              <label class="form-label">Estado</label>
              <select class="form-select<?= isset($errors['status'])?' is-invalid':'' ?>" name="status">
                <option value="draft"     <?= ($post['status']==='draft')?'selected':''; ?>>Borrador</option>
                <option value="published" <?= ($post['status']==='published')?'selected':''; ?>>Publicado</option>
              </select>
            </div>

            <div class="mb-4">
  <label class="form-label">Imagen destacada</label>

  <!-- Preview imagen actual/seleccionada -->
  <div id="featured-preview" class="mb-2 <?= empty($post['image']) ? 'd-none' : '' ?>">
    <img id="featured-preview-img"
         src="<?= !empty($post['image']) ? htmlspecialchars(URLBASE . '/' . $post['image']) : '' ?>"
         alt="" style="max-width:100%; max-height:150px; border-radius:6px; border:1px solid #dee2e6;">
    <div class="mt-1">
      <button type="button" class="btn btn-xs btn-outline-danger" id="btn-remove-featured">
        <i class="bi bi-x-circle me-1"></i> Quitar imagen
      </button>
    </div>
  </div>

  <!-- Guarda la ruta relativa — precargada con la imagen actual -->
  <input type="hidden" name="image_path" id="image_path"
         value="<?= htmlspecialchars($post['image'] ?? '') ?>">

  <!-- File para subir nueva -->
  <input type="file" id="media-upload-input" name="image" accept="image/*" class="d-none">

  <div class="mb-2">
    <label class="form-label small">Texto alternativo (alt)</label>
    <input type="text" name="image_alt" id="image_alt" class="form-control form-control-sm"
           placeholder="Descripción de la imagen para accesibilidad"
           value="<?= htmlspecialchars($post['image_alt'] ?? '') ?>">
  </div>

  <div class="d-flex gap-2">
    <button type="button" class="btn btn-outline-primary btn-sm" id="btn-open-media">
      <i class="bi bi-images me-1"></i> <?= empty($post['image']) ? 'Seleccionar imagen' : 'Cambiar imagen' ?>
    </button>
  </div>
</div>

            <div class="divider"></div>
            <div class="d-grid gap-2">
              <button class="btn btn-success btn-lg" type="submit"><i class="fas fa-save"></i> Guardar Entrada</button>
              <a class="btn btn-secondary" href="<?= htmlspecialchars($url) ?>/admin/blog/index.php"><i class="fa fa-arrow-left"></i> Volver al listado</a>
            </div>
          </div>
        </div>
      </form>
    </div>
  </div>
</div>
<?php require_once __DIR__ . '/../inc/menu-footer.php'; ?>

<script>
/* Slug auto si está vacío */
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
    counter.className = "badge bg-danger"; // vacío
  } else if(len < min){
    counter.className = "badge bg-warning"; // demasiado corto
  } else if(len > max){
    counter.className = "badge bg-danger"; // demasiado largo
  } else {
    counter.className = "badge bg-success"; // perfecto
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
            <div class="row g-2 mb-3 align-items-end">
              <div class="col-sm-5">
                <label class="form-label small fw-semibold mb-1">Buscar</label>
                <input type="text" id="media-search" class="form-control form-control-sm"
                       placeholder="Nombre del archivo...">
              </div>
              <div class="col-sm-3">
                <label class="form-label small fw-semibold mb-1">Tipo</label>
                <select id="media-filter-type" class="form-select form-select-sm">
                  <option value="image">Imágenes</option>
                  <option value="">Todos</option>
                </select>
              </div>
              <div class="col-sm-2">
                <button type="button" class="btn btn-sm btn-primary w-100" id="btn-media-search">
                  <i class="bi bi-search me-1"></i> Buscar
                </button>
              </div>
              <div class="col-sm-2 text-end">
                <span class="text-muted small" id="media-total"></span>
              </div>
            </div>

            <div class="row g-2">
              <!-- Grid imágenes -->
              <div class="col-lg-8">
                <div id="media-library-grid" class="row g-2" style="min-height:300px">
                  <div class="col-12 text-center py-5 text-muted" id="media-loading">
                    <div class="spinner-border spinner-border-sm me-2"></div> Cargando...
                  </div>
                </div>
                <div id="media-pagination" class="d-flex justify-content-center mt-3 gap-1 flex-wrap"></div>
              </div>

              <!-- Panel detalle imagen seleccionada -->
              <div class="col-lg-4">
                <div id="media-detail-panel"
                     class="border rounded p-3 h-100"
                     style="background:#f8f9fa; min-height:300px;">
                  <div id="media-detail-empty" class="text-center text-muted py-5">
                    <i class="bi bi-hand-index-thumb fs-2"></i>
                    <div class="mt-2 small">Selecciona una imagen<br>para ver sus detalles</div>
                  </div>
                  <div id="media-detail-content" class="d-none">
                    <img id="detail-preview-img" src="" alt=""
                         class="img-fluid rounded mb-3 w-100"
                         style="max-height:140px; object-fit:cover;">
                    <div class="small text-muted mb-2" id="detail-file-info"></div>
                    <div class="mb-2">
                      <label class="form-label small fw-semibold mb-1">Texto alternativo (alt)</label>
                      <input type="text" id="detail-alt" class="form-control form-control-sm"
                             placeholder="Descripción de la imagen">
                    </div>
                    <div class="mb-2">
                      <label class="form-label small fw-semibold mb-1">Caption</label>
                      <textarea id="detail-caption" class="form-control form-control-sm"
                                rows="2" placeholder="Pie de foto opcional"></textarea>
                    </div>
                    <div class="small text-break text-muted mt-2" id="detail-path"></div>
                  </div>
                </div>
              </div>
            </div>

          </div>

          <!-- Tab Subir nueva -->
          <div class="tab-pane fade p-3" id="tab-upload">
            <div class="border rounded p-4 text-center mb-3"
                 id="modal-upload-zone"
                 style="cursor:pointer; border-style:dashed !important; border-color:#dee2e6; transition:.2s;">
              <i class="bi bi-cloud-arrow-up fs-1 text-muted"></i>
              <div class="mt-2 text-muted">Arrastra o haz clic para subir</div>
              <small class="text-muted">JPG, PNG, WebP, GIF — Máx 20MB</small>
            </div>
            <input type="file" id="modal-file-input" accept="image/*" class="d-none">

            <div id="modal-upload-preview" class="d-none mb-3 text-center">
              <img id="modal-preview-img" src="" class="img-thumbnail" style="max-height:120px">
              <div class="small text-muted mt-1" id="modal-preview-name"></div>
            </div>

            <!-- Alt y Caption al subir -->
            <div id="modal-upload-meta" class="d-none">
              <div class="row g-2 mb-3">
                <div class="col-sm-6">
                  <label class="form-label small fw-semibold mb-1">Texto alternativo (alt)</label>
                  <input type="text" id="upload-alt" class="form-control form-control-sm"
                         placeholder="Descripción de la imagen">
                </div>
                <div class="col-sm-6">
                  <label class="form-label small fw-semibold mb-1">Caption</label>
                  <input type="text" id="upload-caption" class="form-control form-control-sm"
                         placeholder="Pie de foto">
                </div>
              </div>
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
  let selectedMedia = null; // { path, url, name, alt, caption }
  let uploadedFile  = null;

  const modal      = document.getElementById('modalMediaLibrary');
  const grid       = document.getElementById('media-library-grid');
  const pagination = document.getElementById('media-pagination');
  const btnInsert  = document.getElementById('btn-insert-media');
  const btnOpen    = document.getElementById('btn-open-media');
  const preview    = document.getElementById('featured-preview');
  const previewImg = document.getElementById('featured-preview-img');
  const imagePath  = document.getElementById('image_path');
  const fileInput  = document.getElementById('media-upload-input');

  // Campos ocultos para alt y caption de la imagen destacada
  // Los creamos dinámicamente si no existen
  let altInput     = document.getElementById('featured_alt');
  let captionInput = document.getElementById('featured_caption');
  if (!altInput) {
    altInput = document.createElement('input');
    altInput.type = 'hidden';
    altInput.name = 'featured_alt';
    altInput.id   = 'featured_alt';
    imagePath.parentNode.appendChild(altInput);
  }
  if (!captionInput) {
    captionInput = document.createElement('input');
    captionInput.type = 'hidden';
    captionInput.name = 'featured_caption';
    captionInput.id   = 'featured_caption';
    imagePath.parentNode.appendChild(captionInput);
  }

  /* — Abrir modal — */
  btnOpen?.addEventListener('click', () => {
    selectedMedia = null;
    btnInsert.disabled = true;
    document.getElementById('selected-file-info').textContent = '';
    document.getElementById('media-detail-empty').classList.remove('d-none');
    document.getElementById('media-detail-content').classList.add('d-none');
    loadLibrary(1);
    new bootstrap.Modal(modal).show();
  });

  /* — Quitar imagen — */
  document.getElementById('btn-remove-featured')?.addEventListener('click', () => {
    imagePath.value     = '';
    altInput.value      = '';
    captionInput.value  = '';
    previewImg.src      = '';
    fileInput.value     = '';
    preview.classList.add('d-none');
  });

  /* — Cargar biblioteca — */
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
          grid.innerHTML = '<div class="col-12 text-center py-4 text-muted"><i class="bi bi-images fs-2"></i><div class="mt-2">No hay imágenes</div></div>';
          return;
        }

        data.files.forEach(f => {
          const col = document.createElement('div');
          col.className = 'col-6 col-sm-4 col-md-3';
          col.innerHTML = `
            <div class="media-pick-card border rounded overflow-hidden"
                 style="cursor:pointer; transition:.15s;"
                 data-path="${f.path}"
                 data-url="${f.url}"
                 data-name="${f.name}"
                 data-alt="${f.alt || ''}"
                 data-caption="${f.caption || ''}"
                 data-size="${f.size || ''}"
                 data-dims="${f.dims || ''}">
              <div style="height:90px; overflow:hidden; background:#f0f0f0; display:flex; align-items:center; justify-content:center;">
                <img src="${f.url}" alt="${f.name}"
                     style="width:100%; height:90px; object-fit:cover;" loading="lazy"
                     onerror="this.parentNode.innerHTML='<i class=\'bi bi-image text-muted\' style=\'font-size:32px\'></i>'">
              </div>
              <div class="p-1 bg-white" style="font-size:10px; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;">
                ${f.name}
              </div>
            </div>`;

          col.querySelector('.media-pick-card').addEventListener('click', function () {
            // Deseleccionar anterior
            document.querySelectorAll('.media-pick-card').forEach(el => {
              el.style.outline = '';
              el.style.boxShadow = '';
            });
            // Seleccionar este
            this.style.outline   = '3px solid #0d6efd';
            this.style.boxShadow = '0 0 0 1px #0d6efd';

            selectedMedia = {
              path   : this.dataset.path,
              url    : this.dataset.url,
              name   : this.dataset.name,
              alt    : this.dataset.alt,
              caption: this.dataset.caption,
            };

            // Mostrar panel detalle
            document.getElementById('media-detail-empty').classList.add('d-none');
            document.getElementById('media-detail-content').classList.remove('d-none');
            document.getElementById('detail-preview-img').src = selectedMedia.url;
            document.getElementById('detail-alt').value       = selectedMedia.alt;
            document.getElementById('detail-caption').value   = selectedMedia.caption;
            document.getElementById('detail-file-info').textContent =
              (this.dataset.size ? this.dataset.size + ' · ' : '') + (this.dataset.dims || '');
            document.getElementById('detail-path').textContent = selectedMedia.path;

            btnInsert.disabled = false;
            document.getElementById('selected-file-info').textContent = selectedMedia.name;
          });

          grid.appendChild(col);
        });

        renderPagination(data.page, data.total_pages);
      })
      .catch(() => {
        grid.innerHTML = '<div class="col-12 text-center py-4 text-danger">Error al cargar la biblioteca</div>';
      });
  }

  function renderPagination(current, total) {
    if (total <= 1) return;
    let html = '';
    if (current > 1)    html += `<button class="btn btn-sm btn-outline-secondary media-pg" data-p="${current-1}">‹ Anterior</button>`;
    html += `<span class="btn btn-sm btn-light disabled px-3">${current} / ${total}</span>`;
    if (current < total) html += `<button class="btn btn-sm btn-outline-secondary media-pg" data-p="${current+1}">Siguiente ›</button>`;
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

  /* — Insertar imagen seleccionada — */
  btnInsert?.addEventListener('click', () => {
    if (!selectedMedia) return;

    // Leer alt y caption actualizados del panel detalle
    const finalAlt     = document.getElementById('detail-alt').value;
    const finalCaption = document.getElementById('detail-caption').value;

    // Si se editaron, guardar en BD
    if (finalAlt !== selectedMedia.alt || finalCaption !== selectedMedia.caption) {
      fetch(`${BASE}/admin/multimedia/update_media.php`, {
        method  : 'POST',
        headers : { 'Content-Type': 'application/json' },
        body    : JSON.stringify({
          id      : null, // no tenemos el id aquí
          path    : selectedMedia.path,
          alt_text: finalAlt,
          caption : finalCaption,
        }),
      }).catch(() => {});
    }

    imagePath.value    = selectedMedia.path;
    altInput.value     = finalAlt;
    captionInput.value = finalCaption;
    previewImg.src     = selectedMedia.url;
    previewImg.alt     = finalAlt;
    fileInput.value    = '';
    preview.classList.remove('d-none');
    bootstrap.Modal.getInstance(modal).hide();
  });

  /* — Upload nueva imagen — */
  const modalUploadZone  = document.getElementById('modal-upload-zone');
  const modalFileInput   = document.getElementById('modal-file-input');
  const modalPreview     = document.getElementById('modal-upload-preview');
  const modalPreviewImg  = document.getElementById('modal-preview-img');
  const modalPreviewName = document.getElementById('modal-preview-name');
  const btnModalUpload   = document.getElementById('btn-modal-upload');
  const uploadProgress   = document.getElementById('modal-upload-progress');
  const uploadMeta       = document.getElementById('modal-upload-meta');

  modalUploadZone?.addEventListener('click', () => modalFileInput.click());
  ['dragenter','dragover'].forEach(e => modalUploadZone?.addEventListener(e, ev => {
    ev.preventDefault(); modalUploadZone.style.background = '#f0f4ff'; modalUploadZone.style.borderColor = '#0d6efd';
  }));
  ['dragleave','drop'].forEach(e => modalUploadZone?.addEventListener(e, ev => {
    ev.preventDefault(); modalUploadZone.style.background = ''; modalUploadZone.style.borderColor = '#dee2e6';
  }));
  modalUploadZone?.addEventListener('drop', ev => { modalFileInput.files = ev.dataTransfer.files; handleModalFile(); });
  modalFileInput?.addEventListener('change', handleModalFile);

  function handleModalFile() {
    if (!modalFileInput.files.length) return;
    uploadedFile = modalFileInput.files[0];
    modalPreviewImg.src      = URL.createObjectURL(uploadedFile);
    modalPreviewName.textContent = uploadedFile.name + ' (' + (uploadedFile.size/1024).toFixed(0) + 'KB)';
    modalPreview.classList.remove('d-none');
    uploadMeta.classList.remove('d-none');
    btnModalUpload.disabled = false;
  }

  btnModalUpload?.addEventListener('click', () => {
    if (!uploadedFile) return;

    const formData = new FormData();
    formData.append('file',    uploadedFile);
    formData.append('alt',     document.getElementById('upload-alt').value);
    formData.append('caption', document.getElementById('upload-caption').value);

    btnModalUpload.disabled = true;
    uploadProgress.classList.remove('d-none');

    fetch(`${BASE}/admin/multimedia/upload_ajax.php`, { method:'POST', body: formData })
      .then(r => r.json())
      .then(d => {
        uploadProgress.classList.add('d-none');
        btnModalUpload.disabled = false;
        if (d.success) {
          imagePath.value    = d.path;
          altInput.value     = d.alt     || '';
          captionInput.value = d.caption || '';
          previewImg.src     = d.url;
          previewImg.alt     = d.alt || '';
          fileInput.value    = '';
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
</body>
</html>


