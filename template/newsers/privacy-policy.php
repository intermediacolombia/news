<?php
// =======================
// Variables SEO dinámicas
// =======================
$page_title       = "Política de Privacidad | " . NOMBRE_SITIO;
$page_description = "Conoce nuestra politica de privacidad";
$page_keywords    = NOMBRE_SITIO . ", politica de privacidad de " . NOMBRE_SITIO;
$page_author      = NOMBRE_SITIO;

// Imagen SEO → primera del producto o logo por defecto
$page_image = rtrim(URLBASE, '/') .FAVICON;
if (!empty($images)) {
    $path = $images[0]['path'];
    $path = ($path[0] === '/') ? $path : '/' . $path;
    $page_image = rtrim(URLBASE, '/') . $path;
}

// Canonical automático (desde URL actual)
$currentPath    = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$page_canonical = rtrim(URLBASE, '/') . '/' . ltrim($currentPath, '/');

// =======================
// Fin SEO
// =======================

?>
<div class="container-fluid py-5">
<div class="container py-5">
<!-- News With Sidebar Start -->
<div class="container-fluid py-3">
    <div class="container-bk">
        <div class="row">
            <div class="col-lg-8">
               <div class="bg-light" style= "padding: 50px;">
				   <?php
echo $sys['privacy-policy']
?>	
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
<!-- News With Sidebar End -->