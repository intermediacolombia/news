<?php
// =======================
// Variables SEO dinámicas
// =======================
$page_title       = "Política de Privacidad | " . NOMBRE_SITIO;
$page_description = "Conoce nuestra política de privacidad";
$page_keywords    = NOMBRE_SITIO . ", política de privacidad de " . NOMBRE_SITIO;
$page_author      = NOMBRE_SITIO;

// Imagen SEO
$page_image = rtrim(URLBASE, '/') . FAVICON;
if (!empty($images)) {
    $path = $images[0]['path'];
    $path = ($path[0] === '/') ? $path : '/' . $path;
    $page_image = rtrim(URLBASE, '/') . $path;
}

// Canonical automático
$currentPath    = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$page_canonical = rtrim(URLBASE, '/') . '/' . ltrim($currentPath, '/');
?>

<!-- Breadcrumb Area Start Here -->
<section class="breadcrumbs-area" style="background-image: url('<?= URLBASE ?>/template/newsedge/img/breadcrumbs-bg.jpg');">
    <div class="container">
        <div class="breadcrumbs-content">
            <h1>Política de Privacidad</h1>
            <ul>
                <li>
                    <a href="<?= URLBASE ?>">Inicio</a>
                    <i class="fa fa-angle-right" aria-hidden="true"></i>
                </li>
                <li>Política de Privacidad</li>
            </ul>
        </div>
    </div>
</section>
<!-- Breadcrumb Area End Here -->

<!-- Privacy Policy Page Area Start Here -->
<section class="bg-body section-space-less30">
    <div class="container">
        <div class="row">
            
            <!-- Main Content -->
            <div class="col-lg-8 col-md-12 mb-30">
                <div class="ne-main-content">
                    <div class="single-post-layout1">
                        <div class="single-post-content">
                            <div class="item-description bg-white p-5 shadow-sm border-radius-4">
                                <?php 
                                    // Imprimimos el contenido de la política de privacidad
                                    echo $sys['privacy-policy']; 
                                ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Sidebar -->
            <div class="col-lg-4 col-md-12 mb-30">
                <div class="ne-sidebar">
                    <?php 
                        // Incluimos el sidebar dinámico que ya adaptamos
                        include __DIR__ . '/partials/sidebar.php'; 
                    ?>
                </div>
            </div>

        </div>
    </div>
</section>
<!-- Privacy Policy Page Area End Here -->