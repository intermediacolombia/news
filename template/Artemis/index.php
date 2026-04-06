<?php
$page_title       = !empty($sys['seo_home_title']) 
    ? $sys['seo_home_title'] 
    : NOMBRE_SITIO;

$page_description = !empty($sys['seo_home_description']) 
    ? $sys['seo_home_description'] 
    : "Bienvenido a " . NOMBRE_SITIO;

$page_keywords    = !empty($sys['seo_home_keywords']) 
    ? $sys['seo_home_keywords'] 
    : NOMBRE_SITIO . ", noticias, artículos";

$page_image = rtrim(URLBASE, '/') . FAVICON;
$currentPath    = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$page_canonical = rtrim(URLBASE, '/') . '/' . ltrim($currentPath, '/');

if (!function_exists('img_url')) {
    function img_url(?string $path): string {
        if (empty($path)) return URLBASE . '/template/Artemis/img/placeholder.jpg';
        if (preg_match('#^https?://#i', $path)) return $path;
        return URLBASE . '/' . ltrim($path, '/');
    }
}

if (!function_exists('truncate_text')) {
    function truncate_text(string $text, int $limit = 80): string {
        $text = htmlspecialchars($text, ENT_QUOTES, 'UTF-8');
        if (mb_strlen($text) <= $limit) return $text;
        return mb_substr($text, 0, $limit) . '...';
    }
}

$sliderNews = db()->query("
    SELECT p.id, p.title, p.slug, p.image, p.created_at, p.author,
           c.name AS category_name, c.slug AS category_slug
    FROM blog_posts p
    LEFT JOIN blog_post_category pc ON pc.post_id = p.id
    LEFT JOIN blog_categories c ON c.id = pc.category_id
    WHERE p.status = 'published' AND p.deleted = 0
    GROUP BY p.id
    ORDER BY p.created_at DESC
    LIMIT 5
")->fetchAll();

$featuredPosts = db()->query("
    SELECT p.id, p.title, p.slug, p.image, p.created_at, p.author,
           c.name AS category_name, c.slug AS category_slug
    FROM blog_posts p
    LEFT JOIN blog_post_category pc ON pc.post_id = p.id
    LEFT JOIN blog_categories c ON c.id = pc.category_id
    WHERE p.status = 'published' AND p.deleted = 0
    GROUP BY p.id
    ORDER BY p.created_at DESC
    LIMIT 8
")->fetchAll();

$latestPosts = db()->query("
    SELECT p.id, p.title, p.slug, p.image, p.created_at, p.author,
           c.name AS category_name, c.slug AS category_slug
    FROM blog_posts p
    LEFT JOIN blog_post_category pc ON pc.post_id = p.id
    LEFT JOIN blog_categories c ON c.id = pc.category_id
    WHERE p.status = 'published' AND p.deleted = 0
    GROUP BY p.id
    ORDER BY p.created_at DESC
    LIMIT 6
")->fetchAll();
?>

<section class="hero-section py-5">
    <div class="pattern-overlay"></div>
    <div class="container position-relative">
        <div class="row align-items-center">
            <div class="col-lg-7">
                <div class="swiper heroSwiper">
                    <div class="swiper-wrapper">
                        <?php foreach ($sliderNews as $index => $slide): 
                            $postUrl = URLBASE . "/" . htmlspecialchars($slide['category_slug']) . "/" . htmlspecialchars($slide['slug']) . "/";
                        ?>
                        <div class="swiper-slide">
                            <div class="hero-card" style="position: relative; border-radius: 20px; overflow: hidden; height: 450px;">
                                <img src="<?= img_url($slide['image']) ?>" 
                                     alt="<?= htmlspecialchars($slide['title']) ?>" 
                                     style="width: 100%; height: 100%; object-fit: cover;">
                                <div style="position: absolute; bottom: 0; left: 0; right: 0; background: linear-gradient(transparent, rgba(0,0,0,0.9)); padding: 40px 30px;">
                                    <span class="category-badge mb-3 d-inline-block"><?= htmlspecialchars($slide['category_name']) ?></span>
                                    <h2 class="hero-title mb-3" style="font-size: 1.8rem; color: #fff;">
                                        <a href="<?= $postUrl ?>" style="color: #fff; text-decoration: none;">
                                            <?= truncate_text($slide['title'], 80) ?>
                                        </a>
                                    </h2>
                                    <div style="color: rgba(255,255,255,0.7); font-size: 14px;">
                                        <i class="far fa-calendar mr-2"></i>
                                        <?= date('d M, Y', strtotime($slide['created_at'])) ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <div class="swiper-pagination"></div>
                </div>
            </div>
            
            <div class="col-lg-5">
                <div class="hero-sidebar">
                    <?php 
                    $sidebarNews = array_slice($sliderNews, 0, 3);
                    foreach ($sidebarNews as $side): 
                        $postUrl = URLBASE . "/" . htmlspecialchars($side['category_slug']) . "/" . htmlspecialchars($side['slug']) . "/";
                    ?>
                    <div class="hero-sidebar-item mb-3" style="background: var(--dark-secondary); border-radius: 16px; overflow: hidden; display: flex; transition: all 0.3s ease;">
                        <div style="width: 100px; flex-shrink: 0;">
                            <img src="<?= img_url($side['image']) ?>" 
                                 alt="<?= htmlspecialchars($side['title']) ?>" 
                                 style="width: 100%; height: 100%; object-fit: cover; min-height: 80px;">
                        </div>
                        <div class="p-3" style="flex: 1;">
                            <span style="font-size: 11px; color: var(--primary); font-weight: 600; text-transform: uppercase;">
                                <?= htmlspecialchars($side['category_name']) ?>
                            </span>
                            <h5 style="font-size: 14px; color: var(--text-color); margin: 5px 0; line-height: 1.4;">
                                <a href="<?= $postUrl ?>" style="color: inherit; text-decoration: none;">
                                    <?= truncate_text($side['title'], 50) ?>
                                </a>
                            </h5>
                            <span style="font-size: 12px; color: var(--text-muted);">
                                <?= date('d M', strtotime($side['created_at'])) ?>
                            </span>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
</section>

<section class="py-5" style="background: var(--dark);">
    <div class="container">
        <?php include __DIR__ . '/partials/ads3.php'; ?>
    </div>
</section>

<section class="py-5" style="background: var(--dark);">
    <div class="container">
        <div class="row mb-4">
            <div class="col-12">
                <h2 class="section-title" style="color: #fff;">LO MÁS DESTACADO</h2>
            </div>
        </div>
        
        <div class="row">
            <?php foreach ($featuredPosts as $post): 
                $postUrl = URLBASE . "/" . htmlspecialchars($post['category_slug']) . "/" . htmlspecialchars($post['slug']) . "/";
            ?>
            <div class="col-lg-4 col-md-6 mb-4">
                <div class="news-card">
                    <div class="position-relative" style="overflow: hidden;">
                        <a href="<?= $postUrl ?>">
                            <img src="<?= img_url($post['image']) ?>" 
                                 alt="<?= htmlspecialchars($post['title']) ?>" 
                                 class="card-img"
                                 style="width: 100%; height: 220px; object-fit: cover;">
                        </a>
                        <span class="category-badge position-absolute" style="top: 15px; left: 15px;">
                            <?= htmlspecialchars($post['category_name']) ?>
                        </span>
                    </div>
                    <div class="p-4">
                        <h4 style="color: #fff; font-size: 18px; font-weight: 600; margin-bottom: 10px; line-height: 1.4;">
                            <a href="<?= $postUrl ?>" style="color: inherit; text-decoration: none;">
                                <?= truncate_text($post['title'], 60) ?>
                            </a>
                        </h4>
                        <div style="color: var(--text-muted); font-size: 13px;">
                            <i class="far fa-calendar mr-2"></i>
                            <?= date('d M, Y', strtotime($post['created_at'])) ?>
                        </div>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<section class="py-5" style="background: var(--dark-secondary);">
    <div class="container">
        <?php include __DIR__ . '/partials/ads4.php'; ?>
    </div>
</section>

<section class="py-5" style="background: var(--dark);">
    <div class="container">
        <div class="row">
            <div class="col-lg-8">
                <div class="row mb-4">
                    <div class="col-12">
                        <h2 class="section-title" style="color: #fff;">ÚLTIMAS NOTICIAS</h2>
                    </div>
                </div>
                
                <div class="row">
                    <?php foreach ($latestPosts as $post): 
                        $postUrl = URLBASE . "/" . htmlspecialchars($post['category_slug']) . "/" . htmlspecialchars($post['slug']) . "/";
                    ?>
                    <div class="col-md-6 mb-4">
                        <div class="news-card">
                            <div class="position-relative" style="overflow: hidden;">
                                <a href="<?= $postUrl ?>">
                                    <img src="<?= img_url($post['image']) ?>" 
                                         alt="<?= htmlspecialchars($post['title']) ?>" 
                                         class="card-img"
                                         style="width: 100%; height: 180px; object-fit: cover;">
                                </a>
                                <span class="category-badge position-absolute" style="top: 12px; left: 12px; font-size: 10px;">
                                    <?= htmlspecialchars($post['category_name']) ?>
                                </span>
                            </div>
                            <div class="p-3">
                                <h5 style="color: #fff; font-size: 16px; font-weight: 600; margin-bottom: 8px; line-height: 1.4;">
                                    <a href="<?= $postUrl ?>" style="color: inherit; text-decoration: none;">
                                        <?= truncate_text($post['title'], 55) ?>
                                    </a>
                                </h5>
                                <span style="color: var(--text-muted); font-size: 12px;">
                                    <i class="far fa-calendar mr-1"></i>
                                    <?= date('d M, Y', strtotime($post['created_at'])) ?>
                                </span>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                
                <div class="text-center mt-4">
                    <a href="<?= URLBASE ?>/noticias/" class="btn-artemis">
                        Ver Todas las Noticias <i class="fas fa-arrow-right ml-2"></i>
                    </a>
                </div>
            </div>
            
            <div class="col-lg-4">
                <?php include __DIR__ . '/partials/sidebar.php'; ?>
            </div>
        </div>
    </div>
</section>

<section class="py-5" style="background: var(--dark-secondary);">
    <div class="container">
        <?php include __DIR__ . '/partials/columnists.php'; ?>
    </div>
</section>

<section class="py-5" style="background: var(--dark);">
    <div class="container">
        <?php include __DIR__ . '/partials/categories.php'; ?>
    </div>
</section>

<style>
    .hero-card:hover {
        transform: scale(1.02);
        transition: transform 0.4s ease;
    }
    
    .hero-sidebar-item:hover {
        transform: translateX(5px);
    }
    
    .news-card:hover .card-img {
        transform: scale(1.05);
    }
    
    @media (max-width: 991px) {
        .hero-sidebar {
            margin-top: 30px;
        }
    }
</style>