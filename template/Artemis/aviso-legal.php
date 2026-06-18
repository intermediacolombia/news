<?php
$legalPage = null;
try {
    $stmt = db()->prepare("SELECT * FROM legal_pages WHERE slug = 'aviso-legal' LIMIT 1");
    $stmt->execute();
    $legalPage = $stmt->fetch();
} catch (Throwable $e) {}

$page_title       = (!empty($legalPage['title']) ? $legalPage['title'] : 'Aviso Legal') . ' | ' . NOMBRE_SITIO;
$page_description = 'Aviso legal de ' . NOMBRE_SITIO . '.';
$currentPath      = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$page_canonical   = rtrim(URLBASE, '/') . '/' . ltrim($currentPath, '/');
?>

<section class="py-5" style="background: var(--dark); min-height: 60vh;">
    <div class="container">
        <div class="row mb-4">
            <div class="col-12">
                <h1 class="section-title" style="color: var(--text-color);">
                    <?= htmlspecialchars($legalPage['title'] ?? 'Aviso Legal') ?>
                </h1>
                <?php if (!empty($legalPage['updated_at'])): ?>
                    <p style="color: var(--text-muted); font-size:.85em;">
                        Última actualización: <?= date('d/m/Y', strtotime($legalPage['updated_at'])) ?>
                    </p>
                <?php endif; ?>
            </div>
        </div>
        <div class="row">
            <div class="col-lg-10 mx-auto">
                <div style="background: var(--dark-secondary); border-radius: 16px; padding: 40px; color: var(--text-color); line-height: 1.8;">
                    <?php if (!empty($legalPage['content'])): ?>
                        <?= $legalPage['content'] ?>
                    <?php else: ?>
                        <p style="color: var(--text-muted); text-align:center;">Contenido en preparación.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</section>
