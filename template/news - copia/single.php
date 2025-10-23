<?php
require_once __DIR__ . '/../../inc/config.php';

$categorySlug = $_GET['category'] ?? null;
$postSlug     = $_GET['post'] ?? null;

if (!$categorySlug || !$postSlug) {
    http_response_code(404);
    include __DIR__ . '/../404.php';
    exit;
}

$stmt = $pdo->prepare("
    SELECT p.*, pc.category_id, c.name AS category_name, c.slug AS category_slug
    FROM blog_posts p
    INNER JOIN blog_post_category pc ON pc.post_id = p.id
    INNER JOIN blog_categories c ON c.id = pc.category_id
    WHERE p.slug = ? AND c.slug = ?
      AND p.status='published' AND p.deleted=0
    LIMIT 1
");

$stmt->execute([$postSlug, $categorySlug]);
$post = $stmt->fetch();

if (!$post) {
    http_response_code(404);
    include __DIR__ . '/../404.php';
    exit;
}

/* ================================
   REGISTRO DE VISTAS POR IP
   ================================ */
$ipAddress = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';

// Verificar si ya existe un registro de esta IP para este post
$stmtView = $pdo->prepare("
    SELECT 1 
    FROM blog_post_views 
    WHERE post_id = ? AND ip_address = ?
    LIMIT 1
");
$stmtView->execute([$post['id'], $ipAddress]);

if (!$stmtView->fetch()) {
    // Registrar la vista en la tabla auxiliar
    $stmtInsert = $pdo->prepare("
        INSERT INTO blog_post_views (post_id, ip_address) 
        VALUES (?, ?)
    ");
    $stmtInsert->execute([$post['id'], $ipAddress]);
}

/* ================================
   OBTENER TOTAL DE LECTURAS
   ================================ */
$stmtCount = $pdo->prepare("
    SELECT COUNT(*) 
    FROM blog_post_views 
    WHERE post_id = ?
");
$stmtCount->execute([$post['id']]);
$totalViews = (int)$stmtCount->fetchColumn();

/* ================================
   VARIABLES SEO
   ================================ */
$page_title       = $post['seo_title'] ?: $post['title'];
$page_description = $post['seo_description'] ?: substr(strip_tags($post['content']), 0, 150);
$page_keywords    = $post['seo_keywords'] ?: $post['title'];
?>
<style>
.contador-vistas {
    margin-left: auto; /* lo manda a la derecha en un contenedor flex */
}
</style>

<!-- Breadcrumb Start -->
<div class="container-fluid">
    <div class="container">
        <nav class="breadcrumb bg-transparent m-0 p-0">
            <a class="breadcrumb-item" href="<?= URLBASE ?>">Home</a>
            <a class="breadcrumb-item" href="<?= URLBASE ?>/noticias/<?= htmlspecialchars($post['category_slug']) ?>/">
                <?= htmlspecialchars($post['category_name']) ?>
            </a>
            <span class="breadcrumb-item active"><?= htmlspecialchars($post['title']) ?></span>
        </nav>
    </div>
</div>
<!-- Breadcrumb End -->

<!-- News With Sidebar Start -->
<div class="container-fluid py-3">
    <div class="container">
        <div class="row">
            <div class="col-lg-8">
                <div class="position-relative mb-3">
                    <?php if (!empty($post['image'])): ?>
                        <img class="img-fluid w-100"
                             src="<?= URLBASE . '/' . htmlspecialchars($post['image']) ?>"
                             style="object-fit: cover;"
                             alt="<?= htmlspecialchars($post['title']) ?>">
                    <?php endif; ?>
                    <div class="overlay position-relative bg-light">
                        <!-- Categoría, fecha y vistas -->
                        <div class="mb-3 d-flex align-items-center">
                            <div>
                                <a href="<?= URLBASE ?>/noticias/<?= htmlspecialchars($post['category_slug']) ?>/">
                                    <?= htmlspecialchars($post['category_name']) ?>
                                </a>
                                <span class="px-1">/</span>
                                <span><?= date("F d, Y", strtotime($post['created_at'])) ?></span>
                            </div>&nbsp;&nbsp;
                            <div class="text-muted contador-vistas">
                                <i class="fas fa-eye"></i> <?= $totalViews ?>
                            </div>
                        </div>

                        <!-- Título y contenido -->
                        <div>
                            <h3 class="mb-3"><?= htmlspecialchars($post['title']) ?></h3>
                            <div class="post-content">
                                <?= $post['content'] ?>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Noticias relacionadas -->
                <?php
                $stmtRelated = $pdo->prepare("
                    SELECT p.id, p.title, p.slug, p.image, p.created_at, c.slug AS category_slug
                    FROM blog_posts p
                    INNER JOIN blog_post_category pc ON pc.post_id = p.id
                    INNER JOIN blog_categories c ON c.id = pc.category_id
                    WHERE p.status='published' AND p.deleted=0
                      AND pc.category_id = ? 
                      AND p.id != ?
                    ORDER BY p.created_at DESC
                    LIMIT 3
                ");
                $stmtRelated->execute([$post['category_id'], $post['id']]);
                $relatedPosts = $stmtRelated->fetchAll();
                ?>

                <?php if ($relatedPosts): ?>
                    <div class="mt-5">
                        <h4 class="mb-4">Te Puede Interesar</h4>
                        <div class="row">
                            <?php foreach ($relatedPosts as $rel): ?>
                                <div class="col-md-4 mb-3">
                                    <div class="position-relative">
                                        <img class="img-fluid w-100"
                                             src="<?= $rel['image'] ? URLBASE . '/' . htmlspecialchars($rel['image']) : URLBASE . '/template/news/img/news-500x280-1.jpg' ?>"
                                             style="object-fit: cover; height: 180px;"
                                             alt="<?= htmlspecialchars($rel['title']) ?>">
                                        <div class="overlay position-relative bg-light p-2">
                                            <div style="font-size: 12px;">
                                                <span><?= date("F d, Y", strtotime($rel['created_at'])) ?></span>
                                            </div>
                                            <a class="h6 d-block mt-1"
                                               href="<?= URLBASE ?>/noticias/<?= htmlspecialchars($rel['category_slug']) ?>/<?= htmlspecialchars($rel['slug']) ?>/">
                                               <?= htmlspecialchars($rel['title']) ?>
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Sidebar -->
            <?php include __DIR__ . '/partials/sidebar.php'; ?>
        </div>
    </div>
</div>
<!-- News With Sidebar End -->




