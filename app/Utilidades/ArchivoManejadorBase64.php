<?php

namespace App\Utilidades;
use Illuminate\Support\Facades\Storage;
use App\Models\Archivo;
use App\Utilidades\ArchivoApiRespuesta;
use Exception;

/**
 * Se almacenan archivos relacionados a algun modelo
 * y se genera una respuesta con los detalles de los archivos.
 * Finalmente se crea un registro en la clase App\Models\Archivo
 * con la informacion por cada uno de los archivos
 * @package App\Utilidades
 */
class ArchivoManejadorBase64
{
    /**
     * Validar que se guardo el archivo correctamente y genera respuesta
     * @param mixed $carpeta_almacenaje
     * @param mixed $archivos
     * @param mixed $id_empresa
     * @param ArchivoApiRespuesta $respuesta
     * @return array Informacion de el resultado de todos los archivos
     * @throws Exception
     */
    public static function guardaArchivos( $carpeta_almacenaje, $archivos, $id_empresa, ArchivoApiRespuesta &$respuesta ){
        $resultado = [];
        if( $archivos && count($archivos) > 0 ) {
            foreach ( $archivos as $archivo ) {
                if( $archivo ){
                    $res = self::guardaArchivo( $carpeta_almacenaje, $archivo, $id_empresa, $respuesta );
                    if( $archivo ){
                        $resultado[] = $res;
                    }
                }
            }
        }
        return $resultado;
    }

    /**
     * Almacena un archivo y crea instancia de Archivo con la informacion
     * @param mixed $carpeta_almacenaje
     * @param mixed $file
     * @param mixed $id_empresa
     * @param ArchivoApiRespuesta $respuesta
     * @return array|Archivo|false
     * @throws Exception
     */
    public static function guardaArchivo( $carpeta_almacenaje, $archivo, $id_empresa, ArchivoApiRespuesta &$respuesta ){
        $file = $archivo['data'];
        $ext = $archivo['ext'];
        $filename = $archivo['name'];
        $base64file = preg_replace('/^data:image\/(\w+);base64,/', '', $file);
        $file_contents = base64_decode($base64file);
        $uuid = uniqid( '', true );
        $path = $carpeta_almacenaje.$uuid.'.'.$ext;

        if( Storage::put( $path, $file_contents ) ){
            $archivo = new Archivo;
            $archivo->nombre = $filename;
            $archivo->uuid = $uuid;
            $archivo->extension = $ext;
            $archivo->id_empresa = $id_empresa;
            $archivo->path = $path;
            if( $archivo->save() ){
                $api_r = new ArchivoApiRespuesta( true, 'Archivo guardado', $filename );
                $api_r->uuid = $uuid;
                $respuesta->agregar( $api_r );
                return [
                    'id_archivo' => $archivo->id,
                    'uuid' => $uuid,
                    'nombre' => $filename,
                    'path' => $path,
                ];
            } else {
                Storage::delete( $path );
            }
        }
        $api_r = new ArchivoApiRespuesta( false,  'Error en el archivo', $filename );
        $respuesta->agregar( $api_r );
        return false;
    }

}
