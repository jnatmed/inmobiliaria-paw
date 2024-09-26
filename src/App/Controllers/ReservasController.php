<?php

namespace Paw\App\Controllers;

use Paw\App\Utils\Verificador;
use Paw\App\Utils\Uploader;
use Paw\App\Models\Mailer;
use Paw\Core\Database\QueryBuilder;

use Paw\Core\Controller;
use Paw\App\Models\Reserva;
use Paw\App\Models\ReservasCollection;
use Paw\App\Models\PublicacionCollection;
use Exception;

class ReservasController extends Controller
{
    public ?string $modelName = ReservasCollection::class;
    public Uploader $uploader;
    public Verificador $verificador;
    public $usuario;
    public $mailer;
    public $menuAndSession;
    public $publicationCollection;

    public function __construct()
    {
        global $log, $connection;
        parent::__construct();

        $this->uploader = new Uploader;
        $this->usuario = new UsuarioController();
        $this->verificador = new Verificador;
        $this->mailer = new Mailer();
        $this->mailer->setLogger($log);
        $this->publicationCollection = new PublicacionCollection();
        $this->publicationCollection->setQueryBuilder(new QueryBuilder($connection, $log));
        $this->usuario = new UsuarioController();
        $this->menu = $this->usuario->adjustMenuForSession($this->menu);

        $this->menuAndSession = $this->usuario->menuAndSession;
    }

    public function verReservas()
    {

        try {
            // Asumiendo que tienes una forma de obtener el id del usuario
            if (!$this->usuario->isUserLoggedIn()) {
                $resultado = [
                    "success" => false,
                    "message" => "Debe iniciar sesión para ver las reservas."
                ];
                $this->logger->info("Intento de ver pedido sin sesión iniciada.");

                $this->usuario->setRedirectTo($this->request->uri(true));

                redirect('iniciar-sesion');
            }


            // Obtener las reservas pendientes y confirmadas
            $reservas = $this->model->obtenerReservasPendientesYConfirmadas($this->usuario->getUserId());

            $reservasSolicitadasPorUserSesion = $this->model->getSolicitudesDeReserva($this->usuario->getUserId());

            $datos = [
                'reservas' => $reservas,
                'reservasSolicitadasPorUserSesion' => $reservasSolicitadasPorUserSesion,
                'titulo' => "PAWPERTIES | RESERVAS"
            ];

            $this->logger->info("RESERVAS : ", [$reservas]);

            view('publicaciones.reservas.view', array_merge(
                $datos,
                ['idUserSesion' => $this->usuario->getUserId()],
                $this->menuAndSession
            ));
        } catch (Exception $e) {
            $this->logger->error("Error al obtener la lista de reservas: " . $e->getMessage());

            view('errors/internal_error.view', [
                'error_message' => "Error al obtener la lista de reservas: " . $e->getMessage()
            ]);
        }
    }

    public function actualizarEstadoReserva()
    {
        try {
            // Asumiendo que tienes una forma de obtener el id del usuario
            if (!$this->usuario->isUserLoggedIn()) {
                $resultado = [
                    "success" => false,
                    "message" => "Debe iniciar sesión para ver el pedido."
                ];
                $this->logger->info("Intento de ver pedido sin sesión iniciada.");

                redirect('iniciar-sesion');
            }

            $this->logger->info("Segmento 2: " . $this->request->getSegments(2));
            $accion = $this->request->getSegments(2);
            $idPublicacion = htmlspecialchars($this->request->get('id_pub'));
            $idReserva = htmlspecialchars($this->request->get('id_reserva'));

            if ($idPublicacion && $idReserva) {

                $this->model->actualizarEstadoReserva($idReserva, $accion);

                redirect('mis_publicaciones/reservas');
            } else {
                throw new Exception("ID de publicación o reserva no proporcionado: ");
            }
        } catch (Exception $e) {
            $this->logger->error("Error General al cancelar la reserva: " . $e->getMessage());

            view('errors/internal_error.view', [
                'error_message' => "Error General al cancelar la reserva: " . $e->getMessage()
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
        $precio_x_noche = 0;
        $estado_reserva = 'pendiente';
        $notas = 'ninguna';

        $reserva = [
            'id_publicacion' => $id_publicacion,
            'id_usuario_reserva' => $this->usuario->getUserId(),
            'fecha_inicio' => $desde,
            'fecha_fin' => $hasta,
            'precio_por_noche' => $precio_x_noche,
            'estado_reserva' => $estado_reserva,
        ];

        $ObjReserva = new Reserva($reserva, $this->logger);
        $resultadoObjReserva = $ObjReserva->getEstadoConstructor();

        if ($resultadoObjReserva['exito']) {
            $alojamientoReservado = $this->model->reservarAlojamiento($ObjReserva);

            if ($alojamientoReservado['exito']) {
                $this->mailer->comunicarAlInteresadoYalPropietario(
                    $ObjReserva,
                    $alojamientoReservado['nro_reserva'],
                    $this->usuario->getUsername(),
                    $this->usuario->getEmailAddress(),
                    $correo_duenio
                );
            }

            $this->request->setResultadoEnSesion("resultadoReserva", $alojamientoReservado);

            $this->logger->debug("info resultadoReserva: ", [$alojamientoReservado]);

            redirect('publicacion/ver?id_pub=' . $id_publicacion);
        } else {

            $this->request->setResultadoEnSesion("resultadoReserva", $resultadoObjReserva);

            $this->logger->debug("info resultadoReserva: ", [$resultadoObjReserva]);

            redirect('publicacion/ver?id_pub=' . $id_publicacion);
        }
    }
}
