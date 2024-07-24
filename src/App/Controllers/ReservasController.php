<?php

namespace Paw\App\Controllers;

use Paw\App\Utils\Verificador;
use Paw\App\Utils\Uploader;

use Paw\Core\Controller;
use Paw\App\Models\PublicacionCollection;
use Exception;

class ReservasController extends Controller
{
    public ?string $modelName = PublicacionCollection::class;
    public Uploader $uploader;
    public Verificador $verificador;
    public $usuario;

    public function __construct()
    {
        parent::__construct();

        $this->uploader = new Uploader;
        $this->usuario = new UsuarioController();
        $this->verificador = new Verificador;

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

        $id_publicacion = $this->request->get('id_publicacion');
        $desde = $this->request->get('input-desde');
        $hasta = $this->request->get('input-hasta');
        $precio_x_noche = 800;
        $estado_reserva = 'pendiente';
        $notas = 'ninguna';
        
        $alojamientoReservado = $this->model->reservarAlojamiento($id_publicacion, 
                                                                  $desde, 
                                                                  $hasta, 
                                                                  $precio_x_noche, 
                                                                  $estado_reserva,
                                                                  $notas);

        $this->logger->info("resultado reservar alojamiento: ", [$alojamientoReservado]);                                                            

        header('Location: /publicacion/ver?id_pub='.$id_publicacion);
        exit();
    }
    

}