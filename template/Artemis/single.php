<?php
require_once __DIR__ . '/../../inc/config.php';

if (!function_exists('img_url')) {
    function img_url(?string $path): string {
        if (empty($path)) return URLBASE . '/template/Artemis/img/placeholder.jpg';
        if (preg_match('#^https?://#i', $path)) return $path;
        return URLBASE . '/' . ltrim($path, '/');
    }
}

function get_post_image_alt($post) {
    $alt = $post['title'] ?? '';
    
    if (!empty($post['image'])) {
        $stmt = db()->prepare("SELECT alt_text FROM multimedia WHERE file_path = ? AND deleted = 0 LIMIT 1");
        $stmt->execute([$post['image']]);
        $media = $stmt->fetch();
        if (!empty($media['alt_text'])) {
            $alt = $media['alt_text'];
        }
    }
    
    return $alt;
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
    header('Location: /error_404');
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
    header('Location: /error_404');
    exit;
}

$ipAddress = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
$stmtView = db()->prepare("SELECT 1 FROM blog_post_views WHERE post_id=? AND ip_address=? LIMIT 1");
$stmtView->execute([$post['id'], $ipAddress]);
if (!$stmtView->fetch()) {
    db()->prepare("INSERT INTO blog_post_views (post_id, ip_address) VALUES (?, ?)")->execute([$post['id'], $ipAddress]);
}
$totalViews = (int)db()->query("SELECT COUNT(*) FROM blog_post_views WHERE post_id={$post['id']}")->fetchColumn();

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

$page_title       = $post['seo_title'] ?: $post['title'];
$page_description = $post['seo_description'] ?: substr(strip_tags($post['content']), 0, 160);
$page_keywords    = $post['seo_keywords'] ?: $post['title'];
$page_author      = NOMBRE_SITIO;
$page_image       = !empty($post['image']) ? URLBASE . '/' . ltrim($post['image'], '/') : URLBASE . FAVICON;
$currentPath      = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$page_canonical   = rtrim(URLBASE, '/') . '/' . ltrim($currentPath, '/');
?>

<section class="py-5" style="background: var(--dark);">
    <div class="container">
        <div class="row">
            <div class="col-lg-8">
                <div style="background: var(--dark-secondary); border-radius: 20px; overflow: hidden;">
                    <img src="<?= img_url($post['image']) ?>" 
                         alt="<?= htmlspecialchars(get_post_image_alt($post)) ?>" 
                         style="width: 100%; height: 400px; object-fit: cover;">
                    
                    <div class="p-4">
                        <span class="category-badge mb-3 d-inline-block"><?= htmlspecialchars($post['category_name']) ?></span>
                        
                        <h1 class="mb-4" style="color: var(--text-color); font-family: 'Playfair Display', serif; font-size: 2rem; line-height: 1.3;">
                            <?= htmlspecialchars($post['title']) ?>
                        </h1>
                        
                        <div class="d-flex flex-wrap gap-3 mb-4" style="color: var(--text-muted); font-size: 14px;">
                            <?php if ($authorData): ?>
                            <span>
                                <i class="fas fa-user mr-2"></i>
                                Por <a href="<?= URLBASE ?>/autor/<?= urlencode($authorData['nombre'] . ' ' . $authorData['apellido']) ?>/" style="color: var(--primary); text-decoration: none;"><strong><?= htmlspecialchars($authorData['nombre'] . ' ' . $authorData['apellido']) ?></strong></a>
                            </span>
                            <?php elseif (!empty($post['author'])): ?>
                            <span>
                                <i class="fas fa-user mr-2"></i>
                                Por <strong style="color: var(--text-color);"><?= htmlspecialchars($post['author']) ?></strong>
                            </span>
                            <?php endif; ?>
                            <span>
                                <i class="far fa-calendar mr-2"></i>
                                <?= fecha_espanol(date('F d, Y', strtotime($post['created_at']))) ?>
                            </span>
                            <span>
                                <i class="fas fa-eye mr-2"></i>
                                <?= number_format($totalViews) ?> vistas
                            </span>
                        </div>

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
                                                <i class="fas fa-headphones me-2"></i><?= t_theme('theme_escuchar_articulo') ?>
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

                        <div class="post-content" style="color: var(--text-color); font-size: 16px; line-height: 1.8;">
                            <?= render_post_content($post['content']) ?>
                        </div>

                        <?php
                        $tags = [];
                        if (!empty($post['tags'])) {
                            $tags = array_filter(array_map('trim', explode(',', $post['tags'])));
                        }
                        ?>
                        <?php if ($tags): ?>
                        <div class="mt-4 pt-4" style="border-top: 1px solid rgba(255,255,255,0.1);">
                            <span style="color: var(--text-muted); margin-right: 10px;">Tags:</span>
                            <?php foreach ($tags as $tag): ?>
                            <a href="<?= URLBASE ?>/buscar/<?= urlencode($tag) ?>/" 
                               style="background: rgba(255,255,255,0.1); color: var(--text-muted); padding: 5px 12px; border-radius: 15px; font-size: 13px; text-decoration: none; margin-right: 8px; display: inline-block;">
                                #<?= htmlspecialchars($tag) ?>
                            </a>
                            <?php endforeach; ?>
                        </div>
                        <?php endif; ?>

                        <div class="mt-4 pt-4" style="border-top: 1px solid rgba(255,255,255,0.1);">
                            <span style="color: var(--text-muted); margin-right: 15px;">Compartir:</span>
                            <a href="https://facebook.com/sharer/sharer.php?u=<?= urlencode($page_canonical) ?>" target="_blank" style="color: #1877f2; font-size: 20px; margin-right: 15px;"><i class="fab fa-facebook-f"></i></a>
                            <a href="https://twitter.com/intent/tweet?url=<?= urlencode($page_canonical) ?>" target="_blank" style="color: #1da1f2; font-size: 20px; margin-right: 15px;"><i class="fab fa-twitter"></i></a>
                            <a href="https://api.whatsapp.com/send?text=<?= urlencode($page_canonical) ?>" target="_blank" style="color: #25d366; font-size: 20px; margin-right: 15px;"><i class="fab fa-whatsapp"></i></a>
                            <a href="mailto:?subject=<?= urlencode($post['title']) ?>&body=<?= urlencode($page_canonical) ?>" style="color: var(--text-muted); font-size: 20px;"><i class="fas fa-envelope"></i></a>
                        </div>

                        <div class="row mt-4 pt-4" style="border-top: 1px solid rgba(255,255,255,0.1);">
                            <div class="col-6">
                                <?php if ($prevPost): ?>
                                <a href="<?= URLBASE ?>/<?= htmlspecialchars($prevPost['category_slug']) ?>/<?= htmlspecialchars($prevPost['slug']) ?>/" style="color: var(--primary); text-decoration: none;">
                                    <i class="fas fa-arrow-left mr-2"></i><?= t_theme('theme_anterior') ?>
                                </a>
                                <p style="color: var(--text-color); margin-top: 5px; font-size: 14px;"><?= truncate_text($prevPost['title'], 50) ?></p>
                                <?php endif; ?>
                            </div>
                            <div class="col-6 text-right">
                                <?php if ($nextPost): ?>
                                <a href="<?= URLBASE ?>/<?= htmlspecialchars($nextPost['category_slug']) ?>/<?= htmlspecialchars($nextPost['slug']) ?>/" style="color: var(--primary); text-decoration: none;">
                                    <?= t_theme('theme_siguiente') ?><i class="fas fa-arrow-right ml-2"></i>
                                </a>
                                <p style="color: var(--text-color); margin-top: 5px; font-size: 14px;"><?= truncate_text($nextPost['title'], 50) ?></p>
                                <?php endif; ?>
                            </div>
                        </div>

                        <?php if ($authorData): ?>
                        <div class="mt-4 p-4" style="background: rgba(255,255,255,0.05); border-radius: 16px;">
                            <div class="d-flex align-items-center">
                                <?php
                                $fotoAutor = !empty($authorData['foto_perfil'])
                                    ? img_url($authorData['foto_perfil'])
                                    : 'data:image/svg+xml;base64,' . base64_encode('
                                    <svg width="100" height="100" xmlns="http://www.w3.org/2000/svg">
                                        <rect width="100" height="100" fill="#e63946"/>
                                        <text x="50%" y="50%" font-size="40" fill="white" text-anchor="middle" dy=".35em" font-family="Arial">
                                            ' . strtoupper(substr($authorData['nombre'], 0, 1) . substr($authorData['apellido'], 0, 1)) . '
                                        </text>
                                    </svg>');
                                ?>
                                <a href="<?= URLBASE ?>/autor/<?= urlencode($authorData['nombre'] . ' ' . $authorData['apellido']) ?>/" style="text-decoration: none; color: inherit;">
                                    <img src="<?= htmlspecialchars($fotoAutor, ENT_QUOTES, 'UTF-8') ?>"
                                         alt="<?= htmlspecialchars($authorData['nombre'] . ' ' . $authorData['apellido']) ?>"
                                         class="mr-3"
                                         style="width: 70px; height: 70px; border-radius: 50%; object-fit: cover;">
                                </a>
                                <div>
                                    <h5 style="color: var(--text-color); margin: 0;">
                                        <a href="<?= URLBASE ?>/autor/<?= urlencode($authorData['nombre'] . ' ' . $authorData['apellido']) ?>/" style="color: var(--text-color); text-decoration: none;"><?= htmlspecialchars($authorData['nombre'] . ' ' . $authorData['apellido']) ?></a>
                                    </h5>
                                    <span style="color: var(--primary); font-size: 14px;"><?= t_theme('theme_autor') ?></span>
                                </div>
                            </div>
                        </div>
                        <?php endif; ?>

                        <!-- SECCIÓN DE COMENTARIOS -->
                        <div class="mt-4 pt-4" style="border-top: 1px solid rgba(255,255,255,0.1);">
                            <h4 style="color: var(--text-color); margin-bottom: 20px;">
                                <i class="fas fa-comments mr-2"></i><?= t_theme('theme_comentarios') ?>
                            </h4>
                            
                            <!-- Formulario de comentarios -->
                            <div class="mb-4 p-4" style="background: rgba(255,255,255,0.05); border-radius: 16px;">
                                <h5 style="color: var(--text-color); margin-bottom: 15px;"><?= t_theme('theme_dejar_comentario') ?></h5>
                                <form id="commentForm">
                                    <input type="hidden" name="post_id" value="<?= $post['id'] ?>">
                                    
                                    <!-- Honeypot anti-spam (oculto para usuarios reales) -->
                                    <input type="text" name="website" style="display:none;" tabindex="-1" autocomplete="off">
                                    <input type="text" name="phone" style="display:none;" tabindex="-1" autocomplete="off">
                                    
                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label style="color: var(--text-color);"><?= t_theme('theme_nombre') ?> *</label>
                                            <input type="text" name="nombre" id="commentNombre" required
                                                   class="form-control" style="background: var(--dark-secondary); color: var(--text-color); border: 2px solid rgba(255,255,255,0.1);">
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label style="color: var(--text-color);"><?= t_theme('theme_email') ?> *</label>
                                            <input type="email" name="email" id="commentEmail" required
                                                   class="form-control" style="background: var(--dark-secondary); color: var(--text-color); border: 2px solid rgba(255,255,255,0.1);">
                                        </div>
                                    </div>
                                    <div class="mb-3">
                                        <label style="color: var(--text-color);"><?= t_theme('theme_comentario') ?> *</label>
                                        <textarea name="contenido" id="commentContenido" rows="4" required maxlength="2000"
                                                  class="form-control" style="background: var(--dark-secondary); color: var(--text-color); border: 2px solid rgba(255,255,255,0.1);"></textarea>
                                        <small style="color: var(--text-muted);">Mínimo 10 caracteres, máximo 2000</small>
                                    </div>
                                    <button type="submit" class="btn-artemis">
                                        <i class="fas fa-paper-plane mr-2"></i><?= t_theme('theme_enviar_comentario') ?>
                                    </button>
                                </form>
                                <div id="commentMessage" class="mt-3" style="display:none;"></div>
                            </div>
                            
                            <!-- Lista de comentarios aprobados -->
                            <div id="commentsList">
                                <?php
                                $stmtComments = db()->prepare("
                                    SELECT c.*, u.foto_perfil
                                    FROM comments c
                                    LEFT JOIN usuarios u ON u.id = c.user_id
                                    WHERE c.post_id = ? AND c.estado = 'approved' AND c.borrado = 0
                                    ORDER BY c.created_at ASC
                                ");
                                $stmtComments->execute([$post['id']]);
                                $comments = $stmtComments->fetchAll();
                                
                                if (empty($comments)):
                                ?>
                                <div class="text-center py-4" style="color: var(--text-muted);">
                                    <i class="fas fa-comment-slash" style="font-size: 48px; opacity: 0.3;"></i>
                                    <p class="mt-3"><?= t_theme('theme_sin_comentarios') ?></p>
                                </div>
                                <?php else: ?>
                                <?php foreach ($comments as $comment): ?>
                                <div class="mb-3 p-3" style="background: rgba(255,255,255,0.03); border-radius: 12px;">
                                    <div class="d-flex align-items-start mb-2">
                                        <div class="mr-3" style="width: 40px; height: 40px; border-radius: 50%; background: var(--primary); display: flex; align-items: center; justify-content: center; color: white; font-weight: bold;">
                                            <?= strtoupper(substr($comment['nombre'], 0, 1)) ?>
                                        </div>
                                        <div class="flex-grow-1">
                                            <strong style="color: var(--text-color);"><?= htmlspecialchars($comment['nombre']) ?></strong>
                                            <div style="color: var(--text-muted); font-size: 12px;">
                                                <i class="far fa-clock mr-1"></i><?= date('d/m/Y H:i', strtotime($comment['created_at'])) ?>
                                            </div>
                                        </div>
                                    </div>
                                    <div style="color: var(--text-color); line-height: 1.6;">
                                        <?= nl2br(htmlspecialchars($comment['contenido'])) ?>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-4">
                <?php include __DIR__ . '/partials/sidebar.php'; ?>
            </div>
        </div>
    </div>
</section>

<?php if (!empty(TEXT_TO_SPEECH) && TEXT_TO_SPEECH == '1'): ?>
<style>
    /* Estilos del reproductor moderno - Colores adaptados al tema Artemis */
    .audio-player-modern {
        background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
        border-radius: 16px;
        padding: 20px;
        box-shadow: 0 8px 32px rgba(230, 57, 70, 0.25);
        transition: all 0.3s ease;
    }

    .audio-player-modern:hover {
        box-shadow: 0 12px 48px rgba(230, 57, 70, 0.35);
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
        color: var(--text-color);
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
        transition: width 0.1s linear;
        width: 0%;
    }

    .audio-btn-main.playing {
        animation: pulse 2s infinite;
    }
    @keyframes pulse {
        0%, 100% { transform: scale(1); }
        50% { transform: scale(1.05); }
    }

    /* Control de velocidad */
    #speedControl {
        background: rgba(255, 255, 255, 0.2);
        color: white;
        border: 1px solid rgba(255, 255, 255, 0.3);
    }

    #speedControl option {
        background: var(--dark-secondary, #2d2d2d);
        color: white;
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

// Preparar el texto
window.addEventListener('load', function() {
    const articleContent = document.querySelector('.post-content');
    const title = "<?= addslashes($post['title']) ?>";
    const excerpt = "<?= addslashes(strip_tags($post['excerpt'] ?? '')) ?>";

    fullText = (title + '. ' + excerpt + '. ' + (articleContent ? articleContent.innerText : ''))
        .replace(/\s+/g, ' ')
        .trim();

    const wordCount = fullText.split(' ').length;
    totalDuration = Math.ceil((wordCount / 150) * 60);
});

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
    if (!fullText) {
        console.error('Text not ready yet. Waiting for window.load...');
        return;
    }

    if (!('speechSynthesis' in window)) {
        alert('Tu navegador no soporta Text-to-Speech. Intenta con Chrome, Firefox o Edge.');
        return;
    }

    // Si está reproduciendo, pausar
    if (synth.speaking && !isPaused) {
        isPaused = true;
        synth.cancel();
        updateUI('paused');
        return;
    }

    // Si está pausado, reanudar
    if (isPaused) {
        isPaused = false;
        speak(currentPosition);
    } else {
        // Iniciar desde el principio
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
    synth.cancel();
    isPaused = false;
    currentPosition = 0;
    updateUI('stopped');
}

function updateUI(state) {
    const playIcon = document.getElementById('playIcon');
    const playBtn = document.getElementById('playBtn');
    const stopBtn = document.getElementById('stopBtn');
    
    if (playIcon) {
        playIcon.className = state === 'playing' ? 'fas fa-pause' : 'fas fa-play';
    }
    if (playBtn) {
        playBtn.title = state === 'playing' ? 'Pausar' : 'Reproducir';
        if (state === 'playing') {
            playBtn.classList.add('playing');
        } else {
            playBtn.classList.remove('playing');
        }
    }
    if (stopBtn) {
        if (state === 'stopped') {
            stopBtn.classList.add('d-none');
        } else {
            stopBtn.classList.remove('d-none');
        }
    }
}

function updateTime() {
    const elapsed = Math.floor((Date.now() - startTime) / 1000);
    const minutes = Math.floor(elapsed / 60);
    const seconds = elapsed % 60;
    const timeDisplay = document.getElementById('timeDisplay');
    if (timeDisplay) {
        timeDisplay.textContent = minutes + ':' + String(seconds).padStart(2, '0');
    }
    
    if (synth.speaking && !isPaused) {
        setTimeout(updateTime, 1000);
    }
}

window.addEventListener('beforeunload', function () {
    synth.cancel();
});
</script>
<?php endif; ?>

<!-- JavaScript para formulario de comentarios -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    const commentForm = document.getElementById('commentForm');
    const commentMessage = document.getElementById('commentMessage');
    
    if (commentForm) {
        commentForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData(commentForm);
            const submitBtn = commentForm.querySelector('button[type="submit"]');
            const originalText = submitBtn.innerHTML;
            
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Enviando...';
            
            fetch('<?= URLBASE ?>/actions/add_comment.php', {
                method: 'POST',
                body: formData
            })
            .then(res => res.json())
            .then(data => {
                commentMessage.style.display = 'block';
                if (data.success) {
                    commentMessage.className = 'alert alert-success';
                    commentMessage.innerHTML = '<i class="fas fa-check-circle mr-2"></i>' + data.message;
                    commentForm.reset();
                    
                    // Recargar comentarios después de 2 segundos
                    setTimeout(() => {
                        location.reload();
                    }, 2000);
                } else {
                    commentMessage.className = 'alert alert-danger';
                    commentMessage.innerHTML = '<i class="fas fa-exclamation-circle mr-2"></i>' + data.message;
                }
                submitBtn.disabled = false;
                submitBtn.innerHTML = originalText;
            })
            .catch(err => {
                commentMessage.style.display = 'block';
                commentMessage.className = 'alert alert-danger';
                commentMessage.innerHTML = '<i class="fas fa-exclamation-circle mr-2"></i>Error al enviar. Intenta nuevamente.';
                submitBtn.disabled = false;
                submitBtn.innerHTML = originalText;
                console.error(err);
            });
        });
    }
});
</script>