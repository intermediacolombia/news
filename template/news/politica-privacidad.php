<?php
$legalPage = null;
try {
    $stmt = db()->prepare("SELECT * FROM legal_pages WHERE slug = 'politica-privacidad' LIMIT 1");
    $stmt->execute();
    $legalPage = $stmt->fetch();
} catch (Throwable $e) {}

$page_title       = (!empty($legalPage['title']) ? $legalPage['title'] : 'Política de Privacidad') . ' | ' . NOMBRE_SITIO;
$page_description = 'Política de privacidad y protección de datos de ' . NOMBRE_SITIO . '.';
$currentPath      = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$page_canonical   = rtrim(URLBASE, '/') . '/' . ltrim($currentPath, '/');
?>
<div class="container-fluid py-3">
    <div class="container-bk">
        <div class="row">
            <div class="col-lg-10 mx-auto">
                <div class="bg-light" style="padding:40px;">
                    <h1 class="mb-3"><?= htmlspecialchars($legalPage['title'] ?? 'Política de Privacidad') ?></h1>
                    <?php if (!empty($legalPage['updated_at'])): ?>
                        <p class="text-muted"><small>Última actualización: <?= date('d/m/Y', strtotime($legalPage['updated_at'])) ?></small></p>
                    <?php endif; ?>
                    <div style="line-height:1.8;">
                        <?php if (!empty($legalPage['content'])): ?>
                            <?= $legalPage['content'] ?>
                        <?php else: ?>
                            <p class="text-muted text-center">Contenido en preparación.</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
