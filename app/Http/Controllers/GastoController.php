<?php

namespace App\Http\Controllers;

use App\Gasto;
use App\Emisor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class GastoController extends Controller
{
    function makeXmlGA(Request $request)
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
            $url=env('EASY_ATV_SERVER_TEST')."/easy-atv/api/atv/recepcion";
            $emisor=DB::table('EMISORES')->select('EMISORES.id','id_tpidentificacion','EMISORES.api_key','EMISORES.usuario_atv_test','EMISORES.contrasena_atv_test','EMISORES.certificado_atv_test','EMISORES.pin_atv_test','EMISORES.consecutivoGAtest')->where('EMISORES.api_key','=',$api_key)->first();
        }
        elseif($entorno=='prod')
        {
            $url=env('EASY_ATV_SERVER_PROD')."/easy-atv/api/atv/recepcion";
            $emisor=DB::table('EMISORES')->select('EMISORES.id','id_tpidentificacion','EMISORES.api_key','EMISORES.usuario_atv_prod','EMISORES.contrasena_atv_prod','EMISORES.certificado_atv_prod','EMISORES.pin_atv_prod','EMISORES.consecutivoGAprod')->where('EMISORES.api_key','=',$api_key)->first();
        }
        else{
            return response()->json(array("code"=>"26","data"=>"Ambiente incorrecto. Ambientes disponibles:[stag] para pruebas y [prod] para producción","entorno"=>$request->header('entorno')), 401);
        }

        if(!$emisor)
        {
            return response()->json(array("code"=>"4","data"=>"Fallo en el proceso de autentificación por un API KEY incorrecto","api_key"=>$request->header('api_key')), 401);
        }
        $rules = [
            'codigo_actividad'=>['max:6'],
            'emisor' => ['required'],
            'emisor.numero' => ['required'],
            'emisor.tipo' => ['required'],
            'mensaje' => ['required','numeric','regex:/^[1-3]+$/u'],
            'total_factura'=>['required'],
            'num_consecutivo_receptor'=>['required']
        ];
        $customMessages = [
            'emisor.required' => 'El nodo [emisor] es requerido *10',
            'emisor.numero.required' => 'El nodo [emisor][numero] es requerido *10',
            'emisor.tipo.required' => 'El nodo [emisor][tipo] es requerido *10',
            'mensaje.required' => 'El nodo [mensaje] es requerido *10',
            'total_factura.required'=>'El nodo [total_factura] es requerido *10',
            'num_consecutivo_receptor.required'=>'El nodo [num_consecutivo_receptor] es requerido *10',
            'mensaje.numeric' => 'El nodo [mensaje] sólo permite valores del 1 al 3. *41',
            'mensaje.regex' => 'El nodo [mensaje] sólo permite valores del 1 al 3. *41',
            'codigo_actividad.max'=>'El formato del [codigo_actividad] es incorrecto *20'

        ];

        $validaciones = Validator::make($payload, $rules,$customMessages);
        if($validaciones->fails())
        {
            $mensaje=$validaciones->errors()->first();
            $code=$this->getCode($mensaje);
            $data=$this->eraseCodeIntoMessage($mensaje,$code);
            return response()->json(array("code"=>$code,"data"=>$data,"fecha"=>$fecha,"body"=>$payload), 400);
        }
        if(!isset($payload['clave']) OR empty($payload['clave']))
        {
            return response()->json(array("code"=>"10","data"=>"Datos incompletos en la solicitud. La [clave] del comprobante es requerida","body"=>$payload,"fecha"=>$fecha), 400);
        }


        $fecha=date(DATE_RFC3339);
        $cedula_emisor=$payload['emisor']['numero'];
        $cedula_receptor=$emisor->id;
        if(strlen($payload['emisor']['numero'])==9)
        {
            $cedula_emisor="000".$payload['emisor']['numero'];
        }
        elseif(strlen($payload['emisor']['numero'])==10)
        {
            $cedula_emisor="00".$payload['emisor']['numero'];
        }
        elseif(strlen($payload['emisor']['numero'])==11)
        {
            $cedula_emisor="0".$payload['emisor']['numero'];
        }

        if(strlen($emisor->id)==9)
        {
            $cedula_receptor="000".$emisor->id;
        }
        elseif(strlen($emisor->id)==10)
        {
            $cedula_receptor="00".$emisor->id;
        }
        elseif(strlen($emisor->id)==11)
        {
            $cedula_receptor="0".$emisor->id;
        }

        //$payload['receptor']['numero']

        $xmlString = '<?xml version="1.0" encoding="utf-8"?>
    <MensajeReceptor xmlns:xsd="http://www.w3.org/2001/XMLSchema" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns="https://cdn.comprobanteselectronicos.go.cr/xml-schemas/v4.3/mensajeReceptor">
    <Clave>' . $payload['clave'] . '</Clave>
    <NumeroCedulaEmisor>' . $cedula_emisor. '</NumeroCedulaEmisor>
    <FechaEmisionDoc>' . $fecha . '</FechaEmisionDoc>
    <Mensaje>' . $payload['mensaje'] . '</Mensaje>';
        if (!empty($payload['detalle_mensaje'])) {
            $xmlString .= '<DetalleMensaje>' . $payload['detalle_mensaje'] . '</DetalleMensaje>';
        }
        if (!empty($payload['monto_total_impuesto'])) {
            $xmlString .= '<MontoTotalImpuesto>' . $payload['monto_total_impuesto'] . '</MontoTotalImpuesto>';
        }
        if (!empty($payload['codigo_actividad'])) {
            $xmlString .='<CodigoActividad>'.$payload['codigo_actividad'].'</CodigoActividad>';
        }
        if(!empty($payload['condicion_impuesto']))
        {
            $xmlString .= '<CondicionImpuesto>' . $payload['condicion_impuesto'] . '</CondicionImpuesto>';
        }
        if(!empty($payload['monto_total_imp_acred']))
        {
            $xmlString .= '<MontoTotalImpuestoAcreditar>' . $payload['monto_total_imp_acred'] . '</MontoTotalImpuestoAcreditar>';
        }
        if(!empty($payload['monto_total_gast_aplic']))
        {
            $xmlString .= '<MontoTotalDeGastoAplicable>' . $payload['monto_total_gast_aplic'] . '</MontoTotalDeGastoAplicable>';
        }




        $xmlString .= '<TotalFactura>' . $payload['total_factura'] . '</TotalFactura>
    <NumeroCedulaReceptor>' . $cedula_receptor . '</NumeroCedulaReceptor>
    <NumeroConsecutivoReceptor>' . $payload['num_consecutivo_receptor'] . '</NumeroConsecutivoReceptor>';

        $xmlString .= '</MensajeReceptor>';
        $xml64=base64_encode($xmlString);
        $ambiente_easy="";
        $body="";
        if($entorno=='stag')
        {
            $body=array("atvUsername"=>$emisor->usuario_atv_test,
                "atvPassword"=>$emisor->contrasena_atv_test,
                "certificateLocation"=>"cer_sandbox/".$emisor->certificado_atv_test,
                "certificatePassword"=>$emisor->pin_atv_test,
                "transmitterIdType"=>$payload['emisor']['tipo'],
                "receiverIdType"=>$emisor->id_tpidentificacion,
                "xmlToSign"=>$xml64);
            $ambiente_easy=Array("Content-Type: application/json","X-KeyLicense: wHSz8BoayhIJQjtP","X-Retrieve-SignedXml:true","X-Environment-Hacienda:test");

        }
        elseif($entorno=='prod')
        {
            $body=array("atvUsername"=>$emisor->usuario_atv_prod,
                "atvPassword"=>$emisor->contrasena_atv_prod,
                "certificateLocation"=>"cer/".$emisor->certificado_atv_prod,
                "certificatePassword"=>$emisor->pin_atv_prod,
                "transmitterIdType"=>$payload['emisor']['tipo'],
                "receiverIdType"=>$emisor->id_tpidentificacion,
                "xmlToSign"=>$xml64);
            $ambiente_easy=Array("Content-Type: application/json","X-KeyLicense: wHSz8BoayhIJQjtP","X-Retrieve-SignedXml:true","X-Environment-Hacienda:prod");
        }

        $xmlCompleto="";
        $location="";
        $estado_comprobante="";
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_HTTPHEADER,$ambiente_easy);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
        curl_setopt($ch, CURLOPT_TIMEOUT, 50);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($body));
        $resposeText = curl_exec($ch);
        $respuesta=json_decode($resposeText);

        if(isset($respuesta->error))
        {
            return response()->json(array("code"=>"0","data"=>$respuesta->message), 400);
        }
        if(isset($respuesta->signedXml))
        {
            $xmlCompleto=$respuesta->signedXml;
        }
        if(isset($respuesta->location))
        {
            $location=$respuesta->location;
        }
        if(isset($respuesta->estado))
        {
            $estado_comprobante=$respuesta->estado;
        }


        /*$arrayResp = array(
            "clave" => $payload['clave'],
            "xml"   => base64_encode($xmlString)
        );

        return $arrayResp;*/
        try{
            $gasto=new Gasto();
            $gasto->clave_comprobante=$payload['clave'];
            $gasto->clave_gasto=$payload['clave']."-".$payload['num_consecutivo_receptor'];
            $gasto->consecutivo_recepcion=$payload['num_consecutivo_receptor'];
            $gasto->tp_emisor=$payload['emisor']['tipo'];
            $gasto->identificacion_emisor=$payload['emisor']['numero'];
            $gasto->fecha_gasto=null;
            $gasto->mensaje=$payload['mensaje'];
            $gasto->detalle=$payload['detalle_mensaje'];
            $gasto->total_impuestos=$payload['monto_total_impuesto'];
            $gasto->total_comprobante=$payload['total_factura'];
            $gasto->tp_receptor=$payload['receptor']['tipo'];
            $gasto->id_receptor=$payload['receptor']['numero'];
            $gasto->xml_completo=$xmlCompleto;
            $gasto->xml_result="";
            $gasto->estado=$estado_comprobante;
            $gasto->respuesta_api=1;
            $gasto->http_hacienda=null;
            $gasto->location=$location;
            $gasto->save();
            $this->updateConsecutivo($entorno,$emisor->id);
            return response()->json(array("code"=>"1","data"=>json_decode($resposeText),"fecha"=>$fecha), 200);
        }
        catch(Exception $exception)
        {
            return "Error: ".$exception->getMessage();
        }
    }
    public function getCode($error)
    {
        return substr($error, strpos($error, "*") + 1);
    }
    public function eraseCodeIntoMessage($message,$toErase)
    {
        return str_replace(" *".$toErase,'',$message);
    }
    public function updateConsecutivo($entorno,$id_emisor)
    {
        if($entorno=='stag') {
            try {
                $e = Emisor::find($id_emisor);
                $e->consecutivoGAtest++;
                return $e->save();
            } catch (\Exception $exception) {
                return $exception->getMessage();
            }

        }
        elseif($entorno=='prod')
        {
            try {
                $e = Emisor::find($id_emisor);
                $e->consecutivoGAprod++;
                return $e->save();
            } catch (\Exception $exception) {
                return $exception->getMessage();
            }
        }
    }
    public function getConsecutivoGA(Request $request)
    {
        $emisor=null;
        $api_key=$request->header('X-Api-Key');
        $entorno=$request->header('entorno');

        if(!isset($api_key) OR empty($api_key))
        {
            return response()->json(array("code"=>"3","data"=>"Requiere que se incluya el API KEY dentro de los parámetros para
poder realizar el proceso."), 400);
        }
        if(!isset($entorno) OR empty($entorno))
        {
            return response()->json(array("code"=>"10","data"=>"Requiere que se incluya el entorno dentro de los parámetros del encabezado de la solicitud."), 400);
        }
        $consecutivoGA=0;

        if($entorno=='stag')
        {
            $emisor=DB::table('EMISORES')->select('EMISORES.id','EMISORES.consecutivoGAtest')->where('EMISORES.api_key','=',$api_key)->first();
            $consecutivoGA=$emisor->consecutivoGAtest;
        }
        elseif($entorno=='prod')
        {
            $emisor=DB::table('EMISORES')->select('EMISORES.id','EMISORES.consecutivoGAprod')->where('EMISORES.api_key','=',$api_key)->first();
            $consecutivoGA=$emisor->consecutivoGAprod;

        }
        else{
            return response()->json(array("code"=>"26","data"=>"Ambiente incorrecto. Ambientes disponibles:[stag] para pruebas y [prod] para producción","entorno"=>$request->header('entorno')), 401);
        }
        if(!$emisor)
        {
            return response()->json(array("code"=>"4","data"=>"Fallo en el proceso de autentificación por un API KEY incorrecto","api_key"=>$request->header('api_key')), 401);
        }
        $consecutivoGA++;
        try{
            $this->updateConsecutivo($entorno,$emisor->id);
        }
        catch(\Exception $exception)
        {
            return response()->json(array("code"=>"0","msj"=>"Error al actualizar el consecutivo","data"=>$exception->getMessage()), 500);
        }

        return response()->json(array("code"=>"1","data"=>$consecutivoGA), 200);
    }

}
