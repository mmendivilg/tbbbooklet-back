<?php

namespace App\Validaciones\Archivo;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\MessageBag;
use App\Validaciones\Validacion;

class ArchivoBase64Validacion extends Validacion
{
    public function reglas()
    {
        return [
            'archivos.*' => "array",
            'id' => 'integer|required',
        ];
    }


    public function mensajes()
    {
        return [
            // 'uuid.required' => 'A uuid is required',
        ];
    }

    public function listaOtrasValidaciones(){ }
}
