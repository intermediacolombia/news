<?php
require_once __DIR__ . '/../../inc/config.php';

if (!function_exists('img_url')) {
    function img_url(?string $path): string {
        if (empty($path)) return URLBASE . '/template/Artemis/img/avatar.jpg';
        if (preg_match('#^https?://#i', $path)) return $path;
        return URLBASE . '/' . ltrim($path, '/');
    }
}

if (!function_exists('truncate_text')) {
    function truncate_text(string $text, int $limit = 150): string {
        $text = strip_tags($text);
        return mb_strlen($text) > $limit ? mb_substr($text, 0, $limit) . '…' : $text;
    }
}

$username = $_GET['columnist_slug'] ?? null;

if (!$username) {
    http_response_code(404);
    return;
}

$sqlUser = "SELECT id, nombre, apellido, foto_perfil, username FROM usuarios WHERE username = ? AND es_columnista = 1 AND estado = 0 AND borrado = 0 LIMIT 1";
$stmt = db()->prepare($sqlUser);
$stmt->execute([$username]);
$usuario = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$usuario) {
    http_response_code(404);
    return;
}

$authorName = trim($usuario['nombre'] . ' ' . $usuario['apellido']);

if (!empty($usuario['foto_perfil'])) {
    $fotoPerfil = img_url($usuario['foto_perfil']);
} else {
    $iniciales = strtoupper(substr($usuario['nombre'], 0, 1) . substr($usuario['apellido'], 0, 1));
    $fotoPerfil = 'data:image/svg+xml;base64,' . base64_encode("
        <svg width='200' height='200' xmlns='http://www.w3.org/2000/svg'>
            <rect width='200' height='200' fill='#e63946'/>
            <text x='50%' y='50%' font-size='60' fill='white' text-anchor='middle' dy='.35em' font-family='Arial'>
                {$iniciales}
            </text>
        </svg>
    ");
}

$sqlPosts = "SELECT p.id, p.title, p.slug, p.content, p.image, p.created_at, p.seo_description, c.name AS category_name, c.slug AS category_slug FROM blog_posts p LEFT JOIN blog_post_category pc ON pc.post_id = p.id LEFT JOIN blog_categories c ON c.id = pc.category_id WHERE p.author_user = ? AND p.status = 'published' AND p.deleted = 0 ORDER BY p.created_at DESC";

$stmt = db()->prepare($sqlPosts);
$stmt->execute([$usuario['username']]);
$posts = $stmt->fetchAll(PDO::FETCH_ASSOC);

$page_title = "Perfil de $authorName | " . NOMBRE_SITIO;
?>

<section class="py-5" style="background: var(--dark);">
    <div class="container">
        <div style="background: var(--dark-secondary); border-radius: 20px; padding: 40px; text-align: center;">
            <img src="<?= $fotoPerfil ?>" 
                 alt="<?= htmlspecialchars($authorName) ?>" 
                 style="width: 150px; height: 150px; border-radius: 50%; object-fit: cover; border: 4px solid var(--primary); margin-bottom: 20px;">
            
            <h1 style="color: #fff; font-family: 'Playfair Display', serif; font-size: 2.5rem; margin-bottom: 10px;">
                <?= htmlspecialchars($authorName) ?>
            </h1>
            <p style="color: var(--primary); font-size: 16px; font-weight: 600; text-transform: uppercase; letter-spacing: 1px; margin-bottom: 15px;">
                Columnista
            </p>
            <p style="color: var(--text-muted); max-width: 600px; margin: 0 auto;">
                Bienvenido al espacio de opinión de <strong style="color: #fff;"><?= htmlspecialchars($authorName) ?></strong>. Aquí encontrarás sus análisis y perspectivas más recientes.
            </p>
            <span style="background: var(--primary); color: #fff; padding: 8px 20px; border-radius: 20px; font-size: 14px; display: inline-block; margin-top: 20px;">
                <i class="fas fa-file-alt mr-2"></i><?= count($posts) ?> Columnas publicadas
            </span>
        </div>

        <div class="row mt-5">
            <div class="col-12 mb-4">
                <h2 class="section-title" style="color: #fff;">COLUMNAS DE OPINIÓN</h2>
            </div>
        </div>

        <?php if ($posts): ?>
        <div class="row">
            <?php foreach ($posts as $post): 
                $postUrl = URLBASE . '/' . htmlspecialchars($post['category_slug']) . '/' . htmlspecialchars($post['slug']) . '/';
            ?>
            <div class="col-lg-6 mb-4">
                <div class="news-card">
                    <div class="position-relative" style="overflow: hidden;">
                        <a href="<?= $postUrl ?>">
                            <img src="<?= img_url($post['image']) ?>" 
                                 alt="<?= htmlspecialchars($post['title']) ?>" 
                                 class="card-img"
                                 style="width: 100%; height: 200px; object-fit: cover;">
                        </a>
                        <span class="category-badge position-absolute" style="top: 12px; left: 12px;">
                            <?= htmlspecialchars($post['category_name']) ?>
                        </span>
                    </div>
                    <div class="p-4">
                        <h4 style="color: #fff; font-size: 18px; font-weight: 600; margin-bottom: 10px; line-height: 1.4;">
                            <a href="<?= $postUrl ?>" style="color: inherit; text-decoration: none;">
                                <?= htmlspecialchars($post['title']) ?>
                            </a>
                        </h4>
                        <p style="color: var(--text-muted); font-size: 14px; margin-bottom: 10px;">
                            <?= truncate_text($post['seo_description'] ?: $post['content'], 100) ?>
                        </p>
                        <span style="color: var(--text-muted); font-size: 13px;">
                            <i class="far fa-calendar mr-1"></i>
                            <?= date('d M, Y', strtotime($post['created_at'])) ?>
                        </span>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php else: ?>
        <div class="text-center py-5">
            <p style="color: var(--text-muted);">Este columnista aún no ha publicado artículos.</p>
        </div>
        <?php endif; ?>
    </div>
</section>