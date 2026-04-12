<?php
require_once __DIR__ . '/../../inc/config.php';
require_once __DIR__ . '/../../inc/translations.php';

$page_title = "404 - " . t_theme('theme_pagina_no_encontrada') . " | " . NOMBRE_SITIO;
?>

<section class="py-5" style="background: var(--dark); min-height: 80vh; display: flex; align-items: center;">
    <div class="container">
        <div class="row">
            <div class="col-lg-8 mx-auto text-center">
                <h1 style="color: var(--text-color); font-size: 8rem; font-weight: 800; margin: 0; line-height: 1;">404</h1>
                <h2 style="color: var(--text-color); font-family: 'Playfair Display', serif; margin: 20px 0;"><?= t_theme('theme_pagina_no_encontrada') ?></h2>
                <p style="color: var(--text-muted); font-size: 18px; margin-bottom: 30px;">
                    <?= t_theme('theme_pagina_no_existe') ?>
                </p>
                <a href="<?= URLBASE ?>" class="btn-artemis">
                    <i class="fas fa-home mr-2"></i><?= t_theme('theme_volver_inicio') ?>
                </a>
            </div>
        </div>
    </div>
</section>