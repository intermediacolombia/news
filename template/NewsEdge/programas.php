<?php
$page_title       = 'Programas | ' . NOMBRE_SITIO;
$page_description = 'Conoce todos los programas de ' . NOMBRE_SITIO . '.';
$currentPath      = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$page_canonical   = rtrim(URLBASE, '/') . '/' . ltrim($currentPath, '/');

$programs = [];
try {
    $programs = db()->query("SELECT * FROM programs WHERE status = 'active' ORDER BY title ASC")->fetchAll();
} catch (Throwable $e) {}
?>
<!-- Breadcrumb Area Start Here -->
<section class="breadcrumbs-area" style="background-image: url('<?= URLBASE ?>/template/NewsEdge/img/banner/breadcrumbs-banner.jpg');">
    <div class="container">
        <div class="breadcrumbs-content">
            <h1>Programas</h1>
            <ul>
                <li><a href="<?= URLBASE ?>"><?= t_theme('theme_inicio') ?></a><i class="fa fa-angle-right" aria-hidden="true"></i></li>
                <li>Programas</li>
            </ul>
        </div>
    </div>
</section>
<!-- Breadcrumb Area End Here -->

<section class="bg-body section-space-less30">
    <div class="container">
        <?php if (empty($programs)): ?>
            <div class="text-center" style="padding:60px 0;">
                <i class="fa fa-radio" style="font-size:3rem; color:#ccc; margin-bottom:15px;"></i>
                <p class="description-body-light">Próximamente nuestros programas.</p>
            </div>
        <?php else: ?>
        <div class="row">
            <?php foreach ($programs as $prog): ?>
            <div class="col-md-6 col-lg-4 mb-30">
                <a href="<?= URLBASE ?>/programas/<?= htmlspecialchars($prog['slug']) ?>/" style="text-decoration:none; color:inherit;">
                    <div class="item-box-light-md item-shadow-1" style="border-radius:4px; overflow:hidden; transition:transform .2s;"
                         onmouseover="this.style.transform='translateY(-4px)'"
                         onmouseout="this.style.transform='translateY(0)'">
                        <?php if (!empty($prog['image'])): ?>
                            <img src="<?= htmlspecialchars(rtrim(URLBASE,'/').'/'.$prog['image']) ?>"
                                 alt="<?= htmlspecialchars($prog['title']) ?>"
                                 style="width:100%; height:200px; object-fit:cover;">
                        <?php else: ?>
                            <div style="height:200px; background:#f5f5f5; display:flex; align-items:center; justify-content:center;">
                                <i class="fa fa-microphone" style="font-size:3rem; color:#ccc;"></i>
                            </div>
                        <?php endif; ?>
                        <div class="p-20">
                            <?php if (!empty($prog['category'])): ?>
                                <span style="background:var(--primary); color:#fff; font-size:.75em; padding:3px 10px; border-radius:4px; margin-bottom:8px; display:inline-block;">
                                    <?= htmlspecialchars($prog['category']) ?>
                                </span>
                            <?php endif; ?>
                            <h5 class="title-semibold-dark mb-10"><?= htmlspecialchars($prog['title']) ?></h5>
                            <?php if (!empty($prog['hosts'])): ?>
                                <small class="description-body-light"><i class="fa fa-user" style="margin-right:4px;"></i><?= htmlspecialchars($prog['hosts']) ?></small>
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
