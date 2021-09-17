<?php

namespace App\Validaciones\Ubicacion;
use Illuminate\Support\Facades\Validator;
use App\Validaciones\Validacion;

class UbicacionValidacion extends Validacion
{
    public function reglas()
    {
        return [
            'nombre' => 'required',
            'lat' => 'required',
            'lng' => 'required',
        ];
    }

    public function mensajes()
    {
        return [
            // 'email.max' => ' 8? TOO MUCH!!',
        ];
    }

    public function listaOtrasValidaciones(){ }
}
