<!-- Footer Area Start Here -->
<footer>
    <div class="footer-area-top">
        <div class="container">
            <div class="row">
                <!-- Columna 1: Contáctanos -->
                <div class="col-lg-4 col-md-6 col-sm-12">
                    <div class="footer-box">
                        <h2 class="title-bold-light title-bar-left text-uppercase">Contáctanos</h2>
                        <div class="footer-contact">
                            <?php if (!empty($sys['info_footer'])): ?>
                                <p class="mb-3"><?= htmlspecialchars($sys['info_footer']) ?></p>
                            <?php endif; ?>
                            
                            <?php if (!empty($sys['business_address'])): ?>
                                <p class="mb-2">
                                    <i class="fa fa-map-marker" aria-hidden="true"></i>
                                    <span style="margin-left: 10px;"><?= htmlspecialchars($sys['business_address']) ?></span>
                                </p>
                            <?php endif; ?>
                            
                            <?php if (!empty($sys['site_email'])): ?>
                                <p class="mb-2">
                                    <i class="fa fa-envelope" aria-hidden="true"></i>
                                    <span style="margin-left: 10px;">
                                        <a href="mailto:<?= htmlspecialchars($sys['site_email']) ?>" style="color: inherit;">
                                            <?= htmlspecialchars($sys['site_email']) ?>
                                        </a>
                                    </span>
                                </p>
                            <?php endif; ?>
                            
                            <?php if (!empty($sys['business_phone'])): ?>
                                <p class="mb-2">
                                    <i class="fa fa-phone" aria-hidden="true"></i>
                                    <span style="margin-left: 10px;">
                                        <a href="tel:<?= htmlspecialchars($sys['business_phone']) ?>" style="color: inherit;">
                                            <?= htmlspecialchars($sys['business_phone']) ?>
                                        </a>
                                    </span>
                                </p>
                            <?php endif; ?>
                            
                            <?php if (!empty($sys['whatsapp'])): ?>
                                <p class="mb-2">
                                    <i class="fa fa-whatsapp" aria-hidden="true"></i>
                                    <span style="margin-left: 10px;">
                                        <a href="https://wa.me/<?= preg_replace('/[^0-9]/', '', $sys['whatsapp']) ?>" 
                                           target="_blank" 
                                           style="color: inherit;">
                                            WhatsApp
                                        </a>
                                    </span>
                                </p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- Columna 2: Últimas Noticias (solo títulos, máx 5) -->
                <div class="col-xl-4 col-lg-3 col-md-6 col-sm-12">
                    <div class="footer-box">
                        <h2 class="title-bold-light title-bar-left text-uppercase">Últimas Noticias</h2>
                        <ul class="popular-categories">
                            <?php
                            $latestNews = db()->query("
                                SELECT p.title, p.slug
                                FROM blog_posts p
                                WHERE p.status='published' AND p.deleted=0
                                ORDER BY p.created_at DESC
                                LIMIT 5
                            ")->fetchAll(PDO::FETCH_ASSOC);
                            
                            foreach ($latestNews as $news):
                                $postUrl = URLBASE . "/noticias/post/" . htmlspecialchars($news['slug']);
                            ?>
                                <li>
                                    <a href="<?= $postUrl ?>" style="display: block; padding: 8px 0;">
                                        <i class="fa fa-angle-right" style="margin-right: 8px;"></i>
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
                        <h2 class="title-bold-light title-bar-left text-uppercase">Categorías</h2>
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
                                    <a href="<?= URLBASE ?>/noticias/<?= htmlspecialchars($cat['slug']) ?>/">
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
                    <a href="<?= URLBASE ?>" class="footer-logo img-fluid">
                        <img src="<?= URLBASE . SITE_LOGO ?>?v=<?= time() ?>" 
                             alt="<?= htmlspecialchars($sys['site_name']) ?>" 
                             class="img-fluid" 
                             style="max-width: 200px;">
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
                    <p>
                        © <?= date('Y') ?> 
                        <strong><?= htmlspecialchars($sys['site_name']) ?></strong>. 
                        Todos los derechos reservados.
                    </p>
                    <p style="margin-top: 5px; font-size: 13px;">
                        Hosting & Diseño por 
                        <a href="https://www.intermediahost.co" target="_blank" style="color: inherit; font-weight: bold;">
                            Intermedia Host
                        </a>
                    </p>
                </div>
            </div>
        </div>
    </div>
</footer>
<!-- Footer Area End Here -->

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
            <!-- Home -->
            <li>
                <a href="<?= URLBASE ?>">
                    <i class="fa fa-home" aria-hidden="true"></i>Inicio
                </a>
            </li>

            <!-- Categorías Dinámicas -->
            <?php
            $menuCats = db()->query("
                SELECT c.name, c.slug
                FROM blog_categories c
                WHERE c.status='active' AND c.deleted=0
                ORDER BY c.name ASC
                LIMIT 10
            ")->fetchAll(PDO::FETCH_ASSOC);
            
            if (!empty($menuCats)): ?>
            <li class="panel panel-default">
                <div class="panel-heading">
                    <a aria-expanded="false" class="accordion-toggle collapsed" 
                       data-toggle="collapse" data-parent="#accordion" href="#collapseCategories">
                        <i class="fa fa-folder" aria-hidden="true"></i>Categorías
                    </a>
                </div>
                <div aria-expanded="false" id="collapseCategories" role="tabpanel" class="panel-collapse collapse">
                    <div class="panel-body">
                        <ul class="offcanvas-sub-nav">
                            <?php foreach ($menuCats as $cat): ?>
                                <li>
                                    <a href="<?= URLBASE ?>/noticias/<?= htmlspecialchars($cat['slug']) ?>/">
                                        <?= htmlspecialchars(ucwords($cat['name'])) ?>
                                    </a>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                </div>
            </li>
            <?php endif; ?>

            <!-- Contacto -->
            <li>
                <a href="<?= URLBASE ?>/contact">
                    <i class="fa fa-phone" aria-hidden="true"></i>Contacto
                </a>
            </li>

            <!-- Políticas -->
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

<!-- jquery -->
<script src="<?= URLBASE ?>/template/NewsEdge/js/jquery-2.2.4.min.js" type="text/javascript"></script>
<!-- Plugins js -->
<script src="<?= URLBASE ?>/template/NewsEdge/js/plugins.js" type="text/javascript"></script>
<!-- Popper js -->
<script src="<?= URLBASE ?>/template/NewsEdge/js/popper.js" type="text/javascript"></script>
<!-- Bootstrap js -->
<script src="<?= URLBASE ?>/template/NewsEdge/js/bootstrap.min.js" type="text/javascript"></script>
<!-- WOW JS -->
<script src="<?= URLBASE ?>/template/NewsEdge/js/wow.min.js"></script>
<!-- Owl Cauosel JS -->
<script src="<?= URLBASE ?>/template/NewsEdge/vendor/OwlCarousel/owl.carousel.min.js" type="text/javascript"></script>
<!-- Meanmenu Js -->
<script src="<?= URLBASE ?>/template/NewsEdge/js/jquery.meanmenu.min.js" type="text/javascript"></script>
<!-- Srollup js -->
<script src="<?= URLBASE ?>/template/NewsEdge/js/jquery.scrollUp.min.js" type="text/javascript"></script>
<!-- jquery.counterup js -->
<script src="<?= URLBASE ?>/template/NewsEdge/js/jquery.counterup.min.js"></script>
<script src="<?= URLBASE ?>/template/NewsEdge/js/waypoints.min.js"></script>
<!-- Isotope js -->
<script src="<?= URLBASE ?>/template/NewsEdge/js/isotope.pkgd.min.js" type="text/javascript"></script>
<!-- Magnific Popup -->
<script src="<?= URLBASE ?>/template/NewsEdge/js/jquery.magnific-popup.min.js"></script>
<!-- Ticker Js -->
<script src="<?= URLBASE ?>/template/NewsEdge/js/ticker.js" type="text/javascript"></script>
<!-- Custom Js -->
<script src="<?= URLBASE ?>/template/NewsEdge/js/main.js" type="text/javascript"></script>

<!-- Código personalizado del footer -->
<?= $sys['code_footer'] ?? '' ?>

<!-- Reproductor de radio -->
<?php 
$playerPath = __DIR__ . '/../../../inc/core/player.php';
if (file_exists($playerPath)) {
    include $playerPath;
}
?>

</body>
</html>