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
                <!-- Añadimos 'owl-loaded' manualmente si es necesario y forzamos el display -->
                <div class="owl-carousel owl-theme" id="adsOwlSlider">
                    <?php foreach ($sliderAds as $ad): ?>
                        <?php $img = img_url($ad['image_url']); ?>
                        <div class="item">
                            <div class="ne-banner-slide-container">
                                <?php if (!empty($ad['target_url'])): ?>
                                    <a href="<​?= htmlspecialchars($ad['target_url']) ?>" target="_blank" rel="nofollow noopener">
                                        <img src="<​?= htmlspecialchars($img) ?>" class="ad-image-slider" alt="Anuncio">
                                    </a>
                                <?php else: ?>
                                    <img src="<​?= htmlspecialchars($img) ?>" class="ad-image-slider" alt="Anuncio">
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
/* FORZADO DE ESTRUCTURA OWL (Esto arregla el 'apeñuscado') */
.ads-slider-wrapper { 
    max-width: 900px; 
    margin: 0 auto; 
    position: relative;
    display: block !important;
}

/* Forzamos que cada item ocupe el 100% del carrusel */
#adsOwlSlider .owl-item {
    float: left;
    width: 100%;
}

#adsOwlSlider .item {
    width: 100%;
    display: block;
}

.ad-image-slider {
    width: 100% !important; /* Owl necesita width 100% para calcular */
    height: 500px;
    object-fit: cover;
    border-radius: 8px;
    display: block;
}

/* Estilo de navegación (Flechas) */
#adsOwlSlider .owl-nav button {
    position: absolute;
    top: 50%;
    transform: translateY(-50%);
    background: rgba(0,0,0,0.5) !important;
    color: #fff !important;
    width: 40px;
    height: 40px;
    border-radius: 50%;
}
#adsOwlSlider .owl-nav .owl-prev { left: -20px; }
#adsOwlSlider .owl-nav .owl-next { right: -20px; }

@media(max-width:768px){ 
    .ad-image-slider { height: 250px; } 
    #adsOwlSlider .owl-nav { display: none; }
}
</style>

<script>
// Usamos un intervalo para esperar a que Owl esté listo si el load falla
var retryOwl = setInterval(function() {
    if (typeof jQuery !== 'undefined' && jQuery.fn.owlCarousel) {
        var $slider = jQuery('#adsOwlSlider');
        
        $slider.owlCarousel({
            items: 1,
            loop: true,
            margin: 0,
            nav: true,
            dots: true,
            autoplay: true,
            autoplayTimeout: 5000,
            smartSpeed: 800,
            navText: ['<i class="fa fa-angle-left"></i>', '<i class="fa fa-angle-right"></i>'],
            responsive: {
                0: { items: 1 },
                600: { items: 1 },
                1000: { items: 1 }
            }
        });
        
        console.log("Owl Ads: Inicializado");
        clearInterval(retryOwl); // Detenemos la búsqueda una vez funciona
    }
}, 500); // Reintenta cada medio segundo
</script>

