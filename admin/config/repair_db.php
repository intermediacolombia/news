<?php
/**
 * Endpoint AJAX — reparar BD desde el panel de administración.
 * Llamado por: admin/config/tabs/tab_sistema.php via fetch POST.
 */
require_once __DIR__ . '/../../inc/config.php';
require_once __DIR__ . '/../login/session.php';
require_once __DIR__ . '/../inc/db_repair.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || ($_POST['action'] ?? '') !== 'repair') {
    echo json_encode(['error' => 'Acción no válida']);
    exit;
}

echo json_encode(repair_database());
exit;
