<?php
declare(strict_types=1);

// 1. Ajuste de ruta: Si rss.php está en la raíz, suele ser ./inc/config.php
require_once __DIR__ . '/inc/config.php'; 

if (ob_get_level()) {
    @ob_end_clean();
}

header('Content-Type: application/rss+xml; charset=UTF-8');

function safe_xml_text(string $s): string {
    return htmlspecialchars(str_replace("\0", '', $s), ENT_XML1 | ENT_QUOTES, 'UTF-8');
}

function cdata(string $s): string {
    return '<![CDATA[' . str_replace(']]>', ']]]]><![CDATA[>', $s) . ']]>';
}

// ---------- Consulta ----------
try {
    // Eliminamos el GROUP BY innecesario que suele causar errores
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
    // Si falla, comentamos esto para debuggear si es necesario:
    // die($e->getMessage()); 
    $posts = [];
}

$baseUrl = rtrim(URLBASE, '/');
$selfUrl = $baseUrl . '/rss.php';
$feedTitle = NOMBRE_SITIO . ' - Noticias';
$feedDescription = 'Últimas publicaciones de ' . NOMBRE_SITIO;

echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
?>
<rss version="2.0"
     xmlns:atom="http://www.w3.org/2005/Atom"
     xmlns:media="http://search.yahoo.com/mrss/">
  <channel>
    <title><?= safe_xml_text($feedTitle) ?></title>
    <link><?= safe_xml_text($baseUrl) ?></link>
    <description><?= safe_xml_text($feedDescription) ?></description>
    <language>es-ES</language>
    <lastBuildDate><?= date(DATE_RSS) ?></lastBuildDate>
    <atom:link href="<​?= safe_xml_text($selfUrl) ?>" rel="self" type="application/rss+xml" />

<?php foreach ($posts as $post):
    $title = $post['title'] ?? '';
    $slug  = $post['slug'] ?? '';
    $cat   = $post['category_slug'] ?? 'noticias';
    
    // Construcción de URL: categoria/slug
    $itemUrl = $baseUrl . '/' . $cat . '/' . $slug;
    
    $pubTs   = strtotime((string)$post['created_at']);
    $pubDate = $pubTs ? date(DATE_RSS, $pubTs) : date(DATE_RSS);
    
    // Limpiar contenido para la descripción
    $desc = strip_tags((string)$post['content']);
    $desc = mb_substr($desc, 0, 300) . '...';

    $imgUrl = '';
    if (!empty($post['image'])) {
        // Usamos la lógica de tu helper img_url
        $img = $post['image'];
        if (filter_var($img, FILTER_VALIDATE_URL)) {
            $imgUrl = $img;
        } else {
            $imgUrl = $baseUrl . '/' . ltrim($img, '/');
        }
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
