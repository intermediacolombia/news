<?php
require_once __DIR__ . '/../../../inc/config.php';
setlocale(LC_TIME, 'es_ES.UTF-8', 'es_ES', 'spanish');
date_default_timezone_set('America/Bogota');

// Últimas publicaciones
$stmt = $pdo->query("
    SELECT p.title, p.slug AS post_slug, p.image,
           c.slug AS category_slug
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

<div class="container-fluid topbar bg-dark d-none d-lg-block">
    <div class="container px-0">
        <div class="topbar-top d-flex justify-content-between align-items-center flex-lg-nowrap">
            
            <!-- Tendencias -->
            <div class="top-info d-flex align-items-center flex-nowrap">
                <span class="rounded-circle btn-sm-square bg-primary me-2">
                    <i class="fas fa-bolt text-white"></i>
                </span>
                <div class="pe-2 me-3 border-end border-white d-flex align-items-center">
                    <p class="mb-0 text-white fs-6 fw-normal">Tendencias</p>
                </div>

                <!-- Bloque de noticias dinámicas -->
                <div class="overflow-hidden" style="width: 735px;">
                    <div id="newsTicker" class="ps-2 text-nowrap d-flex align-items-center"></div>
                </div>
            </div>

            <!-- Fecha + Redes Sociales -->
            <div class="top-link d-flex align-items-center flex-nowrap">
                <i class="fas fa-calendar-alt text-white border-end border-secondary pe-2 me-2">
                    <span class="text-body ms-1"><?= $fechaHoy ?></span>
                </i>
                <div class="d-flex align-items-center">
                    <p class="mb-0 text-white me-2">Síguenos:</p>
                    <?php
                    $redes = ['facebook','twitter','instagram','youtube','linkedin','tiktok'];
                    $icons = [
                        'facebook' => 'fab fa-facebook-f',
                        'twitter' => 'fab fa-twitter',
                        'instagram' => 'fab fa-instagram',
                        'youtube' => 'fab fa-youtube',
                        'linkedin' => 'fab fa-linkedin-in',
                        'tiktok' => 'fab fa-tiktok'
                    ];
                    foreach ($redes as $r):
                        if (!empty($sys[$r])): ?>
                            <a href="<?= htmlspecialchars($sys[$r]) ?>" target="_blank" class="me-2">
                                <i class="<?= $icons[$r] ?> text-body link-hover"></i>
                            </a>
                    <?php endif; endforeach; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Script: Mostrar noticias una por una -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    const posts = <?= json_encode($latestPosts, JSON_UNESCAPED_UNICODE) ?>;
	window.latestPostsData = posts;
window.URLBASE = '<?= URLBASE ?>';

    const ticker = document.getElementById('newsTicker');
    let i = 0;

    function showPost() {
        const post = posts[i];
        if (!post) return;
        const img = post.image ? '<?= URLBASE ?>/' + post.image : '<?= URLBASE ?>/public/images/no-image.jpg';
        const link = `<?= URLBASE ?>/` + post.category_slug + `/` + post.post_slug + `/`;

        ticker.innerHTML = `
            <div class="d-flex align-items-center fadein">
                <img src="${img}" class="img-fluid rounded-circle border border-3 border-primary me-2"
                     style="width:30px; height:30px; object-fit:cover;" alt="">
                <a href="${link}" class="text-white mb-0 link-hover text-nowrap">${post.title}</a>
            </div>
        `;
        i = (i + 1) % posts.length;
    }

    showPost();
    setInterval(showPost, 4500); // cambia cada 4.5 segundos
});
</script>

<style>
/* Corrige alineación y elimina salto de línea */
.topbar-top { white-space: nowrap; }
.top-info { flex-wrap: nowrap; }
.top-link { flex-wrap: nowrap; }

/* Animación suave de entrada */
@keyframes fadein {
  from {opacity: 0; transform: translateY(10px);}
  to {opacity: 1; transform: translateY(0);}
}
.fadein {
  animation: fadein 0.6s ease-in-out;
}
</style>


    

