<div class="col-lg-4">
    <div class="row g-4">
        <div class="col-12">
            <div class="p-3 rounded border">

                <!-- 🔍 Buscador -->
                <div class="input-group w-100 mx-auto d-flex mb-4">
                    <form method="get" action="<?= URLBASE ?>/buscar.php" class="w-100 d-flex">
                        <input type="search" name="q" class="form-control p-3" placeholder="Buscar..." value="<?= htmlspecialchars($_GET['q'] ?? '') ?>">
                        <button type="submit" id="search-icon-1" class="btn btn-primary input-group-text p-3">
                            <i class="fa fa-search text-white"></i>
                        </button>
                    </form>
                </div>

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
                    <h4 class="mb-4">Popular Categories</h4>
                    <div class="row g-2 mb-4">
                        <?php foreach ($categories as $cat): ?>
                            <div class="col-12">
                                <a href="<?= URLBASE ?>/noticias/<?= htmlspecialchars($cat['slug']) ?>/"
                                   class="link-hover btn btn-light w-100 rounded text-uppercase text-dark py-3">
                                   <?= htmlspecialchars($cat['name']) ?>
                                </a>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>

                <!-- 🌐 Redes Sociales -->
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
                            <a href="<?= htmlspecialchars($sys['twitter']) ?>" target="_blank" class="w-100 rounded btn btn-dark d-flex align-items-center p-3 mb-2">
                                <i class="fab fa-x-twitter btn btn-light btn-square rounded-circle me-3"></i>
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

                        <?php if (!empty($sys['whatsapp'])): ?>
                            <a href="<?= htmlspecialchars($sys['whatsapp']) ?>" target="_blank" class="w-100 rounded btn btn-success d-flex align-items-center p-3 mb-4">
                                <i class="fab fa-whatsapp btn btn-light btn-square rounded-circle me-3"></i>
                                <span class="text-white">WhatsApp</span>
                            </a>
                        <?php endif; ?>
                    </div>
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
                    LIMIT 4
                ");
                $trendingPosts = $stmt->fetchAll();
                ?>

                <?php if ($trendingPosts): ?>
                    <h4 class="my-4">Popular News</h4>
                    <div class="row g-4">
                        <?php foreach ($trendingPosts as $post): ?>
                            <div class="col-12">
                                <div class="row g-4 align-items-center features-item">
                                    <div class="col-4">
                                        <div class="rounded-circle position-relative">
                                            <div class="overflow-hidden rounded-circle">
                                                <img src="<?= $post['image'] ? URLBASE . '/' . htmlspecialchars($post['image']) : URLBASE . '/template/news/img/news-100x100-1.jpg' ?>" 
                                                     class="img-zoomin img-fluid rounded-circle w-100" 
                                                     alt="<?= htmlspecialchars($post['title']) ?>">
                                            </div>
                                            <span class="rounded-circle border border-2 border-white bg-primary btn-sm-square text-white position-absolute" style="top: 10%; right: -10px;">+</span>
                                        </div>
                                    </div>
                                    <div class="col-8">
                                        <div class="features-content d-flex flex-column">
                                            <p class="text-uppercase mb-2"><?= htmlspecialchars($post['category_name']) ?></p>
                                            <a href="<?= URLBASE ?>/<?= htmlspecialchars($post['category_slug']) ?>/<?= htmlspecialchars($post['slug']) ?>/" class="h6">
                                                <?= htmlspecialchars($post['title']) ?>
                                            </a>
                                            <small class="text-body d-block">
                                                <i class="fas fa-calendar-alt me-1"></i>
                                                <?= fecha_espanol(date("F d, Y", strtotime($post['created_at']))) ?>
                                            </small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>

                <!-- 🏷️ Tags dinámicos -->
                <?php
                $stmt = $pdo->query("
                    SELECT CONCAT(title, ' ', content) AS texto
                    FROM blog_posts
                    WHERE status='published' AND deleted=0
                ");
                $textos = $stmt->fetchAll(PDO::FETCH_COLUMN);
                $todoTexto = strtolower(strip_tags(implode(' ', $textos)));
                $palabras = preg_split('/\W+/u', $todoTexto, -1, PREG_SPLIT_NO_EMPTY);
                $stopwords = ['de','la','el','en','y','a','los','las','para','con','por','una','un','del','al','que','se','su','sus','ya','muy','más','como','es','son','esto','esta','estas','estos','sobre','the','of','and','to','in','on','for','with','at','by','from','as','are','was','were','be','an','is'];
                $frecuencias = [];
                foreach ($palabras as $pal) {
                    if (mb_strlen($pal) > 3 && !in_array($pal, $stopwords)) {
                        $frecuencias[$pal] = ($frecuencias[$pal] ?? 0) + 1;
                    }
                }
                arsort($frecuencias);
                $tags = array_slice(array_keys($frecuencias), 0, 8);
                ?>

                <?php if ($tags): ?>
                    <div class="col-lg-12">
                        <div class="border-bottom my-3 pb-3">
                            <h4 class="mb-0">Trending Tags</h4>
                        </div>
                        <ul class="nav nav-pills d-inline-flex text-center mb-4">
                            <?php foreach ($tags as $tag): ?>
                                <li class="nav-item mb-3">
                                    <a class="d-flex py-2 bg-light rounded-pill me-2" href="<?= URLBASE ?>/buscar/<?= urlencode($tag) ?>/">
                                        <span class="text-dark link-hover" style="width: 90px;"><?= htmlspecialchars(ucfirst($tag)) ?></span>
                                    </a>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>

                <!-- 📢 Banner inferior -->
                <div class="col-lg-12">
                    <div class="position-relative banner-2">
                        <img src="<?= URLBASE ?>/template/news/img/banner-2.jpg" class="img-fluid w-100 rounded" alt="">
                        <div class="text-center banner-content-2">
                            <h6 class="mb-2">The Most Popular</h6>
                            <p class="text-white mb-2">News & Magazine Theme</p>
                            <a href="#" class="btn btn-primary text-white px-4">Shop Now</a>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>

