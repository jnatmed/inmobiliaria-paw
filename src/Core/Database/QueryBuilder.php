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
        
            if (isset($params['id_usuario_reserva'])) {
                $whereClauses[] = "id_usuario_reserva = :id_usuario_reserva";
                $bindings[':id_usuario_reserva'] = $params['id_usuario_reserva'];
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

            $this->logger->debug("SQL Query: ", [$sql]);
            $this->logger->debug("ID Value: ", [$idUser]);

            return $resultado;
        } catch (PDOException $e) {
            if ($this->logger) {
                $this->logger->error("Error en selectUserAndTipo: " . $e->getMessage(), ['exception' => $e]);
            }
            return null;
        }
    }


    public function insert($table, $data, $uniqs = []) 
    { 
        try {

            $result['hay_repetidos'] = false;
            // Validación de claves únicas
            if (!empty($uniqs)) {
                $result['hay_repetidos'] = $this->validateUniqueKeys($table, $data, $uniqs);
            }
    
            if (!$result['hay_repetidos']){
                // Preparar columnas y valores
                $columnas = implode(', ', array_keys($data));
                
                $valores = ':' . implode(', :', array_keys($data));
                
                // Construir la consulta
                $query = "INSERT INTO $table ($columnas) VALUES ($valores)"; 
                $sentencia = $this->pdo->prepare($query); 
                
                // Asignar valores a los parámetros
                foreach ($data as $clave => $valor) { 
                    $sentencia->bindValue(":$clave", $valor); 
                } 
                
                // Ejecutar la consulta
                $resultado = $sentencia->execute(); 
                
                // Obtener el ID generado
                $idGenerado = $this->pdo->lastInsertId(); 
                
                // Log de resultados
                $this->logger->debug("idGenerado : {$idGenerado}, resultado : {$resultado}"); 
                
                return [$idGenerado, $resultado]; 
            }else{

                $this->logger->error('Error en la inserción: ya existe un usuario con ese email'); 
                throw new Exception('Ya existe un usuario con ese email'); 
    
            }

        } 
        catch (PDOException $e) { 
            // Manejo de errores y excepciones
            $msjError = 'Error en la inserción: ' . $e->getMessage();
            $this->logger->error($msjError); 
            return [null, $msjError]; 
        } 
        catch (Exception $e) {
            // Manejo de errores y excepciones
            $msjError = 'Error en la inserción: ' . $e->getMessage();
            $this->logger->error($msjError);
            
            // Retornar una estructura que indique que hubo un error
            return [null, $msjError]; 
        }        
    }
    
    // Método para validar claves únicas
    private function validateUniqueKeys($table, $data, $uniqs) 
    { 
        foreach ($uniqs as $uniqueKey) { 
            if (array_key_exists($uniqueKey, $data)) {
                $query = "SELECT COUNT(*) FROM $table WHERE $uniqueKey = :value"; 
                $sentencia = $this->pdo->prepare($query); 
                $sentencia->bindValue(':value', $data[$uniqueKey]); 
                $sentencia->execute(); 
                $count = $sentencia->fetchColumn();
                
                if ($count > 0) { 
                    return true; 
                } 
            }
        } 
    }
    
    public function insertMany($table, $data)
    {
        try {
            $this->logger->debug("data en capa DB: ", [$data]);

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

            $this->logger->debug("imagesTable, id_publicacion, id_imagen ", [$imagesTable, $id_publicacion, $id_imagen]);
            $sql = "
                SELECT path_imagen, nombre_imagen
                FROM $imagesTable
                WHERE id_publicacion = :id_publicacion AND id_imagen = :id_imagen;
            ";

            $stmt = $this->pdo->prepare($sql);

            $this->logger->debug("stmt: ", [$stmt]);

            $stmt->bindValue(':id_publicacion', $id_publicacion, PDO::PARAM_INT);
            $stmt->bindValue(':id_imagen', $id_imagen, PDO::PARAM_INT);

            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            // Registrar el éxito utilizando el logger

            if ($result) {
                $this->logger->debug("path_imagen recuperada: ", [$result]);
                return $result;
            } else {
                // Si no se encuentra la imagen, devuelve false
                $this->logger->debug(
                    'No se la imagen solicitada.', 
                    [
                        'publicacion_id' => $id_publicacion,
                        'imagen_id' => (int)$id_imagen
                    ]
                );
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

    public function getFirstImagePath($tableName, $idPublicacion)
    {
        $sql = "
            SELECT path_imagen, nombre_imagen
            FROM $tableName
            WHERE id_publicacion = :id_publicacion
            ORDER BY id_publicacion ASC
            LIMIT 1
        ";
    
        $stmt = $this->pdo->prepare($sql);

        $this->logger->debug("stmt: ", [$stmt]);

        $stmt->bindValue(':id_publicacion', $idPublicacion, PDO::PARAM_INT);

        
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        // Registrar el éxito utilizando el logger

        if ($result) {
            $this->logger->debug("path_imagen recuperada: ", [$result]);
            return $result;
        } else {
            // Si no se encuentra la imagen, devuelve false
            $this->logger->debug(
                'No se encontró ninguna imagen con los IDs proporcionados.', 
                ['publicacion_id' => (int) $idPublicacion]
            );
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
            $query = "
                SELECT
                    main.*,
                    MIN(img.id_imagen) AS id_imagen
                FROM {$mainTable} main

                INNER JOIN imagenes_publicacion img
                    ON main.id = img.id_publicacion

                WHERE main.estado_id = 2

                GROUP BY main.id
            ";

            $statement = $this->pdo->prepare($query);
            $statement->execute();
            return $statement->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            // Logging the error
            $this->logger->error("Error in getAll: " . $e->getMessage());
            return false;
        }
    }

    private function buildPublicationFilters($zona, $tipo, $precio, $instalaciones, $idUser, &$params) {

        $sql = '';

        /*Publicaciones: solamente muestra publicaciones aceptadas. Mis propiedades: muestra solamente publicaciones del usuario en cualquier estado*/
        if ($idUser === null) {
            $sql .= " AND main.estado_id = 2";
        } else {
            $sql .= " AND main.id_usuario = :idUser";
            $params[':idUser'] = (int) $idUser;
        }

        if ($precio !== null && $precio > 0) {
            $sql .= " AND main.precio <= :precio";
            $params[':precio'] = (int) $precio;
        }

        if (!empty($tipo)) {
            $placeholdersTipo = [];

            foreach (array_values($tipo) as $indice => $tipoId) {
                $placeholder = ':tipo_' . $indice;

                $placeholdersTipo[] = $placeholder;
                $params[$placeholder] = (int) $tipoId;
            }

            $sql .= " AND main.tipo_alojamiento_id IN ("
                . implode(', ', $placeholdersTipo)
                . ")";
        }

        $instalacionesPermitidas = [
            'cochera',
            'pileta',
            'aire_acondicionado',
            'wifi'
        ];

        foreach ($instalaciones as $instalacion) {
            if (in_array($instalacion, $instalacionesPermitidas, true)) {
                $sql .= " AND main.{$instalacion} = 1";
            }
        }

        if ($zona !== null && $zona !== '') {
            $sql .= " AND (";
            $sql .= "main.provincia LIKE :zona_provincia ";
            $sql .= "OR main.direccion LIKE :zona_direccion ";
            $sql .= "OR main.nombre_alojamiento LIKE :zona_nombre ";
            $sql .= "OR main.descripcion_alojamiento LIKE :zona_descripcion ";
            $sql .= "OR main.normas_alojamiento LIKE :zona_normas";
            $sql .= ")";

            $zonaBusqueda = '%' . $zona . '%';

            $params[':zona_provincia'] = $zonaBusqueda;
            $params[':zona_direccion'] = $zonaBusqueda;
            $params[':zona_nombre'] = $zonaBusqueda;
            $params[':zona_descripcion'] = $zonaBusqueda;
            $params[':zona_normas'] = $zonaBusqueda;
        }

        return $sql;
    }

    private function bindPublicationParams($stmt, $params) {

        foreach ($params as $param => $value) {

            $tipoParametro = is_int($value) ? PDO::PARAM_INT : PDO::PARAM_STR;

            $stmt->bindValue($param, $value, $tipoParametro);
        }
    }

    public function getFilterWithImages($mainTable, $imageTable, $mainTableKey, $foreignKey, $zona, $tipo, $precio, $instalaciones, $idUser, $limit, $offset) {

        try {

            $params = [];

            $whereSql = $this->buildPublicationFilters($zona, $tipo, $precio, $instalaciones, $idUser, $params);

            /*Primero se seleccionan y paginan solamente los IDs. Despues se recuperan las propiedades y sus imagenes. Esto evita que se corten las imagenes de un carrusel a la mitad con el LIMIT*/
            $sql = "
                SELECT
                    main.*,
                    img.id_imagen,
                    img.path_imagen,
                    img.nombre_imagen,
                    tipo.descripcion_tipo

                FROM (
                    SELECT main.id

                    FROM {$mainTable} main

                    WHERE 1 = 1
                    {$whereSql}

                    ORDER BY main.id ASC

                    LIMIT :limit
                    OFFSET :offset
                ) publicaciones_paginadas

                INNER JOIN {$mainTable} main
                    ON main.{$mainTableKey}
                        = publicaciones_paginadas.id

                LEFT JOIN {$imageTable} img
                    ON main.{$mainTableKey}
                        = img.{$foreignKey}

                LEFT JOIN tipos_alojamiento tipo
                    ON main.tipo_alojamiento_id
                        = tipo.id

                ORDER BY
                    main.id ASC,
                    img.id_imagen ASC
            ";

            $stmt = $this->pdo->prepare($sql);

            $this->bindPublicationParams($stmt, $params);

            $stmt->bindValue(':limit', (int) $limit, PDO::PARAM_INT);

            $stmt->bindValue(':offset', (int) $offset, PDO::PARAM_INT);

            $stmt->execute();

            $this->logger->debug(
                'Consulta paginada de publicaciones ejecutada.',
                [
                    'limite' => (int) $limit,
                    'offset' => (int) $offset,
                    'usuario_id' => $idUser
                ]
            );

            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            $this->logger->error('Error en getFilterWithImages: ' . $e->getMessage()
            );

            throw new Exception('Error al obtener las publicaciones paginadas');
        }
    }

    public function countFilteredPublications($mainTable, $zona, $tipo, $precio, $instalaciones, $idUser) {

        try {

            $params = [];

            $whereSql = $this->buildPublicationFilters($zona, $tipo, $precio, $instalaciones, $idUser, $params);

            $sql = "
                SELECT COUNT(*) AS total

                FROM {$mainTable} main

                WHERE 1 = 1
                {$whereSql}
            ";

            $stmt = $this->pdo->prepare($sql);

            $this->bindPublicationParams($stmt, $params);

            $stmt->execute();

            $result = $stmt->fetch(PDO::FETCH_ASSOC);

            return (int) ($result['total'] ?? 0);

        } catch (PDOException $e) {
            $this->logger->error('Error en countFilteredPublications: ' . $e->getMessage());

            throw new Exception('Error al contar las publicaciones filtradas');
        }
    }

    public function countPublicationsByContext($mainTable, $idUser = null) {

        try {

            /*Si no hay usuario, se cuenta las publicaicones aceptadas*/
            if ($idUser === null) {
                $sql = "
                    SELECT COUNT(*) AS total

                    FROM {$mainTable}

                    WHERE estado_id = 2
                ";

                $stmt = $this->pdo->prepare($sql);
            } else {
                /*Si hay usuario, se cuentan mis propiedades*/
                $sql = "
                    SELECT COUNT(*) AS total

                    FROM {$mainTable}

                    WHERE id_usuario = :idUser
                ";

                $stmt = $this->pdo->prepare($sql);

                $stmt->bindValue(':idUser', (int) $idUser, PDO::PARAM_INT);
            }

            $stmt->execute();

            $result = $stmt->fetch(PDO::FETCH_ASSOC);

            return (int) ($result['total'] ?? 0);

        } catch (PDOException $e) {

            $this->logger->error('Error en countPublicationsByContext: ' . $e->getMessage());

            throw new Exception('Error al contar el total de publicaciones');
        }
    }

    public function traerPublicacionesConEstado()
    {
        $sql = "
            SELECT
                publicaciones.*,
                estado_publicaciones.estado AS estado
            FROM publicaciones

            INNER JOIN estado_publicaciones
                ON publicaciones.estado_id =
                    estado_publicaciones.id

            WHERE publicaciones.estado_id = 1

            ORDER BY publicaciones.id ASC
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

            $this->logger->debug("parametors: ", [$mainTable, $imageTable, $mainTableKey, $foreignKey, $idUser]);
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
                    reservas_publicacion.estado_reserva,
                    publicaciones.nombre_alojamiento
                FROM
                    reservas_publicacion
                INNER JOIN
                    publicaciones ON publicaciones.id = reservas_publicacion.id_publicacion
                WHERE
                    publicaciones.id_usuario = :id_usuario
                    
            ";

            $this->logger->debug("id usuario: $id_usuario");
            $statement = $this->pdo->prepare($query);
            $statement->bindParam(':id_usuario', $id_usuario, PDO::PARAM_INT);
            $statement->execute();
            return $statement->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            $this->logger->error("Error en getReservasByUsuario: " . $e->getMessage());
            return false;
        }
    }

    public function getSolicitudesDeReservaPorUsuario(int $idUsuario){
        try {
            $query = "
                SELECT
                    reserva.*,
                    publicacion.nombre_alojamiento
                FROM
                    reservas_publicacion reserva
                INNER JOIN
                    publicaciones publicacion
                    ON publicacion.id = reserva.id_publicacion
                WHERE
                    reserva.id_usuario_reserva = :id_usuario
                ORDER BY
                    reserva.id DESC
            ";

            $statement = $this->pdo->prepare($query);

            $statement->bindValue(':id_usuario', $idUsuario, PDO::PARAM_INT);

            $statement->execute();

            return $statement->fetchAll(PDO::FETCH_ASSOC);

        } catch (PDOException $e) {

            $this->logger->error('Error en getSolicitudesDeReservaPorUsuario: ' . $e->getMessage());

            return false;
        }
    }

    public function getReservaConDatos(int $idReserva): ?array{
        
        $sql = "
            SELECT
                reserva.id,
                reserva.id_publicacion,
                reserva.id_usuario_reserva,
                reserva.fecha_inicio,
                reserva.fecha_fin,
                reserva.precio_por_noche,
                reserva.estado_reserva,
                reserva.notas,

                publicacion.id_usuario AS id_propietario,
                publicacion.nombre_alojamiento,

                propietario.email AS email_propietario,
                propietario.nombre AS nombre_propietario,

                solicitante.email AS email_solicitante,
                solicitante.nombre AS nombre_solicitante

            FROM reservas_publicacion reserva

            INNER JOIN publicaciones publicacion
                ON publicacion.id = reserva.id_publicacion

            INNER JOIN usuarios propietario
                ON propietario.id = publicacion.id_usuario

            LEFT JOIN usuarios solicitante
                ON solicitante.id = reserva.id_usuario_reserva

            WHERE reserva.id = :id_reserva

            LIMIT 1
        ";

        $statement = $this->pdo->prepare($sql);

        $statement->bindValue(':id_reserva', $idReserva, PDO::PARAM_INT);

        $statement->execute();

        $reserva = $statement->fetch(PDO::FETCH_ASSOC);

        return $reserva ?: null;
    }


    public function update($table, $data, $where)
    {
        $set = [];
        $bindings = [];
        
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

    public function deleteWhere(string $table, array $where): bool {

        if (empty($where)) {
            throw new Exception('No se puede eliminar sin condiciones.');
        }

        $whereClauses = [];
        $bindings = [];

        foreach ($where as $column => $value) {
            $parameter = ':where_' . $column;

            $whereClauses[] = "$column = $parameter";

            $bindings[$parameter] = $value;
        }

        $query = "DELETE FROM $table WHERE " . implode(' AND ', $whereClauses);

        $statement = $this->pdo->prepare($query);

        foreach ($bindings as $parameter => $value) {
            $statement->bindValue(
                $parameter,
                $value
            );
        }

        return $statement->execute();
    }

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

    public function selectMaxPrice($table, $id_user = null) {

        try {

            /*En el catalogo publico el precio max se clacula solo con publicaicones aceptadas*/
            if ($id_user === null) {
                $query = "
                    SELECT
                        COALESCE(MAX(precio), 0)
                            AS max_precio

                    FROM {$table}

                    WHERE estado_id = 2
                ";

                $stmt = $this->pdo->prepare($query);
            } else {
                /*En mis propiedades el precio maximo se calcula solo con las propiedades del usuario*/
                $query = "
                    SELECT
                        COALESCE(MAX(precio), 0)
                            AS max_precio

                    FROM {$table}

                    WHERE id_usuario = :id_user
                ";

                $stmt = $this->pdo->prepare($query);

                $stmt->bindValue(':id_user', (int) $id_user, PDO::PARAM_INT);
            }

            $stmt->execute();

            $result = $stmt->fetch(PDO::FETCH_ASSOC);

            return (int) ($result['max_precio'] ?? 0);

        } catch (PDOException $e) {

            $this->logger->error('Error al obtener el mayor valor del campo precio: ' . $e->getMessage());

            throw new Exception('Error al realizar la consulta en la base de datos');
        }
    }

    public function getReservasActivasPorPublicacion(int $idPublicacion): array {
        $sql = "
            SELECT
                fecha_inicio,
                fecha_fin,
                estado_reserva
            FROM reservas_publicacion
            WHERE id_publicacion = :id_publicacion
            AND estado_reserva IN (
                'pendiente',
                'confirmada'
            )
            ORDER BY fecha_inicio ASC
        ";

        $stmt = $this->pdo->prepare($sql);

        $stmt->bindValue(
            ':id_publicacion',
            $idPublicacion,
            PDO::PARAM_INT
        );

        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function buscarReservasEnConflicto($idPublicacion, $fechaInicio, $fechaFin) {
        
    $sql = "
            SELECT COUNT(*)
            FROM reservas_publicacion
            WHERE id_publicacion = :id_publicacion

            AND estado_reserva IN (
                'pendiente',
                'confirmada'
            )

            AND fecha_inicio <= :fecha_fin
            AND fecha_fin >= :fecha_inicio
        ";

        $stmt = $this->pdo->prepare($sql);

        $stmt->bindValue(':id_publicacion', $idPublicacion, PDO::PARAM_INT);

        $stmt->bindValue(':fecha_inicio',$fechaInicio);

        $stmt->bindValue(':fecha_fin',$fechaFin);

        $stmt->execute();

        return (int) $stmt->fetchColumn();
    }   

    public function traerComentariosYUsuarios($idPublicacion)
    {
        $sql = "
            SELECT calificaciones.*, usuarios.nombre AS nombre_usuario, usuarios.email AS email_usuario
            FROM calificaciones
            JOIN usuarios ON calificaciones.id_usuario = usuarios.id
            WHERE calificaciones.id_publicacion = :idPublicacion
        ";
    
        $statement = $this->pdo->prepare($sql);
        $statement->bindParam(':idPublicacion', $idPublicacion, PDO::PARAM_INT);
        $statement->execute();
        return $statement->fetchAll(PDO::FETCH_ASSOC);
    }    

    public function traerDestacados(){
        
        $sql = "
            SELECT c1.*
            FROM calificaciones c1

            INNER JOIN publicaciones
                ON publicaciones.id = c1.id_publicacion

            INNER JOIN (
                SELECT
                    id_publicacion,
                    MAX(calificacion) AS max_calificacion
                FROM calificaciones
                GROUP BY id_publicacion
            ) c2
                ON c1.id_publicacion = c2.id_publicacion
                AND c1.calificacion = c2.max_calificacion

            WHERE publicaciones.estado_id = 2

            ORDER BY c1.calificacion DESC

            LIMIT 5
        ";

        $statement = $this->pdo->prepare($sql);

        $statement->execute();

        return $statement->fetchAll(PDO::FETCH_ASSOC);
    }
    
}
