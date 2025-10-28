<?php 

require_once __DIR__ . '/../login/session.php';  // Inicia la sesión y carga la información del usuario
$permisopage = 'Crear Categorias';
require_once __DIR__ . '/../login/restriction.php';
session_start();

 require_once __DIR__ . '/blog_category_controller.php'; 

?>

<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <title>Nueva Categoría de Blog</title>
  <?php require_once __DIR__ . '/../inc/header.php'; ?>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body>
	<div class="container" style="padding: 0px; background:rgba(0,0,0,0.00)">
  <div class="portada">
    <h1 class="mb-4"><i class="bi bi-layout-text-window-reverse"></i> Nueva Categoría de Blog</h1>
  </div>
</div>
<?php require_once __DIR__ . '/../inc/menu.php'; ?>

<div class="container" style="max-width:800px;">
  <div class="card">
    <div class="card-header"><h5 class="mb-0">Nueva Categoría de Blog</h5></div>
    <div class="card-body">
      <form method="post">
        <div class="mb-3">
          <label class="form-label">Nombre *</label>
          <input type="text" name="name" id="name" class="form-control" required>
        </div>
        <div class="mb-3">
          <label class="form-label">Slug</label>
          <input type="text" name="slug" id="slug" class="form-control">
          <small class="text-muted">Se genera automáticamente, pero puedes modificarlo.</small>
        </div>
        <div class="mb-3">
          <label class="form-label">Descripción</label>
          <textarea name="description" class="form-control" rows="4"></textarea>
        </div>
        <div class="mb-3">
          <label class="form-label">Estado</label>
          <select name="status" class="form-select">
            <option value="active">Activo</option>
            <option value="inactive">Inactivo</option>
          </select>
        </div>
        <button type="submit" class="btn btn-brand">Guardar</button>
        <a href="<?= $url ?>/admin/blog/categories.php" class="btn btn-secondary">Cancelar</a>
      </form>
    </div>
  </div>
</div>

<?php require_once __DIR__ . '/../inc/menu-footer.php'; ?>
<?php require_once __DIR__ . '/../inc/flash_simple.php'; ?>

<script>
// Generar slug automáticamente
function slugify(text) {
  return text.toString().normalize("NFD").replace(/[\u0300-\u036f]/g, "")
    .toLowerCase().trim().replace(/[^a-z0-9]+/g, '-').replace(/^-+|-+$/g, '');
}

document.getElementById('name').addEventListener('input', function() {
  const slugField = document.getElementById('slug');
  if (!slugField.dataset.userEdited) {
    slugField.value = slugify(this.value);
  }
});

document.getElementById('slug').addEventListener('input', function() {
  this.dataset.userEdited = true; // Si el usuario lo cambia, ya no se autogenera
});
</script>
</body>
</html>

