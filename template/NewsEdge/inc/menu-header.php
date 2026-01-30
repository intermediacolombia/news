<!--[if lt IE 8]>
    <p class="browserupgrade">You are using an 
        <strong>outdated</strong> browser. Please 
        <a href="http://browsehappy.com/">upgrade your browser</a> to improve your experience.
    </p>
<![endif]-->

<div id="preloader"></div>

<div id="wrapper">
    <!-- Header Area Start Here -->
    <header>
        <div id="header-layout2" class="header-style7">
            <div class="header-top-bar">
                <div class="top-bar-top bg-primarytextcolor border-bottom">
                    <div class="container">
                        <div class="row">
                            <div class="col-lg-8 col-md-12">
                                <ul class="news-info-list text-center--md">
                                    <li>
                                        <i class="fa fa-map-marker" aria-hidden="true"></i>
                                        <?php echo date('L') ? 'Ubicación' : 'Soporte'; ?>
                                    </li>
                                    <li>
                                        <i class="fa fa-calendar" aria-hidden="true"></i>
                                        <span id="current_date"><?php echo date('d/m/Y'); ?></span>
                                    </li>
                                    <li>
                                        <i class="fa fa-clock-o" aria-hidden="true"></i>
                                        Actualizado: <?php echo date('H:i a'); ?>
                                    </li>
                                </ul>
                            </div>
                            <div class="col-lg-4 d-none d-lg-block">
                                <ul class="header-social">
                                    <li><a href="#" title="facebook"><i class="fa fa-facebook" aria-hidden="true"></i></a></li>
                                    <li><a href="#" title="twitter"><i class="fa fa-twitter" aria-hidden="true"></i></a></li>
                                    <li><a href="#" title="linkedin"><i class="fa fa-linkedin" aria-hidden="true"></i></a></li>
                                    <li><a href="#" title="rss"><i class="fa fa-rss" aria-hidden="true"></i></a></li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="main-menu-area bg-body border-bottom" id="sticker">
                <div class="container">
                    <div class="row no-gutters d-flex align-items-center">
                        <!-- Logo Area -->
                        <div class="col-lg-2 col-md-2 d-none d-lg-block">
                            <div class="logo-area">
                                <a href="<?php echo URLBASE; ?>" class="img-fluid">
                                    <img src="<?php echo URLBASE . SITE_LOGO; ?>?<?php echo time(); ?>" alt="Logo" class="img-fluid">
                                </a>
                            </div>
                        </div>

                        <!-- Main Menu Area -->
                        <div class="col-lg-8 d-none d-lg-block position-static min-height-none">
                            <div class="ne-main-menu">
                                <nav id="dropdown">
                                    <ul>
                                        <li class="active"><a href="<​?= URLBASE ?>">INICIO</a></li>
                                        <li><a href="<?= URLBASE ?>/noticias">NOTICIAS</a></li>
                                        
                                        <!-- Categorías Dinámicas -->
                                        <li>
                                            <a href="#">CATEGORÍAS</a>
                                            <ul class="ne-dropdown-menu">
                                                <?php
                                                $st = db()->query("
                                                    SELECT c.name, c.slug, COUNT(p.id) AS total
                                                    FROM blog_categories c
                                                    INNER JOIN blog_post_category pc ON pc.category_id = c.id
                                                    INNER JOIN blog_posts p ON p.id = pc.post_id
                                                    WHERE c.status='active' AND c.deleted=0
                                                      AND p.status='published' AND p.deleted=0
                                                    GROUP BY c.id, c.name, c.slug
                                                    HAVING total > 0
                                                    ORDER BY c.name ASC
                                                ");
                                                $cats = $st->fetchAll(PDO::FETCH_ASSOC);
                                                foreach ($cats as $cat): ?>
                                                    <li>
                                                        <a href="<?= URLBASE ?>/noticias/<?= htmlspecialchars($cat['slug']) ?>/">
                                                            <?= htmlspecialchars($cat['name']) ?>
                                                        </a>
                                                    </li>
                                                <?php endforeach; ?>
                                            </ul>
                                        </li>

                                        <!-- Nosotros / Institucional Dinámico -->
                                        <li>
                                            <a href="<?= URLBASE ?>/institucional">NOSOTROS</a>
                                            <ul class="ne-dropdown-menu">
                                                <?php
                                                $stInst = db()->query("
                                                    SELECT title, slug 
                                                    FROM institutional_pages 
                                                    WHERE status = 'published' 
                                                    ORDER BY display_order ASC, title ASC
                                                ");
                                                $institucionalPages = $stInst->fetchAll(PDO::FETCH_ASSOC);
                                                foreach ($institucionalPages as $instPage): ?>
                                                    <li>
                                                        <a href="<?= URLBASE ?>/institucional/<?= htmlspecialchars($instPage['slug']) ?>">
                                                            <?= htmlspecialchars($instPage['title']) ?>
                                                        </a>
                                                    </li>
                                                <?php endforeach; ?>
                                                <li><a href="<?= URLBASE ?>/institucional"><i class="fa fa-list mr-2"></i>Ver todas</a></li>
                                            </ul>
                                        </li>
                                        
                                        <li><a href="<?= URLBASE ?>/contact">CONTACTO</a></li>
                                    </ul>
                                </nav>
                            </div>
                        </div>

                        <!-- Search and Mobile Actions -->
                        <div class="col-lg-2 col-md-2 col-sm-2 text-right position-static">
                            <div class="header-action-item on-mobile-fixed">
                                <ul>
                                    <li>
                                        <form action="<?= URLBASE ?>/buscar/" method="get" id="top-search-form" class="header-search-dark">
                                            <input type="text" name="q" class="search-input" placeholder="Buscar...." required="" style="display: none;">
                                            <button type="submit" class="search-button">
                                                <i class="fa fa-search" aria-hidden="true"></i>
                                            </button>
                                        </form>
                                    </li>
                                    <li>
                                        <div id="side-menu-trigger" class="offcanvas-menu-btn offcanvas-btn-repoint">
                                            <a href="#" class="menu-bar">
                                                <span></span><span></span><span></span>
                                            </a>
                                            <a href="#" class="menu-times close">
                                                <span></span><span></span>
                                            </a>
                                        </div>
                                    </li>
                                </ul>
                            </div>
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
                <a href="<​?= htmlspecialchars($ad['target_url']) ?>" target="_blank" rel="noopener">
                    <img class="img-fluid" src="<​?= URLBASE . htmlspecialchars($ad['image_url']) ?>" alt="Publicidad">
                </a>
            <?php else: ?>
                <img class="img-fluid" src="<​?= URLBASE . htmlspecialchars($ad['image_url']) ?>" alt="Publicidad">
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
                                SELECT title, slug 
                                FROM blog_posts 
                                WHERE status = 'published' AND deleted = 0 
                                ORDER BY created_at DESC 
                                LIMIT 5
                            ");
                            $tickerNews = $stTicker->fetchAll(PDO::FETCH_ASSOC);
                            foreach ($tickerNews as $news): ?>
                                <li>
                                    <a href="<?= URLBASE ?>/noticias/post/<?= htmlspecialchars($news['slug']) ?>">
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
