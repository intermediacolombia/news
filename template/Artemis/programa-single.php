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

<section class="py-5" style="background: var(--dark);">
    <div class="container">
        <div class="row align-items-center mb-5">
            <div class="col-lg-5 mb-4 mb-lg-0">
                <?php if (!empty($program['image'])): ?>
                    <img src="<?= htmlspecialchars(rtrim(URLBASE,'/').'/'.$program['image']) ?>"
                         alt="<?= htmlspecialchars($program['title']) ?>"
                         style="width:100%; border-radius:16px; object-fit:cover; max-height:360px;">
                <?php else: ?>
                    <div style="width:100%; height:300px; background: var(--dark-secondary); border-radius:16px; display:flex; align-items:center; justify-content:center;">
                        <i class="fas fa-microphone" style="font-size:4rem; color: var(--text-muted);"></i>
                    </div>
                <?php endif; ?>
            </div>
            <div class="col-lg-7">
                <?php if (!empty($program['category'])): ?>
                    <span style="background: var(--primary-color, #e21f0c); color:#fff; font-size:.8em; padding:4px 14px; border-radius:20px;">
                        <?= htmlspecialchars($program['category']) ?>
                    </span>
                <?php endif; ?>
                <h1 style="color: var(--text-color); margin: 14px 0 10px; font-size:2rem;">
                    <?= htmlspecialchars($program['title']) ?>
                </h1>
                <?php if (!empty($program['hosts'])): ?>
                    <p style="color: var(--text-muted);">
                        <i class="fas fa-user mr-2"></i><?= htmlspecialchars($program['hosts']) ?>
                    </p>
                <?php endif; ?>
                <?php if (!empty($program['description'])): ?>
                    <div style="color: var(--text-color); line-height:1.8; margin-top:16px;">
                        <?= nl2br(htmlspecialchars($program['description'])) ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <?php if (!empty($schedules)): ?>
        <div class="row">
            <div class="col-12">
                <h2 style="color: var(--text-color); margin-bottom:20px; font-size:1.3rem;">
                    <i class="fas fa-clock mr-2" style="color: var(--primary-color, #e21f0c);"></i> Horarios
                </h2>
                <div style="background: var(--dark-secondary); border-radius:12px; overflow:hidden;">
                    <table class="table table-borderless mb-0">
                        <thead>
                            <tr style="background: var(--primary-color, #e21f0c); color:#fff;">
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
                            <tr style="color: var(--text-color); border-bottom: 1px solid rgba(255,255,255,.05);">
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
            </div>
        </div>
        <?php endif; ?>
    </div>
</section>
