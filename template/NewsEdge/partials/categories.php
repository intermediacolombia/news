<?php
// 1. Obtener las 6 categorías más activas y su último post
$sqlExplore = "
    SELECT c.id, c.name, c.slug, 
           (SELECT p.image FROM blog_posts p 
            INNER JOIN blog_post_category pc ON pc.post_id = p.id 
            WHERE pc.category_id = c.id AND p.status = 'published' AND p.deleted = 0
            ORDER BY p.created_at DESC LIMIT 1) as last_post_image,
           (SELECT p.title FROM blog_posts p 
            INNER JOIN blog_post_category pc ON pc.post_id = p.id 
            WHERE pc.category_id = c.id AND p.status = 'published' AND p.deleted = 0
            ORDER BY p.created_at DESC LIMIT 1) as last_post_title,
           (SELECT p.slug FROM blog_posts p 
            INNER JOIN blog_post_category pc ON pc.post_id = p.id 
            WHERE pc.category_id = c.id AND p.status = 'published' AND p.deleted = 0
            ORDER BY p.created_at DESC LIMIT 1) as last_post_slug,
           (SELECT p.author FROM blog_posts p 
            INNER JOIN blog_post_category pc ON pc.post_id = p.id 
            WHERE pc.category_id = c.id AND p.status = 'published' AND p.deleted = 0
            ORDER BY p.created_at DESC LIMIT 1) as last_post_author,
           (SELECT p.created_at FROM blog_posts p 
            INNER JOIN blog_post_category pc ON pc.post_id = p.id 
            WHERE pc.category_id = c.id AND p.status = 'published' AND p.deleted = 0
            ORDER BY p.created_at DESC LIMIT 1) as last_post_date
    FROM blog_categories c
    WHERE c.status = 'active' AND c.deleted = 0
    HAVING last_post_title IS NOT NULL
    ORDER BY (SELECT COUNT(*) FROM blog_post_category pc WHERE pc.category_id = c.id) DESC
    LIMIT 6
";

$exploreCats = db()->query($sqlExplore)->fetchAll(PDO::FETCH_ASSOC);

// Array de clases de colores del tema NewsEdge para variar el diseño
$themeColors = ['color-apple', 'color-pomegranate', 'color-java', 'color-mandy', 'color-royal-blue', 'color-burning-orange'];
?>

<?php if (!empty($exploreCats)): ?>
<section class="bg-body section-space-less10">
	 
    <div class="container">
		<div class="topic-border color-cinnabar mb-30">
        <div class="topic-box-lg color-cinnabar">Explorar por Categorías</div>
    </div>
        <div class="row tab-space5">
            <?php foreach ($exploreCats as $index => $cat): 
                $postUrl = URLBASE . '/noticias/' . htmlspecialchars($cat['last_post_slug']) . '/';
                $catUrl = URLBASE . '/noticias/' . htmlspecialchars($cat['slug']) . '/';
                // Asignamos un color del array según el índice
                $currentColor = $themeColors[$index % count($themeColors)];
            ?>
                <div class="col-lg-4 col-md-6 col-sm-6 col-12">
                    <!-- overlay-dark-level-2 y img-grayscale-hover son clases clave del CSS del tema -->
                    <div class="category-box-layout1 overlay-dark-level-2 img-grayscale-hover text-center mb-10">
                        <img src="<?= img_url($cat['last_post_image']) ?>" 
                             alt="<?= htmlspecialchars($cat['name']) ?>" 
                             class="img-fluid width-100" 
                             style="height: 350px; object-fit: cover;">
                        
                        <div class="content p-30-r">
                            <!-- Usamos la clase de color dinámica del tema -->
                            <div class="ctg-title-xs <?= $currentColor ?>">
                                <a href="<?= $catUrl ?>"><?= htmlspecialchars($cat['name']) ?></a>
                            </div>
                            
                            <h3 class="title-regular-light size-lg">
                                <a href="<?= $postUrl ?>"><?= truncate_text($cat['last_post_title'], 70) ?></a>
                            </h3>
                            
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