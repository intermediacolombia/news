<?php

/* ========= Conexión PDO (única instancia) ========= */
function db() {
    static $pdo = null;
    if ($pdo !== null) return $pdo;

    $config_file = __DIR__ . '/url_bd.php';

    if (!file_exists($config_file)) {
        if (strpos($_SERVER['REQUEST_URI'], '/install/') === false) {
            header('Location: ./install/index.php');
            exit;
        }
        return null;
    }

    try {
        // CARGAR EL ARCHIVO
        require_once $config_file;
        
        // HACER QUE $url_site SEA ACCESIBLE FUERA DE LA FUNCIÓN
        if (isset($url_site)) {
            $GLOBALS['url_site'] = $url_site;
        }

        $host   = $GLOBALS['host'] ?? (isset($host) ? $host : 'localhost');
        $dbname = $GLOBALS['dbname'] ?? (isset($dbname) ? $dbname : '');
        $dbuser = $GLOBALS['dbuser'] ?? (isset($dbuser) ? $dbuser : '');
        $dbpass = $GLOBALS['dbpass'] ?? (isset($dbpass) ? $dbpass : '');

        $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $dbuser, $dbpass, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]);
        
        return $pdo;

    } catch (PDOException $e) {
        if (strpos($_SERVER['REQUEST_URI'], '/install/') === false) {
            header('Location: ./install/index.php?error=db_connection');
            exit;
        }
        return null;
    }
}

// Inicializar la base de datos para cargar las variables
db();

// AHORA DEFINIR LAS CONSTANTES USANDO EL GLOBALS
if (!defined('URLBASE')) {
    // Si por alguna razón db() no cargó la variable, usamos un fallback
    define('URLBASE', $GLOBALS['url_site'] ?? 'http://localhost');
}

date_default_timezone_set('America/Bogota');
/* ========= Carga de ajustes del sistema (con cache global) ========= */
if (!isset($GLOBALS['SYS_SETTINGS'])) {
    try {
        $stmt = db()->query("SELECT setting_name, value FROM system_settings");
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
define('SITE_LOGO', $sys['site_logo'] ?? '/img/logo.webp');

/*=======Tienda=====================*/
define('FREE_SHIPPING', $sys['free_shipping'] ?? '');


define('WS_API', $sys['api_whatsapp'] ?? '');

/*=======mail config=====*/
define('MAIL_SENDER', $sys['mail_sender'] ?? '');
define('SMTP_HOST', $sys['mail_smtp_host'] ?? '');
define('SMTP_USER', $sys['mail_smtp_user'] ?? '');
define('SMTP_PASS', $sys['mail_smtp_pass'] ?? '');
define('SMTP_PORT', $sys['mail_smtp_port'] ?? '');

define('COLOR_PRIMARY', $sys['primary'] ?? '#000');
define('COLOR_PRIMARY_HOVER_LINK', $sys['color-hover-link'] ?? '#242424');


/* ========= Mensajes ws y mail ========= */

define('EMAIL_NEW_ORDER', $sys['mail_new_order_message'] ?? '');
define('WS_NEW_ORDER', $sys['ws_new_order_message'] ?? '');

define('EMAIL_SHIPPED_ORDER', $sys['mail_shipped_message'] ?? '');
define('WS_SHIPPED_ORDER', $sys['ws_shipped_message'] ?? '');

define('EMAIL_DELIVERED_ORDER', $sys['mail_delivered_message'] ?? '');
define('WS_DELIVERED_ORDER', $sys['ws_delivered_message'] ?? '');

/* =========Fin Mensajes ws y mail ========= */

/* =========Apariencia ========= */
define('THEME', $sys['site_theme'] ?? 'news');
define('TEXT_TO_SPEECH', $sys['enable_text_to_speech'] ?? '');

define('ENABLE_STOP_PLAYER', $sys['enable_stop_player'] ?? '');


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


/* ========= SEO Avanzado ========= */
define('SEO_HOME_TITLE',       $sys['seo_home_title']       ?? '');
define('SEO_HOME_DESCRIPTION', $sys['seo_home_description'] ?? '');
define('SEO_HOME_KEYWORDS',    $sys['seo_home_keywords']    ?? '');

/* ========= Monetización Google AdSense ========= */
define('ADSENSE_PUBLISHER_ID', $sys['adsense_publisher_id'] ?? '');
define('ADSENSE_AUTO_ADS',     $sys['adsense_auto_ads']     ?? '0');

/* ========= Google Tag Manager ========= */
define('GTM_CONTAINER_ID',     $sys['gtm_container_id']     ?? '');

/* ========= Verificación de Buscadores ========= */
define('VERIFY_GOOGLE',        $sys['verify_google']        ?? '');
define('VERIFY_BING',          $sys['verify_bing']          ?? '');
define('VERIFY_YANDEX',        $sys['verify_yandex']        ?? '');
define('VERIFY_META',          $sys['verify_meta']          ?? '');
define('VERIFY_PINTEREST',     $sys['verify_pinterest']     ?? '');


/* ========= Helpers ========= */
if (!function_exists('setFlash')) {
    function setFlash(string $type, string $message): void {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $_SESSION['flash'][] = ['type' => $type, 'msg' => $message];
    }
}

/* ========= Helper: renderizar bloque AdSense por posición ========= */
if (!function_exists('renderAdsenseBlock')) {
    function renderAdsenseBlock(int $position): string {
        $sys   = $GLOBALS['SYS_SETTINGS'] ?? [];
        $pubId = trim($sys['adsense_publisher_id'] ?? '');

        // Si Auto Ads está activo, Google gestiona todo
        if (!empty($sys['adsense_auto_ads']) && $sys['adsense_auto_ads'] == '1') return '';
        if (empty($pubId)) return '';

        try {
            $stmt = db()->prepare(
                "SELECT * FROM ads WHERE position = ? AND ad_type = 'adsense' AND status = 'active' LIMIT 1"
            );
            $stmt->execute([$position]);
            $block = $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (Throwable $e) {
            return '';
        }

        if (!$block) return '';

        $meta   = json_decode($block['ad_code'] ?? '{}', true);
        $slotId = trim($meta['slot_id'] ?? '');
        $format = trim($meta['format']  ?? 'auto');

        if (empty($slotId)) return '';

        return '<ins class="adsbygoogle"'
             . ' style="display:block"'
             . ' data-ad-client="' . htmlspecialchars($pubId,   ENT_QUOTES) . '"'
             . ' data-ad-slot="'   . htmlspecialchars($slotId,  ENT_QUOTES) . '"'
             . ' data-ad-format="' . htmlspecialchars($format,  ENT_QUOTES) . '"'
             . ' data-full-width-responsive="true"></ins>'
             . '<script>(adsbygoogle = window.adsbygoogle || []).push({});</script>';
    }
}



