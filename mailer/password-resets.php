<?php 
require_once __DIR__ . '/../inc/config.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require __DIR__ . '/../vendor/autoload.php'; // Composer autoloader

/**
 * Envía el correo de restablecimiento de contraseña.
 *
 * @param string $email         Correo electrónico del destinatario.
 * @param string $nombreCompleto Nombre completo del usuario (nombre y apellido).
 * @param string $resetLink     Enlace para restablecer la contraseña.
 * @param string $url           URL base de la aplicación.
 * @param string $logo          Nombre del archivo de logo (usado en la imagen del correo).
 * @return bool                 True si se envió el correo; false en caso de error.
 */
function sendResetPasswordEmail($email, $nombreCompleto, $resetLink, $url, $logo) {
    $mail = new PHPMailer(true);

    try {
        // Configurar SMTP
        // Config SMTP
        $mail->isSMTP();
        $mail->Host       = SMTP_HOST;
        $mail->SMTPAuth   = true;
        $mail->Username   = SMTP_USER;
        $mail->Password   = SMTP_PASS;
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = SMTP_PORT;

        $mail->CharSet    = 'UTF-8';

        // Configurar remitente y destinatario usando los parámetros recibidos
        $mail->setFrom(MAIL_SENDER, NOMBRE_SITIO);
        $mail->addAddress($email, $nombreCompleto);

        // Configurar el contenido del correo (HTML)
        $mail->isHTML(true);
        $mail->Subject = 'Restablece tu contraseña';

        $mensaje = '<h2>¡Hola, '.htmlspecialchars($nombreCompleto).'!</h2>
      <p>Recibes este correo porque has solicitado restablecer la contraseña de tu cuenta en '.NOMBRE_SITIO.'. Para continuar con el proceso, haz clic en el siguiente enlace:</p>
      <div class="cta">
        <a href="'.$resetLink.'" target="_blank">Restablecer Contraseña</a>
      </div>
      <p>Si el botón anterior no funciona, copia y pega la siguiente dirección en tu navegador:</p>
      <p>'.$resetLink.'</p>
      <p><strong>Nota:</strong> Este enlace expirará en 1 hora. Si no has solicitado restablecer tu contraseña, ignora este mensaje.</p>
      <p>Gracias,<br>El equipo de '.NOMBRE_SITIO.'</p>';

        ob_start();	
        require __DIR__ . '/header_mail.php';
        echo $mensaje;
        require __DIR__ . '/footer_mail.php';
        $mail->Body = ob_get_clean();

        $mail->send();
        return true;
    } catch (Exception $e) {
        error_log("Error al enviar el correo: {$mail->ErrorInfo}");
        return false;
    }
}
?>



