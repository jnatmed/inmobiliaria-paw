<?php

namespace Paw\Core;

use Paw\Core\Model; 
use Paw\Core\Database\QueryBuilder;
use Paw\Core\Traits\Loggable;

class Controller 
{
    public string $viewsDir;
    public string $viewsDirCliente;

    public string $viewsDirEmpleado;

    public array $menu;
    public array $menuEmpleado;
    public array $menuPerfil;
    public ?string $modelName = null;   
    protected $model;
    use Loggable;
    public $qb;

    public function __construct(){
        
        global $connection, $log;        

        $this->viewsDir = __DIR__ . '/../App/views/';
        $this->viewsDirCliente = __DIR__ . '/../App/views/cliente/';
        $this->viewsDirEmpleado = __DIR__ . '/../App/views/empleado/';

        $this->menu = [
            [
                'href' => '/nuestro_menu',
                'name' => 'MENU'
            ],
            [
                'href' => '/promociones',
                'name' => 'PROMOS'
            ],
            [
                'href' => '/sucursales',
                'name' => 'SUCURSALES'
            ],
            [
                'href' => '/noticias',
                'name' => 'NOTICIAS'
            ],
            [
                'href' => '/pedir',
                'name' => 'PEDIR'
            ],
            [
                'href' => '/reservar_cliente',
                'name' => 'RESERVAR'
            ]
        ];

        $this->menuEmpleado = [
            [
                'href' => '/gestion_lista_mesas',
                'name' => 'GESTION MESAS'
            ],
            [
                'href' => '/gestion_mesa',
                'name' => 'GESTION MESA'
            ],
            [
                'href' => '/pedidos_entrantes',
                'name' => 'PEDIDOS ENTRANTES'
            ],
            [
                'href' => '/plato/new',
                'name' => 'NUEVO PLATO'
            ]
        ];

        $this->menuPerfil = [
            [
                'href' => '/perfil_usuario',
                'name' => 'Mi Perfil'
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