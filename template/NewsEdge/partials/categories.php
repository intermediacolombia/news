<?php
// 1. Obtener las 6 categorías con más posts publicados
$sqlExplore = "
    SELECT c.id, c.name, c.slug, 
           (SELECT p.image FROM blog_posts p 
            INNER JOIN blog_post_category pc ON pc.post_id = p.id 
            WHERE pc.category_id = c.id AND p.status = 'published' 
            ORDER BY p.created_at DESC LIMIT 1) as last_post_image,
           (SELECT p.title FROM blog_posts p 
            INNER JOIN blog_post_category pc ON pc.post_id = p.id 
            WHERE pc.category_id = c.id AND p.status = 'published' 
            ORDER BY p.created_at DESC LIMIT 1) as last_post_title,
           (SELECT p.slug FROM blog_posts p 
            INNER JOIN blog_post_category pc ON pc.post_id = p.id 
            WHERE pc.category_id = c.id AND p.status = 'published' 
            ORDER BY p.created_at DESC LIMIT 1) as last_post_slug,
           (SELECT p.author FROM blog_posts p 
            INNER JOIN blog_post_category pc ON pc.post_id = p.id 
            WHERE pc.category_id = c.id AND p.status = 'published' 
            ORDER BY p.created_at DESC LIMIT 1) as last_post_author,
           (SELECT p.created_at FROM blog_posts p 
            INNER JOIN blog_post_category pc ON pc.post_id = p.id 
            WHERE pc.category_id = c.id AND p.status = 'published' 
            ORDER BY p.created_at DESC LIMIT 1) as last_post_date
    FROM blog_categories c
    WHERE c.status = 'active' AND c.deleted = 0
    HAVING last_post_title IS NOT NULL
    ORDER BY (SELECT COUNT(*) FROM blog_post_category pc WHERE pc.category_id = c.id) DESC
    LIMIT 6
";

$exploreCats = db()->query($sqlExplore)->fetchAll(PDO::FETCH_ASSOC);
?>

<?php if (!empty($exploreCats)): ?>
<section class="bg-body section-space-less10">
    <div class="container">
        <!-- Título de sección opcional -->
        <div class="ne-main-content mb-30">
            <div class="topic-border color-tertiary-accent mb-30">
                <div class="topic-box-lg color-tertiary-accent">Explora por Secciones</div>
            </div>
        </div>

        <div class="row tab-space5">
            <?php foreach ($exploreCats as $cat): 
                $postUrl = URLBASE . '/noticias/' . htmlspecialchars($cat['last_post_slug']) . '/';
                $catUrl = URLBASE . '/noticias/' . htmlspecialchars($cat['slug']) . '/';
            ?>
                <div class="col-lg-4 col-md-6 col-sm-6 col-12">
                    <div class="category-box-layout1 overlay-dark-level-2 img-grayscale-hover text-center mb-10">
                        <!-- Imagen del último post de la categoría -->
                        <img src="<?= img_url($cat['last_post_image']) ?>" 
                             alt="<?= htmlspecialchars($cat['name']) ?>" 
                             class="img-fluid width-100" 
                             style="height: 300px; object-fit: cover;">
                        
                        <div class="content p-30-r">
                            <!-- Nombre de la Categoría -->
                            <div class="ctg-title-xs">
                                <a href="<?= $catUrl ?>" style="color: #fff;"><?= htmlspecialchars($cat['name']) ?></a>
                            </div>
                            
                            <!-- Título del último post -->
                            <h3 class="title-regular-light size-lg">
                                <a href="<?= $postUrl ?>"><?= truncate_text($cat['last_post_title'], 60) ?></a>
                            </h3>
                            
                            <!-- Meta info -->
                            <div class="post-date-light d-block d-sm-none d-md-block">
                                <ul>
                                    <li>
                                        <span>por</span>
                                        <a href="#"><?= htmlspecialchars($cat['last_post_author']) ?></a>
                                    </li>
                                    <li>
                                        <span>
                                            <i class="fa fa-calendar" aria-hidden="true"></i>
                                        </span>
                                        <?= date('d/m/Y', strtotime($cat['last_post_date'])) ?>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>