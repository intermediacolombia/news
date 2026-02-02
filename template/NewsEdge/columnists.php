<?php
if (!defined('DIRECT_ACCESS') && !isset($config)) {
    require_once $_SERVER['DOCUMENT_ROOT'] . '/inc/config.php';
}

$username = $_GET['columnist_slug'] ?? null;

if (!$username) {
    http_response_code(404);
    include __DIR__ . '/404.php';
    exit;
}

// 1. Obtener datos del columnista
$sqlCol = "
    SELECT u.id, u.nombre, u.apellido, u.foto_perfil, u.descripcion
    FROM usuarios u
    WHERE u.username = ? 
      AND u.es_columnista = 1 
      AND u.estado = 0 
      AND u.borrado = 0
    LIMIT 1
";

try {
    $stmt = db()->prepare($sqlCol);
    $stmt->execute([$username]);
    $columnista = $stmt->fetch(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log("Error al cargar columnista: " . $e->getMessage());
    $columnista = null;
}

if (!$columnista) {
    http_response_code(404);
    include __DIR__ . '/404.php';
    exit;
}

$nombreCompleto = htmlspecialchars($columnista['nombre'] . ' ' . $columnista['apellido']);

// 2. Obtener sus posts (asumiendo que la tabla `posts` tiene `autor_id`)
$sqlPosts = "
    SELECT p.id, p.titulo, p.slug, p.contenido, p.imagen, p.fecha_publicacion, c.slug AS categoria_slug
    FROM posts p
    INNER JOIN categorias c ON p.categoria_id = c.id
    WHERE p.autor_id = ?
      AND p.estado = 'publicado'
    ORDER BY p.fecha_publicacion DESC
";

try {
    $stmt = db()->prepare($sqlPosts);
    $stmt->execute([$columnista['id']]);
    $posts = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log("Error al cargar posts del columnista: " . $e->getMessage());
    $posts = [];
}
?>

<div class="container py-5">
    <div class="row mb-4">
        <div class="col-md-3 text-center">
            <?php if (!empty($columnista['foto_perfil'])): ?>
                <img src="<?= img_url($columnista['foto_perfil']) ?>" 
                     class="rounded-circle mb-3" 
                     alt="<?= $nombreCompleto ?>"
                     style="width: 150px; height: 150px; object-fit: cover;">
            <?php else: ?>
                <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center mb-3"
                     style="width: 150px; height: 150px; font-size: 40px;">
                    <?= strtoupper(substr($columnista['nombre'], 0, 1) . substr($columnista['apellido'], 0, 1)) ?>
                </div>
            <?php endif; ?>
        </div>
        <div class="col-md-9">
            <h1><?= $nombreCompleto ?></h1>
            <?php if (!empty($columnista['descripcion'])): ?>
                <p class="lead"><?= htmlspecialchars($columnista['descripcion']) ?></p>
            <?php endif; ?>
        </div>
    </div>

    <hr class="my-5">

    <h2 class="mb-4">Columnas recientes</h2>

    <?php if (!empty($posts)): ?>
        <div class="row g-4">
            <?php foreach ($posts as $post): ?>
                <div class="col-md-6">
                    <div class="card h-100">
                        <?php if (!empty($post['imagen'])): ?>
                            <img src="<?= img_url($post['imagen']) ?>" 
                                 class="card-img-top" 
                                 alt="<?= htmlspecialchars($post['titulo']) ?>"
                                 style="height: 200px; object-fit: cover;">
                        <?php endif; ?>
                        <div class="card-body d-flex flex-column">
                            <small class="text-muted mb-2">
                                <?= fecha_espanol(date('l j \d\e F \d\e Y', strtotime($post['fecha_publicacion']))) ?>
                            </small>
                            <h5 class="card-title"><?= htmlspecialchars($post['titulo']) ?></h5>
                            <p class="card-text"><?= htmlspecialchars(mb_substr(strip_tags($post['contenido']), 0, 120)) ?>...</p>
                            <a href="<?= URLBASE ?>/<?= htmlspecialchars($post['categoria_slug']) ?>/<?= htmlspecialchars($post['slug']) ?>" 
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