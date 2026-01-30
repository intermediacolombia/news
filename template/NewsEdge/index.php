<?php
// =======================
// Variables SEO dinÃ¡micas
// =======================
$page_title       = !empty($sys['seo_home_title']) 
                    ? $sys['seo_home_title'] 
                    : NOMBRE_SITIO;

$page_description = !empty($sys['seo_home_description']) 
                    ? $sys['seo_home_description'] 
                    : "Bienvenido a " . NOMBRE_SITIO;

$page_keywords    = !empty($sys['seo_home_keywords']) 
                    ? $sys['seo_home_keywords'] 
                    : NOMBRE_SITIO . ", tienda online, comprar, ofertas";


// Imagen SEO â†’ primera del producto o logo por defecto
$page_image = rtrim(URLBASE, '/') .FAVICON;
if (!empty($images)) {
    $path = $images[0]['path'];
    $path = ($path[0] === '/') ? $path : '/' . $path;
    $page_image = rtrim(URLBASE, '/') . $path;
}

// Canonical automÃ¡tico (desde URL actual)
$currentPath    = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$page_canonical = rtrim(URLBASE, '/') . '/' . ltrim($currentPath, '/');

// =======================
// Fin SEO
// =======================

?>
 
            <!-- News Feed Area End Here -->
            <!-- Slider Area Start Here -->
            <?php include __DIR__ . '/partials/home_latest_news.php'; ?>
            
            <!-- Slider Area End Here -->
            <!-- Popular Area Start Here -->
            <?php include __DIR__ . '/partials/features.php'; ?>
            <!-- Popular Area End Here -->
            <!-- Latest Articles Area Start Here -->
            <section class="section-space-bottom-less30">
                <div class="container">
                    <div class="row">
                        
                      <?php include __DIR__ . '/partials/lastest.php'; ?>
                      <?php include __DIR__ . '/partials/sidebar.php'; ?>
                    </div>
                </div>
            </section>
            <!-- Latest Articles Area End Here -->
            <!-- Videos Area Start Here -->
           <?php include __DIR__ . '/partials/columnists.php'; ?>
            <!-- Videos Area Start Here -->
            <!-- Category Area Start Here -->
            <section class="bg-body section-space-less10">
                <div class="container">
                    <div class="row tab-space5">
                        <div class="col-lg-4 col-md-6 col-sm-6 col-12">
                            <div class="category-box-layout1 overlay-dark-level-2 img-grayscale-hover text-center mb-10">
                                <img src="img/category/ctg10.jpg" alt="news" class="img-fluid width-100">
                                <div class="content p-30-r">
                                    <div class="ctg-title-xs">Food</div>
                                    <h3 class="title-regular-light size-lg">
                                        <a href="post-style-1.html">Boti kebab is here summer drinks Recipe by Healthy Kadai</a>
                                    </h3>
                                    <div class="post-date-light d-block d-sm-none d-md-block">
                                        <ul>
                                            <li>
                                                <span>by</span>
                                                <a href="single-news-1.html">Adams</a>
                                            </li>
                                            <li>
                                                <span>
                                                    <i class="fa fa-calendar" aria-hidden="true"></i>
                                                </span>March 22, 2017</li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-4 col-md-6 col-sm-6 col-12">
                            <div class="category-box-layout1 overlay-dark-level-2 img-grayscale-hover text-center mb-10">
                                <img src="img/category/ctg11.jpg" alt="news" class="img-fluid width-100">
                                <div class="content p-30-r">
                                    <div class="ctg-title-xs">Food</div>
                                    <h3 class="title-regular-light size-lg">
                                        <a href="post-style-1.html">Boti kebab is here summer drinks Recipe by Healthy Kadai</a>
                                    </h3>
                                    <div class="post-date-light d-block d-sm-none d-md-block">
                                        <ul>
                                            <li>
                                                <span>by</span>
                                                <a href="single-news-1.html">Adams</a>
                                            </li>
                                            <li>
                                                <span>
                                                    <i class="fa fa-calendar" aria-hidden="true"></i>
                                                </span>March 22, 2017</li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-4 col-md-6 col-sm-6 col-12">
                            <div class="category-box-layout1 overlay-dark-level-2 img-grayscale-hover text-center mb-10">
                                <img src="img/category/ctg12.jpg" alt="news" class="img-fluid width-100">
                                <div class="content p-30-r">
                                    <div class="ctg-title-xs">Food</div>
                                    <h3 class="title-regular-light size-lg">
                                        <a href="post-style-1.html">Boti kebab is here summer drinks Recipe by Healthy Kadai</a>
                                    </h3>
                                    <div class="post-date-light d-block d-sm-none d-md-block">
                                        <ul>
                                            <li>
                                                <span>by</span>
                                                <a href="single-news-1.html">Adams</a>
                                            </li>
                                            <li>
                                                <span>
                                                    <i class="fa fa-calendar" aria-hidden="true"></i>
                                                </span>March 22, 2017</li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-4 col-md-6 col-sm-6 col-12">
                            <div class="category-box-layout1 overlay-dark-level-2 img-grayscale-hover text-center mb-10">
                                <img src="img/category/ctg13.jpg" alt="news" class="img-fluid width-100">
                                <div class="content p-30-r">
                                    <div class="ctg-title-xs">Food</div>
                                    <h3 class="title-regular-light size-lg">
                                        <a href="post-style-1.html">Boti kebab is here summer drinks Recipe by Healthy Kadai</a>
                                    </h3>
                                    <div class="post-date-light d-block d-sm-none d-md-block">
                                        <ul>
                                            <li>
                                                <span>by</span>
                                                <a href="single-news-1.html">Adams</a>
                                            </li>
                                            <li>
                                                <span>
                                                    <i class="fa fa-calendar" aria-hidden="true"></i>
                                                </span>March 22, 2017</li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-4 col-md-6 col-sm-6 col-12">
                            <div class="category-box-layout1 overlay-dark-level-2 img-grayscale-hover text-center mb-10">
                                <img src="img/category/ctg14.jpg" alt="news" class="img-fluid width-100">
                                <div class="content p-30-r">
                                    <div class="ctg-title-xs">Food</div>
                                    <h3 class="title-regular-light size-lg">
                                        <a href="post-style-1.html">Boti kebab is here summer drinks Recipe by Healthy Kadai</a>
                                    </h3>
                                    <div class="post-date-light d-block d-sm-none d-md-block">
                                        <ul>
                                            <li>
                                                <span>by</span>
                                                <a href="single-news-1.html">Adams</a>
                                            </li>
                                            <li>
                                                <span>
                                                    <i class="fa fa-calendar" aria-hidden="true"></i>
                                                </span>March 22, 2017</li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-4 col-md-6 col-sm-6 col-12">
                            <div class="category-box-layout1 overlay-dark-level-2 img-grayscale-hover text-center mb-10">
                                <img src="img/category/ctg15.jpg" alt="news" class="img-fluid width-100">
                                <div class="content p-30-r">
                                    <div class="ctg-title-xs">Food</div>
                                    <h3 class="title-regular-light size-lg">
                                        <a href="post-style-1.html">Boti kebab is here summer drinks Recipe by Healthy Kadai</a>
                                    </h3>
                                    <div class="post-date-light d-block d-sm-none d-md-block">
                                        <ul>
                                            <li>
                                                <span>by</span>
                                                <a href="single-news-1.html">Adams</a>
                                            </li>
                                            <li>
                                                <span>
                                                    <i class="fa fa-calendar" aria-hidden="true"></i>
                                                </span>March 22, 2017</li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>
            <!-- Category Area End Here -->
            <!-- Footer Area Start Here -->
            <footer>
                <div class="footer-area-top">
                    <div class="container">
                        <div class="row">
                            <div class="col-lg-4 col-md-6 col-sm-12">
                                <div class="footer-box">
                                    <h2 class="title-bold-light title-bar-left text-uppercase">Most Viewed Posts</h2>
                                    <ul class="most-view-post">
                                        <li>
                                            <div class="media">
                                                <a href="post-style-1.html">
                                                    <img src="img/footer/post1.jpg" alt="post" class="img-fluid">
                                                </a>
                                                <div class="media-body">
                                                    <h3 class="title-medium-light size-md mb-10">
                                                        <a href="#">Basketball Stars Face Off itim ate Playoff Beard Battle</a>
                                                    </h3>
                                                    <div class="post-date-light">
                                                        <ul>
                                                            <li>
                                                                <span>
                                                                    <i class="fa fa-calendar" aria-hidden="true"></i>
                                                                </span>November 11, 2017</li>
                                                        </ul>
                                                    </div>
                                                </div>
                                            </div>
                                        </li>
                                        <li>
                                            <div class="media">
                                                <a href="post-style-2.html">
                                                    <img src="img/footer/post2.jpg" alt="post" class="img-fluid">
                                                </a>
                                                <div class="media-body">
                                                    <h3 class="title-medium-light size-md mb-10">
                                                        <a href="#">Basketball Stars Face Off in ate Playoff Beard Battle</a>
                                                    </h3>
                                                    <div class="post-date-light">
                                                        <ul>
                                                            <li>
                                                                <span>
                                                                    <i class="fa fa-calendar" aria-hidden="true"></i>
                                                                </span>August 22, 2017</li>
                                                        </ul>
                                                    </div>
                                                </div>
                                            </div>
                                        </li>
                                        <li>
                                            <div class="media">
                                                <a href="post-style-3.html">
                                                    <img src="img/footer/post3.jpg" alt="post" class="img-fluid">
                                                </a>
                                                <div class="media-body">
                                                    <h3 class="title-medium-light size-md mb-10">
                                                        <a href="#">Basketball Stars Face tim ate Playoff Battle</a>
                                                    </h3>
                                                    <div class="post-date-light">
                                                        <ul>
                                                            <li>
                                                                <span>
                                                                    <i class="fa fa-calendar" aria-hidden="true"></i>
                                                                </span>March 31, 2017</li>
                                                        </ul>
                                                    </div>
                                                </div>
                                            </div>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                            <div class="col-xl-4 col-lg-3 col-md-6 col-sm-12">
                                <div class="footer-box">
                                    <h2 class="title-bold-light title-bar-left text-uppercase">Popular Categories</h2>
                                    <ul class="popular-categories">
                                        <li>
                                            <a href="#">Gadgets
                                                <span>15</span>
                                            </a>
                                        </li>
                                        <li>
                                            <a href="#">Architecture
                                                <span>10</span>
                                            </a>
                                        </li>
                                        <li>
                                            <a href="#">New look 2017
                                                <span>14</span>
                                            </a>
                                        </li>
                                        <li>
                                            <a href="#">Reviews
                                                <span>13</span>
                                            </a>
                                        </li>
                                        <li>
                                            <a href="#">Mobile and Phones
                                                <span>19</span>
                                            </a>
                                        </li>
                                        <li>
                                            <a href="#">Recipes
                                                <span>26</span>
                                            </a>
                                        </li>
                                        <li>
                                            <a href="#">Decorating
                                                <span>21</span>
                                            </a>
                                        </li>
                                        <li>
                                            <a href="#">IStreet fashion
                                                <span>09</span>
                                            </a>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                            <div class="col-xl-4 col-lg-5 col-md-12 col-sm-12">
                                <div class="footer-box">
                                    <h2 class="title-bold-light title-bar-left text-uppercase">Post Gallery</h2>
                                    <ul class="post-gallery shine-hover ">
                                        <li>
                                            <a href="gallery-style1.html">
                                                <figure>
                                                    <img src="img/footer/post4.jpg" alt="post" class="img-fluid">
                                                </figure>
                                            </a>
                                        </li>
                                        <li>
                                            <a href="gallery-style2.html">
                                                <figure>
                                                    <img src="img/footer/post5.jpg" alt="post" class="img-fluid">
                                                </figure>
                                            </a>
                                        </li>
                                        <li>
                                            <a href="gallery-style1.html">
                                                <figure>
                                                    <img src="img/footer/post6.jpg" alt="post" class="img-fluid">
                                                </figure>
                                            </a>
                                        </li>
                                        <li>
                                            <a href="gallery-style2.html">
                                                <figure>
                                                    <img src="img/footer/post7.jpg" alt="post" class="img-fluid">
                                                </figure>
                                            </a>
                                        </li>
                                        <li>
                                            <a href="gallery-style1.html">
                                                <figure>
                                                    <img src="img/footer/post8.jpg" alt="post" class="img-fluid">
                                                </figure>
                                            </a>
                                        </li>
                                        <li>
                                            <a href="gallery-style2.html">
                                                <figure>
                                                    <img src="img/footer/post9.jpg" alt="post" class="img-fluid">
                                                </figure>
                                            </a>
                                        </li>
                                        <li>
                                            <a href="gallery-style1.html">
                                                <figure>
                                                    <img src="img/footer/post10.jpg" alt="post" class="img-fluid">
                                                </figure>
                                            </a>
                                        </li>
                                        <li>
                                            <a href="gallery-style2.html">
                                                <figure>
                                                    <img src="img/footer/post11.jpg" alt="post" class="img-fluid">
                                                </figure>
                                            </a>
                                        </li>
                                        <li>
                                            <a href="gallery-style1.html">
                                                <figure>
                                                    <img src="img/footer/post12.jpg" alt="post" class="img-fluid">
                                                </figure>
                                            </a>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
