<?php

namespace App\Validaciones\Archivo;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\MessageBag;
use App\Validaciones\Validacion;

class ArchivoValidacion extends Validacion
{
    public function reglas()
    {
        return [
            'archivos.*' => "mimes:jpg,jpeg,png|max:20000",
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
