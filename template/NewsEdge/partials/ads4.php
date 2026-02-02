<?php
if (!function_exists('img_url')) {
    function img_url(?string $path): string {
        if (empty($path)) {
            return URLBASE . '/template/newsedge/img/placeholder.jpg';
        }
        if (filter_var($path, FILTER_VALIDATE_URL)) {
            return $path;
        }
        return URLBASE . '/' . ltrim($path, '/');
    }
}

// Obtener banners de la Sección 4
$stmt = db()->query("
    SELECT * FROM ads_gallery 
    WHERE section = 4 AND status = 'active' 
    ORDER BY created_at DESC
");
$ads = $stmt->fetchAll(PDO::FETCH_ASSOC);

$horizontales = array_filter($ads, fn($a) => $a['type'] === 'horizontal');
$cuadrados    = array_filter($ads, fn($a) => $a['type'] === 'square');
?>

<?php if ($horizontales || $cuadrados): ?>
<!-- Advertisement Section 4 Start -->
<section class="bg-accent section-space-less30">
    <div class="container">
        
        <!-- BANNERS HORIZONTALES -->
        <?php if ($horizontales): ?>
            <div class="ne-main-content mb-50">
                <!-- Título de sección con estilo del tema -->
                <div class="topic-border color-royal-blue mb-30">
                    <div class="topic-box-lg color-royal-blue">Publicidad Destacada</div>
                </div>
                
                <div class="row">
                    <?php foreach ($horizontales as $ad): 
                        $adImage = img_url($ad['image_url']);
                    ?>
                        <div class="col-12 mb-30">
                            <div class="ne-banner-layout1">
                                <?php if (!empty($ad['target_url'])): ?>
                                    <a href="<?= htmlspecialchars($ad['target_url']) ?>" 
                                       target="_blank" 
                                       rel="nofollow noopener">
                                        <img src="<?= $adImage ?>" 
                                             alt="Publicidad" 
                                             class="img-fluid width-100">
                                    </a>
                                <?php else: ?>
                                    <img src="<?= $adImage ?>" 
                                         alt="Publicidad" 
                                         class="img-fluid width-100">
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>

        <!-- BANNERS CUADRADOS -->
        <?php if ($cuadrados): ?>
            <div class="ne-main-content">
                <!-- Título de sección con estilo del tema -->
                <div class="topic-border color-mandy mb-30">
                    <div class="topic-box-lg color-mandy">Anuncios Promocionales</div>
                </div>
                
                <div class="row">
                    <?php foreach ($cuadrados as $ad): 
                        $adImage = img_url($ad['image_url']);
                    ?>
                        <div class="col-lg-3 col-md-4 col-sm-6 col-12 mb-30">
                            <div class="ne-banner-layout2 img-scale-animate">
                                <?php if (!empty($ad['target_url'])): ?>
                                    <a href="<?= htmlspecialchars($ad['target_url']) ?>" 
                                       target="_blank" 
                                       rel="nofollow noopener">
                                        <img src="<?= $adImage ?>" 
                                             alt="Publicidad" 
                                             class="img-fluid width-100"
                                             style="height: 250px; object-fit: cover;">
                                    </a>
                                <?php else: ?>
                                    <img src="<?= $adImage ?>" 
                                         alt="Publicidad" 
                                         class="img-fluid width-100"
                                         style="height: 250px; object-fit: cover;">
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>

    </div>
</section>
<!-- Advertisement Section 4 End -->

<!-- Estilos adicionales para banners (si no están ya definidos) -->
<style>
/* Banner horizontal con efecto hover suave */
.ne-banner-layout1 {
    overflow: hidden;
    border-radius: 4px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.08);
    transition: box-shadow 0.3s ease;
}

.ne-banner-layout1:hover {
    box-shadow: 0 8px 20px rgba(0,0,0,0.15);
}

.ne-banner-layout1 img {
    transition: transform 0.4s ease;
}

.ne-banner-layout1:hover img {
    transform: scale(1.02);
}

/* Banner cuadrado con efecto de escala */
.ne-banner-layout2 {
    overflow: hidden;
    border-radius: 4px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.08);
    transition: all 0.3s ease;
}

.ne-banner-layout2:hover {
    box-shadow: 0 8px 20px rgba(0,0,0,0.15);
    transform: translateY(-4px);
}

.img-scale-animate img {
    transition: transform 0.4s ease;
}

.img-scale-animate:hover img {
    transform: scale(1.05);
}
</style>
<?php endif; ?>