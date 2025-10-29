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
    const mainWrapper = document.getElementById('mainWrapper');
    if (!mainWrapper) return;

    function reinitAllComponents() {
        if (typeof bootstrap !== 'undefined') {
            document.querySelectorAll('[data-bs-toggle="tooltip"]').forEach(el => new bootstrap.Tooltip(el));
            document.querySelectorAll('[data-bs-toggle="popover"]').forEach(el => new bootstrap.Popover(el));
            document.querySelectorAll('.dropdown-toggle').forEach(el => new bootstrap.Dropdown(el));
        }

        if (window.jQuery && typeof jQuery.fn.owlCarousel !== 'undefined') {
            setTimeout(() => {
                jQuery('.owl-carousel').each(function() {
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
    }

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
            mainWrapper.style.transition = 'opacity 0.3s';
            mainWrapper.style.opacity = '0.5';
            mainWrapper.style.pointerEvents = 'none';

            fetch(url, {
                headers: { 'X-Requested-With': 'XMLHttpRequest', 'X-Ajax-Navigation': 'true' },
                credentials: 'same-origin'
            })
            .then(response => response.text())
            .then(html => {
                const parser = new DOMParser();
                const doc = parser.parseFromString(html, 'text/html');
                const newMain = doc.querySelector('#mainWrapper');
                if (newMain) {
                    mainWrapper.innerHTML = newMain.innerHTML;
                    const newTitle = doc.querySelector('title');
                    if (newTitle) document.title = newTitle.textContent;
                    reinitAllComponents();
                    mainWrapper.style.opacity = '1';
                    mainWrapper.style.pointerEvents = 'auto';
                    window.history.pushState({ path: url }, '', url);
                } else {
                    window.location.href = url;
                }
            })
            .catch(() => window.location.href = url);
        }
    });

    window.addEventListener('popstate', function(e) {
        const targetPath = e.state?.path || window.location.href;
        fetch(targetPath, {
            headers: { 'X-Requested-With': 'XMLHttpRequest', 'X-Ajax-Navigation': 'true' },
            credentials: 'same-origin'
        })
        .then(response => response.text())
        .then(html => {
            const parser = new DOMParser();
            const doc = parser.parseFromString(html, 'text/html');
            const newMain = doc.querySelector('#mainWrapper');
            if (newMain) {
                mainWrapper.innerHTML = newMain.innerHTML;
                const newTitle = doc.querySelector('title');
                if (newTitle) document.title = newTitle.textContent;
                reinitAllComponents();
            }
        })
        .catch(() => location.reload());
    });

    window.history.replaceState({ path: window.location.href }, '', window.location.href);
    setTimeout(() => reinitAllComponents(), 150);
});
</script>


<?php endif; ?>