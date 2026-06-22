<?php
$page_title       = 'Contacto | ' . NOMBRE_SITIO;
$page_description = 'Contáctanos para cualquier consulta, comentario o sugerencia.';
$currentPath      = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$page_canonical   = rtrim(URLBASE, '/') . '/' . ltrim($currentPath, '/');
?>
<!-- Breadcrumb Area Start Here -->
<section class="breadcrumbs-area" style="background-image: url('<?= URLBASE ?>/template/NewsEdge/img/banner/breadcrumbs-banner.jpg');">
    <div class="container">
        <div class="breadcrumbs-content">
            <h1><?= t_theme('theme_contacto') ?></h1>
            <ul>
                <li><a href="<?= URLBASE ?>"><?= t_theme('theme_inicio') ?></a><i class="fa fa-angle-right" aria-hidden="true"></i></li>
                <li><?= t_theme('theme_contacto') ?></li>
            </ul>
        </div>
    </div>
</section>
<!-- Breadcrumb Area End Here -->

<section class="bg-body section-space-less30">
    <div class="container">
        <div class="item-box-light-md-less30">
            <div class="row">
                <div class="col-lg-8 mx-auto mb-30">
                    <div class="contact-form-box item-box-light-md item-shadow-1 p-30">
                        <h3 class="title-semibold-dark size-xl mb-30"><?= t_theme('theme_enviar_mensaje') ?></h3>

                        <div id="contact-message" class="mb-20" style="display:none;"></div>

                        <form id="contactForm" novalidate>
                            <div style="display:none;"><input type="text" name="website" tabindex="-1" autocomplete="off"></div>
                            <div class="row">
                                <div class="col-lg-6 col-md-6 col-12 mb-20">
                                    <input type="text" name="name" class="form-control contact-input" placeholder="<?= t_theme('theme_tu_nombre') ?>" required>
                                </div>
                                <div class="col-lg-6 col-md-6 col-12 mb-20">
                                    <input type="email" name="email" class="form-control contact-input" placeholder="<?= t_theme('theme_tu_correo') ?>" required>
                                </div>
                                <div class="col-12 mb-20">
                                    <input type="tel" name="phone" class="form-control contact-input" placeholder="<?= t_theme('theme_tu_telefono') ?>">
                                </div>
                                <div class="col-12 mb-20">
                                    <textarea name="message" class="form-control contact-textarea" rows="5" placeholder="<?= t_theme('theme_tu_mensaje') ?>" required></textarea>
                                </div>
                                <div class="col-12">
                                    <button type="submit" id="contactBtn" class="contact-submit-btn">
                                        <?= t_theme('theme_enviar_mensaje') ?>
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<script>
document.getElementById('contactForm').addEventListener('submit', function(e) {
    e.preventDefault();
    var btn = document.getElementById('contactBtn');
    var msg = document.getElementById('contact-message');
    btn.disabled = true;
    btn.innerHTML = '<i class="fa fa-spinner fa-spin"></i> Enviando...';
    fetch('<?= URLBASE ?>/actions/contact.php', { method: 'POST', body: new FormData(this) })
    .then(function(r) { return r.json(); })
    .then(function(data) {
        msg.style.display = 'block';
        msg.style.padding = '12px 16px';
        msg.style.borderRadius = '4px';
        msg.style.marginBottom = '20px';
        if (data.ok) {
            msg.style.background = '#d4edda';
            msg.style.color = '#155724';
            msg.innerHTML = '<i class="fa fa-check-circle" style="margin-right:6px;"></i>' + data.msg;
            document.getElementById('contactForm').reset();
        } else {
            msg.style.background = '#f8d7da';
            msg.style.color = '#721c24';
            msg.innerHTML = '<i class="fa fa-exclamation-circle" style="margin-right:6px;"></i>' + data.msg;
        }
    })
    .catch(function() {
        msg.style.display = 'block';
        msg.style.background = '#f8d7da';
        msg.style.color = '#721c24';
        msg.innerHTML = 'Error de conexión. Inténtalo de nuevo.';
    })
    .finally(function() {
        btn.disabled = false;
        btn.innerHTML = '<?= t_theme('theme_enviar_mensaje') ?>';
    });
});
</script>
