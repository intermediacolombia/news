<?php require_once('../../inc/config.php'); // Este archivo debería definir $host, $dbname, $dbuser, $dbpass, etc.

session_start();

$username = $_SESSION["user"]["username"] ?? $_SESSION["user"]["correo"] ?? null;
$userId = $_SESSION["user"]["id"] ?? null;

// Si existe la cookie "remember_me", elimina el token de la base de datos y la cookie
if (isset($_COOKIE['remember_me'])) {
    $token = $_COOKIE['remember_me'];
    $stmt = db()->prepare("DELETE FROM user_tokens WHERE token = :token");
    $stmt->execute([':token' => $token]);
    setcookie('remember_me', '', time() - 3600, '/', 'app.activgym.com.co', true, true);
}

// Registrar cierre de sesión antes de destruir la sesión
if ($username) {
    log_system_action('logout', 'Usuario cerró sesión: ' . $username, 'user', $userId);
}

// Destruir la sesión
session_unset();
session_destroy();

// Iniciar una nueva sesión para el mensaje flash
session_start();
$_SESSION['success'] = 'Has cerrado sesión correctamente.';
header("Location: $url/admin/login/");
exit();
?>

