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

    public function __construct()
    {
        parent::__construct();

        $this->uploader = new Uploader;
        $this->usuario = new UsuarioController();
        $this->verificador = new Verificador;
        $this->mailer = new Mailer();

        $this->usuario = new UsuarioController();
        $this->menu = $this->usuario->adjustMenuForSession($this->menu);
    }

    public function reservas()
    {
        try {
            /**
             * envio los periodos que van a estar reservados y los muestro en el front y manipulo javascript
             */
            $id_publicacion = $this->request->get('id_pub');
            $this->logger->info("id_publicacion: $id_publicacion");

            // Obtén las reservas usando el modelo
            $reservas = $this->model->getReservas($id_publicacion);

            // Codifica las reservas a JSON para su uso en JavaScript
            $periodos_json = json_encode($reservas, JSON_UNESCAPED_SLASHES);

            $this->logger->info("periodos_json: $periodos_json");

            require $this->viewsDir . 'reservas-propiedad.view.php';
        } catch (Exception $e) {
            $this->logger->error('Error al obtener las reservas: ' . $e->getMessage());
            // Puedes redirigir a una página de error o mostrar un mensaje de error
            require $this->viewsDir . 'errors/not-found.view.php';
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
            require $this->viewsDir . 'login.view.php'; // Redirigir a la página de inicio de sesión
            return;
        }

        $id_publicacion = $this->request->get('id_publicacion');
        $desde = $this->request->get('input-desde');
        $hasta = $this->request->get('input-hasta');
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
        $mensajeCorreo = '
        <!DOCTYPE html>
        <html lang="es">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <style>
                body {
                    font-family: Arial, sans-serif;
                    line-height: 1.6;
                }
                .container {
                    max-width: 600px;
                    margin: 0 auto;
                    padding: 20px;
                    border: 1px solid #ddd;
                    border-radius: 10px;
                }
                .header {
                    background-color: #f4f4f4;
                    padding: 10px 0;
                    text-align: center;
                    border-bottom: 1px solid #ddd;
                }
                .content {
                    padding: 20px;
                }
                h4 {
                    color: #333;
                }
                p {
                    color: #555;
                }
            </style>
        </head>
        <body>
            <div class="container">
                <div class="header">
                    <h4>Reserva Realizada con Éxito</h4>
                </div>
                <div class="content">
                    <p>
                        Reserva Nro: ' . $nroReserva . '<br>
                        Reserva para el usuario: ' . $userName . '<br>
                        Desde: ' . $desde . '<br>
                        Hasta: ' . $hasta . '
                    </p>
                </div>
            </div>
        </body>
        </html>
        ';

        // aca deberia enviar un correo al usuario que esta logueado       
        $resultadoSend = $this->mailer->send(
            $emailAddress,
            "Reserva Exitosa para el usuario: $userName ",
            $mensajeCorreo,
        );

        if ($resultadoSend) {
            $this->logger->info("Correo enviado con exito: ", [$this->usuario]);
        } else {
            $this->logger->info("ERROR al enviar el Correo: ", [$this->usuario]);
        }
        $this->logger->info("Resultado Envio Correo: ", [$this->usuario]);
        $this->logger->info("resultado reservar alojamiento: ", [$alojamientoReservado]);

        header('Location: /publicacion/ver?id_pub=' . $id_publicacion);
        exit();
    }
}
