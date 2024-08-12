<?php
namespace Paw\App\Models;

use Paw\Core\Model;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use Paw\Core\Traits\Loggable;

class Mailer extends Model
{
    private $mail;
    use Loggable;

    public function __construct()
    {
        global $config;

        $this->mail = new PHPMailer(true);

        // Configuración del servidor SMTP
        $this->mail->isSMTP();
        $this->mail->Host       = $config->get('MAIL_HOST');
        $this->mail->SMTPAuth   = true;
        $this->mail->Username   = $config->get('MAIL_FROM');
        $this->mail->Password   = $config->get('MAIL_PASS');
        $this->mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $this->mail->Port       = 587;
        $this->mail->CharSet    = 'UTF-8';
    }

    public function clearAddresses()
    {
        $this->mail->clearAddresses();
    }
    
    public function send($to, $subject, $body, $altBody = '')
    {
        global $config;
        try {
            // Configuración del correo electrónico
            $this->mail->setFrom($config->get('MAIL_FROM'), $config->get('MAIL_NAME'));
            $this->mail->addAddress($to);

            // Contenido del correo electrónico
            $this->mail->isHTML(true);
            $this->mail->Subject = $subject;
            $this->mail->Body    = $body;
            $this->mail->AltBody = $altBody;

            $this->mail->send();
            return true;
        } catch (Exception $e) {
            // Puedes agregar un log o manejar el error de otra manera aquí
            
            return false;
        }
    }
}
