<?php

namespace Paw\App\Controllers;

use Paw\App\Utils\Verificador;
use Paw\App\Utils\Uploader;

use Paw\Core\Controller;

class BuscarController extends Controller
{
    public Uploader $uploader;
    public Verificador $verificador;
    private $apiKey;
    public $usuario;

    public function __construct()
    {
        global $config;

        $this->usuario = new UsuarioController();

        parent::__construct();

        $this->uploader = new Uploader;

        $this->verificador = new Verificador;
        // Cargar la API key desde una variable de entorno
        $this->apiKey = $config->get('OPENCAGEDATA_API_KEY');
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

    public function geocode()
    {
        global $request, $log;
    
        $location = $request->get('q');
    
        $apiKey = $this->apiKey;
        $apiUrl = "https://api.opencagedata.com/geocode/v1/json?q=" . urlencode($location) . "&key=" . $apiKey;
    
        $log->info("url: ", [$apiUrl]);

        $options = [
            "http" => [
                "header" => "Cache-Control: no-cache, no-store, must-revalidate\r\n" .
                            "Pragma: no-cache\r\n" .
                            "Expires: 0\r\n"
            ]
        ];
        $context = stream_context_create($options);
    
        $response = file_get_contents($apiUrl, false, $context);
    
        if ($response === FALSE) {
            $errorResponse = ['error' => 'Error fetching data from OpenCage'];
            header("Content-Type: application/json");
            echo json_encode($errorResponse);
            return;
        }
    
        $data = json_decode($response, true);
    
        // $log->info("data: ", [$data]);
    
        header("Content-Type: application/json");
        echo json_encode($data);
    }

}