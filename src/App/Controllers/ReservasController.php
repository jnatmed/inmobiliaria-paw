<?php

namespace Paw\App\Controllers;

use Paw\App\Utils\Verificador;
use Paw\App\Utils\Uploader;

use Paw\Core\Controller;

class ReservasController extends Controller
{
    public Uploader $uploader;
    public Verificador $verificador;
    public $usuario;

    public function __construct()
    {
        parent::__construct();

        $this->uploader = new Uploader;
        $this->usuario = new UsuarioController();
        $this->verificador = new Verificador;

        $this->usuario = new UsuarioController();
        $this->menu = $this->usuario->adjustMenuForSession($this->menu);

    }

    public function reservas(){

        /**
         * envio los periodos que van a estar reservados y los muestro en el front y manipulo javascript
         */
        $periodos = [
            ["17/05/2024", "27/05/2024"],
            ["17/06/2024", "27/06/2024"]
        ];        

        $periodos_json = json_encode($periodos, JSON_UNESCAPED_SLASHES);

        // echo $periodos_json;
        // exit;

        require $this->viewsDir . 'reservas-propiedad.view.php';

    }

    public function obtenerIntervalosReserva()
    {
        // Obtener los intervalos de reserva (simulado)
        $periodos = [
            ["17/05/2024", "27/05/2024"],
            ["17/06/2024", "27/06/2024"]
        ];

        // Devolver los intervalos de reserva como JSON
        echo json_encode($periodos);
    }
}