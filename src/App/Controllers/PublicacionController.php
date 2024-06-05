<?php

namespace Paw\App\Controllers;

use Paw\Core\Controller;
use Exception;
use Paw\App\Utils\Verificador;
use Paw\App\Utils\Uploader;
use Paw\App\Models\Publicacion;

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
        global $router;
        global $log;
        $titulo = 'PAW POWER | NUEVA PUBLICACION';


        if ($request->method() == 'GET') {
            require $this->viewsDir . 'publicacion.new.view.php';
        } elseif ($request->method() == 'POST') {

            try {

                $newPublicacion = new Publicacion(
                    [
                        'nombre' => $request->get('nombre'),
                    ]
                );

                $newPublicacion->setQueryBuilder($this->getQb());

                $uploader = new Uploader;

                $resultado = $uploader->verificar_imagen($_FILES, $newPublicacion);

                if (!$resultado['exito']) {
                    throw new Exception("Error al subir la imagen del plato: " . $resultado['description']);
                }

                if (!$this->model->insert($newPublicacion)) {
                    throw new Exception("Faltan datos para crear el objeto Publicacion.");
                } else {
                    $platos = $this->model->getAll();
                    
                }
            } catch (Exception $e) {

                $verificador_campos = [
                    'exito' => false,
                    'description' => "Error al crear el objeto Publicacion: " . $e->getMessage()
                ];
                require $this->viewsDir . 'publicacion.new.view.php';
            }
        }
    }


}
