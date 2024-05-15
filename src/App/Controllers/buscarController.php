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
        require $this->viewsDir . 'buscar-propiedad.view.php';        
    }
}