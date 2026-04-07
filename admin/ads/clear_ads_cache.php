<?php
require_once __DIR__ . '/../../inc/config.php';
require_once __DIR__ . '/../login/session.php';
header('Content-Type: application/json');
if (empty($_SESSION['user_id'])) { echo json_encode(['success'=>false]); exit; }
$f = sys_get_temp_dir() . '/ads_zonemap_' . md5(URLBASE) . '.json';
if (file_exists($f)) unlink($f);
log_system_action('clear_ads_cache', 'Limipió caché de publicidad', 'system_settings');
echo json_encode(['success' => true, 'message' => 'Caché limpiado correctamente.']);