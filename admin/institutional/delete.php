<?php
require_once __DIR__ . '/../../inc/config.php';
require_once __DIR__ . '/../login/session.php';
$permisopage = 'Eliminar Institucional';
require_once __DIR__ . '/../login/restriction.php';

header('Content-Type: application/json');

if($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Método no permitido']);
    exit;
}

$id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);

if(!$id) {
    echo json_encode(['success' => false, 'message' => 'ID inválido']);
    exit;
}

// Obtener datos de la página para eliminar imagen
$sql = "SELECT image FROM institutional_pages WHERE id = ?";
$stmt = db()->prepare($sql);
$stmt->execute([$id]);
$page = $stmt->fetch();

if(!$page) {
    echo json_encode(['success' => false, 'message' => 'Página no encontrada']);
    exit;
}

// Eliminar página
$deleteSql = "DELETE FROM institutional_pages WHERE id = ?";
$deleteStmt = db()->prepare($deleteSql);

if($deleteStmt->execute([$id])) {
    // Eliminar imagen física si existe
    if(!empty($page['image'])) {
        $imagePath = __DIR__ . '/../../' . $page['image'];
        if(file_exists($imagePath)) {
            @unlink($imagePath);
        }
    }
    
    $_SESSION['success'] = 'Página eliminada correctamente';
    echo json_encode(['success' => true, 'message' => 'Página eliminada correctamente']);
} else {
    echo json_encode(['success' => false, 'message' => 'Error al eliminar la página']);
}