<?php
require_once __DIR__ . '/../../inc/config.php';

$categorySlug = $_GET['category'] ?? null;
$postSlug     = $_GET['post'] ?? null;

if (!$categorySlug || !$postSlug) {
    http_response_code(404);
    include __DIR__ . '/../404.php';
    exit;
}

// Buscar la noticia
$stmt = db()->prepare("
    SELECT p.*, pc.category_id, c.name AS category_name, c.slug AS category_slug
    FROM blog_posts p
    INNER JOIN blog_post_category pc ON pc.post_id = p.id
    INNER JOIN blog_categories c ON c.id = pc.category_id
    WHERE p.slug = ? AND c.slug = ?
      AND p.status='published' AND p.deleted=0
    LIMIT 1
");
$stmt->execute([$postSlug, $categorySlug]);
$post = $stmt->fetch();

if (!$post) {
    http_response_code(404);
    include __DIR__ . '/../404.php';
    exit;
}

/* ================================
   Registro de vistas
================================ */
$ipAddress = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
$stmtView = db()->prepare("SELECT 1 FROM blog_post_views WHERE post_id=? AND ip_address=? LIMIT 1");
$stmtView->execute([$post['id'], $ipAddress]);
if (!$stmtView->fetch()) {
    db()->prepare("INSERT INTO blog_post_views (post_id, ip_address) VALUES (?, ?)")->execute([$post['id'], $ipAddress]);
}
$totalViews = (int)db()->query("SELECT COUNT(*) FROM blog_post_views WHERE post_id={$post['id']}")->fetchColumn();

/* ================================
   SEO dinámico
================================ */
$page_title       = $post['seo_title'] ?: $post['title'];
$page_description = $post['seo_description'] ?: substr(strip_tags($post['content']), 0, 160);
$page_keywords    = $post['seo_keywords'] ?: $post['title'];
$page_author      = NOMBRE_SITIO;
$page_image       = !empty($post['image']) ? URLBASE . '/' . ltrim($post['image'], '/') : URLBASE . FAVICON;
$currentPath      = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$page_canonical   = rtrim(URLBASE, '/') . '/' . ltrim($currentPath, '/');
?>

<!-- Single Product Start -->
<div class="container-fluid py-5">
    <div class="container py-5">
        <!-- Breadcrumb --
        <ol class="breadcrumb justify-content-start mb-4">
            <li class="breadcrumb-item"><a href="<?= URLBASE ?>">Inicio</a></li>
            <li class="breadcrumb-item">
                <a href="<?= URLBASE ?>/noticias/<?= htmlspecialchars($post['category_slug']) ?>/">
                    <?= htmlspecialchars($post['category_name']) ?>
                </a>
            </li>
            <li class="breadcrumb-item active text-dark"><?= htmlspecialchars($post['title']) ?></li>
        </ol>-->

        <div class="row g-4">
            <!-- CONTENIDO PRINCIPAL -->
            <div class="col-lg-8">
                <div class="mb-4">
                    <h1 class="h2 display-6 fw-bold text-dark"><?= htmlspecialchars($post['title']) ?></h1>
                </div>

                <?php if (!empty($post['image'])): ?>
                <div class="position-relative rounded overflow-hidden mb-3">
                    <img src="<?= URLBASE . '/' . htmlspecialchars($post['image']) ?>" class="img-zoomin img-fluid rounded w-100" alt="<?= htmlspecialchars($post['title']) ?>">
                    <div class="position-absolute text-white px-4 py-2 bg-primary rounded" style="top: 20px; right: 20px;">
                        <?= htmlspecialchars($post['category_name']) ?>
                    </div>
                </div>
                <?php endif; ?>

                <div class="d-flex justify-content-between align-items-center text-secondary small mb-3 border-bottom pb-2 flex-wrap">
    <div class="d-flex align-items-center flex-wrap gap-3">
        <span><i class="fa fa-calendar-alt me-1 text-primary"></i> <?= fecha_espanol(date("F d, Y", strtotime($post['created_at']))) ?></span>
        <?php if (!empty($post['author'])): ?>
            <span><i class="fa fa-user-edit me-1 text-primary"></i> <?= htmlspecialchars($post['author']) ?></span>
        <?php endif; ?>
    </div>
    <span><i class="fa fa-eye me-1 text-primary"></i> <?= number_format($totalViews) ?> vistas</span>
</div>


                <div class="my-4 post-content">
                    <?= $post['content'] ?>
                </div>

                <?php if (!empty($post['excerpt'])): ?>
                    <div class="bg-light p-4 mb-4 rounded border-start border-3 border-primary">
                        <h5 class="mb-0"><?= htmlspecialchars($post['excerpt']) ?></h5>
                    </div>
                <?php endif; ?>

                <!-- Bloque compartir -->
                <div class="d-flex justify-content-between align-items-center border-top pt-3 mb-4">
                    <div>
                        <strong class="text-dark me-2">Compartir:</strong>
                        <a href="https://facebook.com/sharer/sharer.php?u=<?= urlencode($page_canonical) ?>" target="_blank" class="btn btn-light btn-sm rounded-circle me-1"><i class="fab fa-facebook-f"></i></a>
                        <a href="https://twitter.com/intent/tweet?url=<?= urlencode($page_canonical) ?>" target="_blank" class="btn btn-light btn-sm rounded-circle me-1"><i class="fab fa-twitter"></i></a>
                        <a href="https://api.whatsapp.com/send?text=<?= urlencode($page_canonical) ?>" target="_blank" class="btn btn-light btn-sm rounded-circle me-1"><i class="fab fa-whatsapp"></i></a>
                    </div>
                    <small class="text-muted"><?= htmlspecialchars($post['category_name']) ?></small>
                </div>

				
				<!-- Botón Text-to-Speech -->
<div class="text-to-speech-section bg-light rounded p-3 mb-4">
    <div class="d-flex justify-content-between align-items-center">
        <h6 class="mb-0 fw-bold">
            <i class="fas fa-volume-up me-2 text-primary"></i> Escuchar artículo
        </h6>
        <div class="btn-group">
            <button id="playBtn" class="btn btn-primary btn-sm" onclick="toggleAudio()" disabled>
                <i class="fas fa-spinner fa-spin"></i> Cargando audio...
            </button>
            <button id="stopBtn" class="btn btn-danger btn-sm d-none" onclick="stopAudio()">
                <i class="fas fa-stop"></i> Detener
            </button>
        </div>
    </div>
    <div class="progress mt-2 d-none" id="progressBar" style="height: 5px;">
        <div class="progress-bar" role="progressbar" style="width: 0%"></div>
    </div>
</div>

<script>
let audio = new Audio();
let isReady = false;

window.addEventListener('load', function() {
    const articleContent = document.querySelector('.post-content');
    const title = '<?= addslashes($post['title']) ?>';
    const fullText = (title + '. ' + articleContent.innerText).substring(0, 800); // Google limita caracteres

    // Llamamos a nuestro propio servidor PHP
    audio.src = 'tts.php?text=' + encodeURIComponent(fullText);
    
    audio.oncanplaythrough = function() {
        isReady = true;
        const btn = document.getElementById('playBtn');
        btn.disabled = false;
        btn.innerHTML = '<i class="fas fa-play"></i> Reproducir';
    };

    audio.onerror = function() {
        document.getElementById('playBtn').innerHTML = '<i class="fas fa-times"></i> Error de carga';
        console.error("Error cargando el audio desde tts.php");
    };

    audio.ontimeupdate = function() {
        const progress = (audio.currentTime / audio.duration) * 100;
        document.querySelector('.progress-bar').style.width = progress + '%';
    };

    audio.onended = function() {
        stopAudio();
    };
});

function toggleAudio() {
    if (!isReady) return;
    
    const btn = document.getElementById('playBtn');
    const stopBtn = document.getElementById('stopBtn');
    const progress = document.getElementById('progressBar');

    if (audio.paused) {
        audio.play();
        btn.innerHTML = '<i class="fas fa-pause"></i> Pausar';
        stopBtn.classList.remove('d-none');
        progress.classList.remove('d-none');
    } else {
        audio.pause();
        btn.innerHTML = '<i class="fas fa-play"></i> Reanudar';
    }
}

function stopAudio() {
    audio.pause();
    audio.currentTime = 0;
    document.getElementById('playBtn').innerHTML = '<i class="fas fa-play"></i> Reproducir';
    document.getElementById('stopBtn').classList.add('d-none');
    document.getElementById('progressBar').classList.add('d-none');
}
</script>
				
                <!-- TAGS -->
                <?php
                // Si tienes campo "tags" separado por comas
                $tags = [];
                if (!empty($post['tags'])) {
                    $tags = array_filter(array_map('trim', explode(',', $post['tags'])));
                }
                ?>
                <?php if ($tags): ?>
                    <div class="border-top pt-3 mb-4">
                        <h6 class="fw-bold mb-3">Tags:</h6>
                        <?php foreach ($tags as $tag): ?>
                            <a href="<?= URLBASE ?>/buscar.php?tag=<?= urlencode($tag) ?>" class="btn btn-light btn-sm rounded-pill me-2 mb-2"><?= htmlspecialchars($tag) ?></a>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>

                <!-- Comentarios -->
                <div class="bg-light rounded p-4 mb-4">
                    <h4 class="mb-3">Comentarios</h4>
                    <div class="fb-comments" data-href="<?= 'https://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'] ?>" data-width="100%" data-numposts="10"></div>
                </div>

                <!-- Noticias relacionadas -->
                <?php
                $stmtRelated = db()->prepare("
                    SELECT p.id, p.title, p.slug, p.image, p.created_at, c.slug AS category_slug
                    FROM blog_posts p
                    INNER JOIN blog_post_category pc ON pc.post_id = p.id
                    INNER JOIN blog_categories c ON c.id = pc.category_id
                    WHERE p.status='published' AND p.deleted=0
                      AND pc.category_id = ? AND p.id != ?
                    ORDER BY p.created_at DESC
                    LIMIT 2
                ");
                $stmtRelated->execute([$post['category_id'], $post['id']]);
                $relatedPosts = $stmtRelated->fetchAll();
                ?>

                <?php if ($relatedPosts): ?>
                    <div class="bg-light rounded my-4 p-4">
                        <h4 class="mb-4">También te puede interesar</h4>
                        <div class="row g-4">
                            <?php foreach ($relatedPosts as $rel): ?>
                                <div class="col-lg-6">
                                    <div class="d-flex align-items-center p-3 bg-white rounded">
                                        <img src="<?= $rel['image'] ? URLBASE . '/' . htmlspecialchars($rel['image']) : URLBASE . '/template/newsers/img/news-1.jpg' ?>" class="img-fluid rounded" style="width:90px; height:90px; object-fit:cover;" alt="">
                                        <div class="ms-3">
                                            <a href="<?= URLBASE ?>/<?= htmlspecialchars($rel['category_slug']) ?>/<?= htmlspecialchars($rel['slug']) ?>/" class="h6 mb-2 text-dark link-hover">
                                                <?= htmlspecialchars($rel['title']) ?>
                                            </a>
                                            <p class="text-body small mb-0"><i class="fa fa-calendar-alt me-1"></i><?= fecha_espanol(date("F d, Y", strtotime($rel['created_at']))) ?></p>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endif; ?>
            </div>

			  
            <div class="col-lg-4">
                <?php include __DIR__ . '/partials/sidebar.php'; ?>            
        </div>
        </div>
    </div>
</div>
<!-- Single Product End -->
<style>
.text-secondary.small i {
  opacity: 0.7;
}
.text-secondary.small span {
  display: flex;
  align-items: center;
  gap: 4px;
}
</style>





        