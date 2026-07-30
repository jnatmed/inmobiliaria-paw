<?php

namespace Paw\App\Controllers;

use Paw\App\Utils\Verificador;
use Paw\App\Utils\Uploader;
use Paw\App\Models\Mailer;

use Paw\Core\Controller;
use Paw\App\Models\Reserva;
use Paw\App\Models\ReservasCollection;
use Paw\App\Models\PublicacionCollection;
use Paw\Core\Database\QueryBuilder;
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
        $this->usuario->setLogger($log);
        $this->verificador = new Verificador;
        $this->mailer = new Mailer();
        $this->mailer->setLogger($log);
        $this->publicationCollection = new PublicacionCollection();
        $this->publicationCollection->setQueryBuilder(new QueryBuilder($connection, $log));
        $this->menu = $this->usuario->adjustMenuForSession($this->menu);

        $this->menuAndSession = $this->usuario->menuAndSession;
    }

    public function verReservas()
    {

        try {

            $this->usuario->chequearTiposPermitidos([1, 3]);

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
            $this->usuario->chequearSesion();

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

    public function reservarAlojamiento(){

        $this->usuario->chequearTiposPermitidos([1, 3]);

        $errores = [];

        $idPublicacion = $this->verificador->entero(
            $this->request->post('id_publicacion'),
            'id_publicacion',
            $errores,
            1
        );

        $desde = $this->verificador->fecha(
            $this->request->post('input-desde'),
            'input-desde',
            $errores
        );

        $hasta = $this->verificador->fecha(
            $this->request->post('input-hasta'),
            'input-hasta',
            $errores
        );

        if ($desde !== null && $hasta !== null && $desde >= $hasta){
            $errores['rango'] = 'La fecha desde debe ser anterior a la fecha hasta.';
        }

        if ($desde !== null && $desde < date('Y-m-d')){
            $errores['fecha_desde'] = 'No se puede reservar una fecha pasada.';
        }

        //Si el ID no es un entero valido no existe una publicacion a la cual volver
        if ($idPublicacion === null){
            //Eliminar cualquier mensaje anterior para que no aparezca despues en otra publicacion
            $this->request->eliminarResultadoEnSesion('resultadoReserva');

            http_response_code(400);

            view(
                'errors/bads-request.view',
                array_merge(
                    ['error_message' => 'El identificador de la publicacion no es valido'],
                    $this->menuAndSession
                )
            );

            return;
        }


        if (!empty($errores)){
            $this->request->setResultadoEnSesion(
                    'resultadoReserva',
                    [
                        'exito' => false,
                        'mensaje' => implode(' ', $errores)
                    ]
                );

                redirect('publicacion/ver?id_pub=' . $idPublicacion);

                return;
        }


        //Recuperar desde la base la propiedad real
        $publicacion = $this->publicationCollection->getOne($idPublicacion);

        if (!$publicacion){
            http_response_code(404);

            view('errors/not-found.view', [
                'error_message' => "La publicación no existe."
            ]);

            return;
        }


        if ((int) $publicacion['estado_id'] !== 2) {
            $this->logger->warning(
                'Intento de reservar una publicación no habilitada.',
                [
                    'usuario_id' => $this->usuario->getUserId(),
                    'publicacion_id' => $idPublicacion,
                    'estado_id' => (int) $publicacion['estado_id']
                ]
            );

            http_response_code(404);

            view(
                'errors/not-found.view',
                array_merge(
                    ['error_message' =>'La publicación no está disponible para reservas.'],
                    $this->menuAndSession
                )
            );

            return;
        }

        $usuarioActual = (int) $this->usuario->getUserId();

        $propietario = (int) $publicacion['id_usuario'];

        //Regla de autorizacion del servidor
        if ($usuarioActual === $propietario){
            $this->logger->warning(
                'Intento de reservar una propiedad propia',
                [
                    'usuario_id' => $usuarioActual,
                    'publicacion_id' => $idPublicacion
                ]
            );

            $this->request->setResultadoEnSesion(
                'resultadoReserva',
                [
                    'exito' => false,
                    'mensaje' => 'No podes reservar una propiedad propia.'
                ]
            );

            redirect('publicacion/ver?id_pub=' . $idPublicacion);

            return;
        }

        //El correo y el precio se obtienen de la bd. No se confia en campos ocultos
        $correoDuenio = $publicacion['email'];
        $precioPorNoche = $publicacion['precio'];

        $reserva = [
            'id_publicacion' => $idPublicacion,
            'id_usuario_reserva' => $usuarioActual,
            'fecha_inicio' => $desde,
            'fecha_fin' => $hasta,
            'precio_por_noche' => $precioPorNoche,
            'estado_reserva' => 'pendiente'
        ];

        $objReserva = new Reserva(
            $reserva,
            $this->logger
        );

        $resultadoObjReserva = $objReserva->getEstadoConstructor();

        if (!$resultadoObjReserva['exito']){
            $this->request->setResultadoEnSesion(
                'resultadoReserva',
                $resultadoObjReserva
            );

            redirect('publicacion/ver?id_pub=' . $idPublicacion);

            return;
        }

        $alojamientoReservado = $this->model->reservarAlojamiento($objReserva);

        if ($alojamientoReservado['exito']){
            $this->mailer->comunicarAlInteresadoYalPropietario(
                $objReserva,
                $alojamientoReservado['nro_reserva'],
                $this->usuario->getUserName(),
                $this->usuario->getEmailAddress(),
                $correoDuenio
            );
        }

        $this->request->setResultadoEnSesion(
            'resultadoReserva',
            $alojamientoReservado
        );

        redirect('publicacion/ver?id_pub=' . $idPublicacion);

    }
}
