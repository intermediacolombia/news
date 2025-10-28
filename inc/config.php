<?php
// Zona horaria
date_default_timezone_set('America/Bogota');

require_once __DIR__ . '/url_bd.php';





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
        $stmt = $pdo->query("SELECT setting_name, value FROM system_settings");
        $GLOBALS['SYS_SETTINGS'] = [];
        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
            $GLOBALS['SYS_SETTINGS'][$row['setting_name']] = $row['value'];
        }
    } catch (Throwable $e) {
        $GLOBALS['SYS_SETTINGS'] = [];
    }
}
$sys = $GLOBALS['SYS_SETTINGS'];



/* ========= Constantes de rutas/URL con guardas ========= */
if (!defined('URLBASE'))   define('URLBASE', $url_site);

if (!defined('BASE_PATH')) define('BASE_PATH', dirname(__DIR__));

$url = URLBASE;

define('NOMBRE_SITIO', $sys['site_name'] ?? '');

define('FAVICON', $sys['site_favicon'] ?? '');
define('SITE_LOGO', $sys['site_logo'] ?? '');

/*=======Tienda=====================*/
define('FREE_SHIPPING', $sys['free_shipping'] ?? '');


define('WS_API', $sys['api_whatsapp'] ?? '');

/*=======mail config=====*/
define('MAIL_SENDER', $sys['mail_sender'] ?? '');
define('SMTP_HOST', $sys['mail_smtp_host'] ?? '');
define('SMTP_USER', $sys['mail_smtp_user'] ?? '');
define('SMTP_PASS', $sys['mail_smtp_pass'] ?? '');
define('SMTP_PORT', $sys['mail_smtp_port'] ?? '');

define('COLOR_PRIMARY', $sys['primary'] ?? '');
define('COLOR_PRIMARY_HOVER_LINK', $sys['primary'] ?? '');


/* ========= Mensajes ws y mail ========= */

define('EMAIL_NEW_ORDER', $sys['mail_new_order_message'] ?? '');
define('WS_NEW_ORDER', $sys['ws_new_order_message'] ?? '');

define('EMAIL_SHIPPED_ORDER', $sys['mail_shipped_message'] ?? '');
define('WS_SHIPPED_ORDER', $sys['ws_shipped_message'] ?? '');

define('EMAIL_DELIVERED_ORDER', $sys['mail_delivered_message'] ?? '');
define('WS_DELIVERED_ORDER', $sys['ws_delivered_message'] ?? '');

/* =========Fin Mensajes ws y mail ========= */

/* =========Apariencia ========= */
define('THEME', $sys['site_theme'] ?? '');


/* =========Fin Apariencia ========= */




/* ========= Mercado Pago desde system_settings ========= */
if (!defined('MP_ACCESS_TOKEN')) {
    define('MP_ACCESS_TOKEN', $sys['mercadopago_access_token'] ?? '');
}
if (!defined('MP_PUBLIC_KEY')) {
    define('MP_PUBLIC_KEY', $sys['mercadopago_public_key'] ?? '');
}

// Opcionales si manejas sandbox/producción
if (!defined('MP_TEST_ACCESS_TOKEN')) {
    define('MP_TEST_ACCESS_TOKEN', $sys['mercadopago_test_access_token'] ?? '');
}
if (!defined('MP_TEST_PUBLIC_KEY')) {
    define('MP_TEST_PUBLIC_KEY', $sys['mercadopago_test_public_key'] ?? '');
}
if (!defined('MP_PROD_ACCESS_TOKEN')) {
    define('MP_PROD_ACCESS_TOKEN', $sys['mercadopago_prod_access_token'] ?? '');
}
if (!defined('MP_PROD_PUBLIC_KEY')) {
    define('MP_PROD_PUBLIC_KEY', $sys['mercadopago_prod_public_key'] ?? '');
}

/* ========= URLs de retorno y notificación MP ========= */
if (!defined('MP_NOTIFICATION_URL')) {
    define('MP_NOTIFICATION_URL', URLBASE . '/actions/mp_webhook.php'); // ¡pública y https!
}
if (!defined('MP_RETURN_URL')) {
    define('MP_RETURN_URL', URLBASE . '/pago/retorno');
}
if (!defined('MP_SUCCESS_URL')) define('MP_SUCCESS_URL', URLBASE . '/mp_success');
if (!defined('MP_FAILURE_URL')) define('MP_FAILURE_URL', URLBASE . '/mp_failure');
if (!defined('MP_PENDING_URL')) define('MP_PENDING_URL', URLBASE . '/mp_pending');



/* ========= Helpers ========= */
if (!function_exists('setFlash')) {
    function setFlash(string $type, string $message): void {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $_SESSION['flash'][] = ['type' => $type, 'msg' => $message];
    }
}



