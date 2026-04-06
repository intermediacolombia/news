<!-- 404 Inicio -->
<?php
require_once __DIR__ . '/../../inc/config.php';
require_once __DIR__ . '/../../inc/translations.php';
?>
<div class="container-fluid py-5">
    <div class="container py-5 text-center">
        <ol class="breadcrumb justify-content-center mb-5">
            <li class="breadcrumb-item"><a href="#"><?= t_theme('theme_inicio') ?></a></li>
            <li class="breadcrumb-item"><a href="#"><?= t_theme('theme_paginas') ?></a></li>
            <li class="breadcrumb-item active text-dark">404</li>
        </ol>
        <div class="row justify-content-center">
            <div class="col-lg-6">
                <i class="bi bi-exclamation-triangle display-1 text-secondary"></i>
                <h1 class="display-1">404</h1>
                <h1 class="mb-4"><?= t_theme('theme_pagina_no_encontrada') ?></h1>
                <p class="mb-4">
                    <?= t_theme('theme_pagina_no_existe') ?>
                </p>
                <a class="btn link-hover border border-primary rounded-pill py-3 px-5" href="/">
                    <?= t_theme('theme_volver_inicio') ?>
                </a>
            </div>
        </div>
    </div>
</div>
<!-- 404 Fin -->

