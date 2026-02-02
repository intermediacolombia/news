<?php
require_once __DIR__ . '/../../inc/config.php';

// Helper para truncar texto
if (!function_exists('truncate_text')) {
    function truncate_text(string $text, int $limit = 100): string {
        $text = strip_tags($text);
        return (mb_strlen($text) > $limit) ? mb_substr($text, 0, $limit) . '...' : $text;
    }
}

// Helper para imágenes
if (!function_exists('img_url')) {
    function img_url(?string $path): string {
        if (empty($path)) {
            return URLBASE . '/template/NewsEdge/img/placeholder.jpg';
        }
        if (filter_var($path, FILTER_VALIDATE_URL)) {
            return $path;
        }
        $path = ltrim($path, '/');
        return URLBASE . '/' . $path;
    }
}

$q = trim($_GET['q'] ?? '');
$results = [];
$totalResults = 0;

if ($q !== '') {
    $stmt = db()->prepare("
        SELECT p.*, c.name AS category_name, c.slug AS category_slug
        FROM blog_posts p
        INNER JOIN blog_post_category pc ON pc.post_id = p.id
        INNER JOIN blog_categories c ON c.id = pc.category_id
        WHERE (p.title LIKE :q OR p.content LIKE :q OR p.seo_description LIKE :q)
          AND p.status='published' AND p.deleted=0
        GROUP BY p.id
        ORDER BY p.created_at DESC
    ");
    $stmt->execute([':q' => "%$q%"]);
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $totalResults = count($results);
}

// =======================
// Variables SEO dinámicas
// =======================
$page_title       = !empty($q) 
    ? "Resultados de búsqueda para \"" . htmlspecialchars($q) . "\" | " . $sys['site_name']
    : "Búsqueda | " . $sys['site_name'];
$page_description = !empty($q)
    ? "Resultados de búsqueda para: " . htmlspecialchars($q) . " en " . $sys['site_name']
    : "Busca noticias, artículos y contenido en " . $sys['site_name'];
$page_keywords    = htmlspecialchars($q) . ", búsqueda, " . $sys['site_name'];
$page_author      = $sys['site_name'];
$page_image       = URLBASE . SITE_LOGO;
$currentPath      = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$page_canonical   = rtrim(URLBASE, '/') . '/' . ltrim($currentPath, '/');


?>

<!-- Inner Page Banner Area Start Here -
<section class="inner-page-banner bg-common" style="background-image: url('<?= URLBASE ?>/template/NewsEdge/img/banner/inner-page-banner.jpg');">
    <div class="container">
        <div class="row">
            <div class="col-12">
                <div class="breadcrumbs-area">
                    <h1>Resultados de Búsqueda</h1>
                    <ul>
                        <li>
                            <a href="<?= URLBASE ?>">Inicio</a>
                        </li>
                        <li>Búsqueda</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</section>
<!-- Inner Page Banner Area End Here -->

<!-- Search Results Area Start -->
<section class="bg-body section-space-less30">
    <div class="container">
        <div class="row">
            <!-- Main Content -->
            <div class="col-xl-9 col-lg-8 col-md-12 col-sm-12">
                <!-- Search Info Box -->
                <div class="search-info-box mb-30">
                    <div class="topic-border color-cinnabar mb-20">
                        <div class="topic-box-lg color-cinnabar">
                            <?php if (!empty($q)): ?>
                                Resultados para: "<?= htmlspecialchars($q) ?>"
                            <?php else: ?>
                                Búsqueda
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <?php if (!empty($q)): ?>
                        <p class="search-results-count">
                            Se <?= $totalResults === 1 ? 'encontró' : 'encontraron' ?> 
                            <strong><?= $totalResults ?></strong> 
                            <?= $totalResults === 1 ? 'resultado' : 'resultados' ?>
                        </p>
                    <?php endif; ?>

                    <!-- Search Form -->
                    <form action="<?= URLBASE ?>/buscar/" method="get" class="search-page-form">
                        <div class="search-form-wrapper">
                            <input type="text" 
                                   name="q" 
                                   class="search-page-input" 
                                   placeholder="Buscar noticias, artículos..." 
                                   value="<?= htmlspecialchars($q) ?>"
                                   required>
                            <button type="submit" class="search-page-btn">
                                <i class="fa fa-search"></i> Buscar
                            </button>
                        </div>
                    </form>
                </div>

                <!-- Results -->
                <?php if (!empty($q)): ?>
                    <?php if ($results): ?>
                        <div class="row">
                            <?php foreach ($results as $p): 
                                $postUrl = URLBASE . "/" . htmlspecialchars($p['category_slug']) . "/" . htmlspecialchars($p['slug']) . "/";
                                $categoryUrl = URLBASE . "/" . htmlspecialchars($p['category_slug']) . "/";
                            ?>
                                <div class="col-lg-6 col-md-6 col-sm-12 mb-30">
                                    <div class="news-item-box item-shadow-1">
                                        <div class="img-wrapper">
                                            <a href="<?= $postUrl ?>">
                                                <img src="<?= img_url($p['image']) ?>" 
                                                     alt="<?= htmlspecialchars($p['title']) ?>" 
                                                     class="img-fluid search-result-img">
                                            </a>
                                            <div class="category-badge">
                                                <a href="<?= $categoryUrl ?>">
                                                    <?= htmlspecialchars($p['category_name']) ?>
                                                </a>
                                            </div>
                                        </div>
                                        <div class="news-content-box">
                                            <h3 class="title-medium-dark size-lg mb-10">
                                                <a href="<?= $postUrl ?>">
                                                    <?= htmlspecialchars($p['title']) ?>
                                                </a>
                                            </h3>
                                            <ul class="post-meta mb-10">
                                                <li>
                                                    <i class="fa fa-calendar" aria-hidden="true"></i>
                                                    <?= date('d M Y', strtotime($p['created_at'])) ?>
                                                </li>
                                                <?php if (!empty($p['author'])): ?>
                                                <li>
                                                    <i class="fa fa-user" aria-hidden="true"></i>
                                                    <?= htmlspecialchars($p['author']) ?>
                                                </li>
                                                <?php endif; ?>
                                            </ul>
                                            <p class="description-body-dark">
                                                <?= truncate_text($p['seo_description'] ?: $p['content'], 120) ?>
                                            </p>
                                            <a href="<?= $postUrl ?>" class="read-more-link">
                                                Leer más <i class="fa fa-long-arrow-right"></i>
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <!-- No Results -->
                        <div class="no-results-box">
                            <div class="no-results-icon">
                                <i class="fa fa-search"></i>
                            </div>
                            <h3 class="no-results-title">No se encontraron resultados</h3>
                            <p class="no-results-text">
                                No pudimos encontrar ningún resultado para "<strong><?= htmlspecialchars($q) ?></strong>".
                            </p>
                            <div class="no-results-suggestions">
                                <h4>Sugerencias:</h4>
                                <ul>
                                    <li>Verifica que todas las palabras estén escritas correctamente</li>
                                    <li>Intenta con palabras clave diferentes</li>
                                    <li>Intenta con palabras clave más generales</li>
                                    <li>Intenta con menos palabras clave</li>
                                </ul>
                            </div>
                            <a href="<?= URLBASE ?>" class="btn-back-home">
                                <i class="fa fa-home"></i> Volver al inicio
                            </a>
                        </div>
                    <?php endif; ?>
                <?php else: ?>
                    <!-- Empty Search State -->
                    <div class="empty-search-box">
                        <div class="empty-search-icon">
                            <i class="fa fa-search"></i>
                        </div>
                        <h3 class="empty-search-title">¿Qué estás buscando?</h3>
                        <p class="empty-search-text">
                            Utiliza el formulario de búsqueda para encontrar noticias, artículos y contenido.
                        </p>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Sidebar -->
            <div class="col-xl-3 col-lg-4 col-md-12 col-sm-12 sidebar-break-md mb-30">
                <?php include __DIR__ . '/partials/sidebar.php'; ?>
            </div>
        </div>
    </div>
</section>
<!-- Search Results Area End -->

<style>
    /* Search Info Box */
    .search-info-box {
        background: #fff;
        padding: 30px;
        border-radius: 4px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.08);
    }
    
    .search-results-count {
        margin-bottom: 20px;
        font-size: 16px;
        color: #666;
    }
    
    /* Search Form */
    .search-form-wrapper {
        display: flex;
        gap: 10px;
        margin-top: 20px;
    }
    
    .search-page-input {
        flex: 1;
        height: 50px;
        padding: 0 20px;
        border: 2px solid #e0e0e0;
        border-radius: 4px;
        font-size: 15px;
        transition: all 0.3s ease;
    }
    
    .search-page-input:focus {
        outline: none;
        border-color: var(--primary, #c41e3a);
        box-shadow: 0 0 0 3px rgba(196, 30, 58, 0.1);
    }
    
    .search-page-btn {
        height: 50px;
        padding: 0 30px;
        background: #000;
        color: white;
        border: none;
        border-radius: 4px;
        font-size: 15px;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.3s ease;
        white-space: nowrap;
    }
    
    .search-page-btn:hover {
        background: var(--primary, #c41e3a);
    }
    
    /* News Item Box */
    .news-item-box {
        background: #fff;
        border-radius: 4px;
        overflow: hidden;
        transition: all 0.3s ease;
        height: 100%;
        display: flex;
        flex-direction: column;
    }
    
    .news-item-box:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 30px rgba(0,0,0,0.15) !important;
    }
    
    .img-wrapper {
        position: relative;
        overflow: hidden;
    }
    
    .search-result-img {
        width: 100%;
        height: 220px;
        object-fit: cover;
        transition: transform 0.3s ease;
    }
    
    .news-item-box:hover .search-result-img {
        transform: scale(1.05);
    }
    
    .category-badge {
        position: absolute;
        top: 15px;
        left: 15px;
        background: var(--primary, #c41e3a);
        padding: 5px 15px;
        border-radius: 3px;
    }
    
    .category-badge a {
        color: white;
        font-size: 12px;
        font-weight: 600;
        text-transform: uppercase;
    }
    
    .news-content-box {
        padding: 20px;
        flex: 1;
        display: flex;
        flex-direction: column;
    }
    
    .post-meta {
        list-style: none;
        padding: 0;
        margin: 0;
        display: flex;
        flex-wrap: wrap;
        gap: 15px;
        font-size: 13px;
        color: #999;
    }
    
    .post-meta i {
        margin-right: 5px;
    }
    
    .read-more-link {
        color: #000;
        font-weight: 600;
        margin-top: auto;
        display: inline-block;
        transition: color 0.3s ease;
    }
    
    .read-more-link:hover {
        color: var(--primary, #c41e3a);
    }
    
    /* No Results */
    .no-results-box,
    .empty-search-box {
        background: #fff;
        padding: 60px 40px;
        border-radius: 4px;
        text-align: center;
        box-shadow: 0 2px 10px rgba(0,0,0,0.08);
    }
    
    .no-results-icon,
    .empty-search-icon {
        font-size: 80px;
        color: #ddd;
        margin-bottom: 20px;
    }
    
    .no-results-title,
    .empty-search-title {
        font-size: 28px;
        font-weight: 700;
        color: #333;
        margin-bottom: 15px;
    }
    
    .no-results-text,
    .empty-search-text {
        font-size: 16px;
        color: #666;
        margin-bottom: 30px;
    }
    
    .no-results-suggestions {
        text-align: left;
        max-width: 500px;
        margin: 30px auto;
        padding: 20px;
        background: #f9f9f9;
        border-radius: 4px;
    }
    
    .no-results-suggestions h4 {
        font-size: 18px;
        font-weight: 600;
        margin-bottom: 15px;
        color: #333;
    }
    
    .no-results-suggestions ul {
        list-style: none;
        padding: 0;
        margin: 0;
    }
    
    .no-results-suggestions li {
        padding: 8px 0;
        padding-left: 25px;
        position: relative;
        color: #666;
    }
    
    .no-results-suggestions li:before {
        content: "•";
        position: absolute;
        left: 10px;
        color: var(--primary, #c41e3a);
        font-weight: bold;
    }
    
    .btn-back-home {
        display: inline-block;
        padding: 12px 30px;
        background: #000;
        color: white;
        border-radius: 4px;
        font-weight: 600;
        transition: all 0.3s ease;
    }
    
    .btn-back-home:hover {
        background: var(--primary, #c41e3a);
        color: white;
        text-decoration: none;
    }
    
    /* Responsive */
    @media (max-width: 768px) {
        .search-form-wrapper {
            flex-direction: column;
        }
        
        .search-page-btn {
            width: 100%;
        }
        
        .search-info-box {
            padding: 20px;
        }
        
        .no-results-box,
        .empty-search-box {
            padding: 40px 20px;
        }
    }
</style>




