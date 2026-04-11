<?php
require_once __DIR__ . '/../../../inc/config.php';


// Últimas 5 noticias publicadas
$stmt = db()->query("
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
                                 alt="<?= htmlspecialchars(get_image_alt($post['image'], $post['title'])) ?>"
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
    // Obtener 4 categorías con más posts + imagen aleatoria (single query)
    $catsQuery = db()->query("
        SELECT c.id, c.name, c.slug,
               (SELECT p2.image
                FROM blog_posts p2
                INNER JOIN blog_post_category pc2 ON pc2.post_id = p2.id
                WHERE pc2.category_id = c.id
                  AND p2.status='published'
                  AND p2.deleted=0
                  AND p2.image IS NOT NULL
                ORDER BY RAND()
                LIMIT 1
               ) AS random_image
        FROM blog_categories c
        INNER JOIN blog_post_category pc ON c.id = pc.category_id
        INNER JOIN blog_posts p ON p.id = pc.post_id AND p.status='published' AND p.deleted=0
        WHERE c.status='active' AND c.deleted=0
        GROUP BY c.id, c.name, c.slug
        ORDER BY COUNT(p.id) DESC
        LIMIT 4
    ")->fetchAll();

    foreach ($catsQuery as $cat):
        $imgSrc = !empty($cat['random_image']) ? URLBASE . '/' . htmlspecialchars($cat['random_image']) : URLBASE . '/template/news/img/cat-500x80-1.jpg';
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
