<?php
require_once __DIR__ . '/../../inc/config.php';

if (!function_exists('img_url')) {
    function img_url(?string $path): string {
        if (empty($path)) return URLBASE . '/template/news/img/user.jpg';
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
if (!$username) { http_response_code(404); return; }

$stmt = db()->prepare("SELECT id, nombre, apellido, foto_perfil, username FROM usuarios WHERE username = ? AND es_columnista = 1 AND estado = 0 AND borrado = 0 LIMIT 1");
$stmt->execute([$username]);
$usuario = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$usuario) { http_response_code(404); return; }

$authorName = trim($usuario['nombre'] . ' ' . $usuario['apellido']);
if (!empty($usuario['foto_perfil'])) {
    $fotoPerfil = img_url($usuario['foto_perfil']);
} else {
    $iniciales  = strtoupper(substr($usuario['nombre'], 0, 1) . substr($usuario['apellido'], 0, 1));
    $fotoPerfil = 'data:image/svg+xml;base64,' . base64_encode("<svg width='200' height='200' xmlns='http://www.w3.org/2000/svg'><rect width='200' height='200' fill='#5fca00'/><text x='50%' y='50%' font-size='60' fill='white' text-anchor='middle' dy='.35em' font-family='Arial'>{$iniciales}</text></svg>");
}

$stmt = db()->prepare("SELECT p.id, p.title, p.slug, p.content, p.image, p.created_at, p.seo_description, c.name AS category_name, c.slug AS category_slug FROM blog_posts p LEFT JOIN blog_post_category pc ON pc.post_id = p.id LEFT JOIN blog_categories c ON c.id = pc.category_id WHERE p.author_user = ? AND p.status = 'published' AND p.deleted = 0 ORDER BY p.created_at DESC");
$stmt->execute([$usuario['username']]);
$posts = $stmt->fetchAll(PDO::FETCH_ASSOC);

$page_title = "Perfil de $authorName | " . NOMBRE_SITIO;
?>
<div class="container-fluid py-3">
    <div class="container-bk">
        <div class="text-center bg-light p-4 mb-4" style="border-radius:8px;">
            <img src="<?= $fotoPerfil ?>"
                 alt="<?= htmlspecialchars($authorName) ?>"
                 style="width:120px; height:120px; border-radius:50%; object-fit:cover; border:4px solid var(--primary); margin-bottom:15px;">
            <h2><?= htmlspecialchars($authorName) ?></h2>
            <p class="text-muted" style="text-transform:uppercase; letter-spacing:1px;">Columnista</p>
            <span class="badge badge-primary"><?= count($posts) ?> columna<?= count($posts) !== 1 ? 's' : '' ?> publicada<?= count($posts) !== 1 ? 's' : '' ?></span>
        </div>

        <div class="bg-light py-2 px-4 mb-3">
            <h4 class="m-0">Columnas de opinión</h4>
        </div>

        <?php if ($posts): ?>
        <div class="row">
            <?php foreach ($posts as $post):
                $postUrl = URLBASE . '/' . htmlspecialchars($post['category_slug']) . '/' . htmlspecialchars($post['slug']) . '/';
            ?>
            <div class="col-md-6 mb-4">
                <div class="card h-100 shadow-sm">
                    <?php if (!empty($post['image'])): ?>
                        <a href="<?= $postUrl ?>">
                            <img src="<?= img_url($post['image']) ?>" class="card-img-top" style="height:200px; object-fit:cover;" alt="<?= htmlspecialchars($post['title']) ?>">
                        </a>
                    <?php endif; ?>
                    <div class="card-body">
                        <?php if (!empty($post['category_name'])): ?>
                            <span class="badge badge-secondary mb-2"><?= htmlspecialchars($post['category_name']) ?></span>
                        <?php endif; ?>
                        <h5 class="card-title">
                            <a href="<?= $postUrl ?>" style="color:inherit; text-decoration:none;"><?= htmlspecialchars($post['title']) ?></a>
                        </h5>
                        <p class="card-text text-muted small"><?= truncate_text($post['seo_description'] ?: $post['content'], 100) ?></p>
                        <small class="text-muted"><i class="far fa-calendar" style="margin-right:4px;"></i><?= date('d M, Y', strtotime($post['created_at'])) ?></small>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php else: ?>
        <p class="text-muted text-center py-4">Este columnista aún no ha publicado artículos.</p>
        <?php endif; ?>
    </div>
</div>
