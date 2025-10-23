<?php
// Zona horaria
date_default_timezone_set('America/Bogota');

/* ========= Credenciales DB (solo si no existen) ========= */
if (!isset($host))   $host   = 'localhost';
if (!isset($dbname)) $dbname = 'intermed_shop';
if (!isset($dbuser)) $dbuser = 'intermed_shop';
if (!isset($dbpass)) $dbpass = '=a{p6q2uWG8hiTBo';

/* ========= Constantes de rutas/URL con guardas ========= */
if (!defined('URLBASE'))   define('URLBASE', 'https://shop.intermediacolombia.com');
if (!defined('BASE_PATH')) define('BASE_PATH', dirname(__DIR__));
$url = 'https://shop.intermediacolombia.com';
define('NOMBRE_SITIO', 'Capell B5');
define('FAVICON', 'https://shop.intermediacolombia.com/template/assets/images/logo.png');

// === MERCADO PAGO ===
if (!defined('MP_ACCESS_TOKEN')) {
  // De tu aplicación de MP (usa el de producción si ya estás live; mientras, usa test)
  define('MP_ACCESS_TOKEN', 'TEST-5302646716770684-082811-e37ae728767c302d95eb1b7b272cf776-47181710'); 
}
if (!defined('MP_PUBLIC_KEY')) {
  define('MP_PUBLIC_KEY', 'TEST-277ef300-6b73-4c5f-bab3-9c2746de4294');
}

// Rutas útiles
if (!defined('MP_NOTIFICATION_URL')) {
  define('MP_NOTIFICATION_URL', URLBASE . '/actions/mp_webhook.php'); // ¡pública y https!
}
if (!defined('MP_RETURN_URL')) {
  // Con tu router: /pago/retorno => page=pago&slug=retorno
  define('MP_RETURN_URL', URLBASE . '/pago/retorno');	
}

if (!defined('MP_SUCCESS_URL')) define('MP_SUCCESS_URL', URLBASE . '/mp_success');
if (!defined('MP_FAILURE_URL')) define('MP_FAILURE_URL', URLBASE . '/mp_failure');
if (!defined('MP_PENDING_URL')) define('MP_PENDING_URL', URLBASE . '/mp_pending');

// inc/config.php
require_once dirname(__DIR__) . '/vendor/autoload.php';


/* ========= Conexión PDO (única instancia) ========= */
if (!isset($GLOBALS['pdo']) || !($GLOBALS['pdo'] instanceof PDO)) {
    try {
        $dsn = "mysql:host={$host};dbname={$dbname};charset=utf8mb4";
        $GLOBALS['pdo'] = new PDO($dsn, $dbuser, $dbpass, [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]);
        $GLOBALS['pdo']->exec("SET NAMES 'utf8mb4'");
    } catch (PDOException $e) {
        // En producción, loguea y muestra un mensaje amigable
        die('Error de conexión a la base de datos.');
    }
}
$pdo = $GLOBALS['pdo'];

/* ========= Carga de ajustes del sistema (con cache global) ========= */
if (!isset($GLOBALS['SYS_SETTINGS'])) {
    try {
        $stmt = $pdo->query("SELECT * FROM system_settings LIMIT 1");
        $GLOBALS['SYS_SETTINGS'] = $stmt->fetch(PDO::FETCH_ASSOC) ?: [];
    } catch (Throwable $e) {
        $GLOBALS['SYS_SETTINGS'] = [];
    }
}
$sys = $GLOBALS['SYS_SETTINGS'];
$api_ws                  = $sys['wa_api']              ?? null;
$wa_consent              = $sys['wa_consent']          ?? null;
$wa_client_pay           = $sys['wa_client_pay']       ?? null;
$wa_client_pay_general   = $sys['wa_client_pay_general'] ?? null;
$wa_hbd                  = $sys['wa_hbd']              ?? null;
$wa_notify_expired       = $sys['wa_notify_expired']   ?? null;
$wa_paymentReminder      = $sys['wa_paymentReminder']  ?? null;
$wa_valoracion           = $sys['wa_valoracion']       ?? null;
$wa_creditReminder       = $sys['wa_creditReminder']   ?? null;
$wa_creditReminder_day   = $sys['wa_creditReminder_day'] ?? null;
$wa_creditReminder_hour  = $sys['wa_creditReminder_hour'] ?? null;

/* ========= Helpers ========= */
if (!function_exists('setFlash')) {
    function setFlash(string $type, string $message): void {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $_SESSION['flash'][] = ['type' => $type, 'msg' => $message];
    }
}


