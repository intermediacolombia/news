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
        return URLBASE . '/uploads/' . ltrim($path, '/');
    }
}

/* ================= Slug ================= */
$username = $_GET['columnist_name_slug'] ?? null;

if (!$username) {
    http_response_code(404);
    return;
}

/* ================= Columnista ================= */
$sqlUser = "
    SELECT nombre, apellido, foto_perfil, bio
    FROM usuarios
    WHERE username = ?
      AND es_columnista = 1
      AND estado = 0
      AND borrado = 0
    LIMIT 1
";

$stmt = db()->prepare($sqlUser);
$stmt->execute([$username]);
$usuario = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$usuario) {
    http_response_code(404);
    return;
}

$authorName = trim($usuario['nombre'] . ' ' . $usuario['apellido']);
$fotoPerfil = img_url($usuario['foto_perfil']);

/* ================= Posts ================= */
$sqlPosts = "
    SELECT p.id, p.title, p.slug, p.content, p.image, p.created_at,
           c.name AS category_name, c.slug AS category_slug
    FROM blog_posts p
    LEFT JOIN blog_post_category pc ON pc.post_id = p.id
    LEFT JOIN blog_categories c ON c.id = pc.category_id
    WHERE p.author = ?
      AND p.status = 'published'
      AND p.deleted = 0
    ORDER BY p.created_at DESC
";

$stmt = db()->prepare($sqlPosts);
$stmt->execute([$authorName]);
$posts = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<section class="bg-body section-space-less30">
    <div class="container">

        <!-- PERFIL -->
        <div class="columnist-profile-header mb-40">
            <div class="row align-items-center">
                <div class="col-lg-4 text-center mb-30">
                    <div class="columnist-profile-image">
                        <img src="<?= $fotoPerfil ?>" alt="<?= htmlspecialchars($authorName) ?>">
                    </div>
                </div>
                <div class="col-lg-8">
                    <span class="columnist-profile-badge">COLUMNISTA</span>
                    <h1 class="columnist-profile-name"><?= htmlspecialchars($authorName) ?></h1>
                    <?php if (!empty($usuario['bio'])): ?>
                        <p class="columnist-profile-bio"><?= nl2br(htmlspecialchars($usuario['bio'])) ?></p>
                    <?php endif; ?>
                    <p class="columnist-profile-stats">
                        <i class="fa fa-file-text-o"></i>
                        <?= count($posts) ?> columnas publicadas
                    </p>
                </div>
            </div>
        </div>

        <!-- COLUMNAS -->
        <?php if ($posts): ?>
            <div class="row">
                <?php foreach ($posts as $post):
                    $postUrl = URLBASE . '/' . $post['category_slug'] . '/' . $post['slug'] . '/';
                ?>
                <div class="col-lg-6 mb-30">
                    <div class="columnist-post-card">
                        <div class="columnist-post-image">
                            <a href="<?= $postUrl ?>">
                                <img src="<?= img_url($post['image']) ?>" alt="<?= htmlspecialchars($post['title']) ?>">
                            </a>
                            <span class="columnist-post-category">
                                <?= htmlspecialchars($post['category_name']) ?>
                            </span>
                        </div>
                        <div class="columnist-post-content">
                            <h3>
                                <a href="<?= $postUrl ?>"><?= htmlspecialchars($post['title']) ?></a>
                            </h3>
                            <p><?= truncate_text($post['content']) ?></p>
                            <a href="<?= $postUrl ?>" class="read-more-link">
                                Leer columna <i class="fa fa-long-arrow-right"></i>
                            </a>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <p class="text-center">Este columnista aún no ha publicado columnas.</p>
        <?php endif; ?>

    </div>
</section>

