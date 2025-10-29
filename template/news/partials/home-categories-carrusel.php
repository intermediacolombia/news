<?php
require_once __DIR__ . '/../../../inc/config.php';

// Obtener solo las categorías que tengan al menos 1 post publicado
$categories = $pdo->query("
    SELECT c.id, c.name, c.slug, COUNT(p.id) AS total_posts
    FROM blog_categories c
    INNER JOIN blog_post_category pc ON pc.category_id = c.id
    INNER JOIN blog_posts p ON p.id = pc.post_id 
         AND p.status='published' 
         AND p.deleted=0
    WHERE c.status='active' AND c.deleted=0
    GROUP BY c.id
    HAVING total_posts > 0
    ORDER BY c.name ASC
")->fetchAll();
?>

<?php if ($categories): ?>
<!-- Category News Slider Start -->
<div class="container-fluid py-4">
    <div class="container">
        <div class="row">
            <?php foreach ($categories as $cat): ?>
                <div class="col-lg-6 col-md-12 mb-4">
                    <div class="bg-white rounded shadow-sm p-3 mb-3 d-flex align-items-center justify-content-between">
                        <h3 class="m-0 text-primary fw-bold"><?= htmlspecialchars($cat['name']) ?></h3>
                        <a href="<?= URLBASE ?>/noticias/<?= htmlspecialchars($cat['slug']) ?>/" 
                           class="btn btn-sm btn-outline-primary rounded-pill">
                            Ver más
                        </a>
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
                            <div class="news-card position-relative overflow-hidden rounded shadow-sm">
                                <div class="ratio ratio-16x9">
                                    <img class="img-fluid w-100" 
                                         src="<?= $post['image'] ? URLBASE . '/' . htmlspecialchars($post['image']) : URLBASE . '/template/news/img/news-500x280-1.jpg' ?>"
                                         alt="<?= htmlspecialchars($post['title']) ?>"
                                         style="object-fit: cover; transition: transform 0.3s ease;">
                                </div>
                                <div class="overlay bg-white p-3 border-top">
                                    <div class="text-muted small mb-2">
                                        <a href="<?= URLBASE ?>/noticias/<?= htmlspecialchars($cat['slug']) ?>/" class="text-primary">
                                            <?= htmlspecialchars($cat['name']) ?>
                                        </a>
                                        <span class="mx-1">•</span>
                                        <span><?= fecha_espanol(date("F d, Y", strtotime($post['created_at']))) ?></span>
                                    </div>
                                    <a class="h5 fw-semibold text-dark d-block text-truncate-2"
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

<style>
/* --- Modern Category Slider --- */
.news-card {
  border-radius: 12px;
  overflow: hidden;
  background: #fff;
  transition: all 0.3s ease;
}
.news-card:hover {
  transform: translateY(-5px);
  box-shadow: 0 8px 18px rgba(0,0,0,0.1);
}
.news-card img:hover {
  transform: scale(1.05);
}
.text-truncate-2 {
  overflow: hidden;
  text-overflow: ellipsis;
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
}
.title-widgets h3 {
  font-weight: 600;
}
.owl-carousel .owl-item {
  padding: 5px;
}
.owl-carousel .owl-nav button.owl-prev,
.owl-carousel .owl-nav button.owl-next {
  position: absolute;
  top: 40%;
  background: #fff;
  color: #333;
  border-radius: 50%;
  width: 32px;
  height: 32px;
  box-shadow: 0 2px 4px rgba(0,0,0,0.15);
  transition: 0.3s;
}
.owl-carousel .owl-nav button.owl-prev:hover,
.owl-carousel .owl-nav button.owl-next:hover {
  background: var(--primary);
  color: #fff;
}
.owl-carousel .owl-nav button.owl-prev { left: -15px; }
.owl-carousel .owl-nav button.owl-next { right: -15px; }
</style>
<?php endif; ?>
