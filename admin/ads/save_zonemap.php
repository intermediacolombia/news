<?php
// admin/publicidad/save_zonemap.php
require_once __DIR__ . '/../../inc/config.php';
require_once __DIR__ . '/../login/session.php';
header('Content-Type: application/json');

if (empty($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'No autorizado']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);
if (!$data) {
    echo json_encode(['success' => false, 'message' => 'Datos inválidos']);
    exit;
}

// Guardar en system_settings como JSON
$stmt = db()->prepare("INSERT INTO system_settings (setting_name, value, enabled)
    VALUES ('ads_zone_map', :value, 1)
    ON DUPLICATE KEY UPDATE value = VALUES(value), updated_at = CURRENT_TIMESTAMP");

$stmt->execute([':value' => json_encode($data, JSON_UNESCAPED_UNICODE)]);

// Limpiar cache
$cacheFile = sys_get_temp_dir() . '/ads_zonemap_' . md5(URLBASE) . '.json';
if (file_exists($cacheFile)) unlink($cacheFile);

echo json_encode(['success' => true, 'message' => 'Mapa de zonas guardado.']);