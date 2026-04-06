<?php
$stmt = db()->prepare("SELECT * FROM ads WHERE position = 5 AND status = 'active' LIMIT 1");
$stmt->execute();
$ad = $stmt->fetch(PDO::FETCH_ASSOC);
if ($ad && !empty($ad['image_url'])): 
?>
<div class="text-center my-4">
    <?php if (!empty($ad['target_url'])): ?>
        <a href="<?= htmlspecialchars($ad['target_url']) ?>" target="_blank" rel="noopener">
            <img class="img-fluid" 
                 src="<?= URLBASE . htmlspecialchars($ad['image_url']) ?>" 
                 alt="Publicidad"
                 style="max-height: 100px; border-radius: 12px;">
        </a>
    <?php else: ?>
        <img class="img-fluid" 
             src="<?= URLBASE . htmlspecialchars($ad['image_url']) ?>" 
             alt="Publicidad"
             style="max-height: 100px; border-radius: 12px;">
    <?php endif; ?>
</div>
<?php endif; ?>