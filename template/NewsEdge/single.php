<?php
require_once __DIR__ . '/../../inc/config.php';

// ===============================
// Helpers Locales
// ===============================
if (!function_exists('img_url')) {
    function img_url(?string $path): string {
        if (empty($path)) return URLBASE . '/template/newsedge/img/news/default.jpg';
        if (preg_match('#^https?://#i', $path)) return $path;
        return URLBASE . '/' . ltrim($path, '/');
    }
}

if (!function_exists('truncate_text')) {
    function truncate_text(string $text, int $limit = 100): string {
        $text = strip_tags($text);
        return (mb_strlen($text) > $limit) ? mb_substr($text, 0, $limit) . '...' : $text;
    }
}

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
   Obtener datos del autor
================================ */
/* ================================
   Obtener datos del autor
================================ */
$authorData = null;
if (!empty($post['author'])) {
    $stmtAuthor = db()->prepare("
        SELECT u.id, u.nombre, u.apellido, u.foto_perfil
        FROM usuarios u
        WHERE CONCAT(u.nombre, ' ', u.apellido) = ? 
           OR u.username = ?
        LIMIT 1
    ");
    $stmtAuthor->execute([$post['author'], $post['author']]);
    $authorData = $stmtAuthor->fetch();
}

/* ================================
   Artículos Anterior y Siguiente
================================ */
$prevPost = db()->query("
    SELECT p.title, p.slug, c.slug AS category_slug 
    FROM blog_posts p
    INNER JOIN blog_post_category pc ON pc.post_id = p.id
    INNER JOIN blog_categories c ON c.id = pc.category_id
    WHERE p.id < {$post['id']} AND p.status='published' AND p.deleted=0
    ORDER BY p.id DESC 
    LIMIT 1
")->fetch();

$nextPost = db()->query("
    SELECT p.title, p.slug, c.slug AS category_slug 
    FROM blog_posts p
    INNER JOIN blog_post_category pc ON pc.post_id = p.id
    INNER JOIN blog_categories c ON c.id = pc.category_id
    WHERE p.id > {$post['id']} AND p.status='published' AND p.deleted=0
    ORDER BY p.id ASC 
    LIMIT 1
")->fetch();

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

<section class="bg-body section-space-less30">
    <div class="container">
        <div class="row">
            <div class="col-lg-8 col-md-12 mb-30">
                <div class="news-details-layout1">
                    
                    <!-- IMAGEN PRINCIPAL -->
<div class="position-relative mb-30">
    <?php if (!empty($post['image'])): ?>
    <img src="<?= img_url($post['image']) ?>" 
         alt="<?= htmlspecialchars($post['title']) ?>" 
         class="img-fluid"
         style="width: 100%; height: 450px; object-fit: cover; display: block;">
    <?php endif; ?>
    <div class="topic-box-top-sm">
        <div class="topic-box-sm color-cinnabar mb-20">
            <?= htmlspecialchars($post['category_name']) ?>
        </div>
    </div>
</div>

                    <!-- TÍTULO -->
                    <h2 class="title-semibold-dark size-c30">
                        <?= htmlspecialchars($post['title']) ?>
                    </h2>

                    <!-- METADATA -->
                    <ul class="post-info-dark mb-30">
                        <?php if ($authorData): ?>
                        <li>
                            <a href="#">
                                <span>Por</span> <?= htmlspecialchars($authorData['nombre'] . ' ' . $authorData['apellido']) ?>
                            </a>
                        </li>
                        <?php elseif (!empty($post['author'])): ?>
                        <li>
                            <a href="#">
                                <span>Por</span> <?= htmlspecialchars($post['author']) ?>
                            </a>
                        </li>
                        <?php endif; ?>
                        <li>
                            <a href="#">
                                <i class="fa fa-calendar" aria-hidden="true"></i>
                                <?= fecha_espanol(date('F d, Y', strtotime($post['created_at']))) ?>
                            </a>
                        </li>
                        <li>
                            <a href="#">
                                <i class="fa fa-eye" aria-hidden="true"></i>
                                <?= number_format($totalViews) ?>
                            </a>
                        </li>
                    </ul>

                    <!-- 🎵 REPRODUCTOR DE AUDIO MODERNO -->
                    <?php if (!empty(TEXT_TO_SPEECH) && TEXT_TO_SPEECH == '1'): ?>
                    <div class="audio-player-modern mb-4">
                        <div class="audio-player-inner">
                            <div class="d-flex align-items-center gap-3">
                                <!-- Botón Play/Pause -->
                                <button id="playBtn" class="audio-btn-main" onclick="handlePlay()" title="Reproducir">
                                    <i class="fas fa-play" id="playIcon"></i>
                                </button>
                                
                                <!-- Info y Progreso -->
                                <div class="audio-info">
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <span class="audio-label">
                                            <i class="fas fa-headphones me-2"></i>Escuchar artículo
                                        </span>
                                        <div class="d-flex align-items-center gap-2">
                                            <!-- Control de Velocidad -->
                                            <select id="speedControl" class="form-select form-select-sm" style="width: auto; font-size: 12px; padding: 2px 8px; background: rgba(255,255,255,0.2); color: white; border: 1px solid rgba(255,255,255,0.3);" onchange="changeSpeed(this.value)">
                                                <option value="0.5">0.5x</option>
                                                <option value="0.75">0.75x</option>
                                                <option value="1" selected>1x</option>
                                                <option value="1.25">1.25x</option>
                                                <option value="1.5">1.5x</option>
                                                <option value="1.75">1.75x</option>
                                                <option value="2">2x</option>
                                            </select>
                                            <span class="audio-time" id="timeDisplay">0:00</span>
                                        </div>
                                    </div>
                                    
                                    <!-- Barra de progreso -->
                                    <div class="audio-progress-container">
                                        <div class="audio-progress-bar" id="audioProgress"></div>
                                    </div>
                                </div>
                                
                                <!-- Botón Stop -->
                                <button id="stopBtn" class="audio-btn-stop d-none" onclick="handleStop()" title="Detener">
                                    <i class="fas fa-stop"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>

                    <!-- CONTENIDO DEL ARTÍCULO -->
                    <div class="post-content">
                        <?= $post['content'] ?>
                    </div>

                    <!-- TAGS -->
                    <?php
                    $tags = [];
                    if (!empty($post['tags'])) {
                        $tags = array_filter(array_map('trim', explode(',', $post['tags'])));
                    }
                    ?>
                    <?php if ($tags): ?>
                    <ul class="blog-tags item-inline mb-30">
                        <li>Tags</li>
                        <?php foreach ($tags as $tag): ?>
                        <li>
                            <a href="<?= URLBASE ?>/buscar/<?= urlencode($tag) ?>/">
                                #<?= htmlspecialchars($tag) ?>
                            </a>
                        </li>
                        <?php endforeach; ?>
                    </ul>
                    <?php endif; ?>

                    <!-- COMPARTIR -->
                    <div class="post-share-area mb-40 item-shadow-1">
                        <p>¡Puedes compartir este artículo!</p>
                        <ul class="social-default item-inline">
                            <li>
                                <a href="https://facebook.com/sharer/sharer.php?u=<?= urlencode($page_canonical) ?>" target="_blank" class="facebook">
                                    <i class="fa fa-facebook" aria-hidden="true"></i>
                                </a>
                            </li>
                            <li>
                                <a href="https://twitter.com/intent/tweet?url=<?= urlencode($page_canonical) ?>" target="_blank" class="twitter">
                                    <i class="fa fa-twitter" aria-hidden="true"></i>
                                </a>
                            </li>
                            <li>
                                <a href="https://api.whatsapp.com/send?text=<?= urlencode($page_canonical) ?>" target="_blank" style="background-color: #25d366;">
                                    <i class="fa fa-whatsapp" aria-hidden="true"></i>
                                </a>
                            </li>
                            <li>
                                <a href="mailto:?subject=<?= urlencode($post['title']) ?>&body=<?= urlencode($page_canonical) ?>" class="rss">
                                    <i class="fa fa-envelope" aria-hidden="true"></i>
                                </a>
                            </li>
                            <li>
                                <a href="https://www.linkedin.com/sharing/share-offsite/?url=<?= urlencode($page_canonical) ?>" target="_blank" class="linkedin">
                                    <i class="fa fa-linkedin" aria-hidden="true"></i>
                                </a>
                            </li>
                        </ul>
                    </div>

                    <!-- NAVEGACIÓN ANTERIOR / SIGUIENTE -->
                    <div class="row no-gutters divider blog-post-slider">
                        <div class="col-lg-6 col-md-6 col-sm-6 col-6">
                            <?php if ($prevPost): ?>
                            <a href="<?= URLBASE ?>/<?= htmlspecialchars($prevPost['category_slug']) ?>/<?= htmlspecialchars($prevPost['slug']) ?>/" class="prev-article">
                                <i class="fa fa-angle-left" aria-hidden="true"></i>Artículo anterior
                            </a>
                            <h3 class="title-medium-dark pr-50">
                                <?= truncate_text($prevPost['title'], 60) ?>
                            </h3>
                            <?php endif; ?>
                        </div>
                        <div class="col-lg-6 col-md-6 col-sm-6 col-6 text-right">
                            <?php if ($nextPost): ?>
                            <a href="<?= URLBASE ?>/<?= htmlspecialchars($nextPost['category_slug']) ?>/<?= htmlspecialchars($nextPost['slug']) ?>/" class="next-article">
                                Siguiente artículo
                                <i class="fa fa-angle-right" aria-hidden="true"></i>
                            </a>
                            <h3 class="title-medium-dark pl-50">
                                <?= truncate_text($nextPost['title'], 60) ?>
                            </h3>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- INFORMACIÓN DEL AUTOR -->
                    <?php if ($authorData): ?>
                    <div class="author-info p-35-r mb-50 border-all">
                        <div class="media media-none-xs">
                            <?php 
                            $fotoAutor = !empty($authorData['foto_perfil']) 
                                ? img_url($authorData['foto_perfil']) 
                                : 'data:image/svg+xml;base64,' . base64_encode('
                                <svg width="200" height="200" xmlns="http://www.w3.org/2000/svg">
                                    <rect width="200" height="200" fill="#667eea"/>
                                    <text x="50%" y="50%" font-size="80" fill="white" text-anchor="middle" dy=".35em" font-family="Arial">
                                        ' . strtoupper(substr($authorData['nombre'], 0, 1) . substr($authorData['apellido'], 0, 1)) . '
                                    </text>
                                </svg>');
                            ?>
                            <img src="<?= $fotoAutor ?>" 
                                 alt="<?= htmlspecialchars($authorData['nombre'] . ' ' . $authorData['apellido']) ?>" 
                                 class="img-fluid rounded-circle"
                                 style="width: 120px; height: 120px; object-fit: cover;">
                            <div class="media-body pt-10 media-margin30">
                                <h3 class="size-lg mb-5">
                                    <?= htmlspecialchars($authorData['nombre'] . ' ' . $authorData['apellido']) ?>
                                </h3>
                                <div class="post-by mb-5">Autor</div>
                                <p class="mb-15">
    <?php if (!empty($authorData['bio'])): // Por si la agregas en el futuro ?>
        <?= truncate_text($authorData['bio'], 150) ?>
    <?php else: ?>
        Colaborador y redactor de <?= htmlspecialchars($post['category_name']) ?>.
    <?php endif; ?>
</p>
                                <ul class="author-social-style2 item-inline">
                                    <li>
                                        <a href="#" title="facebook">
                                            <i class="fa fa-facebook" aria-hidden="true"></i>
                                        </a>
                                    </li>
                                    <li>
                                        <a href="#" title="twitter">
                                            <i class="fa fa-twitter" aria-hidden="true"></i>
                                        </a>
                                    </li>
                                    <li>
                                        <a href="#" title="instagram">
                                            <i class="fa fa-instagram" aria-hidden="true"></i>
                                        </a>
                                    </li>
                                    <li>
                                        <a href="#" title="linkedin">
                                            <i class="fa fa-linkedin" aria-hidden="true"></i>
                                        </a>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>

                    <!-- COMENTARIOS -->
                    <div class="comments-area">
                        <h2 class="title-semibold-dark size-xl border-bottom mb-40 pb-20">Comentarios</h2>
                        <div class="fb-comments" data-href="<?= 'https://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'] ?>" data-width="100%" data-numposts="10"></div>
                    </div>

                </div>
            </div>

            <!-- SIDEBAR -->
            <div class="ne-sidebar sidebar-break-md col-lg-4 col-md-12">
                <?php include __DIR__ . '/partials/sidebar.php'; ?>                            
            </div>
        </div>
    </div>
</section>

<?php if (!empty(TEXT_TO_SPEECH) && TEXT_TO_SPEECH == '1'): ?>
<style>
    
	/* Estilos del reproductor moderno */
    .audio-player-modern {
        background: linear-gradient(135deg, var(--primary) 0%, var(--color-hover-link) 100%);
        border-radius: 16px;
        padding: 20px;
        box-shadow: 0 8px 32px rgba(0, 123, 255, 0.25);
        transition: all 0.3s ease;
    }
    
    .audio-player-modern:hover {
        box-shadow: 0 12px 48px rgba(0, 123, 255, 0.35);
        transform: translateY(-2px);
    }
    
    .audio-player-inner {
        position: relative;
    }
    
    .audio-btn-main {
        width: 56px;
        height: 56px;
        border-radius: 50%;
        background: white;
        border: none;
        color: var(--primary);
        font-size: 20px;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        transition: all 0.3s ease;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        flex-shrink: 0;
    }
    
    .audio-btn-main:hover {
        transform: scale(1.1);
        box-shadow: 0 6px 20px rgba(0, 0, 0, 0.2);
        color: var(--color-hover-link);
    }
    
    .audio-btn-main:active {
        transform: scale(0.95);
    }
    
    .audio-btn-stop {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        background: rgba(255, 255, 255, 0.2);
        border: 2px solid white;
        color: white;
        font-size: 14px;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        transition: all 0.3s ease;
        flex-shrink: 0;
    }
    
    .audio-btn-stop:hover {
        background: rgba(255, 255, 255, 0.3);
        transform: scale(1.1);
    }
    
    .audio-info {
        flex: 1;
        min-width: 0;
    }
    
    .audio-label {
        color: white;
        font-weight: 600;
        font-size: 14px;
        display: flex;
        align-items: center;
    }
    
    .audio-time {
        color: rgba(255, 255, 255, 0.9);
        font-size: 13px;
        font-weight: 500;
        font-family: 'Courier New', monospace;
    }
    
    .audio-progress-container {
        height: 6px;
        background: rgba(255, 255, 255, 0.2);
        border-radius: 10px;
        overflow: hidden;
        position: relative;
    }
    
    .audio-progress-bar {
        height: 100%;
        background: white;
        border-radius: 10px;
        width: 0%;
        transition: width 0.3s ease;
        box-shadow: 0 0 10px rgba(255, 255, 255, 0.5);
    }
    
    /* Animación del icono cuando está reproduciendo */
    @keyframes pulse {
        0%, 100% {
            transform: scale(1);
        }
        50% {
            transform: scale(1.05);
        }
    }
    
    .audio-btn-main.playing {
        animation: pulse 2s infinite;
    }
    
    /* Responsive */
    @media (max-width: 576px) {
        .audio-player-modern {
            padding: 16px;
        }
        
        .audio-btn-main {
            width: 48px;
            height: 48px;
            font-size: 18px;
        }
        
        .audio-label {
            font-size: 13px;
        }
        
        .audio-time {
            font-size: 12px;
        }
    }
    
    /* Otros estilos existentes */
    .text-secondary.small i {
        opacity: 0.7;
    }
    
    .text-secondary.small span {
        display: flex;
        align-items: center;
        gap: 4px;
    }
#speedControl {
    cursor: pointer;
    transition: all 0.2s;
}

#speedControl:hover {
    background: rgba(255,255,255,0.3) !important;
}

#speedControl option {
    background: #333;;
    color: white;
}

/* Responsive para móvil */
@media (max-width: 576px) {
    #speedControl {
        font-size: 11px;
        padding: 1px 6px;
    }
}
</style>



<script>
const synth = window.speechSynthesis;
let utterance = null;
let isPaused = false;
let currentPosition = 0;
let fullText = '';
let startTime = 0;
let totalDuration = 0;
let currentRate = 1.0; // 👈 Variable para la velocidad

// Preparar el texto
window.addEventListener('load', function() {
    const articleContent = document.querySelector('.post-content');
    const title = "<?= addslashes($post['title']) ?>";
    const excerpt = "<?= addslashes(strip_tags($post['excerpt'] ?? '')) ?>";
    
    fullText = (title + '. ' + excerpt + '. ' + articleContent.innerText)
        .replace(/\s+/g, ' ')
        .trim();
    
    const wordCount = fullText.split(' ').length;
    totalDuration = Math.ceil((wordCount / 150) * 60);
});

// 👇 NUEVA FUNCIÓN: Cambiar velocidad
function changeSpeed(speed) {
    currentRate = parseFloat(speed);
    
    // Si está reproduciendo, reiniciar con nueva velocidad
    if (synth.speaking && !isPaused) {
        const wasPlaying = true;
        synth.cancel();
        if (wasPlaying) {
            setTimeout(() => speak(currentPosition), 100);
        }
    }
}

function handlePlay() {
    if (!('speechSynthesis' in window)) {
        alert('Tu navegador no soporta Text-to-Speech. Intenta con Chrome, Firefox o Edge.');
        return;
    }

    if (synth.speaking && !isPaused) return;

    if (isPaused) {
        isPaused = false;
        speak(currentPosition);
    } else {
        currentPosition = 0;
        startTime = Date.now();
        speak(0);
    }
}

function speak(startOffset) {
    synth.cancel();

    const textToSpeak = fullText.substring(startOffset);
    utterance = new SpeechSynthesisUtterance(textToSpeak);
    utterance.lang = 'es-ES';
    utterance.rate = currentRate; // 👈 Usar velocidad actual
    utterance.pitch = 1.0;
    utterance.volume = 1.0;

    const voices = synth.getVoices();
    const spanishVoice = voices.find(voice => voice.lang.startsWith('es'));
    if (spanishVoice) {
        utterance.voice = spanishVoice;
    }

    utterance.onstart = () => {
        updateUI('playing');
        updateTime();
    };

    utterance.onboundary = (event) => {
        currentPosition = startOffset + event.charIndex;
        const progress = (currentPosition / fullText.length) * 100;
        document.getElementById('audioProgress').style.width = progress + '%';
    };

    utterance.onend = () => {
        if (!isPaused) {
            handleStop();
        }
    };

    utterance.onerror = (event) => {
        if (event.error !== 'canceled' && event.error !== 'interrupted') {
            console.error('Error en Text-to-Speech:', event);
        }
    };

    synth.speak(utterance);
}

function handlePause() {
    if (synth.speaking) {
        isPaused = true;
        synth.cancel();
        updateUI('paused');
    }
}

function handleStop() {
    isPaused = false;
    currentPosition = 0;
    synth.cancel();
    updateUI('stopped');
}

function updateUI(state) {
    const playBtn = document.getElementById('playBtn');
    const stopBtn = document.getElementById('stopBtn');
    const playIcon = document.getElementById('playIcon');

    if (state === 'playing') {
        playIcon.className = 'fas fa-pause';
        playBtn.onclick = handlePause;
        playBtn.classList.add('playing');
        stopBtn.classList.remove('d-none');
    } else if (state === 'paused') {
        playIcon.className = 'fas fa-play';
        playBtn.onclick = handlePlay;
        playBtn.classList.remove('playing');
    } else {
        playIcon.className = 'fas fa-play';
        playBtn.onclick = handlePlay;
        playBtn.classList.remove('playing');
        stopBtn.classList.add('d-none');
        document.getElementById('audioProgress').style.width = '0%';
        document.getElementById('timeDisplay').textContent = '0:00';
    }
}

function updateTime() {
    if (synth.speaking && !isPaused) {
        const elapsed = Math.floor((Date.now() - startTime) / 1000);
        const minutes = Math.floor(elapsed / 60);
        const seconds = elapsed % 60;
        document.getElementById('timeDisplay').textContent = 
            `${minutes}:${seconds.toString().padStart(2, '0')}`;
        setTimeout(updateTime, 1000);
    }
}

if (synth.onvoiceschanged !== undefined) {
    synth.onvoiceschanged = () => {};
}

window.addEventListener('beforeunload', () => {
    synth.cancel();
});
</script>
<?php endif; ?>
            
