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

            $this->logger->debug(
                'Reservas cargadas.',
                [
                    'usuario_id' => $this->usuario->getUserId(),

                    'reservas_publicadas' => is_array($reservas) ? count($reservas) : 0,

                    'reservas_solicitadas' => is_array($reservasSolicitadasPorUserSesion) ? count($reservasSolicitadasPorUserSesion) : 0
                ]
            );

            view('publicaciones.reservas.view', array_merge(
                $datos,
                ['idUserSesion' => $this->usuario->getUserId()],
                $this->menuAndSession
            ));

        } catch (Exception $e) {
            $this->logger->error("Error al obtener la lista de reservas: " . $e->getMessage());

            http_response_code(500);

            view(
                'errors/internal_error.view',
                array_merge(
                    ['error_message' => 'No se pudo obtener la lista de reservas.'],
                    $this->menuAndSession
                )
            );
        }
    }

    public function actualizarEstadoReserva(){

        try {

            // Solamente usuarios normales
            $this->usuario->chequearTiposPermitidos([1, 3]);

            $this->usuario->chequearCsrf();

            $accion = $this->request->getSegments(2);

            if (!in_array($accion,['aceptar', 'rechazar', 'cancelar'], true)) {

                http_response_code(400);

                view(
                    'errors/bads-request.view',
                    array_merge(
                        ['error_message' => 'La acción solicitada no es válida.'],
                        $this->menuAndSession
                    )
                );

                return;
            }

            //Validar el ID enviado mediante post
            $errores = [];

            $idReserva =
                $this->verificador->entero(
                    $this->request->post('id_reserva'),
                    'id_reserva',
                    $errores,
                    1
                );

            if (!empty($errores)) {

                http_response_code(400);

                view(
                    'errors/bads-request.view',
                    array_merge(
                        ['error_message' => implode(' ', $errores)],
                        $this->menuAndSession
                    )
                );

                return;
            }

            //La reserva real, junto con el dueño y el solicitante, se obtiene de la base de datso.
            $reserva = $this->model->getReservaConDatos($idReserva);

            if (!$reserva) {

                http_response_code(404);

                view(
                    'errors/not-found.view',
                    array_merge(
                        ['error_message' => 'La reserva no existe.'],
                        $this->menuAndSession
                    )
                );

                return;
            }

            $usuarioActual = (int) $this->usuario->getUserId();

            $estadoActual = $reserva['estado_reserva'];

            $esPropietario = $usuarioActual === (int) $reserva['id_propietario'];

            $esSolicitante = $usuarioActual === (int) $reserva['id_usuario_reserva'];

            //Solamente el duenio de la publicacion puede aceptar o rechazar, y solamente si esta pendiente
            if (in_array($accion, ['aceptar', 'rechazar'], true)) {

                if (!$esPropietario) {

                    http_response_code(403);

                    view(
                        'errors/forbidden.view',
                        array_merge(
                            ['error_message' => 'Solo el dueño de la publicación puede aceptar o rechazar esta reserva.'],
                            $this->menuAndSession
                        )
                    );

                    return;
                }

                if ($estadoActual !== 'pendiente') {

                    http_response_code(400);

                    view(
                        'errors/bads-request.view',
                        array_merge(
                            ['error_message' => 'La reserva ya fue procesada y no puede aceptarse ni rechazarse nuevamente.'],
                            $this->menuAndSession
                        )
                    );

                    return;
                }

                $nuevoEstado = $accion === 'aceptar' ? 'confirmada' : 'rechazada';
            }

            //Solamente al solicitante se le permite cancelar una reserva pendiente o confirmada
            else {
                if (!$esSolicitante) {

                    http_response_code(403);

                    view(
                        'errors/forbidden.view',
                        array_merge(
                            ['error_message' => 'Solo el usuario que solicitó la reserva puede cancelarla.'],
                            $this->menuAndSession
                        )
                    );

                    return;
                }

                if (!in_array($estadoActual, ['pendiente', 'confirmada'], true)) {

                    http_response_code(400);

                    view(
                        'errors/bads-request.view',
                        array_merge(
                            ['error_message' => 'La reserva ya fue cancelada o rechazada y no puede cancelarse nuevamente.'],
                            $this->menuAndSession
                        )
                    );

                    return;
                }

                $nuevoEstado = 'cancelada';
            }

            //Despues de todas las verificaciones es modifica la base de datos
            $this->model->actualizarEstadoReserva($idReserva, $nuevoEstado);

            $correoEnviado = $this->mailer->comunicarCambioEstadoReserva($reserva, $nuevoEstado);

            if ($correoEnviado) {
                $this->logger->info(
                    'Correo de cambio de estado de reserva enviado.',
                    [
                        'reserva_id' => $idReserva,
                        'estado_nuevo' => $nuevoEstado
                    ]
                );
            } else {
                //La reserva ya fue actualizada
                $this->logger->warning(
                    'El estado de la reserva cambió, pero el correo no pudo enviarse.',
                    [
                        'reserva_id' => $idReserva,
                        'estado_nuevo' => $nuevoEstado
                    ]
                );
            }

            $this->logger->info(
                'Estado de reserva actualizado.',
                [
                    'reserva_id' => $idReserva,
                    'estado_anterior' => $estadoActual,
                    'estado_nuevo' => $nuevoEstado,
                    'usuario_id' => $usuarioActual
                ]
            );

            redirect('mis_publicaciones/reservas');

        } catch (Exception $e) {

            $this->logger->error(
                'Error al actualizar el estado de una reserva.',
                ['mensaje' => $e->getMessage()]
            );

            http_response_code(500);

            view(
                'errors/internal_error.view',
                array_merge(
                    ['error_message' => 'No se pudo actualizar el estado de la reserva.'],
                    $this->menuAndSession
                )
            );
        }
    }

    public function obtenerIntervalosReserva(){

        header('Content-Type: application/json; charset=UTF-8');

        $errores = [];

        $idPublicacion = $this->verificador->entero(
            $this->request->query('id_pub'),
            'id_pub',
            $errores,
            1
        );

        if ($idPublicacion === null) {
            http_response_code(400);
            echo json_encode(['error' => 'El identificador de la publicación no es válido.']);
            return;
        }

        try {
            $publicacion = $this->publicationCollection->getOne($idPublicacion);

            if (!$publicacion) {
                http_response_code(404);

                echo json_encode(['error' => 'La publicación no existe.']);

                return;
            }

            $usuarioActual = (int) ($this->usuario->getUserId() ?? 0);

            $tipoUsuario = $this->usuario->getUserType();

            $esDuenio = $usuarioActual > 0 && $usuarioActual === (int) $publicacion['id_usuario'];

            $esEmpleado = $tipoUsuario === 2;

            $estaAceptada = (int) $publicacion['estado_id'] === 2;

            if (!$estaAceptada && !$esDuenio && !$esEmpleado) {
                
                http_response_code(404);

                echo json_encode(['error' => 'La publicación no está disponible.']);

                return;
            }

            $this->logger->debug(
                'Intervalos de reserva solicitados.',
                ['publicacion_id' => $idPublicacion]
            );

            $periodos = $this->model->getReservas($idPublicacion);

            echo json_encode($periodos, JSON_UNESCAPED_SLASHES);

        } catch (Exception $e) {
            $this->logger->error(
                'Error al obtener los intervalos de reserva.',
                ['mensaje' => $e->getMessage()]
            );

            http_response_code(500);

            echo json_encode(['error' => 'No se pudieron obtener los intervalos de reserva.']);
        }
    }

    public function reservarAlojamiento(){

        $this->usuario->chequearTiposPermitidos([1, 3]);

        $this->usuario->chequearCsrf();

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
