<?php
// session.php
require_once __DIR__ . '/../../inc/config.php';

// Cargar funciones de traducción para el admin
if (file_exists(__DIR__ . '/../../inc/translations.php')) {
    require_once __DIR__ . '/../../inc/translations.php';
}

$cookieDomain = str_replace(['https://','http://'], '', $url);
// Configurar los parámetros de la cookie de sesión
$tiempoUnAno = 365 * 24 * 60 * 60;
session_set_cookie_params([
    'lifetime' => $tiempoUnAno,
    'path'     => '/',
    'domain'   => $cookieDomain,
    'secure'   => true,
    'httponly' => true,
    'samesite' => 'Lax'
]);

ini_set('session.gc_maxlifetime', $tiempoUnAno);
session_start();


// Aquí se usa db(), por ejemplo, para revisar la cookie "remember_me"
if (!isset($_SESSION["user"]) && isset($_COOKIE['remember_me'])) {
    $token = $_COOKIE['remember_me'];
    $now = time();

    $stmt = db()->prepare("SELECT user_id FROM user_tokens WHERE token = :token AND expires_at > :now LIMIT 1");
    $stmt->execute([
        ':token' => $token,
        ':now'   => $now
    ]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($result) {
        $stmtUser = db()->prepare("SELECT * FROM usuarios WHERE id = :id LIMIT 1");
        $stmtUser->execute([':id' => $result['user_id']]);
        $user = $stmtUser->fetch(PDO::FETCH_ASSOC);

        if ($user) {
            $_SESSION["user"] = $user;
            // Opcional: regenerar el token para mayor seguridad
            $newToken = bin2hex(random_bytes(16));
            $newExpiry = $now + (30 * 24 * 60 * 60);
            $updateStmt = db()->prepare("UPDATE user_tokens SET token = :newToken, expires_at = :newExpiry WHERE token = :oldToken");
            $updateStmt->execute([
                ':newToken'  => $newToken,
                ':newExpiry' => $newExpiry,
                ':oldToken'  => $token
            ]);
            setcookie('remember_me', $newToken, $newExpiry, '/', $cookieDomain, true, true);
        }
    } else {
        setcookie('remember_me', '', time() - 3600, '/', $cookieDomain, true, true);
    }
}

// Verificar que el usuario esté logueado
if (!isset($_SESSION["user"])) {
    header("Location: $url/admin/login");
    exit();
}

// Registrar inicio de sesión en logs
$currentUser = $_SESSION["user"];
$username = $currentUser['username'] ?? $currentUser['correo'] ?? 'desconocido';

// Ya hay una sesión activa - verificar si no se ha logueado recently
if (!isset($_SESSION['_logged_at'])) {
    $_SESSION['_logged_at'] = time();
    log_system_action('login', 'Usuario inició sesión: ' . $username, 'user', $currentUser['id'] ?? null);
}

// Reparación automática de BD al detectar nueva versión desplegada
// Compara el hash actual del repo con el último hash reparado (archivo de bandera)
try {
    $repairFlagFile = __DIR__ . '/../../admin/inc/cache/last_repair_hash.txt';
    $dbRepairFile   = __DIR__ . '/../../admin/inc/db_repair.php';
    $gitHeadFile    = __DIR__ . '/../../.git/refs/heads/main';

    if (file_exists($dbRepairFile) && file_exists($gitHeadFile)) {
        $currentHash  = trim(file_get_contents($gitHeadFile));
        $lastHash     = file_exists($repairFlagFile) ? trim(file_get_contents($repairFlagFile)) : '';

        if ($currentHash && $currentHash !== $lastHash) {
            require_once $dbRepairFile;
            repair_database();
            // Guardar el hash reparado para no volver a correr hasta la próxima versión
            $cacheDir = dirname($repairFlagFile);
            if (!is_dir($cacheDir)) mkdir($cacheDir, 0755, true);
            file_put_contents($repairFlagFile, $currentHash);
        }
    }
} catch (Throwable $e) {
    // Silencioso — nunca debe romper el flujo de sesión
}

// Obtener datos del usuario para usarlos en la aplicación
$id_user   = $_SESSION["user"]["id"];
$nombre    = $_SESSION["user"]["nombre"];
$apellido  = $_SESSION["user"]["apellido"];
$rol_id    = $_SESSION["user"]["rol_id"]; // Obtener el ID del rol

// Consultar el nombre del rol
try {
    $stmtRol = db()->prepare("SELECT name FROM roles WHERE id = :rol_id LIMIT 1");
    $stmtRol->execute([':rol_id' => $rol_id]);
    $rolData = $stmtRol->fetch(PDO::FETCH_ASSOC);
    $rolUser = $rolData ? $rolData['name'] : 'Sin Rol'; // Almacenar el nombre del rol
} catch (PDOException $e) {
    $rolUser = 'Sin Rol'; // En caso de error, asignar un valor predeterminado
}


// Consultar los permisos asociados al rol del usuario
try {
    $stmtPermisos = db()->prepare("SELECT p.name AS permission_name 
                                   FROM role_permissions rp 
                                   JOIN permissions p ON rp.permission_id = p.id 
                                   WHERE rp.role_id = :rol_id");
    $stmtPermisos->execute([':rol_id' => $rol_id]);
    $permisos = $stmtPermisos->fetchAll(PDO::FETCH_COLUMN); // Devuelve un array con los nombres de los permisos
} catch (PDOException $e) {
    $permisos = []; // En caso de error, dejar la lista de permisos vacía
}

// Guardar los permisos en una variable accesible globalmente
$_SESSION["user_permissions"] = $permisos;

// Obtener la caja abierta para el usuario actual
/*$stmtCaja = db()->prepare("SELECT id FROM cajas WHERE usuario_id = :usuario_id AND estado = 1 LIMIT 1");
$stmtCaja->execute([':usuario_id' => $id_user]);
$caja = $stmtCaja->fetch(PDO::FETCH_ASSOC);
$caja_id = $caja ? $caja['id'] : null;*/

// Imprimir los permisos para depuración
//echo "<pre>";
//print_r($_SESSION["user_permissions"]);
//echo "</pre>";
?>





