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
<!-- Breadcrumb Area Start Here -->
<section class="breadcrumbs-area" style="background-image: url('<?= URLBASE ?>/template/NewsEdge/img/banner/breadcrumbs-banner.jpg');">
    <div class="container">
        <div class="breadcrumbs-content">
            <h1><?= htmlspecialchars($legalPage['title'] ?? 'Política de Privacidad') ?></h1>
            <ul>
                <li><a href="<?= URLBASE ?>"><?= t_theme('theme_inicio') ?></a><i class="fa fa-angle-right" aria-hidden="true"></i></li>
                <li><?= htmlspecialchars($legalPage['title'] ?? 'Política de Privacidad') ?></li>
            </ul>
        </div>
    </div>
</section>
<!-- Breadcrumb Area End Here -->

<section class="bg-body section-space-less30">
    <div class="container">
        <div class="row">
            <div class="col-lg-10 mx-auto mb-30">
                <?php if (!empty($legalPage['updated_at'])): ?>
                    <p class="description-body-dark mb-20"><small>Última actualización: <?= date('d/m/Y', strtotime($legalPage['updated_at'])) ?></small></p>
                <?php endif; ?>
                <div class="item-description bg-white p-5 shadow-sm border-radius-4" style="line-height:1.8;">
                    <?php if (!empty($legalPage['content'])): ?>
                        <?= $legalPage['content'] ?>
                    <?php else: ?>
                        <p class="description-body-light text-center">Contenido en preparación.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</section>
