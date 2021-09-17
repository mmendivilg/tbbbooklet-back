<?php

namespace App\Validaciones\User;
use Illuminate\Support\Facades\Validator;
use App\Validaciones\Validacion;

class UserSignupValidacion extends Validacion
{
    public function reglas()
    {
        return [
            'name' => 'required|max:80',
            'email' => 'required|unique:users',
            'password' => 'required|min:8',
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
