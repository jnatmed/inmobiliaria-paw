<?php

namespace Paw\Core\Database;

use PDO;
use Monolog\Logger;
use Exception;
use PDOException;

class QueryBuilder
{
    public PDO $pdo;
    public Logger $logger;
    private $lastQuery;

    public function __construct(PDO $pdo, Logger $logger = null)
    {
        $this->pdo = $pdo;
        $this->logger = $logger;
    }

    public function select($table, $params = [])
    {
        try {
            $whereClauses = [];
            $bindings = [];

            // Construir las cláusulas WHERE y los parámetros de enlace
            if (isset($params['id'])) {
                $whereClauses[] = "id = :id";
                $bindings[':id'] = $params['id'];
            }

            if (isset($params['token'])) {
                $whereClauses[] = "token = :token";
                $bindings[':token'] = $params['token'];
            }

            if (isset($params['email'])) {
                $whereClauses[] = "email = :email";
                $bindings[':email'] = $params['email'];
            }

            if (isset($params['id_publicacion'])) {
                $whereClauses[] = "id_publicacion = :id_publicacion";
                $bindings[':id_publicacion'] = $params['id_publicacion'];
            }

            if (isset($params['id_imagen'])) {
                $whereClauses[] = "id_imagen = :id_imagen";
                $bindings[':id_imagen'] = $params['id_imagen'];
            }

            // Verificar si hay cláusulas WHERE
            $where = '';
            if (!empty($whereClauses)) {
                $where = 'WHERE ' . implode(' AND ', $whereClauses);
            }

            // Construir la consulta
            $query = "SELECT * FROM {$table} {$where}";

            // Preparar la sentencia
            $sentencia = $this->pdo->prepare($query);

            // Enlazar los valores de los parámetros
            foreach ($bindings as $key => $value) {
                $sentencia->bindValue($key, $value);
            }

            // Establecer el modo de obtención y ejecutar la consulta
            $sentencia->setFetchMode(PDO::FETCH_ASSOC);
            $sentencia->execute();
            return $sentencia->fetchAll();
        } catch (PDOException $e) {
            // Manejar la excepción de la base de datos
            $this->logger->error('Database error: ' . $e->getMessage());
            throw new Exception('Error al realizar la consulta en la base de datos');
        } catch (Exception $e) {
            // Manejar otras excepciones
            $this->logger->error('General error: ' . $e->getMessage());
            throw new Exception('Ocurrió un error inesperado');
        }
    }



    public function countRows($table)
    {
        try {
            $query = "SELECT COUNT(*) as total FROM {$table}";
            $statement = $this->pdo->prepare($query);
            $statement->execute();
            $result = $statement->fetch(PDO::FETCH_ASSOC);
            return $result['total'];
        } catch (PDOException $e) {
            $this->logger->error('Database error: ' . $e->getMessage());
            throw new Exception('Error al realizar la consulta en la base de datos');
        } catch (Exception $e) {
            $this->logger->error('General error: ' . $e->getMessage());
            throw new Exception('Ocurrió un error inesperado');
        }
    }

    public function selectUserAndTipo($idUser)
    {
        try {
            $sql = "
                SELECT usuarios.*, tipos_usuarios.tipo as tipo
                FROM usuarios
                JOIN tipos_usuarios ON usuarios.tipo_usuario_id = tipos_usuarios.id
                WHERE usuarios.id = :id
            ";

            $stmt = $this->pdo->prepare($sql);
            $stmt->bindValue(':id', $idUser, PDO::PARAM_INT);

            $stmt->setFetchMode(PDO::FETCH_ASSOC);
            $stmt->execute();

            $resultado = $stmt->fetchAll();

            $this->logger->info("SQL Query: ", [$sql]);
            $this->logger->info("ID Value: ", [$idUser]);
            $this->logger->info("resultado selectUserAndTipo: ", [$resultado]);

            return $resultado;
        } catch (PDOException $e) {
            if ($this->logger) {
                $this->logger->error("Error en selectUserAndTipo: " . $e->getMessage(), ['exception' => $e]);
            }
            return null;
        }
    }


    public function insert($table, $data)
    {
        try {
            $this->logger->info("Data -> " , [$data]);
            $columnas = implode(', ', array_keys($data));
            $this->logger->info("Columnas -> " , [$columnas]);
            $valores = ':' . implode(', :', array_keys($data));
            $this->logger->info("Valores -> " , [$valores]);
            $query = "INSERT INTO $table ($columnas) VALUES ($valores)";
            $sentencia = $this->pdo->prepare($query);

            // Asignar valores a los parámetros
            foreach ($data as $clave => $valor) {
                $sentencia->bindValue(":$clave", $valor);
            }

            $resultado = $sentencia->execute();

            $idGenerado = $this->pdo->lastInsertId();

            $this->logger->debug("idGenerado : {$idGenerado}, resultado : {$resultado}");

            return [$idGenerado, $resultado];
        } catch (PDOException $e) {
            // Manejo de errores y excepciones
            $this->logger->error('Error en la inserción: ' . $e->getMessage());
            return [null, false];
        }
    }

    public function insertMany($table, $data)
    {
        try {
            $this->logger->info("data en capa DB: ", [$data]);

            // Preparar las columnas
            $columns = ['id_publicacion', 'path_imagen', 'nombre_imagen', 'id_usuario'];
            $placeholders = rtrim(str_repeat('(?, ?, ?, ?), ', count($data)), ', '); // Genera los marcadores de posición

            // Preparar la consulta de inserción
            $query = "INSERT INTO $table (" . implode(', ', $columns) . ") VALUES $placeholders";

            // Preparar los valores para la inserción
            $values = [];
            foreach ($data as $imagen) {
                $values[] = $imagen['id_publicacion'];
                $values[] = $imagen['path_imagen'];
                $values[] = $imagen['nombre_imagen'];
                $values[] = $imagen['id_usuario'];
            }

            // Preparar la sentencia
            $statement = $this->pdo->prepare($query);

            // Ejecutar la consulta con los valores preparados
            $statement->execute($values);

            return true;
        } catch (PDOException $e) {
            // Manejo de la excepción
            $this->logger->error("Error al insertar múltiples imágenes: " . $e->getMessage());
            return false;
        }
    }

    public function getImagePath($imagesTable, $id_publicacion, $id_imagen)
    {
        try {

            $this->logger->info("imagesTable, id_publicacion, id_imagen ", [$imagesTable, $id_publicacion, $id_imagen]);
            $sql = "
                SELECT path_imagen, nombre_imagen
                FROM $imagesTable
                WHERE id_publicacion = :id_publicacion AND id_imagen = :id_imagen;
            ";

            $stmt = $this->pdo->prepare($sql);

            $this->logger->info("stmt: ", [$stmt]);

            $stmt->bindValue(':id_publicacion', $id_publicacion, PDO::PARAM_INT);
            $stmt->bindValue(':id_imagen', $id_imagen, PDO::PARAM_INT);

            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            // Registrar el éxito utilizando el logger

            if ($result) {
                $this->logger->info("path_imagen recuperada: ", [$result]);
                return $result;
            } else {
                // Si no se encuentra la imagen, devuelve false
                $this->logger->info("No se encontró ninguna imagen con los IDs proporcionados.", [$result]);
                return false;
            }
        } catch (PDOException $e) {
            // Manejo de la excepción
            // Registrar el error utilizando el logger
            $this->logger->error("Error al ejecutar la consulta: " . $e->getMessage());
            // Puedes lanzar una excepción personalizada aquí o manejarla de otra manera según tus necesidades
            return false;
        }
    }

    public function getAllWithImages($mainTable, $imageTable, $mainTableKey, $foreignKey)
    {
        try {
            $query = "
                SELECT 
                    main.*, 
                    img.id_imagen, 
                    img.path_imagen, 
                    img.nombre_imagen 
                FROM 
                    {$mainTable} main
                LEFT JOIN 
                    {$imageTable} img
                ON 
                    main.{$mainTableKey} = img.{$foreignKey}
            ";


            $statement = $this->pdo->prepare($query);
            $statement->execute();
            return $statement->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            // Logging the error
            $this->logger->error("Error in getAllWithImages: " . $e->getMessage());
            return false;
        }
    }

    public function getAll($mainTable)
    {
        try {
            $query = "SELECT main.* FROM {$mainTable} main ";

            $statement = $this->pdo->prepare($query);
            $statement->execute();
            return $statement->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            // Logging the error
            $this->logger->error("Error in getAll: " . $e->getMessage());
            return false;
        }
    }

    public function getFilterWithImages($mainTable, $imageTable, $mainTableKey, $foreignKey, $zona, $tipo, $allowedTipos, $precio, $instalaciones, $idUser)
    {
        try {
            $sql = "
            SELECT 
                main.*, 
                img.id_imagen, 
                img.path_imagen, 
                img.nombre_imagen,
                tipo.descripcion_tipo
            FROM 
                {$mainTable} main
            LEFT JOIN 
                {$imageTable} img ON main.{$mainTableKey} = img.{$foreignKey}
            LEFT JOIN 
                tipos_alojamiento tipo ON main.tipo_alojamiento_id = tipo.id
            WHERE 1=1
            ";
            $params = [];
    
            if ($precio) {
                $sql .= " AND main.precio <= :precio";
                $params[':precio'] = $precio;
            }
    
            if ($idUser) {
                $sql .= " AND main.id_usuario = :idUser";
                $params[':idUser'] = $idUser;
            }
    
            if (!empty($tipo)) {
                // $allowedTipos = ['casa', 'departamento', 'quinta'];
                $condiciones = [];
                // $this->logger->debug("Tipos Querybuilder:", [$allowedTipos]);
                foreach ($tipo as $t) {
                    // $this->logger->debug("Tipo a buscar: $t");
                    if (in_array($t, $allowedTipos)) {
                        $condiciones[] = "tipo.id = '{$t}'";
                        // $this->logger->debug("agrego a filtro: $t");
                    }
                }
                if (!empty($condiciones)) {
                    $sql .= " AND (" . implode(' OR ', $condiciones) . ")";
                }
            }
    
            if (!empty($instalaciones)) {
                $allowedInstalaciones = ['cochera', 'pileta', 'aire_acondicionado', 'wifi'];
                foreach ($instalaciones as $instalacion) {
                    if (in_array($instalacion, $allowedInstalaciones)) {
                        $sql .= " AND main.{$instalacion} = 1";
                    }
                }
            }
    
            if ($zona) {
                $sql .= " AND (main.provincia LIKE :zona OR main.direccion LIKE :zona)";
                $params[':zona'] = '%' . $zona . '%';
            }
    
            $this->logger->debug("SQL: " , [$sql]);
    
            $stmt = $this->pdo->prepare($sql);
            foreach ($params as $param => $value) {
                $stmt->bindValue($param, $value);
            }
            $stmt->execute();
    
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            $this->logger->error("Error in getFilterWithImages: " . $e->getMessage());
            return false;
        }
    }

    public function traerPublicacionesConEstado()
    {
        $sql = "
            SELECT publicaciones.*, estado_publicaciones.estado as estado
            FROM publicaciones
            JOIN estado_publicaciones ON publicaciones.estado_id = estado_publicaciones.id
        ";

        $statement = $this->pdo->prepare($sql);
        $statement->execute();
        return $statement->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getOneWithImages($mainTable, $imageTable, $mainTableKey, $foreignKey, $id)
    {
        try {
            $query = "
                SELECT 
                    main.*, 
                    img.id_imagen, 
                    img.path_imagen, 
                    img.nombre_imagen,
                    usr.nombre AS nombre,
                    usr.apellido AS apellido,
                    usr.email AS email,
                    usr.telefono AS telefono
                FROM 
                    {$mainTable} main
                LEFT JOIN 
                    {$imageTable} img
                ON 
                    main.{$mainTableKey} = img.{$foreignKey}
                LEFT JOIN
                    usuarios usr
                ON
                    main.id_usuario = usr.id
                WHERE 
                    main.{$mainTableKey} = :id
            ";

            $statement = $this->pdo->prepare($query);
            $statement->bindParam(':id', $id, PDO::PARAM_INT);
            $statement->execute();
            return $statement->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            // Logging the error
            $this->logger->error("Error in getOneWithImages: " . $e->getMessage());
            return false;
        }
    }
    public function getAllWithImagesByUser($mainTable, $imageTable, $mainTableKey, $foreignKey, $idUser)
    {
        try {

            $this->logger->info("parametors: ", [$mainTable, $imageTable, $mainTableKey, $foreignKey, $idUser]);
            $query = "
                SELECT 
                    main.*, 
                    img.id_imagen, 
                    img.path_imagen, 
                    img.nombre_imagen 
                FROM 
                    {$mainTable} main
                LEFT JOIN 
                    {$imageTable} img
                ON 
                    main.{$mainTableKey} = img.{$foreignKey}
                WHERE
                    main.id_usuario = :id_usuario
            ";

            $statement = $this->pdo->prepare($query);
            $statement->bindParam(':id_usuario', $idUser, PDO::PARAM_INT);
            $statement->execute();
            return $statement->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            // Logging the error
            $this->logger->error("Error in getAllWithImagesByUser: " . $e->getMessage());
            return false;
        }
    }

    public function getLastQuery()
    {
        return $this->lastQuery;
    }

    public function getReservasByUsuario($id_usuario)
    {
        try {
            $query = "
                SELECT
                    reservas_publicacion.id AS id_reserva,
                    reservas_publicacion.id_usuario_reserva AS id_usuario_reserva,
                    reservas_publicacion.fecha_inicio AS desde,
                    reservas_publicacion.fecha_fin AS hasta,
                    reservas_publicacion.notas AS nota,
                    reservas_publicacion.id_publicacion AS id_pub,
                    reservas_publicacion.estado_reserva
                FROM
                    reservas_publicacion
                INNER JOIN
                    publicaciones ON publicaciones.id = reservas_publicacion.id_publicacion
                WHERE
                    publicaciones.id_usuario = :id_usuario
                    AND reservas_publicacion.estado_reserva IN ('pendiente', 'confirmada')
            ";

            $this->logger->info("id usuario: $id_usuario");
            $statement = $this->pdo->prepare($query);
            $statement->bindParam(':id_usuario', $id_usuario, PDO::PARAM_INT);
            $statement->execute();
            return $statement->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            $this->logger->error("Error en getReservasByUsuario: " . $e->getMessage());
            return false;
        }
    }

    public function update($table, $data, $where)
    {
        $set = [];
        $bindings = [];

        $this->logger->info("data, where", [$data, $where]);
        
        foreach ($data as $column => $value) {
            $set[] = "$column = :$column";
            $bindings[":$column"] = $value;
        }

        $whereClauses = [];
        foreach ($where as $column => $value) {
            $whereClauses[] = "$column = :where_$column";
            $bindings[":where_$column"] = $value;
        }

        $setString = implode(', ', $set);
        $whereString = implode(' AND ', $whereClauses);

        $query = "UPDATE $table SET $setString WHERE $whereString";

        $statement = $this->pdo->prepare($query);
        foreach ($bindings as $param => $value) {
            $statement->bindValue($param, $value);
        }

        $statement->execute();
    }

    public function delete() {}

    public function traerTipos()
    {
        try {
            $sql = "SELECT * FROM tipos_alojamiento"; // Asegúrate de que el nombre de la tabla sea correcto.
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            $this->logger->error("Error en traerTipos: " . $e->getMessage());
            return false;
        }
    }    

    public function selectMaxPrice($table)
    {
        try {
            // Consulta para obtener el mayor valor del campo `precio`
            $query = "SELECT MAX(precio) AS max_precio FROM {$table}";
            
            // Preparar la sentencia
            $stmt = $this->pdo->prepare($query);
            
            // Ejecutar la consulta
            $stmt->execute();
            
            // Obtener el resultado
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            
            // Registrar la consulta y el resultado
            $this->logger->info("Consulta SQL: ", [$query]);
            $this->logger->info("Resultado selectMaxPrice: ", [$result]);
            
            // Retornar el valor máximo
            return $result['max_precio'];
        } catch (PDOException $e) {
            // Manejo de errores
            $this->logger->error('Error al obtener el mayor valor del campo precio: ' . $e->getMessage());
            throw new Exception('Error al realizar la consulta en la base de datos');
        }
    }
}
