<?php
require_once __DIR__ . '/../../../inc/config.php';

// Obtener todas las categorías activas
$categories = $pdo->query("
    SELECT id, name, slug 
    FROM blog_categories 
    WHERE status='active' AND deleted=0
    ORDER BY name ASC
")->fetchAll();
?>

<?php if ($categories): ?>
<!-- Category News Slider Start -->
<div class="container-fluid">
    <div class="container">
        <div class="row">
            <?php foreach ($categories as $cat): ?>
                <div class="col-lg-6 py-3">
                    <div class="bg-light py-2 px-4 mb-3">
                        <h3 class="m-0"><?= htmlspecialchars($cat['name']) ?></h3>
                    </div>
                    <div class="owl-carousel owl-carousel-3 carousel-item-2 position-relative">
                        <?php
                        // Obtener las últimas 3 noticias de esta categoría
                        $stmtPosts = $pdo->prepare("
                            SELECT p.id, p.title, p.slug, p.image, p.created_at
                            FROM blog_posts p
                            INNER JOIN blog_post_category pc ON pc.post_id = p.id
                            WHERE pc.category_id = ? 
                              AND p.status='published' 
                              AND p.deleted=0
                            ORDER BY p.created_at DESC
                            LIMIT 3
                        ");
                        $stmtPosts->execute([$cat['id']]);
                        $posts = $stmtPosts->fetchAll();
                        ?>

                        <?php foreach ($posts as $post): ?>
                            <div class="position-relative">
                                <img class="img-fluid w-100"
                                     src="<?= $post['image'] ? URLBASE . '/' . htmlspecialchars($post['image']) : URLBASE . '/template/news/img/news-500x280-1.jpg' ?>"
                                     style="object-fit: cover;"
                                     alt="<?= htmlspecialchars($post['title']) ?>">
                                <div class="overlay position-relative bg-light">
                                    <div class="mb-2" style="font-size: 13px;">
                                        <a href="<?= URLBASE ?>/noticias/<?= htmlspecialchars($cat['slug']) ?>/">
                                            <?= htmlspecialchars($cat['name']) ?>
                                        </a>
                                        <span class="px-1">/</span>
                                        <span><?= date("F d, Y", strtotime($post['created_at'])) ?></span>
                                    </div>
                                    <a class="h4 m-0"
                                       href="<?= URLBASE ?>/<?= htmlspecialchars($cat['slug']) ?>/<?= htmlspecialchars($post['slug']) ?>/">
                                        <?= htmlspecialchars($post['title']) ?>
                                    </a>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>
<!-- Category News Slider End -->
<?php endif; ?>