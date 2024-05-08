<?php

namespace Paw\App\Utils;

class Verificador
{
    /**
     * entrada: formulario de campos 
     * salida: - booleano (campos vacios o no)
     *         - descripcion
     *         - array con los nombres de los campos vacios 
     */
    public function verificarCamposVacios(Array $datos, Array $required)
    {   
        foreach (array_keys($datos) as $key) {
            // me fijo si esta dentro de los requeridos
            if(in_array($key, $required)){
                // si esta, me fijo si esta vacio
                if(empty($datos[$key])){
                    // en caso de estar vacio 
                    // lo guardo en array de vacios 
                    // para que sirva en caso de necesitar informar vacios
                    $camposVacios[] = $key;  
                }
            }
        };

        $noHayCamposVacios = empty($camposVacios);

        return $noHayCamposVacios ? [
            'exito' => true,
            'description' => 'Campos Completos',
            'campos_vacios' => []
            ] : [
            'exito' => false,
            'description' => 'Uno de los campos esta Vacio',
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

}