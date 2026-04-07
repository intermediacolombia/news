<!doctype html>
<html class="no-js" lang="es">

<head>
    <meta charset="utf-8">
    <meta http-equiv="x-ua-compatible" content="ie=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- Favicon -->
    <link rel="shortcut icon" type="image/x-icon" href="<?= URLBASE . FAVICON ?>">

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

    <!-- ═══ Verificación de Buscadores ═══════════════════════════════════ -->
    <?php if (!empty($sys['verify_google'])): ?>
    <meta name="google-site-verification" content="<?= htmlspecialchars($sys['verify_google'], ENT_QUOTES, 'UTF-8') ?>">
    <?php endif; ?>

    <?php if (!empty($sys['verify_bing'])): ?>
    <meta name="msvalidate.01" content="<?= htmlspecialchars($sys['verify_bing'], ENT_QUOTES, 'UTF-8') ?>">
    <?php endif; ?>

    <?php if (!empty($sys['verify_yandex'])): ?>
    <meta name="yandex-verification" content="<?= htmlspecialchars($sys['verify_yandex'], ENT_QUOTES, 'UTF-8') ?>">
    <?php endif; ?>

    <?php if (!empty($sys['verify_meta'])): ?>
    <meta name="facebook-domain-verification" content="<?= htmlspecialchars($sys['verify_meta'], ENT_QUOTES, 'UTF-8') ?>">
    <?php endif; ?>

    <?php if (!empty($sys['verify_pinterest'])): ?>
    <meta name="p:domain_verify" content="<?= htmlspecialchars($sys['verify_pinterest'], ENT_QUOTES, 'UTF-8') ?>">
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

    <!-- ═══ Google Tag Manager (head) ════════════════════════════════════ -->
    <?php
    $gtmId = trim($sys['gtm_container_id'] ?? '');
    if (!empty($gtmId) && preg_match('/^GTM-[A-Z0-9]+$/i', $gtmId)):
    ?>
    <script>(function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':
    new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],
    j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src=
    'https://www.googletagmanager.com/gtm.js?id='+i+dl;f.parentNode.insertBefore(j,f);
    })(window,document,'script','dataLayer','<?= htmlspecialchars($gtmId, ENT_QUOTES, 'UTF-8') ?>');</script>
    <?php endif; ?>

    <!-- ═══ Google AdSense ════════════════════════════════════════════════ -->
    <?php
    $pubId = trim($sys['adsense_publisher_id'] ?? '');
    if (!empty($pubId) && preg_match('/^ca-pub-\d+$/', $pubId)):
    ?>
    <script async
        src="https://pagead2.googlesyndication.com/pagead/js/adsbygoogle.js?client=<?= htmlspecialchars($pubId, ENT_QUOTES, 'UTF-8') ?>"
        crossorigin="anonymous"></script>
    <?php endif; ?>

    <!-- Google Web Fonts -->
    <link rel="preconnect" href="https://fonts.gstatic.com">
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700;900&display=swap" rel="stylesheet">

    <!-- Normalize CSS -->
    <link rel="stylesheet" href="<?= URLBASE ?>/template/NewsEdge/css/normalize.css">
    <!-- Main CSS -->
    <link rel="stylesheet" href="<?= URLBASE ?>/template/NewsEdge/css/main.css">
    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="<?= URLBASE ?>/template/NewsEdge/css/bootstrap.min.css">
    <!-- Animate CSS -->
    <link rel="stylesheet" href="<?= URLBASE ?>/template/NewsEdge/css/animate.min.css">
    <!-- Font-awesome CSS -->
    <link rel="stylesheet" href="<?= URLBASE ?>/template/NewsEdge/css/font-awesome.min.css">
    <!-- Owl Carousel CSS -->
    <link rel="stylesheet" href="<?= URLBASE ?>/template/NewsEdge/vendor/OwlCarousel/owl.carousel.min.css">
    <link rel="stylesheet" href="<?= URLBASE ?>/template/NewsEdge/vendor/OwlCarousel/owl.theme.default.min.css">
    <!-- Main Menu CSS -->
    <link rel="stylesheet" href="<?= URLBASE ?>/template/NewsEdge/css/meanmenu.min.css">
    <!-- Nivo Slider CSS -->
    <link rel="stylesheet" href="<?= URLBASE ?>/template/NewsEdge/vendor/slider/css/nivo-slider.css" type="text/css">
    <link rel="stylesheet" href="<?= URLBASE ?>/template/NewsEdge/vendor/slider/css/preview.css" type="text/css" media="screen">
    <!-- Magnific CSS -->
    <link rel="stylesheet" href="<?= URLBASE ?>/template/NewsEdge/css/magnific-popup.css">
    <!-- Hover CSS -->
    <link rel="stylesheet" href="<?= URLBASE ?>/template/NewsEdge/css/hover-min.css">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="<?= URLBASE ?>/template/NewsEdge/style.css?<?= time() ?>">
    <!-- IE Only -->
    <link rel="stylesheet" href="<?= URLBASE ?>/template/NewsEdge/css/ie-only.css">

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <!-- Modernizr -->
    <script src="<?= URLBASE ?>/template/NewsEdge/js/modernizr-2.8.3.min.js"></script>

    <!-- Facebook SDK -->
    <div id="fb-root"></div>
    <script async defer crossorigin="anonymous"
        src="https://connect.facebook.net/es_LA/sdk.js#xfbml=1&version=v23.0"></script>

    <!-- Código HEAD personalizado (admin) -->
    <?= $sys['code_head'] ?? '' ?>

    <!-- CSS Variables Dinámicas -->
    <style>
    :root {
        --primary:          <?= htmlspecialchars($sys['primary']          ?? '#007bff', ENT_QUOTES, 'UTF-8') ?>;
        --color-hover-link: <?= htmlspecialchars($sys['color-hover-link'] ?? '#0056b3', ENT_QUOTES, 'UTF-8') ?>;
        --blue: #007bff; --indigo: #6610f2; --purple: #6f42c1;
        --pink: #e83e8c; --red: #dc3545; --orange: #fd7e14;
        --yellow: #ffc107; --green: #28a745; --teal: #20c997;
        --cyan: #17a2b8; --white: #fff; --gray: #6c757d;
        --gray-dark: #343a40; --success: #28a745; --info: #17a2b8;
        --warning: #ffc107; --danger: #dc3545; --light: #ffffff; --dark: #343a40;
        --breakpoint-xs: 0; --breakpoint-sm: 576px; --breakpoint-md: 768px;
        --breakpoint-lg: 992px; --breakpoint-xl: 1200px;
        --font-family-sans-serif: "Roboto", sans-serif;
        --font-family-monospace: SFMono-Regular, Menlo, Monaco, Consolas, monospace;
    }

    #preloader {
        background: url(<?= URLBASE . SITE_LOGO ?>?<?= time() ?>) center center no-repeat var(--primary) !important;
    }

    /* ── Slider tamaño fijo ───────────────────────────────────────────── */
    .main-slider1 { height: 610px; overflow: hidden; position: relative; }
    .main-slider1 .bend.niceties.preview-1 { height: 610px !important; overflow: hidden; }
    .main-slider1 #ensign-nivoslider-3,
    .main-slider1 #ensign-nivoslider-3 img {
        width: 100%; height: 610px !important;
        object-fit: cover; object-position: center;
    }
    .main-slider1 .slider-direction { height: 610px; }

    @media only screen and (max-width: 991px) {
        .main-slider1, .main-slider1 .bend.niceties.preview-1,
        .main-slider1 #ensign-nivoslider-3, .main-slider1 #ensign-nivoslider-3 img,
        .main-slider1 .slider-direction { height: 400px !important; }
    }
    @media only screen and (max-width: 767px) {
        .main-slider1, .main-slider1 .bend.niceties.preview-1,
        .main-slider1 #ensign-nivoslider-3, .main-slider1 #ensign-nivoslider-3 img,
        .main-slider1 .slider-direction { height: 350px !important; }
    }
    @media only screen and (max-width: 575px) {
        .main-slider1, .main-slider1 .bend.niceties.preview-1,
        .main-slider1 #ensign-nivoslider-3, .main-slider1 #ensign-nivoslider-3 img,
        .main-slider1 .slider-direction { height: 280px !important; }
    }

    /* ── Layout ───────────────────────────────────────────────────────── */
    .img-fluid-home { max-width: 140px; height: auto; }

    @media (min-width: 1200px) {
        .container, .container-lg, .container-md,
        .container-sm, .container-xl { max-width: 1300px; }
    }

    .topic-border { position: relative; padding-right: 140px; }
    .more-info-link {
        position: absolute; right: 0; top: 50%;
        transform: translateY(-50%); margin-left: 20px; white-space: nowrap;
    }
    .isotope-classes-tab { padding-right: 20px; }

    /* ── Search modal ─────────────────────────────────────────────────── */
    .ne-search-modal .modal-dialog { max-width: 680px; }
    .ne-search-modal-content {
        border: 0; border-radius: 14px; overflow: hidden;
        box-shadow: 0 30px 80px rgba(0,0,0,.35);
    }
    .ne-search-modal-body { position: relative; padding: 28px 26px 24px; background: #fff; }
    .ne-search-close { position: absolute; right: 14px; top: 10px; font-size: 32px; opacity: .6; }
    .ne-search-close:hover { opacity: 1; }
    .ne-search-title { margin: 0 0 6px; font-weight: 800; font-size: 24px; color: #111; }
    .ne-search-subtitle { margin: 0 0 18px; color: #666; font-size: 14px; }
    .ne-search-input {
        width: 100%; height: 54px; border: 1px solid #e6e6e6;
        border-radius: 10px; padding: 0 16px; font-size: 16px; transition: .2s ease;
    }
    .ne-search-input:focus {
        outline: none; border-color: var(--primary, #c41e3a);
        box-shadow: 0 0 0 4px rgba(196,30,58,.12);
    }
    .ne-search-btn {
        margin-top: 12px; width: 100%; height: 48px; border: 0;
        border-radius: 10px; background: var(--primary, #c41e3a);
        color: #fff; font-weight: 700; font-size: 16px; cursor: pointer; transition: .2s ease;
    }
    .ne-search-btn:hover { filter: brightness(.95); }
    .header-search-trigger {
        background: transparent; border: 0; padding: 8px 10px;
        cursor: pointer; font-size: 18px; color: #111;
    }
    .header-search-trigger:hover { color: var(--primary, #c41e3a); }
    </style>

<?php if (!empty($pubId) && defined('ADSENSE_AUTO_ADS') && ADSENSE_AUTO_ADS !== '1'): ?>
<script src="<?= URLBASE ?>/admin/publicidad/ads_config.php"></script>
<script defer src="<?= URLBASE ?>/public/js/ads-injector.js"></script>
<?php endif; ?>

<?php if (function_exists('renderPopup')) echo renderPopup(); ?>
</head>

<body>
    <!--[if lt IE 8]>
    <p class="browserupgrade">You are using an <strong>outdated</strong> browser.
    Please <a href="http://browsehappy.com/">upgrade your browser</a>.</p>
    <![endif]-->

    <!-- ═══ Google Tag Manager (body noscript) ════════════════════════════ -->
    <?php if (!empty($gtmId) && preg_match('/^GTM-[A-Z0-9]+$/i', $gtmId)): ?>
    <noscript><iframe
        src="https://www.googletagmanager.com/ns.html?id=<?= htmlspecialchars($gtmId, ENT_QUOTES, 'UTF-8') ?>"
        height="0" width="0" style="display:none;visibility:hidden"></iframe></noscript>
    <?php endif; ?>

    <?php include __DIR__ . "/menu-header.php"; ?>
    <div id="appRoot">