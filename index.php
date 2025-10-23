<?php
require_once __DIR__ . '/inc/config.php';

$uri   = trim(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH), '/');
$parts = explode('/', $uri);

$templateFile = null;

// ===============================
// Búsqueda: /buscar/termino/
// ===============================
if ($parts[0] === 'buscar') {
    $_GET['page'] = 'search';

    // si lo mandas como /buscar/termino/
    if (!empty($parts[1])) {
        $_GET['q'] = urldecode($parts[1]);
    }
    // si lo mandas como /buscar/?q=termino
    elseif (isset($_GET['q'])) {
        $_GET['q'] = urldecode($_GET['q']);
    }

    $templateFile = __DIR__ . "/template/newsers/search.php";

// ===============================
// Noticias
// ===============================
} elseif ($parts[0] === 'noticias') {
    $_GET['page'] = 'noticias';

    // /noticias/page/2/
    if (isset($parts[1]) && $parts[1] === 'page') {
        $_GET['page_num'] = (int)($parts[2] ?? 1);

    // /noticias/categoria/page/3/
    } elseif (isset($parts[2]) && $parts[2] === 'page') {
        $_GET['slug']     = $parts[1];
        $_GET['page_num'] = (int)($parts[3] ?? 1);

    // /noticias/categoria/
    } elseif (isset($parts[1])) {
        $_GET['slug']     = $parts[1];
        $_GET['page_num'] = 1;

    // /noticias/
    } else {
        $_GET['page_num'] = 1;
    }

    $templateFile = __DIR__ . "/template/newsers/noticias.php";

// ===============================
// Single post: /categoria/post/
// ===============================
} elseif (count($parts) === 2) {
    $_GET['category'] = $parts[0];
    $_GET['post']     = $parts[1];
    $templateFile     = __DIR__ . "/template/newsers/single.php";

// ===============================
// Página estática: /pagina/
// ===============================
} elseif (count($parts) === 1 && $parts[0] !== '') {
    $_GET['page']     = $parts[0];
    $templateFile     = __DIR__ . "/template/newsers/{$_GET['page']}.php";

// ===============================
// Home
// ===============================
} else {
    $_GET['page']     = 'index';
    $templateFile     = __DIR__ . "/template/newsers/index.php";
}

// ===============================
// Render
// ===============================
if ($templateFile && file_exists($templateFile)) {
    ob_start();
    include $templateFile;
    $pageContent = ob_get_clean();

    include __DIR__ . "/template/newsers/inc/header.php";
    echo $pageContent;
    include __DIR__ . "/template/newsers/inc/footer.php";
} else {
    http_response_code(404);
    $errorFile = __DIR__ . "/template/newsers/404.php";
    if (file_exists($errorFile)) {
        include __DIR__ . "/template/newsers/inc/header.php";
        include $errorFile;
        include __DIR__ . "/template/newsers/inc/footer.php";
    } else {
        // Fallback si no existe tu 404.php
        echo "<div style='text-align:center;padding:100px;'>
                <h1>404</h1>
                <p>Página no encontrada</p>
                <a href='" . URLBASE . "'>Volver al inicio</a>
              </div>";
    }
}


//Para fechas en español
function fecha_espanol(string $fecha): string {
    $ingles = [
        'Monday','Tuesday','Wednesday','Thursday','Friday','Saturday','Sunday',
        'Mon','Tue','Wed','Thu','Fri','Sat','Sun',
        'January','February','March','April','May','June','July','August','September','October','November','December',
        'Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'
    ];

    $espanol = [
        'Lunes','Martes','Miércoles','Jueves','Viernes','Sábado','Domingo',
        'Lun','Mar','Mié','Jue','Vie','Sáb','Dom',
        'Enero','Febrero','Marzo','Abril','Mayo','Junio','Julio','Agosto','Septiembre','Octubre','Noviembre','Diciembre',
        'Ene','Feb','Mar','Abr','May','Jun','Jul','Ago','Sep','Oct','Nov','Dic'
    ];

    return str_replace($ingles, $espanol, $fecha);
}






