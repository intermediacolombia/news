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

// Cargar funciones de traducción
if (file_exists(__DIR__ . '/translations.php')) {
    require_once __DIR__ . '/translations.php';
}

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

define('SITE_LANGUAGE', $sys['site_language'] ?? 'es');


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


/* ========= Helper: convierte clases Quill a estilos inline para compatibilidad con temas ========= */
if (!function_exists('render_post_content')) {
    function render_post_content(string $html): string {
        if (empty(trim($html))) return $html;

        // Clases de alineación que Quill guarda y los temas no entienden
        $classToStyle = [
            'ql-align-center'  => 'text-align:center',
            'ql-align-right'   => 'text-align:right',
            'ql-align-justify' => 'text-align:justify',
        ];
        // Indentaciones
        for ($i = 1; $i <= 8; $i++) {
            $classToStyle['ql-indent-' . $i] = 'padding-left:' . ($i * 3) . 'em';
        }

        $dom = new DOMDocument();
        libxml_use_internal_errors(true);
        $dom->loadHTML(
            '<html><head><meta charset="UTF-8"></head><body><div id="_qcewrap_">' . $html . '</div></body></html>',
            LIBXML_NOWARNING | LIBXML_NOERROR
        );
        libxml_clear_errors();

        $xpath = new DOMXPath($dom);
        foreach ($xpath->query('//*[@class]') as $node) {
            $classes      = preg_split('/\s+/', trim($node->getAttribute('class')));
            $newStyles    = [];
            $keepClasses  = [];

            foreach ($classes as $cls) {
                if (isset($classToStyle[$cls])) {
                    $newStyles[] = $classToStyle[$cls];
                } elseif ($cls !== '') {
                    $keepClasses[] = $cls;
                }
            }

            if (empty($newStyles)) continue;

            $existing = trim($node->getAttribute('style'));
            $merged   = implode(';', $newStyles) . ($existing ? ';' . $existing : '');
            $node->setAttribute('style', $merged);

            if ($keepClasses) {
                $node->setAttribute('class', implode(' ', $keepClasses));
            } else {
                $node->removeAttribute('class');
            }
        }

        $wrap = $dom->getElementById('_qcewrap_');
        if (!$wrap) return $html;

        $out = '';
        foreach ($wrap->childNodes as $child) {
            $out .= $dom->saveHTML($child);
        }
        return $out;
    }
}

/* ========= Helper para obtener alt_text de imagen ========= */
function get_image_alt($imagePath, $fallbackTitle = '') {
    if (empty($imagePath)) {
        return $fallbackTitle;
    }
    
    try {
        $stmt = db()->prepare("SELECT alt_text FROM multimedia WHERE file_path = ? AND deleted = 0 LIMIT 1");
        $stmt->execute([$imagePath]);
        $media = $stmt->fetch();
        if (!empty($media['alt_text'])) {
            return $media['alt_text'];
        }
    } catch (Throwable $e) {
    }
    
    return $fallbackTitle;
}

/* ========= Helper: renderizar popup ========= */
function renderPopup(): string {
    $sys = $GLOBALS['SYS_SETTINGS'] ?? [];
    
    try {
        $stmt = db()->query("SELECT * FROM popups WHERE status = 'active' LIMIT 1");
        $popup = $stmt->fetch(PDO::FETCH_ASSOC);
    } catch (Throwable $e) {
        return '';
    }
    
    if (!$popup) return '';
    
    $isHomepage = isset($_GET['path']) && ($_GET['path'] === '' || $_GET['path'] === '/');
    if (!$popup['show_on_all_pages'] && !$isHomepage) return '';
    
    $cookieName = 'popup_shown_' . $popup['id'];
    if ($popup['show_once_per_visit'] && isset($_COOKIE[$cookieName])) return '';
    
    $positionMap = [
        'center' => 'top: 50%; left: 50%; transform: translate(-50%, -50%);',
        'bottom-right' => 'bottom: 20px; right: 20px;',
        'bottom-left' => 'bottom: 20px; left: 20px;',
        'top-right' => 'top: 20px; right: 20px;',
        'top-left' => 'top: 20px; left: 20px;'
    ];
    $positionStyle = $positionMap[$popup['position']] ?? $positionMap['center'];
    
    $isNotification = $popup['popup_type'] === 'notification';
    if ($isNotification) {
        $positionStyle = 'position: fixed; ' . $positionStyle;
    }
    
    $imageHtml = '';
    if (!empty($popup['image'])) {
        $imagePath = $popup['image'];
        if (strpos($imagePath, 'public/') === 0) {
            $imagePath = substr($imagePath, 7);
        }
        $imageHtml = '<img src="' . URLBASE . '/public/' . htmlspecialchars($imagePath) . '" alt="' . htmlspecialchars($popup['title']) . '" style="max-width: 100%; border-radius: 8px; margin-bottom: 15px;">';
    }
    
    $onclickAction = '';
    if ($popup['action_type'] !== 'none' && !empty($popup['action_url'])) {
        $target = $popup['action_new_tab'] ? ' target="_blank"' : '';
        $onclickAction = ' onclick="window.location.href=\'' . htmlspecialchars($popup['action_url']) . '\'" style="cursor: pointer;"' . $target;
    }
    
    $overlayStyle = $popup['overlay_enabled'] ? 'position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 9999;' : 'display: none;';
    
    $autoClose = $popup['auto_close_seconds'] > 0 ? 'setTimeout(function(){ document.getElementById(\'globalPopup\').remove(); }, ' . ($popup['auto_close_seconds'] * 1000) . ');' : '';
    
    $btnStyle = 'background:' . htmlspecialchars($popup['button_color']) . '; color:' . htmlspecialchars($popup['button_text_color']) . '; border: none; padding: 8px 20px; border-radius: 5px; cursor: pointer; margin-top: 10px;';
    
    $html = '
    <div id="globalPopupOverlay" style="' . $overlayStyle . '"></div>
    <div id="globalPopup" class="global-popup ' . ($isNotification ? 'global-notification' : 'global-modal') . '" style="
        position: fixed; ' . $positionStyle . '
        background: ' . htmlspecialchars($popup['background_color']) . ';
        color: ' . htmlspecialchars($popup['text_color']) . ';
        padding: 20px;
        border-radius: 10px;
        box-shadow: 0 4px 20px rgba(0,0,0,0.3);
        z-index: 10000;
        max-width: ' . htmlspecialchars($popup['width']) . ';
        max-height: 90vh;
        overflow-y: auto;
        display: none;
    ">
        <div style="text-align: center;"' . $onclickAction . '>
            <h3 style="margin: 0 0 10px 0; font-size: 1.5rem;">' . htmlspecialchars($popup['title']) . '</h3>
            ' . $imageHtml . '
            <div style="text-align: left;">' . $popup['content'] . '</div>
        </div>
        <button onclick="closeGlobalPopup()" style="' . $btnStyle . '">' . htmlspecialchars($popup['button_text']) . '</button>
    </div>
    <script>
    document.addEventListener("DOMContentLoaded", function() {
        setTimeout(function() {
            var popup = document.getElementById("globalPopup");
            var overlay = document.getElementById("globalPopupOverlay");
            if (popup && overlay) {
                popup.style.display = "block";
                ' . $autoClose . '
            }
        }, ' . (intval($popup['delay_seconds']) * 1000) . ');
    });
    function closeGlobalPopup() {
        var popup = document.getElementById("globalPopup");
        var overlay = document.getElementById("globalPopupOverlay");
        if (popup) popup.remove();
        if (overlay) overlay.remove();
        document.cookie = "popup_shown_' . $popup['id'] . '=1; path=/; max-age=86400";
    }
    </script>
    <style>
    @media (max-width: 600px) {
        .global-popup {
            width: 90% !important;
            left: 5% !important;
            transform: none !important;
        }
        .global-popup.global-modal {
            top: 50% !important;
            transform: translateY(-50%) !important;
        }
        .global-popup.global-notification {
            top: auto !important;
            bottom: 10px !important;
            left: 5% !important;
            right: 5% !important;
            width: 90% !important;
        }
    }
    </style>
    ';
    
    return $html;
}



