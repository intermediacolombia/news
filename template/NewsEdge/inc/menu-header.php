<!--[if lt IE 8]>
<p class="browserupgrade">You are using an <strong>outdated</strong> browser.
<a href="http://browsehappy.com/">upgrade your browser</a></p>
<![endif]-->

<div id="wrapper">
<header>
<div id="header-layout2" class="header-style7">

<!-- TOP BAR -->
<div class="header-top-bar">
<div class="top-bar-top bg-primarytextcolor border-bottom">
<div class="container">
<div class="row">

<div class="col-lg-8 col-md-12">
<ul class="news-info-list text-center--md">
<li><i class="fa fa-map-marker"></i> <?= htmlspecialchars($sys['business_address']) ?></li>
<li><i class="fa fa-calendar"></i> <?= date('d/m/Y') ?></li>
<li><i class="fa fa-clock-o"></i> Actualizado: <?= date('H:i a') ?></li>
</ul>
</div>

<div class="col-lg-4 d-none d-lg-block">
<ul class="header-social">
<?php
$redesHeader = [
    'facebook'=>'fa-facebook','twitter'=>'fa-twitter','instagram'=>'fa-instagram',
    'youtube'=>'fa-youtube','tiktok'=>'fa-music','linkedin'=>'fa-linkedin'
];
foreach ($redesHeader as $red=>$icono):
    if (!empty($sys[$red])):
?>
<li><a href="<?= htmlspecialchars($sys[$red]) ?>" target="_blank"><i class="fa <?= $icono ?>"></i></a></li>
<?php endif; endforeach; ?>
<?php if (!empty($sys['rss'])): ?>
<li><a href="<?= htmlspecialchars($sys['rss']) ?>"><i class="fa fa-rss"></i></a></li>
<?php endif; ?>
</ul>
</div>

</div>
</div>
</div>
</div>

<!-- MAIN MENU -->
<div class="main-menu-area bg-body border-bottom" id="sticker">
<div class="container">
<div class="row no-gutters d-flex align-items-center">

<!-- LOGO -->
<div class="col-lg-2 d-none d-lg-block">
<a href="<?= URLBASE ?>"><img src="<?= URLBASE.SITE_LOGO ?>?<?= time() ?>" class="img-fluid"></a>
</div>

<!-- DESKTOP MENU -->
<div class="col-lg-8 d-none d-lg-block">
<div class="ne-main-menu">
<nav id="dropdown">
<ul>

<li class="active"><a href="<?= URLBASE ?>">INICIO</a></li>
<li><a href="<?= URLBASE ?>/noticias">NOTICIAS</a></li>

<!-- CATEGORÍAS -->
<li>
<a href="#">CATEGORÍAS</a>
<ul class="ne-dropdown-menu">
<?php
$cats = db()->query("
SELECT c.name,c.slug
FROM blog_categories c
JOIN blog_post_category pc ON pc.category_id=c.id
JOIN blog_posts p ON p.id=pc.post_id
WHERE c.status='active' AND c.deleted=0 AND p.status='published' AND p.deleted=0
GROUP BY c.id ORDER BY c.name
")->fetchAll(PDO::FETCH_ASSOC);
foreach ($cats as $cat): ?>
<li><a href="<?= URLBASE ?>/noticias/<?= $cat['slug'] ?>/"><?= htmlspecialchars($cat['name']) ?></a></li>
<?php endforeach; ?>
</ul>
</li>

<!-- COLUMNISTAS -->
<?php
$columnistas = db()->query("
SELECT nombre,apellido,username
FROM usuarios
WHERE es_columnista=1 AND estado=0 AND borrado=0
ORDER BY nombre,apellido
")->fetchAll(PDO::FETCH_ASSOC);
if ($columnistas):
?>
<li>
<a href="#">COLUMNISTAS</a>
<ul class="ne-dropdown-menu">
<?php foreach ($columnistas as $c): ?>
<li><a href="<?= URLBASE ?>/columnistas/<?= htmlspecialchars($c['username']) ?>/">
<?= htmlspecialchars(trim($c['nombre'].' '.$c['apellido'])) ?>
</a></li>
<?php endforeach; ?>
</ul>
</li>
<?php endif; ?>

<!-- NOSOTROS -->
<li>
<a href="<?= URLBASE ?>/institucional">NOSOTROS</a>
<ul class="ne-dropdown-menu">
<?php
$inst = db()->query("
SELECT title,slug FROM institutional_pages
WHERE status='published'
ORDER BY display_order,title
")->fetchAll(PDO::FETCH_ASSOC);
foreach ($inst as $i): ?>
<li><a href="<?= URLBASE ?>/institucional/<?= $i['slug'] ?>"><?= htmlspecialchars($i['title']) ?></a></li>
<?php endforeach; ?>
<li><a href="<?= URLBASE ?>/institucional"><i class="fa fa-list mr-2"></i>Ver todas</a></li>
</ul>
</li>

<li><a href="<?= URLBASE ?>/contact">CONTACTO</a></li>

</ul>
</nav>
</div>
</div>

<!-- SEARCH + MOBILE -->
<div class="col-lg-2 text-right">
<button class="header-search-trigger" data-toggle="modal" data-target="#searchModal">
<i class="fa fa-search"></i>
</button>
</div>

</div>
</div>
</div>
</div>
</header>

    <!-- Publicidad Position 1 (Banner Central) -->
    <div class="container text-center mt-4 mb-2">
        <?php
        $stmt = db()->prepare("SELECT * FROM ads WHERE position = 1 AND status = 'active' LIMIT 1");
        $stmt->execute();
        $ad = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($ad && !empty($ad['image_url'])): ?>
            <?php if (!empty($ad['target_url'])): ?>
                <a href="<?= htmlspecialchars($ad['target_url']) ?>" target="_blank" rel="noopener">
                    <img class="img-fluid" src="<?= URLBASE . htmlspecialchars($ad['image_url']) ?>" alt="Publicidad">
                </a>
            <?php else: ?>
                <img class="img-fluid" src="<?= URLBASE . htmlspecialchars($ad['image_url']) ?>" alt="Publicidad">
            <?php endif; ?>
        <?php endif; ?>
    </div>

    <!-- News Feed Area (Ticker Dinámico) -->
<section class="container">
    <div class="bg-body-color ml-15 pr-15 mb-10 mt-10">
        <div class="row no-gutters d-flex align-items-center">
            <div class="col-lg-2 col-md-3 col-sm-4 col-5">
                <div class="topic-box">ÚLTIMAS NOTICIAS</div>
            </div>
            <div class="col-lg-10 col-md-9 col-sm-8 col-7">
                <div class="feeding-text-light2">
                    <ol id="sample" class="ticker">
                        <?php
                        $stTicker = db()->query("
                            SELECT p.title, p.slug, c.slug AS category_slug
                            FROM blog_posts p
                            LEFT JOIN blog_post_category pc ON pc.post_id = p.id
                            LEFT JOIN blog_categories c ON c.id = pc.category_id
                            WHERE p.status = 'published' AND p.deleted = 0 
                            GROUP BY p.id
                            ORDER BY p.created_at DESC 
                            LIMIT 5
                        ");
                        $tickerNews = $stTicker->fetchAll(PDO::FETCH_ASSOC);
                        foreach ($tickerNews as $news): ?>
                            <li>
                                <a href="<?= URLBASE ?>/<?= htmlspecialchars($news['category_slug']) ?>/<?= htmlspecialchars($news['slug']) ?>/">
                                    <?= htmlspecialchars($news['title']) ?>
                                </a>
                            </li>
                        <?php endforeach; ?>
                    </ol>
                </div>
            </div>
        </div>
    </div>
</section>
