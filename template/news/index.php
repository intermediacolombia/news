<?php
// =======================
// Variables SEO dinÃ¡micas
// =======================
$page_title       = !empty($sys['seo_home_title']) 
                    ? $sys['seo_home_title'] 
                    : NOMBRE_SITIO;

$page_description = !empty($sys['seo_home_description']) 
                    ? $sys['seo_home_description'] 
                    : "Bienvenido a " . NOMBRE_SITIO;

$page_keywords    = !empty($sys['seo_home_keywords']) 
                    ? $sys['seo_home_keywords'] 
                    : NOMBRE_SITIO . ", tienda online, comprar, ofertas";


// Imagen SEO â†’ primera del producto o logo por defecto
$page_image = rtrim(URLBASE, '/') .FAVICON;
if (!empty($images)) {
    $path = $images[0]['path'];
    $path = ($path[0] === '/') ? $path : '/' . $path;
    $page_image = rtrim(URLBASE, '/') . $path;
}

// Canonical automÃ¡tico (desde URL actual)
$currentPath    = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$page_canonical = rtrim(URLBASE, '/') . '/' . ltrim($currentPath, '/');

// =======================
// Fin SEO
// =======================

?>
 


    


    <?php include __DIR__ . '/partials/news-carrusel-home-1.php'; ?>
    <?php include __DIR__ . '/partials/news-carrusel-home.php'; ?>
    <?php include __DIR__ . '/partials/most-read.php'; ?>
    <?php include __DIR__ . '/partials/ads3.php'; ?>
    <?php include __DIR__ . '/partials/home-categories-carrusel.php'; ?>
	<?php include __DIR__ . '/partials/ads4.php'; ?>

    


   


    <!-- News With Sidebar Start -->
    <div class="container-fluid py-3">
        <div class="container-bk">
            <div class="row">
                <div class="col-lg-8">
                 
					<?php include __DIR__ . '/partials/popular.php'; ?>
                    
					
					
                    <div class="mb-3 pb-3">
                        <?php
    $stmt = db()->prepare("
        SELECT * FROM ads 
        WHERE position = 2 AND status = 'active' 
        LIMIT 1
    ");
    $stmt->execute();
    $ad = $stmt->fetch(PDO::FETCH_ASSOC);
    ?>

    <?php if ($ad && !empty($ad['image_url'])): ?>
        <?php if (!empty($ad['target_url'])): ?>
            <a href="<?= htmlspecialchars($ad['target_url']) ?>" target="_blank" rel="noopener">
                <img class="img-fluid"
                     src="<?= URLBASE . htmlspecialchars($ad['image_url']) ?>"
                     alt="<?= htmlspecialchars($ad['title'] ?? 'Publicidad') ?>">
            </a>
        <?php else: ?>
            <img class="img-fluid"
                 src="<?= URLBASE . htmlspecialchars($ad['image_url']) ?>"
                 alt="<?= htmlspecialchars($ad['title'] ?? 'Publicidad') ?>">
        <?php endif; ?>
    <?php endif; ?>
                    </div>
					
                    <?php include __DIR__ . '/partials/latest.php'; ?>
                    
					<?php if (!empty($sys['banner_inferior'])): ?>
  <div class="footer-banner text-center my-3">
    <img src="<?= htmlspecialchars($sys['banner_inferior'], ENT_QUOTES, 'UTF-8') ?>" alt="Banner Inferior" class="img-fluid">
  </div>
<?php endif; ?>
					
                </div>
                
                 <?php include __DIR__ . '/partials/sidebar.php'; ?>
            </div>
        </div>
    </div>

    

    <!-- News With Sidebar End -->


    