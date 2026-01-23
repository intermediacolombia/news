<?php
require_once __DIR__ . '/../../inc/config.php';
require_once __DIR__ . '/../login/session.php';
$permisopage = 'Ver Blogs';
include('../login/restriction.php');
session_start();

require_once __DIR__ . '/../inc/flash_helpers.php';
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

  #massActions {
    background: #f8f9fa;
    border: 1px solid #dee2e6;
    border-radius: 6px;
    padding: 10px 12px;
    margin-bottom: 15px;
  }
	
	.post-title {
    max-width: 300px;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
    cursor: help;
}

.post-title:hover {
    color: #0d6efd;
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

      <div id="massActions" class="justify-content-between align-items-center" style="display:none;">
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
            <!-- Los datos se cargan dinámicamente vía AJAX -->
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>

<?php include('../inc/menu-footer.php'); ?>
<?php include('../inc/flash_simple.php'); ?>

<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap5.min.js"></script>

<script>
$(document).ready(function(){
  
  // Inicializar DataTable con server-side processing
  const table = $('#postsTable').DataTable({
    processing: true,
    serverSide: true,
    ajax: {
      url: '<?= $url ?>/admin/blog/ajax_posts.php',
      type: 'POST'
    },
    columns: [
      { orderable: false, searchable: false },
      { orderable: false },
      { orderable: true },
      { orderable: false },
      { orderable: true },
      { orderable: true },
      { orderable: true },
      { orderable: false, searchable: false }
    ],
    language: { 
      url: "//cdn.datatables.net/plug-ins/1.13.7/i18n/es-ES.json",
      processing: "Cargando..."
    },
    order: [[6,'desc']],
    pageLength: 25,
    columnDefs: [
      { targets: -1, className: 'text-end no-click' }
    ]
  });

  const $massActions = $('#massActions');
  const $countSelected = $('#countSelected');

  function toggleMassActions() {
    const selected = $('.chkPost:checked').length;
    
    if (selected > 0) {
      if (!$massActions.is(':visible')) {
        $massActions.css('display', 'flex').hide().slideDown(150);
      }
      $countSelected.text(`${selected} seleccionada${selected>1?'s':''}`);
    } else {
      if ($massActions.is(':visible')) {
        $massActions.slideUp(150);
      }
      $countSelected.text('');
    }
  }

  // Eventos de checkbox (delegados para contenido dinámico)
  $('#postsTable').on('change', '#selectAll', function() {
    $('.chkPost').prop('checked', this.checked);
    toggleMassActions();
  });

  $('#postsTable').on('change', '.chkPost', function() {
    const all = $('.chkPost').length;
    const checked = $('.chkPost:checked').length;
    $('#selectAll').prop('checked', all === checked);
    toggleMassActions();
  });

  // Acciones masivas
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
        $.post('bulk_actions.php', {action, ids}, () => table.ajax.reload());
      }
    });
  }

  $('#btnDeleteSelected').on('click', () => bulkAction('delete', '¿Eliminar seleccionados?', 'Se eliminarán las entradas seleccionadas.', '#d33'));
  $('#btnDraftSelected').on('click', () => bulkAction('draft', '¿Pasar a borrador?', 'Las entradas seleccionadas se marcarán como borrador.', '#6c757d'));
  $('#btnPublishSelected').on('click', () => bulkAction('publish', '¿Publicar seleccionados?', 'Las entradas seleccionadas se publicarán.', '#28a745'));

  // Eliminación individual (delegado)
  $('#postsTable').on('click', '.btn-delete', function(e){
    e.preventDefault();
    const id = $(this).data('id');
    const name = $(this).data('name') || 'la entrada';
    
    Swal.fire({
      icon:'warning',
      title:'¿Eliminar?',
      text:`Se eliminará "${name}".`,
      showCancelButton:true,
      confirmButtonText:'Sí, eliminar',
      cancelButtonText:'Cancelar',
      confirmButtonColor:'#d33'
    }).then(res => {
      if(res.isConfirmed) {
        $.post('<?= $url ?>/admin/blog/delete.php', {id}, () => table.ajax.reload());
      }
    });
  });
});
</script>
</body>
</html>






