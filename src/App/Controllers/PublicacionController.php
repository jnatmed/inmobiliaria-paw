<?php

namespace Paw\App\Controllers;

use Paw\Core\Controller;
use Paw\App\Utils\Uploader;
use Paw\App\Utils\Utils;
use Paw\App\Models\Publicacion;
use Paw\App\Utils\Verificador;


use PDOException;
use Throwable;
use Exception;

class PublicacionController extends Controller
{
    public ?string $modelName = Publicacion::class;
    public $usuario;
    public Verificador $verificador;
    public Uploader $uploader;
    public $utils;

    public function __construct()
    {
        global $config;

        $this->uploader = new Uploader;
        $this->verificador = new Verificador;
        $this->usuario = new UsuarioController();
        $this->utils = new  Utils();

        parent::__construct();
    }

    public function list()
    {
        global $log;
        try {
            $publicaciones = $this->model->getAll();

            // var_dump($publicaciones);
            $log->info("Publicaciones: ", [$publicaciones]);

            require $this->viewsDir . 'publicaciones.list.view.php';
        } catch (PDOException $e) {
            $error_message = "Error de base de datos al obtener las publicaciones: " . $e->getMessage();
            $log->error($error_message);
            require $this->viewsDir . 'errors/not-found.view.php';
        }
    }

    public function listaPublicacionesPropietarrio()
    {
        global $log;

        try {
            // Verificar si hay sesión iniciada
            if (!$this->usuario->isUserLoggedIn()) {
                $resultado = [
                    "success" => false,
                    "message" => "Debe iniciar sesión para ver el pedido."
                ];
                $log->info("Intento de ver pedido sin sesión iniciada.");
                require $this->viewsDir . 'login.view.php'; // Redirigir a la página de inicio de sesión
                return;
            }

            // Obtener el ID del usuario desde la sesión
            $log->info("sesion: ", [$_SESSION]);

            $idUser = $this->usuario->getUserId();

            $publicaciones = $this->model->getAllbyUser($idUser);

            // var_dump($publicaciones);
            $log->info("Publicaciones: ", [$publicaciones]);

            require $this->viewsDir . 'publicaciones.list.view.php';
        } catch (PDOException $e) {
            $error_message = "Error de base de datos al obtener las publicaciones: " . $e->getMessage();
            $log->error($error_message);
            require $this->viewsDir . 'errors/not-found.view.php';
        }
    }

    public function getImgPublicacion()
    {
        global $log;

        $idPublicacion = $this->request->get('id_pub');
        $idImagen = $this->request->get('id_img');

        try {

            // Obtener la imagen de la publicación
            $imagenPublicacion = $this->model->getImg($idPublicacion, $idImagen);

            $log->info("(method- getImgPublicacion) - imagenPublicacion:", [$imagenPublicacion]);

            if ($imagenPublicacion === false) {
                // Si no se encuentra la imagen, devolver un código de error 404
                http_response_code(404);
                // exit;
            }


            $mime_type = Uploader::getMimeType($imagenPublicacion['path_imagen']);

            $log->info("(method- getImgPublicacion) - mime_type: ", [$mime_type]);

            $log->info("imagenPublicacion: ", [Uploader::UPLOADDIRECTORY . $imagenPublicacion['path_imagen']]);


            // Establecer el tipo MIME de la imagen y enviarla al cliente
            header("Content-type: " . $mime_type);
            echo file_get_contents(Uploader::UPLOADDIRECTORY . $imagenPublicacion['path_imagen']);
        } catch (Exception $e) {
            // Manejo de la excepción
            // Registrar el error utilizando el logger
            $log->error("Error al obtener la imagen de la publicación: " . $e->getMessage());

            $mime_type = Uploader::getMimeType('image-not-found.png');
            header("Content-type: " . $mime_type);
            echo file_get_contents(Uploader::UPLOADDIRECTORY . 'image-not-found.png');
        }
    }


    public function new()
    {

        global $log;


        if ($this->request->method() == 'POST') {

            if (!$this->usuario->isUserLoggedIn()) {
                $resultado = [
                    "success" => false,
                    "message" => "Debe iniciar sesión para ver el pedido."
                ];
                $log->info("Intento de ver pedido sin sesión iniciada.");
                header('Location: /iniciar_sesion');
                exit();
            }

            // Obtener el ID del usuario desde la sesión
            $log->info("sesion: ", [$_SESSION]);

            $idUser = $this->usuario->getUserId();
            $log->info("idUser: ", [$idUser]);

            $log->info("POST: ", [$this->request->all()]);
            $log->info("FILES: ", [$_FILES]);

            // Obtiene y verifica los valores del request
            $nombre = htmlspecialchars($this->request->get('nombre') ?? '');
            $apellido = htmlspecialchars($this->request->get('apellido') ?? '');
            $dni = htmlspecialchars($this->request->get('dni') ?? '');
            $telefono = htmlspecialchars($this->request->get('telefono') ?? '');
            $email = htmlspecialchars($this->request->get('email') ?? '');
            $provincia = htmlspecialchars($this->request->get('provincia') ?? '');
            $localidad = htmlspecialchars($this->request->get('localidad') ?? '');
            /**
             * $direccion viene en formato json y tengo q descomponerlo en latitud y longitud 
             * */
            $direccion = htmlspecialchars($this->request->get('direccion') ?? ''); // 
            $coordenadas = json_decode($direccion, true);

            $coordenadas = $this->utils->obtenerCoordenadas($direccion);
            $latitud = $coordenadas['lat'];
            $longitud = $coordenadas['lng'];
            $precio = htmlspecialchars($this->request->get('precio'));

            $nombreAlojamiento = htmlspecialchars($this->request->get('nombre-alojamiento') ?? '');
            $tipoAlojamiento = htmlspecialchars($this->request->get('tipo-alojamiento') ?? '');
            $capacidadMaxima = htmlspecialchars($this->request->get('capacidad-maxima') ?? '');
            $cantBanios = htmlspecialchars($this->request->get('cant-banios') ?? '');
            $cantidadDormitorios = htmlspecialchars($this->request->get('cantidad-dormitorios') ?? '');
            $cochera = $this->request->get('cochera') ? 1 : 0;
            $pileta = $this->request->get('pileta') ? 1 : 0;
            $aireAcondicionado = $this->request->get('aire-acondicionado') ? 1 : 0;

            $wifi = $this->request->get('wifi') ? 1 : 0;
            $normasAlojamiento = htmlspecialchars($this->request->get('normas-alojamiento') ?? '');
            $descripcionAlojamiento = htmlspecialchars($this->request->get('descripcion-alojamiento') ?? '');

            // Preparar el array de datos para la inserción
            $publicacion = [
                'nombre' => $nombre,
                'apellido' => $apellido,
                'dni' => $dni,
                'telefono' => $telefono,
                'email' => $email,
                'provincia' => $provincia,
                'localidad' => $localidad,
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
                'id_usuario' => $idUser
            ];

            // Manejar la inserción de datos
            list($idPublicacionGenerado, $resultado) = $this->model->create($publicacion);

            /**
             * tengo :
             *     - idPublicacionGenerado
             *     - idUser
             *     - luego por cada imagen recibida en el formulario
             *          Obtengo el path_imagen y nombre_imagen
             *      - si todo ok entonces insertar en la tabla 
             *      
             */
            // Si la inserción fue exitosa, procede con el manejo de las imágenes
            if ($idPublicacionGenerado) {
                $imagenesPublicacion = [];

                foreach ($_FILES as $file) {
                    if ($file["name"] != "") {

                        $result = $this->uploader->uploadFile($file);

                        $log->info("resultado insercion capa CONTROLLER  ", [$result]);
                        if ($result['exito'] === Uploader::UPLOAD_COMPLETED) {

                            $imagenesPublicacion[] = [
                                'id_publicacion' => $idPublicacionGenerado,
                                'path_imagen' => $result['nombre_imagen'],
                                'nombre_imagen' => $result['nombre_imagen'],
                                'id_usuario' => $idUser
                            ];

                            // No es necesario hacer la inserción aquí, acumula los datos para luego realizar una inserción masiva
                        } else {
                            // Si ocurre un error al subir una imagen, lanza una excepción
                            throw new Exception("Error al subir una imagen: " . $result['description']);
                        }
                    }
                }

                $log->info("imagenesPublicacion: ", [$imagenesPublicacion]);
                // Inserta todas las imágenes en la base de datos en una única operación
                $this->model->insertMany('imagenes_publicacion', $imagenesPublicacion);
                header('Location: /mis_publicaciones');
                exit();
            } else {
                $log->error("Publicacion no generada: ", [$idPublicacionGenerado]);
            }
        } else {
            require $this->viewsDir . 'publicacion.new.view.php';
        }
    }
}
