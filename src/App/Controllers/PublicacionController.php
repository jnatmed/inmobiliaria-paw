<?php

namespace Paw\App\Controllers;

use Paw\Core\Controller;
use Paw\App\Utils\Uploader;
use Paw\App\Utils\Utils;
use Paw\App\Models\PublicacionCollection;
use Paw\App\Utils\Verificador;
use Paw\App\Models\Mailer;
use Paw\Core\Exceptions\PostVacioException; 
use Paw\Core\Exceptions\JsonVacioException; 
use Paw\Core\Exceptions\DireccionFailException; 
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
    // use Loggable;
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

    public function list()
    {
        try {
            $zona = !is_null($this->request->get('zona')) ? htmlspecialchars($this->request->get('zona')) : null;
            $zona = $zona !== null ? ucwords(strtolower(trim($zona))) : null;
            $tipo = $this->request->get('tipo') ?? []; 

            $tipo = $this->request->get('tipo');
            if (is_array($tipo) && isset($tipo[0]) && is_array($tipo[0])) {
                $tipo = $tipo[0]; // Asegura que tipo sea un array simple
                $this->logger->debug("es array multidimensional");
            } elseif (is_null($tipo)) {
                $tipo = [];
                $this->logger->debug("no es array []");
            }
            $this->logger->debug("tipo: ", $tipo);

            $precio = !is_null($this->request->get('precio')) ? htmlspecialchars($this->request->get('precio')) : null;
            $instalaciones = array_merge($this->request->get('instalaciones') ?? []); //aplica la funcion a cada elemento del array

            $publicaciones = $this->model->getAllFilter($zona, $tipo, $precio, $instalaciones, null);

            $cantidadTotalPublicaciones = $this->model->getPublicacionesTotales();

            $this->logger->info("Publicaciones: ", [$publicaciones]);

            $datos = [
                'zona' => $zona,
                'tipos' => $tipo,
                'precio' => $precio,
                'publicaciones' => $publicaciones,
                'cantidadTotalPublicaciones' => $cantidadTotalPublicaciones,
                'titulo' => "PAWPERTIES | PROPIEDADES"
            ];

            $mergedArray = array_merge(
                $datos,
                ['tipo' => $tipo],
                ['instalaciones' => $instalaciones]
            );

            $this->logger->debug("dato (antes de pasar a la vista): ", $datos);
            $this->logger->debug("menuAndSession (antes de pasar a la vista): ", $this->menuAndSession);

            view('publicaciones.list.view',  array_merge($datos, $this->menuAndSession));

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
            require $this->viewsDir . 'error.view.php'; // Redirigir a una página de error
            return;
        }

        // Aca se obtienen las reservas usando el modelo
        $reservas = $this->model->getReservas($id_publicacion);

        // se codifican las reservas a JSON para su uso en JavaScript
        $periodos_json = json_encode($reservas, JSON_UNESCAPED_SLASHES);

        // Preparar los datos para la vista
        $datos = [
            'publicacion' => $publicacion,
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
            // var_dump($publicaciones);
            $this->logger->info("Publicaciones: ", [$publicaciones]);

            $datos = [
                'idUser' => $idUser,
                'zona' => $zona,
                'tipos' => $tipo,
                'precio' => $precio,
                'instalaciones' => $instalaciones,
                'publicaciones' => $publicaciones,
                'cantidadTotalPublicaciones' => $cantidadTotalPublicaciones,
                'titulo' => "PAWPERTIES | MIS PROPIEDADES"
            ];

            view(
                'publicaciones-propietario.list.view',
                array_merge(
                    $datos,
                    $this->menuAndSession
                )
            );
            // require $this->viewsDir . 'publicaciones-propietario.list.view.php';
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


            $mime_type = Uploader::getMimeType($imagenPublicacion['path_imagen']);

            $this->logger->info("(method- getImgPublicacion) - mime_type: ", [$mime_type]);

            $this->logger->info("imagenPublicacion: ", [Uploader::UPLOADDIRECTORY . $imagenPublicacion['path_imagen']]);


            // Establecer el tipo MIME de la imagen y enviarla al cliente
            header("Content-type: " . $mime_type);
            echo file_get_contents(Uploader::UPLOADDIRECTORY . $imagenPublicacion['path_imagen']);
        } catch (Exception $e) {
            // Manejo de la excepción
            // Registrar el error utilizando el logger
            $this->logger->error("Error al obtener la imagen de la publicación: " . $e->getMessage());

            $mime_type = Uploader::getMimeType('image-not-found.png');
            header("Content-type: " . $mime_type);
            echo file_get_contents(Uploader::UPLOADDIRECTORY . 'image-not-found.png');
        }
    }


    public function new()
    {
        try {
            if ($this->request->method() == 'POST') {

                if (!$this->usuario->isUserLoggedIn()) {
                    $resultado = [
                        "success" => false,
                        "message" => "Debe iniciar sesión para ver el pedido."
                    ];
                    $this->logger->info("Intento de ver pedido sin sesión iniciada.");

                    redirect('iniciar-sesion');
                }

                // Verificar si $_POST está vacío
                if (empty($_POST)) {
                    throw new PostVacioException("Error: La solicitud POST está vacía.");
                }

                // Obtener el ID del usuario desde la sesión
                $this->logger->info("sesion: ", [$_SESSION]);

                $idUser = $this->usuario->getUserId();
                $this->logger->info("idUser: ", [$idUser]);

                $this->logger->info("POST: ", [$this->request->all()]);
                $this->logger->info("FILES: ", [$_FILES]);

                $provincia = htmlspecialchars($this->request->get('provincia') ?? '');
                $codigo_postal = htmlspecialchars($this->request->get('codigo_postal') ?? '');

                // Verificar y decodificar el JSON de la dirección
                $direccion = htmlspecialchars($this->request->get('direccion') ?? '');
                if (is_null($direccion) || $direccion === '') {
                    throw new JsonVacioException("Error: JSON proporcionado es nulo o vacío.");
                } else {
                    // Convertir entidades HTML a caracteres normales
                    $direccion = html_entity_decode($direccion);

                    // Decodificar la cadena JSON
                    $coordenadas = json_decode($direccion, true);
                    if ($coordenadas === null) {
                        throw new DireccionFailException("Error al decodificar la dirección: " . json_last_error_msg());
                    }
                }

                $latitud = $coordenadas['lat'] ?? null;
                $longitud = $coordenadas['lng'] ?? null;

                $precio = sanitize($this->request->get('precio'));
                $nombreAlojamiento = sanitize($this->request->get('nombre-alojamiento'));
                $tipoAlojamiento = sanitize($this->request->get('tipo-alojamiento'));
                $capacidadMaxima = sanitize($this->request->get('capacidad-maxima'));
                $cantBanios = sanitize($this->request->get('cant-banios'));
                $cantidadDormitorios = sanitize($this->request->get('cantidad-dormitorios'));
                $cochera = sanitize($this->request->get('cochera')) ? 1 : 0;
                $pileta = sanitize($this->request->get('pileta')) ? 1 : 0;
                $aireAcondicionado = sanitize($this->request->get('aire-acondicionado')) ? 1 : 0;
                $wifi = sanitize($this->request->get('wifi')) ? 1 : 0;
                $normasAlojamiento = sanitize($this->request->get('normas-alojamiento'));
                $descripcionAlojamiento = sanitize($this->request->get('descripcion-alojamiento'));

                // Preparar el array de datos para la inserción
                $publicacion = [
                    'provincia' => $provincia,
                    'codigo_postal' => $codigo_postal,
                    'direccion' => $direccion,
                    'latitud' => $latitud,
                    'longitud' => $longitud,
                    'precio' => $precio,
                    'nombre_alojamiento' => $nombreAlojamiento,
                    'tipo_alojamiento' => $tipoAlojamiento,
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

                // Manejar la inserción de datos
                list($idPublicacionGenerado, $resultado) = $this->model->create($publicacion);

                // Si la inserción fue exitosa, procede con el manejo de las imágenes
                if ($idPublicacionGenerado) {
                    // Verificar si $_FILES está vacío
                    if (empty($_FILES['imagenes'])) {
                        throw new FallaEnCargaDeImagenesException("Error: No se han subido archivos.");
                    }

                    $imagenesPublicacion = [];
                    $files = $_FILES['imagenes'];

                    for ($i = 0; $i < count($files['name']); $i++) {
                        if ($files['name'][$i] != "") {
                            $file = [
                                'name' => $files['name'][$i],
                                'type' => $files['type'][$i],
                                'tmp_name' => $files['tmp_name'][$i],
                                'error' => $files['error'][$i],
                                'size' => $files['size'][$i],
                            ];
                        }

                        if ($file['error'] != UPLOAD_ERR_OK) {
                            throw new FallaEnCargaDeImagenesException("Error al subir una imagen: " . $file['error']);
                        }

                        // Subiendo el archivo usando el metodo uploadFile de Uploader
                        $resultUpload = $this->uploader->uploadFile($file);

                        $this->logger->info("resultado insercion capa CONTROLLER", [$resultUpload]);

                        if ($resultUpload['exito'] == Uploader::UPLOAD_COMPLETED) {
                            $imagenesPublicacion[] = [
                                'id_publicacion' => $idPublicacionGenerado,
                                'path_imagen' => $resultUpload['nombre_imagen'],
                                'nombre_imagen' => $resultUpload['nombre_imagen'],
                                'id_usuario' => $idUser
                            ];
                        } else {
                            throw new FallaEnCargaDeImagenesException("Error al subir una imagen: " . $resultUpload['description']);
                        }
                    }

                    $this->logger->info("imagenesPublicacion: ", [$imagenesPublicacion]);
                    // Inserta todas las imágenes en la base de datos en una única operación
                    $this->model->insertMany('imagenes_publicacion', $imagenesPublicacion);

                    redirect('mis_publicaciones');
                } else {

                    $this->logger->error("Publicacion no generada: ", [$idPublicacionGenerado]);
                    throw new PublicacionFailException("Publicacion no generada: $idPublicacionGenerado");
                }
            } else {
                $datos = ['titulo' => 'PAWPERTIES | NUEVA PUBLICACION'];
                view('publicacion.new.view', array_merge(
                    $this->menuAndSession,
                    $datos
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

                redirect('iniciar-sesion');
            }


            // Obtener las reservas pendientes y confirmadas
            $reservas = $this->model->obtenerReservasPendientesYConfirmadas($this->usuario->getUserId());

            $datos = [
                'reservas' => $reservas,
                'titulo' => "PAWPERTIES | RESERVAS"
            ];

            $this->logger->info("RESERVAS : ", [$datos]);

            view('publicaciones.reservas.view', array_merge(
                $datos,
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
                throw new Exception("ID de publicación o reserva no proporcionado: " . $e->getMessage());
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

                redirect('iniciar-sesion');
            }
            $this->logger->info("Segmento 2: " . $this->request->getSegments(2));
            $accion = $this->request->getSegments(2);
            $idPublicacion = htmlspecialchars($this->request->get('id_pub'));

            if (!is_null($idPublicacion)) {

                $this->model->actualizarEstadoPublicacion($idPublicacion, $accion);

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
