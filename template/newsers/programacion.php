<?php
$page_title       = 'Programación | ' . NOMBRE_SITIO;
$page_description = 'Parrilla de programación de ' . NOMBRE_SITIO . '.';
$currentPath      = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$page_canonical   = rtrim(URLBASE, '/') . '/' . ltrim($currentPath, '/');

$days      = ['lunes','martes','miercoles','jueves','viernes','sabado','domingo'];
$dayLabels = ['lunes'=>'Lunes','martes'=>'Martes','miercoles'=>'Miércoles','jueves'=>'Jueves','viernes'=>'Viernes','sabado'=>'Sábado','domingo'=>'Domingo'];
$todayMap  = [1=>'lunes',2=>'martes',3=>'miercoles',4=>'jueves',5=>'viernes',6=>'sabado',7=>'domingo'];
$activeDay = $todayMap[(int)date('N')] ?? 'lunes';
$nowTime   = date('H:i:s');

$allSlots = [];
try {
    $rows = db()->query("
        SELECT s.*, p.title AS program_title, p.slug AS program_slug
        FROM schedules s
        INNER JOIN programs p ON p.id = s.program_id
        WHERE s.status = 'active' AND p.status = 'active'
        ORDER BY FIELD(s.day_of_week,'lunes','martes','miercoles','jueves','viernes','sabado','domingo'), s.start_time
    ")->fetchAll();
    foreach ($rows as $row) {
        $allSlots[$row['day_of_week']][] = $row;
    }
} catch (Throwable $e) {}
?>
<div class="container-fluid py-5">
    <div class="container py-3">
        <h1 class="mb-4">Programación</h1>

        <!-- Tabs días -->
        <div class="d-flex flex-wrap gap-2 mb-4">
            <?php foreach ($days as $day): ?>
            <button onclick="showDay('<?= $day ?>')"
                    id="tab-<?= $day ?>"
                    class="btn <?= $day === $activeDay ? 'btn-primary' : 'btn-outline-secondary' ?> btn-sm">
                <?= $dayLabels[$day] ?>
            </button>
            <?php endforeach; ?>
        </div>

        <?php foreach ($days as $day): ?>
        <div id="day-<?= $day ?>" style="display:<?= $day === $activeDay ? 'block' : 'none' ?>;">
            <?php if (empty($allSlots[$day])): ?>
                <p class="text-muted text-center py-4">Sin programación registrada para este día.</p>
            <?php else: ?>
                <div class="row g-3">
                    <?php foreach ($allSlots[$day] as $slot):
                        $isLive = ($nowTime >= $slot['start_time'] && $nowTime < $slot['end_time'] && $day === $activeDay);
                    ?>
                    <div class="col-md-6 col-lg-4">
                        <a href="<?= URLBASE ?>/programas/<?= htmlspecialchars($slot['program_slug']) ?>/" class="text-decoration-none text-dark">
                            <div class="bg-light p-3 rounded h-100" style="border:<?= $isLive ? '2px solid var(--primary)' : '1px solid #dee2e6' ?>;">
                                <div class="d-flex justify-content-between align-items-start mb-2">
                                    <strong style="color:var(--primary);">
                                        <?= htmlspecialchars(substr($slot['start_time'],0,5)) ?> — <?= htmlspecialchars(substr($slot['end_time'],0,5)) ?>
                                    </strong>
                                    <?php if ($isLive): ?>
                                        <span class="badge bg-danger" style="animation:pulse 1.5s infinite;">● EN VIVO</span>
                                    <?php endif; ?>
                                </div>
                                <h6 class="mb-1"><?= htmlspecialchars($slot['program_title']) ?></h6>
                                <?php if (!empty($slot['host'])): ?>
                                    <small class="text-muted"><i class="fas fa-user me-1"></i><?= htmlspecialchars($slot['host']) ?></small>
                                <?php endif; ?>
                            </div>
                        </a>
                    </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
        <?php endforeach; ?>
    </div>
</div>
<style>@keyframes pulse { 0%,100%{opacity:1} 50%{opacity:.5} }</style>
<script>
function showDay(day) {
    ['lunes','martes','miercoles','jueves','viernes','sabado','domingo'].forEach(function(d) {
        document.getElementById('day-' + d).style.display = 'none';
        document.getElementById('tab-' + d).className = 'btn btn-outline-secondary btn-sm';
    });
    document.getElementById('day-' + day).style.display = 'block';
    document.getElementById('tab-' + day).className = 'btn btn-primary btn-sm';
}
</script>
