<?php
/**
 * Template: Listado de columnistas
 * Ubicación: /template/NewsEdge/columnists-list.php
 * Diseño: Moderno e Impactante
 */

require_once __DIR__ . '/../../inc/config.php';

// Cargar todos los columnistas activos
$sql = "SELECT id, nombre, apellido, username, foto_perfil 
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

// Contar posts por columnista
foreach ($columnistas as &$columnista) {
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

// Generar colores únicos para cada columnista
function getUniqueColor($nombre) {
    $colors = [
        ['#667eea', '#764ba2'], // Purple gradient
        ['#f093fb', '#f5576c'], // Pink gradient
        ['#4facfe', '#00f2fe'], // Blue gradient
        ['#43e97b', '#38f9d7'], // Green gradient
        ['#fa709a', '#fee140'], // Orange gradient
        ['#30cfd0', '#330867'], // Teal gradient
        ['#a8edea', '#fed6e3'], // Pastel gradient
        ['#ff9a56', '#ff6a88'], // Coral gradient
    ];
    $index = ord($nombre[0]) % count($colors);
    return $colors[$index];
}
?>

<!-- Hero Section -->
<section class="columnist-hero" style="background: linear-gradient(135deg, var(--primary) 0%, var(--color-hover-link) 100%); padding: 80px 0 100px; position: relative; overflow: hidden;">
    <!-- Patrón de fondo animado -->
    <div class="hero-pattern"></div>
    
    <div class="container" style="position: relative; z-index: 2;">
        <div class="row justify-content-center">
            <div class="col-lg-8 text-center">
                <div class="hero-badge mb-20" style="display: inline-block; background: rgba(255,255,255,0.2); padding: 8px 20px; border-radius: 30px; backdrop-filter: blur(10px);">
                    <i class="fa fa-users" style="color: #fff; margin-right: 8px;"></i>
                    <span style="color: #fff; font-size: 13px; font-weight: 600; letter-spacing: 1px; text-transform: uppercase;">Nuestro Equipo</span>
                </div>
                
                <h1 class="hero-title" style="color: #fff; font-size: 48px; font-weight: 700; margin-bottom: 20px; line-height: 1.2;">
                    Conoce a Nuestros <br><span style="position: relative; display: inline-block;">Columnistas
                        <svg style="position: absolute; bottom: -10px; left: 0; width: 100%;" height="12" viewBox="0 0 200 12" fill="none">
                            <path d="M2 10C60 2 140 2 198 10" stroke="#fff" stroke-width="3" stroke-linecap="round"/>
                        </svg>
                    </span>
                </h1>
                
                <p class="hero-description" style="color: rgba(255,255,255,0.9); font-size: 18px; line-height: 1.8; max-width: 600px; margin: 0 auto;">
                    Voces expertas con perspectivas únicas sobre los temas que más importan
                </p>
            </div>
        </div>
    </div>
    
    <!-- Decoración de círculos -->
    <div class="hero-decoration" style="position: absolute; width: 300px; height: 300px; border-radius: 50%; background: rgba(255,255,255,0.1); top: -150px; right: -100px;"></div>
    <div class="hero-decoration" style="position: absolute; width: 200px; height: 200px; border-radius: 50%; background: rgba(255,255,255,0.05); bottom: -100px; left: -50px;"></div>
</section>

<!-- Columnists Grid Section -->
<section class="columnists-grid" style="background: #f8f9fa; padding: 80px 0; margin-top: -40px; position: relative; z-index: 1;">
    <div class="container">
        
        <?php if(empty($columnistas)): ?>
            <div class="row justify-content-center">
                <div class="col-lg-6">
                    <div class="empty-state" style="text-align: center; padding: 60px 40px; background: #fff; border-radius: 16px; box-shadow: 0 10px 40px rgba(0,0,0,0.08);">
                        <i class="fa fa-users" style="font-size: 64px; color: var(--primary); margin-bottom: 20px; opacity: 0.3;"></i>
                        <h3 style="color: #333; margin-bottom: 10px;">No hay columnistas disponibles</h3>
                        <p style="color: #666;">Próximamente agregaremos nuevos columnistas a nuestro equipo.</p>
                    </div>
                </div>
            </div>
        <?php else: ?>
            
            <!-- Contador de columnistas -->
            <div class="text-center mb-50">
                <div class="columnist-count" style="display: inline-block; background: #fff; padding: 12px 30px; border-radius: 50px; box-shadow: 0 4px 20px rgba(0,0,0,0.08);">
                    <span style="color: var(--primary); font-weight: 700; font-size: 24px;"><?= count($columnistas) ?></span>
                    <span style="color: #666; margin-left: 8px; font-size: 15px;">Columnista<?= count($columnistas) !== 1 ? 's' : '' ?> en nuestro equipo</span>
                </div>
            </div>
            
            <!-- Grid de columnistas -->
            <div class="row">
                <?php foreach($columnistas as $index => $col): 
                    $nombreCompleto = trim($col['nombre'] . ' ' . $col['apellido']);
                    $profileUrl = URLBASE . '/columnista/' . urlencode($col['username']) . '/';
                    $gradientColors = getUniqueColor($col['nombre']);
                    
                    // Generar avatar
                    if (!empty($col['foto_perfil'])) {
                        $hasImage = true;
                        $imageUrl = img_url($col['foto_perfil']);
                    } else {
                        $hasImage = false;
                        $iniciales = strtoupper(substr($col['nombre'], 0, 1) . substr($col['apellido'], 0, 1));
                    }
                ?>
                
                <div class="col-lg-3 col-md-4 col-sm-6 mb-40">
                    <div class="columnist-card-modern" data-aos="fade-up" data-aos-delay="<?= $index * 100 ?>">
                        
                        <!-- Foto/Avatar -->
                        <div class="columnist-avatar-wrapper">
                            <a href="<?= $profileUrl ?>" class="columnist-avatar-link">
                                <?php if ($hasImage): ?>
                                    <div class="columnist-avatar-img" style="background-image: url('<?= $imageUrl ?>');"></div>
                                <?php else: ?>
                                    <div class="columnist-avatar-initials" style="background: linear-gradient(135deg, <?= $gradientColors[0] ?> 0%, <?= $gradientColors[1] ?> 100%);">
                                        <span><?= $iniciales ?></span>
                                    </div>
                                <?php endif; ?>
                                
                                <!-- Badge flotante con posts -->
                                <?php if ($col['post_count'] > 0): ?>
                                <div class="columnist-badge-float">
                                    <i class="fa fa-pencil"></i>
                                    <span><?= $col['post_count'] ?></span>
                                </div>
                                <?php endif; ?>
                            </a>
                        </div>
                        
                        <!-- Información -->
                        <div class="columnist-info">
                            <h3 class="columnist-name">
                                <a href="<?= $profileUrl ?>">
                                    <?= htmlspecialchars($nombreCompleto) ?>
                                </a>
                            </h3>
                            
                            <p class="columnist-role">
                                <i class="fa fa-user-circle"></i>
                                Columnista
                            </p>
                            
                            <p class="columnist-stats">
                                <span class="stat-item">
                                    <i class="fa fa-newspaper-o"></i>
                                    <?= $col['post_count'] ?> columna<?= $col['post_count'] !== 1 ? 's' : '' ?>
                                </span>
                            </p>
                            
                            <!-- Botón Ver Perfil -->
                            <a href="<?= $profileUrl ?>" class="btn-columnist-modern">
                                <span>Ver Perfil</span>
                                <i class="fa fa-arrow-right"></i>
                            </a>
                        </div>
                        
                    </div>
                </div>
                
                <?php endforeach; ?>
            </div>
            
        <?php endif; ?>
        
    </div>
</section>

<!-- CTA Section -->
<section class="columnist-cta" style="background: linear-gradient(135deg, #1a1a2e 0%, #16213e 100%); padding: 60px 0;">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-8">
                <h3 style="color: #fff; font-size: 28px; margin-bottom: 10px; font-weight: 600;">
                    ¿Quieres ser parte de nuestro equipo?
                </h3>
                <p style="color: rgba(255,255,255,0.8); font-size: 16px; margin: 0;">
                    Si tienes experiencia y pasión por escribir, únete a nuestro equipo de columnistas.
                </p>
            </div>
            <div class="col-lg-4 text-right text-center-sm">
                <a href="<?= URLBASE ?>/contacto/" class="btn-cta-light" style="display: inline-block; background: #fff; color: #333; padding: 15px 35px; border-radius: 50px; font-weight: 600; text-decoration: none; transition: all 0.3s ease; box-shadow: 0 4px 15px rgba(255,255,255,0.2);">
                    Contáctanos
                    <i class="fa fa-arrow-right" style="margin-left: 10px;"></i>
                </a>
            </div>
        </div>
    </div>
</section>

<style>
    /* ========================================
       HERO SECTION - Moderno e Impactante
       ======================================== */
    
    .columnist-hero {
        position: relative;
    }
    
    .hero-pattern {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background-image: 
            radial-gradient(circle at 20% 50%, rgba(255,255,255,0.1) 1px, transparent 1px),
            radial-gradient(circle at 80% 80%, rgba(255,255,255,0.1) 1px, transparent 1px);
        background-size: 50px 50px;
        opacity: 0.3;
        animation: patternMove 20s linear infinite;
    }
    
    @keyframes patternMove {
        0% { background-position: 0 0; }
        100% { background-position: 50px 50px; }
    }
    
    .hero-title {
        animation: fadeInUp 0.8s ease;
    }
    
    .hero-description {
        animation: fadeInUp 1s ease 0.2s both;
    }
    
    @keyframes fadeInUp {
        from {
            opacity: 0;
            transform: translateY(30px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }
    
    /* ========================================
       TARJETAS DE COLUMNISTAS - Ultra Modernas
       ======================================== */
    
    .columnist-card-modern {
        background: #fff;
        border-radius: 20px;
        padding: 30px;
        text-align: center;
        transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
        box-shadow: 0 4px 20px rgba(0,0,0,0.08);
        position: relative;
        overflow: hidden;
        height: 100%;
    }
    
    .columnist-card-modern:before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 4px;
        background: linear-gradient(90deg, var(--primary), var(--color-hover-link));
        transform: scaleX(0);
        transform-origin: left;
        transition: transform 0.4s ease;
    }
    
    .columnist-card-modern:hover {
        transform: translateY(-10px);
        box-shadow: 0 20px 50px rgba(0,0,0,0.15);
    }
    
    .columnist-card-modern:hover:before {
        transform: scaleX(1);
    }
    
    /* Avatar Circular Moderno */
    .columnist-avatar-wrapper {
        position: relative;
        margin: 0 auto 25px;
        width: 140px;
        height: 140px;
    }
    
    .columnist-avatar-link {
        display: block;
        position: relative;
        width: 100%;
        height: 100%;
    }
    
    .columnist-avatar-link:before {
        content: '';
        position: absolute;
        inset: -6px;
        border-radius: 50%;
        padding: 3px;
        background: linear-gradient(135deg, var(--primary), var(--color-hover-link));
        -webkit-mask: linear-gradient(#fff 0 0) content-box, linear-gradient(#fff 0 0);
        mask: linear-gradient(#fff 0 0) content-box, linear-gradient(#fff 0 0);
        -webkit-mask-composite: xor;
        mask-composite: exclude;
        opacity: 0;
        transition: opacity 0.3s ease;
    }
    
    .columnist-card-modern:hover .columnist-avatar-link:before {
        opacity: 1;
    }
    
    .columnist-avatar-img {
        width: 140px;
        height: 140px;
        border-radius: 50%;
        background-size: cover;
        background-position: center;
        transition: transform 0.4s ease;
        box-shadow: 0 8px 25px rgba(0,0,0,0.15);
    }
    
    .columnist-card-modern:hover .columnist-avatar-img {
        transform: scale(1.05);
    }
    
    .columnist-avatar-initials {
        width: 140px;
        height: 140px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        box-shadow: 0 8px 25px rgba(0,0,0,0.15);
        transition: transform 0.4s ease;
    }
    
    .columnist-avatar-initials span {
        font-size: 52px;
        font-weight: 700;
        color: #fff;
        font-family: 'Roboto', sans-serif;
    }
    
    .columnist-card-modern:hover .columnist-avatar-initials {
        transform: scale(1.05) rotate(5deg);
    }
    
    /* Badge flotante */
    .columnist-badge-float {
        position: absolute;
        bottom: 5px;
        right: 5px;
        background: var(--primary);
        color: #fff;
        width: 42px;
        height: 42px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 13px;
        font-weight: 700;
        box-shadow: 0 4px 15px rgba(0,0,0,0.2);
        border: 3px solid #fff;
        animation: pulse 2s infinite;
    }
    
    .columnist-badge-float i {
        font-size: 12px;
        margin-right: 3px;
    }
    
    @keyframes pulse {
        0%, 100% {
            transform: scale(1);
        }
        50% {
            transform: scale(1.05);
        }
    }
    
    /* Información del columnista */
    .columnist-info {
        padding-top: 10px;
    }
    
    .columnist-name {
        font-size: 20px;
        font-weight: 700;
        margin-bottom: 8px;
        line-height: 1.3;
    }
    
    .columnist-name a {
        color: #1a1a2e;
        text-decoration: none;
        transition: color 0.3s ease;
    }
    
    .columnist-card-modern:hover .columnist-name a {
        color: var(--primary);
    }
    
    .columnist-role {
        color: #666;
        font-size: 13px;
        text-transform: uppercase;
        letter-spacing: 1px;
        font-weight: 600;
        margin-bottom: 12px;
    }
    
    .columnist-role i {
        color: var(--primary);
        margin-right: 5px;
    }
    
    .columnist-stats {
        background: #f8f9fa;
        padding: 10px 15px;
        border-radius: 10px;
        margin: 0 0 20px;
        font-size: 14px;
        color: #555;
    }
    
    .stat-item i {
        color: var(--primary);
        margin-right: 6px;
    }
    
    /* Botón moderno */
    .btn-columnist-modern {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        padding: 12px 28px;
        background: linear-gradient(135deg, var(--primary) 0%, var(--color-hover-link) 100%);
        color: #fff;
        text-decoration: none;
        border-radius: 50px;
        font-weight: 600;
        font-size: 14px;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        transition: all 0.3s ease;
        box-shadow: 0 4px 15px rgba(0,0,0,0.1);
    }
    
    .btn-columnist-modern:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 25px rgba(0,0,0,0.2);
        color: #fff;
    }
    
    .btn-columnist-modern i {
        transition: transform 0.3s ease;
    }
    
    .btn-columnist-modern:hover i {
        transform: translateX(4px);
    }
    
    /* CTA Button */
    .btn-cta-light:hover {
        background: #f0f0f0 !important;
        transform: translateY(-3px);
        box-shadow: 0 8px 25px rgba(255,255,255,0.3) !important;
    }
    
    /* ========================================
       RESPONSIVE
       ======================================== */
    
    @media (max-width: 991px) {
        .hero-title {
            font-size: 36px !important;
        }
        
        .columnist-avatar-wrapper,
        .columnist-avatar-img,
        .columnist-avatar-initials {
            width: 120px;
            height: 120px;
        }
        
        .columnist-avatar-initials span {
            font-size: 44px;
        }
    }
    
    @media (max-width: 767px) {
        .columnist-hero {
            padding: 60px 0 80px !important;
        }
        
        .hero-title {
            font-size: 32px !important;
        }
        
        .hero-description {
            font-size: 16px !important;
        }
        
        .columnist-cta .text-right {
            text-align: center !important;
            margin-top: 20px;
        }
    }
    
    @media (max-width: 575px) {
        .columnist-card-modern {
            padding: 25px 20px;
        }
    }
    
    /* Animaciones de entrada */
    @keyframes fadeInScale {
        from {
            opacity: 0;
            transform: scale(0.9);
        }
        to {
            opacity: 1;
            transform: scale(1);
        }
    }
    
    .columnist-card-modern {
        animation: fadeInScale 0.6s ease backwards;
    }
    
    .columnist-card-modern:nth-child(1) { animation-delay: 0.1s; }
    .columnist-card-modern:nth-child(2) { animation-delay: 0.2s; }
    .columnist-card-modern:nth-child(3) { animation-delay: 0.3s; }
    .columnist-card-modern:nth-child(4) { animation-delay: 0.4s; }
    .columnist-card-modern:nth-child(5) { animation-delay: 0.5s; }
    .columnist-card-modern:nth-child(6) { animation-delay: 0.6s; }
    .columnist-card-modern:nth-child(7) { animation-delay: 0.7s; }
    .columnist-card-modern:nth-child(8) { animation-delay: 0.8s; }
</style>