<?php

require_once __DIR__ . '/../../inc/config.php';

$categorySlug = $_GET['slug'] ?? null;
$pageNum = isset($_GET['page_num']) ? max(1, (int)$_GET['page_num']) : 1;
$perPage = 6;
$offset  = ($pageNum - 1) * $perPage;

if ($categorySlug) {
    // Categoría
    $stmtCat = $pdo->prepare("SELECT id, name, slug 
                              FROM blog_categories 
                              WHERE slug=? AND status='active' AND deleted=0 
                              LIMIT 1");
    $stmtCat->execute([$categorySlug]);
    $category = $stmtCat->fetch();

    if (!$category) {
        http_response_code(404);
        include __DIR__ . '/404.php';
        exit;
    }

    // Total posts
    $stmtCount = $pdo->prepare("
        SELECT COUNT(*) 
        FROM blog_posts p
        INNER JOIN blog_post_category pc ON pc.post_id = p.id
        WHERE pc.category_id=? AND p.status='published' AND p.deleted=0
    ");
    $stmtCount->execute([$category['id']]);
    $totalPosts = $stmtCount->fetchColumn();

    // Posts con paginación
    $stmt = $pdo->prepare("
        SELECT p.*, c.name AS category_name, c.slug AS category_slug
        FROM blog_posts p
        INNER JOIN blog_post_category pc ON pc.post_id = p.id
        INNER JOIN blog_categories c ON c.id = pc.category_id
        WHERE c.id=? AND p.status='published' AND p.deleted=0
        ORDER BY p.created_at DESC
        LIMIT $perPage OFFSET $offset
    ");
    $stmt->execute([$category['id']]);
    $posts = $stmt->fetchAll();
} else {
    // Todas las noticias
    $stmtCount = $pdo->query("
        SELECT COUNT(*) FROM blog_posts
        WHERE status='published' AND deleted=0
    ");
    $totalPosts = $stmtCount->fetchColumn();

    $stmt = $pdo->query("
        SELECT p.*, c.name AS category_name, c.slug AS category_slug
        FROM blog_posts p
        INNER JOIN blog_post_category pc ON pc.post_id = p.id
        INNER JOIN blog_categories c ON c.id = pc.category_id
        WHERE p.status='published' AND p.deleted=0
        ORDER BY p.created_at DESC
        LIMIT $perPage OFFSET $offset
    ");
    $posts = $stmt->fetchAll();
    $category = ['name' => 'Noticias', 'slug' => 'noticias'];
}

// Total de páginas
$totalPages = ceil($totalPosts / $perPage);

?>

<!-- News With Sidebar Start -->
<div class="container-fluid py-3">
    <div class="container">
        <div class="row">
            <div class="col-lg-8">
                <div class="row">
                    <div class="col-12">
                        <div class="d-flex align-items-center justify-content-between bg-light py-2 px-4 mb-3">
                            <h3 class="m-0"><?= htmlspecialchars($category['name']) ?></h3>
                            <a class="text-secondary font-weight-medium text-decoration-none" href="<?= URLBASE ?>/noticias/">
                                View All
                            </a>
                        </div>
                    </div>

                    <?php if ($posts): ?>
                        <?php foreach ($posts as $index => $p): ?>
                            <div class="col-lg-6">
                                <div class="position-relative mb-3">
                                    <img class="img-fluid w-100"
                                         src="<?= URLBASE . '/' . htmlspecialchars($p['image']) ?>"
                                         style="object-fit: cover;">
                                    <div class="overlay position-relative bg-light">
                                        <div class="mb-2" style="font-size: 14px;">
                                            <a href="<?= URLBASE ?>/noticias/<?= htmlspecialchars($p['category_slug']) ?>/">
                                                <?= htmlspecialchars($p['category_name']) ?>
                                            </a>
                                            <span class="px-1">/</span>
                                            <span><?= date("M d, Y", strtotime($p['created_at'])) ?></span>
                                        </div>
                                        <a class="h4" href="<?= URLBASE ?>/<?= htmlspecialchars($p['category_slug']) ?>/<?= htmlspecialchars($p['slug']) ?>/">
                                            <?= htmlspecialchars($p['title']) ?>
                                        </a>
                                        <p class="m-0">
                                            <?= htmlspecialchars(substr(strip_tags($p['seo_description'] ?: $p['content']), 0, 120)) ?>...
                                        </p>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="col-12">
                            <p>No hay noticias en esta categoría.</p>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- ADS Banner -->
                <div class="mb-3 pb-3">
                        <?php
    $stmt = $pdo->prepare("
        SELECT * FROM ads 
        WHERE position = 2 AND status = 'active' 
        LIMIT 1
    ");
    $stmt->execute();
    $ad = $stmt->fetch(PDO::FETCH_ASSOC);
    ?>

    <?php if ($ad && !empty($ad['image_url'])): ?>
        <?php if (!empty($ad['target_url'])): ?>
            <a href="<?= htmlspecialchars($ad['target_url']) ?>" target="_blank" rel="noopener">
                <img class="img-fluid"
                     src="<?= URLBASE . htmlspecialchars($ad['image_url']) ?>"
                     alt="<?= htmlspecialchars($ad['title'] ?? 'Publicidad') ?>">
            </a>
        <?php else: ?>
            <img class="img-fluid"
                 src="<?= URLBASE . htmlspecialchars($ad['image_url']) ?>"
                 alt="<?= htmlspecialchars($ad['title'] ?? 'Publicidad') ?>">
        <?php endif; ?>
    <?php endif; ?>
                    </div>
				
				
                <!-- Mini posts -->
                <div class="row">
                    <?php foreach ($posts as $p): ?>
                        <div class="col-lg-6">
                            <div class="d-flex mb-3">
                                <img src="<?= URLBASE . '/' . htmlspecialchars($p['image']) ?>"
                                     style="width: 100px; height: 100px; object-fit: cover;">
                                <div class="w-100 d-flex flex-column justify-content-center bg-light px-3" style="height: 100px;">
                                    <div class="mb-1" style="font-size: 13px;">
                                        <a href="<?= URLBASE ?>/noticias/<?= htmlspecialchars($p['category_slug']) ?>/">
                                            <?= htmlspecialchars($p['category_name']) ?>
                                        </a>
                                        <span class="px-1">/</span>
                                        <span><?= date("M d, Y", strtotime($p['created_at'])) ?></span>
                                    </div>
                                    <a class="h6 m-0" href="<?= URLBASE ?>/<?= htmlspecialchars($p['category_slug']) ?>/<?= htmlspecialchars($p['slug']) ?>/">
                                        <?= htmlspecialchars($p['title']) ?>
                                    </a>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

                <!-- Pagination -->
                <div class="row">
                    <?php if ($totalPages > 1): ?>
<nav aria-label="Page navigation">
  <ul class="pagination justify-content-center">

    <!-- Prev -->
    <li class="page-item <?= $pageNum <= 1 ? 'disabled' : '' ?>">
      <a class="page-link" href="<?= URLBASE ?>/noticias<?= $categorySlug ? '/'.htmlspecialchars($category['slug']) : '' ?>/page/<?= $pageNum-1 ?>/" aria-label="Previous">
        <span class="fa fa-angle-double-left"></span>
      </a>
    </li>

    <!-- Números -->
    <?php
      $range = 2; // números alrededor del actual
      $ellipsisShown = false;

      for ($i=1; $i <= $totalPages; $i++) {
          if ($i == 1 || $i == $totalPages || ($i >= $pageNum - $range && $i <= $pageNum + $range)) {
              echo '<li class="page-item '.($i == $pageNum ? 'active' : '').'">
                      <a class="page-link" href="'.URLBASE.'/noticias'.($categorySlug ? '/'.htmlspecialchars($category['slug']) : '').'/page/'.$i.'/">'.$i.'</a>
                    </li>';
              $ellipsisShown = false;
          } else {
              if (!$ellipsisShown) {
                  echo '<li class="page-item disabled"><span class="page-link">...</span></li>';

                  $ellipsisShown = true;
              }
          }
      }
    ?>

    <!-- Next -->
    <li class="page-item <?= $pageNum >= $totalPages ? 'disabled' : '' ?>">
      <a class="page-link" href="<?= URLBASE ?>/noticias<?= $categorySlug ? '/'.htmlspecialchars($category['slug']) : '' ?>/page/<?= $pageNum+1 ?>/" aria-label="Next">
        <span class="fa fa-angle-double-right"></span>
      </a>
    </li>

  </ul>
</nav>
<?php endif; ?>

                </div>
            </div>

            <!-- Sidebar -->
           
                <?php include __DIR__ . '/partials/sidebar.php'; ?>
            
        </div>
    </div>
</div>
<!-- News With Sidebar End -->



