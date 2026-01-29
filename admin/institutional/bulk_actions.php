<?php
require_once __DIR__ . '/../../inc/config.php';
require_once __DIR__ . '/../../inc/db.php';
require_once __DIR__ . '/../login/session.php';
$permisopage = 'Editar Institucional';
require_once __DIR__ . '/../login/restriction.php';

header('Content-Type: application/json');

if($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Método no permitido']);
    exit;
}

$action = $_POST['action'] ?? '';
$ids = $_POST['ids'] ?? [];

if(empty($ids) || !is_array($ids)) {
    echo json_encode(['success' => false, 'message' => 'No se seleccionaron elementos']);
    exit;
}

// Sanitizar IDs
$ids = array_map('intval', $ids);
$ids = array_filter($ids);

if(empty($ids)) {
    echo json_encode(['success' => false, 'message' => 'IDs inválidos']);
    exit;
}

$placeholders = implode(',', array_fill(0, count($ids), '?'));
$success = false;
$message = '';

switch($action) {
    case 'delete':
        // Obtener imágenes antes de eliminar
        $sqlImages = "SELECT image FROM institutional_pages WHERE id IN ($placeholders)";
        $stmtImages = $conn->prepare($sqlImages);
        $stmtImages->bind_param(str_repeat('i', count($ids)), ...$ids);
        $stmtImages->execute();
        $resultImages = $stmtImages->get_result();
        $images = [];
        while($row = $resultImages->fetch_assoc()) {
            if(!empty($row['image'])) {
                $images[] = $row['image'];
            }
        }
        
        // Eliminar páginas
        $sql = "DELETE FROM institutional_pages WHERE id IN ($placeholders)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param(str_repeat('i', count($ids)), ...$ids);
        
        if($stmt->execute()) {
            // Eliminar imágenes físicas
            foreach($images as $image) {
                $imagePath = __DIR__ . '/../../' . $image;
                if(file_exists($imagePath)) {
                    @unlink($imagePath);
                }
            }
            
            $success = true;
            $message = count($ids) . ' página(s) eliminada(s) correctamente';
            $_SESSION['success'] = $message;
        } else {
            $message = 'Error al eliminar las páginas';
        }
        break;
        
    case 'draft':
        $sql = "UPDATE institutional_pages SET status = 'draft', updated_at = NOW() WHERE id IN ($placeholders)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param(str_repeat('i', count($ids)), ...$ids);
        
        if($stmt->execute()) {
            $success = true;
            $message = count($ids) . ' página(s) marcada(s) como borrador';
            $_SESSION['success'] = $message;
        } else {
            $message = 'Error al actualizar las páginas';
        }
        break;
        
    case 'publish':
        $sql = "UPDATE institutional_pages SET status = 'published', updated_at = NOW() WHERE id IN ($placeholders)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param(str_repeat('i', count($ids)), ...$ids);
        
        if($stmt->execute()) {
            $success = true;
            $message = count($ids) . ' página(s) publicada(s) correctamente';
            $_SESSION['success'] = $message;
        } else {
            $message = 'Error al publicar las páginas';
        }
        break;
        
    default:
        $message = 'Acción no válida';
}

echo json_encode([
    'success' => $success,
    'message' => $message
]);