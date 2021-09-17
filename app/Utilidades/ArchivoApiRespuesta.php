<?php

namespace App\Utilidades;

/**
 * Crear una respuesta despues de procesar archivos
 * @package App\Utilidades
 */
class ArchivoApiRespuesta
{
    public $estatus;
    public $mensaje;
    public $mensajes;
    public $archivo;
    public $muestra_status;
    public $muestra_message;
    public $muestra_archivo;
    public $datos;
    public $uuid;
    public $id_archivo;
    public $img_data;

    public function __construct ( $estatus = 0, $mensaje = null, $archivo = null, $datos = null, $mensajes = null ) {
        $this->estatus = $estatus;
        $this->mensaje = $mensaje;
        $this->mensajes = $mensajes;
        $this->archivo = $archivo;
        $this->datos = $datos ?: [];
        $this->muestra_status = 1; //always
        $this->muestra_message = $mensaje ? 1 : 0;
        $this->muestra_archivo = $archivo ? 1 : 0;
    }

    public function agregar ( $api_respuesta ) {
        if(is_array ( $api_respuesta) ) {
            foreach  ( $api_respuesta as $api_r ) {
                $this->datos[] = $api_r;
            }
        } else {
            $this->datos[] = $api_respuesta;
        }
    }

    public function respuesta() {
        $result = [];

        if ( $this->muestra_status ) {
            $result['status'] = $this->estatus();
        }
        if ( $this->muestra_message ) {
            $result['message'] = $this->mensaje;
        }
        if ( $this->muestra_archivo ) {
            $result['filename'] = $this->archivo;
        }
        if ( $this->uuid ) {
            $result['uuid'] = $this->uuid;
        }
        if ( $this->id_archivo ) {
            $result['id_archivo'] = $this->id_archivo;
        }
        if ( $this->img_data ) {
            $result['img_data'] = $this->img_data;
        }
        if ( $this->datos ) {
            $counts = $this->contador();
            if ( $counts ) {
                $result['has_errors'] = 'false';
                if ( $counts['error_count'] ) {
                    $result['has_errors'] = 'true';
                }
                $result['success_count'] = $counts['success_count'];
                $result['error_count'] = $counts['error_count'];
            }
            $datos = [];
            foreach  ( $this->datos as $api_r ) {
                $datos[] = $api_r->respuesta();
            }
            $result['data'] = $datos;
        }
        if ( $this->mensajes ) {
            $result['messages'] = $this->mensajes;
        }
        return $result;
    }

    private function contador() {
        if(!$this->datos ) {
            $result = [];
            $result['error_count'] = !$this->estatus;
            $result['success_count'] = $this->estatus;
            return $result;
        }
        $conteo_errores = 0;
        $conteo_exitos = 0;
        foreach  ( $this->datos as $api_respuesta ) {
            if ( $api_respuesta->datos ) {
                $conteo = $api_respuesta->contador();
                $conteo_errores += $conteo['error_count'];
                $conteo_exitos += $conteo['success_count'];
            } else {
                $conteo_errores += !$api_respuesta->estatus;
                $conteo_exitos += $api_respuesta->estatus;
            }
        }
        $result = [];
        $result['error_count'] = $conteo_errores;
        $result['success_count'] = $conteo_exitos;
        return $result;
    }

    protected function estatus() {
        return $this->estatus ? 'success' : 'error';
    }
}
