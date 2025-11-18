


<div class="container-fluid sticky-top px-0">

<?php include __DIR__ . '/../partials/header-bar.php'; ?><!-- Topbar Start -->
    <!-- Navbar principal -->
    <div class="container-fluid bg-light">
        <div class="container px-0">
            <nav class="navbar navbar-light navbar-expand-xl">
                <a href="<?= URLBASE ?>" class="navbar-brand mt-3">
                    <img src="<?= URLBASE . SITE_LOGO ?>?<?= time() ?>" alt="Logo" width="180">
                </a>

                <button class="navbar-toggler py-2 px-3" type="button" data-bs-toggle="collapse" data-bs-target="#navbarCollapse">
                    <span class="fa fa-bars text-primary"></span>
                </button>

                <div class="collapse navbar-collapse bg-light py-3" id="navbarCollapse">
                    <div class="navbar-nav mx-auto border-top">
                        <a href="<?= URLBASE ?>" class="nav-item nav-link <?= ($_SERVER['REQUEST_URI'] == '/' ? 'active' : '') ?>">Inicio</a>
                        <a href="<?= URLBASE ?>/noticias" class="nav-item nav-link">Noticias</a>

                        <?php
                        // Categorías dinámicas
                        $st = $pdo->query("
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
                        ?>

                        <div class="nav-item dropdown">
                            <a href="#" class="nav-link dropdown-toggle" data-bs-toggle="dropdown">
                                Categorías
                            </a>
                            <div class="dropdown-menu m-0 bg-secondary rounded-0">
                                <?php foreach ($cats as $cat): ?>
                                    <a href="<?= URLBASE ?>/noticias/<?= htmlspecialchars($cat['slug']) ?>/" class="dropdown-item text-white">
                                        <?= htmlspecialchars(ucwords($cat['name'])) ?>
                                    </a>
                                <?php endforeach; ?>
                            </div>
                        </div>

                        <a href="<?= URLBASE ?>/about-us" class="nav-item nav-link">Nosotros</a>
                        <a href="<?= URLBASE ?>/contact" class="nav-item nav-link">Contacto</a>
                    </div>

                   <div class="d-flex flex-nowrap border-top pt-3 pt-xl-0">                             
                                    
									<?php //include __DIR__ . '/../partials/weather-widget.php'; ?>

                               
                                <button class="btn-search btn border border-primary btn-md-square rounded-circle bg-white my-auto" data-bs-toggle="modal" data-bs-target="#searchModal"><i class="fas fa-search text-primary"></i></button>
                   </div>
                </div>
            </nav>
        </div>
    </div>
</div>
<!-- Navbar End -->

  <!-- Modal Search Start -->
<div class="modal fade" id="searchModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-fullscreen">
        <div class="modal-content rounded-0">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel">Buscar noticias</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>

            <div class="modal-body d-flex align-items-center">
                <form id="formSearchModal" class="input-group w-75 mx-auto d-flex" method="get" action="<?= URLBASE ?>/buscar">
                    <input type="search" name="q" id="searchInputModal" class="form-control p-3"
                        placeholder="Escribe una palabra clave..."
                        aria-describedby="search-icon-1" required>
                    <button type="submit" id="search-icon-1" class="input-group-text p-3 btn btn-primary">
                        <i class="fa fa-search text-white"></i>
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
<!-- Modal Search End -->

