<?php
error_reporting(0);
ini_set('display_errors', 0);

require_once __DIR__ . '/../inc/config.php';
require_once __DIR__ . '/../vendor/autoload.php';

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

    $smtpHost = defined('SMTP_HOST') ? SMTP_HOST : '';
    $smtpUser = defined('SMTP_USER') ? SMTP_USER : '';
    $smtpPass = defined('SMTP_PASS') ? SMTP_PASS : '';
    $smtpPort = defined('SMTP_PORT') ? SMTP_PORT : '587';

    if (empty($smtpHost) || empty($smtpUser)) {
        echo json_encode(['success' => false, 'message' => 'Configure los datos SMTP primero.']);
        exit;
    }

    $mail = new PHPMailer(true);
    
    $mail->SMTPDebug = SMTP::DEBUG_OFF;
    $mail->isSMTP();
    $mail->Host       = $smtpHost;
    $mail->SMTPAuth   = true;
    $mail->Username   = $smtpUser;
    $mail->Password   = $smtpPass;
    $mail->SMTPSecure = in_array($smtpPort, [465, '465']) ? PHPMailer::ENCRYPTION_SMTPS : PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port       = (int)$smtpPort;
    $mail->CharSet    = 'UTF-8';

    $mail->setFrom($smtpUser, 'Prueba de Conexión');
    $mail->addAddress($testEmail);
    $mail->Subject = 'Prueba de conexión SMTP';
    $mail->Body    = 'Esta es una prueba de conexión desde el servidor de noticias. Si recibe este correo, la configuración SMTP es correcta.';

    $mail->send();
    
    echo json_encode(['success' => true, 'message' => 'Correo de prueba enviado correctamente a ' . htmlspecialchars($testEmail)]);

} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Error al enviar: ' . htmlspecialchars($e->getMessage())]);
} catch (Throwable $e) {
    echo json_encode(['success' => false, 'message' => 'Error: ' . htmlspecialchars($e->getMessage())]);
}
