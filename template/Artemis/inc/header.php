<!doctype html>
<html class="no-js" lang="es" data-theme="light">

<head>
    <meta charset="utf-8">
    <meta http-equiv="x-ua-compatible" content="ie=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <link rel="shortcut icon" type="image/x-icon" href="<?= URLBASE . FAVICON ?>">

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

    <?php
    $pubId = trim($sys['adsense_publisher_id'] ?? '');
    if (!empty($pubId) && preg_match('/^ca-pub-\d+$/', $pubId)):
    ?>
    <script async
        src="https://pagead2.googlesyndication.com/pagead/js/adsbygoogle.js?client=<?= htmlspecialchars($pubId, ENT_QUOTES, 'UTF-8') ?>"
        crossorigin="anonymous"></script>
    <?php endif; ?>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&family=Playfair+Display:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/meanmenu@2.0.12/meanmenu.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css">
    <link rel="stylesheet" href="<?= URLBASE ?>/template/Artemis/style.css?<?= time() ?>">

    <div id="fb-root"></div>
    <script async defer crossorigin="anonymous"
        src="https://connect.facebook.net/es_LA/sdk.js#xfbml=1&version=v23.0"></script>

    <?= $sys['code_head'] ?? '' ?>

    <style>
        /* === Variables: Modo Claro (default) === */
        :root {
            --primary: <?= htmlspecialchars($sys['primary'] ?? '#e63946', ENT_QUOTES, 'UTF-8') ?>;
            --primary-dark: #c1121f;
            --secondary: #1d3557;
            --accent: #f4a261;
            --bg-color: #ffffff;
            --bg-secondary: #f8f9fa;
            --text-color: #212529;
            --text-muted: #6c757d;
            --text-muted-color: #6c757d;
            --border-color: rgba(0,0,0,0.1);
            --dark: #ffffff;
            --dark-secondary: #f8f9fa;
            --gradient-hero: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 50%, #dee2e6 100%);
            --gradient-card: linear-gradient(145deg, #f8f9fa 0%, #ffffff 100%);
            --shadow-glow: 0 0 40px rgba(230, 57, 70, 0.15);
        }

        /* === Variables: Modo Oscuro === */
        [data-theme="dark"] {
            --bg-color: #0d1117;
            --bg-secondary: #161b22;
            --text-color: #e6edf3;
            --text-muted: #8b949e;
            --text-muted-color: #8b949e;
            --border-color: rgba(255,255,255,0.08);
            --dark: #0d1117;
            --dark-secondary: #161b22;
            --gradient-hero: linear-gradient(135deg, #0d1117 0%, #161b22 50%, #1d3557 100%);
            --gradient-card: linear-gradient(145deg, #1d3557 0%, #0d1117 100%);
            --shadow-glow: 0 0 40px rgba(230, 57, 70, 0.25);
        }

        /* === Base === */
        body {
            font-family: 'Outfit', sans-serif;
            background: var(--bg-color);
            color: var(--text-color);
            transition: background 0.3s ease, color 0.3s ease;
        }

        h1, h2, h3, h4, h5, h6 {
            color: var(--text-color);
        }

        #preloader {
            background: url(<?= URLBASE . SITE_LOGO ?>?<?= time() ?>) center center no-repeat var(--primary) !important;
        }

        /* === Navbar === */
        .artemis-navbar {
            background: var(--bg-secondary);
            backdrop-filter: blur(20px);
            border-bottom: 1px solid var(--border-color);
            padding: 15px 0;
            transition: all 0.3s ease;
        }

        .artemis-navbar.scrolled {
            padding: 10px 0;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
        }

        .artemis-navbar .navbar-brand img {
            max-height: 50px;
        }

        .artemis-navbar .nav-link {
            color: var(--text-color) !important;
            font-weight: 500;
            font-size: 14px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            transition: all 0.3s ease;
            position: relative;
        }

        .artemis-navbar .nav-link:hover {
            color: var(--primary) !important;
        }

        .artemis-navbar .nav-link::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 50%;
            width: 0;
            height: 2px;
            background: var(--primary);
            transition: all 0.3s ease;
            transform: translateX(-50%);
        }

        .artemis-navbar .nav-link:hover::after,
        .artemis-navbar .nav-link.active::after {
            width: 80%;
        }

        /* === Dropdown === */
        .dropdown-menu {
            background: var(--bg-secondary) !important;
            border: 1px solid var(--border-color) !important;
            border-radius: 12px !important;
        }

        .dropdown-item {
            color: var(--text-color) !important;
            border-radius: 8px !important;
            transition: all 0.3s ease !important;
        }

        .dropdown-item:hover {
            background: var(--primary) !important;
            color: #fff !important;
        }

        /* === Hero === */
        .hero-section {
            background: var(--gradient-hero);
            position: relative;
            overflow: hidden;
            min-height: 500px;
        }

        .hero-section::before {
            content: '';
            position: absolute;
            top: 0; left: 0; right: 0; bottom: 0;
            background:
                radial-gradient(circle at 20% 80%, rgba(230,57,70,0.15) 0%, transparent 50%),
                radial-gradient(circle at 80% 20%, rgba(29,53,87,0.3) 0%, transparent 50%);
        }

        .hero-section .pattern-overlay {
            position: absolute;
            top: 0; left: 0;
            width: 100%; height: 100%;
            background-image:
                linear-gradient(rgba(0,0,0,0.04) 1px, transparent 1px),
                linear-gradient(90deg, rgba(0,0,0,0.04) 1px, transparent 1px);
            background-size: 50px 50px;
        }

        .hero-title {
            font-family: 'Playfair Display', serif;
            font-size: 3.5rem;
            font-weight: 700;
            line-height: 1.1;
            color: var(--text-color);
        }

        /* === Cards === */
        .news-card {
            background: var(--dark-secondary);
            border-radius: 16px;
            overflow: hidden;
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            border: 1px solid var(--border-color);
        }

        .news-card:hover {
            transform: translateY(-8px);
            box-shadow: var(--shadow-glow);
        }

        .news-card .card-img {
            height: 220px;
            object-fit: cover;
            transition: transform 0.4s ease;
        }

        .news-card:hover .card-img {
            transform: scale(1.05);
        }

        .news-card p,
        .news-card span:not(.category-badge),
        .news-card h1, .news-card h2, .news-card h3,
        .news-card h4, .news-card h5, .news-card h6 {
            color: var(--text-color);
        }

        /* === Badges & Titles === */
        .category-badge {
            background: var(--primary);
            color: #fff !important;
            padding: 6px 16px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .section-title {
            font-family: 'Playfair Display', serif;
            font-size: 2.5rem;
            font-weight: 700;
            position: relative;
            display: inline-block;
            color: var(--text-color);
        }

        .section-title::after {
            content: '';
            position: absolute;
            bottom: -10px;
            left: 0;
            width: 60px;
            height: 4px;
            background: var(--primary);
            border-radius: 2px;
        }

        /* === Ticker === */
        .ticker-wrapper {
            background: linear-gradient(90deg, var(--primary) 0%, var(--primary-dark) 100%);
            padding: 12px 0;
            overflow: hidden;
        }

        .ticker-wrapper .ticker-label {
            background: #fff;
            color: var(--primary);
            padding: 8px 20px;
            font-weight: 700;
            text-transform: uppercase;
            font-size: 12px;
            letter-spacing: 1px;
            margin-right: 20px;
            border-radius: 4px;
        }

        /* === Sidebar & Footer === */
        .sidebar-section {
            background: var(--dark-secondary);
            border-radius: 16px;
        }

        .footer-section {
            background: var(--dark-secondary);
            border-top: 1px solid var(--border-color);
            color: var(--text-color);
        }

        .footer-section h1, .footer-section h2, .footer-section h3,
        .footer-section h4, .footer-section h5, .footer-section h6 {
            color: var(--text-color);
        }

        /* === Search Modal === */
        .search-modal .modal-content {
            background: var(--bg-secondary);
            border: 1px solid var(--border-color);
            border-radius: 20px;
        }

        .search-input {
            background: var(--bg-color);
            border: 2px solid var(--border-color);
            color: var(--text-color);
            padding: 15px 20px;
            border-radius: 12px;
            font-size: 16px;
            transition: all 0.3s ease;
            width: 100%;
        }

        .search-input:focus {
            outline: none;
            border-color: var(--primary);
        }

        .search-input::placeholder {
            color: var(--text-muted);
        }

        /* === Buttons === */
        .btn-artemis {
            background: var(--primary);
            color: #fff;
            padding: 14px 32px;
            border-radius: 12px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            transition: all 0.3s ease;
            border: none;
        }

        .btn-artemis:hover {
            background: var(--primary-dark);
            transform: translateY(-2px);
            box-shadow: 0 10px 30px rgba(230,57,70,0.3);
            color: #fff;
        }

        /* === Filter === */
        .isotope-filter .filter-btn {
            background: transparent;
            border: 2px solid var(--border-color);
            color: var(--text-color);
            padding: 10px 24px;
            border-radius: 30px;
            margin: 5px;
            font-weight: 500;
            transition: all 0.3s ease;
            cursor: pointer;
        }

        .isotope-filter .filter-btn:hover,
        .isotope-filter .filter-btn.active {
            background: var(--primary);
            border-color: var(--primary);
            color: #fff;
        }

        /* === Post Content === */
        .post-content {
            color: var(--text-color);
            font-size: 16px;
            line-height: 1.8;
        }

        .post-content h1, .post-content h2, .post-content h3,
        .post-content h4, .post-content h5, .post-content h6 {
            color: var(--text-color);
        }

        /* === Hero overlay (siempre blanco, sobre imagen oscura) === */
        .hero-card .hero-title,
        .hero-card .hero-title a {
            color: #fff !important;
        }

        /* === Audio player (sobre fondo primary) === */
        .audio-player-modern span {
            color: #fff;
        }

        /* === Paginación activa === */
        .pagination-active {
            background: var(--primary);
            color: #fff !important;
        }

        /* === Responsive === */
        @media (max-width: 991px) {
            .hero-title { font-size: 2.5rem; }
        }

        @media (max-width: 767px) {
            .hero-title { font-size: 2rem; }
            .section-title { font-size: 1.8rem; }
        }
    </style>

<?php if (!empty($pubId) && defined('ADSENSE_AUTO_ADS') && ADSENSE_AUTO_ADS !== '1'): ?>
<script src="<?= URLBASE ?>/admin/publicidad/ads_config.php"></script>
<script defer src="<?= URLBASE ?>/public/js/ads-injector.js"></script>
<?php endif; ?>

</head>

<body>
    <?php if (!empty($gtmId) && preg_match('/^GTM-[A-Z0-9]+$/i', $gtmId)): ?>
    <noscript><iframe
        src="https://www.googletagmanager.com/ns.html?id=<?= htmlspecialchars($gtmId, ENT_QUOTES, 'UTF-8') ?>"
        height="0" width="0" style="display:none;visibility:hidden"></iframe></noscript>
    <?php endif; ?>

    <?php include __DIR__ . "/menu-header.php"; ?>
    <div id="appRoot">