<?php
require_once __DIR__ . '/../../../inc/config.php';

if (!function_exists('img_url')) {
    function img_url(?string $path): string {
        if (empty($path)) return URLBASE . '/template/newsedge/img/placeholder.jpg';
        if (filter_var($path, FILTER_VALIDATE_URL)) return $path;
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

<section class="bg-body section-space-less30">
    <div class="container">
        <div class="ne-main-content">
            <div class="topic-border color-burning-orange mb-30">
                <div class="topic-box-lg color-burning-orange">Publicidad Destacada</div>
            </div>

            <div class="ads-slider-wrapper">
                <!-- Añadimos las clases nativas del tema: ne-carousel nav-control-top-right -->
                <div class="owl-carousel ne-carousel nav-control-top-right" 
                     id="adsOwlSlider" 
                     data-owl-options='{"nav": true, "dots": false, "autoplay": true, "autoplayTimeout": 5000, "smartSpeed": 700, "items": 1}'>
                    
                    <?php foreach ($sliderAds as $ad): ?>
                        <?php $img = img_url($ad['image_url']); ?>
                        <div class="item">
                            <div class="ne-banner-layout1">
                                <?php if (!empty($ad['target_url'])): ?>
                                    <a href="<?= htmlspecialchars($ad['target_url']) ?>" target="_blank" rel="nofollow noopener">
                                        <img src="<?= htmlspecialchars($img) ?>" class="img-fluid width-100 ad-image" alt="Anuncio">
                                    </a>
                                <?php else: ?>
                                    <img src="<?= htmlspecialchars($img) ?>" class="img-fluid width-100 ad-image" alt="Anuncio">
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
</section>

<style>
/* Forzamos que se vea mientras carga */
.ads-slider-wrapper { max-width: 900px; margin: 0 auto; display: block; }
.ad-image { width: 100%; height: 500px; object-fit: cover; border-radius: 4px; }
@media(max-width:768px){ .ad-image { height: 250px; } }

/* Estilo de flechas nativo del tema */
#adsOwlSlider .owl-nav { display: block !important; }
</style>

<!-- SCRIPT DE INICIALIZACIÓN ULTRA-SEGURO -->
<script>
(function($) {
    "use strict";

    var initSlider = function() {
        var slider = $('#adsOwlSlider');
        if (slider.length > 0 && $.fn.owlCarousel) {
            // Si ya estaba inicializado por el tema, lo destruimos para reiniciarlo bien
            if (slider.hasClass('owl-loaded')) {
                slider.trigger('destroy.owl.carousel');
            }
            
            slider.owlCarousel({
                items: 1,
                loop: true,
                autoplay: true,
                autoplayTimeout: 5000,
                autoplayHoverPause: true,
                smartSpeed: 700,
                nav: true,
                dots: true,
                navText: ['<i class="fa fa-angle-left"></i>', '<i class="fa fa-angle-right"></i>']
            });
            console.log("Ads Slider inicializado con éxito");
        } else {
            console.error("Error: OwlCarousel no cargado o elemento no encontrado");
        }
    };

    // Intentar inicializar en diferentes etapas para asegurar
    $(window).on('load', function() {
        initSlider();
    });

    // Por si el load ya pasó
    if (document.readyState === 'complete') {
        initSlider();
    }

})(jQuery);
</script>

