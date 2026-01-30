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
              <div class="img-drop">
                <input class="form-control<?= isset($errors['image'])?' is-invalid':'' ?>" type="file" name="image" id="image" accept="image/*">
                <div class="hint mt-2">JPG/PNG/WebP, máx 5 MB.</div>
                <?php if(isset($errors['image'])): ?><div class="invalid-feedback d-block"><?= htmlspecialchars($errors['image']) ?></div><?php endif; ?>
              </div>
              <div id="imagePreview" class="preview-grid mt-2"></div>
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

	

	
<?php require_once __DIR__ . '/../inc/summernote.php'; ?>
<?php require_once __DIR__ . '/../inc/flash_simple.php'; ?>
	<!-- Summernote JS -->


</body>
</html>
