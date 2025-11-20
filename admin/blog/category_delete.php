<?php
require_once __DIR__ . '/../../inc/config.php';

require_once __DIR__ . '/../login/session.php';  // Inicia la sesión y carga la información del usuario
$permisopage = 'Borrar Categorias';
require_once __DIR__ . '/../login/restriction.php';
session_start();

require_once __DIR__ . '/../inc/flash_helpers.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = (int)($_POST['id'] ?? 0);

    if ($id > 0) {
        try {
            // Marcamos como eliminada en lugar de borrar físicamente
            $st = db()->prepare("UPDATE blog_categories SET deleted = 1 WHERE id = ?");
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
