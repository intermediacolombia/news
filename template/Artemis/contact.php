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
                            <label style="color: var(--text-color); margin-bottom: 8px; display: block;">Nombre</label>
                            <input type="text" class="search-input" style="width: 100%;" placeholder="Tu nombre">
                        </div>
                        <div class="form-group mb-4">
                            <label style="color: var(--text-color); margin-bottom: 8px; display: block;">Email</label>
                            <input type="email" class="search-input" style="width: 100%;" placeholder="tu@email.com">
                        </div>
                        <div class="form-group mb-4">
                            <label style="color: var(--text-color); margin-bottom: 8px; display: block;">Mensaje</label>
                            <textarea class="search-input" style="width: 100%; min-height: 150px;" placeholder="Escribe tu mensaje..."></textarea>
                        </div>
                        <button type="submit" class="btn-artemis w-100">
                            <i class="fas fa-paper-plane mr-2"></i>Enviar Mensaje
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</section>