<?php

namespace App\Utilidades\Helper;

/**
 * Organizar los campos usando un arreglo obtenido de una clase de Validacion::reglas()
 * se usa para evaluar el contenido de $request y evitar hacer cambios cuando no venga
 * definido el valor en el $request y pueda destruir datos, tambien para guardar la informacion
 * adecuadamente segun el tipo de dato
 * @package App\Utilidades\Helper
 */
trait SegmentaCampos
{
    /**
     * Usar clase para acceder a sus reglas
     * y a sus campos, extraer informacion de los campos,
     * poner el resultado en una matriz con la organizacion
     * @param mixed $validacion_clase 
     * @return array 
     */
    static public function segmentaCamposPorTipo( $validacion_clase ){
        $reglas = ( new $validacion_clase( null, null ) )->reglas();
        $general = [];
        $bool = [];
        $string = [];
        $array = [];
        foreach ($reglas as $campo => $rule) {
            if ( strpos($rule, 'boolean') !== false ) {
                $bool[] = $campo;
            } else if ( strpos($rule, 'string') !== false ) {
                $string[] = $campo;
            } else if ( strpos($rule, 'array') !== false ) {
                $array[] = $campo;
            } else {
                $general[] = $campo;
            }
        }
        $resultado = [
            'general' => $general,
            'boolean' => $bool,
            'string' => $string,
            'array' => $array
        ];
        return $resultado;
    }
}
