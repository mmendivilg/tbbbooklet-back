<?php

namespace App\Utilidades;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use App\Models\Archivo;
use App\Utilidades\ArchivoApiRespuesta;
use App\Utilidades\GeneradorThumbnail;
use Exception;

/**
 * Se almacenan archivos relacionados a algun modelo
 * y se genera una respuesta con los detalles de los archivos.
 * Finalmente se crea un registro en la clase App\Models\Archivo
 * con la informacion por cada uno de los archivos
 * @package App\Utilidades
 */
class ArchivoManejador
{
    /**
     * Validar que se guardo el archivo correctamente y genera respuesta
     * @param mixed $carpeta_almacenaje
     * @param mixed $files
     * @param mixed $id_empresa
     * @param ArchivoApiRespuesta $respuesta
     * @return array Informacion de el resultado de todos los archivos
     * @throws Exception
     */
    public static function guardaArchivos( $carpeta_almacenaje, $files, $id_empresa, ArchivoApiRespuesta &$respuesta ){
        $resultado = [];
        if( $files && count($files) > 0 ) {
            foreach ( $files as $file ) {
                if( $file instanceof UploadedFile ){
                    $archivo = self::guardaArchivo( $carpeta_almacenaje, $file, $id_empresa, $respuesta );
                    if( $archivo ){
                        $resultado[] = $archivo;
                    }
                }
            }
        }
        return $resultado;
    }

    /**
     * Almacena un archivo y crea instancia de Archivo con la informacion
     * @param mixed $carpeta_almacenaje
     * @param UploadedFile $file
     * @param mixed $id_empresa
     * @param ArchivoApiRespuesta $respuesta
     * @return array|Archivo|false
     * @throws Exception
     */
    public static function guardaArchivo( $carpeta_almacenaje, UploadedFile $file, $id_empresa, ArchivoApiRespuesta &$respuesta ){
        $ext = strtolower( $file->extension() );
        $filename = $file->getClientOriginalName();
        $file_contents = $file->get();
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
                $api_r->id_archivo = $archivo->id;
                $api_r->img_data = GeneradorThumbnail::generar_individual($archivo->toArray());
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
