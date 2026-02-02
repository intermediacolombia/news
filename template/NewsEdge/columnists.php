<?php
require_once __DIR__ . '/../../inc/config.php';

// Helpers
if (!function_exists('truncate_text')) {
    function truncate_text(string $text, int $limit = 120): string {
        $text = strip_tags($text);
        return (mb_strlen($text) > $limit) ? mb_substr($text, 0, $limit) . '...' : $text;
    }
}

if (!function_exists('img_url')) {
    function img_url(?string $path): string {
        if (empty($path)) {
            return URLBASE . '/template/NewsEdge/img/placeholder.jpg';
        }
        if (filter_var($path, FILTER_VALIDATE_URL)) {
            return $path;
        }
        $path = ltrim($path, '/');
        if (strpos($path, 'uploads/') !== 0) {
            $path = 'uploads/' . $path;
        }
        return URLBASE . '/' . $path;
    }
}

$username = $_GET['columnist_name_slug'] ?? null;

if (!$username) {
    http_response_code(404);
    include __DIR__ . '/404.php';
    exit;
}

// 1. Obtener datos del columnista
$sqlUser = "
    SELECT nombre, apellido, foto_perfil, bio
    FROM usuarios
    WHERE username = ?
      AND es_columnista = 1
      AND estado = 0
      AND borrado = 0
    LIMIT 1
";

try {
    $stmt = db()->prepare($sqlUser);
    $stmt->execute([$username]);
    $usuario = $stmt->fetch(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log("Error al buscar usuario: " . $e->getMessage());
    $usuario = null;
}

if (!$usuario) {
    http_response_code(404);
    include __DIR__ . '/404.php';
    exit;
}

$authorName = trim($usuario['nombre'] . ' ' . $usuario['apellido']);

// Foto de perfil o avatar
if (!empty($usuario['foto_perfil'])) {
    $fotoPerfil = img_url($usuario['foto_perfil']);
} else {
    $iniciales = strtoupper(substr($usuario['nombre'], 0, 1) . substr($usuario['apellido'], 0, 1));
    $fotoPerfil = 'data:image/svg+xml;base64,' . base64_encode("
        <svg width='400' height='400' xmlns='http://www.w3.org/2000/svg'>
            <rect width='400' height='400' fill='#667eea'/>
            <text x='50%' y='50%' font-size='140' fill='white' text-anchor='middle' dy='.35em' font-family='Arial, sans-serif' font-weight='bold'>
                {$iniciales}
            </text>
        </svg>
    ");
}

// 2. Obtener sus posts
$sqlPosts = "
    SELECT p.id, p.title, p.slug, p.content, p.image, p.created_at, p.seo_description,
           c.name as category_name, c.slug as category_slug
    FROM blog_posts p
    LEFT JOIN blog_post_category pc ON pc.post_id = p.id
    LEFT JOIN blog_categories c ON c.id = pc.category_id
    WHERE p.author = ?
      AND p.status = 'published'
      AND p.deleted = 0
    ORDER BY p.created_at DESC
";

try {
    $stmt = db()->prepare($sqlPosts);
    $stmt->execute([$authorName]);
    $posts = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log("Error al cargar posts: " . $e->getMessage());
    $posts = [];
}

// SEO
$page_title = htmlspecialchars($authorName) . " - Columnista | " . $sys['site_name'];
$page_description = "Lee todas las columnas de " . htmlspecialchars($authorName) . " en " . $sys['site_name'];
$page_keywords = htmlspecialchars($authorName) . ", columnista, opinión, " . $sys['site_name'];
$page_author = $authorName;
$page_image = $fotoPerfil;
$currentPath = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$page_canonical = rtrim(URLBASE, '/') . '/' . ltrim($currentPath, '/');


?>

<!-- Inner Page Banner -->
<section class="inner-page-banner bg-common" style="background-image: url('<?= URLBASE ?>/template/NewsEdge/img/banner/inner-page-banner.jpg');">
    <div class="container">
        <div class="row">
            <div class="col-12">
                <div class="breadcrumbs-area">
                    <h1>Columnista</h1>
                    <ul>
                        <li><a href="<​?= URLBASE ?>">Inicio</a></li>
                        <li>Columnistas</li>
                        <li><?= htmlspecialchars($authorName) ?></li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Columnist Profile & Posts -->
<section class="bg-body section-space-less30">
    <div class="container">
        <!-- Header del Columnista -->
        <div class="columnist-profile-header mb-40">
            <div class="row align-items-center">
                <div class="col-xl-3 col-lg-4 col-md-5 col-sm-12 text-center mb-30">
                    <div class="columnist-profile-image">
                        <img src="<​?= $fotoPerfil ?>" 
                             alt="<​?= htmlspecialchars($authorName) ?>"
                             class="img-fluid">
                    </div>
                </div>
                <div class="col-xl-9 col-lg-8 col-md-7 col-sm-12 mb-30">
                    <div class="columnist-profile-info">
                        <span class="columnist-profile-badge">COLUMNISTA</span>
                        <h1 class="columnist-profile-name"><?= htmlspecialchars($authorName) ?></h1>
                        <?php if (!empty($usuario['bio'])): ?>
                            <p class="columnist-profile-bio"><?= nl2br(htmlspecialchars($usuario['bio'])) ?></p>
                        <?php endif; ?>
                        <p class="columnist-profile-stats">
                            <i class="fa fa-file-text-o"></i>
                            <?= count($posts) ?> columna<?= count($posts) !== 1 ? 's' : '' ?> publicada<?= count($posts) !== 1 ? 's' : '' ?>
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Listado de Columnas -->
        <?php if (!empty($posts)): ?>
            <div class="row">
                <div class="col-12 mb-30">
                    <div class="topic-border color-cinnabar">
                        <div class="topic-box-lg color-cinnabar">COLUMNAS PUBLICADAS</div>
                    </div>
                </div>
            </div>
            
            <div class="row">
                <?php foreach ($posts as $post): 
                    $postUrl = URLBASE . "/" . htmlspecialchars($post['category_slug']) . "/" . htmlspecialchars($post['slug']) . "/";
                    $categoryUrl = URLBASE . "/" . htmlspecialchars($post['category_slug']) . "/";
                ?>
                    <div class="col-xl-6 col-lg-6 col-md-12 col-sm-12 mb-30">
                        <div class="columnist-post-card">
                            <div class="columnist-post-image">
                                <a href="<​?= $postUrl ?>">
                                    <img src="<​?= img_url($post['image']) ?>" 
                                         alt="<​?= htmlspecialchars($post['title']) ?>"
                                         class="img-fluid">
                                </a>
                                <?php if (!empty($post['category_name'])): ?>
                                <div class="columnist-post-category">
                                    <a href="<​?= $categoryUrl ?>">
                                        <?= htmlspecialchars($post['category_name']) ?>
                                    </a>
                                </div>
                                <?php endif; ?>
                            </div>
                            <div class="columnist-post-content">
                                <h3 class="columnist-post-title">
                                    <a href="<​?= $postUrl ?>"><?= htmlspecialchars($post['title']) ?></a>
                                </h3>
                                <ul class="columnist-post-meta">
                                    <li>
                                        <i class="fa fa-calendar"></i>
                                        <?= date('d M Y', strtotime($post['created_at'])) ?>
                                    </li>
                                </ul>
                                <p class="columnist-post-excerpt">
                                    <?= truncate_text($post['seo_description'] ?: $post['content'], 150) ?>
                                </p>
                                <a href="<​?= $postUrl ?>" class="columnist-post-link">
                                    Leer columna <i class="fa fa-long-arrow-right"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="no-posts-box">
                <div class="no-posts-icon">
                    <i class="fa fa-file-text-o"></i>
                </div>
                <h3 class="no-posts-title">Sin columnas publicadas</h3>
                <p class="no-posts-text">
                    Este columnista aún no ha publicado ninguna columna.
                </p>
                <a href="<​?= URLBASE ?>" class="btn-back-home">
                    <i class="fa fa-home"></i> Volver al inicio
                </a>
            </div>
        <?php endif; ?>
    </div>
</section>

<style>
    /* Columnist Profile Header */
    .columnist-profile-header {
        background: #fff;
        padding: 40px;
        border-radius: 8px;
        box-shadow: 0 2px 15px rgba(0,0,0,0.08);
    }
    
    .columnist-profile-image {
        width: 250px;
        height: 250px;
        margin: 0 auto;
        border-radius: 50%;
        overflow: hidden;
        border: 5px solid #f5f5f5;
        box-shadow: 0 5px 20px rgba(0,0,0,0.1);
    }
    
    .columnist-profile-image img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }
    
    .columnist-profile-badge {
        display: inline-block;
        background: var(--primary, #c41e3a);
        color: white;
        font-size: 12px;
        font-weight: 700;
        letter-spacing: 1px;
        padding: 6px 15px;
        border-radius: 3px;
        margin-bottom: 15px;
    }
    
    .columnist-profile-name {
        font-size: 36px;
        font-weight: 800;
        color: #111;
        margin: 0 0 15px;
    }
    
    .columnist-profile-bio {
        font-size: 16px;
        color: #666;
        line-height: 1.7;
        margin-bottom: 20px;
    }
    
    .columnist-profile-stats {
        font-size: 15px;
        color: #999;
        margin: 0;
    }
    
    .columnist-profile-stats i {
        margin-right: 8px;
        color: var(--primary, #c41e3a);
    }
    
    /* Columnist Post Card */
    .columnist-post-card {
        background: #fff;
        border-radius: 8px;
        overflow: hidden;
        box-shadow: 0 2px 10px rgba(0,0,0,0.08);
        transition: all 0.3s ease;
        height: 100%;
        display: flex;
        flex-direction: column;
    }
    
    .columnist-post-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 30px rgba(0,0,0,0.15);
    }
    
    .columnist-post-image {
        position: relative;
        overflow: hidden;
    }
    
    .columnist-post-image img {
        width: 100%;
        height: 250px;
        object-fit: cover;
        transition: transform 0.3s ease;
    }
    
    .columnist-post-card:hover .columnist-post-image img {
        transform: scale(1.05);
    }
    
    .columnist-post-category {
        position: absolute;
        top: 15px;
        left: 15px;
        background: var(--primary, #c41e3a);
        padding: 5px 15px;
        border-radius: 3px;
    }
    
    .columnist-post-category a {
        color: white;
        font-size: 12px;
        font-weight: 600;
        text-transform: uppercase;
    }
    
    .columnist-post-content {
        padding: 25px;
        flex: 1;
        display: flex;
        flex-direction: column;
    }
    
    .columnist-post-title {
        font-size: 20px;
        font-weight: 700;
        margin: 0 0 12px;
    }
    
    .columnist-post-title a {
        color: #111;
        transition: color 0.3s ease;
    }
    
    .columnist-post-title a:hover {
        color: var(--primary, #c41e3a);
    }
    
    .columnist-post-meta {
        list-style: none;
        padding: 0;
        margin: 0 0 15px;
        display: flex;
        gap: 15px;
        font-size: 13px;
        color: #999;
    }
    
    .columnist-post-meta i {
        margin-right: 5px;
    }
    
    .columnist-post-excerpt {
        font-size: 15px;
        color: #666;
        line-height: 1.6;
        margin-bottom: 15px;
        flex: 1;
    }
    
    .columnist-post-link {
        color: #000;
        font-weight: 600;
        transition: color 0.3s ease;
        align-self: flex-start;
    }
    
    .columnist-post-link:hover {
        color: var(--primary, #c41e3a);
    }
    
    /* No Posts */
    .no-posts-box {
        background: #fff;
        padding: 80px 40px;
        border-radius: 8px;
        text-align: center;
        box-shadow: 0 2px 10px rgba(0,0,0,0.08);
    }
    
    .no-posts-icon {
        font-size: 80px;
        color: #ddd;
        margin-bottom: 20px;
    }
    
    .no-posts-title {
        font-size: 28px;
        font-weight: 700;
        color: #333;
        margin-bottom: 15px;
    }
    
    .no-posts-text {
        font-size: 16px;
        color: #666;
        margin-bottom: 30px;
    }
    
    .btn-back-home {
        display: inline-block;
        padding: 12px 30px;
        background: #000;
        color: white;
        border-radius: 4px;
        font-weight: 600;
        transition: all 0.3s ease;
    }
    
    .btn-back-home:hover {
        background: var(--primary, #c41e3a);
        color: white;
        text-decoration: none;
    }
    
    /* Responsive */
    @media (max-width: 768px) {
        .columnist-profile-header {
            padding: 30px 20px;
        }
        
        .columnist-profile-image {
            width: 180px;
            height: 180px;
        }
        
        .columnist-profile-name {
            font-size: 28px;
        }
        
        .columnist-post-image img {
            height: 200px;
        }
    }
</style>

