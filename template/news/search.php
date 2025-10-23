<?php
require_once __DIR__ . '/../../inc/config.php';

$q = trim($_GET['q'] ?? '');
$results = [];

if ($q !== '') {
    $stmt = $pdo->prepare("
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
$page_title       = "Resultados de buequeda para ". htmlspecialchars($q) ." | " . NOMBRE_SITIO;
$page_description = "Página de busqueda";
$page_keywords    = NOMBRE_SITIO . ", pagina de busqueda de " . NOMBRE_SITIO;
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

<!-- Search Results Start -->
<div class="container-fluid py-3">
    <div class="container-bk">
        <div class="row">
            <div class="col-lg-8">
                <div class="row">
                    <div class="col-12">
                        <div class="d-flex align-items-center justify-content-between bg-light py-2 px-4 mb-3 title-widgets">
                            <h3 class="m-0">Resultados para: "<?= htmlspecialchars($q) ?>"</h3>
                            <!--a class="text-secondary font-weight-medium text-decoration-none" href="<?= URLBASE ?>/noticias/">
                                View All
                            </a-->
                        </div>
                    </div>

                    <?php if ($results): ?>
                        <?php foreach ($results as $p): ?>
                            <div class="col-lg-6">
                                <div class="position-relative mb-3">
                                    <img class="img-fluid w-100"
                                         src="<?= $p['image'] ? URLBASE . '/' . htmlspecialchars($p['image']) : URLBASE . '/template/news/img/news-500x280-1.jpg' ?>"
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
                            <p>No se encontraron resultados para "<strong><?= htmlspecialchars($q) ?></strong>".</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Sidebar -->
            <?php include __DIR__ . '/partials/sidebar.php'; ?>
        </div>
    </div>
</div>
<!-- Search Results End -->



