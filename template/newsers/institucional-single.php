<?php
/**
 * Template: Página individual institucional
 * Ubicación: /template/TU_TEMA/institucional-single.php
 */

// Obtener el slug de la URL
$slug = $_GET['institutional_slug'] ?? '';

if(empty($slug)) {
    header('Location: ' . URLBASE . '/institucional');
    exit;
}

// Cargar página específica
$sql = "SELECT * FROM institutional_pages WHERE slug = ? AND status = 'published'";
$stmt = db()->prepare($sql);
$stmt->execute([$slug]);
$page = $stmt->fetch();

if(!$page) {
    // Página no encontrada
    header("HTTP/1.0 404 Not Found");
    http_response_code(404);
    include __DIR__ . '/404.php';
    exit;
}

// =======================
// Variables SEO dinámicas
// =======================
$page_title       = ($page['seo_title'] ?: $page['title']) . " | " . NOMBRE_SITIO;
$page_description = $page['seo_description'] ?: substr(strip_tags($page['content']), 0, 160);
$page_keywords    = $page['seo_keywords'] ?: NOMBRE_SITIO . ", " . $page['title'];
$page_author      = NOMBRE_SITIO;

// Imagen SEO
$page_image = rtrim(URLBASE, '/') . FAVICON;
if (!empty($page['image'])) {
    $imagePath = $page['image'];
    $imagePath = ($imagePath[0] === '/') ? $imagePath : '/' . $imagePath;
    $page_image = rtrim(URLBASE, '/') . $imagePath;
}

// Canonical
$currentPath    = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$page_canonical = rtrim(URLBASE, '/') . '/' . ltrim($currentPath, '/');
// =======================
?>

<div class="container-fluid py-5">
    <div class="container py-5">
        <div class="container-fluid py-3">
            <div class="container-bk">
                <div class="row">
                    <div class="col-lg-8">
                        <div class="bg-light" style="padding: 50px;">
                            
                            <!-- Botón volver -->
                            <div class="mb-3">
                                <a href="<?= URLBASE ?>/institucional" class="btn btn-sm btn-outline-secondary">
                                    <i class="fa fa-arrow-left"></i> Volver al listado
                                </a>
                            </div>
                            
                            <!-- Título de la página -->
                            <h1 class="mb-4"><?= htmlspecialchars($page['title']) ?></h1>
                            
                            <!-- Imagen destacada -->
                            <?php if(!empty($page['image'])): ?>
                                <div class="mb-4">
                                    <img src="<?= htmlspecialchars(URLBASE . $page['image']) ?>" 
                                         alt="<?= htmlspecialchars($page['title']) ?>"
                                         class="img-fluid rounded"
                                         style="width: 100%; max-height: 400px; object-fit: cover;">
                                </div>
                            <?php endif; ?>
                            
                            <!-- Contenido -->
                            <div class="content">
                                <?= $page['content'] ?>
                            </div>
                            
                            <!-- Fecha de actualización -->
                            <div class="mt-5 pt-3 border-top text-muted small">
                                <i class="fa fa-calendar"></i> 
                                Última actualización: <?= date('d/m/Y', strtotime($page['updated_at'])) ?>
                            </div>
                            
                        </div>
                    </div>
                    
                    <!-- Sidebar -->
                    <div class="col-lg-4">
                        <?php include __DIR__ . '/partials/sidebar.php'; ?>            
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
