<?php
require_once __DIR__ . '/../../inc/config.php';
require_once __DIR__ . '/../login/session.php';
require_once __DIR__ . '/../inc/flash_helpers.php';

try {
  $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $dbuser, $dbpass, [
    PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE=>PDO::FETCH_ASSOC
  ]);
} catch(Throwable $e) { die("DB error: ".$e->getMessage()); }

$st = $pdo->query("
  SELECT p.*,
         GROUP_CONCAT(c.name SEPARATOR ', ') AS categorias
  FROM blog_posts p
  LEFT JOIN blog_post_category pc ON pc.post_id = p.id
  LEFT JOIN blog_categories c ON c.id = pc.category_id AND c.deleted=0
  WHERE p.deleted=0
  GROUP BY p.id
  ORDER BY p.created_at DESC
");
$posts = $st->fetchAll();
?>
<!doctype html>
<html lang="es">
<head>
<meta charset="utf-8">
<title>Entradas del Blog</title>
<?php include('../inc/header.php'); ?>
<style>
  #postsTable thead th {
    background-color:#214A82; color:#fff;
  }
  #postsTable tbody tr:hover {
    background-color:#4972AA !important;
    color:#fff; cursor:pointer;
  }
  .post-thumb {
    width:100px; border-radius:6px; box-shadow:0 2px 6px rgba(0,0,0,.1);
  }
  .no-click { cursor:default !important; }
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
    <h1><i class="bi bi-journal-text"></i> Blog</h1>

    <!-- BS5: data-bs-* -->
    <a class="btn btn-success float-end" href="<?= $url ?>/admin/blog/create.php">
      <i class="fa-solid fa-plus"></i> Nueva entrada
    </a>
  </div>
</div>
<?php include('../inc/menu.php'); ?>

<div class="container-fluid">
  
  <!-- Mensajes -->
  <?php if(isset($_SESSION['error'])): ?>
    <div class="alert alert-danger alert-dismissible fade show"><?= $_SESSION['error']; unset($_SESSION['error']); ?></div>
  <?php endif; ?>
  <?php if(isset($_SESSION['success'])): ?>
    <div class="alert alert-success alert-dismissible fade show"><?= $_SESSION['success']; unset($_SESSION['success']); ?></div>
  <?php endif; ?>

  <div class="card shadow-sm">
    <div class="card-body">
      <div class="table-responsive">
        <table id="postsTable" class="table table-striped table-hover align-middle nowrap" style="width:100%">
          <thead>
            <tr>
              <th>Imagen</th>
              <th>Título</th>
              <th>Categorías</th>
              <th>Autor</th>
              <th>Estado</th>
              <th>Creado</th>
              <th class="text-end no-click">Acciones</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach($posts as $p): ?>
              <tr>
                <td>
                  <?php if($p['image']): ?>
                    <img src="<?= $url ?>/<?= htmlspecialchars($p['image']) ?>" class="post-thumb">
                  <?php else: ?>
                    <span class="text-muted">Sin imagen</span>
                  <?php endif; ?>
                </td>
                <td style="max-width:250px;" class="text-truncate" title="<?= htmlspecialchars($p['title']) ?>">
  <strong><?= htmlspecialchars($p['title']) ?></strong>
</td>

                <td><?= htmlspecialchars($p['categorias'] ?: '—') ?></td>
                <td><?= htmlspecialchars($p['author']) ?></td>
                <td>
                  <span class="badge <?= $p['status']==='published' ? 'bg-success' : 'bg-secondary' ?>">
                    <?= $p['status']==='published' ? 'Publicado' : 'Borrador' ?>
                  </span>
                </td>
                <td><?= $p['created_at'] ?></td>
                <td class="text-end no-click">
                  <!-- 🚀 botón de editar conservado -->
                  <a class="btn btn-sm btn-outline-primary" 
                     href="<?= $url ?>/admin/blog/edit.php?id=<?= (int)$p['id'] ?>" title="Editar">
                    <i class="fa fa-pencil"></i>
                  </a>
                  <!-- 🚀 botón de eliminar con SweetAlert -->
                  <form method="post" action="<?= $url ?>/admin/blog/delete.php"
                        class="d-inline-block del-form" data-name="<?= htmlspecialchars($p['title']) ?>">
                    <input type="hidden" name="id" value="<?= (int)$p['id'] ?>">
                    <button type="submit" class="btn-trash" title="Eliminar">
                      <i class="fa fa-trash"></i>
                    </button>
                  </form>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>

<?php include('../inc/menu-footer.php'); ?>
<?php include('../inc/flash_simple.php'); ?>

<script>
$(function(){
  // DataTable con Bootstrap 5 nativo
  $('#postsTable').DataTable({
    language: { url: "//cdn.datatables.net/plug-ins/1.13.7/i18n/es-ES.json" },
    order: [[5,'desc']],
    pageLength: 25
  });

  // Confirmación eliminar con SweetAlert
  $('.del-form').on('submit', function(e){
    e.preventDefault();
    const form = this;
    const name = form.dataset.name || 'la entrada';
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





