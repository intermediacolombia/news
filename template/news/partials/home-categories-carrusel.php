<?php
require_once __DIR__ . '/../../../inc/config.php';

// Solo categorías con publicaciones activas
$categories = db()->query("
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
<!-- Category News Fixed Start -->
<div class="container-fluid">
    <div class="container-bk">
        <div class="row">
            <?php foreach ($categories as $cat): ?>
                <div class="col-lg-6 py-3">
                    <div class="d-flex justify-content-between align-items-center mb-3 bg-light px-4 py-2 rounded-top shadow-sm">
                        <h3 class="m-0 text-primary fw-bold"><?= htmlspecialchars($cat['name']) ?></h3>
                        <a href="<?= URLBASE ?>/noticias/<?= htmlspecialchars($cat['slug']) ?>/" class="btn btn-sm btn-outline-primary rounded-pill">Ver más</a>
                    </div>

                    <div class="row g-3">
                        <?php
                        $stmtPosts = db()->prepare("
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
                            <div class="col-md-4">
                                <div class="news-card rounded overflow-hidden shadow-sm bg-white h-100">
                                    <div class="image-container">
                                        <img class="img-fluid w-100"
                                             src="<?= !empty($post['image']) ? htmlspecialchars(URLBASE . '/' . $post['image']) : URLBASE . '/template/news/img/news-500x280-1.jpg' ?>"
                                             alt="<?= htmlspecialchars($post['title']) ?>">
                                    </div>
                                    <div class="p-3">
                                        <div class="small text-muted mb-2">
                                            <a href="<?= URLBASE ?>/noticias/<?= htmlspecialchars($cat['slug']) ?>/" class="text-primary">
                                                <?= htmlspecialchars($cat['name']) ?>
                                            </a>
                                            <span class="mx-1">•</span>
                                            <span><?= fecha_espanol(date("F d, Y", strtotime($post['created_at']))) ?></span>
                                        </div>
                                        <a class="h6 text-dark d-block fw-semibold text-truncate-2"
                                           href="<?= URLBASE ?>/<?= htmlspecialchars($cat['slug']) ?>/<?= htmlspecialchars($post['slug']) ?>/">
                                            <?= htmlspecialchars($post['title']) ?>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>
<!-- Category News Fixed End -->

<style>
/* === Diseño limpio y moderno === */
.news-card {
  border: 1px solid #e9ecef;
  transition: all 0.3s ease;
  background: #fff;
}
.news-card:hover {
  transform: translateY(-4px);
  box-shadow: 0 6px 18px rgba(0,0,0,0.1);
}

/* Imágenes con proporción 16:9 */
.image-container {
  position: relative;
  width: 100%;
  aspect-ratio: 16 / 9;
  overflow: hidden;
  background: #f8f9fa;
}
.image-container img {
  width: 100%;
  height: 100%;
  object-fit: cover;
  transition: transform 0.4s ease;
}
.image-container:hover img {
  transform: scale(1.05);
}

/* Título truncado a 2 líneas */
.text-truncate-2 {
  overflow: hidden;
  text-overflow: ellipsis;
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
}

/* Botones y encabezado */
.btn-outline-primary {
  border-color: var(--primary);
  color: var(--primary);
}
.btn-outline-primary:hover {
  background: var(--primary);
  color: #fff;
}
</style>
<?php endif; ?>

