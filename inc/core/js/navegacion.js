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
// NAVEGACI�N AJAX
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

    // Actualiza t�tulo y metadatos b�sicos
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

    // Reactiva todos los scripts din�micos y SDKs
    reactivarScripts();

    window.scrollTo({ top: 0, behavior: 'smooth' });
  } catch (err) {
    console.error('Error cargando p�gina:', err);
  }
}

// ===============================
// BOT�N ATR�S / ADELANTE
// ===============================
window.addEventListener('popstate', e => {
  if (e.state && e.state.url) cargarPagina(e.state.url);
});

// ===============================
// REACTIVAR TODO AUTOM�TICAMENTE
// ===============================
function reactivarScripts() {
  // Reejecutar scripts embebidos del contenido cargado
  document.querySelectorAll('#pageContent script').forEach(oldScript => {
    const newScript = document.createElement('script');
    if (oldScript.src) {
      newScript.src = oldScript.src;
    } else {
      newScript.textContent = oldScript.textContent;
    }
    document.body.appendChild(newScript);
    oldScript.remove();
  });

  // Reactivar SDKs y librer�as conocidas
  try {
    if (window.FB && FB.XFBML && typeof FB.XFBML.parse === 'function') {
      FB.XFBML.parse(document.getElementById('pageContent'));
    }
    if (window.twttr && twttr.widgets && typeof twttr.widgets.load === 'function') {
      twttr.widgets.load(document.getElementById('pageContent'));
    }
    if (window.instgrm && instgrm.Embeds && typeof instgrm.Embeds.process === 'function') {
      instgrm.Embeds.process();
    }
    if (window.tiktokEmbed && typeof tiktokEmbed.init === 'function') {
      tiktokEmbed.init();
    }
    if (window.AOS && typeof AOS.refresh === 'function') {
      AOS.refresh();
    }
    if (window.WOW && typeof WOW === 'function') {
      new WOW().init();
    }
    if (typeof $ !== 'undefined' && $('.owl-carousel').length && typeof $('.owl-carousel').owlCarousel === 'function') {
      $('.owl-carousel').owlCarousel({
        autoplay: true,
        smartSpeed: 1000,
        margin: 25,
        loop: true,
        center: true,
        dots: false,
        nav: true,
        navText: [
          '<i class="bi bi-chevron-left"></i>',
          '<i class="bi bi-chevron-right"></i>'
        ],
        responsive: { 0: { items: 1 }, 768: { items: 2 }, 992: { items: 3 } }
      });
    }
  } catch (err) {
    console.warn('Error al reactivar librer�as:', err);
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

    // Evita m�ltiples intervalos duplicados
    if (window.newsTickerInterval) clearInterval(window.newsTickerInterval);
    showPost();
    window.newsTickerInterval = setInterval(showPost, 4500);
  }
	
	// ===============================
// Reejecutar main.js si existe
// ===============================
try {
  const mainPath = `${window.URLBASE}/template/news/js/main.js`;
  const existing = document.querySelector(`script[src="${mainPath}"]`);

  // Si el archivo ya estaba cargado, recargarlo para reactivar sliders
  if (existing) {
    const clone = document.createElement('script');
    clone.src = mainPath + '?v=' + Date.now(); // cache-buster
    clone.async = false;
    document.head.appendChild(clone);
  } else {
    // Si no estaba en el DOM, insertarlo
    const s = document.createElement('script');
    s.src = mainPath;
    s.async = false;
    document.head.appendChild(s);
  }
} catch (err) {
  console.warn('No se pudo recargar main.js:', err);
}

}









