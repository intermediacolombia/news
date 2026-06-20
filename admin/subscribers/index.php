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
<div class="main-content">
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
                        <tr><th>Nombre</th><th>Email</th><th>Privacidad</th><th>Estado</th><th>Fecha</th><th>Acciones</th></tr>
                    </thead>
                    <tbody>
                    <?php if (empty($subs)): ?>
                        <tr><td colspan="6" class="text-center text-muted py-4">Sin suscriptores aún</td></tr>
                    <?php else: ?>
                        <?php foreach ($subs as $s): ?>
                        <tr>
                            <td><?= htmlspecialchars($s['name']) ?></td>
                            <td><?= htmlspecialchars($s['email']) ?></td>
                            <td><?= $s['privacy_accepted'] ? '<span class="badge bg-success">Sí</span>' : '<span class="badge bg-warning text-dark">No</span>' ?></td>
                            <td>
                                <form method="POST" style="display:inline;">
                                    <input type="hidden" name="id" value="<?= $s['id'] ?>">
                                    <input type="hidden" name="action" value="toggle">
                                    <button type="submit" class="badge bg-<?= $s['status']==='active'?'success':'secondary' ?> border-0 text-white" style="cursor:pointer;">
                                        <?= $s['status'] === 'active' ? 'Activo' : 'Inactivo' ?>
                                    </button>
                                </form>
                            </td>
                            <td><?= date('d/m/Y', strtotime($s['created_at'])) ?></td>
                            <td>
                                <form method="POST" style="display:inline;"
                                      onsubmit="return confirm('¿Eliminar este suscriptor?')">
                                    <input type="hidden" name="id" value="<?= $s['id'] ?>">
                                    <input type="hidden" name="action" value="delete">
                                    <button type="submit" class="btn btn-sm btn-outline-danger">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
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
<?php include('../inc/menu-footer.php'); ?>
<?php include('../inc/flash_simple.php'); ?>
</body>
</html>
