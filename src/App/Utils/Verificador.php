<?php

namespace Paw\App\Utils;

class Verificador
{
    public function verificarCamposVacios(Array $datos, Array $required){   
        
        $camposVacios = [];

        foreach ($required as $key){
            if (!array_key_exists($key, $datos) || $datos[$key] === null || $datos[$key] === ''){
                $camposVacios[] = $key;
            }
        }

        if (empty($camposVacios)){
            return [
                'exito' => true,
                'description' => 'Campos completos',
                'campos_vacios' => []
            ];
        }

        return [
            'exito' => false,
            'description' => 'Uno o mas campos estan vacios',
            'campos_vacios' => $camposVacios
        ];

    }

    function array_has_empty_values($array) {
        foreach ($array as $value) {
            if (empty($value)) {
                return true;
            }
        }
        return false;
    }




    private function agregarError(array &$errores, string $campo, string $mensaje){

        $errores[$campo] = $mensaje;

    }

    private function obtenerLongitud($valor){
        
        if (function_exists('mb_strlen')){
            return mb_strlen($valor, 'UTF-8');
        }

        return strlen($valor);

    }

    //Valida un texto normal
    public function texto($valor, $campo, array &$errores, $obligatorio = true, $minimo = 1, $maximo = 255){

        if ($valor === null || $valor === ''){
            if ($obligatorio){
                $this->agregarError($errores, $campo, "El campo {$campo} es obligatorio");
            }

            return null;
        }

        if (!is_string($valor)){
            $this->agregarError($errores, $campo, "El campo {$campo} debe ser texto");
            return null;
        }

        $valor = trim($valor);
        $longitud = $this->obtenerLongitud($valor);

        if ($longitud < $minimo){
            $this->agregarError($errores, $campo, "El campo {$campo} debe tener al meno {$minimo} caracteres");
            return null;
        }

        if ($longitud > $maximo){
            $this->agregarError($errores, $campo, "El campo {$campo} debe tener como maximo {$maximo} caracteres");
            return null;
        }

        return $valor;
        
    }

    
    //Valida un correo electronico
    public function email($valor, $campo, array &$errores){
        
        $valor = $this->texto($valor, $campo, $errores, true, 3, 255);

        if ($valor === null){
            return null;
        }

        $valor = strtolower($valor);

        if (!filter_var($valor, FILTER_VALIDATE_EMAIL)){
            $this->agregarError($errores, $campo, "El correo electronico no es valido");
            return null;
        }

        return $valor;

    }

    //Validar contraseña sin modificarla
    public function password($valor, $campo, array &$errores, $minimo = 8, $maximo = 255){

        if (!is_string($valor) || $valor === ''){
            $this->agregarError($errores, $campo, "La contraseña es obligatoria");
            return null;
        }

        //No se usa el trim ni el htmlspetialchars para no modificar la contraseña
        
        $longitud = $this->obtenerLongitud($valor);

        if ($longitud < $minimo){
            $this->agregarError($errores, $campo, "La contraseña debe tener al menos {$minimo} caracteres");
            return null;
        }

        if ($longitud > $maximo){
            $this->agregarError($errores, $campo, "La contraseña debe tener como maximo {$maximo} caracteres");
            return null;
        }

        return $valor;

    }


    //Validar numero entero
    public function entero($valor, $campo, array &$errores, $minimo = null, $maximo = null){

        if ($valor === null || $valor === ''){
            $this->agregarError($errores, $campo, "El campo {$campo} es obligatorio");
            return null;
        }

        $entero = filter_var($valor, FILTER_VALIDATE_INT);

        if ($entero === false){
            $this->agregarError($errores, $campo, "El campo {$campo} debe ser un numero entero");
            return null;
        }

        if ($minimo !== null && $entero < $minimo){
            $this->agregarError($errores, $campo, "El campo {$campo} debe ser mayor o igual a {$minimo}");
            return null;
        }

        if ($maximo !== null && $entero > $maximo){
            $this->agregarError($errores, $campo, "El campo {$campo} debe ser menor o igual a {$maximo}");
            return null;
        }

        return $entero;

    }


    //Validar una opcion contra una lista de IDs permitidos
    public function opcionEntera($valor, $campo, array &$errores, array $opcionesPermitidas){

        $valor = $this->entero($valor, $campo, $errores, 1);

        if ($valor === null){
            return null;
        }

        $opcionesPermitidas = array_map('intval', $opcionesPermitidas);

        if (!in_array($valor, $opcionesPermitidas, true)){
            $this->agregarError($errores, $campo, "La opcion seleccionada para {$campo} no es valida");
            return null;
        }

        return $valor;

    }

    //Validar un numero de telefono
    public function telefono($valor, $campo, array &$errores){

        $valor = $this->texto($valor, $campo, $errores, true, 7, 25);

        if ($valor === null){
            return null;
        }

        //Permitir +54 11 1234-5678, (011) 1234-5678
        if (!preg_match('/^\+?[0-9\s\-()]+$/', $valor)){
            $this->agregarError($errores, $campo, "El telefono contiene caracteres no permitidos");
            return null;
        }

        return $valor;

    }


    //Valida fecha con formato YYYY-MM-DD
    public function fecha($valor, $campo, array &$errores){

        if (!is_string($valor) || $valor === ''){
            $this->agregarError($errores, $campo, "La fecha {$campo} es obligatoria");
            return null;
        }

        $fecha = \DateTime::createFromFormat('!Y-m-d', $valor);

        $estadoFecha = \DateTime::getLastErrors();

        $tieneErrores = $estadoFecha !== false && ($estadoFecha['warning_count'] > 0 || $estadoFecha['error_count'] > 0);

        if ($fecha === false || $tieneErrores || $fecha->format('Y-m-d') !== $valor){
            $this->agregarError($errores, $campo, "La fecha {$campo} no es valida");
            return null;
        }

        return $valor;

    }


    //Valida un precio entero, si el formulario envia 12.500 se transforma a 12500
    public function precioEntero($valor, $campo, array &$errores){

        if (!is_string($valor) && !is_int($valor)){
            $this->agregarError($errores, $campo, "El precio no es valido");
            return null;
        }

        $valor = trim((string) $valor);
        $valor = str_replace(['.', ' '], '', $valor);

        if (!preg_match('/^\d+$/', $valor)){
            $this->agregarError($errores, $campo, "El precio debe contener solo numeros");
            return null;
        }

        $precio = (int) $valor;

        if ($precio <= 0){
            $this->agregarError($errores, $campo, "El precio debe ser mayor a cero");
            return null;
        }

        return $precio;

    }


    //Convierte un checkbox en 1 o 0
    public function checkbox($valor){

        return $valor === null ? 0 : 1;

    }


    //Valida las coordenadas JSON generadas oir Leaflet
    public function coordenadas($valor, $campo, array &$errores){

        if (!is_string($valor) || $valor === ''){
            $this->agregarError($errores, $campo, "Debe seleccionar una ubicacion en el mapa");
            return null;
        }

        $coordenadas = json_decode($valor, true);

        if (!is_array($coordenadas) || !isset($coordenadas['lat']) || !isset($coordenadas['lng']) || !is_numeric($coordenadas['lat']) || !is_numeric($coordenadas['lng'])){
            $this->agregarError($errores, $campo, "Las coordenadas no son validas");
            return null;
        }

        $latitud = (float) $coordenadas['lat'];
        $longitud = (float) $coordenadas['lng'];

        if ($latitud < -90 || $latitud > 90 || $longitud < -180 || $longitud > 180){
            $this->agregarError($errores, $campo, "Las coordenadas no son validas");
            return null;
        }

        return json_encode(['lat' => $latitud, 'lng' => $longitud]);

    }


}