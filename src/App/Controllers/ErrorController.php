<?php

namespace Paw\App\Controllers;

use Paw\Core\Controller;


class ErrorController extends Controller
{
    public $usuario;
    public $menuAndSession;

    public function __construct(){
        parent::__construct();

        $this->usuario = new UsuarioController();
        $this->menu = $this->usuario->adjustMenuForSession($this->menu);

        $this->menuAndSession = $this->usuario->menuAndSession;

    }
    
    public function notFound() {
        http_response_code(404);
        $titulo = 'Page Not Found';
        $main = 'Page Not Found';

        view('errors/not-found.view', array_merge(
            ['titulo' => $titulo],
            ['main' => $main],
            $this->menuAndSession
        ));

    }
    
    public function internalError() {
        http_response_code(500);
        $titulo = 'Internal Error';
        $main = 'Internal Server Error';
        view('errors/internal_error.view', array_merge(
            ['titulo' => $titulo],
            ['main' => $main],
            $this->menuAndSession
        ));
    }

    
}