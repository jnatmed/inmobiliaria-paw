<?php

namespace Paw\App\Utils;

use Exception;

class Utils
{
    public function obtenerCoordenadas($jsonString) {
        // Decodificar entidades HTML
        $jsonString = html_entity_decode($jsonString);

        // Intentar decodificar el JSON
        $coordenadas = json_decode($jsonString, true);
    
        // Verificar si hubo errores al decodificar el JSON
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new Exception('Error al decodificar el JSON: ' . json_last_error_msg());
        }
    
        // Verificar si existen las claves 'lat' y 'lng'
        if (!isset($coordenadas['lat']) || !isset($coordenadas['lng'])) {
            throw new Exception('Las claves "lat" o "lng" no están presentes en el JSON.');
        }
    
        return $coordenadas;
    }
}
