<?php
// admin/publicidad/save_zonemap.php
require_once __DIR__ . '/../../inc/config.php';

// Iniciar sesión con los mismos parámetros que session.php
$cookieDomain  = str_replace(['https://','http://'], '', $url);
$tiempoUnAno   = 365 * 24 * 60 * 60;
session_set_cookie_params([
    'lifetime' => $tiempoUnAno,
    'path'     => '/',
    'domain'   => $cookieDomain,
    'secure'   => true,
    'httponly' => true,
    'samesite' => 'Lax'
]);
ini_set('session.gc_maxlifetime', $tiempoUnAno);
session_start();

header('Content-Type: application/json; charset=UTF-8');

// Verificar sesión directamente — sin session.php
if (empty($_SESSION['user']['id'])) {
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

    echo json_encode(['success' => true, 'message' => 'Mapa de zonas guardado correctamente.']);

} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
}