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

$q = trim($_GET['q'] ?? '');
$results = [];
$totalResults = 0;

if ($q !== '') {
    $stmt = db()->prepare("
        SELECT p.*, c.name AS category_name, c.slug AS category_slug
        FROM blog_posts p
        INNER JOIN blog_post_category pc ON pc.post_id = p.id
        INNER JOIN blog_categories c ON c.id = pc.category_id
        WHERE (p.title LIKE :q1 OR p.content LIKE :q2 OR p.seo_description LIKE :q3)
          AND p.status='published' AND p.deleted=0
        GROUP BY p.id
        ORDER BY p.created_at DESC
    ");
    $stmt->execute([':q1' => "%$q%", ':q2' => "%$q%", ':q3' => "%$q%"]);
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $totalResults = count($results);
}

$page_title = !empty($q) ? "Resultados para \"$q\" | " . NOMBRE_SITIO : "Búsqueda | " . NOMBRE_SITIO;
$page_description = !empty($q) ? "Resultados de búsqueda para: $q en " . NOMBRE_SITIO : "Busca noticias y artículos en " . NOMBRE_SITIO;
?>

<section class="py-5" style="background: var(--dark); min-height: 60vh;">
    <div class="container">
        <div class="row mb-4">
            <div class="col-12">
                <h1 class="section-title" style="color: var(--text-color);"><?= t_theme('theme_buscar_noticias') ?></h1>
                <?php if (!empty($q)): ?>
                <p style="color: var(--text-muted); margin-top: 10px;">
                    Se <?= $totalResults === 1 ? 'encontró' : 'encontraron' ?> <strong style="color: var(--text-color);"><?= $totalResults ?></strong> <?= $totalResults === 1 ? 'resultado' : 'resultados' ?> para "<?= htmlspecialchars($q) ?>"
                </p>
                <?php endif; ?>
            </div>
        </div>
        
        <div class="row mb-5">
            <div class="col-lg-8 col-md-10">
                <form action="<?= URLBASE ?>/buscar/" method="get" class="d-flex" style="gap: 10px;">
                    <input type="text" 
                           name="q" 
                           value="<?= htmlspecialchars($q) ?>"
                           placeholder="Buscar noticias, artículos..."
                           class="search-input"
                           style="flex: 1; padding: 15px 20px; border-radius: 12px; border: 2px solid rgba(255,255,255,0.1); background: var(--dark-secondary); color: #fff;">
                    <button type="submit" class="btn-artemis">
                        <i class="fas fa-search"></i>
                    </button>
                </form>
            </div>
        </div>

        <div class="row">
            <?php if (!empty($q)): ?>
                <?php if ($results): ?>
                    <div class="col-12">
                        <div class="row">
                            <?php foreach ($results as $p): 
                                $postUrl = URLBASE . "/" . htmlspecialchars($p['category_slug']) . "/" . htmlspecialchars($p['slug']) . "/";
                            ?>
                            <div class="col-lg-4 col-md-6 mb-4">
                                <div class="news-card">
                                    <div class="position-relative" style="overflow: hidden;">
                                        <a href="<?= $postUrl ?>">
                                            <img src="<?= img_url($p['image']) ?>" 
                                                 alt="<?= htmlspecialchars(get_image_alt($p['image'], $p['title'])) ?>" 
                                                 class="card-img"
                                                 style="width: 100%; height: 180px; object-fit: cover;">
                                        </a>
                                        <span class="category-badge position-absolute" style="top: 12px; left: 12px; font-size: 10px;">
                                            <?= htmlspecialchars($p['category_name']) ?>
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
                        </div>
                    </div>
                <?php else: ?>
                    <div class="col-12 text-center py-5">
                        <i class="fas fa-search" style="font-size: 60px; color: var(--text-muted); opacity: 0.3;"></i>
                        <h3 style="color: var(--text-color); margin-top: 20px;">No se encontraron resultados</h3>
                        <p style="color: var(--text-muted);">No pudimos encontrar ningún resultado para "<strong><?= htmlspecialchars($q) ?></strong>".</p>
                        <a href="<?= URLBASE ?>" class="btn-artemis mt-3"><i class="fas fa-home mr-2"></i>Volver al inicio</a>
                    </div>
                <?php endif; ?>
            <?php else: ?>
                <div class="col-12 text-center py-5">
                    <i class="fas fa-search" style="font-size: 60px; color: var(--text-muted); opacity: 0.3;"></i>
                    <h3 style="color: var(--text-color); margin-top: 20px;">¿Qué estás buscando?</h3>
                    <p style="color: var(--text-muted);">Utiliza el formulario de búsqueda para encontrar noticias y artículos.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</section>