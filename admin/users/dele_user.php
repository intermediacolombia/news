<?php include('../login/session.php');?>
<?php 
$permisopage = 'Ver y Editar Usuarios';
include('../login/restriction.php');?>
<?php
session_start();
if(!isset($_GET['id'])) {
    $_SESSION['error'] = "ID no proporcionado.";
    header("Location: $url$url/admin/users");
    exit();
}

$id = intval($_GET['id']);

// Datos de conexión
include('../../inc/config.php');
try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $dbuser, $dbpass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Actualizar el campo "borrado" a 1 para el usuario con el id indicado
    $stmt = $pdo->prepare("UPDATE usuarios SET borrado = 1 WHERE id = :id");
    $stmt->execute([':id' => $id]);
    
    if($stmt->rowCount() > 0) {
        $_SESSION['success'] = "Usuario borrado exitosamente.";
    } else {
        $_SESSION['error'] = "No se encontró el usuario o ya estaba borrado.";
    }
} catch (PDOException $e) {
    $_SESSION['error'] = "Error al borrar: " . $e->getMessage();
}
header("Location: $url/admin/users");
exit();
?>
