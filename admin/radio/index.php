<?php
require_once __DIR__ . '/../../inc/config.php';
require_once __DIR__ . '/../login/session.php';
$permisopage = 'Gestionar Radio';
include('../login/restriction.php');
require_once __DIR__ . '/../inc/flash_helpers.php';

$programs = db()->query("SELECT * FROM programs ORDER BY title ASC")->fetchAll();
?>
<!doctype html>
<html lang="es">
<head>
<meta charset="utf-8">
<title>Programas de Radio</title>
<?php include('../inc/header.php'); ?>
</head>
<body>
<?php include('../inc/menu.php'); ?>
<div class="page-wrapper">
    <div class="container-fluid">
        <div class="page-header">
            <h4><i class="fas fa-broadcast-tower me-2" style="color:var(--primary-color)"></i>Programas de Radio</h4>
            <div class="d-flex align-items-center gap-2">
                <span class="badge" style="background:var(--primary-color);font-size:.85rem;padding:.45em .9em;border-radius:8px;"><?= count($programs) ?> programas</span>
                <a href="<?= URLBASE ?>/admin/radio/create.php" class="btn btn-primary btn-sm">
                    <i class="fas fa-plus me-1"></i> Nuevo Programa
                </a>
            </div>
        </div>
        <?php renderFlashMessages(); ?>
        <div class="card shadow-sm">
            <div class="card-body p-0">
                <table class="table table-hover mb-0">
                    <thead class="thead-light">
                        <tr>
                            <th>Título</th>
                            <th>Categoría</th>
                            <th>Conductores</th>
                            <th>Estado</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php if (empty($programs)): ?>
                        <tr><td colspan="5" class="text-center text-muted py-4">Sin programas. <a href="<?= URLBASE ?>/admin/radio/create.php">Crear primero</a>.</td></tr>
                    <?php else: ?>
                        <?php foreach ($programs as $p): ?>
                        <tr>
                            <td><strong><?= htmlspecialchars($p['title']) ?></strong></td>
                            <td><?= htmlspecialchars($p['category'] ?? '') ?></td>
                            <td><?= htmlspecialchars($p['hosts'] ?? '') ?></td>
                            <td>
                                <span class="badge badge-<?= $p['status'] === 'active' ? 'success' : 'secondary' ?>">
                                    <?= $p['status'] === 'active' ? 'Activo' : 'Inactivo' ?>
                                </span>
                            </td>
                            <td>
                                <a href="<?= URLBASE ?>/admin/radio/edit.php?id=<?= $p['id'] ?>" class="btn btn-sm btn-outline-primary mr-1">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <button class="btn btn-sm btn-outline-danger"
                                        onclick="confirmDelete(<?= $p['id'] ?>, '<?= htmlspecialchars($p['title'], ENT_QUOTES) ?>')">
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
<script>
function confirmDelete(id, title) {
    Swal.fire({
        title: '¿Eliminar "' + title + '"?',
        text: 'Se eliminará también su programación asociada.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonText: 'Cancelar',
        confirmButtonText: 'Eliminar'
    }).then(function(result) {
        if (result.isConfirmed) {
            fetch('<?= URLBASE ?>/admin/radio/delete.php', {
                method: 'POST',
                headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                body: 'id=' + id
            })
            .then(r => r.json())
            .then(data => {
                if (data.success) location.reload();
                else Swal.fire('Error', data.msg, 'error');
            });
        }
    });
}
</script>
<?php include('../inc/menu-footer.php'); ?>
<?php include('../inc/flash_simple.php'); ?>
</body>
</html>
