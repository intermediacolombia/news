<div id="wrapper">
<?php 
require_once __DIR__ . '/../../../inc/config.php';
require_once __DIR__ . '/../../../inc/translations.php';
?>
    <header class="artemis-navbar navbar-expand-lg fixed-top">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-2 col-md-3">
                    <a href="<?= URLBASE ?>" class="navbar-brand">
                        <img src="<?= URLBASE . SITE_LOGO ?>?<?= time() ?>" 
                             alt="Logo" 
                             class="img-fluid"
                             style="max-height: 50px;">
                    </a>
                </div>
                
                <div class="col-lg-8 col-md-6">
                    <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#artemisNav">
                        <i class="fas fa-bars" style="color: var(--text-color);"></i>
                    </button>
                    
                    <nav class="collapse navbar-collapse justify-content-center" id="artemisNav">
                        <ul class="navbar-nav">
                            <li class="nav-item">
                                <a class="nav-link <?= ($_GET['page'] ?? '') === 'index' ? 'active' : '' ?>" href="<?= URLBASE ?>">
                                    <?= strtoupper(t_theme('theme_inicio')) ?>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="<?= URLBASE ?>/noticias"><?= strtoupper(t_theme('theme_noticias')) ?></a>
                            </li>
                            <li class="nav-item dropdown">
                                <a class="nav-link" href="<?= URLBASE ?>/noticias">
                                    <?= strtoupper(t_theme('theme_categorias')) ?>
                                </a>
                                <div class="dropdown-menu">
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
                                    foreach ($cats as $cat):
                                    ?>
                                        <a class="dropdown-item" href="<?= URLBASE ?>/noticias/<?= htmlspecialchars($cat['slug']) ?>/">
                                            <?= htmlspecialchars($cat['name']) ?>
                                        </a>
                                    <?php endforeach; ?>
                                </div>
                            </li>

                            <?php
                            $stCols = db()->query("
                                SELECT nombre, apellido, username
                                FROM usuarios
                                WHERE es_columnista = 1
                                  AND estado = 0
                                  AND borrado = 0
                                ORDER BY nombre ASC, apellido ASC
                            ");
                            $columnistasMenu = $stCols->fetchAll(PDO::FETCH_ASSOC);
                            ?>

                            <?php if (count($columnistasMenu) === 1): ?>
                            <li class="nav-item">
                                <a class="nav-link" href="<?= URLBASE ?>/columnista/<?= htmlspecialchars($columnistasMenu[0]['username']) ?>/">
                                    <?= strtoupper(t_theme('theme_columnistas')) ?>
                                </a>
                            </li>
                            <?php elseif (count($columnistasMenu) > 1): ?>
                            <li class="nav-item dropdown">
                                <a class="nav-link" href="<?= URLBASE ?>/columnista">
                                    <?= strtoupper(t_theme('theme_columnistas')) ?>
                                </a>
                                <div class="dropdown-menu">
                                    <?php foreach ($columnistasMenu as $col):
                                        $nombreCompleto = trim($col['nombre'] . ' ' . $col['apellido']);
                                    ?>
                                        <a class="dropdown-item" href="<?= URLBASE ?>/columnista/<?= htmlspecialchars($col['username']) ?>/">
                                            <?= htmlspecialchars($nombreCompleto) ?>
                                        </a>
                                    <?php endforeach; ?>
                                </div>
                            </li>
                            <?php endif; ?>

                            <li class="nav-item dropdown">
                                <a class="nav-link" href="<?= URLBASE ?>/institucional">
                                    <?= strtoupper(t_theme('theme_nosotros')) ?>
                                </a>
                                <div class="dropdown-menu">
                                    <?php
                                    $stInst = db()->query("
                                        SELECT title, slug
                                        FROM institutional_pages
                                        WHERE status = 'published'
                                        ORDER BY display_order ASC, title ASC
                                    ");
                                    $institucionalPages = $stInst->fetchAll(PDO::FETCH_ASSOC);
                                    foreach ($institucionalPages as $instPage):
                                    ?>
                                        <a class="dropdown-item" href="<?= URLBASE ?>/institucional/<?= htmlspecialchars($instPage['slug']) ?>">
                                            <?= htmlspecialchars($instPage['title']) ?>
                                        </a>
                                    <?php endforeach; ?>
                                    <a class="dropdown-item" href="<?= URLBASE ?>/institucional">
                                        <i class="fas fa-list mr-2"></i><?= t_theme('theme_ver_todas') ?>
                                    </a>
                                </div>
                            </li>

                            <li class="nav-item">
                                <a class="nav-link" href="<?= URLBASE ?>/contact"><?= strtoupper(t_theme('theme_contacto')) ?></a>
                            </li>
                        </ul>
                    </nav>
                </div>
                
                <div class="col-lg-2 col-md-3 text-right">
                    <button type="button"
                            class="header-search-trigger"
                            data-toggle="modal"
                            data-target="#searchModal"
                            aria-label="Abrir buscador"
                            style="background: transparent; border: none; color: var(--text-color); font-size: 18px; cursor: pointer;">
                        <i class="fas fa-search"></i>
                    </button>

                    <button id="theme-toggle" 
                            type="button"
                            onclick="toggleTheme()"
                            style="background: transparent; border: none; color: var(--text-color); font-size: 18px; cursor: pointer; margin: 0 10px;"
                            aria-label="Cambiar tema">
                        <i class="fas fa-moon" id="theme-icon"></i>
                    </button>
                    
                    <div id="side-menu-trigger" class="offcanvas-menu-btn offcanvas-btn-repoint d-inline-block d-lg-none ml-3" style="cursor: pointer;">
                        <i class="fas fa-bars" style="color: var(--text-color); font-size: 18px;"></i>
                    </div>
                </div>
            </div>
        </div>
    </header>

    <div style="height: 80px;"></div>

    <div class="container text-center mt-3 mb-2">
        <?php
        $stmt = db()->prepare("SELECT * FROM ads WHERE position = 1 AND status = 'active' LIMIT 1");
        $stmt->execute();
        $ad = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($ad && !empty($ad['image_url'])): 
        ?>
            <?php if (!empty($ad['target_url'])): ?>
                <a href="<?= htmlspecialchars($ad['target_url']) ?>" target="_blank" rel="noopener">
                    <img class="img-fluid" 
                         src="<?= URLBASE . htmlspecialchars($ad['image_url']) ?>" 
                         alt="Publicidad"
                         style="max-height: 100px;">
                </a>
            <?php else: ?>
                <img class="img-fluid" 
                     src="<?= URLBASE . htmlspecialchars($ad['image_url']) ?>" 
                     alt="Publicidad"
                     style="max-height: 100px;">
            <?php endif; ?>
        <?php endif; ?>
    </div>

    <div class="ticker-wrapper">
        <div class="container">
            <div style="display: flex; align-items: center; overflow: hidden;">
                <span class="ticker-label" style="flex-shrink: 0;"><?= strtoupper(t_theme('theme_ultimas')) ?></span>
                <div class="ticker-content" style="flex: 1; overflow: hidden; min-width: 0;">
                    <div class="ticker-track">
                        <?php
                        $stTicker = db()->query("
                            SELECT p.title, p.slug, c.slug AS category_slug
                            FROM blog_posts p
                            LEFT JOIN blog_post_category pc ON pc.post_id = p.id
                            LEFT JOIN blog_categories c ON c.id = pc.category_id
                            WHERE p.status = 'published' AND p.deleted = 0
                            GROUP BY p.id
                            ORDER BY p.created_at DESC
                            LIMIT 10
                        ");
                        $tickerNews = $stTicker->fetchAll(PDO::FETCH_ASSOC);

                        // Duplicate for seamless loop
                        $allNews = array_merge($tickerNews, $tickerNews);

                        foreach ($allNews as $news):
                        ?>
                            <span class="ticker-item">
                                <a href="<?= URLBASE ?>/<?= htmlspecialchars($news['category_slug']) ?>/<?= htmlspecialchars($news['slug']) ?>/"
                                   style="color: #fff; text-decoration: none;">
                                    <?= htmlspecialchars($news['title']) ?>
                                </a>
                            </span>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>