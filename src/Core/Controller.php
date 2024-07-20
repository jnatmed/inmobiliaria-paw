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
    public $sesion_en_curso;

    public function __construct(){
        
        global $connection, $log;        

        $this->viewsDir = __DIR__ . '/../App/views/';
        $this->viewsDirCliente = __DIR__ . '/../App/views/cliente/';
        $this->viewsDirEmpleado = __DIR__ . '/../App/views/empleado/';

        $this->sesion_en_curso = false;

        $this->menu = [
            // [
            //     'href' => '/publicacion/buscar',
            //     'name' => 'BUSCAR'
            // ],
            [
                'href' => '/publicaciones/list',
                'name' => 'PUBLICACIONES'
            ],
            [
                'href' => '/mis_publicaciones',
                'name' => 'MIS PUBLICACIONES'   
            ],
            [
                'href' => '/mis_publicaciones/lista',
                'name' => 'MIS RESERVAS'   
            ],                      
            [
                'href' => '/usuario/mi_perfil',
                'name' => 'MI PERFIL'   
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