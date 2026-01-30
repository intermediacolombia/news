<!doctype html>
<html class="no-js" lang="es">

<head>
    <meta charset="utf-8">
    <meta http-equiv="x-ua-compatible" content="ie=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    
    <!-- Favicon -->
    <link rel="shortcut icon" type="image/x-icon" href="<?php echo URLBASE . FAVICON; ?>">
    
    <!-- Site Metas -->
    <?php if (!empty($page_title)): ?>
    <title><?= htmlspecialchars($page_title) ?></title>
    <?php else: ?>
    <title><?= NOMBRE_SITIO ?> | Noticias</title>
    <?php endif; ?>

    <?php if (!empty($page_description)): ?>
    <meta name="description" content="<?= htmlspecialchars($page_description) ?>">
    <?php endif; ?>

    <?php if (!empty($page_keywords)): ?>
    <meta name="keywords" content="<?= htmlspecialchars($page_keywords) ?>">
    <?php endif; ?>

    <?php if (!empty($page_author)): ?>
    <meta name="author" content="<?= htmlspecialchars($page_author) ?>">
    <?php endif; ?>

    <?php if (!empty($page_canonical)): ?>
    <link rel="canonical" href="<?= htmlspecialchars($page_canonical) ?>">
    <?php endif; ?>

    <!-- Open Graph -->
    <?php if (!empty($page_title)): ?>
    <meta property="og:title" content="<?= htmlspecialchars($page_title) ?>">
    <?php endif; ?>

    <?php if (!empty($page_description)): ?>
    <meta property="og:description" content="<?= htmlspecialchars($page_description) ?>">
    <?php endif; ?>

    <?php if (!empty($page_image)): ?>
    <meta property="og:image" content="<?= htmlspecialchars($page_image) ?>">
    <?php endif; ?>

    <?php if (!empty($page_canonical)): ?>
    <meta property="og:url" content="<?= htmlspecialchars($page_canonical) ?>">
    <?php endif; ?>
    <meta property="og:type" content="website">

    <!-- Twitter Card -->
    <meta name="twitter:card" content="summary_large_image">
    <?php if (!empty($page_title)): ?>
    <meta name="twitter:title" content="<?= htmlspecialchars($page_title) ?>">
    <?php endif; ?>
    <?php if (!empty($page_description)): ?>
    <meta name="twitter:description" content="<?= htmlspecialchars($page_description) ?>">
    <?php endif; ?>
    <?php if (!empty($page_image)): ?>
    <meta name="twitter:image" content="<?= htmlspecialchars($page_image) ?>">
    <?php endif; ?>

    <!-- Google Web Fonts -->
    <link rel="preconnect" href="https://fonts.gstatic.com">
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700;900&display=swap" rel="stylesheet">

    <!-- Normalize CSS -->
    <link rel="stylesheet" href="<?php echo URLBASE; ?>/template/NewsEdge/css/normalize.css">
    <!-- Main CSS -->
    <link rel="stylesheet" href="<?php echo URLBASE; ?>/template/NewsEdge/css/main.css">
    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="<?php echo URLBASE; ?>/template/NewsEdge/css/bootstrap.min.css">
    <!-- Animate CSS -->
    <link rel="stylesheet" href="<?php echo URLBASE; ?>/template/NewsEdge/css/animate.min.css">
    <!-- Font-awesome CSS-->
    <link rel="stylesheet" href="<?php echo URLBASE; ?>/template/NewsEdge/css/font-awesome.min.css">
    <!-- Owl Carousel CSS -->
    <link rel="stylesheet" href="<?php echo URLBASE; ?>/template/NewsEdge/vendor/OwlCarousel/owl.carousel.min.css">
    <link rel="stylesheet" href="<?php echo URLBASE; ?>/template/NewsEdge/vendor/OwlCarousel/owl.theme.default.min.css">
    <!-- Main Menu CSS -->
    <link rel="stylesheet" href="<?php echo URLBASE; ?>/template/NewsEdge/css/meanmenu.min.css">
    <!-- Nivo Slider CSS-->
    <link rel="stylesheet" href="<?php echo URLBASE; ?>/template/NewsEdge/vendor/slider/css/nivo-slider.css" type="text/css" />
    <link rel="stylesheet" href="<?php echo URLBASE; ?>/template/NewsEdge/vendor/slider/css/preview.css" type="text/css" media="screen" />
    <!-- Magnific CSS -->
    <link rel="stylesheet" type="text/css" href="<?php echo URLBASE; ?>/template/NewsEdge/css/magnific-popup.css">
    <!-- Switch Style CSS -->
    <link rel="stylesheet" href="<?php echo URLBASE; ?>/template/NewsEdge/css/hover-min.css">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="<?php echo URLBASE; ?>/template/NewsEdge/style.css?<?php echo time(); ?>">
    <!-- For IE -->
    <link rel="stylesheet" type="text/css" href="<?php echo URLBASE; ?>/template/NewsEdge/css/ie-only.css" />
    
    <!-- Font Awesome Kit -->
    <script src="https://kit.fontawesome.com/332d1c4e86.js" crossorigin="anonymous"></script>
    
    <!-- Modernizr Js -->
    <script src="<?php echo URLBASE; ?>/template/NewsEdge/js/modernizr-2.8.3.min.js"></script>

    <!-- Facebook SDK -->
    <div id="fb-root"></div>
    <script async defer crossorigin="anonymous" src="https://connect.facebook.net/es_LA/sdk.js#xfbml=1&version=v23.0"></script>

    <!-- Custom Head Code -->
    <?= $sys['code_head'] ?? '' ?>

    <!-- CSS Variables Dinámicas -->
    <style>
    :root {
        --blue: #007bff;
        --indigo: #6610f2;
        --purple: #6f42c1;
        --pink: #e83e8c;
        --red: #dc3545;
        --orange: #fd7e14;
        --yellow: #ffc107;
        --green: #28a745;
        --teal: #20c997;
        --cyan: #17a2b8;
        --white: #fff;
        --gray: #6c757d;
        --gray-dark: #343a40;
        --primary: <?= $sys['primary'] ?? '#007bff' ?>;
        --color-hover-link: <?= $sys['color-hover-link'] ?? '#0056b3' ?>;
        --success: #28a745;
        --info: #17a2b8;
        --warning: #ffc107;
        --danger: #dc3545;
        --light: #ffffff;
        --dark: #343a40;
        --breakpoint-xs: 0;
        --breakpoint-sm: 576px;
        --breakpoint-md: 768px;
        --breakpoint-lg: 992px;
        --breakpoint-xl: 1200px;
        --font-family-sans-serif: "Roboto", sans-serif;
        --font-family-monospace: SFMono-Regular, Menlo, Monaco, Consolas, "Liberation Mono", "Courier New", monospace;
    }
		
		#preloader {
    background: url(<?php echo URLBASE . SITE_LOGO; ?>?<?php echo time(); ?>) center center no-repeat var(--primary) !important;
		}
		
		
		/* ========================================
   FIX: SLIDER TAMAÑO FIJO
   Mantiene el slider con altura constante
   ======================================== */

/* Contenedor principal del slider */
.main-slider1 {
    height: 532px; /* Altura fija del slider */
    overflow: hidden;
    position: relative;
}

/* Contenedor del Nivo Slider */
.main-slider1 .bend.niceties.preview-1 {
    height: 532px !important;
    overflow: hidden;
}
		
		.img-fluid-home{
    max-width: 140px;
    height: auto;
}

/* Imágenes del slider */
.main-slider1 #ensign-nivoslider-3,
.main-slider1 #ensign-nivoslider-3 img {
    width: 100%;
    height: 532px !important;
    object-fit: cover; /* Recorta la imagen para que encaje sin deformarse */
    object-position: center; /* Centra la imagen */
}

/* Contenedor de las direcciones del slider */
.main-slider1 .slider-direction {
    height: 532px;
}

/* ========================================
   RESPONSIVE: Ajusta altura en móviles
   ======================================== */

/* Tablets */
@media only screen and (max-width: 991px) {
    .main-slider1,
    .main-slider1 .bend.niceties.preview-1,
    .main-slider1 #ensign-nivoslider-3,
    .main-slider1 #ensign-nivoslider-3 img,
    .main-slider1 .slider-direction {
        height: 400px !important;
    }
}

/* Móviles grandes */
@media only screen and (max-width: 767px) {
    .main-slider1,
    .main-slider1 .bend.niceties.preview-1,
    .main-slider1 #ensign-nivoslider-3,
    .main-slider1 #ensign-nivoslider-3 img,
    .main-slider1 .slider-direction {
        height: 350px !important;
    }
}

/* Móviles pequeños */
@media only screen and (max-width: 575px) {
    .main-slider1,
    .main-slider1 .bend.niceties.preview-1,
    .main-slider1 #ensign-nivoslider-3,
    .main-slider1 #ensign-nivoslider-3 img,
    .main-slider1 .slider-direction {
        height: 280px !important;
    }
}


/* ========================================
   ALTERNATIVA: Si object-fit no funciona
   (para navegadores antiguos)
   ======================================== */

/* Descomenta esta sección si object-fit no funciona */
/*
.main-slider1 #ensign-nivoslider-3 img {
    width: 100%;
    height: 500px !important;
    min-height: 500px;
    max-height: 500px;
}
*/
	
		
	@media (min-width: 1200px) {
    .container, .container-lg, .container-md, .container-sm, .container-xl {
        max-width: 1300px;
    }
}
	.topic-border {
    position: relative;
    padding-right: 140px; /* ⬅️ espacio reservado para "Ver más" */
}

.more-info-link {
    position: absolute;
    right: 0;
    top: 50%;
    transform: translateY(-50%);
    margin-left: 20px; /* separación visual */
    white-space: nowrap;
}

/* separación interna entre categorías y borde derecho */
.isotope-classes-tab {
    padding-right: 20px;
}

    </style>
</head>

<body>
    <!--[if lt IE 8]>
        <p class="browserupgrade">You are using an 
            <strong>outdated</strong> browser. Please 
            <a href="http://browsehappy.com/">upgrade your browser</a> to improve your experience.
        </p>
    <![endif]-->

    <?php include __DIR__ . "/menu-header.php"; ?>