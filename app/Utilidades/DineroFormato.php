<?php

namespace App\Utilidades;
use Cknow\Money\Money;

/**
 * Funciones generales para perzonalizar formatos de cantidad monetaria
 * @package App\Utilidades
 */
class DineroFormato
{
    /**
     * Personaliza la apariencia de una cantidad monetaria ej. '$ 4,100.15'
     * @param mixed $cantidad 
     * @return Money 
     */
    static public function dinero( $cantidad ) {
        // USD se usa para ambas monedas en este caso, el resultado
        // obtenido es el necesario para cualquiera, ej. '$ 4,100.15'.
        // Se multiplica por 100, por que pide la cantidad en centavos
        return Money::USD( $cantidad * 100 );
    }
}