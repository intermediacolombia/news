<?php
/**
 * Template: Listado de páginas institucionales
 * Ubicación: /template/TU_TEMA/institucional-list.php
 */

// Cargar todas las páginas institucionales publicadas
$sql = "SELECT id, title, slug, page_type, image, seo_description, display_order 
        FROM institutional_pages 
        WHERE status = 'published' 
        ORDER BY display_order ASC, title ASC";
$stmt = db()->query($sql);
$pages = $stmt->fetchAll();

// =======================
// Variables SEO dinámicas
// =======================
$page_title       = "Información Institucional | " . NOMBRE_SITIO;
$page_description = "Conoce más sobre " . NOMBRE_SITIO . ". Nuestra misión, visión, historia y valores corporativos.";
$page_keywords    = NOMBRE_SITIO . ", Quiénes Somos, Misión, Visión, Historia, Valores";
$page_author      = NOMBRE_SITIO;
$page_image       = rtrim(URLBASE, '/') . FAVICON;

$currentPath    = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$page_canonical = rtrim(URLBASE, '/') . '/' . ltrim($currentPath, '/');
// =======================

// Nombres de tipos
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

<div class="container-fluid py-5">
    <div class="container py-5">
        <div class="container-fluid py-3">
            <div class="container-bk">
                <div class="row">
                    <div class="col-lg-8">
                        <div class="bg-light" style="padding: 50px;">
                            
                            <!-- Título principal -->
                            <h1 class="mb-4">Información Institucional</h1>
                            <p class="lead mb-5">
                                Conoce más sobre nuestra organización, nuestra historia y nuestros valores.
                            </p>
                            
                            <?php if(empty($pages)): ?>
                                <div class="alert alert-info">
                                    <i class="fa fa-info-circle"></i>
                                    No hay información institucional disponible en este momento.
                                </div>
                            <?php else: ?>
                                
                                <!-- Grid de páginas -->
                                <div class="row g-4">
                                    <?php foreach($pages as $page): 
                                        $typeName = $typeNames[$page['page_type']] ?? 'General';
                                        $typeIcon = $typeIcons[$page['page_type']] ?? 'fa-file-alt';
                                        $excerpt = $page['seo_description'] ?: substr(strip_tags($page['content'] ?? ''), 0, 150);
                                    ?>
                                    
                                    <div class="col-md-6 col-lg-6">
                                        <div class="card h-100 shadow-sm" style="border: none;">
                                            
                                            <?php if(!empty($page['image'])): ?>
                                                <img src="<?= htmlspecialchars(URLBASE . $page['image']) ?>" 
                                                     class="card-img-top" 
                                                     alt="<?= htmlspecialchars($page['title']) ?>"
                                                     style="height: 200px; object-fit: cover;">
                                            <?php endif; ?>
                                            
                                            <div class="card-body">
                                                <span class="badge bg-primary mb-2">
                                                    <i class="fa <?= $typeIcon ?>"></i> <?= htmlspecialchars($typeName) ?>
                                                </span>
                                                
                                                <h5 class="card-title">
                                                    <?= htmlspecialchars($page['title']) ?>
                                                </h5>
                                                
                                                <p class="card-text text-muted">
                                                    <?= htmlspecialchars(substr($excerpt, 0, 120)) ?>...
                                                </p>
                                                
                                                <a href="<?= URLBASE ?>/institucional/<?= urlencode($page['slug']) ?>" 
                                                   class="btn btn-primary">
                                                    Leer más <i class="fa fa-arrow-right"></i>
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <?php endforeach; ?>
                                </div>
                                
                            <?php endif; ?>
                            
                        </div>
                    </div>
                    
                    <!-- Sidebar -->
                    <div class="col-lg-4">
                        <?php include __DIR__ . '/partials/sidebar.php'; ?>            
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
