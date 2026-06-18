<?php
require_once __DIR__ . '/../../inc/config.php';
require_once __DIR__ . '/../login/session.php';
$permisopage = 'Gestionar Mensajes';
include('../login/restriction.php');
require_once __DIR__ . '/../inc/flash_helpers.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id     = (int)($_POST['id'] ?? 0);
    $action = $_POST['action'] ?? '';

    if ($id && $action === 'mark_read') {
        db()->prepare("UPDATE contact_messages SET status = 'read' WHERE id = ?")
           ->execute([$id]);

    } elseif ($id && $action === 'delete') {
        $stmt = db()->prepare("SELECT name FROM contact_messages WHERE id = ?");
        $stmt->execute([$id]);
        $row = $stmt->fetch();
        db()->prepare("DELETE FROM contact_messages WHERE id = ?")->execute([$id]);
        log_system_action(
            'Eliminar Mensaje de Contacto',
            'Mensaje eliminado de: ' . ($row['name'] ?? ''),
            'contact_messages',
            $id
        );
        setFlash('success', 'Mensaje eliminado correctamente');
    }

    header('Location: ' . URLBASE . '/admin/contact/');
    exit;
}

$total  = (int) db()->query("SELECT COUNT(*) FROM contact_messages")->fetchColumn();
$unread = (int) db()->query("SELECT COUNT(*) FROM contact_messages WHERE status = 'unread'")->fetchColumn();
$msgs   = db()->query("SELECT * FROM contact_messages ORDER BY created_at DESC")->fetchAll();
?>
<!doctype html>
<html lang="es">
<head>
<meta charset="utf-8">
<title>Mensajes de Contacto</title>
<?php include('../inc/header.php'); ?>
</head>
<body>
<?php include('../inc/menu.php'); ?>
<div class="main-content">
    <div class="container-fluid">
        <h1 class="h3 mb-4">Mensajes de Contacto</h1>
        <?php if (function_exists('renderFlashMessages')) renderFlashMessages(); ?>

        <div class="row mb-4">
            <div class="col-md-3">
                <div class="card text-center shadow-sm">
                    <div class="card-body">
                        <div class="h4"><?= $total ?></div>
                        <div class="text-muted">Total</div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card text-center shadow-sm">
                    <div class="card-body">
                        <div class="h4 text-warning"><?= $unread ?></div>
                        <div class="text-muted">No leídos</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card shadow-sm">
            <div class="card-body p-0">
                <table class="table table-hover mb-0">
                    <thead class="thead-light">
                        <tr>
                            <th>Nombre</th>
                            <th>Email</th>
                            <th>Teléfono</th>
                            <th>Mensaje</th>
                            <th>Estado</th>
                            <th>Fecha</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php if (empty($msgs)): ?>
                        <tr><td colspan="7" class="text-center text-muted py-4">Sin mensajes aún</td></tr>
                    <?php else: ?>
                        <?php foreach ($msgs as $m): ?>
                        <tr data-id="<?= (int)$m['id'] ?>">
                            <td><?= htmlspecialchars($m['name']) ?></td>
                            <td><?= htmlspecialchars($m['email']) ?></td>
                            <td><?= $m['phone'] ? htmlspecialchars($m['phone']) : '<span class="text-muted">—</span>' ?></td>
                            <td><?= htmlspecialchars(mb_strimwidth($m['message'], 0, 80, '…')) ?></td>
                            <td>
                                <?php if ($m['status'] === 'unread'): ?>
                                    <span class="badge badge-warning">No leído</span>
                                <?php else: ?>
                                    <span class="badge badge-secondary">Leído</span>
                                <?php endif; ?>
                            </td>
                            <td><?= date('d/m/Y H:i', strtotime($m['created_at'])) ?></td>
                            <td>
                                <button type="button"
                                        class="btn btn-sm btn-outline-primary mr-1"
                                        title="Ver mensaje"
                                        onclick="openMessage(
                                            <?= (int)$m['id'] ?>,
                                            <?= json_encode($m['name'],    JSON_HEX_TAG|JSON_HEX_AMP|JSON_HEX_APOS|JSON_HEX_QUOT|JSON_UNESCAPED_UNICODE) ?>,
                                            <?= json_encode($m['email'],   JSON_HEX_TAG|JSON_HEX_AMP|JSON_HEX_APOS|JSON_HEX_QUOT|JSON_UNESCAPED_UNICODE) ?>,
                                            <?= json_encode($m['phone'] ?? '', JSON_HEX_TAG|JSON_HEX_AMP|JSON_HEX_APOS|JSON_HEX_QUOT|JSON_UNESCAPED_UNICODE) ?>,
                                            <?= json_encode($m['message'], JSON_HEX_TAG|JSON_HEX_AMP|JSON_HEX_APOS|JSON_HEX_QUOT|JSON_UNESCAPED_UNICODE) ?>,
                                            <?= json_encode(date('d/m/Y H:i', strtotime($m['created_at'])), JSON_HEX_TAG|JSON_HEX_AMP|JSON_HEX_APOS|JSON_HEX_QUOT|JSON_UNESCAPED_UNICODE) ?>,
                                            <?= $m['status'] === 'unread' ? 'true' : 'false' ?>
                                        )">
                                    <i class="fas fa-eye"></i>
                                </button>
                                <button type="button"
                                        class="btn btn-sm btn-outline-danger"
                                        title="Eliminar"
                                        onclick="confirmDelete(<?= (int)$m['id'] ?>, <?= json_encode($m['name'], JSON_HEX_TAG|JSON_HEX_AMP|JSON_HEX_APOS|JSON_HEX_QUOT|JSON_UNESCAPED_UNICODE) ?>)">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Modal: Ver mensaje -->
<div class="modal fade" id="msgModal" tabindex="-1" aria-labelledby="msgModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="msgModalLabel">Detalle del mensaje</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Cerrar">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <dl class="row">
                    <dt class="col-sm-3">Nombre</dt>
                    <dd class="col-sm-9" id="mdName"></dd>
                    <dt class="col-sm-3">Email</dt>
                    <dd class="col-sm-9" id="mdEmail"></dd>
                    <dt class="col-sm-3">Teléfono</dt>
                    <dd class="col-sm-9" id="mdPhone"></dd>
                    <dt class="col-sm-3">Fecha</dt>
                    <dd class="col-sm-9" id="mdDate"></dd>
                    <dt class="col-sm-3">Mensaje</dt>
                    <dd class="col-sm-9">
                        <div id="mdMessage" style="white-space:pre-wrap;background:#f8f9fa;padding:12px;border-radius:4px;"></div>
                    </dd>
                </dl>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>

<!-- Hidden form for POST actions -->
<form id="actionForm" method="POST" style="display:none;">
    <input type="hidden" name="id"     id="actionId">
    <input type="hidden" name="action" id="actionName">
</form>

<script>
function openMessage(id, name, email, phone, message, date, isUnread) {
    document.getElementById('mdName').textContent    = name;
    document.getElementById('mdEmail').textContent   = email;
    document.getElementById('mdPhone').textContent   = phone || '—';
    document.getElementById('mdDate').textContent    = date;
    document.getElementById('mdMessage').textContent = message;

    $('#msgModal').modal('show');

    if (isUnread) {
        const fd = new FormData();
        fd.append('action', 'mark_read');
        fd.append('id', id);
        fetch(location.href, { method: 'POST', body: fd })
            .then(() => {
                const row = document.querySelector('tr[data-id="' + id + '"]');
                if (row) {
                    const badge = row.querySelector('.badge');
                    if (badge) { badge.className = 'badge badge-secondary'; badge.textContent = 'Leído'; }
                }
            });
    }
}

function confirmDelete(id, name) {
    Swal.fire({
        title: '¿Eliminar mensaje de "' + name + '"?',
        text: 'Esta acción no se puede deshacer.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#6c757d',
        cancelButtonText: 'Cancelar',
        confirmButtonText: 'Eliminar'
    }).then(function(result) {
        if (result.isConfirmed) {
            document.getElementById('actionId').value   = id;
            document.getElementById('actionName').value = 'delete';
            document.getElementById('actionForm').submit();
        }
    });
}
</script>

<?php include('../inc/menu-footer.php'); ?>
<?php include('../inc/sweetAlert.php'); ?>
</body>
</html>
