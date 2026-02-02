<?php
/* ===== Helper para generar URL de imágenes ===== */
if (!function_exists('img_url')) {
    function img_url(?string $path): string {
        $path = trim((string)$path);

        // Fallback si viene vacío
        if ($path === '') {
            return URLBASE . '/template/NewsEdge/img/banner/slide1.jpg';
        }

        // Si ya es URL absoluta (http/https) la devolvemos tal cual
        if (preg_match('~^https?://~i', $path)) {
            return $path;
        }

        // Si ya viene empezando con / lo pegamos a URLBASE
        if (strpos($path, '/') === 0) {
            return URLBASE . $path;
        }

        // Caso típico: ruta relativa guardada en DB (ej: uploads/..., img/..., etc.)
        return URLBASE . '/' . $path;
    }
}

/* ===== Helper para truncar texto ===== */
if (!function_exists('truncate_text')) {
    function truncate_text(string $text, int $maxLength = 80): string {
        $text = htmlspecialchars($text, ENT_QUOTES, 'UTF-8');
        if (mb_strlen($text) <= $maxLength) {
            return $text;
        }
        return mb_substr($text, 0, $maxLength) . '...';
    }
}

/* ===== Consulta: Noticias destacadas para el slider (últimas 3) ===== */
$sqlSlider = "
  SELECT p.id, p.title, p.slug, p.image, p.created_at, p.author,
         c.name AS category_name, c.slug AS category_slug
  FROM blog_posts p
  LEFT JOIN blog_post_category pc ON pc.post_id = p.id
  LEFT JOIN blog_categories c ON c.id = pc.category_id
  WHERE p.status = 'published' AND p.deleted = 0
  GROUP BY p.id
  ORDER BY p.created_at DESC
  LIMIT 3
";
$sliderNews = db()->query($sqlSlider)->fetchAll();

/* ===== Consulta: Noticias laterales (4 siguientes) ===== */
$sqlSidebar = "
  SELECT p.id, p.title, p.slug, p.image, p.created_at, p.author,
         c.name AS category_name, c.slug AS category_slug
  FROM blog_posts p
  LEFT JOIN blog_post_category pc ON pc.post_id = p.id
  LEFT JOIN blog_categories c ON c.id = pc.category_id
  WHERE p.status = 'published' AND p.deleted = 0
  GROUP BY p.id
  ORDER BY p.created_at DESC
  LIMIT 4 OFFSET 3
";
$sidebarNews = db()->query($sqlSidebar)->fetchAll();
?>

<!-- Main Slider Section Start -->
<section class="section-space-bottom">
    <div class="container">
        <div class="row no-gutters slider-row-equal-height">
            <!-- Slider Principal -->
            <div class="col-xl-8 col-lg-12">
                <div class="main-slider1 img-overlay-slider">
                    <div class="bend niceties preview-1">
                        <div id="ensign-nivoslider-3" class="slides">
                            <?php 
                            $slideIndex = 1;
                            foreach ($sliderNews as $slide): 
                                $imageUrl = img_url($slide['image']);
                            ?>
                                <img src="<?= $imageUrl ?>" 
                                     alt="<?= htmlspecialchars($slide['title']) ?>" 
                                     title="#slider-direction-<?= $slideIndex ?>" />
                            <?php 
                                $slideIndex++;
                            endforeach; 
                            ?>
                        </div>

                        <!-- Direcciones del Slider -->
                        <?php 
                        $slideIndex = 1;
                        foreach ($sliderNews as $slide): 
                        ?>
                        <div id="slider-direction-<?= $slideIndex ?>" class="t-cn slider-direction">
                            <div class="slider-content s-tb slide-<?= $slideIndex ?>">
                                <div class="title-container s-tb-c">
                                    <div class="text-left pl-50 pl20-xs">
                                        <?php if (!empty($slide['category_name'])): ?>
                                        <div class="topic-box-sm color-cinnabar mb-20">
                                            <?= htmlspecialchars($slide['category_name']) ?>
                                        </div>
                                        <?php endif; ?>
                                        
                                        <div class="post-date-light d-none d-sm-block">
                                            <ul>
                                                <?php if (!empty($slide['author'])): ?>
                                                <li>
                                                    <span>por</span>
                                                    <a href="<?= URLBASE ?>/<?= htmlspecialchars($slide['category_slug']) ?>/<?= htmlspecialchars($slide['slug']) ?>/">
                                                        <?= htmlspecialchars($slide['author']) ?>
                                                    </a>
                                                </li>
                                                <?php endif; ?>
                                                <li>
                                                    <span>
                                                        <i class="fa fa-calendar" aria-hidden="true"></i>
                                                    </span>
                                                    <?= date('d M, Y', strtotime($slide['created_at'])) ?>
                                                </li>
                                            </ul>
                                        </div>
                                        
                                        <div class="slider-title">
                                            <a href="<?= URLBASE ?>/<?= htmlspecialchars($slide['category_slug']) ?>/<?= htmlspecialchars($slide['slug']) ?>/" 
                                               class="text-white">
                                                <?= truncate_text($slide['title'], 100) ?>
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <?php 
                            $slideIndex++;
                        endforeach; 
                        ?>
                    </div>
                </div>
            </div>

            <!-- Noticias Laterales -->
            <div class="col-xl-4 col-lg-12">
                <div class="sidebar-news-container">
                    <?php foreach ($sidebarNews as $index => $sideNews): 
                        $sideImageUrl = img_url($sideNews['image']);
                    ?>
                    <div class="sidebar-news-item <?= $index === count($sidebarNews) - 1 ? 'last-item' : '' ?>">
                        <div class="media">
                            <a class="img-opacity-hover sidebar-news-img" 
                               href="<?= URLBASE ?>/<?= htmlspecialchars($sideNews['category_slug']) ?>/<?= htmlspecialchars($sideNews['slug']) ?>/">
                                <img src="<?= $sideImageUrl ?>" 
                                     alt="<?= htmlspecialchars($sideNews['title']) ?>" 
                                     class="img-fluid">
                            </a>
                            <div class="media-body">
                                <div class="post-date-dark mb-5">
                                    <ul>
                                        <li>
                                            <span>
                                                <i class="fa fa-calendar" aria-hidden="true"></i>
                                            </span>
                                            <?= date('d M, Y', strtotime($sideNews['created_at'])) ?>
                                        </li>
                                    </ul>
                                </div>
                                <h3 class="title-medium-dark size-md mb-none">
                                    <a href="<?= URLBASE ?>/<?= htmlspecialchars($sideNews['category_slug']) ?>/<?= htmlspecialchars($sideNews['slug']) ?>/">
                                        <?= truncate_text($sideNews['title'], 60) ?>
                                    </a>
                                </h3>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
</section>
<!-- Main Slider Section End -->

<style>
/* =============================================
   SLIDER CON ALTURA UNIFORME
   ============================================= */

/* Hacer que ambas columnas tengan la misma altura */
.slider-row-equal-height {
    display: flex;
    flex-wrap: wrap;
}

.slider-row-equal-height > [class*='col-'] {
    display: flex;
    flex-direction: column;
}

/* Asegurar que el slider tenga altura fija */
.main-slider1,
.main-slider1 .bend,
.main-slider1 #ensign-nivoslider-3,
.main-slider1 #ensign-nivoslider-3 img,
.main-slider1 .slider-direction {
    height: 500px !important;
}

.main-slider1 #ensign-nivoslider-3 img {
    object-fit: cover;
    object-position: center;
}

/* Container de noticias laterales - MISMA ALTURA que el slider */
.sidebar-news-container {
    height: 500px;
    display: flex;
    flex-direction: column;
    background: #fff;
    overflow: hidden;
}

/* Cada item de noticia lateral ocupa 25% del espacio (4 items = 100%) */
.sidebar-news-item {
    flex: 1;
    display: flex;
    flex-direction: column;
    border-bottom: 1px solid #e8e8e8;
    padding: 20px;
    transition: all 0.3s ease;
}

.sidebar-news-item.last-item {
    border-bottom: none;
}

.sidebar-news-item:hover {
    background: #f8f9fa;
}

/* Media object dentro de cada item */
.sidebar-news-item .media {
    display: flex;
    gap: 15px;
    height: 100%;
    align-items: flex-start;
}

/* Imagen lateral - tamaño fijo */
.sidebar-news-img {
    flex-shrink: 0;
    width: 100px;
    height: 100px;
    overflow: hidden;
    border-radius: 4px;
    display: block;
}

.sidebar-news-img img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: transform 0.3s ease;
}

.sidebar-news-item:hover .sidebar-news-img img {
    transform: scale(1.05);
}

/* Body del media */
.sidebar-news-item .media-body {
    flex: 1;
    display: flex;
    flex-direction: column;
    justify-content: flex-start;
}

/* Ajustar títulos para que no se desborden */
.sidebar-news-item .title-medium-dark {
    font-size: 15px;
    line-height: 1.4;
    display: -webkit-box;
    -webkit-line-clamp: 3;
    -webkit-box-orient: vertical;
    overflow: hidden;
}

.sidebar-news-item .post-date-dark ul {
    margin-bottom: 8px;
}

.sidebar-news-item .post-date-dark li {
    font-size: 12px;
}

/* =============================================
   RESPONSIVE
   ============================================= */

/* Tablets */
@media (max-width: 1199px) {
    .main-slider1,
    .main-slider1 .bend,
    .main-slider1 #ensign-nivoslider-3,
    .main-slider1 #ensign-nivoslider-3 img,
    .main-slider1 .slider-direction {
        height: 450px !important;
    }
    
    .sidebar-news-container {
        height: auto;
        margin-top: 30px;
    }
    
    .sidebar-news-item {
        min-height: 120px;
    }
    
    .slider-row-equal-height > [class*='col-'] {
        flex: none;
    }
}

/* Móviles grandes */
@media (max-width: 991px) {
    .main-slider1,
    .main-slider1 .bend,
    .main-slider1 #ensign-nivoslider-3,
    .main-slider1 #ensign-nivoslider-3 img,
    .main-slider1 .slider-direction {
        height: 400px !important;
    }
}

/* Móviles */
@media (max-width: 767px) {
    .main-slider1,
    .main-slider1 .bend,
    .main-slider1 #ensign-nivoslider-3,
    .main-slider1 #ensign-nivoslider-3 img,
    .main-slider1 .slider-direction {
        height: 350px !important;
    }
    
    .sidebar-news-img {
        width: 80px;
        height: 80px;
    }
    
    .sidebar-news-item .title-medium-dark {
        font-size: 14px;
    }
}

/* Móviles pequeños */
@media (max-width: 575px) {
    .main-slider1,
    .main-slider1 .bend,
    .main-slider1 #ensign-nivoslider-3,
    .main-slider1 #ensign-nivoslider-3 img,
    .main-slider1 .slider-direction {
        height: 280px !important;
    }
    
    .sidebar-news-item {
        padding: 15px;
    }
}
</style>