<?php

namespace Paw\App\Controllers;

use Paw\Core\Controller;
use Paw\App\Utils\Uploader;
use Paw\App\Models\Publicacion;

use PDOException;
use Throwable;
use Exception;

class PublicacionController extends Controller
{
    public ?string $modelName = Publicacion::class;
    public $usuario;

    public function __construct()
    {
        global $config;

        $this->usuario = new UsuarioController();
        
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
            $log->info("sesion: ",[$_SESSION]);

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
            
            if ($imagenPublicacion === false) {
                // Si no se encuentra la imagen, devolver un código de error 404
                http_response_code(404);
                // exit;
            }
    
            $mime_type = Uploader::getMimeType($imagenPublicacion['path_imagen']);
    
            $log->info("imagenPublicacion: " , [$imagenPublicacion]);
    
            // Establecer el tipo MIME de la imagen y enviarla al cliente
            header("Content-type: " . $mime_type);
            echo file_get_contents( Uploader::UPLOADDIRECTORY.$imagenPublicacion['path_imagen']);
        } catch (Exception $e) {
            // Manejo de la excepción
            // Registrar el error utilizando el logger
            $log->error("Error al obtener la imagen de la publicación: " . $e->getMessage());
            
            // Mostrar una vista de error
            require $this->viewsDir . 'errors/not-found.view.php';
        }
    }
    

    public function new()
    {
        
        global $log;
    
        
        if($this->request->method() == 'POST'){
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
            $direccion = htmlspecialchars($this->request->get('direccion') ?? '');
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
            $data = [
                'nombre' => $nombre,
                'apellido' => $apellido,
                'dni' => $dni,
                'telefono' => $telefono,
                'email' => $email,
                'provincia' => $provincia,
                'localidad' => $localidad,
                'direccion' => $direccion,
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
                'descripcion_alojamiento' => $descripcionAlojamiento
            ];
        
            // Manejar la inserción de datos
            $publicaciones = $this->model->create($data);
    
            
            if ($publicaciones) {
                // Redirigir a la vista de lista de publicaciones
                
            } else {
                // Manejar error
                $log->error("Error al crear la publicación");
            }
        }else{
            require $this->viewsDir . 'publicacion.new.view.php';
        }


    }

    

}


