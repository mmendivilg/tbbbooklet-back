<?php

namespace Tests;

use App\Utilidades\NumeroATexto as N;

class Prueba
{
    public static function test(){
        $imagick = new \Imagick(storage_path("app/ubicaciones/5fc4db2f5a11c0.25018246.jpeg"));
        $width = $imagick->getImageWidth();
        $height = $imagick->getImageHeight();
        $new_width = 360;
        $new_height = $height/($width/$new_width);
        $imagick->resizeImage($new_width, $new_height, \Imagick::FILTER_UNDEFINED, 1, true);
        $imagick->writeImage(storage_path("app/ubicaciones/mythumb.jpeg"));
    }
}
