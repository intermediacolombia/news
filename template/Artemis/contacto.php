<?php
$page_title       = 'Contacto | ' . NOMBRE_SITIO;
$page_description = 'Contáctanos para cualquier consulta, comentario o sugerencia.';
$currentPath      = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$page_canonical   = rtrim(URLBASE, '/') . '/' . ltrim($currentPath, '/');
?>

<section class="py-5" style="background: var(--dark); min-height: 60vh;">
    <div class="container">
        <div class="row mb-4 text-center">
            <div class="col-12">
                <h1 class="section-title" style="color: var(--text-color);">CONTACTO</h1>
                <p style="color: var(--text-muted);">¿Tienes alguna pregunta? Escríbenos</p>
            </div>
        </div>

        <div class="row">
            <div class="col-lg-8 mx-auto">
                <div style="background: var(--dark-secondary); border-radius: 20px; padding: 40px;">

                    <div id="contact-message" class="mb-3" style="display:none;"></div>

                    <form id="contactForm" novalidate>
                        <!-- Honeypot -->
                        <div style="display:none;"><input type="text" name="website" tabindex="-1" autocomplete="off"></div>

                        <div class="mb-4">
                            <label style="color: var(--text-color); margin-bottom: 8px; display: block;">Nombre *</label>
                            <input type="text" name="name" class="search-input" style="width:100%;" placeholder="Tu nombre completo" required>
                        </div>
                        <div class="mb-4">
                            <label style="color: var(--text-color); margin-bottom: 8px; display: block;">Correo electrónico *</label>
                            <input type="email" name="email" class="search-input" style="width:100%;" placeholder="tu@correo.com" required>
                        </div>
                        <div class="mb-4">
                            <label style="color: var(--text-color); margin-bottom: 8px; display: block;">Teléfono <span style="color:var(--text-muted);font-size:.85em;">(opcional)</span></label>
                            <input type="tel" name="phone" class="search-input" style="width:100%;" placeholder="+34 600 000 000">
                        </div>
                        <div class="mb-4">
                            <label style="color: var(--text-color); margin-bottom: 8px; display: block;">Mensaje *</label>
                            <textarea name="message" class="search-input" style="width:100%; min-height:150px; resize:vertical;" placeholder="Escribe tu mensaje..." required></textarea>
                        </div>
                        <button type="submit" id="contactBtn" class="btn-artemis w-100">
                            <i class="fas fa-paper-plane mr-2"></i> Enviar mensaje
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</section>

<script>
document.getElementById('contactForm').addEventListener('submit', function(e) {
    e.preventDefault();
    const btn = document.getElementById('contactBtn');
    const msg = document.getElementById('contact-message');
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i> Enviando...';

    fetch('<?= URLBASE ?>/actions/contact.php', {
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
            document.getElementById('contactForm').reset();
        } else {
            msg.style.background = '#f8d7da';
            msg.style.color = '#721c24';
            msg.innerHTML = '<i class="fas fa-exclamation-circle mr-2"></i>' + data.msg;
        }
    })
    .catch(() => {
        msg.style.display = 'block';
        msg.style.background = '#f8d7da';
        msg.style.color = '#721c24';
        msg.innerHTML = 'Error de conexión. Inténtalo de nuevo.';
    })
    .finally(() => {
        btn.disabled = false;
        btn.innerHTML = '<i class="fas fa-paper-plane mr-2"></i> Enviar mensaje';
    });
});
</script>
