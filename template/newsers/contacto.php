<?php
$page_title       = 'Contacto | ' . NOMBRE_SITIO;
$page_description = 'Contáctanos para cualquier consulta, comentario o sugerencia.';
$currentPath      = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$page_canonical   = rtrim(URLBASE, '/') . '/' . ltrim($currentPath, '/');
?>
<div class="container-fluid py-5">
    <div class="container py-3">
        <div class="bg-light rounded p-4 p-md-5">
            <div class="row justify-content-center">
                <div class="col-lg-8">
                    <h1 class="mb-4"><?= t_theme('theme_contactanos') ?></h1>

                    <div id="contact-message" class="mb-3" style="display:none;"></div>

                    <form id="contactForm" novalidate>
                        <div style="display:none;"><input type="text" name="website" tabindex="-1" autocomplete="off"></div>
                        <div class="row g-3 mb-3">
                            <div class="col-md-6">
                                <input type="text" name="name" class="form-control py-3" placeholder="<?= t_theme('theme_tu_nombre') ?>" required>
                            </div>
                            <div class="col-md-6">
                                <input type="email" name="email" class="form-control py-3" placeholder="<?= t_theme('theme_tu_correo') ?>" required>
                            </div>
                            <div class="col-12">
                                <input type="tel" name="phone" class="form-control py-3" placeholder="<?= t_theme('theme_tu_telefono') ?>">
                            </div>
                            <div class="col-12">
                                <textarea name="message" class="form-control" rows="5" placeholder="<?= t_theme('theme_tu_mensaje') ?>" required></textarea>
                            </div>
                            <div class="col-12">
                                <button type="submit" id="contactBtn" class="w-100 btn btn-primary py-3">
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
<script>
document.getElementById('contactForm').addEventListener('submit', function(e) {
    e.preventDefault();
    var btn = document.getElementById('contactBtn');
    var msg = document.getElementById('contact-message');
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i> Enviando...';
    fetch('<?= URLBASE ?>/actions/contact.php', { method: 'POST', body: new FormData(this) })
    .then(function(r) { return r.json(); })
    .then(function(data) {
        msg.style.display = 'block';
        msg.style.padding = '12px 16px';
        msg.style.borderRadius = '8px';
        msg.style.marginBottom = '16px';
        if (data.ok) {
            msg.style.background = '#d4edda';
            msg.style.color = '#155724';
            msg.innerHTML = '<i class="fas fa-check-circle me-2"></i>' + data.msg;
            document.getElementById('contactForm').reset();
        } else {
            msg.style.background = '#f8d7da';
            msg.style.color = '#721c24';
            msg.innerHTML = '<i class="fas fa-exclamation-circle me-2"></i>' + data.msg;
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
