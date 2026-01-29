<!-- Footer Area Start Here -->
<footer>
    <div class="footer-area-bottom">
        <div class="container">
            <div class="row">
                <!-- Logo y Redes Sociales -->
                <div class="col-lg-3 col-md-6 mb-5 text-center text-lg-left">
                    <a href="<​?= URLBASE ?>" class="footer-logo img-fluid d-block mb-3">
                        <img src="<?= URLBASE . SITE_LOGO ?>?<?= time() ?>" alt="Logo" class="img-fluid" style="max-width: 150px;">
                    </a>
                    <p class="text-muted"><?= htmlspecialchars($sys['info_footer'] ?? '') ?></p>
                    
                    <ul class="footer-social">
                        <?php if (!empty($sys['facebook'])): ?>
                        <li>
                            <a href="<​?= htmlspecialchars($sys['facebook']) ?>" target="_blank" title="facebook">
                                <i class="fa fa-facebook" aria-hidden="true"></i>
                            </a>
                        </li>
                        <?php endif; ?>
                        
                        <?php if (!empty($sys['twitter'])): ?>
                        <li>
                            <a href="<​?= htmlspecialchars($sys['twitter']) ?>" target="_blank" title="twitter">
                                <i class="fa fa-twitter" aria-hidden="true"></i>
                            </a>
                        </li>
                        <?php endif; ?>
                        
                        <?php if (!empty($sys['instagram'])): ?>
                        <li>
                            <a href="<​?= htmlspecialchars($sys['instagram']) ?>" target="_blank" title="instagram">
                                <i class="fa fa-instagram" aria-hidden="true"></i>
                            </a>
                        </li>
                        <?php endif; ?>
                        
                        <?php if (!empty($sys['youtube'])): ?>
                        <li>
                            <a href="<​?= htmlspecialchars($sys['youtube']) ?>" target="_blank" title="youtube">
                                <i class="fa fa-youtube" aria-hidden="true"></i>
                            </a>
                        </li>
                        <?php endif; ?>
                        
                        <?php if (!empty($sys['tiktok'])): ?>
                        <li>
                            <a href="<​?= htmlspecialchars($sys['tiktok']) ?>" target="_blank" title="tiktok">
                                <i class="fa fa-tiktok" aria-hidden="true"></i>
                            </a>
                        </li>
                        <?php endif; ?>
                        
                        <?php if (!empty($sys['whatsapp'])): ?>
                        <li>
                            <a href="<​?= htmlspecialchars($sys['whatsapp']) ?>" target="_blank" title="whatsapp">
                                <i class="fa fa-whatsapp" aria-hidden="true"></i>
                            </a>
                        </li>
                        <?php endif; ?>
                    </ul>
                </div>

                <!-- Categorías Dinámicas -->
                <?php include __DIR__ . '/../partials/footer-categories.php'; ?>

                <!-- Tags Dinámicos -->
                <?php include __DIR__ . '/../partials/tags.php'; ?>

                <!-- Links Rápidos -->
                <div class="col-lg-3 col-md-6 mb-5">
                    <h4 class="font-weight-bold mb-4">Links Rápidos</h4>
                    <div class="d-flex flex-column justify-content-start">
                        <a class="text-secondary mb-2" href="<​?= URLBASE ?>">
                            <i class="fa fa-angle-right mr-2"></i>Inicio
                        </a>
                        <a class="text-secondary mb-2" href="<?= URLBASE ?>/noticias">
                            <i class="fa fa-angle-right mr-2"></i>Noticias
                        </a>
                        <a class="text-secondary mb-2" href="<?= URLBASE ?>/institucional">
                            <i class="fa fa-angle-right mr-2"></i>Nosotros
                        </a>
                        <a class="text-secondary mb-2" href="<?= URLBASE ?>/privacy-policy">
                            <i class="fa fa-angle-right mr-2"></i>Política de Privacidad
                        </a>
                        <a class="text-secondary mb-2" href="<?= URLBASE ?>/terms-and-conditions">
                            <i class="fa fa-angle-right mr-2"></i>Términos y Condiciones
                        </a>
                        <a class="text-secondary" href="<?= URLBASE ?>/contact">
                            <i class="fa fa-angle-right mr-2"></i>Contacto
                        </a>
                    </div>
                </div>
            </div>

            <!-- Copyright -->
            <div class="row mt-4">
                <div class="col-12 text-center">
                    <p class="text-muted">
                        © <?= date('Y') ?> 
                        <a href="<​?= URLBASE ?>" class="font-weight-bold"><?= NOMBRE_SITIO ?></a>. 
                        Todos los derechos reservados.
                    </p>
                    <p class="text-muted small">
                        Hosting & Diseño 
                        <a href="https://www.intermediahost.co" target="_blank" class="font-weight-bold">Intermedia Host</a>
                    </p>
                </div>
            </div>
        </div>
    </div>
</footer>
<!-- Footer Area End Here -->

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
            <li>
                <a href="<​?= URLBASE ?>">
                    <i class="fa fa-home" aria-hidden="true"></i>Inicio
                </a>
            </li>
            <li>
                <a href="<?= URLBASE ?>/noticias">
                    <i class="fa fa-newspaper-o" aria-hidden="true"></i>Noticias
                </a>
            </li>
            
            <!-- Categorías en Offcanvas -->
            <li class="panel panel-default">
                <div class="panel-heading">
                    <a aria-expanded="false" class="accordion-toggle collapsed" data-toggle="collapse" data-parent="#accordion" href="#collapseCategories">
                        <i class="fa fa-list" aria-hidden="true"></i>Categorías
                    </a>
                </div>
                <div aria-expanded="false" id="collapseCategories" role="tabpanel" class="panel-collapse collapse">
                    <div class="panel-body">
                        <ul class="offcanvas-sub-nav">
                            <?php
                            $stOffcanvas = db()->query("
                                SELECT c.name, c.slug
                                FROM blog_categories c
                                INNER JOIN blog_post_category pc ON pc.category_id = c.id
                                INNER JOIN blog_posts p ON p.id = pc.post_id
                                WHERE c.status='active' AND c.deleted=0
                                  AND p.status='published' AND p.deleted=0
                                GROUP BY c.id, c.name, c.slug
                                ORDER BY c.name ASC
                            ");
                            $catsOffcanvas = $stOffcanvas->fetchAll(PDO::FETCH_ASSOC);
                            foreach ($catsOffcanvas as $cat): ?>
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

            <!-- Institucional en Offcanvas -->
            <li class="panel panel-default">
                <div class="panel-heading">
                    <a aria-expanded="false" class="accordion-toggle collapsed" data-toggle="collapse" data-parent="#accordion" href="#collapseInstitutional">
                        <i class="fa fa-info-circle" aria-hidden="true"></i>Nosotros
                    </a>
                </div>
                <div aria-expanded="false" id="collapseInstitutional" role="tabpanel" class="panel-collapse collapse">
                    <div class="panel-body">
                        <ul class="offcanvas-sub-nav">
                            <?php
                            $stInstOffcanvas = db()->query("
                                SELECT title, slug 
                                FROM institutional_pages 
                                WHERE status = 'published' 
                                ORDER BY display_order ASC
                            ");
                            $instPagesOffcanvas = $stInstOffcanvas->fetchAll(PDO::FETCH_ASSOC);
                            foreach ($instPagesOffcanvas as $instPage): ?>
                                <li>
                                    <a href="<?= URLBASE ?>/institucional/<?= htmlspecialchars($instPage['slug']) ?>">
                                        <?= htmlspecialchars($instPage['title']) ?>
                                    </a>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                </div>
            </li>

            <li>
                <a href="<?= URLBASE ?>/contact">
                    <i class="fa fa-phone" aria-hidden="true"></i>Contacto
                </a>
            </li>
        </ul>
    </div>
</div>
<!-- Offcanvas Menu End -->

<!-- Back to Top -->
<a href="#" class="btn btn-dark back-to-top"><i class="fa fa-angle-up"></i></a>

</div>
<!-- Wrapper End -->

<!-- jQuery -->
<script src="<?= URLBASE ?>/template/NewsEdge/js/jquery-2.2.4.min.js" type="text/javascript"></script>
<!-- Plugins js -->
<script src="<?= URLBASE ?>/template/NewsEdge/js/plugins.js" type="text/javascript"></script>
<!-- Popper js -->
<script src="<?= URLBASE ?>/template/NewsEdge/js/popper.js" type="text/javascript"></script>
<!-- Bootstrap js -->
<script src="<?= URLBASE ?>/template/NewsEdge/js/bootstrap.min.js" type="text/javascript"></script>
<!-- WOW JS -->
<script src="<?= URLBASE ?>/template/NewsEdge/js/wow.min.js"></script>
<!-- Owl Carousel JS -->
<script src="<?= URLBASE ?>/template/NewsEdge/vendor/OwlCarousel/owl.carousel.min.js" type="text/javascript"></script>
<!-- Meanmenu Js -->
<script src="<?= URLBASE ?>/template/NewsEdge/js/jquery.meanmenu.min.js" type="text/javascript"></script>
<!-- Scroll Up js -->
<script src="<?= URLBASE ?>/template/NewsEdge/js/jquery.scrollUp.min.js" type="text/javascript"></script>
<!-- Counter Up js -->
<script src="<?= URLBASE ?>/template/NewsEdge/js/jquery.counterup.min.js"></script>
<script src="<?= URLBASE ?>/template/NewsEdge/js/waypoints.min.js"></script>
<!-- Nivo slider js -->
<script src="<?= URLBASE ?>/template/NewsEdge/vendor/slider/js/jquery.nivo.slider.js" type="text/javascript"></script>
<script src="<?= URLBASE ?>/template/NewsEdge/vendor/slider/home.js" type="text/javascript"></script>
<!-- Isotope js -->
<script src="<?= URLBASE ?>/template/NewsEdge/js/isotope.pkgd.min.js" type="text/javascript"></script>
<!-- Magnific Popup -->
<script src="<?= URLBASE ?>/template/NewsEdge/js/jquery.magnific-popup.min.js"></script>
<!-- Ticker Js -->
<script src="<?= URLBASE ?>/template/NewsEdge/js/ticker.js" type="text/javascript"></script>
<!-- Custom Js -->
<script src="<?= URLBASE ?>/template/NewsEdge/js/main.js?<?= time() ?>" type="text/javascript"></script>

<!-- Custom Footer Code -->
<?= $sys['code_footer'] ?? '' ?>

<!-- Player Include -->
<?php include __DIR__ . '/../../../inc/core/player.php'; ?>

</body>
</html>