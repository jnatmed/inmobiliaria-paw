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

    
    public function getOne($id_publicacion)
    {
        try {
            $result = $this->queryBuilder->getOneWithImages(
                $this->table, // Nombre de la tabla principal (publicaciones)
                'imagenes_publicacion', // Nombre de la tabla de imágenes
                'id', // Nombre de la clave primaria en la tabla principal
                'id_publicacion', // Nombre de la clave foránea que relaciona las dos tablas
                $id_publicacion // El identificador de la publicación que queremos obtener
            );
    
            // Verificar si se encontraron resultados
            if (empty($result)) {
                return null; // O lanzar una excepción personalizada si prefieres
            }
    
            // Estructurar el resultado
            $publicacion = [];
            foreach ($result as $row) {
                if (empty($publicacion)) {
                    foreach ($row as $key => $value) {
                        $publicacion[$key] = $value;
                    }
                    $publicacion['imagenes'] = [];
                }
                if (!is_null($row['id_imagen'])) {
                    $publicacion['imagenes'][] = [
                        'id_imagen' => $row['id_imagen'],
                        'path_imagen' => $row['path_imagen'],
                        'nombre_imagen' => $row['nombre_imagen']
                    ];
                }
            }
    
            return $publicacion;
        } catch (PDOException $e) {
            global $log;
            $log->error("Error al obtener la publicación: " . $e->getMessage());
            return false; // O lanzar una excepción personalizada si prefieres
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


    public function getImg($idPublicacion, $idImagen) {
        

        try {
            $pathImagen = $this->queryBuilder->getImagePath('imagenes_publicacion', $idPublicacion, $idImagen);
            
            // Si no se encuentra la imagen, devuelve false
            if (!$pathImagen) {
                return false;
            }
    
            // Devuelve el path de la imagen encontrada
            return $pathImagen;
        } catch (Exception $e) {
            // Manejo de la excepción
            // Registra el error utilizando el logger
            $this->logger->error("Error al obtener la imagen de la publicación: " . $e->getMessage());
            return false; // Devolver false en caso de error
        }
    }
    
    

    public function getAll()
    {
        try {
            $result = $this->queryBuilder->getAllWithImages(
                $this->table, // Nombre de la tabla principal (publicaciones)
                'imagenes_publicacion', // Nombre de la tabla de imágenes
                'id', // Nombre de la clave primaria en la tabla principal
                'id_publicacion' // Nombre de la clave foránea que relaciona las dos tablas
            );
    
            // Estructurar los resultados
            $publicaciones = [];
            foreach ($result as $row) {
                $id = $row['id'];
                if (!isset($publicaciones[$id])) {
                    $publicaciones[$id] = [];
                    foreach ($row as $key => $value) {
                        $publicaciones[$id][$key] = $value;
                    }
                    $publicaciones[$id]['imagenes'] = [];
                }
                if (!is_null($row['id_imagen'])) {
                    $publicaciones[$id]['imagenes'][] = [
                        'id_imagen' => $row['id_imagen'],
                        'path_imagen' => $row['path_imagen'],
                        'nombre_imagen' => $row['nombre_imagen']
                    ];
                }
            }
    
            return array_values($publicaciones);
        } catch (PDOException $e) {
            global $log;
            $log->error("Error al obtener las publicaciones: " . $e->getMessage());
            return false; // O lanzar una excepción personalizada si prefieres
        }
    }
    
    public function getAllbyUser($idUser)
    {
        try {
            $result = $this->queryBuilder->getAllWithImagesByUser(
                $this->table, // Nombre de la tabla principal (publicaciones)
                'imagenes_publicacion', // Nombre de la tabla de imágenes
                'id', // Nombre de la clave primaria en la tabla principal
                'id_publicacion', // Nombre de la clave foránea que relaciona las dos tablas
                $idUser // ID del usuario
            );
    
            // Estructurar los resultados
            $publicaciones = [];
            foreach ($result as $row) {
                $id = $row['id'];
                if (!isset($publicaciones[$id])) {
                    $publicaciones[$id] = [];
                    foreach ($row as $key => $value) {
                        $publicaciones[$id][$key] = $value;
                    }
                    $publicaciones[$id]['imagenes'] = [];
                }
                if (!is_null($row['id_imagen'])) {
                    $publicaciones[$id]['imagenes'][] = [
                        'id_imagen' => $row['id_imagen'],
                        'path_imagen' => $row['path_imagen'],
                        'nombre_imagen' => $row['nombre_imagen']
                    ];
                }
            }
    
            return array_values($publicaciones);
        } catch (PDOException $e) {
            global $log;
            $log->error("Error al obtener las publicaciones: " . $e->getMessage());
            return false; // O lanzar una excepción personalizada si prefieres
        }
    } 


    public function insertMany($table, $insertData)
    {
        try {
            // Preparar los datos para la inserción

            global $log;

            $log->info("data en capa model: ",$insertData);

            // Realizar la inserción utilizando el método de queryBuilder
            $result = $this->queryBuilder->insertMany($table, $insertData);
    
            // Retornar el resultado de la operación de inserción
            return $result;
        } catch (PDOException $e) {
            // Manejo de la excepción
            $this->logger->error("Error al insertar múltiples imágenes: " . $e->getMessage());
            return false;
        }
    }    
}