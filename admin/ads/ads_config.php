<?php
require_once __DIR__ . '/../../inc/config.php';
header('Content-Type: application/javascript; charset=UTF-8');
header('Cache-Control: public, max-age=60');

$pubId   = trim($sys['adsense_publisher_id'] ?? '');
$autoAds = ADSENSE_AUTO_ADS === '1';

if ($autoAds || empty($pubId)) {
    echo 'window.ADS_CONFIG = null;';
    exit;
}

// ── 1. Fetch del HTML del sitio ───────────────────────────────────────────
function fetchHtml(string $url): string {
    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_TIMEOUT        => 8,
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_USERAGENT      => 'AdsInjector/1.0',
    ]);
    $html = curl_exec($ch);
    curl_close($ch);
    return $html ?: '';
}

// ── 2. Extraer selectores únicos del HTML ─────────────────────────────────
function extractSelectors(string $html): array {
    $dom = new DOMDocument();
    libxml_use_internal_errors(true);
    $dom->loadHTML($html);
    libxml_clear_errors();

    $xpath    = new DOMXPath($dom);
    $found    = [];

    // Buscar nodos con id o class relevantes
    $nodes = $xpath->query('//*[@id or @class]');
    foreach ($nodes as $node) {
        $tag   = strtolower($node->nodeName);
        $id    = trim($node->getAttribute('id'));
        $class = trim($node->getAttribute('class'));

        if ($id) {
            $found["#{$id}"] = ['tag' => $tag, 'id' => $id, 'classes' => []];
        }
        if ($class) {
            foreach (explode(' ', $class) as $cls) {
                $cls = trim($cls);
                if (strlen($cls) > 2) {
                    $found[".{$cls}"] = ['tag' => $tag, 'id' => $id, 'classes' => [$cls]];
                }
            }
        }
    }

    return array_keys($found);
}

// ── 3. Detectar zona por keywords en el selector ──────────────────────────
function detectZone(string $selector): ?string {
    $s = strtolower($selector);

    $zoneKeywords = [
        'header'  => ['header', 'top-bar', 'navbar', 'hero', 'banner', 'masthead', 'approot', 'main-content'],
        'loop'    => ['news', 'article', 'post', 'feed', 'grid', 'list', 'blog', 'entries', 'latest', 'featured'],
        'sidebar' => ['sidebar', 'aside', 'widget', 'related', 'secondary'],
        'single'  => ['content', 'entry', 'body', 'detail', 'single', 'text', 'story'],
        'footer'  => ['footer', 'bottom', 'foot'],
    ];

    foreach ($zoneKeywords as $zone => $keywords) {
        foreach ($keywords as $kw) {
            if (str_contains($s, $kw)) return $zone;
        }
    }

    return null;
}

// ── Cache del zoneMap por 1 hora ──────────────────────────────────────────
$cacheFile = sys_get_temp_dir() . '/ads_zonemap_' . md5(URLBASE) . '.json';
$cacheTtl  = 3600; // 1 hora

if (file_exists($cacheFile) && (time() - filemtime($cacheFile)) < $cacheTtl) {
    $zoneMap = json_decode(file_get_contents($cacheFile), true);
} else {
    $zoneMap = buildZoneMap(URLBASE);
    file_put_contents($cacheFile, json_encode($zoneMap));
}

// ── 4. Construir zoneMap desde el HTML real ───────────────────────────────
function buildZoneMap(string $baseUrl): array {
    $pages = [
        $baseUrl,                    // home
        $baseUrl . '/noticias',      // loop
    ];

    $zoneMap    = [];
    $zoneFilled = [];

    foreach ($pages as $url) {
        $html = fetchHtml($url);
        if (empty($html)) continue;

        $selectors = extractSelectors($html);

        foreach ($selectors as $selector) {
            $zone = detectZone($selector);
            if (!$zone || isset($zoneFilled[$zone])) continue;

            $position = match($zone) {
                'loop'   => 'every-nth',
                'footer' => 'before',
                default  => 'inside-first',
            };

            $zoneMap[$zone] = [
                'selector' => $selector,
                'position' => $position,
                'every'    => $zone === 'loop' ? 4 : null,
            ];
            $zoneFilled[$zone] = true;
        }

        if (count($zoneFilled) >= 5) break;
    }

    // Fallbacks si no encontró alguna zona
    $fallbacks = [
        'header'  => ['selector' => '#appRoot',        'position' => 'inside-first'],
        'loop'    => ['selector' => 'main',             'position' => 'every-nth', 'every' => 4],
        'sidebar' => ['selector' => 'aside',            'position' => 'inside-first'],
        'single'  => ['selector' => 'article',         'position' => 'after'],
        'footer'  => ['selector' => 'footer',           'position' => 'before'],
    ];

    foreach ($fallbacks as $zone => $fallback) {
        if (!isset($zoneMap[$zone])) {
            $zoneMap[$zone] = $fallback;
        }
    }

    return $zoneMap;
}

// ── 5. Cargar bloques activos ─────────────────────────────────────────────
try {
    $blocks = db()->query(
        "SELECT ad_code, zone FROM ads
         WHERE ad_type = 'adsense' AND status = 'active'
         ORDER BY position ASC"
    )->fetchAll(PDO::FETCH_ASSOC);
} catch (Throwable $e) {
    echo 'window.ADS_CONFIG = null;';
    exit;
}

if (empty($blocks)) {
    echo 'window.ADS_CONFIG = null;';
    exit;
}

// ── 6. Construir zoneMap desde HTML real ─────────────────────────────────
$zoneMap = buildZoneMap(URLBASE);

// ── 7. Armar output final ─────────────────────────────────────────────────
$output = [];
foreach ($blocks as $block) {
    $meta = json_decode($block['ad_code'] ?? '{}', true);
    $zone = $block['zone'] ?? 'header';
    $map  = $zoneMap[$zone] ?? null;
    if (!$map || empty($meta['slot_id'])) continue;

    $output[] = [
        'slotId'   => $meta['slot_id'],
        'format'   => $meta['format'] ?? 'auto',
        'selector' => $map['selector'],
        'position' => $map['position'],
        'every'    => $map['every'] ?? null,
    ];
}

echo 'window.ADS_CONFIG = ' . json_encode([
    'pubId'  => $pubId,
    'blocks' => $output,
], JSON_UNESCAPED_UNICODE) . ';';