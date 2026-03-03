<?php
require_once __DIR__ . '/../../inc/config.php';
header('Content-Type: application/javascript; charset=UTF-8');
header('Cache-Control: public, max-age=300'); // cache 5 min

$pubId = trim($sys['adsense_publisher_id'] ?? '');
$autoAds = ADSENSE_AUTO_ADS === '1';

// Si Auto Ads está ON o no hay pubId, output vacío
if ($autoAds || empty($pubId)) {
    echo 'window.ADS_CONFIG = null;';
    exit;
}

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

// Mapeo zona → selector CSS + posición de inyección
$zoneMap = [
    'header'  => ['selector' => '.main-content, #appRoot, .container:first-of-type', 'position' => 'inside-first'],
    'loop'    => ['selector' => '.news-list, .articles-grid, .post-list',             'position' => 'every-nth', 'every' => 4],
    'sidebar' => ['selector' => '.sidebar, aside, .widget-area',                      'position' => 'inside-first'],
    'single'  => ['selector' => '.post-content, .article-body, .entry-content',       'position' => 'after'],
    'footer'  => ['selector' => 'footer, .footer, #footer',                           'position' => 'before'],
];

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