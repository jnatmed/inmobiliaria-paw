<?php

namespace Paw\App\Controllers;

use Paw\App\Utils\Verificador;
use Paw\App\Utils\Uploader;

use Paw\Core\Controller;

class PageController extends Controller
{

    public Uploader $uploader;
    public Verificador $verificador;

    public function __construct()
    {
        parent::__construct();

        $this->uploader = new Uploader;

        $this->verificador = new Verificador;
    }

    public function index()
    {
        $titulo = "PAW POWER | HOME";
        require $this->viewsDir . 'index.view.php';
    }


}


