
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="utf-8">
    <meta content="width=device-width, initial-scale=1.0" name="viewport">

    <!-- ═══ Favicon ═══════════════════════════════════════════════════════ -->
    <link rel="shortcut icon" type="image/x-icon" href="<?= URLBASE ?><?= FAVICON ?>">

    <!-- ═══ SEO: Title, Description, Keywords ════════════════════════════ -->
    <?php if (!empty($page_title)): ?>
    <title><?= htmlspecialchars($page_title) ?></title>
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

    <!-- ═══ Open Graph ════════════════════════════════════════════════════ -->
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
    <meta property="og:type" content="news">

    <!-- ═══ Twitter Card ══════════════════════════════════════════════════ -->
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

    <!-- ═══ Schema.org Structured Data ═══════════════════════════════════ -->
    <?php if (!empty($product['name'])): ?>
    <script type="application/ld+json">
    <?= json_encode([
        "@context" => "https://schema.org/",
        "@type"    => "Product",
        "name"        => $product['name'],
        "image"       => !empty($page_image) ? [$page_image] : [],
        "description" => $page_description ?? '',
        "sku"         => $product['sku'] ?? '',
        "brand"       => ["@type" => "Brand", "name" => NOMBRE_SITIO],
        "offers"      => [
            "@type"         => "Offer",
            "url"           => $page_canonical ?? '',
            "priceCurrency" => "COP",
            "price"         => $product['discount_price'] ?: $product['price'],
            "availability"  => "https://schema.org/" . ($agotado ? 'OutOfStock' : 'InStock')
        ]
    ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) ?>
    </script>
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

    <!-- ═══ Fuentes y Librerías ═══════════════════════════════════════════ -->
    <link rel="preconnect" href="https://fonts.gstatic.com">
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700;900&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css" rel="stylesheet">
    <link href="<?= URLBASE ?>/template/news/lib/owlcarousel/assets/owl.carousel.min.css" rel="stylesheet">
    <link href="<?= URLBASE ?>/template/news/css/style.css?<?= time() ?>" rel="stylesheet">

    <!-- ═══ Facebook SDK ══════════════════════════════════════════════════ -->
    <div id="fb-root"></div>
    <script async defer crossorigin="anonymous"
        src="https://connect.facebook.net/es_LA/sdk.js#xfbml=1&version=v23.0&appId=APP_ID"></script>

    <!-- ═══ Código HEAD personalizado (admin) ════════════════════════════ -->
    <?= $sys['code_head'] ?? '' ?>

    <!-- ═══ Variables CSS (colores dinámicos) ════════════════════════════ -->
    <style>
    :root {
        --primary:           <?= htmlspecialchars($sys['primary']          ?? '#5fca00', ENT_QUOTES, 'UTF-8') ?>;
        --color-hover-link:  <?= htmlspecialchars($sys['color-hover-link'] ?? '#214A82', ENT_QUOTES, 'UTF-8') ?>;
        --blue: #007bff; --indigo: #6610f2; --purple: #6f42c1;
        --pink: #e83e8c; --red: #dc3545; --orange: #fd7e14;
        --yellow: #ffc107; --green: #28a745; --teal: #20c997;
        --cyan: #17a2b8; --white: #fff; --gray: #6c757d;
        --gray-dark: #343a40; --success: #28a745; --info: #17a2b8;
        --warning: #ffc107; --danger: #dc3545; --light: #ffffff;
        --dark: #343a40;
        --breakpoint-xs: 0; --breakpoint-sm: 576px;
        --breakpoint-md: 768px; --breakpoint-lg: 992px;
        --breakpoint-xl: 1200px;
        --font-family-sans-serif: "Roboto", sans-serif;
        --font-family-monospace: SFMono-Regular, Menlo, Monaco, Consolas, monospace;
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
    <noscript><iframe src="https://www.googletagmanager.com/ns.html?id=<?= htmlspecialchars($gtmId, ENT_QUOTES, 'UTF-8') ?>"
        height="0" width="0" style="display:none;visibility:hidden"></iframe></noscript>
    <?php endif; ?>

    <?php include __DIR__ . "/menu-header.php"; ?>
    <div id="appRoot">