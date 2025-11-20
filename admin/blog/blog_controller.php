<?php
require_once __DIR__ . '/../../inc/config.php';
require_once __DIR__ . '/../inc/flash_helpers.php';

try {
  db() = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $dbuser, $dbpass, [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
  ]);
} catch(Throwable $e) {
  die("DB error: ".$e->getMessage());
}

// Cargar categorías activas
$cats = db()->query("SELECT id, name FROM blog_categories WHERE deleted=0 AND status='active' ORDER BY name")->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $title   = trim($_POST['title'] ?? '');
  $slug    = trim($_POST['slug'] ?? '');
  $content = trim($_POST['content'] ?? '');
  $status  = $_POST['status'] ?? 'draft';
  $catsSel = $_POST['categories'] ?? [];

  // Autor visible (nombre completo)
  $author = ($_SESSION['user']['nombre'] ?? 'Admin') . ' ' . ($_SESSION['user']['apellido'] ?? '');

  // Autor interno (nombre de usuario)
  $authorUser = $_SESSION['user']['username'] ?? $_SESSION['user']['correo'] ?? 'sistema';

  // Campos SEO
  $seoTitle       = trim($_POST['seo_title'] ?? '');
  $seoDescription = trim($_POST['seo_description'] ?? '');
  $seoKeywords    = trim($_POST['seo_keywords'] ?? '');

  $errors = [];
  $old    = $_POST;

  // Validaciones
  if ($title === '') {
    $errors['title'] = "El título es obligatorio.";
  }
  if ($content === '') {
    $errors['content'] = "El contenido no puede estar vacío.";
  }
  if ($slug === '') {
    $slug = strtolower(preg_replace('/[^a-z0-9]+/i', '-', $title));
  }

  // Slug único
  $st = db()->prepare("SELECT COUNT(*) FROM blog_posts WHERE slug=? AND deleted=0");
  $st->execute([$slug]);
  if ($st->fetchColumn() > 0) {
    $errors['slug'] = "El slug ya existe, elige otro.";
  }

  // Imagen destacada
  $imagePath = null;
  if (!empty($_FILES['image']['tmp_name'])) {
    $file = $_FILES['image'];
    if ($file['error'] === UPLOAD_ERR_OK) {
      $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
      if (!in_array($ext, ['jpg','jpeg','png','webp'])) {
        $errors['image'] = "Formato no válido. Solo JPG, PNG o WebP.";
      } elseif ($file['size'] > 5*1024*1024) {
        $errors['image'] = "La imagen supera los 5MB.";
      } else {
        $uploadDir = realpath(__DIR__ . '/../../public/images') . '/blog/';
        if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);
        $fileName = time().'_'.preg_replace('/[^a-z0-9\.-]/i','_', $file['name']);
        $destino  = $uploadDir.$fileName;
        if (move_uploaded_file($file['tmp_name'], $destino)) {
          $imagePath = 'public/images/blog/'.$fileName;
        } else {
          $errors['image'] = "No se pudo guardar la imagen.";
        }
      }
    }
  }

  // Si hay errores, redirigir de nuevo
  if (!empty($errors)) {
    $_SESSION['errors'] = $errors;
    $_SESSION['old']    = $old;
    header("Location: create.php");
    exit;
  }

  // =============================
  // GUARDAR ENTRADA CON author_user
  // =============================
  $sql = "INSERT INTO blog_posts 
          (title, slug, content, image, author, author_user, status, seo_title, seo_description, seo_keywords, deleted)
          VALUES (?,?,?,?,?,?,?,?,?,?,0)";
  $st  = db()->prepare($sql);
  $st->execute([
    $title,
    $slug,
    $content,
    $imagePath,
    $author,
    $authorUser,
    $status,
    $seoTitle,
    $seoDescription,
    $seoKeywords
  ]);

  $postId = db()->lastInsertId();

  // Guardar categorías
  if (!empty($catsSel)) {
    $stCat = db()->prepare("INSERT INTO blog_post_category (post_id, category_id) VALUES (?,?)");
    foreach ($catsSel as $cid) {
      $stCat->execute([$postId, (int)$cid]);
    }
  }

  flash_set("success", "Entrada creada", "La entrada fue creada correctamente.");
  header("Location: index.php");
  exit;
}

