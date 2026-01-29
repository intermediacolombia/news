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
                        <div class="col-xl-8 col-lg-12 mb-30">
                            <div class="item-box-light-md-less30 ie-full-width">
                                <div class="topic-border color-cinnabar mb-30">
                                    <div class="topic-box-lg color-cinnabar">Food Reviews</div>
                                </div>
                                <div class="row">
                                    <div class="col-lg-12 col-md-6 col-sm-12">
                                        <div class="media media-none--md mb-30">
                                            <div class="position-relative width-40">
                                                <a href="single-news-1.html" class="img-opacity-hover">
                                                    <img src="img/news/news294.jpg" alt="news" class="img-fluid">
                                                </a>
                                                <div class="topic-box-top-xs">
                                                    <div class="topic-box-sm color-cod-gray mb-20">Desert</div>
                                                </div>
                                            </div>
                                            <div class="media-body p-mb-none-child media-margin30">
                                                <div class="post-date-dark">
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
                                                <h3 class="title-semibold-dark size-lg mb-15">
                                                    <a href="single-news-1.html">Bread medu vada recipe Hebbars Kitchen</a>
                                                </h3>
                                                <p>Separated they live in Bookmarksgrove right at the coast of the Semantics,
                                                    a large language ocean. A small river named Duden flows by their place
                                                    and ...
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-lg-12 col-md-6 col-sm-12">
                                        <div class="media media-none--md mb-30">
                                            <div class="position-relative width-40">
                                                <a href="single-news-2.html" class="img-opacity-hover">
                                                    <img src="img/news/news295.jpg" alt="news" class="img-fluid">
                                                </a>
                                                <div class="topic-box-top-xs">
                                                    <div class="topic-box-sm color-cod-gray mb-20">Desert</div>
                                                </div>
                                            </div>
                                            <div class="media-body p-mb-none-child media-margin30">
                                                <div class="post-date-dark">
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
                                                <h3 class="title-semibold-dark size-lg mb-15">
                                                    <a href="single-news-2.html">Quick Tips: Cling Wrap Hack One Pot Chef</a>
                                                </h3>
                                                <p>Separated they live in Bookmarksgrove right at the coast of the Semantics,
                                                    a large language ocean. A small river named Duden flows by their place
                                                    and ...
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-lg-12 col-md-6 col-sm-12">
                                        <div class="media media-none--md mb-30">
                                            <div class="position-relative width-40">
                                                <a href="single-news-3.html" class="img-opacity-hover">
                                                    <img src="img/news/news296.jpg" alt="news" class="img-fluid">
                                                </a>
                                                <div class="topic-box-top-xs">
                                                    <div class="topic-box-sm color-cod-gray mb-20">Desert</div>
                                                </div>
                                            </div>
                                            <div class="media-body p-mb-none-child media-margin30">
                                                <div class="post-date-dark">
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
                                                <h3 class="title-semibold-dark size-lg mb-15">
                                                    <a href="single-news-3.html">Sushi Rice with Salmon and Vegetables</a>
                                                </h3>
                                                <p>Separated they live in Bookmarksgrove right at the coast of the Semantics,
                                                    a large language ocean. A small river named Duden flows by their place
                                                    and ...
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-lg-12 col-md-6 col-sm-12">
                                        <div class="media media-none--md mb-30">
                                            <div class="position-relative width-40">
                                                <a href="single-news-1.html" class="img-opacity-hover">
                                                    <img src="img/news/news297.jpg" alt="news" class="img-fluid">
                                                </a>
                                                <div class="topic-box-top-xs">
                                                    <div class="topic-box-sm color-cod-gray mb-20">Desert</div>
                                                </div>
                                            </div>
                                            <div class="media-body p-mb-none-child media-margin30">
                                                <div class="post-date-dark">
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
                                                <h3 class="title-semibold-dark size-lg mb-15">
                                                    <a href="single-news-1.html">Indian summer drinks Recipe by Healthy</a>
                                                </h3>
                                                <p>Separated they live in Bookmarksgrove right at the coast of the Semantics,
                                                    a large language ocean. A small river named Duden flows by their place
                                                    and ...
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="ne-sidebar sidebar-break-lg col-xl-4 col-lg-12">
                            <div class="sidebar-box item-box-light-md">
                                <div class="topic-border color-cinnabar mb-30">
                                    <div class="topic-box-lg color-cinnabar">Stay Connected</div>
                                </div>
                                <ul class="stay-connected-color overflow-hidden">
                                    <li class="facebook">
                                        <a href="#">
                                            <i class="fa fa-facebook" aria-hidden="true"></i>
                                            <div class="connection-quantity">50.2 k</div>
                                            <p>Fans</p>
                                        </a>
                                    </li>
                                    <li class="twitter">
                                        <a href="#">
                                            <i class="fa fa-twitter" aria-hidden="true"></i>
                                            <div class="connection-quantity">10.3 k</div>
                                            <p>Followers</p>
                                        </a>
                                    </li>
                                    <li class="linkedin">
                                        <a href="#">
                                            <i class="fa fa-linkedin" aria-hidden="true"></i>
                                            <div class="connection-quantity">25.4 k</div>
                                            <p>Fans</p>
                                        </a>
                                    </li>
                                    <li class="rss">
                                        <a href="#">
                                            <i class="fa fa-rss" aria-hidden="true"></i>
                                            <div class="connection-quantity">20.8 k</div>
                                            <p>Subscriber</p>
                                        </a>
                                    </li>
                                </ul>
                            </div>
                            <div class="sidebar-box item-box-light-md-less30">
                                <ul class="btn-tab item-inline block-xs nav nav-tabs" role="tablist">
                                    <li class="nav-item">
                                        <a href="#recent" data-toggle="tab" aria-expanded="true" class="active">Recent</a>
                                    </li>
                                    <li class="nav-item">
                                        <a href="#popular" data-toggle="tab" aria-expanded="false">Popular</a>
                                    </li>
                                    <li class="nav-item">
                                        <a href="#common" data-toggle="tab" aria-expanded="false">Common</a>
                                    </li>
                                </ul>
                                <div class="tab-content">
                                    <div role="tabpanel" class="tab-pane fade active show" id="recent">
                                        <div class="row">
                                            <div class="col-xl-6 col-lg-4 col-md-4 col-sm-6 col-6 mb-25">
                                                <div class="position-relative">
                                                    <div class="topic-box-top-xs">
                                                        <div class="topic-box-sm color-cod-gray mb-20">Food</div>
                                                    </div>
                                                    <a href="single-news-1.html" class="img-opacity-hover">
                                                        <img src="img/news/news298.jpg" alt="news" class="img-fluid width-100 mb-10">
                                                    </a>
                                                    <h3 class="title-medium-dark size-sm mb-none">
                                                        <a href="single-news-1.html">Rosie Hutin ghton Habits Career.</a>
                                                    </h3>
                                                </div>
                                            </div>
                                            <div class="col-xl-6 col-lg-4 col-md-4 col-sm-6 col-6 mb-25">
                                                <div class="position-relative">
                                                    <div class="topic-box-top-xs">
                                                        <div class="topic-box-sm color-cod-gray mb-20">Pizza</div>
                                                    </div>
                                                    <a href="single-news-1.html" class="img-opacity-hover">
                                                        <img src="img/news/news299.jpg" alt="news" class="img-fluid width-100 mb-10">
                                                    </a>
                                                    <h3 class="title-medium-dark size-sm mb-none">
                                                        <a href="single-news-1.html">3 Students Arrested After Body.</a>
                                                    </h3>
                                                </div>
                                            </div>
                                            <div class="col-xl-6 col-lg-4 col-md-4 col-sm-6 col-6 mb-25">
                                                <div class="position-relative">
                                                    <div class="topic-box-top-xs">
                                                        <div class="topic-box-sm color-cod-gray mb-20">Chines</div>
                                                    </div>
                                                    <a href="single-news-1.html" class="img-opacity-hover">
                                                        <img src="img/news/news300.jpg" alt="news" class="img-fluid width-100 mb-10">
                                                    </a>
                                                    <h3 class="title-medium-dark size-sm mb-none">
                                                        <a href="single-news-1.html">Rosie Hutin ghton Habits Career.</a>
                                                    </h3>
                                                </div>
                                            </div>
                                            <div class="col-xl-6 col-lg-4 col-md-4 col-sm-6 col-6 mb-25">
                                                <div class="position-relative">
                                                    <div class="topic-box-top-xs">
                                                        <div class="topic-box-sm color-cod-gray mb-20">Drinks</div>
                                                    </div>
                                                    <a href="single-news-1.html" class="img-opacity-hover">
                                                        <img src="img/news/news301.jpg" alt="news" class="img-fluid width-100 mb-10">
                                                    </a>
                                                    <h3 class="title-medium-dark size-sm mb-none">
                                                        <a href="single-news-1.html">3 Students Arrested After Body.</a>
                                                    </h3>
                                                </div>
                                            </div>
                                            <div class="col-xl-6 col-lg-4 col-md-4 col-sm-6 col-6 mb-25">
                                                <div class="position-relative">
                                                    <div class="topic-box-top-xs">
                                                        <div class="topic-box-sm color-cod-gray mb-20">Fastfood</div>
                                                    </div>
                                                    <a href="single-news-1.html" class="img-opacity-hover">
                                                        <img src="img/news/news302.jpg" alt="news" class="img-fluid width-100 mb-10">
                                                    </a>
                                                    <h3 class="title-medium-dark size-sm mb-none">
                                                        <a href="single-news-1.html">Rosie Hutin ghton Habits Career.</a>
                                                    </h3>
                                                </div>
                                            </div>
                                            <div class="col-xl-6 col-lg-4 col-md-4 col-sm-6 col-6 mb-25">
                                                <div class="position-relative">
                                                    <div class="topic-box-top-xs">
                                                        <div class="topic-box-sm color-cod-gray mb-20">Desert</div>
                                                    </div>
                                                    <a href="single-news-1.html" class="img-opacity-hover">
                                                        <img src="img/news/news303.jpg" alt="news" class="img-fluid width-100 mb-10">
                                                    </a>
                                                    <h3 class="title-medium-dark size-sm mb-none">
                                                        <a href="single-news-1.html">3 Students Arrested After Body.</a>
                                                    </h3>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div role="tabpanel" class="tab-pane fade" id="popular">
                                        <div class="row">
                                            <div class="col-xl-6 col-lg-4 col-md-4 col-sm-6 col-6 mb-25">
                                                <div class="position-relative">
                                                    <div class="topic-box-top-xs">
                                                        <div class="topic-box-sm color-cod-gray mb-20">Chines</div>
                                                    </div>
                                                    <a href="single-news-1.html" class="img-opacity-hover">
                                                        <img src="img/news/news300.jpg" alt="news" class="img-fluid width-100 mb-10">
                                                    </a>
                                                    <h3 class="title-medium-dark size-sm mb-none">
                                                        <a href="single-news-1.html">Rosie Hutin ghton Habits Career.</a>
                                                    </h3>
                                                </div>
                                            </div>
                                            <div class="col-xl-6 col-lg-4 col-md-4 col-sm-6 col-6 mb-25">
                                                <div class="position-relative">
                                                    <div class="topic-box-top-xs">
                                                        <div class="topic-box-sm color-cod-gray mb-20">Drinks</div>
                                                    </div>
                                                    <a href="single-news-1.html" class="img-opacity-hover">
                                                        <img src="img/news/news301.jpg" alt="news" class="img-fluid width-100 mb-10">
                                                    </a>
                                                    <h3 class="title-medium-dark size-sm mb-none">
                                                        <a href="single-news-1.html">3 Students Arrested After Body.</a>
                                                    </h3>
                                                </div>
                                            </div>
                                            <div class="col-xl-6 col-lg-4 col-md-4 col-sm-6 col-6 mb-25">
                                                <div class="position-relative">
                                                    <div class="topic-box-top-xs">
                                                        <div class="topic-box-sm color-cod-gray mb-20">Food</div>
                                                    </div>
                                                    <a href="single-news-1.html" class="img-opacity-hover">
                                                        <img src="img/news/news298.jpg" alt="news" class="img-fluid width-100 mb-10">
                                                    </a>
                                                    <h3 class="title-medium-dark size-sm mb-none">
                                                        <a href="single-news-1.html">Rosie Hutin ghton Habits Career.</a>
                                                    </h3>
                                                </div>
                                            </div>
                                            <div class="col-xl-6 col-lg-4 col-md-4 col-sm-6 col-6 mb-25">
                                                <div class="position-relative">
                                                    <div class="topic-box-top-xs">
                                                        <div class="topic-box-sm color-cod-gray mb-20">Pizza</div>
                                                    </div>
                                                    <a href="single-news-1.html" class="img-opacity-hover">
                                                        <img src="img/news/news299.jpg" alt="news" class="img-fluid width-100 mb-10">
                                                    </a>
                                                    <h3 class="title-medium-dark size-sm mb-none">
                                                        <a href="single-news-1.html">3 Students Arrested After Body.</a>
                                                    </h3>
                                                </div>
                                            </div>
                                            <div class="col-xl-6 col-lg-4 col-md-4 col-sm-6 col-6 mb-25">
                                                <div class="position-relative">
                                                    <div class="topic-box-top-xs">
                                                        <div class="topic-box-sm color-cod-gray mb-20">Fastfood</div>
                                                    </div>
                                                    <a href="single-news-1.html" class="img-opacity-hover">
                                                        <img src="img/news/news302.jpg" alt="news" class="img-fluid width-100 mb-10">
                                                    </a>
                                                    <h3 class="title-medium-dark size-sm mb-none">
                                                        <a href="single-news-1.html">Rosie Hutin ghton Habits Career.</a>
                                                    </h3>
                                                </div>
                                            </div>
                                            <div class="col-xl-6 col-lg-4 col-md-4 col-sm-6 col-6 mb-25">
                                                <div class="position-relative">
                                                    <div class="topic-box-top-xs">
                                                        <div class="topic-box-sm color-cod-gray mb-20">Desert</div>
                                                    </div>
                                                    <a href="single-news-1.html" class="img-opacity-hover">
                                                        <img src="img/news/news303.jpg" alt="news" class="img-fluid width-100 mb-10">
                                                    </a>
                                                    <h3 class="title-medium-dark size-sm mb-none">
                                                        <a href="single-news-1.html">3 Students Arrested After Body.</a>
                                                    </h3>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div role="tabpanel" class="tab-pane fade" id="common">
                                        <div class="row">
                                            <div class="col-xl-6 col-lg-4 col-md-4 col-sm-6 col-6 mb-25">
                                                <div class="position-relative">
                                                    <div class="topic-box-top-xs">
                                                        <div class="topic-box-sm color-cod-gray mb-20">Fastfood</div>
                                                    </div>
                                                    <a href="single-news-1.html" class="img-opacity-hover">
                                                        <img src="img/news/news302.jpg" alt="news" class="img-fluid width-100 mb-10">
                                                    </a>
                                                    <h3 class="title-medium-dark size-sm mb-none">
                                                        <a href="single-news-1.html">Rosie Hutin ghton Habits Career.</a>
                                                    </h3>
                                                </div>
                                            </div>
                                            <div class="col-xl-6 col-lg-4 col-md-4 col-sm-6 col-6 mb-25">
                                                <div class="position-relative">
                                                    <div class="topic-box-top-xs">
                                                        <div class="topic-box-sm color-cod-gray mb-20">Desert</div>
                                                    </div>
                                                    <a href="single-news-1.html" class="img-opacity-hover">
                                                        <img src="img/news/news303.jpg" alt="news" class="img-fluid width-100 mb-10">
                                                    </a>
                                                    <h3 class="title-medium-dark size-sm mb-none">
                                                        <a href="single-news-1.html">3 Students Arrested After Body.</a>
                                                    </h3>
                                                </div>
                                            </div>
                                            <div class="col-xl-6 col-lg-4 col-md-4 col-sm-6 col-6 mb-25">
                                                <div class="position-relative">
                                                    <div class="topic-box-top-xs">
                                                        <div class="topic-box-sm color-cod-gray mb-20">Food</div>
                                                    </div>
                                                    <a href="single-news-1.html" class="img-opacity-hover">
                                                        <img src="img/news/news298.jpg" alt="news" class="img-fluid width-100 mb-10">
                                                    </a>
                                                    <h3 class="title-medium-dark size-sm mb-none">
                                                        <a href="single-news-1.html">Rosie Hutin ghton Habits Career.</a>
                                                    </h3>
                                                </div>
                                            </div>
                                            <div class="col-xl-6 col-lg-4 col-md-4 col-sm-6 col-6 mb-25">
                                                <div class="position-relative">
                                                    <div class="topic-box-top-xs">
                                                        <div class="topic-box-sm color-cod-gray mb-20">Pizza</div>
                                                    </div>
                                                    <a href="single-news-1.html" class="img-opacity-hover">
                                                        <img src="img/news/news299.jpg" alt="news" class="img-fluid width-100 mb-10">
                                                    </a>
                                                    <h3 class="title-medium-dark size-sm mb-none">
                                                        <a href="single-news-1.html">3 Students Arrested After Body.</a>
                                                    </h3>
                                                </div>
                                            </div>
                                            <div class="col-xl-6 col-lg-4 col-md-4 col-sm-6 col-6 mb-25">
                                                <div class="position-relative">
                                                    <div class="topic-box-top-xs">
                                                        <div class="topic-box-sm color-cod-gray mb-20">Chines</div>
                                                    </div>
                                                    <a href="single-news-1.html" class="img-opacity-hover">
                                                        <img src="img/news/news300.jpg" alt="news" class="img-fluid width-100 mb-10">
                                                    </a>
                                                    <h3 class="title-medium-dark size-sm mb-none">
                                                        <a href="single-news-1.html">Rosie Hutin ghton Habits Career.</a>
                                                    </h3>
                                                </div>
                                            </div>
                                            <div class="col-xl-6 col-lg-4 col-md-4 col-sm-6 col-6 mb-25">
                                                <div class="position-relative">
                                                    <div class="topic-box-top-xs">
                                                        <div class="topic-box-sm color-cod-gray mb-20">Drinks</div>
                                                    </div>
                                                    <a href="single-news-1.html" class="img-opacity-hover">
                                                        <img src="img/news/news301.jpg" alt="news" class="img-fluid width-100 mb-10">
                                                    </a>
                                                    <h3 class="title-medium-dark size-sm mb-none">
                                                        <a href="single-news-1.html">3 Students Arrested After Body.</a>
                                                    </h3>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>
            <!-- Latest Articles Area End Here -->
            <!-- Videos Area Start Here -->
            <section class="bg-secondary-body section-space-default">
                <div class="container">
                    <div class="row">
                        <div class="col-12">
                            <div class="topic-border color-cinnabar mb-30 width-100">
                                <div class="topic-box-lg color-cinnabar">Watch Videos</div>
                            </div>
                        </div>
                    </div>
                    <div class="ne-carousel nav-control-top2 color-white2" data-loop="true" data-items="3" data-margin="10" data-autoplay="true"
                        data-autoplay-timeout="5000" data-smart-speed="2000" data-dots="false" data-nav="true" data-nav-speed="false"
                        data-r-x-small="1" data-r-x-small-nav="true" data-r-x-small-dots="false" data-r-x-medium="1" data-r-x-medium-nav="true"
                        data-r-x-medium-dots="false" data-r-small="2" data-r-small-nav="true" data-r-small-dots="false" data-r-medium="2"
                        data-r-medium-nav="true" data-r-medium-dots="false" data-r-Large="3" data-r-Large-nav="true" data-r-Large-dots="false">
                        <div class="img-overlay-70-c">
                            <div class="mask-content-sm">
                                <div class="topic-box-sm color-cod-gray mb-20">Food</div>
                                <h3 class="title-medium-light">
                                    <a href="single-news-3.html">Gym Fitness area coverded they Governed this in 2017</a>
                                </h3>
                            </div>
                            <div class="text-center">
                                <a class="play-btn popup-youtube" href="http://www.youtube.com/watch?v=1iIZeIy7TqM">
                                    <img src="img/banner/play.png" alt="play" class="img-fluid">
                                </a>
                            </div>
                            <img src="img/news/news291.jpg" alt="news" class="img-fluid width-100">
                        </div>
                        <div class="img-overlay-70-c">
                            <div class="mask-content-sm">
                                <div class="topic-box-sm color-cod-gray mb-20">Desert</div>
                                <h3 class="title-medium-light">
                                    <a href="single-news-3.html">Gym Fitness area coverded they Governed this in 2017</a>
                                </h3>
                            </div>
                            <div class="text-center">
                                <a class="play-btn popup-youtube" href="http://www.youtube.com/watch?v=1iIZeIy7TqM">
                                    <img src="img/banner/play.png" alt="play" class="img-fluid">
                                </a>
                            </div>
                            <img src="img/news/news292.jpg" alt="news" class="img-fluid width-100">
                        </div>
                        <div class="img-overlay-70-c">
                            <div class="mask-content-sm">
                                <div class="topic-box-sm color-cod-gray mb-20">Chines</div>
                                <h3 class="title-medium-light">
                                    <a href="single-news-3.html">Gym Fitness area coverded they Governed this in 2017</a>
                                </h3>
                            </div>
                            <div class="text-center">
                                <a class="play-btn popup-youtube" href="http://www.youtube.com/watch?v=1iIZeIy7TqM">
                                    <img src="img/banner/play.png" alt="play" class="img-fluid">
                                </a>
                            </div>
                            <img src="img/news/news293.jpg" alt="news" class="img-fluid width-100">
                        </div>
                        <div class="img-overlay-70-c">
                            <div class="mask-content-sm">
                                <div class="topic-box-sm color-cod-gray mb-20">Food</div>
                                <h3 class="title-medium-light">
                                    <a href="single-news-3.html">Gym Fitness area coverded they Governed this in 2017</a>
                                </h3>
                            </div>
                            <div class="text-center">
                                <a class="play-btn popup-youtube" href="http://www.youtube.com/watch?v=1iIZeIy7TqM">
                                    <img src="img/banner/play.png" alt="play" class="img-fluid">
                                </a>
                            </div>
                            <img src="img/news/news291.jpg" alt="news" class="img-fluid width-100">
                        </div>
                        <div class="img-overlay-70-c">
                            <div class="mask-content-sm">
                                <div class="topic-box-sm color-cod-gray mb-20">Desert</div>
                                <h3 class="title-medium-light">
                                    <a href="single-news-3.html">Gym Fitness area coverded they Governed this in 2017</a>
                                </h3>
                            </div>
                            <div class="text-center">
                                <a class="play-btn popup-youtube" href="http://www.youtube.com/watch?v=1iIZeIy7TqM">
                                    <img src="img/banner/play.png" alt="play" class="img-fluid">
                                </a>
                            </div>
                            <img src="img/news/news292.jpg" alt="news" class="img-fluid width-100">
                        </div>
                        <div class="img-overlay-70-c">
                            <div class="mask-content-sm">
                                <div class="topic-box-sm color-cod-gray mb-20">Chines</div>
                                <h3 class="title-medium-light">
                                    <a href="single-news-3.html">Gym Fitness area coverded they Governed this in 2017</a>
                                </h3>
                            </div>
                            <div class="text-center">
                                <a class="play-btn popup-youtube" href="http://www.youtube.com/watch?v=1iIZeIy7TqM">
                                    <img src="img/banner/play.png" alt="play" class="img-fluid">
                                </a>
                            </div>
                            <img src="img/news/news293.jpg" alt="news" class="img-fluid width-100">
                        </div>
                    </div>
                </div>
            </section>
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
                
