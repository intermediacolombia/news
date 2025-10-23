<?php
// inc/flash_simple.php
if (session_status() === PHP_SESSION_NONE) session_start();

$type  = $_SESSION['flash_type']  ?? null;
$title = $_SESSION['flash_title'] ?? null;
$text  = $_SESSION['flash_text']  ?? null;

// limpia inmediatamente (flash)
unset($_SESSION['flash_type'], $_SESSION['flash_title'], $_SESSION['flash_text']);

if ($type === null && $text === null) return;

// defaults
$allowed = ['success','error','warning','info','question'];
if (!in_array($type, $allowed, true)) $type = 'info';
if ($title === null || $title === '') {
  $title = ($type === 'success' ? 'Listo' : ($type === 'error' ? 'Error' : 'Aviso'));
}
?>
<script>
(function(){
  function fire(){
    var t  = <?= json_encode($type,  JSON_UNESCAPED_UNICODE) ?>;
    var ti = <?= json_encode($title, JSON_UNESCAPED_UNICODE) ?>;
    var tx = <?= json_encode($text ?? '', JSON_UNESCAPED_UNICODE) ?>;

    // Opcional: Colores de tu tema (si existen variables CSS)
    var gold      = getComputedStyle(document.documentElement).getPropertyValue('--gold').trim() || '#ddc686';
    var graphite  = getComputedStyle(document.documentElement).getPropertyValue('--graphite').trim() || '#2d2d2d';
    var warmwhite = getComputedStyle(document.documentElement).getPropertyValue('--warmwhite').trim() || '#faf9f6';

    if (typeof Swal !== 'undefined') {
      Swal.fire({
        icon: t, title: ti, text: tx,
        confirmButtonColor: gold, color: graphite, background: warmwhite
      });
    } else {
      alert((ti ? ti + ': ' : '') + (tx || ''));
    }
  }
  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', fire, { once:true });
  } else {
    fire();
  }
})();
</script>
