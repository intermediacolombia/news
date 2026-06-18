<?php
$page_title       = 'Suscripción | ' . NOMBRE_SITIO;
$page_description = 'Suscríbete a nuestro boletín y recibe las últimas noticias de ' . NOMBRE_SITIO . '.';
$currentPath      = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$page_canonical   = rtrim(URLBASE, '/') . '/' . ltrim($currentPath, '/');
?>

<section class="py-5" style="background: var(--dark); min-height: 60vh;">
    <div class="container">
        <div class="row mb-4 text-center">
            <div class="col-12">
                <h1 class="section-title" style="color: var(--text-color);">SUSCRIPCIÓN</h1>
                <p style="color: var(--text-muted);">Únete a nuestra comunidad y mantente informado</p>
            </div>
        </div>

        <div class="row">
            <div class="col-lg-6 mx-auto">
                <div style="background: var(--dark-secondary); border-radius: 20px; padding: 40px; text-align:center;">

                    <i class="fas fa-envelope-open-text" style="font-size:3rem; color: var(--primary-color, #e21f0c); margin-bottom:20px;"></i>

                    <div id="sub-message" class="mb-3" style="display:none;"></div>

                    <form id="subscribeForm" novalidate>
                        <!-- Honeypot -->
                        <div style="display:none;"><input type="text" name="website" tabindex="-1" autocomplete="off"></div>

                        <div class="mb-4 text-left">
                            <label style="color: var(--text-color); margin-bottom: 8px; display: block;">Nombre *</label>
                            <input type="text" name="name" class="search-input" style="width:100%;" placeholder="Tu nombre" required>
                        </div>
                        <div class="mb-4 text-left">
                            <label style="color: var(--text-color); margin-bottom: 8px; display: block;">Correo electrónico *</label>
                            <input type="email" name="email" class="search-input" style="width:100%;" placeholder="tu@correo.com" required>
                        </div>
                        <div class="mb-4 text-left">
                            <label style="color: var(--text-color); cursor:pointer; display:flex; align-items:flex-start; gap:10px;">
                                <input type="checkbox" name="privacy" required style="margin-top:3px; flex-shrink:0;">
                                <span style="font-size:.9em;">
                                    He leído y acepto la
                                    <a href="<?= URLBASE ?>/politica-privacidad/" style="color: var(--primary-color, #e21f0c);" target="_blank">Política de Privacidad</a>
                                    y consiento el tratamiento de mis datos personales. *
                                </span>
                            </label>
                        </div>
                        <button type="submit" id="subBtn" class="btn-artemis w-100">
                            <i class="fas fa-bell mr-2"></i> Suscribirme
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</section>

<script>
document.getElementById('subscribeForm').addEventListener('submit', function(e) {
    e.preventDefault();
    const btn = document.getElementById('subBtn');
    const msg = document.getElementById('sub-message');
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i> Procesando...';

    fetch('<?= URLBASE ?>/actions/subscribe.php', {
        method: 'POST',
        body: new FormData(this)
    })
    .then(r => r.json())
    .then(data => {
        msg.style.display = 'block';
        msg.style.padding = '12px 16px';
        msg.style.borderRadius = '8px';
        if (data.ok) {
            msg.style.background = '#d4edda';
            msg.style.color = '#155724';
            msg.innerHTML = '<i class="fas fa-check-circle mr-2"></i>' + data.msg;
            document.getElementById('subscribeForm').style.display = 'none';
        } else {
            msg.style.background = '#f8d7da';
            msg.style.color = '#721c24';
            msg.innerHTML = '<i class="fas fa-exclamation-circle mr-2"></i>' + data.msg;
            btn.disabled = false;
            btn.innerHTML = '<i class="fas fa-bell mr-2"></i> Suscribirme';
        }
    })
    .catch(() => {
        msg.style.display = 'block';
        msg.style.background = '#f8d7da';
        msg.style.color = '#721c24';
        msg.innerHTML = 'Error de conexión. Inténtalo de nuevo.';
        btn.disabled = false;
        btn.innerHTML = '<i class="fas fa-bell mr-2"></i> Suscribirme';
    });
});
</script>
