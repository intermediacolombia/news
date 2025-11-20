<?php
// Consultar los 10 posts mÃ¡s leÃ­dos
$stmt = db()->query("
    SELECT p.id, p.title, p.slug, p.image, p.created_at, 
           c.name AS category_name, c.slug AS category_slug,
           COUNT(v.id) AS total_views
    FROM blog_post_views v
    INNER JOIN blog_posts p ON p.id = v.post_id
    INNER JOIN blog_post_category pc ON pc.post_id = p.id
    INNER JOIN blog_categories c ON c.id = pc.category_id
    WHERE p.status='published' AND p.deleted=0
    GROUP BY p.id, p.title, p.slug, p.image, p.created_at, c.name, c.slug
    ORDER BY total_views DESC
    LIMIT 10
");
$mostRead = $stmt->fetchAll();
?>

<?php if ($mostRead): ?>
<!-- Featured News Slider Start -->
<div class="container-fluid py-3" style="width: 100%!important">
    <div class="container-bk">
        <div class="d-flex align-items-center justify-content-between bg-light py-2 px-4 mb-3 title-widgets">
            <h3 class="m-0">Más Leídas</h3>
            <!-- ðŸ”¹ Puedes quitar este enlace si no lo quieres -->
            <!-- <a class="text-secondary font-weight-medium text-decoration-none" href="<?= URLBASE ?>/noticias/">Ver Todas</a> -->
        </div>
        <div class="owl-carousel owl-carousel-2 carousel-item-4 position-relative">
            <?php foreach ($mostRead as $post): ?>
                <div class="position-relative overflow-hidden" style="height: 300px;">
                    <img class="img-fluid w-100 h-100"
                         src="<?= $post['image'] ? URLBASE . '/' . htmlspecialchars($post['image']) : URLBASE . '/template/news/img/news-300x300-1.jpg' ?>"
                         alt="<?= htmlspecialchars($post['title']) ?>"
                         style="object-fit: cover;">
                    <div class="overlay">
                        <div class="mb-1" style="font-size: 13px;">
                            <a class="text-white" href="<?= URLBASE ?>/noticias/<?= htmlspecialchars($post['category_slug']) ?>/">
                                <?= htmlspecialchars($post['category_name']) ?>
                            </a>
                            <span class="px-1 text-white">/</span>
                            <span class="text-white"><?= fecha_espanol(date("F d, Y", strtotime($post['created_at']))) ?></span>
                        </div>
                        <a class="h4 m-0 text-white" 
                           href="<?= URLBASE ?>/<?= htmlspecialchars($post['category_slug']) ?>/<?= htmlspecialchars($post['slug']) ?>/">
                           <?= htmlspecialchars($post['title']) ?>
                        </a>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>
<!-- Featured News Slider End -->
<?php endif; ?>





