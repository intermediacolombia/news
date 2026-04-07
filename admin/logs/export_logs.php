<?php
require_once __DIR__ . '/../inc/config.php';
require_once __DIR__ . '/login/session.php';

$permisopage = 'Ver Logs';
require_once __DIR__ . '/login/restriction.php';

$filters = [];
$params = [];

if (!empty($_GET['action'])) {
    $filters[] = "action LIKE :action";
    $params[':action'] = '%' . $_GET['action'] . '%';
}

if (!empty($_GET['username'])) {
    $filters[] = "username LIKE :username";
    $params[':username'] = '%' . $_GET['username'] . '%';
}

if (!empty($_GET['entity_type'])) {
    $filters[] = "entity_type = :entity_type";
    $params[':entity_type'] = $_GET['entity_type'];
}

if (!empty($_GET['date_from'])) {
    $filters[] = "created_at >= :date_from";
    $params[':date_from'] = $_GET['date_from'] . ' 00:00:00';
}

if (!empty($_GET['date_to'])) {
    $filters[] = "created_at <= :date_to";
    $params[':date_to'] = $_GET['date_to'] . ' 23:59:59';
}

$whereClause = !empty($filters) ? 'WHERE ' . implode(' AND ', $filters) : '';

$sql = "SELECT * FROM system_logs {$whereClause} ORDER BY created_at DESC LIMIT 10000";
$stmt = db()->prepare($sql);
foreach ($params as $key => $value) {
    $stmt->bindValue($key, $value);
}
$stmt->execute();
$logs = $stmt->fetchAll();

header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename=system_logs_' . date('Y-m-d') . '.csv');

$output = fopen('php://output', 'w');
fputcsv($output, ['ID', 'Fecha', 'Usuario', 'Acción', 'Descripción', 'Tipo Entidad', 'ID Entidad', 'IP', 'User Agent'], ';');

foreach ($logs as $log) {
    fputcsv($output, [
        $log['id'],
        $log['created_at'],
        $log['username'],
        $log['action'],
        $log['description'],
        $log['entity_type'],
        $log['entity_id'],
        $log['ip_address'],
        $log['user_agent']
    ], ';');
}

fclose($output);
