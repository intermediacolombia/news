<?php
if (!function_exists('img_url')) {
    function img_url(?string $path): string {
        if (empty($path)) return URLBASE . '/template/Artemis/img/placeholder.jpg';
        if (preg_match('#^https?://#i', $path)) return $path;
        return URLBASE . '/' . ltrim($path, '/');
    }
}

if (!function_exists('truncate_text')) {
    function truncate_text(string $text, int $limit = 100): string {
        $text = strip_tags($text);
        return (mb_strlen($text) > $limit) ? mb_substr($text, 0, $limit) . '...' : $text;
    }
}

global $sys;

$categories = db()->query("
    SELECT c.name, c.slug, COUNT(pc.post_id) AS total
    FROM blog_categories c
    LEFT JOIN blog_post_category pc ON c.id = pc.category_id
    INNER JOIN blog_posts p ON p.id = pc.post_id AND p.status='published' AND p.deleted=0
    WHERE c.status='active' AND c.deleted=0
    GROUP BY c.id ORDER BY total DESC LIMIT 6
")->fetchAll();

$popular = db()->query("
    SELECT p.title, p.slug, p.image, p.created_at, c.name as cat_name, c.slug as category_slug
    FROM blog_post_views v
    INNER JOIN blog_posts p ON p.id = v.post_id
    LEFT JOIN blog_post_category pc ON pc.post_id = p.id
    LEFT JOIN blog_categories c ON c.id = pc.category_id
    WHERE p.status='published' AND p.deleted=0
    GROUP BY p.id ORDER BY COUNT(v.id) DESC LIMIT 4
")->fetchAll();

$textos = db()->query("SELECT CONCAT(title, ' ', content) FROM blog_posts WHERE status='published' LIMIT 10")->fetchAll(PDO::FETCH_COLUMN);
$palabras = preg_split('/\W+/u', strtolower(strip_tags(implode(' ', $textos))), -1, PREG_SPLIT_NO_EMPTY);
$stop = ['que','con','para','este','esta','entre','cuando','pero','sobre','nbsp','como','del','las','los'];
$freq = array_count_values(array_filter($palabras, function($p) use ($stop) {
    return mb_strlen($p) > 4 && !in_array($p, $stop);
}));
arsort($freq);
$tags = array_slice(array_keys($freq), 0, 9);
?>

<div class="sidebar-section p-4" style="background: var(--dark-secondary); border-radius: 16px;">
    <h5 class="mb-4" style="color: #fff; font-family: 'Playfair Display', serif;">Síguenos</h5>
    <div class="row">
        <?php if(!empty($sys['facebook'])): ?>
        <div class="col-6 mb-3">
            <a href="<?= htmlspecialchars($sys['facebook']) ?>" target="_blank" 
               class="btn btn-block d-flex align-items-center justify-content-center"
               style="background: #1877f2; color: #fff; border-radius: 10px; text-decoration: none;">
                <i class="fab fa-facebook-f mr-2"></i> Facebook
            </a>
        </div>
        <?php endif; ?>
        
        <?php if(!empty($sys['twitter'])): ?>
        <div class="col-6 mb-3">
            <a href="<?= htmlspecialchars($sys['twitter']) ?>" target="_blank" 
               class="btn btn-block d-flex align-items-center justify-content-center"
               style="background: #1da1f2; color: #fff; border-radius: 10px; text-decoration: none;">
                <i class="fab fa-twitter mr-2"></i> Twitter
            </a>
        </div>
        <?php endif; ?>

        <?php if(!empty($sys['instagram'])): ?>
        <div class="col-6 mb-3">
            <a href="<?= htmlspecialchars($sys['instagram']) ?>" target="_blank" 
               class="btn btn-block d-flex align-items-center justify-content-center"
               style="background: #e4405f; color: #fff; border-radius: 10px; text-decoration: none;">
                <i class="fab fa-instagram mr-2"></i> Instagram
            </a>
        </div>
        <?php endif; ?>

        <?php if(!empty($sys['youtube'])): ?>
        <div class="col-6 mb-3">
            <a href="<?= htmlspecialchars($sys['youtube']) ?>" target="_blank" 
               class="btn btn-block d-flex align-items-center justify-content-center"
               style="background: #ff0000; color: #fff; border-radius: 10px; text-decoration: none;">
                <i class="fab fa-youtube mr-2"></i> YouTube
            </a>
        </div>
        <?php endif; ?>
    </div>
</div>

<?php include __DIR__ . '/ads5.php'; ?>

<div class="sidebar-section p-4 mt-4" style="background: var(--dark-secondary); border-radius: 16px;">
    <h5 class="mb-4" style="color: #fff; font-family: 'Playfair Display', serif;">Categorías</h5>
    <?php foreach ($categories as $cat): ?>
    <a href="<?= URLBASE ?>/noticias/<?= htmlspecialchars($cat['slug']) ?>/" 
       class="d-flex justify-content-between align-items-center p-2 mb-2"
       style="background: rgba(255,255,255,0.05); border-radius: 8px; text-decoration: none; transition: all 0.3s;">
        <span style="color: #e6edf3; font-size: 14px;"><?= htmlspecialchars($cat['name']) ?></span>
        <span style="background: var(--primary); color: #fff; padding: 2px 10px; border-radius: 12px; font-size: 12px;">
            <?= $cat['total'] ?>
        </span>
    </a>
    <?php endforeach; ?>
</div>

<div class="sidebar-section p-4 mt-4" style="background: var(--dark-secondary); border-radius: 16px;">
    <h5 class="mb-4" style="color: #fff; font-family: 'Playfair Display', serif;">Populares</h5>
    <?php foreach ($popular as $p): 
        $postUrl = URLBASE . "/" . htmlspecialchars($p['category_slug']) . "/" . htmlspecialchars($p['slug']) . "/";
    ?>
    <div class="media mb-3" style="align-items: start;">
        <img src="<?= img_url($p['image']) ?>" 
             alt="<?= htmlspecialchars($p['title']) ?>" 
             class="mr-3"
             style="width: 80px; height: 60px; object-fit: cover; border-radius: 8px;">
        <div class="media-body">
            <h6 style="font-size: 14px; color: #e6edf3; margin-bottom: 5px; line-height: 1.4;">
                <a href="<?= $postUrl ?>" style="color: inherit; text-decoration: none;">
                    <?= truncate_text($p['title'], 45) ?>
                </a>
            </h6>
            <span style="color: var(--text-muted); font-size: 12px;">
                <i class="far fa-calendar mr-1"></i>
                <?= date('d M, Y', strtotime($p['created_at'])) ?>
            </span>
        </div>
    </div>
    <?php endforeach; ?>
</div>

<div class="sidebar-section p-4 mt-4" style="background: var(--dark-secondary); border-radius: 16px;">
    <h5 class="mb-4" style="color: #fff; font-family: 'Playfair Display', serif;">Tags</h5>
    <div class="d-flex flex-wrap gap-2">
        <?php foreach ($tags as $t): ?>
        <a href="<?= URLBASE ?>/buscar/<?= urlencode($t) ?>/" 
           style="background: rgba(255,255,255,0.1); color: var(--text-muted); padding: 6px 14px; border-radius: 20px; font-size: 13px; text-decoration: none; transition: all 0.3s;">
            #<?= htmlspecialchars(ucfirst($t)) ?>
        </a>
        <?php endforeach; ?>
    </div>
</div>