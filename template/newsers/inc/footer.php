<!-- Footer Start -->
<div class="container-fluid bg-dark footer py-5 mt-5">
    <div class="container py-5">

        <!-- Logo + Suscripción -->
        <div class="pb-4 mb-4 border-bottom border-light">
            <div class="row g-4 align-items-center">
                <div class="col-lg-3">
                    <a href="<?= URLBASE ?>" class="d-flex flex-column flex-wrap text-decoration-none">
                        <img src="<?= URLBASE . SITE_LOGO ?>?<?= time() ?>" alt="<?= htmlspecialchars($sys['site_name']) ?>" width="160" class="mb-2">
                        <small class="text-light" style="letter-spacing: 8px; line-height: 1;"><?= htmlspecialchars($sys['site_name']) ?></small>
                    </a>
                </div>
                <div class="col-lg-9">
                    <div class="d-flex position-relative rounded-pill overflow-hidden">
                        <input class="form-control border-0 w-100 py-3 rounded-pill" type="email" placeholder="example@gmail.com">
                        <button type="submit" class="btn btn-primary border-0 py-3 px-5 rounded-pill text-white position-absolute" style="top: 0; right: 0;">Suscribirse</button>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-5">

            <!-- Datos de contacto -->
            <div class="col-lg-6 col-xl-3">
                <div class="footer-item-1">
                    <h4 class="mb-4 text-white">Contáctanos</h4>
                    <p class="text-secondary line-h mb-1">Dirección: <span class="text-white"><?= htmlspecialchars($sys['business_address'] ?? '') ?></span></p>
                    <p class="text-secondary line-h mb-1">Email: <span class="text-white"><?= htmlspecialchars($sys['site_email'] ?? '') ?></span></p>
                    <p class="text-secondary line-h mb-3">Teléfono: <span class="text-white"><?= htmlspecialchars($sys['business_phone'] ?? '') ?></span></p>
                    
                    <div class="d-flex">
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
                                <a class="btn btn-light me-2 btn-md-square rounded-circle" href="<?= htmlspecialchars($sys[$nombre]) ?>" target="_blank">
                                    <i class="<?= $icono ?> text-dark"></i>
                                </a>
                        <?php endif; endforeach; ?>
                    </div>
                </div>
            </div>

            <!-- Últimas noticias -->
            <div class="col-lg-6 col-xl-3">
                <div class="footer-item-2">
                    <h4 class="mb-4 text-white">Últimas Noticias</h4>
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
                        $img = !empty($post['image']) ? URLBASE . '/' . $post['image'] : URLBASE . '/public/images/no-image.jpg';
                    ?>
                    <a href="<?= URLBASE ?>/<?= htmlspecialchars($post['category_slug']) ?>/<?= htmlspecialchars($post['post_slug']) ?>/" class="text-decoration-none mb-3 d-block">
                        <div class="d-flex align-items-center">
                            <div class="rounded-circle border border-2 border-primary overflow-hidden" style="width:60px; height:60px;">
                                <img src="<?= $img ?>" class="img-fluid rounded-circle" alt="<?= htmlspecialchars($post['title']) ?>" style="object-fit: cover;">
                            </div>
                            <div class="d-flex flex-column ps-3">
                                <p class="text-uppercase text-white mb-1 small"><?= htmlspecialchars($post['category_name']) ?></p>
                                <span class="h6 text-white mb-0"><?= htmlspecialchars($post['title']) ?></span>
                                <small class="text-white-50"><i class="fas fa-calendar-alt me-1"></i> <?= strftime('%d %b %Y', strtotime($post['created_at'])) ?></small>
                            </div>
                        </div>
                    </a>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Categorías dinámicas -->
            <div class="col-lg-6 col-xl-3">
                <div class="footer-item-3">
                    <h4 class="mb-4 text-white">Categorías</h4>
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
                        <a class="btn-link text-white d-block mb-1" href="<?= URLBASE ?>/noticias/<?= htmlspecialchars($cat['slug']) ?>/">
                            <i class="fas fa-angle-right text-white me-2"></i> <?= htmlspecialchars(ucwords($cat['name'])) ?>
                        </a>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Galería dinámica (imágenes destacadas recientes) -->
            <div class="col-lg-6 col-xl-3">
                <div class="footer-item-4">
                    <h4 class="mb-4 text-white">Galería</h4>
                    <div class="row g-2">
                        <?php
                        $galeria = $pdo->query("
                            SELECT image FROM blog_posts 
                            WHERE status='published' AND deleted=0 AND image IS NOT NULL
                            ORDER BY created_at DESC
                            LIMIT 6
                        ")->fetchAll(PDO::FETCH_ASSOC);
                        foreach ($galeria as $g): ?>
                            <div class="col-4">
                                <div class="rounded overflow-hidden">
                                    <img src="<?= URLBASE . '/' . htmlspecialchars($g['image']) ?>" 
                                         class="img-fluid img-zoomin w-100" alt="Post">
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>
<!-- Footer End -->


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
<script src="<?= URLBASE ?>/template/news/lib/easing/easing.min.js"></script>
<script src="<?= URLBASE ?>/template/news/lib/waypoints/waypoints.min.js"></script>
<script src="<?= URLBASE ?>/template/news/lib/owlcarousel/owl.carousel.min.js"></script>

<!-- Template Javascript -->
<script src="<?= URLBASE ?>/template/news/js/main.js?<?= time(); ?>"></script>
<?= $sys['code_footer'] ?? '' ?>

</body>
</html>
