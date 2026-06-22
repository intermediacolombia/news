<?php
$page_title       = 'Programas | ' . NOMBRE_SITIO;
$page_description = 'Conoce todos los programas de ' . NOMBRE_SITIO . '.';
$currentPath      = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$page_canonical   = rtrim(URLBASE, '/') . '/' . ltrim($currentPath, '/');

$programs = [];
try {
    $programs = db()->query("SELECT * FROM programs WHERE status = 'active' ORDER BY title ASC")->fetchAll();
} catch (Throwable $e) {}
?>
<div class="container-fluid py-3">
    <div class="container-bk">
        <div class="bg-light py-2 px-4 mb-3">
            <h3 class="m-0">Programas</h3>
        </div>
        <?php if (empty($programs)): ?>
            <div class="text-center py-5">
                <i class="fas fa-radio fa-3x text-muted mb-3"></i>
                <p class="text-muted">Próximamente nuestros programas.</p>
            </div>
        <?php else: ?>
        <div class="row">
            <?php foreach ($programs as $prog): ?>
            <div class="col-md-6 col-lg-4 mb-4">
                <a href="<?= URLBASE ?>/programas/<?= htmlspecialchars($prog['slug']) ?>/" style="text-decoration:none; color:inherit;">
                    <div class="card h-100 shadow-sm" style="border-radius:8px; overflow:hidden;">
                        <?php if (!empty($prog['image'])): ?>
                            <img src="<?= htmlspecialchars(rtrim(URLBASE,'/').'/'.$prog['image']) ?>"
                                 alt="<?= htmlspecialchars($prog['title']) ?>"
                                 class="card-img-top" style="height:200px; object-fit:cover;">
                        <?php else: ?>
                            <div style="height:200px; background:#f8f9fa; display:flex; align-items:center; justify-content:center;">
                                <i class="fas fa-microphone fa-3x text-muted"></i>
                            </div>
                        <?php endif; ?>
                        <div class="card-body">
                            <?php if (!empty($prog['category'])): ?>
                                <span class="badge badge-primary mb-2"><?= htmlspecialchars($prog['category']) ?></span>
                            <?php endif; ?>
                            <h5 class="card-title"><?= htmlspecialchars($prog['title']) ?></h5>
                            <?php if (!empty($prog['hosts'])): ?>
                                <small class="text-muted"><i class="fas fa-user" style="margin-right:4px;"></i><?= htmlspecialchars($prog['hosts']) ?></small>
                            <?php endif; ?>
                        </div>
                    </div>
                </a>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </div>
</div>
