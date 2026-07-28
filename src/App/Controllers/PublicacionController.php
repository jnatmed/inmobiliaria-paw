<?php

namespace Paw\App\Controllers;

use Paw\Core\Controller;
use Paw\App\Utils\Uploader;
use Paw\App\Utils\Utils;
use Paw\App\Models\PublicacionCollection;
use Paw\App\Models\ReservasCollection;
use Paw\App\Utils\Verificador;
use Paw\App\Models\Mailer;
use Paw\App\Models\Publicacion;
use Paw\App\Models\Imagen;
use Paw\App\Models\ImagenCollection;

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
    public ReservasCollection $ReservasCollection;

    public function __construct()
    {
        global $config, $log;
        parent::__construct();

        $this->uploader = new Uploader;
        $this->verificador = new Verificador;
        $this->utils = new  Utils();
        $this->mailer = new Mailer();
        $this->mailer->setLogger($log);
        $this->ReservasCollection = new ReservasCollection();
        $this->ReservasCollection->setQueryBuilder($this->qb);

        $this->usuario = new UsuarioController();
        $this->menu = $this->usuario->adjustMenuForSession($this->menu);

        $this->menuAndSession = $this->usuario->menuAndSession;
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
                $this->logger->debug("tipo ES ARRAY", [$tipo]);
            } elseif (is_string($tipo)) {
                if (empty($tipo) || $tipo == "" || is_null($tipo)) {
                    $tipo = [];
                    $this->logger->debug("tipo ES string y es null o empty ", [$tipo]);
                } else {
                    $tipo = [$tipo];
                    $this->logger->debug("tipo ES string pero tiene valor", [$tipo]);
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


    public function verPublicacion(){

        $errores = [];

        //El ID llega por la URL: /publicacion/ver?id_pub=2 Por eso se usa query() y se comprueba que sea un entero positivo
        
        $idPublicacion = $this->verificador->entero(
            $this->request->query('id_pub'),
            'id_pub',
            $errores,
            1
        );


        if ($idPublicacion === null) {
            http_response_code(400);

            view(
                'errors/bads-request.view',
                array_merge(
                    ['error_message' => 'El identificador de la publicación no es válido.'],
                    $this->menuAndSession
                )
            );

            return;
        }

        $publicacion = $this->model->getOne($idPublicacion);

        //El ID tiene formato válido, pero la publicación no existe

        if (!$publicacion) {
            http_response_code(404);

            $this->logger->info(
                'Publicación no encontrada.',
                ['publicacion_id' => $idPublicacion]
            );

            view(
                'errors/not-found.view',
                array_merge(
                    ['error_message' => 'La publicación solicitada no existe.'],
                    $this->menuAndSession
                )
            );

            return;
        }

        $reservas = $this->ReservasCollection->getReservas($idPublicacion);

        $comentarios = $this->model->getComentarios($idPublicacion);

        $periodosJson = json_encode(
            $reservas,
            JSON_UNESCAPED_SLASHES
        );

        //Recuperar los mensajes temporales antes de eliminarlos de la sesión
        
        $resultadoReserva = $this->request->getResultadoGuardado('resultadoReserva');

        $resultadoComentario = $this->request->getResultadoGuardado('resultadoComentario');

        $resultadoContacto = $this->request->getResultadoGuardado('resultadoContacto');

        $datos = [
            'publicacion' => $publicacion,
            'idUserSesion' => $this->usuario->getUserId(),
            'periodos_json' => $periodosJson,
            'reservas' => $reservas,
            'titulo' => 'PAWPERTIES | PROPIEDAD',
            'comentarios' => $comentarios,
            'resultadoReserva' => $resultadoReserva,
            'resultadoComentario' => $resultadoComentario,
            'resultadoContacto' => $resultadoContacto
        ];

        //Los mensajes se muestran una sola vez

        $this->request->eliminarResultadoEnSesion('resultadoReserva');

        $this->request->eliminarResultadoEnSesion('resultadoComentario');

        $this->request->eliminarResultadoEnSesion('resultadoContacto');

        view(
            'publicacion.details.view',
            array_merge(
                $datos,
                $this->menuAndSession
            )
        );
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

        $resultadoSend = $this->mailer->enviarMailAlDuenio($emailInteresado, $telefonoDelInteresado, $textoConsultaDelInteresado, $fullUrl, $emailDuenio);

        if ($resultadoSend) {
            $this->request->setResultadoEnSesion(
                'resultadoContacto',
                [
                    'exito' => true,
                    'mensaje' => 'La consulta fue enviada correctamente.'
                ]
            );

            $this->logger->info(
                'Consulta enviada por correo.',
                ['publicacion_id' => $id_publicacion]
            );
        } else {
            $this->request->setResultadoEnSesion(
                'resultadoContacto',
                [
                    'exito' => false,
                    'mensaje' => 'No se pudo enviar la consulta. Intentá nuevamente más tarde.'
                ]
            );

            $this->logger->warning(
                'No se pudo enviar la consulta por correo.',
                ['publicacion_id' => $id_publicacion]
            );
        }

        redirect('publicacion/ver?id_pub=' . $id_publicacion);

    }

    public function listaPublicacionesPropietario()
    {
        try {
            $this->usuario->chequearSesion();

            // Obtener el ID del usuario desde la sesión

            $idUser = $this->usuario->getUserId();
            $zona = !is_null($this->request->get('zona')) ? htmlspecialchars($this->request->get('zona')) : null;
            $zona = $zona !== null ? ucwords(strtolower(trim($zona))) : null;
            $tipo = array_map('htmlspecialchars', $this->request->get('tipo') ?? []); //aplica la funcion a cada elemento del array
            $precio = !is_null($this->request->get('precio')) ? htmlspecialchars($this->request->get('precio')) : null;
            $instalaciones = $this->request->get('instalaciones') ?? [];

            $publicaciones = $this->model->getAllFilter($zona, $tipo, $precio, $instalaciones, $idUser);

            $cantidadTotalPublicaciones = $this->model->getPublicacionesTotales();

            $mayorPrecio = $this->model->getPublicacionMayorPrecio($idUser);

            // Datos para pasar a la vista
            $datos = [
                'idUser' => $idUser,
                'zona' => $zona,
                'tipos' => $tipo,
                'precio' => $precio,
                'mayorPrecio' => $mayorPrecio,
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
        $idPublicacion = $this->request->get('id_pub');
        $idImagen = $this->request->get('id_img');

        try {

            // Obtener la imagen de la publicación
            $imagenPublicacion = $this->model->getImg($idPublicacion, $idImagen);

            $this->logger->info("(method- getImgPublicacion) - imagenPublicacion:", [$imagenPublicacion]);

            if ($imagenPublicacion === false) {
                // Si no se encuentra la imagen, devolver un código de error 404
                http_response_code(404);
                // exit;
            }

            $this->logger->info("VALOR DE imagenPublicacion", [$imagenPublicacion]);

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

            $this->usuario->chequearSesion();

            if ($this->request->method() == 'POST') {

                $errors = [];
                
                $idUser = $this->usuario->getUserId();

                $tiposResultado = $this->model->traerTipos();

                $tiposDisponibles = $tiposResultado['tipos_alojamiento'] ?? [];

                $tiposPermitidos = is_array($tiposDisponibles) ? array_column($tiposDisponibles, 'id') : [];

                $provincia = $this->verificador->texto(
                    $this->request->post('provincia'),
                    'provincia',
                    $errors,
                    true,
                    2,
                    100
                );

                $codigo_postal = $this->verificador->texto(
                    $this->request->post('codigo_postal'),
                    'codigo_postal',
                    $errors,
                    true,
                    2,
                    20
                );

                $direccion = $this->verificador->coordenadas(
                    $this->request->post('direccion'),
                    'direccion',
                    $errors
                );

                $direccion_completa = $this->verificador->texto(
                    $this->request->post('direccion_completa'),
                    'direccion_completa',
                    $errors,
                    true,
                    3,
                    255
                );

                $precio = $this->verificador->precioEntero(
                    $this->request->post('precio'),
                    'precio',
                    $errors
                );

                $nombreAlojamiento = $this->verificador->texto(
                    $this->request->post('nombre-alojamiento'),
                    'nombre-alojamiento',
                    $errors,
                    true,
                    3,
                    255
                );

                $tipoAlojamiento = $this->verificador->opcionEntera(
                    $this->request->post('tipo-alojamiento'),
                    'tipo-alojamiento',
                    $errors,
                    $tiposPermitidos
                );

                $capacidadMaxima = $this->verificador->entero(
                    $this->request->post('capacidad-maxima'),
                    'capacidad-maxima',
                    $errors,
                    1,
                    100
                );

                $cantBanios = $this->verificador->entero(
                    $this->request->post('cant-banios'),
                    'cant-banios',
                    $errors,
                    1,
                    50
                );

                $cantidadDormitorios = $this->verificador->entero(
                    $this->request->post('cantidad-dormitorios'),
                    'cantidad-dormitorios',
                    $errors,
                    1,
                    100
                );

                $cochera = $this->verificador->checkbox(
                    $this->request->post('cochera')
                );

                $pileta = $this->verificador->checkbox(
                    $this->request->post('pileta')
                );

                $aireAcondicionado = $this->verificador->checkbox(
                    $this->request->post('aire-acondicionado')
                );

                $wifi = $this->verificador->checkbox(
                    $this->request->post('wifi')
                );

                $normasAlojamiento = $this->verificador->texto(
                    $this->request->post('normas-alojamiento'),
                    'normas-alojamiento',
                    $errors,
                    true,
                    3,
                    5000
                );

                $descripcionAlojamiento = $this->verificador->texto(
                    $this->request->post('descripcion-alojamiento'),
                    'descripcion-alojamiento',
                    $errors,
                    true,
                    3,
                    5000
                );

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
                            view('publicacion.new.view', array_merge(
                                $this->menuAndSession,
                                [
                                    'errors' => ['imagenes' => 'Debe subir al menos una imagen.']
                                ],
                                $this->model->traerTipos()
                            ));
                            
                            return;
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

    public function gestionarPublicaciones()
    {
        $this->usuario->chequearSesion();

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

    public function actualizarEstadoPublicacion()
    {

        try {
            $this->usuario->chequearSesion();

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

    public function guardarComentario(){
        
    $this->usuario->chequearSesion();

        $errores = [];

        $idPublicacion = $this->verificador->entero(
            $this->request->post('id_pub'),
            'id_pub',
            $errores,
            1
        );

        $rating = $this->verificador->entero(
            $this->request->post('rating'),
            'rating',
            $errores,
            1,
            5
        );

        $textoComentario = $this->verificador->texto(
                $this->request->post('comment'),
                'comment',
                $errores,
                true,
                1,
                2000
            );

        //Si el ID de publicación es válido, se regresa al detalle y se muestran los errores.
        
        if (!empty($errores)) {
            if ($idPublicacion !== null) {
                $this->request->setResultadoEnSesion(
                    'resultadoComentario',
                    [
                        'exito' => false,
                        'mensaje' => implode(' ', $errores)
                    ]
                );

                redirect('publicacion/ver?id_pub=' . $idPublicacion);

                return;
            }

            //No se puede volver al detalle porque el propio ID de la publicación es inválido

            http_response_code(400);

            view(
                'errors/bad-request.view',
                array_merge(
                    ['error_message' => 'El identificador de la publicación no es válido.'],
                    $this->menuAndSession
                )
            );

            return;
        }

        $publicacion = $this->model->getOne($idPublicacion);

        if (!$publicacion) {
            http_response_code(404);

            view(
                'errors/not-found.view',
                array_merge(
                    ['error_message' => 'La publicación no existe.'],
                    $this->menuAndSession
                )
            );

            return;
        }

        $comentario = [
            'id_publicacion' => $idPublicacion,

            //El autor sale de la sesión.
            'id_user' => $this->usuario->getUserId(),

            'rating' => $rating,
            'comment' => $textoComentario
        ];

        $resultadoComentario = $this->model->insertarComentario($comentario);


        if (!$resultadoComentario['exito']) {
            $resultadoComentario = [
                'exito' => false,
                'mensaje' =>
                    'No se pudo guardar el comentario. '
                    . 'Si ya calificaste esta publicación, '
                    . 'no podés volver a calificarla.'
            ];
        }

        $this->request->setResultadoEnSesion(
            'resultadoComentario',
            $resultadoComentario
        );

        redirect('publicacion/ver?id_pub=' . $idPublicacion);

    }

}
