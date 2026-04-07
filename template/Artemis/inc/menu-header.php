<div id="wrapper">
<?php 
require_once __DIR__ . '/../../../inc/config.php';
require_once __DIR__ . '/../../../inc/translations.php';
?>
    <header class="artemis-navbar navbar-expand-lg fixed-top">
        <div class="container">
            <div class="row align-items-center">
                
                <!-- Mobile: iconos izquierda -->
                <div class="col-4 d-lg-none d-flex align-items-center">
                    <button type="button"
                            class="header-search-trigger artemis-icon-btn"
                            data-toggle="modal"
                            data-target="#searchModal"
                            aria-label="Buscar"
                            style="background: transparent; border: none; color: var(--text-color); font-size: 18px; cursor: pointer; padding: 8px;">
                        <i class="fas fa-search"></i>
                    </button>

                    <button id="theme-toggle-mobile"
                            type="button"
                            onclick="toggleTheme()"
                            class="artemis-icon-btn"
                            style="background: transparent; border: none; color: var(--text-color); font-size: 18px; cursor: pointer; padding: 8px;"
                            aria-label="Cambiar tema">
                        <i class="fas fa-moon" id="theme-icon-mobile"></i>
                    </button>
                </div>

                <!-- Desktop: logo izquierda -->
                <div class="col-lg-2 col-md-3 d-none d-lg-block">
                    <a href="<?= URLBASE ?>" class="navbar-brand">
                        <img src="<?= URLBASE . SITE_LOGO ?>?<?= time() ?>"
                             alt="Logo"
                             class="img-fluid"
                             style="max-height: 50px;">
                    </a>
                </div>

                <!-- Mobile: logo centro -->
                <div class="col-4 d-lg-none text-center">
                    <a href="<?= URLBASE ?>" class="navbar-brand mb-0">
                        <img src="<?= URLBASE . SITE_LOGO ?>?<?= time() ?>"
                             alt="Logo"
                             class="img-fluid artemis-logo"
                             style="max-height: 42px;">
                    </a>
                </div>

                <!-- Mobile: hamburguesa derecha -->
                <div class="col-4 d-lg-none d-flex align-items-center justify-content-end">
                    <button type="button"
                            class="artemis-menu-toggle"
                            onclick="toggleMobileMenu()"
                            aria-label="Abrir menú"
                            style="background: transparent; border: none; color: var(--text-color); cursor: pointer; padding: 8px;">
                        <i class="fas fa-bars" style="font-size: 20px;"></i>
                    </button>
                </div>
                
                <div class="col-lg-8 col-md-6 d-none d-lg-block">
                    <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#artemisNav">
                        <i class="fas fa-bars" style="color: var(--text-color);"></i>
                    </button>
                    
                    <nav class="collapse navbar-collapse justify-content-center" id="artemisNav">
                        <ul class="navbar-nav justify-content-center">
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
                
                <div class="col-lg-2 col-md-3 d-none d-lg-block text-right">
                    <button type="button"
                            class="header-search-trigger"
                            data-toggle="modal"
                            data-target="#searchModal"
                            aria-label="Buscar"
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
                </div>
            </div>
        </div>
    </header>
    
    <div id="artemis-mobile-menu" class="artemis-mobile-menu">
        <div class="artemis-mobile-menu-header">
            <a href="<?= URLBASE ?>">
                <img src="<?= URLBASE . SITE_LOGO ?>?<?= time() ?>" alt="Logo" style="max-height: 38px;">
            </a>
            <button type="button" class="artemis-menu-close" onclick="toggleMobileMenu()" aria-label="Cerrar menú">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <ul class="artemis-mobile-nav">
            <li>
                <a href="<?= URLBASE ?>">
                    <i class="fas fa-home"></i><?= t_theme('theme_inicio') ?>
                </a>
            </li>
            <li>
                <a href="<?= URLBASE ?>/noticias">
                    <i class="fas fa-newspaper"></i><?= t_theme('theme_noticias') ?>
                </a>
            </li>
            <li class="artemis-mobile-dropdown">
                <a href="javascript:void(0)" class="artemis-dropdown-toggle">
                    <span><i class="fas fa-folder"></i><?= t_theme('theme_categorias') ?></span>
                    <i class="fas fa-chevron-down"></i>
                </a>
                <ul class="artemis-mobile-submenu">
                    <?php foreach ($cats as $cat): ?>
                    <li>
                        <a href="<?= URLBASE ?>/noticias/<?= htmlspecialchars($cat['slug']) ?>/">
                            <?= htmlspecialchars($cat['name']) ?>
                        </a>
                    </li>
                    <?php endforeach; ?>
                </ul>
            </li>
            <?php if (count($columnistasMenu) === 1): ?>
            <li>
                <a href="<?= URLBASE ?>/columnista/<?= htmlspecialchars($columnistasMenu[0]['username']) ?>/">
                    <i class="fas fa-pen"></i><?= t_theme('theme_columnistas') ?>
                </a>
            </li>
            <?php elseif (count($columnistasMenu) > 1): ?>
            <li class="artemis-mobile-dropdown">
                <a href="javascript:void(0)" class="artemis-dropdown-toggle">
                    <span><i class="fas fa-pen"></i><?= t_theme('theme_columnistas') ?></span>
                    <i class="fas fa-chevron-down"></i>
                </a>
                <ul class="artemis-mobile-submenu">
                    <?php foreach ($columnistasMenu as $col):
                        $nombreCompleto = trim($col['nombre'] . ' ' . $col['apellido']);
                    ?>
                    <li>
                        <a href="<?= URLBASE ?>/columnista/<?= htmlspecialchars($col['username']) ?>/">
                            <?= htmlspecialchars($nombreCompleto) ?>
                        </a>
                    </li>
                    <?php endforeach; ?>
                </ul>
            </li>
            <?php endif; ?>
            <li class="artemis-mobile-dropdown">
                <a href="javascript:void(0)" class="artemis-dropdown-toggle">
                    <span><i class="fas fa-info-circle"></i><?= t_theme('theme_nosotros') ?></span>
                    <i class="fas fa-chevron-down"></i>
                </a>
                <ul class="artemis-mobile-submenu">
                    <?php foreach ($institucionalPages as $instPage): ?>
                    <li>
                        <a href="<?= URLBASE ?>/institucional/<?= htmlspecialchars($instPage['slug']) ?>">
                            <?= htmlspecialchars($instPage['title']) ?>
                        </a>
                    </li>
                    <?php endforeach; ?>
                    <li>
                        <a href="<?= URLBASE ?>/institucional">
                            <i class="fas fa-list"></i><?= t_theme('theme_ver_todas') ?>
                        </a>
                    </li>
                </ul>
            </li>
            <li>
                <a href="<?= URLBASE ?>/contact">
                    <i class="fas fa-envelope"></i><?= t_theme('theme_contacto') ?>
                </a>
            </li>
        </ul>
    </div>
    
    <div id="artemis-mobile-overlay" class="artemis-mobile-overlay" onclick="toggleMobileMenu()"></div>

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