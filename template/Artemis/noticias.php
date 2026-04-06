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
    $category = ['name' => 'Noticias', 'slug' => 'noticias'];
}

$totalPages = max(1, ceil($totalPosts / $perPage));

$page_title = $categorySlug ? $category['name'] . " | " . NOMBRE_SITIO : "Noticias | " . NOMBRE_SITIO;
?>

<section class="py-5" style="background: var(--dark); min-height: 60vh;">
    <div class="container">
        <div class="row mb-4">
            <div class="col-12">
                <h1 class="section-title" style="color: var(--text-color);"><?= htmlspecialchars($category['name']) ?></h1>
                <p style="color: var(--text-muted); margin-top: 10px;"><?= $totalPosts ?> <?= $totalPosts === 1 ? 'artículo' : 'artículos' ?></p>
            </div>
        </div>
        
        <div class="row">
            <?php if (empty($posts)): ?>
            <div class="col-12 text-center py-5">
                <i class="fas fa-newspaper" style="font-size: 60px; color: var(--text-muted); opacity: 0.3;"></i>
                <h3 style="color: var(--text-color); margin-top: 20px;">No hay noticias disponibles</h3>
                <p style="color: var(--text-muted);">Pronto tendremos nuevo contenido para ti.</p>
                <a href="<?= URLBASE ?>" class="btn-artemis mt-3">Volver al Inicio</a>
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
                                 alt="<?= htmlspecialchars($p['title']) ?>" 
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
        ?>
        <div class="row mt-4">
            <div class="col-12 text-center">
                <nav>
                    <ul class="pagination justify-content-center" style="display: flex; gap: 8px; list-style: none; padding: 0;">
                        <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                        <li>
                            <a href="<?= $pageBase . $i ?>/" 
                               class="<?= $i == $pageNum ? 'btn-artemis' : 'btn' ?>"
                               style="<?= $i == $pageNum ? '' : 'background: var(--dark-secondary); color: var(--text-color); border: 1px solid var(--border-color);' ?> padding: 10px 16px; border-radius: 8px; text-decoration: none; display: inline-block;">
                                <?= $i ?>
                            </a>
                        </li>
                        <?php endfor; ?>
                    </ul>
                </nav>
                <p style="color: var(--text-muted); margin-top: 15px;">Página <?= $pageNum ?> de <?= $totalPages ?></p>
            </div>
        </div>
        <?php endif; ?>
    </div>
</section>