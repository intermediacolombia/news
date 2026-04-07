<?php
require_once __DIR__ . '/../../inc/config.php';
require_once __DIR__ . '/../login/session.php';
$permisopage = 'Editar Entrada';
require_once __DIR__ . '/../login/restriction.php';
require_once __DIR__ . '/../inc/flash_helpers.php';

/* ========= Método válido ========= */
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    die("Método inválido.");
}

/* ========= Forzar UTF-8 ========= */
if (!headers_sent()) header('Content-Type: text/html; charset=UTF-8');
ini_set('default_charset', 'UTF-8');
if (function_exists('mb_internal_encoding')) mb_internal_encoding('UTF-8');
db()->exec("SET NAMES utf8mb4");
db()->exec("SET CHARACTER SET utf8mb4");
db()->exec("SET SESSION collation_connection = utf8mb4_unicode_ci");

/* ========= ID ========= */
$id = (int)($_POST['id'] ?? 0);
if ($id <= 0) die("ID inválido.");

/* ========= Datos del formulario ========= */
$title          = trim($_POST['title']          ?? '');
$slug           = trim($_POST['slug']           ?? '');
$content        = $_POST['content']             ?? '';
$status         = $_POST['status']              ?? 'draft';
$cats           = $_POST['categories']          ?? [];
$seoTitle       = $_POST['seo_title']           ?? '';
$seoDescription = $_POST['seo_description']     ?? '';
$seoKeywords    = $_POST['seo_keywords']        ?? '';
$imageAlt       = trim($_POST['image_alt']       ?? '');
$imageFromLib   = trim($_POST['image_path']     ?? ''); // ← ruta desde biblioteca

/* ========= Validaciones ========= */
$errors = [];
if ($title === '')         $errors['title']   = "El título es obligatorio.";
if (trim($content) === '') $errors['content'] = "El contenido es obligatorio.";

/* ========= Traer post existente ========= */
$stmt = db()->prepare("SELECT * FROM blog_posts WHERE id=? AND deleted=0 LIMIT 1");
$stmt->execute([$id]);
$post = $stmt->fetch();
if (!$post) die("Entrada no encontrada.");

/* ========= Slug ========= */
if ($slug === '') {
    $slug = strtolower(trim(preg_replace('/[^a-z0-9]+/i', '-', $title), '-'));
}
$slug = trim(preg_replace('/-+/', '-', $slug), '-');

$stSlug = db()->prepare("SELECT COUNT(*) FROM blog_posts WHERE slug=? AND id<>? AND deleted=0");
$stSlug->execute([$slug, $id]);
if ((int)$stSlug->fetchColumn() > 0) {
    $errors['slug'] = "El slug ya existe, elige otro.";
}

/* ========= Si hay errores ========= */
if (!empty($errors)) {
    $_SESSION['errors'] = $errors;
    $_SESSION['old']    = $_POST;
    header("Location: edit.php?id=" . $id);
    exit;
}

/* =========================================================
   IMAGEN DESTACADA — 3 casos posibles:

   1. Se subió archivo nuevo  → procesar upload, borrar anterior
   2. Vino image_path de biblioteca → usar esa ruta directamente
   3. image_path vacío + sin archivo → imagen quedó en null (la quitaron)
   ========================================================= */

$imagePath = $post['image']; // valor por defecto: conservar actual

$hasNewFile = !empty($_FILES['image']['tmp_name'])
              && $_FILES['image']['error'] === UPLOAD_ERR_OK;

if ($hasNewFile) {
    /* ── Caso 1: subió archivo nuevo ── */
    $file = $_FILES['image'];
    $ext  = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

    if (!in_array($ext, ['jpg','jpeg','png','webp','gif'], true)) {
        $_SESSION['errors'] = ['image' => "Formato no válido. Solo JPG, PNG, WebP, GIF."];
        $_SESSION['old']    = $_POST;
        header("Location: edit.php?id=" . $id); exit;
    }
    if ($file['size'] > 5 * 1024 * 1024) {
        $_SESSION['errors'] = ['image' => "La imagen supera los 5MB."];
        $_SESSION['old']    = $_POST;
        header("Location: edit.php?id=" . $id); exit;
    }

    $uploadDir = realpath(__DIR__ . '/../../public/images') . '/blog/';
    if (!is_dir($uploadDir)) @mkdir($uploadDir, 0755, true);

    $fileName = time() . '_' . preg_replace('/[^a-zA-Z0-9_\.-]/', '_', basename($file['name']));
    $dest     = $uploadDir . $fileName;

    if (!move_uploaded_file($file['tmp_name'], $dest)) {
        $_SESSION['errors'] = ['image' => "No se pudo guardar la imagen."];
        $_SESSION['old']    = $_POST;
        header("Location: edit.php?id=" . $id); exit;
    }

    // Registrar en multimedia
    try {
        $info = @getimagesize($dest);
        db()->prepare("INSERT INTO multimedia
            (file_name, file_path, file_type, mime_type, file_size, width, height, uploaded_by, origin, origin_id)
            VALUES (?,?,'image',?,?,?,?,?,'blog',?)")
            ->execute([
                $fileName,
                'public/images/blog/' . $fileName,
                mime_content_type($dest),
                $file['size'],
                $info[0] ?? null,
                $info[1] ?? null,
                $_SESSION['user']['id'],
                $id,
            ]);
    } catch (Throwable $e) {}

    // Borrar imagen anterior del disco
    if (!empty($post['image'])) {
        $oldAbs = realpath(__DIR__ . '/../../') . '/' . ltrim($post['image'], '/');
        if ($oldAbs && file_exists($oldAbs)) @unlink($oldAbs);
    }

    $imagePath = 'public/images/blog/' . $fileName;

} elseif (!empty($imageFromLib)) {
    /* ── Caso 2: seleccionó imagen de la biblioteca ── */
    $imagePath = $imageFromLib;

} else {
    /* ── Caso 3: image_path vacío y sin archivo nuevo ──
       Si el campo hidden image_path llegó vacío significa que
       el usuario quitó la imagen con el botón "Quitar imagen" */
    if (isset($_POST['image_path'])) {
        // El campo existe en el POST pero está vacío → quitaron la imagen
        $imagePath = null;
    }
    // Si image_path no existe en POST (form viejo sin el campo) → conservar actual
}

/* ========= Actualizar blog_posts ========= */
$sql = "UPDATE blog_posts 
        SET title=?, slug=?, content=?, image=?, status=?,
            seo_title=?, seo_description=?, seo_keywords=?,
            updated_at=NOW()
        WHERE id=?";
db()->prepare($sql)->execute([
    $title,
    $slug,
    $content,
    $imagePath,
    $status,
    $seoTitle,
    $seoDescription,
    $seoKeywords,
    $id,
]);

/* ========= Actualizar alt_text en multimedia ========= */
if (!empty($imagePath)) {
    db()->prepare("UPDATE multimedia SET alt_text = ? WHERE file_path = ? AND deleted = 0")
        ->execute([$imageAlt, $imagePath]);
}

/* ========= Actualizar categorías ========= */
db()->prepare("DELETE FROM blog_post_category WHERE post_id=?")->execute([$id]);
if (!empty($cats) && is_array($cats)) {
    $ins = db()->prepare("INSERT INTO blog_post_category (post_id, category_id) VALUES (?,?)");
    foreach ($cats as $catId) {
        $ins->execute([$id, (int)$catId]);
    }
}

log_system_action('update_post', 'Entrada actualizada: ' . $title, 'post', $id);

/* ========= Éxito ========= */
flash_set('success', '¡Entrada actualizada!', 'Se guardaron los cambios correctamente.');
header("Location: index.php");
exit;
