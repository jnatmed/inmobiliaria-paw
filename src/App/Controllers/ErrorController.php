<?php

namespace Paw\App\Controllers;

use Paw\Core\Controller;


class ErrorController extends Controller
{
    public $usuario;

    public function __construct(){
        parent::__construct();

        $this->usuario = new UsuarioController();
        $this->menu = $this->usuario->adjustMenuForSession($this->menu);

    }
    
    public function notFound() {
        http_response_code(404);
        $titulo = 'Page Not Found';
        $main = 'Page Not Found';

        view('errors/not-found.view', [
            'titulo' => $titulo,
            'main' => $main,
            'menu' => $this->menu
        ]);

    }
    
    public function internalError() {
        http_response_code(500);
        $titulo = 'Internal Error';
        $main = 'Internal Server Error';
        require $this->viewsDir. 'internal-error.view.php';
    }

    
}