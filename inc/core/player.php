<?php if (!empty($sys['code_player'])): ?>
<style>
.container-fluid.copyright.bg-dark.py-4 {
    padding-bottom: <?= $sys['player_height'] + 10 ?? 70 ?>px!important;
}
</style>

<!-- PLAYER CON IFRAME (se mantiene igual) -->
<div class="ajax-persist"
     style="bottom: 0;display: flex;height: <?= $sys['player_height'] ?? 70 ?>px;left: 0;position: fixed;right: 0;width: 100%;z-index: 1500;overflow: hidden;">
    <iframe src="<?= $sys['code_player'] ?? '' ?>" frameborder="0" scrolling="no" style="width: 100%;"></iframe>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const contentWrapper = document.getElementById('pageContent');
    if (!contentWrapper) return;

    // ============================================
    // Configuración: Clases / elementos persistentes
    // ============================================
    const persistentClasses = [
        'owl-carousel',
        'keep-alive',
        'no-reload',
        'music-player',
        'sticky-widget',
		'container-fluid'
    ];

    // Selectores de elementos que NUNCA deben recargarse
    const persistentSelectors = [
        '.ajax-persist' // tu reproductor
    ];

    function shouldPreserveElement(el) {
        return persistentClasses.some(cls => el.classList.contains(cls));
    }

    // ============================================
    // Reinicializar componentes dinámicos
    // ============================================
    function reinitAllComponents() {
        // Destruir Owl Carousel (excepto los persistentes)
        if (window.jQuery && typeof jQuery.fn.owlCarousel !== 'undefined') {
            jQuery('.owl-carousel').each(function() {
                const $carousel = jQuery(this);
                if (shouldPreserveElement(this)) return;
                if ($carousel.data('owl.carousel')) {
                    $carousel.trigger('destroy.owl.carousel');
                    $carousel.removeClass('owl-loaded owl-drag owl-grab');
                    $carousel.find('.owl-stage-outer').children().unwrap();
                }
            });
        }

        // Reinicializar Bootstrap
        if (typeof bootstrap !== 'undefined') {
            document.querySelectorAll('[data-bs-toggle="tooltip"]').forEach(el => new bootstrap.Tooltip(el));
            document.querySelectorAll('[data-bs-toggle="popover"]').forEach(el => new bootstrap.Popover(el));
            document.querySelectorAll('.dropdown-toggle').forEach(el => new bootstrap.Dropdown(el));
            document.querySelectorAll('.offcanvas').forEach(el => new bootstrap.Offcanvas(el));
            document.querySelectorAll('.toast').forEach(el => new bootstrap.Toast(el));

            document.querySelectorAll('.carousel').forEach(el => {
                const old = bootstrap.Carousel.getInstance(el);
                if (old) old.dispose();
                const c = new bootstrap.Carousel(el);
                if (el.getAttribute('data-bs-ride') === 'carousel') c.cycle();
            });
        }

        // Reinstanciar Owl Carousel
        if (window.jQuery && typeof jQuery.fn.owlCarousel !== 'undefined') {
            setTimeout(() => {
                jQuery('.owl-carousel').each(function() {
                    if (shouldPreserveElement(this)) return;
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

        // Ejecutar scripts inline nuevos
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

        // Reinicializar jQuery o lanzar eventos
        if (window.jQuery) jQuery(document).trigger('contentLoaded');
        window.dispatchEvent(new CustomEvent('pageContentLoaded', { detail: { container: contentWrapper } }));
    }

    // ============================================
    // Interceptar enlaces y preservar elementos
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
                    // Guardar los persistentes
                    const persistents = [];
                    persistentSelectors.forEach(sel => {
                        document.querySelectorAll(sel).forEach(el => {
                            persistents.push({ el, parent: el.parentNode, next: el.nextSibling });
                        });
                    });

                    // Reemplazar contenido
                    contentWrapper.innerHTML = newContent.innerHTML;

                    // Restaurar los persistentes (player, etc.)
                    persistents.forEach(({ el, parent, next }) => {
                        if (!document.body.contains(el)) {
                            if (next) parent.insertBefore(el, next);
                            else parent.appendChild(el);
                        }
                    });

                    reinitAllComponents();
                    contentWrapper.style.opacity = '1';
                    contentWrapper.style.pointerEvents = 'auto';
                    const loaderEl = document.getElementById('ajax-loader');
                    if (loaderEl) loaderEl.remove();

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