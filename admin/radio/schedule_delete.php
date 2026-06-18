<?php
require_once __DIR__ . '/../../inc/config.php';
require_once __DIR__ . '/../login/session.php';
header('Content-Type: application/json');

if (!isset($_SESSION['user_permissions']) || !in_array('Gestionar Radio', $_SESSION['user_permissions'])) {
    echo json_encode(['success' => false, 'msg' => 'Sin permisos']);
    exit;
}

$id = (int)($_POST['id'] ?? 0);
if (!$id) { echo json_encode(['success' => false, 'msg' => 'ID inválido']); exit; }

try {
    db()->prepare("DELETE FROM schedules WHERE id = ?")->execute([$id]);
    echo json_encode(['success' => true]);
} catch (Throwable $e) {
    echo json_encode(['success' => false, 'msg' => 'Error al eliminar']);
}
