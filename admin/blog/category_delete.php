<?php
require_once __DIR__ . '/../../inc/config.php';
require_once __DIR__ . '/../login/session.php';
require_once __DIR__ . '/../inc/flash_helpers.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = (int)($_POST['id'] ?? 0);

    if ($id > 0) {
        try {
            $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $dbuser, $dbpass, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            ]);

            // Marcamos como eliminada en lugar de borrar físicamente
            $st = $pdo->prepare("UPDATE blog_categories SET deleted = 1 WHERE id = ?");
            $st->execute([$id]);

            flash_set("success", "¡Exito!", "Categoría eliminada correctamente.");
        } catch (Throwable $e) {
            flash_set("error", "Error al eliminar: " . $e->getMessage());
        }
    } else {
        flash_set("error", "ID inválido para eliminar.");
    }
} else {
    flash_set("error", "Solicitud inválida.");
}

header("Location: " . $url . "/admin/blog/categories.php");
exit;
