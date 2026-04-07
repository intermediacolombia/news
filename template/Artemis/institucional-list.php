<?php
require_once __DIR__ . '/../../inc/config.php';

$sql = "SELECT id, title, slug, page_type, image, seo_description, display_order FROM institutional_pages WHERE status = 'published' ORDER BY display_order ASC, title ASC";
$stmt = db()->query($sql);
$pages = $stmt->fetchAll();

$page_title = "Información Institucional | " . NOMBRE_SITIO;

if (!function_exists('img_url')) {
    function img_url(?string $path): string {
        if (empty($path)) return URLBASE . '/template/Artemis/img/placeholder.jpg';
        if (preg_match('#^https?://#i', $path)) return $path;
        return URLBASE . '/' . ltrim($path, '/');
    }
}

$typeNames = [
    'general' => 'General',
    'about' => 'Quiénes Somos',
    'mission' => 'Misión y Visión',
    'history' => 'Historia',
    'organization' => 'Organigrama',
    'board' => 'Junta Directiva',
    'team' => 'Equipo',
    'values' => 'Valores',
    'policies' => 'Políticas'
];

$typeIcons = [
    'general' => 'fa-info-circle',
    'about' => 'fa-building',
    'mission' => 'fa-bullseye',
    'history' => 'fa-history',
    'organization' => 'fa-sitemap',
    'board' => 'fa-users',
    'team' => 'fa-user-friends',
    'values' => 'fa-star',
    'policies' => 'fa-file-contract'
];
?>

<section class="py-5" style="background: var(--dark); min-height: 60vh;">
    <div class="container">
        <div class="row mb-4">
            <div class="col-12">
                <h1 class="section-title" style="color: var(--text-color);">INFORMACIÓN INSTITUCIONAL</h1>
                <p style="color: var(--text-muted); margin-top: 15px;"><?= t_theme('theme_conoce_mas_organizacion') ?></p>
            </div>
        </div>
        
        <?php if(empty($pages)): ?>
        <div class="text-center py-5">
            <i class="fas fa-building" style="font-size: 60px; color: var(--text-muted); opacity: 0.3;"></i>
            <h3 style="color: var(--text-color); margin-top: 20px;"><?= t_theme('theme_no_hay_info_institucional') ?></h3>
            <p style="color: var(--text-muted);"><?= t_theme('theme_proximamente_info') ?></p>
        </div>
        <?php else: ?>
        <div class="row">
            <?php foreach($pages as $page): 
                $typeName = $typeNames[$page['page_type']] ?? 'General';
                $typeIcon = $typeIcons[$page['page_type']] ?? 'fa-file-alt';
                $excerpt = $page['seo_description'] ?: substr(strip_tags($page['content'] ?? ''), 0, 150);
                $pageUrl = URLBASE . '/institucional/' . urlencode($page['slug']) . '/';
            ?>
            <div class="col-lg-6 col-md-6 mb-4">
                <div class="news-card">
                    <?php if(!empty($page['image'])): ?>
                    <div class="position-relative" style="overflow: hidden;">
                        <a href="<?= $pageUrl ?>">
                            <img src="<?= img_url($page['image']) ?>" 
                                 alt="<?= htmlspecialchars($page['title']) ?>"
                                 class="card-img"
                                 style="width: 100%; height: 180px; object-fit: cover;">
                        </a>
                        <span class="category-badge position-absolute" style="top: 12px; left: 12px; font-size: 10px;">
                            <i class="fas <?= $typeIcon ?> mr-1"></i><?= htmlspecialchars($typeName) ?>
                        </span>
                    </div>
                    <?php endif; ?>
                    <div class="p-4">
                        <h4 style="color: var(--text-color); font-size: 18px; font-weight: 600; margin-bottom: 10px;">
                            <a href="<?= $pageUrl ?>" style="color: inherit; text-decoration: none;">
                                <?= htmlspecialchars($page['title']) ?>
                            </a>
                        </h4>
                        <p style="color: var(--text-muted); font-size: 14px; margin-bottom: 0;">
                            <?= htmlspecialchars(substr($excerpt, 0, 100)) ?>...
                        </p>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </div>
</section>