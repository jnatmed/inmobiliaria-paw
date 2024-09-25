<?php
namespace Paw\App\Models;

use Paw\Core\Model;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use Paw\Core\Traits\Loggable;
use Paw\App\Models\Reserva;

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

            return false;
        }
    }

    public function enviarMailAlDuenio($emailInteresado, $telefonoDelInteresado, $textoConsultaDelInteresado, $fullUrl, $emailDuenio)
    {
        /**
         * aca lo que se busca es usar las plantilla para redactar un
         * correo con estilos en linea guardarlos en el body y enviarlo
         * aqui evitamos mezclar html con php y combinamos 
         * el poder del motor de plantillas con php
         *  */
        $body = view('correoAlDuenioDeLaPublicacion', [
            'emailInteresado' => $emailInteresado,
            'telefonoDelInteresado' => $telefonoDelInteresado,
            'textoConsultaDelInteresado' => $textoConsultaDelInteresado,
            'fullUrl' => $fullUrl
        ], true);

        // Aca enviar un correo al usuario que esta logueado       
        $resultadoSend = $this->send(
            $emailDuenio,
            "Consulta sobre publicacion: ",
            $body
        );        

        return $resultadoSend;
    }

    public function comunicarAlInteresadoYalPropietario(Reserva $reserva, $nroReserva, $userName, $emailAddress, $correo_duenio)
    {

        // Mensaje de correo con estilos en línea
        $body = view('solicitudDeReservaAlojamiento', [
            'nroReserva' => $nroReserva,
            'userName' => $userName,
            'desde' => $reserva->getFechaInicio(),
            'hasta' => $reserva->getFechaFin(),
            'destino' => 'interesado'
        ], true);

        // Mensaje de correo con estilos en línea
        $bodyPropietario = view('solicitudDeReservaAlojamiento', [
            'nroReserva' => $nroReserva,
            'userName' => $userName,
            'desde' => $reserva->getFechaInicio(),
            'hasta' => $reserva->getFechaFin(),
            'destino' => 'propietario'
        ], true);

        // aca deberia enviar un correo al usuario que esta logueado       
        $resultadoSend = $this->send($emailAddress,
                            "Solicitud de Reserva Enviada para el usuario: $userName ",
                            $body,
                            );
                      
        if($resultadoSend){
            $this->logger->info("Correo enviado con exito ");
        }else{
            $this->logger->info("ERROR al enviar el Correo ");
        }                
        // Limpia la lista de destinatarios antes de enviar el siguiente correo
        $this->clearAddresses();

        $resultadoSendPropietario = $this->send($correo_duenio,
                            "Solicitud de Reserva del usuario: $userName ",
                            $body,
                            );
        

        $this->logger->info("resultado reservar alojamiento: ", [$resultadoSendPropietario]);                                   
    }

}
