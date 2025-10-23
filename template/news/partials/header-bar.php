<?php
require_once __DIR__ . '/../../../inc/config.php';

// Configurar idioma y zona horaria
setlocale(LC_TIME, 'es_ES.UTF-8', 'es_ES', 'spanish');
date_default_timezone_set('America/Bogota');

// Consulta: últimas 10 entradas
$stmt = $pdo->query("
    SELECT p.title, p.slug AS post_slug, p.created_at,
           c.slug AS category_slug, c.name AS category_name
    FROM blog_posts p
    INNER JOIN blog_post_category pc ON pc.post_id = p.id
    INNER JOIN blog_categories c ON c.id = pc.category_id
    WHERE p.status='published' AND p.deleted=0
    ORDER BY p.created_at DESC
    LIMIT 10
");
$latestPosts = $stmt->fetchAll();

// Fecha actual en español
$fechaHoy = strftime('%A, %d de %B de %Y');
?>
<div class="row align-items-center bg-light px-lg-5">
    <div class="col-12 col-md-8">
        <div class="d-flex justify-content-between">
            <div class="bg-primary text-white text-center px-3 py-2 d-inline-flex align-items-center rounded">
  <i class="fas fa-bolt me-2"></i> Tendencias
</div>

            <div class="owl-carousel owl-carousel-1 tranding-carousel position-relative d-inline-flex align-items-center ml-3" 
                 style="width: calc(100% - 100px); padding-left: 90px;">

                <?php foreach ($latestPosts as $post): ?>
                    <div class="text-truncate">
                        <a class="text-secondary" 
                           href="<?= URLBASE ?>/<?= htmlspecialchars($post['category_slug']) ?>/<?= htmlspecialchars($post['post_slug']) ?>/">
                           <?= htmlspecialchars($post['title']) ?>
                        </a>
                    </div>
                <?php endforeach; ?>

            </div>
        </div>
    </div>
    <div class="col-md-4 text-right d-none d-md-block">
        <?= ucfirst($fechaHoy) ?>
    </div>
</div>
