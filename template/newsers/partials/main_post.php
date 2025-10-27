<?php
require_once __DIR__ . '/inc/config.php';

/* ===== Helpers ===== */
function safe_excerpt(string $html, int $words = 35): string {
    $text = strip_tags($html);
    $parts = preg_split('/\s+/', trim($text));
    if (count($parts) <= $words) return htmlspecialchars($text, ENT_QUOTES, 'UTF-8');
    return htmlspecialchars(implode(' ', array_slice($parts, 0, $words)) . '...', ENT_QUOTES, 'UTF-8');
}
function read_time_minutes(string $html, int $wpm = 200): int {
    $words = str_word_count(strip_tags($html));
    return max(1, (int)ceil($words / max(120, $wpm))); // conservador, mínimo 1
}
function post_url(array $p): string {
    // Ajusta a tu ruta real de detalle
    return URLBASE . '/noticias/' . urlencode($p['slug']);
}
function img_url(?string $filename): string {
    $base = URLBASE . '/public/images/blog/';
    return $filename ? ($base . rawurlencode($filename)) : (URLBASE . '/assets/img/placeholder-16x9.jpg');
}

/* ===== Consulta: últimas 8 con vistas ===== */
$pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $dbuser, $dbpass, [
  PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION,
  PDO::ATTR_DEFAULT_FETCH_MODE=>PDO::FETCH_ASSOC
]);

$sql = "
  SELECT p.id, p.title, p.slug, p.image, p.created_at, p.content,
         COALESCE(COUNT(v.id),0) AS views
  FROM blog_posts p
  LEFT JOIN blog_post_views v ON v.post_id = p.id
  WHERE p.status='published' AND p.deleted=0
  GROUP BY p.id
  ORDER BY p.created_at DESC
  LIMIT 8
";
$posts = $pdo->query($sql)->fetchAll();

$main   = $posts[0] ?? null;            // grande izquierda
$top    = $posts[1] ?? null;            // “Top Story” dentro de la caja
$right1 = $posts[2] ?? null;            // grande de la derecha
$rightList = array_slice($posts, 3);    // items pequeños de la derecha
?>

<!-- Main Post Section Start -->
<div class="container-fluid py-5">
  <div class="container py-5">
    <div class="row g-4">
      <div class="col-lg-7 col-xl-8 mt-0">

        <?php if ($main): ?>
        <div class="position-relative overflow-hidden rounded">
          <a href="<?= post_url($main) ?>">
            <img src="<?= img_url($main['image']) ?>" class="img-fluid rounded img-zoomin w-100" alt="<?= htmlspecialchars($main['title']) ?>">
          </a>
          <div class="d-flex justify-content-center px-4 position-absolute flex-wrap" style="bottom: 10px; left: 0;">
            <a href="<?= post_url($main) ?>" class="text-white me-3 link-hover">
              <i class="fa fa-clock"></i>
              <?= str_pad(read_time_minutes($main['content']), 2, '0', STR_PAD_LEFT) ?> minute read
            </a>
            <span class="text-white me-3 link-hover">
              <i class="fa fa-eye"></i> <?= number_format((int)$main['views'], 0, ',', '.') ?> Views
            </span>
            <!-- Comentarios/Share: ajusta si tienes esos contadores -->
            <!-- <span class="text-white me-3 link-hover"><i class="fa fa-comment-dots"></i> 05 Comment</span> -->
            <!-- <span class="text-white link-hover"><i class="fa fa-arrow-up"></i> 1.5k Share</span> -->
          </div>
        </div>

        <div class="border-bottom py-3">
          <a href="<?= post_url($main) ?>" class="display-4 text-dark mb-0 link-hover">
            <?= htmlspecialchars($main['title']) ?>
          </a>
        </div>
        <p class="mt-3 mb-4">
          <?= safe_excerpt($main['content'], 40) ?>
        </p>
        <?php endif; ?>

        <?php if ($top): ?>
        <div class="bg-light p-4 rounded">
          <div class="news-2">
            <h3 class="mb-4">Top Story</h3>
          </div>
          <div class="row g-4 align-items-center">
            <div class="col-md-6">
              <div class="rounded overflow-hidden">
                <a href="<?= post_url($top) ?>">
                  <img src="<?= img_url($top['image']) ?>" class="img-fluid rounded img-zoomin w-100" alt="<?= htmlspecialchars($top['title']) ?>">
                </a>
              </div>
            </div>
            <div class="col-md-6">
              <div class="d-flex flex-column">
                <a href="<?= post_url($top) ?>" class="h3"><?= htmlspecialchars($top['title']) ?></a>
                <p class="mb-0 fs-5">
                  <i class="fa fa-clock"></i>
                  <?= str_pad(read_time_minutes($top['content']), 2, '0', STR_PAD_LEFT) ?> minute read
                </p>
                <p class="mb-0 fs-5">
                  <i class="fa fa-eye"></i>
                  <?= number_format((int)$top['views'], 0, ',', '.') ?> Views
                </p>
              </div>
            </div>
          </div>
        </div>
        <?php endif; ?>

      </div>

      <div class="col-lg-5 col-xl-4">
        <div class="bg-light rounded p-4 pt-0">
          <div class="row g-4">

            <?php if ($right1): ?>
            <div class="col-12">
              <div class="rounded overflow-hidden">
                <a href="<?= post_url($right1) ?>">
                  <img src="<?= img_url($right1['image']) ?>" class="img-fluid rounded img-zoomin w-100" alt="<?= htmlspecialchars($right1['title']) ?>">
                </a>
              </div>
            </div>
            <div class="col-12">
              <div class="d-flex flex-column">
                <a href="<?= post_url($right1) ?>" class="h4 mb-2"><?= htmlspecialchars($right1['title']) ?></a>
                <p class="fs-5 mb-0">
                  <i class="fa fa-clock"></i>
                  <?= str_pad(read_time_minutes($right1['content']), 2, '0', STR_PAD_LEFT) ?> minute read
                </p>
                <p class="fs-5 mb-0">
                  <i class="fa fa-eye"></i>
                  <?= number_format((int)$right1['views'], 0, ',', '.') ?> Views
                </p>
              </div>
            </div>
            <?php endif; ?>

            <?php foreach ($rightList as $i => $p): ?>
            <div class="col-12">
              <div class="row g-4 align-items-center">
                <div class="col-5">
                  <div class="overflow-hidden rounded">
                    <a href="<?= post_url($p) ?>">
                      <img src="<?= img_url($p['image']) ?>" class="img-zoomin img-fluid rounded w-100" alt="<?= htmlspecialchars($p['title']) ?>">
                    </a>
                  </div>
                </div>
                <div class="col-7">
                  <div class="features-content d-flex flex-column">
                    <a href="<?= post_url($p) ?>" class="h6"><?= htmlspecialchars($p['title']) ?></a>
                    <small>
                      <i class="fa fa-clock"></i>
                      <?= str_pad(read_time_minutes($p['content']), 2, '0', STR_PAD_LEFT) ?> minute read
                    </small>
                    <small>
                      <i class="fa fa-eye"></i>
                      <?= number_format((int)$p['views'], 0, ',', '.') ?> Views
                    </small>
                  </div>
                </div>
              </div>
            </div>
            <?php endforeach; ?>

          </div>
        </div>
      </div>

    </div>
  </div>
</div>
<!-- Main Post Section End -->
