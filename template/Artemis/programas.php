<?php
$page_title       = 'Programas | ' . NOMBRE_SITIO;
$page_description = 'Conoce todos los programas de ' . NOMBRE_SITIO . '.';
$currentPath      = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$page_canonical   = rtrim(URLBASE, '/') . '/' . ltrim($currentPath, '/');

$programs = [];
try {
    $programs = db()->query("
        SELECT * FROM programs WHERE status = 'active' ORDER BY title ASC
    ")->fetchAll();
} catch (Throwable $e) {}
?>

<section class="py-5" style="background: var(--dark); min-height: 60vh;">
    <div class="container">
        <div class="row mb-4 text-center">
            <div class="col-12">
                <h1 class="section-title" style="color: var(--text-color);">PROGRAMAS</h1>
                <p style="color: var(--text-muted);">Toda nuestra parrilla de contenido</p>
            </div>
        </div>

        <?php if (empty($programs)): ?>
            <div class="row"><div class="col-12 text-center py-5">
                <i class="fas fa-radio" style="font-size:3rem; color: var(--text-muted);"></i>
                <p style="color: var(--text-muted); margin-top:15px;">Próximamente nuestros programas.</p>
            </div></div>
        <?php else: ?>
        <div class="row">
            <?php foreach ($programs as $prog): ?>
            <div class="col-md-6 col-lg-4 mb-4">
                <a href="<?= URLBASE ?>/programas/<?= htmlspecialchars($prog['slug']) ?>/"
                   style="text-decoration:none;">
                    <div style="background: var(--dark-secondary); border-radius: 16px; overflow:hidden; height:100%; transition: transform .2s;"
                         onmouseover="this.style.transform='translateY(-4px)'"
                         onmouseout="this.style.transform='translateY(0)'">
                        <?php if (!empty($prog['image'])): ?>
                            <img src="<?= htmlspecialchars(rtrim(URLBASE,'/').'/'.$prog['image']) ?>"
                                 alt="<?= htmlspecialchars($prog['title']) ?>"
                                 style="width:100%; height:200px; object-fit:cover;">
                        <?php else: ?>
                            <div style="width:100%; height:200px; background: var(--dark); display:flex; align-items:center; justify-content:center;">
                                <i class="fas fa-microphone" style="font-size:3rem; color: var(--text-muted);"></i>
                            </div>
                        <?php endif; ?>
                        <div style="padding: 20px;">
                            <?php if (!empty($prog['category'])): ?>
                                <span style="background: var(--primary-color, #e21f0c); color:#fff; font-size:.75em; padding:3px 10px; border-radius:20px;">
                                    <?= htmlspecialchars($prog['category']) ?>
                                </span>
                            <?php endif; ?>
                            <h2 style="color: var(--text-color); margin: 10px 0 8px; font-size:1.1rem;">
                                <?= htmlspecialchars($prog['title']) ?>
                            </h2>
                            <?php if (!empty($prog['hosts'])): ?>
                                <p style="color: var(--text-muted); font-size:.85em; margin:0;">
                                    <i class="fas fa-user mr-1"></i><?= htmlspecialchars($prog['hosts']) ?>
                                </p>
                            <?php endif; ?>
                        </div>
                    </div>
                </a>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </div>
</section>
