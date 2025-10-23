<?php
require_once __DIR__ . '/../../../inc/config.php';

// Configurar idioma y zona horaria
setlocale(LC_TIME, 'es_ES.UTF-8', 'es_ES', 'spanish');
date_default_timezone_set('America/Bogota');

// Consultar últimas 10 publicaciones
$stmt = $pdo->query("
    SELECT p.title, p.slug AS post_slug, p.created_at,
           c.slug AS category_slug, c.name AS category_name, p.image
    FROM blog_posts p
    INNER JOIN blog_post_category pc ON pc.post_id = p.id
    INNER JOIN blog_categories c ON c.id = pc.category_id
    WHERE p.status='published' AND p.deleted=0
    ORDER BY p.created_at DESC
    LIMIT 10
");
$latestPosts = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Fecha actual en español
$fechaHoy = ucfirst(strftime('%A, %d de %B de %Y'));
?>

<!-- Navbar Start -->
<div class="container-fluid sticky-top px-0">

    <!-- Topbar -->
    <div class="container-fluid topbar bg-dark d-none d-lg-block">
        <div class="container px-0">
            <div class="topbar-top d-flex justify-content-between flex-lg-wrap">
                
                <!-- Tendencias dinámicas -->
                <div class="top-info flex-grow-0 d-flex align-items-center">
                    <span class="rounded-circle btn-sm-square bg-primary me-2">
                        <i class="fas fa-bolt text-white"></i>
                    </span>
                    <div class="pe-2 me-3 border-end border-white d-flex align-items-center">
                        <p class="mb-0 text-white fs-6 fw-normal">Tendencias</p>
                    </div>
                    <div class="overflow-hidden" style="width: 735px;">
                        <div id="note" class="ps-2 owl-carousel owl-theme">
                            <?php foreach ($latestPosts as $post): ?>
                                <div class="d-flex align-items-center">
                                    <?php if (!empty($post['image'])): ?>
                                        <img src="<?= URLBASE . '/' . htmlspecialchars($post['image']) ?>" 
                                             class="img-fluid rounded-circle border border-3 border-primary me-2" 
                                             style="width: 30px; height: 30px; object-fit: cover;" 
                                             alt="<?= htmlspecialchars($post['title']) ?>">
                                    <?php else: ?>
                                        <img src="<?= URLBASE ?>/public/images/no-image.jpg"
                                             class="img-fluid rounded-circle border border-3 border-primary me-2"
                                             style="width: 30px; height: 30px;" alt="">
                                    <?php endif; ?>
                                    <a href="<?= URLBASE ?>/<?= htmlspecialchars($post['category_slug']) ?>/<?= htmlspecialchars($post['post_slug']) ?>/" 
                                       class="text-white text-truncate link-hover mb-0">
                                        <?= htmlspecialchars($post['title']) ?>
                                    </a>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>

                <!-- Fecha + Redes Sociales -->
                <div class="top-link d-flex align-items-center flex-lg-wrap">
                    <i class="fas fa-calendar-alt text-white border-end border-secondary pe-2 me-2">
                        <span class="text-body ms-1"><?= $fechaHoy ?></span>
                    </i>

                    <div class="d-flex align-items-center">
                        <p class="mb-0 text-white me-2">Síguenos:</p>
                        <?php
                        $redes = ['facebook','twitter','instagram','youtube','tiktok'];
                        $icons = [
                            'facebook' => 'fab fa-facebook-f',
                            'twitter' => 'fab fa-twitter',
                            'instagram' => 'fab fa-instagram',
                            'youtube' => 'fab fa-youtube',
                            'tiktok' => 'fab fa-tiktok'
                        ];
                        foreach ($redes as $r):
                            if (!empty($sys[$r])): ?>
                                <a href="<?= htmlspecialchars($sys[$r]) ?>" target="_blank" class="me-2">
                                    <i class="<?= $icons[$r] ?> text-body link-hover"></i>
                                </a>
                            <?php endif;
                        endforeach;
                        ?>
                    </div>
                </div>

            </div>
        </div>
    </div>

    

