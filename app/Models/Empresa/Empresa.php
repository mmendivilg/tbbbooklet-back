<?php


namespace App\Models\Empresa;

use Illuminate\Database\Eloquent\Model;
use App\Utilidades\ModelHelper\Empresa\Datos;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Utilidades\ModelHelper\Empresa\Logotipo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Empresa extends Model
{
    use SoftDeletes, Logotipo, HasFactory;

    protected $table = "empresas";

    protected static function newFactory()
    {
        return \Database\Factories\EmpresaFactory::new();
    }
}
