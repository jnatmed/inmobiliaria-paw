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

    public function send($to, $subject, $body, $altBody = '', $from = null,$name = null) {

        global $config;

        try {
            //evita que un destinatario anterior quede agregado al siguiente correo
            $this->mail->clearAddresses();

            if (!isset($from) || !isset($name)) {
                $this->mail->setFrom(
                    $config->get('MAIL_FROM'),
                    $config->get('MAIL_NAME')
                );
            } else {
                $this->mail->setFrom(
                    $from,
                    $name
                );
            }

            $this->mail->addAddress($to);

            $this->mail->isHTML(true);

            $this->mail->Subject = $subject;

            $this->mail->Body = $body;

            $this->mail->AltBody = $altBody;

            $this->mail->send();

            return true;

        } catch (Exception $e) {
            $this->logger->error(
                'Error al enviar un correo.',
                ['mensaje' => $e->getMessage()]
            );

            return false;

        } finally {
            //Tambien se limpia si phpmailer arroja una excepcion
            $this->mail->clearAddresses();
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
        $resultadoSend = $this->send(
            $emailAddress,
            "Solicitud de Reserva Enviada para el usuario: $userName ",
            $body,
        );

        if ($resultadoSend) {
            $this->logger->info("Correo enviado con exito ");
        } else {
            $this->logger->info("ERROR al enviar el Correo ");
        }
        // Limpia la lista de destinatarios antes de enviar el siguiente correo
        $this->clearAddresses();

        $resultadoSendPropietario = $this->send(
            $correo_duenio,
            "Solicitud de Reserva del usuario: $userName ",
            $bodyPropietario,
        );


        $this->logger->info("resultado reservar alojamiento: ", [$resultadoSendPropietario]);
    }


    public function comunicarCambioEstadoReserva(array $reserva, string $nuevoEstado): bool {

        $destinatario = null;
        $asunto = null;
        $titulo = null;
        $mensaje = null;

        //Cuando el propietario acepta se avisa al solicitante
        if ($nuevoEstado === 'confirmada') {
            $destinatario = $reserva['email_solicitante'] ?? null;

            $asunto = 'Tu reserva fue confirmada';

            $titulo = 'Reserva confirmada';

            $mensaje = 'El propietario aceptó tu solicitud de reserva.';
        }

        //Cuando el propietario rechaza se avisa al solicitante
        elseif ($nuevoEstado === 'rechazada') {
            $destinatario = $reserva['email_solicitante'] ?? null;

            $asunto = 'Tu reserva fue rechazada';

            $titulo = 'Reserva rechazada';

            $mensaje = 'El propietario rechazó tu solicitud de reserva.';
        }

        //Cuando el solicitante cancela se avisa al propietario
        elseif ($nuevoEstado === 'cancelada') {
            $destinatario = $reserva['email_propietario'] ?? null;

            $asunto = 'Una reserva fue cancelada';

            $titulo = 'Reserva cancelada';

            $nombreSolicitante = $reserva['nombre_solicitante'] ?? 'El solicitante';

            $mensaje = $nombreSolicitante . ' canceló la reserva.';

        } else {
            return false;
        }

        //No se intenta enviar si el correo recuperado de la base de datos no es valido
        if (!is_string($destinatario) || !filter_var($destinatario, FILTER_VALIDATE_EMAIL)) {
            return false;
        }

        $body = view(
            'correoCambioEstadoReserva',
            [
                'titulo' => $titulo,

                'mensaje' => $mensaje,

                'nroReserva' => $reserva['id'],

                'alojamiento' => $reserva['nombre_alojamiento'],

                'desde' => $reserva['fecha_inicio'],

                'hasta' => $reserva['fecha_fin'],

                'estado' => $nuevoEstado
            ],
            true
        );

        return $this->send(
            $destinatario,
            $asunto,
            $body
        );
    }


    // Este es el formulario de contacto del home
    public function enviarFormContacto($nombre, $apellido, $telefono, $emailOrigen, $consulta)
    {
        global $config;

        $body = view('correoContacto', [
            'nombre' => $nombre,
            'apellido' => $apellido,
            'telefono' => $telefono,
            'emailOrigen' => $emailOrigen,
            'consulta' => $consulta
        ], true);

        $emailEmpresa = $config->get('COMPANY_MAIL');

        $resultadoSend = $this->send(
            $emailEmpresa,
            "Consulta",
            $body,
            from: $emailOrigen,
            name: ($nombre . " " . $apellido)
        );

        if($resultadoSend)
        {
            return [
                "exito" => true,
                "mensaje" => "Consulta Enviada.",
            ];
    
        }else{

            return [
                "exito" => false,
                "mensaje" => "No se pudo tu mensaje"
            ];        
    
        }
    }
}
