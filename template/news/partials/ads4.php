<?php
require_once __DIR__ . '/../../../inc/config.php';

// Obtener banners de la Sección 3
$stmt = db()->query("
    SELECT * FROM ads_gallery 
    WHERE section = 4 AND status = 'active' 
    ORDER BY created_at DESC
");
$ads = $stmt->fetchAll(PDO::FETCH_ASSOC);

$horizontales = array_filter($ads, fn($a) => $a['type'] === 'horizontal');
$cuadrados    = array_filter($ads, fn($a) => $a['type'] === 'square');
?>

<!-- SECCIÓN DE BANNERS -->
<section class="py-5">
  <div class="container">

    <!-- BANNERS HORIZONTALES -->
    <?php if ($horizontales): ?>
      <div class="mb-5">
        <h3 class="fw-bold text-center mb-4 text-uppercase text-secondary">Publicidad Destacada</h3>
        <div class="row g-4 justify-content-center">
          <?php foreach ($horizontales as $ad): ?>
            <div class="col-12 col-md-10">
              <div class="card border-0 shadow-sm rounded-3 overflow-hidden hover-zoom">
                <?php if (!empty($ad['target_url'])): ?>
                  <a href="<?= htmlspecialchars($ad['target_url']) ?>" target="_blank" rel="nofollow">
                    <img src="<?= htmlspecialchars($ad['image_url']) ?>" class="img-fluid w-100" alt="Banner Horizontal">
                  </a>
                <?php else: ?>
                  <img src="<?= htmlspecialchars($ad['image_url']) ?>" class="img-fluid w-100" alt="Banner Horizontal">
                <?php endif; ?>
              </div>
            </div>
          <?php endforeach; ?>
        </div>
      </div>
    <?php endif; ?>


    <!-- BANNERS CUADRADOS -->
    <?php if ($cuadrados): ?>
      <div>
        <h3 class="fw-bold text-center mb-4 text-uppercase text-secondary">Anuncios Promocionales</h3>
        <div class="row row-cols-2 row-cols-md-3 row-cols-lg-4 g-4 justify-content-center">
          <?php foreach ($cuadrados as $ad): ?>
            <div class="col">
              <div class="card border-0 shadow-sm rounded-4 overflow-hidden hover-zoom">
                <?php if (!empty($ad['target_url'])): ?>
                  <a href="<?= htmlspecialchars($ad['target_url']) ?>" target="_blank" rel="nofollow">
                    <img src="<?= htmlspecialchars($ad['image_url']) ?>" class="img-fluid" alt="Banner Cuadrado">
                  </a>
                <?php else: ?>
                  <img src="<?= htmlspecialchars($ad['image_url']) ?>" class="img-fluid" alt="Banner Cuadrado">
                <?php endif; ?>
              </div>
            </div>
          <?php endforeach; ?>
        </div>
      </div>
    <?php endif; ?>

  </div>
</section>

<!-- EFECTO SUAVE DE HOVER -->
<style>
.hover-zoom img {
  transition: transform .4s ease, box-shadow .3s ease;
}
.hover-zoom:hover img {
  transform: scale(1.03);
  box-shadow: 0 8px 25px rgba(0,0,0,0.15);
}
</style>
