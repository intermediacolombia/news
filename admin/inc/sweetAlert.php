<?php
// inc/sweetAlert.php
if (session_status() === PHP_SESSION_NONE) session_start();
if (!isset($_SESSION['flash'])) return;

$f = $_SESSION['flash']; unset($_SESSION['flash']);

$type  = (string)($f['type']  ?? ($f[0] ?? 'info'));
$title = (string)($f['title'] ?? ($f[1] ?? ''));
$text  = (string)($f['text']  ?? ($f[2] ?? ''));

$allowed = ['success','error','warning','info','question'];
if (!in_array($type, $allowed, true)) $type = 'info';

if ($title === '' && $text !== '') $title = ($type==='success'?'Listo':($type==='error'?'Error':'Aviso'));
if ($title === '' && $text === '')  $title = ($type==='success'?'Operación exitosa':($type==='error'?'Ocurrió un error':'Aviso'));
?>
<script>
(function(){
  // DEBUG visible en consola – ¡mira el navegador!
  console.log('FLASH_DEBUG', <?= json_encode($f, JSON_UNESCAPED_UNICODE) ?>);

  function fireAlert(){
    var gold      = getComputedStyle(document.documentElement).getPropertyValue('--gold').trim() || '#ddc686';
    var graphite  = getComputedStyle(document.documentElement).getPropertyValue('--graphite').trim() || '#2d2d2d';
    var warmwhite = getComputedStyle(document.documentElement).getPropertyValue('--warmwhite').trim() || '#faf9f6';

    Swal.fire({
      icon: <?= json_encode($type) ?>,
      title: <?= json_encode($title, JSON_UNESCAPED_UNICODE) ?>,
      html: <?= json_encode(nl2br($text), JSON_UNESCAPED_UNICODE) ?>,
      confirmButtonColor: gold,
      color: graphite,
      background: warmwhite
    });
  }

  // Ejecuta aun si DOMContentLoaded ya pasó
  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', fireAlert, { once:true });
  } else {
    fireAlert();
  }
})();
</script>


