<?php if (!empty($sys['code_player'])): ?>
<style>
.container-fluid.copyright.bg-dark.py-4 {
    padding-bottom: <?= $sys['player_height'] + 10 ?? 70 ?>px!important;
}
</style>

<!-- PLAYER CON IFRAME (se mantiene igual) -->
<div style="bottom: 0;display: flex;height: <?= $sys['player_height'] ?? 70 ?>px;left: 0;position: fixed;right: 0;width: 100%;z-index: 1500;overflow: hidden;">
    <iframe src="<?= $sys['code_player'] ?? '' ?>" frameborder="0" scrolling="no" style="width: 100%;"></iframe>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const contentWrapper = document.getElementById('pageContent');
    
    if (!contentWrapper) return;
    
    // Función para reinicializar TODO (Bootstrap, jQuery, Owl Carousel, etc)
    function reinitAllComponents() {
        // 1. DESTRUIR Owl Carousel existentes PRIMERO
        if (window.jQuery && typeof jQuery.fn.owlCarousel !== 'undefined') {
            jQuery('.owl-carousel').each(function() {
                const $carousel = jQuery(this);
                
                // Destruir instancia anterior si existe
                if ($carousel.data('owl.carousel')) {
                    $carousel.trigger('destroy.owl.carousel');
                    $carousel.removeClass('owl-loaded owl-drag owl-grab');
                    $carousel.find('.owl-stage-outer').children().unwrap();
                }
            });
        }
        
        // 2. Reinicializar componentes de Bootstrap
        if (typeof bootstrap !== 'undefined') {
            // Tooltips
            const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
            tooltipTriggerList.map(function (tooltipTriggerEl) {
                return new bootstrap.Tooltip(tooltipTriggerEl);
            });
            
            // Popovers
            const popoverTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="popover"]'));
            popoverTriggerList.map(function (popoverTriggerEl) {
                return new bootstrap.Popover(popoverTriggerEl);
            });
            
            // Dropdowns
            const dropdownElementList = [].slice.call(document.querySelectorAll('.dropdown-toggle'));
            dropdownElementList.map(function (dropdownToggleEl) {
                return new bootstrap.Dropdown(dropdownToggleEl);
            });
            
            // Modales
            const modalElementList = [].slice.call(document.querySelectorAll('.modal'));
            modalElementList.map(function (modalEl) {
                return new bootstrap.Modal(modalEl);
            });
            
            // Carruseles Bootstrap
            const carouselElementList = [].slice.call(document.querySelectorAll('.carousel'));
            carouselElementList.forEach(function (carouselEl) {
                const oldInstance = bootstrap.Carousel.getInstance(carouselEl);
                if (oldInstance) {
                    oldInstance.dispose();
                }
                
                const newCarousel = new bootstrap.Carousel(carouselEl, {
                    interval: carouselEl.getAttribute('data-bs-interval') || 5000,
                    wrap: carouselEl.getAttribute('data-bs-wrap') !== 'false',
                    keyboard: carouselEl.getAttribute('data-bs-keyboard') !== 'false',
                    pause: carouselEl.getAttribute('data-bs-pause') || 'hover',
                    ride: carouselEl.getAttribute('data-bs-ride') || false,
                    touch: carouselEl.getAttribute('data-bs-touch') !== 'false'
                });
                
                if (carouselEl.getAttribute('data-bs-ride') === 'carousel') {
                    newCarousel.cycle();
                }
            });
            
            // Toasts
            const toastElList = [].slice.call(document.querySelectorAll('.toast'));
            toastElList.map(function (toastEl) {
                return new bootstrap.Toast(toastEl);
            });
            
            // Offcanvas
            const offcanvasElementList = [].slice.call(document.querySelectorAll('.offcanvas'));
            offcanvasElementList.map(function (offcanvasEl) {
                return new bootstrap.Offcanvas(offcanvasEl);
            });
        }
        
        // 3. REINICIALIZAR Owl Carousel
        if (window.jQuery && typeof jQuery.fn.owlCarousel !== 'undefined') {
            // Pequeño delay para asegurar que el DOM está listo
            setTimeout(function() {
                jQuery('.owl-carousel').each(function() {
                    const $carousel = jQuery(this);
                    
                    // Obtener configuración del data-attribute o usar default
                    const options = {
                        loop: $carousel.data('loop') !== undefined ? $carousel.data('loop') : true,
                        margin: $carousel.data('margin') || 10,
                        nav: $carousel.data('nav') !== undefined ? $carousel.data('nav') : true,
                        dots: $carousel.data('dots') !== undefined ? $carousel.data('dots') : true,
                        autoplay: $carousel.data('autoplay') !== undefined ? $carousel.data('autoplay') : false,
                        autoplayTimeout: $carousel.data('autoplay-timeout') || 3000,
                        autoplayHoverPause: $carousel.data('autoplay-hover-pause') !== undefined ? $carousel.data('autoplay-hover-pause') : true,
                        responsive: $carousel.data('responsive') || {
                            0: {
                                items: $carousel.data('items-mobile') || 1
                            },
                            600: {
                                items: $carousel.data('items-tablet') || 2
                            },
                            1000: {
                                items: $carousel.data('items') || 3
                            }
                        }
                    };
                    
                    // Inicializar Owl Carousel
                    $carousel.owlCarousel(options);
                });
            }, 50);
        }
        
        // 4. Ejecutar scripts inline del nuevo contenido
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
        
        // 5. Reinicializar jQuery si existe
        if (window.jQuery) {
            jQuery(document).trigger('contentLoaded');
        }
        
        // 6. Evento personalizado global
        window.dispatchEvent(new CustomEvent('pageContentLoaded', {
            detail: { container: contentWrapper }
        }));
        
        // 7. Reinicializar validaciones de formularios HTML5
        const forms = contentWrapper.querySelectorAll('form');
        forms.forEach(form => {
            if (form.hasAttribute('novalidate')) {
                form.setAttribute('novalidate', '');
            }
        });
        
        // 8. Lazy loading de imágenes
        if ('loading' in HTMLImageElement.prototype) {
            const images = contentWrapper.querySelectorAll('img[loading="lazy"]');
            images.forEach(img => {
                if (img.loading === 'lazy') {
                    img.loading = 'lazy';
                }
            });
        }
    }
    
    // Interceptar clicks en enlaces internos
    document.addEventListener('click', function(e) {
        const link = e.target.closest('a');
        
        if (link && 
            link.href && 
            link.hostname === window.location.hostname &&
            !link.hasAttribute('download') &&
            !link.hasAttribute('target') &&
            !link.classList.contains('no-ajax') &&
            !link.hasAttribute('data-bs-toggle') &&
            !link.hasAttribute('data-toggle') &&
            link.getAttribute('href') !== '#' &&
            !link.getAttribute('href')?.startsWith('#') &&
            !link.getAttribute('href')?.includes('javascript:')) {
            
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
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-Ajax-Navigation': 'true'
                },
                credentials: 'same-origin'
            })
            .then(response => {
                if (!response.ok) throw new Error('Error HTTP: ' + response.status);
                return response.text();
            })
            .then(html => {
                const parser = new DOMParser();
                const doc = parser.parseFromString(html, 'text/html');
                const newContent = doc.querySelector('#pageContent');
                
                if (newContent) {
                    // Limpiar Owl Carousel antes de cambiar contenido
                    if (window.jQuery && typeof jQuery.fn.owlCarousel !== 'undefined') {
                        jQuery('.owl-carousel').each(function() {
                            const $carousel = jQuery(this);
                            if ($carousel.data('owl.carousel')) {
                                $carousel.trigger('destroy.owl.carousel');
                                $carousel.removeClass('owl-loaded owl-drag owl-grab');
                            }
                        });
                    }
                    
                    // Limpiar Bootstrap components
                    if (typeof bootstrap !== 'undefined') {
                        const tooltips = contentWrapper.querySelectorAll('[data-bs-toggle="tooltip"]');
                        tooltips.forEach(el => {
                            const instance = bootstrap.Tooltip.getInstance(el);
                            if (instance) instance.dispose();
                        });
                        
                        const popovers = contentWrapper.querySelectorAll('[data-bs-toggle="popover"]');
                        popovers.forEach(el => {
                            const instance = bootstrap.Popover.getInstance(el);
                            if (instance) instance.dispose();
                        });
                        
                        const carousels = contentWrapper.querySelectorAll('.carousel');
                        carousels.forEach(el => {
                            const instance = bootstrap.Carousel.getInstance(el);
                            if (instance) {
                                instance.pause();
                                instance.dispose();
                            }
                        });
                        
                        const modals = document.querySelectorAll('.modal.show');
                        modals.forEach(modalEl => {
                            const instance = bootstrap.Modal.getInstance(modalEl);
                            if (instance) instance.hide();
                        });
                    }
                    
                    // Actualizar contenido
                    contentWrapper.innerHTML = newContent.innerHTML;
                    
                    // Actualizar título
                    const newTitle = doc.querySelector('title');
                    if (newTitle) {
                        document.title = newTitle.textContent;
                    }
                    
                    // Actualizar meta tags
                    const newDesc = doc.querySelector('meta[name="description"]');
                    const currentDesc = document.querySelector('meta[name="description"]');
                    if (newDesc && currentDesc) {
                        currentDesc.setAttribute('content', newDesc.getAttribute('content'));
                    }
                    
                    // Actualizar canonical
                    const newCanonical = doc.querySelector('link[rel="canonical"]');
                    const currentCanonical = document.querySelector('link[rel="canonical"]');
                    if (newCanonical && currentCanonical) {
                        currentCanonical.setAttribute('href', newCanonical.getAttribute('href'));
                    }
                    
                    // Actualizar URL
                    window.history.pushState({path: url}, '', url);
                    
                    // Scroll arriba
                    window.scrollTo({top: 0, behavior: 'smooth'});
                    
                    // Delay para que las imágenes se carguen antes de inicializar Owl
                    setTimeout(() => {
                        // Reinicializar TODO
                        reinitAllComponents();
                        
                        // Restaurar interacción
                        contentWrapper.style.opacity = '1';
                        contentWrapper.style.pointerEvents = 'auto';
                        
                        // Remover loader
                        const loaderEl = document.getElementById('ajax-loader');
                        if (loaderEl) loaderEl.remove();
                    }, 150); // 150ms para Owl Carousel
                    
                    // Google Analytics
                    if (typeof gtag !== 'undefined') {
                        gtag('config', 'GA_MEASUREMENT_ID', {
                            page_path: new URL(url).pathname
                        });
                    }
                    
                    // Facebook Pixel
                    if (typeof fbq !== 'undefined') {
                        fbq('track', 'PageView');
                    }
                    
                } else {
                    console.warn('No se encontró #pageContent, navegando normalmente');
                    window.location.href = url;
                }
            })
            .catch(error => {
                console.error('Error en navegación AJAX:', error);
                window.location.href = url;
            });
        }
    });
    
    // Manejar botón atrás/adelante
    window.addEventListener('popstate', function(e) {
        const targetPath = e.state?.path || window.location.href;
        
        fetch(targetPath, {
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'X-Ajax-Navigation': 'true'
            },
            credentials: 'same-origin'
        })
        .then(response => response.text())
        .then(html => {
            const parser = new DOMParser();
            const doc = parser.parseFromString(html, 'text/html');
            const newContent = doc.querySelector('#pageContent');
            
            if (newContent) {
                // Limpiar Owl Carousel
                if (window.jQuery && typeof jQuery.fn.owlCarousel !== 'undefined') {
                    jQuery('.owl-carousel').each(function() {
                        const $carousel = jQuery(this);
                        if ($carousel.data('owl.carousel')) {
                            $carousel.trigger('destroy.owl.carousel');
                            $carousel.removeClass('owl-loaded owl-drag owl-grab');
                        }
                    });
                }
                
                // Limpiar Bootstrap
                if (typeof bootstrap !== 'undefined') {
                    const tooltips = contentWrapper.querySelectorAll('[data-bs-toggle="tooltip"]');
                    tooltips.forEach(el => {
                        const instance = bootstrap.Tooltip.getInstance(el);
                        if (instance) instance.dispose();
                    });
                    
                    const carousels = contentWrapper.querySelectorAll('.carousel');
                    carousels.forEach(el => {
                        const instance = bootstrap.Carousel.getInstance(el);
                        if (instance) {
                            instance.pause();
                            instance.dispose();
                        }
                    });
                }
                
                contentWrapper.innerHTML = newContent.innerHTML;
                
                const newTitle = doc.querySelector('title');
                if (newTitle) {
                    document.title = newTitle.textContent;
                }
                
                window.scrollTo({top: 0, behavior: 'smooth'});
                
                setTimeout(() => {
                    reinitAllComponents();
                }, 150);
            }
        })
        .catch(error => {
            console.error('Error en popstate:', error);
            location.reload();
        });
    });
    
    // Guardar estado inicial
    window.history.replaceState({path: window.location.href}, '', window.location.href);
    
    // Inicializar componentes en la carga inicial
    setTimeout(() => {
        reinitAllComponents();
    }, 150);
});
</script>
<?php endif; ?>