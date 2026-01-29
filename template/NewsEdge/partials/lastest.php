<?php
/* ===== Consulta: Últimas 4 noticias con excerpt ===== */
$sqlLatest = "
    SELECT p.id, p.title, p.slug, p.image, p.content, p.created_at, p.author,
           c.name AS category_name, c.slug AS category_slug
    FROM blog_posts p
    LEFT JOIN blog_post_category pc ON pc.post_id = p.id
    LEFT JOIN blog_categories c ON c.id = pc.category_id
    WHERE p.status = 'published' AND p.deleted = 0
    GROUP BY p.id
    ORDER BY p.created_at DESC
    LIMIT 4
";
$latestNews = db()->query($sqlLatest)->fetchAll();
?>

<div class="col-xl-8 col-lg-12 mb-30">
    <div class="item-box-light-md-less30 ie-full-width">
        <div class="topic-border color-cinnabar mb-30">
            <div class="topic-box-lg color-cinnabar">ÚLTIMAS NOTICIAS</div>
        </div>
        <div class="row">
            <?php foreach ($latestNews as $news): 
                $excerpt = truncate_text(strip_tags($news['content']), 150);
                $postUrl = URLBASE . "/noticias/post/" . htmlspecialchars($news['slug']);
            ?>
            <div class="col-lg-12 col-md-6 col-sm-12">
                <div class="media media-none--md mb-30">
                    <!-- Imagen -->
                    <div class="position-relative width-40">
                        <a href="<?= $postUrl ?>" class="img-opacity-hover">
                            <img src="<?= img_url($news['image']) ?>" 
                                 alt="<?= htmlspecialchars($news['title']) ?>" 
                                 class="img-fluid"
                                 style="height: 150px; object-fit: cover;">
                        </a>
                        <div class="topic-box-top-xs">
                            <div class="topic-box-sm color-cod-gray mb-20">
                                <?= htmlspecialchars($news['category_name'] ?? 'Noticia') ?>
                            </div>
                        </div>
                    </div>

                    <!-- Contenido -->
                    <div class="media-body p-mb-none-child media-margin30">
                        <div class="post-date-dark">
                            <ul>
                                <li>
                                    <span>por</span>
                                    <a href="<?= $postUrl ?>">
                                        <?= htmlspecialchars($news['author'] ?? 'Admin') ?>
                                    </a>
                                </li>
                                <li>
                                    <span>
                                        <i class="fa fa-calendar" aria-hidden="true"></i>
                                    </span>
                                    <?= date('M d, Y', strtotime($news['created_at'])) ?>
                                </li>
                            </ul>
                        </div>
                        <h3 class="title-semibold-dark size-lg mb-15">
                            <a href="<?= $postUrl ?>">
                                <?= truncate_text($news['title'], 80) ?>
                            </a>
                        </h3>
                        <p><?= $excerpt ?></p>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>