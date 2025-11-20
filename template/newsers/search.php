<?php
require_once __DIR__ . '/../../inc/config.php';

$q = trim($_GET['q'] ?? '');
$results = [];

if ($q !== '') {
    $stmt = db()->prepare("
        SELECT p.*, c.name AS category_name, c.slug AS category_slug
        FROM blog_posts p
        INNER JOIN blog_post_category pc ON pc.post_id = p.id
        INNER JOIN blog_categories c ON c.id = pc.category_id
        WHERE (p.title LIKE :q OR p.content LIKE :q)
          AND p.status='published' AND p.deleted=0
        ORDER BY p.created_at DESC
    ");
    $stmt->execute([':q' => "%$q%"]);
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
}
?>

<?php
// =======================
// Variables SEO dinámicas
// =======================
$page_title       = "Resultados de búsqueda para “" . htmlspecialchars($q) . "” | " . NOMBRE_SITIO;
$page_description = "Explora las noticias, artículos y contenidos de " . NOMBRE_SITIO . " relacionados con '" . htmlspecialchars($q) . "'.";
$page_keywords    = NOMBRE_SITIO . ", búsqueda, noticias, información, " . htmlspecialchars($q);
$page_author      = NOMBRE_SITIO;

$page_image = rtrim(URLBASE, '/') . FAVICON;
if (!empty($images)) {
    $path = $images[0]['path'];
    $path = ($path[0] === '/') ? $path : '/' . $path;
    $page_image = rtrim(URLBASE, '/') . $path;
}

$currentPath    = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$page_canonical = rtrim(URLBASE, '/') . '/' . ltrim($currentPath, '/');
?>

<!-- Search Results Start -->
<div class="container-fluid py-5">
  <div class="container py-5">
    <div class="row">
      <!-- Columna principal -->
      <div class="col-lg-8">
        <div class="row">
          <div class="col-12">
            <div class="d-flex align-items-center justify-content-between bg-light py-2 px-4 mb-4 title-widgets rounded shadow-sm">
              <h3 class="m-0">
                <i class="fa fa-search text-primary me-2"></i> Resultados para:
                <span class="text-dark">"<?= htmlspecialchars($q) ?>"</span>
              </h3>
              <a class="text-secondary text-decoration-none small fw-semibold" href="<?= URLBASE ?>/noticias/">
                <i class="fa fa-newspaper me-1"></i> Ver todas las noticias
              </a>
            </div>
          </div>
        </div>

        <div class="row">
          <?php if ($results): ?>
            <?php foreach ($results as $p): ?>
              <div class="col-md-6 mb-4">
                <div class="position-relative bg-white border rounded overflow-hidden shadow-sm h-100">
                  <div class="overflow-hidden">
                    <a href="<?= URLBASE ?>/<?= htmlspecialchars($p['category_slug']) ?>/<?= htmlspecialchars($p['slug']) ?>/">
                      <img class="img-fluid w-100"
                        src="<?= $p['image'] ? URLBASE . '/' . htmlspecialchars($p['image']) : URLBASE . '/template/news/img/news-500x280-1.jpg' ?>"
                        alt="<?= htmlspecialchars($p['title']) ?>"
                        style="height: 220px; object-fit: cover; transition: transform .3s;">
                    </a>
                  </div>
                  <div class="p-3">
                    <div class="d-flex justify-content-between small text-muted mb-2">
                      <span>
                        <i class="fa fa-folder-open text-primary me-1"></i>
                        <a href="<?= URLBASE ?>/noticias/<?= htmlspecialchars($p['category_slug']) ?>/" class="text-decoration-none text-secondary">
                          <?= htmlspecialchars($p['category_name']) ?>
                        </a>
                      </span>
                      <span>
                        <i class="fa fa-calendar-alt text-primary me-1"></i>
                        <?= fecha_espanol(date("F d, Y", strtotime($p['created_at']))) ?>
                      </span>
                    </div>

                    <a class="h5 d-block mb-2 text-dark fw-semibold link-hover"
                       href="<?= URLBASE ?>/<?= htmlspecialchars($p['category_slug']) ?>/<?= htmlspecialchars($p['slug']) ?>/">
                      <?= htmlspecialchars($p['title']) ?>
                    </a>

                    <p class="text-muted small m-0">
                      <?= htmlspecialchars(substr(strip_tags($p['seo_description'] ?: $p['content']), 0, 140)) ?>...
                    </p>
                  </div>
                </div>
              </div>
            <?php endforeach; ?>
          <?php else: ?>
            <div class="col-12 text-center py-5">
              <i class="fa fa-search fa-3x text-muted mb-3"></i>
              <h4>No se encontraron resultados</h4>
              <p class="text-muted">No hay publicaciones que coincidan con "<strong><?= htmlspecialchars($q) ?></strong>".</p>
              <a href="<?= URLBASE ?>/noticias/" class="btn btn-primary mt-2">
                <i class="fa fa-arrow-left me-2"></i> Volver a Noticias
              </a>
            </div>
          <?php endif; ?>
        </div>

        <!-- Banner Publicitario -->
        <div class="mt-4 mb-5 text-center">
          <?php
          $stmt = db()->prepare("SELECT * FROM ads WHERE position = 2 AND status='active' LIMIT 1");
          $stmt->execute();
          $ad = $stmt->fetch(PDO::FETCH_ASSOC);
          if ($ad && !empty($ad['image_url'])):
          ?>
            <?php if (!empty($ad['target_url'])): ?>
              <a href="<?= htmlspecialchars($ad['target_url']) ?>" target="_blank" rel="noopener">
                <img class="img-fluid rounded shadow-sm"
                     src="<?= URLBASE . htmlspecialchars($ad['image_url']) ?>"
                     alt="<?= htmlspecialchars($ad['title'] ?? 'Publicidad') ?>">
              </a>
            <?php else: ?>
              <img class="img-fluid rounded shadow-sm"
                   src="<?= URLBASE . htmlspecialchars($ad['image_url']) ?>"
                   alt="<?= htmlspecialchars($ad['title'] ?? 'Publicidad') ?>">
            <?php endif; ?>
          <?php endif; ?>
        </div>

        <!-- Mini grid destacadas -->
        <?php if ($results): ?>
        <div class="row">
          <?php foreach (array_slice($results, 0, 4) as $p): ?>
            <div class="col-md-6 mb-3">
              <div class="d-flex bg-light rounded overflow-hidden shadow-sm">
                <img src="<?= URLBASE . '/' . htmlspecialchars($p['image']) ?>"
                     style="width: 100px; height: 100px; object-fit: cover;">
                <div class="px-3 py-2">
                  <div class="small text-muted mb-1">
                    <?= fecha_espanol(date("F d, Y", strtotime($p['created_at']))) ?>
                  </div>
                  <a class="fw-semibold text-dark link-hover"
                     href="<?= URLBASE ?>/<?= htmlspecialchars($p['category_slug']) ?>/<?= htmlspecialchars($p['slug']) ?>/">
                    <?= htmlspecialchars($p['title']) ?>
                  </a>
                </div>
              </div>
            </div>
          <?php endforeach; ?>
        </div>
        <?php endif; ?>
      </div>

      <!-- Sidebar -->
      <div class="col-lg-4">
        <?php include __DIR__ . '/partials/sidebar.php'; ?>
      </div>
    </div>
  </div>
</div>
<!-- Search Results End -->




