<?php

namespace App\Utilidades\PDF;
use \RuntimeException;
class VersionPDF
{
/*
 * This file is part of the PDF Version Converter.
 *
 * (c) Thiago Rodrigues <xthiago@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
    public static function leer($archivo)
    {
        $version = self::leerVersion($archivo);
        if ($version === null)
            throw new RuntimeException("¿El archivo '{$archivo}' es un PDF valido?");

        return $version;
    }

    protected static function leerVersion($archivo)
    {
        $fp = @fopen($archivo, 'rb');
        if (!$fp) {
            return 0;
        }

        fseek($fp, 0);
        preg_match('/%PDF-(\d\.\d)/', fread($fp,1024), $match);
        fclose($fp);

        if (isset($match[1]))
            return $match[1];

        return null;
    }
}
