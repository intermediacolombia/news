<?php
/* ===== Helper para truncar texto ===== */
function truncate_text(string $text, int $maxLength = 80): string {
    $text = htmlspecialchars($text, ENT_QUOTES, 'UTF-8');
    if (mb_strlen($text) <= $maxLength) {
        return $text;
    }
    return mb_substr($text, 0, $maxLength) . '...';
}

/* ===== Consulta: Latest News (últimas 10 noticias) ===== */
$sqlLatest = "
  SELECT p.id, p.title, p.slug, p.image, p.created_at, p.author,
         c.name AS category_name, c.slug AS category_slug
  FROM blog_posts p
  LEFT JOIN blog_post_category pc ON pc.post_id = p.id
  LEFT JOIN blog_categories c ON c.id = pc.category_id
  WHERE p.status = 'published' AND p.deleted = 0
  GROUP BY p.id, p.title, p.slug, p.image, p.created_at, p.author, c.name, c.slug
  ORDER BY p.created_at DESC
  LIMIT 10
";
$latestNews = $pdo->query($sqlLatest)->fetchAll();
?>

<!-- Latest News Start -->
<div class="container-fluid latest-news py-5">
    <div class="container py-5">
        <h2 class="mb-4">Últimas Noticias</h2>
        <div class="latest-news-carousel owl-carousel">
            <?php foreach ($latestNews as $news): ?>
            <div class="latest-news-item">
                <div class="bg-light rounded">
                    <div class="rounded-top overflow-hidden" style="height: 300px;">
                        <a href="<?= URLBASE ?>/<?= htmlspecialchars($news['category_slug']) ?>/<?= htmlspecialchars($news['slug']) ?>/">
                            <img src="<?= img_url($news['image']) ?>" 
                                 class="img-zoomin img-fluid rounded-top w-100 h-100" 
                                 style="object-fit: cover; object-position: center;"
                                 alt="<?= htmlspecialchars($news['title']) ?>">
                        </a>
                    </div>
                    <div class="d-flex flex-column p-4">
                        <a href="<?= URLBASE ?>/<?= htmlspecialchars($news['category_slug']) ?>/<?= htmlspecialchars($news['slug']) ?>/" 
                           class="h4" 
                           title="<?= htmlspecialchars($news['title']) ?>">
                            <?= truncate_text($news['title'], 80) ?>
                        </a>
                        <div class="d-flex justify-content-between">
                            <?php if (!empty($news['author'])): ?>
                            <span class="small text-body">por <?= htmlspecialchars($news['author']) ?></span>
                            <?php else: ?>
                            <span class="small text-body">por <?= htmlspecialchars($sys['site_name'] ?? 'Admin') ?></span>
                            <?php endif; ?>
                            
                            <small class="text-body d-block">
                                <i class="fas fa-calendar-alt me-1"></i>
                                <?= fecha_espanol(date("F d, Y", strtotime($news['created_at']))) ?>
                            </small>
                        </div>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>
<!-- Latest News End -->