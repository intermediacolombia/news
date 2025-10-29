// ===============================
// REPRODUCTOR PERSISTENTE
// ===============================
document.addEventListener('DOMContentLoaded', () => {
  const playerContainer = document.getElementById('link1');
  if (typeof direccionURL1 !== 'undefined' && playerContainer) {
    playerContainer.innerHTML = direccionURL1;
  }
});

// ===============================
// NAVEGACIÓN AJAX
// ===============================
document.addEventListener('click', function (e) {
  const link = e.target.closest('a');
  if (
    link &&
    link.hostname === window.location.hostname &&
    !link.target &&
    !link.hasAttribute('data-noajax') &&
    !link.href.endsWith('.pdf') &&
    !link.href.includes('#')
  ) {
    e.preventDefault();
    const url = link.href;
    cargarPagina(url);
    history.pushState({ url }, '', url);
  }
});

async function cargarPagina(url) {
  try {
    const response = await fetch(url, { cache: 'no-cache' });
    const html = await response.text();
    const tempDiv = document.createElement('html');
    tempDiv.innerHTML = html;

    // Reemplaza solo el contenido principal
    const nuevoContenido = tempDiv.querySelector('#pageContent');
    if (nuevoContenido) {
      document.querySelector('#pageContent').innerHTML = nuevoContenido.innerHTML;
    }

    // Actualiza título y metadatos básicos
    const newTitle = tempDiv.querySelector('title');
    if (newTitle) document.title = newTitle.textContent;

    tempDiv.querySelectorAll('meta').forEach(meta => {
      const name = meta.getAttribute('name') || meta.getAttribute('property');
      if (name) {
        let existing = document.head.querySelector(
          `meta[name="${name}"], meta[property="${name}"]`
        );
        if (existing) {
          existing.setAttribute('content', meta.getAttribute('content'));
        } else {
          document.head.appendChild(meta.cloneNode(true));
        }
      }
    });

    // Cargar scripts y estilos adicionales si no existen
    tempDiv.querySelectorAll('link[rel="stylesheet"]').forEach(link => {
      if (!document.querySelector(`link[href="${link.href}"]`)) {
        document.head.appendChild(link.cloneNode(true));
      }
    });
    tempDiv.querySelectorAll('script[src]').forEach(script => {
      if (!document.querySelector(`script[src="${script.src}"]`)) {
        const s = document.createElement('script');
        s.src = script.src;
        s.async = false;
        document.head.appendChild(s);
      }
    });

    // Reactiva todos los scripts dinámicos y SDKs
    reactivarScripts();

    window.scrollTo({ top: 0, behavior: 'smooth' });
  } catch (err) {
    console.error('Error cargando página:', err);
  }
}

// ===============================
// BOTÓN ATRÁS / ADELANTE
// ===============================
window.addEventListener('popstate', e => {
  if (e.state && e.state.url) cargarPagina(e.state.url);
});

// ===============================
// REACTIVAR TODO AUTOMÁTICAMENTE
// ===============================
function reactivarScripts() {
  // Reejecutar scripts embebidos del contenido cargado
  document.querySelectorAll('#pageContent script').forEach(oldScript => {
    const newScript = document.createElement('script');
    if (oldScript.src) newScript.src = oldScript.src;
    else newScript.textContent = oldScript.textContent;
    document.body.appendChild(newScript);
    oldScript.remove();
  });

  // Reactivar SDKs y librerías conocidas
  try {
    if (window.FB?.XFBML?.parse) FB.XFBML.parse(document.getElementById('pageContent'));
    if (window.twttr?.widgets?.load) twttr.widgets.load(document.getElementById('pageContent'));
    if (window.instgrm?.Embeds?.process) instgrm.Embeds.process();
    if (window.tiktokEmbed?.init) tiktokEmbed.init();
    if (window.AOS?.refresh) AOS.refresh();
    if (typeof WOW === 'function') new WOW().init();
  } catch (err) {
    console.warn('Error al reactivar librerías:', err);
  }

  // Reiniciar el ticker de noticias si existe
  if (window.latestPostsData && document.getElementById('newsTicker')) {
    const ticker = document.getElementById('newsTicker');
    let i = 0;

    function showPost() {
      const post = window.latestPostsData[i];
      if (!post) return;
      const img = post.image ? `${window.URLBASE}/${post.image}` : `${window.URLBASE}/public/images/no-image.jpg`;
      const link = `${window.URLBASE}/${post.category_slug}/${post.post_slug}/`;
      ticker.innerHTML = `
        <div class="d-flex align-items-center fadein">
          <img src="${img}" class="img-fluid rounded-circle border border-3 border-primary me-2"
               style="width:30px; height:30px; object-fit:cover;" alt="">
          <a href="${link}" class="text-white mb-0 link-hover text-nowrap">${post.title}</a>
        </div>`;
      i = (i + 1) % window.latestPostsData.length;
    }

    if (window.newsTickerInterval) clearInterval(window.newsTickerInterval);
    showPost();
    window.newsTickerInterval = setInterval(showPost, 4500);
  }

  // ===============================
  // Reejecutar main.js si existe
  // ===============================
  try {
    const mainPath = `${window.URLBASE}/template/news/js/main.js`;
    // Borra versiones anteriores que se hayan inyectado dinámicamente
    document.querySelectorAll(`script[src^="${mainPath}"]`).forEach(s => s.remove());

    const s = document.createElement('script');
    s.src = mainPath + '?v=' + Date.now(); // Cache-buster para asegurar recarga
    s.async = false;
    document.head.appendChild(s);
  } catch (err) {
    console.warn('No se pudo recargar main.js:', err);
  }
}










