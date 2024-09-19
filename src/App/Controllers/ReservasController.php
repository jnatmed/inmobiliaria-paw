<?php

namespace Paw\App\Controllers;

use Paw\App\Utils\Verificador;
use Paw\App\Utils\Uploader;
use Paw\App\Models\Mailer;

use Paw\Core\Controller;
use Paw\App\Models\PublicacionCollection;
use Exception;

class ReservasController extends Controller
{
    public ?string $modelName = PublicacionCollection::class;
    public Uploader $uploader;
    public Verificador $verificador;
    public $usuario;
    public $mailer;
    public $menuAndSession;
    public $publicationCollection;

    public function __construct()
    {
        parent::__construct();

        $this->uploader = new Uploader;
        $this->usuario = new UsuarioController();
        $this->verificador = new Verificador;
        $this->mailer = new Mailer();

        $this->usuario = new UsuarioController();
        $this->menu = $this->usuario->adjustMenuForSession($this->menu);

        $this->menuAndSession = $this->usuario->menuAndSession;

        $this->publicationCollection = new PublicacionCollection();

    }

    public function reservas()
    {
        try {
            /**
             * envio los periodos que van a estar reservados y los muestro en el front y manipulo javascript
             */
            $id_publicacion = htmlspecialchars($this->request->get('id_pub'));
            $this->logger->info("id_publicacion: $id_publicacion");

            // Obtén las reservas usando el modelo
            $reservas = $this->model->getReservas($id_publicacion);
            $publicacion = $this->publicationCollection->getOne($id_publicacion);

            // Codifica las reservas a JSON para su uso en JavaScript
            $periodos_json = json_encode($reservas, JSON_UNESCAPED_SLASHES);

            $this->logger->info("publicacion: $publicacion");
            $this->logger->info("periodos_json: $periodos_json");

            view('publicaciones.reservas.view');
        } catch (Exception $e) {
            $this->logger->error('Error al obtener las reservas: ' . $e->getMessage());
            // Puedes redirigir a una página de error o mostrar un mensaje de error

            view('errors/internal_error.view', [
                'error_message' => "Error al obtener las reservas: " . $e->getMessage()
            ]);
        }
    }

    public function obtenerIntervalosReserva()
    {
        try {
            $id_publicacion = $this->request->get('id_pub');
            $this->logger->info("id_publicacion: $id_publicacion");

            // Obtén las reservas usando el modelo
            $periodos = $this->model->getReservas($id_publicacion);

            // Devolver los intervalos de reserva como JSON
            echo json_encode($periodos);
        } catch (Exception $e) {
            $this->logger->error('Error al obtener los intervalos de reserva: ' . $e->getMessage());
            // Devolver un mensaje de error como JSON
            echo json_encode(['error' => 'Ocurrió un error al obtener los intervalos de reserva.']);
        }
    }

    public function reservarAlojamiento()
    {

        // Verificar si hay sesión iniciada
        if (!$this->usuario->isUserLoggedIn()) {
            $resultado = [
                "success" => false,
                "message" => "Debe iniciar sesión para ver el pedido."
            ];
            $this->logger->info("Intento de ver pedido sin sesión iniciada.");

            $this->usuario->setRedirectTo($this->request->uri(true));
            
            redirect('iniciar-sesion');
        }

        $correo_duenio = htmlspecialchars($this->request->get('email_duenio'));
        $id_publicacion = htmlspecialchars($this->request->get('id_publicacion'));
        $desde = htmlspecialchars($this->request->get('input-desde'));
        $hasta = htmlspecialchars($this->request->get('input-hasta'));
        $precio_x_noche = 800;
        $estado_reserva = 'pendiente';
        $notas = 'ninguna';

        $alojamientoReservado = $this->model->reservarAlojamiento(
            $id_publicacion,
            $this->usuario->getUserId(),
            $desde,
            $hasta,
            $precio_x_noche,
            $estado_reserva,
            $notas
        );

        // Datos dinámicos
        $nroReserva = $alojamientoReservado['nro_reserva'];
        $userName = $this->usuario->getUserName();
        $emailAddress = $this->usuario->getEmailAddress();

        // Mensaje de correo con estilos en línea
        $body = view('solicitudDeReservaAlojamiento', [
            'nroReserva' => $nroReserva,
            'userName' => $userName,
            'desde' => $desde,
            'hasta' => $hasta,
            'destino' => 'interesado'
        ], true);

        // Mensaje de correo con estilos en línea
        $bodyPropietario = view('solicitudDeReservaAlojamiento', [
            'nroReserva' => $nroReserva,
            'userName' => $userName,
            'desde' => $desde,
            'hasta' => $hasta,
            'destino' => 'propietario'
        ], true);

        // aca deberia enviar un correo al usuario que esta logueado       
        $resultadoSend = $this->mailer->send($emailAddress,
                            "Solicitud de Reserva Enviada para el usuario: $userName ",
                            $body,
                            );
                      
        if($resultadoSend){
            $this->logger->info("Correo enviado con exito: ", [$this->usuario] );
        }else{
            $this->logger->info("ERROR al enviar el Correo: ", [$this->usuario] );
        }                
        // Limpia la lista de destinatarios antes de enviar el siguiente correo
        $this->mailer->clearAddresses();

        $resultadoSendPropietario = $this->mailer->send($correo_duenio,
                            "Solicitud de Reserva del usuario: $userName ",
                            $body,
                            );
        

        $this->logger->info("resultado reservar alojamiento: ", [$alojamientoReservado]);                                                            

        redirect('publicacion/ver?id_pub='.$id_publicacion);
    }
}
