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
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'No autorizado']);
    exit;
}

$data    = json_decode(file_get_contents('php://input'), true);
$id      = isset($data['id']) && $data['id'] ? (int)$data['id'] : null;
$path    = trim($data['path']     ?? '');
$alt     = trim($data['alt_text'] ?? '');
$caption = trim($data['caption']  ?? '');

if (!$id && !$path) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'ID o ruta requeridos']);
    exit;
}

try {
    if ($id) {
        // Actualizar por ID
        db()->prepare("UPDATE multimedia 
                       SET alt_text = ?, caption = ?, updated_at = NOW() 
                       WHERE id = ? AND deleted = 0")
            ->execute([$alt, $caption, $id]);
    } else {
        // Actualizar por file_path (desde selector de biblioteca en blog)
        db()->prepare("UPDATE multimedia 
                       SET alt_text = ?, caption = ?, updated_at = NOW() 
                       WHERE file_path = ? AND deleted = 0")
            ->execute([$alt, $caption, $path]);
    }

    echo json_encode(['success' => true, 'message' => 'Metadata actualizada correctamente.']);

} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
}