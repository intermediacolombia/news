<?php
/**
 * Template: Listado de columnistas
 * Ubicación: /template/NewsEdge/columnists-list.php
 * Diseño: Basado en institucional-list.php
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
        if (empty($path)) return URLBASE . '/template/newsedge/img/news/default.jpg';
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
?>

<!-- Institutional Pages Section Start -->
<section class="bg-body section-space-less30">
    <div class="container">
        <div class="row">
            <!-- Contenido Principal -->
            <div class="col-lg-8 col-md-12 mb-30">
                <div class="item-box-light-md-less30">
                    
                    <!-- Cabecera -->
                    <div class="topic-border color-cinnabar mb-40">
                        <div class="topic-box-lg color-cinnabar">Nuestros Columnistas</div>
                    </div>
                    
                    <div class="institutional-intro mb-40">
                        <p class="size-lg description-body-dark">
                            Conoce a nuestros columnistas, sus perspectivas únicas y análisis sobre los temas más relevantes.
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
                                
                                // Generar imagen o avatar
                                if (!empty($col['foto_perfil'])) {
                                    $imagenUrl = img_url($col['foto_perfil']);
                                    $tieneImagen = true;
                                } else {
                                    $imagenUrl = null;
                                    $tieneImagen = false;
                                    $iniciales = strtoupper(substr($col['nombre'], 0, 1) . substr($col['apellido'], 0, 1));
                                }
                                
                                $excerpt = $col['post_count'] . ' columna' . ($col['post_count'] !== 1 ? 's' : '') . ' publicada' . ($col['post_count'] !== 1 ? 's' : '');
                                $profileUrl = URLBASE . '/columnista/' . urlencode($col['username']) . '/';
                            ?>
                            
                            <div class="col-lg-6 col-md-6 col-sm-12 mb-30">
                                <div class="institutional-card item-box-light-md item-shadow-1">
                                    
                                    <?php if($tieneImagen): ?>
                                    <div class="institutional-card-img position-relative">
                                        <a href="<?= $profileUrl ?>" class="img-opacity-hover">
                                            <img src="<?= $imagenUrl ?>" 
                                                 class="img-fluid width-100" 
                                                 alt="<?= htmlspecialchars($nombreCompleto) ?>"
                                                 style="height: 200px; object-fit: cover;">
                                        </a>
                                        <div class="topic-box-top-sm">
                                            <div class="topic-box-sm color-cinnabar">
                                                <i class="fa fa-user-circle" aria-hidden="true"></i>
                                                Columnista
                                            </div>
                                        </div>
                                    </div>
                                    <?php else: ?>
                                    <div class="institutional-card-img institutional-card-no-img position-relative">
                                        <div class="columnist-initials">
                                            <?= $iniciales ?>
                                        </div>
                                        <div class="topic-box-top-sm">
                                            <div class="topic-box-sm color-cinnabar">
                                                <i class="fa fa-user-circle" aria-hidden="true"></i>
                                                Columnista
                                            </div>
                                        </div>
                                    </div>
                                    <?php endif; ?>
                                    
                                    <div class="institutional-card-body p-25">
                                        <h3 class="title-semibold-dark size-lg mb-15">
                                            <a href="<?= $profileUrl ?>" class="institutional-card-title">
                                                <?= htmlspecialchars($nombreCompleto) ?>
                                            </a>
                                        </h3>
                                        
                                        <p class="description-body-dark mb-20 institutional-card-excerpt">
                                            <i class="fa fa-pencil" aria-hidden="true"></i>
                                            <?= htmlspecialchars($excerpt) ?>
                                        </p>
                                        
                                        <div class="more-info-link">
                                            <a href="<?= $profileUrl ?>" class="btn-text-dark">
                                                Ver Perfil
                                                <i class="fa fa-angle-right" aria-hidden="true"></i>
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
            
            <!-- Sidebar -->
            <div class="ne-sidebar sidebar-break-md col-lg-4 col-md-12">
                <?php include __DIR__ . '/partials/sidebar.php'; ?>            
            </div>
        </div>
    </div>
</section>
<!-- Institutional Pages Section End -->

<style>
    /* Tarjetas institucionales */
    .institutional-card {
        border-radius: 4px;
        overflow: hidden;
        height: 100%;
        display: flex;
        flex-direction: column;
        transition: all 0.3s ease;
    }
    
    .institutional-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 15px 40px rgba(0,0,0,0.15) !important;
    }
    
    .institutional-card-img {
        position: relative;
    }
    
    .institutional-card-no-img {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        height: 200px;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    
    .institutional-card-no-img .columnist-initials {
        font-size: 64px;
        font-weight: bold;
        color: rgba(255,255,255,0.9);
        font-family: Arial, sans-serif;
    }
    
    .institutional-card-body {
        flex: 1;
        display: flex;
        flex-direction: column;
    }
    
    .institutional-card-title {
        color: #000;
        transition: color 0.3s ease;
        text-decoration: none;
    }
    
    .institutional-card:hover .institutional-card-title {
        color: var(--primary);
    }
    
    .institutional-card-excerpt {
        line-height: 1.7;
        flex: 1;
    }
    
    .institutional-card-excerpt i {
        color: var(--primary);
        margin-right: 8px;
    }
    
    .btn-text-dark {
        color: #000;
        font-weight: 600;
        text-decoration: none;
        transition: all 0.3s ease;
        display: inline-block;
    }
    
    .btn-text-dark:hover {
        color: var(--primary);
        padding-left: 5px;
    }
    
    .btn-text-dark i {
        transition: transform 0.3s ease;
        margin-left: 5px;
    }
    
    .btn-text-dark:hover i {
        transform: translateX(5px);
    }
    
    /* Alert personalizado */
    .alert-info-custom {
        background: #e3f2fd;
        border-left: 4px solid #2196f3;
        border-radius: 4px;
        padding: 15px;
        display: flex;
        align-items: center;
    }
    
    .alert-info-custom i {
        color: #2196f3;
        font-size: 20px;
        margin-right: 10px;
    }
    
    .alert-info-custom span {
        color: #1976d2;
        font-weight: 500;
    }
    
    /* Responsive adjustments */
    @media (max-width: 768px) {
        .institutional-card-body {
            padding: 20px !important;
        }
        
        .institutional-card-img,
        .institutional-card-no-img {
            height: 180px !important;
        }
        
        .institutional-card-no-img .columnist-initials {
            font-size: 48px;
        }
    }
</style>