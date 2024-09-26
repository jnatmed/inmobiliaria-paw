<?php

namespace Paw\App\Models;

use ReflectionClass;
use ReflectionProperty;
use Paw\Core\Model;
use DateTime;

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

        // Asignar valores del array $data a los atributos de la clase utilizando los setters
        if (isset($data['id_publicacion'])) {
            $this->setIdPublicacion($data['id_publicacion']);
        } else {
            $this->erroresCollection[] = "ID de publicación no proporcionado";
            $this->exito = false;
        }

        if (isset($data['id_usuario_reserva'])) {
            $this->setIdUsuarioReserva($data['id_usuario_reserva']);
        } else {
            $this->erroresCollection[] = "ID de usuario de reserva no proporcionado";
            $this->exito = false;
        }

        if (isset($data['fecha_inicio'])) {
            $this->setFechaInicio($data['fecha_inicio']);
        } else {
            $this->erroresCollection[] = "Fecha de inicio no proporcionada";
            $this->exito = false;
        }

        if (isset($data['fecha_fin'])) {
            $this->setFechaFin($data['fecha_fin']);
        } else {
            $this->erroresCollection[] = "Fecha de fin no proporcionada";
            $this->exito = false;
        }

        $fechaInicio = new DateTime($this->getFechaInicio()); 
        $fechaFin = new DateTime($this->getFechaFin());
    
        if ($fechaInicio >= $fechaFin) {
            $this->erroresCollection[] = "La fecha de inicio debe ser menor que la fecha de fin";
            $this->exito = false;
        }

        if (isset($data['precio_por_noche'])) {
            $this->setPrecioPorNoche($data['precio_por_noche']);
        } else {
            $this->erroresCollection[] = "Precio por noche no proporcionado";
            $this->exito = false;
        }

        if (isset($data['estado_reserva'])) {
            $this->setEstadoReserva($data['estado_reserva']);
        } else {
            $this->erroresCollection[] = "Estado de reserva no proporcionado";
            $this->exito = false;
        }

        // Se podría agregar el campo 'notas' si se encuentra en los datos
        if (isset($data['notas'])) {
            $this->setNotas($data['notas']);
        }        
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
            $method = 'get' . str_replace('_', '', ucwords($key, '_'));
            $this->logger->info("method : " . $method);

            if (method_exists($this, $method) && !in_array($method, ['getExito', 'getErroresCollection'])) {
                $value = $this->$method();
                // Transformar el nombre del método en snake_case para la clave
                $transformedKey = lcfirst(str_replace(' ', '_', preg_replace('/([a-z])([A-Z])/', '$1 $2', str_replace('get', '', $method))));
                $values[$transformedKey] = $value;                
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
