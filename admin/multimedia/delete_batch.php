<?php
require_once __DIR__ . '/../../inc/config.php';

$cookieDomain = str_replace(['https://','http://'], '', $url);
session_set_cookie_params([
    'lifetime' => 365 * 24 * 60 * 60,
    'path'     => '/',
    'domain'   => $cookieDomain,
    'secure'   => true,
    'httponly' => true,
    'samesite' => 'Lax',
]);
ini_set('session.gc_maxlifetime', 365 * 24 * 60 * 60);
session_start();

header('Content-Type: application/json; charset=UTF-8');

if (empty($_SESSION['user']['id'])) {
    echo json_encode(['success' => false, 'message' => 'No autorizado']); exit;
}

$data = json_decode(file_get_contents('php://input'), true);
$ids  = array_filter(array_map('intval', $data['ids'] ?? []));

if (empty($ids)) {
    echo json_encode(['success' => false, 'message' => 'No se recibieron IDs']); exit;
}

try {
    $placeholders = implode(',', array_fill(0, count($ids), '?'));

    // Obtener paths para borrar del disco
    $stmt = db()->prepare("SELECT file_path FROM multimedia WHERE id IN ($placeholders) AND deleted = 0");
    $stmt->execute($ids);
    $paths = $stmt->fetchAll(PDO::FETCH_COLUMN);

    $deleted = 0;
    foreach ($paths as $path) {
        $abs = realpath(__DIR__ . '/../../') . '/' . ltrim($path, '/');
        if ($abs && file_exists($abs)) {
            @unlink($abs);
        }
        $deleted++;
    }

    // Soft delete en BD
    db()->prepare("UPDATE multimedia SET deleted = 1, updated_at = NOW() WHERE id IN ($placeholders)")
        ->execute($ids);
    log_system_action('Eliminar Multimedia', json_encode(['ids' => $ids, 'archivos_eliminados' => $deleted]), 'multimedia');

    echo json_encode([
        'success' => true,
        'message' => $deleted . ' archivo(s) eliminado(s) correctamente.',
    ]);

} catch (Throwable $e) {
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
}
