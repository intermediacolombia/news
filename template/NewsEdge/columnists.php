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
            return URLBASE . '/template/NewsEdge/img/avatar-default.jpg';
        }
        if (filter_var($path, FILTER_VALIDATE_URL)) {
            return $path;
        }
        // Según tu estructura, las fotos suelen estar en uploads/
        return URLBASE . '/uploads/' . ltrim($path, '/');
    }
}

/* ================= Capturar Slug del Router ================= */
$username = $_GET['columnist_name_slug'] ?? null;

if (!$username) {
    http_response_code(404);
    echo "<div class='container text-center py-5'><h1>404 - Columnista no especificado</h1></div>";
    return;
}

/* ================= 1. Obtener Datos del Usuario (Sin columna 'bio') ================= */
$sqlUser = "
    SELECT nombre, apellido, foto_perfil, username
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
    error_log("Error SQL Columnista: " . $e->getMessage());
    $usuario = null;
}

if (!$usuario) {
    http_response_code(404);
    echo "<div class='container text-center py-5'><h1>404 - Columnista no encontrado</h1></div>";
    return;
}

$authorName = trim($usuario['nombre'] . ' ' . $usuario['apellido']);
$fotoPerfil = img_url($usuario['foto_perfil']);

/* ================= 2. Obtener Columnas (Posts) del Autor ================= */
$sqlPosts = "
    SELECT p.id, p.title, p.slug, p.content, p.image, p.created_at, p.seo_description,
           c.name AS category_name, c.slug AS category_slug
    FROM blog_posts p
    LEFT JOIN blog_post_category pc ON pc.post_id = p.id
    LEFT JOIN blog_categories c ON c.id = pc.category_id
    WHERE p.author = ?
      AND p.status = 'published'
      AND p.deleted = 0
    ORDER BY p.created_at DESC
";

try {
    $stmt = db()->prepare($sqlPosts);
    $stmt->execute([$authorName]);
    $posts = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log("Error SQL Posts Columnista: " . $e->getMessage());
    $posts = [];
}
?>

<section class="bg-body section-space-less30">
    <div class="container">

        <!-- HEADER PERFIL COLUMNISTA -->
        <div class="columnist-profile-header mb-40">
            <div class="row align-items-center">
                <div class="col-lg-3 col-md-4 text-center mb-30">
                    <div class="columnist-profile-image">
                        <img src="<?= $fotoPerfil ?>" alt="<?= htmlspecialchars($authorName) ?>" class="img-fluid rounded-circle shadow">
                    </div>
                </div>
                <div class="col-lg-9 col-md-8">
                    <div class="columnist-profile-info">
                        <span class="columnist-profile-badge">COLUMNISTA</span>
                        <h1 class="columnist-profile-name"><?= htmlspecialchars($authorName) ?></h1>
                        <p class="columnist-profile-stats">
                            <i class="fa fa-pencil-square-o"></i>
                            Has publicado <strong><?= count($posts) ?></strong> <?= count($posts) === 1 ? 'columna' : 'columnas' ?> en nuestro portal.
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <!-- LISTADO DE COLUMNAS -->
        <div class="row">
            <div class="col-12 mb-30">
                <div class="topic-border color-cinnabar">
                    <div class="topic-box-lg color-cinnabar">ÚLTIMAS COLUMNAS</div>
                </div>
            </div>
        </div>

        <?php if ($posts): ?>
            <div class="row">
                <?php foreach ($posts as $post):
                    $postUrl = URLBASE . '/' . htmlspecialchars($post['category_slug']) . '/' . htmlspecialchars($post['slug']) . '/';
                ?>
                <div class="col-lg-6 col-md-12 mb-30">
                    <div class="columnist-post-card h-100 shadow-sm border-0">
                        <div class="row no-gutters h-100">
                            <div class="col-sm-5">
                                <div class="columnist-post-image h-100">
                                    <a href="<?= $postUrl ?>">
                                        <img src="<?= img_url($post['image']) ?>" alt="<?= htmlspecialchars($post['title']) ?>" class="h-100 w-100" style="object-fit: cover; min-height: 200px;">
                                    </a>
                                </div>
                            </div>
                            <div class="col-sm-7">
                                <div class="columnist-post-content p-4">
                                    <span class="text-uppercase text-danger font-weight-bold" style="font-size: 11px;">
                                        <?= htmlspecialchars($post['category_name']) ?>
                                    </span>
                                    <h3 class="title-medium-dark mt-2">
                                        <a href="<?= $postUrl ?>"><?= htmlspecialchars($post['title']) ?></a>
                                    </h3>
                                    <p class="text-muted small mb-3">
                                        <i class="fa fa-calendar"></i> <?= date('d M, Y', strtotime($post['created_at'])) ?>
                                    </p>
                                    <p class="description-body-dark mb-3">
                                        <?= truncate_text($post['seo_description'] ?: $post['content'], 100) ?>
                                    </p>
                                    <a href="<?= $postUrl ?>" class="read-more-link font-weight-bold text-dark">
                                        Leer más <i class="fa fa-long-arrow-right"></i>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="alert alert-light text-center py-5 border">
                <i class="fa fa-info-circle fa-3x mb-3 text-muted"></i>
                <p class="lead">Este columnista aún no tiene columnas publicadas.</p>
            </div>
        <?php endif; ?>

    </div>
</section>

<style>
    .columnist-profile-header {
        background: #f8f9fa;
        padding: 40px;
        border-radius: 15px;
        border-left: 5px solid #c41e3a;
    }
    .columnist-profile-image img {
        width: 180px;
        height: 180px;
        object-fit: cover;
        border: 5px solid #fff;
    }
    .columnist-profile-badge {
        background: #c41e3a;
        color: #fff;
        padding: 5px 15px;
        border-radius: 20px;
        font-size: 12px;
        font-weight: bold;
        display: inline-block;
        margin-bottom: 10px;
    }
    .columnist-profile-name {
        font-size: 32px;
        font-weight: 800;
        margin-bottom: 10px;
    }
    .columnist-post-card {
        background: #fff;
        transition: transform 0.3s;
        overflow: hidden;
        border-radius: 8px;
    }
    .columnist-post-card:hover {
        transform: translateY(-5px);
    }
    .read-more-link:hover {
        color: #c41e3a !important;
        text-decoration: none;
    }
</style>

