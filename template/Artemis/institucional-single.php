<?php
require_once __DIR__ . '/../../inc/config.php';

$slug = $_GET['institutional_slug'] ?? '';

if(empty($slug)) {
    header('Location: ' . URLBASE . '/institucional');
    exit;
}

$sql = "SELECT * FROM institutional_pages WHERE slug = ? AND status = 'published'";
$stmt = db()->prepare($sql);
$stmt->execute([$slug]);
$page = $stmt->fetch();

if(!$page) {
    http_response_code(404);
    include __DIR__ . '/404.php';
    exit;
}

$page_title = ($page['seo_title'] ?: $page['title']) . " | " . NOMBRE_SITIO;
$page_description = $page['seo_description'] ?: substr(strip_tags($page['content']), 0, 160);

if (!function_exists('img_url')) {
    function img_url(?string $path): string {
        if (empty($path)) return URLBASE . '/template/Artemis/img/placeholder.jpg';
        if (preg_match('#^https?://#i', $path)) return $path;
        return URLBASE . '/' . ltrim($path, '/');
    }
}
?>

<section class="py-5" style="background: var(--dark);">
    <div class="container">
        <div class="row">
            <div class="col-lg-8">
                <a href="<?= URLBASE ?>/institucional/" style="color: var(--primary); text-decoration: none; display: inline-block; margin-bottom: 20px;">
                    <i class="fas fa-arrow-left mr-2"></i><?= t_theme('theme_volver_listado') ?>
                </a>
                
                <div style="background: var(--dark-secondary); border-radius: 20px; overflow: hidden;">
                    <?php if(!empty($page['image'])): ?>
                    <img src="<?= img_url($page['image']) ?>" 
                         alt="<?= htmlspecialchars($page['title']) ?>"
                         style="width: 100%; max-height: 400px; object-fit: cover;">
                    <?php endif; ?>
                    
                    <div class="p-4">
                        <h1 style="color: var(--text-color); font-family: 'Playfair Display', serif; font-size: 2rem; margin-bottom: 20px;">
                            <?= htmlspecialchars($page['title']) ?>
                        </h1>
                        
                        <div class="institutional-content" style="color: var(--text-color); font-size: 16px; line-height: 1.8;">
                            <?= render_post_content($page['content']) ?>
                        </div>
                        
                        <div class="mt-4 pt-4" style="border-top: 1px solid rgba(255,255,255,0.1);">
                            <span style="color: var(--text-muted); margin-right: 15px;"><?= t_theme('theme_compartir') ?></span>
                            <a href="https://www.facebook.com/sharer/sharer.php?u=<?= urlencode($_SERVER['REQUEST_URI']) ?>" target="_blank" style="color: #1877f2; font-size: 18px; margin-right: 12px;"><i class="fab fa-facebook-f"></i></a>
                            <a href="https://twitter.com/intent/tweet?url=<?= urlencode($_SERVER['REQUEST_URI']) ?>&text=<?= urlencode($page['title']) ?>" target="_blank" style="color: #1da1f2; font-size: 18px; margin-right: 12px;"><i class="fab fa-twitter"></i></a>
                            <a href="https://api.whatsapp.com/send?text=<?= urlencode($page['title'] . ' ' . $_SERVER['REQUEST_URI']) ?>" target="_blank" style="color: #25d366; font-size: 18px; margin-right: 12px;"><i class="fab fa-whatsapp"></i></a>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-4">
                <?php include __DIR__ . '/partials/sidebar.php'; ?>
            </div>
        </div>
    </div>
</section>

<style>
    .institutional-content h2, .institutional-content h3, .institutional-content h4 {
        color: var(--text-color);
        font-weight: 600;
        margin-top: 25px;
        margin-bottom: 15px;
    }
    .institutional-content h2 { font-size: 24px; border-bottom: 2px solid var(--primary); padding-bottom: 10px; }
    .institutional-content h3 { font-size: 20px; }
    .institutional-content p { margin-bottom: 20px; color: var(--text-color); }
    .institutional-content ul, .institutional-content ol { margin-bottom: 20px; padding-left: 30px; }
    .institutional-content li { margin-bottom: 10px; color: var(--text-color); }
    .institutional-content img { max-width: 100%; height: auto; border-radius: 8px; margin: 20px 0; }
    .institutional-content blockquote { border-left: 4px solid var(--primary); padding-left: 20px; margin: 25px 0; font-style: italic; color: var(--text-muted); }
</style>