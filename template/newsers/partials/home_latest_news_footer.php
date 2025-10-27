<?php
/* ===== Consulta: Obtener las 5 categorías con más posts ===== */
$sqlCategories = "
  SELECT c.id, c.name, c.slug, COUNT(pc.post_id) AS total_posts
  FROM blog_categories c
  LEFT JOIN blog_post_category pc ON c.id = pc.category_id
  INNER JOIN blog_posts p ON p.id = pc.post_id AND p.status='published' AND p.deleted=0
  GROUP BY c.id, c.name, c.slug
  HAVING total_posts > 0
  ORDER BY total_posts DESC
  LIMIT 5
";
$topCategories = $pdo->query($sqlCategories)->fetchAll();

/* ===== Función para obtener posts por categoría ===== */
function getPostsByCategory($pdo, $categoryId, $limit = 6) {
    $sql = "
      SELECT p.id, p.title, p.slug, p.image, p.created_at, p.content, p.author,
             c.name AS category_name, c.slug AS category_slug,
             COALESCE(COUNT(v.id), 0) AS views
      FROM blog_posts p
      LEFT JOIN blog_post_views v ON v.post_id = p.id
      INNER JOIN blog_post_category pc ON pc.post_id = p.id
      INNER JOIN blog_categories c ON c.id = pc.category_id
      WHERE p.status = 'published' AND p.deleted = 0 AND c.id = :category_id
      GROUP BY p.id, p.title, p.slug, p.image, p.created_at, p.content, p.author, c.name, c.slug
      ORDER BY p.created_at DESC
      LIMIT :limit
    ";
    $stmt = $pdo->prepare($sql);
    $stmt->bindValue(':category_id', $categoryId, PDO::PARAM_INT);
    $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetchAll();
}

/* ===== Consulta: Most Views News (noticias más vistas) ===== */
$sqlMostViews = "
  SELECT p.id, p.title, p.slug, p.image, p.created_at, p.author,
         c.name AS category_name, c.slug AS category_slug,
         COUNT(v.id) AS views
  FROM blog_post_views v
  INNER JOIN blog_posts p ON p.id = v.post_id
  LEFT JOIN blog_post_category pc ON pc.post_id = p.id
  LEFT JOIN blog_categories c ON c.id = pc.category_id
  WHERE p.status = 'published' AND p.deleted = 0
  GROUP BY p.id, p.title, p.slug, p.image, p.created_at, p.author, c.name, c.slug
  ORDER BY views DESC, p.created_at DESC
  LIMIT 10
";
$mostViewedNews = $pdo->query($sqlMostViews)->fetchAll();
?>

<div class="container-fluid populer-news py-5">
    <div class="container py-5">
        <div class="tab-class mb-4">
            <div class="row g-4">
                <div class="col-lg-8 col-xl-9">
                    <div class="d-flex flex-column flex-md-row justify-content-md-between border-bottom mb-4">
                        <h1 class="mb-4">What's New</h1>
                        <ul class="nav nav-pills d-inline-flex text-center">
                            <?php foreach ($topCategories as $index => $cat): ?>
                            <li class="nav-item mb-3">
                                <a class="d-flex py-2 bg-light rounded-pill <?= $index === 0 ? 'active' : '' ?> me-2" 
                                   data-bs-toggle="pill" 
                                   href="#tab-<?= $cat['id'] ?>">
                                    <span class="text-dark" style="width: 100px;">
                                        <?= htmlspecialchars($cat['name']) ?>
                                    </span>
                                </a>
                            </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                    
                    <div class="tab-content mb-4">
                        <?php foreach ($topCategories as $index => $cat): ?>
                            <?php $categoryPosts = getPostsByCategory($pdo, $cat['id'], 6); ?>
                            <?php $mainPost = $categoryPosts[0] ?? null; ?>
                            <?php $sidePosts = array_slice($categoryPosts, 1, 5); ?>
                            
                            <div id="tab-<?= $cat['id'] ?>" class="tab-pane fade <?= $index === 0 ? 'show active' : '' ?> p-0">
                                <div class="row g-4">
                                    <?php if ($mainPost): ?>
                                    <div class="col-lg-8">
                                        <div class="position-relative rounded overflow-hidden">
                                            <a href="<?= URLBASE ?>/<?= htmlspecialchars($mainPost['category_slug']) ?>/<?= htmlspecialchars($mainPost['slug']) ?>/">
                                                <img src="<?= img_url($mainPost['image']) ?>" 
                                                     class="img-zoomin img-fluid rounded w-100" 
                                                     alt="<?= htmlspecialchars($mainPost['title']) ?>">
                                            </a>
                                            <div class="position-absolute text-white px-4 py-2 bg-primary rounded" 
                                                 style="top: 20px; right: 20px;">
                                                <?= htmlspecialchars($cat['name']) ?>
                                            </div>
                                        </div>
                                        <div class="my-4">
                                            <a href="<?= URLBASE ?>/<?= htmlspecialchars($mainPost['category_slug']) ?>/<?= htmlspecialchars($mainPost['slug']) ?>/" 
                                               class="h4">
                                                <?= htmlspecialchars($mainPost['title']) ?>
                                            </a>
                                        </div>
                                        <div class="d-flex justify-content-between">
                                            <span class="text-dark me-3">
                                                <i class="fa fa-clock"></i> 
                                                <?= str_pad(read_time_minutes($mainPost['content']), 2, '0', STR_PAD_LEFT) ?> minute read
                                            </span>
                                            <span class="text-dark me-3">
                                                <i class="fa fa-eye"></i> 
                                                <?= number_format((int)$mainPost['views'], 0, ',', '.') ?> Views
                                            </span>
                                        </div>
                                        <p class="my-4">
                                            <?= safe_excerpt($mainPost['content'], 35) ?>
                                        </p>
                                    </div>
                                    <?php endif; ?>
                                    
                                    <div class="col-lg-4">
                                        <div class="row g-4">
                                            <?php foreach ($sidePosts as $post): ?>
                                            <div class="col-12">
                                                <div class="row g-4 align-items-center">
                                                    <div class="col-5">
                                                        <div class="overflow-hidden rounded">
                                                            <a href="<?= URLBASE ?>/<?= htmlspecialchars($post['category_slug']) ?>/<?= htmlspecialchars($post['slug']) ?>/">
                                                                <img src="<?= img_url($post['image']) ?>" 
                                                                     class="img-zoomin img-fluid rounded w-100" 
                                                                     alt="<?= htmlspecialchars($post['title']) ?>">
                                                            </a>
                                                        </div>
                                                    </div>
                                                    <div class="col-7">
                                                        <div class="features-content d-flex flex-column">
                                                            <p class="text-uppercase mb-2">
                                                                <?= htmlspecialchars($cat['name']) ?>
                                                            </p>
                                                            <a href="<?= URLBASE ?>/<?= htmlspecialchars($post['category_slug']) ?>/<?= htmlspecialchars($post['slug']) ?>/" 
                                                               class="h6">
                                                                <?= truncate_text($post['title'], 60) ?>
                                                            </a>
                                                            <small class="text-body d-block">
                                                                <i class="fas fa-calendar-alt me-1"></i>
                                                                <?= fecha_espanol(date("F d, Y", strtotime($post['created_at']))) ?>
                                                            </small>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <?php endforeach; ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    
                    <!-- Most Views News -->
                    <div class="border-bottom mb-4">
                        <h2 class="my-4">Most Views News</h2>
                    </div>
                    <div class="whats-carousel owl-carousel">
                        <?php foreach ($mostViewedNews as $newsItem): ?>
                        <div class="latest-news-item">
                            <div class="bg-light rounded">
                                <div class="rounded-top overflow-hidden" style="height: 250px;">
                                    <a href="<?= URLBASE ?>/<?= htmlspecialchars($newsItem['category_slug']) ?>/<?= htmlspecialchars($newsItem['slug']) ?>/">
                                        <img src="<?= img_url($newsItem['image']) ?>" 
                                             class="img-zoomin img-fluid rounded-top w-100 h-100" 
                                             style="object-fit: cover; object-position: center;"
                                             alt="<?= htmlspecialchars($newsItem['title']) ?>">
                                    </a>
                                </div>
                                <div class="d-flex flex-column p-4">
                                    <a href="<?= URLBASE ?>/<?= htmlspecialchars($newsItem['category_slug']) ?>/<?= htmlspecialchars($newsItem['slug']) ?>/" 
                                       class="h4"
                                       title="<?= htmlspecialchars($newsItem['title']) ?>">
                                        <?= truncate_text($newsItem['title'], 70) ?>
                                    </a>
                                    <div class="d-flex justify-content-between">
                                        <?php if (!empty($newsItem['author'])): ?>
                                        <span class="small text-body">by <?= htmlspecialchars($newsItem['author']) ?></span>
                                        <?php else: ?>
                                        <span class="small text-body">by <?= htmlspecialchars($sys['site_name'] ?? 'Admin') ?></span>
                                        <?php endif; ?>
                                        
                                        <small class="text-body d-block">
                                            <i class="fas fa-calendar-alt me-1"></i>
                                            <?= fecha_espanol(date("F d, Y", strtotime($newsItem['created_at']))) ?>
                                        </small>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    
                </div>
                <?php include __DIR__ . '/partials/sidebar.php'; ?>
            </div>
        </div>
    </div>
</div>