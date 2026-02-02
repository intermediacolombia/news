<?php
require_once __DIR__ . '/../../inc/config.php';

// Helpers
if (!function_exists('truncate_text')) {
    function truncate_text(string $text, int $limit = 120): string {
        $text = strip_tags($text);
        return (mb_strlen($text) > $limit) ? mb_substr($text, 0, $limit) . '...' : $text;
    }
}

if (!function_exists('img_url')) {
    function img_url(?string $path): string {
        if (empty($path)) {
            return URLBASE . '/template/NewsEdge/img/placeholder.jpg';
        }
        if (filter_var($path, FILTER_VALIDATE_URL)) {
            return $path;
        }
        return URLBASE . '/' . ltrim($path, '/');
    }
}

$username = $_GET['columnist_name_slug'] ?? null;

if (!$username) {
    http_response_code(404);
    include __DIR__ . '/404.php';
    exit;
}

// 1. Obtener datos del columnista
$sqlUser = "
    SELECT nombre, apellido, foto_perfil
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
    error_log("Error al buscar usuario: " . $e->getMessage());
    $usuario = null;
}

if (!$usuario) {
    http_response_code(404);
    include __DIR__ . '/404.php';
    exit;
}

$authorName = $usuario['nombre'] . ' ' . $usuario['apellido'];
$fotoPerfil = !empty($usuario['foto_perfil']) 
    ? img_url($usuario['foto_perfil']) 
    : URLBASE . '/template/NewsEdge/img/avatar-default.jpg';

// 2. Obtener sus posts
$sqlPosts = "
    SELECT id, title, slug, content, image, created_at
    FROM blog_post
    WHERE author = ?
      AND status = 'published'
      AND deleted = 0
    ORDER BY created_at DESC
";

try {
    $stmt = db()->prepare($sqlPosts);
    $stmt->execute([$authorName]);
    $posts = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log("Error al cargar posts: " . $e->getMessage());
    $posts = [];
}
?>

<section class="bg-body section-space-less30">
    <div class="container">
        <!-- Header del Columnista -->
        <div class="row mb-40">
            <div class="col-xl-3 col-lg-4 col-md-5 col-sm-12 text-center mb-30">
                <div class="img-wrapper" style="height: 250px; overflow: hidden; margin: 0 auto; border-radius: 8px;">
                    <img src="<?= $fotoPerfil ?>" 
                         alt="<?= htmlspecialchars($authorName) ?>"
                         class="img-fluid"
                         style="width: 100%; height: 100%; object-fit: cover;">
                </div>
            </div>
            <div class="col-xl-9 col-lg-8 col-md-7 col-sm-12 d-flex align-items-center">
                <div>
                    <h1 class="title-medium-dark size-xl mb-15"><?= htmlspecialchars($authorName) ?></h1>
                    <p class="description-body-dark">
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
                ?>
                    <div class="col-xl-6 col-lg-6 col-md-12 mb-30">
                        <div class="news-item-box item-shadow-1 h-100">
                            <div class="img-wrapper">
                                <a href="<?= $postUrl ?>">
                                    <img src="<?= img_url($post['image']) ?>" 
                                         alt="<?= htmlspecialchars($post['title']) ?>"
                                         class="img-fluid search-result-img">
                                </a>
                            </div>
                            <div class="news-content-box">
                                <h3 class="title-medium-dark size-lg mb-10">
                                    <a href="<?= $postUrl ?>"><?= htmlspecialchars($post['title']) ?></a>
                                </h3>
                                <ul class="post-meta mb-10">
                                    <li>
                                        <i class="fa fa-calendar"></i>
                                        <?= fecha_espanol(date('l j \d\e F \d\e Y', strtotime($post['created_at']))) ?>
                                    </li>
                                </ul>
                                <p class="description-body-dark">
                                    <?= truncate_text($post['content'], 150) ?>
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
                <p>Este columnista aún no ha publicado ninguna columna.</p>
            </div>
        <?php endif; ?>
    </div>
</section>