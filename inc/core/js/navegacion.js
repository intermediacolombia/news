// ====== REPRODUCTOR ======
document.addEventListener('DOMContentLoaded', () => {
  const playerContainer = document.getElementById('link1');
  if (typeof direccionURL1 !== 'undefined' && playerContainer) {
    playerContainer.innerHTML = direccionURL1;
  }
});

// ====== NAVEGACIÓN AJAX ======
document.addEventListener('click', function(e) {
  const link = e.target.closest('a');
  if (link && link.hostname === window.location.hostname && !link.target && !link.hasAttribute('data-noajax')) {
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

    const tempDiv = document.createElement('div');
    tempDiv.innerHTML = html;

    const nuevoContenido = tempDiv.querySelector('#pageContent');
    if (nuevoContenido) {
      document.querySelector('#pageContent').innerHTML = nuevoContenido.innerHTML;

      //Reiniciar scripts y componentes tras cargar
      reactivarScripts();
    }

    window.scrollTo({ top: 0, behavior: 'smooth' });
  } catch (err) {
    console.error('Error cargando página:', err);
  }
}

// ====== SOPORTE BOTÓN ATRÁS ======
window.addEventListener('popstate', e => {
  if (e.state && e.state.url) cargarPagina(e.state.url);
});

// ====== REACTIVAR SCRIPTS TRAS CARGAR ======
function reactivarScripts() {
  //  Re-ejecutar <script> embebidos dentro de #pageContent
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

  //  Detectar y reactivar librer�as autom�ticamente
  try {
    // Facebook SDK
    if (window.FB && FB.XFBML && typeof FB.XFBML.parse === 'function') {
      FB.XFBML.parse(document.getElementById('pageContent'));
    }

    // Twitter Widgets
    if (window.twttr && twttr.widgets && typeof twttr.widgets.load === 'function') {
      twttr.widgets.load(document.getElementById('pageContent'));
    }

    // Instagram Embeds
    if (window.instgrm && instgrm.Embeds && typeof instgrm.Embeds.process === 'function') {
      instgrm.Embeds.process();
    }

    // TikTok Embeds
    if (window.tiktokEmbed && typeof tiktokEmbed.init === 'function') {
      tiktokEmbed.init();
    }

    // YouTube iframes (revisa API)
    if (window.YT && typeof YT.ready === 'function') {
      YT.ready();
    }

    // AOS (Animate on Scroll)
    if (window.AOS && typeof AOS.refresh === 'function') {
      AOS.refresh();
    }

    // OwlCarousel
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

    // WOW.js
    if (window.WOW && typeof WOW === 'function') {
      new WOW().init();
    }

  } catch (err) {
    console.warn('Error al reactivar scripts:', err);
  }
}

