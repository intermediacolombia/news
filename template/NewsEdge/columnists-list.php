<?php
/**
 * Template: Listado de columnistas
 * Ubicación: /template/NewsEdge/columnists-list.php
 */

require_once __DIR__ . '/../../inc/config.php';

// Cargar todos los columnistas activos
$sql = "SELECT id, nombre, apellido, username, foto_perfil, bio 
        FROM usuarios 
        WHERE es_columnista = 1 
          AND estado = 0 
          AND borrado = 0
        ORDER BY nombre ASC, apellido ASC";
$stmt = db()->query($sql);
$columnistas = $stmt->fetchAll(PDO::FETCH_ASSOC);

// =======================
// Variables SEO dinámicas
// =======================
$page_title       = "Nuestros Columnistas | " . NOMBRE_SITIO;
$page_description = "Conoce a nuestros columnistas y lee sus análisis y perspectivas más recientes en " . NOMBRE_SITIO;
$page_keywords    = "columnistas, opinión, análisis, " . NOMBRE_SITIO;
$page_author      = NOMBRE_SITIO;
$page_image       = rtrim(URLBASE, '/') . FAVICON;

$currentPath    = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$page_canonical = rtrim(URLBASE, '/') . '/' . ltrim($currentPath, '/');
// =======================

// Helpers
if (!function_exists('img_url')) {
    function img_url(?string $path): string {
        if (empty($path)) return URLBASE . '/template/NewsEdge/img/news/default.jpg';
        if (preg_match('#^https?://#i', $path)) return $path;
        return URLBASE . '/' . ltrim($path, '/');
    }
}

if (!function_exists('get_columnist_avatar')) {
    function get_columnist_avatar($nombre, $apellido, $foto_perfil): string {
        if (!empty($foto_perfil)) {
            return img_url($foto_perfil);
        }
        
        // Generar avatar con iniciales
        $iniciales = strtoupper(substr($nombre, 0, 1) . substr($apellido, 0, 1));
        $colores = ['#667eea', '#764ba2', '#f093fb', '#4facfe', '#43e97b', '#fa709a', '#30cfd0'];
        $color = $colores[array_sum(str_split(ord($nombre[0]))) % count($colores)];
        
        return 'data:image/svg+xml;base64,' . base64_encode("
            <svg width='400' height='400' xmlns='http://www.w3.org/2000/svg'>
                <rect width='400' height='400' fill='{$color}'/>
                <text x='50%' y='50%' font-size='160' fill='white' text-anchor='middle' dy='.35em' font-family='Arial, sans-serif' font-weight='bold'>
                    {$iniciales}
                </text>
            </svg>
        ");
    }
}

// Contar posts por columnista
foreach ($columnistas as &$columnista) {
    $authorName = trim($columnista['nombre'] . ' ' . $columnista['apellido']);
    $stmt = db()->prepare("
        SELECT COUNT(*) 
        FROM blog_posts 
        WHERE author_user = ? 
          AND status = 'published' 
          AND deleted = 0
    ");
    $stmt->execute([$columnista['username']]);
    $columnista['post_count'] = (int)$stmt->fetchColumn();
}
unset($columnista);
?>

<!-- Columnists Section Start -->
<section class="bg-body section-space-less30">
    <div class="container">
        <div class="row">
            <!-- Contenido Principal -->
            <div class="col-lg-12 col-md-12 mb-30">
                <div class="item-box-light-md-less30">
                    
                    <!-- Cabecera -->
                    <div class="topic-border color-cinnabar mb-40">
                        <div class="topic-box-lg color-cinnabar">
                            <i class="fa fa-users" aria-hidden="true"></i>
                            Nuestros Columnistas
                        </div>
                    </div>
                    
                    <div class="columnists-intro mb-40">
                        <p class="size-lg description-body-dark">
                            Conoce a nuestros columnistas, sus perspectivas únicas y análisis sobre los temas más relevantes de la actualidad.
                        </p>
                    </div>
                    
                    <?php if(empty($columnistas)): ?>
                        <div class="item-box-light-md item-shadow-1 p-30 mb-30">
                            <div class="alert-info-custom">
                                <i class="fa fa-info-circle" aria-hidden="true"></i>
                                <span>No hay columnistas disponibles en este momento.</span>
                            </div>
                        </div>
                    <?php else: ?>
                        
                        <!-- Grid de columnistas -->
                        <div class="row">
                            <?php foreach($columnistas as $col): 
                                $nombreCompleto = trim($col['nombre'] . ' ' . $col['apellido']);
                                $avatarUrl = get_columnist_avatar($col['nombre'], $col['apellido'], $col['foto_perfil']);
                                $profileUrl = URLBASE . '/columnista/' . urlencode($col['username']) . '/';
                                $bio = !empty($col['bio']) ? $col['bio'] : 'Columnista de ' . NOMBRE_SITIO;
                            ?>
                            
                            <div class="col-lg-3 col-md-4 col-sm-6 mb-30">
                                <div class="columnist-card item-box-light-md item-shadow-1">
                                    
                                    <!-- Foto del columnista -->
                                    <div class="columnist-card-img position-relative">
                                        <a href="<?= $profileUrl ?>" class="img-opacity-hover">
                                            <img src="<?= $avatarUrl ?>" 
                                                 class="img-fluid" 
                                                 alt="<?= htmlspecialchars($nombreCompleto) ?>"
                                                 style="width: 100%; height: 280px; object-fit: cover;">
                                        </a>
                                        
                                        <!-- Badge con cantidad de columnas -->
                                        <?php if($col['post_count'] > 0): ?>
                                        <div class="columnist-badge">
                                            <div class="topic-box-sm color-cinnabar">
                                                <i class="fa fa-pencil" aria-hidden="true"></i>
                                                <?= $col['post_count'] ?> columna<?= $col['post_count'] !== 1 ? 's' : '' ?>
                                            </div>
                                        </div>
                                        <?php endif; ?>
                                    </div>
                                    
                                    <!-- Información del columnista -->
                                    <div class="columnist-card-body p-25 text-center">
                                        <h3 class="title-semibold-dark size-md mb-10">
                                            <a href="<?= $profileUrl ?>" class="columnist-card-title">
                                                <?= htmlspecialchars($nombreCompleto) ?>
                                            </a>
                                        </h3>
                                        
                                        <p class="columnist-role mb-15">
                                            <i class="fa fa-user-circle" aria-hidden="true"></i>
                                            Columnista
                                        </p>
                                        
                                        <?php if(!empty($col['bio'])): ?>
                                        <p class="description-body-dark columnist-bio mb-20">
                                            <?= htmlspecialchars(substr($bio, 0, 80)) ?><?= strlen($bio) > 80 ? '...' : '' ?>
                                        </p>
                                        <?php endif; ?>
                                        
                                        <div class="columnist-actions">
                                            <a href="<?= $profileUrl ?>" class="btn-columnist">
                                                Ver Perfil
                                                <i class="fa fa-arrow-right" aria-hidden="true"></i>
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <?php endforeach; ?>
                        </div>
                        
                    <?php endif; ?>
                    
                </div>
            </div>
        </div>
    </div>
</section>
<!-- Columnists Section End -->

<style>
    /* ========================================
       ESTILOS PARA TARJETAS DE COLUMNISTAS
       ======================================== */
    
    /* Tarjeta principal */
    .columnist-card {
        border-radius: 4px;
        overflow: hidden;
        height: 100%;
        display: flex;
        flex-direction: column;
        transition: all 0.3s ease;
        background: #fff;
    }
    
    .columnist-card:hover {
        transform: translateY(-8px);
        box-shadow: 0 15px 40px rgba(0,0,0,0.15) !important;
    }
    
    /* Imagen del columnista */
    .columnist-card-img {
        position: relative;
        overflow: hidden;
    }
    
    .columnist-card-img img {
        transition: transform 0.5s ease;
    }
    
    .columnist-card:hover .columnist-card-img img {
        transform: scale(1.05);
    }
    
    /* Badge de columnas */
    .columnist-badge {
        position: absolute;
        bottom: 15px;
        left: 50%;
        transform: translateX(-50%);
        z-index: 2;
    }
    
    /* Cuerpo de la tarjeta */
    .columnist-card-body {
        flex: 1;
        display: flex;
        flex-direction: column;
        align-items: center;
    }
    
    /* Nombre del columnista */
    .columnist-card-title {
        color: #111;
        transition: color 0.3s ease;
        text-decoration: none;
        font-size: 18px;
        font-weight: 600;
    }
    
    .columnist-card:hover .columnist-card-title {
        color: var(--primary);
    }
    
    /* Rol */
    .columnist-role {
        color: #666;
        font-size: 13px;
        text-transform: uppercase;
        letter-spacing: 1px;
        font-weight: 500;
    }
    
    .columnist-role i {
        color: var(--primary);
        margin-right: 5px;
    }
    
    /* Bio */
    .columnist-bio {
        font-size: 14px;
        line-height: 1.6;
        color: #666;
        flex: 1;
        min-height: 60px;
    }
    
    /* Botón de perfil */
    .columnist-actions {
        width: 100%;
        margin-top: auto;
    }
    
    .btn-columnist {
        display: inline-block;
        padding: 10px 25px;
        background: var(--primary);
        color: #fff;
        text-decoration: none;
        border-radius: 4px;
        font-weight: 600;
        font-size: 14px;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        transition: all 0.3s ease;
    }
    
    .btn-columnist:hover {
        background: var(--color-hover-link);
        color: #fff;
        transform: translateX(3px);
    }
    
    .btn-columnist i {
        margin-left: 8px;
        transition: transform 0.3s ease;
    }
    
    .btn-columnist:hover i {
        transform: translateX(5px);
    }
    
    /* Intro section */
    .columnists-intro {
        text-align: center;
        max-width: 800px;
        margin-left: auto;
        margin-right: auto;
    }
    
    /* Alert personalizado */
    .alert-info-custom {
        background: #e3f2fd;
        border-left: 4px solid #2196f3;
        border-radius: 4px;
        padding: 20px;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    
    .alert-info-custom i {
        color: #2196f3;
        font-size: 24px;
        margin-right: 15px;
    }
    
    .alert-info-custom span {
        color: #1976d2;
        font-weight: 500;
        font-size: 16px;
    }
    
    /* Topic box personalizado */
    .topic-box-lg i {
        margin-right: 10px;
    }
    
    /* ========================================
       RESPONSIVE
       ======================================== */
    
    @media (max-width: 991px) {
        .columnist-card-img img {
            height: 250px !important;
        }
    }
    
    @media (max-width: 767px) {
        .columnist-card-img img {
            height: 280px !important;
        }
        
        .columnist-card-body {
            padding: 20px !important;
        }
        
        .columnist-bio {
            min-height: 50px;
        }
    }
    
    @media (max-width: 575px) {
        .col-sm-6 {
            flex: 0 0 100%;
            max-width: 100%;
        }
        
        .columnist-card-img img {
            height: 320px !important;
        }
    }
    
    /* ========================================
       ANIMACIONES
       ======================================== */
    
    @keyframes fadeInUp {
        from {
            opacity: 0;
            transform: translateY(20px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }
    
    .columnist-card {
        animation: fadeInUp 0.5s ease forwards;
    }
    
    /* Delay progresivo para las tarjetas */
    .columnist-card:nth-child(1) { animation-delay: 0.1s; }
    .columnist-card:nth-child(2) { animation-delay: 0.2s; }
    .columnist-card:nth-child(3) { animation-delay: 0.3s; }
    .columnist-card:nth-child(4) { animation-delay: 0.4s; }
    .columnist-card:nth-child(5) { animation-delay: 0.5s; }
    .columnist-card:nth-child(6) { animation-delay: 0.6s; }
    .columnist-card:nth-child(7) { animation-delay: 0.7s; }
    .columnist-card:nth-child(8) { animation-delay: 0.8s; }
</style>