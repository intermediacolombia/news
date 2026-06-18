<?php
$page_title       = 'Programación | ' . NOMBRE_SITIO;
$page_description = 'Parrilla de programación de ' . NOMBRE_SITIO . '. Consulta los horarios de todos nuestros programas.';
$currentPath      = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$page_canonical   = rtrim(URLBASE, '/') . '/' . ltrim($currentPath, '/');

$days = ['lunes','martes','miercoles','jueves','viernes','sabado','domingo'];
$dayLabels = ['lunes'=>'Lunes','martes'=>'Martes','miercoles'=>'Miércoles','jueves'=>'Jueves','viernes'=>'Viernes','sabado'=>'Sábado','domingo'=>'Domingo'];

// Día activo: día actual en español
$todayMap = [1=>'lunes',2=>'martes',3=>'miercoles',4=>'jueves',5=>'viernes',6=>'sabado',7=>'domingo'];
$activeDay = $todayMap[(int)date('N')] ?? 'lunes';

// Hora actual para calcular "en vivo"
$nowTime = date('H:i:s');

// Cargar todos los slots activos con nombre del programa
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

<section class="py-5" style="background: var(--dark); min-height: 60vh;">
    <div class="container">
        <div class="row mb-4 text-center">
            <div class="col-12">
                <h1 class="section-title" style="color: var(--text-color);">PROGRAMACIÓN</h1>
                <p style="color: var(--text-muted);">Parrilla semanal — en vivo todos los días</p>
            </div>
        </div>

        <!-- Tabs días -->
        <div class="row mb-3">
            <div class="col-12">
                <div style="display:flex; flex-wrap:wrap; gap:8px; justify-content:center;">
                    <?php foreach ($days as $day): ?>
                    <button onclick="showDay('<?= $day ?>')"
                            id="tab-<?= $day ?>"
                            style="padding:8px 18px; border-radius:30px; border:2px solid var(--primary-color, #e21f0c);
                                   background:<?= $day === $activeDay ? 'var(--primary-color,#e21f0c)' : 'transparent' ?>;
                                   color:<?= $day === $activeDay ? '#fff' : 'var(--text-color)' ?>;
                                   cursor:pointer; font-weight:600; transition:.2s; font-size:.9rem;">
                        <?= $dayLabels[$day] ?>
                    </button>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

        <!-- Contenido por día -->
        <?php foreach ($days as $day): ?>
        <div id="day-<?= $day ?>" style="display:<?= $day === $activeDay ? 'block' : 'none' ?>;">
            <?php if (empty($allSlots[$day])): ?>
                <div class="text-center py-5">
                    <p style="color: var(--text-muted);">Sin programación registrada para este día.</p>
                </div>
            <?php else: ?>
                <div class="row">
                    <?php foreach ($allSlots[$day] as $slot):
                        $isLive = ($nowTime >= $slot['start_time'] && $nowTime < $slot['end_time'] && $day === $activeDay);
                    ?>
                    <div class="col-md-6 col-lg-4 mb-3">
                        <a href="<?= URLBASE ?>/programas/<?= htmlspecialchars($slot['program_slug']) ?>/"
                           style="text-decoration:none;">
                            <div style="background: var(--dark-secondary); border-radius:12px; padding:20px; height:100%;
                                        <?= $isLive ? 'border: 2px solid var(--primary-color,#e21f0c);' : '' ?>
                                        transition: transform .2s;"
                                 onmouseover="this.style.transform='translateY(-3px)'"
                                 onmouseout="this.style.transform='translateY(0)'">
                                <div style="display:flex; justify-content:space-between; align-items:flex-start; margin-bottom:10px;">
                                    <span style="color: var(--primary-color, #e21f0c); font-weight:700; font-size:1.1rem;">
                                        <?= htmlspecialchars(substr($slot['start_time'],0,5)) ?> — <?= htmlspecialchars(substr($slot['end_time'],0,5)) ?>
                                    </span>
                                    <?php if ($isLive): ?>
                                        <span style="background:#e21f0c; color:#fff; font-size:.7rem; font-weight:700;
                                                     padding:3px 10px; border-radius:20px; animation: pulse 1.5s infinite;">
                                            ● EN VIVO
                                        </span>
                                    <?php endif; ?>
                                </div>
                                <h3 style="color: var(--text-color); margin:0 0 6px; font-size:1rem;">
                                    <?= htmlspecialchars($slot['program_title']) ?>
                                </h3>
                                <?php if (!empty($slot['host'])): ?>
                                    <p style="color: var(--text-muted); font-size:.85em; margin:0;">
                                        <i class="fas fa-user mr-1"></i><?= htmlspecialchars($slot['host']) ?>
                                    </p>
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
@keyframes pulse {
    0%, 100% { opacity: 1; }
    50%       { opacity: 0.6; }
}
</style>

<script>
function showDay(day) {
    ['lunes','martes','miercoles','jueves','viernes','sabado','domingo'].forEach(function(d) {
        document.getElementById('day-' + d).style.display = 'none';
        var tab = document.getElementById('tab-' + d);
        tab.style.background = 'transparent';
        tab.style.color = 'var(--text-color)';
    });
    document.getElementById('day-' + day).style.display = 'block';
    var activeTab = document.getElementById('tab-' + day);
    activeTab.style.background = 'var(--primary-color, #e21f0c)';
    activeTab.style.color = '#fff';
}
</script>
