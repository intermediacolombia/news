<?php
/**
 * Template: Página individual institucional
 * Ubicación: /template/NewsEdge/institucional-single.php
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

// Helper para imágenes
if (!function_exists('img_url')) {
    function img_url(?string $path): string {
        if (empty($path)) return URLBASE . '/template/newsedge/img/news/default.jpg';
        if (preg_match('#^https?://#i', $path)) return $path;
        return URLBASE . '/' . ltrim($path, '/');
    }
}
// =======================
?>

<!-- Institutional Single Page Start -->
<section class="bg-body section-space-less30">
    <div class="container">
        <div class="row">
            <!-- Contenido Principal -->
            <div class="col-lg-8 col-md-12 mb-30">
                <div class="item-box-light-md-less30">
                    
                    <!-- Breadcrumb / Botón volver -->
                    <div class="mb-30">
                        <a href="<?= URLBASE ?>/institucional/" class="btn-gtf-dtp-50">
                            <i class="fa fa-arrow-left" aria-hidden="true"></i>
                            Volver al listado
                        </a>
                    </div>
                    
                    <!-- Título de la página -->
                    <div class="topic-border color-cinnabar mb-30">
                        <h1 class="title-bold-dark size-xl mb-0">
                            <?= htmlspecialchars($page['title']) ?>
                        </h1>
                    </div>
                    
                    <!-- Imagen destacada -->
                    <?php if(!empty($page['image'])): ?>
                    <div class="institutional-featured-img mb-30">
                        <img src="<?= img_url($page['image']) ?>" 
                             alt="<?= htmlspecialchars($page['title']) ?>"
                             class="img-fluid width-100"
                             style="max-height: 500px; object-fit: cover; border-radius: 4px;">
                    </div>
                    <?php endif; ?>
                    
                    <!-- Contenido de la página -->
                    <div class="institutional-content item-box-light-md p-30-r mb-30">
                        <div class="content-body">
                            <?= $page['content'] ?>
                        </div>
                    </div>
                    
                    <!-- Fecha de actualización -->
                    <div class="institutional-meta pt-20 mt-20">
                        <div class="post-date-dark">
                            <ul>
                                <li>
                                    <span><i class="fa fa-calendar" aria-hidden="true"></i></span>
                                    <span>
                                        Última actualización: <?= date('d/m/Y', strtotime($page['updated_at'])) ?>
                                    </span>
                                </li>
                            </ul>
                        </div>
                    </div>
                    
                    <!-- Botón compartir (opcional) -->
                    <div class="institutional-share mt-30 item-box-light-md p-30-r">
                        <div class="row">
                            <div class="col-12 mb-20">
                                <h4 class="title-semibold-dark size-md mb-0">
                                    Compartir esta página
                                </h4>
                            </div>
                            <div class="col-12">
                                <ul class="social-default item-inline">
                                    <li>
                                        <a href="https://www.facebook.com/sharer/sharer.php?u=<?= urlencode($page_canonical) ?>" 
                                           target="_blank"
                                           class="facebook"
                                           title="Compartir en Facebook">
                                            <i class="fa fa-facebook" aria-hidden="true"></i>
                                        </a>
                                    </li>
                                    <li>
                                        <a href="https://twitter.com/intent/tweet?url=<?= urlencode($page_canonical) ?>&text=<?= urlencode($page['title']) ?>" 
                                           target="_blank"
                                           class="twitter"
                                           title="Compartir en Twitter">
                                            <i class="fa fa-twitter" aria-hidden="true"></i>
                                        </a>
                                    </li>
                                    <li>
                                        <a href="https://www.linkedin.com/shareArticle?mini=true&url=<?= urlencode($page_canonical) ?>&title=<?= urlencode($page['title']) ?>" 
                                           target="_blank"
                                           class="linkedin"
                                           title="Compartir en LinkedIn">
                                            <i class="fa fa-linkedin" aria-hidden="true"></i>
                                        </a>
                                    </li>
                                    <li>
                                        <a href="https://api.whatsapp.com/send?text=<?= urlencode($page['title'] . ' ' . $page_canonical) ?>" 
                                           target="_blank"
                                           class="whatsapp"
                                           title="Compartir en WhatsApp">
                                            <i class="fa fa-whatsapp" aria-hidden="true"></i>
                                        </a>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>
                    
                </div>
            </div>
            
            <!-- Sidebar -->
            <div class="ne-sidebar sidebar-break-md col-lg-4 col-md-12">
                <?php include __DIR__ . '/partials/sidebar.php'; ?>            
            </div>
        </div>
    </div>
</section>
<!-- Institutional Single Page End -->

<style>
    /* Estilos para el contenido institucional */
    .institutional-content .content-body h2,
    .institutional-content .content-body h3,
    .institutional-content .content-body h4 {
        color: #000;
        font-weight: 600;
        margin-top: 25px;
        margin-bottom: 15px;
    }
    
    .institutional-content .content-body h2 {
        font-size: 28px;
        border-bottom: 2px solid var(--primary);
        padding-bottom: 10px;
    }
    
    .institutional-content .content-body h3 {
        font-size: 24px;
    }
    
    .institutional-content .content-body h4 {
        font-size: 20px;
    }
    
    .institutional-content .content-body p {
        margin-bottom: 20px;
        color: #333;
        line-height: 1.8;
    }
    
    .institutional-content .content-body ul,
    .institutional-content .content-body ol {
        margin-bottom: 20px;
        padding-left: 30px;
    }
    
    .institutional-content .content-body li {
        margin-bottom: 10px;
        color: #333;
    }
    
    .institutional-content .content-body img {
        max-width: 100%;
        height: auto;
        border-radius: 4px;
        margin: 20px 0;
    }
    
    .institutional-content .content-body blockquote {
        border-left: 4px solid var(--primary);
        padding-left: 20px;
        margin: 25px 0;
        font-style: italic;
        color: #666;
    }
    
    .institutional-content .content-body table {
        width: 100%;
        border-collapse: collapse;
        margin: 20px 0;
    }
    
    .institutional-content .content-body table th,
    .institutional-content .content-body table td {
        border: 1px solid #ddd;
        padding: 12px;
        text-align: left;
    }
    
    .institutional-content .content-body table th {
        background-color: #f5f5f5;
        font-weight: 600;
        color: #000;
    }
    
    /* Botones sociales */
    .social-default li a {
        height: 44px;
        width: 48px;
        line-height: 42px;
        text-align: center;
        color: #fff;
        font-size: 16px;
        display: block;
        border: 2px solid transparent;
        transition: all 0.5s ease-out;
        margin-right: 8px;
    }
    
    .social-default li a.facebook {
        background-color: #516eab;
        border-color: #516eab;
    }
    
    .social-default li a.facebook:hover {
        background-color: transparent;
        color: #516eab;
    }
    
    .social-default li a.twitter {
        background-color: #29c5f6;
        border-color: #29c5f6;
    }
    
    .social-default li a.twitter:hover {
        background-color: transparent;
        color: #29c5f6;
    }
    
    .social-default li a.linkedin {
        background-color: #1976d2;
        border-color: #1976d2;
    }
    
    .social-default li a.linkedin:hover {
        background-color: transparent;
        color: #1976d2;
    }
    
    .social-default li a.whatsapp {
        background-color: #25d366;
        border-color: #25d366;
    }
    
    .social-default li a.whatsapp:hover {
        background-color: transparent;
        color: #25d366;
    }
    
    /* Responsive */
    @media (max-width: 768px) {
        .institutional-content {
            padding: 20px !important;
        }
        
        .institutional-share {
            padding: 20px !important;
        }
        
        .institutional-content .content-body {
            font-size: 15px;
        }
        
        .institutional-content .content-body h2 {
            font-size: 24px;
        }
        
        .institutional-content .content-body h3 {
            font-size: 20px;
        }
    }
</style>
