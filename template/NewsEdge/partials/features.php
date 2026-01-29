<?php
/* ===== Consulta: Obtener las categorías que tienen posts (para los botones del filtro) ===== */
$sqlFilterCats = "
    SELECT DISTINCT c.name, c.slug
    FROM blog_categories c
    INNER JOIN blog_post_category pc ON pc.category_id = c.id
    INNER JOIN blog_posts p ON p.id = pc.post_id
    WHERE c.status='active' AND c.deleted=0 AND p.status='published' AND p.deleted=0
    LIMIT 5
";
$filterCategories = db()->query($sqlFilterCats)->fetchAll();

/* ===== Consulta: Obtener los últimos 9 posts con su categoría principal ===== */
$sqlIsotope = "
    SELECT p.id, p.title, p.slug, p.image, p.created_at, p.author,
           c.name AS category_name, c.slug AS category_slug
    FROM blog_posts p
    LEFT JOIN blog_post_category pc ON pc.post_id = p.id
    LEFT JOIN blog_categories c ON c.id = pc.category_id
    WHERE p.status = 'published' AND p.deleted = 0
    GROUP BY p.id
    ORDER BY p.created_at DESC
    LIMIT 9
";
$isotopePosts = db()->query($sqlIsotope)->fetchAll();
?>

<section class="section-space-bottom">
    <div class="container">
        <div class="item-box-light-md-less10">
            <div class="ne-isotope-all">
                <!-- Cabecera con Filtros -->
                <div class="topic-border color-cinnabar mb-30">
                    <div class="topic-box-lg color-cinnabar">LO MÁS DESTACADO</div>
                    <div class="isotope-classes-tab isotop-btn">
                        <a href="#" data-filter="*" class="current">Todos</a>
                        <?php foreach ($filterCategories as $fCat): ?>
                            <a href="#" data-filter=".<?= htmlspecialchars($fCat['slug']) ?>">
                                <?= htmlspecialchars($fCat['name']) ?>
                            </a>
                        <?php endforeach; ?>
                    </div>
                    <div class="more-info-link">
                        <a href="<?= URLBASE ?>/noticias">Ver más
                            <i class="fa fa-angle-right" aria-hidden="true"></i>
                        </a>
                    </div>
                </div>

                <!-- Grid de Noticias (Isotope Container) -->
                <div class="row tab-space5 featuredContainer">
                    <?php foreach ($isotopePosts as $post): 
                        // La clase CSS debe coincidir con el data-filter de arriba
                        $itemClass = !empty($post['category_slug']) ? htmlspecialchars($post['category_slug']) : 'sin-categoria';
                        $postUrl = URLBASE . "/noticias/post/" . htmlspecialchars($post['slug']);
                    ?>
                        <div class="col-lg-4 col-md-6 col-sm-6 col-12 <?= $itemClass ?>">
                            <div class="img-overlay-70 img-scale-animate mb-10">
                                <img src="<​?= img_url($post['image']) ?>" alt="<​?= htmlspecialchars($post['title']) ?>" class="img-fluid width-100" style="height: 250px; object-fit: cover;">
                                
                                <div class="topic-box-top-sm">
                                    <div class="topic-box-sm color-cod-gray mb-20">
                                        <?= htmlspecialchars($post['category_name'] ?? 'Noticia') ?>
                                    </div>
                                </div>

                                <div class="mask-content-xs">
                                    <div class="post-date-light d-none d-md-block">
                                        <ul>
                                            <li>
                                                <span>por</span>
                                                <a href="<​?= $postUrl ?>"><?= htmlspecialchars($post['author'] ?? 'Admin') ?></a>
                                            </li>
                                            <li>
                                                <span>
                                                    <i class="fa fa-calendar" aria-hidden="true"></i>
                                                </span>
                                                <?= date('d M, Y', strtotime($post['created_at'])) ?>
                                            </li>
                                        </ul>
                                    </div>
                                    <h3 class="title-medium-light size-lg">
                                        <a href="<​?= $postUrl ?>">
                                            <?= truncate_text($post['title'], 70) ?>
                                        </a>
                                    </h3>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
</section>
