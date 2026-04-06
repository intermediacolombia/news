<?php
if (!function_exists('truncate_text')) {
    function truncate_text(string $text, int $limit = 100): string {
        $text = strip_tags($text);
        return (mb_strlen($text) > $limit) ? mb_substr($text, 0, $limit) . '...' : $text;
    }
}

global $sys;
?>

<div class="modal fade search-modal" id="searchModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-body p-4">
                <button type="button" class="close position-absolute" data-dismiss="modal" aria-label="Cerrar" style="right: 20px; top: 20px; color: #fff; opacity: 0.7;">
                    <i class="fas fa-times fa-lg"></i>
                </button>

                <h3 class="mb-3" style="color: #fff; font-family: 'Playfair Display', serif;">Buscar</h3>
                <p class="mb-4" style="color: var(--text-muted);">Escribe lo que necesitas y presiona "Buscar".</p>

                <form action="<?= URLBASE ?>/buscar/" method="get">
                    <div class="input-group">
                        <input type="text"
                               name="q"
                               class="search-input"
                               placeholder="Buscar noticias, artículos..."
                               required
                               style="width: 100%;">
                    </div>
                    <button type="submit" class="btn-artemis w-100 mt-3">
                        <i class="fas fa-search mr-2"></i> Buscar
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<footer class="footer-section pt-5 pb-4">
    <div class="container">
        <div class="row">
            <div class="col-lg-4 col-md-6 mb-4">
                <h5 class="mb-4" style="color: #fff; font-family: 'Playfair Display', serif;">Contáctanos</h5>
                <?php if (!empty($sys['info_footer'])): ?>
                    <p class="mb-3" style="color: var(--text-muted);"><?= htmlspecialchars($sys['info_footer']) ?></p>
                <?php endif; ?>
                
                <?php if (!empty($sys['business_address'])): ?>
                    <p class="mb-2" style="color: var(--text-muted);">
                        <i class="fas fa-map-marker-alt mr-2" style="color: var(--primary);"></i>
                        <?= htmlspecialchars($sys['business_address']) ?>
                    </p>
                <?php endif; ?>
                
                <?php if (!empty($sys['site_email'])): ?>
                    <p class="mb-2" style="color: var(--text-muted);">
                        <i class="fas fa-envelope mr-2" style="color: var(--primary);"></i>
                        <a href="mailto:<?= htmlspecialchars($sys['site_email']) ?>" style="color: var(--text-muted); text-decoration: none;">
                            <?= htmlspecialchars($sys['site_email']) ?>
                        </a>
                    </p>
                <?php endif; ?>
                
                <?php if (!empty($sys['business_phone'])): ?>
                    <p class="mb-2" style="color: var(--text-muted);">
                        <i class="fas fa-phone mr-2" style="color: var(--primary);"></i>
                        <a href="tel:<?= htmlspecialchars($sys['business_phone']) ?>" style="color: var(--text-muted); text-decoration: none;">
                            <?= htmlspecialchars($sys['business_phone']) ?>
                        </a>
                    </p>
                <?php endif; ?>
                
                <?php if (!empty($sys['whatsapp'])): ?>
                    <p class="mb-2" style="color: var(--text-muted);">
                        <i class="fab fa-whatsapp mr-2" style="color: var(--primary);"></i>
                        <a href="https://wa.me/<?= preg_replace('/[^0-9]/', '', $sys['whatsapp']) ?>" 
                           target="_blank" style="color: var(--text-muted); text-decoration: none;">
                            WhatsApp
                        </a>
                    </p>
                <?php endif; ?>
            </div>

            <div class="col-lg-4 col-md-6 mb-4">
                <h5 class="mb-4" style="color: #fff; font-family: 'Playfair Display', serif;">Últimas Noticias</h5>
                <ul class="list-unstyled">
                    <?php
                    $latestNews = db()->query("
                        SELECT p.title, p.slug, c.slug as category_slug
                        FROM blog_posts p
                        LEFT JOIN blog_post_category pc ON pc.post_id = p.id
                        LEFT JOIN blog_categories c ON c.id = pc.category_id
                        WHERE p.status='published' AND p.deleted=0
                        ORDER BY p.created_at DESC
                        LIMIT 4
                    ")->fetchAll(PDO::FETCH_ASSOC);
                    
                    foreach ($latestNews as $news):
                        $postUrl = URLBASE . "/" . htmlspecialchars($news['category_slug']) . "/" . htmlspecialchars($news['slug']) . "/";
                    ?>
                        <li class="mb-2">
                            <a href="<?= $postUrl ?>" style="color: var(--text-muted); text-decoration: none; transition: color 0.3s;">
                                <i class="fas fa-angle-right mr-2" style="color: var(--primary); font-size: 12px;"></i>
                                <?= htmlspecialchars(truncate_text($news['title'], 40)) ?>
                            </a>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>

            <div class="col-lg-4 col-md-12 mb-4">
                <h5 class="mb-4" style="color: #fff; font-family: 'Playfair Display', serif;">Categorías</h5>
                <ul class="list-unstyled">
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
                        <li class="mb-2">
                            <a href="<?= URLBASE ?>/<?= htmlspecialchars($cat['slug']) ?>/" 
                               style="color: var(--text-muted); text-decoration: none; transition: color 0.3s;">
                                <i class="fas fa-folder mr-2" style="color: var(--primary); font-size: 12px;"></i>
                                <?= htmlspecialchars(ucwords($cat['name'])) ?>
                                <span style="background: rgba(230, 57, 70, 0.2); padding: 2px 8px; border-radius: 10px; font-size: 11px; margin-left: 8px;">
                                    <?= $cat['total'] ?>
                                </span>
                            </a>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>
        </div>

        <div class="row mt-4 pt-4" style="border-top: 1px solid rgba(255,255,255,0.08);">
            <div class="col-12 text-center">
                <a href="<?= URLBASE ?>" class="footer-logo mb-3 d-inline-block">
                    <img src="<?= URLBASE . SITE_LOGO ?>?v=<?= time() ?>" 
                         alt="<?= htmlspecialchars($sys['site_name']) ?>" 
                         style="max-width: 180px;">
                </a>

                <ul class="list-inline mb-3">
                    <?php
                    $redes = [
                        'facebook' => 'fa-facebook',
                        'twitter' => 'fa-twitter',
                        'instagram' => 'fa-instagram',
                        'youtube' => 'fa-youtube',
                        'tiktok' => 'fa-tiktok',
                        'whatsapp' => 'fa-whatsapp',
                        'linkedin' => 'fa-linkedin'
                    ];
                    
                    foreach ($redes as $nombre => $icono):
                        if (!empty($sys[$nombre])): ?>
                            <li class="list-inline-item mr-3">
                                <a href="<?= htmlspecialchars($sys[$nombre]) ?>" 
                                   title="<?= ucfirst($nombre) ?>"
                                   target="_blank"
                                   style="color: var(--text-muted); font-size: 20px; transition: color 0.3s;">
                                    <i class="fab <?= $icono ?>"></i>
                                </a>
                            </li>
                    <?php endif; endforeach; ?>
                </ul>

                <p class="mb-1" style="color: var(--text-muted); font-size: 14px;">
                    © <?= date('Y') ?> <strong style="color: #fff;"><?= htmlspecialchars($sys['site_name']) ?></strong>. Todos los derechos reservados.
                </p>
                <p style="color: var(--text-muted); font-size: 13px;">
                    Diseño por <a href="https://www.intermediahost.co" target="_blank" style="color: var(--primary); text-decoration: none;">Intermedia Host</a>
                </p>
            </div>
        </div>
    </div>
</footer>

<style>
    .footer-section a:hover {
        color: var(--primary) !important;
    }
</style>

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
                <a href="<?= URLBASE ?>">
                    <i class="fas fa-home" aria-hidden="true"></i>Inicio
                </a>
            </li>

            <li>
                <a href="<?= URLBASE ?>/noticias">
                    <i class="fas fa-newspaper" aria-hidden="true"></i>Noticias
                </a>
            </li>

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
                            <i class="fas fa-folder" aria-hidden="true"></i>Categorías
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
                            <i class="fas fa-pen-nib" aria-hidden="true"></i>Columnistas
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
                            <i class="fas fa-building" aria-hidden="true"></i>Nosotros
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
                                        <i class="fas fa-list mr-2"></i>Ver todas
                                    </a>
                                </li>
                            </ul>
                        </div>
                    </div>
                </li>
            <?php endif; ?>

            <li>
                <a href="<?= URLBASE ?>/contact">
                    <i class="fas fa-phone" aria-hidden="true"></i>Contacto
                </a>
            </li>

            <li>
                <a href="<?= URLBASE ?>/privacy-policy">
                    <i class="fas fa-shield-alt" aria-hidden="true"></i>Política de Privacidad
                </a>
            </li>
            <li>
                <a href="<?= URLBASE ?>/terms-and-conditions">
                    <i class="fas fa-file-alt" aria-hidden="true"></i>Términos y Condiciones
                </a>
            </li>
            
        </ul>
    </div>
</div>

</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/wow/1.1.2/wow.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/meanmenu@2.0.12/jquery.meanmenu.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>
<script src="<?= URLBASE ?>/template/Artemis/js/main.js?<?= time() ?>"></script>

<script>
    var SITE_CONFIG = {
        urlBase: '<?= URLBASE ?>',
        siteLogo: '<?= URLBASE . SITE_LOGO ?>?<?= time() ?>',
        siteLogoAlt: '<?= htmlspecialchars(NOMBRE_SITIO) ?>'
    };
</script>

<script>
  $(function () {
    $('#searchModal').on('shown.bs.modal', function () {
      $('#searchModalInput').trigger('focus');
    });
  });
</script>

<?php 
$playerPath = __DIR__ . '/../../../inc/core/player.php';
if (file_exists($playerPath)) {
    include $playerPath;
}
?>

<?= $sys['code_footer'] ?? '' ?>

</div>
</body>
</html>