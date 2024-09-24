<?php

namespace Paw\App\Models;

use ReflectionClass;
use ReflectionProperty;
use Paw\Core\Model;

class Reserva extends Model
{
    private $idPublicacion;
    private $idUsuarioReserva;
    private $fechaInicio;
    private $fechaFin;
    private $precioPorNoche;
    private $estadoReserva;
    private $notas;
    private $erroresCollection;
    private $exito = true;

    public function __construct(array $data, $logger)
    {
        parent::setLogger($logger);

        // Inicializar erroresCollection y éxito en caso de errores durante la construcción
        $this->erroresCollection = [];
        $this->exito = true;
    }

    // Método para obtener el estado del constructor
    public function getEstadoConstructor()
    {
        if ($this->exito) {
            return [
                'exito' => true,
                'description' => "Datos cargados correctamente.",
            ];
        } else {
            return [
                'exito' => false,
                'description' => "Se encontraron errores al cargar los datos.",
                'errores' => $this->erroresCollection,
            ];
        }
    }

    public function getAll()
    {
        $values = [];
        $reflect = new ReflectionClass($this);
        $properties = $reflect->getProperties(ReflectionProperty::IS_PRIVATE);

        foreach ($properties as $property) {
            $property->setAccessible(true);
            $key = $property->getName();
            
            // Genera el nombre del método getter
            $method = 'get'.str_replace('_', '', ucwords($key, '_'));
            $this->logger->info("method : ". $method);

            if (method_exists($this, $method)) {
                $value = $this->$method();
                $values[$key] = $value;
                $this->logger->info("existe el metodo -> " . $method);
            } else {
                if (!in_array($key, ['exito', 'erroresCollection'])) {
                    $values[$key] = $property->getValue($this);
                }
                $this->logger->info("NO existe el metodo -> " . $method);
            }
        }

        return $values;
    }

    // Getters y Setters para los atributos

    public function getIdPublicacion()
    {
        return $this->idPublicacion;
    }

    public function setIdPublicacion($idPublicacion)
    {
        $this->idPublicacion = $idPublicacion;
    }

    public function getIdUsuarioReserva()
    {
        return $this->idUsuarioReserva;
    }

    public function setIdUsuarioReserva($idUsuarioReserva)
    {
        $this->idUsuarioReserva = $idUsuarioReserva;
    }

    public function getFechaInicio()
    {
        return $this->fechaInicio;
    }

    public function setFechaInicio($fechaInicio)
    {
        $this->fechaInicio = $fechaInicio;
    }

    public function getFechaFin()
    {
        return $this->fechaFin;
    }

    public function setFechaFin($fechaFin)
    {
        $this->fechaFin = $fechaFin;
    }

    public function getPrecioPorNoche()
    {
        return $this->precioPorNoche;
    }

    public function setPrecioPorNoche($precioPorNoche)
    {
        $this->precioPorNoche = $precioPorNoche;
    }

    public function getEstadoReserva()
    {
        return $this->estadoReserva;
    }

    public function setEstadoReserva($estadoReserva)
    {
        $this->estadoReserva = $estadoReserva;
    }

    public function getNotas()
    {
        return $this->notas;
    }

    public function setNotas($notas)
    {
        $this->notas = $notas;
    }

    public function getErroresCollection()
    {
        return $this->erroresCollection;
    }

    public function setErroresCollection($erroresCollection)
    {
        $this->erroresCollection = $erroresCollection;
    }

    public function isExito()
    {
        return $this->exito;
    }

    public function setExito($exito)
    {
        $this->exito = $exito;
    }
}
