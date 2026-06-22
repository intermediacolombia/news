<?php
$page_title       = 'Suscripción | ' . NOMBRE_SITIO;
$page_description = 'Suscríbete a nuestro boletín y recibe las últimas noticias de ' . NOMBRE_SITIO . '.';
$currentPath      = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$page_canonical   = rtrim(URLBASE, '/') . '/' . ltrim($currentPath, '/');
?>
<!-- Breadcrumb Area Start Here -->
<section class="breadcrumbs-area" style="background-image: url('<?= URLBASE ?>/template/NewsEdge/img/banner/breadcrumbs-banner.jpg');">
    <div class="container">
        <div class="breadcrumbs-content">
            <h1>Suscripción</h1>
            <ul>
                <li><a href="<?= URLBASE ?>"><?= t_theme('theme_inicio') ?></a><i class="fa fa-angle-right" aria-hidden="true"></i></li>
                <li>Suscripción</li>
            </ul>
        </div>
    </div>
</section>
<!-- Breadcrumb Area End Here -->

<section class="bg-body section-space-less30">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-6 col-md-8">
                <div class="item-box-light-md item-shadow-1 p-30 text-center" style="border-radius:4px;">
                    <i class="fa fa-envelope-open" style="font-size:3rem; color:var(--primary); margin-bottom:20px;"></i>
                    <h3 class="title-semibold-dark size-lg mb-10">Únete a nuestra comunidad</h3>
                    <p class="description-body-light mb-30">Mantente informado con nuestro boletín</p>

                    <div id="sub-message" class="mb-20" style="display:none;"></div>

                    <form id="subscribeForm" novalidate>
                        <div style="display:none;"><input type="text" name="website" tabindex="-1" autocomplete="off"></div>
                        <div class="mb-20 text-left">
                            <label class="description-body-dark mb-10" style="display:block;">Nombre *</label>
                            <input type="text" name="name" class="form-control contact-input" placeholder="Tu nombre" required>
                        </div>
                        <div class="mb-20 text-left">
                            <label class="description-body-dark mb-10" style="display:block;">Correo electrónico *</label>
                            <input type="email" name="email" class="form-control contact-input" placeholder="tu@correo.com" required>
                        </div>
                        <div class="mb-20 text-left">
                            <label style="cursor:pointer; display:flex; align-items:flex-start; gap:8px;">
                                <input type="checkbox" name="privacy" required style="margin-top:3px; flex-shrink:0;">
                                <span class="description-body-light" style="font-size:.9em;">
                                    He leído y acepto la
                                    <a href="<?= URLBASE ?>/politica-privacidad/" style="color:var(--primary);" target="_blank">Política de Privacidad</a>
                                    y consiento el tratamiento de mis datos personales. *
                                </span>
                            </label>
                        </div>
                        <button type="submit" id="subBtn" class="contact-submit-btn" style="width:100%;">
                            <i class="fa fa-bell" style="margin-right:6px;"></i> Suscribirme
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
    var btn = document.getElementById('subBtn');
    var msg = document.getElementById('sub-message');
    btn.disabled = true;
    btn.innerHTML = '<i class="fa fa-spinner fa-spin" style="margin-right:6px;"></i> Procesando...';
    fetch('<?= URLBASE ?>/actions/subscribe.php', { method: 'POST', body: new FormData(this) })
    .then(function(r) { return r.json(); })
    .then(function(data) {
        msg.style.display = 'block';
        msg.style.padding = '12px 16px';
        msg.style.borderRadius = '4px';
        if (data.ok) {
            msg.style.background = '#d4edda';
            msg.style.color = '#155724';
            msg.innerHTML = '<i class="fa fa-check-circle" style="margin-right:6px;"></i>' + data.msg;
            document.getElementById('subscribeForm').style.display = 'none';
        } else {
            msg.style.background = '#f8d7da';
            msg.style.color = '#721c24';
            msg.innerHTML = '<i class="fa fa-exclamation-circle" style="margin-right:6px;"></i>' + data.msg;
            btn.disabled = false;
            btn.innerHTML = '<i class="fa fa-bell" style="margin-right:6px;"></i> Suscribirme';
        }
    })
    .catch(function() {
        msg.style.display = 'block';
        msg.style.background = '#f8d7da';
        msg.style.color = '#721c24';
        msg.innerHTML = 'Error de conexión. Inténtalo de nuevo.';
        btn.disabled = false;
        btn.innerHTML = '<i class="fa fa-bell" style="margin-right:6px;"></i> Suscribirme';
    });
});
</script>
