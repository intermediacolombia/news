<?php
$page_title = "Contacto | " . NOMBRE_SITIO;
$page_description = "Contáctanos para cualquier consulta o sugerencia";
?>

<section class="py-5" style="background: var(--dark); min-height: 60vh;">
    <div class="container">
        <div class="row mb-5">
            <div class="col-12 text-center">
                <h1 class="section-title" style="color: var(--text-color);">CONTACTO</h1>
                <p style="color: var(--text-muted); margin-top: 15px;">¿Tienes alguna pregunta? Escríbenos</p>
            </div>
        </div>
        
        <div class="row">
            <div class="col-lg-8 mx-auto">
                <div style="background: var(--dark-secondary); border-radius: 20px; padding: 40px;">
                    <form>
                        <div class="form-group mb-4">
<label style="color: var(--text-color); margin-bottom: 8px; display: block;"><?= t_theme('theme_tu_nombre') ?></label>
                            <input type="text" class="search-input" style="width: 100%;" placeholder="<?= t_theme('theme_tu_nombre') ?>" required>
                        </div>
                        <div class="mb-3">
                            <label style="color: var(--text-color); margin-bottom: 8px; display: block;"><?= t_theme('theme_tu_correo') ?></label>
                            <input type="email" class="search-input" style="width: 100%;" placeholder="<?= t_theme('theme_tu_correo') ?>" required>
                        </div>
                        <div class="mb-3">
                            <label style="color: var(--text-color); margin-bottom: 8px; display: block;"><?= t_theme('theme_asunto') ?></label>
                            <input type="text" class="search-input" style="width: 100%;" placeholder="<?= t_theme('theme_asunto') ?>" required>
                        </div>
                        <div class="mb-3">
                            <label style="color: var(--text-color); margin-bottom: 8px; display: block;"><?= t_theme('theme_tu_mensaje') ?></label>
                            <textarea class="search-input" style="width: 100%; min-height: 150px;" placeholder="<?= t_theme('theme_tu_mensaje') ?>..."></textarea>
                        </div>
                        <button type="submit" class="btn-artemis w-100">
                            <i class="fas fa-paper-plane mr-2"></i><?= t_theme('theme_enviar_mensaje') ?>
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</section>