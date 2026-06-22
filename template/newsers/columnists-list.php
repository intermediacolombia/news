<?php
if (!function_exists('img_url')) {
    function img_url(?string $path): string {
        if (empty($path)) return URLBASE . '/template/newsers/img/user.jpg';
        if (preg_match('#^https?://#i', $path)) return $path;
        return URLBASE . '/' . ltrim($path, '/');
    }
}

$stmt = db()->query("SELECT id, nombre, apellido, username, foto_perfil FROM usuarios WHERE es_columnista = 1 AND estado = 0 AND borrado = 0 ORDER BY nombre ASC, apellido ASC");
$columnistas = $stmt->fetchAll(PDO::FETCH_ASSOC);

foreach ($columnistas as &$col) {
    $s = db()->prepare("SELECT COUNT(*) FROM blog_posts WHERE author_user = ? AND status = 'published' AND deleted = 0");
    $s->execute([$col['username']]);
    $col['post_count'] = (int)$s->fetchColumn();
}
unset($col);

$page_title = "Nuestros Columnistas | " . NOMBRE_SITIO;
?>
<div class="container-fluid py-5">
    <div class="container py-3">
        <h1 class="mb-4"><?= t_theme('theme_nuestros_columnistas') ?></h1>

        <?php if (empty($columnistas)): ?>
        <div class="text-center py-5">
            <i class="fas fa-users fa-4x text-muted mb-3"></i>
            <h4 class="text-muted"><?= t_theme('theme_no_hay_disponibles') ?></h4>
            <p class="text-muted">Próximamente agregaremos nuevos columnistas a nuestro equipo.</p>
        </div>
        <?php else: ?>
        <div class="row g-4">
            <?php foreach ($columnistas as $col):
                $nombreCompleto = trim($col['nombre'] . ' ' . $col['apellido']);
                $profileUrl     = URLBASE . '/columnista/' . urlencode($col['username']) . '/';
                if (!empty($col['foto_perfil'])) {
                    $imageUrl = img_url($col['foto_perfil']);
                    $hasImg   = true;
                } else {
                    $iniciales = strtoupper(substr($col['nombre'], 0, 1) . substr($col['apellido'], 0, 1));
                    $hasImg    = false;
                }
            ?>
            <div class="col-md-3 col-sm-6">
                <div class="card text-center h-100 shadow-sm p-3" style="border-radius:8px;">
                    <?php if ($hasImg): ?>
                        <img src="<?= $imageUrl ?>" alt="<?= htmlspecialchars($nombreCompleto) ?>"
                             style="width:100px; height:100px; border-radius:50%; object-fit:cover; border:3px solid var(--primary); margin:0 auto 15px;">
                    <?php else: ?>
                        <div style="width:100px; height:100px; border-radius:50%; background:var(--primary); display:flex; align-items:center; justify-content:center; margin:0 auto 15px;">
                            <span style="color:#fff; font-size:32px; font-weight:700;"><?= $iniciales ?></span>
                        </div>
                    <?php endif; ?>
                    <h5><?= htmlspecialchars($nombreCompleto) ?></h5>
                    <p class="text-muted small text-uppercase">Columnista</p>
                    <p class="text-muted small"><?= $col['post_count'] ?> columna<?= $col['post_count'] !== 1 ? 's' : '' ?></p>
                    <a href="<?= $profileUrl ?>" class="btn btn-primary btn-sm">Ver Perfil</a>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </div>
</div>
