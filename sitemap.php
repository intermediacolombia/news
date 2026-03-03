<?php
ob_start(); 

require_once __DIR__ . '/inc/config.php';

ob_end_clean(); 

header('Content-Type: application/xml; charset=UTF-8');

// Archivo físico donde se guardará el sitemap
$sitemapFile = __DIR__ . '/sitemap.xml';

$base = rtrim(URLBASE, '/');
$urls = [];

// ── 1. Homepage ───────────────────────────────────────────────────────────
$urls[] = [
    'loc'        => $base . '/',
    'changefreq' => 'hourly',
    'priority'   => '1.0',
    'lastmod'    => date('Y-m-d'),
];

// ── 2. Listado General de Noticias ────────────────────────────────────────
$urls[] = [
    'loc'        => $base . '/noticiassssssssssss/',
    'changefreq' => 'hourly',
    'priority'   => '0.9',
    'lastmod'    => date('Y-m-d'),
];

// ── 3. Categorías de Noticias → /noticias/{categoria_slug}/ ───────────────
try {
    $cats = db()->query(
        "SELECT slug, updated_at FROM blog_categories WHERE (borrado = 0 OR borrado IS NULL)"
    )->fetchAll(PDO::FETCH_ASSOC);

    foreach ($cats as $cat) {
        if (empty($cat['slug'])) continue;
        $urls[] = [
            'loc'        => $base . '/noticias/' . $cat['slug'] . '/',
            'changefreq' => 'daily',
            'priority'   => '0.8',
            'lastmod'    => date('Y-m-d', strtotime($cat['updated_at'] ?? 'now')),
        ];
    }
} catch (Throwable $e) {
    error_log('[sitemap] Error categorías: ' . $e->getMessage());
}

// ── 4. Posts Individuales → /{categoria_slug}/{post_slug}/ ────────────────
try {
    $posts = db()->query(
        "SELECT bp.slug AS post_slug, bc.slug AS cat_slug, bp.updated_at, bp.created_at
         FROM blog_posts bp
         LEFT JOIN blog_post_category bpc ON bpc.post_id = bp.id
         LEFT JOIN blog_categories bc ON bc.id = bpc.category_id
         WHERE bp.status = 'published'
         GROUP BY bp.id
         ORDER BY bp.created_at DESC LIMIT 5000"
    )->fetchAll(PDO::FETCH_ASSOC);

    foreach ($posts as $post) {
        $catSlug  = $post['cat_slug']  ?? 'noticias';
        $postSlug = $post['post_slug'] ?? '';
        if (empty($postSlug)) continue;

        $lastmod = $post['updated_at'] ?? $post['created_at'];
        $urls[]  = [
            'loc'        => $base . '/' . $catSlug . '/' . $postSlug . '/',
            'changefreq' => 'weekly',
            'priority'   => '0.7',
            'lastmod'    => date('Y-m-d', strtotime($lastmod)),
        ];
    }
} catch (Throwable $e) {
    error_log('[sitemap] Error posts: ' . $e->getMessage());
}

// ── 5. Columnistas → /columnista/ y /columnista/{slug}/ ──────────────────
try {
    $urls[] = [
        'loc'        => $base . '/columnista/',
        'changefreq' => 'weekly',
        'priority'   => '0.6',
        'lastmod'    => date('Y-m-d'),
    ];

    $columnistas = db()->query(
        "SELECT username AS slug, updated_at FROM usuarios 
         WHERE es_columnista = 1 AND borrado = 0 AND estado = 0"
    )->fetchAll(PDO::FETCH_ASSOC);

    foreach ($columnistas as $col) {
        if (empty($col['slug'])) continue;
        $urls[] = [
            'loc'        => $base . '/columnista/' . $col['slug'] . '/',
            'changefreq' => 'weekly',
            'priority'   => '0.6',
            'lastmod'    => date('Y-m-d', strtotime($col['updated_at'] ?? 'now')),
        ];
    }
} catch (Throwable $e) {
    error_log('[sitemap] Error columnistas: ' . $e->getMessage());
}

// ── 6. Institucional → /institucional/ y /institucional/{slug}/ ───────────
try {
    $urls[] = [
        'loc'        => $base . '/institucional/',
        'changefreq' => 'monthly',
        'priority'   => '0.5',
        'lastmod'    => date('Y-m-d'),
    ];

    $pages = db()->query(
        "SELECT slug, updated_at FROM institutional_pages WHERE (borrado = 0 OR borrado IS NULL)"
    )->fetchAll(PDO::FETCH_ASSOC);

    foreach ($pages as $page) {
        if (empty($page['slug'])) continue;
        $urls[] = [
            'loc'        => $base . '/institucional/' . $page['slug'] . '/',
            'changefreq' => 'monthly',
            'priority'   => '0.5',
            'lastmod'    => date('Y-m-d', strtotime($page['updated_at'] ?? 'now')),
        ];
    }
} catch (Throwable $e) {
    error_log('[sitemap] Error institucional: ' . $e->getMessage());
}

// ── 7. Contacto y Buscar ──────────────────────────────────────────────────
$urls[] = ['loc' => $base . '/contact/', 'changefreq' => 'monthly', 'priority' => '0.5', 'lastmod' => date('Y-m-d')];
$urls[] = ['loc' => $base . '/buscar/',  'changefreq' => 'yearly',  'priority' => '0.3', 'lastmod' => date('Y-m-d')];

// ── Generar XML ───────────────────────────────────────────────────────────
$xml  = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
$xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";

foreach ($urls as $url) {
    $xml .= "  <url>\n";
    $xml .= "    <loc>"        . htmlspecialchars($url['loc'], ENT_XML1, 'UTF-8') . "</loc>\n";
    $xml .= "    <lastmod>"    . $url['lastmod'] . "</lastmod>\n";
    $xml .= "    <changefreq>" . $url['changefreq'] . "</changefreq>\n";
    $xml .= "    <priority>"   . $url['priority'] . "</priority>\n";
    $xml .= "  </url>\n";
}
$xml .= '</urlset>';

// ── Guardar y Enviar ──────────────────────────────────────────────────────
$sitemapDir = dirname($sitemapFile);
if (!is_dir($sitemapDir)) mkdir($sitemapDir, 0775, true);

if (is_writable($sitemapDir)) {
    file_put_contents($sitemapFile, $xml);
} else {
    error_log('[sitemap] Error de permisos en: ' . $sitemapDir);
}

echo $xml;
exit;

