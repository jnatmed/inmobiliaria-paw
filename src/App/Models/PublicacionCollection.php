<?php 


namespace Paw\App\Models;

use Paw\Core\Model;

use Exception;
use PDOException;

use Paw\App\Utils\Verificador;
use Paw\App\Models\Publicacion;

class PublicacionCollection extends Model 
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


    public function create(Publicacion $Publicacion)
    {
        try {
            $data = $Publicacion->getAll();
            $this->logger->info("data : ", [$data]);

            list($idPublicacionGenerado, $resultado) = $this->queryBuilder->insert($this->table, $data);

            $this->logger->info("Info Publicacion (Method - create): " , [$idPublicacionGenerado, $resultado]);

            return [$idPublicacionGenerado, $resultado];
        } catch (PDOException $e) {
            global $log;
            $log->error("Error al crear la publicación: " . $e->getMessage());
            return false; // O lanzar una excepción personalizada si prefieres
        }
    }

    public function getImg($idPublicacion, $idImagen) {
        

        try {
            $pathImagen = $this->queryBuilder->getImagePath('imagenes_publicacion', $idPublicacion, $idImagen);
            
            $this->logger->debug("pathImagen : ", [$pathImagen]);
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

    public function getEstadoById($id_estado)
    {
        try {
            $params = ['id' => $id_estado];
            $result = $this->queryBuilder->select('estado_publicaciones', $params);

            if (!empty($result)) {
                return $result[0]['estado'];
            } else {
                return null; // O puedes lanzar una excepción si prefieres
            }
        } catch (Exception $e) {
            // Manejo de errores
            // Puedes registrar el error utilizando un logger
            return false;
        }
    }    

    public function getAllWithImages()
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

    public function getAll()
    {
        try {
            $result = $this->queryBuilder->getAll($this->table);
            // Estructurar los resultados
            $publicaciones = [];
            foreach ($result as $row) {
                $id = $row['id'];
                $id_imagen = $row['id_imagen'];
                if (!isset($publicaciones[$id])) {
                    $publicaciones[$id] = [];
                    foreach ($row as $key => $value) {
                        $publicaciones[$id][$key] = $value;
                    }
                    $publicaciones[$id]["url_pub"] = "/publicacion/ver?id_pub={$id}";
                    $publicaciones[$id]["img_principal"] = "/publicacion?id_pub={$id}&id_img={$id_imagen}";
                }
            }
            return array_values($publicaciones);

        } catch (PDOException $e) {
            global $log;
            $log->error("Error al obtener las publicaciones: " . $e->getMessage());
            return false; 
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

    public function getPublicacionMayorPrecio($id_user = null)
    {
        return $this->queryBuilder->selectMaxPrice('publicaciones', $id_user);
    }


    public function getPublicacionesTotales() 
    {
        return $this->queryBuilder->countRows('publicaciones');
    }

    public function getAllFilter($zona, $tipo, $precio, $instalaciones, $idUser)
    {
        try {


            $tipos_alojamiento = $this->queryBuilder->traerTipos();
     
            // Extraer solo los IDs usando array_map
            $allowedTipos = array_map(function($tipo) {
                return $tipo['id'];
            }, $tipos_alojamiento);            

            $result = $this->queryBuilder->getFilterWithImages(
                $this->table, // Nombre de la tabla principal (publicaciones)
                'imagenes_publicacion', // Nombre de la tabla de imágenes
                'id', // Nombre de la clave primaria en la tabla principal
                'id_publicacion', // Nombre de la clave foránea que relaciona las dos tablas
                $zona, $tipo, $allowedTipos, $precio, $instalaciones, // Filtros
                $idUser
            );
    
            // Estructurar los resultados
            $publicaciones = [];
            if (!empty($result)) {
                foreach ($result as $row) {
                    $id = $row['id'];
                    if (!isset($publicaciones[$id])) {
                        $publicaciones[$id] = [];
                        foreach ($row as $key => $value) {
                            $publicaciones[$id][$key] = $value;
                        }
                        $publicaciones[$id]['imagenes'] = [];
                        $estadoPublicacion = $this->getEstadoById($publicaciones[$id]['estado_id']);
                        $publicaciones[$id]['estado_publicacion'] = $estadoPublicacion;
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
            } else {
                return [];
            }
    
            
        } catch (PDOException $e) {
            global $log;
            $log->error("Error al obtener las publicaciones: " . $e->getMessage());
            return false; // O lanzar una excepción personalizada si prefieres
        }
    }

    

    public function insertMany($table, $imagenesCollection)
    {
        try {
            // Preparar los datos para la inserción

            global $log;

            $imagenesData = array_map(function($imagen){
                return $imagen->load();
            }, $imagenesCollection);

            $log->info("data en capa model: ",$imagenesData);

            // Realizar la inserción utilizando el método de queryBuilder
            $result = $this->queryBuilder->insertMany($table, $imagenesData);
    
            // Retornar el resultado de la operación de inserción
            return $result;
        } catch (PDOException $e) {
            // Manejo de la excepción
            $this->logger->error("Error al insertar múltiples imágenes: " . $e->getMessage());
            return false;
        }
    }    

    public function getReservas($id_publicacion)
    {
        // Utilizamos el QueryBuilder para obtener las reservas
        $result = $this->queryBuilder->select('reservas_publicacion', ['id_publicacion' => $id_publicacion]);

        // Formatear los resultados según el formato requerido
        $reservas = [];
        foreach ($result as $row) {
            $fecha_inicio = (new \DateTime($row['fecha_inicio']))->format('d/m/Y');
            $fecha_fin = (new \DateTime($row['fecha_fin']))->format('d/m/Y');
            $reservas[] = [$fecha_inicio, $fecha_fin];
        }

        return $reservas;
    }

    public function traerPublicaciones()
    {
        try {
            // Realizamos la consulta para obtener todas las publicaciones sin filtro de usuario
            $result = $this->queryBuilder->traerPublicacionesConEstado();

            if ($result) {
                return [
                    'exito' => true,
                    'publicaciones' => $result,
                ];
            } else {
                return [
                    'exito' => false,
                    'publicaciones' => [],
                ];
            }
        } catch (Exception $e) {
            // Manejo de la excepción
            return [
                'exito' => false,
                'publicaciones' => [],
                'error' => $e->getMessage(),
            ];
        }
    }



    public function actualizarEstadoPublicacion($idPublicacion, $accion)
    {
        try {

            if($accion === 'aceptar'){
                $nuevoEstado = 2;
            }

            if($accion === 'rechazar'){
                $nuevoEstado = 3;
            }

            $this->queryBuilder->update(
                'publicaciones',
                ['estado_id' => $nuevoEstado],
                ['id' => $idPublicacion]
            );
        } catch (Exception $e) {
            throw new Exception("Error al actualizar la publicacion: " . $e->getMessage());
        }        
    }
 
    
    public function traerTipos()
    {
        try{
            return $result = [
                'exito' => true,
                'tipos_alojamiento' => $this->queryBuilder->traerTipos()
            ];
        }catch(Exception $e){
            return $result = [
                'exito' => false,
                'message' => "Error al traer Tipos: " . $e->getMessage()
            ];
        }
    }

}