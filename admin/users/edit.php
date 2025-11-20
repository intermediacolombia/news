<?php include('../login/session.php');?>
<?php 
$permisopage = 'Ver y Editar Usuarios';
include('../login/restriction.php');?>
<?php
session_start();

if (!isset($_GET['id'])) {
    $_SESSION['error'] = "ID de usuario no proporcionado.";
    header("Location: $url/admin/users");
    exit();
}

$id = intval($_GET['id']);

// Datos de conexión a la base de datos
include('../../inc/config.php');
try {
	// Obtener los datos del usuario por ID
    $stmt = db()->prepare("SELECT * FROM usuarios WHERE id = :id LIMIT 1");
    $stmt->execute([':id' => $id]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$user) {
        $_SESSION['error'] = "Usuario no encontrado.";
        header("Location: $url/admin/users");
        exit();
    }
} catch (PDOException $e) {
    $_SESSION['error'] = "Error de conexión: " . $e->getMessage();
    header("Location: $url/admin/users");
    exit();
}
?>





