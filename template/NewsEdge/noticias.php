<?php
require_once __DIR__ . '/../../inc/config.php';

$categorySlug = $_GET['slug'] ?? null;
$pageNum = isset($_GET['page_num']) ? max(1, (int)$_GET['page_num']) : 1;

$perPage = 8;
$offset  = ($pageNum - 1) * $perPage;

// =======================
// CATEGORÍA
// =======================
if ($categorySlug) {

    $stmtCat = db()->prepare("
        SELECT id, name, slug
        FROM blog_categories
        WHERE slug=? AND status='active' AND deleted=0
        LIMIT 1
    ");
    $stmtCat->execute([$categorySlug]);
    $category = $stmtCat->fetch();

    if (!$category) {
        http_response_code(404);
        include __DIR__ . '/../404.php';
        return;
    }

    // Total posts
    $stmtCount = db()->prepare("
        SELECT COUNT(*)
        FROM blog_posts p
        INNER JOIN blog_post_category pc ON pc.post_id = p.id
        WHERE pc.category_id=? AND p.status='published' AND p.deleted=0
    ");
    $stmtCount->execute([$category['id']]);
    $totalPosts = $stmtCount->fetchColumn();

    // Posts
    $stmt = db()->prepare("
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
    $stmtCount = db()->query("
        SELECT COUNT(*)
        FROM blog_posts
        WHERE status='published' AND deleted=0
    ");
    $totalPosts = $stmtCount->fetchColumn();

    $stmt = db()->query("
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

$totalPages = max(1, ceil($totalPosts / $perPage));
?>

<section class="bg-body section-space-less30">
    <div class="container">
        <div class="row">

            <!-- CONTENIDO -->
            <div class="col-lg-8 col-md-12">
                <div class="row">

                    <?php foreach ($posts as $p): ?>
                    <div class="col-sm-6 col-12">
                        <div class="position-relative mb-30">

                            <a class="img-opacity-hover news-thumb"
   href="<?= URLBASE ?>/<?= $p['category_slug'] ?>/<?= $p['slug'] ?>/">
    <img src="<?= URLBASE . '/' . ltrim($p['image'], '/') ?>"
         alt="<?= htmlspecialchars($p['title']) ?>">
</a>

                            <div class="topic-box-top-xs">
                                <div class="topic-box-sm color-cod-gray mb-20">
                                    <?= htmlspecialchars($p['category_name']) ?>
                                </div>
                            </div>

                            <div class="post-date-dark">
                                <ul>
                                    <li>
                                        <span><i class="fa fa-calendar"></i></span>
                                        <?= fecha_espanol(date('F d, Y', strtotime($p['created_at']))) ?>
                                    </li>
                                </ul>
                            </div>

                            <h3 class="title-medium-dark size-lg mb-none">
                                <a href="<?= URLBASE ?>/<?= $p['category_slug'] ?>/<?= $p['slug'] ?>/">
                                    <?= htmlspecialchars($p['title']) ?>
                                </a>
                            </h3>

                        </div>
                    </div>
                    <?php endforeach; ?>

                </div>

                <!-- PAGINACIÓN -->
                <?php if ($totalPages > 1): ?>
                <div class="row mt-20-r mb-30">

                    <div class="col-sm-6 col-12">
                        <div class="pagination-btn-wrapper text-center--xs mb15--xs">
                            <ul>
                                <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                                <li class="<?= $i == $pageNum ? 'active' : '' ?>">
                                    <a href="<?= URLBASE ?>/noticias<?= $categorySlug ? '/' . $categorySlug : '' ?>/page/<?= $i ?>/">
                                        <?= $i ?>
                                    </a>
                                </li>
                                <?php endfor; ?>
                            </ul>
                        </div>
                    </div>

                    <div class="col-sm-6 col-12">
                        <div class="pagination-result text-right pt-10 text-center--xs">
                            <p class="mb-none">
                                Página <?= $pageNum ?> de <?= $totalPages ?>
                            </p>
                        </div>
                    </div>

                </div>
                <?php endif; ?>

            </div>

            <!-- SIDEBAR -->
            <div class="col-lg-4 col-md-12">
                <div class="ne-sidebar sidebar-break-lg">
                    <?php include __DIR__ . '/partials/sidebar.php'; ?>
                </div>
            </div>

        </div>
    </div>
</section>

            