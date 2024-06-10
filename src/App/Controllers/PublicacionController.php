<?php

namespace Paw\App\Controllers;

use Paw\Core\Controller;
use Exception;
use Paw\App\Utils\Verificador;
use Paw\App\Utils\Uploader;
use Paw\App\Models\Publicacion;

class PublicacionController extends Controller
{
    public ?string $modelName = Publicacion::class;

    public function __construct()
    {
        global $config;

        parent::__construct();

    }

    public function list()
    {
        try {

            global $log;
            $publicaciones = $this->model->getAll();
            
            require $this->viewsDir . 'publicaciones.list.view.php';

        } catch (Exception $e) {
            // Manejar la excepción
            $error_message = "Error al obtener las publicaciones: " . $e->getMessage();
            $log->error("error message", [$error_message]);
            require $this->viewsDir . 'errors/not-found.view.php'; // Muestra una vista de error con el mensaje
        }
    }

    public function new()
    {
        global $request;
        global $log;
    
        $log->info("POST: ", [$request->all()]);
        $log->info("FILES: ", [$_FILES]);
    
        // Obtiene y verifica los valores del request
        $nombre = htmlspecialchars($request->get('nombre') ?? '');
        $apellido = htmlspecialchars($request->get('apellido') ?? '');
        $dni = htmlspecialchars($request->get('dni') ?? '');
        $telefono = htmlspecialchars($request->get('telefono') ?? '');
        $email = htmlspecialchars($request->get('email') ?? '');
        $provincia = htmlspecialchars($request->get('provincia') ?? '');
        $localidad = htmlspecialchars($request->get('localidad') ?? '');
        $direccion = htmlspecialchars($request->get('direccion') ?? '');
        $nombreAlojamiento = htmlspecialchars($request->get('nombre-alojamiento') ?? '');
        $tipoAlojamiento = htmlspecialchars($request->get('tipo-alojamiento') ?? '');
        $capacidadMaxima = htmlspecialchars($request->get('capacidad-maxima') ?? '');
        $cantBanios = htmlspecialchars($request->get('cant-banios') ?? '');
        $cantidadDormitorios = htmlspecialchars($request->get('cantidad-dormitorios') ?? '');
        $cochera = $request->get('cochera') ? 1 : 0;
        $pileta = $request->get('pileta') ? 1 : 0;
        $aireAcondicionado = $request->get('aire-acondicionado') ? 1 : 0;

        $wifi = $request->get('wifi') ? 1 : 0;
        $normasAlojamiento = htmlspecialchars($request->get('normas-alojamiento') ?? '');
        $descripcionAlojamiento = htmlspecialchars($request->get('descripcion-alojamiento') ?? '');
    
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
            $this->index();
        } else {
            // Manejar error
            $log->error("Error al crear la publicación");
        }
    }

    

}


