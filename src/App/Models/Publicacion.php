<?php 


namespace Paw\App\Models;

use Paw\Core\Model;

use Exception;
use Paw\Core\Exceptions\InvalidValueFormatException;

use Paw\App\Utils\Uploader;
use Paw\App\Utils\Verificador;
use PDOException;

class Publicacion extends Model 
{
    public $table = 'publicaciones';

    public $fields = [
        'id' => null,
        'nombre' => null,
    ];

    public static $fields_requires = [
        'nombre',     
    ];

    public function __construct($datosPublicacion=[])
    {   
        if (!is_null($datosPublicacion) && is_array($datosPublicacion)) {
            try {
                $verificador = new Verificador();
                if ($verificador->array_has_empty_values(array_values($datosPublicacion))) {
                    throw new Exception("Faltan datos para crear el objeto Publicacion ");
                }
                foreach ($datosPublicacion as $key => $value) {
                    if(!key_exists($key, $this->fields)){
                        throw new Exception("No existe le key: $key");
                    }
                    $this->fields[$key] = $value;
                }
    
            } catch (Exception $e) {
                echo "Error al crear el objeto Publicacion: " . $e->getMessage();
            }
        } else {
            echo "Error: Los datos de la publicacion no son válidos.";
        }

    }


    public function create($data)
    {
        try {
            return $this->queryBuilder->insert($this->table, $data);
        } catch (PDOException $e) {
            global $log;
            $log->error("Error al crear la publicación: " . $e->getMessage());
            return false; // O lanzar una excepción personalizada si prefieres
        }
    }

    public function getAll()
    {
        try {
            return $this->queryBuilder->select($this->table);
        } catch (PDOException $e) {
            global $log;
            $log->error("Error al obtener las publicaciones: " . $e->getMessage());
            return false; // O lanzar una excepción personalizada si prefieres
        }
    }

}