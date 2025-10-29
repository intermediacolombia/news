<?php if (!empty($sys['code_player'])): ?>
<style>
.container-fluid.copyright.bg-dark.py-4 {
    padding-bottom: <?= $sys['player_height'] + 10 ?? 70 ?>px!important;
}
</style>


<!-- Player permanente -->
<div id="globalPlayer" style="bottom:0;display:flex;height:70px;left:0;position:fixed;right:0;width:100%;z-index:1500;overflow:hidden;">
    <iframe src="<?= $sys['code_player'] ?? '' ?>" frameborder="0" scrolling="no" style="width:100%;"></iframe>
</div>



<script>
document.addEventListener('click', function (e) {
    const link = e.target.closest('a');
    if (
        !link ||
        !link.href ||
        link.target === '_blank' ||
        link.download ||
        link.href.startsWith('javascript:') ||
        link.getAttribute('href') === '#' ||
        link.hostname !== location.hostname
    ) return;

    e.preventDefault();

    const url = link.href;

    fetch(url, { credentials: 'same-origin' })
        .then(res => res.text())
        .then(html => {
            const parser = new DOMParser();
            const doc = parser.parseFromString(html, 'text/html');

            // Reemplazar <head>
            const newHead = doc.querySelector('head');
            if (newHead) {
                document.head.innerHTML = newHead.innerHTML;
            }

            // Reemplazar contenido del <body>
            const newBody = doc.querySelector('body');
            if (newBody) {
                const player = document.getElementById('globalPlayer');
                const scripts = [];
                document.body.querySelectorAll('script').forEach(s => {
                    if (s.parentElement.id !== 'globalPlayer') scripts.push(s);
                });

                document.body.innerHTML = newBody.innerHTML;

                // Volver a insertar el player si fue movido
                if (player && !document.body.contains(player)) {
                    document.body.insertAdjacentElement('afterend', player);
                }

                // Volver a cargar scripts de la nueva página
                const newScripts = doc.querySelectorAll('script');
                newScripts.forEach(script => {
                    const newScript = document.createElement('script');
                    if (script.src) {
                        newScript.src = script.src;
                        newScript.defer = script.defer;
                        newScript.async = script.async;
                    } else {
                        newScript.textContent = script.textContent;
                    }
                    document.body.appendChild(newScript);
                });
            }

            // Actualizar el título del documento
            const newTitle = doc.querySelector('title');
            if (newTitle) document.title = newTitle.textContent;

            // Actualizar la URL
            window.history.pushState({ path: url }, '', url);
        })
        .catch(err => {
            console.error('Error en navegación AJAX:', err);
            window.location.href = url;
        });
});

// Soporte para navegación con botones del navegador
window.addEventListener('popstate', e => {
    const path = e.state?.path || location.href;
    fetch(path)
        .then(r => r.text())
        .then(html => {
            const parser = new DOMParser();
            const doc = parser.parseFromString(html, 'text/html');

            const newHead = doc.querySelector('head');
            if (newHead) document.head.innerHTML = newHead.innerHTML;

            const newBody = doc.querySelector('body');
            if (newBody) {
                const player = document.getElementById('globalPlayer');
                document.body.innerHTML = newBody.innerHTML;
                if (player && !document.body.contains(player)) {
                    document.body.insertAdjacentElement('afterend', player);
                }
            }

            const newTitle = doc.querySelector('title');
            if (newTitle) document.title = newTitle.textContent;
        })
        .catch(() => location.reload());
});

</script>


<?php endif; ?>