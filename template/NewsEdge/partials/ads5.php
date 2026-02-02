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

<?php if ($sliderAds): ?>
<section class="bg-body section-space-less30">
  <div class="container">

    <div class="ne-main-content">
      <div class="topic-border color-burning-orange mb-30">
        <div class="topic-box-lg color-burning-orange">Publicidad Destacada</div>
      </div>

      <div class="ads-slider-wrapper">
        <div class="owl-carousel owl-theme" id="adsOwlSlider">
          <?php foreach ($sliderAds as $ad): ?>
            <?php $img = img_url($ad['image_url']); ?>
            <div class="item">
              <?php if (!empty($ad['target_url'])): ?>
                <a href="<?= htmlspecialchars($ad['target_url']) ?>" target="_blank" rel="nofollow noopener">
                  <img src="<?= htmlspecialchars($img) ?>" class="img-fluid width-100 ad-image" alt="Anuncio destacado">
                </a>
              <?php else: ?>
                <img src="<?= htmlspecialchars($img) ?>" class="img-fluid width-100 ad-image" alt="Anuncio destacado">
              <?php endif; ?>
            </div>
          <?php endforeach; ?>
        </div>
      </div>

    </div>

  </div>
</section>

<style>
.ads-slider-wrapper{max-width:900px;margin:0 auto;}

/* CLAVE: si el tema oculta owl antes de inicializar, lo anulamos */
#adsOwlSlider{display:block !important; opacity:1 !important; visibility:visible !important;}

/* Si Owl no inicia, al menos se verán como lista vertical */
#adsOwlSlider .item{margin-bottom:15px;}

#adSowlSlider.owl-loaded .item{margin-bottom:0;} /* cuando ya cargue, quitamos margen */

.ad-image{
  width:100%;
  height:500px;
  object-fit:cover;
  border-radius:8px;
}
@media(max-width:768px){.ad-image{height:300px;}}
</style>

<script>
jQuery(function($){
  // Si owl no existe, no rompemos la página; al menos se ven las imágenes.
  if (!$.fn || typeof $.fn.owlCarousel !== 'function') {
    console.warn('Owl Carousel NO está disponible en esta página.');
    return;
  }

  var $el = $('#adsOwlSlider');
  if (!$el.length) return;

  // Evita doble inicialización
  if ($el.hasClass('owl-loaded')) return;

  $el.owlCarousel({
    items: 1,
    loop: <?= count($sliderAds) > 1 ? 'true' : 'false' ?>,
    autoplay: <?= count($sliderAds) > 1 ? 'true' : 'false' ?>,
    autoplayTimeout: 5000,
    autoplayHoverPause: true,
    nav: <?= count($sliderAds) > 1 ? 'true' : 'false' ?>,
    dots: <?= count($sliderAds) > 1 ? 'true' : 'false' ?>,
    smartSpeed: 700,
    navText: ['<span class="fa fa-angle-left"></span>','<span class="fa fa-angle-right"></span>']
  });
});
</script>
<?php endif; ?>

