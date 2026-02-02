<?php
if (!defined('DIRECT_ACCESS') && !isset($config)) {
    require_once $_SERVER['DOCUMENT_ROOT'] . '/inc/config.php';
}

/* ================= Helpers ================= */
if (!function_exists('truncate_text')) {
    function truncate_text(string $text, int $limit = 150): string {
        $text = strip_tags($text);
        return mb_strlen($text) > $limit ? mb_substr($text, 0, $limit) . '…' : $text;
    }
}

if (!function_exists('img_url')) {
    function img_url(?string $path): string {
        if (empty($path)) {
            return URLBASE . '/template/newsedge/img/avatar-default.jpg';
        }
        if (filter_var($path, FILTER_VALIDATE_URL)) {
            return $path;
        }
        return URLBASE . '/' . ltrim($path, '/');
    }
}

/* ================= Capturar Slug ================= */
$username = $_GET['columnist_name_slug'] ?? null;

if (!$username) {
    http_response_code(404);
    include __DIR__ . '/404.php';
    exit;
}

/* ================= 1. Datos del Columnista ================= */
$sqlUser = "
    SELECT id, nombre, apellido, foto_perfil, username
    FROM usuarios
    WHERE username = ?
      AND es_columnista = 1
      AND estado = 0
      AND borrado = 0
    LIMIT 1
";

try {
    $stmt = db()->prepare($sqlUser);
    $stmt->execute([$username]);
    $usuario = $stmt->fetch(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log("Error al buscar columnista: " . $e->getMessage());
    http_response_code(500);
    exit;
}

if (!$usuario) {
    http_response_code(404);
    include __DIR__ . '/404.php';
    exit;
}

$authorName = trim($usuario['nombre'] . ' ' . $usuario['apellido']);
$authorUserId = $usuario['id'];
$authorUsername = $usuario['username'];

// Lógica de imagen de perfil
if (!empty($usuario['foto_perfil'])) {
    $fotoPerfil = img_url($usuario['foto_perfil']);
} else {
    $iniciales = strtoupper(substr($usuario['nombre'], 0, 1) . substr($usuario['apellido'], 0, 1));
    $fotoPerfil = 'data:image/svg+xml;base64,' . base64_encode("
        <svg width='400' height='400' xmlns='http://www.w3.org/2000/svg'>
            <rect width='400' height='400' fill='#667eea'/>
            <text x='50%' y='50%' font-size='120' fill='white' text-anchor='middle' dy='.35em' font-family='Arial'>
                {$iniciales}
            </text>
        </svg>
    ");
}

/* ================= 2. Obtener Columnas ================= */
$sqlPosts = "
    SELECT p.id, p.title, p.slug, p.content, p.image, p.created_at, p.seo_description,
           c.name AS category_name, c.slug AS category_slug
    FROM blog_posts p
    LEFT JOIN blog_post_category pc ON pc.post_id = p.id
    LEFT JOIN blog_categories c ON c.id = pc.category_id
    WHERE p.author_user = ?
      AND p.status = 'published'
      AND p.deleted = 0
    ORDER BY p.created_at DESC
";

try {
    $stmt = db()->prepare($sqlPosts);
    // ✅ IMPORTANTE: Usa el campo correcto según tu BD
    // Si author_user guarda el ID numérico:
    $stmt->execute([$authorUserId]);
    // Si author_user guarda el username:
    // $stmt->execute([$authorUsername]);
    
    $posts = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log("Error al cargar posts del columnista: " . $e->getMessage());
    $posts = [];
}

// Variables SEO
$page_title = htmlspecialchars($authorName) . " - Columnista | " . NOMBRE_SITIO;
$page_description = "Lee todas las columnas de " . htmlspecialchars($authorName) . " en " . NOMBRE_SITIO;
$page_keywords = htmlspecialchars($authorName) . ", columnista, opinión, " . NOMBRE_SITIO;
$page_image = $fotoPerfil;
$page_canonical = URLBASE . '/columnistas/' . htmlspecialchars($username) . '/';
?>

<!-- Breadcrumb Area Start Here -->
<section class="breadcrumbs-area" style="background-image: url('<?= URLBASE ?>/template/newsedge/img/breadcrumbs-bg.jpg');">
    <div class="container">
        <div class="breadcrumbs-content">
            <h1><?= htmlspecialchars($authorName) ?></h1>
            <ul>
                <li>
                    <a href="<?= URLBASE ?>">Inicio</a>
                    <i class="fa fa-angle-right" aria-hidden="true"></i>
                </li>
                <li>
                    <a href="<?= URLBASE ?>/columnistas">Columnistas</a>
                    <i class="fa fa-angle-right" aria-hidden="true"></i>
                </li>
                <li><?= htmlspecialchars($authorName) ?></li>
            </ul>
        </div>
    </div>
</section>
<!-- Breadcrumb Area End Here -->

<!-- Columnist Page Area Start Here -->
<section class="bg-body section-space-less30">
    <div class="container">
        
        <!-- Header del Columnista -->
        <div class="row mb-40">
            <div class="col-xl-3 col-lg-4 col-md-5 col-sm-12 text-center mb-30">
                <div class="img-wrapper" style="max-width: 250px; height: 250px; overflow: hidden; margin: 0 auto; border-radius: 8px; box-shadow: 0 4px 12px rgba(0,0,0,0.1);">
                    <img src="<?= $fotoPerfil ?>" 
                         alt="<?= htmlspecialchars($authorName) ?>"
                         class="img-fluid"
                         style="width: 100%; height: 100%; object-fit: cover;">
                </div>
            </div>
            <div class="col-xl-9 col-lg-8 col-md-7 col-sm-12 d-flex align-items-center">
                <div>
                    <h1 class="title-medium-dark size-xl mb-15">
                        <?= htmlspecialchars($authorName) ?>
                    </h1>
                    <p class="description-body-dark">
                        <i class="fa fa-pencil-square-o mr-2"></i>
                        <?= count($posts) ?> columna<?= count($posts) !== 1 ? 's' : '' ?> publicada<?= count($posts) !== 1 ? 's' : '' ?>
                    </p>
                </div>
            </div>
        </div>

        <!-- Listado de Columnas -->
        <?php if (!empty($posts)): ?>
            <div class="row">
                <?php foreach ($posts as $post): 
                    $postUrl = URLBASE . '/noticias/' . htmlspecialchars($post['slug']) . '/';
                    $categoryUrl = !empty($post['category_slug']) 
                        ? URLBASE . '/noticias/' . htmlspecialchars($post['category_slug']) . '/' 
                        : '#';
                ?>
                    <div class="col-xl-6 col-lg-6 col-md-12 mb-30">
                        <div class="news-item-box item-shadow-1 h-100">
                            <div class="img-wrapper" style="height: 250px; overflow: hidden;">
                                <a href="<?= $postUrl ?>">
                                    <img src="<?= img_url($post['image']) ?>" 
                                         alt="<?= htmlspecialchars($post['title']) ?>"
                                         class="img-fluid"
                                         style="width: 100%; height: 100%; object-fit: cover;">
                                </a>
                                <?php if (!empty($post['category_name'])): ?>
                                    <div class="post-badge-wrapper">
                                        <a href="<?= $categoryUrl ?>" class="post-badge-1">
                                            <?= htmlspecialchars($post['category_name']) ?>
                                        </a>
                                    </div>
                                <?php endif; ?>
                            </div>
                            <div class="news-content-box p-4">
                                <h3 class="title-medium-dark size-lg mb-10">
                                    <a href="<?= $postUrl ?>">
                                        <?= htmlspecialchars($post['title']) ?>
                                    </a>
                                </h3>
                                <ul class="post-meta mb-10">
                                    <li>
                                        <i class="fa fa-calendar"></i>
                                        <?= date('d/m/Y', strtotime($post['created_at'])) ?>
                                    </li>
                                </ul>
                                <p class="description-body-dark">
                                    <?= truncate_text($post['seo_description'] ?: $post['content'], 150) ?>
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
            <div class="text-center py-5">
                <div class="alert alert-info">
                    <i class="fa fa-info-circle"></i>
                    Este columnista aún no ha publicado ninguna columna.
                </div>
            </div>
        <?php endif; ?>
        
    </div>
</section>
<!-- Columnist Page Area End Here -->

<!-- Banner de Página (Breadcrumbs) -->
<section class="breadcrumbs-area" style="background-image: url('<?= URLBASE ?>/template/NewsEdge/img/banner/inner-page-banner.jpg');">
    <div class="container">
        <div class="breadcrumbs-content">
            <h1>Perfil del Columnista</h1>
            <ul>
                <li><a href="<?= URLBASE ?>">Inicio</a></li>
                <li>Columnistas</li>
                <li><?= htmlspecialchars($authorName) ?></li>
            </ul>
        </div>
    </div>
</section>

<section class="bg-body section-space-less30">
    <div class="container">
        
        <!-- HEADER PERFIL (Estilo NewsEdge) -->
        <div class="item-box-light-md mb-50 shadow-sm">
            <div class="row align-items-center">
                <div class="col-lg-3 col-md-4 text-center mb-20">
                    <div class="position-relative d-inline-block">
                        <img src="<?= $fotoPerfil ?>" 
                             alt="<?= htmlspecialchars($authorName) ?>" 
                             class="img-fluid rounded-circle border shadow-sm"
                             style="width: 200px; height: 200px; object-fit: cover;">
                        <div class="topic-box-sm color-cinnabar position-absolute" style="bottom: 0; right: 0;">
                            <i class="fa fa-pencil"></i>
                        </div>
                    </div>
                </div>
                <div class="col-lg-9 col-md-8 text-center--sm">
                    <div class="pl-30 pl-none-xs">
                        <span class="text-uppercase text-danger font-weight-bold mb-10 d-block" style="letter-spacing: 2px; font-size: 12px;">NUESTROS COLUMNISTAS</span>
                        <h2 class="size-c40 mb-15 title-medium-dark"><?= htmlspecialchars($authorName) ?></h2>
                        <p class="description-body-dark size-lg">
                            Bienvenido al espacio de opinión de <strong><?= htmlspecialchars($authorName) ?></strong>. 
                            Aquí encontrarás sus análisis y perspectivas más recientes.
                        </p>
                        <div class="post-date-dark">
                            <ul>
                                <li><span><i class="fa fa-file-text-o"></i></span> <?= count($posts) ?> Columnas publicadas</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- LISTADO DE ENTRADAS -->
        <div class="row">
            <div class="col-12">
                <div class="topic-border color-cinnabar mb-30 width-100">
                    <div class="topic-box-lg color-cinnabar">COLUMNAS DE OPINIÓN</div>
                </div>
            </div>
        </div>

        <?php if ($posts): ?>
            <div class="row">
                <?php foreach ($posts as $post): 
                    $postUrl = URLBASE . '/' . htmlspecialchars($post['category_slug']) . '/' . htmlspecialchars($post['slug']) . '/';
                ?>
                <div class="col-lg-6 col-md-12 mb-30">
                    <div class="media bg-white item-shadow-1 p-none overflow-hidden h-100 media-none--sm">
                        <div class="position-relative width-40 width-100-xs">
                            <a href="<?= $postUrl ?>" class="img-opacity-hover img-scale-animate">
                                <img src="<?= img_url($post['image']) ?>" 
                                     alt="<?= htmlspecialchars($post['title']) ?>" 
                                     class="img-fluid" 
                                     style="height: 220px; width: 100%; object-fit: cover;">
                            </a>
                            <div class="topic-box-top-xs">
                                <div class="topic-box-sm color-cinnabar">
                                    <?= htmlspecialchars($post['category_name']) ?>
                                </div>
                            </div>
                        </div>
                        <div class="media-body p-30-r">
                            <div class="post-date-dark mb-10">
                                <ul>
                                    <li><span><i class="fa fa-calendar"></i></span> <?= date('d M, Y', strtotime($post['created_at'])) ?></li>
                                </ul>
                            </div>
                            <h3 class="title-medium-dark size-lg mb-15">
                                <a href="<?= $postUrl ?>"><?= htmlspecialchars($post['title']) ?></a>
                            </h3>
                            <p class="description-body-dark mb-20">
                                <?= truncate_text($post['seo_description'] ?: $post['content'], 90) ?>
                            </p>
                            <a href="<?= $postUrl ?>" class="read-more-link font-weight-bold">
                                LEER COLUMNA <i class="fa fa-long-arrow-right ml-10"></i>
                            </a>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="item-box-light-md text-center py-5">
                <p class="size-lg">Este columnista aún no ha publicado artículos.</p>
            </div>
        <?php endif; ?>

    </div>
</section>

<style>
    /* Ajustes específicos para respetar tu CSS */
    .columnist-profile-image img {
        transition: all 0.5s ease;
    }
    .columnist-profile-image:hover img {
        transform: scale(1.05);
    }
    .read-more-link {
        font-size: 13px;
        color: #111;
        text-transform: uppercase;
        letter-spacing: 1px;
    }
    .read-more-link:hover {
        color: var(--primary) !important;
    }
    /* Forzar altura en móviles para el media object */
    @media only screen and (max-width: 575px) {
        .width-100-xs { width: 100% !important; }
        .p-30-r { padding: 20px !important; }
    }
</style>

