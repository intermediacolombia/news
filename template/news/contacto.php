<?php
$page_title       = 'Contacto | ' . NOMBRE_SITIO;
$page_description = 'Contáctanos para cualquier consulta, comentario o sugerencia.';
$currentPath      = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$page_canonical   = rtrim(URLBASE, '/') . '/' . ltrim($currentPath, '/');
?>
<div class="container-fluid py-3">
    <div class="container-bk">
        <div class="bg-light py-2 px-4 mb-3">
            <h3 class="m-0"><?= t_theme('theme_contactanos') ?></h3>
        </div>
        <div class="row">
            <div class="col-lg-8 mx-auto">
                <div class="bg-light mb-3" style="padding:30px;">
                    <div id="contact-message" class="mb-3" style="display:none;"></div>
                    <form id="contactForm" novalidate>
                        <div style="display:none;"><input type="text" name="website" tabindex="-1" autocomplete="off"></div>
                        <div class="form-row mb-3">
                            <div class="col-md-6 mb-3">
                                <input type="text" name="name" class="form-control p-3" placeholder="<?= t_theme('theme_tu_nombre') ?>" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <input type="email" name="email" class="form-control p-3" placeholder="<?= t_theme('theme_tu_correo') ?>" required>
                            </div>
                        </div>
                        <div class="form-group mb-3">
                            <input type="tel" name="phone" class="form-control p-3" placeholder="<?= t_theme('theme_tu_telefono') ?>">
                        </div>
                        <div class="form-group mb-3">
                            <textarea name="message" class="form-control" rows="5" placeholder="<?= t_theme('theme_tu_mensaje') ?>" required></textarea>
                        </div>
                        <button type="submit" id="contactBtn" class="btn btn-primary" style="width:100%; padding:12px;">
                            <i class="fas fa-paper-plane" style="margin-right:6px;"></i> <?= t_theme('theme_enviar_mensaje') ?>
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
<script>
document.getElementById('contactForm').addEventListener('submit', function(e) {
    e.preventDefault();
    var btn = document.getElementById('contactBtn');
    var msg = document.getElementById('contact-message');
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin" style="margin-right:6px;"></i> Enviando...';
    fetch('<?= URLBASE ?>/actions/contact.php', { method: 'POST', body: new FormData(this) })
    .then(function(r) { return r.json(); })
    .then(function(data) {
        msg.style.display = 'block';
        msg.style.padding = '12px 16px';
        msg.style.borderRadius = '6px';
        msg.style.marginBottom = '16px';
        if (data.ok) {
            msg.style.background = '#d4edda';
            msg.style.color = '#155724';
            msg.innerHTML = '<i class="fas fa-check-circle" style="margin-right:6px;"></i>' + data.msg;
            document.getElementById('contactForm').reset();
        } else {
            msg.style.background = '#f8d7da';
            msg.style.color = '#721c24';
            msg.innerHTML = '<i class="fas fa-exclamation-circle" style="margin-right:6px;"></i>' + data.msg;
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
        btn.innerHTML = '<i class="fas fa-paper-plane" style="margin-right:6px;"></i> <?= t_theme('theme_enviar_mensaje') ?>';
    });
});
</script>
