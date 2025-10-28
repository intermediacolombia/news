<!-- Topbar Start -->
    <div class="container-fluid">
       
		<?php include __DIR__ . '/../partials/header-bar.php'; ?>
		
		
        <div class="row align-items-center py-2 px-lg-5">
            <div class="col-lg-4">
                <a href="<?php echo URLBASE; ?>" class="navbar-brand d-none d-lg-block">
                    <img src="<?php echo URLBASE; ?><?php echo SITE_LOGO; ?>?<?php echo time()?>" alt="Logo" width="200px">
                </a>
            </div>
            <div class="col-lg-8 text-center text-lg-right">
    <?php
    $stmt = $pdo->prepare("
        SELECT * FROM ads 
        WHERE position = 1 AND status = 'active' 
        LIMIT 1
    ");
    $stmt->execute();
    $ad = $stmt->fetch(PDO::FETCH_ASSOC);
    ?>

    <?php if ($ad && !empty($ad['image_url'])): ?>
        <?php if (!empty($ad['target_url'])): ?>
            <a href="<?= htmlspecialchars($ad['target_url']) ?>" target="_blank" rel="noopener">
                <img class="img-fluid"
                     src="<?= URLBASE . htmlspecialchars($ad['image_url']) ?>"
                     alt="<?= htmlspecialchars($ad['title'] ?? 'Publicidad') ?>">
            </a>
        <?php else: ?>
            <img class="img-fluid"
                 src="<?= URLBASE . htmlspecialchars($ad['image_url']) ?>"
                 alt="<?= htmlspecialchars($ad['title'] ?? 'Publicidad') ?>">
        <?php endif; ?>
    <?php endif; ?>
</div>



        </div>
    </div>
    <!-- Topbar End -->


    <!-- Navbar Start -->
    <div class="container-fluid p-0 mb-3">
        <nav class="navbar navbar-expand-lg bg-light navbar-light py-2 py-lg-0 px-lg-5">
            <a href="<?php echo URLBASE; ?>" class="navbar-brand d-block d-lg-none">
                <img src="<?php echo URLBASE; ?><?php echo SITE_LOGO; ?>?<?php echo time()?>" alt="Logo" width="100px">
            </a>
            <button type="button" class="navbar-toggler" data-toggle="collapse" data-target="#navbarCollapse">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse justify-content-between px-0 px-lg-3" id="navbarCollapse">
                <div class="navbar-nav mr-auto py-0">
                    <a href="<?= URLBASE ?>" class="nav-item nav-link active">INICIO</a>
                    <a href="<?= URLBASE ?>/noticias" class="nav-item nav-link">NOTICIAS</a>
                    
                    
					
					<?php
// Cargar categorías con al menos 1 post publicado y no borrado
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
  <!-- Bootstrap 4: data-toggle | Bootstrap 5: data-bs-toggle -->
  <a href="<?= URLBASE ?>/noticias/" class="nav-link dropdown-toggle" data-toggle="dropdown" data-bs-toggle="dropdown">
    CATEGORIAS
  </a>
  <div class="dropdown-menu rounded-0 m-0">
    
    <?php foreach ($cats as $cat): ?>
      <a href="<?= URLBASE ?>/noticias/<?= htmlspecialchars($cat['slug']) ?>/" class="dropdown-item">
        <?= htmlspecialchars($cat['name']) ?>
        <span class="text-muted"></span>
      </a>
    <?php endforeach; ?>
  </div>
</div>

					
					
					
                    <a href="<?= URLBASE ?>/about-us" class="nav-item nav-link">NOSOTROS</a>
                    <a href="<?= URLBASE ?>/contact" class="nav-item nav-link">CONTACTO</a>
                </div>
				
				
                <form action="/buscar/" method="get" class="input-group ml-auto" style="width: 100%; max-width: 300px;">
					<input type="text" name="q" class="form-control" placeholder="Buscar...">
					<div class="input-group-append">
						<button type="submit" class="input-group-text text-secondary">
							<i class="fa fa-search"></i>
						</button>
					</div>
				</form>



				
				
				
            </div>
        </nav>
    </div>
    <!-- Navbar End -->

<div id="link1" class="radio-player"></div>
<main id="pageContent">