<meta name="viewport" content="width=device-width, initial-scale=1">
<style>
        :root {
            --primary: <?= COLOR_PRIMARY ?? '#007bff' ?>;
            --color-hover-link: <?= COLOR_PRIMARY_HOVER_LINK  ?? '#0056b3' ?>;
            --color-TEST1: <?= COLOR_PRIMARY ?>;
            --color-TEST2: <?= COLOR_PRIMARY_HOVER_LINK ?>;
        }
    </style>
<!-- Favicon -->
<link rel="shortcut icon" type="image/x-icon" href="<?php echo URLBASE; ?><?php echo FAVICON ?>">
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<!-- Bootstrap 5 -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

<!-- Estilos del admin -->
<link href="<?= htmlspecialchars($url) ?>/admin/assets/css/admin.css?cache=<?= time(); ?>" rel="stylesheet">
<link rel="stylesheet" href="<?= htmlspecialchars($url) ?>/admin/assets/css/menu.css?cache=<?= time(); ?>">
<link rel="stylesheet" href="<?= htmlspecialchars($url) ?>/admin/assets/css/style.css?cache=<?= time(); ?>">
<link rel="stylesheet" href="<?= htmlspecialchars($url) ?>/admin/assets/css/cards.css?cache=<?= time(); ?>">

<!-- Font Awesome -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
<script src="https://kit.fontawesome.com/332d1c4e86.js" crossorigin="anonymous"></script>

<!-- SweetAlert2 -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<!-- intl-tel-input -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/17.0.8/css/intlTelInput.css" />

<!-- DataTables Bootstrap 5 (versión unificada 1.13.7) -->
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap5.min.css">

<!-- Flatpickr -->
<link href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css" rel="stylesheet">
<link rel="stylesheet" type="text/css" href="https://npmcdn.com/flatpickr/dist/themes/material_red.css">

<!-- Google Material Icons -->
<link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">

<!-- Script custom -->
<script src="<?= htmlspecialchars($url) ?>/template/assets/js/departamentos.js" crossorigin="anonymous"></script>

