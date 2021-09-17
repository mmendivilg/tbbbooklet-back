<?php

namespace App\Utilidades;
use NumberToWords\NumberToWords;
use Illuminate\Support\Facades\Lang;
use Illuminate\Translation\Translator;
use InvalidArgumentException;

/**
 * Convierte numero a letra
 * @package App\Utilidades
 */
class NumeroATexto
{
    /**
     * Convierte numero a texto ej. convertir ( 150.25, 'MXN', 'es' ):
     * CIENTO CINCUENTA PESOS CON VEINTICINCO CENTAVOS
     * convertir ( 150.25, 'usd', 'EN' ):
     * ONE HUNDRED AND FIFTY DOLLARS AND TWENTY-FIVE CENTS
     * @param mixed $cantidad 
     * @param mixed $moneda 
     * @param mixed $idioma 
     * @return mixed 
     * @throws InvalidArgumentException 
     */
    static function convertir( $cantidad, $moneda, $idioma ){
        $cantidad_formato = number_format( $cantidad, 2, '.', '' );
        $cantidad_entero = floor( $cantidad );
        $cantidad_fraccion = (int)(substr($cantidad_formato, -2, 2));
        
        $numberToWords = new NumberToWords();
        $transformerNumber = $numberToWords->getNumberTransformer( $idioma );
        
        $letra_entero = $transformerNumber->toWords( $cantidad_entero );
        $letra_fraccion = $transformerNumber->toWords( $cantidad_fraccion );
        
        if( $moneda == 'USD' ){
            $singular = Lang::get('messages.dólar',[], $idioma);
            $plural = Lang::get('messages.dólares',[], $idioma);
        }

        if( $moneda == 'MXN' ){
            $singular = Lang::get('messages.peso',[], $idioma);
            $plural = Lang::get('messages.pesos',[], $idioma);
        }

        $singular_centavo = Lang::get('messages.centavo',[], $idioma);
        $plural_centavo = Lang::get('messages.centavos',[], $idioma);

        if( $idioma == 'es' ){
            $letra_entero = self::corrigeUno( $letra_entero );
            $letra_fraccion = self::corrigeUno( $letra_fraccion );
        }

        $con = Lang::get('messages.numero_con',[], $idioma);
        
        if ( $cantidad_entero == 1 ){
            $letra_entero .= " {$singular}";
        } else {
            $letra_entero .= " {$plural}";
        }

        if ( $cantidad_fraccion == 1 ){
            $letra_fraccion .= " {$singular_centavo}";
        } else {
            $letra_fraccion .= " {$plural_centavo}";
        }
        
        if( $cantidad_entero > 0 ) {
            $letra = $letra_entero;
            if($cantidad_fraccion){
                $letra = "{$letra} {$con} {$letra_fraccion}";
            }
        } else {
            $letra = '';
            if($cantidad_fraccion){
                $letra = $letra_fraccion;
            }
        }

        return mb_strtoupper( $letra );
    }

    /**
     * 
     * @param mixed $letra 
     * @return string|string[]|null 
     */
    static function corrigeUno( $letra ) {
        //remueve espacios dobles:
        $letra = preg_replace( '/\s+/', ' ',$letra );
        if ( Texto::endsWith( $letra, 'uno' ) ) {
            //remueve la ultima "o":
            return rtrim($letra, "o");
        }
        return $letra;
    }
}