<?php
declare(strict_types=1);

// rss.php — coloca este archivo en la raíz pública
require_once __DIR__ . '/../../inc/config.php'; // ajusta ruta si es necesario

// Evitar salida previa
if (ob_get_level()) {
    @ob_end_clean();
}

// Cabecera XML
header('Content-Type: application/rss+xml; charset=UTF-8');

// ---------- Helpers ----------
function safe_xml_text(string $s): string {
    $s = str_replace("\0", '', $s);
    return htmlspecialchars($s, ENT_XML1 | ENT_QUOTES, 'UTF-8');
}
function cdata(string $s): string {
    return '<![CDATA[' . str_replace(']]>', ']]]]><![CDATA[>', $s) . ']]>';
}
function build_base_url(): string {
    if (defined('URLBASE') && !empty(URLBASE)) {
        return rtrim(URLBASE, '/');
    }
    $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
           || (!empty($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https')
           ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'] ?? ($_SERVER['SERVER_NAME'] ?? 'localhost');
    return $scheme . '://' . rtrim($host, '/');
}
function current_full_url(): string {
    $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
           || (!empty($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https')
           ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'] ?? ($_SERVER['SERVER_NAME'] ?? 'localhost');
    $uri  = $_SERVER['REQUEST_URI'] ?? '/rss.php';
    return $scheme . '://' . $host . $uri;
}

// ---------- Consulta últimas 30 entradas ----------
try {
    $sql = "SELECT p.id, p.title, p.slug, p.content, p.image, p.created_at,
                   COALESCE(c.slug, '') AS category_slug
            FROM blog_posts p
            LEFT JOIN blog_post_category pc ON pc.post_id = p.id
            LEFT JOIN blog_categories c ON c.id = pc.category_id
            WHERE p.status = :status AND p.deleted = 0
            GROUP BY p.id
            ORDER BY p.created_at DESC
            LIMIT 30";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([':status' => 'published']);
    $posts = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Throwable $e) {
    $posts = [];
}

// ---------- Datos del feed ----------
$baseUrl = build_base_url();
$selfUrl = current_full_url();
$feedTitle = 'Mi sitio - Noticias';
$feedDescription = 'Últimas publicaciones';

// ---------- Emitir XML ----------
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

    <atom:link href="<?= safe_xml_text($selfUrl) ?>"
               rel="self"
               type="application/rss+xml" />

<?php foreach ($posts as $post):
    $title = trim((string)($post['title'] ?? ''));
    $slug  = trim((string)($post['slug'] ?? ''));
    $cat   = trim((string)($post['category_slug'] ?? ''));
    $img   = trim((string)($post['image'] ?? ''));
    $itemPath = [];
    if ($cat !== '') $itemPath[] = rawurlencode($cat);
    if ($slug !== '') $itemPath[] = rawurlencode($slug);
    $itemUrl = $baseUrl . '/' . implode('/', $itemPath);
    $pubTs   = strtotime((string)($post['created_at'] ?? ''));
    $pubDate = $pubTs ? date(DATE_RSS, $pubTs) : date(DATE_RSS);
    $desc    = mb_substr(strip_tags((string)($post['content'] ?? '')), 0, 300);

    // Imagen destacada
    $imgUrl = '';
    $length = 0;
    $mime   = 'image/jpeg';
    if ($img !== '') {
        $imgUrl = $baseUrl . '/' . ltrim($img, '/');
        // calcular path local para filesize y mime
        $localPath = $_SERVER['DOCUMENT_ROOT'] . '/' . ltrim($img, '/');
        if (is_file($localPath)) {
            $length = filesize($localPath) ?: 0;
            $mime = mime_content_type($localPath) ?: $mime;
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
      <enclosure url="<?= safe_xml_text($imgUrl) ?>" length="<?= $length ?>" type="<?= safe_xml_text($mime) ?>" />
      <media:content url="<?= safe_xml_text($imgUrl) ?>" medium="image" />
<?php endif; ?>
    </item>
<?php endforeach; ?>

  </channel>
</rss>
<?php
exit;

