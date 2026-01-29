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

// Tipo de página
$typeNames = [
    'general' => 'General',
    'about' => 'Quiénes Somos',
    'mission' => 'Misión y Visión',
    'history' => 'Historia',
    'organization' => 'Organigrama',
    'board' => 'Junta Directiva',
    'team' => 'Equipo',
    'values' => 'Valores',
    'policies' => 'Políticas'
];

$typeName = $typeNames[$page['page_type']] ?? 'Institucional';
// =======================
?>

<!-- Breadcrumb Start -->
<div class="container-fluid">
    <div class="container-bk">
        <nav class="breadcrumb bg-transparent m-0 p-0">
            <a class="breadcrumb-item" href="<?= URLBASE ?>">Home</a>
            <a class="breadcrumb-item" href="<?= URLBASE ?>/institucional">Institucional</a>
            <span class="breadcrumb-item active"><?= htmlspecialchars($page['title']) ?></span>
        </nav>
    </div>
</div>
<!-- Breadcrumb End -->

<!-- Content Start -->
<div class="container-fluid py-3">
    <div class="container-bk">
        <div class="row">
            <div class="col-lg-8">
                
                <div class="position-relative mb-3">
                    
                    <!-- Imagen destacada -->
                    <?php if(!empty($page['image'])): ?>
                        <img class="img-fluid w-100"
                             src="<?= htmlspecialchars(URLBASE . $page['image']) ?>"
                             style="object-fit: cover; max-height: 500px;"
                             alt="<?= htmlspecialchars($page['title']) ?>">
                    <?php endif; ?>
                    
                    <div class="overlay position-relative bg-light">
                        
                        <!-- Tipo y fecha -->
                        <div class="mb-3">
                            <a href="<?= URLBASE ?>/institucional" class="text-secondary">
                                <i class="fa fa-arrow-left"></i> Volver al listado
                            </a>
                            <span class="px-2">/</span>
                            <span class="text-uppercase text-primary font-weight-medium">
                                <?= htmlspecialchars($typeName) ?>
                            </span>
                            <span class="px-1">/</span>
                            <span><?= fecha_espanol(date("F d, Y", strtotime($page['updated_at']))) ?></span>
                        </div>

                        <!-- Título -->
                        <h1 class="mb-4 display-4"><?= htmlspecialchars($page['title']) ?></h1>
                        
                        <!-- Contenido del artículo -->
                        <div class="post-content">
                            <?= $page['content'] ?>
                        </div>
                        
                        <!-- Fecha de actualización -->
                        <div class="border-top mt-5 pt-3">
                            <p class="text-muted mb-0">
                                <i class="fa fa-calendar mr-2"></i>
                                <small>Última actualización: <?= fecha_espanol(date("F d, Y", strtotime($page['updated_at']))) ?></small>
                            </p>
                        </div>
                        
                    </div>
                </div>
                
                <!-- Páginas relacionadas -->
                <?php
                $stmtRelated = db()->prepare("
                    SELECT title, slug, image, page_type
                    FROM institutional_pages
                    WHERE status = 'published'
                      AND id != ?
                      AND page_type = ?
                    ORDER BY display_order ASC
                    LIMIT 3
                ");
                $stmtRelated->execute([$page['id'], $page['page_type']]);
                $relatedPages = $stmtRelated->fetchAll();
                ?>

                <?php if($relatedPages): ?>
                    <div class="mt-4">
                        <div class="d-flex align-items-center justify-content-between bg-light py-2 px-4 mb-3 title-widgets">
                            <h3 class="m-0">También te puede interesar</h3>
                        </div>
                        
                        <div class="row">
                            <?php foreach($relatedPages as $rel): ?>
                                <div class="col-md-4 mb-3">
                                    <div class="position-relative">
                                        <?php if(!empty($rel['image'])): ?>
                                            <img class="img-fluid w-100"
                                                 src="<?= htmlspecialchars(URLBASE . $rel['image']) ?>"
                                                 style="object-fit: cover; height: 180px;"
                                                 alt="<?= htmlspecialchars($rel['title']) ?>">
                                        <?php else: ?>
                                            <div class="bg-secondary d-flex align-items-center justify-content-center" 
                                                 style="height: 180px;">
                                                <i class="fa fa-file-alt fa-3x text-white opacity-50"></i>
                                            </div>
                                        <?php endif; ?>
                                        
                                        <div class="overlay position-relative bg-light p-2">
                                            <a class="h6 d-block mt-1"
                                               href="<?= URLBASE ?>/institucional/<?= htmlspecialchars($rel['slug']) ?>">
                                               <?= htmlspecialchars($rel['title']) ?>
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endif; ?>
                
                <!-- Comentarios de Facebook (opcional) -->
                <div class="d-flex align-items-center justify-content-between bg-light py-2 px-4 mb-3 title-widgets mt-4">
                    <h3 class="m-0">Comentarios</h3>
                </div>
                <div class="bg-light">
                    <div class="fb-comments" 
                         data-href="<?= 'https://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'] ?>" 
                         data-width="100%" 
                         data-numposts="10" 
                         data-order-by="reverse_time">
                    </div>
                </div>
                
            </div>
            
            <!-- Sidebar -->
            <?php include __DIR__ . '/partials/sidebar.php'; ?>
        </div>
    </div>
</div>
<!-- Content End -->
