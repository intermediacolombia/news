<!-- Footer Area Start Here -->
<footer>
    <div class="footer-area-top">
        <div class="container">
            <div class="row">
                <!-- Most Viewed Posts / Posts Más Vistos -->
                <div class="col-lg-4 col-md-6 col-sm-12">
                    <div class="footer-box">
                        <h2 class="title-bold-light title-bar-left text-uppercase">Posts Más Vistos</h2>
                        <ul class="most-view-post">
                            <?php
                            $mostViewed = db()->query("
                                SELECT p.title, p.slug, p.image, p.created_at
                                FROM blog_posts p
                                LEFT JOIN blog_post_views v ON v.post_id = p.id
                                WHERE p.status='published' AND p.deleted=0
                                GROUP BY p.id
                                ORDER BY COUNT(v.id) DESC, p.created_at DESC
                                LIMIT 3
                            ")->fetchAll(PDO::FETCH_ASSOC);
                            
                            foreach ($mostViewed as $post):
                                $img = !empty($post['image'])
                                    ? URLBASE . '/' . htmlspecialchars($post['image'])
                                    : URLBASE . '/template/NewsEdge/img/footer/post1.jpg';
                                $postUrl = URLBASE . "/noticias/post/" . htmlspecialchars($post['slug']);
                            ?>
                            <li>
                                <div class="media">
                                    <a href="<?= $postUrl ?>">
                                        <img src="<?= $img ?>" alt="<?= htmlspecialchars($post['title']) ?>" class="img-fluid">
                                    </a>
                                    <div class="media-body">
                                        <h3 class="title-medium-light size-md mb-10">
                                            <a href="<?= $postUrl ?>">
                                                <?= htmlspecialchars(truncate_text($post['title'], 60)) ?>
                                            </a>
                                        </h3>
                                        <div class="post-date-light">
                                            <ul>
                                                <li>
                                                    <span>
                                                        <i class="fa fa-calendar" aria-hidden="true"></i>
                                                    </span><?= date('F d, Y', strtotime($post['created_at'])) ?>
                                                </li>
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                            </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                </div>

                <!-- Popular Categories / Categorías Populares -->
                <div class="col-xl-4 col-lg-3 col-md-6 col-sm-12">
                    <div class="footer-box">
                        <h2 class="title-bold-light title-bar-left text-uppercase">Categorías Populares</h2>
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
                                LIMIT 8
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

                <!-- Post Gallery / Galería de Posts -->
                <div class="col-xl-4 col-lg-5 col-md-12 col-sm-12">
                    <div class="footer-box">
                        <h2 class="title-bold-light title-bar-left text-uppercase">Galería de Posts</h2>
                        <ul class="post-gallery shine-hover">
                            <?php
                            $gallery = db()->query("
                                SELECT p.slug, p.image
                                FROM blog_posts p
                                WHERE p.status='published' AND p.deleted=0 AND p.image IS NOT NULL
                                ORDER BY p.created_at DESC
                                LIMIT 9
                            ")->fetchAll(PDO::FETCH_ASSOC);
                            
                            foreach ($gallery as $item):
                                $imgUrl = !empty($item['image'])
                                    ? URLBASE . '/' . htmlspecialchars($item['image'])
                                    : URLBASE . '/template/NewsEdge/img/footer/post4.jpg';
                                $itemUrl = URLBASE . "/noticias/post/" . htmlspecialchars($item['slug']);
                            ?>
                            <li>
                                <a href="<?= $itemUrl ?>">
                                    <figure>
                                        <img src="<?= $imgUrl ?>" alt="post" class="img-fluid">
                                    </figure>
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