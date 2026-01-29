<?php
/* ===== Consulta: Categorías populares (6 con más posts) ===== */
$stmtCat = db()->query("
    SELECT c.name, c.slug, COUNT(pc.post_id) AS total
    FROM blog_categories c
    LEFT JOIN blog_post_category pc ON c.id = pc.category_id
    INNER JOIN blog_posts p ON p.id = pc.post_id AND p.status='published' AND p.deleted=0
    WHERE c.status='active' AND c.deleted=0
    GROUP BY c.id, c.name, c.slug
    ORDER BY total DESC
    LIMIT 6
");
$categories = $stmtCat->fetchAll();

/* ===== Consulta: Noticias recientes (6 últimas) ===== */
$stmtRecent = db()->query("
    SELECT p.id, p.title, p.slug, p.image, p.created_at,
           c.name AS category_name, c.slug AS category_slug
    FROM blog_posts p
    LEFT JOIN blog_post_category pc ON pc.post_id = p.id
    LEFT JOIN blog_categories c ON c.id = pc.category_id
    WHERE p.status='published' AND p.deleted=0
    GROUP BY p.id
    ORDER BY p.created_at DESC
    LIMIT 6
");
$recentNews = $stmtRecent->fetchAll();

/* ===== Consulta: Noticias populares por vistas (6 más vistas) ===== */
$stmtPop = db()->query("
    SELECT p.id, p.title, p.slug, p.image, p.created_at,
           c.name AS category_name, c.slug AS category_slug,
           COUNT(v.id) AS total_views
    FROM blog_post_views v
    INNER JOIN blog_posts p ON p.id = v.post_id
    LEFT JOIN blog_post_category pc ON pc.post_id = p.id
    LEFT JOIN blog_categories c ON c.id = pc.category_id
    WHERE p.status='published' AND p.deleted=0
    GROUP BY p.id
    ORDER BY total_views DESC, p.created_at DESC
    LIMIT 6
");
$popularNews = $stmtPop->fetchAll();

/* ===== Consulta: Noticias comunes (aleatorias) ===== */
$stmtCommon = db()->query("
    SELECT p.id, p.title, p.slug, p.image, p.created_at,
           c.name AS category_name, c.slug AS category_slug
    FROM blog_posts p
    LEFT JOIN blog_post_category pc ON pc.post_id = p.id
    LEFT JOIN blog_categories c ON c.id = pc.category_id
    WHERE p.status='published' AND p.deleted=0
    GROUP BY p.id
    ORDER BY RAND()
    LIMIT 6
");
$commonNews = $stmtCommon->fetchAll();
?>

<div class="ne-sidebar sidebar-break-lg col-xl-4 col-lg-12">
    <!-- Stay Connected -->
    <div class="sidebar-box item-box-light-md">
        <div class="topic-border color-cinnabar mb-30">
            <div class="topic-box-lg color-cinnabar">Síguenos</div>
        </div>
        <ul class="stay-connected-color overflow-hidden">
            <?php if (!empty($sys['facebook'])): ?>
            <li class="facebook">
                <a href="<?= htmlspecialchars($sys['facebook']) ?>" target="_blank">
                    <i class="fa fa-facebook" aria-hidden="true"></i>
                    <div class="connection-quantity">Facebook</div>
                    <p>Síguenos</p>
                </a>
            </li>
            <?php endif; ?>

            <?php if (!empty($sys['twitter'])): ?>
            <li class="twitter">
                <a href="<?= htmlspecialchars($sys['twitter']) ?>" target="_blank">
                    <i class="fa fa-twitter" aria-hidden="true"></i>
                    <div class="connection-quantity">Twitter</div>
                    <p>Síguenos</p>
                </a>
            </li>
            <?php endif; ?>

            <?php if (!empty($sys['instagram'])): ?>
            <li class="linkedin">
                <a href="<?= htmlspecialchars($sys['instagram']) ?>" target="_blank">
                    <i class="fa fa-instagram" aria-hidden="true"></i>
                    <div class="connection-quantity">Instagram</div>
                    <p>Síguenos</p>
                </a>
            </li>
            <?php endif; ?>

            <?php if (!empty($sys['youtube'])): ?>
            <li class="rss">
                <a href="<?= htmlspecialchars($sys['youtube']) ?>" target="_blank">
                    <i class="fa fa-youtube" aria-hidden="true"></i>
                    <div class="connection-quantity">YouTube</div>
                    <p>Suscríbete</p>
                </a>
            </li>
            <?php endif; ?>
        </ul>
    </div>

    <!-- Tabs: Recent / Popular / Common -->
    <div class="sidebar-box item-box-light-md-less30">
        <ul class="btn-tab item-inline block-xs nav nav-tabs" role="tablist">
            <li class="nav-item">
                <a href="#recent" data-toggle="tab" aria-expanded="true" class="active">Recientes</a>
            </li>
            <li class="nav-item">
                <a href="#popular" data-toggle="tab" aria-expanded="false">Populares</a>
            </li>
            <li class="nav-item">
                <a href="#common" data-toggle="tab" aria-expanded="false">Aleatorias</a>
            </li>
        </ul>

        <div class="tab-content">
            <!-- Tab: Recent -->
            <div role="tabpanel" class="tab-pane fade active show" id="recent">
                <div class="row">
                    <?php foreach ($recentNews as $news): ?>
                    <div class="col-xl-6 col-lg-4 col-md-4 col-sm-6 col-6 mb-25">
                        <div class="position-relative">
                            <div class="topic-box-top-xs">
                                <div class="topic-box-sm color-cod-gray mb-20">
                                    <?= htmlspecialchars($news['category_name'] ?? 'Noticia') ?>
                                </div>
                            </div>
                            <a href="<?= URLBASE ?>/noticias/post/<?= htmlspecialchars($news['slug']) ?>" class="img-opacity-hover">
                                <img src="<?= img_url($news['image']) ?>" 
                                     alt="<?= htmlspecialchars($news['title']) ?>" 
                                     class="img-fluid width-100 mb-10"
                                     style="height: 120px; object-fit: cover;">
                            </a>
                            <h3 class="title-medium-dark size-sm mb-none">
                                <a href="<?= URLBASE ?>/noticias/post/<?= htmlspecialchars($news['slug']) ?>">
                                    <?= truncate_text($news['title'], 50) ?>
                                </a>
                            </h3>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Tab: Popular -->
            <div role="tabpanel" class="tab-pane fade" id="popular">
                <div class="row">
                    <?php foreach ($popularNews as $news): ?>
                    <div class="col-xl-6 col-lg-4 col-md-4 col-sm-6 col-6 mb-25">
                        <div class="position-relative">
                            <div class="topic-box-top-xs">
                                <div class="topic-box-sm color-cod-gray mb-20">
                                    <?= htmlspecialchars($news['category_name'] ?? 'Noticia') ?>
                                </div>
                            </div>
                            <a href="<?= URLBASE ?>/noticias/post/<?= htmlspecialchars($news['slug']) ?>" class="img-opacity-hover">
                                <img src="<?= img_url($news['image']) ?>" 
                                     alt="<?= htmlspecialchars($news['title']) ?>" 
                                     class="img-fluid width-100 mb-10"
                                     style="height: 120px; object-fit: cover;">
                            </a>
                            <h3 class="title-medium-dark size-sm mb-none">
                                <a href="<?= URLBASE ?>/noticias/post/<?= htmlspecialchars($news['slug']) ?>">
                                    <?= truncate_text($news['title'], 50) ?>
                                </a>
                            </h3>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Tab: Common (Aleatorias) -->
            <div role="tabpanel" class="tab-pane fade" id="common">
                <div class="row">
                    <?php foreach ($commonNews as $news): ?>
                    <div class="col-xl-6 col-lg-4 col-md-4 col-sm-6 col-6 mb-25">
                        <div class="position-relative">
                            <div class="topic-box-top-xs">
                                <div class="topic-box-sm color-cod-gray mb-20">
                                    <?= htmlspecialchars($news['category_name'] ?? 'Noticia') ?>
                                </div>
                            </div>
                            <a href="<?= URLBASE ?>/noticias/post/<?= htmlspecialchars($news['slug']) ?>" class="img-opacity-hover">
                                <img src="<?= img_url($news['image']) ?>" 
                                     alt="<?= htmlspecialchars($news['title']) ?>" 
                                     class="img-fluid width-100 mb-10"
                                     style="height: 120px; object-fit: cover;">
                            </a>
                            <h3 class="title-medium-dark size-sm mb-none">
                                <a href="<?= URLBASE ?>/noticias/post/<?= htmlspecialchars($news['slug']) ?>">
                                    <?= truncate_text($news['title'], 50) ?>
                                </a>
                            </h3>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
</div>
