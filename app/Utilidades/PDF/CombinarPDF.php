<?php

namespace App\Utilidades\PDF;

use App\Utilidades\Contenedor;
use Exception;
use Mpdf\Mpdf;
use App\Utilidades\Texto;
use App\Utilidades\PDF\VersionPDF;
use App\Utilidades\PDF\GhostScript\Converter;
use Illuminate\Support\Facades\File;
use Spatie\TemporaryDirectory\TemporaryDirectory;
use Illuminate\Support\Facades\Config;

/**
 * Combina varios PDFS en uno
 * @package App\Utilidades\PDF
 */
class CombinarPDF
{
    use Contenedor;
    /**
     * 
     * @param mixed $archivos 
     * @return void 
     * @throws Exception 
     */
    public static function combinar( $archivos )
    {

        $temporaryDirectory = ( new TemporaryDirectory() )->create();
        $ruta = $temporaryDirectory->path();
        $mpdf = new Mpdf(['tempDir' => $ruta]);

        
        foreach ($archivos as $archivo) {
            self::agrega( $archivo, $ruta, $mpdf );
        }
        
        $container = 'app/finanza/';
        $path = storage_path( $container );
        $result = self::contenedor( $container, 'testing.pdf' );

        if(!File::exists( $path ) ){
            File::makeDirectory($path, 0755, true);
        }
        
        $mpdf->Output( $result, 'F' );
    }

    /**
     * Agregar un archivo pdf a un objeto MPDF
     * @param mixed $archivo 
     * @param mixed $ruta_temp 
     * @param mixed $log_path 
     * @param Mpdf $mpdf 
     * @return void 
     * @throws Exception 
     */
    static function agrega( $archivo, $ruta_temp, Mpdf &$mpdf ) {

        $archivo_temporal = self::temporal( $archivo, $ruta_temp );
        $paginas_cant = $mpdf->SetSourceFile( $archivo_temporal );

        $paginas_info = self::creaInfo( 
            $archivo_temporal,
            $ruta_temp
        );
        
        for ($i=1; $i<=$paginas_cant; $i++) {
            $import_page = $mpdf->ImportPage($i);
            $size = $mpdf->getTemplateSize($import_page);
            $w = @$size['width'];
            $h = @$size['height'];

            if( isset ( $paginas_info[$i] ) ){
                $w = $paginas_info[$i]['w']/2.835;
                $h = $paginas_info[$i]['h']/2.835;
            }

            //agregar página en blanco
            $mpdf->AddPage('P','','','','','','','','','','',
            '','','','','','','','','',[$w,$h]);
            //escribir los datos en la pagina nueva
            $mpdf->UseTemplate($import_page, 0, 0, $w, $h, true);
        }

    }

    /**
     * Crear copia del archivo, dependiendo 
     * de la version es necesario convertir a 
     * 1.4 para que sea compatible
     * @param mixed $archivo 
     * @param mixed $ruta_temp 
     * @return string 
     */
    static function temporal ( $archivo, $ruta_temp ) {
        $archivo_nombre = $ruta_temp . '/' . uniqid('pdf_merge_') . '.pdf';
        copy( $archivo, $archivo_nombre );
        self::convierteVersion( $archivo_nombre, $ruta_temp );
        return $archivo_nombre;
    }

    
    /**
     * MPDF es compatible con archivo PDF version 
     * 1.4 o menor.
     * Es posible leer la version y convertir
     * usando gs
     * @param mixed $archivo 
     * @param mixed $rutaTmp 
     * @return int|string 
     * @throws Exception 
     */
    static function convierteVersion( $archivo, $rutaTmp ) {
        $guesser = new VersionPDF();
        $ver = $guesser->leer($archivo);
        if(doubleval($ver) > 1.4) {
            ( new Converter )->convert( $archivo, '1.4', $rutaTmp );
        }
        return $ver;
    }

    /**
     * Generear arreglo dimension w/h de cada pagina en el archivo
     * @param mixed $original 
     * @param mixed $ruta_temp 
     * @return array datos obtenidos por gs, informacion de el archivo pdf (ver pdf_info.ps)
     */
    static function creaInfo( $original, $ruta_temp ){
        
        $pdfinfo_path = Config::get( 'constants.ghostscript.plantilla.pdf.ruta' );
        $log_path = $ruta_temp.'/out.log';
        
        $command = "gs -dNODISPLAY -q -sFile={$original} -dDumpMediaSizes {$pdfinfo_path} > {$log_path}";
        exec($command, $res);

        return self::obtenInfo( $log_path );
    }

    /**
     * Leer el archivo out.log
     * @param mixed 
     * @return array la dimension w/h de cada pagina en el archivo
     */
    static function obtenInfo( $log_path ){
        $pages_info = [];
        if (file_exists($log_path) && is_readable ($log_path)) {
            $fileResource  = fopen($log_path, "r");
            if ($fileResource) {
                while (($line = fgets($fileResource)) !== false) {
                    $page_no = Texto::between($line, 'Page ', ' MediaBox:');
                    $size = Texto::between($line, 'MediaBox: [ ', ' ]');
                    $sizes = explode(" ", $size);

                    $page_no = intval($page_no);
                    foreach ($sizes as $key => &$size) {
                        $size = intval ( $sizes[$key] );
                    }

                    if($page_no && isset( $sizes[0] ) && isset( $sizes[1] )){
                        $newItem = [];
                        $newItem['w'] = $sizes[0];
                        $newItem['h'] = $sizes[1];
                        $pages_info[$page_no] = $newItem;
                    }
                }
                fclose($fileResource);
            }
        }
        return $pages_info;
    }

}
