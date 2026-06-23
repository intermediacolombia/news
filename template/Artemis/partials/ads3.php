<?php
$stmt = db()->query("
    SELECT * FROM ads_gallery
    WHERE section = 3 AND status = 'active'
    ORDER BY created_at DESC
");
$ads = $stmt->fetchAll(PDO::FETCH_ASSOC);

$horizontales = array_filter($ads, fn($a) => $a['type'] === 'horizontal');
$cuadrados    = array_filter($ads, fn($a) => $a['type'] === 'square');

if (!$horizontales && !$cuadrados) return;
?>

<section class="py-4">
    <div class="container">

        <?php if ($horizontales): ?>
        <div class="mb-4">
            <h6 class="text-uppercase text-muted fw-semibold mb-3 text-center" style="letter-spacing:.08em;font-size:.75rem;">Publicidad</h6>
            <?php foreach ($horizontales as $ad): ?>
            <div class="mb-3 rounded-3 overflow-hidden shadow-sm">
                <?php if (!empty($ad['target_url'])): ?>
                    <a href="<?= htmlspecialchars($ad['target_url']) ?>" target="_blank" rel="nofollow noopener">
                        <img src="<?= img_url($ad['image_url']) ?>" class="img-fluid w-100" alt="Publicidad">
                    </a>
                <?php else: ?>
                    <img src="<?= img_url($ad['image_url']) ?>" class="img-fluid w-100" alt="Publicidad">
                <?php endif; ?>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>

        <?php if ($cuadrados): ?>
        <div>
            <h6 class="text-uppercase text-muted fw-semibold mb-3 text-center" style="letter-spacing:.08em;font-size:.75rem;">Anuncios</h6>
            <div class="row row-cols-2 row-cols-md-3 row-cols-lg-4 g-3">
                <?php foreach ($cuadrados as $ad): ?>
                <div class="col">
                    <div class="rounded-3 overflow-hidden shadow-sm art-ad-hover">
                        <?php if (!empty($ad['target_url'])): ?>
                            <a href="<?= htmlspecialchars($ad['target_url']) ?>" target="_blank" rel="nofollow noopener">
                                <img src="<?= img_url($ad['image_url']) ?>" class="img-fluid w-100" alt="Publicidad" style="height:200px;object-fit:cover;">
                            </a>
                        <?php else: ?>
                            <img src="<?= img_url($ad['image_url']) ?>" class="img-fluid w-100" alt="Publicidad" style="height:200px;object-fit:cover;">
                        <?php endif; ?>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>

    </div>
</section>
<style>
.art-ad-hover img { transition: transform .35s ease; }
.art-ad-hover:hover img { transform: scale(1.04); }
</style>
