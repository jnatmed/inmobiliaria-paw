<?php

namespace Paw\App\Controllers;

use Paw\App\Utils\Verificador;
use Paw\App\Utils\Uploader;

use Paw\Core\Controller;

class BuscarController extends Controller
{
    public Uploader $uploader;
    public Verificador $verificador;

    public function __construct()
    {
        parent::__construct();

        $this->uploader = new Uploader;

        $this->verificador = new Verificador;
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
}