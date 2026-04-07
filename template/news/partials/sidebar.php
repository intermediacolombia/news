<div class="col-lg-4 pt-3 pt-lg-0">
                    <!-- Social Follow Start -->
                    <div class="pb-3">
                        <div class="bg-light py-2 px-4 mb-3 title-widgets">
                            <h3 class="m-0"><?= t_theme('theme_siguenos') ?></h3>
                        </div>
                        <div class="d-flex mb-3">
							<?php if (!empty($sys['facebook'])): ?>
                            <a href="<?= htmlspecialchars($sys['facebook']) ?>" target="_blank" class="d-block w-50 py-2 px-3 text-white text-decoration-none mr-2" style="background: #39569E;">
                                <small class="fa fa-facebook-f mr-2"></small><small><?= t_theme('theme_facebook') ?></small>
                            </a>
							<?php endif; ?>
							<?php if (!empty($sys['twitter'])): ?>
                            <a href="<?= htmlspecialchars($sys['twitter']) ?>" target="_blank" class="d-block w-50 py-2 px-3 text-white text-decoration-none ml-2" style="background: #000000;">
                                <small class="fa fa-x-twitter mr-2"></small><small><?= t_theme('theme_twitter') ?></small>
                            </a>
							<?php endif; ?>
                        </div>
						<?php if (!empty($sys['instagram'])): ?>
                        <div class="d-flex mb-3">
                            <a href="<?= htmlspecialchars($sys['instagram']) ?>" target="_blank" class="d-block w-50 py-2 px-3 text-white text-decoration-none mr-2" style="background: #C13584;">
                                <small class="fa fa-instagram mr-2"></small><small><?= t_theme('theme_instagram') ?></small>
                            </a>
							<?php endif; ?>
							<?php if (!empty($sys['tiktok'])): ?>
                            <a href="<?= htmlspecialchars($sys['tiktok']) ?>" target="_blank" class="d-block w-50 py-2 px-3 text-white text-decoration-none ml-2" style="background: #000000;">
                                <small class="fa fa-tiktok mr-2"></small><small><?= t_theme('theme_tiktok') ?></small>
                            </a>
							<?php endif; ?>
                        </div>
                        <div class="d-flex mb-3">
							<?php if (!empty($sys['youtube'])): ?>
                            <a href="<?= htmlspecialchars($sys['youtube']) ?>" target="_blank" class="d-block w-50 py-2 px-3 text-white text-decoration-none mr-2" style="background: #FF0000;">
                                <small class="fa fa-youtube mr-2"></small><small><?= t_theme('theme_youtube') ?></small>
                            </a>
							<?php endif; ?>
							<?php if (!empty($sys['whatsapp'])): ?>
                            <a href="<?= htmlspecialchars($sys['whatsapp']) ?>" target="_blank" class="d-block w-50 py-2 px-3 text-white text-decoration-none ml-2" style="background: #075E54;">
                                <small class="fa fa-whatsapp mr-2"></small><small>WhatsApp</small>
                            </a>
							<?php endif; ?>
							
                        </div>
                    </div>
					<?= $sys['code_sliderbar'] ?>
                    <!-- Social Follow End -->

                    <!-- Newsletter Start -
                    <div class="pb-3">
                        <div class="bg-light py-2 px-4 mb-3">
                            <h3 class="m-0"><?= t_theme('theme_newsletter') ?></h3>
                        </div>
                        <div class="bg-light text-center p-4 mb-3">
                            <p>Aliqu justo et labore at eirmod justo sea erat diam dolor diam vero kasd</p>
                            <div class="input-group" style="width: 100%;">
                                <input type="text" class="form-control form-control-lg" placeholder="<?= t_theme('theme_tu_email') ?>">
                                <div class="input-group-append">
                                    <button class="btn btn-primary"><?= t_theme('theme_sign_up') ?></button>
                                </div>
                            </div>
                            <small>Sit eirmod nonumy kasd eirmod</small>
                        </div>
                    </div>
                    <!-- Newsletter End -->

                    <!-- Ads Start --->
                    <?php include __DIR__ . '/ads5.php'; ?>
                    <!-- Ads End -->

                    <!-- Popular News Start -->
                    <?php
//require_once __DIR__ . '/../../../inc/config.php';

// Obtener 5 noticias populares (aleatorias entre las más vistas en total)
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
    ORDER BY RAND()
    LIMIT 5
");
$trendingPosts = $stmt->fetchAll();
?>

<?php if ($trendingPosts): ?>
<div class="pb-3">
    <div class="bg-light py-2 px-4 mb-3 title-widgets">
        <h3 class="m-0"><?= t_theme('theme_tendencias') ?></h3>
    </div>

    <?php foreach ($trendingPosts as $post): ?>
        <div class="d-flex mb-3">
            <img src="<?= $post['image'] ? URLBASE . '/' . htmlspecialchars($post['image']) : URLBASE . '/template/news/img/news-100x100-1.jpg' ?>"
                 alt="<?= htmlspecialchars(get_image_alt($post['image'], $post['title'])) ?>"
                 style="width: 100px; height: 100px; object-fit: cover;">
            <div class="w-100 d-flex flex-column justify-content-center bg-light px-3" style="height: 100px;">
                <div class="mb-1" style="font-size: 13px;">
                    <a href="<?= URLBASE ?>/noticias/<?= htmlspecialchars($post['category_slug']) ?>/">
                        <?= htmlspecialchars($post['category_name']) ?>
                    </a>
                    <span class="px-1">/</span>
                    <span><?= fecha_espanol(date("F d, Y", strtotime($post['created_at']))) ?></span>
                </div>
                <a class="h6 m-0" href="<?= URLBASE ?>/<?= htmlspecialchars($post['category_slug']) ?>/<?= htmlspecialchars($post['slug']) ?>/">
                    <?= htmlspecialchars($post['title']) ?>
                </a>
            </div>
        </div>
    <?php endforeach; ?>
</div>
<?php endif; ?>


                    <!-- Popular News End -->

                    <!-- Tags Start -
                    <div class="pb-3">
                        <div class="bg-light py-2 px-4 mb-3">
                            <h3 class="m-0"><?= t_theme('theme_tags') ?></h3>
                        </div>
                        <div class="d-flex flex-wrap m-n1">
                            <a href="" class="btn btn-sm btn-outline-secondary m-1">Politics</a>
                            <a href="" class="btn btn-sm btn-outline-secondary m-1">Business</a>
                            <a href="" class="btn btn-sm btn-outline-secondary m-1">Corporate</a>
                            <a href="" class="btn btn-sm btn-outline-secondary m-1">Sports</a>
                            <a href="" class="btn btn-sm btn-outline-secondary m-1">Health</a>
                            <a href="" class="btn btn-sm btn-outline-secondary m-1">Education</a>
                            <a href="" class="btn btn-sm btn-outline-secondary m-1">Science</a>
                            <a href="" class="btn btn-sm btn-outline-secondary m-1">Technology</a>
                            <a href="" class="btn btn-sm btn-outline-secondary m-1">Foods</a>
                            <a href="" class="btn btn-sm btn-outline-secondary m-1">Entertainment</a>
                            <a href="" class="btn btn-sm btn-outline-secondary m-1">Travel</a>
                            <a href="" class="btn btn-sm btn-outline-secondary m-1">Lifestyle</a>
                        </div>
                    </div>
                    <!-- Tags End -->
                </div>