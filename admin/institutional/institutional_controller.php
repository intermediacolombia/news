<?php
require_once __DIR__ . '/../../inc/config.php';
require_once __DIR__ . '/../login/session.php';

/**
 * Obtener todas las páginas institucionales
 */
function getAllPages() {
    $sql = "SELECT ip.*, u.nombre as author_name 
            FROM institutional_pages ip
            LEFT JOIN usuarios u ON ip.created_by = u.id
            ORDER BY ip.display_order ASC, ip.created_at DESC";
    return db()->query($sql)->fetchAll();
}

/**
 * Obtener una página por ID
 */
function getPageById($id) {
    $id = (int)$id;
    $sql = "SELECT ip.*, u.nombre as author_name 
            FROM institutional_pages ip
            LEFT JOIN usuarios u ON ip.created_by = u.id
            WHERE ip.id = ?";
    $stmt = db()->prepare($sql);
    $stmt->execute([$id]);
    return $stmt->fetch();
}

/**
 * Obtener una página por slug
 */
function getPageBySlug($slug) {
    $sql = "SELECT ip.*, u.nombre as author_name 
            FROM institutional_pages ip
            LEFT JOIN usuarios u ON ip.created_by = u.id
            WHERE ip.slug = ? AND ip.status = 'published'";
    $stmt = db()->prepare($sql);
    $stmt->execute([$slug]);
    return $stmt->fetch();
}

/**
 * Crear nueva página institucional
 */
function createPage($data) {
    $sql = "INSERT INTO institutional_pages 
            (title, slug, content, page_type, status, image, display_order, 
             seo_title, seo_description, seo_keywords, created_by, created_at, updated_at) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())";
    
    $stmt = db()->prepare($sql);
    $result = $stmt->execute([
        $data['title'],
        $data['slug'],
        $data['content'],
        $data['page_type'],
        $data['status'],
        $data['image'],
        $data['display_order'],
        $data['seo_title'],
        $data['seo_description'],
        $data['seo_keywords'],
        $data['created_by']
    ]);
    
    return $result ? db()->lastInsertId() : false;
}

/**
 * Actualizar página institucional
 */
function updatePage($id, $data) {
    $id = (int)$id;
    
    $sql = "UPDATE institutional_pages SET 
            title = ?, 
            slug = ?, 
            content = ?, 
            page_type = ?, 
            status = ?, 
            display_order = ?,
            seo_title = ?,
            seo_description = ?,
            seo_keywords = ?,
            updated_at = NOW()";
    
    $params = [
        $data['title'],
        $data['slug'],
        $data['content'],
        $data['page_type'],
        $data['status'],
        $data['display_order'],
        $data['seo_title'],
        $data['seo_description'],
        $data['seo_keywords']
    ];
    
    // Si hay imagen nueva
    if(isset($data['image']) && $data['image']) {
        $sql .= ", image = ?";
        $params[] = $data['image'];
    }
    
    $sql .= " WHERE id = ?";
    $params[] = $id;
    
    $stmt = db()->prepare($sql);
    return $stmt->execute($params);
}

/**
 * Eliminar página institucional
 */
function deletePage($id) {
    $id = (int)$id;
    
    // Obtener imagen para eliminarla
    $page = getPageById($id);
    
    $sql = "DELETE FROM institutional_pages WHERE id = ?";
    $stmt = db()->prepare($sql);
    
    if($stmt->execute([$id])) {
        // Eliminar imagen física si existe
        if($page && !empty($page['image'])) {
            $imagePath = __DIR__ . '/../../' . $page['image'];
            if(file_exists($imagePath)) {
                @unlink($imagePath);
            }
        }
        return true;
    }
    return false;
}

/**
 * Verificar si un slug ya existe (excluyendo un ID específico)
 */
function slugExists($slug, $excludeId = null) {
    if($excludeId) {
        $sql = "SELECT COUNT(*) FROM institutional_pages WHERE slug = ? AND id != ?";
        $stmt = db()->prepare($sql);
        $stmt->execute([$slug, $excludeId]);
    } else {
        $sql = "SELECT COUNT(*) FROM institutional_pages WHERE slug = ?";
        $stmt = db()->prepare($sql);
        $stmt->execute([$slug]);
    }
    
    return $stmt->fetchColumn() > 0;
}

/**
 * Generar slug único desde un título
 */
function generateUniqueSlug($title, $excludeId = null) {
    $slug = slugify($title);
    $originalSlug = $slug;
    $counter = 1;
    
    while(slugExists($slug, $excludeId)) {
        $slug = $originalSlug . '-' . $counter;
        $counter++;
        
        // Prevenir loop infinito
        if($counter > 100) {
            break;
        }
    }
    
    return $slug;
}

/**
 * Convertir string a slug
 */
function slugify($text) {
    $slug = strtolower(preg_replace('/[^a-z0-9]+/i', '-', $text));
    $slug = trim($slug, '-');
    return substr($slug, 0, 180);
}

/**
 * Validar datos de página
 */
function validatePageData($data, $isEdit = false) {
    $errors = [];
    
    // Título
    if(empty(trim($data['title'] ?? ''))) {
        $errors['title'] = 'El título es obligatorio';
    } elseif(strlen($data['title']) > 255) {
        $errors['title'] = 'El título no puede superar 255 caracteres';
    }
    
    // Slug
    if(empty(trim($data['slug'] ?? ''))) {
        $data['slug'] = generateUniqueSlug($data['title'], $isEdit ? ($data['id'] ?? null) : null);
    } else {
        $slug = slugify($data['slug']);
        $excludeId = $isEdit ? ($data['id'] ?? null) : null;
        if(slugExists($slug, $excludeId)) {
            $errors['slug'] = 'Este slug ya está en uso';
        }
        $data['slug'] = $slug;
    }
    
    // Contenido
    if(empty(trim($data['content'] ?? ''))) {
        $errors['content'] = 'El contenido es obligatorio';
    }
    
    // Tipo de página
    $validTypes = ['general', 'about', 'mission', 'history', 'organization', 'board', 'team', 'values', 'policies'];
    if(!in_array($data['page_type'] ?? '', $validTypes)) {
        $data['page_type'] = 'general';
    }
    
    // Estado
    if(!in_array($data['status'] ?? '', ['draft', 'published'])) {
        $data['status'] = 'draft';
    }
    
    // Orden
    $data['display_order'] = max(0, (int)($data['display_order'] ?? 0));
    
    // SEO (opcionales pero con límites)
    if(isset($data['seo_title']) && strlen($data['seo_title']) > 180) {
        $errors['seo_title'] = 'El título SEO no puede superar 180 caracteres';
    }
    
    if(isset($data['seo_description']) && strlen($data['seo_description']) > 300) {
        $errors['seo_description'] = 'La descripción SEO no puede superar 300 caracteres';
    }
    
    if(isset($data['seo_keywords']) && strlen($data['seo_keywords']) > 300) {
        $errors['seo_keywords'] = 'Las keywords no pueden superar 300 caracteres';
    }
    
    return ['data' => $data, 'errors' => $errors];
}

/**
 * Procesar subida de imagen
 */
function handleImageUpload($file) {
    $allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/webp'];
    $maxSize = 5 * 1024 * 1024; // 5MB
    
    if($file['error'] === UPLOAD_ERR_NO_FILE) {
        return ['success' => true, 'path' => null];
    }
    
    if($file['error'] !== UPLOAD_ERR_OK) {
        return ['success' => false, 'error' => 'Error al subir el archivo'];
    }
    
    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if(!in_array('image/' . $ext, $allowedTypes) && !in_array($ext, ['jpg','jpeg','png','webp'])) {
        return ['success' => false, 'error' => 'Tipo de archivo no permitido. Solo JPG, PNG, WebP'];
    }
    
    if($file['size'] > $maxSize) {
        return ['success' => false, 'error' => 'El archivo supera el tamaño máximo de 5MB'];
    }
    
    // Crear directorio si no existe (igual que en blog)
    $uploadDir = realpath(__DIR__ . '/../../public/images') . '/institutional/';
    if(!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }
    
    // Generar nombre único
    $fileName = time() . '_' . preg_replace('/[^a-z0-9\.-]/i', '_', $file['name']);
    $destino = $uploadDir . $fileName;
    
    if(move_uploaded_file($file['tmp_name'], $destino)) {
        return ['success' => true, 'path' => 'public/images/institutional/' . $fileName];
    }
    
    return ['success' => false, 'error' => 'Error al guardar el archivo'];
}

// ========== PROCESAMIENTO DE FORMULARIOS ==========

// Crear página
if($_SERVER['REQUEST_METHOD'] === 'POST' && !isset($_POST['id'])) {
    $validation = validatePageData($_POST);
    $data = $validation['data'];
    $errors = $validation['errors'];
    
    // Procesar imagen
    if(isset($_FILES['image']) && $_FILES['image']['error'] !== UPLOAD_ERR_NO_FILE) {
        $imageResult = handleImageUpload($_FILES['image']);
        if(!$imageResult['success']) {
            $errors['image'] = $imageResult['error'];
        } else {
            $data['image'] = $imageResult['path'];
        }
    } else {
        $data['image'] = null;
    }
    
    if(empty($errors)) {
        $data['created_by'] = $_SESSION['user_id'] ?? 1;
        
        try {
            if(createPage($data)) {
                $_SESSION['success'] = 'Página creada exitosamente';
                header('Location: index.php');
                exit;
            } else {
                $errors['__global'] = 'Error al crear la página';
            }
        } catch (PDOException $e) {
            // Si hay error de slug duplicado
            if($e->getCode() == 23000 && strpos($e->getMessage(), 'slug') !== false) {
                $errors['slug'] = 'El slug ya existe. Por favor, elige otro.';
            } else {
                $errors['__global'] = 'Error al crear la página: ' . $e->getMessage();
            }
        }
    }
    
    $_SESSION['errors'] = $errors;
    $_SESSION['old'] = $_POST;
    header('Location: ' . $_SERVER['PHP_SELF']);
    exit;
}

// Editar página
if($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id'])) {
    $id = (int)$_POST['id'];
    $currentPage = getPageById($id);
    
    if(!$currentPage) {
        $_SESSION['error'] = 'Página no encontrada';
        header('Location: index.php');
        exit;
    }
    
    $validation = validatePageData($_POST, true);
    $data = $validation['data'];
    $errors = $validation['errors'];
    
    // Procesar imagen
    if(isset($_FILES['image']) && $_FILES['image']['error'] !== UPLOAD_ERR_NO_FILE) {
        $imageResult = handleImageUpload($_FILES['image']);
        if(!$imageResult['success']) {
            $errors['image'] = $imageResult['error'];
        } else {
            // Eliminar imagen anterior
            if(!empty($currentPage['image'])) {
                $oldImagePath = __DIR__ . '/../../' . $currentPage['image'];
                if(file_exists($oldImagePath)) {
                    @unlink($oldImagePath);
                }
            }
            $data['image'] = $imageResult['path'];
        }
    } elseif(isset($_POST['remove_image']) && $_POST['remove_image'] == '1') {
        // Eliminar imagen actual
        if(!empty($currentPage['image'])) {
            $oldImagePath = __DIR__ . '/../../' . $currentPage['image'];
            if(file_exists($oldImagePath)) {
                @unlink($oldImagePath);
            }
        }
        $data['image'] = '';
    }
    
    if(empty($errors)) {
        try {
            if(updatePage($id, $data)) {
                $_SESSION['success'] = 'Página actualizada exitosamente';
                header('Location: index.php');
                exit;
            } else {
                $errors['__global'] = 'Error al actualizar la página';
            }
        } catch (PDOException $e) {
            if($e->getCode() == 23000 && strpos($e->getMessage(), 'slug') !== false) {
                $errors['slug'] = 'El slug ya existe. Por favor, elige otro.';
            } else {
                $errors['__global'] = 'Error al actualizar la página: ' . $e->getMessage();
            }
        }
    }
    
    $_SESSION['errors'] = $errors;
    $_SESSION['old'] = $_POST;
    header('Location: ' . $_SERVER['PHP_SELF'] . '?id=' . $id);
    exit;
}