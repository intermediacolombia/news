<?php
require_once __DIR__ . '/../../../inc/config.php';

// Obtener las últimas 5 noticias publicadas con su categoría
$topNews = $pdo->query("
    SELECT p.id, p.title, p.slug, p.image, c.slug AS category_slug
    FROM blog_posts p
    INNER JOIN blog_post_category pc ON pc.post_id = p.id
    INNER JOIN blog_categories c ON c.id = pc.category_id
    WHERE p.status='published' AND p.deleted=0
    ORDER BY p.created_at DESC
    LIMIT 5
")->fetchAll();
?>

<!-- Top News Slider Start -->
<div class="container-fluid py-3">
    <div class="container">
        <div class="owl-carousel owl-carousel-2 carousel-item-3 position-relative">
            <?php foreach ($topNews as $news): 
                $imgSrc = $news['image'] 
                    ? URLBASE . '/' . $news['image'] 
                    : URLBASE . '/template/news/img/news-100x100-1.jpg'; // fallback si no hay imagen

                // recortar título a 70 caracteres
                $title = mb_strlen($news['title'], 'UTF-8') > 70 
                    ? mb_substr($news['title'], 0, 70, 'UTF-8') . '...' 
                    : $news['title'];
            ?>
                <div class="d-flex">
                    <img src="<?= htmlspecialchars($imgSrc) ?>" 
                         alt="<?= htmlspecialchars($news['title']) ?>"
                         style="width: 80px; height: 80px; object-fit: cover;">
                    <div class="d-flex align-items-center bg-light px-3" style="height: 80px;">
                        <a class="text-secondary font-weight-semi-bold" 
                           href="<?= URLBASE ?>/<?= htmlspecialchars($news['category_slug']) ?>/<?= htmlspecialchars($news['slug']) ?>/">
                           <?= htmlspecialchars($title) ?>
                        </a>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>
<!-- Top News Slider End -->


