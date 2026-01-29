<?php
/**
 * Template: Listado de páginas institucionales
 * Ubicación: /template/TU_TEMA/institucional-list.php
 */

// Cargar todas las páginas institucionales publicadas
$sql = "SELECT id, title, slug, page_type, image, seo_description, display_order 
        FROM institutional_pages 
        WHERE status = 'published' 
        ORDER BY display_order ASC, title ASC";
$stmt = db()->query($sql);
$pages = $stmt->fetchAll();

// =======================
// Variables SEO dinámicas
// =======================
$page_title       = "Información Institucional | " . NOMBRE_SITIO;
$page_description = "Conoce más sobre " . NOMBRE_SITIO . ". Nuestra misión, visión, historia y valores corporativos.";
$page_keywords    = NOMBRE_SITIO . ", Quiénes Somos, Misión, Visión, Historia, Valores";
$page_author      = NOMBRE_SITIO;
$page_image       = rtrim(URLBASE, '/') . FAVICON;

$currentPath    = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$page_canonical = rtrim(URLBASE, '/') . '/' . ltrim($currentPath, '/');
// =======================

// Nombres de tipos
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

$typeIcons = [
    'general' => 'fa-info-circle',
    'about' => 'fa-building',
    'mission' => 'fa-bullseye',
    'history' => 'fa-history',
    'organization' => 'fa-sitemap',
    'board' => 'fa-users',
    'team' => 'fa-user-friends',
    'values' => 'fa-star',
    'policies' => 'fa-file-contract'
];
?>

<!-- Breadcrumb Start -->
<div class="container-fluid">
    <div class="container-bk">
        <nav class="breadcrumb bg-transparent m-0 p-0">
            <a class="breadcrumb-item" href="<?= URLBASE ?>">Home</a>
            <span class="breadcrumb-item active">Información Institucional</span>
        </nav>
    </div>
</div>
<!-- Breadcrumb End -->

<!-- Content Start -->
<div class="container-fluid py-3">
    <div class="container-bk">
        <div class="row">
            <div class="col-lg-8">
                
                <!-- Título principal -->
                <div class="d-flex align-items-center justify-content-between bg-light py-2 px-4 mb-3 title-widgets">
                    <h3 class="m-0">Información Institucional</h3>
                </div>
                
                <div class="bg-light p-4 mb-3">
                    <p class="lead mb-4">
                        Conoce más sobre nuestra organización, nuestra historia y nuestros valores.
                    </p>
                    
                    <?php if(empty($pages)): ?>
                        <div class="alert alert-info">
                            <i class="fa fa-info-circle"></i>
                            No hay información institucional disponible en este momento.
                        </div>
                    <?php else: ?>
                        
                        <!-- Grid de páginas -->
                        <div class="row">
                            <?php foreach($pages as $page): 
                                $typeName = $typeNames[$page['page_type']] ?? 'General';
                                $typeIcon = $typeIcons[$page['page_type']] ?? 'fa-file-alt';
                                $excerpt = $page['seo_description'] ?: substr(strip_tags($page['content'] ?? ''), 0, 150);
                            ?>
                            
                            <div class="col-md-6 mb-4">
                                <div class="position-relative h-100">
                                    
                                    <?php if(!empty($page['image'])): ?>
                                        <img class="img-fluid w-100" 
                                             src="<?= htmlspecialchars(URLBASE . $page['image']) ?>" 
                                             alt="<?= htmlspecialchars($page['title']) ?>"
                                             style="object-fit: cover; height: 220px;">
                                    <?php else: ?>
                                        <div class="bg-secondary d-flex align-items-center justify-content-center" 
                                             style="height: 220px;">
                                            <i class="fa <?= $typeIcon ?> fa-5x text-white opacity-50"></i>
                                        </div>
                                    <?php endif; ?>
                                    
                                    <div class="overlay position-relative bg-light">
                                        <div class="mb-2">
                                            <span class="badge bg-primary">
                                                <i class="fa <?= $typeIcon ?>"></i> 
                                                <?= htmlspecialchars($typeName) ?>
                                            </span>
                                        </div>
                                        
                                        <a class="h5 d-block mb-3" 
                                           href="<?= URLBASE ?>/institucional/<?= urlencode($page['slug']) ?>">
                                            <?= htmlspecialchars($page['title']) ?>
                                        </a>
                                        
                                        <p class="m-0 text-secondary">
                                            <?= htmlspecialchars(substr($excerpt, 0, 120)) ?>...
                                        </p>
                                        
                                        <div class="mt-3">
                                            <a href="<?= URLBASE ?>/institucional/<?= urlencode($page['slug']) ?>" 
                                               class="btn btn-sm btn-primary">
                                                Leer más <i class="fa fa-arrow-right ml-1"></i>
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <?php endforeach; ?>
                        </div>
                        
                    <?php endif; ?>
                    
                </div>
                
            </div>
            
            <!-- Sidebar -->
            <?php include __DIR__ . '/partials/sidebar.php'; ?>
        </div>
    </div>
</div>
<!-- Content End -->
