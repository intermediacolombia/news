<?php
require_once __DIR__ . '/../../inc/config.php';

$sql = "SELECT id, nombre, apellido, username, foto_perfil FROM usuarios WHERE es_columnista = 1 AND estado = 0 AND borrado = 0 ORDER BY nombre ASC, apellido ASC";
$stmt = db()->query($sql);
$columnistas = $stmt->fetchAll(PDO::FETCH_ASSOC);

foreach ($columnistas as &$columnista) {
    $stmt = db()->prepare("SELECT COUNT(*) FROM blog_posts WHERE author_user = ? AND status = 'published' AND deleted = 0");
    $stmt->execute([$columnista['username']]);
    $columnista['post_count'] = (int)$stmt->fetchColumn();
}
unset($columnista);

$page_title = "Nuestros Columnistas | " . NOMBRE_SITIO;

if (!function_exists('img_url')) {
    function img_url(?string $path): string {
        if (empty($path)) return URLBASE . '/template/Artemis/img/avatar.jpg';
        if (preg_match('#^https?://#i', $path)) return $path;
        return URLBASE . '/' . ltrim($path, '/');
    }
}
?>

<section class="py-5" style="background: var(--dark);">
    <div class="container">
        <div class="row mb-5">
            <div class="col-12 text-center">
                <h1 class="section-title" style="color: var(--text-color);"><?= t_theme('theme_nuestros_columnistas') ?></h1>
                <p style="color: var(--text-muted); margin-top: 15px; max-width: 600px; margin-left: auto; margin-right: auto;">
                    <?= t_theme('theme_columnistas_descripcion') ?>
                </p>
            </div>
        </div>

        <?php if(empty($columnistas)): ?>
        <div class="text-center py-5">
            <i class="fas fa-users" style="font-size: 60px; color: var(--text-muted); opacity: 0.3;"></i>
            <h3 style="color: var(--text-color); margin-top: 20px;">No hay columnistas disponibles</h3>
            <p style="color: var(--text-muted);">Próximamente agregaremos nuevos columnistas a nuestro equipo.</p>
        </div>
        <?php else: ?>
        <div class="row justify-content-center">
            <?php foreach($columnistas as $col): 
                $nombreCompleto = trim($col['nombre'] . ' ' . $col['apellido']);
                $profileUrl = URLBASE . '/columnista/' . urlencode($col['username']) . '/';
                
                if (!empty($col['foto_perfil'])) {
                    $imageUrl = img_url($col['foto_perfil']);
                } else {
                    $iniciales = strtoupper(substr($col['nombre'], 0, 1) . substr($col['apellido'], 0, 1));
                }
            ?>
            <div class="col-lg-3 col-md-4 col-sm-6 mb-4">
                <div class="text-center p-4" style="background: var(--dark-secondary); border-radius: 20px; transition: all 0.3s ease; border: 1px solid rgba(255,255,255,0.05);">
                    <?php if (!empty($col['foto_perfil'])): ?>
                        <img src="<?= $imageUrl ?>" 
                             alt="<?= htmlspecialchars($nombreCompleto) ?>"
                             style="width: 120px; height: 120px; border-radius: 50%; object-fit: cover; border: 4px solid var(--primary); margin-bottom: 20px;">
                    <?php else: ?>
                        <div style="width: 120px; height: 120px; border-radius: 50%; background: linear-gradient(135deg, var(--primary), var(--primary-dark)); display: flex; align-items: center; justify-content: center; margin: 0 auto 20px; border: 4px solid var(--primary);">
                            <span style="color: var(--text-color); font-size: 40px; font-weight: 700;"><?= $iniciales ?></span>
                        </div>
                    <?php endif; ?>
                    
                    <h4 style="color: var(--text-color); font-size: 20px; font-weight: 600; margin-bottom: 5px;">
                        <a href="<?= $profileUrl ?>" style="color: inherit; text-decoration: none;">
                            <?= htmlspecialchars($nombreCompleto) ?>
                        </a>
                    </h4>
                    <span style="color: var(--primary); font-size: 14px; font-weight: 500;">Columnista</span>
                    <span style="display: block; color: var(--text-muted); font-size: 13px; margin-top: 10px;">
                        <i class="fas fa-file-alt mr-2"></i><?= $col['post_count'] ?> columna<?= $col['post_count'] !== 1 ? 's' : '' ?>
                    </span>
                    <a href="<?= $profileUrl ?>" class="btn-artemis mt-3" style="padding: 10px 24px; font-size: 13px;">
                        Ver Perfil
                    </a>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </div>
</section>

<style>
    div[style*="background: var(--dark-secondary)"]:hover {
        transform: translateY(-5px);
        box-shadow: 0 20px 40px rgba(230, 57, 70, 0.2);
        border-color: var(--primary);
    }
</style>