<?php
// === Datos dinámicos previos ===

// Categorías populares (6 con más posts publicados)
$stmtCat = $pdo->query("
    SELECT c.name, c.slug, COUNT(pc.post_id) AS total
    FROM blog_categories c
    LEFT JOIN blog_post_category pc ON c.id = pc.category_id
    INNER JOIN blog_posts p ON p.id = pc.post_id AND p.status='published' AND p.deleted=0
    GROUP BY c.id, c.name, c.slug
    ORDER BY total DESC
    LIMIT 6
");
$categories = $stmtCat->fetchAll();

// Noticias populares por vistas (TOP 4)
$stmtPop = $pdo->query("
    SELECT p.id, p.title, p.slug, p.image, p.created_at,
           c.name AS category_name, c.slug AS category_slug,
           COUNT(v.id) AS total_views
    FROM blog_post_views v
    INNER JOIN blog_posts p ON p.id = v.post_id
    INNER JOIN blog_post_category pc ON pc.post_id = p.id
    INNER JOIN blog_categories c ON c.id = pc.category_id
    WHERE p.status='published' AND p.deleted=0
    GROUP BY p.id, p.title, p.slug, p.image, p.created_at, c.name, c.slug
    ORDER BY total_views DESC, p.created_at DESC
    LIMIT 4
");
$popular = $stmtPop->fetchAll();

// Tags dinámicos (desde títulos + contenido)
$stmtTxt = $pdo->query("
    SELECT CONCAT(title, ' ', content) AS texto
    FROM blog_posts
    WHERE status='published' AND deleted=0
");
$textos = $stmtTxt->fetchAll(PDO::FETCH_COLUMN);
$todo = strtolower(strip_tags(implode(' ', $textos)));
$palabras = preg_split('/\W+/u', $todo, -1, PREG_SPLIT_NO_EMPTY);
$stop = ['nbsp','a','acá','ahí','al','algo','algún','alguna','algunas','algunos','allá','allí','ante','antes','aquel','aquella','aquellas','aquellos','aquí','así','aunque','bajo','bien','cada','casi','cierta','ciertas','cierto','ciertos','como','con','contra','cual','cuando','cuanta','cuantas','cuanto','cuantos','cuyo','cuyos','cuyas','de','del','desde','donde','dos','el','ella','ellas','ello','ellos','en','entre','era','eran','es','esa','esas','ese','eso','esos','esta','estaba','estado','estamos','estan','estar','estas','este','esto','estos','está','están','fue','fueron','ha','había','habían','han','hasta','hay','la','las','le','les','lo','los','luego','me','mi','mis','muy','más','ni','no','nos','nosotros','nuestra','nuestras','nuestro','nuestros','nunca','o','otra','otras','otro','otros','para','pero','poco','por','porque','primero','puede','que','quien','quienes','se','sea','según','ser','si','sí','sin','sobre','solamente','solo','sólo','son','su','sus','también','tan','tanto','te','tenemos','tener','tengo','ti','tiene','tienen','todo','todos','tras','tu','tus','un','una','uno','unos','usted','ustedes','va','vamos','van','y','ya','yo','él','ésta','éstas','éste','éstos','esto','esta','estas','estos','sino','además','entonces','luego','aun','inclusive','durante','cuál','cuáles','dónde','cuándo','cuánto','cuántos','cuántas','qué','quién','quiénes','cómo','será','estar','estará','habrá','he','hemos','han','mismo','misma','mismos','mismas','propio','propia','propios','propias','ninguno','ninguna','bastante','poco','poca','mucho','mucha','muchos','muchas','demasiado','demasiada','demasiados','demasiadas','otro','otra','otros','otras','varios','varias','demás','algún','ningún','algunos','algunas','the','of','and','to','in','on','for','with','at','by','from','a','an','is','it','that','as','be','are','this','was','were','or','if','has','had','but','they','their','them','you','your','our','we','he','she','his','her','itself','which','what','where','when','how','why'];
$freq = [];
foreach ($palabras as $p) {
    if (mb_strlen($p) > 3 && !in_array($p, $stop)) {
        $freq[$p] = ($freq[$p] ?? 0) + 1;
    }
}
arsort($freq);
$tags = array_slice(array_keys($freq), 0, 8);

// Banner inferior dinámico opcional (misma estructura visual)
$bannerInferior = null;
try {
    $qBanner = $pdo->prepare("SELECT title, subtitle, image, url FROM ads WHERE position = 6 LIMIT 1");
    $qBanner->execute();
    $bannerInferior = $qBanner->fetch();
} catch (\Throwable $e) {}
?>

<div class="col-lg-4">
    <div class="row g-4">
        <div class="col-12">
            <div class="p-3 rounded border">
                <!-- (Diseño original) Buscador -->
                <div class="input-group w-100 mx-auto d-flex mb-4">
                    <form method="get" action="<?= URLBASE ?>/buscar.php" class="m-0 p-0">
    <div class="input-group w-100 mx-auto d-flex mb-4">
        <input 
            type="search" 
            name="q" 
            class="form-control p-3" 
            placeholder="keywords" 
            aria-describedby="search-icon-1"
            value="<?= htmlspecialchars($_GET['q'] ?? '') ?>"
            required>
        <button 
            type="submit" 
            id="search-icon-1" 
            class="btn btn-primary input-group-text p-3 border-0">
            <i class="fa fa-search text-white"></i>
        </button>
    </div>
</form>

					
                </div>

                <!-- (Diseño original) Popular Categories -->
                <h4 class="mb-4">Popular Categories</h4>
                <div class="row g-2">
                    <?php foreach ($categories as $cat): ?>
                        <div class="col-12">
                            <a href="<?= URLBASE ?>/noticias/<?= htmlspecialchars($cat['slug']) ?>/" class="link-hover btn btn-light w-100 rounded text-uppercase text-dark py-3">
                                <?= htmlspecialchars($cat['name']) ?>
                            </a>
                        </div>
                    <?php endforeach; ?>
                </div>

                <!-- (Diseño original) Stay Connected -->
                <h4 class="my-4">Stay Connected</h4>
                <div class="row g-4">
                    <div class="col-12">
                        <?php if (!empty($sys['facebook'])): ?>
                        <a href="<?= htmlspecialchars($sys['facebook']) ?>" target="_blank" class="w-100 rounded btn btn-primary d-flex align-items-center p-3 mb-2">
                            <i class="fab fa-facebook-f btn btn-light btn-square rounded-circle me-3"></i>
                            <span class="text-white">Facebook</span>
                        </a>
                        <?php endif; ?>

                        <?php if (!empty($sys['twitter'])): ?>
                        <a href="<?= htmlspecialchars($sys['twitter']) ?>" target="_blank" class="w-100 rounded btn btn-danger d-flex align-items-center p-3 mb-2">
                            <i class="fab fa-twitter btn btn-light btn-square rounded-circle me-3"></i>
                            <span class="text-white">X (Twitter)</span>
                        </a>
                        <?php endif; ?>

                        <?php if (!empty($sys['youtube'])): ?>
                        <a href="<?= htmlspecialchars($sys['youtube']) ?>" target="_blank" class="w-100 rounded btn btn-warning d-flex align-items-center p-3 mb-2">
                            <i class="fab fa-youtube btn btn-light btn-square rounded-circle me-3"></i>
                            <span class="text-white">YouTube</span>
                        </a>
                        <?php endif; ?>

                        <?php if (!empty($sys['instagram'])): ?>
                        <a href="<?= htmlspecialchars($sys['instagram']) ?>" target="_blank" class="w-100 rounded btn btn-dark d-flex align-items-center p-3 mb-2">
                            <i class="fab fa-instagram btn btn-light btn-square rounded-circle me-3"></i>
                            <span class="text-white">Instagram</span>
                        </a>
                        <?php endif; ?>

                        <?php if (!empty($sys['tiktok'])): ?>
                        <a href="<?= htmlspecialchars($sys['tiktok']) ?>" target="_blank" class="w-100 rounded btn btn-secondary d-flex align-items-center p-3 mb-2">
                            <i class="fab fa-tiktok btn btn-light btn-square rounded-circle me-3"></i>
                            <span class="text-white">TikTok</span>
                        </a>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- (Diseño original) Popular News -->
                <h4 class="my-4">Popular News</h4>
                <div class="row g-4">
                    <?php foreach ($popular as $idx => $p): ?>
                        <div class="col-12">
                            <div class="row g-4 align-items-center features-item">
                                <div class="col-4">
                                    <div class="rounded-circle position-relative">
                                        <div class="overflow-hidden rounded-circle">
                                            <img src="<?= $p['image'] ? URLBASE . '/' . htmlspecialchars($p['image']) : URLBASE . '/template/news/img/features-sports-1.jpg' ?>" class="img-zoomin img-fluid rounded-circle w-100" alt="<?= htmlspecialchars($p['title']) ?>">
                                        </div>
                                        <span class="rounded-circle border border-2 border-white bg-primary btn-sm-square text-white position-absolute" style="top: 10%; right: -10px;">
                                            <?= $idx + 1 ?>
                                        </span>
                                    </div>
                                </div>
                                <div class="col-8">
                                    <div class="features-content d-flex flex-column">
                                        <p class="text-uppercase mb-2"><?= htmlspecialchars($p['category_name']) ?></p>
                                        <a href="<?= URLBASE ?>/<?= htmlspecialchars($p['category_slug']) ?>/<?= htmlspecialchars($p['slug']) ?>/" class="h6">
                                            <?= htmlspecialchars($p['title']) ?>
                                        </a>
                                        <small class="text-body d-block">
                                            <i class="fas fa-calendar-alt me-1"></i>
                                            <?= fecha_espanol(date("F d, Y", strtotime($p['created_at']))) ?>
                                        </small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

                <!-- (Diseño original) View More -->
                <div class="col-lg-12">
                    <a href="<?= URLBASE ?>/noticias/" class="link-hover btn border border-primary rounded-pill text-dark w-100 py-3 mb-4">View More</a>
                </div>

                <!-- (Diseño original) Trending Tags -->
                <div class="col-lg-12">
                    <div class="border-bottom my-3 pb-3">
                        <h4 class="mb-0">Trending Tags</h4>
                    </div>
                    <ul class="nav nav-pills d-inline-flex text-center mb-4">
                        <?php foreach ($tags as $t): ?>
                            <li class="nav-item mb-3">
                                <a class="d-flex py-2 bg-light rounded-pill me-2" href="<?= URLBASE ?>/buscar/<?= urlencode($t) ?>/">
                                    <span class="text-dark link-hover" style="width: 90px;"><?= htmlspecialchars(ucfirst($t)) ?></span>
                                </a>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </div>

                <!-- (Diseño original) Banner inferior -->
                <div class="col-lg-12">
                    <div class="position-relative banner-2">
                        <?php if ($bannerInferior && !empty($bannerInferior['image'])): ?>
                            <a href="<?= !empty($bannerInferior['url']) ? htmlspecialchars($bannerInferior['url']) : '#' ?>" target="_blank" rel="noopener">
                                <img src="<?= URLBASE . '/' . htmlspecialchars($bannerInferior['image']) ?>" class="img-fluid w-100 rounded" alt="<?= htmlspecialchars($bannerInferior['title'] ?? 'Banner') ?>">
                            </a>
                            <div class="text-center banner-content-2">
                                <?php if (!empty($bannerInferior['title'])): ?>
                                    <h6 class="mb-2"><?= htmlspecialchars($bannerInferior['title']) ?></h6>
                                <?php endif; ?>
                                <?php if (!empty($bannerInferior['subtitle'])): ?>
                                    <p class="text-white mb-2"><?= htmlspecialchars($bannerInferior['subtitle']) ?></p>
                                <?php endif; ?>
                                <?php if (!empty($bannerInferior['url'])): ?>
                                    <a href="<?= htmlspecialchars($bannerInferior['url']) ?>" class="btn btn-primary text-white px-4" target="_blank" rel="noopener">Shop Now</a>
                                <?php endif; ?>
                            </div>
                        <?php else: ?>
                            <?php include __DIR__ . '/ads5.php'; ?>
                        <?php endif; ?>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>

