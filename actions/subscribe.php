<?php
require_once __DIR__ . '/../inc/config.php';
header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['ok' => false, 'msg' => 'Método no permitido']);
    exit;
}

// Honeypot
if (!empty($_POST['website'])) {
    echo json_encode(['ok' => true, 'msg' => '¡Suscripción completada!']);
    exit;
}

$name             = trim($_POST['name']    ?? '');
$email            = trim($_POST['email']   ?? '');
$privacyAccepted  = !empty($_POST['privacy']) ? 1 : 0;

$errors = [];
if (empty($name))                                              $errors[] = 'El nombre es obligatorio';
if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Correo inválido';
if (!$privacyAccepted)                                        $errors[] = 'Debes aceptar la política de privacidad';

if ($errors) {
    echo json_encode(['ok' => false, 'msg' => implode('. ', $errors)]);
    exit;
}

// Verificar duplicado
try {
    $check = db()->prepare("SELECT id FROM subscribers WHERE email = ?");
    $check->execute([mb_strtolower($email)]);
    if ($check->fetch()) {
        echo json_encode(['ok' => false, 'msg' => 'Este correo ya está suscrito.']);
        exit;
    }
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['ok' => false, 'msg' => 'Error interno. Inténtalo de nuevo.']);
    exit;
}

try {
    $stmt = db()->prepare("
        INSERT INTO subscribers (name, email, privacy_accepted)
        VALUES (?, ?, ?)
    ");
    $stmt->execute([
        mb_substr($name, 0, 255),
        mb_strtolower(mb_substr($email, 0, 255)),
        $privacyAccepted,
    ]);
    echo json_encode(['ok' => true, 'msg' => '¡Suscripción completada! Gracias por unirte.']);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['ok' => false, 'msg' => 'Error al procesar la suscripción. Inténtalo de nuevo.']);
}
