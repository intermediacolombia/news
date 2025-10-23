<?php
// =======================
// Variables SEO dinámicas
// =======================
$page_title       = "Pagina no encontrada |" . NOMBRE_SITIO;
$page_description = "Error en encontrar la pagina"  . NOMBRE_SITIO;
$page_keywords    = NOMBRE_SITIO . ", noticias, informacion, " . NOMBRE_SITIO;
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
<div class="container text-center py-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <!-- Imagen ilustrativa -->
            <img src="<?= URLBASE ?>/template/news/img/page-crash.webp" 
                 alt="Página no encontrada" 
                 class="img-fluid mb-4" 
                 style="max-height: 250px;">

            <!-- Código de error -->
            <h1 class="display-3 fw-bold text-danger">404</h1>
            <h2 class="mb-3">¡Ups! Página no encontrada</h2>

            <!-- Mensaje -->
            <p class="text-muted mb-4" style="font-size: 1.1rem;">
                Lo sentimos, la página que buscas no existe o fue movida.<br>
                Te invitamos a volver al inicio o explorar las últimas noticias.
            </p>

            <!-- Botones -->
            <div class="d-flex justify-content-center gap-3">
                <a href="<?= URLBASE ?>/" class="btn btn-primary btn-lg">
                    <i class="fa fa-home me-2"></i> Ir al inicio
                </a>
                <a href="<?= URLBASE ?>/noticias/" class="btn btn-outline-secondary btn-lg">
                    <i class="fa fa-newspaper me-2"></i> Ver noticias
                </a>
            </div>
        </div>
    </div>
</div>
