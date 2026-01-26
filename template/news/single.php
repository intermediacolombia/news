<?php
require_once __DIR__ . '/../../inc/config.php';

$categorySlug = $_GET['category'] ?? null;
$postSlug     = $_GET['post'] ?? null;

if (!$categorySlug || !$postSlug) {
    http_response_code(404);
    include __DIR__ . '/../404.php';
    exit;
}

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
   REGISTRO DE VISTAS POR IP
   ================================ */
$ipAddress = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';

// Verificar si ya existe un registro de esta IP para este post
$stmtView = db()->prepare("
    SELECT 1 
    FROM blog_post_views 
    WHERE post_id = ? AND ip_address = ?
    LIMIT 1
");
$stmtView->execute([$post['id'], $ipAddress]);

if (!$stmtView->fetch()) {
    // Registrar la vista en la tabla auxiliar
    $stmtInsert = db()->prepare("
        INSERT INTO blog_post_views (post_id, ip_address) 
        VALUES (?, ?)
    ");
    $stmtInsert->execute([$post['id'], $ipAddress]);
}

/* ================================
   OBTENER TOTAL DE LECTURAS
   ================================ */
$stmtCount = db()->prepare("
    SELECT COUNT(*) 
    FROM blog_post_views 
    WHERE post_id = ?
");
$stmtCount->execute([$post['id']]);
$totalViews = (int)$stmtCount->fetchColumn();

/* ================================
   VARIABLES SEO
   ================================ */

// Variables SEO dinámicas
$page_title = $post['seo_title'] ?: $post['title'];
$page_description = $post['seo_description'] ?: substr(strip_tags($post['content']),0,160);
$page_keywords    = $post['seo_keywords'] ?: $post['title'];
$page_author      = NOMBRE_SITIO;

// Imagen SEO → destacada del post o logo por defecto
$page_image = rtrim(URLBASE, '/') . FAVICON;
if (!empty($post['image'])) {
    $path = $post['image'];
    $path = ($path[0] === '/') ? $path : '/' . $path;
    $page_image = URLBASE . $path;
}

// Canonical automático (desde URL actual)
$currentPath    = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$page_canonical = rtrim(URLBASE, '/') . '/' . ltrim($currentPath, '/');

?>

<style>
.contador-vistas {
    margin-left: auto; /* lo manda a la derecha en un contenedor flex */
}
</style>

<!-- Breadcrumb Start -->
<div class="container-fluid">
    <div class="container-bk">
        <nav class="breadcrumb bg-transparent m-0 p-0">
            <a class="breadcrumb-item" href="<?= URLBASE ?>">Home</a>
            <a class="breadcrumb-item" href="<?= URLBASE ?>/noticias/<?= htmlspecialchars($post['category_slug']) ?>/">
                <?= htmlspecialchars($post['category_name']) ?>
            </a>
            <span class="breadcrumb-item active"><?= htmlspecialchars($post['title']) ?></span>
        </nav>
    </div>
</div>
<!-- Breadcrumb End -->

<!-- News With Sidebar Start -->
<div class="container-fluid py-3">
    <div class="container-bk">
        <div class="row">
            <div class="col-lg-8">
                <div class="position-relative mb-3">
                    <?php if (!empty($post['image'])): ?>
                        <img class="img-fluid w-100"
                             src="<?= URLBASE . '/' . htmlspecialchars($post['image']) ?>"
                             style="object-fit: cover;"
                             alt="<?= htmlspecialchars($post['title']) ?>">
                    <?php endif; ?>
                    <div class="overlay position-relative bg-light">
                        <!-- Categoría, fecha y vistas -->
                        <div class="mb-3 d-flex align-items-center">
                            <div>
                                <a href="<?= URLBASE ?>/noticias/<?= htmlspecialchars($post['category_slug']) ?>/">
                                    <?= htmlspecialchars($post['category_name']) ?>
                                </a>
                                <span class="px-1">/</span>
                                <span><?= fecha_espanol(date("F d, Y", strtotime($post['created_at']))) ?></span>
                            </div>&nbsp;&nbsp;
                            <div class="text-muted contador-vistas">
                                <i class="fas fa-eye"></i> <?= $totalViews ?>
                            </div>
                        </div>

                        <!-- Título -->
                        <h3 class="mb-3"><?= htmlspecialchars($post['title']) ?></h3>
                        
                        <!-- 🎵 REPRODUCTOR DE AUDIO MODERNO -->
                        <?php if (!empty(TEXT_TO_SPEECH) && TEXT_TO_SPEECH == '1'): ?>
                        <div class="audio-player-modern mb-4">
                            <div class="audio-player-inner">
                                <div class="d-flex align-items-center gap-3 flex-wrap">
                                    <!-- Botón Play/Pause -->
                                    <button id="playBtn" class="audio-btn-main" onclick="handlePlay()" title="Reproducir">
                                        <i class="fas fa-play" id="playIcon"></i>
                                    </button>
                                    
                                    <!-- Info y Progreso -->
                                    <div class="audio-info flex-grow-1">
                                        <div class="d-flex justify-content-between align-items-center mb-2 flex-wrap">
                                            <span class="audio-label">
                                                <i class="fas fa-headphones me-2"></i>Escuchar artículo
                                            </span>
                                            <div class="d-flex align-items-center gap-2">
                                                <!-- Control de Velocidad -->
                                                <select id="speedControl" class="form-select form-select-sm audio-speed-select" onchange="changeSpeed(this.value)">
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
                        <div class="post-content">
                            <?= $post['content'] ?>
                        </div>
                    </div>
                </div>
                
                <div class="d-flex align-items-center justify-content-between bg-light py-2 px-4 mb-3 title-widgets">
                    <h3 class="m-0">Comentarios</h3>
                </div>
                <div class="bg-light">
                    <div class="fb-comments" data-href="<?php echo 'https://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];?>" data-width="100%" data-numposts="10" data-order-by="reverse_time"></div>
                </div>
                
                <!-- Noticias relacionadas -->
                <?php
                $stmtRelated = db()->prepare("
                    SELECT p.id, p.title, p.slug, p.image, p.created_at, c.slug AS category_slug
                    FROM blog_posts p
                    INNER JOIN blog_post_category pc ON pc.post_id = p.id
                    INNER JOIN blog_categories c ON c.id = pc.category_id
                    WHERE p.status='published' AND p.deleted=0
                      AND pc.category_id = ? 
                      AND p.id != ?
                    ORDER BY p.created_at DESC
                    LIMIT 3
                ");
                $stmtRelated->execute([$post['category_id'], $post['id']]);
                $relatedPosts = $stmtRelated->fetchAll();
                ?>

                <?php if ($relatedPosts): ?>
                    <div class="mt-5">
                        <div class="d-flex align-items-center justify-content-between bg-light py-2 px-4 mb-3 title-widgets">
                            <h3 class="m-0">Te Puede Interesar</h3>
                        </div>
                        <div class="row">
                            <?php foreach ($relatedPosts as $rel): ?>
                                <div class="col-md-4 mb-3">
                                    <div class="position-relative">
                                        <img class="img-fluid w-100"
                                             src="<?= $rel['image'] ? URLBASE . '/' . htmlspecialchars($rel['image']) : URLBASE . '/template/news/img/news-500x280-1.jpg' ?>"
                                             style="object-fit: cover; height: 180px;"
                                             alt="<?= htmlspecialchars($rel['title']) ?>">
                                        <div class="overlay position-relative bg-light p-2">
                                            <div style="font-size: 12px;">
                                                <span><?= fecha_espanol(date("F d, Y", strtotime($rel['created_at']))) ?></span>
                                            </div>
                                            <a class="h6 d-block mt-1"
                                               href="<?= URLBASE ?>/<?= htmlspecialchars($rel['category_slug']) ?>/<?= htmlspecialchars($rel['slug']) ?>/">
                                               <?= htmlspecialchars($rel['title']) ?>
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Sidebar -->
            <?php include __DIR__ . '/partials/sidebar.php'; ?>
        </div>
    </div>
</div>
<!-- News With Sidebar End -->

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

/* Selector de velocidad */
.audio-speed-select {
    width: auto !important;
    font-size: 12px !important;
    padding: 2px 8px !important;
    background: rgba(255,255,255,0.2) !important;
    color: white !important;
    border: 1px solid rgba(255,255,255,0.3) !important;
    cursor: pointer;
    transition: all 0.2s;
}

.audio-speed-select:hover {
    background: rgba(255,255,255,0.3) !important;
}

.audio-speed-select option {
    background: #333;
    color: white;
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
@media (max-width: 768px) {
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
    
    .audio-speed-select {
        font-size: 11px !important;
        padding: 1px 6px !important;
    }
    
    .audio-info {
        width: 100%;
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
let currentRate = 1.0;

// Preparar el texto al cargar
window.addEventListener('load', function() {
    const articleContent = document.querySelector('.post-content');
    if (!articleContent) {
        console.error('No se encontró .post-content');
        return;
    }
    
    const title = "<?= addslashes($post['title']) ?>";
    const excerpt = "<?= addslashes(strip_tags($post['excerpt'] ?? '')) ?>";
    
    fullText = (title + '. ' + (excerpt ? excerpt + '. ' : '') + articleContent.innerText)
        .replace(/\s+/g, ' ')
        .trim();
    
    const wordCount = fullText.split(' ').length;
    totalDuration = Math.ceil((wordCount / 150) * 60);
    
    console.log('Text-to-Speech inicializado. Palabras:', wordCount);
});

function changeSpeed(speed) {
    currentRate = parseFloat(speed);
    
    if (synth.speaking && !isPaused) {
        synth.cancel();
        setTimeout(() => speak(currentPosition), 100);
    }
}

function handlePlay() {
    if (!('speechSynthesis' in window)) {
        alert('Tu navegador no soporta Text-to-Speech. Intenta con Chrome, Firefox o Edge.');
        return;
    }

    if (!fullText) {
        alert('El contenido aún se está cargando, por favor espera un momento.');
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
    utterance.rate = currentRate;
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
        const progressBar = document.getElementById('audioProgress');
        if (progressBar) {
            progressBar.style.width = progress + '%';
        }
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
    
    if (!playBtn || !stopBtn || !playIcon) return;

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
        
        const progressBar = document.getElementById('audioProgress');
        const timeDisplay = document.getElementById('timeDisplay');
        if (progressBar) progressBar.style.width = '0%';
        if (timeDisplay) timeDisplay.textContent = '0:00';
    }
}

function updateTime() {
    if (synth.speaking && !isPaused) {
        const elapsed = Math.floor((Date.now() - startTime) / 1000);
        const minutes = Math.floor(elapsed / 60);
        const seconds = elapsed % 60;
        const timeDisplay = document.getElementById('timeDisplay');
        if (timeDisplay) {
            timeDisplay.textContent = `${minutes}:${seconds.toString().padStart(2, '0')}`;
        }
        setTimeout(updateTime, 1000);
    }
}

if (synth.onvoiceschanged !== undefined) {
    synth.onvoiceschanged = () => {
        console.log('Voces cargadas:', synth.getVoices().length);
    };
}

window.addEventListener('beforeunload', () => {
    synth.cancel();
});
</script>
<?php endif; ?>



