<?php
$categories = db()->query("
    SELECT c.id, c.name, c.slug, COUNT(p.id) AS total
    FROM blog_categories c
    LEFT JOIN blog_post_category pc ON c.id = pc.category_id
    LEFT JOIN blog_posts p ON p.id = pc.post_id AND p.status='published' AND p.deleted=0
    WHERE c.status='active' AND c.deleted=0
    GROUP BY c.id
    HAVING total > 0
    ORDER BY total DESC
    LIMIT 6
")->fetchAll();

if (!function_exists('img_url')) {
    function img_url(?string $path): string {
        if (empty($path)) return URLBASE . '/template/Artemis/img/category.jpg';
        if (preg_match('#^https?://#i', $path)) return $path;
        return URLBASE . '/' . ltrim($path, '/');
    }
}
?>

<section class="py-5" style="background: var(--dark-secondary);">
    <div class="container">
        <div class="row mb-4">
            <div class="col-12 text-center">
                <h2 class="section-title" style="color: var(--text-color);">CATEGORÍAS</h2>
            </div>
        </div>
        
        <div class="row">
            <?php foreach ($categories as $cat): 
                $catUrl = URLBASE . '/noticias/' . htmlspecialchars($cat['slug']) . '/';
            ?>
            <div class="col-lg-4 col-md-6 mb-4">
                <a href="<?= $catUrl ?>" 
                   class="category-card d-block"
                   style="position: relative; border-radius: 16px; overflow: hidden; height: 150px; text-decoration: none;">
                    <div style="width: 100%; height: 100%; background: linear-gradient(135deg, var(--secondary), var(--dark)); display: flex; align-items: center; justify-content: center;">
                        <i class="fas fa-folder" style="font-size: 40px; color: rgba(255,255,255,0.3);"></i>
                    </div>
                    
                    <div style="position: absolute; top: 0; left: 0; right: 0; bottom: 0; background: linear-gradient(transparent, rgba(0,0,0,0.8)); display: flex; align-items: flex-end; padding: 20px;">
                        <div>
                            <h4 style="color: var(--text-color); font-size: 20px; font-weight: 600; margin: 0;">
                                <?= htmlspecialchars($cat['name']) ?>
                            </h4>
                            <span style="color: var(--text-muted); font-size: 14px;">
                                <?= $cat['total'] ?> <?= $cat['total'] === 1 ? 'artículo' : 'artículos' ?>
                            </span>
                        </div>
                    </div>
                </a>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<style>
    .category-card:hover img {
        transform: scale(1.1);
    }
</style>