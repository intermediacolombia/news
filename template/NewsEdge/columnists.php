<?php
if (!defined('DIRECT_ACCESS') && !isset($config)) {
    require_once $_SERVER['DOCUMENT_ROOT'] . '/inc/config.php';
}

$username = $_GET['columnist_name_slug'] ?? null;

if (!$username) {
    http_response_code(404);
    include __DIR__ . '/404.php';
    exit;
}

// 1. Buscar al usuario por username
$sqlUser = "
    SELECT nombre, apellido
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

// 2. Buscar posts donde author = nombre completo
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

<div class="container py-5">
    <h1 class="text-center mb-4">Columnas de <?= htmlspecialchars($authorName) ?></h1>

    <?php if (!empty($posts)): ?>
        <div class="row g-4">
            <?php foreach ($posts as $post): ?>
                <div class="col-md-6">
                    <div class="card h-100">
                        <?php if (!empty($post['image'])): ?>
                            <img src="<?= img_url($post['image']) ?>" 
                                 class="card-img-top" 
                                 alt="<?= htmlspecialchars($post['title']) ?>"
                                 style="height: 200px; object-fit: cover;">
                        <?php endif; ?>
                        <div class="card-body d-flex flex-column">
                            <small class="text-muted mb-2">
                                <?= fecha_espanol(date('l j \d\e F \d\e Y', strtotime($post['created_at']))) ?>
                            </small>
                            <h5 class="card-title"><?= htmlspecialchars($post['title']) ?></h5>
                            <p class="card-text"><?= htmlspecialchars(mb_substr(strip_tags($post['content']), 0, 150)) ?>...</p>
                            <a href="<?= URLBASE ?>/noticias/<?= htmlspecialchars($post['slug']) ?>" 
                               class="btn btn-sm btn-outline-secondary mt-auto">
                                Leer más
                            </a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <p class="text-center">Este columnista aún no ha publicado ninguna columna.</p>
    <?php endif; ?>
</div>