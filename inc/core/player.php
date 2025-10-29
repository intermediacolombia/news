<?php if (!empty($sys['code_player'])): ?>
<style>
.container-fluid.copyright.bg-dark.py-4, p.m-0.text-center {
    padding-bottom: <?= $sys['player_height'] + 10 ?? 70 ?>px!important;
}
</style>


<!-- Player permanente -->
 <div id="globalPlayer" style="position:fixed;left:0;right:0;bottom:0;height:<?= $sys['player_height'] ?? 70 ?>px;z-index:1500;overflow:hidden;display:flex;">
    <iframe src="<?= $sys['code_player'] ?? '' ?>" frameborder="0" scrolling="no" style="width:100%;height:100%;"></iframe>
  </div>
<!--script>
(function () {
  // === 1) Utilidades para assets en <head> ===
  function assetKey(el) {
    if (el.tagName === 'LINK') return (el.getAttribute('rel') || '') + '|' + new URL(el.href, location.href).href;
    if (el.tagName === 'SCRIPT' && el.src) return new URL(el.src, location.href).href;
    return '';
  }

  function mapHeadAssets(doc) {
    const links = Array.from(doc.querySelectorAll('head link[rel="stylesheet"]'));
    const scripts = Array.from(doc.querySelectorAll('head script[src]'));
    return {
      links,
      scripts,
      linkKeys: new Set(links.map(assetKey)),
      scriptKeys: new Set(scripts.map(assetKey)),
    };
  }

  function currentHeadAssets() {
    const links = Array.from(document.head.querySelectorAll('link[rel="stylesheet"]'));
    const scripts = Array.from(document.head.querySelectorAll('head script[src]'));
    return {
      links,
      scripts,
      linkKeys: new Set(links.map(assetKey)),
      scriptKeys: new Set(scripts.map(assetKey)),
    };
  }

  // Agrega los <link rel="stylesheet"> que falten. No elimina los existentes.
  function addMissingLinks(newLinks, currKeys) {
    const toAppend = [];
    newLinks.forEach(link => {
      const key = assetKey(link);
      if (!currKeys.has(key)) {
        const n = document.createElement('link');
        Array.from(link.attributes).forEach(a => n.setAttribute(a.name, a.value));
        toAppend.push(n);
      }
    });
    toAppend.forEach(n => document.head.appendChild(n));
  }

  // Agrega scripts externos del head que falten, de forma secuencial (respeta dependencias).
  function addMissingHeadScriptsSequential(newScripts, currKeys) {
    const list = newScripts
      .filter(s => !currKeys.has(assetKey(s)))
      .map(s => {
        const n = document.createElement('script');
        Array.from(s.attributes).forEach(a => n.setAttribute(a.name, a.value));
        // Evitar async para preservar orden salvo que la página lo pida explícitamente
        if (!s.hasAttribute('async')) n.async = false;
        return n;
      });

    return list.reduce((p, s) => p.then(() => new Promise((res, rej) => {
      s.onload = () => res();
      s.onerror = () => rej(new Error('Fallo cargando script de <head>: ' + (s.src || 'inline')));
      document.head.appendChild(s);
      if (!s.src) res(); // inline en head
    })), Promise.resolve());
  }

  // === 2) Reemplazar SOLO el interior de #appRoot ===
  function swapAppRoot(fromDoc) {
    const newApp = fromDoc.querySelector('#appRoot');
    const app = document.getElementById('appRoot');
    if (!app || !newApp) return false;

    // Insertar el HTML primero
    app.innerHTML = newApp.innerHTML;

    // Ejecutar scripts en orden de aparición dentro del nuevo #appRoot
    return executeScriptsSequential(app);
  }

  // Ejecuta scripts (externos e inline) en el contenedor, en orden. Respeta dependencias.
  function executeScriptsSequential(container) {
    const scripts = Array.from(container.querySelectorAll('script'));
    // Quitarlos del DOM y volver a crearlos en orden, para garantizar ejecución
    const tasks = scripts.map(old => () => new Promise((resolve, reject) => {
      const s = document.createElement('script');

      // Copiar atributos salvo nonce integrados si no son necesarios
      Array.from(old.attributes).forEach(a => s.setAttribute(a.name, a.value));

      // Forzar orden: si no trae async explícito, ejecutar secuencialmente
      if (!old.hasAttribute('async')) s.async = false;

      // Reemplazar el nodo por el nuevo script
      if (old.parentNode) {
        // Para mantener la posición, insertar el nuevo y luego quitar el viejo
        old.parentNode.insertBefore(s, old);
        old.parentNode.removeChild(old);
      }

      if (s.src) {
        s.onload = () => resolve();
        s.onerror = () => reject(new Error('Fallo cargando script del body: ' + s.src));
      } else {
        s.textContent = old.textContent || '';
        // Los scripts inline ejecutan al insertarse
        resolve();
      }
    }));

    // Ejecutar tareas secuenciales
    return tasks.reduce((p, task) => p.then(task), Promise.resolve());
  }

  // === 3) Aplicar documento nuevo: <head> (add-only) + #appRoot ===
  function fullyApplyDocument(fromDoc) {
    const curr = currentHeadAssets();
    const nxt = mapHeadAssets(fromDoc);

    // CSS: agregar faltantes (no eliminar)
    addMissingLinks(nxt.links, curr.linkKeys);

    // JS del head: agregar faltantes (no eliminar)
    return addMissingHeadScriptsSequential(nxt.scripts, curr.scriptKeys)
      .then(() => {
        // Reemplazar SOLO #appRoot y ejecutar sus scripts
        const swappedOrPromise = swapAppRoot(fromDoc);
        if (swappedOrPromise === false) {
          // Si la página no trae #appRoot, fallback a navegación completa
          location.href = location.href;
          return;
        }
        return swappedOrPromise;
      })
      .then(() => {
        // Actualizar <title>
        const t = fromDoc.querySelector('title');
        if (t) document.title = t.textContent;
      });
  }

  // === 4) Navegación ===
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
          window.scrollTo({ top: 0, behavior: 'smooth' });
        });
      })
      .catch(err => {
        console.error('Error en navegación SPA:', err);
        window.location.href = url; // Fallback duro
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

  // Back/forward
  window.addEventListener('popstate', function (e) {
    const url = (e.state && e.state.path) ? e.state.path : location.href;
    navigateTo(url, false);
  });
})();
</script-->

<script>
(function () {

  // === Utilidades de comparación de assets ===
  function assetKey(el) {
    if (el.tagName === 'LINK') return (el.getAttribute('rel') || '') + '|' + new URL(el.href, location.href).href;
    if (el.tagName === 'SCRIPT' && el.src) return new URL(el.src, location.href).href;
    return '';
  }

  function mapHeadAssets(doc) {
    const links = Array.from(doc.querySelectorAll('head link[rel="stylesheet"]'));
    const scripts = Array.from(doc.querySelectorAll('head script[src]'));
    return {
      links,
      scripts,
      linkKeys: new Set(links.map(assetKey)),
      scriptKeys: new Set(scripts.map(assetKey)),
    };
  }

  function currentHeadAssets() {
    const links = Array.from(document.head.querySelectorAll('link[rel="stylesheet"]'));
    const scripts = Array.from(document.head.querySelectorAll('head script[src]'));
    return {
      links,
      scripts,
      linkKeys: new Set(links.map(assetKey)),
      scriptKeys: new Set(scripts.map(assetKey)),
    };
  }

  // === Cargar CSS nuevos ===
  function addMissingLinks(newLinks, currKeys) {
    const toAppend = [];
    newLinks.forEach(link => {
      const key = assetKey(link);
      if (!currKeys.has(key)) {
        const n = document.createElement('link');
        Array.from(link.attributes).forEach(a => n.setAttribute(a.name, a.value));
        toAppend.push(n);
      }
    });
    toAppend.forEach(n => document.head.appendChild(n));
    // Esperar a que los CSS estén aplicados antes de continuar
    return new Promise(resolve => setTimeout(resolve, 120));
  }

  // === Cargar scripts externos nuevos ===
  function addMissingHeadScriptsSequential(newScripts, currKeys) {
    const list = newScripts
      .filter(s => !currKeys.has(assetKey(s)))
      .map(s => {
        const n = document.createElement('script');
        Array.from(s.attributes).forEach(a => n.setAttribute(a.name, a.value));
        if (!s.hasAttribute('async')) n.async = false;
        return n;
      });

    return list.reduce((p, s) => p.then(() => new Promise((res, rej) => {
      s.onload = () => res();
      s.onerror = () => rej(new Error('Error al cargar script de head: ' + s.src));
      document.head.appendChild(s);
      if (!s.src) res();
    })), Promise.resolve());
  }

  // === Ejecutar scripts del nuevo contenido ===
  function executeScriptsSequential(container) {
    const scripts = Array.from(container.querySelectorAll('script'));
    const tasks = scripts.map(old => () => new Promise((resolve, reject) => {
      const s = document.createElement('script');
      Array.from(old.attributes).forEach(a => s.setAttribute(a.name, a.value));
      if (!old.hasAttribute('async')) s.async = false;
      old.parentNode.insertBefore(s, old);
      old.parentNode.removeChild(old);
      if (s.src) {
        s.onload = () => resolve();
        s.onerror = () => reject(new Error('Error al cargar script: ' + s.src));
      } else {
        s.textContent = old.textContent || '';
        resolve();
      }
    }));
    return tasks.reduce((p, task) => p.then(task), Promise.resolve());
  }

  // === Reinicializar Bootstrap ===
  function reinitBootstrapComponents() {
    if (typeof bootstrap === 'undefined') return;

    const tooltipList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltipList.map(el => new bootstrap.Tooltip(el));

    const popoverList = [].slice.call(document.querySelectorAll('[data-bs-toggle="popover"]'));
    popoverList.map(el => new bootstrap.Popover(el));

    const dropdownList = [].slice.call(document.querySelectorAll('.dropdown-toggle'));
    dropdownList.map(el => new bootstrap.Dropdown(el));

    const offcanvasList = [].slice.call(document.querySelectorAll('.offcanvas'));
    offcanvasList.map(el => new bootstrap.Offcanvas(el));

    const toastList = [].slice.call(document.querySelectorAll('.toast'));
    toastList.map(el => new bootstrap.Toast(el));

    const modalList = [].slice.call(document.querySelectorAll('.modal'));
    modalList.map(el => new bootstrap.Modal(el));

    const carouselList = [].slice.call(document.querySelectorAll('.carousel'));
    carouselList.map(el => {
      const existing = bootstrap.Carousel.getInstance(el);
      if (existing) existing.dispose();
      const c = new bootstrap.Carousel(el, {
        interval: el.getAttribute('data-bs-interval') || 5000,
        ride: el.getAttribute('data-bs-ride') || false,
        wrap: el.getAttribute('data-bs-wrap') !== 'false'
      });
      if (el.getAttribute('data-bs-ride') === 'carousel') c.cycle();
    });
  }

  
  // === Reinicializar Owl Carousel (versión mejorada) ===
function reinitOwlCarousels() {
  if (!window.jQuery || !jQuery.fn.owlCarousel) return;

  // Esperar a que todas las fuentes (Bootstrap Icons incluidas) estén cargadas
  if (document.fonts && document.fonts.ready) {
    document.fonts.ready.then(() => initOwl());
  } else {
    // Fallback si el navegador no soporta document.fonts
    setTimeout(initOwl, 250);
  }

  function initOwl() {
    jQuery('.owl-carousel').each(function () {
      const $c = jQuery(this);

      // Destruir instancia previa si existe
      if ($c.data('owl.carousel')) {
        $c.trigger('destroy.owl.carousel');
        $c.removeClass('owl-loaded owl-drag owl-grab');
        $c.find('.owl-stage-outer').children().unwrap();
      }

      // Configuración automática
      const options = {
        loop: $c.data('loop') ?? true,
        margin: $c.data('margin') || 10,
        nav: $c.data('nav') ?? true,
        dots: $c.data('dots') ?? true,
        autoplay: $c.data('autoplay') ?? false,
        autoplayTimeout: $c.data('autoplay-timeout') || 3000,
        autoplayHoverPause: $c.data('autoplay-hover-pause') ?? true,
        responsive: $c.data('responsive') || {
          0: { items: $c.data('items-mobile') || 1 },
          600: { items: $c.data('items-tablet') || 2 },
          1000: { items: $c.data('items') || 3 }
        },
        // Flechas con íconos Bootstrap
        navText: [
          '<i class="bi bi-chevron-left"></i>',
          '<i class="bi bi-chevron-right"></i>'
        ]
      };

      // Iniciar
      $c.owlCarousel(options);
    });
  }
}
	
	
	// === Reprocesar plugins de Facebook (comentarios, botones, etc.) ===
function reinitFacebookPlugins() {
  // Si el SDK ya está cargado
  if (typeof FB !== 'undefined' && FB.XFBML && typeof FB.XFBML.parse === 'function') {
    // Reprocesar todo el contenido dinámico
    FB.XFBML.parse(document.getElementById('appRoot'));
  } else {
    // Si aún no se ha cargado el SDK, intentar cargarlo de nuevo
    const existing = document.getElementById('facebook-jssdk');
    if (!existing) {
      const js = document.createElement('script');
      js.id = 'facebook-jssdk';
      js.src = 'https://connect.facebook.net/es_LA/sdk.js#xfbml=1&version=v18.0';
      document.body.appendChild(js);
    }
  }
}



  // === Reemplazar #appRoot y reinicializar ===
  function swapAppRoot(fromDoc) {
    const newApp = fromDoc.querySelector('#appRoot');
    const app = document.getElementById('appRoot');
    if (!app || !newApp) return false;
    app.innerHTML = newApp.innerHTML;
    return executeScriptsSequential(app).then(() => {
      reinitBootstrapComponents();
      reinitOwlCarousels();
    });
  }

  // === Aplicar nuevo documento ===
  function fullyApplyDocument(fromDoc) {
    const curr = currentHeadAssets();
    const nxt = mapHeadAssets(fromDoc);
    return addMissingLinks(nxt.links, curr.linkKeys)
      .then(() => addMissingHeadScriptsSequential(nxt.scripts, curr.scriptKeys))
      .then(() => {
        const result = swapAppRoot(fromDoc);
        if (result === false) location.href = location.href;
        return result;
      })
      .then(() => {
        const title = fromDoc.querySelector('title');
        if (title) document.title = title.textContent;
      });
  }

  // === Navegación ===
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
          window.scrollTo({ top: 0, behavior: 'smooth' });
        });
      })
      .catch(err => {
        console.error('Error SPA:', err);
        window.location.href = url;
      });
  }

  // === Interceptar enlaces internos ===
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

  // === Back/forward ===
  window.addEventListener('popstate', function (e) {
    const url = (e.state && e.state.path) ? e.state.path : location.href;
    navigateTo(url, false);
  });

})();

</script>


<?php endif; ?>