<?php
require_once __DIR__ . '/../../inc/config.php';

if (!function_exists('img_url')) {
    function img_url(?string $path): string {
        if (empty($path)) return URLBASE . '/template/Artemis/img/placeholder.jpg';
        if (preg_match('#^https?://#i', $path)) return $path;
        return URLBASE . '/' . ltrim($path, '/');
    }
}

$categorySlug = $_GET['slug'] ?? null;
$pageNum = isset($_GET['page_num']) ? max(1, (int)$_GET['page_num']) : 1;

$perPage = 8;
$offset  = ($pageNum - 1) * $perPage;

$posts = [];
$totalPosts = 0;
$category = ['name' => t_theme('theme_noticias'), 'slug' => 'noticias'];

try {
    if ($categorySlug) {
        $stmtCat = db()->prepare("SELECT id, name, slug FROM blog_categories WHERE slug=? AND status='active' AND deleted=0 LIMIT 1");
        $stmtCat->execute([$categorySlug]);
        $category = $stmtCat->fetch();

        if (!$category) {
            http_response_code(404);
            include __DIR__ . '/404.php';
            return;
        }

        $stmtCount = db()->prepare("SELECT COUNT(*) FROM blog_posts p INNER JOIN blog_post_category pc ON pc.post_id = p.id WHERE pc.category_id=? AND p.status='published' AND p.deleted=0");
        $stmtCount->execute([$category['id']]);
        $totalPosts = $stmtCount->fetchColumn();

        $stmt = db()->prepare("SELECT DISTINCT p.* FROM blog_posts p INNER JOIN blog_post_category pc ON pc.post_id = p.id WHERE pc.category_id=? AND p.status='published' AND p.deleted=0 ORDER BY p.created_at DESC LIMIT $perPage OFFSET $offset");
        $stmt->execute([$category['id']]);
        $posts = $stmt->fetchAll();
        foreach ($posts as &$p) {
            $p['category_name'] = $category['name'];
            $p['category_slug'] = $category['slug'];
        }
        unset($p);
    } else {
        $stmtCount = db()->query("SELECT COUNT(*) FROM blog_posts WHERE status='published' AND deleted=0");
        $totalPosts = $stmtCount->fetchColumn();

        $stmt = db()->query("
            SELECT p.*, c.name AS category_name, c.slug AS category_slug
            FROM blog_posts p
            LEFT JOIN (SELECT post_id, MIN(category_id) AS category_id FROM blog_post_category GROUP BY post_id) fc ON fc.post_id = p.id
            LEFT JOIN blog_categories c ON c.id = fc.category_id
            WHERE p.status='published' AND p.deleted=0
            ORDER BY p.created_at DESC
            LIMIT $perPage OFFSET $offset
        ");
        $posts = $stmt->fetchAll();
    }
} catch (Throwable $e) {
    $posts = [];
    $totalPosts = 0;
}

$totalPages = max(1, ceil($totalPosts / $perPage));

$page_title = $categorySlug ? $category['name'] . " | " . NOMBRE_SITIO : "Noticias | " . NOMBRE_SITIO;
?>

<section class="py-5" style="background: var(--dark); min-height: 60vh;">
    <div class="container">
        <div class="row mb-4">
            <div class="col-12">
                <h1 class="section-title" style="color: var(--text-color);"><?= htmlspecialchars($category['name']) ?></h1>
                <p style="color: var(--text-muted); margin-top: 10px;"><?= $totalPosts ?> <?= $totalPosts === 1 ? t_theme('theme_articulo') : t_theme('theme_articulos') ?></p>
            </div>
        </div>
        
        <div class="row">
            <?php if (empty($posts)): ?>
            <div class="col-12 text-center py-5">
                <i class="fas fa-newspaper" style="font-size: 60px; color: var(--text-muted); opacity: 0.3;"></i>
                <h3 style="color: var(--text-color); margin-top: 20px;"><?= t_theme('theme_no_hay_noticias_disponibles') ?></h3>
                <p style="color: var(--text-muted);"><?= t_theme('theme_pronto_tendremos') ?></p>
                <a href="<?= URLBASE ?>" class="btn-artemis mt-3"><?= t_theme('theme_volver_inicio') ?></a>
            </div>
            <?php else: ?>
            <?php foreach ($posts as $p): 
                $postUrl = URLBASE . "/" . $p['category_slug'] . "/" . $p['slug'] . "/";
            ?>
            <div class="col-lg-4 col-md-6 mb-4">
                <div class="news-card">
                    <div class="position-relative" style="overflow: hidden;">
                        <a href="<?= $postUrl ?>">
                            <img src="<?= img_url($p['image']) ?>" 
                                 alt="<?= htmlspecialchars(get_image_alt($p['image'], $p['title'])) ?>" 
                                 class="card-img"
                                 style="width: 100%; height: 200px; object-fit: cover;">
                        </a>
                        <span class="category-badge position-absolute" style="top: 12px; left: 12px;">
                            <?= htmlspecialchars($p['category_name']) ?>
                        </span>
                    </div>
                    <div class="p-3">
                        <h4 style="color: var(--text-color); font-size: 16px; font-weight: 600; margin-bottom: 10px; line-height: 1.4;">
                            <a href="<?= $postUrl ?>" style="color: inherit; text-decoration: none;">
                                <?= htmlspecialchars($p['title']) ?>
                            </a>
                        </h4>
                        <div style="color: var(--text-muted); font-size: 13px;">
                            <i class="far fa-calendar mr-2"></i>
                            <?= date('d M, Y', strtotime($p['created_at'])) ?>
                        </div>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
            <?php endif; ?>
        </div>

        <?php if ($totalPages > 1):
            $pageBase = URLBASE . '/noticias' . ($categorySlug ? '/' . $categorySlug : '') . '/page/';

            // páginas a mostrar: siempre 1, últma, y ±2 alrededor de la actual
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

                        <!-- Anterior -->
                        <li>
                            <?php if ($pageNum > 1): ?>
                            <a href="<?= $pageBase . ($pageNum - 1) ?>/" style="<?= $btnNav ?>" aria-label="Anterior">
                                <i class="fas fa-chevron-left"></i>
                            </a>
                            <?php else: ?>
                            <span style="<?= $btnDis ?>"><i class="fas fa-chevron-left"></i></span>
                            <?php endif; ?>
                        </li>

                        <!-- Números con elipsis -->
                        <?php $prev = 0; foreach ($show as $i):
                            if ($prev && $i - $prev > 1): ?>
                            <li><span style="<?= $btnDis ?>">…</span></li>
                            <?php endif; ?>
                            <li>
                                <a href="<?= $pageBase . $i ?>/"
                                   style="<?= $i == $pageNum ? $btnActiv : $btnNorm ?>"
                                   <?= $i == $pageNum ? 'aria-current="page"' : '' ?>>
                                    <?= $i ?>
                                </a>
                            </li>
                        <?php $prev = $i; endforeach; ?>

                        <!-- Siguiente -->
                        <li>
                            <?php if ($pageNum < $totalPages): ?>
                            <a href="<?= $pageBase . ($pageNum + 1) ?>/" style="<?= $btnNav ?>" aria-label="Siguiente">
                                <i class="fas fa-chevron-right"></i>
                            </a>
                            <?php else: ?>
                            <span style="<?= $btnDis ?>"><i class="fas fa-chevron-right"></i></span>
                            <?php endif; ?>
                        </li>

                    </ul>
                </nav>
                <p style="color:var(--text-muted);margin-top:12px;font-size:.85rem;">Página <?= $pageNum ?> de <?= $totalPages ?></p>
            </div>
        </div>
        <?php endif; ?>
    </div>
</section>