<?php
// admin/publicidad/clear_ads_cache.php
require_once __DIR__ . '/../../inc/config.php';
header('Content-Type: application/json');

$cacheFile = sys_get_temp_dir() . '/ads_zonemap_' . md5(URLBASE) . '.json';
if (file_exists($cacheFile)) unlink($cacheFile);

echo json_encode(['success' => true, 'message' => 'Mapa de zonas regenerado.']);