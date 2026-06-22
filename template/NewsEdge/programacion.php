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
<!-- Breadcrumb Area Start Here -->
<section class="breadcrumbs-area" style="background-image: url('<?= URLBASE ?>/template/NewsEdge/img/banner/breadcrumbs-banner.jpg');">
    <div class="container">
        <div class="breadcrumbs-content">
            <h1>Programación</h1>
            <ul>
                <li><a href="<?= URLBASE ?>"><?= t_theme('theme_inicio') ?></a><i class="fa fa-angle-right" aria-hidden="true"></i></li>
                <li>Programación</li>
            </ul>
        </div>
    </div>
</section>
<!-- Breadcrumb Area End Here -->

<section class="bg-body section-space-less30">
    <div class="container">
        <!-- Tabs días -->
        <div class="mb-30" style="display:flex; flex-wrap:wrap; gap:8px;">
            <?php foreach ($days as $day): ?>
            <button onclick="showDay('<?= $day ?>')"
                    id="tab-<?= $day ?>"
                    style="padding:8px 18px; border-radius:4px; border:2px solid var(--primary);
                           background:<?= $day === $activeDay ? 'var(--primary)' : 'transparent' ?>;
                           color:<?= $day === $activeDay ? '#fff' : '#333' ?>;
                           cursor:pointer; font-weight:600; transition:.2s;">
                <?= $dayLabels[$day] ?>
            </button>
            <?php endforeach; ?>
        </div>

        <?php foreach ($days as $day): ?>
        <div id="day-<?= $day ?>" style="display:<?= $day === $activeDay ? 'block' : 'none' ?>;">
            <?php if (empty($allSlots[$day])): ?>
                <p class="description-body-light text-center">Sin programación registrada para este día.</p>
            <?php else: ?>
                <div class="row">
                    <?php foreach ($allSlots[$day] as $slot):
                        $isLive = ($nowTime >= $slot['start_time'] && $nowTime < $slot['end_time'] && $day === $activeDay);
                    ?>
                    <div class="col-md-6 col-lg-4 mb-20">
                        <a href="<?= URLBASE ?>/programas/<?= htmlspecialchars($slot['program_slug']) ?>/" style="text-decoration:none; color:inherit;">
                            <div class="item-box-light-md p-20" style="border-radius:4px; height:100%; <?= $isLive ? 'border-left:4px solid var(--primary);' : '' ?>">
                                <div style="display:flex; justify-content:space-between; align-items:flex-start; margin-bottom:8px;">
                                    <strong style="color:var(--primary);">
                                        <?= htmlspecialchars(substr($slot['start_time'],0,5)) ?> — <?= htmlspecialchars(substr($slot['end_time'],0,5)) ?>
                                    </strong>
                                    <?php if ($isLive): ?>
                                        <span style="background:var(--primary); color:#fff; font-size:.7rem; font-weight:700; padding:3px 10px; border-radius:4px; animation:pulse 1.5s infinite;">● EN VIVO</span>
                                    <?php endif; ?>
                                </div>
                                <h6 class="title-semibold-dark mb-5"><?= htmlspecialchars($slot['program_title']) ?></h6>
                                <?php if (!empty($slot['host'])): ?>
                                    <small class="description-body-light"><i class="fa fa-user" style="margin-right:4px;"></i><?= htmlspecialchars($slot['host']) ?></small>
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
</section>
<style>
@keyframes pulse { 0%,100%{opacity:1} 50%{opacity:.5} }
</style>
<script>
function showDay(day) {
    ['lunes','martes','miercoles','jueves','viernes','sabado','domingo'].forEach(function(d) {
        document.getElementById('day-' + d).style.display = 'none';
        var tab = document.getElementById('tab-' + d);
        tab.style.background = 'transparent';
        tab.style.color = '#333';
    });
    document.getElementById('day-' + day).style.display = 'block';
    var t = document.getElementById('tab-' + day);
    t.style.background = 'var(--primary)';
    t.style.color = '#fff';
}
</script>
