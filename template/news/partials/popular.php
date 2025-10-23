<?php
require_once __DIR__ . '/../../../inc/config.php';

// Obtener las 6 noticias más leídas (2 principales grandes + 4 secundarias)
$stmt = $pdo->query("
    SELECT p.id, p.title, p.slug, p.image, p.created_at, p.content,
           c.name AS category_name, c.slug AS category_slug,
           COUNT(v.id) AS total_views
    FROM blog_post_views v
    INNER JOIN blog_posts p ON p.id = v.post_id
    INNER JOIN blog_post_category pc ON pc.post_id = p.id
    INNER JOIN blog_categories c ON c.id = pc.category_id
    WHERE p.status='published' AND p.deleted=0
    GROUP BY p.id, p.title, p.slug, p.image, p.created_at, c.name, c.slug, p.content
    ORDER BY total_views DESC
    LIMIT 6
");
$popularPosts = $stmt->fetchAll();

// Función para excerpt seguro con HTML básico permitido
function safe_excerpt($html, $limit = 30) {
    // Permitir solo etiquetas seguras
    $clean = strip_tags($html, '');
    
    // Convertir saltos dobles en párrafos simples
    $clean = preg_replace('/\s+/', ' ', $clean);

    // Cortar por palabras
    $words = preg_split('/\s+/', $clean);
    if (count($words) > $limit) {
        $clean = implode(' ', array_slice($words, 0, $limit)) . '...';
    }

    // Siempre envolver en <p> para no romper el layout
    return "<p class=\"m-0\">$clean</p>";
}

?>

<?php if ($popularPosts): ?>
<div class="row mb-3">
    <div class="col-12">
        <div class="d-flex align-items-center justify-content-between bg-light py-2 px-4 mb-3 title-widgets">
            <h3 class="m-0">Más Populares</h3>
        </div>
    </div>

    <!-- Columna izquierda -->
    <div class="col-lg-6">
        <?php if (!empty($popularPosts[0])): ?>
            <div class="position-relative mb-3">
                <img class="img-fluid w-100"
                     src="<?= $popularPosts[0]['image'] ? URLBASE . '/' . htmlspecialchars($popularPosts[0]['image']) : URLBASE . '/template/news/img/news-500x280-1.jpg' ?>"
                     style="object-fit: cover;">
                <div class="overlay position-relative bg-light">
                    <div class="mb-2" style="font-size: 14px;">
                        <a href="<?= URLBASE ?>/noticias/<?= htmlspecialchars($popularPosts[0]['category_slug']) ?>/">
                            <?= htmlspecialchars($popularPosts[0]['category_name']) ?>
                        </a>
                        <span class="px-1">/</span>
                        <span><?= fecha_espanol(date("F d, Y", strtotime($popularPosts[0]['created_at']))) ?></span>
                    </div>
                    <a class="h4" href="<?= URLBASE ?>/<?= htmlspecialchars($popularPosts[0]['category_slug']) ?>/<?= htmlspecialchars($popularPosts[0]['slug']) ?>/">
                        <?= htmlspecialchars($popularPosts[0]['title']) ?>
                    </a>
                    <?= safe_excerpt($popularPosts[0]['content'], 30) ?>

                </div>
            </div>
        <?php endif; ?>

        <?php foreach (array_slice($popularPosts, 2, 2) as $post): ?>
            <div class="d-flex mb-3">
                <img src="<?= $post['image'] ? URLBASE . '/' . htmlspecialchars($post['image']) : URLBASE . '/template/news/img/news-100x100-1.jpg' ?>"
                     style="width: 100px; height: 100px; object-fit: cover;">
                <div class="w-100 d-flex flex-column justify-content-center bg-light px-3" style="height: 100px;">
                    <div class="mb-1" style="font-size: 13px;">
                        <a href="<?= URLBASE ?>/<?= htmlspecialchars($post['category_slug']) ?>/">
                            <?= htmlspecialchars($post['category_name']) ?>
                        </a>
                        <span class="px-1">/</span>
                        <span><?= fecha_espanol(date("F d, Y", strtotime($post['created_at']))) ?></span>
                    </div>
                    <a class="h6 m-0" href="<?= URLBASE ?>/<?= htmlspecialchars($post['category_slug']) ?>/<?= htmlspecialchars($post['slug']) ?>/">
                        <?= htmlspecialchars($post['title']) ?>
                    </a>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <!-- Columna derecha -->
    <div class="col-lg-6">
        <?php if (!empty($popularPosts[1])): ?>
            <div class="position-relative mb-3">
                <img class="img-fluid w-100"
                     src="<?= $popularPosts[1]['image'] ? URLBASE . '/' . htmlspecialchars($popularPosts[1]['image']) : URLBASE . '/template/news/img/news-500x280-2.jpg' ?>"
                     style="object-fit: cover;">
                <div class="overlay position-relative bg-light">
                    <div class="mb-2" style="font-size: 14px;">
                        <a href="<?= URLBASE ?>/noticias/<?= htmlspecialchars($popularPosts[1]['category_slug']) ?>/">
                            <?= htmlspecialchars($popularPosts[1]['category_name']) ?>
                        </a>
                        <span class="px-1">/</span>
                        <span><?= fecha_espanol(date("F d, Y", strtotime($popularPosts[1]['created_at']))) ?></span>
                    </div>
                    <a class="h4" href="<?= URLBASE ?>/noticias/<?= htmlspecialchars($popularPosts[1]['category_slug']) ?>/<?= htmlspecialchars($popularPosts[1]['slug']) ?>/">
                        <?= htmlspecialchars($popularPosts[1]['title']) ?>
                    </a>
                    <?= safe_excerpt($popularPosts[0]['content'], 30) ?>
                </div>
            </div>
        <?php endif; ?>

        <?php foreach (array_slice($popularPosts, 4, 2) as $post): ?>
            <div class="d-flex mb-3">
                <img src="<?= $post['image'] ? URLBASE . '/' . htmlspecialchars($post['image']) : URLBASE . '/template/news/img/news-100x100-2.jpg' ?>"
                     style="width: 100px; height: 100px; object-fit: cover;">
                <div class="w-100 d-flex flex-column justify-content-center bg-light px-3" style="height: 100px;">
                    <div class="mb-1" style="font-size: 13px;">
                        <a href="<?= URLBASE ?>/noticias/<?= htmlspecialchars($post['category_slug']) ?>/">
                            <?= htmlspecialchars($post['category_name']) ?>
                        </a>
                        <span class="px-1">/</span>
                        <span><?= fecha_espanol(date("F d, Y", strtotime($post['created_at']))) ?></span>
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

