<?php
require_once __DIR__ . '/../../inc/config.php';
require_once __DIR__ . '/../login/session.php';
$permisopage = 'Gestionar Suscriptores';
include('../login/restriction.php');
require_once __DIR__ . '/../inc/flash_helpers.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id     = (int)($_POST['id'] ?? 0);
    $action = $_POST['action'] ?? '';
    try {
        if ($id && $action === 'toggle') {
            $current = db()->prepare("SELECT status FROM subscribers WHERE id = ?");
            $current->execute([$id]);
            $row = $current->fetch();
            if ($row) {
                $newStatus = $row['status'] === 'active' ? 'inactive' : 'active';
                db()->prepare("UPDATE subscribers SET status = ? WHERE id = ?")->execute([$newStatus, $id]);
                setFlash('success', 'Estado actualizado');
            }
        } elseif ($id && $action === 'delete') {
            db()->prepare("DELETE FROM subscribers WHERE id = ?")->execute([$id]);
            setFlash('success', 'Suscriptor eliminado');
        }
    } catch (Throwable $e) {
        setFlash('error', 'Error al procesar la acción');
    }
    header('Location: ' . URLBASE . '/admin/subscribers/');
    exit;
}

try {
    $total  = db()->query("SELECT COUNT(*) FROM subscribers")->fetchColumn();
    $active = db()->query("SELECT COUNT(*) FROM subscribers WHERE status='active'")->fetchColumn();
    $subs   = db()->query("SELECT * FROM subscribers ORDER BY created_at DESC")->fetchAll();
} catch (Throwable $e) {
    $total = 0; $active = 0; $subs = [];
}
?>
<!doctype html>
<html lang="es">
<head>
<meta charset="utf-8">
<title>Suscriptores</title>
<?php include('../inc/header.php'); ?>
</head>
<body>
<?php include('../inc/menu.php'); ?>
<div class="page-wrapper">
    <div class="container-fluid">
        <div class="page-header">
            <h4><i class="fas fa-envelope-open-text me-2" style="color:var(--primary-color)"></i>Suscriptores</h4>
            <span class="badge" style="background:var(--primary-color);font-size:.85rem;padding:.45em .9em;border-radius:8px;"><?= (int)$total ?> suscriptores</span>
        </div>
        <?php renderFlashMessages(); ?>

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
                        <div class="h4 text-success"><?= $active ?></div>
                        <div class="text-muted">Activos</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card shadow-sm">
            <div class="card-body p-0">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Nombre</th>
                            <th>Email</th>
                            <th>Privacidad</th>
                            <th>Estado</th>
                            <th>Fecha</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php if (empty($subs)): ?>
                        <tr><td colspan="6" class="text-center text-muted py-4">Sin suscriptores aún</td></tr>
                    <?php else: ?>
                        <?php foreach ($subs as $s): ?>
                        <tr data-id="<?= (int)$s['id'] ?>">
                            <td><?= htmlspecialchars($s['name']) ?></td>
                            <td><?= htmlspecialchars($s['email']) ?></td>
                            <td><?= $s['privacy_accepted'] ? '<span class="badge bg-success">Sí</span>' : '<span class="badge bg-warning text-dark">No</span>' ?></td>
                            <td>
                                <span class="badge bg-<?= $s['status']==='active'?'success':'secondary' ?>">
                                    <?= $s['status'] === 'active' ? 'Activo' : 'Inactivo' ?>
                                </span>
                            </td>
                            <td><?= date('d/m/Y', strtotime($s['created_at'])) ?></td>
                            <td>
                                <button type="button"
                                        class="btn btn-sm btn-outline-primary me-1"
                                        title="Ver detalles"
                                        onclick="openSub(<?= (int)$s['id'] ?>)">
                                    <i class="fas fa-eye"></i>
                                </button>
                                <button type="button"
                                        class="btn btn-sm btn-outline-danger"
                                        title="Eliminar"
                                        onclick="confirmDelete(<?= (int)$s['id'] ?>)">
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

<!-- Modal: Ver suscriptor -->
<div class="modal fade" id="subModal" tabindex="-1" aria-labelledby="subModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-md">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="subModalLabel">
                    <i class="fas fa-user me-2" style="color:var(--primary-color)"></i>Detalle del suscriptor
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body">
                <dl class="row mb-0">
                    <dt class="col-sm-4">Nombre</dt>
                    <dd class="col-sm-8" id="sdName"></dd>
                    <dt class="col-sm-4">Email</dt>
                    <dd class="col-sm-8" id="sdEmail"></dd>
                    <dt class="col-sm-4">Privacidad</dt>
                    <dd class="col-sm-8" id="sdPrivacy"></dd>
                    <dt class="col-sm-4">Estado</dt>
                    <dd class="col-sm-8" id="sdStatus"></dd>
                    <dt class="col-sm-4">Registro</dt>
                    <dd class="col-sm-8" id="sdDate"></dd>
                </dl>
            </div>
            <div class="modal-footer d-flex justify-content-between">
                <button type="button" id="btnToggle"
                        class="btn btn-sm btn-outline-secondary"
                        onclick="doToggle()">
                    <i class="fas fa-toggle-on me-1"></i> Cambiar estado
                </button>
                <div>
                    <button type="button" id="btnDelete"
                            class="btn btn-sm btn-danger me-2"
                            onclick="doDelete()">
                        <i class="fas fa-trash me-1"></i> Eliminar
                    </button>
                    <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                </div>
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
var subsData = <?= json_encode(
    array_reduce($subs ?? [], function($carry, $s) {
        $carry[(int)$s['id']] = [
            'name'             => $s['name'] ?? '',
            'email'            => $s['email'] ?? '',
            'privacy_accepted' => (bool)$s['privacy_accepted'],
            'status'           => $s['status'] ?? 'inactive',
            'date'             => date('d/m/Y', strtotime($s['created_at'])),
        ];
        return $carry;
    }, []),
    JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP
) ?>;

var currentSubId = null;

function openSub(id) {
    var s = subsData[id];
    if (!s) return;
    currentSubId = id;

    document.getElementById('sdName').textContent  = s.name;
    document.getElementById('sdEmail').textContent = s.email;
    document.getElementById('sdPrivacy').innerHTML = s.privacy_accepted
        ? '<span class="badge bg-success">Aceptada</span>'
        : '<span class="badge bg-warning text-dark">No aceptada</span>';
    document.getElementById('sdDate').textContent  = s.date;

    updateStatusDisplay(s.status);
    bootstrap.Modal.getOrCreateInstance(document.getElementById('subModal')).show();
}

function updateStatusDisplay(status) {
    var s = subsData[currentSubId];
    document.getElementById('sdStatus').innerHTML = status === 'active'
        ? '<span class="badge bg-success">Activo</span>'
        : '<span class="badge bg-secondary">Inactivo</span>';
    document.getElementById('btnToggle').innerHTML = status === 'active'
        ? '<i class="fas fa-ban me-1"></i> Desactivar'
        : '<i class="fas fa-check me-1"></i> Activar';
}

function doToggle() {
    var fd = new FormData();
    fd.append('action', 'toggle');
    fd.append('id', currentSubId);
    fetch(location.href, { method: 'POST', body: fd })
        .then(function() {
            var s = subsData[currentSubId];
            s.status = s.status === 'active' ? 'inactive' : 'active';
            updateStatusDisplay(s.status);

            var row = document.querySelector('tr[data-id="' + currentSubId + '"]');
            if (row) {
                var badge = row.querySelector('td:nth-child(4) .badge');
                if (badge) {
                    badge.className = 'badge bg-' + (s.status === 'active' ? 'success' : 'secondary');
                    badge.textContent = s.status === 'active' ? 'Activo' : 'Inactivo';
                }
            }
        });
}

function doDelete() {
    Swal.fire({
        title: '¿Eliminar a "' + (subsData[currentSubId]?.name || '') + '"?',
        text: 'Esta acción no se puede deshacer.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#6c757d',
        cancelButtonText: 'Cancelar',
        confirmButtonText: 'Eliminar'
    }).then(function(result) {
        if (result.isConfirmed) {
            document.getElementById('actionId').value   = currentSubId;
            document.getElementById('actionName').value = 'delete';
            document.getElementById('actionForm').submit();
        }
    });
}

function confirmDelete(id) {
    currentSubId = id;
    doDelete();
}
</script>

<?php include('../inc/menu-footer.php'); ?>
<?php include('../inc/sweetAlert.php'); ?>
</body>
</html>
