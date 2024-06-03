<?php

namespace Paw\App\Controllers;

use Paw\Core\Controller;

class PublicacionController extends Controller
{
    

    public function __construct()
    {
        global $config;

        parent::__construct();

    }

    public function new()
    {
        global $request;

        require $this->viewsDir . 'publicacion.new.view.php';
    }


}
