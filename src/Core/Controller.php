<?php

namespace Paw\Core;

use Paw\Core\Model; 
use Paw\Core\Database\QueryBuilder;
use Paw\Core\Traits\Loggable;

class Controller 
{
    use Loggable;
    
    public string $viewsDir;
    public string $viewsDirCliente;

    public string $viewsDirEmpleado;

    public array $menu;
    public array $menuEmpleado;
    public array $menuPerfil;
    public ?string $modelName = null;   
    protected $model;
    public $qb;
    public $request;

    public function __construct(){
        
        global $connection, $log;        

        $this->viewsDir = __DIR__ . '/../App/views/';
        $this->viewsDirCliente = __DIR__ . '/../App/views/cliente/';
        $this->viewsDirEmpleado = __DIR__ . '/../App/views/empleado/';

        $this->sesion_en_curso = false;

        $this->menu = [
            [
                'href' => '/buscar',
                'name' => 'BUSCAR'
            ],
            [
                'href' => '/publicaciones/list',
                'name' => 'PUBLICACIONES'
            ],
            [
                'href' => '/mis_publicaciones',
                'name' => 'MIS PUBLICACIONES'   
            ]        
        ];

        $this->menuEmpleado = [
            [
                'href' => '/gestion_lista_mesas',
                'name' => 'GESTION ALQUILERES'
            ],

        ];

        $this->menuPerfil = [
            [
                'href' => '/buscar',
                'name' => 'Buscar'
            ],
            [
                'href' => '/iniciar_sesion',
                'name' => 'Iniciar Sesion'
            ],
            [
                'href' => '/registrar_usuario',
                'name' => 'Registrar Usuario Sesion'
            ],
            [
                'href' => '/cerrar_sesion',
                'name' => 'Cerrar Sesion'
            ]
        ];     
        
        $this->qb = new QueryBuilder($connection, $log);
        $this->request = new Request();

        if(!is_null($this->modelName)){
            $model = new $this->modelName;
            $model->setQueryBuilder($this->qb);
            $this->setModel($model);
        }

        
    }

    public function setModel(Model $model)
    {
        $this->model = $model;
    }

    public function getQb(){
        return $this->qb;
    }

}