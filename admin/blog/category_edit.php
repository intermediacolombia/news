<?php
require_once __DIR__ . '/../login/session.php';
require_once __DIR__ . '/../../inc/config.php';

$id = (int)($_GET['id'] ?? 0);
$stmt = $pdo->prepare("SELECT * FROM blog_categories WHERE id=?");
$stmt->execute([$id]);
$cat = $stmt->fetch();
if (!$cat) { die("Categoría no encontrada"); }
?>

<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <title>Editar Categoría</title>
  <?php require_once __DIR__ . '/../inc/header.php'; ?>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container" style="padding: 0px; background:rgba(0,0,0,0.00)">
  <div class="portada">
    <h1 class="mb-4"><i class="bi bi-layout-text-window-reverse"></i> Editar Categoría</h1>
  </div>
</div>
<?php require_once __DIR__ . '/../inc/menu.php'; ?>

<div class="container" style="max-width:800px;">
  <div class="card">
    <div class="card-header"><h5 class="mb-0">Editar Categoría</h5></div>
    <div class="card-body">
      <form method="post" action="category_update.php">
        <input type="hidden" name="id" value="<?= $cat['id'] ?>">
        <div class="mb-3">
          <label class="form-label">Nombre *</label>
          <input type="text" name="name" id="name" class="form-control" required value="<?= htmlspecialchars($cat['name']) ?>">
        </div>
        <div class="mb-3">
          <label class="form-label">Slug</label>
          <input type="text" name="slug" id="slug" class="form-control" value="<?= htmlspecialchars($cat['slug']) ?>">
          <small class="text-muted">Se genera automáticamente, pero puedes modificarlo.</small>
        </div>
        <div class="mb-3">
          <label class="form-label">Descripción</label>
          <textarea name="description" class="form-control" rows="4"><?= htmlspecialchars($cat['description']) ?></textarea>
        </div>
        <div class="mb-3">
          <label class="form-label">Estado</label>
          <select name="status" class="form-select">
            <option value="active" <?= $cat['status']=='active'?'selected':'' ?>>Activo</option>
            <option value="inactive" <?= $cat['status']=='inactive'?'selected':'' ?>>Inactivo</option>
          </select>
        </div>
        <button type="submit" class="btn btn-brand">Actualizar</button>
        <a href="<?= $url ?>/admin/blog/categories.php" class="btn btn-secondary">Cancelar</a>
      </form>
    </div>
  </div>
</div>

<?php require_once __DIR__ . '/../inc/menu-footer.php'; ?>
</body>
</html>

<script>
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
  this.dataset.userEdited = true;
});
</script>

