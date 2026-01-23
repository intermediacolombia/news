<?php
require_once __DIR__ . '/../../inc/config.php';
require_once __DIR__ . '/../login/session.php';
$permisopage = 'Crear Blogs';
include('../login/restriction.php');
session_start();

require_once __DIR__ . '/../inc/flash_helpers.php';

// Cargar categorías activas
$cats = db()->query("SELECT id, name FROM blog_categories WHERE deleted=0 AND status='active' ORDER BY name")->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Sanitizar datos de entrada
    $title   = trim($_POST['title'] ?? '');
    $slug    = trim($_POST['slug'] ?? '');
    $content = trim($_POST['content'] ?? '');
    $status  = in_array($_POST['status'] ?? '', ['draft', 'published']) ? $_POST['status'] : 'draft';
    $catsSel = $_POST['categories'] ?? [];
    
    // Autor visible (nombre completo)
    $author = trim(($_SESSION['user']['nombre'] ?? 'Admin') . ' ' . ($_SESSION['user']['apellido'] ?? ''));
    
    // Autor interno (nombre de usuario)
    $authorUser = $_SESSION['user']['username'] ?? $_SESSION['user']['correo'] ?? 'sistema';
    
    // Campos SEO
    $seoTitle       = trim($_POST['seo_title'] ?? '');
    $seoDescription = trim($_POST['seo_description'] ?? '');
    $seoKeywords    = trim($_POST['seo_keywords'] ?? '');
    
    $errors = [];
    $old    = $_POST;
    
    // =============================
    // VALIDACIONES
    // =============================
    if ($title === '') {
        $errors['title'] = "El título es obligatorio.";
    } elseif (mb_strlen($title) > 255) {
        $errors['title'] = "El título no puede exceder 255 caracteres.";
    }
    
    if ($content === '') {
        $errors['content'] = "El contenido no puede estar vacío.";
    }
    
    // Generar slug si está vacío
    if ($slug === '') {
        $slug = generateSlug($title);
    } else {
        $slug = generateSlug($slug);
    }
    
    // Validar slug único
    $st = db()->prepare("SELECT COUNT(*) FROM blog_posts WHERE slug = :slug AND deleted = 0");
    $st->execute([':slug' => $slug]);
    if ($st->fetchColumn() > 0) {
        $errors['slug'] = "El slug ya existe, elige otro.";
    }
    
    // =============================
    // PROCESAMIENTO DE IMAGEN
    // =============================
    $imagePath = null;
    if (!empty($_FILES['image']['tmp_name'])) {
        $result = processUploadedImage($_FILES['image']);
        if ($result['success']) {
            $imagePath = $result['path'];
        } else {
            $errors['image'] = $result['error'];
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
    // TRANSACCIÓN: GUARDAR ENTRADA
    // =============================
    try {
        db()->beginTransaction();
        
        // Insertar entrada
        $sql = "INSERT INTO blog_posts 
                (title, slug, content, image, author, author_user, status, 
                 seo_title, seo_description, seo_keywords, deleted, created_at, updated_at)
                VALUES (:title, :slug, :content, :image, :author, :author_user, :status, 
                        :seo_title, :seo_description, :seo_keywords, 0, NOW(), NOW())";
        
        $st = db()->prepare($sql);
        $st->execute([
            ':title'            => $title,
            ':slug'             => $slug,
            ':content'          => $content,
            ':image'            => $imagePath,
            ':author'           => $author,
            ':author_user'      => $authorUser,
            ':status'           => $status,
            ':seo_title'        => $seoTitle,
            ':seo_description'  => $seoDescription,
            ':seo_keywords'     => $seoKeywords
        ]);
        
        $postId = db()->lastInsertId();
        
        // Guardar categorías
        if (!empty($catsSel) && is_array($catsSel)) {
            $stCat = db()->prepare("INSERT INTO blog_post_category (post_id, category_id) VALUES (:post_id, :category_id)");
            foreach ($catsSel as $cid) {
                if (is_numeric($cid)) {
                    $stCat->execute([
                        ':post_id'     => $postId,
                        ':category_id' => (int)$cid
                    ]);
                }
            }
        }
        
        db()->commit();
        
        flash_set("success", "Entrada creada", "La entrada fue creada correctamente.");
        header("Location: index.php");
        exit;
        
    } catch (Exception $e) {
        db()->rollBack();
        
        // Si se subió una imagen, eliminarla
        if ($imagePath && file_exists(__DIR__ . '/../../' . $imagePath)) {
            unlink(__DIR__ . '/../../' . $imagePath);
        }
        
        $_SESSION['errors'] = ['general' => 'Error al crear la entrada: ' . $e->getMessage()];
        $_SESSION['old'] = $old;
        header("Location: create.php");
        exit;
    }
}

// =============================
// FUNCIONES AUXILIARES
// =============================

/**
 * Genera un slug URL-friendly a partir de un texto
 * 
 * @param string $text Texto a convertir
 * @return string Slug generado
 */
function generateSlug($text) {
    // Convertir a minúsculas
    $text = mb_strtolower($text, 'UTF-8');
    
    // Reemplazar caracteres especiales
    $replacements = [
        'á' => 'a', 'é' => 'e', 'í' => 'i', 'ó' => 'o', 'ú' => 'u',
        'ñ' => 'n', 'ü' => 'u',
        'à' => 'a', 'è' => 'e', 'ì' => 'i', 'ò' => 'o', 'ù' => 'u',
        'ä' => 'a', 'ë' => 'e', 'ï' => 'i', 'ö' => 'o', 'ü' => 'u',
    ];
    $text = strtr($text, $replacements);
    
    // Eliminar caracteres no permitidos
    $text = preg_replace('/[^a-z0-9\s-]/', '', $text);
    
    // Reemplazar espacios y múltiples guiones por un solo guion
    $text = preg_replace('/[\s-]+/', '-', $text);
    
    // Eliminar guiones al inicio y final
    $text = trim($text, '-');
    
    return $text;
}

/**
 * Procesa una imagen subida validando formato, tamaño y guardándola
 * 
 * @param array $file Array de $_FILES
 * @return array ['success' => bool, 'path' => string|null, 'error' => string|null]
 */
function processUploadedImage($file) {
    // Validar error de subida
    if ($file['error'] !== UPLOAD_ERR_OK) {
        return [
            'success' => false,
            'path'    => null,
            'error'   => 'Error al subir el archivo. Código: ' . $file['error']
        ];
    }
    
    // Validar formato
    $allowedExtensions = ['jpg', 'jpeg', 'png', 'webp', 'gif'];
    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    
    if (!in_array($ext, $allowedExtensions)) {
        return [
            'success' => false,
            'path'    => null,
            'error'   => 'Formato no válido. Solo se permiten: ' . implode(', ', array_map('strtoupper', $allowedExtensions))
        ];
    }
    
    // Validar tamaño (5MB máximo)
    $maxSize = 5 * 1024 * 1024; // 5MB
    if ($file['size'] > $maxSize) {
        return [
            'success' => false,
            'path'    => null,
            'error'   => 'La imagen supera los 5MB permitidos.'
        ];
    }
    
    // Validar que sea una imagen real
    $imageInfo = @getimagesize($file['tmp_name']);
    if ($imageInfo === false) {
        return [
            'success' => false,
            'path'    => null,
            'error'   => 'El archivo no es una imagen válida.'
        ];
    }
    
    // Crear directorio si no existe
    $uploadDir = realpath(__DIR__ . '/../../public/images') . '/blog/';
    if (!is_dir($uploadDir)) {
        if (!mkdir($uploadDir, 0755, true)) {
            return [
                'success' => false,
                'path'    => null,
                'error'   => 'No se pudo crear el directorio de imágenes.'
            ];
        }
    }
    
    // Generar nombre único y seguro
    $fileName = time() . '_' . uniqid() . '.' . $ext;
    $destino  = $uploadDir . $fileName;
    
    // Mover archivo
    if (!move_uploaded_file($file['tmp_name'], $destino)) {
        return [
            'success' => false,
            'path'    => null,
            'error'   => 'No se pudo guardar la imagen en el servidor.'
        ];
    }
    
    // Redimensionar imagen si es muy grande (opcional)
    resizeImageIfNeeded($destino, 1920, 1080);
    
    return [
        'success' => true,
        'path'    => 'public/images/blog/' . $fileName,
        'error'   => null
    ];
}

/**
 * Redimensiona una imagen si excede las dimensiones máximas
 * 
 * @param string $filepath Ruta completa del archivo
 * @param int $maxWidth Ancho máximo
 * @param int $maxHeight Alto máximo
 * @return bool
 */
function resizeImageIfNeeded($filepath, $maxWidth = 1920, $maxHeight = 1080) {
    $imageInfo = getimagesize($filepath);
    if (!$imageInfo) return false;
    
    list($width, $height, $type) = $imageInfo;
    
    // Si la imagen es menor que los límites, no hacer nada
    if ($width <= $maxWidth && $height <= $maxHeight) {
        return true;
    }
    
    // Calcular nuevas dimensiones manteniendo proporción
    $ratio = min($maxWidth / $width, $maxHeight / $height);
    $newWidth = round($width * $ratio);
    $newHeight = round($height * $ratio);
    
    // Crear imagen según tipo
    switch ($type) {
        case IMAGETYPE_JPEG:
            $source = imagecreatefromjpeg($filepath);
            break;
        case IMAGETYPE_PNG:
            $source = imagecreatefrompng($filepath);
            break;
        case IMAGETYPE_WEBP:
            $source = imagecreatefromwebp($filepath);
            break;
        case IMAGETYPE_GIF:
            $source = imagecreatefromgif($filepath);
            break;
        default:
            return false;
    }
    
    if (!$source) return false;
    
    // Crear nueva imagen
    $destination = imagecreatetruecolor($newWidth, $newHeight);
    
    // Preservar transparencia para PNG
    if ($type === IMAGETYPE_PNG) {
        imagealphablending($destination, false);
        imagesavealpha($destination, true);
    }
    
    // Redimensionar
    imagecopyresampled($destination, $source, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);
    
    // Guardar según tipo
    switch ($type) {
        case IMAGETYPE_JPEG:
            imagejpeg($destination, $filepath, 85);
            break;
        case IMAGETYPE_PNG:
            imagepng($destination, $filepath, 8);
            break;
        case IMAGETYPE_WEBP:
            imagewebp($destination, $filepath, 85);
            break;
        case IMAGETYPE_GIF:
            imagegif($destination, $filepath);
            break;
    }
    
    // Liberar memoria
    imagedestroy($source);
    imagedestroy($destination);
    
    return true;
}

// El resto de tu HTML del formulario va aquí...
?>

