<?php
require_once __DIR__ . '/../../inc/config.php';
require_once __DIR__ . '/../../vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

header('Content-Type: application/json');

try {
    $input = json_decode(file_get_contents('php://input'), true);
    $testEmail = trim($input['email'] ?? '');

    if (empty($testEmail) || !filter_var($testEmail, FILTER_VALIDATE_EMAIL)) {
        echo json_encode(['success' => false, 'message' => 'Ingrese un correo electrónico válido para la prueba.']);
        exit;
    }

    if (empty(SMTP_HOST) || empty(SMTP_USER)) {
        echo json_encode(['success' => false, 'message' => 'Configure los datos SMTP primero.']);
        exit;
    }

    $mail = new PHPMailer(true);
    
    $mail->SMTPDebug = SMTP::DEBUG_OFF;
    $mail->isSMTP();
    $mail->Host       = SMTP_HOST;
    $mail->SMTPAuth   = true;
    $mail->Username   = SMTP_USER;
    $mail->Password   = SMTP_PASS;
    $mail->SMTPSecure = in_array(SMTP_PORT, [465, '465']) ? PHPMailer::ENCRYPTION_SMTPS : PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port       = (int)SMTP_PORT;
    $mail->CharSet    = 'UTF-8';

    $mail->setFrom(SMTP_USER, 'Prueba de Conexión');
    $mail->addAddress($testEmail);
    $mail->Subject = 'Prueba de conexión SMTP';
    $mail->Body    = 'Esta es una prueba de conexión desde el servidor de noticias. Si recibe este correo, la configuración SMTP es correcta.';

    $mail->send();
    
    echo json_encode(['success' => true, 'message' => 'Correo de prueba enviado correctamente a ' . htmlspecialchars($testEmail)]);

} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Error al enviar: ' . htmlspecialchars($mail->ErrorInfo)]);
}
