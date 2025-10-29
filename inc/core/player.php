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
    // Clases o elementos persistentes
    // ===========================
    const persistentClasses = [
        'owl-carousel',
        'owl-carousel-1',
        'keep-alive',
        'no-reload',
        'music-player',
        'sticky-widget'
    ];

    // Selectores completos (por ejemplo, tu player)
    const persistentSelectors = [
        '.ajax-persist' // tu reproductor iframe debe tener esta clase
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

    // ============================================
    //  Interceptar enlaces y preservar elementos
    // ============================================
    document.addEventListener('click', function(e) {
        const link = e.target.closest('a');
        if (
            link &&
            link.href &&
            link.hostname === window.location.hostname &&
            !link.hasAttribute('download') &&
            !link.hasAttribute('target') &&
            !link.classList.contains('no-ajax') &&
            !link.hasAttribute('data-bs-toggle') &&
            !link.hasAttribute('data-toggle') &&
            link.getAttribute('href') !== '#' &&
            !link.getAttribute('href')?.startsWith('#') &&
            !link.getAttribute('href')?.includes('javascript:')
        ) {
            e.preventDefault();
            e.stopPropagation();

            const url = link.href;
            contentWrapper.style.transition = 'opacity 0.3s';
            contentWrapper.style.opacity = '0.5';
            contentWrapper.style.pointerEvents = 'none';

            const loader = document.createElement('div');
            loader.id = 'ajax-loader';
            loader.style.cssText = 'position:fixed;top:50%;left:50%;transform:translate(-50%,-50%);z-index:9999;';
            loader.innerHTML = '<div class="spinner-border text-primary" role="status"><span class="visually-hidden">Cargando...</span></div>';
            document.body.appendChild(loader);

            fetch(url, {
                headers: { 'X-Requested-With': 'XMLHttpRequest', 'X-Ajax-Navigation': 'true' },
                credentials: 'same-origin'
            })
            .then(response => response.text())
            .then(html => {
                const parser = new DOMParser();
                const doc = parser.parseFromString(html, 'text/html');
                const newContent = doc.querySelector('#pageContent');
                if (newContent) {
                    // ?? Guardar los persistentes
                    const persistents = [];
                    persistentSelectors.forEach(sel => {
                        document.querySelectorAll(sel).forEach(el => {
                            persistents.push({ el, parent: el.parentNode, next: el.nextSibling });
                        });
                    });

                    // Reemplazar contenido
                    contentWrapper.innerHTML = newContent.innerHTML;

                    // ?? Restaurar los persistentes (player, etc.)
                    persistents.forEach(({ el, parent, next }) => {
                        if (!document.body.contains(el)) {
                            if (next) parent.insertBefore(el, next);
                            else parent.appendChild(el);
                        }
                    });

                    // Reinit
                    reinitAllComponents();
                    contentWrapper.style.opacity = '1';
                    contentWrapper.style.pointerEvents = 'auto';
                    const loaderEl = document.getElementById('ajax-loader');
                    if (loaderEl) loaderEl.remove();

                    // Actualizar título
                    const newTitle = doc.querySelector('title');
                    if (newTitle) document.title = newTitle.textContent;

                    window.history.pushState({ path: url }, '', url);
                } else {
                    window.location.href = url;
                }
            })
            .catch(() => (window.location.href = url));
        }
    });

    // Estado inicial
    window.history.replaceState({ path: window.location.href }, '', window.location.href);
    setTimeout(() => reinitAllComponents(), 150);
});
</script>

<?php endif; ?>