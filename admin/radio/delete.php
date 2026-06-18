<?php
require_once __DIR__ . '/../../inc/config.php';
require_once __DIR__ . '/../login/session.php';
header('Content-Type: application/json');

if (!isset($_SESSION['user_permissions']) || !in_array('Gestionar Radio', $_SESSION['user_permissions'])) {
    echo json_encode(['ok' => false, 'msg' => 'Sin permisos']);
    exit;
}

$id = (int)($_POST['id'] ?? 0);
if (!$id) { echo json_encode(['ok' => false, 'msg' => 'ID inválido']); exit; }

try {
    $stmt = db()->prepare("SELECT image FROM programs WHERE id = ?");
    $stmt->execute([$id]);
    $p = $stmt->fetch();
    if ($p && $p['image'] && file_exists(__DIR__ . '/../../' . $p['image'])) {
        @unlink(__DIR__ . '/../../' . $p['image']);
    }
    db()->prepare("DELETE FROM programs WHERE id = ?")->execute([$id]);
    log_system_action('Eliminar Programa', 'ID: ' . $id, 'programs', $id);
    echo json_encode(['ok' => true]);
} catch (Throwable $e) {
    echo json_encode(['ok' => false, 'msg' => 'Error al eliminar']);
}
