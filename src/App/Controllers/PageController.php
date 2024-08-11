<?php

namespace Paw\App\Controllers;

use Paw\App\Utils\Verificador;
use Paw\App\Utils\Uploader;

use Paw\Core\Controller;
// use Paw\App\Controllers\UsuarioController;

class PageController extends Controller
{

    public Uploader $uploader;
    public Verificador $verificador;
    public $usuario;
    public $menuAndSession;

    public function __construct()
    {
        global $log;
        parent::__construct();

        $this->uploader = new Uploader;

        $this->verificador = new Verificador;
        
        $this->usuario = new UsuarioController();
        $this->menu = $this->usuario->adjustMenuForSession($this->menu);
        $this->menuAndSession = [
            'isUserLoggedIn' => $this->usuario->isUserLoggedIn(),
            'menu' => $this->menu,
            'urlPublicacion' => $this->request->fullUrl(),
            'id_usuario' => $this->usuario->getUserId()
        ];

    }

    public function index()
    {
        $datos = ['titulo' => "PAWPERTIES | HOME"];
        
        view('home.view', array_merge($this->menuAndSession, $datos));
    }

    public function mostrarMapa(){
        $datos = ['titulo' => "PAWPERTIES | MAPA"];
        view("mapa-general.view", array_merge($this->menuAndSession, $datos));
    }

}


