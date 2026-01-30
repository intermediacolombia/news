<?php
require_once __DIR__ . '/../../inc/config.php';
require_once __DIR__ . '/../login/session.php';
$permisopage = 'Ver Institucional';
include('../login/restriction.php');


require_once __DIR__ . '/../inc/flash_helpers.php';
?>

<!doctype html>
<html lang="es">
<head>
<meta charset="utf-8">
<title>Información Institucional</title>
<?php include('../inc/header.php'); ?>
<style>
  #institutionalTable thead th {
    background-color:#214A82; color:#fff;
  }
  #institutionalTable tbody tr:hover {
    background-color:#4972AA !important;
    color:#fff; cursor:pointer;
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
	
  .page-title {
    max-width: 300px;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
    cursor: help;
  }

  .page-title:hover {
    color: #0d6efd;
  }
  
  .badge-status {
    font-size: 0.75rem;
    padding: 0.35em 0.65em;
  }
</style>
</head>
<body>
<div class="container" style="padding: 0px;">
  <div class="portada">
    <h1><i class="bi bi-building"></i> Información Institucional</h1>
    <a class="btn btn-success float-end" href="<?= $url ?>/admin/institutional/create.php">
      <i class="fa-solid fa-plus"></i> Nueva Página
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
        <table id="institutionalTable" class="table table-striped table-hover align-middle nowrap" style="width:100%">
          <thead>
            <tr>
              <th><input type="checkbox" id="selectAll" class="form-check-input"></th>
              <th>Título</th>
              <th>Slug</th>
              <th>Tipo</th>
              <th>Estado</th>
              <th>Autor</th>
              <th>Última actualización</th>
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
  
  // Función para mostrar alertas de Bootstrap dinámicamente
  function showAlert(message, type = 'success') {
    // Tipos: success, danger, warning, info
    const alertHtml = `
      <div class="alert alert-${type} alert-dismissible fade show" role="alert">
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
      </div>
    `;
    
    // Insertar al inicio del container-fluid
    $('.container-fluid').prepend(alertHtml);
    
    // Auto-cerrar después de 5 segundos
    setTimeout(function() {
      $('.alert').fadeOut('slow', function() {
        $(this).remove();
      });
    }, 5000);
  }
  
  // Inicializar DataTable con server-side processing
  const table = $('#institutionalTable').DataTable({
    processing: true,
    serverSide: true,
    ajax: {
      url: '<?= $url ?>/admin/institutional/ajax_pages.php',
      type: 'POST'
    },
    columns: [
      { orderable: false, searchable: false },
      { orderable: true },
      { orderable: true },
      { orderable: true },
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
    const selected = $('.chkPage:checked').length;
    
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
  $('#institutionalTable').on('change', '#selectAll', function() {
    $('.chkPage').prop('checked', this.checked);
    toggleMassActions();
  });

  $('#institutionalTable').on('change', '.chkPage', function() {
    const all = $('.chkPage').length;
    const checked = $('.chkPage:checked').length;
    $('#selectAll').prop('checked', all === checked);
    toggleMassActions();
  });

  // Acciones masivas
  function bulkAction(action, title, text, color) {
    const ids = $('.chkPage:checked').map(function(){ return this.value; }).get();
    if (ids.length === 0) return Swal.fire('Nada seleccionado','','info');

    Swal.fire({
      icon: 'question',
      title, text,
      showCancelButton: true,
      confirmButtonText: 'Sí, continuar',
      cancelButtonText: 'Cancelar',
      confirmButtonColor: color
    }).then(res => {
      if (res.isConfirmed) {
        // Mostrar loading
        Swal.fire({
          title: 'Procesando...',
          allowOutsideClick: false,
          didOpen: () => {
            Swal.showLoading();
          }
        });
        
        $.post('bulk_actions.php', {action, ids})
          .done(function(response) {
            // Recargar tabla
            table.ajax.reload();
            
            // Desmarcar checkboxes
            $('#selectAll').prop('checked', false);
            $('.chkPage').prop('checked', false);
            toggleMassActions();
            
            // Cerrar SweetAlert
            Swal.close();
            
            // Mostrar alerta de Bootstrap
            showAlert(response.message || 'Acción completada correctamente', 'success');
          })
          .fail(function(xhr) {
            let errorMsg = 'Error al procesar la acción';
            try {
              const response = JSON.parse(xhr.responseText);
              errorMsg = response.message || errorMsg;
            } catch(e) {}
            
            Swal.fire({
              icon: 'error',
              title: 'Error',
              text: errorMsg
            });
          });
      }
    });
  }

  $('#btnDeleteSelected').on('click', () => bulkAction('delete', '¿Eliminar seleccionados?', 'Se eliminarán las páginas seleccionadas.', '#d33'));
  $('#btnDraftSelected').on('click', () => bulkAction('draft', '¿Pasar a borrador?', 'Las páginas seleccionadas se marcarán como borrador.', '#6c757d'));
  $('#btnPublishSelected').on('click', () => bulkAction('publish', '¿Publicar seleccionados?', 'Las páginas seleccionadas se publicarán.', '#28a745'));

  // Eliminación individual (delegado)
  $('#institutionalTable').on('click', '.btn-delete', function(e){
    e.preventDefault();
    const id = $(this).data('id');
    const name = $(this).data('name') || 'la página';
    
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
        // Mostrar loading
        Swal.fire({
          title: 'Eliminando...',
          allowOutsideClick: false,
          didOpen: () => {
            Swal.showLoading();
          }
        });
        
        $.post('<?= $url ?>/admin/institutional/delete.php', {id})
          .done(function(response) {
            // Recargar tabla
            table.ajax.reload();
            
            // Cerrar SweetAlert
            Swal.close();
            
            // Mostrar alerta de Bootstrap
            showAlert(response.message || 'Página eliminada correctamente', 'success');
          })
          .fail(function(xhr) {
            let errorMsg = 'Error al eliminar la página';
            try {
              const response = JSON.parse(xhr.responseText);
              errorMsg = response.message || errorMsg;
            } catch(e) {}
            
            Swal.fire({
              icon: 'error',
              title: 'Error',
              text: errorMsg
            });
          });
      }
    });
  });
});
</script>
</body>
</html>
