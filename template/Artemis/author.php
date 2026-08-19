<?php
require_once __DIR__ . '/../../inc/config.php';

if (!function_exists('truncate_text')) {
    function truncate_text(string $text, int $limit = 100): string {
        $text = strip_tags($text);
        return (mb_strlen($text) > $limit) ? mb_substr($text, 0, $limit) . '...' : $text;
    }
}

if (!function_exists('img_url')) {
    function img_url(?string $path): string {
        if (empty($path)) return URLBASE . '/template/Artemis/img/placeholder.jpg';
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
?>

<section class="py-5" style="background: var(--dark); min-height: 60vh;">
    <div class="container">
        <!-- Author Profile Header -->
        <div class="row mb-5">
            <div class="col-12">
                <div class="p-4" style="background: rgba(255,255,255,0.05); border-radius: 16px;">
                    <div class="d-flex align-items-center">
                        <?php
                        $fotoAutor = !empty($authorData['foto_perfil'])
                            ? img_url($authorData['foto_perfil'])
                            : 'data:image/svg+xml;base64,' . base64_encode('
                            <svg width="100" height="100" xmlns="http://www.w3.org/2000/svg">
                                <rect width="100" height="100" fill="#e63946"/>
                                <text x="50%" y="50%" font-size="40" fill="white" text-anchor="middle" dy=".35em" font-family="Arial">
                                    ' . strtoupper(substr($authorData['nombre'], 0, 1) . substr($authorData['apellido'], 0, 1)) . '
                                </text>
                            </svg>');
                        ?>
                        <img src="<?= htmlspecialchars($fotoAutor, ENT_QUOTES, 'UTF-8') ?>"
                             alt="<?= htmlspecialchars($authorFullName) ?>"
                             class="mr-4"
                             style="width: 100px; height: 100px; border-radius: 50%; object-fit: cover;">
                        <div>
                            <h1 style="color: var(--text-color); margin: 0 0 8px 0;"><?= htmlspecialchars($authorFullName) ?></h1>
                            <span style="color: var(--primary); font-size: 14px;"><?= t_theme('theme_autor') ?></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Posts Section -->
        <div class="row mb-4">
            <div class="col-12">
                <h2 style="color: var(--text-color);"><?= $totalPosts ?> <?= $totalPosts === 1 ? t_theme('theme_articulo_publicado') : t_theme('theme_articulos_publicados') ?></h2>
            </div>
        </div>

        <div class="row">
            <?php if ($posts): ?>
                <?php foreach ($posts as $p):
                    $postUrl = URLBASE . "/" . htmlspecialchars($p['category_slug']) . "/" . htmlspecialchars($p['slug']) . "/";
                ?>
                <div class="col-lg-3 col-md-6 mb-4">
                    <div class="news-card">
                        <div class="position-relative" style="overflow: hidden;">
                            <a href="<?= $postUrl ?>">
                                <img src="<?= img_url($p['image']) ?>"
                                     alt="<?= htmlspecialchars(get_image_alt($p['image'], $p['title'])) ?>"
                                     class="card-img"
                                     style="width: 100%; height: 180px; object-fit: cover;">
                            </a>
                            <span class="category-badge position-absolute" style="top: 12px; left: 12px; font-size: 10px;">
                                <?= htmlspecialchars($p['category_name'] ?? 'Sin categoría') ?>
                            </span>
                        </div>
                        <div class="p-3">
                            <h5 style="color: var(--text-color); font-size: 16px; font-weight: 600; margin-bottom: 8px; line-height: 1.4;">
                                <a href="<?= $postUrl ?>" style="color: inherit; text-decoration: none;">
                                    <?= htmlspecialchars($p['title']) ?>
                                </a>
                            </h5>
                            <p style="color: var(--text-muted); font-size: 13px; margin-bottom: 8px;">
                                <?= truncate_text($p['seo_description'] ?: $p['content'], 80) ?>
                            </p>
                            <span style="color: var(--text-muted); font-size: 12px;">
                                <i class="far fa-calendar mr-1"></i>
                                <?= date('d M, Y', strtotime($p['created_at'])) ?>
                            </span>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="col-12 text-center py-5">
                    <i class="fas fa-newspaper" style="font-size: 60px; color: var(--text-muted); opacity: 0.3;"></i>
                    <h3 style="color: var(--text-color); margin-top: 20px;"><?= t_theme('theme_no_hay_noticias_disponibles') ?></h3>
                    <p style="color: var(--text-muted);"><?= t_theme('theme_pronto_tendremos') ?></p>
                </div>
            <?php endif; ?>
        </div>

        <!-- Pagination -->
        <?php if ($totalPages > 1):
            $pageBase = URLBASE . '/autor/' . $authorSlug . '/page/';
            $show = array_unique(array_filter(array_merge(
                [1, $totalPages],
                range(max(1, $pageNum - 2), min($totalPages, $pageNum + 2))
            )));
            sort($show);
            $btnBase  = 'display:inline-flex;align-items:center;justify-content:center;min-width:38px;height:38px;padding:0 10px;border-radius:8px;text-decoration:none;font-size:.9rem;transition:opacity .15s;';
            $btnNorm  = $btnBase . 'background:var(--dark-secondary);color:var(--text-color);border:1px solid var(--border-color);';
            $btnActiv = $btnBase . 'background:var(--primary-color);color:#fff;border:1px solid var(--primary-color);font-weight:700;';
            $btnNav   = $btnBase . 'background:var(--dark-secondary);color:var(--text-color);border:1px solid var(--border-color);';
            $btnDis   = $btnBase . 'background:var(--dark-secondary);color:var(--text-muted);border:1px solid var(--border-color);opacity:.45;pointer-events:none;';
        ?>
        <div class="row mt-4">
            <div class="col-12 text-center">
                <nav aria-label="Paginación">
                    <ul style="display:flex;gap:6px;list-style:none;padding:0;margin:0;justify-content:center;flex-wrap:wrap;">
                        <li><?php if ($pageNum > 1): ?>
                            <a href="<?= $pageBase . ($pageNum - 1) ?>/" style="<?= $btnNav ?>" aria-label="Anterior"><i class="fas fa-chevron-left"></i></a>
                            <?php else: ?><span style="<?= $btnDis ?>"><i class="fas fa-chevron-left"></i></span><?php endif; ?></li>
                        <?php $prev = 0; foreach ($show as $i):
                            if ($prev && $i - $prev > 1): ?><li><span style="<?= $btnDis ?>">…</span></li><?php endif; ?>
                            <li><a href="<?= $pageBase . $i ?>/" style="<?= $i == $pageNum ? $btnActiv : $btnNorm ?>" <?= $i == $pageNum ? 'aria-current="page"' : '' ?>><?= $i ?></a></li>
                        <?php $prev = $i; endforeach; ?>
                        <li><?php if ($pageNum < $totalPages): ?>
                            <a href="<?= $pageBase . ($pageNum + 1) ?>/" style="<?= $btnNav ?>" aria-label="Siguiente"><i class="fas fa-chevron-right"></i></a>
                            <?php else: ?><span style="<?= $btnDis ?>"><i class="fas fa-chevron-right"></i></span><?php endif; ?></li>
                    </ul>
                </nav>
                <p style="color:var(--text-muted);margin-top:12px;font-size:.85rem;">Página <?= $pageNum ?> de <?= $totalPages ?></p>
            </div>
        </div>
        <?php endif; ?>
    </div>
</section>
