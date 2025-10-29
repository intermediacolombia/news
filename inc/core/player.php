<?php if (!empty($sys['code_player'])): ?>
<style>
.container-fluid.copyright.bg-dark.py-4 {
    padding-bottom: <?= $sys['player_height'] + 10 ?? 70 ?>px!important;
}
</style>

<!-- PLAYER CON IFRAME (se mantiene igual) -->
<div class="music-player" style="bottom: 0;display: flex;height: <?= $sys['player_height'] ?? 70 ?>px;left: 0;position: fixed;right: 0;width: 100%;z-index: 1500;overflow: hidden;">
    <iframe src="<?= $sys['code_player'] ?? '' ?>" frameborder="0" scrolling="no" style="width: 100%;"></iframe>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const contentWrapper = document.getElementById('pageContent');
    if (!contentWrapper) return;

    // ===========================
    // 🔒 Clases que NO se destruyen
    // ===========================
    const persistentClasses = [
        'owl-carousel',
        'owl-carousel-1',
        'keep-alive',
        'no-reload',
        'music-player',
        'sticky-widget'
    ];

    function shouldPreserveElement(el) {
        return persistentClasses.some(cls => el.classList.contains(cls));
    }

    // ===========================
    // Reinicializar componentes
    // ===========================
    function reinitAllComponents() {
        // 1. DESTRUIR Owl Carousel (excepto los que se preservan)
        if (window.jQuery && typeof jQuery.fn.owlCarousel !== 'undefined') {
            jQuery('.owl-carousel').each(function() {
                const $carousel = jQuery(this);
                if (shouldPreserveElement(this)) return; // no destruir los "protegidos"
                if ($carousel.data('owl.carousel')) {
                    $carousel.trigger('destroy.owl.carousel');
                    $carousel.removeClass('owl-loaded owl-drag owl-grab');
                    $carousel.find('.owl-stage-outer').children().unwrap();
                }
            });
        }

        // 2. Reinicializar Bootstrap
        if (typeof bootstrap !== 'undefined') {
            const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
            tooltipTriggerList.map(el => new bootstrap.Tooltip(el));

            const popoverTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="popover"]'));
            popoverTriggerList.map(el => new bootstrap.Popover(el));

            const dropdownElementList = [].slice.call(document.querySelectorAll('.dropdown-toggle'));
            dropdownElementList.map(el => new bootstrap.Dropdown(el));

            const modalElementList = [].slice.call(document.querySelectorAll('.modal'));
            modalElementList.map(el => new bootstrap.Modal(el));

            const carouselElementList = [].slice.call(document.querySelectorAll('.carousel'));
            carouselElementList.forEach(el => {
                const oldInstance = bootstrap.Carousel.getInstance(el);
                if (oldInstance) oldInstance.dispose();
                const newCarousel = new bootstrap.Carousel(el);
                if (el.getAttribute('data-bs-ride') === 'carousel') newCarousel.cycle();
            });

            const toastElList = [].slice.call(document.querySelectorAll('.toast'));
            toastElList.map(el => new bootstrap.Toast(el));

            const offcanvasElementList = [].slice.call(document.querySelectorAll('.offcanvas'));
            offcanvasElementList.map(el => new bootstrap.Offcanvas(el));
        }

        // 3. REINICIALIZAR Owl Carousel (solo si no es persistente)
        if (window.jQuery && typeof jQuery.fn.owlCarousel !== 'undefined') {
            setTimeout(function() {
                jQuery('.owl-carousel').each(function() {
                    if (shouldPreserveElement(this)) return; // no reinicializar los persistentes
                    const $carousel = jQuery(this);
                    const options = {
                        loop: $carousel.data('loop') ?? true,
                        margin: $carousel.data('margin') || 10,
                        nav: $carousel.data('nav') ?? true,
                        dots: $carousel.data('dots') ?? true,
                        autoplay: $carousel.data('autoplay') ?? false,
                        autoplayTimeout: $carousel.data('autoplay-timeout') || 3000,
                        autoplayHoverPause: $carousel.data('autoplay-hover-pause') ?? true,
                        responsive: $carousel.data('responsive') || {
                            0: { items: $carousel.data('items-mobile') || 1 },
                            600: { items: $carousel.data('items-tablet') || 2 },
                            1000: { items: $carousel.data('items') || 3 }
                        }
                    };
                    $carousel.owlCarousel(options);
                });
            }, 50);
        }

        // 4. Ejecutar scripts inline nuevos
        const scripts = contentWrapper.querySelectorAll('script');
        scripts.forEach(oldScript => {
            const newScript = document.createElement('script');
            if (oldScript.src) {
                newScript.src = oldScript.src;
                newScript.async = false;
            } else {
                newScript.textContent = oldScript.textContent;
            }
            Array.from(oldScript.attributes).forEach(attr => {
                newScript.setAttribute(attr.name, attr.value);
            });
            oldScript.parentNode.replaceChild(newScript, oldScript);
        });

        // 5. Reinicializar jQuery o lanzar eventos
        if (window.jQuery) jQuery(document).trigger('contentLoaded');
        window.dispatchEvent(new CustomEvent('pageContentLoaded', { detail: { container: contentWrapper } }));
    }

    // 🔹 Aquí continúa tu código AJAX tal cual...
    // (No se toca nada de la parte del fetch, popstate o history)
});
</script>

<?php endif; ?>