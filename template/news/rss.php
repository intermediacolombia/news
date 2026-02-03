<?php
// 1. Desactivar reporte de errores visuales para no romper el XML
ini_set('display_errors', '0');
error_reporting(0);

// 2. Cargar configuración
require_once __DIR__ . '/../../inc/config.php';

// 3. LIMPIEZA TOTAL: Eliminar cualquier HTML que config.php haya intentado imprimir
while (ob_get_level()) {
    ob_end_clean();
}

// 4. Cabecera XML estricta
header('Content-Type: application/rss+xml; charset=UTF-8');

function safe_xml_text(string $s): string {
    return htmlspecialchars(str_replace("\0", '', $s), ENT_XML1 | ENT_QUOTES, 'UTF-8');
}

function cdata(string $s): string {
    return '<![CDATA[' . str_replace(']]>', ']]]]><![CDATA[>', $s) . ']]>';
}

// ---------- Consulta SQL ----------
try {
    $sql = "SELECT p.id, p.title, p.slug, p.content, p.image, p.created_at,
                   (SELECT c.slug FROM blog_categories c 
                    JOIN blog_post_category pc ON c.id = pc.category_id 
                    WHERE pc.post_id = p.id LIMIT 1) AS category_slug
            FROM blog_posts p
            WHERE p.status = 'published' AND p.deleted = 0
            ORDER BY p.created_at DESC
            LIMIT 30";
            
    $stmt = db()->query($sql);
    $posts = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Throwable $e) {
    $posts = [];
}

$baseUrl = rtrim(URLBASE, '/');
$feedTitle = (defined('NOMBRE_SITIO') ? NOMBRE_SITIO : 'Noticias');

// ---------- EMPEZAR SALIDA XML ----------
echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
?>
<rss version="2.0"
     xmlns:atom="http://www.w3.org/2005/Atom"
     xmlns:media="http://search.yahoo.com/mrss/">
  <channel>
    <title><?= safe_xml_text($feedTitle) ?></title>
    <link><?= safe_xml_text($baseUrl) ?></link>
    <description>Últimas noticias de <?= safe_xml_text($feedTitle) ?></description>
    <language>es-ES</language>
    <lastBuildDate><?= date(DATE_RSS) ?></lastBuildDate>
    <atom:link href="<?= safe_xml_text($baseUrl) ?>/rss.php" rel="self" type="application/rss+xml" />

<?php foreach ($posts as $post):
    $title = $post['title'] ?? '';
    $slug  = $post['slug'] ?? '';
    $cat   = $post['category_slug'] ?? 'noticias';
    $itemUrl = $baseUrl . '/' . $cat . '/' . $slug;
    
    $pubTs   = strtotime((string)$post['created_at']);
    $pubDate = $pubTs ? date(DATE_RSS, $pubTs) : date(DATE_RSS);
    
    $desc = mb_substr(strip_tags((string)$post['content']), 0, 300) . '...';

    $imgUrl = '';
    if (!empty($post['image'])) {
        $img = $post['image'];
        $imgUrl = (filter_var($img, FILTER_VALIDATE_URL)) ? $img : $baseUrl . '/' . ltrim($img, '/');
    }
?>
    <item>
      <title><?= safe_xml_text($title) ?></title>
      <link><?= safe_xml_text($itemUrl) ?></link>
      <guid isPermaLink="true"><?= safe_xml_text($itemUrl) ?></guid>
      <pubDate><?= $pubDate ?></pubDate>
      <description><?= cdata($desc) ?></description>
<?php if ($imgUrl): ?>
      <media:content url="<​?= safe_xml_text($imgUrl) ?>" medium="image" />
<?php endif; ?>
    </item>
<?php endforeach; ?>
  </channel>
</rss>
<?php
// 5. Finalizar ejecución para que nada más se imprima
exit;
