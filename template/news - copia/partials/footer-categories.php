<?php
require_once __DIR__ . '/../../../inc/config.php';

// Traer todas las categorías que tengan al menos un post publicado
$stmt = $pdo->query("
    SELECT DISTINCT c.id, c.name, c.slug
    FROM blog_categories c
    INNER JOIN blog_post_category pc ON pc.category_id = c.id
    INNER JOIN blog_posts p ON p.id = pc.post_id
    WHERE c.status='active' 
      AND c.deleted=0 
      AND p.status='published' 
      AND p.deleted=0
    ORDER BY c.name ASC
");
$categories = $stmt->fetchAll();
?>

<div class="col-lg-3 col-md-6 mb-5">
    <h4 class="font-weight-bold mb-4">Categorías</h4>
    <div class="d-flex flex-wrap m-n1">
        <?php foreach ($categories as $cat): ?>
            <a href="<?= URLBASE ?>/noticias/<?= htmlspecialchars($cat['slug']) ?>/"
               class="btn btn-sm btn-outline-secondary m-1">
                <?= htmlspecialchars($cat['name']) ?>
            </a>
        <?php endforeach; ?>
    </div>
</div>
