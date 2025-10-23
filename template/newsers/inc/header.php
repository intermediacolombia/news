<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="utf-8">
    <meta content="width=device-width, initial-scale=1.0" name="viewport">

    <!-- SEO Dinámico -->
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

    <!-- Favicon -->
    <link rel="shortcut icon" type="image/x-icon" href="<?= URLBASE . ($sys['site_favicon'] ?? '/public/images/favicon.png') ?>">

    <!-- Google Web Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Open+Sans:wght@400;600&family=Raleway:wght@100;600;800&display=swap" rel="stylesheet"> 

    <!-- Icon Font Stylesheet -->
    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.15.4/css/all.css"/>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.4.1/font/bootstrap-icons.css" rel="stylesheet">

    <!-- Libraries Stylesheet -->
    <link href="<?= URLBASE ?>/template/newsers/lib/animate/animate.min.css" rel="stylesheet">
    <link href="<?= URLBASE ?>/template/newsers/lib/owlcarousel/assets/owl.carousel.min.css" rel="stylesheet">

    <!-- Customized Bootstrap Stylesheet -->
    <link href="<?= URLBASE ?>/template/newsers/css/bootstrap.min.css" rel="stylesheet">

    <!-- Template Stylesheet -->
    <link href="<?= URLBASE ?>/template/newsers/css/style.css?<?= time(); ?>" rel="stylesheet">

    <!-- Facebook SDK -->
    <div id="fb-root"></div>
    <script async defer crossorigin="anonymous" src="https://connect.facebook.net/es_LA/sdk.js#xfbml=1&version=v23.0&appId=APP_ID"></script>

    <!-- Código adicional desde configuración -->
    <?= $sys['code_head'] ?? '' ?>

    <!-- Variables de color -->
    <style>
        :root {
            --primary: <?= $sys['primary'] ?? '#007bff' ?>;
            --color-hover-link: <?= $sys['color-hover-link'] ?? '#0056b3' ?>;
        }
    </style>
</head>

<body>

    <?php include __DIR__ . "/menu-header.php"; ?>
