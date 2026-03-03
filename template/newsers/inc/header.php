<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="utf-8">
    <meta content="width=device-width, initial-scale=1.0" name="viewport">

    <!-- ═══ SEO Dinámico ══════════════════════════════════════════════════ -->
    <?php if (!empty($page_title)): ?>
        <title><?= htmlspecialchars($page_title) ?></title>
        <meta property="og:title" content="<?= htmlspecialchars($page_title) ?>">
        <meta name="twitter:title" content="<?= htmlspecialchars($page_title) ?>">
    <?php else: ?>
        <title><?= htmlspecialchars($sys['site_name'] ?? 'Newsers') ?></title>
    <?php endif; ?>

    <?php if (!empty($page_description)): ?>
        <meta name="description" content="<?= htmlspecialchars($page_description) ?>">
        <meta property="og:description" content="<?= htmlspecialchars($page_description) ?>">
        <meta name="twitter:description" content="<?= htmlspecialchars($page_description) ?>">
    <?php elseif (!empty($sys['seo_home_description'])): ?>
        <meta name="description" content="<?= htmlspecialchars($sys['seo_home_description']) ?>">
        <meta property="og:description" content="<?= htmlspecialchars($sys['seo_home_description']) ?>">
        <meta name="twitter:description" content="<?= htmlspecialchars($sys['seo_home_description']) ?>">
    <?php endif; ?>

    <?php if (!empty($page_keywords)): ?>
        <meta name="keywords" content="<?= htmlspecialchars($page_keywords) ?>">
    <?php elseif (!empty($sys['seo_home_keywords'])): ?>
        <meta name="keywords" content="<?= htmlspecialchars($sys['seo_home_keywords']) ?>">
    <?php endif; ?>

    <?php if (!empty($page_author)): ?>
        <meta name="author" content="<?= htmlspecialchars($page_author) ?>">
    <?php endif; ?>

    <?php if (!empty($page_canonical)): ?>
        <link rel="canonical" href="<?= htmlspecialchars($page_canonical) ?>">
        <meta property="og:url" content="<?= htmlspecialchars($page_canonical) ?>">
    <?php endif; ?>

    <?php if (!empty($page_image)): ?>
        <meta property="og:image" content="<?= htmlspecialchars($page_image) ?>">
        <meta name="twitter:image" content="<?= htmlspecialchars($page_image) ?>">
    <?php endif; ?>

    <meta property="og:type" content="article">
    <meta name="twitter:card" content="summary_large_image">

    <!-- ═══ Favicon ══════════════════════════════════════════════════════ -->
    <link rel="shortcut icon" type="image/x-icon"
          href="<?= URLBASE . ($sys['site_favicon'] ?? '/public/images/favicon.png') ?>">

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

    <!-- ═══ Google Web Fonts ═════════════════════════════════════════════ -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Open+Sans:wght@400;600&family=Raleway:wght@100;600;800&display=swap" rel="stylesheet">

    <!-- Icon Font Stylesheet -->
    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.15.4/css/all.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.4.1/font/bootstrap-icons.css" rel="stylesheet">

    <!-- Libraries Stylesheet -->
    <link href="<?= URLBASE ?>/template/newsers/lib/animate/animate.min.css" rel="stylesheet">
    <link href="<?= URLBASE ?>/template/newsers/lib/owlcarousel/assets/owl.carousel.min.css" rel="stylesheet">

    <!-- Bootstrap -->
    <link href="<?= URLBASE ?>/template/newsers/css/bootstrap.min.css" rel="stylesheet">

    <!-- Template Stylesheet -->
    <link href="<?= URLBASE ?>/template/newsers/css/style.css?<?= time() ?>" rel="stylesheet">
    <link href="<?= URLBASE ?>/template/newsers/css/custom.css?<?= time() ?>" rel="stylesheet">

    <!-- Facebook SDK -->
    <div id="fb-root"></div>
    <script async defer crossorigin="anonymous"
        src="https://connect.facebook.net/es_LA/sdk.js#xfbml=1&version=v23.0&appId=APP_ID"></script>

    <!-- Código adicional desde configuración -->
    <?= $sys['code_head'] ?? '' ?>

    <!-- Variables de color -->
    <style>
        :root {
            --primary:          <?= htmlspecialchars($sys['primary']          ?? '#007bff', ENT_QUOTES, 'UTF-8') ?>;
            --color-hover-link: <?= htmlspecialchars($sys['color-hover-link'] ?? '#0056b3', ENT_QUOTES, 'UTF-8') ?>;
        }
    </style>
<?php if (!empty($pubId) && ADSENSE_AUTO_ADS !== '1'): ?>
<script src="<?= URLBASE ?>/admin/publicidad/ads_config.php"></script>
<script defer src="<?= URLBASE ?>/public/js/ads-injector.js"></script>
<?php endif; ?>
</head>

<body>

    <!-- ═══ Google Tag Manager (body noscript) ════════════════════════════ -->
    <?php if (!empty($gtmId) && preg_match('/^GTM-[A-Z0-9]+$/i', $gtmId)): ?>
    <noscript><iframe
        src="https://www.googletagmanager.com/ns.html?id=<?= htmlspecialchars($gtmId, ENT_QUOTES, 'UTF-8') ?>"
        height="0" width="0" style="display:none;visibility:hidden"></iframe></noscript>
    <?php endif; ?>

    <?php include __DIR__ . "/menu-header.php"; ?>
    <div id="appRoot">