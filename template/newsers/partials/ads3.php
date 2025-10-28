<?php
require_once __DIR__ . '/../../../inc/config.php';
// Mostrar banners de la Sección 3
$stmt = $pdo->query("SELECT * FROM ads_gallery 
                     WHERE section=3 AND status='active' 
                     ORDER BY created_at DESC");
$ads = $stmt->fetchAll(PDO::FETCH_ASSOC);

$horizontales = array_filter($ads, fn($a)=>$a['type']==='horizontal');
$cuadrados    = array_filter($ads, fn($a)=>$a['type']==='square');
?>

<!-- HORIZONTALES -->
<?php if ($horizontales): ?>
		
			
<div class="container-fluid py-5">
	<div class="container py-5">   
        <div class="row">
    <div class="container-bk">
  <div class="mb-4">
    <?php foreach ($horizontales as $ad): ?>
      <div class="mb-3">
        <?php if (!empty($ad['target_url'])): ?>
          <a href="<?= htmlspecialchars($ad['target_url']) ?>" target="_blank" rel="nofollow">
            <img src="<?= htmlspecialchars($ad['image_url']) ?>" class="img-fluid w-100" alt="Banner Horizontal" style="margin: 5px;">
          </a>
        <?php else: ?>
          <img src="<?= htmlspecialchars($ad['image_url']) ?>" class="img-fluid w-100" alt="Banner Horizontal" style="margin: 5px;">
        <?php endif; ?>
      </div>
    <?php endforeach; ?>
  </div>
<?php endif; ?>

<!-- CUADRADOS -->
<?php if ($cuadrados): ?>
  <div class="row row-cols-1 row-cols-sm-2 row-cols-md-3 g-3 mb-4">
    <?php foreach ($cuadrados as $ad): ?>
      <div class="col">
        <?php if (!empty($ad['target_url'])): ?>
          <a href="<?= htmlspecialchars($ad['target_url']) ?>" target="_blank" rel="nofollow">
            <img src="<?= htmlspecialchars($ad['image_url']) ?>" alt="Banner Cuadrado" class="img-fluid" style="margin: 5px;">
          </a>
        <?php else: ?>
          <img src="<?= htmlspecialchars($ad['image_url']) ?>" alt="Banner Cuadrado" class="img-fluid" style="margin: 5px;">
        <?php endif; ?>
      </div>
    <?php endforeach; ?>
  </div>
  </div>
  </div>
  </div>
  </div>
<?php endif; ?>
