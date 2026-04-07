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
                                Por <strong style="color: var(--text-color);"><?= htmlspecialchars($authorData['nombre'] . ' ' . $authorData['apellido']) ?></strong>
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
                        <div class="audio-player-modern mb-4" style="background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%); border-radius: 16px; padding: 20px;">
                            <div class="d-flex align-items-center gap-3">
                                <button id="playBtn" class="audio-btn-main" onclick="handlePlay()" title="Reproducir" style="width: 56px; height: 56px; border-radius: 50%; background: #fff; border: none; color: var(--primary); font-size: 20px; display: flex; align-items: center; justify-content: center; cursor: pointer;">
                                    <i class="fas fa-play" id="playIcon"></i>
                                </button>
                                <div class="flex-grow-1">
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <span style="color: var(--text-color); font-weight: 600;">
                                            <i class="fas fa-headphones mr-2"></i>Escuchar artículo
                                        </span>
                                        <span id="timeDisplay" style="color: rgba(255,255,255,0.9); font-size: 13px;">0:00</span>
                                    </div>
                                    <div style="height: 6px; background: rgba(255,255,255,0.2); border-radius: 10px; overflow: hidden;">
                                        <div id="audioProgress" style="height: 100%; background: #fff; border-radius: 10px; width: 0%;"></div>
                                    </div>
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
                                <img src="<?= $fotoAutor ?>" 
                                     alt="<?= htmlspecialchars($authorData['nombre'] . ' ' . $authorData['apellido']) ?>" 
                                     class="mr-3"
                                     style="width: 70px; height: 70px; border-radius: 50%; object-fit: cover;">
                                <div>
                                    <h5 style="color: var(--text-color); margin: 0;"><?= htmlspecialchars($authorData['nombre'] . ' ' . $authorData['apellido']) ?></h5>
                                    <span style="color: var(--primary); font-size: 14px;"><?= t_theme('theme_autor') ?></span>
                                </div>
                            </div>
                        </div>
                        <?php endif; ?>

                        <div class="mt-4">
                            <h4 style="color: var(--text-color); margin-bottom: 20px;">Comentarios</h4>
                            <div class="fb-comments" data-href="<?= 'https://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'] ?>" data-width="100%" data-numposts="10" data-colorscheme="dark"></div>
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
    .audio-player-modern:hover {
        box-shadow: 0 12px 48px rgba(230, 57, 70, 0.35);
    }
    .audio-btn-main:hover {
        transform: scale(1.1);
    }
    .audio-btn-main.playing {
        animation: pulse 2s infinite;
    }
    @keyframes pulse {
        0%, 100% { transform: scale(1); }
        50% { transform: scale(1.05); }
    }
</style>
<script>
(function () {
    if (!('speechSynthesis' in window)) return;

    const synth       = window.speechSynthesis;
    let chunks        = [];      // oraciones divididas
    let chunkIndex    = 0;       // índice de la oración actual
    let charsSpoken   = 0;       // chars en chunks anteriores al actual
    let isPaused      = false;
    let utterance     = null;
    let keepAliveTimer    = null;
    let keepAlivePausing  = false; // true durante el ciclo pause/resume del keep-alive
    let startTime     = 0;
    let fullText      = '';

    window.addEventListener('load', function () {
        const content = document.querySelector('.post-content');
        const title   = <?= json_encode($post['title']) ?>;
        if (content) {
            fullText = (title + '. ' + content.innerText).replace(/\s+/g, ' ').trim();
        }
    });

    // Dividir en trozos de ~200 chars (por oraciones) para evitar el bug de Chrome en textos largos
    function splitChunks(text, maxLen) {
        maxLen = maxLen || 200;
        var sentences = text.match(/[^.!?]+[.!?]*/g) || [text];
        var result = [], current = '';
        sentences.forEach(function (s) {
            if ((current + s).length > maxLen && current) {
                result.push(current.trim());
                current = s;
            } else {
                current += s;
            }
        });
        if (current.trim()) result.push(current.trim());
        return result.length ? result : [text];
    }

    // Keep-alive: evita que Chrome corte el audio a los ~15 s
    // keepAlivePausing evita que el onend del utterance se dispare erroneamente
    function startKeepAlive() {
        stopKeepAlive();
        keepAliveTimer = setInterval(function () {
            if (synth.speaking && !isPaused && !keepAlivePausing) {
                keepAlivePausing = true;
                synth.pause();
                synth.resume();
                setTimeout(function () { keepAlivePausing = false; }, 300);
            }
        }, 10000);
    }

    function stopKeepAlive() {
        if (keepAliveTimer) { clearInterval(keepAliveTimer); keepAliveTimer = null; }
        keepAlivePausing = false;
    }

    function setPlayState(playing) {
        var icon = document.getElementById('playIcon');
        var btn  = document.getElementById('playBtn');
        if (!icon || !btn) return;
        icon.className = playing ? 'fas fa-pause' : 'fas fa-play';
        btn.title = playing ? 'Pausar' : 'Reproducir';
    }

    function getEsVoice() {
        return synth.getVoices().find(function (v) { return v.lang.startsWith('es'); }) || null;
    }

    function speakChunk(index) {
        if (index >= chunks.length) {
            // Terminó
            chunkIndex = 0; charsSpoken = 0; isPaused = false;
            setPlayState(false);
            stopKeepAlive();
            var bar  = document.getElementById('audioProgress');
            var time = document.getElementById('timeDisplay');
            if (bar)  bar.style.width = '0%';
            if (time) time.textContent = '0:00';
            return;
        }

        chunkIndex = index;
        utterance  = new SpeechSynthesisUtterance(chunks[index]);
        utterance.rate = 1.0;
        var voice = getEsVoice();
        if (voice) utterance.voice = voice;
        utterance.lang = voice ? voice.lang : 'es-ES';

        utterance.onstart = function () {
            setPlayState(true);
            if (index === 0) { startTime = Date.now(); updateTime(); startKeepAlive(); }
        };

        utterance.onboundary = function (e) {
            var total = charsSpoken + (e.charIndex || 0);
            var pct   = fullText.length ? (total / fullText.length) * 100 : 0;
            var bar   = document.getElementById('audioProgress');
            if (bar) bar.style.width = Math.min(pct, 100) + '%';
        };

        utterance.onend = function () {
            if (keepAlivePausing || isPaused) return; // ignorar fin espurio del keep-alive o pausa
            charsSpoken += chunks[index].length + 1;
            speakChunk(index + 1);
        };

        utterance.onerror = function (e) {
            if (e.error === 'interrupted' || e.error === 'canceled') return;
            setPlayState(false);
            stopKeepAlive();
        };

        synth.speak(utterance);
    }

    function doStart(fromChunk, fromCharsSpoken) {
        fromChunk = fromChunk || 0;
        fromCharsSpoken = fromCharsSpoken || 0;
        chunks = splitChunks(fullText, 200);
        chunkIndex  = fromChunk;
        charsSpoken = fromCharsSpoken;
        isPaused    = false;

        function run() { speakChunk(fromChunk); }
        if (synth.getVoices().length > 0) { run(); }
        else { speechSynthesis.addEventListener('voiceschanged', run, { once: true }); }
    }

    function updateTime() {
        var elapsed = Math.floor((Date.now() - startTime) / 1000);
        var m = Math.floor(elapsed / 60);
        var s = elapsed % 60;
        var el = document.getElementById('timeDisplay');
        if (el) el.textContent = m + ':' + String(s).padStart(2, '0');
        if (synth.speaking || isPaused) setTimeout(updateTime, 1000);
    }

    window.handlePlay = function () {
        if (!fullText) return;

        if (isPaused) {
            // Reanudar desde el chunk donde se pausó
            var savedChunk = chunkIndex;
            var savedChars = charsSpoken;
            synth.cancel();
            doStart(savedChunk, savedChars);
            startKeepAlive();
            updateTime();
        } else if (synth.speaking) {
            // Pausar
            isPaused = true;
            synth.cancel();
            stopKeepAlive();
            setPlayState(false);
        } else {
            // Iniciar desde el principio
            if (!chunks.length) chunks = splitChunks(fullText, 200);
            synth.cancel();
            doStart(0, 0);
        }
    };

    window.addEventListener('beforeunload', function () { synth.cancel(); });
})();
</script>
<?php endif; ?>