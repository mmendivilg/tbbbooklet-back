<?php

namespace App\Validaciones\User;
use Illuminate\Support\Facades\Validator;
use App\Validaciones\Validacion;

class UserLoginValidacion extends Validacion
{
    public function reglas()
    {
        return [
            'email' => 'required',
            'password' => 'required',
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
