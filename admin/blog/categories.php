<?php
require_once __DIR__ . '/../../inc/config.php';

require_once __DIR__ . '/../login/session.php';  // Inicia la sesión y carga la información del usuario
$permisopage = 'Ver Categorias';
require_once __DIR__ . '/../login/restriction.php';
session_start();

require_once __DIR__ . '/../inc/flash_helpers.php';



$st = db()->query("SELECT * FROM blog_categories WHERE deleted=0 ORDER BY created_at DESC");
$categories = $st->fetchAll();
?>
<!doctype html>
<html lang="es">
<head>
<meta charset="utf-8">
<title>Categorías del Blog</title>
<?php require_once __DIR__ . '/../inc/header.php'; ?>

<!-- (Opcional) SweetAlert2 si no está ya en header.php -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<!-- DataTables Responsive (Bootstrap 5) CSS -->
<link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.5.0/css/responsive.bootstrap5.min.css">

<style>
  body { background-color: #f8f9fa; }
  .btn-trash {
    display:inline-flex; align-items:center; justify-content:center;
    width:32px; height:32px; border:1px solid #dc3545; border-radius:6px;
    background:#fff; color:#dc3545;
  }
  .btn-trash:hover { background:#fff3f3; }
</style>
</head>

<body>
<div class="container" style="padding: 0px; background:rgba(0,0,0,0.00)">
  <div class="portada">
    <h1><i class="bi bi-journal-text"></i> Categorias</h1>

    <!-- BS5: data-bs-* -->
    <a class="btn btn-success float-end" href="<?= $url ?>/admin/blog/category_create.php">
      <i class="fa-solid fa-plus"></i> Nueva Categoria
    </a>
  </div>
</div>
<?php require_once __DIR__ . '/../inc/menu.php'; ?>

<div class="container-fluid">
  

  <div class="card shadow-sm">
    <div class="card-body">
      <div class="table-responsive">
        <table id="categoriesTable" class="table table-striped table-hover align-middle nowrap" style="width:100%">
          <thead class="table-light">
            <tr>
              <th>Nombre</th>
              <th>Descripción</th>
              <th>Estado</th>
              <th>Creado</th>
              <th class="text-end">Acciones</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach($categories as $c): ?>
              <tr>
                <td><strong><?= htmlspecialchars($c['name']) ?></strong></td>
                <!-- Descripción truncada (Bootstrap nativo) -->
                <td class="text-truncate" style="max-width:380px;" title="<?= htmlspecialchars($c['description'] ?? '') ?>">
                  <?= htmlspecialchars($c['description'] ?? '—') ?>
                </td>
                <td>
                  <span class="badge <?= $c['status']==='active' ? 'bg-success' : 'bg-secondary' ?>">
                    <?= $c['status']==='active' ? 'Activo' : 'Inactivo' ?>
                  </span>
                </td>
                <td><?= htmlspecialchars($c['created_at']) ?></td>
                <td class="text-end">
                  <a class="btn btn-sm btn-outline-primary" 
                     href="<?= $url ?>/admin/blog/category_edit.php?id=<?= (int)$c['id'] ?>" title="Editar">
                    <i class="fa fa-pencil"></i>
                  </a>
                  <form method="post" action="<?= $url ?>/admin/blog/category_delete.php" 
                        class="d-inline-block del-form" data-name="<?= htmlspecialchars($c['name']) ?>">
                    <input type="hidden" name="id" value="<?= (int)$c['id'] ?>">
                    <button type="submit" class="btn-trash" title="Eliminar">
                      <i class="fa fa-trash"></i>
                    </button>
                  </form>
                </td>
              </tr>
            <?php endforeach; ?>
            <?php if(empty($categories)): ?>
              <tr><td colspan="5" class="text-center p-4">No hay categorías.</td></tr>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>

<?php require_once __DIR__ . '/../inc/menu-footer.php'; ?>
<?php require_once __DIR__ . '/../inc/flash_simple.php'; ?>

<!-- DataTables Responsive (si ya los cargas globalmente, puedes quitar estas 2 líneas) -->
<script src="https://cdn.datatables.net/responsive/2.5.0/js/dataTables.responsive.min.js"></script>
<script src="https://cdn.datatables.net/responsive/2.5.0/js/responsive.bootstrap5.min.js"></script>

<script>
$(document).ready(function() {
  // DataTable nativo Bootstrap 5 + Responsive
  $('#categoriesTable').DataTable({
    responsive: {
      details: { type: 'column', target: 'tr' }
    },
    language: {
      url: "//cdn.datatables.net/plug-ins/1.13.7/i18n/es-ES.json"
    },
    order: [[3, 'desc']],
    pageLength: 25
  });

  // Confirmación eliminar con SweetAlert
  $('.del-form').on('submit', function(e){
    e.preventDefault();
    const form = this;
    const name = form.dataset.name || 'la categoría';
    Swal.fire({
      icon: 'warning',
      title: '¿Eliminar?',
      text: `Se eliminará "${name}".`,
      showCancelButton: true,
      confirmButtonText: 'Sí, eliminar',
      cancelButtonText: 'Cancelar',
      confirmButtonColor: '#d33'
    }).then((res)=>{ if(res.isConfirmed) form.submit(); });
  });
});
</script>
</body>
</html>

