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
        $titulo = "INMOBILIARIA-PAW | HOME";
        require $this->viewsDir . 'index.view.php';
    }

    /**esto despues pasa a usuarioController */
    public function login()
    {
        $titulo = "INMOBILIARIA-PAW | LOGIN";
        require $this->viewsDir . 'login.view.php';
    }

    public function register()
    {
        $titulo = "INMOBILIARIA-PAW | REGISTRO";
        require $this->viewsDir . 'register.view.php';
    }


}


