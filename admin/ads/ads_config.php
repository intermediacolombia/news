<?php
require_once __DIR__ . '/../../inc/config.php';
header('Content-Type: application/javascript; charset=UTF-8');
header('Cache-Control: public, max-age=300');

$pubId   = ADSENSE_PUBLISHER_ID;
$autoAds = ADSENSE_AUTO_ADS === '1';

if ($autoAds || empty($pubId)) {
    echo 'window.ADS_CONFIG = null;';
    exit;
}

// ── 1. Usar el mapa guardado por el admin desde system_settings ───────────
$savedMap = $GLOBALS['SYS_SETTINGS']['ads_zone_map'] ?? '';
$zoneMap  = !empty($savedMap) ? (json_decode($savedMap, true) ?? []) : [];

// ── 2. Si no hay mapa guardado, usar fallbacks genéricos ──────────────────
if (empty($zoneMap)) {
    $zoneMap = [
        'header'  => ['selector' => 'header',  'position' => 'inside-first', 'every' => null],
        'loop'    => ['selector' => 'main',     'position' => 'every-nth',    'every' => 4],
        'sidebar' => ['selector' => 'aside',    'position' => 'inside-first', 'every' => null],
        'single'  => ['selector' => 'article',  'position' => 'after',        'every' => null],
        'footer'  => ['selector' => 'footer',   'position' => 'before',       'every' => null],
    ];
}

// ── 3. Cargar bloques activos desde la DB ─────────────────────────────────
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

// ── 4. Armar output ───────────────────────────────────────────────────────
$output = [];
foreach ($blocks as $block) {
    $meta = json_decode($block['ad_code'] ?? '{}', true);
    $zone = $block['zone'] ?? 'loop';

    // Si el bloque tiene selector custom, usarlo directamente
    $cssCustom = trim($meta['css_selector'] ?? '');
    if (!empty($cssCustom)) {
        $selector = $cssCustom;
        $position = 'after';
        $every    = null;
    } else {
        $map = $zoneMap[$zone] ?? null;
        if (!$map) continue;
        $selector = $map['selector'] ?? '';
        $position = $map['position'] ?? 'inside-first';
        $every    = $map['every']    ?? null;
    }

    if (empty($selector) || empty($meta['slot_id'])) continue;

    $output[] = [
        'slotId'   => $meta['slot_id'],
        'format'   => $meta['format'] ?? 'auto',
        'selector' => $selector,
        'position' => $position,
        'every'    => $every ? (int)$every : null,
    ];
}

echo 'window.ADS_CONFIG = ' . json_encode([
    'pubId'  => $pubId,
    'blocks' => $output,
], JSON_UNESCAPED_UNICODE) . ';';
