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
        <div class="row g-4">
            <!-- CONTENIDO PRINCIPAL -->
            <div class="col-lg-8">
                <!-- Título -->
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

                <!-- Metadata -->
                <div class="d-flex justify-content-between align-items-center text-secondary small mb-3 border-bottom pb-2 flex-wrap">
                    <div class="d-flex align-items-center flex-wrap gap-3">
                        <span><i class="fa fa-calendar-alt me-1 text-primary"></i> <?= fecha_espanol(date("F d, Y", strtotime($post['created_at']))) ?></span>
                        <?php if (!empty($post['author'])): ?>
                            <span><i class="fa fa-user-edit me-1 text-primary"></i> <?= htmlspecialchars($post['author']) ?></span>
                        <?php endif; ?>
                    </div>
                    <span><i class="fa fa-eye me-1 text-primary"></i> <?= number_format($totalViews) ?> vistas</span>
                </div>

                <!-- 🎵 REPRODUCTOR DE AUDIO MODERNO -->
                <?php if (!empty(TEXT_TO_SPEECH) && TEXT_TO_SPEECH == '1'): ?>
<!-- 🎵 REPRODUCTOR DE AUDIO MODERNO -->
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
                <!-- Contenido del artículo -->
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

                <!-- TAGS -->
                <?php
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
    background: #667eea;
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


<?php if (!empty(TEXT_TO_SPEECH) && TEXT_TO_SPEECH == '1'): ?>
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

        