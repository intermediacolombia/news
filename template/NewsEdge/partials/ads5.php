<?php
if (!function_exists('img_url')) {
    function img_url(?string $path): string {
        if (empty($path)) {
            return URLBASE . '/template/newsedge/img/placeholder.jpg';
        }
        if (filter_var($path, FILTER_VALIDATE_URL)) {
            return $path;
        }
        return URLBASE . '/' . ltrim($path, '/');
    }
}

$stmt = db()->query("
    SELECT * FROM ads_gallery 
    WHERE section = 5 AND type = 'square' AND status = 'active' 
    ORDER BY created_at DESC
");
$sliderAds = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<?php if ($sliderAds): ?>
<!-- Advertisement Slider Section Start -->
<section class="section-space-less30">
    <div class="container">
        <div class="ne-main-content">
            <!-- Título de sección -->
            <div class="topic-border color-burning-orange mb-30">
                <div class="topic-box-lg color-burning-orange">Publicidad Destacada</div>
            </div>

            <!-- Carrusel de Banners -->
            <div class="ne-ads-slider-wrapper">
                <div class="owl-carousel ne-ads-slider" id="adsOwlCarousel">
                    <?php foreach ($sliderAds as $ad): 
                        $adImage = img_url($ad['image_url']);
                    ?>
                        <div class="item">
                            <div class="ne-banner-slide">
                                <?php if (!empty($ad['target_url'])): ?>
                                    <a href="<​?= htmlspecialchars($ad['target_url']) ?>" 
                                       target="_blank" 
                                       rel="nofollow noopener">
                                        <img src="<​?= $adImage ?>" 
                                             alt="Anuncio destacado" 
                                             class="img-fluid width-100">
                                    </a>
                                <?php else: ?>
                                    <img src="<​?= $adImage ?>" 
                                         alt="Anuncio destacado" 
                                         class="img-fluid width-100">
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
</section>
<!-- Advertisement Slider Section End -->

<!-- Estilos personalizados para el slider de anuncios -->
<style>
.ne-ads-slider-wrapper {
    position: relative;
    max-width: 900px;
    margin: 0 auto;
}

.ne-banner-slide {
    overflow: hidden;
    border-radius: 6px;
    box-shadow: 0 4px 15px rgba(0,0,0,0.1);
    transition: all 0.3s ease;
}

.ne-banner-slide:hover {
    box-shadow: 0 8px 25px rgba(0,0,0,0.2);
    transform: translateY(-3px);
}

.ne-banner-slide img {
    transition: transform 0.5s ease, filter 0.3s ease;
    filter: brightness(98%);
    height: 500px;
    object-fit: cover;
}

.ne-banner-slide:hover img {
    transform: scale(1.05);
    filter: brightness(102%);
}

/* Personalización de controles Owl Carousel */
.ne-ads-slider .owl-nav button.owl-prev,
.ne-ads-slider .owl-nav button.owl-next {
    position: absolute;
    top: 50%;
    transform: translateY(-50%);
    background: rgba(0,0,0,0.5) !important;
    color: #fff !important;
    width: 45px;
    height: 45px;
    border-radius: 50%;
    font-size: 24px;
    line-height: 45px;
    text-align: center;
    transition: all 0.3s ease;
    opacity: 0;
}

.ne-ads-slider-wrapper:hover .owl-nav button {
    opacity: 1;
}

.ne-ads-slider .owl-nav button.owl-prev {
    left: 15px;
}

.ne-ads-slider .owl-nav button.owl-next {
    right: 15px;
}

.ne-ads-slider .owl-nav button:hover {
    background: rgba(0,0,0,0.8) !important;
    transform: translateY(-50%) scale(1.1);
}

/* Personalización de dots (indicadores) */
.ne-ads-slider .owl-dots {
    text-align: center;
    margin-top: 20px;
}

.ne-ads-slider .owl-dot {
    display: inline-block;
    width: 12px;
    height: 12px;
    background: #ddd;
    border-radius: 50%;
    margin: 0 5px;
    transition: all 0.3s ease;
}

.ne-ads-slider .owl-dot.active {
    background: #ff6b35; /* Color naranja del tema */
    width: 30px;
    border-radius: 6px;
}

.ne-ads-slider .owl-dot:hover {
    background: #ff8c5a;
}

/* Responsive /
@media (max-width: 768px) {
    .ne-banner-slide img {
        height: 300px;
    }
    
    .ne-ads-slider .owl-nav button.owl-prev,
    .ne-ads-slider .owl-nav button.owl-next {
        width: 35px;
        height: 35px;
        line-height: 35px;
        font-size: 18px;
    }
}
</style>

<!-- Script de inicialización del carrusel -->
<script>
jQuery(document).ready(function($) {
    $('#adsOwlCarousel').owlCarousel({
        items: 1,
        loop: true,
        autoplay: true,
        autoplayTimeout: 5000,
        autoplayHoverPause: true,
        nav: true,
        navText: ['<i class="fa fa-angle-left"></i>', '<i class="fa fa-angle-right"></i>'],
        dots: true,
        smartSpeed: 800,
        animateOut: 'fadeOut',
        animateIn: 'fadeIn',
        responsive: {
            0: {
                nav: false
            },
            768: {
                nav: true
            }
        }
    });
});
</script>
<?php endif; ?>

