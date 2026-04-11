<?php
/**
 * Moderar comentarios desde panel admin
 * Acciones: approve, hide, delete
 */
require_once __DIR__ . '/../inc/config.php';
require_once __DIR__ . '/../admin/inc/auth.php';

header('Content-Type: application/json');

// Require admin authentication
if (!is_logged_in()) {
    echo json_encode(['success' => false, 'message' => 'No autorizado']);
    exit;
}

// Check permission: 25 = Gestionar Comentarios
if (!has_permission(25) && !has_role('admin')) {
    echo json_encode(['success' => false, 'message' => 'Sin permisos suficientes']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Método no permitido']);
    exit;
}

$action = $_POST['action'] ?? '';
$commentId = (int)($_POST['comment_id'] ?? 0);

if (!$commentId || !in_array($action, ['approve', 'hide', 'delete'])) {
    echo json_encode(['success' => false, 'message' => 'Datos inválidos']);
    exit;
}

try {
    switch ($action) {
        case 'approve':
            $stmt = db()->prepare("UPDATE comments SET estado = 'approved' WHERE id = ?");
            $stmt->execute([$commentId]);
            log_system_action('approve_comment', "Comentario #$commentId aprobado");
            echo json_encode(['success' => true, 'message' => 'Comentario aprobado']);
            break;
            
        case 'hide':
            $stmt = db()->prepare("UPDATE comments SET estado = 'hidden' WHERE id = ?");
            $stmt->execute([$commentId]);
            log_system_action('hide_comment', "Comentario #$commentId ocultado");
            echo json_encode(['success' => true, 'message' => 'Comentario ocultado']);
            break;
            
        case 'delete':
            // Soft delete
            $stmt = db()->prepare("UPDATE comments SET borrado = 1 WHERE id = ?");
            $stmt->execute([$commentId]);
            log_system_action('delete_comment', "Comentario #$commentId eliminado");
            echo json_encode(['success' => true, 'message' => 'Comentario eliminado']);
            break;
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Error al procesar: ' . $e->getMessage()]);
}
