<?php
require_once __DIR__ . '/../../../inc/config.php';

// Obtener las últimas 6 noticias publicadas
$stmt = $pdo->query("
    SELECT p.id, p.title, p.slug, p.image, p.created_at, p.content,
           c.name AS category_name, c.slug AS category_slug
    FROM blog_posts p
    INNER JOIN blog_post_category pc ON pc.post_id = p.id
    INNER JOIN blog_categories c ON c.id = pc.category_id
    WHERE p.status='published' AND p.deleted=0
    ORDER BY p.created_at DESC
    LIMIT 6
");
$latestPosts = $stmt->fetchAll();

// Declarar helper SOLO si no existe (para evitar redeclaración)
if (!function_exists('safe_excerpt')) {
    function safe_excerpt($html, $limit = 30) {
        // permitir solo etiquetas seguras para no romper el diseño
        $clean = strip_tags($html, '<b><i><strong><em><a>');
        $words = preg_split('/\s+/', $clean);
        if (count($words) > $limit) {
            $clean = implode(' ', array_slice($words, 0, $limit)) . '...';
        }
        return $clean;
    }
}
?>

<?php if ($latestPosts): ?>
<div class="row">
    <div class="col-12">
        <div class="d-flex align-items-center justify-content-between bg-light py-2 px-4 mb-3">
            <h3 class="m-0">Últimas</h3>
            <a class="text-secondary font-weight-medium text-decoration-none" href="<?= URLBASE ?>/noticias/">Ver Todas</a>
        </div>
    </div>

    <!-- Columna izquierda -->
    <div class="col-lg-6">
        <?php if (!empty($latestPosts[0])): ?>
            <div class="position-relative mb-3">
                <img class="img-fluid w-100"
                     src="<?= $latestPosts[0]['image'] ? URLBASE . '/' . htmlspecialchars($latestPosts[0]['image']) : URLBASE . '/template/news/img/news-500x280-1.jpg' ?>"
                     style="object-fit: cover;">
                <div class="overlay position-relative bg-light">
                    <div class="mb-2" style="font-size: 14px;">
                        <a href="<?= URLBASE ?>/noticias/<?= htmlspecialchars($latestPosts[0]['category_slug']) ?>/">
                            <?= htmlspecialchars($latestPosts[0]['category_name']) ?>
                        </a>
                        <span class="px-1">/</span>
                        <span><?= date("F d, Y", strtotime($latestPosts[0]['created_at'])) ?></span>
                    </div>
                    <a class="h4" href="<?= URLBASE ?>/noticias/<?= htmlspecialchars($latestPosts[0]['category_slug']) ?>/<?= htmlspecialchars($latestPosts[0]['slug']) ?>/">
                        <?= htmlspecialchars($latestPosts[0]['title']) ?>
                    </a>
                    <p class="m-0"><?= safe_excerpt($latestPosts[0]['content'], 30) ?></p>
                </div>
            </div>
        <?php endif; ?>

        <?php foreach (array_slice($latestPosts, 2, 2) as $post): ?>
            <div class="d-flex mb-3">
                <img src="<?= $post['image'] ? URLBASE . '/' . htmlspecialchars($post['image']) : URLBASE . '/template/news/img/news-100x100-1.jpg' ?>"
                     style="width: 100px; height: 100px; object-fit: cover;">
                <div class="w-100 d-flex flex-column justify-content-center bg-light px-3" style="height: 100px;">
                    <div class="mb-1" style="font-size: 13px;">
                        <a href="<?= URLBASE ?>/noticias/<?= htmlspecialchars($post['category_slug']) ?>/">
                            <?= htmlspecialchars($post['category_name']) ?>
                        </a>
                        <span class="px-1">/</span>
                        <span><?= date("F d, Y", strtotime($post['created_at'])) ?></span>
                    </div>
                    <a class="h6 m-0" href="<?= URLBASE ?>/noticias/<?= htmlspecialchars($post['category_slug']) ?>/<?= htmlspecialchars($post['slug']) ?>/">
                        <?= htmlspecialchars($post['title']) ?>
                    </a>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <!-- Columna derecha -->
    <div class="col-lg-6">
        <?php if (!empty($latestPosts[1])): ?>
            <div class="position-relative mb-3">
                <img class="img-fluid w-100"
                     src="<?= $latestPosts[1]['image'] ? URLBASE . '/' . htmlspecialchars($latestPosts[1]['image']) : URLBASE . '/template/news/img/news-500x280-2.jpg' ?>"
                     style="object-fit: cover;">
                <div class="overlay position-relative bg-light">
                    <div class="mb-2" style="font-size: 14px;">
                        <a href="<?= URLBASE ?>/noticias/<?= htmlspecialchars($latestPosts[1]['category_slug']) ?>/">
                            <?= htmlspecialchars($latestPosts[1]['category_name']) ?>
                        </a>
                        <span class="px-1">/</span>
                        <span><?= date("F d, Y", strtotime($latestPosts[1]['created_at'])) ?></span>
                    </div>
                    <a class="h4" href="<?= URLBASE ?>/noticias/<?= htmlspecialchars($latestPosts[1]['category_slug']) ?>/<?= htmlspecialchars($latestPosts[1]['slug']) ?>/">
                        <?= htmlspecialchars($latestPosts[1]['title']) ?>
                    </a>
                    <p class="m-0"><?= safe_excerpt($latestPosts[1]['content'], 30) ?></p>
                </div>
            </div>
        <?php endif; ?>

        <?php foreach (array_slice($latestPosts, 4, 2) as $post): ?>
            <div class="d-flex mb-3">
                <img src="<?= $post['image'] ? URLBASE . '/' . htmlspecialchars($post['image']) : URLBASE . '/template/news/img/news-100x100-2.jpg' ?>"
                     style="width: 100px; height: 100px; object-fit: cover;">
                <div class="w-100 d-flex flex-column justify-content-center bg-light px-3" style="height: 100px;">
                    <div class="mb-1" style="font-size: 13px;">
                        <a href="<?= URLBASE ?>/noticias/<?= htmlspecialchars($post['category_slug']) ?>/">
                            <?= htmlspecialchars($post['category_name']) ?>
                        </a>
                        <span class="px-1">/</span>
                        <span><?= date("F d, Y", strtotime($post['created_at'])) ?></span>
                    </div>
                    <a class="h6 m-0" href="<?= URLBASE ?>/noticias/<?= htmlspecialchars($post['category_slug']) ?>/<?= htmlspecialchars($post['slug']) ?>/">
                        <?= htmlspecialchars($post['title']) ?>
                    </a>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>
<?php endif; ?>

