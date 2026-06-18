<?php
require_once __DIR__ . '/../../inc/config.php';
require_once __DIR__ . '/../login/session.php';
$permisopage = 'Gestionar Radio';
include('../login/restriction.php');
require_once __DIR__ . '/../inc/flash_helpers.php';

$programs = db()->query("SELECT id, title FROM programs WHERE status='active' ORDER BY title ASC")->fetchAll();
$days = ['lunes','martes','miercoles','jueves','viernes','sabado','domingo'];
$dayLabels = ['lunes'=>'Lunes','martes'=>'Martes','miercoles'=>'Miércoles','jueves'=>'Jueves','viernes'=>'Viernes','sabado'=>'Sábado','domingo'=>'Domingo'];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['program_id'])) {
    $programId = (int)$_POST['program_id'];
    $day       = in_array($_POST['day_of_week'] ?? '', $days) ? $_POST['day_of_week'] : null;
    $start     = $_POST['start_time'] ?? '';
    $end       = $_POST['end_time']   ?? '';
    $host      = trim($_POST['host']  ?? '');
    $errors    = [];

    if (!$programId) $errors[] = 'Selecciona un programa';
    if (!$day)       $errors[] = 'Selecciona un día';
    if (!$start)     $errors[] = 'Hora de inicio requerida';
    if (!$end)       $errors[] = 'Hora de fin requerida';
    if ($start && $end && $start >= $end) $errors[] = 'La hora de fin debe ser mayor a la de inicio';

    if (empty($errors)) {
        $stmt = db()->prepare("INSERT INTO schedules (program_id, day_of_week, start_time, end_time, host, status) VALUES (?,?,?,?,?,?)");
        $stmt->execute([$programId, $day, $start, $end, mb_substr($host,0,255), 'active']);
        setFlash('success', 'Slot agregado');
    } else {
        setFlash('error', implode('<br>', $errors));
    }
    header('Location: ' . URLBASE . '/admin/radio/schedules.php');
    exit;
}

$allSlots = [];
try {
    $rows = db()->query("
        SELECT s.*, p.title AS program_title
        FROM schedules s
        INNER JOIN programs p ON p.id = s.program_id
        ORDER BY FIELD(s.day_of_week,'lunes','martes','miercoles','jueves','viernes','sabado','domingo'), s.start_time
    ")->fetchAll();
    foreach ($rows as $row) {
        $allSlots[$row['day_of_week']][] = $row;
    }
} catch (Throwable $e) {}
?>
<!doctype html>
<html lang="es">
<head>
<meta charset="utf-8">
<title>Programación</title>
<?php include('../inc/header.php'); ?>
</head>
<body>
<?php include('../inc/menu.php'); ?>
<div class="main-content">
    <div class="container-fluid">
        <h1 class="h3 mb-4">Parrilla de Programación</h1>
        <?php renderFlashMessages(); ?>

        <div class="card shadow-sm mb-4">
            <div class="card-header"><strong>Agregar slot</strong></div>
            <div class="card-body">
                <form method="POST">
                    <div class="row">
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>Programa *</label>
                                <select name="program_id" class="form-control" required>
                                    <option value="">— Seleccionar —</option>
                                    <?php foreach ($programs as $p): ?>
                                    <option value="<?= $p['id'] ?>"><?= htmlspecialchars($p['title']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group">
                                <label>Día *</label>
                                <select name="day_of_week" class="form-control" required>
                                    <option value="">— Día —</option>
                                    <?php foreach ($days as $d): ?>
                                    <option value="<?= $d ?>"><?= $dayLabels[$d] ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group">
                                <label>Inicio *</label>
                                <input type="time" name="start_time" class="form-control" required>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group">
                                <label>Fin *</label>
                                <input type="time" name="end_time" class="form-control" required>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group">
                                <label>Conductor</label>
                                <input type="text" name="host" class="form-control" placeholder="Opcional">
                            </div>
                        </div>
                        <div class="col-md-1 d-flex align-items-end">
                            <div class="form-group">
                                <button type="submit" class="btn btn-primary"><i class="fas fa-plus"></i></button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <?php foreach ($days as $day): ?>
        <div class="card shadow-sm mb-3">
            <div class="card-header d-flex justify-content-between">
                <strong><?= $dayLabels[$day] ?></strong>
                <span class="badge badge-secondary"><?= count($allSlots[$day] ?? []) ?> slots</span>
            </div>
            <?php if (!empty($allSlots[$day])): ?>
            <div class="card-body p-0">
                <table class="table table-sm table-hover mb-0">
                    <thead class="thead-light">
                        <tr><th>Inicio</th><th>Fin</th><th>Programa</th><th>Conductor</th><th>Estado</th><th></th></tr>
                    </thead>
                    <tbody>
                    <?php foreach ($allSlots[$day] as $slot): ?>
                    <tr>
                        <td><?= substr($slot['start_time'],0,5) ?></td>
                        <td><?= substr($slot['end_time'],0,5) ?></td>
                        <td><?= htmlspecialchars($slot['program_title']) ?></td>
                        <td><?= htmlspecialchars($slot['host'] ?? '') ?></td>
                        <td><span class="badge badge-<?= $slot['status']==='active'?'success':'secondary' ?>"><?= $slot['status'] === 'active' ? 'Activo' : 'Inactivo' ?></span></td>
                        <td>
                            <button class="btn btn-sm btn-outline-danger"
                                    onclick="delSlot(<?= $slot['id'] ?>)">
                                <i class="fas fa-trash"></i>
                            </button>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php else: ?>
            <div class="card-body text-muted text-center py-3">Sin slots este día</div>
            <?php endif; ?>
        </div>
        <?php endforeach; ?>
    </div>
</div>
<script>
function delSlot(id) {
    Swal.fire({
        title: '¿Eliminar slot?',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonText: 'Cancelar',
        confirmButtonText: 'Eliminar'
    }).then(function(r) {
        if (r.isConfirmed) {
            fetch('<?= URLBASE ?>/admin/radio/schedule_delete.php', {
                method: 'POST',
                headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                body: 'id=' + id
            }).then(r => r.json()).then(data => {
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
