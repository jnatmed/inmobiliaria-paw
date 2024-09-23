<?php

namespace Paw\App\Controllers;

use Paw\Core\Controller;
use Paw\App\Utils\Uploader;
use Paw\App\Utils\Utils;
use Paw\App\Models\PublicacionCollection;
use Paw\App\Utils\Verificador;
use Paw\App\Models\Mailer;
use Paw\App\Models\Publicacion;
use Paw\App\Models\Imagen;
use Paw\App\Models\ImagenCollection;
use Paw\Core\Exceptions\PostVacioException;

use Paw\Core\Exceptions\FallaEnCargaDeImagenesException;
use Paw\Core\Exceptions\PublicacionFailException;

use PDOException;
use Throwable;
use Exception;

class PublicacionController extends Controller
{
    public ?string $modelName = PublicacionCollection::class;
    public $usuario;
    public Verificador $verificador;
    public Uploader $uploader;
    public $utils;
    public $mailer;
    public $menuAndSession;

    public function __construct()
    {
        global $config;

        $this->uploader = new Uploader;
        $this->verificador = new Verificador;
        $this->utils = new  Utils();
        $this->mailer = new Mailer();

        parent::__construct();

        $this->usuario = new UsuarioController();
        $this->menu = $this->usuario->adjustMenuForSession($this->menu);

        $this->menuAndSession = $this->usuario->menuAndSession;
    }

    public function index()
    {
        $datos = ['titulo' => "PAWPERTIES | HOME"];

        view('home.view', array_merge(
            $this->menuAndSession,
            $datos,
            $this->model->traerTipos()
        ));
    }

    public function list()
    {
        try {
            // Obtener los filtros del request
            $zona = !is_null($this->request->get('zona')) ? htmlspecialchars($this->request->get('zona')) : null;
            $zona = $zona !== null ? ucwords(strtolower(trim($zona))) : null;

            $tipo = $this->request->get('tipo');

            // Verificación de tipos
            if (is_array($tipo)) {
                $tipo = $tipo ?? [];
            } elseif (is_string($tipo)) {
                if (empty($tipo)) {
                    $tipo = [];
                } else {
                    $tipo = [$tipo];
                }
            } else {
                $tipo = [];
            }

            $precio = !is_null($this->request->get('precio')) ? htmlspecialchars($this->request->get('precio')) : null;
            $instalaciones = array_merge($this->request->get('instalaciones') ?? []);

            // Obtener publicaciones filtradas
            $publicaciones = $this->model->getAllFilter($zona, $tipo, $precio, $instalaciones, null);

            $cantidadTotalPublicaciones = $this->model->getPublicacionesTotales();
            $mayorPrecio = $this->model->getPublicacionMayorPrecio();

            // Preparar datos para la vista
            $datos = [
                'zona' => $zona,
                'tipos' => $tipo,
                'precio' => $precio,
                'mayorPrecio' => $mayorPrecio,
                'publicaciones' => $publicaciones,
                'cantidadTotalPublicaciones' => $cantidadTotalPublicaciones,
                'titulo' => "PAWPERTIES | PROPIEDADES",
                'subtitulo' => "Propiedades en Alquiler"
            ];

            if ($this->request->isAjaxRequest()) {
                view('parts/lista-publicaciones.view', $datos);
                $this->logger->debug("REQUEST", ["AJAX"]);
            } else {
                view('publicaciones.list.view', array_merge($datos, $this->menuAndSession, $this->model->traerTipos()));
                $this->logger->debug("REQUEST", ["NO AJAX"]);
            }
        } catch (PDOException $e) {
            $error_message = "Error de base de datos al obtener las publicaciones: " . $e->getMessage();
            $this->logger->error($error_message);
            require $this->viewsDir . 'errors/not-found.view.php';
        }
    }


    public function verPublicacion()
    {

        // Obtener el ID de la publicación de la solicitud
        $id_publicacion = htmlspecialchars($this->request->get('id_pub'));

        // Obtener los datos de la publicación y sus imágenes
        $publicacion = $this->model->getOne($id_publicacion);

        // Verificar si se encontró la publicación
        if (!$publicacion) {
            $resultado = [
                "success" => false,
                "message" => "Publicación no encontrada."
            ];
            $this->logger->info("Publicación no encontrada: ID $id_publicacion.");
            view('errors/not-found.view', array_merge(
                ['error_message' => "Publicación no encontrada: ID $id_publicacion"],
                $this->menuAndSession
            ));
            exit();
        }

        // Aca se obtienen las reservas usando el modelo
        $reservas = $this->model->getReservas($id_publicacion);

        // se codifican las reservas a JSON para su uso en JavaScript
        $periodos_json = json_encode($reservas, JSON_UNESCAPED_SLASHES);

        // Preparar los datos para la vista
        $datos = [
            'publicacion' => $publicacion,
            'idUserSesion' => $this->usuario->getUserId(),
            'periodos_json' => $periodos_json,
            'reservas' => $reservas,
            'titulo' => "PAWPERTIES | PROPIEDAD"
        ];

        // Mostrar la vista de detalles de la publicación
        view('publicacion.details.view', array_merge(
            $datos,
            $this->menuAndSession,
        ));
    }


    public function contactarAlDuenio()
    {
        global $config;

        $emailInteresado = htmlspecialchars($this->request->get('email-interesado'));
        $telefonoDelInteresado = htmlspecialchars($this->request->get('telefono-interesado'));
        $textoConsultaDelInteresado = limpiarEntrada($this->request->get('texto-consulta'), true);
        $emailDuenio = htmlspecialchars($this->request->get('emailDuenio'));
        $fullUrl = htmlspecialchars($this->request->get('urlPublicacion'));
        $id_publicacion = htmlspecialchars($this->request->get('id_pub'));

        $this->logger->debug("datos entrada ContactarAlDuenio: ", [
            $emailInteresado,
            $telefonoDelInteresado,
            $textoConsultaDelInteresado,
            $emailDuenio,
            $fullUrl,
            $id_publicacion
        ]);

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
        $resultadoSend = $this->mailer->send(
            $emailDuenio,
            "Consulta sobre publicacion: ",
            $body
        );

        if ($resultadoSend) {
            $this->logger->info("Correo enviado con exito: ", [$this->usuario]);
        } else {
            $this->logger->info("ERROR al enviar el Correo: ", [$this->usuario]);
        }

        redirect('publicacion/ver?id_pub=' . $id_publicacion);
    }

    public function listaPublicacionesPropietario()
    {
        try {
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

            // Obtener el ID del usuario desde la sesión
            $this->logger->info("sesion: ", [$_SESSION]);

            $idUser = $this->usuario->getUserId();
            $zona = !is_null($this->request->get('zona')) ? htmlspecialchars($this->request->get('zona')) : null;
            $zona = $zona !== null ? ucwords(strtolower(trim($zona))) : null;
            $tipo = array_map('htmlspecialchars', $this->request->get('tipo') ?? []); //aplica la funcion a cada elemento del array
            $precio = !is_null($this->request->get('precio')) ? htmlspecialchars($this->request->get('precio')) : null;
            $instalaciones = $this->request->get('instalaciones') ?? [];

            $publicaciones = $this->model->getAllFilter($zona, $tipo, $precio, $instalaciones, $idUser);

            $cantidadTotalPublicaciones = $this->model->getPublicacionesTotales();

            // Datos para pasar a la vista
            $datos = [
                'idUser' => $idUser,
                'zona' => $zona,
                'tipos' => $tipo,
                'precio' => $precio,
                'instalaciones' => $instalaciones,
                'publicaciones' => $publicaciones,
                'cantidadTotalPublicaciones' => $cantidadTotalPublicaciones,
                'titulo' => "PAWPERTIES | MIS PROPIEDADES",
                'subtitulo' => "Mis Propiedades"
            ];

            if ($this->request->isAjaxRequest()) {
                return view('parts/lista-publicaciones.view', $datos);
            } else {
                view(
                    'publicaciones.list.view',
                    array_merge(
                        $datos,
                        $this->menuAndSession,
                        $this->model->traerTipos()
                    )
                );
            }
        } catch (PDOException $e) {
            $error_message = "Error de base de datos al obtener las publicaciones: " . $e->getMessage();
            $this->logger->error($error_message);

            view('errors/internal_error.view', [
                'error_message' => "Error de base de datos al obtener las publicaciones: " . $e->getMessage()
            ]);
        }
    }


    public function getImgPublicacion()
    {
        $idPublicacion = htmlspecialchars($this->request->get('id_pub'));
        $idImagen = htmlspecialchars($this->request->get('id_img'));

        try {

            // Obtener la imagen de la publicación
            $imagenPublicacion = $this->model->getImg($idPublicacion, $idImagen);

            $this->logger->info("(method- getImgPublicacion) - imagenPublicacion:", [$imagenPublicacion]);

            if ($imagenPublicacion === false) {
                // Si no se encuentra la imagen, devolver un código de error 404
                http_response_code(404);
                // exit;
            }


            $mime_type = Imagen::getMimeType($imagenPublicacion['path_imagen']);

            $this->logger->info("(method- getImgPublicacion) - mime_type: ", [$mime_type]);

            $this->logger->info("imagenPublicacion: -- ", [Imagen::UPLOADDIRECTORY . $imagenPublicacion['path_imagen']]);


            // Establecer el tipo MIME de la imagen y enviarla al cliente
            header("Content-type: " . $mime_type);
            echo file_get_contents(Imagen::UPLOADDIRECTORY . $imagenPublicacion['path_imagen']);
        } catch (Exception $e) {
            // Manejo de la excepción
            // Registrar el error utilizando el logger
            $this->logger->error("Error al obtener la imagen de la publicación: " . $e->getMessage());

            $mime_type = Imagen::getMimeType('image-not-found.png');
            header("Content-type: " . $mime_type);
            echo file_get_contents(Imagen::UPLOADDIRECTORY . 'image-not-found.png');
        }
    }


    public function new()
    {
        try {

            // Asumiendo que tienes una forma de obtener el id del usuario
            if (!$this->usuario->isUserLoggedIn()) {
                $resultado = [
                    "success" => false,
                    "message" => "Debe iniciar sesión para ver las reservas."
                ];
                $this->logger->info("Intento de ver pedido sin sesión iniciada.");
                /**
                 * aca guardamos el sitio en una sesion, a efectos de
                 * redirigir a la pagina de publicar
                 * asi no es necesario que tenga q navegar a la opcion 
                 * si de porsi es la accion que desea realizar el usuario
                 */
                $this->usuario->setRedirectTo($this->request->uri(true));

                redirect('iniciar-sesion');
            }

            if ($this->request->method() == 'POST') {

                $errors = [];

                $idUser = $this->usuario->getUserId();
                $this->logger->info("idUser: ", [$idUser]);

                $this->logger->info("POST: ", [$this->request->all()]);
                $this->logger->info("FILES: ", [$_FILES]);

                $provincia = sanitize($this->request->get('provincia'), $errors, 'provincia');
                $codigo_postal = sanitize($this->request->get('codigo_postal'), $errors, 'codigo_postal');
                $direccion = sanitize($this->request->get('direccion'), $errors, 'direccion');
                $direccion_completa = sanitize($this->request->get('direccion_completa'), $errors, 'direccion_completa');
                $precio = sanitize($this->request->get('precio'), $errors, 'precio');
                $nombreAlojamiento = sanitize($this->request->get('nombre-alojamiento'), $errors, 'nombre-alojamiento');
                $tipoAlojamiento = sanitize($this->request->get('tipo-alojamiento'), $errors, 'tipo-alojamiento');
                $capacidadMaxima = sanitize($this->request->get('capacidad-maxima'), $errors, 'capacidad-maxima');
                $cantBanios = sanitize($this->request->get('cant-banios'), $errors, 'cant-banios');
                $cantidadDormitorios = sanitize($this->request->get('cantidad-dormitorios'), $errors, 'cantidad-dormitorios');
                $cochera = sanitize($this->request->get('cochera')) ? 1 : 0; //
                $pileta = sanitize($this->request->get('pileta')) ? 1 : 0; //
                $aireAcondicionado = sanitize($this->request->get('aire-acondicionado')) ? 1 : 0; //
                $wifi = sanitize($this->request->get('wifi')) ? 1 : 0; //
                $normasAlojamiento = sanitize($this->request->get('normas-alojamiento'), $errors, 'normas-alojamiento');
                $descripcionAlojamiento = sanitize($this->request->get('descripcion-alojamiento'), $errors, 'descripcion-alojamiento');

                // Verifica si hay errores
                if (empty($errors)) {
                    // Preparar el array de datos para setear el objecto
                    $this->logger->debug("No hay errores..");

                    $publicacion = [
                        'provincia' => $provincia,
                        'codigo_postal' => $codigo_postal,
                        'direccion' => $direccion,
                        'direccion_completa' => $direccion_completa,
                        'precio' => $precio,
                        'nombre_alojamiento' => $nombreAlojamiento,
                        'tipo_alojamiento_id' => $tipoAlojamiento,
                        'capacidad_maxima' => $capacidadMaxima,
                        'cant_banios' => $cantBanios,
                        'cantidad_dormitorios' => $cantidadDormitorios,
                        'cochera' => $cochera,
                        'pileta' => $pileta,
                        'aire_acondicionado' => $aireAcondicionado,
                        'wifi' => $wifi,
                        'normas_alojamiento' => $normasAlojamiento,
                        'descripcion_alojamiento' => $descripcionAlojamiento,
                        'id_usuario' => $idUser,
                        'estado_id' => 1
                    ];
                    // setear el objeto Publicacion
                    $ObjPublicacion = new Publicacion($publicacion, $this->logger);
                    $publicacionObj = $ObjPublicacion->getEstadoConstructor();

                    if ($publicacionObj['exito']) {
                        // Verificar si $_FILES está vacío
                        $this->logger->info("Objeto publicacion instanciado con exito: ", [$publicacionObj]);

                        if (empty($_FILES['imagenes'])) {
                            // throw new FallaEnCargaDeImagenesException("Error: No se han subido archivos.");
                            $errors[] = '$files vacio';
                        }

                        $imagenesPublicacion = [];

                        $imagenesCollection = new ImagenCollection($_FILES['imagenes']);

                        $resultadoVerificacion = $imagenesCollection->verificarCollectionImagenes();

                        if ($resultadoVerificacion['exito']) {

                            // Manejar la inserción de datos
                            [$idPublicacionGenerado, $resultado] = $this->model->create($ObjPublicacion);

                            $this->logger->info("Info Publicacion: (method - new)", [$idPublicacionGenerado, $resultado]);


                            $resultadoSubidaImagenes = $imagenesCollection->guardarImagenes($idPublicacionGenerado, $idUser);

                            if ($resultadoSubidaImagenes['exito']) {

                                $this->logger->info("imagenesPublicacion: ", [$imagenesCollection->getImagenesCollection()]);
                                // Inserta todas las imágenes en la base de datos en una única operación
                                $this->model->insertMany('imagenes_publicacion', $imagenesCollection->getImagenesCollection());

                                redirect('publicacion/ver?id_pub=' . $idPublicacionGenerado);
                            } else {
                                view('publicacion.new.view', array_merge(
                                    $this->menuAndSession,
                                    ['errors' => $imagenesCollection->getErroresCollectionSubida()],
                                    $this->model->traerTipos()
                                ));
                            }
                        } else {
                            view('publicacion.new.view', array_merge(
                                $this->menuAndSession,
                                ['imagen_errors' => $imagenesCollection->getErroresCollection()],
                                $this->model->traerTipos()
                            ));
                        }
                    } else {

                        $this->logger->error("Publicacion no generada ");
                        // throw new PublicacionFailException("Publicacion no generada: $idPublicacionGenerado");
                        view('publicacion.new.view', array_merge(
                            $this->menuAndSession,
                            ['errors' => $publicacionObj['errors']],
                            $this->model->traerTipos()
                        ));
                    }
                } else {
                    $this->logger->error("Error: ", [$errors]);

                    view('publicacion.new.view', array_merge(
                        $this->menuAndSession,
                        ['errors' => $errors],
                        $this->model->traerTipos()
                    ));
                }
            } else {
                $datos = [
                    'titulo' => 'PAWPERTIES | NUEVA PUBLICACION',
                ];

                view('publicacion.new.view', array_merge(
                    $this->menuAndSession,
                    $datos,
                    $this->model->traerTipos()
                ));
            }
        } catch (Exception $e) {

            // Manejar la excepción
            $this->logger->error("Error en el proceso: " . $e->getMessage());

            view('errors/internal_error.view', [
                'error_message' => "Error en el proceso: " . $e->getMessage()
            ]);
        }
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

    public function gestionarPublicaciones()
    {
        // Asumiendo que tienes una forma de obtener el id del usuario
        if (!$this->usuario->isUserLoggedIn()) {
            $resultado = [
                "success" => false,
                "message" => "Debe iniciar sesión para ver el pedido."
            ];
            $this->logger->info("Intento de ver pedido sin sesión iniciada.");

            $this->usuario->setRedirectTo($this->request->uri(true));

            redirect('iniciar-sesion');
        }

        $listaPublicaciones = $this->model->traerPublicaciones($this->usuario->getUserId());

        $datos = [
            'titulo' => "PAWPERTIES | GESTIONAR",
            "exito" => true
        ];

        view('publicaciones.gestionar.view', array_merge(
            $listaPublicaciones,
            $datos,
            $this->menuAndSession
        ));
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

    public function actualizarEstadoPublicacion()
    {

        try {
            // Asumiendo que tienes una forma de obtener el id del usuario
            if (!$this->usuario->isUserLoggedIn()) {
                $resultado = [
                    "success" => false,
                    "message" => "Debe iniciar sesión para ver el pedido."
                ];
                $this->logger->info("Intento de ver pedido sin sesión iniciada.");

                $this->usuario->setRedirectTo($this->request->uri(true));

                redirect('iniciar-sesion');
            }
            $this->logger->info("Segmento 2: " . $this->request->getSegments(2));
            $accion = $this->request->getSegments(2);
            $idPublicacion = htmlspecialchars($this->request->get('id_pub'));



            if (!is_null($idPublicacion)) {

                $this->model->actualizarEstadoPublicacion($idPublicacion, $accion);

                /**
                 * enviar comunicacion a interesado 
                 */
                $body = view('correoDeCambioEstadoPublicacion', [
                    'fullUrl' => $this->request->host() . "/mis_publicaciones/reservas"
                ], true);

                // Aca enviar un correo al usuario que esta logueado       
                $resultadoSend = $this->mailer->send(
                    $this->usuario->getEmailAddress(),
                    "Cambio el estado de la publicacion: ",
                    $body
                );

                if ($resultadoSend) {
                    $this->logger->info("Correo enviado con exito: ", [$this->usuario]);
                } else {
                    $this->logger->info("ERROR al enviar el Correo: ", [$this->usuario]);
                }

                redirect('publicaciones/gestionar');
            } else {
                throw new Exception("ID de publicación no proporcionado");
            }
        } catch (Exception $e) {
            $this->logger->error("Error General al actualizar el estado de la publicacion: " . $e->getMessage());

            view('errors/internal_error.view', [
                'error_message' => "Error General al actualizar el estado de la publicacion: " . $e->getMessage()
            ]);
        }
    }

    public function apiPublicaciones()
    {
        $publicaciones = $this->model->getAll();
        header('Content-Type: application/json');
        echo json_encode(array_values($publicaciones));
    }
}
