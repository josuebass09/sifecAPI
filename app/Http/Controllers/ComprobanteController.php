<?php

namespace App\Http\Controllers;

use App\Emisor;
use App\Clases\Email;
use App\DetalleComprobante;
use App\DetalleImpuesto;
use App\Comprobante;
use http\Env\Response;
use Illuminate\Http\File;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use phpDocumentor\Reflection\Types\Float_;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use Barryvdh\DomPDF\Facade as PDF;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class ComprobanteController extends Controller
{

    public function testCorreo(Request $request)
    {
        /*$email=new Email('xml firmado','respuesta haciena','50612041900020686079700100001010000000058100004904','josuebass09@gmail.com','Josué Hidalgo R.',1,'nombre del emisor','1234555','Josue Hidalgo R.');

        $this->sendEmail($email);*/
        //$this->makeInvoice('50624041900020686079700100001010000000062100049047');

        try{
            $detalleImpuesto = new DetalleImpuesto;
            $detalleImpuesto->id_comprobante=28;
            $detalleImpuesto->id_linea=135;
            $detalleImpuesto->id_impuesto='00';
            $detalleImpuesto->tarifa=100;
            $detalleImpuesto->monto=90;
            /*$detalleImpuesto->tp_documento_exo='00';
            $detalleImpuesto->numero_doc_exo = "1234";
            $detalleImpuesto->institucion_exo = "IBAO";
            $detalleImpuesto->fecha_emision_exo = date('Y-m-d H:i:s', strtotime("2019-05-08T00:14:20+06:00"));
            $detalleImpuesto->monto_impuesto_exo = 1200;
            $detalleImpuesto->porcentaje_compra_exo = 30;*/
            $detalleImpuesto->save();
        }catch (Exception $exception)
        {
            return $exception->getMessage();
        }

    }
    function makeClave(Request $request)
    {
        $emisor=null;
        $fecha=date(DATE_RFC3339);
        $api_key=$request->header('X-Api-Key');
        $seguridad="";
        $sucursal="001";
        $terminal="00001";
        $codigoPais="506";
        $id_emisor="";
        $entorno=$request->header('entorno');
        if(!isset($api_key) OR empty($api_key))
        {
            return response()->json(array("code"=>"3","data"=>"Requiere que se incluya el API KEY dentro de los parámetros para
poder realizar el proceso.","fecha"=>$fecha), 400);
        }
        if(!isset($entorno) OR empty($entorno))
        {
            return response()->json(array("code"=>"10","data"=>"Requiere que se incluya el entorno dentro de los parámetros del encabezado de la solicitud.","fecha"=>$fecha), 400);
        }
        $payload=$request->json()->all();

        if($entorno=='stag')
        {
            $emisor=DB::table('EMISORES')->select('EMISORES.id','EMISORES.api_key','EMISORES.usuario_atv_test','EMISORES.contrasena_atv_test','EMISORES.certificado_atv_test','EMISORES.pin_atv_test','EMISORES.consecutivoFEtest','EMISORES.consecutivoTEtest','EMISORES.consecutivoNDtest','EMISORES.consecutivoNCtest','EMISORES.consecutivoFECtest','EMISORES.consecutivoFEEtest')->where('EMISORES.api_key','=',$api_key)->first();
        }
        elseif($entorno=='prod')
        {
            $emisor=DB::table('EMISORES')->select('EMISORES.id','EMISORES.api_key','EMISORES.usuario_atv_prod','EMISORES.contrasena_atv_prod','EMISORES.certificado_atv_prod','EMISORES.pin_atv_prod','EMISORES.consecutivoFEprod','EMISORES.consecutivoTEprod','EMISORES.consecutivoNDprod','EMISORES.consecutivoNCprod','EMISORES.consecutivoFECprod','EMISORES.consecutivoFEEprod')->where('EMISORES.api_key','=',$api_key)->first();
        }
        else{
            return response()->json(array("code"=>"26","data"=>"Ambiente incorrecto. Ambientes disponibles:[stag] para pruebas y [prod] para producción","entorno"=>$entorno), 401);
        }

        if(!$emisor)
        {
            return response()->json(array("code"=>"4","data"=>"Fallo en el proceso de autentificación por un API KEY incorrecto","X-Api-Key"=>$request->header('X-Api-Key')), 401);
        }
        $id_emisor=$emisor->id;

        //Se validan los tipos de datos y limitaciones
        $rules = [

            'tipoComprobante'=>['regex:/^[0-9]+$/u','min:2','max:2','required'],
            'situacion'=>['regex:/^[1-3]+$/u','min:1','max:1','required']

        ];
        $customMessages = [

            'tipoComprobante.regex'=>'El formato del tipo de comprobante es incorrecto *20',
            'tipoComprobante.min'=>'El formato del tipo  de comprobante es incorrecto *20',
            'tipoComprobante.max'=>'El formato del tipo de comprobante es incorrecto *20',
            'tipoComprobante.required'=>'El tipo de comprobante es requerido *20',
            'situacion.regex'=>'El formato de la situacion de comprobante es incorrecto *20',
            'situacion.min'=>'El formato de la situacion de comprobante es incorrecto *20',
            'situacion.max'=>'El formato de la situacion de comprobante es incorrecto *20',
            'situacion.required'=>'La situacion de comprobante es requerida *20'
        ];

        $validaciones = Validator::make($payload, $rules,$customMessages);
        if($validaciones->fails())
        {
            $mensaje=$validaciones->errors()->first();
            $code=$this->getCode($mensaje);
            $data=$this->eraseCodeIntoMessage($mensaje,$code);
            return response()->json(array("code"=>$code,"data"=>$data,"fecha"=>$fecha,"payload"=>$payload), 400);
        }

        $dia = date('d');
        $mes = date('m');
        $ano = date('y');
        if($entorno=='stag') {

            if ($payload['tipoComprobante'] == '01') {
                $num_consecutivo = $emisor->consecutivoFEtest;
            } elseif ($payload['tipoComprobante'] == '02') {
                $num_consecutivo = $emisor->consecutivoNDtest;
            } elseif ($payload['tipoComprobante'] == '03') {
                $num_consecutivo = $emisor->consecutivoNCtest;
            } elseif ($payload['tipoComprobante'] == '04') {
                $num_consecutivo = $emisor->consecutivoTEtest;
            }
            elseif($payload['tipoComprobante'] == '08') {
                $num_consecutivo =$emisor->consecutivoFECtest;
            }
            elseif($payload['tipoComprobante'] == '09') {
                $num_consecutivo =$emisor->consecutivoFEEtest;
            }
        }
        elseif ($entorno=='prod')
        {
            if ($payload['tipoComprobante'] == '01') {
                $num_consecutivo = $emisor->consecutivoFEprod;
            } elseif ($payload['tipoComprobante'] == '02') {
                $num_consecutivo = $emisor->consecutivoNDprod;
            } elseif ($payload['tipoComprobante'] == '03') {
                $num_consecutivo = $emisor->consecutivoNCprod;
            } elseif ($payload['tipoComprobante'] == '04') {
                $num_consecutivo = $emisor->consecutivoTEprod;
            }
            elseif($payload['tipoComprobante'] == '08') {
                $num_consecutivo =$emisor->consecutivoFECprod;
            }
            elseif($payload['tipoComprobante'] == '09') {
                $num_consecutivo =$emisor->consecutivoFEEprod;
            }
        }
        if($payload['tipoComprobante']!='01' AND $payload['tipoComprobante']!='02' AND $payload['tipoComprobante']!='03' AND $payload['tipoComprobante']!='04' AND $payload['tipoComprobante']!='08' AND $payload['tipoComprobante']!='09')
        {
            return response()->json(array("code"=>"0","data"=>"Tipo de comprobante inválido","fecha"=>$fecha,"tipoComprobante"=>$payload['tipoComprobante']), 400);
        }
        $num_consecutivo++;

        $consecutivo=str_pad($num_consecutivo,10,"0",STR_PAD_LEFT);
        if(isset($payload['sucursal']))
        {
            if(strlen($payload['sucursal'])<4 AND is_numeric($payload['sucursal']))
            {
                $sucursal=str_pad($payload['sucursal'],3,"0",STR_PAD_LEFT);
            }
            else{
                return response()->json(array("code"=>"0","data"=>"El formato de la sucursal es inválido","fecha"=>$fecha,"sucursal"=>$payload['sucursal']), 400);
            }

        }
        if(isset($payload['terminal']))
        {
            if(strlen($payload['terminal'])<6 AND is_numeric($payload['terminal']))
            {
                $terminal=str_pad($payload['terminal'],5,"0",STR_PAD_LEFT);
            }
            else{
                return response()->json(array("code"=>"0","data"=>"El formato de la terminal es inválido","fecha"=>$fecha,"terminal"=>$payload['terminal']), 400);
            }
        }
        if(isset($payload['codPais']))
        {
            if(strlen($payload['codPais'])<4 AND is_numeric($payload['codPais']))
            {
                $codigoPais=str_pad($payload['codPais'],3,"0",STR_PAD_LEFT);
            }
            else{
                return response()->json(array("code"=>"0","data"=>"El formato del código de país es inválido","fecha"=>$fecha,"codPais"=>$payload['codPais']), 400);
            }

        }
        $consecutivoFinal = $sucursal . $terminal . $payload['tipoComprobante'] . $consecutivo;

        if(strlen($emisor->id)<9 OR strlen($emisor->id)>12)
        {
            return response()->json(array("code"=>"0","data"=>"La identificación del Emisor debe ser mínimo de 9 y máximo 12 caracteres","Emisor"=>$emisor), 400);
        }
        if(strlen($emisor->id)==9)
        {
            $identificacion="000".$emisor->id;
        }
        elseif(strlen($emisor->id)==10)
        {
            $identificacion="00".$emisor->id;
        }
        elseif(strlen($emisor->id)==11)
        {
            $identificacion="0".$emisor->id;
        }
        elseif(strlen($emisor->id)==12)
        {
            $identificacion=$emisor->id;
        }

        if(!isset($payload['codSeguridad']))
        {
            $seguridad=$this->generateSecurityCod(8);
        }
        else{
            if(strlen($payload['codSeguridad'])!=8 OR !is_numeric($payload['codSeguridad']))
            {
                return response()->json(array("code"=>"0","data"=>"El formato del código de seguridad es incorrecto","codSeguridad"=>$payload['codSeguridad']), 400);
            }
            $seguridad=$payload['codSeguridad'];
        }
        $clave = $codigoPais . $dia . $mes . $ano . $identificacion . $consecutivoFinal . $payload['situacion'] . $seguridad;
        try{
            $this->updateConsecutive($emisor,$payload['tipoComprobante'],$entorno,$num_consecutivo);

        }
        catch(\Exception $exception)
        {
            return response()->json(array("code"=>"0","msj"=>"Error al actualizar el consecutivo","data"=>$exception->getMessage()), 500);
        }

        return response()->json(array("code"=>"1","data"=>$clave), 200);
    }
    function generateSecurityCod($length) {
        $result = '';
        for($i = 0; $i < $length; $i++) {
            $result .= mt_rand(0, 9);
        }
        return $result;
    }
    public function saveReceipt($xml,$signedXml,$http_hacienda,$respuesta_api,$estado,$cc)
    {
    try {
        $stringXML = base64_decode($xml);
        $xmlObj = simplexml_load_string($stringXML);
        $comprobante = new Comprobante;
        $comprobante->fecha_emision = date('Y-m-d H:i:s', strtotime($xmlObj->FechaEmision));
        $comprobante->tp_receptor='00';
        if(isset($xmlObj->Receptor))
        {
            $comprobante->tp_receptor = $xmlObj->Receptor->Identificacion->Tipo;
            $comprobante->id_receptor = $xmlObj->Receptor->Identificacion->Numero;
        }
        $comprobante->clave = $xmlObj->Clave;
        $comprobante->numeracion = $xmlObj->NumeroConsecutivo;
        $comprobante->codigo_moneda = $xmlObj->ResumenFactura->CodigoMoneda;
        $comprobante->tp_cambio = $xmlObj->ResumenFactura->TipoCambio;
        if(empty($comprobante->tp_cambio))
        {
            $comprobante->tp_cambio=1;
            $comprobante->codigo_moneda="CRC";
        }

        $comprobante->nombre_receptor = $xmlObj->Receptor->Nombre;
        $comprobante->id_emisor=$xmlObj->Emisor->Identificacion->Numero;
        $comprobante->tp_comprobante=substr((string)$xmlObj->Clave, 29, 2);
        if($comprobante->tp_comprobante=='08')
        {
            $comprobante->id_emisor='000000000';
        }
        //$tipoComprobante = substr($payload['clave'], 29, 2);
        //$comprobante->json_recibido=$json_payload;

        $comprobante->xml_firmado = $signedXml;
        //$comprobante->xml_result=$resultXml;
        $comprobante->subtotal_comprobante = $xmlObj->ResumenFactura->TotalVenta;
        $comprobante->total_impuestos=0;
        if(isset($xmlObj->ResumenFactura->TotalImpuesto) AND $xmlObj->ResumenFactura->TotalImpuesto!='')
        {
            $comprobante->total_impuestos = $xmlObj->ResumenFactura->TotalImpuesto;
        }

        $comprobante->total_comprobante = $xmlObj->ResumenFactura->TotalComprobante;
        $comprobante->http_hacienda = $http_hacienda;
        $comprobante->respuesta_api = $respuesta_api;
        $comprobante->estado=$estado;
        $comprobante->email_receptor=$xmlObj->Receptor->CorreoElectronico;
        $comprobante->tp_condicion_venta=$xmlObj->CondicionVenta;
        $comprobante->tp_medio_pago=$xmlObj->MedioPago;
        $comprobante->cc=null;
        if(isset($cc) AND $cc!='')
        {
            $comprobante->cc=$cc;
        }
        //INFORMACION EN CASO DE TENER NODO DE REFERENCIA
        if($xmlObj->InformacionReferencia) {
            $comprobante->tp_doc_referencia = $xmlObj->InformacionReferencia->TipoDoc;
            $comprobante->numero_referencia = $xmlObj->InformacionReferencia->Numero;
            $comprobante->fecha_emision_ref = date('Y-m-d H:i:s', strtotime($xmlObj->InformacionReferencia->FechaEmision));
            $comprobante->tp_accion_referencia = $xmlObj->InformacionReferencia->Codigo;
            $comprobante->razon_referencia = $xmlObj->InformacionReferencia->Razon;
        }
        $comprobante->save();
        $this->saveDetFact($xmlObj->DetalleServicio->LineaDetalle,$comprobante->id);
    }
    catch (\Exception $exception)
    {
        echo $exception->getMessage();
    }


    }

    public function saveDetFact($detalle,$id_comprobante)
    {
        try {
            $tamano = sizeof($detalle);
            for ($c = 0; $c < $tamano; $c++)
            {
                $detalleComprobante = new DetalleComprobante;
                $detalleComprobante->id_comprobante = $id_comprobante;
                $detalleComprobante->numero_linea = $detalle[$c]->NumeroLinea;
                $detalleComprobante->tp_articulo = "";
                if(isset($detalle[$c]->CodigoComercial->Tipo) AND $detalle[$c]->CodigoComercial->Tipo!='')
                {
                    $detalleComprobante->tp_articulo=$detalle[$c]->CodigoComercial->Tipo;
                }
                $detalleComprobante->cantidad = $detalle[$c]->Cantidad;
                $detalleComprobante->unidad_medida = $detalle[$c]->UnidadMedida;
                $detalleComprobante->unidad_medida_comercial = "";
                if(isset($detalle[$c]->UnidadMedidaComercial) AND $detalle[$c]->UnidadMedidaComercial!='')
                {
                    $detalleComprobante->unidad_medida_comercial=$detalle[$c]->UnidadMedidaComercial;
                }
                $detalleComprobante->detalle =$detalle[$c]->Detalle;
                $detalleComprobante->precio_unitario = $detalle[$c]->PrecioUnitario;
                $detalleComprobante->monto_total = $detalle[$c]->MontoTotalLinea;
                $detalleComprobante->monto_descuento = 0;
                if(isset($detalle[$c]->Descuento->MontoDescuento) AND $detalle[$c]->Descuento->MontoDescuento!='' AND $detalle[$c]->Descuento->MontoDescuento!='0')
                {
                    $detalleComprobante->monto_descuento=$detalle[$c]->Descuento->MontoDescuento;
                }
                $detalleComprobante->naturaleza_descuento = "";
                if(isset($detalle[$c]->Descuento->NaturalezaDescuento) AND $detalle[$c]->Descuento->NaturalezaDescuento!='')
                {
                    $detalleComprobante->naturaleza_descuento=$detalle[$c]->Descuento->NaturalezaDescuento;
                }
                $detalleComprobante->subtotal =$detalle[$c]->SubTotal;
                $detalleComprobante->monto_total_linea = $detalle[$c]->MontoTotalLinea;
                $detalleComprobante->save();
                if(isset($detalle[$c]->Impuesto)) {
                    $this->saveDetImp($detalle[$c]->Impuesto,$detalleComprobante->id_comprobante, $detalleComprobante->id);
                }
            }
            return "ok";
        }catch (\Exception $exception)
        {
            echo $exception->getMessage();
            exit;
        }
    }
    public function saveDetImp($detalleImp,$id_comprobante,$id_linea_detalle)
    {
        try{
            $tamano = sizeof($detalleImp);
            for ($c = 0; $c < $tamano; $c++)
            {
                $detalleImpuesto = new DetalleImpuesto;
                $detalleImpuesto->id_comprobante=$id_comprobante;
                $detalleImpuesto->id_linea=$id_linea_detalle;
                $detalleImpuesto->id_impuesto=$detalleImp[$c]->Codigo;
                $detalleImpuesto->tarifa=$detalleImp[$c]->Tarifa;
                $detalleImpuesto->codigo_tarifa=$detalleImp[$c]->CodigoTarifa;
                $detalleImpuesto->monto=$detalleImp[$c]->Monto;
                if(isset($detalleImp[$c]->Exoneracion))
                {
                    $detalleImpuesto->tp_documento_exo = $detalleImp[$c]->Exoneracion->TipoDocumento;
                    $detalleImpuesto->numero_doc_exo = $detalleImp[$c]->Exoneracion->NumeroDocumento;
                    $detalleImpuesto->institucion_exo = $detalleImp[$c]->Exoneracion->NombreInstitucion;
                    $detalleImpuesto->fecha_emision_exo = date('Y-m-d H:i:s', strtotime($detalleImp[$c]->Exoneracion->FechaEmision));
                    $detalleImpuesto->monto_impuesto_exo = $detalleImp[$c]->Exoneracion->MontoExoneracion;
                    $detalleImpuesto->porcentaje_compra_exo = $detalleImp[$c]->Exoneracion->PorcentajeExoneracion;
                }
                $detalleImpuesto->save();
            }
        }catch (Exception $exception)
        {
            echo $exception->getMessage();
        }
    }
    public function reporteIvaMensual(Request $request)
    {
        //require '../vendor/autoload.php';

        $comprobante = null;
        /*if(!isset($request['api_key']) OR empty($request['api_key']))
        {
            return response()->json(array("code"=>"3","data"=>"Requiere que se incluya el API KEY dentro de los parámetros para
poder realizar el proceso."), 400);
        }
        if(!isset($request['entorno']) OR empty($request['entorno']))
        {
            return response()->json(array("code"=>"10","data"=>"Requiere que se incluya el entorno dentro de los parámetros del encabezado de la solicitud."), 400);
        }*/
        $payload = $request->json()->all();
        if (!isset($request['id']) OR empty($request['id'])) {
            return response()->json(array("code" => "10", "data" => "Requiere que se incluya la cédula del obligado tributario para procesar la solicitud."), 400);
        }
        if (!isset($request['f_inicio']) OR $request['f_inicio'] == '') {
            return response()->json(array("code" => "10", "data" => "Requiere que se incluya la fecha de inicio para descargar el reporte."), 400);
        }
        if (!isset($request['f_fin']) OR $request['f_fin'] == '') {
            return response()->json(array("code" => "10", "data" => "Requiere que se incluya la fecha fin para descargar el reporte."), 400);
        }
        $id_cliente = $request['id'];
        /*$fecha_inicio='2019-07-01 00:00:00';
        $fecha_fin='2019-08-15 23:59:59';*/

        $fecha_inicio = str_replace('/', '-', $request['f_inicio']);
        $fecha_fin = str_replace('/', '-', $request['f_fin']);
        $fecha_fin = str_replace('00:00:00', '', $fecha_fin) . " 23:59:59";

        $comprobante = DB::table('COMPROBANTES')->select('COMPROBANTES.xml_firmado', 'COMPROBANTES.estado', 'COMPROBANTES.nombre_receptor')->where('COMPROBANTES.id_emisor', '=', $id_cliente)->whereDate('fecha_emision', '>', $fecha_inicio)->whereDate('fecha_emision', '<=', $fecha_fin)->get();
        if (!$comprobante) {
            return response()->json(array("code" => "4", "data" => "Fallo en el proceso de autentificación por un API KEY incorrecto o el obligado tributario no ha emitido comprobantes", "X-Api-Key" => $request->header('X-Api-Key')), 401);
        }

        $reporte = array();
        $cuenta = 1;
        $sumIva = 0;
        $sumTotal = 0;
        foreach ($comprobante as $c) {
            $cargaXml = simplexml_load_string(base64_decode($c->xml_firmado));
            $toJson = json_encode($cargaXml);
            $xml = json_decode($toJson);


            $detalle_venta = array();
            /*foreach ($xml->DetalleServicio->LineaDetalle as $d){

            }*/
            $moneda = "CRC";
            $tipo_cambio = "";
            $receptor = "SIN RECEPTOR";
            $totalImpuesto = 0;
            if (isset($c->nombre_receptor) AND $c->nombre_receptor != '') {
                $receptor = $c->nombre_receptor;
            }
            if (isset($xml->ResumenFactura->CodigoTipoMoneda->CodigoMoneda) AND $xml->ResumenFactura->CodigoTipoMoneda->CodigoMoneda != '') {
                $moneda = $xml->ResumenFactura->CodigoTipoMoneda->CodigoMoneda;
            }
            if (isset($xml->ResumenFactura->CodigoTipoMoneda->TipoCambio) AND $xml->ResumenFactura->CodigoTipoMoneda->TipoCambio != '') {
                $tipo_cambio = $xml->ResumenFactura->CodigoTipoMoneda->TipoCambio;
            }
            if (isset($xml->ResumenFactura->TotalImpuesto) AND $xml->ResumenFactura->TotalImpuesto != '') {
                $totalImpuesto = $xml->ResumenFactura->TotalImpuesto;
            }

            $r = array("Identificación" => $xml->Emisor->Identificacion->Numero, "clave" => $xml->Clave, "consecutivo" => $xml->NumeroConsecutivo, "fecha" => date('Y-m-d H:i:s', strtotime($xml->FechaEmision)), "Total Impuesto" => $totalImpuesto, "Total Venta" => $xml->ResumenFactura->TotalComprobante, "Moneda" => $moneda, "Tipo cambio" => $tipo_cambio, "estado" => $c->estado, "receptor" => $receptor);

            array_push($reporte, $r);


        }
        //$gastos = DB::table('GASTOS')->select('GASTOS.id_receptor', 'GASTOS.xml_completo', 'COMPROBANTES.estado')->where('GASTOS.id_receptor', '=', $id_cliente)->whereDate('fecha_emision', '>', $fecha_inicio)->whereDate('fecha_emision', '<=', $fecha_fin)->get();
        $gastos = DB::table('GASTOS')->select('GASTOS.id_receptor', 'GASTOS.xml_completo', 'GASTOS.estado','GASTOS.total_acred','GASTOS.total_apli','GASTOS.location','GASTOS.consecutivo_recepcion','GASTOS.total_impuestos','GASTOS.total_comprobante','GASTOS.identificacion_emisor')->where('GASTOS.id_receptor', '=', $id_cliente)->where('GASTOS.location','NOT LIKE','%sandbox%') ->get();
        $sumAcreditado=0;
        $sumAplicable=0;
        if (!$gastos) {
            return response()->json(array("code" => "4", "data" => "Fallo en el proceso de autentificación por un API KEY incorrecto o el obligado tributario no ha emitido comprobantes", "X-Api-Key" => $request->header('X-Api-Key')), 401);
        }
        /*foreach ($gastos as $g)
        {
            if($g->location!='')
            {
                $sumAcreditado+=floatval($g->total_acred);
                $sumAplicable+=floatval($g->total_apli);
            }

        }*/

        $url_reporte=$this->makeIvaReport($reporte);
        $url_gasto=$this->makeIvaReportGastos($gastos);
        //return \response()->download($url_reporte)->deleteFileAfterSend(true);
    }
    public function makeIvaReport($datos)
    {

        try{
            $comprobante="Reporte Mensual IVA de Ventas";

            $view =  \View::make('reportes/iva_mensual', compact('datos', 'datos', 'iva_mensual'))->render();
            $pdf = \App::make('dompdf.wrapper');
            $pdf->loadHTML($view)->save('../storage/app/temp_pdf/'.$comprobante.'.pdf');
            return '../storage/app/temp_pdf/'.$comprobante.'.pdf';
            //$pdf->loadHTML($view);

            //return $pdf->stream('whateveryourviewname.pdf');
            //return "ok";
            //return $pdf->download('facturilla.pdf');
            //return $pdf->stream('invoice.pdf');
            //return $pdf->download('invoice.pdf');
            //return $pdf->output();
            //$pdf = PDF::loadView('invoice', $data);
            //$pdf->download('invoice.pdf');

        }
        catch (Exception $e)
        {
            return $e->getMessage();
        }

        //return $pdf->stream('invoice');
    }
    public function makeIvaReportGastos($datos)
    {
        try{
            $comprobante="Reporte Mensual IVA de Gastos";

            $view =  \View::make('reportes/iva_mensual_gastos', compact('datos', 'datos', 'iva_mensual_gastos'))->render();
            $pdf = \App::make('dompdf.wrapper');
            $pdf->loadHTML($view)->save('../storage/app/temp_pdf/'.$comprobante.'.pdf');
            return '../storage/app/temp_pdf/'.$comprobante.'.pdf';
            //$pdf->loadHTML($view);

            //return $pdf->stream('whateveryourviewname.pdf');
            //return "ok";
            //return $pdf->download('facturilla.pdf');
            //return $pdf->stream('invoice.pdf');
            //return $pdf->download('invoice.pdf');
            //return $pdf->output();
            //$pdf = PDF::loadView('invoice', $data);
            //$pdf->download('invoice.pdf');

        }
        catch (Exception $e)
        {
            return $e->getMessage();
        }

        //return $pdf->stream('invoice');
    }
    public function makeInvoice($clave)
    {
        try{
            $data = $this->getData($clave);
            $date = date('Y-m-d');
            $invoice = "2222";
            $comprobante="";
            $tipoComprobante = substr($clave, 29, 2);
            if ($tipoComprobante == "01") {
                $comprobante="Factura-Electrónica";
            } elseif ($tipoComprobante == "02") {
                $comprobante="Nota-de-Débito-Electrónica";
            } elseif ($tipoComprobante == "03") {
                $comprobante="Nota-de-Crédito-Electrónica";
            } elseif ($tipoComprobante == "04") {
                $comprobante="Tiquete-Electrónico";
            }

            $view =  \View::make('invoice', compact('data', 'date', 'invoice'))->render();
            $pdf = \App::make('dompdf.wrapper');
            $pdf->loadHTML($view)->save('../storage/app/temp_pdf/'.$comprobante.'-'.$clave.'.pdf');
            return '../storage/app/temp_pdf/'.$comprobante.'-'.$clave.'.pdf';
            //$pdf->loadHTML($view);

            //return $pdf->stream('whateveryourviewname.pdf');
            //return "ok";
            //return $pdf->download('facturilla.pdf');
            //return $pdf->stream('invoice.pdf');
            //return $pdf->download('invoice.pdf');
            //return $pdf->output();
            //$pdf = PDF::loadView('invoice', $data);
             //$pdf->download('invoice.pdf');

        }
        catch (Exception $e)
        {
            return $e->getMessage();
        }

        //return $pdf->stream('invoice');
    }
    public function getData($clave)
    {
        $comprobante=DB::table('COMPROBANTES')->join('EMISORES', 'EMISORES.id', '=', 'COMPROBANTES.id_emisor') ->select('COMPROBANTES.xml_firmado','EMISORES.logo')->where('clave','=',$clave)->first();
        $cargaXml = simplexml_load_string(base64_decode($comprobante->xml_firmado));
        $toJson=json_encode($cargaXml);
        $xml=json_decode($toJson);
        $xml->logo=$comprobante->logo;
        return $xml;
    }

    public function getFacturaXml(Request $request)
    {
        $clave=$request->get('clave');
        $comprobante=DB::table('COMPROBANTES')->select('COMPROBANTES.xml_firmado','COMPROBANTES.tp_comprobante','COMPROBANTES.clave')->where('clave','=',$payload['clave'])->first();

        $tp_comprobante="";
        if ($comprobante->tp_comprobante==1)
        {
            $path_archivo =  storage_path('app/temp_xml/ATV_FAC_Firmada-'.$comprobante->clave.'.xml');
            $tp_comprobante="FE emitida por";
        }
        elseif ($comprobante->tp_comprobante==2)
        {
            $path_archivo = storage_path('app/temp_xml/ATV_ND_Firmada-'.$comprobante->clave.'.xml');
            $tp_comprobante="ND emitida por";
        }
        elseif ($comprobante->tp_comprobante==3)
        {
            $path_archivo = storage_path('app/temp_xml/ATV_NC_Firmada-'.$comprobante->clave.'.xml');
            $tp_comprobante="NC emitida por";
        }
        elseif ($comprobante->tp_comprobante==4)
        {
            $path_archivo = storage_path('app/temp_xml/ATV_TE_Firmada-'.$comprobante->clave.'.xml');
            $tp_comprobante="TE emitido por";
        }
        elseif ($comprobante->tp_comprobante==8)
        {
            $path_archivo = storage_path('app/temp_xml/ATV_FEC_Firmada-'.$comprobante->clave.'.xml');
            $tp_comprobante="FEC emitida por";
        }

        if ($archivo1 = fopen($path_archivo, "a"))
        {
            fwrite($archivo1,$comprobante->xml_firmado);
            fclose($archivo1);
        }
        return \response()->download($path_archivo)->deleteFileAfterSend(true);
    }

    /*public function getRespuestaHaciendaXml($email)
    {
        $tp_comprobante="";
        if ($email->getTipoComprobante()==1)
        {
            $path_archivo = storage_path('app/temp_xml/ATV_FAC_Respuesta-'.$email->getClave().'.xml');
            $tp_comprobante="FE emitida por";
        }
        elseif ($email->getTipoComprobante()==2)
        {
            $path_archivo = storage_path('app/temp_xml/ATV_ND_Respuesta-'.$email->getClave().'.xml');
            $tp_comprobante="ND emitida por";
        }
        elseif ($email->getTipoComprobante()==3)
        {
            $path_archivo = storage_path('app/temp_xml/ATV_NC_Respuesta-'.$email->getClave().'.xml');
            $tp_comprobante="NC emitida por";
        }
        elseif ($email->getTipoComprobante()==4)
        {
            $path_archivo = storage_path('app/temp_xml/ATV_TE_Respuesta-'.$email->getClave().'.xml');
            $tp_comprobante="TE emitido por";
        }
        elseif ($email->getTipoComprobante()==8)
        {
            $path_archivo = storage_path('app/temp_xml/ATV_FEC_Respuesta-'.$email->getClave().'.xml');
            $tp_comprobante="FEC emitida por";
        }

        if ($archivo2 = fopen($path_archivo, "a")) {
            fwrite($archivo2,$email->getXmlRespuestaHacienda());
            fclose($archivo2);
        }
        return \response()->download($path_archivo)->deleteFileAfterSend(true);
    }*/

    public function sendEmail($email,$smtp_opcional,$api_key,$PDF,$cc)
    {
        $HOST=env('SMTP_HOST');
        $USERNAME=env('SMTP_USERNAME');
        $PASSWORD=env('SMTP_CONTRASENA');
        $METODO=env('SMTP_METODO');
        $PUERTO=env('SMTP_PUERTO');
        $correos_cc=array();
        $tp_comprobante="";
        if ($email->getTipoComprobante()==1)
        {
            $nombre_archivo1 =  storage_path('app/temp_xml/ATV_FAC_Firmada-'.$email->getClave().'.xml');
            $nombre_archivo2 = storage_path('app/temp_xml/ATV_FAC_Respuesta-'.$email->getClave().'.xml');
            $tp_comprobante="FE emitida por";
        }
        elseif ($email->getTipoComprobante()==2)
        {
            $nombre_archivo1 = storage_path('app/temp_xml/ATV_ND_Firmada-'.$email->getClave().'.xml');
            $nombre_archivo2 = storage_path('app/temp_xml/ATV_ND_Respuesta-'.$email->getClave().'.xml');
            $tp_comprobante="ND emitida por";
        }
        elseif ($email->getTipoComprobante()==3)
        {
            $nombre_archivo1 = storage_path('app/temp_xml/ATV_NC_Firmada-'.$email->getClave().'.xml');
            $nombre_archivo2 = storage_path('app/temp_xml/ATV_NC_Respuesta-'.$email->getClave().'.xml');
            $tp_comprobante="NC emitida por";
        }
        elseif ($email->getTipoComprobante()==4)
        {
            $nombre_archivo1 = storage_path('app/temp_xml/ATV_TE_Firmada-'.$email->getClave().'.xml');
            $nombre_archivo2 = storage_path('app/temp_xml/ATV_TE_Respuesta-'.$email->getClave().'.xml');
            $tp_comprobante="TE emitido por";
        }
        elseif ($email->getTipoComprobante()==8)
        {
            $nombre_archivo1 = storage_path('app/temp_xml/ATV_FEC_Firmada-'.$email->getClave().'.xml');
            $nombre_archivo2 = storage_path('app/temp_xml/ATV_FEC_Respuesta-'.$email->getClave().'.xml');
            $tp_comprobante="FEC emitida por";
        }

        if ($archivo1 = fopen($nombre_archivo1, "a"))
        {
            fwrite($archivo1,$email->getXmlFirmado());
            fclose($archivo1);
        }
        if ($archivo2 = fopen($nombre_archivo2, "a")) {
            fwrite($archivo2,$email->getXmlRespuestaHacienda());
            fclose($archivo2);
        }

        //Load Composer's autoloader
        require '../vendor/autoload.php';
        $sub_ject=$tp_comprobante." ".$email->getNombreEmi();
        date_default_timezone_set('America/Costa_Rica');
        $n_comercial=$email->getNComercial();
        if(isset($n_comercial) AND $email->getNComercial()!='')
        {
            $sub_ject=$tp_comprobante." ".$n_comercial;
        }
        if($smtp_opcional)
        {
            $emisor=DB::table('EMISORES')->select('EMISORES.host_smtp_secundario','EMISORES.usuario_smtp_secundario','EMISORES.contrasena_smtp_secundario','EMISORES.metodo_smtp_secundario','EMISORES.puerto_smtp_secundario')->where('EMISORES.api_key','=',$api_key)->first();
            $HOST=$emisor->host_smtp_secundario;
            $USERNAME=$emisor->usuario_smtp_secundario;
            $PASSWORD=$emisor->contrasena_smtp_secundario;
            $METODO=$emisor->metodo_smtp_secundario;
            $PUERTO=$emisor->puerto_smtp_secundario;
        }

        $mail = new PHPMailer(TRUE);
        try {
            $mail->SMTPDebug = 0;                                 // Enable verbose debug output
            $mail->isSMTP();                                      // Set mailer to use SMTP
            $mail->Host = $HOST;  // Specify main and backup SMTP servers
            $mail->SMTPAuth = true;                               // Enable SMTP authentication
            $mail->Username = $USERNAME;                 // SMTP username
            $mail->Password = $PASSWORD;                           // SMTP password
            $mail->SMTPSecure = $METODO;                            // Enable TLS encryption, `ssl` also accepted
            $mail->Port = $PUERTO;                                    // TCP port to connect to
            $mail->SMTPOptions = array(
                'ssl' => array(
                    'verify_peer' => false,
                    'verify_peer_name' => false,
                    'allow_self_signed' => true
                )
            );
            $mail->setFrom($USERNAME,$email->getNombreEmi());
            $mail->addAddress($email->getEmailReceptor(), $email->getNombreReceptor());     // JOE User sería el nombre del receptor
            //$mail->addAddress('eliope_alb@yahoo.com');               // Name is optional
            //$mail->addReplyTo('pcwebsoft75@example.com', 'Information');
            //$mail->addCC('cc@example.com');
            //$mail->addBCC('bcc@example.com');
            // Add attachments
            $mail->CharSet = "UTF-8";
            $mail->addAttachment($nombre_archivo1, 'ATV-Comprobante-'.$email->getNumeracion().'.xml');   // Optional name
            $mail->addAttachment($nombre_archivo2, 'ATV-Respuesta-Hacienda-'.$email->getNumeracion().'.xml');
            if($PDF) {
                //Construye y adjunta el PDF.
                $nombre_archivo3 = $this->makeInvoice($email->getClave());
                $mail->addAttachment($nombre_archivo3, 'ATV-Comprobante-Electrónico-' . $email->getClave() . '.pdf');

            }
            if(isset($cc) AND $cc!='' AND strpos($cc,'@'))
            {
                $correos_cc=explode(';',$cc);
                foreach ($correos_cc as $c)
                {
                    if(strpos($c,'@'))
                    {
                        $mail->addCC($c);
                    }
                }
            }
            $mail->isHTML(true);                                  // Set email format to HTML
            $mail->Subject = $sub_ject;
            $mail->Body = '<html><head><meta http-equiv="Content-Type" content="text/html; charset=utf-8"/></head><body><p>Adjunto a este correo encontrará el comprobante electrónico en formato XML y su correspondiente visualización en formato PDF, por concepto de facturación de '.$email->getNombreEmi().'. Lo anterior con base en las especificaciones del Ministerio de Hacienda.
<br>Por favor NO responder este mensaje.</p></body></html>';
            $mail->send();
            // echo 'Message has been sent';
        } catch (Exception $e) {
            echo json_encode(array("php_mailer_exception"=>$mail->ErrorInfo));
            exit;
            //echo "Error";
        }
        unlink($nombre_archivo1);
        unlink($nombre_archivo2);
        if(isset($nombre_archivo3))
        {
            unlink($nombre_archivo3);
        }

    }
    public function sendXml(Request $request)
    {
        $emisor=null;
        $fecha=date(DATE_RFC3339);
        $api_key=$request->header('X-Api-Key');
        $entorno=$request->header('entorno');
        $easyResponse=null;
        $cc=null;
        if(!isset($api_key) OR empty($api_key))
        {
            return response()->json(array("code"=>"3","data"=>"Requiere que se incluya el API KEY dentro de los parámetros para
poder realizar el proceso.","fecha"=>$fecha), 400);
        }
        if(!isset($entorno) OR empty($entorno))
        {
            return response()->json(array("code"=>"10","data"=>"Requiere que se incluya el entorno dentro de los parámetros del encabezado de la solicitud.","fecha"=>$fecha), 400);
        }

        $payload=$request->json()->all();
        if(isset($payload['cc']) AND $payload['cc']!='')
        {
            if(substr_count($payload['cc'],'@')>2)
            {
                return response()->json(array("code"=>"0","data"=>"Error. El máximo de copias por comprobante es de 3.","cc"=>$payload['cc']), 400);
            }
            $cc=$payload['cc'];
        }
        if($entorno=='stag')
        {
            $emisor=DB::table('EMISORES')->select('EMISORES.id','EMISORES.api_key','EMISORES.usuario_atv_test','EMISORES.contrasena_atv_test','EMISORES.certificado_atv_test','EMISORES.pin_atv_test','EMISORES.consecutivoFEtest','EMISORES.consecutivoTEtest','EMISORES.consecutivoNDtest','EMISORES.consecutivoNCtest','EMISORES.consecutivoFECtest','EMISORES.consecutivoFEEtest')->where('EMISORES.api_key','=',$api_key)->first();
            if(!$emisor)
            {
                return response()->json(array("code"=>"4","data"=>"Fallo en el proceso de autentificación por un API KEY incorrecto","X-Api-Key"=>$request->header('X-Api-Key')), 401);
            }

                $pathCertificado="cer_sandbox/".$emisor->certificado_atv_test;
                $easyResponse=$this->conectToEasyATV(array("atvUsername"=>$emisor->usuario_atv_test,
                    "atvPassword"=>$emisor->contrasena_atv_test,
                    "certificateLocation"=>$pathCertificado,
                    "certificatePassword"=>$emisor->pin_atv_test,"xmlToSign"=>$payload['xml']));
        }
        elseif($entorno=='prod')
        {
            $emisor=DB::table('EMISORES')->select('EMISORES.id','EMISORES.api_key','EMISORES.usuario_atv_prod','EMISORES.contrasena_atv_prod','EMISORES.certificado_atv_prod','EMISORES.pin_atv_prod','EMISORES.consecutivoFEprod','EMISORES.consecutivoTEprod','EMISORES.consecutivoNDprod','EMISORES.consecutivoNCprod','EMISORES.consecutivoFECprod','EMISORES.consecutivoFEEprod')->where('EMISORES.api_key','=',$api_key)->first();
            if(!$emisor)
            {
                return response()->json(array("code"=>"4","data"=>"Fallo en el proceso de autentificación por un API KEY incorrecto","X-Api-Key"=>$request->header('X-Api-Key')), 401);
            }

                $pathCertificado="cer/".$emisor->certificado_atv_prod;
                $easyResponse=$this->conectToEasyATV(array("atvUsername"=>$emisor->usuario_atv_prod,
                    "atvPassword"=>$emisor->contrasena_atv_prod,
                    "certificateLocation"=>$pathCertificado,
                    "certificatePassword"=>$emisor->pin_atv_prod,"xmlToSign"=>$payload['xml']));
        }
        else{
            return response()->json(array("code"=>"26","data"=>"Ambiente incorrecto. Ambientes disponibles:[stag] para pruebas y [prod] para producción","entorno"=>$entorno), 401);
        }
        if(isset($easyResponse['response']->error))
        {
            return response()->json(array("code"=>"0","data"=>$easyResponse), 400);
        }
        try{
            $this->saveReceipt($payload['xml'],$easyResponse['response']->signedXml,$easyResponse['http_status_easy'],"1",$easyResponse['response']->estado,$cc);
            /*if($this->updateConsecutive($emisor,substr($payload['clave'], 29, 2),$entorno)){
                return response()->json(array("code"=>"1","data"=>$easyResponse,"fecha"=>$fecha), 200);
            }*/
            //return response()->json(array("code"=>"0","data"=>"Ha ocurrido un error al actualizar el consecutivo","fecha"=>$fecha), 500);
            return response()->json(array("code"=>"1","data"=>$easyResponse,"fecha"=>$fecha), 200);
        }
        catch (\Exception $exception)
        {
            return response()->json(array("code"=>"0","data"=>"Ha ocurrido un error al actualizar el consecutivo:".$exception->getMessage(),"fecha"=>$fecha), 500);
        }


    }
    public function makeXml(Request $request)
    {
        $emisor=null;
        $fecha=date(DATE_RFC3339);
        $api_key=$request->header('X-Api-Key');
        if(!isset($api_key) OR empty($api_key))
        {
            return response()->json(array("code"=>"3","data"=>"Requiere que se incluya el API KEY dentro de los parámetros para
poder realizar el proceso.","fecha"=>$fecha), 400);
        }
        $payload=$request->json()->all();
        //$emisor=DB::table('EMISORES')->select('EMISORES.api_key')->where('EMISORES.api_key','=',$api_key)->count();
        $emisor=DB::table('EMISORES')->select('EMISORES.api_key','EMISORES.razon_social','EMISORES.nombre_comercial','EMISORES.id_tpidentificacion','EMISORES.id','UBICACIONES.cod_pro AS provincia','UBICACIONES.cod_can AS canton','UBICACIONES.cod_dis AS distrito','UBICACIONES.cod_barrio AS barrio','EMISORES.otras_senas AS sennas','EMISORES.telefono','EMISORES.fax','EMISORES.correo AS correo_electronico')->join('UBICACIONES','EMISORES.id_ubicacion','=','UBICACIONES.id')->where('EMISORES.api_key','=',$api_key)->first();

        if(!$emisor)
        {
            return response()->json(array("code"=>"4","data"=>"Fallo en el proceso de autentificación por un API KEY incorrecto","X-Api-Key"=>$request->header('X-Api-Key')), 401);
        }
        $rules = [
            'receptor.correo_electronico' => ['email'],
            'receptor.telefono.numero'=>['numeric'],
            'resumen.moneda'=>['regex:/^[a-zA-Z]+$/u','min:3','max:3'],
            'encabezado.mediopago'=>['regex:/^[0-5-99]+$/u','min:2','max:2'],
            'otros.contenido'=>['max:700'],
            'codigo_actividad'=>['required','max:6'],
            'receptor.nombre'=>['max:100'],
            'receptor.ubicacion.sennas'=>['max:250']

        ];
        $customMessages = [
            'receptor.correo_electronico.email' => 'Correo electrónico R incorrecto *11',
            'receptor.telefono.numero.numeric'=>'Número telefónico receptor incorrecto, solo acepta valores numéricos *12',
            'resumen.moneda.regex'=>'El formato de la moneda es incorrecto *17',
            'resumen.moneda.min'=>'El formato de la moneda es incorrecto *17',
            'resumen.moneda.max'=>'El formato de la moneda es incorrecto *17',
            'encabezado.mediopago.regex'=>'El formato del medio de pago es incorrecto *20',
            'encabezado.mediopago.min'=>'El formato del medio de pago es incorrecto *20',
            'encabezado.mediopago.max'=>'El formato del medio de pago es incorrecto *20',
            'otros.contenido.max'=>'El Nodo [otros.contenido] admite un máximo de 700 caracteres *0',
            'codigo_actividad.required'=>'El campo [codigo_actividad] es requerido *10',
            'codigo_actividad.max'=>'El formato del [codigo_actividad] es incorrecto *20',
            'receptor.nombre.max'=>'El campo [receptor.nombre] admite un máximo de 100 caracteres *0',
            'receptor.ubicacion.sennas.max'=>'El campo [receptor.ubicacion.sennas] admite un máximo de 250 caracteres *0'
        ];

            $validaciones = Validator::make($payload, $rules,$customMessages);
            if($validaciones->fails())
            {
                $mensaje=$validaciones->errors()->first();
                $code=$this->getCode($mensaje);
                $data=$this->eraseCodeIntoMessage($mensaje,$code);
                return response()->json(array("code"=>$code,"data"=>$data,"fecha"=>$fecha,"payload"=>$payload), 400);
            }

            if(!isset($payload['clave']) OR empty($payload['clave']))
            {
                return response()->json(array("code"=>"10","data"=>"Datos incompletos en la solicitud. La [clave] del comprobante es requerida","body"=>$payload,"fecha"=>$fecha), 400);


            }else{
                $tipoComprobante = substr($payload['clave'], 29, 2);
                if($tipoComprobante=='08')
                {
                    $payload['receptor']['nombre']=$emisor->razon_social;
                    $payload['receptor']['identificacion']['tipo']=$emisor->id_tpidentificacion;
                    $payload['receptor']['identificacion']['numero']=$emisor->id;
                    $payload['receptor']['nombre_comercial']=$emisor->nombre_comercial;
                    $payload['receptor']['ubicacion']['provincia']=$emisor->provincia;
                    $payload['receptor']['ubicacion']['canton']=$emisor->canton;
                    $payload['receptor']['ubicacion']['distrito']=$emisor->distrito;
                    if($emisor->barrio=='00' OR $emisor->barrio=='')
                    {
                        $payload['receptor']['ubicacion']['barrio']='01';
                    }
                    else{
                        $payload['receptor']['ubicacion']['barrio']=$emisor->barrio;
                    }
                    $payload['receptor']['ubicacion']['sennas']=$emisor->sennas;
                    if($emisor->telefono!='')
                    {
                        $payload['receptor']['telefono']['cod_pais']='506';
                        $payload['receptor']['telefono']['numero']=$emisor->telefono;
                    }

                    if($emisor->fax!='')
                    {
                        $payload['receptor']['fax']['cod_pais']='506';
                        $payload['receptor']['fax']['numero']=$emisor->fax;
                    }
                    $payload['receptor']['correo_electronico']=$emisor->correo_electronico;
                }
                else{
                    $payload['emisor']['nombre']=$emisor->razon_social;
                    $payload['emisor']['identificacion']['tipo']=$emisor->id_tpidentificacion;
                    $payload['emisor']['identificacion']['numero']=$emisor->id;
                    $payload['emisor']['nombre_comercial']=$emisor->nombre_comercial;
                    $payload['emisor']['ubicacion']['provincia']=$emisor->provincia;
                    $payload['emisor']['ubicacion']['canton']=$emisor->canton;
                    $payload['emisor']['ubicacion']['distrito']=$emisor->distrito;
                    if($emisor->barrio=='00' OR $emisor->barrio=='')
                    {
                        $payload['emisor']['ubicacion']['barrio']='01';
                    }
                    else{
                        $payload['emisor']['ubicacion']['barrio']=$emisor->barrio;
                    }
                    $payload['emisor']['ubicacion']['sennas']=$emisor->sennas;
                    if($emisor->telefono!='')
                    {
                        $payload['emisor']['telefono']['cod_pais']='506';
                        $payload['emisor']['telefono']['numero']=$emisor->telefono;
                    }

                    if($emisor->fax!='')
                    {
                        $payload['emisor']['fax']['cod_pais']='506';
                        $payload['emisor']['fax']['numero']=$emisor->fax;
                    }
                    $payload['emisor']['correo_electronico']=$emisor->correo_electronico;
                }

                if ($tipoComprobante == "01") {
                    return $this->makeFE($payload);
                } elseif ($tipoComprobante == "02") {
                    return $this->makeND($payload);
                } elseif ($tipoComprobante == "03") {
                    return $this->makeNC($payload);
                } elseif ($tipoComprobante == "04") {
                    return $this->makeTE($payload);
                }
                elseif ($tipoComprobante == "08") {
                    return $this->makeFEC($payload);
                }
                elseif ($tipoComprobante == "09") {
                    return $this->makeFEE($payload);
                }

            }

    }
    public function updateConsecutivePOS(Request $request)
    {

        $entorno=$request->header('entorno');
        $tipoComprobante=$request->post('tp_comprobante');
        $id=$request->post('id_emisor');

        if($entorno=='stag') {
            if ($tipoComprobante == "01") {
                //return DB::table('EMISORES')->where('id',$emisor->id)->update(['consecutivoFEtest'=>$emisor->consecutivoFEtest++]);
                try {
                    $e = Emisor::find($id);
                    $e->consecutivoFEtest++;
                    $e->save();
                } catch (\Exception $exception) {
                    return $exception->getMessage();
                }
            } elseif ($tipoComprobante == "02") {
                try {
                    $e = Emisor::find($id);
                    $e->consecutivoNDtest++;
                    $e->save();
                } catch (\Exception $exception) {
                    return $exception->getMessage();
                }
            } elseif ($tipoComprobante == "03") {
                try {
                    $e = Emisor::find($id);
                    $e->consecutivoNCtest++;
                    $e->save();
                } catch (\Exception $exception) {
                    return $exception->getMessage();
                }
            } elseif ($tipoComprobante == "04") {
                try {
                    $e = Emisor::find($id);
                    $e->consecutivoTEtest++;
                    $e->save();
                } catch (\Exception $exception) {
                    return $exception->getMessage();
                }
            }
            elseif ($tipoComprobante == "08") {
                try {
                    $e = Emisor::find($id);
                    $e->consecutivoFECtest++;
                    $e->save();
                } catch (\Exception $exception) {
                    return $exception->getMessage();
                }
            }
            elseif ($tipoComprobante == "09") {
                try {
                    $e = Emisor::find($id);
                    $e->consecutivoFEEtest++;
                    $e->save();
                } catch (\Exception $exception) {
                    return $exception->getMessage();
                }
            }
        }
        elseif($entorno=='prod')
        {
            if ($tipoComprobante == "01") {
                //return DB::table('EMISORES')->where('id',$emisor->id)->update(['consecutivoFEtest'=>$emisor->consecutivoFEtest++]);
                try {
                    $e = Emisor::find($id);
                    $e->consecutivoFEprod++;
                    $e->save();
                } catch (\Exception $exception) {
                    return $exception->getMessage();
                }
            } elseif ($tipoComprobante == "02") {
                try {
                    $e = Emisor::find($id);
                    $e->consecutivoNDprod++;
                    $e->save();
                } catch (\Exception $exception) {
                    return $exception->getMessage();
                }
            } elseif ($tipoComprobante == "03") {
                try {
                    $e = Emisor::find($id);
                    $e->consecutivoNCprod++;
                    $e->save();
                } catch (\Exception $exception) {
                    return $exception->getMessage();
                }
            } elseif ($tipoComprobante == "04") {
                try {
                    $e = Emisor::find($id);
                    $e->consecutivoTEprod++;
                    $e->save();
                } catch (\Exception $exception) {
                    return $exception->getMessage();
                }
            }
            elseif ($tipoComprobante == "08") {
                try {
                    $e = Emisor::find($id);
                    $e->consecutivoFECprod++;
                    $e->save();
                } catch (\Exception $exception) {
                    return $exception->getMessage();
                }
            }
            elseif ($tipoComprobante == "09") {
                try {
                    $e = Emisor::find($id);
                    $e->consecutivoFEEprod++;
                    $e->save();
                } catch (\Exception $exception) {
                    return $exception->getMessage();
                }
            }
        }
        echo json_encode(array("msj"=>"ok"));

    }
    public function makeFE($payload)
    {

        $consecutivo = substr($payload['clave'], 21, 20);
        $fechaEmision=date(DATE_RFC3339);
        /*if(!isset($payload['encabezado']['fecha']) or empty($payload['encabezado']['fecha']))
        {
            return response()->json(array("response"=>"La fecha del comprobante es requerida","estado"=>"error"), 400);
        }*/
        if(!isset($payload['emisor']) or empty($payload['emisor']))
        {
            return response()->json(array("code"=>"10","data"=>"Datos incompletos en la solicitud. El [emisor] es requerido","body"=>$payload,"fecha"=>$fechaEmision), 400);
        }
        if(!isset($payload['emisor']['nombre']) or empty($payload['emisor']['nombre']))
        {
            return response()->json(array("code"=>"10","data"=>"Datos incompletos en la solicitud. El [nombre] del emisor es requerido","body"=>$payload,"fecha"=>$fechaEmision), 400);
        }
        if(!isset($payload['emisor']['identificacion']) or empty($payload['emisor']['identificacion']))
        {
            return response()->json(array("code"=>"10","data"=>"Datos incompletos en la solicitud. La [identificación] del emisor es requerido","body"=>$payload,"fecha"=>$fechaEmision), 400);
        }
        if(!isset($payload['emisor']['identificacion']['tipo']) or empty($payload['emisor']['identificacion']['tipo']))
        {
            return response()->json(array("code"=>"10","data"=>"Datos incompletos en la solicitud. El [tipo] de identificación del emisor es requerido","body"=>$payload,"fecha"=>$fechaEmision), 400);
        }
        if(!isset($payload['emisor']['identificacion']['numero']) or empty($payload['emisor']['identificacion']['numero']))
        {
            return response()->json(array("code"=>"10","data"=>"Datos incompletos en la solicitud. El [numero] de identificación del emisor es requerido","body"=>$payload,"fecha"=>$fechaEmision), 400);
        }
        if(!isset($payload['emisor']['ubicacion']) or empty($payload['emisor']['ubicacion']))
        {
            return response()->json(array("code"=>"10","data"=>"Datos incompletos en la solicitud. La [ubicacion] del emisor es requerida","body"=>$payload,"fecha"=>$fechaEmision), 400);
        }
        if(!isset($payload['emisor']['ubicacion']['provincia']) or empty($payload['emisor']['ubicacion']['provincia']))
        {
            return response()->json(array("code"=>"10","data"=>"Datos incompletos en la solicitud. La [provincia] del emisor es requerida","body"=>$payload,"fecha"=>$fechaEmision), 400);
        }
        if(!isset($payload['emisor']['ubicacion']['canton']) or empty($payload['emisor']['ubicacion']['canton']))
        {
            return response()->json(array("code"=>"10","data"=>"Datos incompletos en la solicitud. El [canton] del emisor es requerido","body"=>$payload,"fecha"=>$fechaEmision), 400);
        }
        if(!isset($payload['emisor']['ubicacion']['distrito']) or empty($payload['emisor']['ubicacion']['distrito']))
        {
            return response()->json(array("code"=>"10","data"=>"Datos incompletos en la solicitud. El [distrito] del emisor es requerido","body"=>$payload,"fecha"=>$fechaEmision), 400);
        }
        if(!isset($payload['emisor']['ubicacion']['sennas']) or empty($payload['emisor']['ubicacion']['sennas']))
        {
            return response()->json(array("code"=>"10","data"=>"Datos incompletos en la solicitud. [sennas] del emisor es requerido","body"=>$payload,"fecha"=>$fechaEmision), 400);
        }
        //SE VALIDA QUE SI EL NODO TELEFON SE INCLUYE, ENTONCES QUE LO QUE ESTÉ ADENTRO ESTÉ COMPLETO
        if(isset($payload['emisor']['telefono']) AND !empty($payload['emisor']['telefono']))
        {
            if(!isset($payload['emisor']['telefono']['cod_pais']) or empty($payload['emisor']['telefono']['cod_pais']))
            {
                return response()->json(array("code"=>"10","data"=>"Datos incompletos en la solicitud. [cod_pais] del telefono del emisor es requerido","body"=>$payload,"fecha"=>$fechaEmision), 400);
            }
            if(!isset($payload['emisor']['telefono']['numero']) or empty($payload['emisor']['telefono']['numero']))
            {
                return response()->json(array("code"=>"10","data"=>"Datos incompletos en la solicitud. El [numero] de telefono del emisor es requerido","body"=>$payload,"fecha"=>$fechaEmision), 400);
            }

        }
        if(isset($payload['emisor']['fax']) AND !empty($payload['emisor']['fax']))
        {
            if(!isset($payload['emisor']['fax']['cod_pais']) or empty($payload['emisor']['fax']['cod_pais']))
            {
                return response()->json(array("code"=>"10","data"=>"Datos incompletos en la solicitud. [cod_pais] del fax del emisor es requerido","body"=>$payload,"fecha"=>$fechaEmision), 400);
            }
            if(!isset($payload['emisor']['fax']['numero']) or empty($payload['emisor']['fax']['numero']))
            {
                return response()->json(array("code"=>"10","data"=>"Datos incompletos en la solicitud. El [numero] de fax del emisor es requerido","body"=>$payload,"fecha"=>$fechaEmision), 400);
            }

        }
        if(!isset($payload['emisor']['correo_electronico']) or empty($payload['emisor']['correo_electronico']))
        {
            return response()->json(array("code"=>"10","data"=>"Datos incompletos en la solicitud. El [correo_electronico] del emisor es requerido","body"=>$payload,"fecha"=>$fechaEmision), 400);
        }


        $xmlString = '<?xml version="1.0" encoding="utf-8"?>
        <FacturaElectronica xmlns:xsd="http://www.w3.org/2001/XMLSchema" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns="https://cdn.comprobanteselectronicos.go.cr/xml-schemas/v4.3/facturaElectronica">
        <Clave>' . $payload['clave'] . '</Clave>
        <CodigoActividad>'.$payload['codigo_actividad'].'</CodigoActividad>
        <NumeroConsecutivo>' . $consecutivo . '</NumeroConsecutivo>
        <FechaEmision>' . /*$payload['encabezado']['fecha']*/ $fechaEmision. '</FechaEmision>
        <Emisor>
            <Nombre>' . $payload['emisor']['nombre'] . '</Nombre>
            <Identificacion>
                <Tipo>' . $payload['emisor']['identificacion']['tipo'] . '</Tipo>
                <Numero>' . $payload['emisor']['identificacion']['numero'] . '</Numero>
            </Identificacion>
            <NombreComercial>' . $payload['emisor']['nombre_comercial'] . '</NombreComercial>';
        $xmlString .= '
        <Ubicacion>
            <Provincia>' . $payload['emisor']['ubicacion']['provincia'] . '</Provincia>
            <Canton>' . $payload['emisor']['ubicacion']['canton'] . '</Canton>
            <Distrito>' . $payload['emisor']['ubicacion']['distrito'] . '</Distrito>';
            if (isset($payload['emisor']['ubicacion']['barrio']) AND $payload['emisor']['ubicacion']['barrio'] != '')
                $xmlString .= '<Barrio>' . $payload['emisor']['ubicacion']['barrio'] . '</Barrio>';
            $xmlString .= '
                <OtrasSenas>' . $payload['emisor']['ubicacion']['sennas'] . '</OtrasSenas>
            </Ubicacion>';


        if (isset($payload['emisor']['telefono']['cod_pais']) AND $payload['emisor']['telefono']['cod_pais'] != '' AND isset($payload['emisor']['telefono']['numero']) AND $payload['emisor']['telefono']['numero'] != '') {
            $xmlString .= '
            <Telefono>
                <CodigoPais>' . $payload['emisor']['telefono']['cod_pais'] . '</CodigoPais>
                <NumTelefono>' . $payload['emisor']['telefono']['numero'] . '</NumTelefono>
            </Telefono>';
        }


        if (isset($payload['emisor']['fax']['cod_pais']) AND $payload['emisor']['fax']['cod_pais'] != '' AND isset($payload['emisor']['fax']['numero']) AND $payload['emisor']['fax']['numero'] != '') {
            $xmlString .= '
            <Fax>
                <CodigoPais>' . $payload['emisor']['fax']['cod_pais'] . '</CodigoPais>
                <NumTelefono>' . $payload['emisor']['fax']['numero'] . '</NumTelefono>
            </Fax>';
        }

        $xmlString .= '<CorreoElectronico>' . $payload['emisor']['correo_electronico'] . '</CorreoElectronico>
        </Emisor>';


        if (isset($payload['receptor']['nombre']) and $payload['receptor']['nombre'] != '')
        {
            $xmlString .= '<Receptor>
            <Nombre>' . $payload['receptor']['nombre'] . '</Nombre>';


            if (isset($payload['receptor']['IdentificacionExtranjero']) AND $payload['receptor']['IdentificacionExtranjero'] != '')
            {
                $xmlString .= '<IdentificacionExtranjero>'
                    . $payload['receptor']['IdentificacionExtranjero']
                    . ' </IdentificacionExtranjero>';
            }


            if (isset($payload['receptor']['identificacion']['tipo']) AND $payload['receptor']['identificacion']['tipo'] != '') {
                $xmlString .= '<Identificacion>
                    <Tipo>' . $payload['receptor']['identificacion']['tipo'] . '</Tipo>';
            } else {
                return response()->json(array("code"=>"10","data" => "Datos incompletos en la solicitud. El [tipo] de identificación del receptor es requerido", "body" => $payload,"fecha"=>$fechaEmision), 400);
            }
            if (isset($payload['receptor']['identificacion']['numero']) AND $payload['receptor']['identificacion']['numero'] != '') {
                $xmlString .='<Numero>' . $payload['receptor']['identificacion']['numero'] . '</Numero></Identificacion>';

            }
            else{
                return response()->json(array("code"=>"10","data" => "Datos incompletos en la solicitud. El [numero] de identificación del receptor es requerido", "body" => $payload,"fecha"=>$fechaEmision), 400);
            }
            if (isset($payload['receptor']['ubicacion']) AND $payload['receptor']['ubicacion'] != '' ) {
                if (!isset($payload['receptor']['ubicacion']['provincia']) OR $payload['receptor']['ubicacion']['provincia'] == '' ) {
                    return response()->json(array("code"=>"10","data" => "Datos incompletos en la solicitud. La [provincia] del receptor es requerida", "body" => $payload,"fecha"=>$fechaEmision), 400);
                }
                if (!isset($payload['receptor']['ubicacion']['canton']) OR $payload['receptor']['ubicacion']['canton'] == '' ) {
                    return response()->json(array("code"=>"10","data" => "Datos incompletos en la solicitud. El [canton] del receptor es requerido", "body" => $payload,"fecha"=>$fechaEmision), 400);
                }
                if (!isset($payload['receptor']['ubicacion']['distrito']) OR $payload['receptor']['ubicacion']['distrito'] == '' ) {
                    return response()->json(array("code"=>"10","data" => "Datos incompletos en la solicitud. El [distrito] del receptor es requerido", "body" => $payload,"fecha"=>$fechaEmision), 400);
                }
                if (!isset($payload['receptor']['ubicacion']['sennas']) OR $payload['receptor']['ubicacion']['sennas'] == '' ) {
                    return response()->json(array("code"=>"10","data" => "Datos incompletos en la solicitud. [sennas] del receptor es requerido", "body" => $payload,"fecha"=>$fechaEmision), 400);
                }
                $xmlString .= '
                    <Ubicacion>
                        <Provincia>' . $payload['receptor']['ubicacion']['provincia'] . '</Provincia>
                        <Canton>' . $payload['receptor']['ubicacion']['canton'] . '</Canton>
                        <Distrito>' . $payload['receptor']['ubicacion']['distrito'] . '</Distrito>';
                if (isset($payload['receptor']['ubicacion']['barrio']) AND $payload['receptor']['ubicacion']['barrio'] != ''){
                    $xmlString .= '
                            <Barrio>' . $payload['receptor']['ubicacion']['barrio'] . '</Barrio>';}
                else{
                    $xmlString .= '<Barrio>01</Barrio>';
                }
                       $xmlString .= ' <OtrasSenas>' . $payload['receptor']['ubicacion']['sennas'] . '</OtrasSenas>
                    </Ubicacion>';

            }
            if (!empty($payload['receptor']['IdentificacionExtranjero']) AND empty($payload['receptor']['sennas_ext']))
            {
                return response()->json(array("code"=>"10","data" => "Datos incompletos en la solicitud. El [sennas_ext] del receptor es requerido", "body"=>$payload,"fecha"=>$fechaEmision), 400);
            }

            if (empty($payload['receptor']['IdentificacionExtranjero']) AND !empty($payload['receptor']['sennas_ext']))
            {
                return response()->json(array("code"=>"10","data" => "Datos incompletos en la solicitud. La [IdentificacionExtranjero] del receptor es requerido", "body"=>$payload,"fecha"=>$fechaEmision), 400);
            }
            if(!empty($payload['receptor']['IdentificacionExtranjero']) AND !empty($payload['receptor']['sennas_ext'])){
                $xmlString .= '<OtrasSenasExtranjero>'
                    . $payload['receptor']['sennas_ext']
                    . ' </OtrasSenasExtranjero>';
            }
            if (isset($payload['receptor']['telefono']) AND $payload['receptor']['telefono'] !='')
            {
                if (!isset($payload['receptor']['telefono']['cod_pais']) OR $payload['receptor']['telefono']['cod_pais'] =='')
                {
                    return response()->json(array("code"=>"10","data" => "Datos incompletos en la solicitud. El [cod_pais] del teléfono del receptor es requerido", "body" => $payload,"fecha"=>$fechaEmision), 400);
                }
                if (!isset($payload['receptor']['telefono']['numero']) OR $payload['receptor']['telefono']['numero'] =='')
                {
                    return response()->json(array("code"=>"10","data" => "Datos incompletos en la solicitud. El [numero] de teléfono del receptor es requerido", "body" => $payload,"fecha"=>$fechaEmision), 400);
                }
                $xmlString .= '<Telefono>
                                   <CodigoPais>' . $payload['receptor']['telefono']['cod_pais'] . '</CodigoPais>';
                $xmlString .= '<NumTelefono>' . $payload['receptor']['telefono']['numero'] . '</NumTelefono>
                    </Telefono>';

            }
            if (isset($payload['receptor']['fax']) AND $payload['receptor']['fax'] !='')
            {
                if (!isset($payload['receptor']['fax']['cod_pais']) OR $payload['receptor']['fax']['cod_pais'] =='')
                {
                    return response()->json(array("code"=>"10","data" => "Datos incompletos en la solicitud. El [cod_pais] del fax del receptor es requerido", "body"=>$payload,"fecha"=>$fechaEmision), 400);
                }
                if (!isset($payload['receptor']['fax']['numero']) OR $payload['receptor']['fax']['numero'] =='')
                {
                    return response()->json(array("code"=>"10","data" => "Datos incompletos en la solicitud. El [numero] de fax del receptor es requerido", "body"=>$payload,"fecha"=>$fechaEmision), 400);
                }
                $xmlString .= '<Fax>
                                   <CodigoPais>' . $payload['receptor']['telefono']['cod_pais'] . '</CodigoPais>';
                $xmlString .= '<NumTelefono>' . $payload['receptor']['telefono']['numero'] . '</NumTelefono>
                    </Fax>';

            }


            if (!isset($payload['receptor']['correo_electronico']) OR $payload['receptor']['correo_electronico'] == '')
            {
                return response()->json(array("code"=>"10","data" => "Datos incompletos en la solicitud. El [correo_electronico] del receptor es requerido", "body"=>$payload,"fecha"=>$fechaEmision), 400);
            }
            $xmlString .= '<CorreoElectronico>' . $payload['receptor']['correo_electronico'] . '</CorreoElectronico>';
            $xmlString .= '</Receptor>';

        }
        if(!isset($payload['encabezado']['condicion_venta']) OR $payload['encabezado']['condicion_venta']=='')
        {
            return response()->json(array("code"=>"10","data" => "Datos incompletos en la solicitud. La [condicion_venta] es requerida", "body"=>$payload,"fecha"=>$fechaEmision), 400);
        }
        $xmlString .= '
        <CondicionVenta>' . $payload['encabezado']['condicion_venta'] . '</CondicionVenta>';
        if (isset($payload['encabezado']['plazo_credito']) AND $payload['encabezado']['plazo_credito']!='')
        {
            $xmlString .= '<PlazoCredito>' . $payload['encabezado']['plazo_credito'] . '</PlazoCredito>';

        }else
        {
            $xmlString .= '<PlazoCredito>0</PlazoCredito>';
        }
        if(!isset($payload['encabezado']['mediopago']) OR $payload['encabezado']['mediopago']=='')
        {
            return response()->json(array("code"=>"10","data" => "Datos incompletos en la solicitud. El [mediopago] es requerido", "body"=>$payload,"fecha"=>$fechaEmision), 400);
        }
        $xmlString .='<MedioPago>' . $payload['encabezado']['mediopago'] . '</MedioPago>';
        $xmlString .='<DetalleServicio>';

        if(!isset($payload['detalle']) OR $payload['detalle']=='')
        {
            return response()->json(array("code"=>"10","data" => "Datos incompletos en la solicitud. El [detalle] es requerido", "body"=>$payload,"fecha"=>$fechaEmision), 400);
        }
        $l = 1;
        $pos=0;
        $totalServGravados=0.00000;
        $totalServExentos=0.00000;
        $totalMercanciasGravadas=0.00000;
        $totalMercanciasExentas=0.00000;
        $totalGravado=0.00000;
        $totalExento=0.00000;
        $totalVenta=0.00000;
        $totalDescuentos=0.00000;
        $totalVentaNeta=0.00000;
        $totalImpuesto=0.00000;
        $totalComprobante=0.00000;


        foreach ($payload['detalle'] as $d)
        {

            if(!isset($d['numero']) OR $d['numero']=='')
            {
                return response()->json(array("code"=>"10","data" => "Datos incompletos en la solicitud. El [numero] de linea en la linea de detalle [".$l."] es requerido", "body" => $payload,"fecha"=>$fechaEmision), 400);
            }
            else{
                $xmlString .= '<LineaDetalle>
                  <NumeroLinea>' . $d['numero'] . '</NumeroLinea>';
            }

            if(!isset($d['codigo']) OR $d['codigo']=='')
            {
                return response()->json(array("code"=>"10","data" => "Datos incompletos en la solicitud. El [codigo] en la linea de detalle [".$l."] es requerida", "body" => $payload,"fecha"=>$fechaEmision), 400);
            }
            else{
                $xmlString .='<Codigo>' . $d['codigo'] . '</Codigo>';
            }

            if(isset($d['codigoComercial']) AND $d['codigoComercial']!='')
            {

            $xmlString.= '<CodigoComercial>';
                if(!isset($d['codigoComercial']['tipo']) OR $d['codigoComercial']['tipo']==''){
                    return response()->json(array("code"=>"10","data" => "Datos incompletos en la solicitud. El [tipo] de codigoComercial en la linea de detalle [".$l."] es requerido ", "body"=>$payload,"fecha"=>$fechaEmision), 400);
                }
                else{
                    $xmlString.='<Tipo>'.$d['codigoComercial']['tipo'].'</Tipo>';
                }
                if(!isset($d['codigoComercial']['codigo']) OR $d['codigoComercial']['codigo']==''){
                    return response()->json(array("code"=>"10","data" => "Datos incompletos en la solicitud. El [codigo] del nodo codigoComercial en la linea de detalle [".$l."] es requerido", "body"=>$payload,"fecha"=>$fechaEmision), 400);
                }
                else{
                    $xmlString.='<Codigo>'.$d['codigoComercial']['codigo']   .'</Codigo>';
                }
                    $xmlString.= '</CodigoComercial>';
            }
            if(!isset($d['cantidad']) OR $d['cantidad']=='')
            {
                return response()->json(array("code"=>"10","data" => "Datos incompletos en la solicitud. La [cantidad] en la linea de detalle [".$l."] es requerida", "body" => $payload,"fecha"=>$fechaEmision), 400);
            }
            else{
                $xmlString .='<Cantidad>' . $d['cantidad'] . '</Cantidad>';
            }
            if(!isset($d['unidad_medida']) OR $d['unidad_medida']=='')
            {
                return response()->json(array("code"=>"10","data" => "Datos incompletos en la solicitud. La [unidad_medida] en la linea de detalle [".$l."] es requerida", "body" => $payload,"fecha"=>$fechaEmision), 400);
            }
            else
            {
                $xmlString.='<UnidadMedida>' . $d['unidad_medida'] . '</UnidadMedida>';
            }
            if(isset($d['unidad_medida_comercial'])){
                $xmlString.='<UnidadMedidaComercial>'.$d['unidad_medida_comercial'].'</UnidadMedidaComercial>';
            }
            if(!isset($d['detalle']) OR $d['detalle']=='')
            {
                return response()->json(array("code"=>"10","data" => "Datos incompletos en la solicitud. El [detalle] en la linea de detalle [".$l."] es requerido", "body" => $payload,"fecha"=>$fechaEmision), 400);
            }else{
                $xmlString.='<Detalle>' . $d['detalle'] . '</Detalle>';
            }
            if(!isset($d['precio_unitario']) OR $d['precio_unitario']=='')
            {
                return response()->json(array("code"=>"10","data" => "Datos incompletos en la solicitud. El [precio_unitario] en la linea de detalle [".$l."] es requerido", "body" => $payload,"fecha"=>$fechaEmision), 400);
            }
            else{
                $xmlString.='<PrecioUnitario>' . $d['precio_unitario'] . '</PrecioUnitario>';
            }
            if(!isset($d['monto_total']) OR $d['monto_total']=='')
            {
                return response()->json(array("code"=>"10","data" => "Datos incompletos en la solicitud. El [monto_total] en la linea de detalle [".$l."] es requerido", "body" => $payload,"fecha"=>$fechaEmision), 400);
            }
            else
            {
                $xmlString.='<MontoTotal>' . $d['monto_total'] . '</MontoTotal>';
            }

            if (isset($d['descuento']) && $d['descuento'] != "")
            {
                if (!isset($d['descuento']['monto']) OR $d['descuento']['monto'] == "")
                {
                    return response()->json(array("code"=>"10","data" => "Datos incompletos en la solicitud. El [monto] del nodo descuento en la linea de detalle [".$l."] es requerido", "body" => $payload,"fecha"=>$fechaEmision), 400);
                }
                else{
                    $xmlString .= '<Descuento><MontoDescuento>' . $d['descuento']['monto'] . '</MontoDescuento>';
                }
                if (!isset($d['descuento']['naturaleza_descuento']) OR $d['descuento']['naturaleza_descuento'] == "")
                {
                    return response()->json(array("code"=>"10","data" => "Datos incompletos en la solicitud. La [naturaleza_descuento] del nodo descuento en la linea de detalle [".$l."] es requerido", "body" => $payload,"fecha"=>$fechaEmision), 400);
                }
                else{
                    $xmlString .= '<NaturalezaDescuento>' . $d['descuento']['naturaleza_descuento'] . '</NaturalezaDescuento></Descuento>';
                }

            }

            if(!isset($d['subtotal']) OR $d['subtotal']==''){
                return response()->json(array("code"=>"10","data" => "Datos incompletos en la solicitud. El [subtotal] en la linea de detalle [".$l."] es requerido", "body" => $payload,"fecha"=>$fechaEmision), 400);
            }
            else{
                $xmlString .= '<SubTotal>' . $d['subtotal'] . '</SubTotal>';
            }
            if (isset($d['impuestos']) && $d['impuestos'] != "")
            {
                foreach ($d['impuestos'] as $y)
                {
                    if($y['codigo']=='07')
                    {
                        if(!isset($d['base_imponible']) OR $d['base_imponible']=='') {
                            return response()->json(array("code" => "10", "data" => "Datos incompletos en la solicitud. La [base_imponible] en la linea de detalle [" . $l . "] es requerido, debido a que uno de los impuestos tiene codigo 07", "body" => $payload, "fecha" => $fechaEmision), 400);
                        }
                        else{
                            $xmlString .= '<BaseImponible>' . $d['base_imponible'] . '</BaseImponible>';
                            break;
                        }
                    }
                }
                $numImp=1;
                foreach ($d['impuestos'] as $i)
                {
                    $xmlString .= '<Impuesto>';
                    if(!isset($i['codigo']) OR $i['codigo']=='')
                    {
                        return response()->json(array("code"=>"10","data" => "Datos incompletos en la solicitud. El [codigo] en la linea de detalle  [".$l."] de la linea del impuesto [".$numImp."] es requerido", "body" => $payload,"fecha"=>$fechaEmision), 400);
                    }else{
                        $xmlString .='<Codigo>' . $i['codigo'] . '</Codigo>';
                    }
                    if($i['codigo']=='01' OR $i['codigo']=='07')
                    {
                        if(!isset($i['codigo_tarifa']) OR $i['codigo_tarifa']=='')
                        {
                            return response()->json(array("code" => "10", "data" => "Datos incompletos en la solicitud. El [codigo_tarifa] en la linea de detalle [" . $l . "] de la linea del impuesto [".$numImp."] es requerido, debido a que uno de los impuestos tiene codigo 01 ó 07", "body" => $payload, "fecha" => $fechaEmision), 400);
                        }
                        else{
                            $xmlString .='<CodigoTarifa>' . $i['codigo_tarifa'] . '</CodigoTarifa>';
                        }
                    }
                    if(!isset($i['tarifa']))
                    {
                        return response()->json(array("code"=>"10","data" => "Datos incompletos en la solicitud. La [tarifa] en la linea de detalle  [".$l."] de la linea del impuesto [".$numImp."] es requerida", "body" => $payload,"fecha"=>$fechaEmision), 400);
                    }
                    else{
                        $xmlString .='<Tarifa>' . $i['tarifa'] . '</Tarifa>';
                    }
                    if($i['codigo']=='08')
                    {
                        if(!isset($i['factor_iva']) OR $i['factor_iva']==''){
                            return response()->json(array("code"=>"10","data" => "Datos incompletos en la solicitud. El [factor_iva] en la linea de detalle  [".$l."] de la linea del impuesto [".$numImp."] es requerida", "body" => $payload,"fecha"=>$fechaEmision), 400);
                        }
                        else{
                            $xmlString .='<FactorIVA>' . $i['factor_iva'] . '</FactorIVA>';
                        }
                    }
                    if(!isset($i['monto']))
                    {
                        return response()->json(array("code"=>"10","data" => "Datos incompletos en la solicitud. El [monto] en la linea de detalle  [".$l."] de la linea del impuesto [".$numImp."] es requerido", "body" => $payload,"fecha"=>$fechaEmision), 400);
                    }
                    else{
                        $xmlString .='<Monto>' . $i['monto'] . '</Monto>';
                        $totalImpuesto+=floatval($i['monto']);
                    }
                    if (isset($i['exoneracion']) && $i['exoneracion'] != "")
                    {
                        $xmlString .= '
                    <Exoneracion>';
                        if (!isset($i['exoneracion']['tipodocumento']) OR $i['exoneracion']['tipodocumento'] == ""){
                            return response()->json(array("code"=>"10","data" => "Datos incompletos en la solicitud. El [tipodocumento] de exoneración en la linea de detalle  [".$l."] del nodo del impuesto [".$numImp."] es requerido", "body" =>$payload,"fecha"=>$fechaEmision), 400);
                        }
                        else{
                            $xmlString .= '<TipoDocumento>' . $i['exoneracion']['tipodocumento'] . '</TipoDocumento>';
                        }
                        if (!isset($i['exoneracion']['numerodocumento']) OR $i['exoneracion']['numerodocumento'] == ""){
                            return response()->json(array("code"=>"10","data" => "Datos incompletos en la solicitud. El [numerodocumento] de exoneración en la linea de detalle  [".$l."] del nodo del impuesto [".$numImp."] es requerido", "body" =>$payload,"fecha"=>$fechaEmision), 400);
                        }
                        else{
                            $xmlString .= '<NumeroDocumento>' . $i['exoneracion']['numerodocumento'] . '</NumeroDocumento>';
                        }
                        if (!isset($i['exoneracion']['nombreinstitucion']) OR $i['exoneracion']['nombreinstitucion'] == ""){
                            return response()->json(array("code"=>"10","data" => "Datos incompletos en la solicitud. El [nombreinstitucion] de exoneración en la linea de detalle  [".$l."] del nodo del impuesto [".$numImp."] es requerido", "body" =>$payload,"fecha"=>$fechaEmision), 400);
                        }
                        else{
                            $xmlString .= '<NombreInstitucion>' . $i['exoneracion']['nombreinstitucion'] . '</NombreInstitucion>';
                        }
                        if (!isset($i['exoneracion']['fechaemision']) OR $i['exoneracion']['fechaemision'] == ""){
                            return response()->json(array("code"=>"10","data" => "La [fechaemision] de exoneración en la linea de detalle  [".$l."] del nodo del impuesto [".$numImp."] es requerido", "body" => $payload,"fecha"=>$fechaEmision), 400);
                        }else{
                            $xmlString .= '<FechaEmision>' . $i['exoneracion']['fechaemision'] . '</FechaEmision>';
                        }
                        if (!isset($i['exoneracion']['porcentaje']) OR $i['exoneracion']['porcentaje'] == ""){
                            return response()->json(array("code"=>"10","data" => "El [porcentaje] del nodo exoneración en la linea de detalle  [".$l."] del nodo del impuesto [".$numImp."] es requerido", "body" => $payload,"fecha"=>$fechaEmision), 400);
                        }else{
                            $xmlString .= '<PorcentajeExoneracion>' . $i['exoneracion']['porcentaje'] . '</PorcentajeExoneracion>';
                        }
                        if (!isset($i['exoneracion']['monto']) OR $i['exoneracion']['monto'] == ""){
                            return response()->json(array("code"=>"10","data" => "El [monto] del nodo exoneración en la linea de detalle  [".$l."] del nodo del impuesto [".$numImp."] es requerido", "body" => $payload,"fecha"=>$fechaEmision), 400);
                        }
                        else{
                            $xmlString .= '<MontoExoneracion>' . $i['exoneracion']['monto'] . '</MontoExoneracion>';
                        }

                        /*if((string)$this->getNewTaxAmount($i['monto'],$i['exoneracion']['porcentaje'])!=$i['exoneracion']['monto'])
                        {
                            return response()->json(array("code"=>"18","data"=>"El monto de exoneración según el porcentaje indicado es incorrecto","fecha"=>$fechaEmision,"detalle"=>$payload['detalle']), 400);
                        }*/

                        $xmlString .= '</Exoneracion>';

                        $totalImpuesto-=floatval($i['monto']);

                        $totalImpuesto+=$this->getNewTaxAmount($i['monto'],$i['exoneracion']['porcentaje']);

                    }

                    $xmlString .= '</Impuesto>';
                    $numImp++;

                }
                if($d['unidad_medida']=='Sp')
                {
                    $totalServGravados+=floatval($d['monto_total']);
                }
                else{
                    $totalMercanciasGravadas+=floatval($d['monto_total']);
                }

            }
            else{
                if($d['unidad_medida']=='Sp')
                {
                    $totalServExentos+=floatval($d['monto_total']);
                }
                else{
                    $totalMercanciasExentas+=floatval($d['monto_total']);
                }
            }
            if(isset($d['impuesto_neto']))
            {
                $xmlString .= '<ImpuestoNeto>' . $d['impuesto_neto'] . '</ImpuestoNeto>';
            }

            if(!isset($d['montototallinea']) OR $d['montototallinea']=='')
            {
                return response()->json( array("code"=>"10","data" => "El [montototallinea] en la linea de detalle [".$l."] es requerido", "body" => $payload,"fecha"=>$fechaEmision), 400);
            }
            else{
                $xmlString .= '<MontoTotalLinea>' . $d['montototallinea'] . '</MontoTotalLinea>';
            }

            $xmlString .= '</LineaDetalle>';
            $l++;
            $pos++;
        }
        $xmlString .= '</DetalleServicio>';
        $totalGravado=$totalServGravados + $totalMercanciasGravadas;
        $totalExento=$totalServExentos + $totalMercanciasExentas;
        $totalVenta=$totalGravado + $totalExento;
        $totalVentaNeta=$totalVenta - $totalDescuentos;
        $totalComprobante=$totalVentaNeta + $totalImpuesto;
        if(isset($payload['otros_cargos']) AND $payload['otros_cargos']!='')
        {
            $xmlString .= '<OtrosCargos>';
            if(!isset($payload['otros_cargos']['tipo_documento']) OR $payload['otros_cargos']['tipo_documento']=='')
            {
                return response()->json( array("code"=>"10","data" => "El [tipo_documento] del nodo otros_cargos es requerido", "body" => $payload,"fecha"=>$fechaEmision), 400);
            }
            else{
                $xmlString .= '<TipoDocumento>' . $payload['otros_cargos']['tipo_documento'] . '</TipoDocumento>';
            }
            if(!isset($payload['otros_cargos']['num_identidad_tercero']) OR $payload['otros_cargos']['num_identidad_tercero']=='')
            {
                return response()->json( array("code"=>"10","data" => "El [num_identidad_tercero] del nodo otros_cargos es requerido", "body" => $payload,"fecha"=>$fechaEmision), 400);
            }
            else{
                $xmlString .= '<NumeroIdentidadTercero>' . $payload['otros_cargos']['num_identidad_tercero'] . '</NumeroIdentidadTercero>';
            }
            if(!isset($payload['otros_cargos']['nombre_tercero']) OR $payload['otros_cargos']['nombre_tercero']=='')
            {
                return response()->json( array("code"=>"10","data" => "El [nombre_tercero] del nodo otros_cargos es requerido", "body" => $payload,"fecha"=>$fechaEmision), 400);
            }
            else{
                $xmlString .= '<NombreTercero>' . $payload['otros_cargos']['nombre_tercero'] . '</NombreTercero>';
            }
            if(!isset($payload['otros_cargos']['detalle']) OR $payload['otros_cargos']['detalle']=='')
            {
                return response()->json( array("code"=>"10","data" => "El [detalle] del nodo otros_cargos es requerido", "body" => $payload,"fecha"=>$fechaEmision), 400);
            }
            else{
                $xmlString .= '<Detalle>' . $payload['otros_cargos']['detalle'] . '</Detalle>';
            }
            if(!isset($payload['otros_cargos']['porcentaje']) OR $payload['otros_cargos']['porcentaje']=='')
            {
                return response()->json( array("code"=>"10","data" => "El [porcentaje] del nodo otros_cargos es requerido", "body" => $payload,"fecha"=>$fechaEmision), 400);
            }
            else{
                $xmlString .= '<Porcentaje>' . $payload['otros_cargos']['porcentaje'] . '</Porcentaje>';
            }
            if(!isset($payload['otros_cargos']['monto_cargo']) OR $payload['otros_cargos']['monto_cargo']=='')
            {
                return response()->json( array("code"=>"10","data" => "El [monto_cargo] del nodo otros_cargos es requerido", "body" => $payload,"fecha"=>$fechaEmision), 400);
            }
            else{
                $xmlString .= '<MontoCargo>' . $payload['otros_cargos']['monto_cargo'] . '</MontoCargo>';
            }
            $xmlString .= '</OtrosCargos>';
        }

        if(!isset($payload['resumen']) OR $payload['resumen']==''){
            return response()->json(array("code"=>"10","data" => "Datos incompletos en la solicitud. El [resumen] es requerido", "body" => $payload,"fecha"=>$fechaEmision), 400);
        }

        //Validación de los totales con respectos a las totales del detalle
        /*if((string)$totalServGravados!=$payload['resumen']['totalserviciogravado']
            OR (string)$totalServExentos!=$payload['resumen']['totalservicioexento']
            OR (string)$totalMercanciasGravadas!=$payload['resumen']['totalmercaderiagravado']
            OR (string)$totalMercanciasExentas!=$payload['resumen']['totalmercaderiaexento']
            OR (string)$totalGravado!=$payload['resumen']['totalgravado']
            OR (string)$totalExento!=$payload['resumen']['totalexento']
            OR (string)$totalVenta!=$payload['resumen']['totalventa']
            OR (string)$totalDescuentos!=$payload['resumen']['totaldescuentos']
            OR (string)$totalVentaNeta!=$payload['resumen']['totalventaneta']
            OR (string)$totalImpuesto!=$payload['resumen']['totalimpuestos']
            OR (string)$totalComprobante!=$payload['resumen']['totalcomprobante'])
        {
            return response()->json(array("code"=>"18","data"=>"Alguno de los montos de las facturas no coinciden con los montos de los
detalles correspondientes.","fecha"=>$fechaEmision,"detalle"=>$payload['detalle'],"resumen"=>$payload['resumen'],"NUm"=>$totalComprobante), 400);
        }*/
        $xmlString .= '<ResumenFactura>';
        if(isset($payload['resumen']['codigo_tipo_moneda']))
        {
            if(!isset($payload['resumen']['codigo_tipo_moneda']['moneda']) OR $payload['resumen']['codigo_tipo_moneda']['moneda']==''
            OR !isset($payload['resumen']['codigo_tipo_moneda']['tipo_cambio']) OR $payload['resumen']['codigo_tipo_moneda']['tipo_cambio']=='')
            {
                return response()->json(array("code"=>"10","data" => "Datos incompletos en la solicitud. El nodo [codigo_tipo_moneda] es requerido y debe estar completo cuando la moneda es extranjera, en caso contrario no utilice", "body" => $payload,"fecha"=>$fechaEmision), 400);
            }
            else{

                $xmlString .= '<CodigoTipoMoneda><CodigoMoneda>' . $payload['resumen']['codigo_tipo_moneda']['moneda'] . '</CodigoMoneda><TipoCambio>' . $payload['resumen']['codigo_tipo_moneda']['tipo_cambio'] . '</TipoCambio></CodigoTipoMoneda>';
            }

        }
        else {
                $xmlString .= '<CodigoTipoMoneda><CodigoMoneda>CRC</CodigoMoneda><TipoCambio>1</TipoCambio></CodigoTipoMoneda>';
        }

        if(isset($payload['resumen']['totalserviciogravado']) AND $payload['resumen']['totalserviciogravado']!=''){
            $xmlString .= '<TotalServGravados>' . $payload['resumen']['totalserviciogravado'] . '</TotalServGravados>';

        }
        if(isset($payload['resumen']['totalservicioexento']) AND $payload['resumen']['totalservicioexento']!=''){
            $xmlString .= '<TotalServExentos>' . $payload['resumen']['totalservicioexento'] . '</TotalServExentos>';
        }
        if(isset($payload['resumen']['totalservicioexonerado']) AND $payload['resumen']['totalservicioexonerado']!=''){
            $xmlString .= '<TotalServExonerado>' . $payload['resumen']['totalservicioexonerado'] . '</TotalServExonerado>';
        }
        if(isset($payload['resumen']['totalmercanciagravada']) AND $payload['resumen']['totalmercanciagravada']!=''){
            $xmlString .= '<TotalMercanciasGravadas>' . $payload['resumen']['totalmercanciagravada'] . '</TotalMercanciasGravadas>';
        }
        if(isset($payload['resumen']['totalmercanciaexenta']) AND $payload['resumen']['totalmercanciaexenta']!=''){
            $xmlString .= '<TotalMercanciasExentas>' . $payload['resumen']['totalmercanciaexenta'] . '</TotalMercanciasExentas>';
        }
        if(isset($payload['resumen']['totalmercanciaexonerada']) AND $payload['resumen']['totalmercanciaexonerada']!=''){
            $xmlString .= '<TotalMercExonerada>' . $payload['resumen']['totalmercanciaexonerada'] . '</TotalMercExonerada>';
        }
        if(isset($payload['resumen']['totalgravado']) AND $payload['resumen']['totalgravado']!=''){
            $xmlString .= '<TotalGravado>' . $payload['resumen']['totalgravado'] . '</TotalGravado>';
        }
        if(isset($payload['resumen']['totalexento']) AND $payload['resumen']['totalexento']!=''){
            $xmlString .= '<TotalExento>' . $payload['resumen']['totalexento'] . '</TotalExento>';
        }
        if(isset($payload['resumen']['totalexonerado']) AND $payload['resumen']['totalexonerado']!=''){
            $xmlString .= '<TotalExonerado>' . $payload['resumen']['totalexonerado'] . '</TotalExonerado>';
        }
        if(!isset($payload['resumen']['totalventa']) OR $payload['resumen']['totalventa']==''){
            return response()->json(array("code"=>"10","data" => "Datos incompletos en la solicitud. El [totalventa] es requerido", "body" => $payload,"fecha"=>$fechaEmision), 400);

        }else{
            $xmlString .= '<TotalVenta>' . $payload['resumen']['totalventa'] . '</TotalVenta>';
        }
        if(isset($payload['resumen']['totaldescuentos']) AND $payload['resumen']['totaldescuentos']!=''){
            $xmlString .= '<TotalDescuentos>' . $payload['resumen']['totaldescuentos'] . '</TotalDescuentos>';
        }
        if(!isset($payload['resumen']['totalventaneta']) OR $payload['resumen']['totalventaneta']==''){
            return response()->json(array("code"=>"10","data" => "Datos incompletos en la solicitud. El [totalventaneta] es requerido", "body" => $payload,"fecha"=>$fechaEmision), 400);

        }else{
            $xmlString .= '<TotalVentaNeta>' . $payload['resumen']['totalventaneta'] . '</TotalVentaNeta>';
        }
        if(isset($payload['resumen']['totalimpuestos']) AND $payload['resumen']['totalimpuestos']!=''){
            $xmlString .= '<TotalImpuesto>' . $payload['resumen']['totalimpuestos'] . '</TotalImpuesto>';
        }
        if(isset($payload['resumen']['totalivadevuelto']) AND $payload['resumen']['totalivadevuelto']!=''){
            $xmlString .= '<TotalIVADevuelto>' . $payload['resumen']['totalivadevuelto'] . '</TotalIVADevuelto>';
        }
        if(isset($payload['resumen']['totalotroscargos']) AND $payload['resumen']['totalotroscargos']!=''){
            $xmlString .= '<TotalOtrosCargos>' . $payload['resumen']['totalotroscargos'] . '</TotalOtrosCargos>';
        }
        if(!isset($payload['resumen']['totalcomprobante']) OR $payload['resumen']['totalcomprobante']==''){
            return response()->json(array("code"=>"10","data" => "Datos incompletos en la solicitud. El [totalcomprobante] es requerido", "body" => $payload,"fecha"=>$fechaEmision), 400);

        }else{
            $xmlString .= '<TotalComprobante>' . $payload['resumen']['totalcomprobante'] . '</TotalComprobante>';
        }
        $xmlString .= '</ResumenFactura>';
        $ot=0;
        if (isset($payload['otros']) AND $payload['otros'] != '')
        {

            if(!isset($payload['otros']['contenido']) OR $payload['otros']['contenido']=='')
            {
                return response()->json(array("code"=>"10","data" => "Datos incompletos en la solicitud. El [contenido] del nodo otros es requerido", "body" => $payload,"fecha"=>$fechaEmision), 400);
            }
            else{
                $xmlString .= '<Otros><OtroTexto>'.$payload['otros']['contenido'].'</OtroTexto></Otros>';
            }
        }

        $xmlString .= '
    </FacturaElectronica>';

        return response()->json(array("code"=>"1","data"=>base64_encode($xmlString),"fecha"=>$fechaEmision), 200);

    }
    public function makeTE($payload)
    {
        $consecutivo = substr($payload['clave'], 21, 20);
        $fechaEmision=date(DATE_RFC3339);
        /*if(!isset($payload['encabezado']['fecha']) or empty($payload['encabezado']['fecha']))
        {
            return response()->json(array("response"=>"La fecha del comprobante es requerida","estado"=>"error"), 400);
        }*/
        if(!isset($payload['emisor']) or empty($payload['emisor']))
        {
            return response()->json(array("code"=>"10","data"=>"Datos incompletos en la solicitud. El [emisor] es requerido","body"=>$payload,"fecha"=>$fechaEmision), 400);
        }
        if(!isset($payload['emisor']['nombre']) or empty($payload['emisor']['nombre']))
        {
            return response()->json(array("code"=>"10","data"=>"Datos incompletos en la solicitud. El [nombre] del emisor es requerido","body"=>$payload,"fecha"=>$fechaEmision), 400);
        }
        if(!isset($payload['emisor']['identificacion']) or empty($payload['emisor']['identificacion']))
        {
            return response()->json(array("code"=>"10","data"=>"Datos incompletos en la solicitud. La [identificación] del emisor es requerido","body"=>$payload,"fecha"=>$fechaEmision), 400);
        }
        if(!isset($payload['emisor']['identificacion']['tipo']) or empty($payload['emisor']['identificacion']['tipo']))
        {
            return response()->json(array("code"=>"10","data"=>"Datos incompletos en la solicitud. El [tipo] de identificación del emisor es requerido","body"=>$payload,"fecha"=>$fechaEmision), 400);
        }
        if(!isset($payload['emisor']['identificacion']['numero']) or empty($payload['emisor']['identificacion']['numero']))
        {
            return response()->json(array("code"=>"10","data"=>"Datos incompletos en la solicitud. El [numero] de identificación del emisor es requerido","body"=>$payload,"fecha"=>$fechaEmision), 400);
        }
        if(!isset($payload['emisor']['ubicacion']) or empty($payload['emisor']['ubicacion']))
        {
            return response()->json(array("code"=>"10","data"=>"Datos incompletos en la solicitud. La [ubicacion] del emisor es requerida","body"=>$payload,"fecha"=>$fechaEmision), 400);
        }
        if(!isset($payload['emisor']['ubicacion']['provincia']) or empty($payload['emisor']['ubicacion']['provincia']))
        {
            return response()->json(array("code"=>"10","data"=>"Datos incompletos en la solicitud. La [provincia] del emisor es requerida","body"=>$payload,"fecha"=>$fechaEmision), 400);
        }
        if(!isset($payload['emisor']['ubicacion']['canton']) or empty($payload['emisor']['ubicacion']['canton']))
        {
            return response()->json(array("code"=>"10","data"=>"Datos incompletos en la solicitud. El [canton] del emisor es requerido","body"=>$payload,"fecha"=>$fechaEmision), 400);
        }
        if(!isset($payload['emisor']['ubicacion']['distrito']) or empty($payload['emisor']['ubicacion']['distrito']))
        {
            return response()->json(array("code"=>"10","data"=>"Datos incompletos en la solicitud. El [distrito] del emisor es requerido","body"=>$payload,"fecha"=>$fechaEmision), 400);
        }
        if(!isset($payload['emisor']['ubicacion']['sennas']) or empty($payload['emisor']['ubicacion']['sennas']))
        {
            return response()->json(array("code"=>"10","data"=>"Datos incompletos en la solicitud. [sennas] del emisor es requerido","body"=>$payload,"fecha"=>$fechaEmision), 400);
        }
        //SE VALIDA QUE SI EL NODO TELEFON SE INCLUYE, ENTONCES QUE LO QUE ESTÉ ADENTRO ESTÉ COMPLETO
        if(isset($payload['emisor']['telefono']) AND !empty($payload['emisor']['telefono']))
        {
            if(!isset($payload['emisor']['telefono']['cod_pais']) or empty($payload['emisor']['telefono']['cod_pais']))
            {
                return response()->json(array("code"=>"10","data"=>"Datos incompletos en la solicitud. [cod_pais] del telefono del emisor es requerido","body"=>$payload,"fecha"=>$fechaEmision), 400);
            }
            if(!isset($payload['emisor']['telefono']['numero']) or empty($payload['emisor']['telefono']['numero']))
            {
                return response()->json(array("code"=>"10","data"=>"Datos incompletos en la solicitud. El [numero] de telefono del emisor es requerido","body"=>$payload,"fecha"=>$fechaEmision), 400);
            }

        }
        if(isset($payload['emisor']['fax']) AND !empty($payload['emisor']['fax']))
        {
            if(!isset($payload['emisor']['fax']['cod_pais']) or empty($payload['emisor']['fax']['cod_pais']))
            {
                return response()->json(array("code"=>"10","data"=>"Datos incompletos en la solicitud. [cod_pais] del fax del emisor es requerido","body"=>$payload,"fecha"=>$fechaEmision), 400);
            }
            if(!isset($payload['emisor']['fax']['numero']) or empty($payload['emisor']['fax']['numero']))
            {
                return response()->json(array("code"=>"10","data"=>"Datos incompletos en la solicitud. El [numero] de fax del emisor es requerido","body"=>$payload,"fecha"=>$fechaEmision), 400);
            }

        }
        if(!isset($payload['emisor']['correo_electronico']) or empty($payload['emisor']['correo_electronico']))
        {
            return response()->json(array("code"=>"10","data"=>"Datos incompletos en la solicitud. El [correo_electronico] del emisor es requerido","body"=>$payload,"fecha"=>$fechaEmision), 400);
        }


        $xmlString = '<?xml version="1.0" encoding="utf-8"?>
    <TiqueteElectronico xmlns:xsd="http://www.w3.org/2001/XMLSchema" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns="https://cdn.comprobanteselectronicos.go.cr/xml-schemas/v4.3/tiqueteElectronico">
    <Clave>'. $payload['clave'] . '</Clave>
    <CodigoActividad>'.$payload['codigo_actividad'].'</CodigoActividad>
        <NumeroConsecutivo>' . $consecutivo . '</NumeroConsecutivo>
        <FechaEmision>' . /*$payload['encabezado']['fecha']*/ $fechaEmision. '</FechaEmision>
        <Emisor>
            <Nombre>' . $payload['emisor']['nombre'] . '</Nombre>
            <Identificacion>
                <Tipo>' . $payload['emisor']['identificacion']['tipo'] . '</Tipo>
                <Numero>' . $payload['emisor']['identificacion']['numero'] . '</Numero>
            </Identificacion>
            <NombreComercial>' . $payload['emisor']['nombre_comercial'] . '</NombreComercial>';
        $xmlString .= '
        <Ubicacion>
            <Provincia>' . $payload['emisor']['ubicacion']['provincia'] . '</Provincia>
            <Canton>' . $payload['emisor']['ubicacion']['canton'] . '</Canton>
            <Distrito>' . $payload['emisor']['ubicacion']['distrito'] . '</Distrito>';
        if (isset($payload['emisor']['ubicacion']['barrio']) AND $payload['emisor']['ubicacion']['barrio'] != ''){
            $xmlString .= '<Barrio>' . $payload['emisor']['ubicacion']['barrio'] . '</Barrio>';}
        else{
            $xmlString .= '<Barrio>01</Barrio>';
        }
        $xmlString .= '
                <OtrasSenas>' . $payload['emisor']['ubicacion']['sennas'] . '</OtrasSenas>
            </Ubicacion>';


        if (isset($payload['emisor']['telefono']['cod_pais']) AND $payload['emisor']['telefono']['cod_pais'] != '' AND isset($payload['emisor']['telefono']['numero']) AND $payload['emisor']['telefono']['numero'] != '') {
            $xmlString .= '
            <Telefono>
                <CodigoPais>' . $payload['emisor']['telefono']['cod_pais'] . '</CodigoPais>
                <NumTelefono>' . $payload['emisor']['telefono']['numero'] . '</NumTelefono>
            </Telefono>';
        }


        if (isset($payload['emisor']['fax']['cod_pais']) AND $payload['emisor']['fax']['cod_pais'] != '' AND isset($payload['emisor']['fax']['numero']) AND $payload['emisor']['fax']['numero'] != '') {
            $xmlString .= '
            <Fax>
                <CodigoPais>' . $payload['emisor']['fax']['cod_pais'] . '</CodigoPais>
                <NumTelefono>' . $payload['emisor']['fax']['numero'] . '</NumTelefono>
            </Fax>';
        }

        $xmlString .= '<CorreoElectronico>' . $payload['emisor']['correo_electronico'] . '</CorreoElectronico>
        </Emisor>';


        if (isset($payload['receptor']['nombre']) and $payload['receptor']['nombre'] != '')
        {
            $xmlString .= '<Receptor>
            <Nombre>' . $payload['receptor']['nombre'] . '</Nombre>';


            if (isset($payload['receptor']['IdentificacionExtranjero']) AND $payload['receptor']['IdentificacionExtranjero'] != '')
            {
                $xmlString .= '<IdentificacionExtranjero>'
                    . $payload['receptor']['IdentificacionExtranjero']
                    . ' </IdentificacionExtranjero>';
            }


            if (isset($payload['receptor']['identificacion']['tipo']) AND $payload['receptor']['identificacion']['tipo'] != '') {
                $xmlString .= '<Identificacion>
                    <Tipo>' . $payload['receptor']['identificacion']['tipo'] . '</Tipo>';
            } else {
                return response()->json(array("code"=>"10","data" => "Datos incompletos en la solicitud. El [tipo] de identificación del receptor es requerido", "body" => $payload,"fecha"=>$fechaEmision), 400);
            }
            if (isset($payload['receptor']['identificacion']['numero']) AND $payload['receptor']['identificacion']['numero'] != '') {
                $xmlString .='<Numero>' . $payload['receptor']['identificacion']['numero'] . '</Numero></Identificacion>';

            }
            else{
                return response()->json(array("code"=>"10","data" => "Datos incompletos en la solicitud. El [numero] de identificación del receptor es requerido", "body" => $payload,"fecha"=>$fechaEmision), 400);
            }
            if (isset($payload['receptor']['ubicacion']) AND $payload['receptor']['ubicacion'] != '' ) {
                if (!isset($payload['receptor']['ubicacion']['provincia']) OR $payload['receptor']['ubicacion']['provincia'] == '' ) {
                    return response()->json(array("code"=>"10","data" => "Datos incompletos en la solicitud. La [provincia] del receptor es requerida", "body" => $payload,"fecha"=>$fechaEmision), 400);
                }
                if (!isset($payload['receptor']['ubicacion']['canton']) OR $payload['receptor']['ubicacion']['canton'] == '' ) {
                    return response()->json(array("code"=>"10","data" => "Datos incompletos en la solicitud. El [canton] del receptor es requerido", "body" => $payload,"fecha"=>$fechaEmision), 400);
                }
                if (!isset($payload['receptor']['ubicacion']['distrito']) OR $payload['receptor']['ubicacion']['distrito'] == '' ) {
                    return response()->json(array("code"=>"10","data" => "Datos incompletos en la solicitud. El [distrito] del receptor es requerido", "body" => $payload,"fecha"=>$fechaEmision), 400);
                }
                if (!isset($payload['receptor']['ubicacion']['sennas']) OR $payload['receptor']['ubicacion']['sennas'] == '' ) {
                    return response()->json(array("code"=>"10","data" => "Datos incompletos en la solicitud. [sennas] del receptor es requerido", "body" => $payload,"fecha"=>$fechaEmision), 400);
                }
                $xmlString .= '
                    <Ubicacion>
                        <Provincia>' . $payload['receptor']['ubicacion']['provincia'] . '</Provincia>
                        <Canton>' . $payload['receptor']['ubicacion']['canton'] . '</Canton>
                        <Distrito>' . $payload['receptor']['ubicacion']['distrito'] . '</Distrito>';
                if (isset($payload['receptor']['ubicacion']['barrio']) AND $payload['receptor']['ubicacion']['barrio'] != ''){
                    $xmlString .= '
                            <Barrio>' . $payload['receptor']['ubicacion']['barrio'] . '</Barrio>';}
                $xmlString .= ' <OtrasSenas>' . $payload['receptor']['ubicacion']['sennas'] . '</OtrasSenas>
                    </Ubicacion>';

            }
            if (!empty($payload['receptor']['IdentificacionExtranjero']) AND empty($payload['receptor']['sennas_ext']))
            {
                return response()->json(array("code"=>"10","data" => "Datos incompletos en la solicitud. El [sennas_ext] del receptor es requerido", "body"=>$payload,"fecha"=>$fechaEmision), 400);
            }

            if (empty($payload['receptor']['IdentificacionExtranjero']) AND !empty($payload['receptor']['sennas_ext']))
            {
                return response()->json(array("code"=>"10","data" => "Datos incompletos en la solicitud. La [IdentificacionExtranjero] del receptor es requerido", "body"=>$payload,"fecha"=>$fechaEmision), 400);
            }
            if(!empty($payload['receptor']['IdentificacionExtranjero']) AND !empty($payload['receptor']['sennas_ext'])){
                $xmlString .= '<OtrasSenasExtranjero>'
                    . $payload['receptor']['sennas_ext']
                    . ' </OtrasSenasExtranjero>';
            }
            if (isset($payload['receptor']['telefono']) AND $payload['receptor']['telefono'] !='')
            {
                if (!isset($payload['receptor']['telefono']['cod_pais']) OR $payload['receptor']['telefono']['cod_pais'] =='')
                {
                    return response()->json(array("code"=>"10","data" => "Datos incompletos en la solicitud. El [cod_pais] del teléfono del receptor es requerido", "body" => $payload,"fecha"=>$fechaEmision), 400);
                }
                if (!isset($payload['receptor']['telefono']['numero']) OR $payload['receptor']['telefono']['numero'] =='')
                {
                    return response()->json(array("code"=>"10","data" => "Datos incompletos en la solicitud. El [numero] de teléfono del receptor es requerido", "body" => $payload,"fecha"=>$fechaEmision), 400);
                }
                $xmlString .= '<Telefono>
                                   <CodigoPais>' . $payload['receptor']['telefono']['cod_pais'] . '</CodigoPais>';
                $xmlString .= '<NumTelefono>' . $payload['receptor']['telefono']['numero'] . '</NumTelefono>
                    </Telefono>';

            }
            if (isset($payload['receptor']['fax']) AND $payload['receptor']['fax'] !='')
            {
                if (!isset($payload['receptor']['fax']['cod_pais']) OR $payload['receptor']['fax']['cod_pais'] =='')
                {
                    return response()->json(array("code"=>"10","data" => "Datos incompletos en la solicitud. El [cod_pais] del fax del receptor es requerido", "body"=>$payload,"fecha"=>$fechaEmision), 400);
                }
                if (!isset($payload['receptor']['fax']['numero']) OR $payload['receptor']['fax']['numero'] =='')
                {
                    return response()->json(array("code"=>"10","data" => "Datos incompletos en la solicitud. El [numero] de fax del receptor es requerido", "body"=>$payload,"fecha"=>$fechaEmision), 400);
                }
                $xmlString .= '<Fax>
                                   <CodigoPais>' . $payload['receptor']['telefono']['cod_pais'] . '</CodigoPais>';
                $xmlString .= '<NumTelefono>' . $payload['receptor']['telefono']['numero'] . '</NumTelefono>
                    </Fax>';

            }


            if (!isset($payload['receptor']['correo_electronico']) OR $payload['receptor']['correo_electronico'] == '')
            {
                return response()->json(array("code"=>"10","data" => "Datos incompletos en la solicitud. El [correo_electronico] del receptor es requerido", "body"=>$payload,"fecha"=>$fechaEmision), 400);
            }
            $xmlString .= '<CorreoElectronico>' . $payload['receptor']['correo_electronico'] . '</CorreoElectronico>';
            $xmlString .= '</Receptor>';

        }
        if(isset($payload['encabezado']['IdentificacionExtranjero']) AND $payload['encabezado']['IdentificacionExtranjero']!='')
        {
            $xmlString .='<IdentificacionExtranjero>'.$payload['encabezado']['IdentificacionExtranjero'].'</IdentificacionExtranjero>';
        }
        if(isset($payload['encabezado']['OtrasSenasExtranjero']) AND $payload['encabezado']['OtrasSenasExtranjero']!='')
        {
            $xmlString.='<OtrasSenasExtranjero>'.$payload['encabezado']['OtrasSenasExtranjero'].'</OtrasSenasExtranjero>';
        }
        if(!isset($payload['encabezado']['condicion_venta']) OR $payload['encabezado']['condicion_venta']=='')
        {
            return response()->json(array("code"=>"10","data" => "Datos incompletos en la solicitud. La [condicion_venta] es requerida", "body"=>$payload,"fecha"=>$fechaEmision), 400);
        }
        $xmlString .= '
        <CondicionVenta>' . $payload['encabezado']['condicion_venta'] . '</CondicionVenta>';
        if (isset($payload['encabezado']['plazo_credito']) AND $payload['encabezado']['plazo_credito']!='')
        {
            $xmlString .= '<PlazoCredito>' . $payload['encabezado']['plazo_credito'] . '</PlazoCredito>';

        }else
        {
            $xmlString .= '<PlazoCredito>0</PlazoCredito>';
        }
        if(!isset($payload['encabezado']['mediopago']) OR $payload['encabezado']['mediopago']=='')
        {
            return response()->json(array("code"=>"10","data" => "Datos incompletos en la solicitud. El [mediopago] es requerido", "body"=>$payload,"fecha"=>$fechaEmision), 400);
        }
        $xmlString .='<MedioPago>' . $payload['encabezado']['mediopago'] . '</MedioPago>';
        $xmlString .='<DetalleServicio>';

        if(!isset($payload['detalle']) OR $payload['detalle']=='')
        {
            return response()->json(array("code"=>"10","data" => "Datos incompletos en la solicitud. El [detalle] es requerido", "body"=>$payload,"fecha"=>$fechaEmision), 400);
        }
        $l = 1;
        $pos=0;
        $totalServGravados=0.00000;
        $totalServExentos=0.00000;
        $totalMercanciasGravadas=0.00000;
        $totalMercanciasExentas=0.00000;
        $totalGravado=0.00000;
        $totalExento=0.00000;
        $totalVenta=0.00000;
        $totalDescuentos=0.00000;
        $totalVentaNeta=0.00000;
        $totalImpuesto=0.00000;
        $totalComprobante=0.00000;


        foreach ($payload['detalle'] as $d)
        {

            if(!isset($d['numero']) OR $d['numero']=='')
            {
                return response()->json(array("code"=>"10","data" => "Datos incompletos en la solicitud. El [numero] de linea en la linea de detalle [".$l."] es requerido", "body" => $payload,"fecha"=>$fechaEmision), 400);
            }
            else{
                $xmlString .= '<LineaDetalle>
                  <NumeroLinea>' . $d['numero'] . '</NumeroLinea>';
            }
            if(!isset($d['codigo']) OR $d['codigo']=='')
            {
                return response()->json(array("code"=>"10","data" => "Datos incompletos en la solicitud. El [codigo] en la linea de detalle [".$l."] es requerida", "body" => $payload,"fecha"=>$fechaEmision), 400);
            }
            else{
                $xmlString .='<Codigo>' . $d['codigo'] . '</Codigo>';
            }
            if(isset($d['codigoComercial']) AND $d['codigoComercial']!='')
            {

                $xmlString.= '<CodigoComercial>';
                if(!isset($d['codigoComercial']['tipo']) OR $d['codigoComercial']['tipo']==''){
                    return response()->json(array("code"=>"10","data" => "Datos incompletos en la solicitud. El [tipo] de codigoComercial en la linea de detalle [".$l."] es requerido ", "body"=>$payload,"fecha"=>$fechaEmision), 400);
                }
                else{
                    $xmlString.='<Tipo>'.$d['codigoComercial']['tipo'].'</Tipo>';
                }
                if(!isset($d['codigoComercial']['codigo']) OR $d['codigoComercial']['codigo']==''){
                    return response()->json(array("code"=>"10","data" => "Datos incompletos en la solicitud. El [codigo] del nodo codigoComercial en la linea de detalle [".$l."] es requerido", "body"=>$payload,"fecha"=>$fechaEmision), 400);
                }
                else{
                    $xmlString.='<Codigo>'.$d['codigoComercial']['codigo']   .'</Codigo>';
                }
                $xmlString.= '</CodigoComercial>';
            }
            if(!isset($d['cantidad']) OR $d['cantidad']=='')
            {
                return response()->json(array("code"=>"10","data" => "Datos incompletos en la solicitud. La [cantidad] en la linea de detalle [".$l."] es requerida", "body" => $payload,"fecha"=>$fechaEmision), 400);
            }
            else{
                $xmlString .='<Cantidad>' . $d['cantidad'] . '</Cantidad>';
            }
            if(!isset($d['unidad_medida']) OR $d['unidad_medida']=='')
            {
                return response()->json(array("code"=>"10","data" => "Datos incompletos en la solicitud. La [unidad_medida] en la linea de detalle [".$l."] es requerida", "body" => $payload,"fecha"=>$fechaEmision), 400);
            }
            else
            {
                $xmlString.='<UnidadMedida>' . $d['unidad_medida'] . '</UnidadMedida>';
            }
            if(isset($d['unidad_medida_comercial'])){
                $xmlString.='<UnidadMedidaComercial>'.$d['unidad_medida_comercial'].'</UnidadMedidaComercial>';
            }
            if(!isset($d['detalle']) OR $d['detalle']=='')
            {
                return response()->json(array("code"=>"10","data" => "Datos incompletos en la solicitud. El [detalle] en la linea de detalle [".$l."] es requerido", "body" => $payload,"fecha"=>$fechaEmision), 400);
            }else{
                $xmlString.='<Detalle>' . $d['detalle'] . '</Detalle>';
            }
            if(!isset($d['precio_unitario']) OR $d['precio_unitario']=='')
            {
                return response()->json(array("code"=>"10","data" => "Datos incompletos en la solicitud. El [precio_unitario] en la linea de detalle [".$l."] es requerido", "body" => $payload,"fecha"=>$fechaEmision), 400);
            }
            else{
                $xmlString.='<PrecioUnitario>' . $d['precio_unitario'] . '</PrecioUnitario>';
            }
            if(!isset($d['monto_total']) OR $d['monto_total']=='')
            {
                return response()->json(array("code"=>"10","data" => "Datos incompletos en la solicitud. El [monto_total] en la linea de detalle [".$l."] es requerido", "body" => $payload,"fecha"=>$fechaEmision), 400);
            }
            else
            {
                $xmlString.='<MontoTotal>' . $d['monto_total'] . '</MontoTotal>';
            }

            if (isset($d['descuento']) && $d['descuento'] != "")
            {
                if (!isset($d['descuento']['monto']) OR $d['descuento']['monto'] == "")
                {
                    return response()->json(array("code"=>"10","data" => "Datos incompletos en la solicitud. El [monto] del nodo descuento en la linea de detalle [".$l."] es requerido", "body" => $payload,"fecha"=>$fechaEmision), 400);
                }
                else{
                    $xmlString .= '<Descuento><MontoDescuento>' . $d['descuento']['monto'] . '</MontoDescuento>';
                }
                if (!isset($d['descuento']['naturaleza_descuento']) OR $d['descuento']['naturaleza_descuento'] == "")
                {
                    return response()->json(array("code"=>"10","data" => "Datos incompletos en la solicitud. La [naturaleza_descuento] del nodo descuento en la linea de detalle [".$l."] es requerido", "body" => $payload,"fecha"=>$fechaEmision), 400);
                }
                else{
                    $xmlString .= '<NaturalezaDescuento>' . $d['descuento']['naturaleza_descuento'] . '</NaturalezaDescuento></Descuento>';
                }

            }
            if(!isset($d['subtotal']) OR $d['subtotal']==''){
                return response()->json(array("code"=>"10","data" => "Datos incompletos en la solicitud. El [subtotal] en la linea de detalle [".$l."] es requerido", "body" => $payload,"fecha"=>$fechaEmision), 400);
            }
            else{
                $xmlString .= '<SubTotal>' . $d['subtotal'] . '</SubTotal>';
            }
            if (isset($d['impuestos']) && $d['impuestos'] != "")
            {
                foreach ($d['impuestos'] as $y)
                {

                    if($y['codigo']=='07')
                    {
                        if(!isset($d['base_imponible']) OR $d['base_imponible']=='') {
                            return response()->json(array("code" => "10", "data" => "Datos incompletos en la solicitud. La [base_imponible] en la linea de detalle [" . $l . "] es requerido, debido a que uno de los impuestos tiene codigo 07", "body" => $payload, "fecha" => $fechaEmision), 400);
                        }
                        else{
                            $xmlString .= '<BaseImponible>' . $d['base_imponible'] . '</BaseImponible>';
                            break;
                        }
                    }
                }
                $numImp=1;
                foreach ($d['impuestos'] as $i)
                {
                    $xmlString .= '<Impuesto>';
                    if(!isset($i['codigo']) OR $i['codigo']=='')
                    {
                        return response()->json(array("code"=>"10","data" => "Datos incompletos en la solicitud. El [codigo] en la linea de detalle  [".$l."] de la linea del impuesto [".$numImp."] es requerido", "body" => $payload,"fecha"=>$fechaEmision), 400);
                    }else{
                        $xmlString .='<Codigo>' . $i['codigo'] . '</Codigo>';
                    }
                    if($i['codigo']=='01' OR $i['codigo']=='07')
                    {
                        if(!isset($i['codigo_tarifa']) OR $i['codigo_tarifa']=='')
                        {
                            return response()->json(array("code" => "10", "data" => "Datos incompletos en la solicitud. El [codigo_tarifa] en la linea de detalle [" . $l . "] de la linea del impuesto [".$numImp."] es requerido, debido a que uno de los impuestos tiene codigo 01 ó 07", "body" => $payload, "fecha" => $fechaEmision), 400);
                        }
                        else{
                            $xmlString .='<CodigoTarifa>' . $i['codigo_tarifa'] . '</CodigoTarifa>';
                        }
                    }
                    if(!isset($i['tarifa']))
                    {
                        return response()->json(array("code"=>"10","data" => "Datos incompletos en la solicitud. La [tarifa] en la linea de detalle  [".$l."] de la linea del impuesto [".$numImp."] es requerida", "body" => $payload,"fecha"=>$fechaEmision), 400);
                    }
                    else{
                        $xmlString .='<Tarifa>' . $i['tarifa'] . '</Tarifa>';
                    }
                    if($i['codigo']=='08')
                    {
                        if(!isset($i['factor_iva']) OR $i['factor_iva']==''){
                            return response()->json(array("code"=>"10","data" => "Datos incompletos en la solicitud. El [factor_iva] en la linea de detalle  [".$l."] de la linea del impuesto [".$numImp."] es requerida", "body" => $payload,"fecha"=>$fechaEmision), 400);
                        }
                        else{
                            $xmlString .='<FactorIVA>' . $i['factor_iva'] . '</FactorIVA>';
                        }
                    }
                    if(!isset($i['monto']))
                    {
                        return response()->json(array("code"=>"10","data" => "Datos incompletos en la solicitud. El [monto] en la linea de detalle  [".$l."] de la linea del impuesto [".$numImp."] es requerido", "body" => $payload,"fecha"=>$fechaEmision), 400);
                    }
                    else{
                        $xmlString .='<Monto>' . $i['monto'] . '</Monto>';
                        $totalImpuesto+=floatval($i['monto']);
                    }
                    if (isset($i['exoneracion']) && $i['exoneracion'] != "")
                    {
                        $xmlString .= '
                    <Exoneracion>';
                        if (!isset($i['exoneracion']['tipodocumento']) OR $i['exoneracion']['tipodocumento'] == ""){
                            return response()->json(array("code"=>"10","data" => "Datos incompletos en la solicitud. El [tipodocumento] de exoneración en la linea de detalle  [".$l."] del nodo del impuesto [".$numImp."] es requerido", "body" =>$payload,"fecha"=>$fechaEmision), 400);
                        }
                        else{
                            $xmlString .= '<TipoDocumento>' . $i['exoneracion']['tipodocumento'] . '</TipoDocumento>';
                        }
                        if (!isset($i['exoneracion']['numerodocumento']) OR $i['exoneracion']['numerodocumento'] == ""){
                            return response()->json(array("code"=>"10","data" => "Datos incompletos en la solicitud. El [numerodocumento] de exoneración en la linea de detalle  [".$l."] del nodo del impuesto [".$numImp."] es requerido", "body" =>$payload,"fecha"=>$fechaEmision), 400);
                        }
                        else{
                            $xmlString .= '<NumeroDocumento>' . $i['exoneracion']['numerodocumento'] . '</NumeroDocumento>';
                        }
                        if (!isset($i['exoneracion']['nombreinstitucion']) OR $i['exoneracion']['nombreinstitucion'] == ""){
                            return response()->json(array("code"=>"10","data" => "Datos incompletos en la solicitud. El [nombreinstitucion] de exoneración en la linea de detalle  [".$l."] del nodo del impuesto [".$numImp."] es requerido", "body" =>$payload,"fecha"=>$fechaEmision), 400);
                        }
                        else{
                            $xmlString .= '<NombreInstitucion>' . $i['exoneracion']['nombreinstitucion'] . '</NombreInstitucion>';
                        }
                        if (!isset($i['exoneracion']['fechaemision']) OR $i['exoneracion']['fechaemision'] == ""){
                            return response()->json(array("code"=>"10","data" => "La [fechaemision] de exoneración en la linea de detalle  [".$l."] del nodo del impuesto [".$numImp."] es requerido", "body" => $payload,"fecha"=>$fechaEmision), 400);
                        }else{
                            $xmlString .= '<FechaEmision>' . $i['exoneracion']['fechaemision'] . '</FechaEmision>';
                        }
                        if (!isset($i['exoneracion']['porcentaje']) OR $i['exoneracion']['porcentaje'] == ""){
                            return response()->json(array("code"=>"10","data" => "El [porcentaje] del nodo exoneración en la linea de detalle  [".$l."] del nodo del impuesto [".$numImp."] es requerido", "body" => $payload,"fecha"=>$fechaEmision), 400);
                        }else{
                            $xmlString .= '<PorcentajeExoneracion>' . $i['exoneracion']['porcentaje'] . '</PorcentajeExoneracion>';
                        }
                        if (!isset($i['exoneracion']['monto']) OR $i['exoneracion']['monto'] == ""){
                            return response()->json(array("code"=>"10","data" => "El [monto] del nodo exoneración en la linea de detalle  [".$l."] del nodo del impuesto [".$numImp."] es requerido", "body" => $payload,"fecha"=>$fechaEmision), 400);
                        }
                        else{
                            $xmlString .= '<MontoExoneracion>' . $i['exoneracion']['monto'] . '</MontoExoneracion>';
                        }

                        /*if((string)$this->getNewTaxAmount($i['monto'],$i['exoneracion']['porcentaje'])!=$i['exoneracion']['monto'])
                        {
                            return response()->json(array("code"=>"18","data"=>"El monto de exoneración según el porcentaje indicado es incorrecto","fecha"=>$fechaEmision,"detalle"=>$payload['detalle']), 400);
                        }*/

                        $xmlString .= '</Exoneracion>';

                        $totalImpuesto-=floatval($i['monto']);

                        $totalImpuesto+=$this->getNewTaxAmount($i['monto'],$i['exoneracion']['porcentaje']);

                    }

                    $xmlString .= '</Impuesto>';
                    $numImp++;

                }
                if($d['unidad_medida']=='Sp')
                {
                    $totalServGravados+=floatval($d['monto_total']);
                }
                else{
                    $totalMercanciasGravadas+=floatval($d['monto_total']);
                }

            }
            else{
                if($d['unidad_medida']=='Sp')
                {
                    $totalServExentos+=floatval($d['monto_total']);
                }
                else{
                    $totalMercanciasExentas+=floatval($d['monto_total']);
                }
            }
            if(isset($d['impuesto_neto']))
            {
                $xmlString .= '<ImpuestoNeto>' . $d['impuesto_neto'] . '</ImpuestoNeto>';
            }
            if(!isset($d['montototallinea']) OR $d['montototallinea']=='')
            {
                return response()->json( array("code"=>"10","data" => "El [montototallinea] en la linea de detalle [".$l."] es requerido", "body" => $payload,"fecha"=>$fechaEmision), 400);
            }
            else{
                $xmlString .= '<MontoTotalLinea>' . $d['montototallinea'] . '</MontoTotalLinea>';
            }

            $xmlString .= '</LineaDetalle>';
            $l++;
            $pos++;
        }
        $xmlString .= '</DetalleServicio>';
        $totalGravado=$totalServGravados + $totalMercanciasGravadas;
        $totalExento=$totalServExentos + $totalMercanciasExentas;
        $totalVenta=$totalGravado + $totalExento;
        $totalVentaNeta=$totalVenta - $totalDescuentos;
        $totalComprobante=$totalVentaNeta + $totalImpuesto;
        if(isset($payload['otros_cargos']) AND $payload['otros_cargos']!='')
        {
            $xmlString .= '<OtrosCargos>';
            if(!isset($payload['otros_cargos']['tipo_documento']) OR $payload['otros_cargos']['tipo_documento']=='')
            {
                return response()->json( array("code"=>"10","data" => "El [tipo_documento] del nodo otros_cargos es requerido", "body" => $payload,"fecha"=>$fechaEmision), 400);
            }
            else{
                $xmlString .= '<TipoDocumento>' . $payload['otros_cargos']['tipo_documento'] . '</TipoDocumento>';
            }
            if(!isset($payload['otros_cargos']['num_identidad_tercero']) OR $payload['otros_cargos']['num_identidad_tercero']=='')
            {
                return response()->json( array("code"=>"10","data" => "El [num_identidad_tercero] del nodo otros_cargos es requerido", "body" => $payload,"fecha"=>$fechaEmision), 400);
            }
            else{
                $xmlString .= '<NumeroIdentidadTercero>' . $payload['otros_cargos']['num_identidad_tercero'] . '</NumeroIdentidadTercero>';
            }
            if(!isset($payload['otros_cargos']['nombre_tercero']) OR $payload['otros_cargos']['nombre_tercero']=='')
            {
                return response()->json( array("code"=>"10","data" => "El [nombre_tercero] del nodo otros_cargos es requerido", "body" => $payload,"fecha"=>$fechaEmision), 400);
            }
            else{
                $xmlString .= '<NombreTercero>' . $payload['otros_cargos']['nombre_tercero'] . '</NombreTercero>';
            }
            if(!isset($payload['otros_cargos']['detalle']) OR $payload['otros_cargos']['detalle']=='')
            {
                return response()->json( array("code"=>"10","data" => "El [detalle] del nodo otros_cargos es requerido", "body" => $payload,"fecha"=>$fechaEmision), 400);
            }
            else{
                $xmlString .= '<Detalle>' . $payload['otros_cargos']['detalle'] . '</Detalle>';
            }
            if(!isset($payload['otros_cargos']['porcentaje']) OR $payload['otros_cargos']['porcentaje']=='')
            {
                return response()->json( array("code"=>"10","data" => "El [porcentaje] del nodo otros_cargos es requerido", "body" => $payload,"fecha"=>$fechaEmision), 400);
            }
            else{
                $xmlString .= '<Porcentaje>' . $payload['otros_cargos']['porcentaje'] . '</Porcentaje>';
            }
            if(!isset($payload['otros_cargos']['monto_cargo']) OR $payload['otros_cargos']['monto_cargo']=='')
            {
                return response()->json( array("code"=>"10","data" => "El [monto_cargo] del nodo otros_cargos es requerido", "body" => $payload,"fecha"=>$fechaEmision), 400);
            }
            else{
                $xmlString .= '<MontoCargo>' . $payload['otros_cargos']['monto_cargo'] . '</MontoCargo>';
            }
            $xmlString .= '</OtrosCargos>';
        }


        if(!isset($payload['resumen']) OR $payload['resumen']==''){
            return response()->json(array("code"=>"10","data" => "Datos incompletos en la solicitud. El [resumen] es requerido", "body" => $payload,"fecha"=>$fechaEmision), 400);
        }

        //Validación de los totales con respectos a las totales del detalle
        /*if((string)$totalServGravados!=$payload['resumen']['totalserviciogravado']
            OR (string)$totalServExentos!=$payload['resumen']['totalservicioexento']
            OR (string)$totalMercanciasGravadas!=$payload['resumen']['totalmercaderiagravado']
            OR (string)$totalMercanciasExentas!=$payload['resumen']['totalmercaderiaexento']
            OR (string)$totalGravado!=$payload['resumen']['totalgravado']
            OR (string)$totalExento!=$payload['resumen']['totalexento']
            OR (string)$totalVenta!=$payload['resumen']['totalventa']
            OR (string)$totalDescuentos!=$payload['resumen']['totaldescuentos']
            OR (string)$totalVentaNeta!=$payload['resumen']['totalventaneta']
            OR (string)$totalImpuesto!=$payload['resumen']['totalimpuestos']
            OR (string)$totalComprobante!=$payload['resumen']['totalcomprobante'])
        {
            return response()->json(array("code"=>"18","data"=>"Alguno de los montos de las facturas no coinciden con los montos de los
detalles correspondientes.","fecha"=>$fechaEmision,"detalle"=>$payload['detalle'],"resumen"=>$payload['resumen']), 400);
        }*/
        $xmlString .= '<ResumenFactura>';
        if(isset($payload['resumen']['codigo_tipo_moneda']))
        {
            if(!isset($payload['resumen']['codigo_tipo_moneda']['moneda']) OR $payload['resumen']['codigo_tipo_moneda']['moneda']==''
                OR !isset($payload['resumen']['codigo_tipo_moneda']['tipo_cambio']) OR $payload['resumen']['codigo_tipo_moneda']['tipo_cambio']=='')
            {
                return response()->json(array("code"=>"10","data" => "Datos incompletos en la solicitud. El nodo [codigo_tipo_moneda] es requerido y debe estar completo cuando la moneda es extranjera, en caso contrario no utilice", "body" => $payload,"fecha"=>$fechaEmision), 400);
            }
            else{

                $xmlString .= '<CodigoTipoMoneda><CodigoMoneda>' . $payload['resumen']['codigo_tipo_moneda']['moneda'] . '</CodigoMoneda><TipoCambio>' . $payload['resumen']['codigo_tipo_moneda']['tipo_cambio'] . '</TipoCambio></CodigoTipoMoneda>';
            }

        }
        else {
            $xmlString .= '<CodigoTipoMoneda><CodigoMoneda>CRC</CodigoMoneda><TipoCambio>1</TipoCambio></CodigoTipoMoneda>';
        }
        if(isset($payload['resumen']['totalserviciogravado']) AND $payload['resumen']['totalserviciogravado']!=''){
            $xmlString .= '<TotalServGravados>' . $payload['resumen']['totalserviciogravado'] . '</TotalServGravados>';

        }
        if(isset($payload['resumen']['totalservicioexento']) AND $payload['resumen']['totalservicioexento']!=''){
            $xmlString .= '<TotalServExentos>' . $payload['resumen']['totalservicioexento'] . '</TotalServExentos>';
        }
        if(isset($payload['resumen']['totalservicioexonerado']) AND $payload['resumen']['totalservicioexonerado']!=''){
            $xmlString .= '<TotalServExonerado>' . $payload['resumen']['totalservicioexento'] . '</TotalServExonerado>';
        }
        if(isset($payload['resumen']['totalmercanciagravada']) AND $payload['resumen']['totalmercanciagravada']!=''){
            $xmlString .= '<TotalMercanciasGravadas>' . $payload['resumen']['totalmercanciagravada'] . '</TotalMercanciasGravadas>';
        }
        if(isset($payload['resumen']['totalmercanciaexenta']) AND $payload['resumen']['totalmercanciaexenta']!=''){
            $xmlString .= '<TotalMercanciasExentas>' . $payload['resumen']['totalmercanciaexenta'] . '</TotalMercanciasExentas>';
        }
        if(isset($payload['resumen']['totalmercanciaexonerada']) AND $payload['resumen']['totalmercanciaexonerada']!=''){
            $xmlString .= '<TotalMercExonerada>' . $payload['resumen']['totalmercanciaexonerada'] . '</TotalMercExonerada>';
        }
        if(isset($payload['resumen']['totalgravado']) AND $payload['resumen']['totalgravado']!=''){
            $xmlString .= '<TotalGravado>' . $payload['resumen']['totalgravado'] . '</TotalGravado>';
        }
        if(isset($payload['resumen']['totalexento']) AND $payload['resumen']['totalexento']!=''){
            $xmlString .= '<TotalExento>' . $payload['resumen']['totalexento'] . '</TotalExento>';
        }
        if(isset($payload['resumen']['totalexonerado']) AND $payload['resumen']['totalexonerado']!=''){
            $xmlString .= '<TotalExonerado>' . $payload['resumen']['totalexonerado'] . '</TotalExonerado>';
        }
        if(!isset($payload['resumen']['totalventa']) OR $payload['resumen']['totalventa']==''){
            return response()->json(array("code"=>"10","data" => "Datos incompletos en la solicitud. El [totalventa] es requerido", "body" => $payload,"fecha"=>$fechaEmision), 400);

        }else{
            $xmlString .= '<TotalVenta>' . $payload['resumen']['totalventa'] . '</TotalVenta>';
        }
        if(isset($payload['resumen']['totaldescuentos']) AND $payload['resumen']['totaldescuentos']!=''){
            $xmlString .= '<TotalDescuentos>' . $payload['resumen']['totaldescuentos'] . '</TotalDescuentos>';
        }
        if(!isset($payload['resumen']['totalventaneta']) OR $payload['resumen']['totalventaneta']==''){
            return response()->json(array("code"=>"10","data" => "Datos incompletos en la solicitud. El [totalventaneta] es requerido", "body" => $payload,"fecha"=>$fechaEmision), 400);

        }else{
            $xmlString .= '<TotalVentaNeta>' . $payload['resumen']['totalventaneta'] . '</TotalVentaNeta>';
        }
        if(isset($payload['resumen']['totalimpuestos']) AND $payload['resumen']['totalimpuestos']!=''){
            $xmlString .= '<TotalImpuesto>' . $payload['resumen']['totalimpuestos'] . '</TotalImpuesto>';
        }
        if(isset($payload['resumen']['totalivadevuelto']) AND $payload['resumen']['totalivadevuelto']!=''){
            $xmlString .= '<TotalIVADevuelto>' . $payload['resumen']['totalivadevuelto'] . '</TotalIVADevuelto>';
        }
        if(isset($payload['resumen']['totalotroscargos']) AND $payload['resumen']['totalotroscargos']!=''){
            $xmlString .= '<TotalOtrosCargos>' . $payload['resumen']['totalotroscargos'] . '</TotalOtrosCargos>';
        }
        if(!isset($payload['resumen']['totalcomprobante']) OR $payload['resumen']['totalcomprobante']==''){
            return response()->json(array("code"=>"10","data" => "Datos incompletos en la solicitud. El [totalcomprobante] es requerido", "body" => $payload,"fecha"=>$fechaEmision), 400);

        }else{
            $xmlString .= '<TotalComprobante>' . $payload['resumen']['totalcomprobante'] . '</TotalComprobante>';
        }
        $xmlString .= '</ResumenFactura>';
        $ot=0;
        if (isset($payload['otros']) AND $payload['otros'] != '')
        {

            if(!isset($payload['otros']['contenido']) OR $payload['otros']['contenido']=='')
            {
                return response()->json(array("code"=>"10","data" => "Datos incompletos en la solicitud. El [contenido] del nodo otros es requerido", "body" => $payload,"fecha"=>$fechaEmision), 400);
            }
            else{
                $xmlString .= '<Otros><OtroTexto>'.$payload['otros']['contenido'].'</OtroTexto></Otros>';
            }
        }

        $xmlString .= '
    </TiqueteElectronico>';
        return response()->json(array("code"=>"1","data"=>base64_encode($xmlString),"fecha"=>$fechaEmision), 200);
    }
    public function makeNC($payload)
    {
        $consecutivo = substr($payload['clave'], 21, 20);
        $fechaEmision=date(DATE_RFC3339);
        /*if(!isset($payload['encabezado']['fecha']) or empty($payload['encabezado']['fecha']))
        {
            return response()->json(array("response"=>"La fecha del comprobante es requerida","estado"=>"error"), 400);
        }*/
        if(!isset($payload['emisor']) or empty($payload['emisor']))
        {
            return response()->json(array("code"=>"10","data"=>"Datos incompletos en la solicitud. El [emisor] es requerido","body"=>$payload,"fecha"=>$fechaEmision), 400);
        }
        if(!isset($payload['emisor']['nombre']) or empty($payload['emisor']['nombre']))
        {
            return response()->json(array("code"=>"10","data"=>"Datos incompletos en la solicitud. El [nombre] del emisor es requerido","body"=>$payload,"fecha"=>$fechaEmision), 400);
        }
        if(!isset($payload['emisor']['identificacion']) or empty($payload['emisor']['identificacion']))
        {
            return response()->json(array("code"=>"10","data"=>"Datos incompletos en la solicitud. La [identificación] del emisor es requerido","body"=>$payload,"fecha"=>$fechaEmision), 400);
        }
        if(!isset($payload['emisor']['identificacion']['tipo']) or empty($payload['emisor']['identificacion']['tipo']))
        {
            return response()->json(array("code"=>"10","data"=>"Datos incompletos en la solicitud. El [tipo] de identificación del emisor es requerido","body"=>$payload,"fecha"=>$fechaEmision), 400);
        }
        if(!isset($payload['emisor']['identificacion']['numero']) or empty($payload['emisor']['identificacion']['numero']))
        {
            return response()->json(array("code"=>"10","data"=>"Datos incompletos en la solicitud. El [numero] de identificación del emisor es requerido","body"=>$payload,"fecha"=>$fechaEmision), 400);
        }
        if(!isset($payload['emisor']['ubicacion']) or empty($payload['emisor']['ubicacion']))
        {
            return response()->json(array("code"=>"10","data"=>"Datos incompletos en la solicitud. La [ubicacion] del emisor es requerida","body"=>$payload,"fecha"=>$fechaEmision), 400);
        }
        if(!isset($payload['emisor']['ubicacion']['provincia']) or empty($payload['emisor']['ubicacion']['provincia']))
        {
            return response()->json(array("code"=>"10","data"=>"Datos incompletos en la solicitud. La [provincia] del emisor es requerida","body"=>$payload,"fecha"=>$fechaEmision), 400);
        }
        if(!isset($payload['emisor']['ubicacion']['canton']) or empty($payload['emisor']['ubicacion']['canton']))
        {
            return response()->json(array("code"=>"10","data"=>"Datos incompletos en la solicitud. El [canton] del emisor es requerido","body"=>$payload,"fecha"=>$fechaEmision), 400);
        }
        if(!isset($payload['emisor']['ubicacion']['distrito']) or empty($payload['emisor']['ubicacion']['distrito']))
        {
            return response()->json(array("code"=>"10","data"=>"Datos incompletos en la solicitud. El [distrito] del emisor es requerido","body"=>$payload,"fecha"=>$fechaEmision), 400);
        }
        if(!isset($payload['emisor']['ubicacion']['sennas']) or empty($payload['emisor']['ubicacion']['sennas']))
        {
            return response()->json(array("code"=>"10","data"=>"Datos incompletos en la solicitud. [sennas] del emisor es requerido","body"=>$payload,"fecha"=>$fechaEmision), 400);
        }
        //SE VALIDA QUE SI EL NODO TELEFON SE INCLUYE, ENTONCES QUE LO QUE ESTÉ ADENTRO ESTÉ COMPLETO
        if(isset($payload['emisor']['telefono']) AND !empty($payload['emisor']['telefono']))
        {
            if(!isset($payload['emisor']['telefono']['cod_pais']) or empty($payload['emisor']['telefono']['cod_pais']))
            {
                return response()->json(array("code"=>"10","data"=>"Datos incompletos en la solicitud. [cod_pais] del telefono del emisor es requerido","body"=>$payload,"fecha"=>$fechaEmision), 400);
            }
            if(!isset($payload['emisor']['telefono']['numero']) or empty($payload['emisor']['telefono']['numero']))
            {
                return response()->json(array("code"=>"10","data"=>"Datos incompletos en la solicitud. El [numero] de telefono del emisor es requerido","body"=>$payload,"fecha"=>$fechaEmision), 400);
            }

        }
        if(isset($payload['emisor']['fax']) AND !empty($payload['emisor']['fax']))
        {
            if(!isset($payload['emisor']['fax']['cod_pais']) or empty($payload['emisor']['fax']['cod_pais']))
            {
                return response()->json(array("code"=>"10","data"=>"Datos incompletos en la solicitud. [cod_pais] del fax del emisor es requerido","body"=>$payload,"fecha"=>$fechaEmision), 400);
            }
            if(!isset($payload['emisor']['fax']['numero']) or empty($payload['emisor']['fax']['numero']))
            {
                return response()->json(array("code"=>"10","data"=>"Datos incompletos en la solicitud. El [numero] de fax del emisor es requerido","body"=>$payload,"fecha"=>$fechaEmision), 400);
            }

        }
        if(!isset($payload['emisor']['correo_electronico']) or empty($payload['emisor']['correo_electronico']))
        {
            return response()->json(array("code"=>"10","data"=>"Datos incompletos en la solicitud. El [correo_electronico] del emisor es requerido","body"=>$payload,"fecha"=>$fechaEmision), 400);
        }


        $xmlString = '<?xml version = "1.0" encoding = "utf-8"?>
    <NotaCreditoElectronica xmlns:xsd="http://www.w3.org/2001/XMLSchema" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns="https://cdn.comprobanteselectronicos.go.cr/xml-schemas/v4.3/notaCreditoElectronica">
    <Clave>'.$payload['clave'] . '</Clave>
    <CodigoActividad>'.$payload['codigo_actividad'].'</CodigoActividad>
        <NumeroConsecutivo>' . $consecutivo . '</NumeroConsecutivo>
        <FechaEmision>' . /*$payload['encabezado']['fecha']*/ $fechaEmision. '</FechaEmision>
        <Emisor>
            <Nombre>' . $payload['emisor']['nombre'] . '</Nombre>
            <Identificacion>
                <Tipo>' . $payload['emisor']['identificacion']['tipo'] . '</Tipo>
                <Numero>' . $payload['emisor']['identificacion']['numero'] . '</Numero>
            </Identificacion>
            <NombreComercial>' . $payload['emisor']['nombre_comercial'] . '</NombreComercial>';
        $xmlString .= '
        <Ubicacion>
            <Provincia>' . $payload['emisor']['ubicacion']['provincia'] . '</Provincia>
            <Canton>' . $payload['emisor']['ubicacion']['canton'] . '</Canton>
            <Distrito>' . $payload['emisor']['ubicacion']['distrito'] . '</Distrito>';
        if (isset($payload['emisor']['ubicacion']['barrio']) AND $payload['emisor']['ubicacion']['barrio'] != ''){
            $xmlString .= '<Barrio>' . $payload['emisor']['ubicacion']['barrio'] . '</Barrio>';}
        else{
            $xmlString .= '<Barrio>01</Barrio>';
        }
        $xmlString .= '
                <OtrasSenas>' . $payload['emisor']['ubicacion']['sennas'] . '</OtrasSenas>
            </Ubicacion>';


        if (isset($payload['emisor']['telefono']['cod_pais']) AND $payload['emisor']['telefono']['cod_pais'] != '' AND isset($payload['emisor']['telefono']['numero']) AND $payload['emisor']['telefono']['numero'] != '') {
            $xmlString .= '
            <Telefono>
                <CodigoPais>' . $payload['emisor']['telefono']['cod_pais'] . '</CodigoPais>
                <NumTelefono>' . $payload['emisor']['telefono']['numero'] . '</NumTelefono>
            </Telefono>';
        }


        if (isset($payload['emisor']['fax']['cod_pais']) AND $payload['emisor']['fax']['cod_pais'] != '' AND isset($payload['emisor']['fax']['numero']) AND $payload['emisor']['fax']['numero'] != '') {
            $xmlString .= '
            <Fax>
                <CodigoPais>' . $payload['emisor']['fax']['cod_pais'] . '</CodigoPais>
                <NumTelefono>' . $payload['emisor']['fax']['numero'] . '</NumTelefono>
            </Fax>';
        }

        $xmlString .= '<CorreoElectronico>' . $payload['emisor']['correo_electronico'] . '</CorreoElectronico>
        </Emisor>';


        if (isset($payload['receptor']['nombre']) and $payload['receptor']['nombre'] != '')
        {
            $xmlString .= '<Receptor>
            <Nombre>' . $payload['receptor']['nombre'] . '</Nombre>';


            if (isset($payload['receptor']['IdentificacionExtranjero']) AND $payload['receptor']['IdentificacionExtranjero'] != '')
            {
                $xmlString .= '<IdentificacionExtranjero>'
                    . $payload['receptor']['IdentificacionExtranjero']
                    . ' </IdentificacionExtranjero>';
            }


            if (isset($payload['receptor']['identificacion']['tipo']) AND $payload['receptor']['identificacion']['tipo'] != '') {
                $xmlString .= '<Identificacion>
                    <Tipo>' . $payload['receptor']['identificacion']['tipo'] . '</Tipo>';
            } else {
                return response()->json(array("code"=>"10","data" => "Datos incompletos en la solicitud. El [tipo] de identificación del receptor es requerido", "body" => $payload,"fecha"=>$fechaEmision), 400);
            }
            if (isset($payload['receptor']['identificacion']['numero']) AND $payload['receptor']['identificacion']['numero'] != '') {
                $xmlString .='<Numero>' . $payload['receptor']['identificacion']['numero'] . '</Numero></Identificacion>';

            }
            else{
                return response()->json(array("code"=>"10","data" => "Datos incompletos en la solicitud. El [numero] de identificación del receptor es requerido", "body" => $payload,"fecha"=>$fechaEmision), 400);
            }
            if (isset($payload['receptor']['ubicacion']) AND $payload['receptor']['ubicacion'] != '' ) {
                if (!isset($payload['receptor']['ubicacion']['provincia']) OR $payload['receptor']['ubicacion']['provincia'] == '' ) {
                    return response()->json(array("code"=>"10","data" => "Datos incompletos en la solicitud. La [provincia] del receptor es requerida", "body" => $payload,"fecha"=>$fechaEmision), 400);
                }
                if (!isset($payload['receptor']['ubicacion']['canton']) OR $payload['receptor']['ubicacion']['canton'] == '' ) {
                    return response()->json(array("code"=>"10","data" => "Datos incompletos en la solicitud. El [canton] del receptor es requerido", "body" => $payload,"fecha"=>$fechaEmision), 400);
                }
                if (!isset($payload['receptor']['ubicacion']['distrito']) OR $payload['receptor']['ubicacion']['distrito'] == '' ) {
                    return response()->json(array("code"=>"10","data" => "Datos incompletos en la solicitud. El [distrito] del receptor es requerido", "body" => $payload,"fecha"=>$fechaEmision), 400);
                }
                if (!isset($payload['receptor']['ubicacion']['sennas']) OR $payload['receptor']['ubicacion']['sennas'] == '' ) {
                    return response()->json(array("code"=>"10","data" => "Datos incompletos en la solicitud. [sennas] del receptor es requerido", "body" => $payload,"fecha"=>$fechaEmision), 400);
                }
                $xmlString .= '
                    <Ubicacion>
                        <Provincia>' . $payload['receptor']['ubicacion']['provincia'] . '</Provincia>
                        <Canton>' . $payload['receptor']['ubicacion']['canton'] . '</Canton>
                        <Distrito>' . $payload['receptor']['ubicacion']['distrito'] . '</Distrito>';
                if (isset($payload['receptor']['ubicacion']['barrio']) AND $payload['receptor']['ubicacion']['barrio'] != ''){
                    $xmlString .= '
                            <Barrio>' . $payload['receptor']['ubicacion']['barrio'] . '</Barrio>';}
                $xmlString .= ' <OtrasSenas>' . $payload['receptor']['ubicacion']['sennas'] . '</OtrasSenas>
                    </Ubicacion>';

            }
            if (!empty($payload['receptor']['IdentificacionExtranjero']) AND empty($payload['receptor']['sennas_ext']))
            {
                return response()->json(array("code"=>"10","data" => "Datos incompletos en la solicitud. El [sennas_ext] del receptor es requerido", "body"=>$payload,"fecha"=>$fechaEmision), 400);
            }

            if (empty($payload['receptor']['IdentificacionExtranjero']) AND !empty($payload['receptor']['sennas_ext']))
            {
                return response()->json(array("code"=>"10","data" => "Datos incompletos en la solicitud. La [IdentificacionExtranjero] del receptor es requerido", "body"=>$payload,"fecha"=>$fechaEmision), 400);
            }
            if(!empty($payload['receptor']['IdentificacionExtranjero']) AND !empty($payload['receptor']['sennas_ext'])){
                $xmlString .= '<OtrasSenasExtranjero>'
                    . $payload['receptor']['sennas_ext']
                    . ' </OtrasSenasExtranjero>';
            }
            if (isset($payload['receptor']['telefono']) AND $payload['receptor']['telefono'] !='')
            {
                if (!isset($payload['receptor']['telefono']['cod_pais']) OR $payload['receptor']['telefono']['cod_pais'] =='')
                {
                    return response()->json(array("code"=>"10","data" => "Datos incompletos en la solicitud. El [cod_pais] del teléfono del receptor es requerido", "body" => $payload,"fecha"=>$fechaEmision), 400);
                }
                if (!isset($payload['receptor']['telefono']['numero']) OR $payload['receptor']['telefono']['numero'] =='')
                {
                    return response()->json(array("code"=>"10","data" => "Datos incompletos en la solicitud. El [numero] de teléfono del receptor es requerido", "body" => $payload,"fecha"=>$fechaEmision), 400);
                }
                $xmlString .= '<Telefono>
                                   <CodigoPais>' . $payload['receptor']['telefono']['cod_pais'] . '</CodigoPais>';
                $xmlString .= '<NumTelefono>' . $payload['receptor']['telefono']['numero'] . '</NumTelefono>
                    </Telefono>';

            }
            if (isset($payload['receptor']['fax']) AND $payload['receptor']['fax'] !='')
            {
                if (!isset($payload['receptor']['fax']['cod_pais']) OR $payload['receptor']['fax']['cod_pais'] =='')
                {
                    return response()->json(array("code"=>"10","data" => "Datos incompletos en la solicitud. El [cod_pais] del fax del receptor es requerido", "body"=>$payload,"fecha"=>$fechaEmision), 400);
                }
                if (!isset($payload['receptor']['fax']['numero']) OR $payload['receptor']['fax']['numero'] =='')
                {
                    return response()->json(array("code"=>"10","data" => "Datos incompletos en la solicitud. El [numero] de fax del receptor es requerido", "body"=>$payload,"fecha"=>$fechaEmision), 400);
                }
                $xmlString .= '<Fax>
                                   <CodigoPais>' . $payload['receptor']['telefono']['cod_pais'] . '</CodigoPais>';
                $xmlString .= '<NumTelefono>' . $payload['receptor']['telefono']['numero'] . '</NumTelefono>
                    </Fax>';

            }


            if (!isset($payload['receptor']['correo_electronico']) OR $payload['receptor']['correo_electronico'] == '')
            {
                return response()->json(array("code"=>"10","data" => "Datos incompletos en la solicitud. El [correo_electronico] del receptor es requerido", "body"=>$payload,"fecha"=>$fechaEmision), 400);
            }
            $xmlString .= '<CorreoElectronico>' . $payload['receptor']['correo_electronico'] . '</CorreoElectronico>';
            $xmlString .= '</Receptor>';

        }
        if(!isset($payload['encabezado']['condicion_venta']) OR $payload['encabezado']['condicion_venta']=='')
        {
            return response()->json(array("code"=>"10","data" => "Datos incompletos en la solicitud. La [condicion_venta] es requerida", "body"=>$payload,"fecha"=>$fechaEmision), 400);
        }
        $xmlString .= '
        <CondicionVenta>' . $payload['encabezado']['condicion_venta'] . '</CondicionVenta>';
        if (isset($payload['encabezado']['plazo_credito']) AND $payload['encabezado']['plazo_credito']!='')
        {
            $xmlString .= '<PlazoCredito>' . $payload['encabezado']['plazo_credito'] . '</PlazoCredito>';

        }else
        {
            $xmlString .= '<PlazoCredito>0</PlazoCredito>';
        }
        if(!isset($payload['encabezado']['mediopago']) OR $payload['encabezado']['mediopago']=='')
        {
            return response()->json(array("code"=>"10","data" => "Datos incompletos en la solicitud. El [mediopago] es requerido", "body"=>$payload,"fecha"=>$fechaEmision), 400);
        }
        $xmlString .='<MedioPago>' . $payload['encabezado']['mediopago'] . '</MedioPago>';
        $xmlString .='<DetalleServicio>';

        if(!isset($payload['detalle']) OR $payload['detalle']=='')
        {
            return response()->json(array("code"=>"10","data" => "Datos incompletos en la solicitud. El [detalle] es requerido", "body"=>$payload,"fecha"=>$fechaEmision), 400);
        }
        $l = 1;
        $pos=0;
        $totalServGravados=0.00000;
        $totalServExentos=0.00000;
        $totalMercanciasGravadas=0.00000;
        $totalMercanciasExentas=0.00000;
        $totalGravado=0.00000;
        $totalExento=0.00000;
        $totalVenta=0.00000;
        $totalDescuentos=0.00000;
        $totalVentaNeta=0.00000;
        $totalImpuesto=0.00000;
        $totalComprobante=0.00000;


        foreach ($payload['detalle'] as $d)
        {

            if(!isset($d['numero']) OR $d['numero']=='')
            {
                return response()->json(array("code"=>"10","data" => "Datos incompletos en la solicitud. El [numero] de linea en la linea de detalle [".$l."] es requerido", "body" => $payload,"fecha"=>$fechaEmision), 400);
            }
            else{
                $xmlString .= '<LineaDetalle>
                  <NumeroLinea>' . $d['numero'] . '</NumeroLinea>';
            }

            if(!isset($payload['referencia']) OR empty($payload['referencia']))
            {
                return response()->json(array("code"=>"10","data" => "Datos incompletos en la solicitud. La [referencia] es requerida para este tipo de comprobante", "body" => $payload,"fecha"=>$fechaEmision), 400);
            }
            else{

                if(substr($payload['referencia']['numero_documento'], 29, 2)=='09')
                {
                    if($d['unidad_medida']!='Al' AND $d['unidad_medida']!='Alc' AND $d['unidad_medida']!='Cm' AND $d['unidad_medida']!='I'
                        AND $d['unidad_medida']!='Os' AND $d['unidad_medida']!='Sp' AND $d['unidad_medida']!='Spe' AND $d['unidad_medida']!='St')
                    {
                        if(!isset($d['partida_arancelaria']) OR $d['partida_arancelaria']=='')
                        {
                            return response()->json(array("code"=>"10","data" => "Datos incompletos en la solicitud. La [partida_arancelaria] en la linea de detalle [".$l."] es requerida", "body" => $payload,"fecha"=>$fechaEmision), 400);
                        }
                        else{
                            $xmlString .= '<PartidaArancelaria>' . $d['partida_arancelaria'] . '</PartidaArancelaria>';
                        }
                    }
                }
            }
            if(!isset($d['codigo']) OR $d['codigo']=='')
            {
                return response()->json(array("code"=>"10","data" => "Datos incompletos en la solicitud. El [codigo] en la linea de detalle [".$l."] es requerida", "body" => $payload,"fecha"=>$fechaEmision), 400);
            }
            else{
                $xmlString .='<Codigo>' . $d['codigo'] . '</Codigo>';
            }

            if(isset($d['codigoComercial']) AND $d['codigoComercial']!='')
            {

                $xmlString.= '<CodigoComercial>';
                if(!isset($d['codigoComercial']['tipo']) OR $d['codigoComercial']['tipo']==''){
                    return response()->json(array("code"=>"10","data" => "Datos incompletos en la solicitud. El [tipo] de codigoComercial en la linea de detalle [".$l."] es requerido ", "body"=>$payload,"fecha"=>$fechaEmision), 400);
                }
                else{
                    $xmlString.='<Tipo>'.$d['codigoComercial']['tipo'].'</Tipo>';
                }
                if(!isset($d['codigoComercial']['codigo']) OR $d['codigoComercial']['codigo']==''){
                    return response()->json(array("code"=>"10","data" => "Datos incompletos en la solicitud. El [codigo] del nodo codigoComercial en la linea de detalle [".$l."] es requerido", "body"=>$payload,"fecha"=>$fechaEmision), 400);
                }
                else{
                    $xmlString.='<Codigo>'.$d['codigoComercial']['codigo']   .'</Codigo>';
                }
                $xmlString.= '</CodigoComercial>';
            }
            if(!isset($d['cantidad']) OR $d['cantidad']=='')
            {
                return response()->json(array("code"=>"10","data" => "Datos incompletos en la solicitud. La [cantidad] en la linea de detalle [".$l."] es requerida", "body" => $payload,"fecha"=>$fechaEmision), 400);
            }
            else{
                $xmlString .='<Cantidad>' . $d['cantidad'] . '</Cantidad>';
            }
            if(!isset($d['unidad_medida']) OR $d['unidad_medida']=='')
            {
                return response()->json(array("code"=>"10","data" => "Datos incompletos en la solicitud. La [unidad_medida] en la linea de detalle [".$l."] es requerida", "body" => $payload,"fecha"=>$fechaEmision), 400);
            }
            else
            {
                $xmlString.='<UnidadMedida>' . $d['unidad_medida'] . '</UnidadMedida>';
            }
            if(isset($d['unidad_medida_comercial'])){
                $xmlString.='<UnidadMedidaComercial>'.$d['unidad_medida_comercial'].'</UnidadMedidaComercial>';
            }
            if(!isset($d['detalle']) OR $d['detalle']=='')
            {
                return response()->json(array("code"=>"10","data" => "Datos incompletos en la solicitud. El [detalle] en la linea de detalle [".$l."] es requerido", "body" => $payload,"fecha"=>$fechaEmision), 400);
            }else{
                $xmlString.='<Detalle>' . $d['detalle'] . '</Detalle>';
            }
            if(!isset($d['precio_unitario']) OR $d['precio_unitario']=='')
            {
                return response()->json(array("code"=>"10","data" => "Datos incompletos en la solicitud. El [precio_unitario] en la linea de detalle [".$l."] es requerido", "body" => $payload,"fecha"=>$fechaEmision), 400);
            }
            else{
                $xmlString.='<PrecioUnitario>' . $d['precio_unitario'] . '</PrecioUnitario>';
            }
            if(!isset($d['monto_total']) OR $d['monto_total']=='')
            {
                return response()->json(array("code"=>"10","data" => "Datos incompletos en la solicitud. El [monto_total] en la linea de detalle [".$l."] es requerido", "body" => $payload,"fecha"=>$fechaEmision), 400);
            }
            else
            {
                $xmlString.='<MontoTotal>' . $d['monto_total'] . '</MontoTotal>';
            }

            if (isset($d['descuento']) && $d['descuento'] != "")
            {
                if (!isset($d['descuento']['monto']) OR $d['descuento']['monto'] == "")
                {
                    return response()->json(array("code"=>"10","data" => "Datos incompletos en la solicitud. El [monto] del nodo descuento en la linea de detalle [".$l."] es requerido", "body" => $payload,"fecha"=>$fechaEmision), 400);
                }
                else{
                    $xmlString .= '<Descuento><MontoDescuento>' . $d['descuento']['monto'] . '</MontoDescuento>';
                }
                if (!isset($d['descuento']['naturaleza_descuento']) OR $d['descuento']['naturaleza_descuento'] == "")
                {
                    return response()->json(array("code"=>"10","data" => "Datos incompletos en la solicitud. La [naturaleza_descuento] del nodo descuento en la linea de detalle [".$l."] es requerido", "body" => $payload,"fecha"=>$fechaEmision), 400);
                }
                else{
                    $xmlString .= '<NaturalezaDescuento>' . $d['descuento']['naturaleza_descuento'] . '</NaturalezaDescuento></Descuento>';
                }

            }
            if(!isset($d['subtotal']) OR $d['subtotal']==''){
                return response()->json(array("code"=>"10","data" => "Datos incompletos en la solicitud. El [subtotal] en la linea de detalle [".$l."] es requerido", "body" => $payload,"fecha"=>$fechaEmision), 400);
            }
            else{
                $xmlString .= '<SubTotal>' . $d['subtotal'] . '</SubTotal>';
            }

            if (isset($d['impuestos']) && $d['impuestos'] != "")
            {
                foreach ($d['impuestos'] as $y) {
                if($y['codigo']=='07')
                {
                    if(!isset($d['base_imponible']) OR $d['base_imponible']=='') {
                        return response()->json(array("code" => "10", "data" => "Datos incompletos en la solicitud. La [base_imponible] en la linea de detalle [" . $l . "] es requerido, debido a que uno de los impuestos tiene codigo 07", "body" => $payload, "fecha" => $fechaEmision), 400);
                    }
                    else{
                        $xmlString .= '<BaseImponible>' . $d['base_imponible'] . '</BaseImponible>';
                        break;
                    }
                }
            }
                $numImp=1;
                foreach ($d['impuestos'] as $i)
                {
                    $xmlString .= '<Impuesto>';
                    if(!isset($i['codigo']) OR $i['codigo']=='')
                    {
                        return response()->json(array("code"=>"10","data" => "Datos incompletos en la solicitud. El [codigo] en la linea de detalle  [".$l."] de la linea del impuesto [".$numImp."] es requerido", "body" => $payload,"fecha"=>$fechaEmision), 400);
                    }else{
                        $xmlString .='<Codigo>' . $i['codigo'] . '</Codigo>';
                    }
                    if($i['codigo']=='01' OR $i['codigo']=='07')
                    {
                        if(!isset($i['codigo_tarifa']) OR $i['codigo_tarifa']=='')
                        {
                            return response()->json(array("code" => "10", "data" => "Datos incompletos en la solicitud. El [codigo_tarifa] en la linea de detalle [" . $l . "] de la linea del impuesto [".$numImp."] es requerido, debido a que uno de los impuestos tiene codigo 01 ó 07", "body" => $payload, "fecha" => $fechaEmision), 400);
                        }
                        else{
                            $xmlString .='<CodigoTarifa>' . $i['codigo_tarifa'] . '</CodigoTarifa>';
                        }
                    }
                    if(!isset($i['tarifa']))
                    {
                        return response()->json(array("code"=>"10","data" => "Datos incompletos en la solicitud. La [tarifa] en la linea de detalle  [".$l."] de la linea del impuesto [".$numImp."] es requerida", "body" => $payload,"fecha"=>$fechaEmision), 400);
                    }
                    else{
                        $xmlString .='<Tarifa>' . $i['tarifa'] . '</Tarifa>';
                    }
                    if($i['codigo']=='08')
                    {
                        if(!isset($i['factor_iva']) OR $i['factor_iva']==''){
                            return response()->json(array("code"=>"10","data" => "Datos incompletos en la solicitud. El [factor_iva] en la linea de detalle  [".$l."] de la linea del impuesto [".$numImp."] es requerida", "body" => $payload,"fecha"=>$fechaEmision), 400);
                        }
                        else{
                            $xmlString .='<FactorIVA>' . $i['factor_iva'] . '</FactorIVA>';
                        }
                    }
                    if(!isset($i['monto']))
                    {
                        return response()->json(array("code"=>"10","data" => "Datos incompletos en la solicitud. El [monto] en la linea de detalle  [".$l."] de la linea del impuesto [".$numImp."] es requerido", "body" => $payload,"fecha"=>$fechaEmision), 400);
                    }
                    else{
                        $xmlString .='<Monto>' . $i['monto'] . '</Monto>';
                        $totalImpuesto+=floatval($i['monto']);
                    }
                    if(isset($i['monto_exportacion']) AND $i['monto_exportacion']!='')
                    {
                        $xmlString .='<MontoExportacion>' . $i['monto_exportacion'] . '</MontoExportacion>';
                    }
                    if (isset($i['exoneracion']) && $i['exoneracion'] != "")
                    {
                        $xmlString .= '
                    <Exoneracion>';
                        if (!isset($i['exoneracion']['tipodocumento']) OR $i['exoneracion']['tipodocumento'] == ""){
                            return response()->json(array("code"=>"10","data" => "Datos incompletos en la solicitud. El [tipodocumento] de exoneración en la linea de detalle  [".$l."] del nodo del impuesto [".$numImp."] es requerido", "body" =>$payload,"fecha"=>$fechaEmision), 400);
                        }
                        else{
                            $xmlString .= '<TipoDocumento>' . $i['exoneracion']['tipodocumento'] . '</TipoDocumento>';
                        }
                        if (!isset($i['exoneracion']['numerodocumento']) OR $i['exoneracion']['numerodocumento'] == ""){
                            return response()->json(array("code"=>"10","data" => "Datos incompletos en la solicitud. El [numerodocumento] de exoneración en la linea de detalle  [".$l."] del nodo del impuesto [".$numImp."] es requerido", "body" =>$payload,"fecha"=>$fechaEmision), 400);
                        }
                        else{
                            $xmlString .= '<NumeroDocumento>' . $i['exoneracion']['numerodocumento'] . '</NumeroDocumento>';
                        }
                        if (!isset($i['exoneracion']['nombreinstitucion']) OR $i['exoneracion']['nombreinstitucion'] == ""){
                            return response()->json(array("code"=>"10","data" => "Datos incompletos en la solicitud. El [nombreinstitucion] de exoneración en la linea de detalle  [".$l."] del nodo del impuesto [".$numImp."] es requerido", "body" =>$payload,"fecha"=>$fechaEmision), 400);
                        }
                        else{
                            $xmlString .= '<NombreInstitucion>' . $i['exoneracion']['nombreinstitucion'] . '</NombreInstitucion>';
                        }
                        if (!isset($i['exoneracion']['fechaemision']) OR $i['exoneracion']['fechaemision'] == ""){
                            return response()->json(array("code"=>"10","data" => "La [fechaemision] de exoneración en la linea de detalle  [".$l."] del nodo del impuesto [".$numImp."] es requerido", "body" => $payload,"fecha"=>$fechaEmision), 400);
                        }else{
                            $xmlString .= '<FechaEmision>' . $i['exoneracion']['fechaemision'] . '</FechaEmision>';
                        }
                        if (!isset($i['exoneracion']['porcentaje']) OR $i['exoneracion']['porcentaje'] == ""){
                            return response()->json(array("code"=>"10","data" => "El [porcentaje] del nodo exoneración en la linea de detalle  [".$l."] del nodo del impuesto [".$numImp."] es requerido", "body" => $payload,"fecha"=>$fechaEmision), 400);
                        }else{
                            $xmlString .= '<PorcentajeExoneracion>' . $i['exoneracion']['porcentaje'] . '</PorcentajeExoneracion>';
                        }
                        if (!isset($i['exoneracion']['monto']) OR $i['exoneracion']['monto'] == ""){
                            return response()->json(array("code"=>"10","data" => "El [monto] del nodo exoneración en la linea de detalle  [".$l."] del nodo del impuesto [".$numImp."] es requerido", "body" => $payload,"fecha"=>$fechaEmision), 400);
                        }
                        else{
                            $xmlString .= '<MontoExoneracion>' . $i['exoneracion']['monto'] . '</MontoExoneracion>';
                        }

                        /*if((string)$this->getNewTaxAmount($i['monto'],$i['exoneracion']['porcentaje'])!=$i['exoneracion']['monto'])
                        {
                            return response()->json(array("code"=>"18","data"=>"El monto de exoneración según el porcentaje indicado es incorrecto","fecha"=>$fechaEmision,"detalle"=>$payload['detalle']), 400);
                        }*/

                        $xmlString .= '</Exoneracion>';

                        $totalImpuesto-=floatval($i['monto']);

                        $totalImpuesto+=$this->getNewTaxAmount($i['monto'],$i['exoneracion']['porcentaje']);

                    }

                    $xmlString .= '</Impuesto>';
                    $numImp++;

                }
                if($d['unidad_medida']=='Sp')
                {
                    $totalServGravados+=floatval($d['monto_total']);
                }
                else{
                    $totalMercanciasGravadas+=floatval($d['monto_total']);
                }

            }
            else{
                if($d['unidad_medida']=='Sp')
                {
                    $totalServExentos+=floatval($d['monto_total']);
                }
                else{
                    $totalMercanciasExentas+=floatval($d['monto_total']);
                }
            }
            if(isset($d['impuesto_neto']))
            {
                $xmlString .= '<ImpuestoNeto>' . $d['impuesto_neto'] . '</ImpuestoNeto>';
            }
            if(!isset($d['montototallinea']) OR $d['montototallinea']=='')
            {
                return response()->json( array("code"=>"10","data" => "El [montototallinea] en la linea de detalle [".$l."] es requerido", "body" => $payload,"fecha"=>$fechaEmision), 400);
            }
            else{
                $xmlString .= '<MontoTotalLinea>' . $d['montototallinea'] . '</MontoTotalLinea>';
            }

            $xmlString .= '</LineaDetalle>';
            $l++;
            $pos++;
        }
        $xmlString .= '</DetalleServicio>';
        $totalGravado=$totalServGravados + $totalMercanciasGravadas;
        $totalExento=$totalServExentos + $totalMercanciasExentas;
        $totalVenta=$totalGravado + $totalExento;
        $totalVentaNeta=$totalVenta - $totalDescuentos;
        $totalComprobante=$totalVentaNeta + $totalImpuesto;
        if(isset($payload['otros_cargos']) AND $payload['otros_cargos']!='')
        {
            $xmlString .= '<OtrosCargos>';
            if(!isset($payload['otros_cargos']['tipo_documento']) OR $payload['otros_cargos']['tipo_documento']=='')
            {
                return response()->json( array("code"=>"10","data" => "El [tipo_documento] del nodo otros_cargos es requerido", "body" => $payload,"fecha"=>$fechaEmision), 400);
            }
            else{
                $xmlString .= '<TipoDocumento>' . $payload['otros_cargos']['tipo_documento'] . '</TipoDocumento>';
            }
            if(!isset($payload['otros_cargos']['num_identidad_tercero']) OR $payload['otros_cargos']['num_identidad_tercero']=='')
            {
                return response()->json( array("code"=>"10","data" => "El [num_identidad_tercero] del nodo otros_cargos es requerido", "body" => $payload,"fecha"=>$fechaEmision), 400);
            }
            else{
                $xmlString .= '<NumeroIdentidadTercero>' . $payload['otros_cargos']['num_identidad_tercero'] . '</NumeroIdentidadTercero>';
            }
            if(!isset($payload['otros_cargos']['nombre_tercero']) OR $payload['otros_cargos']['nombre_tercero']=='')
            {
                return response()->json( array("code"=>"10","data" => "El [nombre_tercero] del nodo otros_cargos es requerido", "body" => $payload,"fecha"=>$fechaEmision), 400);
            }
            else{
                $xmlString .= '<NombreTercero>' . $payload['otros_cargos']['nombre_tercero'] . '</NombreTercero>';
            }
            if(!isset($payload['otros_cargos']['detalle']) OR $payload['otros_cargos']['detalle']=='')
            {
                return response()->json( array("code"=>"10","data" => "El [detalle] del nodo otros_cargos es requerido", "body" => $payload,"fecha"=>$fechaEmision), 400);
            }
            else{
                $xmlString .= '<Detalle>' . $payload['otros_cargos']['detalle'] . '</Detalle>';
            }
            if(!isset($payload['otros_cargos']['porcentaje']) OR $payload['otros_cargos']['porcentaje']=='')
            {
                return response()->json( array("code"=>"10","data" => "El [porcentaje] del nodo otros_cargos es requerido", "body" => $payload,"fecha"=>$fechaEmision), 400);
            }
            else{
                $xmlString .= '<Porcentaje>' . $payload['otros_cargos']['porcentaje'] . '</Porcentaje>';
            }
            if(!isset($payload['otros_cargos']['monto_cargo']) OR $payload['otros_cargos']['monto_cargo']=='')
            {
                return response()->json( array("code"=>"10","data" => "El [monto_cargo] del nodo otros_cargos es requerido", "body" => $payload,"fecha"=>$fechaEmision), 400);
            }
            else{
                $xmlString .= '<MontoCargo>' . $payload['otros_cargos']['monto_cargo'] . '</MontoCargo>';
            }
            $xmlString .= '</OtrosCargos>';
        }

        if(!isset($payload['resumen']) OR $payload['resumen']==''){
            return response()->json(array("code"=>"10","data" => "Datos incompletos en la solicitud. El [resumen] es requerido", "body" => $payload,"fecha"=>$fechaEmision), 400);
        }

        //Validación de los totales con respectos a las totales del detalle
        /*if((string)$totalServGravados!=$payload['resumen']['totalserviciogravado']
            OR (string)$totalServExentos!=$payload['resumen']['totalservicioexento']
            OR (string)$totalMercanciasGravadas!=$payload['resumen']['totalmercaderiagravado']
            OR (string)$totalMercanciasExentas!=$payload['resumen']['totalmercaderiaexento']
            OR (string)$totalGravado!=$payload['resumen']['totalgravado']
            OR (string)$totalExento!=$payload['resumen']['totalexento']
            OR (string)$totalVenta!=$payload['resumen']['totalventa']
            OR (string)$totalDescuentos!=$payload['resumen']['totaldescuentos']
            OR (string)$totalVentaNeta!=$payload['resumen']['totalventaneta']
            OR (string)$totalImpuesto!=$payload['resumen']['totalimpuestos']
            OR (string)$totalComprobante!=$payload['resumen']['totalcomprobante'])
        {
            return response()->json(array("code"=>"18","data"=>"Alguno de los montos de las facturas no coinciden con los montos de los
detalles correspondientes.","fecha"=>$fechaEmision,"detalle"=>$payload['detalle'],"resumen"=>$payload['resumen']), 400);
        }*/
        $xmlString .= '<ResumenFactura>';
        if(isset($payload['resumen']['codigo_tipo_moneda']))
        {
            if(!isset($payload['resumen']['codigo_tipo_moneda']['moneda']) OR $payload['resumen']['codigo_tipo_moneda']['moneda']==''
                OR !isset($payload['resumen']['codigo_tipo_moneda']['tipo_cambio']) OR $payload['resumen']['codigo_tipo_moneda']['tipo_cambio']=='')
            {
                return response()->json(array("code"=>"10","data" => "Datos incompletos en la solicitud. El nodo [codigo_tipo_moneda] es requerido y debe estar completo cuando la moneda es extranjera, en caso contrario no utilice", "body" => $payload,"fecha"=>$fechaEmision), 400);
            }
            else{

                $xmlString .= '<CodigoTipoMoneda><CodigoMoneda>' . $payload['resumen']['codigo_tipo_moneda']['moneda'] . '</CodigoMoneda><TipoCambio>' . $payload['resumen']['codigo_tipo_moneda']['tipo_cambio'] . '</TipoCambio></CodigoTipoMoneda>';
            }

        }
        else {
            $xmlString .= '<CodigoTipoMoneda><CodigoMoneda>CRC</CodigoMoneda><TipoCambio>1</TipoCambio></CodigoTipoMoneda>';
        }
        if(isset($payload['resumen']['totalserviciogravado']) AND $payload['resumen']['totalserviciogravado']!=''){
            $xmlString .= '<TotalServGravados>' . $payload['resumen']['totalserviciogravado'] . '</TotalServGravados>';

        }
        if(isset($payload['resumen']['totalservicioexento']) AND $payload['resumen']['totalservicioexento']!=''){
            $xmlString .= '<TotalServExentos>' . $payload['resumen']['totalservicioexento'] . '</TotalServExentos>';
        }
        if(isset($payload['resumen']['totalservicioexonerado']) AND $payload['resumen']['totalservicioexonerado']!=''){
            $xmlString .= '<TotalServExonerado>' . $payload['resumen']['totalservicioexento'] . '</TotalServExonerado>';
        }
        if(isset($payload['resumen']['totalmercanciagravada']) AND $payload['resumen']['totalmercanciagravada']!=''){
            $xmlString .= '<TotalMercanciasGravadas>' . $payload['resumen']['totalmercanciagravada'] . '</TotalMercanciasGravadas>';
        }
        if(isset($payload['resumen']['totalmercanciaexenta']) AND $payload['resumen']['totalmercanciaexenta']!=''){
            $xmlString .= '<TotalMercanciasExentas>' . $payload['resumen']['totalmercanciaexenta'] . '</TotalMercanciasExentas>';
        }
        if(isset($payload['resumen']['totalmercanciaexonerada']) AND $payload['resumen']['totalmercanciaexonerada']!=''){
            $xmlString .= '<TotalMercExonerada>' . $payload['resumen']['totalmercanciaexonerada'] . '</TotalMercExonerada>';
        }
        if(isset($payload['resumen']['totalgravado']) AND $payload['resumen']['totalgravado']!=''){
            $xmlString .= '<TotalGravado>' . $payload['resumen']['totalgravado'] . '</TotalGravado>';
        }
        if(isset($payload['resumen']['totalexento']) AND $payload['resumen']['totalexento']!=''){
            $xmlString .= '<TotalExento>' . $payload['resumen']['totalexento'] . '</TotalExento>';
        }
        if(isset($payload['resumen']['totalexonerado']) AND $payload['resumen']['totalexonerado']!=''){
            $xmlString .= '<TotalExonerado>' . $payload['resumen']['totalexonerado'] . '</TotalExonerado>';
        }
        if(!isset($payload['resumen']['totalventa']) OR $payload['resumen']['totalventa']==''){
            return response()->json(array("code"=>"10","data" => "Datos incompletos en la solicitud. El [totalventa] es requerido", "body" => $payload,"fecha"=>$fechaEmision), 400);

        }else{
            $xmlString .= '<TotalVenta>' . $payload['resumen']['totalventa'] . '</TotalVenta>';
        }
        if(isset($payload['resumen']['totaldescuentos']) AND $payload['resumen']['totaldescuentos']!=''){
            $xmlString .= '<TotalDescuentos>' . $payload['resumen']['totaldescuentos'] . '</TotalDescuentos>';
        }
        if(!isset($payload['resumen']['totalventaneta']) OR $payload['resumen']['totalventaneta']==''){
            return response()->json(array("code"=>"10","data" => "Datos incompletos en la solicitud. El [totalventaneta] es requerido", "body" => $payload,"fecha"=>$fechaEmision), 400);

        }else{
            $xmlString .= '<TotalVentaNeta>' . $payload['resumen']['totalventaneta'] . '</TotalVentaNeta>';
        }
        if(isset($payload['resumen']['totalimpuestos']) AND $payload['resumen']['totalimpuestos']!=''){
            $xmlString .= '<TotalImpuesto>' . $payload['resumen']['totalimpuestos'] . '</TotalImpuesto>';
        }
        if(isset($payload['resumen']['totalivadevuelto']) AND $payload['resumen']['totalivadevuelto']!=''){
            $xmlString .= '<TotalIVADevuelto>' . $payload['resumen']['totalivadevuelto'] . '</TotalIVADevuelto>';
        }
        if(isset($payload['resumen']['totalotroscargos']) AND $payload['resumen']['totalotroscargos']!=''){
            $xmlString .= '<TotalOtrosCargos>' . $payload['resumen']['totalotroscargos'] . '</TotalOtrosCargos>';
        }
        if(!isset($payload['resumen']['totalcomprobante']) OR $payload['resumen']['totalcomprobante']==''){
            return response()->json(array("code"=>"10","data" => "Datos incompletos en la solicitud. El [totalcomprobante] es requerido", "body" => $payload,"fecha"=>$fechaEmision), 400);

        }else{
            $xmlString .= '<TotalComprobante>' . $payload['resumen']['totalcomprobante'] . '</TotalComprobante>';
        }
        $xmlString .= '</ResumenFactura>';
        if(!isset($payload['referencia']) OR empty($payload['referencia']))
        {
            return response()->json(array("code"=>"10","data" => "Datos incompletos en la solicitud. La [referencia] es requerida para este tipo de comprobante", "body" => $payload,"fecha"=>$fechaEmision), 400);
        }
        if(!isset($payload['referencia']['tipo_documento']) OR empty($payload['referencia']['tipo_documento']))
        {
            return response()->json(array("code"=>"10","data" => "Datos incompletos en la solicitud. El [tipo_documento] de la referencia es requerido", "body" => $payload,"fecha"=>$fechaEmision), 400);
        }

        if(!isset($payload['referencia']['numero_documento']) OR empty($payload['referencia']['numero_documento']))
        {
            return response()->json(array("code"=>"10","data" => "Datos incompletos en la solicitud. El [numero_documento] de la referencia es requerido", "body" => $payload,"fecha"=>$fechaEmision), 400);

        }

        if(!isset($payload['referencia']['fecha_emision']) OR empty($payload['referencia']['fecha_emision']))
        {
            return response()->json(array("code"=>"10","data" => "Datos incompletos en la solicitud. La [fecha_emision] de la referencia es requerida", "body" => $payload,"fecha"=>$fechaEmision), 400);
        }

        if(!isset($payload['referencia']['codigo']) OR empty($payload['referencia']['codigo']))
        {
            return response()->json(array("code"=>"10","data" => "Datos incompletos en la solicitud. El [codigo] de la referencia es requerido", "body" => $payload,"fecha"=>$fechaEmision), 400);
        }

        if(!isset($payload['referencia']['razon']) OR empty($payload['referencia']['razon']))
        {
            return response()->json(array("code"=>"10","data" => "Datos incompletos en la solicitud. La [razon] de la referencia es requerida", "body" => $payload,"fecha"=>$fechaEmision), 400);
        }
        $reglas = [
            'referencia.tipo_documento'=>['regex:/^[0-2-4-5-99]+$/u','min:2','max:2'],
            'referencia.numero_documento'=>['regex:/^[0-9]+$/u','min:50','max:50'],
            'referencia.codigo'=>['regex:/^[0-5-99]+$/u','min:2','max:2'],
            'referencia.razon'=>['max:180']
        ];
        $mensajes = [

            'referencia.tipo_documento.regex'=>'El formato del [tipo_documento] de referencia es incorrecto *41',
            'referencia.tipo_documento.min'=>'El formato del [tipo_documento] de referencia es incorrecto *41',
            'referencia.tipo_documento.max'=>'El formato del [tipo_documento] de referencia es incorrecto *41',
            'referencia.numero_documento.regex'=>'El formato del [numero_documento] de referencia es incorrecto *41',
            'referencia.numero_documento.min'=>'El formato del [numero_documento] de referencia es incorrecto *41',
            'referencia.numero_documento.max'=>'El formato del [numero_documento] de referencia es incorrecto *41',
            'referencia.codigo.regex'=>'El formato del [codigo] de referencia es incorrecto *41',
            'referencia.codigo.min'=>'El formato del [codigo] de referencia es incorrecto *41',
            'referencia.codigo.max'=>'El formato del [codigo] de referencia es incorrecto *41',
            'referencia.razon.max'=>'La [razon] de referencia debe menor o igual a 180 caracteres *41',
        ];

        $v = Validator::make($payload, $reglas,$mensajes);
        if($v->fails())
        {
            $mensaje=$v->errors()->first();
            $code=$this->getCode($mensaje);
            $data=$this->eraseCodeIntoMessage($mensaje,$code);
            return response()->json(array("code"=>$code,"data"=>$data,"fecha"=>$fechaEmision,"payload"=>$payload), 400);
        }

        $xmlString .='<InformacionReferencia>
        <TipoDoc>' . $payload['referencia']['tipo_documento'] . '</TipoDoc>
        <Numero>' . $payload['referencia']['numero_documento'] . '</Numero>
        <FechaEmision>' . $payload['referencia']['fecha_emision'] . '</FechaEmision>
        <Codigo>' . $payload['referencia']['codigo'] . '</Codigo>
        <Razon>' . $payload['referencia']['razon'] . '</Razon>
    </InformacionReferencia>';
        $ot=0;
        if (isset($payload['otros']) AND $payload['otros'] != '')
        {

            if(!isset($payload['otros']['contenido']) OR $payload['otros']['contenido']=='')
            {
                return response()->json(array("code"=>"10","data" => "Datos incompletos en la solicitud. El [contenido] del nodo otros es requerido", "body" => $payload,"fecha"=>$fechaEmision), 400);
            }
            else{
                $xmlString .= '<Otros><OtroTexto>'.$payload['otros']['contenido'].'</OtroTexto></Otros>';
            }
        }

        $xmlString .= '
    </NotaCreditoElectronica>';
        return response()->json(array("code"=>"1","data"=>base64_encode($xmlString),"fecha"=>$fechaEmision), 200);
    }
    public function makeND($payload)
    {
        $consecutivo = substr($payload['clave'], 21, 20);
        $fechaEmision=date(DATE_RFC3339);
        /*if(!isset($payload['encabezado']['fecha']) or empty($payload['encabezado']['fecha']))
        {
            return response()->json(array("response"=>"La fecha del comprobante es requerida","estado"=>"error"), 400);
        }*/
        if(!isset($payload['emisor']) or empty($payload['emisor']))
        {
            return response()->json(array("code"=>"10","data"=>"Datos incompletos en la solicitud. El [emisor] es requerido","body"=>$payload,"fecha"=>$fechaEmision), 400);
        }
        if(!isset($payload['emisor']['nombre']) or empty($payload['emisor']['nombre']))
        {
            return response()->json(array("code"=>"10","data"=>"Datos incompletos en la solicitud. El [nombre] del emisor es requerido","body"=>$payload,"fecha"=>$fechaEmision), 400);
        }
        if(!isset($payload['emisor']['identificacion']) or empty($payload['emisor']['identificacion']))
        {
            return response()->json(array("code"=>"10","data"=>"Datos incompletos en la solicitud. La [identificación] del emisor es requerido","body"=>$payload,"fecha"=>$fechaEmision), 400);
        }
        if(!isset($payload['emisor']['identificacion']['tipo']) or empty($payload['emisor']['identificacion']['tipo']))
        {
            return response()->json(array("code"=>"10","data"=>"Datos incompletos en la solicitud. El [tipo] de identificación del emisor es requerido","body"=>$payload,"fecha"=>$fechaEmision), 400);
        }
        if(!isset($payload['emisor']['identificacion']['numero']) or empty($payload['emisor']['identificacion']['numero']))
        {
            return response()->json(array("code"=>"10","data"=>"Datos incompletos en la solicitud. El [numero] de identificación del emisor es requerido","body"=>$payload,"fecha"=>$fechaEmision), 400);
        }
        if(!isset($payload['emisor']['ubicacion']) or empty($payload['emisor']['ubicacion']))
        {
            return response()->json(array("code"=>"10","data"=>"Datos incompletos en la solicitud. La [ubicacion] del emisor es requerida","body"=>$payload,"fecha"=>$fechaEmision), 400);
        }
        if(!isset($payload['emisor']['ubicacion']['provincia']) or empty($payload['emisor']['ubicacion']['provincia']))
        {
            return response()->json(array("code"=>"10","data"=>"Datos incompletos en la solicitud. La [provincia] del emisor es requerida","body"=>$payload,"fecha"=>$fechaEmision), 400);
        }
        if(!isset($payload['emisor']['ubicacion']['canton']) or empty($payload['emisor']['ubicacion']['canton']))
        {
            return response()->json(array("code"=>"10","data"=>"Datos incompletos en la solicitud. El [canton] del emisor es requerido","body"=>$payload,"fecha"=>$fechaEmision), 400);
        }
        if(!isset($payload['emisor']['ubicacion']['distrito']) or empty($payload['emisor']['ubicacion']['distrito']))
        {
            return response()->json(array("code"=>"10","data"=>"Datos incompletos en la solicitud. El [distrito] del emisor es requerido","body"=>$payload,"fecha"=>$fechaEmision), 400);
        }
        if(!isset($payload['emisor']['ubicacion']['sennas']) or empty($payload['emisor']['ubicacion']['sennas']))
        {
            return response()->json(array("code"=>"10","data"=>"Datos incompletos en la solicitud. [sennas] del emisor es requerido","body"=>$payload,"fecha"=>$fechaEmision), 400);
        }
        //SE VALIDA QUE SI EL NODO TELEFON SE INCLUYE, ENTONCES QUE LO QUE ESTÉ ADENTRO ESTÉ COMPLETO
        if(isset($payload['emisor']['telefono']) AND !empty($payload['emisor']['telefono']))
        {
            if(!isset($payload['emisor']['telefono']['cod_pais']) or empty($payload['emisor']['telefono']['cod_pais']))
            {
                return response()->json(array("code"=>"10","data"=>"Datos incompletos en la solicitud. [cod_pais] del telefono del emisor es requerido","body"=>$payload,"fecha"=>$fechaEmision), 400);
            }
            if(!isset($payload['emisor']['telefono']['numero']) or empty($payload['emisor']['telefono']['numero']))
            {
                return response()->json(array("code"=>"10","data"=>"Datos incompletos en la solicitud. El [numero] de telefono del emisor es requerido","body"=>$payload,"fecha"=>$fechaEmision), 400);
            }

        }
        if(isset($payload['emisor']['fax']) AND !empty($payload['emisor']['fax']))
        {
            if(!isset($payload['emisor']['fax']['cod_pais']) or empty($payload['emisor']['fax']['cod_pais']))
            {
                return response()->json(array("code"=>"10","data"=>"Datos incompletos en la solicitud. [cod_pais] del fax del emisor es requerido","body"=>$payload,"fecha"=>$fechaEmision), 400);
            }
            if(!isset($payload['emisor']['fax']['numero']) or empty($payload['emisor']['fax']['numero']))
            {
                return response()->json(array("code"=>"10","data"=>"Datos incompletos en la solicitud. El [numero] de fax del emisor es requerido","body"=>$payload,"fecha"=>$fechaEmision), 400);
            }

        }
        if(!isset($payload['emisor']['correo_electronico']) or empty($payload['emisor']['correo_electronico']))
        {
            return response()->json(array("code"=>"10","data"=>"Datos incompletos en la solicitud. El [correo_electronico] del emisor es requerido","body"=>$payload,"fecha"=>$fechaEmision), 400);
        }


        $xmlString = '<?xml version="1.0" encoding="utf-8"?>
    <NotaDebitoElectronica xmlns:xs="http://www.w3.org/2001/XMLSchema" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns="https://cdn.comprobanteselectronicos.go.cr/xml-schemas/v4.3/notaDebitoElectronica">
    <Clave>'.$payload['clave'] . '</Clave>
    <CodigoActividad>'.$payload['codigo_actividad'].'</CodigoActividad>
        <NumeroConsecutivo>' . $consecutivo . '</NumeroConsecutivo>
        <FechaEmision>' . /*$payload['encabezado']['fecha']*/ $fechaEmision. '</FechaEmision>
        <Emisor>
            <Nombre>' . $payload['emisor']['nombre'] . '</Nombre>
            <Identificacion>
                <Tipo>' . $payload['emisor']['identificacion']['tipo'] . '</Tipo>
                <Numero>' . $payload['emisor']['identificacion']['numero'] . '</Numero>
            </Identificacion>
            <NombreComercial>' . $payload['emisor']['nombre_comercial'] . '</NombreComercial>';
        $xmlString .= '
        <Ubicacion>
            <Provincia>' . $payload['emisor']['ubicacion']['provincia'] . '</Provincia>
            <Canton>' . $payload['emisor']['ubicacion']['canton'] . '</Canton>
            <Distrito>' . $payload['emisor']['ubicacion']['distrito'] . '</Distrito>';
        if (isset($payload['emisor']['ubicacion']['barrio']) AND $payload['emisor']['ubicacion']['barrio'] != ''){
            $xmlString .= '<Barrio>' . $payload['emisor']['ubicacion']['barrio'] . '</Barrio>';}
        else{
            $xmlString .= '<Barrio>01</Barrio>';
        }
        $xmlString .= '
                <OtrasSenas>' . $payload['emisor']['ubicacion']['sennas'] . '</OtrasSenas>
            </Ubicacion>';


        if (isset($payload['emisor']['telefono']['cod_pais']) AND $payload['emisor']['telefono']['cod_pais'] != '' AND isset($payload['emisor']['telefono']['numero']) AND $payload['emisor']['telefono']['numero'] != '') {
            $xmlString .= '
            <Telefono>
                <CodigoPais>' . $payload['emisor']['telefono']['cod_pais'] . '</CodigoPais>
                <NumTelefono>' . $payload['emisor']['telefono']['numero'] . '</NumTelefono>
            </Telefono>';
        }


        if (isset($payload['emisor']['fax']['cod_pais']) AND $payload['emisor']['fax']['cod_pais'] != '' AND isset($payload['emisor']['fax']['numero']) AND $payload['emisor']['fax']['numero'] != '') {
            $xmlString .= '
            <Fax>
                <CodigoPais>' . $payload['emisor']['fax']['cod_pais'] . '</CodigoPais>
                <NumTelefono>' . $payload['emisor']['fax']['numero'] . '</NumTelefono>
            </Fax>';
        }

        $xmlString .= '<CorreoElectronico>' . $payload['emisor']['correo_electronico'] . '</CorreoElectronico>
        </Emisor>';


        if (isset($payload['receptor']['nombre']) and $payload['receptor']['nombre'] != '')
        {
            $xmlString .= '<Receptor>
            <Nombre>' . $payload['receptor']['nombre'] . '</Nombre>';


            if (isset($payload['receptor']['IdentificacionExtranjero']) AND $payload['receptor']['IdentificacionExtranjero'] != '')
            {
                $xmlString .= '<IdentificacionExtranjero>'
                    . $payload['receptor']['IdentificacionExtranjero']
                    . ' </IdentificacionExtranjero>';
            }


            if (isset($payload['receptor']['identificacion']['tipo']) AND $payload['receptor']['identificacion']['tipo'] != '') {
                $xmlString .= '<Identificacion>
                    <Tipo>' . $payload['receptor']['identificacion']['tipo'] . '</Tipo>';
            } else {
                return response()->json(array("code"=>"10","data" => "Datos incompletos en la solicitud. El [tipo] de identificación del receptor es requerido", "body" => $payload,"fecha"=>$fechaEmision), 400);
            }
            if (isset($payload['receptor']['identificacion']['numero']) AND $payload['receptor']['identificacion']['numero'] != '') {
                $xmlString .='<Numero>' . $payload['receptor']['identificacion']['numero'] . '</Numero></Identificacion>';

            }
            else{
                return response()->json(array("code"=>"10","data" => "Datos incompletos en la solicitud. El [numero] de identificación del receptor es requerido", "body" => $payload,"fecha"=>$fechaEmision), 400);
            }
            if (isset($payload['receptor']['ubicacion']) AND $payload['receptor']['ubicacion'] != '' ) {
                if (!isset($payload['receptor']['ubicacion']['provincia']) OR $payload['receptor']['ubicacion']['provincia'] == '' ) {
                    return response()->json(array("code"=>"10","data" => "Datos incompletos en la solicitud. La [provincia] del receptor es requerida", "body" => $payload,"fecha"=>$fechaEmision), 400);
                }
                if (!isset($payload['receptor']['ubicacion']['canton']) OR $payload['receptor']['ubicacion']['canton'] == '' ) {
                    return response()->json(array("code"=>"10","data" => "Datos incompletos en la solicitud. El [canton] del receptor es requerido", "body" => $payload,"fecha"=>$fechaEmision), 400);
                }
                if (!isset($payload['receptor']['ubicacion']['distrito']) OR $payload['receptor']['ubicacion']['distrito'] == '' ) {
                    return response()->json(array("code"=>"10","data" => "Datos incompletos en la solicitud. El [distrito] del receptor es requerido", "body" => $payload,"fecha"=>$fechaEmision), 400);
                }
                if (!isset($payload['receptor']['ubicacion']['sennas']) OR $payload['receptor']['ubicacion']['sennas'] == '' ) {
                    return response()->json(array("code"=>"10","data" => "Datos incompletos en la solicitud. [sennas] del receptor es requerido", "body" => $payload,"fecha"=>$fechaEmision), 400);
                }
                $xmlString .= '
                    <Ubicacion>
                        <Provincia>' . $payload['receptor']['ubicacion']['provincia'] . '</Provincia>
                        <Canton>' . $payload['receptor']['ubicacion']['canton'] . '</Canton>
                        <Distrito>' . $payload['receptor']['ubicacion']['distrito'] . '</Distrito>';
                if (isset($payload['receptor']['ubicacion']['barrio']) AND $payload['receptor']['ubicacion']['barrio'] != ''){
                    $xmlString .= '
                            <Barrio>' . $payload['receptor']['ubicacion']['barrio'] . '</Barrio>';}
                $xmlString .= ' <OtrasSenas>' . $payload['receptor']['ubicacion']['sennas'] . '</OtrasSenas>
                    </Ubicacion>';

            }
            if (!empty($payload['receptor']['IdentificacionExtranjero']) AND empty($payload['receptor']['sennas_ext']))
            {
                return response()->json(array("code"=>"10","data" => "Datos incompletos en la solicitud. El [sennas_ext] del receptor es requerido", "body"=>$payload,"fecha"=>$fechaEmision), 400);
            }

            if (empty($payload['receptor']['IdentificacionExtranjero']) AND !empty($payload['receptor']['sennas_ext']))
            {
                return response()->json(array("code"=>"10","data" => "Datos incompletos en la solicitud. La [IdentificacionExtranjero] del receptor es requerido", "body"=>$payload,"fecha"=>$fechaEmision), 400);
            }
            if(!empty($payload['receptor']['IdentificacionExtranjero']) AND !empty($payload['receptor']['sennas_ext'])){
                $xmlString .= '<OtrasSenasExtranjero>'
                    . $payload['receptor']['sennas_ext']
                    . ' </OtrasSenasExtranjero>';
            }
            if (isset($payload['receptor']['telefono']) AND $payload['receptor']['telefono'] !='')
            {
                if (!isset($payload['receptor']['telefono']['cod_pais']) OR $payload['receptor']['telefono']['cod_pais'] =='')
                {
                    return response()->json(array("code"=>"10","data" => "Datos incompletos en la solicitud. El [cod_pais] del teléfono del receptor es requerido", "body" => $payload,"fecha"=>$fechaEmision), 400);
                }
                if (!isset($payload['receptor']['telefono']['numero']) OR $payload['receptor']['telefono']['numero'] =='')
                {
                    return response()->json(array("code"=>"10","data" => "Datos incompletos en la solicitud. El [numero] de teléfono del receptor es requerido", "body" => $payload,"fecha"=>$fechaEmision), 400);
                }
                $xmlString .= '<Telefono>
                                   <CodigoPais>' . $payload['receptor']['telefono']['cod_pais'] . '</CodigoPais>';
                $xmlString .= '<NumTelefono>' . $payload['receptor']['telefono']['numero'] . '</NumTelefono>
                    </Telefono>';

            }
            if (isset($payload['receptor']['fax']) AND $payload['receptor']['fax'] !='')
            {
                if (!isset($payload['receptor']['fax']['cod_pais']) OR $payload['receptor']['fax']['cod_pais'] =='')
                {
                    return response()->json(array("code"=>"10","data" => "Datos incompletos en la solicitud. El [cod_pais] del fax del receptor es requerido", "body"=>$payload,"fecha"=>$fechaEmision), 400);
                }
                if (!isset($payload['receptor']['fax']['numero']) OR $payload['receptor']['fax']['numero'] =='')
                {
                    return response()->json(array("code"=>"10","data" => "Datos incompletos en la solicitud. El [numero] de fax del receptor es requerido", "body"=>$payload,"fecha"=>$fechaEmision), 400);
                }
                $xmlString .= '<Fax>
                                   <CodigoPais>' . $payload['receptor']['telefono']['cod_pais'] . '</CodigoPais>';
                $xmlString .= '<NumTelefono>' . $payload['receptor']['telefono']['numero'] . '</NumTelefono>
                    </Fax>';

            }


            if (!isset($payload['receptor']['correo_electronico']) OR $payload['receptor']['correo_electronico'] == '')
            {
                return response()->json(array("code"=>"10","data" => "Datos incompletos en la solicitud. El [correo_electronico] del receptor es requerido", "body"=>$payload,"fecha"=>$fechaEmision), 400);
            }
            $xmlString .= '<CorreoElectronico>' . $payload['receptor']['correo_electronico'] . '</CorreoElectronico>';
            $xmlString .= '</Receptor>';

        }
        if(!isset($payload['encabezado']['condicion_venta']) OR $payload['encabezado']['condicion_venta']=='')
        {
            return response()->json(array("code"=>"10","data" => "Datos incompletos en la solicitud. La [condicion_venta] es requerida", "body"=>$payload,"fecha"=>$fechaEmision), 400);
        }
        $xmlString .= '
        <CondicionVenta>' . $payload['encabezado']['condicion_venta'] . '</CondicionVenta>';
        if (isset($payload['encabezado']['plazo_credito']) AND $payload['encabezado']['plazo_credito']!='')
        {
            $xmlString .= '<PlazoCredito>' . $payload['encabezado']['plazo_credito'] . '</PlazoCredito>';

        }else
        {
            $xmlString .= '<PlazoCredito>0</PlazoCredito>';
        }
        if(!isset($payload['encabezado']['mediopago']) OR $payload['encabezado']['mediopago']=='')
        {
            return response()->json(array("code"=>"10","data" => "Datos incompletos en la solicitud. El [mediopago] es requerido", "body"=>$payload,"fecha"=>$fechaEmision), 400);
        }
        $xmlString .='<MedioPago>' . $payload['encabezado']['mediopago'] . '</MedioPago>';
        $xmlString .='<DetalleServicio>';

        if(!isset($payload['detalle']) OR $payload['detalle']=='')
        {
            return response()->json(array("code"=>"10","data" => "Datos incompletos en la solicitud. El [detalle] es requerido", "body"=>$payload,"fecha"=>$fechaEmision), 400);
        }
        $l = 1;
        $pos=0;
        $totalServGravados=0.00000;
        $totalServExentos=0.00000;
        $totalMercanciasGravadas=0.00000;
        $totalMercanciasExentas=0.00000;
        $totalGravado=0.00000;
        $totalExento=0.00000;
        $totalVenta=0.00000;
        $totalDescuentos=0.00000;
        $totalVentaNeta=0.00000;
        $totalImpuesto=0.00000;
        $totalComprobante=0.00000;


        foreach ($payload['detalle'] as $d)
        {

            if(!isset($d['numero']) OR $d['numero']=='')
            {
                return response()->json(array("code"=>"10","data" => "Datos incompletos en la solicitud. El [numero] de linea en la linea de detalle [".$l."] es requerido", "body" => $payload,"fecha"=>$fechaEmision), 400);
            }
            else{
                $xmlString .= '<LineaDetalle>
                  <NumeroLinea>' . $d['numero'] . '</NumeroLinea>';
            }

            if(!isset($payload['referencia']) OR empty($payload['referencia']))
            {
                return response()->json(array("code"=>"10","data" => "Datos incompletos en la solicitud. La [referencia] es requerida para este tipo de comprobante", "body" => $payload,"fecha"=>$fechaEmision), 400);
            }
            else{

                if(substr($payload['referencia']['numero_documento'], 29, 2)=='09')
                {
                    if($d['unidad_medida']!='Al' AND $d['unidad_medida']!='Alc' AND $d['unidad_medida']!='Cm' AND $d['unidad_medida']!='I'
                        AND $d['unidad_medida']!='Os' AND $d['unidad_medida']!='Sp' AND $d['unidad_medida']!='Spe' AND $d['unidad_medida']!='St')
                    {
                        if(!isset($d['partida_arancelaria']) OR $d['partida_arancelaria']=='')
                        {
                            return response()->json(array("code"=>"10","data" => "Datos incompletos en la solicitud. La [partida_arancelaria] en la linea de detalle [".$l."] es requerida", "body" => $payload,"fecha"=>$fechaEmision), 400);
                        }
                        else{
                            $xmlString .= '<PartidaArancelaria>' . $d['partida_arancelaria'] . '</PartidaArancelaria>';
                        }
                    }
                }
            }
            if(!isset($d['codigo']) OR $d['codigo']=='')
            {
                return response()->json(array("code"=>"10","data" => "Datos incompletos en la solicitud. El [codigo] en la linea de detalle [".$l."] es requerida", "body" => $payload,"fecha"=>$fechaEmision), 400);
            }
            else{
                $xmlString .='<Codigo>' . $d['codigo'] . '</Codigo>';
            }
            if(isset($d['codigoComercial']) AND $d['codigoComercial']!='')
            {

                $xmlString.= '<CodigoComercial>';
                if(!isset($d['codigoComercial']['tipo']) OR $d['codigoComercial']['tipo']==''){
                    return response()->json(array("code"=>"10","data" => "Datos incompletos en la solicitud. El [tipo] de codigoComercial en la linea de detalle [".$l."] es requerido ", "body"=>$payload,"fecha"=>$fechaEmision), 400);
                }
                else{
                    $xmlString.='<Tipo>'.$d['codigoComercial']['tipo'].'</Tipo>';
                }
                if(!isset($d['codigoComercial']['codigo']) OR $d['codigoComercial']['codigo']==''){
                    return response()->json(array("code"=>"10","data" => "Datos incompletos en la solicitud. El [codigo] del nodo codigoComercial en la linea de detalle [".$l."] es requerido", "body"=>$payload,"fecha"=>$fechaEmision), 400);
                }
                else{
                    $xmlString.='<Codigo>'.$d['codigoComercial']['codigo']   .'</Codigo>';
                }
                $xmlString.= '</CodigoComercial>';
            }

            if(!isset($d['cantidad']) OR $d['cantidad']=='')
            {
                return response()->json(array("code"=>"10","data" => "Datos incompletos en la solicitud. La [cantidad] en la linea de detalle [".$l."] es requerida", "body" => $payload,"fecha"=>$fechaEmision), 400);
            }
            else{
                $xmlString .='<Cantidad>' . $d['cantidad'] . '</Cantidad>';
            }
            if(!isset($d['unidad_medida']) OR $d['unidad_medida']=='')
            {
                return response()->json(array("code"=>"10","data" => "Datos incompletos en la solicitud. La [unidad_medida] en la linea de detalle [".$l."] es requerida", "body" => $payload,"fecha"=>$fechaEmision), 400);
            }
            else
            {
                $xmlString.='<UnidadMedida>' . $d['unidad_medida'] . '</UnidadMedida>';
            }
            if(isset($d['unidad_medida_comercial'])){
                $xmlString.='<UnidadMedidaComercial>'.$d['unidad_medida_comercial'].'</UnidadMedidaComercial>';
            }
            if(!isset($d['detalle']) OR $d['detalle']=='')
            {
                return response()->json(array("code"=>"10","data" => "Datos incompletos en la solicitud. El [detalle] en la linea de detalle [".$l."] es requerido", "body" => $payload,"fecha"=>$fechaEmision), 400);
            }else{
                $xmlString.='<Detalle>' . $d['detalle'] . '</Detalle>';
            }
            if(!isset($d['precio_unitario']) OR $d['precio_unitario']=='')
            {
                return response()->json(array("code"=>"10","data" => "Datos incompletos en la solicitud. El [precio_unitario] en la linea de detalle [".$l."] es requerido", "body" => $payload,"fecha"=>$fechaEmision), 400);
            }
            else{
                $xmlString.='<PrecioUnitario>' . $d['precio_unitario'] . '</PrecioUnitario>';
            }
            if(!isset($d['monto_total']) OR $d['monto_total']=='')
            {
                return response()->json(array("code"=>"10","data" => "Datos incompletos en la solicitud. El [monto_total] en la linea de detalle [".$l."] es requerido", "body" => $payload,"fecha"=>$fechaEmision), 400);
            }
            else
            {
                $xmlString.='<MontoTotal>' . $d['monto_total'] . '</MontoTotal>';
            }

            if (isset($d['descuento']) && $d['descuento'] != "")
            {
                if (!isset($d['descuento']['monto']) OR $d['descuento']['monto'] == "")
                {
                    return response()->json(array("code"=>"10","data" => "Datos incompletos en la solicitud. El [monto] del nodo descuento en la linea de detalle [".$l."] es requerido", "body" => $payload,"fecha"=>$fechaEmision), 400);
                }
                else{
                    $xmlString .= '<Descuento><MontoDescuento>' . $d['descuento']['monto'] . '</MontoDescuento>';
                }
                if (!isset($d['descuento']['naturaleza_descuento']) OR $d['descuento']['naturaleza_descuento'] == "")
                {
                    return response()->json(array("code"=>"10","data" => "Datos incompletos en la solicitud. La [naturaleza_descuento] del nodo descuento en la linea de detalle [".$l."] es requerido", "body" => $payload,"fecha"=>$fechaEmision), 400);
                }
                else{
                    $xmlString .= '<NaturalezaDescuento>' . $d['descuento']['naturaleza_descuento'] . '</NaturalezaDescuento></Descuento>';
                }

            }
            if(!isset($d['subtotal']) OR $d['subtotal']==''){
                return response()->json(array("code"=>"10","data" => "Datos incompletos en la solicitud. El [subtotal] en la linea de detalle [".$l."] es requerido", "body" => $payload,"fecha"=>$fechaEmision), 400);
            }
            else{
                $xmlString .= '<SubTotal>' . $d['subtotal'] . '</SubTotal>';
            }
            if (isset($d['impuestos']) && $d['impuestos'] != "")
            {
                foreach ($d['impuestos'] as $y)
                {
                    if($y['codigo']=='07')
                    {
                        if(!isset($d['base_imponible']) OR $d['base_imponible']=='') {
                            return response()->json(array("code" => "10", "data" => "Datos incompletos en la solicitud. La [base_imponible] en la linea de detalle [" . $l . "] es requerido, debido a que uno de los impuestos tiene codigo 07", "body" => $payload, "fecha" => $fechaEmision), 400);
                        }
                        else{
                            $xmlString .= '<BaseImponible>' . $d['base_imponible'] . '</BaseImponible>';
                            break;
                        }
                    }
                }
                $numImp=1;
                foreach ($d['impuestos'] as $i)
                {
                    $xmlString .= '<Impuesto>';
                    if(!isset($i['codigo']) OR $i['codigo']=='')
                    {
                        return response()->json(array("code"=>"10","data" => "Datos incompletos en la solicitud. El [codigo] en la linea de detalle  [".$l."] de la linea del impuesto [".$numImp."] es requerido", "body" => $payload,"fecha"=>$fechaEmision), 400);
                    }else{
                        $xmlString .='<Codigo>' . $i['codigo'] . '</Codigo>';
                    }
                    if($i['codigo']=='01' OR $i['codigo']=='07')
                    {
                        if(!isset($i['codigo_tarifa']) OR $i['codigo_tarifa']=='')
                        {
                            return response()->json(array("code" => "10", "data" => "Datos incompletos en la solicitud. El [codigo_tarifa] en la linea de detalle [" . $l . "] de la linea del impuesto [".$numImp."] es requerido, debido a que uno de los impuestos tiene codigo 01 ó 07", "body" => $payload, "fecha" => $fechaEmision), 400);
                        }
                        else{
                            $xmlString .='<CodigoTarifa>' . $i['codigo_tarifa'] . '</CodigoTarifa>';
                        }
                    }
                    if(!isset($i['tarifa']))
                    {
                        return response()->json(array("code"=>"10","data" => "Datos incompletos en la solicitud. La [tarifa] en la linea de detalle  [".$l."] de la linea del impuesto [".$numImp."] es requerida", "body" => $payload,"fecha"=>$fechaEmision), 400);
                    }
                    else{
                        $xmlString .='<Tarifa>' . $i['tarifa'] . '</Tarifa>';
                    }
                    if($i['codigo']=='08')
                    {
                        if(!isset($i['factor_iva']) OR $i['factor_iva']==''){
                            return response()->json(array("code"=>"10","data" => "Datos incompletos en la solicitud. El [factor_iva] en la linea de detalle  [".$l."] de la linea del impuesto [".$numImp."] es requerida", "body" => $payload,"fecha"=>$fechaEmision), 400);
                        }
                        else{
                            $xmlString .='<FactorIVA>' . $i['factor_iva'] . '</FactorIVA>';
                        }
                    }
                    if(!isset($i['monto']))
                    {
                        return response()->json(array("code"=>"10","data" => "Datos incompletos en la solicitud. El [monto] en la linea de detalle  [".$l."] de la linea del impuesto [".$numImp."] es requerido", "body" => $payload,"fecha"=>$fechaEmision), 400);
                    }
                    else{
                        $xmlString .='<Monto>' . $i['monto'] . '</Monto>';
                        $totalImpuesto+=floatval($i['monto']);
                    }
                    if(isset($i['monto_exportacion']) AND $i['monto_exportacion']!='')
                    {
                        $xmlString .='<MontoExportacion>' . $i['monto_exportacion'] . '</MontoExportacion>';
                    }
                    if (isset($i['exoneracion']) && $i['exoneracion'] != "")
                    {
                        $xmlString .= '
                    <Exoneracion>';
                        if (!isset($i['exoneracion']['tipodocumento']) OR $i['exoneracion']['tipodocumento'] == ""){
                            return response()->json(array("code"=>"10","data" => "Datos incompletos en la solicitud. El [tipodocumento] de exoneración en la linea de detalle  [".$l."] del nodo del impuesto [".$numImp."] es requerido", "body" =>$payload,"fecha"=>$fechaEmision), 400);
                        }
                        else{
                            $xmlString .= '<TipoDocumento>' . $i['exoneracion']['tipodocumento'] . '</TipoDocumento>';
                        }
                        if (!isset($i['exoneracion']['numerodocumento']) OR $i['exoneracion']['numerodocumento'] == ""){
                            return response()->json(array("code"=>"10","data" => "Datos incompletos en la solicitud. El [numerodocumento] de exoneración en la linea de detalle  [".$l."] del nodo del impuesto [".$numImp."] es requerido", "body" =>$payload,"fecha"=>$fechaEmision), 400);
                        }
                        else{
                            $xmlString .= '<NumeroDocumento>' . $i['exoneracion']['numerodocumento'] . '</NumeroDocumento>';
                        }
                        if (!isset($i['exoneracion']['nombreinstitucion']) OR $i['exoneracion']['nombreinstitucion'] == ""){
                            return response()->json(array("code"=>"10","data" => "Datos incompletos en la solicitud. El [nombreinstitucion] de exoneración en la linea de detalle  [".$l."] del nodo del impuesto [".$numImp."] es requerido", "body" =>$payload,"fecha"=>$fechaEmision), 400);
                        }
                        else{
                            $xmlString .= '<NombreInstitucion>' . $i['exoneracion']['nombreinstitucion'] . '</NombreInstitucion>';
                        }
                        if (!isset($i['exoneracion']['fechaemision']) OR $i['exoneracion']['fechaemision'] == ""){
                            return response()->json(array("code"=>"10","data" => "La [fechaemision] de exoneración en la linea de detalle  [".$l."] del nodo del impuesto [".$numImp."] es requerido", "body" => $payload,"fecha"=>$fechaEmision), 400);
                        }else{
                            $xmlString .= '<FechaEmision>' . $i['exoneracion']['fechaemision'] . '</FechaEmision>';
                        }
                        if (!isset($i['exoneracion']['porcentaje']) OR $i['exoneracion']['porcentaje'] == ""){
                            return response()->json(array("code"=>"10","data" => "El [porcentaje] del nodo exoneración en la linea de detalle  [".$l."] del nodo del impuesto [".$numImp."] es requerido", "body" => $payload,"fecha"=>$fechaEmision), 400);
                        }else{
                            $xmlString .= '<PorcentajeExoneracion>' . $i['exoneracion']['porcentaje'] . '</PorcentajeExoneracion>';
                        }
                        if (!isset($i['exoneracion']['monto']) OR $i['exoneracion']['monto'] == ""){
                            return response()->json(array("code"=>"10","data" => "El [monto] del nodo exoneración en la linea de detalle  [".$l."] del nodo del impuesto [".$numImp."] es requerido", "body" => $payload,"fecha"=>$fechaEmision), 400);
                        }
                        else{
                            $xmlString .= '<MontoExoneracion>' . $i['exoneracion']['monto'] . '</MontoExoneracion>';
                        }

                        /*if((string)$this->getNewTaxAmount($i['monto'],$i['exoneracion']['porcentaje'])!=$i['exoneracion']['monto'])
                        {
                            return response()->json(array("code"=>"18","data"=>"El monto de exoneración según el porcentaje indicado es incorrecto","fecha"=>$fechaEmision,"detalle"=>$payload['detalle']), 400);
                        }*/

                        $xmlString .= '</Exoneracion>';

                        $totalImpuesto-=floatval($i['monto']);

                        $totalImpuesto+=$this->getNewTaxAmount($i['monto'],$i['exoneracion']['porcentaje']);

                    }

                    $xmlString .= '</Impuesto>';
                    $numImp++;

                }
                if($d['unidad_medida']=='Sp')
                {
                    $totalServGravados+=floatval($d['monto_total']);
                }
                else{
                    $totalMercanciasGravadas+=floatval($d['monto_total']);
                }

            }
            else{
                if($d['unidad_medida']=='Sp')
                {
                    $totalServExentos+=floatval($d['monto_total']);
                }
                else{
                    $totalMercanciasExentas+=floatval($d['monto_total']);
                }
            }
            if(isset($d['impuesto_neto']))
            {
                $xmlString .= '<ImpuestoNeto>' . $d['impuesto_neto'] . '</ImpuestoNeto>';
            }
            if(!isset($d['montototallinea']) OR $d['montototallinea']=='')
            {
                return response()->json( array("code"=>"10","data" => "El [montototallinea] en la linea de detalle [".$l."] es requerido", "body" => $payload,"fecha"=>$fechaEmision), 400);
            }
            else{
                $xmlString .= '<MontoTotalLinea>' . $d['montototallinea'] . '</MontoTotalLinea>';
            }

            $xmlString .= '</LineaDetalle>';
            $l++;
            $pos++;
        }
        $xmlString .= '</DetalleServicio>';
        $totalGravado=$totalServGravados + $totalMercanciasGravadas;
        $totalExento=$totalServExentos + $totalMercanciasExentas;
        $totalVenta=$totalGravado + $totalExento;
        $totalVentaNeta=$totalVenta - $totalDescuentos;
        $totalComprobante=$totalVentaNeta + $totalImpuesto;
        if(isset($payload['otros_cargos']) AND $payload['otros_cargos']!='')
        {
            $xmlString .= '<OtrosCargos>';
            if(!isset($payload['otros_cargos']['tipo_documento']) OR $payload['otros_cargos']['tipo_documento']=='')
            {
                return response()->json( array("code"=>"10","data" => "El [tipo_documento] del nodo otros_cargos es requerido", "body" => $payload,"fecha"=>$fechaEmision), 400);
            }
            else{
                $xmlString .= '<TipoDocumento>' . $payload['otros_cargos']['tipo_documento'] . '</TipoDocumento>';
            }
            if(!isset($payload['otros_cargos']['num_identidad_tercero']) OR $payload['otros_cargos']['num_identidad_tercero']=='')
            {
                return response()->json( array("code"=>"10","data" => "El [num_identidad_tercero] del nodo otros_cargos es requerido", "body" => $payload,"fecha"=>$fechaEmision), 400);
            }
            else{
                $xmlString .= '<NumeroIdentidadTercero>' . $payload['otros_cargos']['num_identidad_tercero'] . '</NumeroIdentidadTercero>';
            }
            if(!isset($payload['otros_cargos']['nombre_tercero']) OR $payload['otros_cargos']['nombre_tercero']=='')
            {
                return response()->json( array("code"=>"10","data" => "El [nombre_tercero] del nodo otros_cargos es requerido", "body" => $payload,"fecha"=>$fechaEmision), 400);
            }
            else{
                $xmlString .= '<NombreTercero>' . $payload['otros_cargos']['nombre_tercero'] . '</NombreTercero>';
            }
            if(!isset($payload['otros_cargos']['detalle']) OR $payload['otros_cargos']['detalle']=='')
            {
                return response()->json( array("code"=>"10","data" => "El [detalle] del nodo otros_cargos es requerido", "body" => $payload,"fecha"=>$fechaEmision), 400);
            }
            else{
                $xmlString .= '<Detalle>' . $payload['otros_cargos']['detalle'] . '</Detalle>';
            }
            if(!isset($payload['otros_cargos']['porcentaje']) OR $payload['otros_cargos']['porcentaje']=='')
            {
                return response()->json( array("code"=>"10","data" => "El [porcentaje] del nodo otros_cargos es requerido", "body" => $payload,"fecha"=>$fechaEmision), 400);
            }
            else{
                $xmlString .= '<Porcentaje>' . $payload['otros_cargos']['porcentaje'] . '</Porcentaje>';
            }
            if(!isset($payload['otros_cargos']['monto_cargo']) OR $payload['otros_cargos']['monto_cargo']=='')
            {
                return response()->json( array("code"=>"10","data" => "El [monto_cargo] del nodo otros_cargos es requerido", "body" => $payload,"fecha"=>$fechaEmision), 400);
            }
            else{
                $xmlString .= '<MontoCargo>' . $payload['otros_cargos']['monto_cargo'] . '</MontoCargo>';
            }
            $xmlString .= '</OtrosCargos>';
        }

        if(!isset($payload['resumen']) OR $payload['resumen']==''){
            return response()->json(array("code"=>"10","data" => "Datos incompletos en la solicitud. El [resumen] es requerido", "body" => $payload,"fecha"=>$fechaEmision), 400);
        }

        //Validación de los totales con respectos a las totales del detalle
        /*if((string)$totalServGravados!=$payload['resumen']['totalserviciogravado']
            OR (string)$totalServExentos!=$payload['resumen']['totalservicioexento']
            OR (string)$totalMercanciasGravadas!=$payload['resumen']['totalmercaderiagravado']
            OR (string)$totalMercanciasExentas!=$payload['resumen']['totalmercaderiaexento']
            OR (string)$totalGravado!=$payload['resumen']['totalgravado']
            OR (string)$totalExento!=$payload['resumen']['totalexento']
            OR (string)$totalVenta!=$payload['resumen']['totalventa']
            OR (string)$totalDescuentos!=$payload['resumen']['totaldescuentos']
            OR (string)$totalVentaNeta!=$payload['resumen']['totalventaneta']
            OR (string)$totalImpuesto!=$payload['resumen']['totalimpuestos']
            OR (string)$totalComprobante!=$payload['resumen']['totalcomprobante'])
        {
            return response()->json(array("code"=>"18","data"=>"Alguno de los montos de las facturas no coinciden con los montos de los
detalles correspondientes.","fecha"=>$fechaEmision,"detalle"=>$payload['detalle'],"resumen"=>$payload['resumen']), 400);
        }*/
        $xmlString .= '<ResumenFactura>';
        if(isset($payload['resumen']['codigo_tipo_moneda']))
        {
            if(!isset($payload['resumen']['codigo_tipo_moneda']['moneda']) OR $payload['resumen']['codigo_tipo_moneda']['moneda']==''
                OR !isset($payload['resumen']['codigo_tipo_moneda']['tipo_cambio']) OR $payload['resumen']['codigo_tipo_moneda']['tipo_cambio']=='')
            {
                return response()->json(array("code"=>"10","data" => "Datos incompletos en la solicitud. El nodo [codigo_tipo_moneda] es requerido y debe estar completo cuando la moneda es extranjera, en caso contrario no utilice", "body" => $payload,"fecha"=>$fechaEmision), 400);
            }
            else{

                $xmlString .= '<CodigoTipoMoneda><CodigoMoneda>' . $payload['resumen']['codigo_tipo_moneda']['moneda'] . '</CodigoMoneda><TipoCambio>' . $payload['resumen']['codigo_tipo_moneda']['tipo_cambio'] . '</TipoCambio></CodigoTipoMoneda>';
            }

        }
        else {
            $xmlString .= '<CodigoTipoMoneda><CodigoMoneda>CRC</CodigoMoneda><TipoCambio>1</TipoCambio></CodigoTipoMoneda>';
        }
        if(isset($payload['resumen']['totalserviciogravado']) AND $payload['resumen']['totalserviciogravado']!=''){
            $xmlString .= '<TotalServGravados>' . $payload['resumen']['totalserviciogravado'] . '</TotalServGravados>';

        }
        if(isset($payload['resumen']['totalservicioexento']) AND $payload['resumen']['totalservicioexento']!=''){
            $xmlString .= '<TotalServExentos>' . $payload['resumen']['totalservicioexento'] . '</TotalServExentos>';
        }
        if(isset($payload['resumen']['totalservicioexonerado']) AND $payload['resumen']['totalservicioexonerado']!=''){
            $xmlString .= '<TotalServExonerado>' . $payload['resumen']['totalservicioexento'] . '</TotalServExonerado>';
        }
        if(isset($payload['resumen']['totalmercanciagravada']) AND $payload['resumen']['totalmercanciagravada']!=''){
            $xmlString .= '<TotalMercanciasGravadas>' . $payload['resumen']['totalmercanciagravada'] . '</TotalMercanciasGravadas>';
        }
        if(isset($payload['resumen']['totalmercanciaexenta']) AND $payload['resumen']['totalmercanciaexenta']!=''){
            $xmlString .= '<TotalMercanciasExentas>' . $payload['resumen']['totalmercanciaexenta'] . '</TotalMercanciasExentas>';
        }
        if(isset($payload['resumen']['totalmercanciaexonerada']) AND $payload['resumen']['totalmercanciaexonerada']!=''){
            $xmlString .= '<TotalMercExonerada>' . $payload['resumen']['totalmercanciaexonerada'] . '</TotalMercExonerada>';
        }
        if(isset($payload['resumen']['totalgravado']) AND $payload['resumen']['totalgravado']!=''){
            $xmlString .= '<TotalGravado>' . $payload['resumen']['totalgravado'] . '</TotalGravado>';
        }
        if(isset($payload['resumen']['totalexento']) AND $payload['resumen']['totalexento']!=''){
            $xmlString .= '<TotalExento>' . $payload['resumen']['totalexento'] . '</TotalExento>';
        }
        if(isset($payload['resumen']['totalexonerado']) AND $payload['resumen']['totalexonerado']!=''){
            $xmlString .= '<TotalExonerado>' . $payload['resumen']['totalexonerado'] . '</TotalExonerado>';
        }
        if(!isset($payload['resumen']['totalventa']) OR $payload['resumen']['totalventa']==''){
            return response()->json(array("code"=>"10","data" => "Datos incompletos en la solicitud. El [totalventa] es requerido", "body" => $payload,"fecha"=>$fechaEmision), 400);

        }else{
            $xmlString .= '<TotalVenta>' . $payload['resumen']['totalventa'] . '</TotalVenta>';
        }
        if(isset($payload['resumen']['totaldescuentos']) AND $payload['resumen']['totaldescuentos']!=''){
            $xmlString .= '<TotalDescuentos>' . $payload['resumen']['totaldescuentos'] . '</TotalDescuentos>';
        }
        if(!isset($payload['resumen']['totalventaneta']) OR $payload['resumen']['totalventaneta']==''){
            return response()->json(array("code"=>"10","data" => "Datos incompletos en la solicitud. El [totalventaneta] es requerido", "body" => $payload,"fecha"=>$fechaEmision), 400);

        }else{
            $xmlString .= '<TotalVentaNeta>' . $payload['resumen']['totalventaneta'] . '</TotalVentaNeta>';
        }
        if(isset($payload['resumen']['totalimpuestos']) AND $payload['resumen']['totalimpuestos']!=''){
            $xmlString .= '<TotalImpuesto>' . $payload['resumen']['totalimpuestos'] . '</TotalImpuesto>';
        }
        if(isset($payload['resumen']['totalivadevuelto']) AND $payload['resumen']['totalivadevuelto']!=''){
            $xmlString .= '<TotalIVADevuelto>' . $payload['resumen']['totalivadevuelto'] . '</TotalIVADevuelto>';
        }
        if(isset($payload['resumen']['totalotroscargos']) AND $payload['resumen']['totalotroscargos']!=''){
            $xmlString .= '<TotalOtrosCargos>' . $payload['resumen']['totalotroscargos'] . '</TotalOtrosCargos>';
        }
        if(!isset($payload['resumen']['totalcomprobante']) OR $payload['resumen']['totalcomprobante']==''){
            return response()->json(array("code"=>"10","data" => "Datos incompletos en la solicitud. El [totalcomprobante] es requerido", "body" => $payload,"fecha"=>$fechaEmision), 400);

        }else{
            $xmlString .= '<TotalComprobante>' . $payload['resumen']['totalcomprobante'] . '</TotalComprobante>';
        }
        $xmlString .= '</ResumenFactura>';
        if(!isset($payload['referencia']) OR empty($payload['referencia']))
        {
            return response()->json(array("code"=>"10","data" => "Datos incompletos en la solicitud. La [referencia] es requerida para este tipo de comprobante", "body" => $payload,"fecha"=>$fechaEmision), 400);
        }
        if(!isset($payload['referencia']['tipo_documento']) OR empty($payload['referencia']['tipo_documento']))
        {
            return response()->json(array("code"=>"10","data" => "Datos incompletos en la solicitud. El [tipo_documento] de la referencia es requerido", "body" => $payload,"fecha"=>$fechaEmision), 400);
        }

        if(!isset($payload['referencia']['numero_documento']) OR empty($payload['referencia']['numero_documento']))
        {
            return response()->json(array("code"=>"10","data" => "Datos incompletos en la solicitud. El [numero_documento] de la referencia es requerido", "body" => $payload,"fecha"=>$fechaEmision), 400);

        }

        if(!isset($payload['referencia']['fecha_emision']) OR empty($payload['referencia']['fecha_emision']))
        {
            return response()->json(array("code"=>"10","data" => "Datos incompletos en la solicitud. La [fecha_emision] de la referencia es requerida", "body" => $payload,"fecha"=>$fechaEmision), 400);
        }

        if(!isset($payload['referencia']['codigo']) OR empty($payload['referencia']['codigo']))
        {
            return response()->json(array("code"=>"10","data" => "Datos incompletos en la solicitud. El [codigo] de la referencia es requerido", "body" => $payload,"fecha"=>$fechaEmision), 400);
        }

        if(!isset($payload['referencia']['razon']) OR empty($payload['referencia']['razon']))
        {
            return response()->json(array("code"=>"10","data" => "Datos incompletos en la solicitud. La [razon] de la referencia es requerida", "body" => $payload,"fecha"=>$fechaEmision), 400);
        }
        $reglas = [
            'referencia.tipo_documento'=>['regex:/^[0-2-4-5-99]+$/u','min:2','max:2'],
            'referencia.numero_documento'=>['regex:/^[0-9]+$/u','min:50','max:50'],
            'referencia.codigo'=>['regex:/^[0-5-99]+$/u','min:2','max:2'],
            'referencia.razon'=>['max:180']
        ];
        $mensajes = [

            'referencia.tipo_documento.regex'=>'El formato del [tipo_documento] de referencia es incorrecto *41',
            'referencia.tipo_documento.min'=>'El formato del [tipo_documento] de referencia es incorrecto *41',
            'referencia.tipo_documento.max'=>'El formato del [tipo_documento] de referencia es incorrecto *41',
            'referencia.numero_documento.regex'=>'El formato del [numero_documento] de referencia es incorrecto *41',
            'referencia.numero_documento.min'=>'El formato del [numero_documento] de referencia es incorrecto *41',
            'referencia.numero_documento.max'=>'El formato del [numero_documento] de referencia es incorrecto *41',
            'referencia.codigo.regex'=>'El formato del [codigo] de referencia es incorrecto *41',
            'referencia.codigo.min'=>'El formato del [codigo] de referencia es incorrecto *41',
            'referencia.codigo.max'=>'El formato del [codigo] de referencia es incorrecto *41',
            'referencia.razon.max'=>'La [razon] de referencia debe menor o igual a 180 caracteres *41',
        ];

        $v = Validator::make($payload, $reglas,$mensajes);
        if($v->fails())
        {
            $mensaje=$v->errors()->first();
            $code=$this->getCode($mensaje);
            $data=$this->eraseCodeIntoMessage($mensaje,$code);
            return response()->json(array("code"=>$code,"data"=>$data,"fecha"=>$fechaEmision,"payload"=>$payload), 400);
        }

        $xmlString .='<InformacionReferencia>
        <TipoDoc>' . $payload['referencia']['tipo_documento'] . '</TipoDoc>
        <Numero>' . $payload['referencia']['numero_documento'] . '</Numero>
        <FechaEmision>' . $payload['referencia']['fecha_emision'] . '</FechaEmision>
        <Codigo>' . $payload['referencia']['codigo'] . '</Codigo>
        <Razon>' . $payload['referencia']['razon'] . '</Razon>
    </InformacionReferencia>';
        $ot=0;
        if (isset($payload['otros']) AND $payload['otros'] != '')
        {

            if(!isset($payload['otros']['contenido']) OR $payload['otros']['contenido']=='')
            {
                return response()->json(array("code"=>"10","data" => "Datos incompletos en la solicitud. El [contenido] del nodo otros es requerido", "body" => $payload,"fecha"=>$fechaEmision), 400);
            }
            else{
                $xmlString .= '<Otros><OtroTexto>'.$payload['otros']['contenido'].'</OtroTexto></Otros>';
            }
        }

        $xmlString .= '
    </NotaDebitoElectronica>';
        return response()->json(array("code"=>"1","data"=>base64_encode($xmlString),"fecha"=>$fechaEmision), 200);
    }
    public function makeFEC($payload)
    {
        $consecutivo = substr($payload['clave'], 21, 20);
        $fechaEmision=date(DATE_RFC3339);
        /*if(!isset($payload['encabezado']['fecha']) or empty($payload['encabezado']['fecha']))
        {
            return response()->json(array("response"=>"La fecha del comprobante es requerida","estado"=>"error"), 400);
        }*/
        if(!isset($payload['emisor']) or empty($payload['emisor']))
        {
            return response()->json(array("code"=>"10","data"=>"Datos incompletos en la solicitud. El [emisor] es requerido","body"=>$payload,"fecha"=>$fechaEmision), 400);
        }
        if(!isset($payload['emisor']['nombre']) or empty($payload['emisor']['nombre']))
        {
            return response()->json(array("code"=>"10","data"=>"Datos incompletos en la solicitud. El [nombre] del emisor es requerido","body"=>$payload,"fecha"=>$fechaEmision), 400);
        }
        if(!isset($payload['emisor']['identificacion']) or empty($payload['emisor']['identificacion']))
        {
            return response()->json(array("code"=>"10","data"=>"Datos incompletos en la solicitud. La [identificación] del emisor es requerido","body"=>$payload,"fecha"=>$fechaEmision), 400);
        }
        if(!isset($payload['emisor']['identificacion']['tipo']) or empty($payload['emisor']['identificacion']['tipo']))
        {
            return response()->json(array("code"=>"10","data"=>"Datos incompletos en la solicitud. El [tipo] de identificación del emisor es requerido","body"=>$payload,"fecha"=>$fechaEmision), 400);
        }
        if(!isset($payload['emisor']['identificacion']['numero']) or empty($payload['emisor']['identificacion']['numero']))
        {
            return response()->json(array("code"=>"10","data"=>"Datos incompletos en la solicitud. El [numero] de identificación del emisor es requerido","body"=>$payload,"fecha"=>$fechaEmision), 400);
        }
        if(!isset($payload['emisor']['ubicacion']) or empty($payload['emisor']['ubicacion']))
        {
            return response()->json(array("code"=>"10","data"=>"Datos incompletos en la solicitud. La [ubicacion] del emisor es requerida","body"=>$payload,"fecha"=>$fechaEmision), 400);
        }
        if(!isset($payload['emisor']['ubicacion']['provincia']) or empty($payload['emisor']['ubicacion']['provincia']))
        {
            return response()->json(array("code"=>"10","data"=>"Datos incompletos en la solicitud. La [provincia] del emisor es requerida","body"=>$payload,"fecha"=>$fechaEmision), 400);
        }
        if(!isset($payload['emisor']['ubicacion']['canton']) or empty($payload['emisor']['ubicacion']['canton']))
        {
            return response()->json(array("code"=>"10","data"=>"Datos incompletos en la solicitud. El [canton] del emisor es requerido","body"=>$payload,"fecha"=>$fechaEmision), 400);
        }
        if(!isset($payload['emisor']['ubicacion']['distrito']) or empty($payload['emisor']['ubicacion']['distrito']))
        {
            return response()->json(array("code"=>"10","data"=>"Datos incompletos en la solicitud. El [distrito] del emisor es requerido","body"=>$payload,"fecha"=>$fechaEmision), 400);
        }
        if(!isset($payload['emisor']['ubicacion']['sennas']) or empty($payload['emisor']['ubicacion']['sennas']))
        {
            return response()->json(array("code"=>"10","data"=>"Datos incompletos en la solicitud. [sennas] del emisor es requerido","body"=>$payload,"fecha"=>$fechaEmision), 400);
        }
        //SE VALIDA QUE SI EL NODO TELEFON SE INCLUYE, ENTONCES QUE LO QUE ESTÉ ADENTRO ESTÉ COMPLETO
        if(isset($payload['emisor']['telefono']) AND !empty($payload['emisor']['telefono']))
        {
            if(!isset($payload['emisor']['telefono']['cod_pais']) or empty($payload['emisor']['telefono']['cod_pais']))
            {
                return response()->json(array("code"=>"10","data"=>"Datos incompletos en la solicitud. [cod_pais] del telefono del emisor es requerido","body"=>$payload,"fecha"=>$fechaEmision), 400);
            }
            if(!isset($payload['emisor']['telefono']['numero']) or empty($payload['emisor']['telefono']['numero']))
            {
                return response()->json(array("code"=>"10","data"=>"Datos incompletos en la solicitud. El [numero] de telefono del emisor es requerido","body"=>$payload,"fecha"=>$fechaEmision), 400);
            }

        }
        if(isset($payload['emisor']['fax']) AND !empty($payload['emisor']['fax']))
        {
            if(!isset($payload['emisor']['fax']['cod_pais']) or empty($payload['emisor']['fax']['cod_pais']))
            {
                return response()->json(array("code"=>"10","data"=>"Datos incompletos en la solicitud. [cod_pais] del fax del emisor es requerido","body"=>$payload,"fecha"=>$fechaEmision), 400);
            }
            if(!isset($payload['emisor']['fax']['numero']) or empty($payload['emisor']['fax']['numero']))
            {
                return response()->json(array("code"=>"10","data"=>"Datos incompletos en la solicitud. El [numero] de fax del emisor es requerido","body"=>$payload,"fecha"=>$fechaEmision), 400);
            }

        }
        if(!isset($payload['emisor']['correo_electronico']) or empty($payload['emisor']['correo_electronico']))
        {
            return response()->json(array("code"=>"10","data"=>"Datos incompletos en la solicitud. El [correo_electronico] del emisor es requerido","body"=>$payload,"fecha"=>$fechaEmision), 400);
        }


        $xmlString = '<?xml version="1.0" encoding="utf-8"?>
        <FacturaElectronicaCompra xmlns:xsd="http://www.w3.org/2001/XMLSchema" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns="https://cdn.comprobanteselectronicos.go.cr/xml-schemas/v4.3/facturaElectronicaCompra">
        <Clave>' . $payload['clave'] . '</Clave>
        <CodigoActividad>'.$payload['codigo_actividad'].'</CodigoActividad>
        <NumeroConsecutivo>' . $consecutivo . '</NumeroConsecutivo>
        <FechaEmision>' . /*$payload['encabezado']['fecha']*/ $fechaEmision. '</FechaEmision>
        <Emisor>
            <Nombre>' . $payload['emisor']['nombre'] . '</Nombre>
            <Identificacion>
                <Tipo>' . $payload['emisor']['identificacion']['tipo'] . '</Tipo>
                <Numero>' . $payload['emisor']['identificacion']['numero'] . '</Numero>
            </Identificacion>
            <NombreComercial>' . $payload['emisor']['nombre_comercial'] . '</NombreComercial>';
        $xmlString .= '
        <Ubicacion>
            <Provincia>' . $payload['emisor']['ubicacion']['provincia'] . '</Provincia>
            <Canton>' . $payload['emisor']['ubicacion']['canton'] . '</Canton>
            <Distrito>' . $payload['emisor']['ubicacion']['distrito'] . '</Distrito>';
        if (isset($payload['emisor']['ubicacion']['barrio']) AND $payload['emisor']['ubicacion']['barrio'] != ''){
            $xmlString .= '<Barrio>' . $payload['emisor']['ubicacion']['barrio'] . '</Barrio>';}
        else{
            $xmlString .= '<Barrio>01</Barrio>';
        }
        $xmlString .= '
                <OtrasSenas>' . $payload['emisor']['ubicacion']['sennas'] . '</OtrasSenas>
            </Ubicacion>';


        if (isset($payload['emisor']['telefono']['cod_pais']) AND $payload['emisor']['telefono']['cod_pais'] != '' AND isset($payload['emisor']['telefono']['numero']) AND $payload['emisor']['telefono']['numero'] != '') {
            $xmlString .= '
            <Telefono>
                <CodigoPais>' . $payload['emisor']['telefono']['cod_pais'] . '</CodigoPais>
                <NumTelefono>' . $payload['emisor']['telefono']['numero'] . '</NumTelefono>
            </Telefono>';
        }


        if (isset($payload['emisor']['fax']['cod_pais']) AND $payload['emisor']['fax']['cod_pais'] != '' AND isset($payload['emisor']['fax']['numero']) AND $payload['emisor']['fax']['numero'] != '') {
            $xmlString .= '
            <Fax>
                <CodigoPais>' . $payload['emisor']['fax']['cod_pais'] . '</CodigoPais>
                <NumTelefono>' . $payload['emisor']['fax']['numero'] . '</NumTelefono>
            </Fax>';
        }

        $xmlString .= '<CorreoElectronico>' . $payload['emisor']['correo_electronico'] . '</CorreoElectronico>
        </Emisor>';


        if (isset($payload['receptor']['nombre']) and $payload['receptor']['nombre'] != '')
        {
            $xmlString .= '<Receptor>
            <Nombre>' . $payload['receptor']['nombre'] . '</Nombre>';


            if (isset($payload['receptor']['IdentificacionExtranjero']) AND $payload['receptor']['IdentificacionExtranjero'] != '')
            {
                $xmlString .= '<IdentificacionExtranjero>'
                    . $payload['receptor']['IdentificacionExtranjero']
                    . ' </IdentificacionExtranjero>';
            }


            if (isset($payload['receptor']['identificacion']['tipo']) AND $payload['receptor']['identificacion']['tipo'] != '') {
                $xmlString .= '<Identificacion>
                    <Tipo>' . $payload['receptor']['identificacion']['tipo'] . '</Tipo>';
            } else {
                return response()->json(array("code"=>"10","data" => "Datos incompletos en la solicitud. El [tipo] de identificación del receptor es requerido", "body" => $payload,"fecha"=>$fechaEmision), 400);
            }
            if (isset($payload['receptor']['identificacion']['numero']) AND $payload['receptor']['identificacion']['numero'] != '') {
                $xmlString .='<Numero>' . $payload['receptor']['identificacion']['numero'] . '</Numero></Identificacion>';

            }
            else{
                return response()->json(array("code"=>"10","data" => "Datos incompletos en la solicitud. El [numero] de identificación del receptor es requerido", "body" => $payload,"fecha"=>$fechaEmision), 400);
            }
            if (isset($payload['receptor']['ubicacion']) AND $payload['receptor']['ubicacion'] != '' ) {
                if (!isset($payload['receptor']['ubicacion']['provincia']) OR $payload['receptor']['ubicacion']['provincia'] == '' ) {
                    return response()->json(array("code"=>"10","data" => "Datos incompletos en la solicitud. La [provincia] del receptor es requerida", "body" => $payload,"fecha"=>$fechaEmision), 400);
                }
                if (!isset($payload['receptor']['ubicacion']['canton']) OR $payload['receptor']['ubicacion']['canton'] == '' ) {
                    return response()->json(array("code"=>"10","data" => "Datos incompletos en la solicitud. El [canton] del receptor es requerido", "body" => $payload,"fecha"=>$fechaEmision), 400);
                }
                if (!isset($payload['receptor']['ubicacion']['distrito']) OR $payload['receptor']['ubicacion']['distrito'] == '' ) {
                    return response()->json(array("code"=>"10","data" => "Datos incompletos en la solicitud. El [distrito] del receptor es requerido", "body" => $payload,"fecha"=>$fechaEmision), 400);
                }
                if (!isset($payload['receptor']['ubicacion']['sennas']) OR $payload['receptor']['ubicacion']['sennas'] == '' ) {
                    return response()->json(array("code"=>"10","data" => "Datos incompletos en la solicitud. [sennas] del receptor es requerido", "body" => $payload,"fecha"=>$fechaEmision), 400);
                }
                $xmlString .= '
                    <Ubicacion>
                        <Provincia>' . $payload['receptor']['ubicacion']['provincia'] . '</Provincia>
                        <Canton>' . $payload['receptor']['ubicacion']['canton'] . '</Canton>
                        <Distrito>' . $payload['receptor']['ubicacion']['distrito'] . '</Distrito>';
                if (isset($payload['receptor']['ubicacion']['barrio']) AND $payload['receptor']['ubicacion']['barrio'] != ''){
                    $xmlString .= '
                            <Barrio>' . $payload['receptor']['ubicacion']['barrio'] . '</Barrio>';}
                $xmlString .= ' <OtrasSenas>' . $payload['receptor']['ubicacion']['sennas'] . '</OtrasSenas>
                    </Ubicacion>';

            }
            if (!empty($payload['receptor']['IdentificacionExtranjero']) AND empty($payload['receptor']['sennas_ext']))
            {
                return response()->json(array("code"=>"10","data" => "Datos incompletos en la solicitud. El [sennas_ext] del receptor es requerido", "body"=>$payload,"fecha"=>$fechaEmision), 400);
            }

            if (empty($payload['receptor']['IdentificacionExtranjero']) AND !empty($payload['receptor']['sennas_ext']))
            {
                return response()->json(array("code"=>"10","data" => "Datos incompletos en la solicitud. La [IdentificacionExtranjero] del receptor es requerido", "body"=>$payload,"fecha"=>$fechaEmision), 400);
            }
            if(!empty($payload['receptor']['IdentificacionExtranjero']) AND !empty($payload['receptor']['sennas_ext'])){
                $xmlString .= '<OtrasSenasExtranjero>'
                    . $payload['receptor']['sennas_ext']
                    . ' </OtrasSenasExtranjero>';
            }
            if (isset($payload['receptor']['telefono']) AND $payload['receptor']['telefono'] !='')
            {
                if (!isset($payload['receptor']['telefono']['cod_pais']) OR $payload['receptor']['telefono']['cod_pais'] =='')
                {
                    return response()->json(array("code"=>"10","data" => "Datos incompletos en la solicitud. El [cod_pais] del teléfono del receptor es requerido", "body" => $payload,"fecha"=>$fechaEmision), 400);
                }
                if (!isset($payload['receptor']['telefono']['numero']) OR $payload['receptor']['telefono']['numero'] =='')
                {
                    return response()->json(array("code"=>"10","data" => "Datos incompletos en la solicitud. El [numero] de teléfono del receptor es requerido", "body" => $payload,"fecha"=>$fechaEmision), 400);
                }
                $xmlString .= '<Telefono>
                                   <CodigoPais>' . $payload['receptor']['telefono']['cod_pais'] . '</CodigoPais>';
                $xmlString .= '<NumTelefono>' . $payload['receptor']['telefono']['numero'] . '</NumTelefono>
                    </Telefono>';

            }
            if (isset($payload['receptor']['fax']) AND $payload['receptor']['fax'] !='')
            {
                if (!isset($payload['receptor']['fax']['cod_pais']) OR $payload['receptor']['fax']['cod_pais'] =='')
                {
                    return response()->json(array("code"=>"10","data" => "Datos incompletos en la solicitud. El [cod_pais] del fax del receptor es requerido", "body"=>$payload,"fecha"=>$fechaEmision), 400);
                }
                if (!isset($payload['receptor']['fax']['numero']) OR $payload['receptor']['fax']['numero'] =='')
                {
                    return response()->json(array("code"=>"10","data" => "Datos incompletos en la solicitud. El [numero] de fax del receptor es requerido", "body"=>$payload,"fecha"=>$fechaEmision), 400);
                }
                $xmlString .= '<Fax>
                                   <CodigoPais>' . $payload['receptor']['telefono']['cod_pais'] . '</CodigoPais>';
                $xmlString .= '<NumTelefono>' . $payload['receptor']['telefono']['numero'] . '</NumTelefono>
                    </Fax>';

            }


            if (!isset($payload['receptor']['correo_electronico']) OR $payload['receptor']['correo_electronico'] == '')
            {
                return response()->json(array("code"=>"10","data" => "Datos incompletos en la solicitud. El [correo_electronico] del receptor es requerido", "body"=>$payload,"fecha"=>$fechaEmision), 400);
            }
            $xmlString .= '<CorreoElectronico>' . $payload['receptor']['correo_electronico'] . '</CorreoElectronico>';
            $xmlString .= '</Receptor>';

        }
        if(!isset($payload['encabezado']['condicion_venta']) OR $payload['encabezado']['condicion_venta']=='')
        {
            return response()->json(array("code"=>"10","data" => "Datos incompletos en la solicitud. La [condicion_venta] es requerida", "body"=>$payload,"fecha"=>$fechaEmision), 400);
        }
        $xmlString .= '
        <CondicionVenta>' . $payload['encabezado']['condicion_venta'] . '</CondicionVenta>';
        if (isset($payload['encabezado']['plazo_credito']) AND $payload['encabezado']['plazo_credito']!='')
        {
            $xmlString .= '<PlazoCredito>' . $payload['encabezado']['plazo_credito'] . '</PlazoCredito>';

        }else
        {
            $xmlString .= '<PlazoCredito>0</PlazoCredito>';
        }
        if(!isset($payload['encabezado']['mediopago']) OR $payload['encabezado']['mediopago']=='')
        {
            return response()->json(array("code"=>"10","data" => "Datos incompletos en la solicitud. El [mediopago] es requerido", "body"=>$payload,"fecha"=>$fechaEmision), 400);
        }
        $xmlString .='<MedioPago>' . $payload['encabezado']['mediopago'] . '</MedioPago>';
        $xmlString .='<DetalleServicio>';

        if(!isset($payload['detalle']) OR $payload['detalle']=='')
        {
            return response()->json(array("code"=>"10","data" => "Datos incompletos en la solicitud. El [detalle] es requerido", "body"=>$payload,"fecha"=>$fechaEmision), 400);
        }
        $l = 1;
        $pos=0;
        $totalServGravados=0.00000;
        $totalServExentos=0.00000;
        $totalMercanciasGravadas=0.00000;
        $totalMercanciasExentas=0.00000;
        $totalGravado=0.00000;
        $totalExento=0.00000;
        $totalVenta=0.00000;
        $totalDescuentos=0.00000;
        $totalVentaNeta=0.00000;
        $totalImpuesto=0.00000;
        $totalComprobante=0.00000;


        foreach ($payload['detalle'] as $d)
        {

            if(!isset($d['numero']) OR $d['numero']=='')
            {
                return response()->json(array("code"=>"10","data" => "Datos incompletos en la solicitud. El [numero] de linea en la linea de detalle [".$l."] es requerido", "body" => $payload,"fecha"=>$fechaEmision), 400);
            }
            else{
                $xmlString .= '<LineaDetalle>
                  <NumeroLinea>' . $d['numero'] . '</NumeroLinea>';
            }
            if(!isset($d['codigo']) OR $d['codigo']=='')
            {
                return response()->json(array("code"=>"10","data" => "Datos incompletos en la solicitud. El [codigo] en la linea de detalle [".$l."] es requerida", "body" => $payload,"fecha"=>$fechaEmision), 400);
            }
            else{
                $xmlString .='<Codigo>' . $d['codigo'] . '</Codigo>';
            }
            if(isset($d['codigoComercial']) AND $d['codigoComercial']!='')
            {

                $xmlString.= '<CodigoComercial>';
                if(!isset($d['codigoComercial']['tipo']) OR $d['codigoComercial']['tipo']==''){
                    return response()->json(array("code"=>"10","data" => "Datos incompletos en la solicitud. El [tipo] de codigoComercial en la linea de detalle [".$l."] es requerido ", "body"=>$payload,"fecha"=>$fechaEmision), 400);
                }
                else{
                    $xmlString.='<Tipo>'.$d['codigoComercial']['tipo'].'</Tipo>';
                }
                if(!isset($d['codigoComercial']['codigo']) OR $d['codigoComercial']['codigo']==''){
                    return response()->json(array("code"=>"10","data" => "Datos incompletos en la solicitud. El [codigo] del nodo codigoComercial en la linea de detalle [".$l."] es requerido", "body"=>$payload,"fecha"=>$fechaEmision), 400);
                }
                else{
                    $xmlString.='<Codigo>'.$d['codigoComercial']['codigo']   .'</Codigo>';
                }
                $xmlString.= '</CodigoComercial>';
            }
            if(!isset($d['cantidad']) OR $d['cantidad']=='')
            {
                return response()->json(array("code"=>"10","data" => "Datos incompletos en la solicitud. La [cantidad] en la linea de detalle [".$l."] es requerida", "body" => $payload,"fecha"=>$fechaEmision), 400);
            }
            else{
                $xmlString .='<Cantidad>' . $d['cantidad'] . '</Cantidad>';
            }
            if(!isset($d['unidad_medida']) OR $d['unidad_medida']=='')
            {
                return response()->json(array("code"=>"10","data" => "Datos incompletos en la solicitud. La [unidad_medida] en la linea de detalle [".$l."] es requerida", "body" => $payload,"fecha"=>$fechaEmision), 400);
            }
            else
            {
                $xmlString.='<UnidadMedida>' . $d['unidad_medida'] . '</UnidadMedida>';
            }
            if(isset($d['unidad_medida_comercial'])){
                $xmlString.='<UnidadMedidaComercial>'.$d['unidad_medida_comercial'].'</UnidadMedidaComercial>';
            }
            if(!isset($d['detalle']) OR $d['detalle']=='')
            {
                return response()->json(array("code"=>"10","data" => "Datos incompletos en la solicitud. El [detalle] en la linea de detalle [".$l."] es requerido", "body" => $payload,"fecha"=>$fechaEmision), 400);
            }else{
                $xmlString.='<Detalle>' . $d['detalle'] . '</Detalle>';
            }
            if(!isset($d['precio_unitario']) OR $d['precio_unitario']=='')
            {
                return response()->json(array("code"=>"10","data" => "Datos incompletos en la solicitud. El [precio_unitario] en la linea de detalle [".$l."] es requerido", "body" => $payload,"fecha"=>$fechaEmision), 400);
            }
            else{
                $xmlString.='<PrecioUnitario>' . $d['precio_unitario'] . '</PrecioUnitario>';
            }
            if(!isset($d['monto_total']) OR $d['monto_total']=='')
            {
                return response()->json(array("code"=>"10","data" => "Datos incompletos en la solicitud. El [monto_total] en la linea de detalle [".$l."] es requerido", "body" => $payload,"fecha"=>$fechaEmision), 400);
            }
            else
            {
                $xmlString.='<MontoTotal>' . $d['monto_total'] . '</MontoTotal>';
            }

            if (isset($d['descuento']) && $d['descuento'] != "")
            {
                if (!isset($d['descuento']['monto']) OR $d['descuento']['monto'] == "")
                {
                    return response()->json(array("code"=>"10","data" => "Datos incompletos en la solicitud. El [monto] del nodo descuento en la linea de detalle [".$l."] es requerido", "body" => $payload,"fecha"=>$fechaEmision), 400);
                }
                else{
                    $xmlString .= '<Descuento><MontoDescuento>' . $d['descuento']['monto'] . '</MontoDescuento>';
                }
                if (!isset($d['descuento']['naturaleza_descuento']) OR $d['descuento']['naturaleza_descuento'] == "")
                {
                    return response()->json(array("code"=>"10","data" => "Datos incompletos en la solicitud. La [naturaleza_descuento] del nodo descuento en la linea de detalle [".$l."] es requerido", "body" => $payload,"fecha"=>$fechaEmision), 400);
                }
                else{
                    $xmlString .= '<NaturalezaDescuento>' . $d['descuento']['naturaleza_descuento'] . '</NaturalezaDescuento></Descuento>';
                }

            }

            if(!isset($d['subtotal']) OR $d['subtotal']==''){
                return response()->json(array("code"=>"10","data" => "Datos incompletos en la solicitud. El [subtotal] en la linea de detalle [".$l."] es requerido", "body" => $payload,"fecha"=>$fechaEmision), 400);
            }
            else{
                $xmlString .= '<SubTotal>' . $d['subtotal'] . '</SubTotal>';
            }

            if (isset($d['impuestos']) && $d['impuestos'] != "")
            {
                foreach ($d['impuestos'] as $y)
                {

                    if($y['codigo']=='07')
                    {
                        if(!isset($d['base_imponible']) OR $d['base_imponible']=='') {
                            return response()->json(array("code" => "10", "data" => "Datos incompletos en la solicitud. La [base_imponible] en la linea de detalle [" . $l . "] es requerido, debido a que uno de los impuestos tiene codigo 07", "body" => $payload, "fecha" => $fechaEmision), 400);
                        }
                        else{
                            $xmlString .= '<BaseImponible>' . $d['base_imponible'] . '</BaseImponible>';
                            break;
                        }
                    }
                }
                $numImp=1;
                foreach ($d['impuestos'] as $i)
                {
                    $xmlString .= '<Impuesto>';
                    if(!isset($i['codigo']) OR $i['codigo']=='')
                    {
                        return response()->json(array("code"=>"10","data" => "Datos incompletos en la solicitud. El [codigo] en la linea de detalle  [".$l."] de la linea del impuesto [".$numImp."] es requerido", "body" => $payload,"fecha"=>$fechaEmision), 400);
                    }else{
                        $xmlString .='<Codigo>' . $i['codigo'] . '</Codigo>';
                    }
                    if($i['codigo']=='01' OR $i['codigo']=='07')
                    {
                        if(!isset($i['codigo_tarifa']) OR $i['codigo_tarifa']=='')
                        {
                            return response()->json(array("code" => "10", "data" => "Datos incompletos en la solicitud. El [codigo_tarifa] en la linea de detalle [" . $l . "] de la linea del impuesto [".$numImp."] es requerido, debido a que uno de los impuestos tiene codigo 01 ó 07", "body" => $payload, "fecha" => $fechaEmision), 400);
                        }
                        else{
                            $xmlString .='<CodigoTarifa>' . $i['codigo_tarifa'] . '</CodigoTarifa>';
                        }
                    }
                    if(!isset($i['tarifa']))
                    {
                        return response()->json(array("code"=>"10","data" => "Datos incompletos en la solicitud. La [tarifa] en la linea de detalle  [".$l."] de la linea del impuesto [".$numImp."] es requerida", "body" => $payload,"fecha"=>$fechaEmision), 400);
                    }
                    else{
                        $xmlString .='<Tarifa>' . $i['tarifa'] . '</Tarifa>';
                    }
                    if($i['codigo']=='08')
                    {
                        if(!isset($i['factor_iva']) OR $i['factor_iva']==''){
                            return response()->json(array("code"=>"10","data" => "Datos incompletos en la solicitud. El [factor_iva] en la linea de detalle  [".$l."] de la linea del impuesto [".$numImp."] es requerida", "body" => $payload,"fecha"=>$fechaEmision), 400);
                        }
                        else{
                            $xmlString .='<FactorIVA>' . $i['factor_iva'] . '</FactorIVA>';
                        }
                    }
                    if(!isset($i['monto']))
                    {
                        return response()->json(array("code"=>"10","data" => "Datos incompletos en la solicitud. El [monto] en la linea de detalle  [".$l."] de la linea del impuesto [".$numImp."] es requerido", "body" => $payload,"fecha"=>$fechaEmision), 400);
                    }
                    else{
                        $xmlString .='<Monto>' . $i['monto'] . '</Monto>';
                        $totalImpuesto+=floatval($i['monto']);
                    }
                    if (isset($i['exoneracion']) && $i['exoneracion'] != "")
                    {
                        $xmlString .= '
                    <Exoneracion>';
                        if (!isset($i['exoneracion']['tipodocumento']) OR $i['exoneracion']['tipodocumento'] == ""){
                            return response()->json(array("code"=>"10","data" => "Datos incompletos en la solicitud. El [tipodocumento] de exoneración en la linea de detalle  [".$l."] del nodo del impuesto [".$numImp."] es requerido", "body" =>$payload,"fecha"=>$fechaEmision), 400);
                        }
                        else{
                            $xmlString .= '<TipoDocumento>' . $i['exoneracion']['tipodocumento'] . '</TipoDocumento>';
                        }
                        if (!isset($i['exoneracion']['numerodocumento']) OR $i['exoneracion']['numerodocumento'] == ""){
                            return response()->json(array("code"=>"10","data" => "Datos incompletos en la solicitud. El [numerodocumento] de exoneración en la linea de detalle  [".$l."] del nodo del impuesto [".$numImp."] es requerido", "body" =>$payload,"fecha"=>$fechaEmision), 400);
                        }
                        else{
                            $xmlString .= '<NumeroDocumento>' . $i['exoneracion']['numerodocumento'] . '</NumeroDocumento>';
                        }
                        if (!isset($i['exoneracion']['nombreinstitucion']) OR $i['exoneracion']['nombreinstitucion'] == ""){
                            return response()->json(array("code"=>"10","data" => "Datos incompletos en la solicitud. El [nombreinstitucion] de exoneración en la linea de detalle  [".$l."] del nodo del impuesto [".$numImp."] es requerido", "body" =>$payload,"fecha"=>$fechaEmision), 400);
                        }
                        else{
                            $xmlString .= '<NombreInstitucion>' . $i['exoneracion']['nombreinstitucion'] . '</NombreInstitucion>';
                        }
                        if (!isset($i['exoneracion']['fechaemision']) OR $i['exoneracion']['fechaemision'] == ""){
                            return response()->json(array("code"=>"10","data" => "La [fechaemision] de exoneración en la linea de detalle  [".$l."] del nodo del impuesto [".$numImp."] es requerido", "body" => $payload,"fecha"=>$fechaEmision), 400);
                        }else{
                            $xmlString .= '<FechaEmision>' . $i['exoneracion']['fechaemision'] . '</FechaEmision>';
                        }
                        if (!isset($i['exoneracion']['porcentaje']) OR $i['exoneracion']['porcentaje'] == ""){
                            return response()->json(array("code"=>"10","data" => "El [porcentaje] del nodo exoneración en la linea de detalle  [".$l."] del nodo del impuesto [".$numImp."] es requerido", "body" => $payload,"fecha"=>$fechaEmision), 400);
                        }else{
                            $xmlString .= '<PorcentajeExoneracion>' . $i['exoneracion']['porcentaje'] . '</PorcentajeExoneracion>';
                        }
                        if (!isset($i['exoneracion']['monto']) OR $i['exoneracion']['monto'] == ""){
                            return response()->json(array("code"=>"10","data" => "El [monto] del nodo exoneración en la linea de detalle  [".$l."] del nodo del impuesto [".$numImp."] es requerido", "body" => $payload,"fecha"=>$fechaEmision), 400);
                        }
                        else{
                            $xmlString .= '<MontoExoneracion>' . $i['exoneracion']['monto'] . '</MontoExoneracion>';
                        }

                        /*if((string)$this->getNewTaxAmount($i['monto'],$i['exoneracion']['porcentaje'])!=$i['exoneracion']['monto'])
                        {
                            return response()->json(array("code"=>"18","data"=>"El monto de exoneración según el porcentaje indicado es incorrecto","fecha"=>$fechaEmision,"detalle"=>$payload['detalle']), 400);
                        }*/

                        $xmlString .= '</Exoneracion>';

                        $totalImpuesto-=floatval($i['monto']);

                        $totalImpuesto+=$this->getNewTaxAmount($i['monto'],$i['exoneracion']['porcentaje']);

                    }

                    $xmlString .= '</Impuesto>';
                    $numImp++;

                }
                if($d['unidad_medida']=='Sp')
                {
                    $totalServGravados+=floatval($d['monto_total']);
                }
                else{
                    $totalMercanciasGravadas+=floatval($d['monto_total']);
                }

            }
            else{
                if($d['unidad_medida']=='Sp')
                {
                    $totalServExentos+=floatval($d['monto_total']);
                }
                else{
                    $totalMercanciasExentas+=floatval($d['monto_total']);
                }
            }
            if(isset($d['impuesto_neto']))
            {
                $xmlString .= '<ImpuestoNeto>' . $d['impuesto_neto'] . '</ImpuestoNeto>';
            }
            if(!isset($d['montototallinea']) OR $d['montototallinea']=='')
            {
                return response()->json( array("code"=>"10","data" => "El [montototallinea] en la linea de detalle [".$l."] es requerido", "body" => $payload,"fecha"=>$fechaEmision), 400);
            }
            else{
                $xmlString .= '<MontoTotalLinea>' . $d['montototallinea'] . '</MontoTotalLinea>';
            }

            $xmlString .= '</LineaDetalle>';
            $l++;
            $pos++;
        }
        $xmlString .= '</DetalleServicio>';
        $totalGravado=$totalServGravados + $totalMercanciasGravadas;
        $totalExento=$totalServExentos + $totalMercanciasExentas;
        $totalVenta=$totalGravado + $totalExento;
        $totalVentaNeta=$totalVenta - $totalDescuentos;
        $totalComprobante=$totalVentaNeta + $totalImpuesto;
        if(isset($payload['otros_cargos']) AND $payload['otros_cargos']!='')
        {
            $xmlString .= '<OtrosCargos>';
            if(!isset($payload['otros_cargos']['tipo_documento']) OR $payload['otros_cargos']['tipo_documento']=='')
            {
                return response()->json( array("code"=>"10","data" => "El [tipo_documento] del nodo otros_cargos es requerido", "body" => $payload,"fecha"=>$fechaEmision), 400);
            }
            else{
                $xmlString .= '<TipoDocumento>' . $payload['otros_cargos']['tipo_documento'] . '</TipoDocumento>';
            }
            if(!isset($payload['otros_cargos']['detalle']) OR $payload['otros_cargos']['detalle']=='')
            {
                return response()->json( array("code"=>"10","data" => "El [detalle] del nodo otros_cargos es requerido", "body" => $payload,"fecha"=>$fechaEmision), 400);
            }
            else{
                $xmlString .= '<Detalle>' . $payload['otros_cargos']['detalle'] . '</Detalle>';
            }
            if(!isset($payload['otros_cargos']['porcentaje']) OR $payload['otros_cargos']['porcentaje']=='')
            {
                return response()->json( array("code"=>"10","data" => "El [porcentaje] del nodo otros_cargos es requerido", "body" => $payload,"fecha"=>$fechaEmision), 400);
            }
            else{
                $xmlString .= '<Porcentaje>' . $payload['otros_cargos']['porcentaje'] . '</Porcentaje>';
            }
            if(!isset($payload['otros_cargos']['monto_cargo']) OR $payload['otros_cargos']['monto_cargo']=='')
            {
                return response()->json( array("code"=>"10","data" => "El [monto_cargo] del nodo otros_cargos es requerido", "body" => $payload,"fecha"=>$fechaEmision), 400);
            }
            else{
                $xmlString .= '<MontoCargo>' . $payload['otros_cargos']['monto_cargo'] . '</MontoCargo>';
            }
            $xmlString .= '</OtrosCargos>';
        }

        if(!isset($payload['resumen']) OR $payload['resumen']==''){
            return response()->json(array("code"=>"10","data" => "Datos incompletos en la solicitud. El [resumen] es requerido", "body" => $payload,"fecha"=>$fechaEmision), 400);
        }

        //Validación de los totales con respectos a las totales del detalle
        /*if((string)$totalServGravados!=$payload['resumen']['totalserviciogravado']
            OR (string)$totalServExentos!=$payload['resumen']['totalservicioexento']
            OR (string)$totalMercanciasGravadas!=$payload['resumen']['totalmercaderiagravado']
            OR (string)$totalMercanciasExentas!=$payload['resumen']['totalmercaderiaexento']
            OR (string)$totalGravado!=$payload['resumen']['totalgravado']
            OR (string)$totalExento!=$payload['resumen']['totalexento']
            OR (string)$totalVenta!=$payload['resumen']['totalventa']
            OR (string)$totalDescuentos!=$payload['resumen']['totaldescuentos']
            OR (string)$totalVentaNeta!=$payload['resumen']['totalventaneta']
            OR (string)$totalImpuesto!=$payload['resumen']['totalimpuestos']
            OR (string)$totalComprobante!=$payload['resumen']['totalcomprobante'])
        {
            return response()->json(array("code"=>"18","data"=>"Alguno de los montos de las facturas no coinciden con los montos de los
detalles correspondientes.","fecha"=>$fechaEmision,"detalle"=>$payload['detalle'],"resumen"=>$payload['resumen'],"NUm"=>$totalComprobante), 400);
        }*/
        $xmlString .= '<ResumenFactura>';
        if(isset($payload['resumen']['codigo_tipo_moneda']))
        {
            if(!isset($payload['resumen']['codigo_tipo_moneda']['moneda']) OR $payload['resumen']['codigo_tipo_moneda']['moneda']==''
                OR !isset($payload['resumen']['codigo_tipo_moneda']['tipo_cambio']) OR $payload['resumen']['codigo_tipo_moneda']['tipo_cambio']=='')
            {
                return response()->json(array("code"=>"10","data" => "Datos incompletos en la solicitud. El nodo [codigo_tipo_moneda] es requerido y debe estar completo cuando la moneda es extranjera, en caso contrario no utilice", "body" => $payload,"fecha"=>$fechaEmision), 400);
            }
            else{

                $xmlString .= '<CodigoTipoMoneda><CodigoMoneda>' . $payload['resumen']['codigo_tipo_moneda']['moneda'] . '</CodigoMoneda><TipoCambio>' . $payload['resumen']['codigo_tipo_moneda']['tipo_cambio'] . '</TipoCambio></CodigoTipoMoneda>';
            }

        }
        else {
            $xmlString .= '<CodigoTipoMoneda><CodigoMoneda>CRC</CodigoMoneda><TipoCambio>1</TipoCambio></CodigoTipoMoneda>';
        }
        if(isset($payload['resumen']['totalserviciogravado']) AND $payload['resumen']['totalserviciogravado']!=''){
            $xmlString .= '<TotalServGravados>' . $payload['resumen']['totalserviciogravado'] . '</TotalServGravados>';

        }
        if(isset($payload['resumen']['totalservicioexento']) AND $payload['resumen']['totalservicioexento']!=''){
            $xmlString .= '<TotalServExentos>' . $payload['resumen']['totalservicioexento'] . '</TotalServExentos>';
        }
        if(isset($payload['resumen']['totalservicioexonerado']) AND $payload['resumen']['totalservicioexonerado']!=''){
            $xmlString .= '<TotalServExonerado>' . $payload['resumen']['totalservicioexento'] . '</TotalServExonerado>';
        }
        if(isset($payload['resumen']['totalmercanciagravada']) AND $payload['resumen']['totalmercanciagravada']!=''){
            $xmlString .= '<TotalMercanciasGravadas>' . $payload['resumen']['totalmercanciagravada'] . '</TotalMercanciasGravadas>';
        }
        if(isset($payload['resumen']['totalmercanciaexenta']) AND $payload['resumen']['totalmercanciaexenta']!=''){
            $xmlString .= '<TotalMercanciasExentas>' . $payload['resumen']['totalmercanciaexenta'] . '</TotalMercanciasExentas>';
        }
        if(isset($payload['resumen']['totalmercanciaexonerada']) AND $payload['resumen']['totalmercanciaexonerada']!=''){
            $xmlString .= '<TotalMercExonerada>' . $payload['resumen']['totalmercanciaexonerada'] . '</TotalMercExonerada>';
        }
        if(isset($payload['resumen']['totalgravado']) AND $payload['resumen']['totalgravado']!=''){
            $xmlString .= '<TotalGravado>' . $payload['resumen']['totalgravado'] . '</TotalGravado>';
        }
        if(isset($payload['resumen']['totalexento']) AND $payload['resumen']['totalexento']!=''){
            $xmlString .= '<TotalExento>' . $payload['resumen']['totalexento'] . '</TotalExento>';
        }
        if(isset($payload['resumen']['totalexonerado']) AND $payload['resumen']['totalexonerado']!=''){
            $xmlString .= '<TotalExonerado>' . $payload['resumen']['totalexonerado'] . '</TotalExonerado>';
        }
        if(!isset($payload['resumen']['totalventa']) OR $payload['resumen']['totalventa']==''){
            return response()->json(array("code"=>"10","data" => "Datos incompletos en la solicitud. El [totalventa] es requerido", "body" => $payload,"fecha"=>$fechaEmision), 400);

        }else{
            $xmlString .= '<TotalVenta>' . $payload['resumen']['totalventa'] . '</TotalVenta>';
        }
        if(isset($payload['resumen']['totaldescuentos']) AND $payload['resumen']['totaldescuentos']!=''){
            $xmlString .= '<TotalDescuentos>' . $payload['resumen']['totaldescuentos'] . '</TotalDescuentos>';
        }
        if(!isset($payload['resumen']['totalventaneta']) OR $payload['resumen']['totalventaneta']==''){
            return response()->json(array("code"=>"10","data" => "Datos incompletos en la solicitud. El [totalventaneta] es requerido", "body" => $payload,"fecha"=>$fechaEmision), 400);

        }else{
            $xmlString .= '<TotalVentaNeta>' . $payload['resumen']['totalventaneta'] . '</TotalVentaNeta>';
        }
        if(isset($payload['resumen']['totalimpuestos']) AND $payload['resumen']['totalimpuestos']!=''){
            $xmlString .= '<TotalImpuesto>' . $payload['resumen']['totalimpuestos'] . '</TotalImpuesto>';
        }
        if(isset($payload['resumen']['totalivadevuelto']) AND $payload['resumen']['totalivadevuelto']!=''){
            $xmlString .= '<TotalIVADevuelto>' . $payload['resumen']['totalivadevuelto'] . '</TotalIVADevuelto>';
        }
        if(isset($payload['resumen']['totalotroscargos']) AND $payload['resumen']['totalotroscargos']!=''){
            $xmlString .= '<TotalOtrosCargos>' . $payload['resumen']['totalotroscargos'] . '</TotalOtrosCargos>';
        }
        if(!isset($payload['resumen']['totalcomprobante']) OR $payload['resumen']['totalcomprobante']==''){
            return response()->json(array("code"=>"10","data" => "Datos incompletos en la solicitud. El [totalcomprobante] es requerido", "body" => $payload,"fecha"=>$fechaEmision), 400);

        }else{
            $xmlString .= '<TotalComprobante>' . $payload['resumen']['totalcomprobante'] . '</TotalComprobante>';
        }
        $xmlString .= '</ResumenFactura>';
        $ot=0;
        if (isset($payload['otros']) AND $payload['otros'] != '')
        {

            if(!isset($payload['otros']['contenido']) OR $payload['otros']['contenido']=='')
            {
                return response()->json(array("code"=>"10","data" => "Datos incompletos en la solicitud. El [contenido] del nodo otros es requerido", "body" => $payload,"fecha"=>$fechaEmision), 400);
            }
            else{
                $xmlString .= '<Otros><OtroTexto>'.$payload['otros']['contenido'].'</OtroTexto></Otros>';
            }
        }

        $xmlString .= '
    </FacturaElectronicaCompra>';

        return response()->json(array("code"=>"1","data"=>base64_encode($xmlString),"fecha"=>$fechaEmision), 200);
    }
    public function makeFEE($payload)
    {
        $consecutivo = substr($payload['clave'], 21, 20);
        $fechaEmision=date(DATE_RFC3339);
        /*if(!isset($payload['encabezado']['fecha']) or empty($payload['encabezado']['fecha']))
        {
            return response()->json(array("response"=>"La fecha del comprobante es requerida","estado"=>"error"), 400);
        }*/
        if(!isset($payload['emisor']) or empty($payload['emisor']))
        {
            return response()->json(array("code"=>"10","data"=>"Datos incompletos en la solicitud. El [emisor] es requerido","body"=>$payload,"fecha"=>$fechaEmision), 400);
        }
        if(!isset($payload['emisor']['nombre']) or empty($payload['emisor']['nombre']))
        {
            return response()->json(array("code"=>"10","data"=>"Datos incompletos en la solicitud. El [nombre] del emisor es requerido","body"=>$payload,"fecha"=>$fechaEmision), 400);
        }
        if(!isset($payload['emisor']['identificacion']) or empty($payload['emisor']['identificacion']))
        {
            return response()->json(array("code"=>"10","data"=>"Datos incompletos en la solicitud. La [identificación] del emisor es requerido","body"=>$payload,"fecha"=>$fechaEmision), 400);
        }
        if(!isset($payload['emisor']['identificacion']['tipo']) or empty($payload['emisor']['identificacion']['tipo']))
        {
            return response()->json(array("code"=>"10","data"=>"Datos incompletos en la solicitud. El [tipo] de identificación del emisor es requerido","body"=>$payload,"fecha"=>$fechaEmision), 400);
        }
        if(!isset($payload['emisor']['identificacion']['numero']) or empty($payload['emisor']['identificacion']['numero']))
        {
            return response()->json(array("code"=>"10","data"=>"Datos incompletos en la solicitud. El [numero] de identificación del emisor es requerido","body"=>$payload,"fecha"=>$fechaEmision), 400);
        }
        if(!isset($payload['emisor']['ubicacion']) or empty($payload['emisor']['ubicacion']))
        {
            return response()->json(array("code"=>"10","data"=>"Datos incompletos en la solicitud. La [ubicacion] del emisor es requerida","body"=>$payload,"fecha"=>$fechaEmision), 400);
        }
        if(!isset($payload['emisor']['ubicacion']['provincia']) or empty($payload['emisor']['ubicacion']['provincia']))
        {
            return response()->json(array("code"=>"10","data"=>"Datos incompletos en la solicitud. La [provincia] del emisor es requerida","body"=>$payload,"fecha"=>$fechaEmision), 400);
        }
        if(!isset($payload['emisor']['ubicacion']['canton']) or empty($payload['emisor']['ubicacion']['canton']))
        {
            return response()->json(array("code"=>"10","data"=>"Datos incompletos en la solicitud. El [canton] del emisor es requerido","body"=>$payload,"fecha"=>$fechaEmision), 400);
        }
        if(!isset($payload['emisor']['ubicacion']['distrito']) or empty($payload['emisor']['ubicacion']['distrito']))
        {
            return response()->json(array("code"=>"10","data"=>"Datos incompletos en la solicitud. El [distrito] del emisor es requerido","body"=>$payload,"fecha"=>$fechaEmision), 400);
        }
        if(!isset($payload['emisor']['ubicacion']['sennas']) or empty($payload['emisor']['ubicacion']['sennas']))
        {
            return response()->json(array("code"=>"10","data"=>"Datos incompletos en la solicitud. [sennas] del emisor es requerido","body"=>$payload,"fecha"=>$fechaEmision), 400);
        }
        //SE VALIDA QUE SI EL NODO TELEFON SE INCLUYE, ENTONCES QUE LO QUE ESTÉ ADENTRO ESTÉ COMPLETO
        if(isset($payload['emisor']['telefono']) AND !empty($payload['emisor']['telefono']))
        {
            if(!isset($payload['emisor']['telefono']['cod_pais']) or empty($payload['emisor']['telefono']['cod_pais']))
            {
                return response()->json(array("code"=>"10","data"=>"Datos incompletos en la solicitud. [cod_pais] del telefono del emisor es requerido","body"=>$payload,"fecha"=>$fechaEmision), 400);
            }
            if(!isset($payload['emisor']['telefono']['numero']) or empty($payload['emisor']['telefono']['numero']))
            {
                return response()->json(array("code"=>"10","data"=>"Datos incompletos en la solicitud. El [numero] de telefono del emisor es requerido","body"=>$payload,"fecha"=>$fechaEmision), 400);
            }

        }
        if(isset($payload['emisor']['fax']) AND !empty($payload['emisor']['fax']))
        {
            if(!isset($payload['emisor']['fax']['cod_pais']) or empty($payload['emisor']['fax']['cod_pais']))
            {
                return response()->json(array("code"=>"10","data"=>"Datos incompletos en la solicitud. [cod_pais] del fax del emisor es requerido","body"=>$payload,"fecha"=>$fechaEmision), 400);
            }
            if(!isset($payload['emisor']['fax']['numero']) or empty($payload['emisor']['fax']['numero']))
            {
                return response()->json(array("code"=>"10","data"=>"Datos incompletos en la solicitud. El [numero] de fax del emisor es requerido","body"=>$payload,"fecha"=>$fechaEmision), 400);
            }

        }
        if(!isset($payload['emisor']['correo_electronico']) or empty($payload['emisor']['correo_electronico']))
        {
            return response()->json(array("code"=>"10","data"=>"Datos incompletos en la solicitud. El [correo_electronico] del emisor es requerido","body"=>$payload,"fecha"=>$fechaEmision), 400);
        }


        $xmlString = '<?xml version="1.0" encoding="utf-8"?>
        <FacturaElectronicaExportacion xmlns:xsd="http://www.w3.org/2001/XMLSchema" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns="https://cdn.comprobanteselectronicos.go.cr/xml-schemas/v4.3/facturaElectronicaExportacion">
        <Clave>' . $payload['clave'] . '</Clave>
        <CodigoActividad>'.$payload['codigo_actividad'].'</CodigoActividad>
        <NumeroConsecutivo>' . $consecutivo . '</NumeroConsecutivo>
        <FechaEmision>' . /*$payload['encabezado']['fecha']*/ $fechaEmision. '</FechaEmision>
        <Emisor>
            <Nombre>' . $payload['emisor']['nombre'] . '</Nombre>
            <Identificacion>
                <Tipo>' . $payload['emisor']['identificacion']['tipo'] . '</Tipo>
                <Numero>' . $payload['emisor']['identificacion']['numero'] . '</Numero>
            </Identificacion>
            <NombreComercial>' . $payload['emisor']['nombre_comercial'] . '</NombreComercial>';
        $xmlString .= '
        <Ubicacion>
            <Provincia>' . $payload['emisor']['ubicacion']['provincia'] . '</Provincia>
            <Canton>' . $payload['emisor']['ubicacion']['canton'] . '</Canton>
            <Distrito>' . $payload['emisor']['ubicacion']['distrito'] . '</Distrito>';
        if (isset($payload['emisor']['ubicacion']['barrio']) AND $payload['emisor']['ubicacion']['barrio'] != ''){
            $xmlString .= '<Barrio>' . $payload['emisor']['ubicacion']['barrio'] . '</Barrio>';}
        else{
            $xmlString .= '<Barrio>01</Barrio>';
        }
        $xmlString .= '
                <OtrasSenas>' . $payload['emisor']['ubicacion']['sennas'] . '</OtrasSenas>
            </Ubicacion>';


        if (isset($payload['emisor']['telefono']['cod_pais']) AND $payload['emisor']['telefono']['cod_pais'] != '' AND isset($payload['emisor']['telefono']['numero']) AND $payload['emisor']['telefono']['numero'] != '') {
            $xmlString .= '
            <Telefono>
                <CodigoPais>' . $payload['emisor']['telefono']['cod_pais'] . '</CodigoPais>
                <NumTelefono>' . $payload['emisor']['telefono']['numero'] . '</NumTelefono>
            </Telefono>';
        }


        if (isset($payload['emisor']['fax']['cod_pais']) AND $payload['emisor']['fax']['cod_pais'] != '' AND isset($payload['emisor']['fax']['numero']) AND $payload['emisor']['fax']['numero'] != '') {
            $xmlString .= '
            <Fax>
                <CodigoPais>' . $payload['emisor']['fax']['cod_pais'] . '</CodigoPais>
                <NumTelefono>' . $payload['emisor']['fax']['numero'] . '</NumTelefono>
            </Fax>';
        }

        $xmlString .= '<CorreoElectronico>' . $payload['emisor']['correo_electronico'] . '</CorreoElectronico>
        </Emisor>';


        if (isset($payload['receptor']['nombre']) and $payload['receptor']['nombre'] != '')
        {
            $xmlString .= '<Receptor>
            <Nombre>' . $payload['receptor']['nombre'] . '</Nombre>';


            if (isset($payload['receptor']['IdentificacionExtranjero']) AND $payload['receptor']['IdentificacionExtranjero'] != '')
            {
                $xmlString .= '<IdentificacionExtranjero>'
                    . $payload['receptor']['IdentificacionExtranjero']
                    . ' </IdentificacionExtranjero>';
            }


            if (isset($payload['receptor']['identificacion']['tipo']) AND $payload['receptor']['identificacion']['tipo'] != '') {
                $xmlString .= '<Identificacion>
                    <Tipo>' . $payload['receptor']['identificacion']['tipo'] . '</Tipo>';
            } else {
                return response()->json(array("code"=>"10","data" => "Datos incompletos en la solicitud. El [tipo] de identificación del receptor es requerido", "body" => $payload,"fecha"=>$fechaEmision), 400);
            }
            if (isset($payload['receptor']['identificacion']['numero']) AND $payload['receptor']['identificacion']['numero'] != '') {
                $xmlString .='<Numero>' . $payload['receptor']['identificacion']['numero'] . '</Numero></Identificacion>';

            }
            else{
                return response()->json(array("code"=>"10","data" => "Datos incompletos en la solicitud. El [numero] de identificación del receptor es requerido", "body" => $payload,"fecha"=>$fechaEmision), 400);
            }
            if (isset($payload['receptor']['ubicacion']) AND $payload['receptor']['ubicacion'] != '' ) {
                if (!isset($payload['receptor']['ubicacion']['provincia']) OR $payload['receptor']['ubicacion']['provincia'] == '' ) {
                    return response()->json(array("code"=>"10","data" => "Datos incompletos en la solicitud. La [provincia] del receptor es requerida", "body" => $payload,"fecha"=>$fechaEmision), 400);
                }
                if (!isset($payload['receptor']['ubicacion']['canton']) OR $payload['receptor']['ubicacion']['canton'] == '' ) {
                    return response()->json(array("code"=>"10","data" => "Datos incompletos en la solicitud. El [canton] del receptor es requerido", "body" => $payload,"fecha"=>$fechaEmision), 400);
                }
                if (!isset($payload['receptor']['ubicacion']['distrito']) OR $payload['receptor']['ubicacion']['distrito'] == '' ) {
                    return response()->json(array("code"=>"10","data" => "Datos incompletos en la solicitud. El [distrito] del receptor es requerido", "body" => $payload,"fecha"=>$fechaEmision), 400);
                }
                if (!isset($payload['receptor']['ubicacion']['sennas']) OR $payload['receptor']['ubicacion']['sennas'] == '' ) {
                    return response()->json(array("code"=>"10","data" => "Datos incompletos en la solicitud. [sennas] del receptor es requerido", "body" => $payload,"fecha"=>$fechaEmision), 400);
                }
                $xmlString .= '
                    <Ubicacion>
                        <Provincia>' . $payload['receptor']['ubicacion']['provincia'] . '</Provincia>
                        <Canton>' . $payload['receptor']['ubicacion']['canton'] . '</Canton>
                        <Distrito>' . $payload['receptor']['ubicacion']['distrito'] . '</Distrito>';
                if (isset($payload['receptor']['ubicacion']['barrio']) AND $payload['receptor']['ubicacion']['barrio'] != ''){
                    $xmlString .= '
                            <Barrio>' . $payload['receptor']['ubicacion']['barrio'] . '</Barrio>';}
                $xmlString .= ' <OtrasSenas>' . $payload['receptor']['ubicacion']['sennas'] . '</OtrasSenas>
                    </Ubicacion>';

            }
            if (!empty($payload['receptor']['IdentificacionExtranjero']) AND empty($payload['receptor']['sennas_ext']))
            {
                return response()->json(array("code"=>"10","data" => "Datos incompletos en la solicitud. El [sennas_ext] del receptor es requerido", "body"=>$payload,"fecha"=>$fechaEmision), 400);
            }

            if (empty($payload['receptor']['IdentificacionExtranjero']) AND !empty($payload['receptor']['sennas_ext']))
            {
                return response()->json(array("code"=>"10","data" => "Datos incompletos en la solicitud. La [IdentificacionExtranjero] del receptor es requerido", "body"=>$payload,"fecha"=>$fechaEmision), 400);
            }
            if(!empty($payload['receptor']['IdentificacionExtranjero']) AND !empty($payload['receptor']['sennas_ext'])){
                $xmlString .= '<OtrasSenasExtranjero>'
                    . $payload['receptor']['sennas_ext']
                    . ' </OtrasSenasExtranjero>';
            }
            if (isset($payload['receptor']['telefono']) AND $payload['receptor']['telefono'] !='')
            {
                if (!isset($payload['receptor']['telefono']['cod_pais']) OR $payload['receptor']['telefono']['cod_pais'] =='')
                {
                    return response()->json(array("code"=>"10","data" => "Datos incompletos en la solicitud. El [cod_pais] del teléfono del receptor es requerido", "body" => $payload,"fecha"=>$fechaEmision), 400);
                }
                if (!isset($payload['receptor']['telefono']['numero']) OR $payload['receptor']['telefono']['numero'] =='')
                {
                    return response()->json(array("code"=>"10","data" => "Datos incompletos en la solicitud. El [numero] de teléfono del receptor es requerido", "body" => $payload,"fecha"=>$fechaEmision), 400);
                }
                $xmlString .= '<Telefono>
                                   <CodigoPais>' . $payload['receptor']['telefono']['cod_pais'] . '</CodigoPais>';
                $xmlString .= '<NumTelefono>' . $payload['receptor']['telefono']['numero'] . '</NumTelefono>
                    </Telefono>';

            }
            if (isset($payload['receptor']['fax']) AND $payload['receptor']['fax'] !='')
            {
                if (!isset($payload['receptor']['fax']['cod_pais']) OR $payload['receptor']['fax']['cod_pais'] =='')
                {
                    return response()->json(array("code"=>"10","data" => "Datos incompletos en la solicitud. El [cod_pais] del fax del receptor es requerido", "body"=>$payload,"fecha"=>$fechaEmision), 400);
                }
                if (!isset($payload['receptor']['fax']['numero']) OR $payload['receptor']['fax']['numero'] =='')
                {
                    return response()->json(array("code"=>"10","data" => "Datos incompletos en la solicitud. El [numero] de fax del receptor es requerido", "body"=>$payload,"fecha"=>$fechaEmision), 400);
                }
                $xmlString .= '<Fax>
                                   <CodigoPais>' . $payload['receptor']['telefono']['cod_pais'] . '</CodigoPais>';
                $xmlString .= '<NumTelefono>' . $payload['receptor']['telefono']['numero'] . '</NumTelefono>
                    </Fax>';

            }


            if (!isset($payload['receptor']['correo_electronico']) OR $payload['receptor']['correo_electronico'] == '')
            {
                return response()->json(array("code"=>"10","data" => "Datos incompletos en la solicitud. El [correo_electronico] del receptor es requerido", "body"=>$payload,"fecha"=>$fechaEmision), 400);
            }
            $xmlString .= '<CorreoElectronico>' . $payload['receptor']['correo_electronico'] . '</CorreoElectronico>';
            $xmlString .= '</Receptor>';

        }
        if(!isset($payload['encabezado']['condicion_venta']) OR $payload['encabezado']['condicion_venta']=='')
        {
            return response()->json(array("code"=>"10","data" => "Datos incompletos en la solicitud. La [condicion_venta] es requerida", "body"=>$payload,"fecha"=>$fechaEmision), 400);
        }
        $xmlString .= '
        <CondicionVenta>' . $payload['encabezado']['condicion_venta'] . '</CondicionVenta>';
        if (isset($payload['encabezado']['plazo_credito']) AND $payload['encabezado']['plazo_credito']!='')
        {
            $xmlString .= '<PlazoCredito>' . $payload['encabezado']['plazo_credito'] . '</PlazoCredito>';

        }else
        {
            $xmlString .= '<PlazoCredito>0</PlazoCredito>';
        }
        if(!isset($payload['encabezado']['mediopago']) OR $payload['encabezado']['mediopago']=='')
        {
            return response()->json(array("code"=>"10","data" => "Datos incompletos en la solicitud. El [mediopago] es requerido", "body"=>$payload,"fecha"=>$fechaEmision), 400);
        }
        $xmlString .='<MedioPago>' . $payload['encabezado']['mediopago'] . '</MedioPago>';
        $xmlString .='<DetalleServicio>';

        if(!isset($payload['detalle']) OR $payload['detalle']=='')
        {
            return response()->json(array("code"=>"10","data" => "Datos incompletos en la solicitud. El [detalle] es requerido", "body"=>$payload,"fecha"=>$fechaEmision), 400);
        }
        $l = 1;
        $pos=0;
        $totalServGravados=0.00000;
        $totalServExentos=0.00000;
        $totalMercanciasGravadas=0.00000;
        $totalMercanciasExentas=0.00000;
        $totalGravado=0.00000;
        $totalExento=0.00000;
        $totalVenta=0.00000;
        $totalDescuentos=0.00000;
        $totalVentaNeta=0.00000;
        $totalImpuesto=0.00000;
        $totalComprobante=0.00000;


        foreach ($payload['detalle'] as $d)
        {

            if(!isset($d['numero']) OR $d['numero']=='')
            {
                return response()->json(array("code"=>"10","data" => "Datos incompletos en la solicitud. El [numero] de linea en la linea de detalle [".$l."] es requerido", "body" => $payload,"fecha"=>$fechaEmision), 400);
            }
            else{
                $xmlString .= '<LineaDetalle>
                  <NumeroLinea>' . $d['numero'] . '</NumeroLinea>';
            }

            if(!isset($d['codigo']) OR $d['codigo']=='')
            {
                return response()->json(array("code"=>"10","data" => "Datos incompletos en la solicitud. El [codigo] en la linea de detalle [".$l."] es requerida", "body" => $payload,"fecha"=>$fechaEmision), 400);
            }
            else{
                $xmlString .='<Codigo>' . $d['codigo'] . '</Codigo>';
            }
            if(isset($d['codigoComercial']) AND $d['codigoComercial']!='')
            {

                $xmlString.= '<CodigoComercial>';
                if(!isset($d['codigoComercial']['tipo']) OR $d['codigoComercial']['tipo']==''){
                    return response()->json(array("code"=>"10","data" => "Datos incompletos en la solicitud. El [tipo] de codigoComercial en la linea de detalle [".$l."] es requerido ", "body"=>$payload,"fecha"=>$fechaEmision), 400);
                }
                else{
                    $xmlString.='<Tipo>'.$d['codigoComercial']['tipo'].'</Tipo>';
                }
                if(!isset($d['codigoComercial']['codigo']) OR $d['codigoComercial']['codigo']==''){
                    return response()->json(array("code"=>"10","data" => "Datos incompletos en la solicitud. El [codigo] del nodo codigoComercial en la linea de detalle [".$l."] es requerido", "body"=>$payload,"fecha"=>$fechaEmision), 400);
                }
                else{
                    $xmlString.='<Codigo>'.$d['codigoComercial']['codigo']   .'</Codigo>';
                }
                $xmlString.= '</CodigoComercial>';
            }
            if(!isset($d['cantidad']) OR $d['cantidad']=='')
            {
                return response()->json(array("code"=>"10","data" => "Datos incompletos en la solicitud. La [cantidad] en la linea de detalle [".$l."] es requerida", "body" => $payload,"fecha"=>$fechaEmision), 400);
            }
            else{
                $xmlString .='<Cantidad>' . $d['cantidad'] . '</Cantidad>';
            }
            if(!isset($d['unidad_medida']) OR $d['unidad_medida']=='')
            {
                return response()->json(array("code"=>"10","data" => "Datos incompletos en la solicitud. La [unidad_medida] en la linea de detalle [".$l."] es requerida", "body" => $payload,"fecha"=>$fechaEmision), 400);
            }
            else
            {
                $xmlString.='<UnidadMedida>' . $d['unidad_medida'] . '</UnidadMedida>';
            }
            if(isset($d['unidad_medida_comercial'])){
                $xmlString.='<UnidadMedidaComercial>'.$d['unidad_medida_comercial'].'</UnidadMedidaComercial>';
            }
            if(!isset($d['detalle']) OR $d['detalle']=='')
            {
                return response()->json(array("code"=>"10","data" => "Datos incompletos en la solicitud. El [detalle] en la linea de detalle [".$l."] es requerido", "body" => $payload,"fecha"=>$fechaEmision), 400);
            }else{
                $xmlString.='<Detalle>' . $d['detalle'] . '</Detalle>';
            }
            if(!isset($d['precio_unitario']) OR $d['precio_unitario']=='')
            {
                return response()->json(array("code"=>"10","data" => "Datos incompletos en la solicitud. El [precio_unitario] en la linea de detalle [".$l."] es requerido", "body" => $payload,"fecha"=>$fechaEmision), 400);
            }
            else{
                $xmlString.='<PrecioUnitario>' . $d['precio_unitario'] . '</PrecioUnitario>';
            }
            if(!isset($d['monto_total']) OR $d['monto_total']=='')
            {
                return response()->json(array("code"=>"10","data" => "Datos incompletos en la solicitud. El [monto_total] en la linea de detalle [".$l."] es requerido", "body" => $payload,"fecha"=>$fechaEmision), 400);
            }
            else
            {
                $xmlString.='<MontoTotal>' . $d['monto_total'] . '</MontoTotal>';
            }

            if (isset($d['descuento']) && $d['descuento'] != "")
            {
                if (!isset($d['descuento']['monto']) OR $d['descuento']['monto'] == "")
                {
                    return response()->json(array("code"=>"10","data" => "Datos incompletos en la solicitud. El [monto] del nodo descuento en la linea de detalle [".$l."] es requerido", "body" => $payload,"fecha"=>$fechaEmision), 400);
                }
                else{
                    $xmlString .= '<Descuento><MontoDescuento>' . $d['descuento']['monto'] . '</MontoDescuento>';
                }
                if (!isset($d['descuento']['naturaleza_descuento']) OR $d['descuento']['naturaleza_descuento'] == "")
                {
                    return response()->json(array("code"=>"10","data" => "Datos incompletos en la solicitud. La [naturaleza_descuento] del nodo descuento en la linea de detalle [".$l."] es requerido", "body" => $payload,"fecha"=>$fechaEmision), 400);
                }
                else{
                    $xmlString .= '<NaturalezaDescuento>' . $d['descuento']['naturaleza_descuento'] . '</NaturalezaDescuento></Descuento>';
                }

            }

            if(!isset($d['subtotal']) OR $d['subtotal']==''){
                return response()->json(array("code"=>"10","data" => "Datos incompletos en la solicitud. El [subtotal] en la linea de detalle [".$l."] es requerido", "body" => $payload,"fecha"=>$fechaEmision), 400);
            }
            else{
                $xmlString .= '<SubTotal>' . $d['subtotal'] . '</SubTotal>';
            }

            if (isset($d['impuestos']) && $d['impuestos'] != "")
            {$numImp=1;
                foreach ($d['impuestos'] as $i)
                {
                    $xmlString .= '<Impuesto>';
                    if(!isset($i['codigo']) OR $i['codigo']=='')
                    {
                        return response()->json(array("code"=>"10","data" => "Datos incompletos en la solicitud. El [codigo] en la linea de detalle  [".$l."] de la linea del impuesto [".$numImp."] es requerido", "body" => $payload,"fecha"=>$fechaEmision), 400);
                    }else{
                        $xmlString .='<Codigo>' . $i['codigo'] . '</Codigo>';
                    }
                    if($i['codigo']=='01' OR $i['codigo']=='07')
                    {
                        if(!isset($i['codigo_tarifa']) OR $i['codigo_tarifa']=='')
                        {
                            return response()->json(array("code" => "10", "data" => "Datos incompletos en la solicitud. El [codigo_tarifa] en la linea de detalle [" . $l . "] de la linea del impuesto [".$numImp."] es requerido, debido a que uno de los impuestos tiene codigo 01 ó 07", "body" => $payload, "fecha" => $fechaEmision), 400);
                        }
                        else{
                            $xmlString .='<CodigoTarifa>' . $i['codigo_tarifa'] . '</CodigoTarifa>';
                        }
                    }
                    if(!isset($i['tarifa']))
                    {
                        return response()->json(array("code"=>"10","data" => "Datos incompletos en la solicitud. La [tarifa] en la linea de detalle  [".$l."] de la linea del impuesto [".$numImp."] es requerida", "body" => $payload,"fecha"=>$fechaEmision), 400);
                    }
                    else{
                        $xmlString .='<Tarifa>' . $i['tarifa'] . '</Tarifa>';
                    }
                    if($i['codigo']=='08')
                    {
                        if(!isset($i['factor_iva']) OR $i['factor_iva']==''){
                            return response()->json(array("code"=>"10","data" => "Datos incompletos en la solicitud. El [factor_iva] en la linea de detalle  [".$l."] de la linea del impuesto [".$numImp."] es requerida", "body" => $payload,"fecha"=>$fechaEmision), 400);
                        }
                        else{
                            $xmlString .='<FactorIVA>' . $i['factor_iva'] . '</FactorIVA>';
                        }
                    }
                    if(!isset($i['monto']))
                    {
                        return response()->json(array("code"=>"10","data" => "Datos incompletos en la solicitud. El [monto] en la linea de detalle  [".$l."] de la linea del impuesto [".$numImp."] es requerido", "body" => $payload,"fecha"=>$fechaEmision), 400);
                    }
                    else{
                        $xmlString .='<Monto>' . $i['monto'] . '</Monto>';
                        $totalImpuesto+=floatval($i['monto']);
                    }
                    if(isset($i['monto_exportacion']) AND $i['monto_exportacion']!='')
                    {
                        $xmlString .='<MontoExportacion>' . $i['monto_exportacion'] . '</MontoExportacion>';
                    }
                    $xmlString .= '</Impuesto>';
                    $numImp++;

                }
                if($d['unidad_medida']=='Sp')
                {
                    $totalServGravados+=floatval($d['monto_total']);
                }
                else{
                    $totalMercanciasGravadas+=floatval($d['monto_total']);
                }

            }
            else{
                if($d['unidad_medida']=='Sp')
                {
                    $totalServExentos+=floatval($d['monto_total']);
                }
                else{
                    $totalMercanciasExentas+=floatval($d['monto_total']);
                }
            }
            if(!isset($d['montototallinea']) OR $d['montototallinea']=='')
            {
                return response()->json( array("code"=>"10","data" => "El [montototallinea] en la linea de detalle [".$l."] es requerido", "body" => $payload,"fecha"=>$fechaEmision), 400);
            }
            else{
                $xmlString .= '<MontoTotalLinea>' . $d['montototallinea'] . '</MontoTotalLinea>';
            }

            $xmlString .= '</LineaDetalle>';
            $l++;
            $pos++;
        }
        $xmlString .= '</DetalleServicio>';
        $totalGravado=$totalServGravados + $totalMercanciasGravadas;
        $totalExento=$totalServExentos + $totalMercanciasExentas;
        $totalVenta=$totalGravado + $totalExento;
        $totalVentaNeta=$totalVenta - $totalDescuentos;
        $totalComprobante=$totalVentaNeta + $totalImpuesto;
        if(isset($payload['otros_cargos']) AND $payload['otros_cargos']!='')
        {
            $xmlString .= '<OtrosCargos>';
            if(!isset($payload['otros_cargos']['tipo_documento']) OR $payload['otros_cargos']['tipo_documento']=='')
            {
                return response()->json( array("code"=>"10","data" => "El [tipo_documento] del nodo otros_cargos es requerido", "body" => $payload,"fecha"=>$fechaEmision), 400);
            }
            else{
                $xmlString .= '<TipoDocumento>' . $payload['otros_cargos']['tipo_documento'] . '</TipoDocumento>';
            }
            if(!isset($payload['otros_cargos']['detalle']) OR $payload['otros_cargos']['detalle']=='')
            {
                return response()->json( array("code"=>"10","data" => "El [detalle] del nodo otros_cargos es requerido", "body" => $payload,"fecha"=>$fechaEmision), 400);
            }
            else{
                $xmlString .= '<Detalle>' . $payload['otros_cargos']['detalle'] . '</Detalle>';
            }
            if(!isset($payload['otros_cargos']['porcentaje']) OR $payload['otros_cargos']['porcentaje']=='')
            {
                return response()->json( array("code"=>"10","data" => "El [porcentaje] del nodo otros_cargos es requerido", "body" => $payload,"fecha"=>$fechaEmision), 400);
            }
            else{
                $xmlString .= '<Porcentaje>' . $payload['otros_cargos']['porcentaje'] . '</Porcentaje>';
            }
            if(!isset($payload['otros_cargos']['monto_cargo']) OR $payload['otros_cargos']['monto_cargo']=='')
            {
                return response()->json( array("code"=>"10","data" => "El [monto_cargo] del nodo otros_cargos es requerido", "body" => $payload,"fecha"=>$fechaEmision), 400);
            }
            else{
                $xmlString .= '<MontoCargo>' . $payload['otros_cargos']['monto_cargo'] . '</MontoCargo>';
            }
            $xmlString .= '</OtrosCargos>';
        }

        if(!isset($payload['resumen']) OR $payload['resumen']==''){
            return response()->json(array("code"=>"10","data" => "Datos incompletos en la solicitud. El [resumen] es requerido", "body" => $payload,"fecha"=>$fechaEmision), 400);
        }

        //Validación de los totales con respectos a las totales del detalle
        /*if((string)$totalServGravados!=$payload['resumen']['totalserviciogravado']
            OR (string)$totalServExentos!=$payload['resumen']['totalservicioexento']
            OR (string)$totalMercanciasGravadas!=$payload['resumen']['totalmercaderiagravado']
            OR (string)$totalMercanciasExentas!=$payload['resumen']['totalmercaderiaexento']
            OR (string)$totalGravado!=$payload['resumen']['totalgravado']
            OR (string)$totalExento!=$payload['resumen']['totalexento']
            OR (string)$totalVenta!=$payload['resumen']['totalventa']
            OR (string)$totalDescuentos!=$payload['resumen']['totaldescuentos']
            OR (string)$totalVentaNeta!=$payload['resumen']['totalventaneta']
            OR (string)$totalImpuesto!=$payload['resumen']['totalimpuestos']
            OR (string)$totalComprobante!=$payload['resumen']['totalcomprobante'])
        {
            return response()->json(array("code"=>"18","data"=>"Alguno de los montos de las facturas no coinciden con los montos de los
detalles correspondientes.","fecha"=>$fechaEmision,"detalle"=>$payload['detalle'],"resumen"=>$payload['resumen'],"NUm"=>$totalComprobante), 400);
        }*/
        $xmlString .= '<ResumenFactura>';
        if(isset($payload['resumen']['codigo_tipo_moneda']))
        {
            if(!isset($payload['resumen']['codigo_tipo_moneda']['moneda']) OR $payload['resumen']['codigo_tipo_moneda']['moneda']==''
                OR !isset($payload['resumen']['codigo_tipo_moneda']['tipo_cambio']) OR $payload['resumen']['codigo_tipo_moneda']['tipo_cambio']=='')
            {
                return response()->json(array("code"=>"10","data" => "Datos incompletos en la solicitud. El nodo [codigo_tipo_moneda] es requerido y debe estar completo cuando la moneda es extranjera, en caso contrario no utilice", "body" => $payload,"fecha"=>$fechaEmision), 400);
            }
            else{

                $xmlString .= '<CodigoTipoMoneda><CodigoMoneda>' . $payload['resumen']['codigo_tipo_moneda']['moneda'] . '</CodigoMoneda><TipoCambio>' . $payload['resumen']['codigo_tipo_moneda']['tipo_cambio'] . '</TipoCambio></CodigoTipoMoneda>';
            }

        }
        else {
            $xmlString .= '<CodigoTipoMoneda><CodigoMoneda>CRC</CodigoMoneda><TipoCambio>1</TipoCambio></CodigoTipoMoneda>';
        }
        if(isset($payload['resumen']['totalserviciogravado']) AND $payload['resumen']['totalserviciogravado']!=''){
            $xmlString .= '<TotalServGravados>' . $payload['resumen']['totalserviciogravado'] . '</TotalServGravados>';

        }
        if(isset($payload['resumen']['totalservicioexento']) AND $payload['resumen']['totalservicioexento']!=''){
            $xmlString .= '<TotalServExentos>' . $payload['resumen']['totalservicioexento'] . '</TotalServExentos>';
        }
        if(isset($payload['resumen']['totalservicioexonerado']) AND $payload['resumen']['totalservicioexonerado']!=''){
            $xmlString .= '<TotalServExonerado>' . $payload['resumen']['totalservicioexento'] . '</TotalServExonerado>';
        }
        if(isset($payload['resumen']['totalmercanciagravada']) AND $payload['resumen']['totalmercanciagravada']!=''){
            $xmlString .= '<TotalMercanciasGravadas>' . $payload['resumen']['totalmercanciagravada'] . '</TotalMercanciasGravadas>';
        }
        if(isset($payload['resumen']['totalmercanciaexenta']) AND $payload['resumen']['totalmercanciaexenta']!=''){
            $xmlString .= '<TotalMercanciasExentas>' . $payload['resumen']['totalmercanciaexenta'] . '</TotalMercanciasExentas>';
        }
        if(isset($payload['resumen']['totalmercanciaexonerada']) AND $payload['resumen']['totalmercanciaexonerada']!=''){
            $xmlString .= '<TotalMercExonerada>' . $payload['resumen']['totalmercanciaexonerada'] . '</TotalMercExonerada>';
        }
        if(isset($payload['resumen']['totalgravado']) AND $payload['resumen']['totalgravado']!=''){
            $xmlString .= '<TotalGravado>' . $payload['resumen']['totalgravado'] . '</TotalGravado>';
        }
        if(isset($payload['resumen']['totalexento']) AND $payload['resumen']['totalexento']!=''){
            $xmlString .= '<TotalExento>' . $payload['resumen']['totalexento'] . '</TotalExento>';
        }
        if(isset($payload['resumen']['totalexonerado']) AND $payload['resumen']['totalexonerado']!=''){
            $xmlString .= '<TotalExonerado>' . $payload['resumen']['totalexonerado'] . '</TotalExonerado>';
        }
        if(!isset($payload['resumen']['totalventa']) OR $payload['resumen']['totalventa']==''){
            return response()->json(array("code"=>"10","data" => "Datos incompletos en la solicitud. El [totalventa] es requerido", "body" => $payload,"fecha"=>$fechaEmision), 400);

        }else{
            $xmlString .= '<TotalVenta>' . $payload['resumen']['totalventa'] . '</TotalVenta>';
        }
        if(isset($payload['resumen']['totaldescuentos']) AND $payload['resumen']['totaldescuentos']!=''){
            $xmlString .= '<TotalDescuentos>' . $payload['resumen']['totaldescuentos'] . '</TotalDescuentos>';
        }
        if(!isset($payload['resumen']['totalventaneta']) OR $payload['resumen']['totalventaneta']==''){
            return response()->json(array("code"=>"10","data" => "Datos incompletos en la solicitud. El [totalventaneta] es requerido", "body" => $payload,"fecha"=>$fechaEmision), 400);

        }else{
            $xmlString .= '<TotalVentaNeta>' . $payload['resumen']['totalventaneta'] . '</TotalVentaNeta>';
        }
        if(isset($payload['resumen']['totalimpuestos']) AND $payload['resumen']['totalimpuestos']!=''){
            $xmlString .= '<TotalImpuesto>' . $payload['resumen']['totalimpuestos'] . '</TotalImpuesto>';
        }
        if(isset($payload['resumen']['totalivadevuelto']) AND $payload['resumen']['totalivadevuelto']!=''){
            $xmlString .= '<TotalIVADevuelto>' . $payload['resumen']['totalivadevuelto'] . '</TotalIVADevuelto>';
        }
        if(isset($payload['resumen']['totalotroscargos']) AND $payload['resumen']['totalotroscargos']!=''){
            $xmlString .= '<TotalOtrosCargos>' . $payload['resumen']['totalotroscargos'] . '</TotalOtrosCargos>';
        }
        if(!isset($payload['resumen']['totalcomprobante']) OR $payload['resumen']['totalcomprobante']==''){
            return response()->json(array("code"=>"10","data" => "Datos incompletos en la solicitud. El [totalcomprobante] es requerido", "body" => $payload,"fecha"=>$fechaEmision), 400);

        }else{
            $xmlString .= '<TotalComprobante>' . $payload['resumen']['totalcomprobante'] . '</TotalComprobante>';
        }
        $xmlString .= '</ResumenFactura>';
        $ot=0;
        if (isset($payload['otros']) AND $payload['otros'] != '')
        {

            if(!isset($payload['otros']['contenido']) OR $payload['otros']['contenido']=='')
            {
                return response()->json(array("code"=>"10","data" => "Datos incompletos en la solicitud. El [contenido] del nodo otros es requerido", "body" => $payload,"fecha"=>$fechaEmision), 400);
            }
            else{
                $xmlString .= '<Otros><OtroTexto>'.$payload['otros']['contenido'].'</OtroTexto></Otros>';
            }
        }

        $xmlString .= '
    </FacturaElectronicaExportacion>';

        return response()->json(array("code"=>"1","data"=>base64_encode($xmlString),"fecha"=>$fechaEmision), 200);
    }

    public function getCode($error)
    {
        return substr($error, strpos($error, "*") + 1);
    }
    public function eraseCodeIntoMessage($message,$toErase)
    {
        return str_replace(" *".$toErase,'',$message);
    }
    public function getNewTaxAmount($montoActual,$porcentajeExonerado)
    {
        return floatval($montoActual-($porcentajeExonerado/100)*$montoActual);

    }
    public function conectToEasyATV($parametrosATV)
    {
    $entorno=$parametrosATV['certificateLocation'];
    $ambiente_easy="";
        try {
            if(strpos($entorno,'sandbox'))
            {
                $url = env('EASY_ATV_SERVER_TEST')."/easy-atv/api/atv/recepcion";
                $ambiente_easy=Array("Content-Type: application/json", "X-KeyLicense: wHSz8BoayhIJQjtP","X-Retrieve-SignedXml:true","X-Environment-Hacienda:test");
            }
            else
            {
                $url = env('EASY_ATV_SERVER_PROD')."/easy-atv/api/atv/recepcion";
                $ambiente_easy=Array("Content-Type: application/json", "X-KeyLicense: wHSz8BoayhIJQjtP","X-Retrieve-SignedXml:true","X-Environment-Hacienda:prod");
            }


            $ch = curl_init();
            curl_setopt($ch, CURLOPT_HTTPHEADER,$ambiente_easy);
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_TIMEOUT, 400);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($parametrosATV));
            $resposeText = curl_exec($ch);
            $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);
            $respuesta=array("http_status_easy"=>$httpcode,"response"=>json_decode($resposeText));
            return $respuesta;
        }
        catch (\Exception $exception)
        {
            return $exception->getMessage();
        }

    }
    public function checkAllReceiptsATV(Request $request)
    {
        $emisor=null;
        $fecha=date(DATE_RFC3339);
        $api_key=$request->header('X-Api-Key');
        $url="";
        if(!isset($api_key) OR empty($api_key))
        {
            return response()->json(array("code"=>"3","data"=>"Requiere que se incluya el API KEY dentro de los parámetros para
poder realizar el proceso.","fecha"=>$fecha), 400);
        }
//        $payload=$request->json()->all();
        $emisor=DB::table('EMISORES')->select('EMISORES.api_key','EMISORES.id','EMISORES.id_tpidentificacion','EMISORES.usuario_atv_test','EMISORES.contrasena_atv_test')->where('EMISORES.api_key','=',$api_key)->first();
        if(!$emisor)
        {
            return response()->json(array("code"=>"4","data"=>"Fallo en el proceso de autentificación por un API KEY incorrecto","X-Api-Key"=>$request->header('X-Api-Key')), 401);
        }
        try{
            //$pathCertificado="cer/".$emisor->certificado_atv_test;
            if($request->header('entorno')=='stag')
            {
                $url=env('EASY_ATV_SERVER_TEST')."/easy-atv/api/atv/comprobantes";
            }
            elseif($request->header('entorno')=='prod')
            {
                $url=env('EASY_ATV_SERVER_PROD')."/easy-atv/api/atv/comprobantes";
            }
            else{
                return response()->json(array("code"=>"26","data"=>"Ambiente incorrecto. Ambientes disponibles:[stag] para pruebas y [prod] para producción","entorno"=>$request->header('entorno')), 401);
            }

            /*$body=array("atvUsername"=>$emisor->usuario_atv_test,
                "atvPassword"=>$emisor->contrasena_atv_test);*/
            $body=array("atvUsername"=>$emisor->usuario_atv_test,
                "atvPassword"=>$emisor->contrasena_atv_test,"emisor"=>$emisor->id_tpidentificacion.$emisor->id);
            /*print_r($body);
            exit;*/
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_HTTPHEADER, Array("Content-Type: application/json","X-KeyLicense: wHSz8BoayhIJQjtP","X-Retrieve-Xml:true"));
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
            curl_setopt($ch, CURLOPT_TIMEOUT, 30);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($body));
            $resposeText = curl_exec($ch);
            curl_close($ch);
            $resposeText=json_decode($resposeText);
            if(!isset($resposeText))
            {
                return response()->json(array("code"=>"0","data"=>"Error al comunicarse con hacienda","fecha"=>$fecha), 500);
            }
            return response()->json(array("code"=>"1","data"=>$resposeText,"fecha"=>$fecha), 200);

        }
        catch (\Exception $exception)
        {
            return $exception->getMessage();
        }
    }
    public function checkreceiptATV(Request $request)
    {
        $emisor=null;
        $fecha=date(DATE_RFC3339);
        $api_key=$request->header('X-Api-Key');
        $entorno=$request->header('entorno');
        if(!isset($api_key) OR empty($api_key))
        {
            return response()->json(array("code"=>"3","data"=>"Requiere que se incluya el API KEY dentro de los parámetros para
poder realizar el proceso.","fecha"=>$fecha), 400);
        }
        if(!isset($entorno) OR empty($entorno))
        {
            return response()->json(array("code"=>"10","data"=>"Requiere que se incluya el entorno dentro de los parámetros del encabezado de la solicitud.","fecha"=>$fecha), 400);
        }
        $payload=$request->json()->all();
        if($entorno=='stag')
        {
            $emisor=DB::table('EMISORES')->select('EMISORES.api_key','EMISORES.usuario_atv_test','EMISORES.contrasena_atv_test','EMISORES.certificado_atv_test','EMISORES.pin_atv_test','EMISORES.razon_social','EMISORES.nombre_comercial','EMISORES.SMTP_OP','EMISORES.PDF')->where('EMISORES.api_key','=',$api_key)->first();
        }
        elseif($entorno=='prod')
        {
            $emisor=DB::table('EMISORES')->select('EMISORES.api_key','EMISORES.usuario_atv_prod','EMISORES.contrasena_atv_prod','EMISORES.certificado_atv_prod','EMISORES.pin_atv_prod','EMISORES.razon_social','EMISORES.nombre_comercial','EMISORES.SMTP_OP','EMISORES.PDF')->where('EMISORES.api_key','=',$api_key)->first();
        }

        if(!$emisor)
        {
            return response()->json(array("code"=>"4","data"=>"Fallo en el proceso de autentificación por un API KEY incorrecto","X-Api-Key"=>$request->header('X-Api-Key')), 401);
        }
        try{
            //$pathCertificado="cer/".$emisor->certificado_atv_test;
            $url="";
            $ambiente_easy="";

            if($entorno=='stag')
            {
                $url=env('EASY_ATV_SERVER_TEST')."/easy-atv/api/atv/recepcion/".$payload['clave'];
                $body=array("atvUsername"=>$emisor->usuario_atv_test,
                    "atvPassword"=>$emisor->contrasena_atv_test);
                $ambiente_easy=Array("Content-Type: application/json","X-KeyLicense: wHSz8BoayhIJQjtP","X-Retrieve-Xml:true","X-Environment-Hacienda:test");
            }
            elseif($entorno=='prod')
            {
                $url=env('EASY_ATV_SERVER_PROD')."/easy-atv/api/atv/recepcion/".$payload['clave'];
                $body=array("atvUsername"=>$emisor->usuario_atv_prod,
                    "atvPassword"=>$emisor->contrasena_atv_prod);
                $ambiente_easy=Array("Content-Type: application/json","X-KeyLicense: wHSz8BoayhIJQjtP","X-Retrieve-Xml:true","X-Environment-Hacienda:prod");
            }
            else{
                return response()->json(array("code"=>"26","data"=>"Ambiente incorrecto. Ambientes disponibles:[stag] para pruebas y [prod] para producción","entorno"=>$request->header('entorno')), 401);
            }

            $ch = curl_init();
            curl_setopt($ch, CURLOPT_HTTPHEADER, $ambiente_easy);
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
            curl_setopt($ch, CURLOPT_TIMEOUT, 30);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($body));
            $resposeText = curl_exec($ch);
            curl_close($ch);
            $resposeText=json_decode($resposeText);
            if(!isset($resposeText))
            {
                return response()->json(array("code"=>"0","data"=>"Error al comunicarse con hacienda","fecha"=>$fecha), 500);
            }
            if(strpos($payload['clave'],'-'))
            {
                try{
                    if(isset($resposeText->estado))
                    {
                        DB::table('GASTOS')->where('clave_gasto','=',$payload['clave'])->update(['estado' => $resposeText->estado,'xml_result'=>$resposeText->resultXml]);
                    }
                    else{
                        return response()->json(array("code"=>"0","msj"=>"El estado de la respuesta no existe","data"=>$resposeText,"fecha"=>$fecha), 200);
                    }
                    return response()->json(array("code"=>"1","data"=>$resposeText,"fecha"=>$fecha), 200);
                }
                catch (Exception $exception)
                {
                    return "Error al actualizar consecutivo de GA:".$exception->getMessage();
                }

            }
            try{
                if(isset($resposeText->estado) AND $resposeText->estado!='')
                {
                    $resultXml=$resposeText->resultXml;
                    DB::table('COMPROBANTES')->where('clave','=',$payload['clave'])->update(['estado' => $resposeText->estado,'xml_result'=>$resultXml]);
                }
                else{
                    return response()->json(array("code"=>"0","msj"=>"Estado ATV no disponible","data"=>$resposeText,"fecha"=>$fecha), 200);
                }
                $comprobante=DB::table('COMPROBANTES')->select('COMPROBANTES.xml_firmado','COMPROBANTES.nombre_receptor','COMPROBANTES.email_receptor','COMPROBANTES.tp_comprobante','COMPROBANTES.numeracion','COMPROBANTES.cc')->where('clave','=',$payload['clave'])->first();
                /*if(isset($resposeText->estado) AND $resposeText->estado==="aceptado" AND isset($comprobante->email_receptor) AND $comprobante->email_receptor!='')
                {
                    $email = new Email(base64_decode($comprobante->xml_firmado), base64_decode($resposeText->resultXml), $payload['clave'], $comprobante->email_receptor, $comprobante->nombre_receptor, (int)str_replace('0', '', $comprobante->tp_comprobante), $emisor->razon_social, $comprobante->numeracion, $emisor->nombre_comercial);
                    $this->sendEmail($email,$emisor->SMTP_OP,$api_key,$emisor->PDF,$comprobante->cc);
                }*/
                return response()->json(array("code"=>"1","data"=>$resposeText,"fecha"=>$fecha), 200);
            }
            catch (\Exception $exception)
            {
                return $exception->getMessage();
            }

        }
        catch (\Exception $exception)
        {
            return $exception->getMessage();
        }

    }

    public function updateConsecutive($emisor,$tipoComprobante,$entorno,$numConsecutivo)
    {
        if($entorno=='stag') {
            if ($tipoComprobante == "01") {
                //return DB::table('EMISORES')->where('id',$emisor->id)->update(['consecutivoFEtest'=>$emisor->consecutivoFEtest++]);
                try {
                    $e = Emisor::find($emisor->id);
                    $e->consecutivoFEtest=$numConsecutivo;
                    return $e->save();
                } catch (\Exception $exception) {
                    return $exception->getMessage();
                }
            } elseif ($tipoComprobante == "02") {
                try {
                    $e = Emisor::find($emisor->id);
                    $e->consecutivoNDtest=$numConsecutivo;
                    return $e->save();
                } catch (\Exception $exception) {
                    return $exception->getMessage();
                }
            } elseif ($tipoComprobante == "03") {
                try {
                    $e = Emisor::find($emisor->id);
                    $e->consecutivoNCtest=$numConsecutivo;
                    return $e->save();
                } catch (\Exception $exception) {
                    return $exception->getMessage();
                }
            } elseif ($tipoComprobante == "04") {
                try {
                    $e = Emisor::find($emisor->id);
                    $e->consecutivoTEtest=$numConsecutivo;
                    return $e->save();
                } catch (\Exception $exception) {
                    return $exception->getMessage();
                }
            }
            elseif ($tipoComprobante == "08") {
                try {
                    $e = Emisor::find($emisor->id);
                    $e->consecutivoFECtest=$numConsecutivo;
                    return $e->save();
                } catch (\Exception $exception) {
                    return $exception->getMessage();
                }
            }
            elseif ($tipoComprobante == "09") {
                try {
                    $e = Emisor::find($emisor->id);
                    $e->consecutivoFEEtest=$numConsecutivo;
                    return $e->save();
                } catch (\Exception $exception) {
                    return $exception->getMessage();
                }
            }
        }
        elseif($entorno=='prod')
        {
            if ($tipoComprobante == "01") {
                //return DB::table('EMISORES')->where('id',$emisor->id)->update(['consecutivoFEtest'=>$emisor->consecutivoFEtest++]);
                try {
                    $e = Emisor::find($emisor->id);
                    $e->consecutivoFEprod=$numConsecutivo;
                    return $e->save();
                } catch (\Exception $exception) {
                    return $exception->getMessage();
                }
            } elseif ($tipoComprobante == "02") {
                try {
                    $e = Emisor::find($emisor->id);
                    $e->consecutivoNDprod=$numConsecutivo;
                    return $e->save();
                } catch (\Exception $exception) {
                    return $exception->getMessage();
                }
            } elseif ($tipoComprobante == "03") {
                try {
                    $e = Emisor::find($emisor->id);
                    $e->consecutivoNCprod=$numConsecutivo;
                    return $e->save();
                } catch (\Exception $exception) {
                    return $exception->getMessage();
                }
            } elseif ($tipoComprobante == "04") {
                try {
                    $e = Emisor::find($emisor->id);
                    $e->consecutivoTEprod=$numConsecutivo;
                    return $e->save();
                } catch (\Exception $exception) {
                    return $exception->getMessage();
                }
            }
            elseif ($tipoComprobante == "08") {
                try {
                    $e = Emisor::find($emisor->id);
                    $e->consecutivoFECprod=$numConsecutivo;
                    return $e->save();
                } catch (\Exception $exception) {
                    return $exception->getMessage();
                }
            }
            elseif ($tipoComprobante == "09") {
                try {
                    $e = Emisor::find($emisor->id);
                    $e->consecutivoFEEprod=$numConsecutivo;
                    return $e->save();
                } catch (\Exception $exception) {
                    return $exception->getMessage();
                }
            }
        }

    }
    public function downloadPdfInvoice(Request $request)
    {

        $clave=$request->get('clave');
        if($clave=='' or is_null($clave))
        {
            return response()->json(array("code"=>"0","data"=>"La [clave] del comprobante electrónico es requerida"), 400);
        }

        $xmlFirmado=DB::table('COMPROBANTES')->select('COMPROBANTES.xml_firmado')->where('COMPROBANTES.clave','=',$clave)->first()->xml_firmado;
        if(!$xmlFirmado or $xmlFirmado=='')
        {
            return "El comprobante electrónico [".$clave."] en formato PDF no se encuetra disponible.";
        }
        $url=$this->makeInvoice($clave);
        return \response()->download($url)->deleteFileAfterSend(true);
    }


}
