<?php
require_once __DIR__ . '/../../../inc/config.php';

/* ================================
   1. CATEGORÍAS POPULARES
================================ */
$stmtCat = $pdo->query("
    SELECT c.id, c.name, c.slug, COUNT(pc.post_id) AS total
    FROM blog_categories c
    LEFT JOIN blog_post_category pc ON c.id = pc.category_id
    INNER JOIN blog_posts p ON p.id = pc.post_id AND p.status='published' AND p.deleted=0
    GROUP BY c.id, c.name, c.slug
    ORDER BY total DESC
    LIMIT 6
");
$categories = $stmtCat->fetchAll();

/* ================================
   2. NOTICIAS POPULARES (por vistas)
================================ */
$stmtPopular = $pdo->query("
    SELECT p.id, p.title, p.slug, p.image, p.created_at, c.slug AS category_slug, c.name AS category_name,
           COUNT(v.id) AS views
    FROM blog_post_views v
    INNER JOIN blog_posts p ON p.id = v.post_id
    INNER JOIN blog_post_category pc ON pc.post_id = p.id
    INNER JOIN blog_categories c ON c.id = pc.category_id
    WHERE p.status='published' AND p.deleted=0
    GROUP BY p.id
    ORDER BY views DESC
    LIMIT 4
");
$popularPosts = $stmtPopular->fetchAll();

/* ================================
   3. TAGS TENDENCIA
================================ */
$stmtTags = $pdo->query("
    SELECT DISTINCT LOWER(TRIM(t)) AS tag
    FROM (
        SELECT SUBSTRING_INDEX(SUBSTRING_INDEX(p.tags, ',', n.n), ',', -1) AS t
        FROM blog_posts p
        INNER JOIN (
            SELECT a.N + b.N * 10 + 1 AS n
            FROM (SELECT 0 AS N UNION ALL SELECT 1 UNION ALL SELECT 2 UNION ALL SELECT 3 UNION ALL SELECT 4
                  UNION ALL SELECT 5 UNION ALL SELECT 6 UNION ALL SELECT 7 UNION ALL SELECT 8 UNION ALL SELECT 9) a,
                 (SELECT 0 AS N UNION ALL SELECT 1 UNION ALL SELECT 2 UNION ALL SELECT 3 UNION ALL SELECT 4
                  UNION ALL SELECT 5 UNION ALL SELECT 6 UNION ALL SELECT 7 UNION ALL SELECT 8 UNION ALL SELECT 9) b
        ) n
        WHERE n.n <= 1 + (LENGTH(p.tags) - LENGTH(REPLACE(p.tags, ',', '')))
          AND p.tags IS NOT NULL
          AND p.tags <> ''
    ) tags
    WHERE tag <> ''
    ORDER BY RAND()
    LIMIT 8
");
$tags = $stmtTags->fetchAll(PDO::FETCH_COLUMN);

/* ================================
   4. BANNER LATERAL (dinámico)
================================ */
$stmtBanner = $pdo->prepare("SELECT * FROM ads WHERE position = ? LIMIT 1");
$stmtBanner->execute([2]); // ejemplo: banner lateral
$banner = $stmtBanner->fetch();
?>

<div class="col-lg-4">
    <div class="row g-4">
        <div class="col-12">
            <div class="p-3 rounded border">
                <!-- 🔍 Buscador -->
                <form method="get" action="<?= URLBASE ?>/buscar.php" class="mb-4">
                    <div class="input-group w-100 mx-auto d-flex">
                        <input type="search" name="q" class="form-control p-3" placeholder="Buscar..." value="<?= htmlspecialchars($_GET['q'] ?? '') ?>">
                        <button type="submit" id="search-icon-1" class="btn btn-primary input-group-text p-3">
                            <i class="fa fa-search text-white"></i>
                        </button>
                    </div>
                </form>

                <!-- 📂 Categorías populares -->
                <h4 class="mb-4">Categorías Populares</h4>
                <div class="row g-2 mb-4">
                    <?php foreach ($categories as $cat): ?>
                        <div class="col-12">
                            <a href="<?= URLBASE ?>/noticias/<?= htmlspecialchars($cat['slug']) ?>/"
                               class="link-hover btn btn-light w-100 rounded text-uppercase text-dark py-3">
                               <?= htmlspecialchars($cat['name']) ?> (<?= $cat['total'] ?>)
                            </a>
                        </div>
                    <?php endforeach; ?>
                </div>

                <!-- 🌐 Redes sociales -->
                <h4 class="my-4">Síguenos</h4>
                <div class="row g-3 mb-4">
                    <?php if (!empty($sys['facebook'])): ?>
                        <a href="<?= htmlspecialchars($sys['facebook']) ?>" target="_blank" class="w-100 rounded btn btn-primary d-flex align-items-center p-3 mb-2">
                            <i class="fab fa-facebook-f btn btn-light btn-square rounded-circle me-3"></i>
                            <span class="text-white">Facebook</span>
                        </a>
                    <?php endif; ?>
                    <?php if (!empty($sys['twitter'])): ?>
                        <a href="<?= htmlspecialchars($sys['twitter']) ?>" target="_blank" class="w-100 rounded btn btn-info d-flex align-items-center p-3 mb-2">
                            <i class="fab fa-twitter btn btn-light btn-square rounded-circle me-3"></i>
                            <span class="text-white">Twitter</span>
                        </a>
                    <?php endif; ?>
                    <?php if (!empty($sys['youtube'])): ?>
                        <a href="<?= htmlspecialchars($sys['youtube']) ?>" target="_blank" class="w-100 rounded btn btn-danger d-flex align-items-center p-3 mb-2">
                            <i class="fab fa-youtube btn btn-light btn-square rounded-circle me-3"></i>
                            <span class="text-white">YouTube</span>
                        </a>
                    <?php endif; ?>
                    <?php if (!empty($sys['instagram'])): ?>
                        <a href="<?= htmlspecialchars($sys['instagram']) ?>" target="_blank" class="w-100 rounded btn btn-dark d-flex align-items-center p-3 mb-2">
                            <i class="fab fa-instagram btn btn-light btn-square rounded-circle me-3"></i>
                            <span class="text-white">Instagram</span>
                        </a>
                    <?php endif; ?>
                    <?php if (!empty($sys['tiktok'])): ?>
                        <a href="<?= htmlspecialchars($sys['tiktok']) ?>" target="_blank" class="w-100 rounded btn btn-secondary d-flex align-items-center p-3 mb-2">
                            <i class="fab fa-tiktok btn btn-light btn-square rounded-circle me-3"></i>
                            <span class="text-white">TikTok</span>
                        </a>
                    <?php endif; ?>
                </div>

                <!-- 🔥 Noticias populares -->
                <h4 class="my-4">Noticias Populares</h4>
                <div class="row g-4">
                    <?php foreach ($popularPosts as $index => $pop): ?>
                        <div class="col-12">
                            <div class="row g-3 align-items-center features-item">
                                <div class="col-4">
                                    <div class="rounded-circle position-relative">
                                        <div class="overflow-hidden rounded-circle">
                                            <img src="<?= $pop['image'] ? URLBASE . '/' . htmlspecialchars($pop['image']) : URLBASE . '/template/newsers/img/default.jpg' ?>"
                                                 class="img-zoomin img-fluid rounded-circle w-100" alt="<?= htmlspecialchars($pop['title']) ?>">
                                        </div>
                                        <span class="rounded-circle border border-2 border-white bg-primary btn-sm-square text-white position-absolute" style="top:10%; right:-10px;">
                                            <?= $index + 1 ?>
                                        </span>
                                    </div>
                                </div>
                                <div class="col-8">
                                    <div class="features-content d-flex flex-column">
                                        <p class="text-uppercase mb-1 text-primary small"><?= htmlspecialchars($pop['category_name']) ?></p>
                                        <a href="<?= URLBASE ?>/<?= htmlspecialchars($pop['category_slug']) ?>/<?= htmlspecialchars($pop['slug']) ?>/" class="h6 link-hover text-dark">
                                            <?= htmlspecialchars($pop['title']) ?>
                                        </a>
                                        <small class="text-body d-block"><i class="fas fa-calendar-alt me-1"></i>
                                            <?= fecha_espanol(date("F d, Y", strtotime($pop['created_at']))) ?>
                                        </small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

                <!-- 🏷️ Tags en tendencia -->
                <?php if ($tags): ?>
                    <div class="col-lg-12 mt-4">
                        <div class="border-bottom my-3 pb-3">
                            <h4 class="mb-0">Tendencias</h4>
                        </div>
                        <ul class="nav nav-pills d-inline-flex text-center mb-4 flex-wrap">
                            <?php foreach ($tags as $tag): ?>
                                <li class="nav-item mb-3">
                                    <a class="d-flex py-2 bg-light rounded-pill me-2" href="<?= URLBASE ?>/buscar.php?tag=<?= urlencode($tag) ?>">
                                        <span class="text-dark link-hover" style="width:90px;"><?= htmlspecialchars($tag) ?></span>
                                    </a>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>

                <!-- 📢 Banner lateral -->
                <?php if ($banner): ?>
                    <div class="col-lg-12 mt-4">
                        <div class="position-relative banner-2">
                            <a href="<?= htmlspecialchars($banner['url']) ?>" target="_blank">
                                <img src="<?= URLBASE . '/' . htmlspecialchars($banner['image']) ?>" class="img-fluid w-100 rounded" alt="Banner">
                            </a>
                            <?php if (!empty($banner['title'])): ?>
                                <div class="text-center banner-content-2">
                                    <h6 class="mb-2 text-white"><?= htmlspecialchars($banner['title']) ?></h6>
                                    <?php if (!empty($banner['subtitle'])): ?>
                                        <p class="text-white mb-2"><?= htmlspecialchars($banner['subtitle']) ?></p>
                                    <?php endif; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
