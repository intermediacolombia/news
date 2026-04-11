<?php
require_once __DIR__ . '/../../inc/config.php';
require_once __DIR__ . '/../../inc/translations.php';

if (!function_exists('truncate_text')) {
    function truncate_text(string $text, int $limit = 100): string {
        $text = strip_tags($text);
        return (mb_strlen($text) > $limit) ? mb_substr($text, 0, $limit) . '...' : $text;
    }
}

if (!function_exists('img_url')) {
    function img_url(?string $path): string {
        if (empty($path)) return URLBASE . '/template/newsers/img/placeholder.jpg';
        if (preg_match('#^https?://#i', $path)) return $path;
        return URLBASE . '/' . ltrim($path, '/');
    }
}

$authorSlug = trim($_GET['author_slug'] ?? '');
$authorData = null;
$posts = [];
$totalPosts = 0;

if (!empty($authorSlug)) {
    // Convertir slug a nombre (reemplazar guiones por espacios)
    $authorName = str_replace('-', ' ', $authorSlug);
    
    // Buscar autor por nombre completo o username
    $stmtAuthor = db()->prepare("
        SELECT u.id, u.nombre, u.apellido, u.username, u.foto_perfil
        FROM usuarios u
        WHERE (CONCAT(u.nombre, ' ', u.apellido) = ? OR u.username = ?)
        AND u.borrado = 0
        LIMIT 1
    ");
    $stmtAuthor->execute([$authorName, $authorSlug]);
    $authorData = $stmtAuthor->fetch();
    
    if ($authorData) {
        $page_num = isset($_GET['page_num']) ? max(1, (int)$_GET['page_num']) : 1;
        $perPage = 8;
        $offset = ($page_num - 1) * $perPage;
        
        // Obtener posts del autor
        $stmtCount = db()->prepare("
            SELECT COUNT(*) FROM blog_posts p
            WHERE (p.author = ? OR p.author_user = ?)
            AND p.status = 'published' AND p.deleted = 0
        ");
        $stmtCount->execute([$authorData['nombre'] . ' ' . $authorData['apellido'], $authorData['username']]);
        $totalPosts = $stmtCount->fetchColumn();
        $totalPages = max(1, ceil($totalPosts / $perPage));
        
        $stmtPosts = db()->prepare("
            SELECT p.*, c.name AS category_name, c.slug AS category_slug
            FROM blog_posts p
            LEFT JOIN (SELECT post_id, MIN(category_id) AS category_id FROM blog_post_category GROUP BY post_id) fc ON fc.post_id = p.id
            LEFT JOIN blog_categories c ON c.id = fc.category_id
            WHERE (p.author = ? OR p.author_user = ?)
            AND p.status = 'published' AND p.deleted = 0
            ORDER BY p.created_at DESC
            LIMIT $perPage OFFSET $offset
        ");
        $stmtPosts->execute([$authorData['nombre'] . ' ' . $authorData['apellido'], $authorData['username']]);
        $posts = $stmtPosts->fetchAll(PDO::FETCH_ASSOC);
    }
}

if (!$authorData) {
    http_response_code(404);
    include __DIR__ . '/404.php';
    return;
}

$authorFullName = trim($authorData['nombre'] . ' ' . $authorData['apellido']);
$authorUrl = URLBASE . '/autor/' . $authorSlug . '/';

$page_title = "$authorFullName | " . NOMBRE_SITIO;
$page_description = "Artículos y publicaciones de $authorFullName en " . NOMBRE_SITIO;
$page_author = NOMBRE_SITIO;

// Imagen SEO
$page_image = rtrim(URLBASE, '/') . FAVICON;
if (!empty($authorData['foto_perfil'])) {
    $path = $authorData['foto_perfil'];
    $path = ($path[0] === '/') ? $path : '/' . $path;
    $page_image = rtrim(URLBASE, '/') . $path;
}

// Canonical automático
$currentPath = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$page_canonical = rtrim(URLBASE, '/') . '/' . ltrim($currentPath, '/');
?>

<!-- Author Profile Start -->
<div class="container-fluid py-5">
  <div class="container py-5">
    <div class="row">
      <!-- Columna principal -->
      <div class="col-lg-8">
        <!-- Author Header -->
        <div class="row mb-4">
          <div class="col-12">
            <div class="bg-light p-4 border rounded shadow-sm">
              <div class="d-flex align-items-center">
                <?php
                $fotoAutor = !empty($authorData['foto_perfil'])
                    ? img_url($authorData['foto_perfil'])
                    : URLBASE . '/template/newsers/img/placeholder-author.jpg';
                ?>
                <img src="<?= htmlspecialchars($fotoAutor, ENT_QUOTES, 'UTF-8') ?>"
                     alt="<?= htmlspecialchars($authorFullName) ?>"
                     class="img-fluid rounded-circle mr-4"
                     style="width: 100px; height: 100px; object-fit: cover;">
                <div>
                  <h1 class="mb-2"><?= htmlspecialchars($authorFullName) ?></h1>
                  <p class="text-secondary mb-2"><?= t_theme('theme_autor') ?></p>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Posts Count -->
        <div class="row mb-4">
          <div class="col-12">
            <div class="d-flex align-items-center justify-content-between bg-light py-2 px-4 mb-4 title-widgets rounded shadow-sm">
              <h3 class="m-0">
                <i class="fa fa-newspaper text-primary me-2"></i> 
                <?= $totalPosts ?> <?= $totalPosts === 1 ? t_theme('theme_articulo_publicado') : t_theme('theme_articulos_publicados') ?>
              </h3>
            </div>
          </div>
        </div>

        <!-- Posts Grid -->
        <div class="row">
          <?php if ($posts): ?>
            <?php foreach ($posts as $p): 
                $postUrl = URLBASE . "/" . htmlspecialchars($p['category_slug']) . "/" . htmlspecialchars($p['slug']) . "/";
            ?>
              <div class="col-md-6 mb-4">
                <div class="position-relative bg-white border rounded overflow-hidden shadow-sm h-100">
                  <div class="overflow-hidden">
                    <a href="<?= $postUrl ?>">
                      <img class="img-fluid w-100"
                        src="<?= img_url($p['image']) ?>"
                        alt="<?= htmlspecialchars(get_image_alt($p['image'], $p['title'])) ?>"
                        style="height: 220px; object-fit: cover; transition: transform .3s;">
                    </a>
                  </div>
                  <div class="p-3">
                    <div class="d-flex justify-content-between small text-muted mb-2">
                      <span>
                        <i class="fa fa-folder-open text-primary me-1"></i>
                        <a href="<?= URLBASE ?>/noticias/<?= htmlspecialchars($p['category_slug']) ?>/" class="text-decoration-none text-secondary">
                          <?= htmlspecialchars($p['category_name'] ?? 'Sin categoría') ?>
                        </a>
                      </span>
                      <span>
                        <i class="fa fa-calendar-alt text-primary me-1"></i>
                        <?= fecha_espanol(date("F d, Y", strtotime($p['created_at']))) ?>
                      </span>
                    </div>

                    <a class="h5 d-block mb-2 text-dark fw-semibold link-hover"
                       href="<?= $postUrl ?>">
                      <?= htmlspecialchars($p['title']) ?>
                    </a>

                    <p class="text-muted small m-0">
                      <?= htmlspecialchars(truncate_text($p['seo_description'] ?: $p['content'], 140)) ?>
                    </p>
                  </div>
                </div>
              </div>
            <?php endforeach; ?>
          <?php else: ?>
            <div class="col-12">
              <div class="bg-light p-5 text-center rounded">
                <i class="fa fa-newspaper fa-3x text-muted mb-3"></i>
                <h4><?= t_theme('theme_no_hay_noticias_disponibles') ?></h4>
                <p class="text-muted"><?= t_theme('theme_pronto_tendremos') ?></p>
              </div>
            </div>
          <?php endif; ?>
        </div>

        <!-- Pagination -->
        <?php if ($totalPages > 1):
            $pageBase = URLBASE . '/autor/' . $authorSlug . '/page/';
        ?>
        <div class="row">
          <div class="col-12">
            <nav>
              <ul class="pagination justify-content-center">
                <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                <li class="page-item <?= $i == $page_num ? 'active' : '' ?>">
                  <a class="page-link" href="<?= $pageBase . $i ?>/"><?= $i ?></a>
                </li>
                <?php endfor; ?>
              </ul>
            </nav>
            <p class="text-center text-muted mt-3">Página <?= $page_num ?> de <?= $totalPages ?></p>
          </div>
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
<!-- Author Profile End -->
