<?php

namespace App\Utilidades\Excel;
use App\Models\Gasto\Gasto;
use App\Models\Empresa\Empresa;
use App\Utilidades\Contenedor;
use PhpOffice\PhpSpreadsheet\Exception;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Reader\Exception as ReaderException;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Shared\Date;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;
use PhpOffice\PhpSpreadsheet\Writer\Exception as WriterException;

/**
 * Funciones generales de excel
 * @package App\Utilidades\Excel
 */
class Excel
{
    use Contenedor;
    /**
     * Empresa asociada con la instancia
     * @var Empresa
     */
    protected $empresa;
    /**
     * Objeto PHPExcel asociado con la instancia
     * @var Spreadsheet
     */
    protected $spreadsheet;

    /**
     *  Inicia archivo excel
     * @param Empresa $empresa
     * @param mixed $ruta
     * @param mixed $titulo
     * @return void
     * @throws ReaderException
     */
    public function __construct ( Empresa $empresa, $ruta, $titulo ) {
        $this->empresa = $empresa;
        if( $ruta ){
            $reader = IOFactory::createReader('Xls');
            $this->spreadsheet = $reader->load( $ruta );
        } else {
            $this->spreadsheet = new Spreadsheet();
        }

        $this->spreadsheet->getProperties()->setCreator('OSU HOA Management '.date('Y') )
                ->setLastModifiedBy('OSU HOA Management')
                ->setTitle( $titulo )
                ->setSubject('Report')
                ->setDescription('HOA Admin Generated Document')
                ->setKeywords('office 2007 openphp')
                ->setCategory('Reporting');
    }

    /**
     * Enviar los headers necesarios,
     * generar el archivo final
     * @param mixed|null $container carpeta contenedora ej. var/www/carpeta/
     * @param mixed|null $file Nombre del archivo ej. archivo.xlsx
     * @param mixed|null $ext Por defecto es Xlsx
     * @return exit
     * @throws Exception
     */
    public function output( $container = null, $file = null, $ext = null ){
        self::headers( $file );
        $ext = $ext ?: 'Xlsx';
        $writer = IOFactory::createWriter( $this->spreadsheet, $ext );
        if( $container && $file ){
            $file = self::contenedor( $container, $file );
            $writer->save( $file );
        } else {
            $writer->save('php://output');
        }
        exit;
    }

    /**
     * Acceso rapido a la funcion original de PHPExcel
     * @param mixed $celda
     * @param mixed $dato
     * @return void
     * @throws Exception
     */
    public function setCellValue( $celda, $dato ){
        $this->spreadsheet->getActiveSheet()->setCellValue( $celda, $dato );
    }

    /**
     * Acceso rapido a la funcion original de PHPExcel
     * @param mixed $range
     * @return void
     * @throws Exception
     */
    public function mergeCells( $range ){
        $this->spreadsheet->getActiveSheet()->mergeCells($range);
    }

    /**
     * Acceso rapido a la funcion original de PHPExcel
     * @param mixed $index
     * @return void
     * @throws Exception
     */
    public function setActiveSheetIndex( $index ){
        $this->spreadsheet->setActiveSheetIndex( $index );
    }

    /**
     * Acceso rapido a la funcion original de PHPExcel
     * @param mixed $before
     * @param mixed $amount
     * @return void
     * @throws Exception
     */
    public function insertNewRowBefore( $before, $amount ){
        $this->spreadsheet->getActiveSheet()->insertNewRowBefore($before, $amount);
    }

    /**
     * Acceso rapido a la funcion original de PHPExcel
     * @param mixed $cell
     * @param mixed $styleArray
     * @return void
     * @throws Exception
     */
    public function applyStyleFromArray( $cell, $styleArray ){
        $this->spreadsheet->getActiveSheet()->getStyle( $cell )->applyFromArray($styleArray);
    }

    /**
     * Acceso rapido a la funcion original de PHPExcel
     * @param mixed $row
     * @param mixed $numRows
     * @return void
     * @throws Exception
     */
    public function removeRow( $row, $numRows ){
        $this->spreadsheet->getActiveSheet()->removeRow( $row, $numRows );
    }

    /**
     * Acceso rapido a la funcion original de PHPExcel
     * @param mixed $row
     * @param mixed $visible
     * @return void
     * @throws Exception
     */
    public function setVisibleRow( $row, $visible ){
        $this->spreadsheet->getActiveSheet()->getRowDimension( $row )->setVisible( $visible );
    }

    /**
     * Remover la parte de arriba de un borde de el area
     * @param mixed $cell celda o rango
     * @return void
     * @throws Exception
     */
    public function removeBorderTop( $cell ){
        $styleArray = [
            'borders' => [
                'top' => [
                    'borderStyle' => Border::BORDER_NONE,
                ],
            ],
        ];
        $this->applyStyleFromArray( $cell, $styleArray );
    }

    /**
     * Poner borde delgado en la parte de arriba del area
     * @param mixed $cell celda o rango
     * @return void
     * @throws Exception
     */
    public function thinBorderTop( $cell ){
        $styleArray = [
            'borders' => [
                'top' => [
                    'borderStyle' => Border::BORDER_THIN,
                ],
            ],
        ];
        $this->applyStyleFromArray( $cell, $styleArray );
    }

    /**
     * Insertar el logotipo de la empresa asociada en la celda
     * @param mixed $cell celda o rango
     * @param mixed|null $maxAlto Por defecto: 94
     * @param mixed|null $maxAncho Por defecto: 99
     * @return void
     * @throws Exception
     */
    public function insertaLogotipo( $cell, $maxAlto = null, $maxAncho = null ){
        $drawing = new Drawing();
        $drawing->setName('Logo');
        $drawing->setDescription('Logo');
        if (!$this->empresa->getOriginal('logo')){
            return;
        }
        $drawing->setPath( $this->empresa->logo );
        $maxAlto = $maxAlto ?: 94;
        $maxAncho = $maxAncho ?: 99;
        $drawing->setWidth($maxAncho);
        $coordY = $maxAlto - $drawing->getHeight();
        $drawing->setCoordinates( $cell );
        $drawing->setOffsetY( $coordY );
        $drawing->setWorksheet($this->spreadsheet->getActiveSheet());
    }

    /**
     * Headers necesarios para enviarse un archivo excel
     * @param mixed $archivo
     * @return void
     */
    public static function headers( $archivo ){
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header("Content-Disposition: attachment;filename=\"{$archivo}\"");
        header('Cache-Control: max-age=0');
        // If you're serving to IE over SSL, then the following may be needed
        header('Expires: Mon, 26 Jul 1997 05:00:00 GMT'); // Date in the past
        header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT'); // always modified
        header('Cache-Control: cache, must-revalidate'); // HTTP/1.1
        header('Pragma: public'); // HTTP/1.0
    }
}
