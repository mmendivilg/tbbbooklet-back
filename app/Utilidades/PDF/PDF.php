<?php

namespace App\Utilidades\PDF;
use Mpdf\Mpdf;
use App\Utilidades\Contenedor;
use App\Models\Gasto\Gasto;
use App\Models\Empresa\Empresa;
use Exception;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Config;
use Symfony\Component\Templating\PhpEngine;
use Symfony\Component\Templating\TemplateNameParser;
use Symfony\Component\Templating\Loader\FilesystemLoader;

/**
 * Funciones generales de PDF
 * @package App\Utilidades\PDF
 */
class PDF
{
    use Contenedor;
    /**
     *
     * @var Empresa
     */
    protected $empresa;
    /**
     *
     * @var Mpdf
     */
    protected $mpdf;
    /**
     *
     * @var FilesystemLoader
     */
    protected $filesystemLoader;

    /**
     *
     * @var PhpEngine
     */
    protected $templating;

    /**
     * Inicializa el archivo PDF
     * @param Empresa $empresa
     * @param mixed|null $spreadsheet Arreglo de las rutas de archivos css
     * @param mixed|null $style Arreglo de string de codigo css
     * @param mixed $ruta Ruta donde se buscan las plantillas
     * @return void
     * @throws Exception
     */
    public function __construct ( Empresa $empresa, $spreadsheet = null, $style = null, $ruta ) {
        $this->empresa = $empresa;
        $this->mpdf = new Mpdf();
        $this->filesystemLoader = new FilesystemLoader( $ruta );
        $this->templating = new PhpEngine(new TemplateNameParser(), $this->filesystemLoader);
        $spreadsheet = $spreadsheet ?: [];
        foreach ($spreadsheet as $ruta_css) {
            $this->agregarCSS( $ruta_css );
        }
        $style = $style ?: [];
        foreach ($style as $css) {
            $this->agregarCSSInline( $css );
        }
    }

    /**
     * Agregar codigo CSS de la variable
     * @param mixed $css
     * @return void
     * @throws Exception
     */
    protected function agregarCSSInline( $css ){
        $this->mpdf->WriteHTML($css, \Mpdf\HTMLParserMode::HEADER_CSS);
    }

    /**
     * Agregar CSS del archivo
     * @param mixed $ruta
     * @return void
     * @throws Exception
     */
    protected function agregarCSS( $ruta ){
        $stylesheet = file_get_contents( $ruta );
        $this->mpdf->WriteHTML($stylesheet, \Mpdf\HTMLParserMode::HEADER_CSS);
    }

    /**
     * Evaluar la plantilla usando phpengine
     * @param mixed $array Nombre del archivo de la plantilla
     * @param mixed $parameters
     * @return void
     * @throws Exception
     */
    protected function render( $array, $parameters ){
        $this->mpdf->WriteHTML(
            $this->templating->render( $array, $parameters ),
            \Mpdf\HTMLParserMode::HTML_BODY
        );
    }

    /**
     * Acceso rapido a la funcion original de MPDF
     * @param mixed $footer
     * @return void
     * @throws Exception
     */
    protected function setFooter( $footer ){
        $this->mpdf->SetFooter( $footer );
    }

    /**
     * Finaliza el archivo PDF
     * @param mixed|null $container
     * @param mixed|null $file
     * @return void
     * @throws Exception
     */
    public function output( $container = null, $file = null ){
        if( $container && $file ){
            $file = self::contenedor( $container, $file );
            $this->mpdf->Output( $file, \Mpdf\Output\Destination::FILE );
        } else {
            $this->mpdf->Output();
        }
    }
}
