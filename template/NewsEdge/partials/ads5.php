<?php
require_once __DIR__ . '/../../../inc/config.php';

$stmt = db()->query("
    SELECT * FROM ads_gallery 
    WHERE section = 5 AND type = 'square' AND status = 'active' 
    ORDER BY created_at DESC
");
$sliderAds = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<?php if ($sliderAds): ?>
<!-- Advertisement Slider Section Start -->
<section class="bg-body section-space-less30">
    <div class="container">
        <div class="ne-main-content">
            <!-- Título de sección -->
            <div class="topic-border color-burning-orange mb-30">
                <div class="topic-box-lg color-burning-orange">Publicidad Destacada</div>
            </div>

            <!-- Carrusel de Banners con Owl Carousel -->
            <div class="ads-slider-wrapper">
                <div class="owl-carousel owl-theme" id="adsOwlSlider">
                    <?php foreach ($sliderAds as $ad): 
                        $adImage = img_url($ad['image_url']);
                    ?>
                        <div class="item">
                            <div class="ne-banner-slide">
                                <?php if (!empty($ad['target_url'])): ?>
                                    <a href="<?= htmlspecialchars($ad['target_url']) ?>" 
                                       target="_blank" 
                                       rel="nofollow noopener">
                                        <img src="<?= $adImage ?>" 
                                             alt="Anuncio destacado" 
                                             class="img-fluid width-100 ad-image">
                                    </a>
                                <?php else: ?>
                                    <img src="<?= $adImage ?>" 
                                         alt="Anuncio destacado" 
                                         class="img-fluid width-100 ad-image">
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

<!-- Estilos personalizados -->
<style>
.ads-slider-wrapper {
    max-width: 900px;
    margin: 0 auto;
    position: relative;
}

.ne-banner-slide {
    overflow: hidden;
    border-radius: 8px;
    box-shadow: 0 4px 20px rgba(0,0,0,0.12);
    transition: all 0.3s ease;
}

.ne-banner-slide:hover {
    box-shadow: 0 8px 30px rgba(0,0,0,0.2);
}

.ad-image {
    height: 500px;
    object-fit: cover;
    transition: transform 0.6s ease, filter 0.4s ease;
    filter: brightness(96%);
}

.ne-banner-slide:hover .ad-image {
    transform: scale(1.05);
    filter: brightness(100%);
}

/* Controles personalizados de Owl Carousel */
#adsOwlSlider .owl-nav {
    position: absolute;
    top: 50%;
    width: 100%;
    transform: translateY(-50%);
    pointer-events: none;
}

#adsOwlSlider .owl-nav button.owl-prev,
#adsOwlSlider .owl-nav button.owl-next {
    position: absolute;
    width: 50px;
    height: 50px;
    background: rgba(0,0,0,0.6) !important;
    color: #fff !important;
    border-radius: 50%;
    font-size: 28px;
    line-height: 50px;
    text-align: center;
    transition: all 0.3s ease;
    opacity: 0;
    pointer-events: all;
}

.ads-slider-wrapper:hover #adsOwlSlider .owl-nav button {
    opacity: 1;
}

#adsOwlSlider .owl-nav button.owl-prev {
    left: 20px;
}

#adsOwlSlider .owl-nav button.owl-next {
    right: 20px;
}

#adsOwlSlider .owl-nav button:hover {
    background: rgba(0,0,0,0.85) !important;
    transform: scale(1.1);
}

/* Indicadores (dots) personalizados */
#adsOwlSlider .owl-dots {
    text-align: center;
    margin-top: 25px;
}

#adsOwlSlider .owl-dot {
    display: inline-block;
    width: 12px;
    height: 12px;
    background: #ddd;
    border-radius: 50%;
    margin: 0 6px;
    transition: all 0.3s ease;
}

#adsOwlSlider .owl-dot.active {
    background: #ff6b35;
    width: 35px;
    border-radius: 6px;
}

#adsOwlSlider .owl-dot:hover {
    background: #ff8c5a;
}

/* Responsive */
@media (max-width: 768px) {
    .ad-image {
        height: 300px;
    }
    
    #adsOwlSlider .owl-nav button.owl-prev,
    #adsOwlSlider .owl-nav button.owl-next {
        width: 40px;
        height: 40px;
        font-size: 22px;
        line-height: 40px;
        opacity: 1;
    }
    
    #adsOwlSlider .owl-nav button.owl-prev {
        left: 10px;
    }
    
    #adsOwlSlider .owl-nav button.owl-next {
        right: 10px;
    }
}
</style>

<!-- Script de inicialización de Owl Carousel -->
<script>
jQuery(document).ready(function($) {
    $('#adsOwlSlider').owlCarousel({
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
                items: 1,
                nav: true,
                dots: true
            },
            768: {
                items: 1,
                nav: true,
                dots: true
            },
            1024: {
                items: 1,
                nav: true,
                dots: true
            }
        }
    });
});
</script>
<?php endif; ?>

