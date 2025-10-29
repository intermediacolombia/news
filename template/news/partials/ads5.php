<?php
require_once __DIR__ . '/../../../inc/config.php';

$stmt = $pdo->query("
    SELECT * FROM ads_gallery 
    WHERE section = 5 AND type = 'square' AND status = 'active' 
    ORDER BY created_at DESC
");
$sliderAds = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<?php if ($sliderAds): ?>
<section class="ads-slider-section my-5">
  <div id="adsSlider" class="carousel slide shadow-lg rounded-4 overflow-hidden" data-bs-ride="carousel">
    
    <div class="carousel-inner">
      <?php foreach ($sliderAds as $i => $ad): ?>
        <div class="carousel-item <?= $i === 0 ? 'active' : '' ?>">
          <?php if (!empty($ad['target_url'])): ?>
            <a href="<?= htmlspecialchars($ad['target_url']) ?>" target="_blank" rel="nofollow">
              <img src="<?= htmlspecialchars($ad['image_url']) ?>" 
                   class="d-block w-100 ad-image" 
                   alt="Anuncio destacado">
            </a>
          <?php else: ?>
            <img src="<?= htmlspecialchars($ad['image_url']) ?>" 
                 class="d-block w-100 ad-image" 
                 alt="Anuncio destacado">
          <?php endif; ?>
        </div>
      <?php endforeach; ?>
    </div>

    <!-- Indicadores -->
    <div class="carousel-indicators mb-0">
      <?php foreach ($sliderAds as $i => $ad): ?>
        <button type="button" data-bs-target="#adsSlider" data-bs-slide-to="<?= $i ?>" 
                class="<?= $i === 0 ? 'active' : '' ?>" 
                aria-current="<?= $i === 0 ? 'true' : 'false' ?>"></button>
      <?php endforeach; ?>
    </div>

    <!-- Controles -->
    <button class="carousel-control-prev" type="button" data-bs-target="#adsSlider" data-bs-slide="prev">
      <span class="carousel-control-prev-icon" aria-hidden="true"></span>
      <span class="visually-hidden">Anterior</span>
    </button>
    <button class="carousel-control-next" type="button" data-bs-target="#adsSlider" data-bs-slide="next">
      <span class="carousel-control-next-icon" aria-hidden="true"></span>
      <span class="visually-hidden">Siguiente</span>
    </button>
  </div>
</section>

<!-- ESTILOS PERSONALIZADOS -->
<style>
.ads-slider-section {
  max-width: 900px;
  margin: 0 auto;
  border-radius: 1rem;
}

.ad-image {
  transition: transform .6s ease, filter .4s ease;
  filter: brightness(95%);
}

.ad-image:hover {
  transform: scale(1.03);
  filter: brightness(105%);
}

/* Indicadores modernos */
.carousel-indicators [data-bs-target] {
  width: 12px;
  height: 12px;
  border-radius: 50%;
  background-color: rgba(255, 255, 255, .6);
  transition: background-color .3s ease;
  margin: 5px;
}

.carousel-indicators .active {
  background-color: #00c853; /* verde moderno */
}

/* Botones de control más elegantes */
.carousel-control-prev-icon,
.carousel-control-next-icon {
  background-color: rgba(0,0,0,0.4);
  border-radius: 50%;
  padding: 1rem;
  background-size: 60%;
  transition: background-color .3s ease;
}

.carousel-control-prev-icon:hover,
.carousel-control-next-icon:hover {
  background-color: rgba(0,0,0,0.6);
}

/* Sombra exterior del carrusel */
.carousel.slide {
  border-radius: 1rem;
  overflow: hidden;
}
</style>
<?php endif; ?>

