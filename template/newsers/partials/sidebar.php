<div class="col-lg-4">
    <div class="row g-4">
        <div class="col-12">
            <div class="p-3 rounded border">

                <!-- 🔍 Buscador -->
                <form method="get" action="<?= URLBASE ?>/buscar.php" class="mb-4">
                    <div class="input-group w-100 mx-auto d-flex">
                        <input type="search" name="q" class="form-control p-3" placeholder="Buscar..." value="<?= htmlspecialchars($_GET['q'] ?? '') ?>">
                        <button type="submit" class="btn btn-primary input-group-text p-3">
                            <i class="fa fa-search text-white"></i>
                        </button>
                    </div>
                </form>

                <!-- 📂 Categorías Populares -->
                <?php
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
                ?>

                <?php if ($categories): ?>
                    <h4 class="mb-4">Categorías Populares</h4>
                    <div class="row g-2 mb-4">
                        <?php foreach ($categories as $cat): ?>
                            <div class="col-12">
                                <a href="<?= URLBASE ?>/noticias/<?= htmlspecialchars($cat['slug']) ?>/"
                                   class="link-hover btn btn-light w-100 rounded text-uppercase text-dark py-3">
                                   <?= htmlspecialchars($cat['name']) ?> (<?= $cat['total'] ?>)
                                </a>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>

                <!-- 🌐 Redes Sociales -->
                <h4 class="my-4">Síguenos</h4>
                <div class="row g-3 mb-4">
                    <?php if (!empty($sys['facebook'])): ?>
                        <a href="<?= htmlspecialchars($sys['facebook']) ?>" target="_blank" class="w-100 rounded btn btn-primary d-flex align-items-center p-3 mb-2">
                            <i class="fab fa-facebook-f btn btn-light btn-square rounded-circle me-3"></i>
                            <span class="text-white">Facebook</span>
                        </a>
                    <?php endif; ?>
                    <?php if (!empty($sys['twitter'])): ?>
                        <a href="<?= htmlspecialchars($sys['twitter']) ?>" target="_blank" class="w-100 rounded btn btn-dark d-flex align-items-center p-3 mb-2">
                            <i class="fab fa-x-twitter btn btn-light btn-square rounded-circle me-3"></i>
                            <span class="text-white">X (Twitter)</span>
                        </a>
                    <?php endif; ?>
                    <?php if (!empty($sys['instagram'])): ?>
                        <a href="<?= htmlspecialchars($sys['instagram']) ?>" target="_blank" class="w-100 rounded btn btn-danger d-flex align-items-center p-3 mb-2">
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
                    <?php if (!empty($sys['youtube'])): ?>
                        <a href="<?= htmlspecialchars($sys['youtube']) ?>" target="_blank" class="w-100 rounded btn btn-danger d-flex align-items-center p-3 mb-2">
                            <i class="fab fa-youtube btn btn-light btn-square rounded-circle me-3"></i>
                            <span class="text-white">YouTube</span>
                        </a>
                    <?php endif; ?>
                    <?php if (!empty($sys['whatsapp'])): ?>
                        <a href="<?= htmlspecialchars($sys['whatsapp']) ?>" target="_blank" class="w-100 rounded btn btn-success d-flex align-items-center p-3 mb-2">
                            <i class="fab fa-whatsapp btn btn-light btn-square rounded-circle me-3"></i>
                            <span class="text-white">WhatsApp</span>
                        </a>
                    <?php endif; ?>
                </div>

                <!-- ⚡ Código personalizado desde sistema -->
                <?= $sys['code_sliderbar'] ?? '' ?>

                <!-- 📢 Banner lateral -->
                <?php include __DIR__ . '/ads5.php'; ?>

                <!-- 🔥 Noticias Populares -->
                <?php
                $stmt = $pdo->query("
                    SELECT p.id, p.title, p.slug, p.image, p.created_at,
                           c.name AS category_name, c.slug AS category_slug,
                           COUNT(v.id) AS total_views
                    FROM blog_post_views v
                    INNER JOIN blog_posts p ON p.id = v.post_id
                    INNER JOIN blog_post_category pc ON pc.post_id = p.id
                    INNER JOIN blog_categories c ON c.id = pc.category_id
                    WHERE p.status='published' AND p.deleted=0
                    GROUP BY p.id, p.title, p.slug, p.image, p.created_at, c.name, c.slug
                    ORDER BY RAND()
                    LIMIT 5
                ");
                $trendingPosts = $stmt->fetchAll();
                ?>

                <?php if ($trendingPosts): ?>
                    <h4 class="my-4">Tendencias</h4>
                    <?php foreach ($trendingPosts as $post): ?>
                        <div class="d-flex mb-3">
                            <img src="<?= $post['image'] ? URLBASE . '/' . htmlspecialchars($post['image']) : URLBASE . '/template/news/img/news-100x100-1.jpg' ?>"
                                 style="width: 100px; height: 100px; object-fit: cover;"
                                 alt="<?= htmlspecialchars($post['title']) ?>">
                            <div class="w-100 d-flex flex-column justify-content-center bg-light px-3" style="height: 100px;">
                                <div class="mb-1" style="font-size: 13px;">
                                    <a href="<?= URLBASE ?>/noticias/<?= htmlspecialchars($post['category_slug']) ?>/">
                                        <?= htmlspecialchars($post['category_name']) ?>
                                    </a>
                                    <span class="px-1">/</span>
                                    <span><?= fecha_espanol(date("F d, Y", strtotime($post['created_at']))) ?></span>
                                </div>
                                <a class="h6 m-0" href="<?= URLBASE ?>/<?= htmlspecialchars($post['category_slug']) ?>/<?= htmlspecialchars($post['slug']) ?>/">
                                    <?= htmlspecialchars($post['title']) ?>
                                </a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>

               <!-- 🏷️ Tags dinámicos -->
<?php
// Extraer texto de títulos y contenidos para generar etiquetas reales
$stmt = $pdo->query("
    SELECT CONCAT(title, ' ', content) AS texto
    FROM blog_posts
    WHERE status='published' AND deleted=0
");
$textos = $stmt->fetchAll(PDO::FETCH_COLUMN);

// Unir todo el contenido
$todoTexto = strtolower(strip_tags(implode(' ', $textos)));

// Dividir en palabras
$palabras = preg_split('/\W+/u', $todoTexto, -1, PREG_SPLIT_NO_EMPTY);

// Palabras comunes a excluir
$stopwords = [
    'nbsp','a','acá','ahí','al','algo','algún','alguna','algunas','algunos','allá','allí','ante','antes','aquel','aquella','aquellas','aquellos','aquí',
    'así','aunque','bajo','bien','cada','casi','cierta','ciertas','cierto','ciertos','como','con','contra','cual','cuando','cuanta','cuantas','cuanto',
    'cuantos','cuyo','cuyos','cuyas','de','del','desde','donde','dos','el','ella','ellas','ello','ellos','en','entre','era','eran','es','esa','esas',
    'ese','eso','esos','esta','estaba','estado','estamos','estan','estar','estas','este','esto','estos','está','están','fue','fueron','ha','había',
    'habían','han','hasta','hay','la','las','le','les','lo','los','luego','me','mi','mis','muy','más','ni','no','nos','nosotros','nuestra','nuestras',
    'nuestro','nuestros','nunca','o','otra','otras','otro','otros','para','pero','poco','por','porque','primero','puede','que','quien','quienes',
    'se','sea','según','ser','si','sí','sin','sobre','solamente','solo','sólo','son','su','sus','también','tan','tanto','te','tenemos','tener',
    'tengo','ti','tiene','tienen','todo','todos','tras','tu','tus','un','una','uno','unos','usted','ustedes','va','vamos','van','y','ya','yo',
    'él','ésta','éstas','éste','éstos','esto','esta','estas','estos','sino','además','entonces','luego','aun','inclusive','según','durante','cuál',
    'cuáles','dónde','cuándo','cuánto','cuántos','cuántas','qué','quién','quiénes','cómo','será','estar','estará','habrá','he','hemos','han','mismo',
    'misma','mismos','mismas','propio','propia','propios','propias','ninguno','ninguna','bastante','poco','poca','mucho','mucha','muchos','muchas',
    'demasiado','demasiada','demasiados','demasiadas','otro','otra','otros','otras','varios','varias','demás','algún','ningún','algunos','algunas',
    'the','of','and','to','in','on','for','with','at','by','from','a','an','is','it','that','as','be','are','this','was','were','or','if','has','had',
    'but','they','their','them','you','your','our','we','he','she','his','her','itself','which','what','where','when','how','why'
];

// Calcular frecuencia de palabras relevantes
$frecuencias = [];
foreach ($palabras as $pal) {
    if (mb_strlen($pal) > 3 && !in_array($pal, $stopwords)) {
        $frecuencias[$pal] = ($frecuencias[$pal] ?? 0) + 1;
    }
}

// Ordenar por frecuencia
arsort($frecuencias);

// Tomar las 12 palabras más comunes
$tags = array_slice(array_keys($frecuencias), 0, 12);
?>

<?php if (!empty($tags)): ?>
    <h4 class="my-4">Tags</h4>
    <div class="d-flex flex-wrap m-n1">
        <?php foreach ($tags as $tag): ?>
            <a href="<?= URLBASE ?>/buscar/<?= urlencode($tag) ?>/"
               class="btn btn-sm btn-outline-secondary m-1">
                <?= htmlspecialchars(ucfirst($tag)) ?>
            </a>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

            </div>
        </div>
    </div>
</div>
