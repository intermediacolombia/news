<?php
require_once('../login/session.php');

$permisopage = 'Ver y Editar Usuarios';
require_once('../login/restriction.php');

// Validar que exista el ID
if (!isset($_GET['id'])) {
    $_SESSION['error'] = "ID no proporcionado.";
    header("Location: {$url}/admin/users");
    exit();
}

$id = intval($_GET['id']);

// Validar que el ID sea válido
if ($id <= 0) {
    $_SESSION['error'] = "ID inválido.";
    header("Location: {$url}/admin/users");
    exit();
}

try {
    $pdo = db();
    
    // Verificar si el usuario existe y no está borrado
    $stmt = $pdo->prepare("SELECT id, borrado FROM usuarios WHERE id = :id");
    $stmt->execute([':id' => $id]);
    $usuario = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$usuario) {
        $_SESSION['error'] = "Usuario no encontrado.";
    } elseif ($usuario['borrado'] == 1) {
        $_SESSION['error'] = "El usuario ya estaba borrado.";
    } else {
        // Marcar como borrado
        $stmt = $pdo->prepare("UPDATE usuarios SET borrado = 1 WHERE id = :id");
        $stmt->execute([':id' => $id]);
        
        $_SESSION['success'] = "Usuario borrado exitosamente.";
    }
} catch (PDOException $e) {
    error_log("Error al borrar usuario {$id}: " . $e->getMessage());
    $_SESSION['error'] = "Error al borrar el usuario. Por favor, intente nuevamente.";
}

header("Location: {$url}/admin/users");
exit();
?>