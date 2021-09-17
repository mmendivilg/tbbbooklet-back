<?php

namespace App\Utilidades;

use Exception;
use Illuminate\Support\Facades\File;

/**
 * Verifica que exista la carpeta,
 * donde se va a crear el archivo
 * @package App\Utilidades
 */
trait Contenedor
{
    /**
     * Verificar que exista la carpeta,
     * cuando no existe se crea
     * @param mixed $container 
     * @param mixed $file 
     * @return string 
     * @throws Exception 
     */
    static function contenedor( $container, $file ){
        $path = storage_path( $container );

        if(!File::exists( $path ) ){
            File::makeDirectory($path, 0755, true);
        }
        return storage_path( "{$container}{$file}" );
    }
}