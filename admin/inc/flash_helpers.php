<?php
// inc/flash_helpers.php (opcional, o pega estas funciones en tus controladores)
if (!function_exists('flash_set')) {
  function flash_set(string $type, string $title = '', string $text): void {
    if (session_status() === PHP_SESSION_NONE) session_start();
    $_SESSION['flash_type']  = $type;          // 'success','error','info',...
    $_SESSION['flash_title'] = $title;         // opcional
    $_SESSION['flash_text']  = $text;          // mensaje
    // importante: asegurar que la sesión se escriba antes del redirect
    if (session_status() === PHP_SESSION_ACTIVE) session_write_close();
  }
}
if (!function_exists('flash_ok')) {
  function flash_ok(string $text, string $title = 'Listo'): void { flash_set('success', $text, $title); }
}
if (!function_exists('flash_err')) {
  function flash_err(string $text, string $title = 'Error'): void { flash_set('error', $text, $title); }
}

/*if (!function_exists('flash_info')) {
  function flash_err(string $text, string $title = 'Error'): void { flash_set('info', $text, $title); }
}*/

if (!function_exists('renderFlashMessages')) {
  function renderFlashMessages(): void {
    if (session_status() === PHP_SESSION_NONE) session_start();
    $flashes = $_SESSION['flash'] ?? [];
    unset($_SESSION['flash']);
    $map = ['success' => 'success', 'error' => 'danger', 'warning' => 'warning', 'info' => 'info'];
    foreach ($flashes as $f) {
      $cls = $map[$f['type']] ?? 'info';
      echo '<div class="alert alert-' . $cls . ' alert-dismissible fade show" role="alert">'
         . $f['msg']
         . '<button type="button" class="btn-close" data-bs-dismiss="alert"></button>'
         . '</div>';
    }
  }
}
