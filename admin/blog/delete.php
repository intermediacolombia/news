<?php
require_once __DIR__ . '/../../inc/config.php';


require_once __DIR__ . '/../login/session.php';  // Inicia la sesión y carga la información del usuario
$permisopage = 'Borrar Entrada';
require_once __DIR__ . '/../login/restriction.php';
session_start();


require_once __DIR__ . '/../inc/flash_helpers.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    die("Método inválido.");
}

$id = (int)($_POST['id'] ?? 0);
if ($id <= 0) {
    die("ID inválido.");
}

// 1) Verificar si existe
$stmt = $pdo->prepare("SELECT * FROM blog_posts WHERE id=? AND deleted=0 LIMIT 1");
$stmt->execute([$id]);
$post = $stmt->fetch();

if (!$post) {
    flash_set('error', 'Error', 'La entrada no existe o ya fue eliminada.');
    header("Location: index.php");
    exit;
}

// 2) Borrado lógico
$pdo->prepare("UPDATE blog_posts SET deleted=1, updated_at=NOW() WHERE id=?")->execute([$id]);

// 3) Opcional: limpiar relaciones en blog_post_category
$pdo->prepare("DELETE FROM blog_post_category WHERE post_id=?")->execute([$id]);

// 4) Mensaje de éxito
flash_set('success', '¡Entrada eliminada!', 'La entrada del blog fue eliminada correctamente.');
header("Location: index.php");
exit;
