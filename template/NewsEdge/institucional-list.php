<?php
/**
 * Template: Listado de páginas institucionales
 * Ubicación: /template/NewsEdge/institucional-list.php
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

// Helpers
if (!function_exists('img_url')) {
    function img_url(?string $path): string {
        if (empty($path)) return URLBASE . '/template/newsedge/img/news/default.jpg';
        if (preg_match('#^https?://#i', $path)) return $path;
        return URLBASE . '/' . ltrim($path, '/');
    }
}

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

<!-- Institutional Pages Section Start -->
<section class="bg-body section-space-less30">
    <div class="container">
        <div class="row">
            <!-- Contenido Principal -->
            <div class="col-lg-8 col-md-12 mb-30">
                <div class="item-box-light-md-less30">
                    
                    <!-- Cabecera -->
                    <div class="topic-border color-cinnabar mb-40">
                        <div class="topic-box-lg color-cinnabar">Información Institucional</div>
                    </div>
                    
                    <div class="institutional-intro mb-40">
                        <p class="size-lg description-body-dark">
                            Conoce más sobre nuestra organización, nuestra historia y nuestros valores.
                        </p>
                    </div>
                    
                    <?php if(empty($pages)): ?>
                        <div class="item-box-light-md item-shadow-1 p-30 mb-30">
                            <div class="alert-info-custom">
                                <i class="fa fa-info-circle" aria-hidden="true"></i>
                                <span>No hay información institucional disponible en este momento.</span>
                            </div>
                        </div>
                    <?php else: ?>
                        
                        <!-- Grid de páginas institucionales -->
                        <div class="row">
                            <?php foreach($pages as $page): 
                                $typeName = $typeNames[$page['page_type']] ?? 'General';
                                $typeIcon = $typeIcons[$page['page_type']] ?? 'fa-file-alt';
                                $excerpt = $page['seo_description'] ?: substr(strip_tags($page['content'] ?? ''), 0, 150);
                                $pageUrl = URLBASE . '/institucional/' . urlencode($page['slug']) . '/';
                            ?>
                            
                            <div class="col-lg-6 col-md-6 col-sm-12 mb-30">
                                <div class="institutional-card item-box-light-md item-shadow-1">
                                    
                                    <?php if(!empty($page['image'])): ?>
                                    <div class="institutional-card-img position-relative">
                                        <a href="<?= $pageUrl ?>" class="img-opacity-hover">
                                            <img src="<?= img_url($page['image']) ?>" 
                                                 class="img-fluid width-100" 
                                                 alt="<?= htmlspecialchars($page['title']) ?>"
                                                 style="height: 200px; object-fit: cover;">
                                        </a>
                                        <div class="topic-box-top-sm">
                                            <div class="topic-box-sm color-cinnabar">
                                                <i class="fa <?= $typeIcon ?>" aria-hidden="true"></i>
                                                <?= htmlspecialchars($typeName) ?>
                                            </div>
                                        </div>
                                    </div>
                                    <?php else: ?>
                                    <div class="institutional-card-img institutional-card-no-img position-relative">
                                        <i class="fa <?= $typeIcon ?>" aria-hidden="true"></i>
                                        <div class="topic-box-top-sm">
                                            <div class="topic-box-sm color-cinnabar">
                                                <i class="fa <?= $typeIcon ?>" aria-hidden="true"></i>
                                                <?= htmlspecialchars($typeName) ?>
                                            </div>
                                        </div>
                                    </div>
                                    <?php endif; ?>
                                    
                                    <div class="institutional-card-body p-25">
                                        <h3 class="title-semibold-dark size-lg mb-15">
                                            <a href="<?= $pageUrl ?>" class="institutional-card-title">
                                                <?= htmlspecialchars($page['title']) ?>
                                            </a>
                                        </h3>
                                        
                                        <p class="description-body-dark mb-20 institutional-card-excerpt">
                                            <?= htmlspecialchars(substr($excerpt, 0, 120)) ?>...
                                        </p>
                                        
                                        <div class="more-info-link">
                                            <a href="<?= $pageUrl ?>" class="btn-text-dark">
                                                Leer más
                                                <i class="fa fa-angle-right" aria-hidden="true"></i>
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
            <div class="ne-sidebar sidebar-break-md col-lg-4 col-md-12">
                <?php include __DIR__ . '/partials/sidebar.php'; ?>            
            </div>
        </div>
    </div>
</section>
<!-- Institutional Pages Section End -->

<style>
    /* Tarjetas institucionales */
    .institutional-card {
        border-radius: 4px;
        overflow: hidden;
        height: 100%;
        display: flex;
        flex-direction: column;
        transition: all 0.3s ease;
    }
    
    .institutional-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 15px 40px rgba(0,0,0,0.15) !important;
    }
    
    .institutional-card-img {
        position: relative;
    }
    
    .institutional-card-no-img {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        height: 200px;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    
    .institutional-card-no-img > i {
        font-size: 64px;
        color: rgba(255,255,255,0.9);
    }
    
    .institutional-card-body {
        flex: 1;
        display: flex;
        flex-direction: column;
    }
    
    .institutional-card-title {
        color: #000;
        transition: color 0.3s ease;
        text-decoration: none;
    }
    
    .institutional-card:hover .institutional-card-title {
        color: var(--primary);
    }
    
    .institutional-card-excerpt {
        line-height: 1.7;
        flex: 1;
    }
    
    .btn-text-dark {
        color: #000;
        font-weight: 600;
        text-decoration: none;
        transition: all 0.3s ease;
        display: inline-block;
    }
    
    .btn-text-dark:hover {
        color: var(--primary);
        padding-left: 5px;
    }
    
    .btn-text-dark i {
        transition: transform 0.3s ease;
        margin-left: 5px;
    }
    
    .btn-text-dark:hover i {
        transform: translateX(5px);
    }
    
    /* Alert personalizado */
    .alert-info-custom {
        background: #e3f2fd;
        border-left: 4px solid #2196f3;
        border-radius: 4px;
        padding: 15px;
        display: flex;
        align-items: center;
    }
    
    .alert-info-custom i {
        color: #2196f3;
        font-size: 20px;
        margin-right: 10px;
    }
    
    .alert-info-custom span {
        color: #1976d2;
        font-weight: 500;
    }
    
    /* Responsive adjustments /
    @media (max-width: 768px) {
        .institutional-card-body {
            padding: 20px !important;
        }
        
        .institutional-card-img,
        .institutional-card-no-img {
            height: 180px !important;
        }
        
        .institutional-card-no-img > i {
            font-size: 48px;
        }
    }
</style>