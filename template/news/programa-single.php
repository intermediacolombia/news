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
<div class="container-fluid py-3">
    <div class="container-bk">
        <div class="row mb-4">
            <div class="col-md-4">
                <?php if (!empty($program['image'])): ?>
                    <img src="<?= htmlspecialchars(rtrim(URLBASE,'/').'/'.$program['image']) ?>"
                         alt="<?= htmlspecialchars($program['title']) ?>"
                         class="img-fluid" style="border-radius:8px;">
                <?php else: ?>
                    <div style="height:280px; background:#f8f9fa; border-radius:8px; display:flex; align-items:center; justify-content:center;">
                        <i class="fas fa-microphone fa-4x text-muted"></i>
                    </div>
                <?php endif; ?>
            </div>
            <div class="col-md-8">
                <?php if (!empty($program['category'])): ?>
                    <span class="badge badge-primary mb-2"><?= htmlspecialchars($program['category']) ?></span>
                <?php endif; ?>
                <h1><?= htmlspecialchars($program['title']) ?></h1>
                <?php if (!empty($program['hosts'])): ?>
                    <p class="text-muted"><i class="fas fa-user" style="margin-right:6px;"></i><?= htmlspecialchars($program['hosts']) ?></p>
                <?php endif; ?>
                <?php if (!empty($program['description'])): ?>
                    <div style="line-height:1.8;"><?= nl2br(htmlspecialchars($program['description'])) ?></div>
                <?php endif; ?>
            </div>
        </div>

        <?php if (!empty($schedules)): ?>
        <div class="bg-light p-4" style="border-radius:8px;">
            <h4 class="mb-3"><i class="fas fa-clock" style="margin-right:8px; color:var(--primary);"></i> Horarios</h4>
            <table class="table table-hover mb-0">
                <thead class="thead-light">
                    <tr>
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
</div>
