<?php

namespace App\Validaciones;

use Illuminate\Support\Facades\Validator;

/**
 *
 * @package App\Validaciones
 */
abstract class Validacion
{
    protected $errores;
    protected $modelo;
    protected $datos;

    /**
     *
     * @param mixed $modelo
     * @param mixed|null $datos
     * @return void
     */
    public function __construct($modelo, $datos = null) {
        $this->modelo = $modelo;
        $this->datos = $datos;
    }

    /**
     * Definir las reglas para la instancias Validator
     * @return mixed
     */
    abstract public function reglas();

    /**
     * Definir los mensajes para la instancia Validator
     * @return mixed
     */
    abstract public function mensajes();

    /**
     * Se implementa cuando sea necesario validar manualmente
     * algun campo
     * Es necesario armar un arreglo con valores de falso/verdadero
     * dentro de la funcion y retornarlo
     * @return mixed
     */
    abstract public function listaOtrasValidaciones();

    /**
     * Crear instancia Validator
     * @return Illuminate\Contracts\Validation\Validator
     */
    protected function validacion(){
        $reglas = $this->reglas();
        $mensajes = $this->mensajes();
        $datos = null;
        if($this->modelo){
            //significa que es modelo de Eloquent con lo que se cuenta
            $class = get_class( $this->modelo );
            $hidden = ( new $class() )->getHidden();
            $this->modelo->makeVisible($hidden);
            $datos = $this->modelo->toArray();
        } else {
            //solo se cuenta con un arreglo basico
            $datos = $this->datos;
        }
        return Validator::make( $datos, $reglas, $mensajes );
    }

    /**
     * Ejecutar validaciones automaticas usando la instancia Validator
     * @return bool
     */
    public function esValido(){
        $validator = $this->validacion();
        return !$validator->fails();
    }

    /**
     * Ejecutar validaciones manuales y evaluar si todas son
     * validas
     * @return mixed
     */
    public function otrasValidaciones(){
        $this->errores = [];
        //obtener un arreglo con valores falso/verdadero
        $validaciones = $this->listaOtrasValidaciones();
        //evaluar que todas las validaciones sean true
        return $validaciones ? min( $validaciones ) : true;
    }

    /**
     * Combinar los errores adicionales con los de la instancia Validator
     * @return array Contiene el resultado de los errores
     */
    public function errores(){
        // errores de otrasValidaciones()
        $errores = $this->errores;
        $validacion = $this->validacion();
        //agregar los errores que resultaron de otrasValidaciones()
        $this->agregaErrores( $errores, $validacion );
        //crear una copia en la variable por que se destruye al llamar fails()
        $error_bag = $validacion->errors()->messages();
        //es necesario revalidar para poder combinar los errores
        if ( $validacion->fails() ) { //se borran errores
            $this->agregaErrores( $errores, $validacion ); //de nuevo agregarlos
            return $validacion->errors()->messages();
        }
        //errores que se evaluaron en otrasValidaciones()
        return $error_bag;
    }

    /**
     * Agrega errores a la instancia $validator
     * @param mixed $errors
     * @param mixed $validator
     * @return void
     */
    protected function agregaErrores( $errors, &$validator ){
        $errors = $errors ?: [];
        foreach ( $errors as $error ) {
            $validator->errors()->add( $error['field'], $error['message'] );
        }
    }

    /**
     * Verifica que el valor venga especificado en el request antes de asignar
     * algun valor, esto evita que cuando no venga definido en el request
     * asigne valor false a un posible existente true
     * @param mixed $modelo
     * @param mixed $campo
     * @param mixed $arreglo
     * @return void
     */
    static public function booleano( &$modelo, $campo, $arreglo ){
        if( isset( $arreglo[$campo] ) ) {
            $modelo->{$campo} = $arreglo[$campo];
        }
    }
}
