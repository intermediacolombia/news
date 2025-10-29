<?php if (!empty($sys['code_player'])): ?>
<style>
.container-fluid.copyright.bg-dark.py-4 {
    padding-bottom: <?= $sys['player_height'] + 10 ?? 70 ?>px!important;
}
</style>


<!-- Player permanente -->
 <div id="globalPlayer" style="position:fixed;left:0;right:0;bottom:0;height:<?= $sys['player_height'] ?? 70 ?>px;z-index:1500;overflow:hidden;display:flex;">
    <iframe src="<?= $sys['code_player'] ?? '' ?>" frameborder="0" scrolling="no" style="width:100%;height:100%;"></iframe>
  </div>



<script>
(function() {
  const player = document.getElementById('globalPlayer');

  function replaceDocument(html) {
    const parser = new DOMParser();
    const newDoc = parser.parseFromString(html, 'text/html');

    // 1. Guardar referencia al reproductor actual
    const playerParent = player?.parentNode;
    const playerNext = player?.nextSibling;

    // 2. Reemplazar el contenido completo del documento
    document.documentElement.replaceWith(newDoc.documentElement);

    // 3. Reinsertar el reproductor original en la nueva estructura
    if (player && playerParent) {
      const existing = document.getElementById('globalPlayer');
      if (existing) existing.remove(); // eliminar duplicado del nuevo HTML
      if (playerNext) playerParent.insertBefore(player, playerNext);
      else document.body.insertAdjacentElement('afterend', player);
    }

    // 4. Forzar ejecución de todos los scripts del nuevo documento
    const scripts = document.querySelectorAll('script');
    scripts.forEach(script => {
      const newScript = document.createElement('script');
      Array.from(script.attributes).forEach(attr => newScript.setAttribute(attr.name, attr.value));
      if (script.textContent) newScript.textContent = script.textContent;
      script.parentNode.replaceChild(newScript, script);
    });
  }

  function navigateTo(url, push = true) {
    fetch(url, { credentials: 'same-origin' })
      .then(res => {
        if (!res.ok) throw new Error(res.status);
        return res.text();
      })
      .then(html => {
        replaceDocument(html);
        if (push) history.pushState({ path: url }, '', url);
      })
      .catch(err => {
        console.error('Error de navegación:', err);
        location.href = url;
      });
  }

  // Interceptar enlaces internos
  document.addEventListener('click', function(e) {
    const a = e.target.closest('a');
    if (!a) return;
    if (a.target || a.hasAttribute('download') || a.href.startsWith('javascript:') || a.getAttribute('href') === '#') return;
    if (a.hostname !== location.hostname) return;

    e.preventDefault();
    navigateTo(a.href, true);
  });

  // Soporte para atrás/adelante
  window.addEventListener('popstate', e => {
    const path = e.state?.path || location.href;
    navigateTo(path, false);
  });
})();



</script>


<?php endif; ?>