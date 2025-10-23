<?php
require_once __DIR__ . '/../../../inc/config.php';

$stmt = $pdo->query("SELECT * FROM ads_gallery 
                     WHERE section=5 AND type='square' AND status='active' 
                     ORDER BY created_at DESC");
$sliderAds = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<?php if ($sliderAds): ?>
<div id="adsSlider" class="carousel slide mb-4" data-bs-ride="carousel">
  <div class="carousel-inner">
    <?php foreach ($sliderAds as $i=>$ad): ?>
      <div class="carousel-item <?= $i===0 ? 'active' : '' ?>">
        <?php if (!empty($ad['target_url'])): ?>
          <a href="<?= htmlspecialchars($ad['target_url']) ?>" target="_blank" rel="nofollow">
            <img src="<?= htmlspecialchars($ad['image_url']) ?>" class="d-block w-100" alt="Banner Cuadrado">
          </a>
        <?php else: ?>
          <img src="<?= htmlspecialchars($ad['image_url']) ?>" class="d-block w-100" alt="Banner Cuadrado">
        <?php endif; ?>
      </div>
    <?php endforeach; ?>
  </div>

  <!-- Controles -->
  <button class="carousel-control-prev" type="button" data-bs-target="#adsSlider" data-bs-slide="prev">
    <span class="carousel-control-prev-icon"></span>
  </button>
  <button class="carousel-control-next" type="button" data-bs-target="#adsSlider" data-bs-slide="next">
    <span class="carousel-control-next-icon"></span>
  </button>
</div>
<?php endif; ?>
