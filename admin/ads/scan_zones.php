<?php
// admin/publicidad/scan_zones.php
require_once __DIR__ . '/../../inc/config.php';
header('Content-Type: application/json; charset=UTF-8');

function fetchHtml(string $url): string {
    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_TIMEOUT        => 8,
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_USERAGENT      => 'Mozilla/5.0 AdsScanner/1.0',
    ]);
    $html = curl_exec($ch);
    curl_close($ch);
    return $html ?: '';
}

function scanSelectors(string $html): array {
    $dom = new DOMDocument();
    libxml_use_internal_errors(true);
    $dom->loadHTML(mb_convert_encoding($html, 'HTML-ENTITIES', 'UTF-8'));
    libxml_clear_errors();

    $xpath   = new DOMXPath($dom);
    $results = [];

    // Solo tags estructurales relevantes
    $tags = ['div', 'section', 'main', 'article', 'aside', 'header', 'footer', 'ul', 'nav'];

    foreach ($tags as $tag) {
        $nodes = $xpath->query("//{$tag}[@id or @class]");
        foreach ($nodes as $node) {
            $id      = trim($node->getAttribute('id'));
            $classes = array_filter(explode(' ', trim($node->getAttribute('class'))));
            $classes = array_values(array_filter($classes, fn($c) => strlen($c) > 2));

            // Contar hijos directos para saber si es un contenedor real
            $childCount = 0;
            foreach ($node->childNodes as $child) {
                if ($child->nodeType === XML_ELEMENT_NODE) $childCount++;
            }

            if ($childCount < 1) continue; // ignorar vacíos

            $selector = $id ? "#{$id}" : '.' . ($classes[0] ?? '');
            if (!$selector || $selector === '.') continue;

            $results[$selector] = [
                'selector'   => $selector,
                'tag'        => $tag,
                'id'         => $id,
                'classes'    => $classes,
                'children'   => $childCount,
                'text_preview' => mb_substr(trim($node->textContent), 0, 60),
            ];
        }
    }

    // Ordenar por hijos descendente (los contenedores más ricos primero)
    uasort($results, fn($a, $b) => $b['children'] <=> $a['children']);

    return array_values(array_slice($results, 0, 60)); // máximo 60
}

$url  = trim($_GET['url'] ?? URLBASE);
$html = fetchHtml($url);

if (empty($html)) {
    echo json_encode(['success' => false, 'message' => 'No se pudo acceder a: ' . $url]);
    exit;
}

$selectors = scanSelectors($html);

echo json_encode([
    'success'   => true,
    'url'       => $url,
    'total'     => count($selectors),
    'selectors' => $selectors,
], JSON_UNESCAPED_UNICODE);