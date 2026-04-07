<?php
require_once __DIR__ . '/../../inc/config.php';
require_once __DIR__ . '/../login/session.php';

$permisopage = 'Ver Logs';
require_once __DIR__ . '/../login/restriction.php';

try {
    $stmt = db()->prepare("DELETE FROM system_logs WHERE created_at < DATE_SUB(NOW(), INTERVAL 30 DAY)");
    $stmt->execute();
    $deleted = $stmt->rowCount();
    
    echo json_encode(['success' => true, 'message' => "Se eliminaron {$deleted} registros"]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
}
