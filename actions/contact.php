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
    echo json_encode(['ok' => true, 'msg' => 'Mensaje enviado']);
    exit;
}

$name    = trim($_POST['name']    ?? '');
$email   = trim($_POST['email']   ?? '');
$phone   = trim($_POST['phone']   ?? '');
$message = trim($_POST['message'] ?? '');

$errors = [];
if (empty($name))                          $errors[] = 'El nombre es obligatorio';
if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Correo inválido';
if (empty($message))                       $errors[] = 'El mensaje es obligatorio';
if (mb_strlen($message) > 2000)            $errors[] = 'El mensaje es demasiado largo';

if ($errors) {
    echo json_encode(['ok' => false, 'msg' => implode('. ', $errors)]);
    exit;
}

try {
    $stmt = db()->prepare("
        INSERT INTO contact_messages (name, email, phone, message)
        VALUES (?, ?, ?, ?)
    ");
    $stmt->execute([
        mb_substr($name, 0, 255),
        mb_substr($email, 0, 255),
        mb_substr($phone, 0, 50),
        mb_substr($message, 0, 2000),
    ]);
    echo json_encode(['ok' => true, 'msg' => '¡Mensaje enviado! Te responderemos pronto.']);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['ok' => false, 'msg' => 'Error al enviar el mensaje. Inténtalo de nuevo.']);
}
