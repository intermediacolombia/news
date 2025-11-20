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
    $name        = trim($_POST['name'] ?? '');
    $slug        = trim($_POST['slug'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $status      = $_POST['status'] ?? 'active';

    if ($name === '') {
        flash_set('danger', 'Error', 'El nombre es obligatorio.');
        $_SESSION['old'] = $_POST;
        header("Location: category_create.php");
        exit;
    }

    if ($slug === '') {
        $slug = slugify($name);
    }

    try {
        $stmt = db()->prepare("INSERT INTO blog_categories (name, slug, description, status) VALUES (?,?,?,?)");
        $stmt->execute([$name, $slug, $description, $status]);

        flash_set('success', '¡Éxito!', 'Categoría creada correctamente.');
        header("Location: categories.php");
        exit;
    } catch (Throwable $e) {
        flash_set('danger', 'Error', 'Error al guardar: ' . $e->getMessage());
        $_SESSION['old'] = $_POST;
        header("Location: category_create.php");
        exit;
    }
}


