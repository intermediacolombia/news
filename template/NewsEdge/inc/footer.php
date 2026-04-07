<?php
/**
 * FOOTER DINÁMICO - NEWSEDGE
 * Helpers necesarios para evitar errores
 */

// Helper para truncar texto
if (!function_exists('truncate_text')) {
    function truncate_text(string $text, int $limit = 100): string {
        $text = strip_tags($text);
        return (mb_strlen($text) > $limit) ? mb_substr($text, 0, $limit) . '...' : $text;
    }
}

// Variables globales
global $sys;
?>

<!-- Search Modal -->
<div class="modal fade ne-search-modal" id="searchModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered ne-search-modal-dialog" role="document">
        <div class="modal-content ne-search-modal-content">
            <div class="modal-body ne-search-modal-body">
                <button type="button" class="close ne-search-close" data-dismiss="modal" aria-label="Cerrar">
                    <span aria-hidden="true">&times;</span>
                </button>

                <h3 class=”ne-search-title”><?= t_theme('theme_buscar') ?></h3>
                <p class=”ne-search-subtitle”><?= t_theme('theme_buscar_descripcion') ?></p>

                <form action="<?= URLBASE ?>/buscar/" method="get" class="ne-search-form" id="searchModalForm">
                    <input type="text"
                           name="q"
                           id="searchModalInput"
                           class="ne-search-input"
                           placeholder="<?= t_theme('theme_buscar_placeholder') ?>"
                           required>

                    <!-- Campos extra (opcional) para compatibilidad, mismos valores -->
                    <input type="hidden" name="s" id="searchHiddenS" value="">
                    <input type="hidden" name="search" id="searchHiddenSearch" value="">

                    <button type="submit" class="ne-search-btn">
                        <?= t_theme('theme_buscar') ?>
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Footer Area Start Here -->
<footer>
    <div class="footer-area-top">
        <div class="container">
            <div class="row">
                <!-- Columna 1: Contáctanos -->
                <div class="col-lg-4 col-md-6 col-sm-12">
                    <div class="footer-box">
                        <h2 class="title-bold-light title-bar-left text-uppercase"><?= t_theme('theme_contactanos') ?></h2>
                        <div class="footer-contact">
                            <?php if (!empty($sys['info_footer'])): ?>
                                <p class="footer-text mb-3"><?= htmlspecialchars($sys['info_footer']) ?></p>
                            <?php endif; ?>
                            
                            <?php if (!empty($sys['business_address'])): ?>
                                <p class="footer-contact-item mb-2">
                                    <i class="fa fa-map-marker" aria-hidden="true"></i>
                                    <span><?= htmlspecialchars($sys['business_address']) ?></span>
                                </p>
                            <?php endif; ?>
                            
                            <?php if (!empty($sys['site_email'])): ?>
                                <p class="footer-contact-item mb-2">
                                    <i class="fa fa-envelope" aria-hidden="true"></i>
                                    <span>
                                        <a href="mailto:<?= htmlspecialchars($sys['site_email']) ?>" class="footer-link">
                                            <?= htmlspecialchars($sys['site_email']) ?>
                                        </a>
                                    </span>
                                </p>
                            <?php endif; ?>
                            
                            <?php if (!empty($sys['business_phone'])): ?>
                                <p class="footer-contact-item mb-2">
                                    <i class="fa fa-phone" aria-hidden="true"></i>
                                    <span>
                                        <a href="tel:<?= htmlspecialchars($sys['business_phone']) ?>" class="footer-link">
                                            <?= htmlspecialchars($sys['business_phone']) ?>
                                        </a>
                                    </span>
                                </p>
                            <?php endif; ?>
                            
                            <?php if (!empty($sys['whatsapp'])): ?>
                                <p class="footer-contact-item mb-2">
                                    <i class="fa fa-whatsapp" aria-hidden="true"></i>
                                    <span>
                                        <a href="https://wa.me/<?= preg_replace('/[^0-9]/', '', $sys['whatsapp']) ?>" 
                                           target="_blank" 
                                           class="footer-link">
                                            WhatsApp
                                        </a>
                                    </span>
                                </p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- Columna 2: Últimas Noticias -->
                <div class="col-xl-4 col-lg-3 col-md-6 col-sm-12">
                    <div class="footer-box">
                        <h2 class="title-bold-light title-bar-left text-uppercase"><?= t_theme('theme_ultimas_noticias') ?></h2>
                        <ul class="popular-categories">
                            <?php
                            $latestNews = db()->query("
                                SELECT p.title, p.slug, c.slug as category_slug
                                FROM blog_posts p
                                LEFT JOIN blog_post_category pc ON pc.post_id = p.id
                                LEFT JOIN blog_categories c ON c.id = pc.category_id
                                WHERE p.status='published' AND p.deleted=0
                                ORDER BY p.created_at DESC
                                LIMIT 5
                            ")->fetchAll(PDO::FETCH_ASSOC);
                            
                            foreach ($latestNews as $news):
                                $postUrl = URLBASE . "/" . htmlspecialchars($news['category_slug']) . "/" . htmlspecialchars($news['slug']) . "/";
                            ?>
                                <li>
                                    <a href="<?= $postUrl ?>" class="footer-news-link">
                                        <i class="fa fa-angle-right footer-icon"></i>
                                        <?= htmlspecialchars(truncate_text($news['title'], 50)) ?>
                                    </a>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                </div>

                <!-- Columna 3: Categorías -->
                <div class="col-xl-4 col-lg-5 col-md-12 col-sm-12">
                    <div class="footer-box">
                        <h2 class="title-bold-light title-bar-left text-uppercase"><?= t_theme('theme_categorias') ?></h2>
                        <ul class="popular-categories">
                            <?php
                            $cats = db()->query("
                                SELECT c.name, c.slug, COUNT(p.id) AS total
                                FROM blog_categories c
                                INNER JOIN blog_post_category pc ON pc.category_id = c.id
                                INNER JOIN blog_posts p ON p.id = pc.post_id
                                WHERE c.status='active' AND c.deleted=0
                                  AND p.status='published' AND p.deleted=0
                                GROUP BY c.id, c.name, c.slug
                                HAVING total > 0
                                ORDER BY total DESC
                                LIMIT 10
                            ")->fetchAll(PDO::FETCH_ASSOC);
                            
                            foreach ($cats as $cat): ?>
                                <li>
                                    <a href="<?= URLBASE ?>/<?= htmlspecialchars($cat['slug']) ?>/">
                                        <?= htmlspecialchars(ucwords($cat['name'])) ?>
                                        <span><?= $cat['total'] ?></span>
                                    </a>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="footer-area-bottom">
        <div class="container">
            <div class="row">
                <div class="col-12 text-center">
                    <!-- Logo -->
                    <a href="<?= URLBASE ?>" class="footer-logo">
                        <img src="<?= URLBASE . SITE_LOGO ?>?v=<?= time() ?>" 
                             alt="<?= htmlspecialchars($sys['site_name']) ?>" 
                             class="img-fluid footer-logo-img">
                    </a>

                    <!-- Redes Sociales -->
                    <ul class="footer-social">
                        <?php
                        $redes = [
                            'facebook' => 'fa-facebook',
                            'twitter' => 'fa-twitter',
                            'instagram' => 'fa-instagram',
                            'youtube' => 'fa-youtube',
                            'tiktok' => 'fa-music',
                            'whatsapp' => 'fa-whatsapp',
                            'linkedin' => 'fa-linkedin'
                        ];
                        
                        foreach ($redes as $nombre => $icono):
                            if (!empty($sys[$nombre])): ?>
                                <li>
                                    <a href="<?= htmlspecialchars($sys[$nombre]) ?>" 
                                       title="<?= ucfirst($nombre) ?>"
                                       target="_blank">
                                        <i class="fa <?= $icono ?>" aria-hidden="true"></i>
                                    </a>
                                </li>
                        <?php endif; endforeach; ?>
                    </ul>

                    <!-- Copyright -->
                    <p class="footer-copyright">
                        © <?= date('Y') ?> 
                        <strong><?= htmlspecialchars($sys['site_name']) ?></strong>. 
                        Todos los derechos reservados.
                    </p>
                    <p class="footer-credits">
                        Hosting & Diseño por 
                        <a href="https://www.intermediahost.co" target="_blank" class="footer-credits-link">
                            Intermedia Host
                        </a>
                    </p>
                </div>
            </div>
        </div>
    </div>
</footer>
<!-- Footer Area End Here -->

<style>
    /* Footer Contact */
    .footer-contact {
        color: #fff;
    }
    
    .footer-text {
        line-height: 1.6;
    }
    
    .footer-contact-item {
        display: flex;
        align-items: start;
    }
    
    .footer-contact-item i {
        margin-right: 10px;
        margin-top: 3px;
        flex-shrink: 0;
    }
    
    .footer-link {
        color: inherit;
        transition: color 0.3s ease;
    }
    
    .footer-link:hover {
        color: var(--primary);
    }
    
    /* Footer News Links */
    .footer-news-link {
        display: block;
        padding: 8px 0;
        transition: color 0.3s ease;
    }
    
    .footer-icon {
        margin-right: 8px;
    }
    
    /* Footer Logo */
    .footer-logo-img {
        max-width: 200px;
    }
    
    /* Footer Copyright */
    .footer-copyright {
        margin-top: 15px;
    }
    
    .footer-credits {
        margin-top: 5px;
        font-size: 13px;
    }
    
    .footer-credits-link {
        color: inherit;
        font-weight: bold;
        transition: color 0.3s ease;
    }
    
    .footer-credits-link:hover {
        color: var(--primary);
    }
</style>

<!-- Modal Start (Login Form) -->
<div class="modal fade" id="myModal" role="dialog">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <div class="title-login-form">Iniciar Sesión</div>
            </div>
            <div class="modal-body">
                <div class="login-form">
                    <form>
                        <label>Usuario o correo electrónico *</label>
                        <input type="text" placeholder="Nombre o E-mail" />
                        <label>Contraseña *</label>
                        <input type="password" placeholder="Contraseña" />
                        <div class="checkbox checkbox-primary">
                            <input id="checkbox" type="checkbox" checked>
                            <label for="checkbox">Recordarme</label>
                        </div>
                        <button type="submit" value="Login">Iniciar Sesión</button>
                        <button class="form-cancel" type="button" data-dismiss="modal">Cancelar</button>
                        <label class="lost-password">
                            <a href="#">¿Olvidaste tu contraseña?</a>
                        </label>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- Modal End -->

<!-- Offcanvas Menu Start -->
<div id="offcanvas-body-wrapper" class="offcanvas-body-wrapper">
    <div id="offcanvas-nav-close" class="offcanvas-nav-close offcanvas-menu-btn">
        <a href="#" class="menu-times re-point">
            <span></span>
            <span></span>
        </a>
    </div>
    <div class="offcanvas-main-body">
        <ul id="accordion" class="offcanvas-nav panel-group">
            
            <!-- INICIO -->
            <li>
                <a href="<?= URLBASE ?>">
                    <i class="fa fa-home" aria-hidden="true"></i><?= t_theme('theme_inicio') ?>
                </a>
            </li>

            <!-- NOTICIAS -->
            <li>
                <a href="<?= URLBASE ?>/noticias">
                    <i class="fa fa-newspaper-o" aria-hidden="true"></i><?= t_theme('theme_noticias') ?>
                </a>
            </li>

            <!-- CATEGORÍAS DINÁMICAS -->
            <?php
            $menuCats = db()->query("
                SELECT c.name, c.slug, COUNT(p.id) AS total
                FROM blog_categories c
                INNER JOIN blog_post_category pc ON pc.category_id = c.id
                INNER JOIN blog_posts p ON p.id = pc.post_id
                WHERE c.status='active' AND c.deleted=0
                  AND p.status='published' AND p.deleted=0
                GROUP BY c.id, c.name, c.slug
                HAVING total > 0
                ORDER BY c.name ASC
            ")->fetchAll(PDO::FETCH_ASSOC);
            
            if (!empty($menuCats)): 
            ?>
                <li class="panel panel-default">
                    <div class="panel-heading">
                        <a aria-expanded="false" 
                           class="accordion-toggle collapsed" 
                           data-toggle="collapse" 
                           data-parent="#accordion" 
                           href="#collapseCategories">
                            <i class="fa fa-folder" aria-hidden="true"></i><?= t_theme('theme_categorias') ?>
                        </a>
                    </div>
                    <div aria-expanded="false" 
                         id="collapseCategories" 
                         role="tabpanel" 
                         class="panel-collapse collapse">
                        <div class="panel-body">
                            <ul class="offcanvas-sub-nav">
                                <?php foreach ($menuCats as $cat): ?>
                                    <li>
                                        <a href="<?= URLBASE ?>/noticias/<?= htmlspecialchars($cat['slug']) ?>/">
                                            <?= htmlspecialchars($cat['name']) ?>
                                        </a>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    </div>
                </li>
            <?php endif; ?>

            <!-- COLUMNISTAS (solo si existen) -->
            <?php
            $menuColumnistas = db()->query("
                SELECT nombre, apellido, username
                FROM usuarios
                WHERE es_columnista = 1
                  AND estado = 0
                  AND borrado = 0
                ORDER BY nombre ASC, apellido ASC
            ")->fetchAll(PDO::FETCH_ASSOC);
            
            if (!empty($menuColumnistas)): 
            ?>
                <li class="panel panel-default">
                    <div class="panel-heading">
                        <a aria-expanded="false" 
                           class="accordion-toggle collapsed" 
                           data-toggle="collapse" 
                           data-parent="#accordion" 
                           href="#collapseColumnistas">
                            <i class="fa fa-pencil-square-o" aria-hidden="true"></i><?= t_theme('theme_columnistas') ?>
                        </a>
                    </div>
                    <div aria-expanded="false" 
                         id="collapseColumnistas" 
                         role="tabpanel" 
                         class="panel-collapse collapse">
                        <div class="panel-body">
                            <ul class="offcanvas-sub-nav">
                                <?php foreach ($menuColumnistas as $col): 
                                    $nombreCompleto = trim($col['nombre'] . ' ' . $col['apellido']);
                                ?>
                                    <li>
                                        <a href="<?= URLBASE ?>/columnista/<?= htmlspecialchars($col['username']) ?>/">
                                            <?= htmlspecialchars($nombreCompleto) ?>
                                        </a>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    </div>
                </li>
            <?php endif; ?>

            <!-- NOSOTROS / INSTITUCIONAL DINÁMICO -->
            <?php
            $menuInstitucional = db()->query("
                SELECT title, slug 
                FROM institutional_pages 
                WHERE status = 'published' 
                ORDER BY display_order ASC, title ASC
            ")->fetchAll(PDO::FETCH_ASSOC);
            
            if (!empty($menuInstitucional)): 
            ?>
                <li class="panel panel-default">
                    <div class="panel-heading">
                        <a aria-expanded="false" 
                           class="accordion-toggle collapsed" 
                           data-toggle="collapse" 
                           data-parent="#accordion" 
                           href="#collapseInstitucional">
                            <i class="fa fa-building-o" aria-hidden="true"></i><?= t_theme('theme_nosotros') ?>
                        </a>
                    </div>
                    <div aria-expanded="false" 
                         id="collapseInstitucional" 
                         role="tabpanel" 
                         class="panel-collapse collapse">
                        <div class="panel-body">
                            <ul class="offcanvas-sub-nav">
                                <?php foreach ($menuInstitucional as $inst): ?>
                                    <li>
                                        <a href="<?= URLBASE ?>/institucional/<?= htmlspecialchars($inst['slug']) ?>">
                                            <?= htmlspecialchars($inst['title']) ?>
                                        </a>
                                    </li>
                                <?php endforeach; ?>
                                <li>
                                    <a href="<?= URLBASE ?>/institucional">
                                        <i class="fa fa-list mr-2"></i><?= t_theme('theme_ver_todas') ?>
                                    </a>
                                </li>
                            </ul>
                        </div>
                    </div>
                </li>
            <?php endif; ?>

            <!-- CONTACTO -->
            <li>
                <a href="<?= URLBASE ?>/contact">
                    <i class="fa fa-phone" aria-hidden="true"></i><?= t_theme('theme_contacto') ?>
                </a>
            </li>

            <!-- POLÍTICAS -->
            <li>
                <a href="<?= URLBASE ?>/privacy-policy">
                    <i class="fa fa-shield" aria-hidden="true"></i>Política de Privacidad
                </a>
            </li>
            <li>
                <a href="<?= URLBASE ?>/terms-and-conditions">
                    <i class="fa fa-file-text" aria-hidden="true"></i>Términos y Condiciones
                </a>
            </li>
            
        </ul>
    </div>
</div>
<!-- Offcanvas Menu End -->

</div>
<!-- Wrapper End -->

<!-- Scripts -->
<script src="<?= URLBASE ?>/template/NewsEdge/js/jquery-2.2.4.min.js"></script>
<script src="<?= URLBASE ?>/template/NewsEdge/js/plugins.js"></script>
<script src="<?= URLBASE ?>/template/NewsEdge/js/popper.js"></script>
<script src="<?= URLBASE ?>/template/NewsEdge/js/bootstrap.min.js"></script>
<script src="<?= URLBASE ?>/template/NewsEdge/js/wow.min.js"></script>
<script src="<?= URLBASE ?>/template/NewsEdge/vendor/slider/js/jquery.nivo.slider.js"></script>
<script src="<?= URLBASE ?>/template/NewsEdge/vendor/slider/home.js"></script>
<script src="<?= URLBASE ?>/template/NewsEdge/vendor/OwlCarousel/owl.carousel.min.js"></script>
<script src="<?= URLBASE ?>/template/NewsEdge/js/jquery.meanmenu.min.js"></script>
<script src="<?= URLBASE ?>/template/NewsEdge/js/jquery.scrollUp.min.js"></script>
<script src="<?= URLBASE ?>/template/NewsEdge/js/jquery.counterup.min.js"></script>
<script src="<?= URLBASE ?>/template/NewsEdge/js/waypoints.min.js"></script>
<script src="<?= URLBASE ?>/template/NewsEdge/js/isotope.pkgd.min.js"></script>
<script src="<?= URLBASE ?>/template/NewsEdge/js/jquery.magnific-popup.min.js"></script>
<script src="<?= URLBASE ?>/template/NewsEdge/js/ticker.js"></script>

<script>
    var SITE_CONFIG = {
        urlBase: '<?= URLBASE ?>',
        siteLogo: '<?= URLBASE . SITE_LOGO ?>?<?= time() ?>',
        siteLogoAlt: '<?= htmlspecialchars(NOMBRE_SITIO) ?>'
    };
</script>

<!-- Luego cargas main.js -->
<script src="<?= URLBASE ?>/template/NewsEdge/js/main.js?<?= time() ?>"></script>


<!-- Inicialización del Nivo Slider -->
<script>
$(document).ready(function() {
    if ($('#ensign-nivoslider-3').length) {
        $('#ensign-nivoslider-3').nivoSlider({
            effect: 'random',
            slices: 15,
            boxCols: 8,
            boxRows: 4,
            animSpeed: 500,
            pauseTime: 5000,
            startSlide: 0,
            directionNav: true,
            controlNav: true,
            controlNavThumbs: false,
            pauseOnHover: true,
            manualAdvance: false,
            prevText: '<i class="fa fa-angle-left nivo-prev-icon"></i>',
            nextText: '<i class="fa fa-angle-right nivo-next-icon"></i>'
        });
    }
});
</script>

<!-- Isotope Filter -->
<script>
document.addEventListener("DOMContentLoaded", function () {
    const filterLinks = document.querySelectorAll(".isotope-classes-tab a");
    filterLinks.forEach(link => {
        link.addEventListener("click", function (e) {
            e.preventDefault();
            filterLinks.forEach(l => l.classList.remove("current"));
            this.classList.add("current");
            const filterValue = this.getAttribute("data-filter");
            $('.featuredContainer').isotope({ filter: filterValue });
        });
    });
});
</script>

<!-- Código personalizado del footer -->
<?= $sys['code_footer'] ?? '' ?>

<!-- Reproductor de radio -->
<?php 
$playerPath = __DIR__ . '/../../../inc/core/player.php';
if (file_exists($playerPath)) {
    include $playerPath;
}
?>



<script>
    // Script para expandir/contraer el buscador
    document.addEventListener('DOMContentLoaded', function() {
        const searchForm = document.getElementById('top-search-form');
        const searchButton = searchForm.querySelector('.search-button');
        const searchInput = searchForm.querySelector('.search-input');
        
        searchButton.addEventListener('click', function(e) {
            // Si el formulario no está activo, expandir el input
            if (!searchForm.classList.contains('active')) {
                e.preventDefault();
                searchForm.classList.add('active');
                searchInput.focus();
            }
            // Si está activo y el input está vacío, contraer
            else if (searchInput.value.trim() === '') {
                e.preventDefault();
                searchForm.classList.remove('active');
            }
            // Si está activo y tiene texto, permitir el submit
        });
        
        // Cerrar el buscador si se hace clic fuera de él
        document.addEventListener('click', function(e) {
            if (!searchForm.contains(e.target)) {
                searchForm.classList.remove('active');
            }
        });
        
        // Prevenir que el clic en el input cierre el formulario
        searchInput.addEventListener('click', function(e) {
            e.stopPropagation();
        });
    });
</script>

<script>
  $(function () {
    $('#searchModal').on('shown.bs.modal', function () {
      $('#searchModalInput').trigger('focus');
    });

    $('#searchModalForm').on('submit', function () {
      var val = ($('#searchModalInput').val() || '').trim();
      $('#searchHiddenS').val(val);
      $('#searchHiddenSearch').val(val);
    });
  });
</script>


<!--fin player-->
</div>
</body>
</html>