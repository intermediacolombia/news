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
(function () {
  // Utilidades de comparación
  function assetKey(el) {
    // Normaliza para comparar: para <link> usa href + rel; para <script> usa src
    if (el.tagName === 'LINK') return (el.getAttribute('rel') || '') + '|' + (new URL(el.href, location.href)).href;
    if (el.tagName === 'SCRIPT') return (new URL(el.src, location.href)).href;
    return '';
  }

  function mapHeadAssets(doc) {
    const links = Array.from(doc.querySelectorAll('head link[rel="stylesheet"]'));
    const scripts = Array.from(doc.querySelectorAll('head script[src]'));
    return {
      links,
      scripts,
      linkKeys: new Set(links.map(assetKey)),
      scriptKeys: new Set(scripts.map(assetKey))
    };
  }

  function currentHeadAssets() {
    const links = Array.from(document.head.querySelectorAll('link[rel="stylesheet"]'));
    const scripts = Array.from(document.head.querySelectorAll('script[src]'));
    return {
      links,
      scripts,
      linkKeys: new Set(links.map(assetKey)),
      scriptKeys: new Set(scripts.map(assetKey))
    };
  }

  function loadNewLinks(newLinks, currKeys) {
    const toAppend = [];
    newLinks.forEach(link => {
      const key = assetKey(link);
      if (!currKeys.has(key)) {
        const n = document.createElement('link');
        // Copiar atributos relevantes
        Array.from(link.attributes).forEach(a => n.setAttribute(a.name, a.value));
        toAppend.push(n);
      }
    });
    // Insertar al final del <head> para respetar cascada
    toAppend.forEach(n => document.head.appendChild(n));
  }

  function removeOldLinks(currLinks, newKeys) {
    // Opcional: elimina CSS que ya no están en la nueva página
    currLinks.forEach(link => {
      const key = assetKey(link);
      if (!newKeys.has(key)) {
        link.parentNode.removeChild(link);
      }
    });
  }

  function loadNewHeadScriptsSequential(newScripts, currKeys) {
    // Carga secuencial para respetar el orden de dependencias
    const list = newScripts
      .filter(s => !currKeys.has(assetKey(s)))
      .map(s => {
        const n = document.createElement('script');
        Array.from(s.attributes).forEach(a => n.setAttribute(a.name, a.value)); // src, defer, async, etc.
        return n;
      });

    return list.reduce((p, s) => p.then(() => new Promise((res, rej) => {
      s.onload = () => res();
      s.onerror = () => rej(new Error('Fallo cargando script: ' + (s.src || 'inline-head')));
      document.head.appendChild(s);
      // Si es async/defer puede no disparar onload en el orden esperado; preferimos no usar async aquí si no es necesario.
    })), Promise.resolve());
  }

  function executeInlineScripts(container) {
    // Re-ejecuta scripts inline dentro del contenedor (en orden)
    const scripts = Array.from(container.querySelectorAll('script'));
    scripts.forEach(old => {
      const n = document.createElement('script');
      // Replicar atributos no-src (type, data-*)
      Array.from(old.attributes).forEach(a => {
        if (a.name !== 'src') n.setAttribute(a.name, a.value);
      });
      if (old.src) {
        // Si hay scripts con src dentro del body, recrearlos también
        n.src = old.src;
        n.async = old.async;
        n.defer = old.defer;
      } else {
        n.textContent = old.textContent;
      }
      old.parentNode.replaceChild(n, old);
    });
  }

  function updateTitle(fromDoc) {
    const t = fromDoc.querySelector('title');
    if (t) document.title = t.textContent;
  }

  function swapAppRoot(fromDoc) {
    const newApp = fromDoc.querySelector('#appRoot');
    const app = document.getElementById('appRoot');
    if (!app || !newApp) return false;

    // Reemplazar contenido interno de #appRoot
    app.innerHTML = newApp.innerHTML;

    // Ejecutar scripts inline del nuevo #appRoot
    executeInlineScripts(app);
    return true;
  }

  function fullyApplyDocument(fromDoc) {
    // 1) HEAD: diff de CSS y JS externos
    const curr = currentHeadAssets();
    const nxt = mapHeadAssets(fromDoc);

    // CSS: cargar nuevos y opcionalmente eliminar viejos
    loadNewLinks(nxt.links, curr.linkKeys);
    removeOldLinks(curr.links, nxt.linkKeys); // si no deseas eliminar, comenta esta línea

    // JS externos en <head>: cargar los que faltan, secuencialmente
    return loadNewHeadScriptsSequential(nxt.scripts, curr.scriptKeys).then(() => {
      // 2) BODY principal: solo #appRoot, NO tocar #globalPlayer
      const swapped = swapAppRoot(fromDoc);
      if (!swapped) {
        // Si la página no trae #appRoot, fallback a navegación normal
        location.href = location.href;
        return;
      }
      // 3) Title
      updateTitle(fromDoc);
    });
  }

  function navigateTo(url, pushState = true) {
    const app = document.getElementById('appRoot');
    if (app) {
      app.style.transition = 'opacity .2s';
      app.style.opacity = '0.5';
      app.style.pointerEvents = 'none';
    }

    return fetch(url, { credentials: 'same-origin' })
      .then(r => {
        if (!r.ok) throw new Error('HTTP ' + r.status);
        return r.text();
      })
      .then(html => {
        const doc = new DOMParser().parseFromString(html, 'text/html');
        return fullyApplyDocument(doc).then(() => {
          if (app) {
            app.style.opacity = '1';
            app.style.pointerEvents = 'auto';
          }
          if (pushState) history.pushState({ path: url }, '', url);
          // Scroll al inicio tras carga
          window.scrollTo({ top: 0, behavior: 'smooth' });
        });
      })
      .catch(err => {
        console.error('Error en navegación SPA:', err);
        // Fallback duro
        window.location.href = url;
      });
  }

  // Interceptar enlaces internos
  document.addEventListener('click', function (e) {
    const a = e.target.closest('a');
    if (!a) return;

    const sameHost = a.hostname === location.hostname;
    const safe = !a.hasAttribute('download') &&
                 !a.hasAttribute('target') &&
                 a.getAttribute('href') &&
                 a.getAttribute('href') !== '#' &&
                 !a.getAttribute('href').startsWith('javascript:');

    if (sameHost && safe) {
      e.preventDefault();
      navigateTo(a.href, true);
    }
  });

  // Soporte para back/forward
  window.addEventListener('popstate', function (e) {
    const url = (e.state && e.state.path) ? e.state.path : location.href;
    navigateTo(url, false);
  });
})();


</script>


<?php endif; ?>