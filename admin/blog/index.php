<?php
require_once __DIR__ . '/../../inc/config.php';
require_once __DIR__ . '/../login/session.php';
$permisopage = 'Ver Blogs';
include('../login/restriction.php');

require_once __DIR__ . '/../inc/flash_helpers.php';

// Cargar usuarios para el selector de transferencia
$usuarios = db()->query("SELECT id, nombre, apellido, username, foto_perfil 
                         FROM usuarios 
                         WHERE borrado = 0 AND estado = 0 
                         ORDER BY nombre ASC")
                ->fetchAll(PDO::FETCH_ASSOC);
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
    background:#f8f9fa; border:1px solid #dee2e6;
    border-radius:6px; padding:10px 12px; margin-bottom:15px;
}
.post-title {
    max-width:300px; overflow:hidden;
    text-overflow:ellipsis; white-space:nowrap; cursor:help;
}
.post-title:hover { color:#0d6efd; }

/* — Modal transferencia — */
.transfer-modal-header {
    background: linear-gradient(135deg, #214A82 0%, #4972AA 100%);
    color: #fff;
}
.transfer-modal-header .btn-close { filter: invert(1); }
.author-card {
    border: 2px solid #dee2e6;
    border-radius: 12px;
    padding: 16px;
    text-align: center;
    transition: .2s;
}
.author-card.current { background: #fff3cd; border-color: #ffc107; }
.author-card.new     { background: #d1fae5; border-color: #10b981; }
.author-avatar {
    width: 56px; height: 56px; border-radius: 50%;
    display: flex; align-items: center; justify-content: center;
    font-size: 22px; font-weight: 700; margin: 0 auto 8px;
    color: #fff;
}
.avatar-current { background: #f59e0b; }
.avatar-new     { background: #10b981; }
.transfer-arrow {
    font-size: 28px; color: #6c757d;
    display: flex; align-items: center; justify-content: center;
}

/* — Lista de usuarios — */
.user-select-option {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 10px 12px;
    cursor: pointer;
    border-radius: 8px;
    transition: .15s;
    border: 1px solid transparent;
}
.user-select-option:hover   { background: #f0f4ff; border-color: #bfdbfe; }
.user-select-option.selected { background: #dbeafe; border-color: #3b82f6; }

.user-option-avatar {
    width: 40px; height: 40px; border-radius: 50%;
    background: #10b981; color: #fff;
    display: flex; align-items: center; justify-content: center;
    font-size: 14px; font-weight: 700; flex-shrink: 0;
}
.user-option-info { flex: 1; text-align: left; }
.user-option-info .name     { font-weight: 600; font-size: 14px; color: #1e293b; }
.user-option-info .username { font-size: 12px; color: #64748b; }

#user-search-input {
    border-radius: 8px; border: 1.5px solid #dee2e6;
    padding: 8px 12px; width: 100%; margin-bottom: 10px;
    font-size: 14px;
}
#user-search-input:focus { outline: none; border-color: #214A82; box-shadow: 0 0 0 3px rgba(33,74,130,.1); }

#users-list {
    max-height: 240px;
    overflow-y: auto;
    border: 1.5px solid #e2e8f0;
    border-radius: 10px;
    padding: 4px;
    background: #fff;
    scrollbar-width: thin;
    scrollbar-color: #cbd5e1 transparent;
}
#users-list::-webkit-scrollbar       { width: 6px; }
#users-list::-webkit-scrollbar-track { background: transparent; }
#users-list::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 3px; }

.transfer-summary {
    background: linear-gradient(135deg, #f0f4ff, #e0f2fe);
    border-radius: 12px; padding: 16px; margin-top: 16px;
    border: 1px solid #bfdbfe;
}
#btn-transfer-confirm {
    background: linear-gradient(135deg, #214A82, #4972AA);
    border: none; color: #fff; font-weight: 600;
    padding: 10px 24px; border-radius: 8px; transition: .2s;
}
#btn-transfer-confirm:hover    { opacity: .9; transform: translateY(-1px); }
#btn-transfer-confirm:disabled { opacity: .5; cursor: not-allowed; transform: none; }

/* — Botón transferir en fila — */
.btn-transfer-row {
    display: inline-flex; align-items: center; justify-content: center;
    width: 32px; height: 32px;
    border: 1px solid #214A82; border-radius: 6px;
    background: #fff; color: #214A82;
    cursor: pointer; transition: .15s;
}
.btn-transfer-row:hover { background: #214A82; color: #fff; }
</style>
</head>
<body>
<div class="container" style="padding:0">
  <div class="portada">
    <h1><i class="bi bi-journal-text"></i> Blog</h1>
    <a class="btn btn-success float-end" href="<?= $url ?>/admin/blog/create.php">
      <i class="fa-solid fa-plus"></i> Nueva entrada
    </a>
  </div>
</div>

<?php include('../inc/menu.php'); ?>

<div class="container-fluid">
  <div class="card shadow-sm">
    <div class="card-body">

      <div id="massActions" class="justify-content-between align-items-center" style="display:none;">
        <div class="d-flex flex-wrap gap-2">
          <button id="btnDeleteSelected" class="btn btn-outline-danger btn-sm">
            <i class="fa fa-trash"></i> Borrar seleccionados
          </button>
          <button id="btnDraftSelected" class="btn btn-outline-secondary btn-sm">
            <i class="fa fa-file"></i> Pasar a borrador
          </button>
          <button id="btnPublishSelected" class="btn btn-outline-success btn-sm">
            <i class="fa fa-check"></i> Pasar a publicado
          </button>
          <button id="btnTransferSelected" class="btn btn-outline-primary btn-sm">
            <i class="fa-solid fa-arrow-right-arrow-left"></i> Transferir autoría
          </button>
        </div>
        <small class="text-muted mt-1" id="countSelected"></small>
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
          <tbody></tbody>
        </table>
      </div>
    </div>
  </div>
</div>

<!-- ══ Modal Transferir Autoría ══ -->
<div class="modal fade" id="modalTransfer" tabindex="-1" data-bs-backdrop="static">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content border-0 shadow-lg">

      <div class="modal-header transfer-modal-header">
        <div>
          <h5 class="modal-title mb-0">
            <i class="fa-solid fa-arrow-right-arrow-left me-2"></i>
            Transferir autoría
          </h5>
          <small class="opacity-75">Reasigna entradas a otro autor del sistema</small>
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>

      <div class="modal-body p-4">

        <!-- Info entradas seleccionadas -->
        <div class="alert alert-info d-flex align-items-center gap-2 py-2 mb-4">
          <i class="bi bi-info-circle-fill fs-5"></i>
          <span>
            Transfiriendo <strong id="transfer-count">0</strong>
            entrada<span id="transfer-plural"></span> seleccionada<span id="transfer-plural2"></span>
          </span>
        </div>

        <div class="row g-3 align-items-start">

          <!-- Autor actual -->
          <div class="col-md-5">
            <p class="text-muted small fw-semibold mb-2 text-uppercase">Autor actual</p>
            <div class="author-card current">
              <div class="author-avatar avatar-current" id="current-avatar">?</div>
              <div class="fw-semibold" id="current-author-name">—</div>
              <small class="text-muted" id="current-author-user">—</small>
            </div>
          </div>

          <!-- Flecha -->
          <div class="col-md-2 transfer-arrow">
            <i class="fa-solid fa-arrow-right"></i>
          </div>

          <!-- Nuevo autor -->
          <div class="col-md-5">
            <p class="text-muted small fw-semibold mb-2 text-uppercase">Nuevo autor</p>
            <div class="author-card new" id="new-author-card"
                 style="opacity:.4; min-height:100px; display:flex; align-items:center; justify-content:center;">
              <span class="text-muted small">Selecciona abajo</span>
            </div>
          </div>

        </div>

        <!-- Buscador de usuarios -->
        <div class="mt-4">
          <label class="fw-semibold mb-2">
            <i class="bi bi-person-search me-1"></i>
            Seleccionar nuevo autor
          </label>
          <input type="text" id="user-search-input" placeholder="Buscar por nombre o usuario...">
          <div id="users-list">
            <?php foreach ($usuarios as $u):
              $initials = strtoupper(substr($u['nombre'], 0, 1) . substr($u['apellido'], 0, 1));
              $fullName = htmlspecialchars($u['nombre'] . ' ' . $u['apellido']);
              $username = htmlspecialchars($u['username'] ?? '');
            ?>
            <div class="user-select-option"
                data-id="<?= $u['id'] ?>"
                data-name="<?= $fullName ?>"
                data-username="<?= $username ?>"
                data-initials="<?= $initials ?>"
                data-foto="<?= htmlspecialchars($u['foto_perfil'] ?? '') ?>">
              <div class="user-option-avatar">
                <?php if (!empty($u['foto_perfil']) && file_exists('../../' . $u['foto_perfil'])): ?>
                  <img src="<?= $url . '/' . htmlspecialchars($u['foto_perfil']) ?>"
                      style="width:40px;height:40px;border-radius:50%;object-fit:cover;">
                <?php else: ?>
                  <?= $initials ?>
                <?php endif; ?>
              </div>
              <div class="user-option-info">
                <div class="name"><?= $fullName ?></div>
                <div class="username">@<?= $username ?></div>
              </div>
            </div>
            <?php endforeach; ?>
          </div>
        </div>

        <!-- Resumen -->
        <div class="transfer-summary d-none" id="transfer-summary">
          <div class="d-flex align-items-center gap-2 mb-1">
            <i class="bi bi-check-circle-fill text-success"></i>
            <strong>Resumen de la transferencia</strong>
          </div>
          <div class="small text-muted">
            <strong id="summary-count">0</strong> entrada(s) de
            <strong id="summary-from">—</strong> →
            <strong id="summary-to" class="text-success">—</strong>
          </div>
        </div>

      </div>

      <div class="modal-footer border-0 pt-0 px-4 pb-4">
        <button type="button" class="btn btn-light px-4" data-bs-dismiss="modal">
          <i class="bi bi-x me-1"></i> Cancelar
        </button>
        <button type="button" id="btn-transfer-confirm" disabled>
          <i class="fa-solid fa-arrow-right-arrow-left me-2"></i>
          Confirmar transferencia
        </button>
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
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
$(document).ready(function () {

  /* ── DataTable ── */
  const table = $('#postsTable').DataTable({
    processing : true,
    serverSide : true,
    ajax       : { url: '<?= $url ?>/admin/blog/ajax_posts.php', type: 'POST' },
    columns    : [
      { orderable:false, searchable:false },
      { orderable:false },
      { orderable:true  },
      { orderable:false },
      { orderable:true  },
      { orderable:true  },
      { orderable:true  },
      { orderable:false, searchable:false }
    ],
    language   : { url:'//cdn.datatables.net/plug-ins/1.13.7/i18n/es-ES.json', processing:'Cargando...' },
    order      : [[6,'desc']],
    pageLength : 25,
    columnDefs : [{ targets:-1, className:'text-end no-click' }]
  });

  /* ── Checkboxes ── */
  const $massActions = $('#massActions');

  function toggleMassActions() {
    const n = $('.chkPost:checked').length;
    if (n > 0) {
      $massActions.css('display','flex').hide().slideDown(150);
      $('#countSelected').text(`${n} seleccionada${n>1?'s':''}`);
    } else {
      $massActions.slideUp(150);
      $('#countSelected').text('');
    }
  }

  $('#postsTable').on('change','#selectAll', function () {
    $('.chkPost').prop('checked', this.checked);
    toggleMassActions();
  });
  $('#postsTable').on('change','.chkPost', function () {
    const all = $('.chkPost').length;
    $('#selectAll').prop('checked', all === $('.chkPost:checked').length);
    toggleMassActions();
  });

  /* ── Acciones masivas ── */
  function bulkAction(action, title, text, color) {
    const ids = $('.chkPost:checked').map(function(){ return this.value; }).get();
    if (!ids.length) return Swal.fire('Nada seleccionado','','info');
    Swal.fire({
      icon:'question', title, text,
      showCancelButton:true,
      confirmButtonText:'Sí, continuar',
      confirmButtonColor:color
    }).then(r => {
      if (r.isConfirmed) $.post('bulk_actions.php', {action, ids}, () => table.ajax.reload());
    });
  }

  $('#btnDeleteSelected').on('click',  () => bulkAction('delete',  '¿Eliminar seleccionados?',     'Se eliminarán las entradas seleccionadas.',           '#d33'));
  $('#btnDraftSelected').on('click',   () => bulkAction('draft',   '¿Pasar a borrador?',            'Las entradas seleccionadas se marcarán como borrador.','#6c757d'));
  $('#btnPublishSelected').on('click', () => bulkAction('publish', '¿Publicar seleccionados?',      'Las entradas seleccionadas se publicarán.',            '#28a745'));

  /* ── Eliminar individual ── */
  $('#postsTable').on('click', '.btn-delete', function (e) {
    e.preventDefault();
    const id   = $(this).data('id');
    const name = $(this).data('name') || 'la entrada';
    Swal.fire({
      icon:'warning', title:'¿Eliminar?',
      text:`Se eliminará "${name}".`,
      showCancelButton:true,
      confirmButtonText:'Sí, eliminar',
      cancelButtonText:'Cancelar',
      confirmButtonColor:'#d33'
    }).then(r => {
      if (r.isConfirmed) $.post('<?= $url ?>/admin/blog/delete.php', {id}, () => table.ajax.reload());
    });
  });

  /* ══════════════════════════════════════════
     TRANSFERIR AUTORÍA — variables compartidas
  ══════════════════════════════════════════ */
  let selectedUserId   = null;
  let selectedUserName = null;

  /* — Función reutilizable para abrir el modal — */
  function openTransferModal(ids, authorName, authorFoto) {
    const n        = ids.length;
    const initials = authorName.split(' ').map(w => w[0] || '').join('').substring(0,2).toUpperCase();

    // Resetear
    selectedUserId   = null;
    selectedUserName = null;
    $('#btn-transfer-confirm').prop('disabled', true);
    $('#transfer-summary').addClass('d-none');
    $('#new-author-card').css('opacity','.4').html('<span class="text-muted small">Selecciona abajo</span>');
    $('#user-search-input').val('');
    $('#users-list .user-select-option').removeClass('selected').show();
    $('#modalTransfer').data('transfer-ids', ids);

    $('#transfer-count').text(n);
    $('#transfer-plural').text(n > 1 ? 's' : '');
    $('#transfer-plural2').text(n > 1 ? 's' : '');

    $('#current-author-name').text(authorName);
    $('#current-author-user').text('');

    // ← Mostrar foto o iniciales en el avatar actual
    if (authorFoto) {
        $('#current-avatar').html(
            `<img src="${authorFoto}"
                  style="width:56px;height:56px;border-radius:50%;object-fit:cover;">`
        ).css('background', 'transparent');
    } else {
        $('#current-avatar').text(initials || '?').css('background', '');
    }

    new bootstrap.Modal(document.getElementById('modalTransfer')).show();
}

/* — Botón masivo — */
$('#btnTransferSelected').on('click', function () {
    const ids = $('.chkPost:checked').map(function(){ return this.value; }).get();
    if (!ids.length) return Swal.fire('Nada seleccionado','Selecciona al menos una entrada.','info');
    const firstRow   = table.row($('.chkPost:checked').first().closest('tr')).data();
    const authorName = firstRow ? ($(firstRow[4]).text() || firstRow[4]) : '—';
    openTransferModal(ids, authorName, null); // masivo no tiene foto fácil
});

/* — Botón fila individual — */
$('#postsTable').on('click', '.btn-transfer-single', function (e) {
    e.preventDefault();
    e.stopPropagation();
    const id         = $(this).data('id');
    const authorName = $(this).data('author') || '—';
    const foto       = $(this).data('foto')
                       ? '<?= URLBASE ?>/' + $(this).data('foto')
                       : null;
    openTransferModal([id], authorName, foto);
});

  /* — Botón masivo — */
  $('#btnTransferSelected').on('click', function () {
    const ids = $('.chkPost:checked').map(function(){ return this.value; }).get();
    if (!ids.length) return Swal.fire('Nada seleccionado', 'Selecciona al menos una entrada.', 'info');

    // Autor del primer row seleccionado
    const firstRow   = table.row($('.chkPost:checked').first().closest('tr')).data();
    const authorName = firstRow ? ($(firstRow[4]).text() || firstRow[4]) : '—';

    openTransferModal(ids, authorName);
  });

  /* — Botón en fila individual — */
  $('#postsTable').on('click', '.btn-transfer-single', function (e) {
    e.preventDefault();
    e.stopPropagation();

    const id         = $(this).data('id');
    const authorName = $(this).data('author') || '—';

    openTransferModal([id], authorName);
  });

  /* — Buscador de usuarios — */
  $('#user-search-input').on('input', function () {
    const q = this.value.toLowerCase();
    $('#users-list .user-select-option').each(function () {
      const name = $(this).data('name').toLowerCase();
      const user = $(this).data('username').toLowerCase();
      $(this).toggle(name.includes(q) || user.includes(q));
    });
  });

  /* — Seleccionar usuario de la lista — */
  $('#users-list').on('click', '.user-select-option', function () {
    $('#users-list .user-select-option').removeClass('selected');
    $(this).addClass('selected');

    selectedUserId   = $(this).data('id');
    selectedUserName = $(this).data('name');
    const initials   = $(this).data('initials');
    const username   = $(this).data('username');
    const foto       = $(this).data('foto');

    // Avatar con foto o iniciales
    const avatarHtml = foto
        ? `<img src="<?= URLBASE ?>/${foto}"
               style="width:56px;height:56px;border-radius:50%;object-fit:cover;margin:0 auto 8px;display:block;">`
        : `<div class="author-avatar avatar-new">${initials}</div>`;

    $('#new-author-card')
      .css('opacity','1')
      .html(`
        ${avatarHtml}
        <div class="fw-semibold">${selectedUserName}</div>
        <small class="text-muted">@${username}</small>
      `);

    const ids = $('#modalTransfer').data('transfer-ids') || [];
    $('#summary-count').text(ids.length);
    $('#summary-from').text($('#current-author-name').text());
    $('#summary-to').text(selectedUserName);
    $('#transfer-summary').removeClass('d-none');
    $('#btn-transfer-confirm').prop('disabled', false);
});

  /* — Confirmar transferencia — */
  $('#btn-transfer-confirm').on('click', function () {
    const ids = $('#modalTransfer').data('transfer-ids') || [];
    if (!ids.length || !selectedUserId) return;

    const btn = $(this);

    Swal.fire({
      icon             : 'question',
      title            : '¿Confirmar transferencia?',
      html             : `Se reasignarán <strong>${ids.length}</strong> entrada(s) a <strong>${selectedUserName}</strong>.<br>Esta acción se puede revertir.`,
      showCancelButton : true,
      confirmButtonText: 'Sí, transferir',
      cancelButtonText : 'Cancelar',
      confirmButtonColor: '#214A82',
    }).then(r => {
      if (!r.isConfirmed) return;

      btn.prop('disabled', true)
         .html('<span class="spinner-border spinner-border-sm me-2"></span> Transfiriendo...');

      $.post('transfer_author.php', { ids: ids, user_id: selectedUserId }, function (res) {
        bootstrap.Modal.getInstance(document.getElementById('modalTransfer')).hide();
        btn.prop('disabled', false)
           .html('<i class="fa-solid fa-arrow-right-arrow-left me-2"></i> Confirmar transferencia');

        if (res.success) {
          Swal.fire({
            icon : 'success',
            title: '¡Transferido!',
            text : res.message,
            timer: 2000,
            showConfirmButton: false,
          }).then(() => table.ajax.reload());
        } else {
          Swal.fire('Error', res.message || 'No se pudo completar.', 'error');
        }
      }, 'json').fail(() => {
        btn.prop('disabled', false)
           .html('<i class="fa-solid fa-arrow-right-arrow-left me-2"></i> Confirmar transferencia');
        Swal.fire('Error', 'Error de conexión.', 'error');
      });
    });
  });

});
</script>
</body>
</html>






