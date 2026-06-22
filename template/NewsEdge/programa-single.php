<?php
$programSlug = $_GET['program_slug'] ?? '';
$program     = null;
$schedules   = [];

try {
    $stmt = db()->prepare("SELECT * FROM programs WHERE slug = ? AND status = 'active' LIMIT 1");
    $stmt->execute([$programSlug]);
    $program = $stmt->fetch();
} catch (Throwable $e) {}

if (!$program) {
    http_response_code(404);
    $errorFile = __DIR__ . '/404.php';
    if (file_exists($errorFile)) include $errorFile;
    else echo '<div style="text-align:center;padding:100px;"><h1>404</h1><p>Programa no encontrado</p><a href="' . URLBASE . '">Volver al inicio</a></div>';
    exit;
}

try {
    $stmt = db()->prepare("
        SELECT * FROM schedules WHERE program_id = ? AND status = 'active'
        ORDER BY FIELD(day_of_week,'lunes','martes','miercoles','jueves','viernes','sabado','domingo'), start_time
    ");
    $stmt->execute([$program['id']]);
    $schedules = $stmt->fetchAll();
} catch (Throwable $e) {}

$page_title       = htmlspecialchars($program['title']) . ' | ' . NOMBRE_SITIO;
$page_description = !empty($program['description']) ? mb_substr(strip_tags($program['description']), 0, 160) : 'Programa de ' . NOMBRE_SITIO;
$currentPath      = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$page_canonical   = rtrim(URLBASE, '/') . '/' . ltrim($currentPath, '/');
?>
<!-- Breadcrumb Area Start Here -->
<section class="breadcrumbs-area" style="background-image: url('<?= URLBASE ?>/template/NewsEdge/img/banner/breadcrumbs-banner.jpg');">
    <div class="container">
        <div class="breadcrumbs-content">
            <h1><?= htmlspecialchars($program['title']) ?></h1>
            <ul>
                <li><a href="<?= URLBASE ?>"><?= t_theme('theme_inicio') ?></a><i class="fa fa-angle-right" aria-hidden="true"></i></li>
                <li><a href="<?= URLBASE ?>/programas/">Programas</a><i class="fa fa-angle-right" aria-hidden="true"></i></li>
                <li><?= htmlspecialchars($program['title']) ?></li>
            </ul>
        </div>
    </div>
</section>
<!-- Breadcrumb Area End Here -->

<section class="bg-body section-space-less30">
    <div class="container">
        <div class="row align-items-center mb-30">
            <div class="col-lg-4 mb-30">
                <?php if (!empty($program['image'])): ?>
                    <img src="<?= htmlspecialchars(rtrim(URLBASE,'/').'/'.$program['image']) ?>"
                         alt="<?= htmlspecialchars($program['title']) ?>"
                         class="img-fluid" style="border-radius:8px; width:100%;">
                <?php else: ?>
                    <div style="height:280px; background:#f5f5f5; border-radius:8px; display:flex; align-items:center; justify-content:center;">
                        <i class="fa fa-microphone" style="font-size:4rem; color:#ccc;"></i>
                    </div>
                <?php endif; ?>
            </div>
            <div class="col-lg-8">
                <?php if (!empty($program['category'])): ?>
                    <span style="background:var(--primary); color:#fff; font-size:.8em; padding:4px 14px; border-radius:4px; margin-bottom:10px; display:inline-block;">
                        <?= htmlspecialchars($program['category']) ?>
                    </span>
                <?php endif; ?>
                <h1 class="title-semibold-dark size-xl mb-10"><?= htmlspecialchars($program['title']) ?></h1>
                <?php if (!empty($program['hosts'])): ?>
                    <p class="description-body-dark mb-15"><i class="fa fa-user" style="margin-right:6px; color:var(--primary);"></i><?= htmlspecialchars($program['hosts']) ?></p>
                <?php endif; ?>
                <?php if (!empty($program['description'])): ?>
                    <div class="description-body-dark" style="line-height:1.8;"><?= nl2br(htmlspecialchars($program['description'])) ?></div>
                <?php endif; ?>
            </div>
        </div>

        <?php if (!empty($schedules)): ?>
        <div class="item-box-light-md p-30" style="border-radius:4px;">
            <h4 class="title-semibold-dark size-lg mb-20">
                <i class="fa fa-clock-o" style="margin-right:8px; color:var(--primary);"></i> Horarios
            </h4>
            <table class="table mb-0">
                <thead>
                    <tr style="background:var(--primary); color:#fff;">
                        <th>Día</th>
                        <th>Inicio</th>
                        <th>Fin</th>
                        <?php if (!empty(array_filter(array_column($schedules, 'host')))): ?>
                        <th>Conductor</th>
                        <?php endif; ?>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($schedules as $slot): ?>
                    <tr>
                        <td><?= ucfirst(htmlspecialchars($slot['day_of_week'])) ?></td>
                        <td><?= htmlspecialchars(substr($slot['start_time'], 0, 5)) ?></td>
                        <td><?= htmlspecialchars(substr($slot['end_time'], 0, 5)) ?></td>
                        <?php if (!empty(array_filter(array_column($schedules, 'host')))): ?>
                        <td><?= htmlspecialchars($slot['host'] ?? '') ?></td>
                        <?php endif; ?>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>
    </div>
</section>
