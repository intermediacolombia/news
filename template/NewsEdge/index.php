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
 
            <!-- News Feed Area End Here -->
            <!-- Slider Area Start Here -->
            <?php include __DIR__ . '/partials/home_latest_news.php'; ?>

             <?php include __DIR__ . '/partials/ads3.php'; ?>
            <!-- Slider Area End Here -->
            <!-- Popular Area Start Here -->
            <?php include __DIR__ . '/partials/features.php'; ?>

 			<?php include __DIR__ . '/partials/ads4.php'; ?>
            <!-- Popular Area End Here -->
            <!-- Latest Articles Area Start Here -->
            <section class="section-space-bottom-less30">
                <div class="container">
                    <div class="row">
                        
                      <?php include __DIR__ . '/partials/lastest.php'; ?>
						
						<div class="ne-sidebar sidebar-break-lg col-xl-4 col-lg-12">
                      <?php include __DIR__ . '/partials/sidebar.php'; ?>
						</div>	
                    </div>
                </div>
            </section>
            <!-- Latest Articles Area End Here -->
            <!-- Videos Area Start Here -->
           <?php include __DIR__ . '/partials/columnists.php'; ?>
            <!-- Videos Area Start Here -->
            <!-- Category Area Start Here -->
            <?php include __DIR__ . '/partials/categories.php'; ?>
            
            
                
