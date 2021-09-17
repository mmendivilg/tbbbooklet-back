<?php

namespace App\Utilidades;
use Carbon\Carbon;
use Carbon\Exceptions\InvalidFormatException;
use Illuminate\Contracts\Container\BindingResolutionException;

/**
 * Crea un objeto de fecha con cualquier formato definido
 * ej. personalizar el formato
 * @package App\Utilidades
 */
class FechaFormato
{
    /**
     * @var Carbon|string
     */
    public $fecha;

    /**
     * Definir si se incluye un formato con la fecha
     * si no lo trae, se usa la funcion parse para fechas
     * en general
     * @param mixed $fecha 
     * @param mixed|null $formato 
     * @return void 
     * @throws InvalidFormatException 
     */
    public function __construct ( $fecha, $formato = null ) {
        if($formato){
            $this->fecha = Carbon::createFromFormat( $formato, $fecha )->locale('es');;
        } else {
            $this->fecha = Carbon::parse($fecha)->locale('es');;
        }
    }

    /**
     * Aplica un formato a la fecha
     * @param mixed $formato 
     * @return string 
     */
    public function formato( $formato ){
        return $this->fecha->format( $formato );
    }

    /**
     * El nombre completo en texto del mes
     * @param bool $abreviado se muestran los primeros 3 caracteres
     * @return string|false|array|null 
     * @throws BindingResolutionException 
     */
    public function mesLetras ( $abreviado = false ){
        $messages = 'messages.'.mb_strtolower( $this->fecha->monthName );
        //traducir al idioma
        $mes = __($messages);
        if( $abreviado ){
            return substr( $mes, 0, 3 );
        } else {
            return $mes;
        }
    }

    /**
     * El numero del mes
     * @param bool $string el numero incluye el '0', si es falso se convierte a int
     * @return string|int 
     */
    public function mes ( $string = false ){
        $mes = $this->fecha->month;
        if( $string ){
            //string con 0 antes del numero
            return sprintf('%02d', $mes);
        } else {
            //integer
            return $mes;
        }
    }

    /**
     * El numero del año
     * @return int 
     */
    public function anyo (){
        return $this->fecha->year;
    }

    public static function fechaAleatoriaEsteAnyo( $maxNow = true){
        $month = rand( 1, $maxNow ? date('m') : 12 );
        $d = cal_days_in_month( CAL_GREGORIAN, $month, date( 'Y' ) );
        if( $month == date('m') ){
            $d = date('d');
        }
        
        $d = cal_days_in_month( CAL_GREGORIAN, $month, date( 'Y' ) );
        return Carbon::create( date( 'Y' ), $month, rand( 1, $d ), 0, 0, 0 );
    }
}