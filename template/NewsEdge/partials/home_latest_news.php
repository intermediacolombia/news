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
        <div class="row no-gutters">
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
                                                    <a href="<?= URLBASE ?>/noticias/post/<?= htmlspecialchars($slide['slug']) ?>">
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
                                            <a href="<?= URLBASE ?>/noticias/post/<?= htmlspecialchars($slide['slug']) ?>" 
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
                <div class="item-box-light-md-less30 ie-full-width">
                    <div class="row">
                        <?php foreach ($sidebarNews as $sideNews): 
                            $sideImageUrl = img_url($sideNews['image']);
                        ?>
                        <div class="media mb-30 col-xl-12 col-lg-6 col-md-6 col-sm-12">
                            <a class="img-opacity-hover" 
                               href="<?= URLBASE ?>/noticias/post/<?= htmlspecialchars($sideNews['slug']) ?>">
                                <img src="<?= $sideImageUrl ?>" 
                                     alt="<?= htmlspecialchars($sideNews['title']) ?>" 
                                     class="img-fluid"
                                     style="max-height: 120px; object-fit: cover; width: 100%;">
                            </a>
                            <div class="media-body media-padding5">
                                <div class="post-date-dark">
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
                                    <a href="<?= URLBASE ?>/noticias/post/<?= htmlspecialchars($sideNews['slug']) ?>">
                                        <?= truncate_text($sideNews['title'], 60) ?>
                                    </a>
                                </h3>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
<!-- Main Slider Section End -->