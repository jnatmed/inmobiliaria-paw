<?php 

namespace Paw\App\Models;

use Paw\Core\Exceptions\JsonVacioException; 
use Paw\Core\Exceptions\DireccionFailException; 

use ReflectionClass;
use ReflectionProperty;


use Exception;

use Paw\Core\Model;

class Publicacion extends Model
{
    private $provincia;
    private $codigo_postal;
    private $direccion;
    private $latitud;
    private $longitud;
    private $precio;
    private $nombre_alojamiento;
    private $tipo_alojamiento_id;
    private $capacidad_maxima;
    private $cant_banios;
    private $cantidad_dormitorios;
    private $cochera;
    private $pileta;
    private $aire_acondicionado;
    private $wifi;
    private $normas_alojamiento;
    private $descripcion_alojamiento;
    private $id_usuario;
    private $estado_id;

    private $erroresCollection;
    private $exito = true;

    public function __construct(array $data, $logger)
    {
        parent::setLogger($logger);

        // Inicializar erroresCollection y éxito en caso de errores durante la construcción
        $this->erroresCollection = [];
        $this->exito = true;

        // Establecer los valores iniciales utilizando los métodos correspondientes
        $this->setProvincia($data['provincia'] ?? null);
        $this->setCodigoPostal($data['codigo_postal'] ?? null);

        if (is_null($data['direccion']) || $data['direccion'] === '') {
            // Almacenar el error y marcar el estado como fallo
            $this->erroresCollection[] = "Error: JSON proporcionado es nulo o vacío.";
            $this->exito = false;
        } else {
            $direccion = $data['direccion'];
            try {
                // Decodificar las entidades HTML
                $direccionDecoded = html_entity_decode($direccion);
            } catch (\Throwable $th) {
                // Almacenar el error y marcar el estado como fallo
                $this->erroresCollection[] = "Error en html_entity_decode: " . $th->getMessage();
                $this->exito = false;
            }

            // Intentar decodificar JSON solo si `direccionDecoded` se ha asignado correctamente
            if (isset($direccionDecoded)) {
                $direccionArray = json_decode($direccionDecoded, true);
                if ($direccionArray === null) {
                    $this->erroresCollection[] = "Error al decodificar la dirección: " . json_last_error_msg() . " || " . $data['direccion'];
                    $this->exito = false;
                } elseif (isset($direccionArray['lat']) && isset($direccionArray['lng'])) {
                    $this->setLatitud($direccionArray['lat'] ?? null);
                    $this->setLongitud($direccionArray['lng'] ?? null);
                    $this->logger->info("Latitud y longitud seteados..", [$this->getLongitud(), $this->getLatitud()]);
                } else {
                    $this->erroresCollection[] = "Error al decodificar JSON o faltan claves 'lat' o 'lng'.";
                    $this->exito = false;
                }
            }
        }

        // Continuar con la asignación de otros campos
        $this->setPrecio($data['precio'] ?? null);
        $this->setDireccion($data['direccion_completa'] ?? null);
        $this->setNombreAlojamiento($data['nombre_alojamiento'] ?? null);
        $this->setTipoAlojamientoId($data['tipo_alojamiento_id'] ?? null);
        $this->setCapacidadMaxima($data['capacidad_maxima'] ?? null);
        $this->setCantBanios($data['cant_banios'] ?? null);
        $this->setCantidadDormitorios($data['cantidad_dormitorios'] ?? null);
        $this->setCochera($data['cochera'] ?? 0); // Valores booleanos por defecto a 0
        $this->setPileta($data['pileta'] ?? 0);
        $this->setAireAcondicionado($data['aire_acondicionado'] ?? 0);
        $this->setWifi($data['wifi'] ?? 0);
        $this->setNormasAlojamiento($data['normas_alojamiento'] ?? null);
        $this->setDescripcionAlojamiento($data['descripcion_alojamiento'] ?? null);
        $this->setIdUsuario($data['id_usuario'] ?? null);
        $this->setEstadoId($data['estado_id'] ?? 1); // Valor por defecto 1
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

    // Getters y Setters...

    public function getProvincia() { return $this->provincia; }
    public function setProvincia($provincia) { $this->provincia = $provincia; }

    public function getCodigoPostal() { return $this->codigo_postal; }
    public function setCodigoPostal($codigo_postal) { $this->codigo_postal = $codigo_postal; }

    public function getDireccion() { return $this->direccion; }
    public function setDireccion($direccion) { $this->direccion = $direccion; }

    public function getLatitud() { return $this->latitud; }
    public function setLatitud($latitud) { $this->latitud = $latitud; }

    public function getLongitud() { return $this->longitud; }
    public function setLongitud($longitud) { $this->longitud = $longitud; }

    public function getPrecio() { return $this->precio; }
    public function setPrecio($precio) { $this->precio = $precio; }

    public function getNombreAlojamiento() { return $this->nombre_alojamiento; }
    public function setNombreAlojamiento($nombre_alojamiento) { $this->nombre_alojamiento = $nombre_alojamiento; }

    public function getTipoAlojamientoId() { return $this->tipo_alojamiento_id; }
    public function setTipoAlojamientoId($tipo_alojamiento_id) { $this->tipo_alojamiento_id = $tipo_alojamiento_id; }

    public function getCapacidadMaxima() { return $this->capacidad_maxima; }
    public function setCapacidadMaxima($capacidad_maxima) { $this->capacidad_maxima = $capacidad_maxima; }

    public function getCantBanios() { return $this->cant_banios; }
    public function setCantBanios($cant_banios) { $this->cant_banios = $cant_banios; }

    public function getCantidadDormitorios() { return $this->cantidad_dormitorios; }
    public function setCantidadDormitorios($cantidad_dormitorios) { $this->cantidad_dormitorios = $cantidad_dormitorios; }

    public function getCochera() { return $this->cochera; }
    public function setCochera($cochera) { $this->cochera = $cochera; }

    public function getPileta() { return $this->pileta; }
    public function setPileta($pileta) { $this->pileta = $pileta; }

    public function getAireAcondicionado() { return $this->aire_acondicionado; }
    public function setAireAcondicionado($aire_acondicionado) { $this->aire_acondicionado = $aire_acondicionado; }

    public function getWifi() { return $this->wifi; }
    public function setWifi($wifi) { $this->wifi = $wifi; }

    public function getNormasAlojamiento() { return $this->normas_alojamiento; }
    public function setNormasAlojamiento($normas_alojamiento) { $this->normas_alojamiento = $normas_alojamiento; }

    public function getDescripcionAlojamiento() { return $this->descripcion_alojamiento; }
    public function setDescripcionAlojamiento($descripcion_alojamiento) { $this->descripcion_alojamiento = $descripcion_alojamiento; }

    public function getIdUsuario() { return $this->id_usuario; }
    public function setIdUsuario($id_usuario) { $this->id_usuario = $id_usuario; }

    public function getEstadoId() { return $this->estado_id; }
    public function setEstadoId($estado_id) { $this->estado_id = $estado_id; }
}
