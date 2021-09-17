<?php

namespace App\Utilidades\ModelHelper\Empresa;
use Illuminate\Support\Facades\Config;

/**
 * Asistente del modelo Empresa
 * Operaciones relacionadas al logotipo
 * @package App\Utilidades\ModelHelper\Empresa
 */
trait Logotipo
{
    /**
     * Cuando se requiere de logo 
     * se le agrega la ruta completa
     * @param mixed $value esta variable trae el valor de logo
     * @return string|void 
     */
    public function getLogoAttribute( $value )
    {
        //Evitar retornar ruta sin archivo si no existe logo
        if( $value ) {
            return Config::get( 'constants.empresa.logotipo.ruta' ).$value;
        }
        return '';
    }
}