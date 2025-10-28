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

      // 🔁 Reiniciar scripts y componentes tras cargar
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
  // 1️⃣ Re-ejecutar <script> embebidos del contenido cargado
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

  // 2️⃣ Reiniciar OwlCarousel si existe
  if (typeof $ !== 'undefined' && $('.owl-carousel').length) {
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

  // 3️⃣ Volver a ejecutar animaciones o librerías si las usas (ej. AOS)
  if (typeof AOS !== 'undefined') {
    AOS.refresh();
  }
}
