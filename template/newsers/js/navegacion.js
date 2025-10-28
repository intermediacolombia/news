// ====== REPRODUCTOR ======
const direccionURL1 = `
  <div style="bottom: 0;display: flex;height: 500px;left: 0;position: fixed;right: 0;width: 100%;z-index: 1500;overflow: hidden;"><iframe src="https://players.intermediahost.co/player-bottom/?station=guaca-stereo&v=1.1" frameborder="0" scrolling="no" style="width: 100%;"></iframe></div>
`;

document.addEventListener('DOMContentLoaded', () => {
  document.getElementById('link1').innerHTML = direccionURL1;
});

// ====== NAVEGACIÓN AJAX ======
document.addEventListener('click', function(e) {
  const link = e.target.closest('a');
  if (link && link.hostname === window.location.hostname && !link.target) {
    e.preventDefault();
    const url = link.href;
    cargarPagina(url);
    history.pushState({url}, '', url);
  }
});

async function cargarPagina(url) {
  try {
    const response = await fetch(url);
    const html = await response.text();

    const tempDiv = document.createElement('div');
    tempDiv.innerHTML = html;

    const nuevoContenido = tempDiv.querySelector('#pageContent');
    if (nuevoContenido) {
      document.querySelector('#pageContent').innerHTML = nuevoContenido.innerHTML;
    }

    window.scrollTo(0, 0);
  } catch (err) {
    console.error('Error cargando página:', err);
  }
}

window.addEventListener('popstate', e => {
  if (e.state && e.state.url) cargarPagina(e.state.url);
});
