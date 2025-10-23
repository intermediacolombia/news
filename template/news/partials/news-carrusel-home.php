<?php
require_once __DIR__ . '/../../../inc/config.php';


// Últimas 5 noticias publicadas
$stmt = $pdo->query("
    SELECT p.id, p.title, p.slug, p.image, p.created_at, c.name AS category, c.slug AS category_slug
    FROM blog_posts p
    LEFT JOIN blog_post_category pc ON pc.post_id = p.id
    LEFT JOIN blog_categories c ON c.id = pc.category_id
    WHERE p.status='published' AND p.deleted=0
    ORDER BY p.created_at DESC
    LIMIT 5
");
$latestPosts = $stmt->fetchAll();
?>

<!-- Main News Slider Start -->
<div class="container-fluid py-3">
    <div class="container-bk">
        <div class="row">
            <div class="col-lg-8">
                <div class="owl-carousel owl-carousel-2 carousel-item-1 position-relative mb-3 mb-lg-0">
                    <?php foreach ($latestPosts as $post): ?>
                        <div class="position-relative overflow-hidden" style="height: 435px;">
                            <img class="img-fluid h-100"
                                 src="<?= URLBASE . '/' . htmlspecialchars($post['image']) ?>"
                                 alt="<?= htmlspecialchars($post['title']) ?>"
                                 style="object-fit: cover;">
                            <div class="overlay d-flex flex-column justify-content-end p-3" 
     style="position:absolute; bottom:0; left:0; right:0; background:rgba(0,0,0,0.5);">
    <div class="mb-1">
        <a class="text-white fw-bold" href="<?= URLBASE ?>/noticias/<?= htmlspecialchars($post['category_slug']) ?>/">
            <?= htmlspecialchars($post['category'] ?? 'General') ?>
        </a>
        <span class="px-2 text-white">/</span>
        <a class="text-white" href="#">
            <?= fecha_espanol(date("F d, Y", strtotime($post['created_at']))) ?>
        </a>
    </div>
    <a class="h4 m-0 text-white fw-bold"
       href="<?= URLBASE ?>/<?= htmlspecialchars($post['category'] ?? 'General') ?>/<?= htmlspecialchars($post['slug']) ?>/">
       <?= htmlspecialchars($post['title']) ?>
    </a>
</div>

                        </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Aquí mantengo la columna de categorías -->
            <div class="col-lg-4">
    <div class="d-flex align-items-center justify-content-between bg-light py-2 px-4 mb-3 title-widgets">
            <h3 class="m-0">Categorias</h3>
        <a class="text-secondary font-weight-medium text-decoration-none" href="<?= URLBASE ?>/noticias/">Ver Todas</a>
    </div>
    <?php
    // Seleccionar 4 categorías activas al azar
    $cats = $pdo->query("
        SELECT id, name, slug 
        FROM blog_categories 
        WHERE status='active' AND deleted=0 
        ORDER BY RAND() 
        LIMIT 4
    ")->fetchAll();

    foreach ($cats as $cat):
        // Buscar imagen aleatoria de un post de esa categoría
        $stmtImg = $pdo->prepare("
            SELECT p.image 
            FROM blog_posts p
            INNER JOIN blog_post_category pc ON pc.post_id = p.id
            WHERE pc.category_id = ? 
              AND p.status='published' 
              AND p.deleted=0 
              AND p.image IS NOT NULL 
            ORDER BY RAND() 
            LIMIT 1
        ");
        $stmtImg->execute([$cat['id']]);
        $postImg = $stmtImg->fetchColumn();

        // Si no hay imagen asociada, usar una de respaldo
        $imgSrc = $postImg ? URLBASE . '/' . $postImg : URLBASE . '/template/news/img/cat-500x80-1.jpg';
    ?>
        <div class="position-relative overflow-hidden mb-3" style="height: 80px;">
            <img class="img-fluid w-100 h-100"
                 src="<?= htmlspecialchars($imgSrc) ?>"
                 alt="<?= htmlspecialchars($cat['name']) ?>"
                 style="object-fit: cover;">
            <center><a href="<?= URLBASE ?>/noticias/<?= htmlspecialchars($cat['slug']) ?>/"
               class="overlay align-items-center justify-content-center h4 m-0 text-white text-decoration-none">
                <?= htmlspecialchars($cat['name']) ?>
            </a></center>
        </div>
    <?php endforeach; ?>
</div>

        </div>
    </div>
</div>
<!-- Main News Slider End -->
