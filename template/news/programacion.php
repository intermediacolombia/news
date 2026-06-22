<?php
$page_title       = 'Programación | ' . NOMBRE_SITIO;
$page_description = 'Parrilla de programación de ' . NOMBRE_SITIO . '. Consulta los horarios de todos nuestros programas.';
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
        SELECT s.*, p.title AS program_title, p.slug AS program_slug, p.image AS program_image
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
<div class="container-fluid py-3">
    <div class="container-bk">
        <div class="bg-light py-2 px-4 mb-3">
            <h3 class="m-0">Programación</h3>
        </div>

        <!-- Tabs días -->
        <div class="mb-3" style="display:flex; flex-wrap:wrap; gap:6px;">
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
                <div class="row">
                    <?php foreach ($allSlots[$day] as $slot):
                        $isLive = ($nowTime >= $slot['start_time'] && $nowTime < $slot['end_time'] && $day === $activeDay);
                    ?>
                    <div class="col-md-6 col-lg-4 mb-3">
                        <a href="<?= URLBASE ?>/programas/<?= htmlspecialchars($slot['program_slug']) ?>/" style="text-decoration:none; color:inherit;">
                            <div class="bg-light p-3" style="border-radius:8px; height:100%; border:<?= $isLive ? '2px solid var(--primary)' : '1px solid #dee2e6' ?>;">
                                <div style="display:flex; justify-content:space-between; align-items:flex-start; margin-bottom:8px;">
                                    <strong style="color:var(--primary);">
                                        <?= htmlspecialchars(substr($slot['start_time'],0,5)) ?> — <?= htmlspecialchars(substr($slot['end_time'],0,5)) ?>
                                    </strong>
                                    <?php if ($isLive): ?>
                                        <span class="badge badge-danger" style="animation:pulse 1.5s infinite;">● EN VIVO</span>
                                    <?php endif; ?>
                                </div>
                                <h6 class="mb-1"><?= htmlspecialchars($slot['program_title']) ?></h6>
                                <?php if (!empty($slot['host'])): ?>
                                    <small class="text-muted"><i class="fas fa-user" style="margin-right:4px;"></i><?= htmlspecialchars($slot['host']) ?></small>
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
<style>
@keyframes pulse { 0%,100%{opacity:1} 50%{opacity:.5} }
</style>
<script>
function showDay(day) {
    ['lunes','martes','miercoles','jueves','viernes','sabado','domingo'].forEach(function(d) {
        document.getElementById('day-' + d).style.display = 'none';
        var tab = document.getElementById('tab-' + d);
        tab.className = 'btn btn-outline-secondary btn-sm';
    });
    document.getElementById('day-' + day).style.display = 'block';
    document.getElementById('tab-' + day).className = 'btn btn-primary btn-sm';
}
</script>
