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
<?php
// =======================
// Variables SEO dinámicas
// =======================
$page_title       = "Noticias ".$category['name']." | " . NOMBRE_SITIO;
$page_description = "Últimas noticias"  . NOMBRE_SITIO;
$page_keywords    = NOMBRE_SITIO . ", noticias, informacion, " . NOMBRE_SITIO;
$page_author      = NOMBRE_SITIO;

// Imagen SEO → primera del producto o logo por defecto
$page_image = rtrim(URLBASE, '/') .FAVICON;
if (!empty($images)) {
    $path = $images[0]['path'];
    $path = ($path[0] === '/') ? $path : '/' . $path;
    $page_image = rtrim(URLBASE, '/') . $path;
}

// Canonical automático (desde URL actual)
$currentPath    = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$page_canonical = rtrim(URLBASE, '/') . '/' . ltrim($currentPath, '/');

// =======================
// Fin SEO
// =======================

?>

<!-- News With Sidebar Start -->
<div class="container-fluid py-5">
<div class="container py-5">
	
   
        <div class="row">
            <div class="col-lg-8">
                <div class="row">
                    <div class="col-12">
                        <div class="d-flex align-items-center justify-content-between bg-light py-2 px-4 mb-3 title-widgets">
            <h3 class="m-0"><?= htmlspecialchars($category['name']) ?></h3>
                            <a class="text-secondary font-weight-medium text-decoration-none" href="<?= URLBASE ?>/noticias/">
                              <?php if (!empty($categorySlug)): ?>
    							Ver Todas
								<?php endif; ?>


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
                                            <span><?= fecha_espanol(date("F d, Y", strtotime($p['created_at']))) ?></span>
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
                                        <span><?= fecha_espanol(date("F d, Y", strtotime($p['created_at']))) ?></span>
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
           <div class="col-lg-4">
                <?php include __DIR__ . '/partials/sidebar.php'; ?>
            
        </div>
        </div>
    </div>
</div>

<style>
/* ===============================
   ESTILOS MODERNOS DE LISTADO DE NOTICIAS
   =============================== */
body {
  background-color: #f5f7fa;
  font-family: "Roboto", sans-serif;
  color: #333;
}

/* ======= Título de categoría ======= */
.title-widgets {
  border-left: 4px solid #0d6efd;
  background-color: #f8f9fa;
  border-radius: 0.5rem;
  box-shadow: 0 2px 8px rgba(0,0,0,0.05);
  padding: 0.75rem 1rem;
  transition: all 0.3s ease;
}

.title-widgets:hover {
  background-color: #eef3ff;
}

.title-widgets h3 {
  font-size: 1.25rem;
  font-weight: 600;
  color: #222;
}

.title-widgets a {
  font-weight: 500;
  color: #6c757d;
  text-decoration: none;
}

.title-widgets a:hover {
  color: #0d6efd;
}

/* ======= Tarjeta principal de noticia ======= */
.position-relative.mb-3 {
  background: #fff;
  border-radius: 0.75rem;
  overflow: hidden;
  box-shadow: 0 4px 12px rgba(0,0,0,0.08);
  transition: transform 0.35s ease, box-shadow 0.35s ease;
  border: none;
}

.position-relative.mb-3:hover {
  transform: translateY(-6px);
  box-shadow: 0 6px 18px rgba(0,0,0,0.12);
}

.position-relative.mb-3 img {
  height: 220px;
  object-fit: cover;
  border-radius: 0.75rem 0.75rem 0 0;
  transition: transform 0.5s ease;
}

.position-relative.mb-3:hover img {
  transform: scale(1.05);
}

/* ======= Contenido de noticia ======= */
.overlay {
  padding: 1.25rem;
  background-color: #fff;
}

.overlay a.h4 {
  display: block;
  font-size: 1.1rem;
  font-weight: 600;
  color: #212529;
  text-decoration: none;
  transition: color 0.3s ease;
}

.overlay a.h4:hover {
  color: #0d6efd;
}

.overlay p {
  font-size: 0.95rem;
  color: #666;
  margin-top: 0.5rem;
  line-height: 1.4;
}

.overlay .mb-2 a {
  color: #0d6efd;
  font-weight: 500;
  text-decoration: none;
}

.overlay .mb-2 a:hover {
  text-decoration: underline;
}

/* ======= Mini posts ======= */
.d-flex.mb-3 {
  background-color: #fff;
  border-radius: 0.75rem;
  overflow: hidden;
  box-shadow: 0 3px 10px rgba(0,0,0,0.06);
  transition: all 0.3s ease;
}

.d-flex.mb-3:hover {
  transform: translateY(-3px);
  box-shadow: 0 6px 15px rgba(0,0,0,0.1);
}

.d-flex.mb-3 img {
  border-radius: 0.75rem 0 0 0.75rem;
}

.d-flex.mb-3 .h6 {
  font-weight: 600;
  color: #222;
}

.d-flex.mb-3 .h6:hover {
  color: #0d6efd;
}

/* ======= Banner de publicidad ======= */
.mb-3.pb-3 img {
  border-radius: 0.75rem;
  box-shadow: 0 4px 16px rgba(0,0,0,0.08);
  transition: all 0.3s ease;
}

.mb-3.pb-3 img:hover {
  transform: scale(1.02);
  box-shadow: 0 6px 20px rgba(0,0,0,0.15);
}

/* ======= Paginación moderna ======= */
.pagination {
  margin-top: 1rem;
}

.pagination .page-item .page-link {
  border: none;
  border-radius: 0.5rem;
  margin: 0 0.2rem;
  color: #0d6efd;
  background-color: #fff;
  box-shadow: 0 2px 6px rgba(0,0,0,0.08);
  transition: all 0.3s ease;
}

.pagination .page-item .page-link:hover {
  background-color: #0d6efd;
  color: #fff;
}

.pagination .page-item.active .page-link {
  background-color: #0d6efd;
  color: #fff;
  box-shadow: 0 3px 10px rgba(13,110,253,0.3);
}

/* ======= Sidebar ======= */
.col-lg-4 {
  border-left: 1px solid #dee2e6;
}

@media (max-width: 991px) {
  .col-lg-4 {
    border-left: none;
    margin-top: 2rem;
  }
}

/* ======= Animaciones pequeñas ======= */
a, img, .pagination .page-link {
  transition: all 0.3s ease;
}
</style>


<!-- News With Sidebar End -->



