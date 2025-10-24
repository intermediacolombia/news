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

  /* Contenedor de acciones masivas */
  #massActions {
    display: none; /* <--- OCULTO POR DEFECTO EN CSS */
    background: #f8f9fa;
    border: 1px solid #dee2e6;
    border-radius: 6px;
    padding: 10px 12px;
    margin-bottom: 15px;
  }
</style>
</head>
<body>
<div class="container" style="padding: 0px;">
  <div class="portada">
    <h1><i class="bi bi-journal-text"></i> Blog</h1>
    <a class="btn btn-success float-end" href="<?= $url ?>/admin/blog/create.php">
      <i class="fa-solid fa-plus"></i> Nueva entrada
    </a>
  </div>
</div>

<?php include('../inc/menu.php'); ?>

<div class="container-fluid">

    <?php if(isset($_SESSION['error'])): ?>
    <div class="alert alert-danger alert-dismissible fade show"><?= $_SESSION['error']; unset($_SESSION['error']); ?></div>
  <?php endif; ?>
  <?php if(isset($_SESSION['success'])): ?>
    <div class="alert alert-success alert-dismissible fade show"><?= $_SESSION['success']; unset($_SESSION['success']); ?></div>
  <?php endif; ?>

  <div class="card shadow-sm">
    <div class="card-body">

            <div id="massActions" class="d-flex justify-content-between align-items-center">
        <div>
          <button id="btnDeleteSelected" class="btn btn-outline-danger btn-sm me-2">
            <i class="fa fa-trash"></i> Borrar seleccionados
          </button>
          <button id="btnDraftSelected" class="btn btn-outline-secondary btn-sm me-2">
            <i class="fa fa-file"></i> Pasar a borrador
          </button>
          <button id="btnPublishSelected" class="btn btn-outline-success btn-sm">
            <i class="fa fa-check"></i> Pasar a publicado
          </button>
        </div>
        <small class="text-muted" id="countSelected"></small>
      </div>

      <div class="table-responsive">
        <table id="postsTable" class="table table-striped table-hover align-middle nowrap" style="width:100%">
          <thead>
            <tr>
              <th><input type="checkbox" id="selectAll" class="form-check-input"></th>
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
                <td><input type="checkbox" class="chkPost form-check-input" value="<?= (int)$p['id'] ?>"></td>
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
                  <a class="btn btn-sm btn-outline-primary" href="<?= $url ?>/admin/blog/edit.php?id=<?= (int)$p['id'] ?>" title="Editar">
                    <i class="fa fa-pencil"></i>
                  </a>
                  <form method="post" action="<?= $url ?>/admin/blog/delete.php" class="d-inline-block del-form" data-name="<?= htmlspecialchars($p['title']) ?>">
                    <input type="hidden" name="id" value="<?= (int)$p['id'] ?>">
                    <button type="submit" class="btn-trash" title="Eliminar"><i class="fa fa-trash"></i></button>
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
  const table = $('#postsTable').DataTable({
    language: { url: "//cdn.datatables.net/plug-ins/1.13.7/i18n/es-ES.json" },
    order: [[6,'desc']],
    pageLength: 25
  });

  const $massActions = $('#massActions');
  const $countSelected = $('#countSelected');

  // === Mostrar / ocultar barra de acciones masivas ===
  function toggleMassActions() {
    const selected = $('.chkPost:checked').length;
    if (selected > 0) {
      // Muestra el contenedor de acciones masivas
      if (!$massActions.is(':visible')) $massActions.slideDown(150);
      $countSelected.text(`${selected} seleccionada${selected>1?'s':''}`);
    } else {
      // Oculta el contenedor de acciones masivas
      if ($massActions.is(':visible')) $massActions.slideUp(150);
      $countSelected.text('');
    }
  }

  // === Delegar eventos ===
  $(document).on('change', '#selectAll', function() {
    $('.chkPost').prop('checked', this.checked);
    toggleMassActions();
  });

  $(document).on('change', '.chkPost', function() {
    const all = $('.chkPost').length;
    const checked = $('.chkPost:checked').length;
    $('#selectAll').prop('checked', all === checked);
    toggleMassActions();
  });

  // === Acciones masivas ===
  function bulkAction(action, title, text, color) {
    const ids = $('.chkPost:checked').map(function(){ return this.value; }).get();
    if (ids.length === 0) return Swal.fire('Nada seleccionado','','info');

    Swal.fire({
      icon: 'question',
      title, text,
      showCancelButton: true,
      confirmButtonText: 'Sí, continuar',
      confirmButtonColor: color
    }).then(res => {
      if (res.isConfirmed) {
        $.post('bulk_actions.php', {action, ids}, () => location.reload());
      }
    });
  }

  // Botones
  $('#btnDeleteSelected').on('click', () => bulkAction('delete', '¿Eliminar seleccionados?', 'Se eliminarán las entradas seleccionadas.', '#d33'));
  $('#btnDraftSelected').on('click', () => bulkAction('draft', '¿Pasar a borrador?', 'Las entradas seleccionadas se marcarán como borrador.', '#6c757d'));
  $('#btnPublishSelected').on('click', () => bulkAction('publish', '¿Publicar seleccionados?', 'Las entradas seleccionadas se publicarán.', '#28a745'));

  // === Confirmación individual ===
  $(document).on('submit', '.del-form', function(e){
    e.preventDefault();
    const form = this;
    const name = form.dataset.name || 'la entrada';
    Swal.fire({
      icon:'warning',
      title:'¿Eliminar?',
      text:`Se eliminará "${name}".`,
      showCancelButton:true,
      confirmButtonText:'Sí, eliminar',
      cancelButtonText:'Cancelar',
      confirmButtonColor:'#d33'
    }).then(res=>{ if(res.isConfirmed) form.submit(); });
  });
});
</script>
</body>
</html>







