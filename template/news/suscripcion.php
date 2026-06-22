<?php
$page_title       = 'Suscripción | ' . NOMBRE_SITIO;
$page_description = 'Suscríbete a nuestro boletín y recibe las últimas noticias de ' . NOMBRE_SITIO . '.';
$currentPath      = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$page_canonical   = rtrim(URLBASE, '/') . '/' . ltrim($currentPath, '/');
?>
<div class="container-fluid py-3">
    <div class="container-bk">
        <div class="bg-light py-2 px-4 mb-3">
            <h3 class="m-0">Suscripción</h3>
        </div>
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="bg-light p-4 text-center" style="border-radius:8px;">
                    <i class="fas fa-envelope-open-text fa-3x mb-3" style="color:var(--primary);"></i>
                    <h4>Únete a nuestra comunidad</h4>
                    <p class="text-muted">Mantente informado con nuestro boletín</p>

                    <div id="sub-message" class="mb-3" style="display:none;"></div>

                    <form id="subscribeForm" novalidate>
                        <div style="display:none;"><input type="text" name="website" tabindex="-1" autocomplete="off"></div>
                        <div class="form-group mb-3 text-left">
                            <label>Nombre *</label>
                            <input type="text" name="name" class="form-control" placeholder="Tu nombre" required>
                        </div>
                        <div class="form-group mb-3 text-left">
                            <label>Correo electrónico *</label>
                            <input type="email" name="email" class="form-control" placeholder="tu@correo.com" required>
                        </div>
                        <div class="form-group mb-3 text-left">
                            <div class="form-check">
                                <input type="checkbox" name="privacy" id="privacyCheck" class="form-check-input" required>
                                <label class="form-check-label" for="privacyCheck" style="font-size:.9em;">
                                    He leído y acepto la
                                    <a href="<?= URLBASE ?>/politica-privacidad/" target="_blank">Política de Privacidad</a>
                                    y consiento el tratamiento de mis datos personales. *
                                </label>
                            </div>
                        </div>
                        <button type="submit" id="subBtn" class="btn btn-primary" style="width:100%; padding:12px;">
                            <i class="fas fa-bell" style="margin-right:6px;"></i> Suscribirme
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
<script>
document.getElementById('subscribeForm').addEventListener('submit', function(e) {
    e.preventDefault();
    var btn = document.getElementById('subBtn');
    var msg = document.getElementById('sub-message');
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin" style="margin-right:6px;"></i> Procesando...';
    fetch('<?= URLBASE ?>/actions/subscribe.php', { method: 'POST', body: new FormData(this) })
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
            document.getElementById('subscribeForm').style.display = 'none';
        } else {
            msg.style.background = '#f8d7da';
            msg.style.color = '#721c24';
            msg.innerHTML = '<i class="fas fa-exclamation-circle" style="margin-right:6px;"></i>' + data.msg;
            btn.disabled = false;
            btn.innerHTML = '<i class="fas fa-bell" style="margin-right:6px;"></i> Suscribirme';
        }
    })
    .catch(function() {
        msg.style.display = 'block';
        msg.style.background = '#f8d7da';
        msg.style.color = '#721c24';
        msg.innerHTML = 'Error de conexión. Inténtalo de nuevo.';
        btn.disabled = false;
        btn.innerHTML = '<i class="fas fa-bell" style="margin-right:6px;"></i> Suscribirme';
    });
});
</script>
