<?php
require_once __DIR__ . '/../../inc/config.php';
require_once __DIR__ . '/../login/session.php';
require_once __DIR__ . '/../inc/flash_helpers.php';

/* ========= Método válido ========= */
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    die("Método inválido.");
}

/* ========= Forzar UTF-8/utf8mb4 en salida y conexión ========= */
if (!headers_sent()) {
    header('Content-Type: text/html; charset=UTF-8');
}
ini_set('default_charset', 'UTF-8');
if (function_exists('mb_internal_encoding')) {
    mb_internal_encoding('UTF-8');
}
if (isset($pdo) && $pdo instanceof PDO) {
    $pdo->exec("SET NAMES utf8mb4");
    $pdo->exec("SET CHARACTER SET utf8mb4");
    // Recomendado en MySQL 5.7/8.0 para idiomas latinos:
    $pdo->exec("SET SESSION collation_connection = utf8mb4_unicode_ci");
}

/* ========= ID ========= */
$id = (int)($_POST['id'] ?? 0);
if ($id <= 0) {
    die("ID inválido.");
}

/* ========= Datos del formulario ========= */
$title     = trim($_POST['title'] ?? '');
$slug      = trim($_POST['slug'] ?? '');
$content   = $_POST['content'] ?? ''; // RAW (no tocar para emojis/HTML)
$status    = $_POST['status'] ?? 'draft';
$cats      = $_POST['categories'] ?? [];

// Campos SEO (RAW)
$seoTitle       = $_POST['seo_title'] ?? '';
$seoDescription = $_POST['seo_description'] ?? '';
$seoKeywords    = $_POST['seo_keywords'] ?? '';

/* ========= Validaciones básicas ========= */
$errors = [];
if ($title === '') {
    $errors['title'] = "El título es obligatorio.";
}
if (trim($content) === '') {
    $errors['content'] = "El contenido es obligatorio.";
}

/* ========= Traer post existente ========= */
$stmt = $pdo->prepare("SELECT * FROM blog_posts WHERE id=? AND deleted=0 LIMIT 1");
$stmt->execute([$id]);
$post = $stmt->fetch();
if (!$post) {
    die("Entrada no encontrada.");
}

/* ========= Slug: generar si viene vacío ========= */
if ($slug === '') {
    // Genera un slug básico desde título
    $slug = strtolower(trim(preg_replace('/[^a-z0-9]+/i', '-', $title), '-'));
}
// Normalización adicional
$slug = preg_replace('/-+/', '-', $slug);
$slug = trim($slug, '-');

/* ========= Slug único (excluyendo el propio ID) ========= */
$stSlug = $pdo->prepare("SELECT COUNT(*) FROM blog_posts WHERE slug=? AND id<>? AND deleted=0");
$stSlug->execute([$slug, $id]);
if ((int)$stSlug->fetchColumn() > 0) {
    $errors['slug'] = "El slug ya existe, elige otro.";
}

/* ========= Si hay errores, redirige a edición ========= */
if (!empty($errors)) {
    $_SESSION['errors'] = $errors;
    $_SESSION['old']    = $_POST;
    header("Location: edit.php?id=".$id);
    exit;
}

/* ========= Imagen destacada ========= */
$imagePath = $post['image']; // por defecto conserva la actual
if (!empty($_FILES['image']['tmp_name']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
    $file = $_FILES['image'];
    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

    $allowed = ['jpg','jpeg','png','webp','gif'];
    if (!in_array($ext, $allowed, true)) {
        $_SESSION['errors'] = ['image' => "Formato no válido. Solo: ".implode(', ', $allowed)."."];
        $_SESSION['old']    = $_POST;
        header("Location: edit.php?id=".$id);
        exit;
    }
    if ($file['size'] > 5 * 1024 * 1024) { // 5MB
        $_SESSION['errors'] = ['image' => "La imagen supera los 5MB."];
        $_SESSION['old']    = $_POST;
        header("Location: edit.php?id=".$id);
        exit;
    }

    $uploadDir = realpath(__DIR__ . '/../../public/images') . '/blog/';
    if (!is_dir($uploadDir)) {
        @mkdir($uploadDir, 0755, true);
    }

    $safeName = preg_replace('/[^a-zA-Z0-9_\.-]/', '_', basename($file['name']));
    $fileName = time() . '_' . $safeName;
    $dest     = $uploadDir . $fileName;

    if (!move_uploaded_file($file['tmp_name'], $dest)) {
        $_SESSION['errors'] = ['image' => "No se pudo guardar la imagen."];
        $_SESSION['old']    = $_POST;
        header("Location: edit.php?id=".$id);
        exit;
    }

    // Borrar la anterior si existía físicamente
    if (!empty($post['image'])) {
        $oldAbs = realpath(__DIR__ . '/../../') . '/' . ltrim($post['image'], '/');
        if ($oldAbs && file_exists($oldAbs)) {
            @unlink($oldAbs);
        }
    }

    $imagePath = 'public/images/blog/' . $fileName;
}

/* ========= Actualizar blog_posts (RAW para content/SEO) ========= */
$sql = "UPDATE blog_posts 
        SET title=?, slug=?, content=?, image=?, status=?, 
            seo_title=?, seo_description=?, seo_keywords=?, 
            updated_at=NOW()
        WHERE id=?";
$stmt = $pdo->prepare($sql);
$stmt->execute([
    $title,
    $slug,
    $content,        // RAW (emojis/HTML)
    $imagePath,
    $status,
    $seoTitle,       // RAW
    $seoDescription, // RAW
    $seoKeywords,    // RAW
    $id
]);

/* ========= Actualizar categorías ========= */
$pdo->prepare("DELETE FROM blog_post_category WHERE post_id=?")->execute([$id]);
if (!empty($cats) && is_array($cats)) {
    $ins = $pdo->prepare("INSERT INTO blog_post_category (post_id, category_id) VALUES (?, ?)");
    foreach ($cats as $catId) {
        $ins->execute([$id, (int)$catId]);
    }
}

/* ========= Éxito ========= */
flash_set('success', '¡Entrada actualizada!', 'Se guardaron los cambios correctamente.');
header("Location: index.php");
exit;
