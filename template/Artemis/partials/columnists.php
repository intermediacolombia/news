<?php
if (!function_exists('img_url')) {
    function img_url(?string $path): string {
        if (empty($path)) return URLBASE . '/template/Artemis/img/avatar.jpg';
        if (preg_match('#^https?://#i', $path)) return $path;
        return URLBASE . '/' . ltrim($path, '/');
    }
}

$columnistas = db()->query("
    SELECT nombre, apellido, username, foto_perfil
    FROM usuarios
    WHERE es_columnista = 1
      AND estado = 0
      AND borrado = 0
    ORDER BY nombre ASC, apellido ASC
    LIMIT 6
")->fetchAll();
?>

<?php if (!empty($columnistas)): ?>
<section class="py-5" style="background: var(--dark);">
    <div class="container">
        <div class="row mb-4">
            <div class="col-12 text-center">
                <h2 class="section-title" style="color: var(--text-color);"><?= t_theme('theme_nuestros_columnistas') ?></h2>
                <p style="color: var(--text-muted); margin-top: 15px;"><?= t_theme('theme_columnistas_descripcion') ?></p>
            </div>
        </div>
        
        <div class="row">
            <?php foreach ($columnistas as $col): 
                $nombreCompleto = trim($col['nombre'] . ' ' . $col['apellido']);
                $profileUrl = URLBASE . '/columnista/' . urlencode($col['username']) . '/';
                
                if (!empty($col['foto_perfil'])) {
                    $imageUrl = img_url($col['foto_perfil']);
                } else {
                    $iniciales = strtoupper(substr($col['nombre'], 0, 1) . substr($col['apellido'], 0, 1));
                }
            ?>
            <div class="col-lg-2 col-md-4 col-sm-6 mb-4">
                <div class="columnist-card text-center p-4" 
                     style="background: var(--dark-secondary); border-radius: 16px; transition: all 0.3s ease; border: 1px solid rgba(255,255,255,0.05);">
                    
                    <div class="mb-3" style="position: relative; display: inline-block;">
                        <?php if (!empty($col['foto_perfil'])): ?>
                            <img src="<?= htmlspecialchars($imageUrl) ?>"
                                 alt="<?= htmlspecialchars($nombreCompleto) ?>"
                                 style="width: 80px; height: 80px; border-radius: 50%; object-fit: cover; border: 3px solid var(--primary);">
                        <?php else: ?>
                            <div style="width: 80px; height: 80px; border-radius: 50%; background: linear-gradient(135deg, var(--primary), var(--primary-dark)); display: flex; align-items: center; justify-content: center; margin: 0 auto; border: 3px solid var(--primary);">
                                <span style="color: var(--text-color); font-size: 28px; font-weight: 700;"><?= htmlspecialchars($iniciales) ?></span>
                            </div>
                        <?php endif; ?>
                    </div>
                    
                    <h5 style="color: var(--text-color); font-size: 16px; font-weight: 600; margin-bottom: 5px;">
                        <a href="<?= $profileUrl ?>" style="color: inherit; text-decoration: none;">
                            <?= htmlspecialchars($nombreCompleto) ?>
                        </a>
                    </h5>
                    <span style="color: var(--primary); font-size: 13px; font-weight: 500;">Columnista</span>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        
        <div class="text-center mt-4">
            <a href="<?= URLBASE ?>/columnista/" class="btn-artemis">
                <?= t_theme('theme_ver_todos_columnistas') ?> <i class="fas fa-arrow-right ml-2"></i>
            </a>
        </div>
    </div>
</section>
<?php endif; ?>

<style>
    .columnist-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 20px 40px rgba(230, 57, 70, 0.2);
        border-color: var(--primary);
    }
</style>