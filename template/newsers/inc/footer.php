</main>
<!-- Footer Start -->
<div class="container-fluid bg-dark footer py-5 mt-5">
  <div class="container py-5">

    <!-- Logo + Suscripción -->
    <div class="pb-4 mb-5 border-bottom border-secondary">
      <div class="row g-4 align-items-center">
        <div class="col-lg-3 text-center text-lg-start">
          <a href="<?= URLBASE ?>" class="text-decoration-none">
            <img src="<?= URLBASE . SITE_LOGO ?>?<?= time() ?>" alt="<?= htmlspecialchars($sys['site_name']) ?>" width="170" class="mb-2">
            <div class="text-uppercase text-white-50 small fw-light" style="letter-spacing: 6px;">
              <?= htmlspecialchars($sys['site_name']) ?>
            </div>
          </a>
        </div>

        <div class="col-lg-9">
          <form class="d-flex position-relative rounded-pill overflow-hidden shadow-sm">
            <input class="form-control border-0 w-100 py-3 ps-4 rounded-pill" type="email" placeholder="Tu correo electrónico...">
            <button type="submit" class="btn btn-primary border-0 py-3 px-5 rounded-pill position-absolute end-0 top-0 text-white">
              Suscribirse
            </button>
          </form>
        </div>
      </div>
    </div>

    <!-- Contenido principal del footer -->
    <div class="row g-5">

      <!-- Contacto -->
      <div class="col-lg-4">
        <div class="footer-item">
          <h4 class="text-white mb-4 fw-semibold">Contáctanos</h4>
          <p><?= htmlspecialchars($sys['info_footer']) ?></p>
          <p class="text-secondary mb-1"><i class="fa fa-map-marker-alt text-primary me-2"></i><?= htmlspecialchars($sys['business_address'] ?? '') ?></p>
          <p class="text-secondary mb-1"><i class="fa fa-envelope text-primary me-2"></i><?= htmlspecialchars($sys['site_email'] ?? '') ?></p>
          <p class="text-secondary mb-3"><i class="fa fa-phone-alt text-primary me-2"></i><?= htmlspecialchars($sys['business_phone'] ?? '') ?></p>

          <div class="d-flex flex-wrap gap-2 mt-3">
            <?php
            $redes = [
              'facebook' => 'fab fa-facebook-f',
              'instagram' => 'fab fa-instagram',
              'twitter' => 'fab fa-x-twitter',
              'youtube' => 'fab fa-youtube',
              'tiktok' => 'fab fa-tiktok',
              'whatsapp' => 'fab fa-whatsapp'
            ];
            foreach ($redes as $nombre => $icono):
              if (!empty($sys[$nombre])): ?>
                <a href="<?= htmlspecialchars($sys[$nombre]) ?>" target="_blank"
                   class="btn btn-outline-light btn-sm rounded-circle d-flex align-items-center justify-content-center"
                   style="width: 38px; height: 38px;">
                  <i class="<?= $icono ?>"></i>
                </a>
            <?php endif; endforeach; ?>
          </div>
        </div>
      </div>

      <!-- Últimas noticias -->
      <div class="col-lg-4">
        <div class="footer-item">
          <h4 class="text-white mb-4 fw-semibold">Últimas Noticias</h4>
          <?php
          $stmt = $pdo->query("
            SELECT p.title, p.slug AS post_slug, p.image, p.created_at,
                   c.slug AS category_slug, c.name AS category_name
            FROM blog_posts p
            INNER JOIN blog_post_category pc ON pc.post_id = p.id
            INNER JOIN blog_categories c ON c.id = pc.category_id
            WHERE p.status='published' AND p.deleted=0
            ORDER BY p.created_at DESC
            LIMIT 2
          ");
          $recentPosts = $stmt->fetchAll(PDO::FETCH_ASSOC);
          foreach ($recentPosts as $post):
              $img = !empty($post['image'])
                  ? URLBASE . '/' . htmlspecialchars($post['image'])
                  : URLBASE . '/public/images/no-image.jpg';
          ?>
          <a href="<?= URLBASE ?>/<?= htmlspecialchars($post['category_slug']) ?>/<?= htmlspecialchars($post['post_slug']) ?>/"
             class="text-decoration-none d-block mb-3">
            <div class="d-flex align-items-center">
              <div class="rounded-circle overflow-hidden border border-2 border-primary flex-shrink-0"
                   style="width: 65px; height: 65px;">
                <img src="<?= $img ?>" class="img-fluid w-100 h-100" alt="<?= htmlspecialchars($post['title']) ?>" style="object-fit: cover;">
              </div>
              <div class="ps-3">
                <p class="text-uppercase small mb-1 text-primary"><?= htmlspecialchars($post['category_name']) ?></p>
                <span class="text-white fw-semibold d-block"><?= htmlspecialchars($post['title']) ?></span>
                <small class="text-white-50"><i class="fa fa-calendar-alt me-1"></i><?= strftime('%d %b %Y', strtotime($post['created_at'])) ?></small>
              </div>
            </div>
          </a>
          <?php endforeach; ?>
        </div>
      </div>

      <!-- Categorías -->
      <div class="col-lg-4">
        <div class="footer-item">
          <h4 class="text-white mb-4 fw-semibold">Categorías</h4>
          <?php
          $cats = $pdo->query("
              SELECT c.name, c.slug, COUNT(p.id) AS total
              FROM blog_categories c
              INNER JOIN blog_post_category pc ON pc.category_id = c.id
              INNER JOIN blog_posts p ON p.id = pc.post_id
              WHERE c.status='active' AND c.deleted=0
                AND p.status='published' AND p.deleted=0
              GROUP BY c.id, c.name, c.slug
              HAVING total > 0
              ORDER BY c.name ASC
              LIMIT 6
          ")->fetchAll(PDO::FETCH_ASSOC);
          foreach ($cats as $cat): ?>
            <a href="<?= URLBASE ?>/noticias/<?= htmlspecialchars($cat['slug']) ?>/"
               class="text-white-50 d-block mb-2 text-decoration-none link-light-hover">
              <i class="fas fa-angle-right text-primary me-2"></i><?= htmlspecialchars(ucwords($cat['name'])) ?>
            </a>
          <?php endforeach; ?>
        </div>
      </div>
    </div>

    <!-- Línea inferior -->
    <div class="border-top border-secondary mt-5 pt-4 text-center">
      <small class="text-white-50">
        © <?= date('Y') ?> <?= htmlspecialchars($sys['site_name']) ?>. Todos los derechos reservados. |
        <a href="<?= URLBASE ?>/privacy-policy" class="text-white-50 text-decoration-none hover-link">Política de Privacidad</a> |
        <a href="<?= URLBASE ?>/terms-and-conditions" class="text-white-50 text-decoration-none hover-link">Términos y Condiciones</a>
      </small>
    </div>

  </div>
</div>
<!-- Footer End -->

<style>
.footer {
  background: #0d0d0d;
  color: #bbb;
  font-family: "Roboto", sans-serif;
  letter-spacing: 0.2px;
}

.footer-item h4 {
  position: relative;
}

.footer-item h4::after {
  content: "";
  display: block;
  width: 40px;
  height: 2px;
  background: var(--primary);
  margin-top: 6px;
}

.footer-item a {
  transition: all 0.3s ease;
  color: #fff !important;
}

.footer-item a:hover {
  color: #fff !important;
  text-decoration: underline;
}

.footer-item .btn-outline-light:hover {
  background-color: var(--primary);
  border-color: var(--primary);
  color: #fff;
}

.footer input::placeholder {
  color: #999;
}

.footer small,
.footer p,
.footer span {
  line-height: 1.6;
}

.hover-link:hover {
  color: var(--primary) !important;
}

/* Responsivo */
@media (max-width: 768px) {
  .footer-item {
    text-align: center;
  }
  .footer .btn-outline-light {
    margin: 0 auto;
  }
}
</style>


<!-- Copyright Start -->
<div class="container-fluid copyright bg-dark py-4">

    <div class="container text-center text-white small">
        <p class="mb-1">
            &copy; <?= date('Y') ?> <strong><?= NOMBRE_SITIO ?></strong>. Todos los derechos reservados.
        </p>
        <p class="mb-0">
            Hosting & Diseño por <a class="text-white border-bottom" href="https://www.intermediahost.co" target="_blank">Intermedia Host</a>
        </p>
    </div>
</div>
<!-- Copyright End -->


<!-- Back to Top -->
<a href="#" class="btn btn-primary border-2 border-white rounded-circle back-to-top">
    <i class="fa fa-arrow-up"></i>
</a>


<!-- JavaScript Libraries -->
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.4/jquery.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="<?= URLBASE ?>/template/newsers/lib/easing/easing.min.js"></script>
<script src="<?= URLBASE ?>/template/newsers/lib/waypoints/waypoints.min.js"></script>
<script src="<?= URLBASE ?>/template/newsers/lib/owlcarousel/owl.carousel.min.js"></script>

<!-- Template Javascript -->
<script src="<?= URLBASE ?>/template/newsers/js/main.js?<?= time(); ?>"></script>


<?php if (!empty($sys['code_player'])): ?>
<style>
.container-fluid.copyright.bg-dark.py-4 {
    padding-bottom: <?= $sys['player_height'] + 10 ?? 70 ?>px!important;
}
</style>

<!-- PLAYER SIN IFRAME -->
<div style="bottom: 0;display: flex;height: <?= $sys['player_height'] ?? 70 ?>px;left: 0;position: fixed;right: 0;width: 100%;z-index: 1500;overflow: hidden;"><iframe src="<?= $sys['code_player'] ?? '' ?>" frameborder="0" scrolling="no" style="width: 100%;"></iframe></div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const contentWrapper = document.getElementById('pageContent');
    const player = document.getElementById('radio-player');
    
    if (!contentWrapper || !player) return;
    
    // Función para reinicializar TODO (Bootstrap, jQuery, etc)
    function reinitAllComponents() {
        // 1. Reinicializar componentes de Bootstrap
        if (typeof bootstrap !== 'undefined') {
            // Tooltips
            const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
            tooltipTriggerList.map(function (tooltipTriggerEl) {
                return new bootstrap.Tooltip(tooltipTriggerEl);
            });
            
            // Popovers
            const popoverTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="popover"]'));
            popoverTriggerList.map(function (popoverTriggerEl) {
                return new bootstrap.Popover(popoverTriggerEl);
            });
            
            // Dropdowns (se inicializan automáticamente pero por si acaso)
            const dropdownElementList = [].slice.call(document.querySelectorAll('.dropdown-toggle'));
            dropdownElementList.map(function (dropdownToggleEl) {
                return new bootstrap.Dropdown(dropdownToggleEl);
            });
            
            // Modales
            const modalElementList = [].slice.call(document.querySelectorAll('.modal'));
            modalElementList.map(function (modalEl) {
                return new bootstrap.Modal(modalEl);
            });
            
            // Carousels
            const carouselElementList = [].slice.call(document.querySelectorAll('.carousel'));
            carouselElementList.map(function (carouselEl) {
                return new bootstrap.Carousel(carouselEl);
            });
            
            // Toasts
            const toastElList = [].slice.call(document.querySelectorAll('.toast'));
            toastElList.map(function (toastEl) {
                return new bootstrap.Toast(toastEl);
            });
        }
        
        // 2. Ejecutar scripts inline del nuevo contenido
        const scripts = contentWrapper.querySelectorAll('script');
        scripts.forEach(oldScript => {
            const newScript = document.createElement('script');
            
            if (oldScript.src) {
                // Script externo
                newScript.src = oldScript.src;
                newScript.async = false; // Mantener orden de ejecución
            } else {
                // Script inline
                newScript.textContent = oldScript.textContent;
            }
            
            // Copiar todos los atributos
            Array.from(oldScript.attributes).forEach(attr => {
                newScript.setAttribute(attr.name, attr.value);
            });
            
            // Reemplazar el script
            oldScript.parentNode.replaceChild(newScript, oldScript);
        });
        
        // 3. Reinicializar jQuery si existe
        if (window.jQuery) {
            // Trigger para tus scripts personalizados
            jQuery(document).trigger('contentLoaded');
            
            // Re-bindear eventos de jQuery que usen delegación
            jQuery(contentWrapper).find('a, button, input, select, textarea').off();
        }
        
        // 4. Evento personalizado global
        window.dispatchEvent(new CustomEvent('pageContentLoaded', {
            detail: { container: contentWrapper }
        }));
        
        // 5. Reinicializar validaciones de formularios HTML5
        const forms = contentWrapper.querySelectorAll('form');
        forms.forEach(form => {
            if (form.hasAttribute('novalidate')) {
                form.setAttribute('novalidate', '');
            }
        });
        
        // 6. Lazy loading de imágenes si lo usas
        if ('loading' in HTMLImageElement.prototype) {
            const images = contentWrapper.querySelectorAll('img[loading="lazy"]');
            images.forEach(img => {
                if (img.loading === 'lazy') {
                    img.loading = 'lazy';
                }
            });
        }
    }
    
    // Interceptar clicks en enlaces internos
    document.addEventListener('click', function(e) {
        const link = e.target.closest('a');
        
        // Validaciones para usar AJAX
        if (link && 
            link.href && 
            link.hostname === window.location.hostname &&
            !link.hasAttribute('download') &&
            !link.hasAttribute('target') &&
            !link.classList.contains('no-ajax') &&
            !link.hasAttribute('data-bs-toggle') && // Excluir modales/dropdowns de Bootstrap
            !link.hasAttribute('data-toggle') && // Bootstrap 4
            link.getAttribute('href') !== '#' &&
            !link.getAttribute('href')?.startsWith('#') &&
            !link.getAttribute('href')?.includes('javascript:')) {
            
            e.preventDefault();
            e.stopPropagation();
            
            const url = link.href;
            
            // Indicador de carga
            contentWrapper.style.transition = 'opacity 0.3s';
            contentWrapper.style.opacity = '0.5';
            contentWrapper.style.pointerEvents = 'none';
            
            // Mostrar loader (opcional)
            const loader = document.createElement('div');
            loader.id = 'ajax-loader';
            loader.style.cssText = 'position:fixed;top:50%;left:50%;transform:translate(-50%,-50%);z-index:9999;';
            loader.innerHTML = '<div class="spinner-border text-primary" role="status"><span class="visually-hidden">Cargando...</span></div>';
            document.body.appendChild(loader);
            
            // Cargar contenido vía AJAX
            fetch(url, {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-Ajax-Navigation': 'true'
                },
                credentials: 'same-origin'
            })
            .then(response => {
                if (!response.ok) throw new Error('Error HTTP: ' + response.status);
                return response.text();
            })
            .then(html => {
                const parser = new DOMParser();
                const doc = parser.parseFromString(html, 'text/html');
                const newContent = doc.querySelector('#pageContent');
                
                if (newContent) {
                    // Limpiar event listeners anteriores de Bootstrap
                    if (typeof bootstrap !== 'undefined') {
                        // Destruir tooltips activos
                        const tooltips = contentWrapper.querySelectorAll('[data-bs-toggle="tooltip"]');
                        tooltips.forEach(el => {
                            const instance = bootstrap.Tooltip.getInstance(el);
                            if (instance) instance.dispose();
                        });
                        
                        // Destruir popovers activos
                        const popovers = contentWrapper.querySelectorAll('[data-bs-toggle="popover"]');
                        popovers.forEach(el => {
                            const instance = bootstrap.Popover.getInstance(el);
                            if (instance) instance.dispose();
                        });
                        
                        // Cerrar modales abiertos
                        const modals = document.querySelectorAll('.modal.show');
                        modals.forEach(modalEl => {
                            const instance = bootstrap.Modal.getInstance(modalEl);
                            if (instance) instance.hide();
                        });
                    }
                    
                    // Actualizar contenido
                    contentWrapper.innerHTML = newContent.innerHTML;
                    
                    // Actualizar título
                    const newTitle = doc.querySelector('title');
                    if (newTitle) {
                        document.title = newTitle.textContent;
                    }
                    
                    // Actualizar meta tags
                    const newDesc = doc.querySelector('meta[name="description"]');
                    const currentDesc = document.querySelector('meta[name="description"]');
                    if (newDesc && currentDesc) {
                        currentDesc.setAttribute('content', newDesc.getAttribute('content'));
                    }
                    
                    // Actualizar canonical (SEO)
                    const newCanonical = doc.querySelector('link[rel="canonical"]');
                    const currentCanonical = document.querySelector('link[rel="canonical"]');
                    if (newCanonical && currentCanonical) {
                        currentCanonical.setAttribute('href', newCanonical.getAttribute('href'));
                    }
                    
                    // Actualizar URL
                    window.history.pushState({path: url}, '', url);
                    
                    // Scroll arriba
                    window.scrollTo({top: 0, behavior: 'smooth'});
                    
                    // Pequeño delay para que el DOM se estabilice
                    setTimeout(() => {
                        // Reinicializar TODO
                        reinitAllComponents();
                        
                        // Restaurar interacción
                        contentWrapper.style.opacity = '1';
                        contentWrapper.style.pointerEvents = 'auto';
                        
                        // Remover loader
                        const loaderEl = document.getElementById('ajax-loader');
                        if (loaderEl) loaderEl.remove();
                    }, 50);
                    
                    // Google Analytics
                    if (typeof gtag !== 'undefined') {
                        gtag('config', 'GA_MEASUREMENT_ID', {
                            page_path: new URL(url).pathname
                        });
                    }
                    
                    // Facebook Pixel
                    if (typeof fbq !== 'undefined') {
                        fbq('track', 'PageView');
                    }
                    
                } else {
                    console.warn('No se encontró #pageContent, navegando normalmente');
                    window.location.href = url;
                }
            })
            .catch(error => {
                console.error('Error en navegación AJAX:', error);
                // En caso de error, navegar normalmente
                window.location.href = url;
            });
        }
    });
    
    // Manejar botón atrás/adelante
    window.addEventListener('popstate', function(e) {
        const targetPath = e.state?.path || window.location.href;
        
        fetch(targetPath, {
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'X-Ajax-Navigation': 'true'
            },
            credentials: 'same-origin'
        })
        .then(response => response.text())
        .then(html => {
            const parser = new DOMParser();
            const doc = parser.parseFromString(html, 'text/html');
            const newContent = doc.querySelector('#pageContent');
            
            if (newContent) {
                // Limpiar Bootstrap components
                if (typeof bootstrap !== 'undefined') {
                    const tooltips = contentWrapper.querySelectorAll('[data-bs-toggle="tooltip"]');
                    tooltips.forEach(el => {
                        const instance = bootstrap.Tooltip.getInstance(el);
                        if (instance) instance.dispose();
                    });
                }
                
                contentWrapper.innerHTML = newContent.innerHTML;
                
                const newTitle = doc.querySelector('title');
                if (newTitle) {
                    document.title = newTitle.textContent;
                }
                
                window.scrollTo({top: 0, behavior: 'smooth'});
                
                setTimeout(() => {
                    reinitAllComponents();
                }, 50);
            }
        })
        .catch(error => {
            console.error('Error en popstate:', error);
            location.reload();
        });
    });
    
    // Guardar estado inicial
    window.history.replaceState({path: window.location.href}, '', window.location.href);
    
    // Inicializar componentes en la carga inicial
    reinitAllComponents();
});
</script>
<?php endif; ?>



<?= $sys['code_footer'] ?? '' ?>

</body>
</html>
