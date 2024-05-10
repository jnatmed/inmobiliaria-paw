<?php

namespace Paw\App\Controllers;

use Paw\Core\Controller;


class ErrorController extends Controller
{

    public function __construct(){
        parent::__construct();
        
        $this->viewsDir = __DIR__ . '/../views/errors/';
    }
    
    public function notFound() {
        http_response_code(404);
        $titulo = 'Page Not Found';
        $main = 'Page Not Found';
        require $this->viewsDir. 'not-found.view.php';
    }
    
    public function internalError() {
        http_response_code(500);
        $titulo = 'Internal Error';
        $main = 'Internal Server Error';
        require $this->viewsDir. 'internal-error.view.php';
    }

    
}