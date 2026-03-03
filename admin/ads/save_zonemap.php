<?php
// admin/publicidad/save_zonemap.php
session_start(); // ← PRIMERO, antes de todo

require_once __DIR__ . '/../../inc/config.php';
require_once __DIR__ . '/../login/session.php';

header('Content-Type: application/json; charset=UTF-8');

if (empty($_SESSION['user_id'])) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'No autorizado']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);
if (!$data) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Datos inválidos']);
    exit;
}

try {
    $stmt = db()->prepare("INSERT INTO system_settings (setting_name, value, enabled)
        VALUES ('ads_zone_map', :value, 1)
        ON DUPLICATE KEY UPDATE value = VALUES(value), updated_at = CURRENT_TIMESTAMP");

    $stmt->execute([':value' => json_encode($data, JSON_UNESCAPED_UNICODE)]);

    // Limpiar cache
    $cacheFile = sys_get_temp_dir() . '/ads_zonemap_' . md5(URLBASE) . '.json';
    if (file_exists($cacheFile)) unlink($cacheFile);

    // Recargar SYS_SETTINGS para que la próxima petición ya tenga el nuevo mapa
    $GLOBALS['SYS_SETTINGS']['ads_zone_map'] = json_encode($data, JSON_UNESCAPED_UNICODE);

    echo json_encode(['success' => true, 'message' => 'Mapa de zonas guardado correctamente.']);

} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Error al guardar: ' . $e->getMessage()]);
}