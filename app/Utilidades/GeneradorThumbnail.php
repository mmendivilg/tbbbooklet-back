<?php

namespace App\Utilidades;

use GuzzleHttp\Psr7\MimeType;
use Spatie\TemporaryDirectory\TemporaryDirectory;


class GeneradorThumbnail
{
    public static function generar_individual( $archivo ){
        $file = storage_path('app/'.$archivo['path']);
        $path_info = pathinfo($file);
        $mimetype = MimeType::fromFilename($file);
        $imagick = new \Imagick($file);
        $width = (clone $imagick)->getImageWidth();
        $height = (clone $imagick)->getImageHeight();
        $new_width = 360;
        $new_height = $height / ( $width / $new_width );
        $imagick->resizeImage($new_width, $new_height, \Imagick::FILTER_UNDEFINED, 1, true);
        $contents = $imagick->getImageBlob();
        $base_64_img = 'data:'.$mimetype.';base64,'.base64_encode($contents);
        $archivo['dimensions'] = ['width' => $width, 'height'=> $height];
        $archivo['type'] = $mimetype;
        $archivo['ext'] = $path_info['extension'];
        $archivo['size'] = filesize ($file);
        $archivo['sizeText'] = self::size_as_kb( filesize ($file) );
        $archivo['url_resized'] = $base_64_img;
        return $archivo;
    }

	public static function generar ( $archivos )
	{
        $archivos = json_decode( $archivos, true);
        $archivos = $archivos ?: [];
        foreach ($archivos as &$archivo) {
            $archivo = self::generar_individual( $archivo );
        }
	    return $archivos;
    }

    private static function size_as_kb( $size )
    {
        if ($size < 1024) {
            return "{$size} bytes";
        } elseif ($size < 1048576) {
            $size_kb = round($size/1024);
            return "{$size_kb} KB";
        } else {
            $size_mb = round($size/1048576, 1);
            return "{$size_mb} MB";
        }
    }

}
