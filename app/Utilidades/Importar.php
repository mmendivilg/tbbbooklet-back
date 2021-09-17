<?php

namespace App\Utilidades;
use \ZipArchive;
use App\Models\Empresa\Empresa;
use App\Models\Factura\FacturaXML;
use Illuminate\Http\UploadedFile;
use App\Models\Factura\FacturaXmlDetalle;
use App\Models\Catalogo\FacturaMetodoPago;
use App\Validaciones\Factura\FacturaValidacion;
use Exception;
use InvalidArgumentException;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Spatie\TemporaryDirectory\TemporaryDirectory;

/**
 * Importar algun tipo de archivo(s) mediante el API,
 * validar, y procesar informacion para guardarla
 * @package App\Utilidades
 */
class Importar
{

    /**
     * Procesar CFDI's en formato XML,
     * estos pueden venir en ZIP
     * @param mixed $files
     * @param Empresa $empresa
     * @return mixed
     * @throws FileNotFoundException
     * @throws InvalidArgumentException
     * @throws Exception
     */
    public static function importarCFDIs($files, Empresa $empresa){
        if(!is_array($files) || !$files){
            $respuesta = new ArchivoApiRespuesta( false, 'No hay archivo' );
            return $respuesta->respuesta();
        }

        $respuesta = new ArchivoApiRespuesta( true );
        foreach ( $files as $file ) {
            if( $file instanceof UploadedFile ){
                $type = strtolower( $file->extension() );
                self::cfdi( $file, $type, $respuesta, $empresa );
            } else {
                $api_r = new ArchivoApiRespuesta( false,  'Error en el archivo', $file.'' );
                $respuesta->agregar( $api_r );
            }
        }
        return $respuesta->respuesta();
    }

    /**
     * Procesar un archivo y evaluar si es ZIP o XML,
     * dirigir al metodo necesario
     * @param UploadedFile $file
     * @param mixed $tipo puede ser zip o xml
     * @param ArchivoApiRespuesta $respuesta
     * @param mixed $empresa
     * @return ArchivoApiRespuesta
     * @throws FileNotFoundException
     * @throws InvalidArgumentException
     * @throws Exception
     */
    protected static function cfdi(UploadedFile $file, $tipo, ArchivoApiRespuesta &$respuesta, $empresa ){
        if( in_array( $tipo, ['xml', 'zip'] ) ){
            if($tipo == 'xml') {
                $respuesta->agregar( self::cfdi_xml( null, null, $file, $empresa ) );
            } else if($tipo == 'zip'){
                $respuesta->agregar( self::cfdi_zip( null, $file, $empresa ) );
            }
        } else {
            $api_r = new ArchivoApiRespuesta( false,  'Tipo de archivo no permitido', $file->getClientOriginalName() );
            $respuesta->agregar( $api_r );
        }
        return $respuesta;
    }

    /**
     * Procesar un archivo de CFDI en formato XML
     * @param mixed|null $file_contents
     * @param mixed|null $filename
     * @param UploadedFile|null $uploaded_file
     * @param mixed $empresa
     * @return ArchivoApiRespuesta
     * @throws FileNotFoundException
     * @throws InvalidArgumentException
     */
    protected static function cfdi_xml($file_contents = null, $filename = null, UploadedFile $uploaded_file = null, $empresa ){
        if( $uploaded_file ){
            $filename = $uploaded_file->getClientOriginalName();
            $file_contents = $uploaded_file->get();
        }

        $fac_xml_detalle = new FacturaXmlDetalle;
        $fac_xml_detalle->id_empresa = $empresa->id;
        $validacion = new FacturaValidacion($fac_xml_detalle);


        $import_errors = self::cfdi_xml_import($file_contents, $filename, $fac_xml_detalle);

        $errores = null;
        $exito = false;

        //validaciones personalizadas
        $otras_validaciones = $validacion->otrasValidaciones();

        //validaciones automaticas de objeto Validator
        $modelo_validacion = $validacion->esValido();
        $es_valido = $modelo_validacion && $otras_validaciones;

        if( $es_valido ){
            $exito = $fac_xml_detalle->save();
        } else {
            $errores = $validacion->errores();
        }

        $message = $exito ? 'Archivo importado' : 'Error';
        $resultado = new ArchivoApiRespuesta( $exito, $message, $filename, null, $errores );

        if( $exito ){
            // Registrar el archivo de XML raw solamente
            // como historial de la importacion,
            // no tiene usos practicos en el proyecto
            $fac_xml = new FacturaXML;
            $fac_xml->uuid = $fac_xml_detalle->uuid;
            $fac_xml->xml = $file_contents;
            $fac_xml->id_empresa = $empresa->id;
            $fac_xml->save();
        }

        // $import_errors tiene que venir como null cuando se importo exitosamente
        // de cualquier manera se retorna una instancia de ArchivoApiRespuesta
        return $import_errors ?: $resultado;
    }

    /**
     * Procesar la informacion que viene en formato XML
     * extrayendo solamente los datos que se necesiten
     * @param mixed $file_contents
     * @param mixed $filename
     * @param mixed $fac_xml_detalle
     * @return ArchivoApiRespuesta retorna un objeto con la descripcion del error
     * @return void retorna null si la operacion tiene exito
     */
    protected static function cfdi_xml_import($file_contents, $filename, &$fac_xml_detalle){
        $streamXML = @simplexml_load_string($file_contents);
        if(!$streamXML){
            $message = 'Archivo XML no puede ser leido, o tiene errores';
            $resultado = new ArchivoApiRespuesta( false, $message, $filename );
            return $resultado;
        }

        $namespaces = $streamXML->getNamespaces(true);
	    $streamXML->registerXPathNamespace('c', $namespaces['cfdi']);
	    $streamXML->registerXPathNamespace('t', $namespaces['tfd']);

        self::import_xml_comprobante($streamXML, $fac_xml_detalle);
        self::import_xml_emisor($streamXML, $fac_xml_detalle);
        self::import_xml_domicilio_fiscal($streamXML, $fac_xml_detalle);
        self::import_xml_receptor($streamXML, $fac_xml_detalle);
        self::import_xml_timbre_fiscal_digital($streamXML, $fac_xml_detalle);

        $conceptos = self::import_xml_conceptos($streamXML, $fac_xml_detalle);
        $concepto = implode(", ", $conceptos);
        $string = substr($concepto,0,500);
        $fac_xml_detalle->concepto = $string;
        if(doubleval($fac_xml_detalle->version)>=3.3){
            self::import_3dot3_impuestos($streamXML, $fac_xml_detalle);
        } else {
            self::import_3dot2_impuestos($streamXML, $fac_xml_detalle);
        }
        return;
    }

    /**
     * Procesar un archivo en formato ZIP,
     * extraer el contenido a una carpeta temporal,
     * recorrer la carpeta recursivamente e importar
     * cada XML
     * @param mixed|null $filename
     * @param UploadedFile|null $uploaded_file
     * @param mixed $empresa
     * @return ArchivoApiRespuesta|array
     * @throws Exception
     * @throws InvalidArgumentException
     * @throws FileNotFoundException
     */
    protected static function cfdi_zip($filename = null, UploadedFile $uploaded_file = null, $empresa){
        $zip = new \ZipArchive();

        if( $uploaded_file ){
            $filename = $uploaded_file->path();
        }
        if( !$filename ){
            throw new \Exception;
        }
        $open = @$zip->open( $filename );

        $resultado = [];
        $error = false;
        if($open){
            $temporaryDirectory = ( new TemporaryDirectory() )->create();
            $path = $temporaryDirectory->path();

            $extract = $zip->extractTo( $path );

            if( $extract ) {
                $it = new \RecursiveDirectoryIterator( $path );
                $ext_arr = Array ( 'xml', 'XML' );
                foreach(new \RecursiveIteratorIterator( $it ) as $file)
                {
                    $ext = substr( $file, strrpos( $file, '.' ) + 1);
                    if( in_array( $ext, $ext_arr ) ) {
                        $fc = file_get_contents( $file );
                        $resultado[] = self::cfdi_xml( $fc, basename( $file ), null, $empresa );
                    }
                }
            } else {
                $error = true;
            }
        } else {
            $error = true;
        }

        $temporaryDirectory->delete();
        if($error){
            return new ArchivoApiRespuesta( false, 'Error al abrir el archivo zip', $file->getClientOriginalName() );
        }
        return $resultado;
    }

    /**
     *
     * @param mixed $streamXML
     * @param mixed $fac_xml_detalle
     * @return void
     */
    protected static function import_xml_comprobante($streamXML, &$fac_xml_detalle){
        foreach ($streamXML->xpath('//cfdi:Comprobante') as $xml){
            $xmlArray = self::xml2array($xml);
			$xmlArray = array_change_key_case($xmlArray['@attributes'], CASE_LOWER);
            $fac_xml_detalle->version = @$xmlArray['version'];
            $fac_xml_detalle->fecha = @$xmlArray['fecha'];
            $fac_xml_detalle->total = 0;
            $fac_xml_detalle->subtotal = 0;
            $fac_xml_detalle->total = @$xmlArray['total'];
            $fac_xml_detalle->subtotal = @$xmlArray['subtotal'];
            $fac_xml_detalle->condiciones_de_pago = @$xmlArray['condicionesdepago'];
            $fac_xml_detalle->id_forma_pago = (int)@$xmlArray['formapago'];
            $fac_xml_detalle->id_metodo_pago = self::obtenerMetodoPago(@$xmlArray['metodopago']);
            $fac_xml_detalle->tipo_de_comprobante = @$xmlArray['tipodecomprobante'];
            $fac_xml_detalle->sello_cfd = @$xmlArray['sello'];
            $fac_xml_detalle->no_certificado = @$xmlArray['nocertificado'];
            $moneda = 'MXN';

            if(isset($xmlArray['moneda']))
            	if(substr(strtoupper($xmlArray['moneda']), 0, 1) == 'P' || substr(strtoupper($xmlArray['moneda']), 0, 1) == 'M' || substr(strtoupper($xmlArray['moneda']), 0, 1) == 'N')
                	$moneda = 'MXN';
                elseif(substr(strtoupper($xmlArray['moneda']), 0, 1) == 'D' || substr(strtoupper($xmlArray['moneda']), 0, 1) == 'U')
                	$moneda = 'USD';
            $fac_xml_detalle->moneda = $moneda;
	        $fac_xml_detalle->folio = @$xmlArray['folio'];
            if(isset($xmlArray['serie']))
                $fac_xml_detalle->folio = $xmlArray['serie']."-".$fac_xml_detalle->folio;
        }
    }

    /**
     *
     * @param mixed $streamXML
     * @param mixed $fac_xml_detalle
     * @return void
     */
    protected static function import_xml_emisor($streamXML, &$fac_xml_detalle){
        foreach ($streamXML->xpath('//cfdi:Comprobante//cfdi:Emisor') as $xml)
        {
            $xmlArray = self::xml2array($xml);
            $xmlArray = array_change_key_case($xmlArray['@attributes'], CASE_LOWER);
            $fac_xml_detalle->rfc_emisor = @$xmlArray['rfc'];
            $fac_xml_detalle->nombre_emisor = @$xmlArray['nombre'];
            $fac_xml_detalle->regimen_emisor = @$xmlArray['regimenfiscal'];
        }
    }

    /**
     *
     * @param mixed $streamXML
     * @param mixed $fac_xml_detalle
     * @return void
     */
    protected static function import_xml_domicilio_fiscal($streamXML, &$fac_xml_detalle){
        foreach ($streamXML->xpath('//cfdi:Comprobante//cfdi:Emisor//cfdi:DomicilioFiscal') as $xml)
        {
            $xmlArray = self::xml2array($xml);
            $xmlArray = array_change_key_case($xmlArray['@attributes'], CASE_LOWER);
            $fac_xml_detalle->calle_emisor = @$xmlArray['calle'];
            $fac_xml_detalle->estado_emisor = @$xmlArray['estado'];
            $fac_xml_detalle->colonia_emisor = $xmlArray['colonia'] ?? null;
            $fac_xml_detalle->municipio_emisor = @$xmlArray['municipio'];
            $fac_xml_detalle->no_ext_emisor = $xmlArray['noexterior'] ?? null;
        }
    }

    /**
     *
     * @param mixed $streamXML
     * @param mixed $fac_xml_detalle
     * @return void
     */
    protected static function import_xml_receptor($streamXML, &$fac_xml_detalle){
        foreach ($streamXML->xpath('//cfdi:Comprobante//cfdi:Receptor') as $xml)
        {
            $xmlArray = self::xml2array($xml);
            $xmlArray = array_change_key_case($xmlArray['@attributes'], CASE_LOWER);
            $fac_xml_detalle->rfc_receptor = @$xmlArray['rfc'];
            $fac_xml_detalle->nombre_receptor = $xmlArray['nombre'] ?? null;
            $fac_xml_detalle->usocfdi_receptor = $xmlArray['usocfdi'] ?? null;
        }
    }

    /**
     *
     * @param mixed $streamXML
     * @param mixed $fac_xml_detalle
     * @return void
     */
    protected static function import_xml_timbre_fiscal_digital($streamXML, &$fac_xml_detalle){
        foreach ($streamXML->xpath('//t:TimbreFiscalDigital') as $xml)
        {
            $xmlArray = self::xml2array($xml);
            $xmlArray = array_change_key_case($xmlArray['@attributes'], CASE_LOWER);

            $fac_xml_detalle->uuid = @$xmlArray['uuid'];
            $fac_xml_detalle->sello_cfd = @$xmlArray['sellocfd'];
            $fac_xml_detalle->sello_sat = @$xmlArray['sellosat'];
            $fac_xml_detalle->no_certificado_sat = @$xmlArray['nocertificadosat'];
            $fac_xml_detalle->rfc_prov_cert = @$xmlArray['rfcprovcertif'];
            $fac_xml_detalle->timbrefiscal_version = @$xmlArray['version'];
            $fac_xml_detalle->fecha_timbrado = @$xmlArray['fechatimbrado'];
        }
    }

    /**
     *
     * @param mixed $streamXML
     * @param mixed $fac_xml_detalle
     * @return array
     */
    protected static function import_xml_conceptos($streamXML, &$fac_xml_detalle){
        $conceptos = [];
        foreach ($streamXML->xpath('//cfdi:Comprobante//cfdi:Conceptos//cfdi:Concepto') as $xml)
        {
            $xmlArray = self::xml2array($xml);
            $xmlArray = array_change_key_case($xmlArray['@attributes'], CASE_LOWER);
            if(isset($xmlArray['descripcion']))
                $conceptos[] =  $xmlArray['descripcion'];
        }
        return $conceptos;
    }

    /**
     *
     * @param mixed $streamXML
     * @param mixed $fac_xml_detalle
     * @return void
     */
    private static function import_3dot3_impuestos($streamXML, &$fac_xml_detalle){
        $iva = 0;
        foreach ($streamXML->xpath('//cfdi:Comprobante//cfdi:Conceptos//cfdi:Concepto//cfdi:Impuestos//cfdi:Traslados//cfdi:Traslado') as $xml)
        {
            $xmlArray = self::xml2array($xml);
            $xmlArray = array_change_key_case($xmlArray['@attributes'], CASE_LOWER);
            if(isset($xmlArray['impuesto']) && isset($xmlArray['importe']))
                if(strtoupper($xmlArray['impuesto']) == "002")
                    if(is_numeric($xmlArray['importe']))
                        $iva += $xmlArray['importe'];
        }
        $fac_xml_detalle->iva = $iva;
        $ret_isr = 0;
        $ret_iva = 0;
        foreach ($streamXML->xpath('//cfdi:Comprobante//cfdi:Conceptos//cfdi:Concepto//cfdi:Impuestos//cfdi:Retenciones//cfdi:Retencion') as $xml)
        {
            $xmlArray = self::xml2array($xml);
            $xmlArray = array_change_key_case($xmlArray['@attributes'], CASE_LOWER);
            if(isset($xmlArray['impuesto']) && isset($xmlArray['importe']))
                if(strtoupper($xmlArray['impuesto']) == "002"){
                    if(is_numeric($xmlArray['importe']))
                        $ret_iva += $xmlArray['importe'];
                } else if(strtoupper($xmlArray['impuesto']) == "001"){
                        $ret_isr += $xmlArray['importe'];
                }
        }
        if($ret_isr >0)
            $fac_xml_detalle->ret_isr = $ret_isr;
        if($ret_iva >0)
            $fac_xml_detalle->ret_iva = $ret_iva;
    }

    /**
     *
     * @param mixed $streamXML
     * @param mixed $fac_xml_detalle
     * @return void
     */
    private static function import_3dot2_impuestos($streamXML, &$fac_xml_detalle){
        $iva = 0;
        foreach ($streamXML->xpath('//cfdi:Comprobante//cfdi:Impuestos//cfdi:Traslados//cfdi:Traslado') as $xml)
        {
            $xmlArray = self::xml2array($xml);
            $xmlArray = array_change_key_case($xmlArray['@attributes'], CASE_LOWER);
            if(isset($xmlArray['impuesto']) && isset($xmlArray['importe']))
                if(strtoupper($xmlArray['impuesto']) == "IVA")
                    if(is_numeric($xmlArray['importe']))
                        $iva += $xmlArray['importe'];
        }
        $fac_xml_detalle->iva = $iva;
        $ret_isr = 0;
        $ret_iva = 0;
        foreach ($streamXML->xpath('//cfdi:Comprobante//cfdi:Impuestos//cfdi:Retenciones//cfdi:Retencion') as $xml)
        {
            $xmlArray = self::xml2array($xml);
            $xmlArray = array_change_key_case($xmlArray['@attributes'], CASE_LOWER);
            if(isset($xmlArray['impuesto']) && isset($xmlArray['importe']))
                if(strtoupper($xmlArray['impuesto']) == "IVA"){
                    if(is_numeric($xmlArray['importe']))
                        $ret_iva += $xmlArray['importe'];
                } else if(strtoupper($xmlArray['impuesto']) == "ISR"){
                        $ret_isr += $xmlArray['importe'];
                }
        }
        if($ret_isr >0)
            $fac_xml_detalle->ret_isr = $ret_isr;
        if($ret_iva >0)
            $fac_xml_detalle->ret_iva = $ret_iva;
    }

    /**
     * Buscar el id de un metodo de pago, utilizando la descripcion (clave) que
     * utilizan los CFDIs
     * @param mixed $metodopago
     * @return mixed
     */
    protected static function obtenerMetodoPago($metodopago){
        if( $metodopago ){
            $mdp = FacturaMetodoPago::where( 'clave', 'like', $metodopago )->first();
            if($mdp){
                return $mdp->id;
            }
        }
        return null;
    }

    /**
     *
     * @param mixed $xmlObject
     * @param array $out
     * @return array
     */
	private static function xml2array ( $xmlObject, $out = array () )
	{
	    foreach ( (array) $xmlObject as $index => $node )
	        $out[$index] = ( is_object ( $node ) ) ? self::xml2array ( $node ) : $node;
	    return $out;
	}
}
