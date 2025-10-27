<!-- Features Start -->
<div class="container-fluid features mb-5">
  <div class="container py-5">
    <div class="row g-4">
      <?php
      // ======================
      // Últimas 4 noticias reales
      // ======================
      $stmt = $pdo->query("
        SELECT p.id, p.title, p.slug, p.image, p.created_at,
               c.name AS category_name, c.slug AS category_slug
        FROM blog_posts p
        INNER JOIN blog_post_category pc ON pc.post_id = p.id
        INNER JOIN blog_categories c ON c.id = pc.category_id
        WHERE p.status='published' AND p.deleted=0
        ORDER BY p.created_at DESC
        LIMIT 4
      ");
      $latestPosts = $stmt->fetchAll(PDO::FETCH_ASSOC);
      ?>

      <?php if ($latestPosts): ?>
        <?php foreach ($latestPosts as $idx => $p): ?>
          <div class="col-md-6 col-lg-6 col-xl-3">
            <div class="row g-4 align-items-center features-item">
              <div class="col-4">
                <div class="rounded-circle position-relative">
                  <div class="overflow-hidden rounded-circle">
                    <img src="<?= $p['image'] ? URLBASE . '/' . htmlspecialchars($p['image']) : URLBASE . '/template/news/img/features-default.jpg' ?>"
                         class="img-zoomin img-fluid rounded-circle w-100"
                         alt="<?= htmlspecialchars($p['title']) ?>">
                  </div>
                  <span class="rounded-circle border border-2 border-white bg-primary btn-sm-square text-white position-absolute"
                        style="top: 10%; right: -10px;">
                        <?= $idx + 1 ?>
                  </span>
                </div>
              </div>
              <div class="col-8">
                <div class="features-content d-flex flex-column">
                  <p class="text-uppercase mb-2"><?= htmlspecialchars($p['category_name']) ?></p>
                  <a href="<?= URLBASE ?>/<?= htmlspecialchars($p['category_slug']) ?>/<?= htmlspecialchars($p['slug']) ?>/"
   class="h6">
   <?= htmlspecialchars(mb_strimwidth($p['title'], 0, 70, '...')) ?>
</a>

                  <small class="text-body d-block">
                    <i class="fas fa-calendar-alt me-1"></i>
                    <?= fecha_espanol(date("F d, Y", strtotime($p['created_at']))) ?>
                  </small>
                </div>
              </div>
            </div>
          </div>
        <?php endforeach; ?>
      <?php else: ?>
        <p class="text-center">No hay noticias recientes.</p>
      <?php endif; ?>
    </div>
  </div>
</div>
<!-- Features End -->
