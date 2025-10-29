
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="utf-8">
   
    <meta content="width=device-width, initial-scale=1.0" name="viewport">
    <meta content="Free HTML Templates" name="keywords">
    <meta content="Free HTML Templates" name="description">

    <link rel="shortcut icon" type="image/x-icon" href="<?php echo URLBASE; ?><?php echo FAVICON ?>">
<!-- Site Metas -->
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
<meta property="og:type" content="news">

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

<!-- Datos estructurados Schema.org -->
<?php if (!empty($product['name'])): ?>
<script type="application/ld+json">
<?= json_encode([
  "@context" => "https://schema.org/",
  "@type"    => "Product",
  "name"        => $product['name'],
  "image"       => !empty($page_image) ? [$page_image] : [],
  "description" => $page_description ?? '',
  "sku"         => $product['sku'] ?? '',
  "brand"       => [
    "@type" => "Brand",
    "name"  => NOMBRE_SITIO
  ],
  "offers"      => [
    "@type"         => "Offer",
    "url"           => $page_canonical ?? '',
    "priceCurrency" => "COP",
    "price"         => $product['discount_price'] ?: $product['price'],
    "availability"  => "https://schema.org/".($agotado ? 'OutOfStock' : 'InStock')
  ]
], JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT) ?>
</script>
<?php endif; ?>

    <!-- Google Web Fonts -->
    <link rel="preconnect" href="https://fonts.gstatic.com">
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700;900&display=swap" rel="stylesheet">   

    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.0/css/all.min.css" rel="stylesheet">

    <!-- Libraries Stylesheet -->
    <link href="<?php echo URLBASE; ?>/template/news/lib/owlcarousel/assets/owl.carousel.min.css" rel="stylesheet">

    <!-- Customized Bootstrap Stylesheet -->
    <link href="<?php echo URLBASE; ?>/template/news/css/style.css?<?php echo time();?>" rel="stylesheet">
	<script src="https://kit.fontawesome.com/332d1c4e86.js" crossorigin="anonymous"></script>
	<div id="fb-root"></div>
<script async defer crossorigin="anonymous" src="https://connect.facebook.net/es_LA/sdk.js#xfbml=1&version=v23.0&appId=APP_ID"></script>
	<?= $sys['code_head'] ?>
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
  --primary: <?= $sys['primary']?>;
  --color-hover-link: <?= $sys['color-hover-link']?>;
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
	</style>

</head>
<body>
	<?php include __DIR__ . "/menu-header.php";?>
	<div id="appRoot">