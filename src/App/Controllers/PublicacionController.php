<?php

namespace Paw\App\Controllers;

use Paw\Core\Controller;
use Paw\App\Utils\Uploader;
use Paw\App\Utils\Utils;
use Paw\App\Models\PublicacionCollection;
use Paw\App\Utils\Verificador;


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

    public function __construct()
    {
        global $config;

        $this->uploader = new Uploader;
        $this->verificador = new Verificador;
        $this->usuario = new UsuarioController();
        $this->utils = new  Utils();

        parent::__construct();

        $this->usuario = new UsuarioController();
        $this->menu = $this->usuario->adjustMenuForSession($this->menu);

    }
    
    public function buscar()
    {
        // Verificar si se recibe una consulta de búsqueda
        if (isset($_GET['ubicacion'])) {
            $location = $_GET['ubicacion'];

            // Devolver el resultado como JSON
            header('Content-Type: application/json');
            echo json_encode(['location' => $location]);
        } else {
            // Establecer la cookie para la vista inicial, si no está presente
            if (!isset($_COOKIE['location'])) {
                setcookie('location', 'No hay búsquedas previas', time() + (7 * 24 * 60 * 60), "/");
            }            
            // Si no hay búsqueda, cargar la vista.
            require $this->viewsDir . 'buscar-propiedad.view.php';
        }
    }

    public function list()
    {
        try {
            $publicaciones = $this->model->getAll();

            // var_dump($publicaciones);
            $this->logger->info("Publicaciones: ", [$publicaciones]);

            require $this->viewsDir . 'publicaciones.list.view.php';
        } catch (PDOException $e) {
            $error_message = "Error de base de datos al obtener las publicaciones: " . $e->getMessage();
            $this->logger->error($error_message);
            require $this->viewsDir . 'errors/not-found.view.php';
        }
    }

    public function verPublicacion()
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
    
        // Obtener el ID de la publicación de la solicitud
        $idPub = htmlspecialchars($this->request->get('id_pub'));
    
        // Obtener los datos de la publicación y sus imágenes
        $publicacion = $this->model->getOne($idPub);
    
        // Verificar si se encontró la publicación
        if (!$publicacion) {
            $resultado = [
                "success" => false,
                "message" => "Publicación no encontrada."
            ];
            $this->logger->info("Publicación no encontrada: ID $idPub.");
            require $this->viewsDir . 'error.view.php'; // Redirigir a una página de error
            return;
        }
    

        $fullUrl = $this->request->fullUrl();
        // Mostrar la vista de detalles de la publicación
        require $this->viewsDir . 'publicacion.details.view.php';
    }

    public function listaPublicacionesPropietarrio()
    {
        

        try {
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

            // Obtener el ID del usuario desde la sesión
            $this->logger->info("sesion: ", [$_SESSION]);

            $idUser = $this->usuario->getUserId();

            $publicaciones = $this->model->getAllbyUser($idUser);

            // var_dump($publicaciones);
            $this->logger->info("Publicaciones: ", [$publicaciones]);

            require $this->viewsDir . 'publicaciones.list.view.php';
        } catch (PDOException $e) {
            $error_message = "Error de base de datos al obtener las publicaciones: " . $e->getMessage();
            $this->logger->error($error_message);
            require $this->viewsDir . 'errors/not-found.view.php';
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
        
    
        $this->logger->info("request:", [$_REQUEST]);
        $this->logger->info("server:", [$_SERVER]);
        try {
            if ($this->request->method() == 'POST') {
    
                if (!$this->usuario->isUserLoggedIn()) {
                    $resultado = [
                        "success" => false,
                        "message" => "Debe iniciar sesión para ver el pedido."
                    ];
                    $this->logger->info("Intento de ver pedido sin sesión iniciada.");
                    header('Location: /iniciar_sesion');
                    exit();
                }
                
                // Verificar si $_POST está vacío
                if (empty($_POST)) {
                    throw new Exception("Error: La solicitud POST está vacía.");
                }
    
                // Obtener el ID del usuario desde la sesión
                $this->logger->info("sesion: ", [$_SESSION]);
    
                $idUser = $this->usuario->getUserId();
                $this->logger->info("idUser: ", [$idUser]);
    
                $this->logger->info("POST: ", [$this->request->all()]);
                $this->logger->info("FILES: ", [$_FILES]);
    
                // Obtiene y verifica los valores del request
                $nombre = htmlspecialchars($this->request->get('nombre') ?? '');
                $apellido = htmlspecialchars($this->request->get('apellido') ?? '');
                $dni = htmlspecialchars($this->request->get('dni') ?? '');
                $telefono = htmlspecialchars($this->request->get('telefono') ?? '');
                $email = htmlspecialchars($this->request->get('email') ?? '');
                $provincia = htmlspecialchars($this->request->get('provincia') ?? '');
                $localidad = htmlspecialchars($this->request->get('localidad') ?? '');
                
                // Verificar y decodificar el JSON de la dirección
                $direccion = htmlspecialchars($this->request->get('direccion') ?? '');
                if (is_null($direccion) || $direccion === '') {
                    throw new Exception("Error: JSON proporcionado es nulo o vacío.");
                } else {
                    // Convertir entidades HTML a caracteres normales
                    $direccion = html_entity_decode($direccion);
    
                    // Decodificar la cadena JSON
                    $coordenadas = json_decode($direccion, true);
                    if ($coordenadas === null) {
                        throw new Exception("Error al decodificar la dirección: " . json_last_error_msg());
                    }
                }
    
                $latitud = $coordenadas['lat'] ?? null;
                $longitud = $coordenadas['lng'] ?? null;
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
    
                // Si la inserción fue exitosa, procede con el manejo de las imágenes
                if ($idPublicacionGenerado) {
                    // Verificar si $_FILES está vacío
                    if (empty($_FILES)) {
                        throw new Exception("Error: No se han subido archivos.");
                    }
    
                    $imagenesPublicacion = [];
    
                    foreach ($_FILES as $file) {
                        if ($file["name"] != "") {
                            $result = $this->uploader->uploadFile($file);
    
                            $this->logger->info("resultado insercion capa CONTROLLER  ", [$result]);
                            if ($result['exito'] === Uploader::UPLOAD_COMPLETED) {
                                $imagenesPublicacion[] = [
                                    'id_publicacion' => $idPublicacionGenerado,
                                    'path_imagen' => $result['nombre_imagen'],
                                    'nombre_imagen' => $result['nombre_imagen'],
                                    'id_usuario' => $idUser
                                ];
                            } else {
                                throw new Exception("Error al subir una imagen: " . $result['description']);
                            }
                        }
                    }
    
                    $this->logger->info("imagenesPublicacion: ", [$imagenesPublicacion]);
                    // Inserta todas las imágenes en la base de datos en una única operación
                    $this->model->insertMany('imagenes_publicacion', $imagenesPublicacion);
                    header('Location: /mis_publicaciones');
                    exit();
                } else {
                    $this->logger->error("Publicacion no generada: ", [$idPublicacionGenerado]);
                }
            } else {
                require $this->viewsDir . 'publicacion.new.view.php';
            }
        } catch (Exception $e) {
            // Manejar la excepción
            $this->logger->error("Error en el proceso: " . $e->getMessage());
            echo "Ocurrió un error: " . $e->getMessage();
        }
    }
    

    

}
