<?php
/**
 * SIDEBAR DINÁMICO - NEWSEDGE
 */

// ===============================
// Helpers Locales (Evitan errores)
// ===============================
if (!function_exists('img_url')) {
    function img_url(?string $path): string {
        if (empty($path)) return URLBASE . '/template/newsedge/img/news/default.jpg';
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

// ===============================
// Lógica de Datos
// ===============================
global $sys;

// 1. Categorías con conteo
$categories = db()->query("
    SELECT c.name, c.slug, COUNT(pc.post_id) AS total
    FROM blog_categories c
    LEFT JOIN blog_post_category pc ON c.id = pc.category_id
    INNER JOIN blog_posts p ON p.id = pc.post_id AND p.status='published' AND p.deleted=0
    WHERE c.status='active' AND c.deleted=0
    GROUP BY c.id ORDER BY total DESC LIMIT 6
")->fetchAll();

// 2. Noticias Populares (Más leídas)
$popular = db()->query("
    SELECT p.title, p.slug, p.image, p.created_at, c.name as cat_name, c.slug as category_slug
    FROM blog_post_views v
    INNER JOIN blog_posts p ON p.id = v.post_id
    LEFT JOIN blog_post_category pc ON pc.post_id = p.id
    LEFT JOIN blog_categories c ON c.id = pc.category_id
    WHERE p.status='published' AND p.deleted=0
    GROUP BY p.id ORDER BY COUNT(v.id) DESC LIMIT 4
")->fetchAll();

// 3. Generación de Tags automáticos
$textos = db()->query("SELECT CONCAT(title, ' ', content) FROM blog_posts WHERE status='published' LIMIT 10")->fetchAll(PDO::FETCH_COLUMN);
$palabras = preg_split('/\W+/u', strtolower(strip_tags(implode(' ', $textos))), -1, PREG_SPLIT_NO_EMPTY);
$stop = ['que','con','para','este','esta','entre','cuando','pero','sobre','nbsp','como','del','las','los'];
$freq = array_count_values(array_filter($palabras, function($p) use ($stop) {
    return mb_strlen($p) > 4 && !in_array($p, $stop);
}));
arsort($freq);
$tags = array_slice(array_keys($freq), 0, 9);
?>

<style>
    /* Títulos del sidebar */
    .sidebar-box .title-medium-dark a, 
    .sidebar-box h4.title-medium-dark a {
        color: #000000;
        transition: color 0.3s ease;
    }
    
    .sidebar-box .title-medium-dark a:hover {
        color: var(--primary);
    }
    
    /* Categorías del sidebar */
    .sidebar-category-item {
        display: block;
        padding: 15px;
        background-color: #f5f5f5;
        border-radius: 4px;
        text-decoration: none;
        border-left: 4px solid #3E3E3E;
        transition: all 0.3s ease;
    }
    
    .sidebar-category-item:hover {
        background-color: var(--primary);
        border-left-color: var(--primary);
    }
    
    .sidebar-category-item:hover .sidebar-category-name {
        color: white;
    }
    
    .sidebar-category-item:hover .sidebar-category-badge {
        background-color: white;
        color: var(--primary);
    }
    
    .sidebar-category-content {
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
    
    .sidebar-category-name {
        color: #000;
        font-weight: 600;
        transition: color 0.3s ease;
    }
    
    .sidebar-category-badge {
        background-color: #3E3E3E;
        color: white;
        padding: 5px 10px;
        border-radius: 20px;
        font-size: 12px;
        transition: all 0.3s ease;
    }
    
    /* Tags del sidebar */
    .sidebar-tag-link {
        display: inline-block;
        padding: 6px 12px;
        background-color: #f5f5f5;
        color: #333;
        border-radius: 20px;
        font-size: 12px;
        transition: all 0.3s ease;
        border: 1px solid #ddd;
        text-decoration: none;
    }
    
    .sidebar-tag-link:hover {
        background-color: #000;
        color: white;
        border-color: #000;
    }
    
    /* Imágenes populares */
    .sidebar-popular-img {
        height: 80px;
        object-fit: cover;
        border-radius: 4px;
    }
</style>

<!-- REDES SOCIALES -->
<div class="sidebar-box item-box-light-md">
    <div class="topic-border color-cinnabar mb-30">
        <div class="topic-box-lg color-cinnabar">Síguenos</div>
    </div>
    <ul class="stay-connected overflow-hidden">
        <?php if(!empty($sys['facebook'])): ?>
        <li class="facebook">
            <a href="<?= htmlspecialchars($sys['facebook']) ?>" target="_blank" rel="noopener">
                <i class="fa fa-facebook" aria-hidden="true"></i>
                <div class="connection-quantity">Facebook</div>
                <p>Síguenos</p>
            </a>
        </li>
        <?php endif; ?>
        
        <?php if(!empty($sys['twitter'])): ?>
        <li class="twitter">
            <a href="<?= htmlspecialchars($sys['twitter']) ?>" target="_blank" rel="noopener">
                <i class="fa fa-twitter" aria-hidden="true"></i>
                <div class="connection-quantity">Twitter / X</div>
                <p>Síguenos</p>
            </a>
        </li>
        <?php endif; ?>

        <?php if(!empty($sys['instagram'])): ?>
        <li class="linkedin">
            <a href="<?= htmlspecialchars($sys['instagram']) ?>" target="_blank" rel="noopener">
                <i class="fa fa-instagram" aria-hidden="true"></i>
                <div class="connection-quantity">Instagram</div>
                <p>Síguenos</p>
            </a>
        </li>
        <?php endif; ?>

        <?php if(!empty($sys['youtube'])): ?>
        <li class="rss">
            <a href="<?= htmlspecialchars($sys['youtube']) ?>" target="_blank" rel="noopener">
                <i class="fa fa-youtube" aria-hidden="true"></i>
                <div class="connection-quantity">YouTube</div>
                <p>Suscríbete</p>
            </a>
        </li>
        <?php endif; ?>
    </ul>
</div>

<!-- PUBLICIDAD -->
<div class="sidebar-box item-box-light-md">
    <div class="ne-banner-layout1 text-center">
        <?php include __DIR__ . '/ads5.php'; ?>
    </div>
</div>

<!-- CATEGORÍAS -->
<div class="sidebar-box item-box-light-md">
    <div class="topic-border color-cinnabar mb-30">
        <div class="topic-box-lg color-cinnabar">Categorías</div>
    </div>
    <div class="row">
        <?php foreach ($categories as $cat): ?>
        <div class="col-12 mb-15">
            <a href="<?= URLBASE ?>/<?= htmlspecialchars($cat['slug']) ?>/" class="sidebar-category-item">
                <div class="sidebar-category-content">
                    <span class="sidebar-category-name size-md">
                        <?= htmlspecialchars($cat['name']) ?>
                    </span>
                    <span class="sidebar-category-badge">
                        <?= $cat['total'] ?>
                    </span>
                </div>
            </a>
        </div>
        <?php endforeach; ?>
    </div>
</div>

<!-- LAS MÁS LEÍDAS -->
<div class="sidebar-box item-box-light-md">
    <div class="topic-border color-cinnabar mb-30">
        <div class="topic-box-lg color-cinnabar">Populares</div>
    </div>
    <?php foreach ($popular as $p): 
        $postUrl = URLBASE . "/" . htmlspecialchars($p['category_slug']) . "/" . htmlspecialchars($p['slug']) . "/";
    ?>
    <div class="media media-none--md mb-20">
        <div class="position-relative width-40">
            <a href="<?= $postUrl ?>" class="img-opacity-hover">
                <img src="<?= img_url($p['image']) ?>" 
                     alt="<?= htmlspecialchars($p['title']) ?>" 
                     class="img-fluid width-100 sidebar-popular-img">
            </a>
        </div>
        <div class="media-body p-mb-none-child media-margin15">
            <div class="post-date-dark">
                <ul>
                    <li>
                        <span><i class="fa fa-calendar" aria-hidden="true"></i></span>
                        <?= date('d M, Y', strtotime($p['created_at'])) ?>
                    </li>
                </ul>
            </div>
            <h4 class="title-medium-dark size-sm mb-none">
                <a href="<?= $postUrl ?>">
                    <?= truncate_text($p['title'], 50) ?>
                </a>
            </h4>
        </div>
    </div>
    <?php endforeach; ?>
</div>

<!-- TAGS -->
<div class="sidebar-box item-box-light-md">
    <div class="topic-border color-cinnabar mb-25">
        <div class="topic-box-lg color-cinnabar">Tags Tendencias</div>
    </div>
    <ul class="sidebar-tags">
        <?php foreach ($tags as $t): ?>
        <li>
            <a href="<?= URLBASE ?>/buscar/<?= urlencode($t) ?>/" class="sidebar-tag-link">
                <?= htmlspecialchars(ucfirst($t)) ?>
            </a>
        </li>
        <?php endforeach; ?>
    </ul>
</div>



