<?php
require_once __DIR__ . '/../../inc/config.php';
require_once __DIR__ . '/../login/session.php';
header('Content-Type: application/json; charset=UTF-8');

if (empty($_SESSION['user_permissions']) || !in_array('Editar Entrada', $_SESSION['user_permissions'])) {
    echo json_encode(['success' => false, 'message' => 'Sin permisos']); exit;
}

$ids    = array_filter(array_map('intval', $_POST['ids']    ?? []));
$userId = (int)($_POST['user_id'] ?? 0);

if (empty($ids) || !$userId) {
    echo json_encode(['success' => false, 'message' => 'Datos incompletos']); exit;
}

// Verificar que el usuario destino existe
$userStmt = db()->prepare("SELECT nombre, apellido, username FROM usuarios WHERE id = ? AND borrado = 0 LIMIT 1");
$userStmt->execute([$userId]);
$newUser = $userStmt->fetch(PDO::FETCH_ASSOC);

if (!$newUser) {
    echo json_encode(['success' => false, 'message' => 'Usuario no encontrado']); exit;
}

$newAuthor     = $newUser['nombre'] . ' ' . $newUser['apellido'];
$newAuthorUser = $newUser['username'];

try {
    $placeholders = implode(',', array_fill(0, count($ids), '?'));
    $params       = array_merge([$newAuthor, $newAuthorUser], $ids);

    db()->prepare("UPDATE blog_posts 
                   SET author = ?, author_user = ?, updated_at = NOW()
                   WHERE id IN ($placeholders) AND deleted = 0")
        ->execute($params);

    echo json_encode([
        'success' => true,
        'message' => count($ids) . ' entrada(s) transferidas a ' . $newAuthor . '.',
    ]);

} catch (Throwable $e) {
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
}