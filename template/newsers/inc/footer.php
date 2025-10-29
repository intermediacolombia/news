
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






<?= $sys['code_footer'] ?? '' ?>

</div>
<?php include __DIR__ . '/../../../inc/core/player.php'; ?>
</body>
</html>
