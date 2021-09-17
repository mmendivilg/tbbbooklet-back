<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Storage;
use App\Models\Ubicacion\Ubicacion;
use App\Models\Archivo;
use Illuminate\Http\Request;
use App\Validaciones\Archivo\ArchivoBase64Validacion;
use App\Validaciones\Archivo\ArchivoValidacion;
use App\Utilidades\ArchivoApiRespuesta;
use App\Utilidades\ArchivoManejadorBase64;
use App\Utilidades\ArchivoManejador;
use \Exception;
use Illuminate\Support\Facades\Config;

class ArchivoUbicacionController extends Controller
{
    public function store(Request $request)
    {
        try {
            $validacion = new ArchivoValidacion( null, $request->all() );
            if( !$validacion->esValido() ) {
                return response()->json( array('errors' => $validacion->errores() ), 400 );
            }

            $id_empresa = 1;
            $id = $request->id;

            $archivos_datos = $this->obtenerArchivos ( $id );
            $carpeta_almacenaje = 'ubicaciones/';
            // file_put_contents(storage_path('app/ubicaciones/filename.txt'), print_r($ubicacion->archivos, true));

            $respuesta = new ArchivoApiRespuesta( true );
            if( isset( $request->all()['archivos'] ) ){
                $archivos = $request->all()['archivos'];
                if( $archivos && count($archivos) > 0 ) {
                    $archivos_guardados = ArchivoManejador::guardaArchivos( $carpeta_almacenaje, $archivos, $id_empresa, $respuesta );
                }
            } else {
                return response()->json(["errors" => ["archivos" => ["Necesitas enviar un archivo"]]], 400);
            }

            if($archivos_guardados) {
                $archivos_datos = array_merge( $archivos_datos, $archivos_guardados );
            }

            if( !$this->actualizarArchivos( $id, $archivos_datos ) ){
                throw new Exception('Error de sistema');
            }

            return $respuesta->respuesta();

        } catch (\Exception | \Throwable | \Error $e) {
            return response()->json(["error" => $e->getMessage()], 500);
        }
    }

    public function obtenerArchivos ( $id, $stdClass = false ){
        if ( ! ( $ubicacion = Ubicacion::find($id) ) ){
            throw new Exception( 'No existe el modelo especificado' );
        }
        $archivos = json_decode( $ubicacion->archivos, $stdClass );
        $archivos = $archivos ?: [];
        return $archivos;
    }

    public function actualizarArchivos ( $id, $archivos_datos ){
        if ( ! ( $ubicacion = Ubicacion::find($id) ) ){
            throw new Exception( 'No existe el modelo especificado' );
        }
        $ubicacion->archivos = json_encode( $archivos_datos );
        return $ubicacion->save();
    }

/*     public function show($id, Request $request)
    {
        if( $archivo = Archivo::find( $id ) ) {
            // echo base64_encode( Storage::download( $archivo->path, $archivo->nombre ) );
            $file = storage_path('app\\'.$archivo->path);
            $contents = file_get_contents($file);
            return base64_encode($contents);
            // return base64_encode( Storage::download( $archivo->path, $archivo->nombre ) );
            // return Storage::download( $archivo->path, $archivo->nombre );
        }
    } */

    public function show($id, Request $request)
    {
        // $archivo = Archivo::find( 1 );
        // $file = storage_path('app\\ubicaciones\\'.$image);
        // return response()->file($file);
        if( $ubicacion = Ubicacion::find( $id ) ){
            $archivos = $ubicacion->archivos;
        }
        $archivos = $this->obtenerArchivos ( $id );
        if( count( $archivos ) > 0 ) {
            $archivos_datos = [];
            foreach ($archivos as $archivo) {
                // $archivos_datos[] = 'data:image/jpg;base64,'.base64_encode($contents);
                $file = storage_path('app/'.$archivo->path);
                $contents = file_get_contents($file);
                $mime = mime_content_type($file);
                $archivos_datos[] = 'data:'.$mime.';base64,'.base64_encode($contents);
            }
            return $archivos_datos;
        }
        // if( $archivo = Archivo::find( $id ) ) {
        //     // echo base64_encode( Storage::download( $archivo->path, $archivo->nombre ) );
        //     $contents = file_get_contents(storage_path('app\\'.$archivo->path));
        //     return base64_encode($contents);
        //     // return base64_encode( Storage::download( $archivo->path, $archivo->nombre ) );
        //     // return Storage::download( $archivo->path, $archivo->nombre );
        // }
    }

    public function destroy($id_ubicacion, $id_archivo)
    {
        if( $archivo = Archivo::find( $id_archivo ) ){
            $archivos_datos = $this->obtenerArchivos ( $id_ubicacion );
            $archivos_datos_nuevo = [];
            $archivo_encontrado = false;
            foreach ($archivos_datos as $archivo_datos) {
                if($archivo_datos->id_archivo != $id_archivo){
                    $archivos_datos_nuevo[] = $archivo_datos;
                } else {
                    $archivo_encontrado = true;
                }
            }
            if( !$archivo_encontrado ){
                throw new Exception('Archivo no registrado en la ubicacion especificada');
            }
            if( !$this->actualizarArchivos( $id_ubicacion, $archivos_datos_nuevo ) ){
                throw new Exception('Error de sistema');
            }
            $archivo->delete();
            Storage::delete( $archivo->path );
        }
    }
}
