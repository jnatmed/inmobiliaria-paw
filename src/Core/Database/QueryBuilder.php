<?php 

namespace Paw\Core\Database;

use PDO;
use Monolog\Logger;

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

    public function select($table, $params = []) {
        $where = "1 = 1";
        if(isset($params['id'])){
            $where = "id = :id ";
        }
        
        $query = "select * from {$table} where {$where}";    

        // $this->logger->info($query);

        $sentencia = $this->pdo->prepare($query);
        
        if(isset($params['id'])){
            
            $sentencia->bindValue(":id", $params['id']);
        }

        $sentencia->setFetchMode(PDO::FETCH_ASSOC);
        $sentencia->execute();
        return $sentencia->fetchAll();
    }

    public function insert($table, $data)
    {
        $columnas = implode(', ', array_keys($data));
        $valores = ':' . implode(', :', array_keys($data));
        $query = "INSERT INTO $table ($columnas) VALUES ($valores)";
        $sentencia = $this->pdo->prepare($query);
    
        // Asignar valores a los parámetros
        foreach ($data as $clave => $valor) {
            $sentencia->bindValue(":$clave", $valor);
        }
    
        $resultado = $sentencia->execute();
        
        $idGenerado = $this->pdo->lastInsertId();

        return [$idGenerado, $resultado];
    }

    public function getLastQuery()
    {
        return $this->lastQuery;
    }


    public function update()
    {

    }

    public function delete()
    {

    }
}