<?php
require_once __DIR__ . '/../../inc/config.php';
require_once __DIR__ . '/../inc/flash_helpers.php';

function slugify($text) {
    $text = iconv('UTF-8', 'ASCII//TRANSLIT', $text);
    $text = preg_replace('~[^\\pL\d]+~u', '-', $text);
    $text = trim($text, '-');
    $text = strtolower($text);
    $text = preg_replace('~[^-\w]+~', '', $text);
    return $text ?: 'n-a';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id          = (int)($_POST['id'] ?? 0);
    $name        = trim($_POST['name'] ?? '');
    $slug        = trim($_POST['slug'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $status      = $_POST['status'] ?? 'active';

    if ($name === '') {
        flash_set('danger', 'Error', 'El nombre es obligatorio.');
        header("Location: category_edit.php?id=".$id);
        exit;
    }

    if ($slug === '') {
        $slug = slugify($name);
    }

    try {
        $stmt = db()->prepare("UPDATE blog_categories SET name=?, slug=?, description=?, status=? WHERE id=?");
        $stmt->execute([$name, $slug, $description, $status, $id]);

        flash_set('success', '¡Éxito!', 'Categoría actualizada correctamente.');
        header("Location: categories.php");
        exit;
    } catch (Throwable $e) {
        flash_set('danger', 'Error', 'Error al actualizar: ' . $e->getMessage());
        header("Location: category_edit.php?id=".$id);
        exit;
    }
}

