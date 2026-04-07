<?php
require_once __DIR__ . '/../../inc/config.php';
require_once __DIR__ . '/../login/session.php';
$permisopage = 'Crear Entrada';
include('../login/restriction.php');

require_once __DIR__ . '/../inc/flash_helpers.php';

// Cargar categorías activas
$cats = db()->query("SELECT id, name FROM blog_categories WHERE deleted=0 AND status='active' ORDER BY name")->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title          = trim($_POST['title']          ?? '');
    $slug           = trim($_POST['slug']           ?? '');
    $content        = trim($_POST['content']        ?? '');
    $status         = $_POST['status']              ?? 'draft';
    $catsSel        = $_POST['categories']          ?? [];
    $seoTitle       = trim($_POST['seo_title']      ?? '');
    $seoDescription = trim($_POST['seo_description'] ?? '');
    $seoKeywords    = trim($_POST['seo_keywords']   ?? '');
    $imageFromLib   = trim($_POST['image_path']     ?? ''); // ← desde biblioteca
$imageAlt       = trim($_POST['image_alt']       ?? '');

    // Autor
    $author     = ($_SESSION['user']['nombre'] ?? 'Admin') . ' ' . ($_SESSION['user']['apellido'] ?? '');
    $authorUser = $_SESSION['user']['username'] ?? $_SESSION['user']['correo'] ?? 'sistema';

    $errors = [];
    $old    = $_POST;

    /* ========= Validaciones ========= */
    if ($title === '')   $errors['title']   = "El título es obligatorio.";
    if ($content === '') $errors['content'] = "El contenido no puede estar vacío.";

    /* ========= Slug ========= */
    if ($slug === '') {
        $slug = strtolower(preg_replace('/[^a-z0-9]+/i', '-', $title));
        $slug = trim($slug, '-');
    }

    // Slug único
    $originalSlug = $slug;
    $counter      = 1;
    while (true) {
        $st = db()->prepare("SELECT COUNT(*) FROM blog_posts WHERE slug = ? AND deleted = 0");
        $st->execute([$slug]);
        if ((int)$st->fetchColumn() === 0) break;
        $slug = $originalSlug . '-' . $counter++;
        if ($counter > 100) { $errors['slug'] = "No se pudo generar un slug único."; break; }
    }

    /* =========================================================
       IMAGEN DESTACADA — 3 casos:

       1. Se subió archivo nuevo  → procesar upload
       2. Vino image_path de biblioteca → usar esa ruta
       3. Ninguno → sin imagen
       ========================================================= */
    $imagePath = null;
    $hasNewFile = !empty($_FILES['image']['tmp_name'])
                  && $_FILES['image']['error'] === UPLOAD_ERR_OK;

    if ($hasNewFile) {
        /* ── Caso 1: archivo nuevo ── */
        $file = $_FILES['image'];
        $ext  = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

        if (!in_array($ext, ['jpg','jpeg','png','webp'], true)) {
            $errors['image'] = "Formato no válido. Solo JPG, PNG o WebP.";
        } elseif ($file['size'] > 5 * 1024 * 1024) {
            $errors['image'] = "La imagen supera los 5MB.";
        } else {
            $uploadDir = realpath(__DIR__ . '/../../public/images') . '/blog/';
            if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);

            $fileName = time() . '_' . preg_replace('/[^a-z0-9\.-]/i', '_', $file['name']);
            $dest     = $uploadDir . $fileName;

            if (move_uploaded_file($file['tmp_name'], $dest)) {
                $imagePath = 'public/images/blog/' . $fileName;
            } else {
                $errors['image'] = "No se pudo guardar la imagen.";
            }
        }

    } elseif (!empty($imageFromLib)) {
        /* ── Caso 2: desde biblioteca ── */
        $imagePath = $imageFromLib;
    }
    // Caso 3: sin imagen → $imagePath queda null

    /* ========= Si hay errores ========= */
    if (!empty($errors)) {
        $_SESSION['errors'] = $errors;
        $_SESSION['old']    = $old;
        header("Location: create.php");
        exit;
    }

    /* ========= Guardar entrada ========= */
    try {
        $sql = "INSERT INTO blog_posts 
                (title, slug, content, image, author, author_user, status,
                 seo_title, seo_description, seo_keywords, deleted)
                VALUES (?,?,?,?,?,?,?,?,?,?,0)";
        db()->prepare($sql)->execute([
            $title, $slug, $content, $imagePath,
            $author, $authorUser, $status,
            $seoTitle, $seoDescription, $seoKeywords,
        ]);
        $postId = db()->lastInsertId();

        log_system_action('create_post', 'Nueva entrada creada: ' . $title, 'post', $postId);

        /* ── Categorías ── */
        if (!empty($catsSel)) {
            $stCat = db()->prepare("INSERT INTO blog_post_category (post_id, category_id) VALUES (?,?)");
            foreach ($catsSel as $cid) {
                $stCat->execute([$postId, (int)$cid]);
            }
        }

        /* ── Registrar imagen en multimedia si fue upload nuevo ── */
        if ($hasNewFile && $imagePath && empty($errors['image'])) {
            try {
                $dest = __DIR__ . '/../../' . $imagePath;
                $info = @getimagesize($dest);
                db()->prepare("INSERT INTO multimedia
                    (file_name, file_path, file_type, mime_type, file_size, width, height, alt_text, uploaded_by, origin, origin_id)
                    VALUES (?,?,'image',?,?,?,?,?,?,'blog',?)")
                    ->execute([
                        basename($imagePath),
                        $imagePath,
                        mime_content_type($dest),
                        $_FILES['image']['size'],
                        $info[0] ?? null,
                        $info[1] ?? null,
                        $imageAlt,
                        $_SESSION['user']['id'],
                        $postId,
                    ]);
            } catch (Throwable $e) {}
        }

        flash_set("success", "Entrada creada", "La entrada fue creada correctamente.");
        header("Location: index.php");
        exit;

    } catch (PDOException $e) {
        if ($e->getCode() == 23000 && strpos($e->getMessage(), 'slug') !== false) {
            $_SESSION['errors'] = ['slug' => 'El slug ya existe. Por favor, elige otro.'];
        } else {
            $_SESSION['errors'] = ['general' => 'Error al crear la entrada: ' . $e->getMessage()];
        }
        $_SESSION['old'] = $old;
        header("Location: create.php");
        exit;
    }
}

