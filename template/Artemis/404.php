<?php
$page_title = "404 - Página No Encontrada | " . NOMBRE_SITIO;
?>

<section class="py-5" style="background: var(--dark); min-height: 80vh; display: flex; align-items: center;">
    <div class="container">
        <div class="row">
            <div class="col-lg-8 mx-auto text-center">
                <h1 style="color: #fff; font-size: 8rem; font-weight: 800; margin: 0; line-height: 1;">404</h1>
                <h2 style="color: #fff; font-family: 'Playfair Display', serif; margin: 20px 0;">Página No Encontrada</h2>
                <p style="color: var(--text-muted); font-size: 18px; margin-bottom: 30px;">
                    Lo sentimos, la página que estás buscando no existe o ha sido movida.
                </p>
                <a href="<?= URLBASE ?>" class="btn-artemis">
                    <i class="fas fa-home mr-2"></i>Volver al Inicio
                </a>
            </div>
        </div>
    </div>
</section>