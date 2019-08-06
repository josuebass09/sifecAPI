<?php
$comprobante="";
$moneda="CRC";
$tipo_cambio="1";
$tipoComprobante = substr($data->Clave, 29, 2);
if ($tipoComprobante == "01") {
    $comprobante="Factura Electrónica";
} elseif ($tipoComprobante == "02") {
    $comprobante="Nota de Débito Electrónica";
} elseif ($tipoComprobante == "03") {
    $comprobante="Nota de Crédito Electrónica";
} elseif ($tipoComprobante == "04") {
    $comprobante="Tiquete Electrónico";
}
elseif ($tipoComprobante == "08") {
    $comprobante="Factura Electrónica de Compra";
}
$nombre_comercial="";
if(!empty((array)$data->Emisor->NombreComercial))
{
    $nombre_comercial=$data->Emisor->NombreComercial;
}
function getFechaVencimiento($plazoCredito)
{
    $date = strtotime("+ ".$plazoCredito." days");
    return  date('d/m/Y', $date);
}
function getCondicionVenta($codigoCondVenta)
{
    if($codigoCondVenta=='01')
    {
        return "Contado";
    }elseif($codigoCondVenta=='02')
    {
        return "Crédito";
    }
    elseif($codigoCondVenta=='03')
    {
        return "Consignación";
    }
    elseif($codigoCondVenta=='04')
    {
        return "Apartado";
    }
    elseif($codigoCondVenta=='05')
    {
        return "Arrendamiento con opción de compra";
    }
    elseif($codigoCondVenta=='06')
    {
        return "Arrendamiento en función financiera";
    }
}
function getFormaPago($medioPago)
{
    if($medioPago=='01')
    {
        return "Efectivo";
    }elseif($medioPago=='02')
    {
        return "Tarjeta";
    }
    elseif($medioPago=='03')
    {
        return "Cheque";
    }
    elseif($medioPago=='04')
    {
        return "Transferencia - depósito bancario";
    }
    elseif($medioPago=='05')
    {
        return "Recaudado por terceros";
    }

}
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>{{$comprobante."-".$data->Clave}}</title>

    <style type="text/css">
        * {
            font-family: Time New Roman, Arial, sans-serif;
        }
        table{
            font-size: x-small;
        }
        tfoot tr td{
            font-weight: bold;
            font-size: x-small;
        }
        .gray {
            background-color: lightgray
        }
        #tabla_detalle {
            border: 1px solid red;
            border-collapse: collapse;
        }

        #tabla_detalle td {
            border: 1px;
        }

        #tabla_detalle {
            width: 100%;
            border-collapse: collapse;
            border-spacing: 0;

        }

        #tabla_detalle tr:nth-child(2n-1) td {
            background: #e9e9e9;
        }

        #tabla_detalle th,
        #tabla_detalle td {
            text-align: center;

        }

        #tabla_detalle th {
            /*padding: 5px 5px;*/
            color: #000;
            border-bottom: 1px solid black;
            white-space: nowrap;
            font-size: 12px;
            font-weight: bold;

        }
        #tabla_resumen th {
            /*padding: 5px 5px;*/
            color: #000;
            border-bottom: 0.5px solid #000;
            white-space: nowrap;
            font-size: 12px;
            font-weight: bold;

        }

        #tabla_resumen tr:nth-child(2n-1) th {
            background: #e9e9e9;
        }
        
        #tabla_resumen td {
            border: 1px;
        }

        #tabla_resumen {
            width: 50%;
            border-collapse: inherit;
            border-spacing: 0;

            border-left: 1px solid black;
            border-top: 1px solid black;
            border-bottom: 1px solid black;
            border-right: 1px solid black;

        }

        .div_resumen_obs{
            border: 2px solid black;
        }
    </style>

</head>
<body>
<?php if($data->logo!=''){?>
<div id="logo">
    <img src="{{'img/'.$data->logo}}">
</div>
<?php }?>
<table width="100%">
    <tr>

        <td align="right"> <!--HAY QUE VALIDAR ESTO SEGÚN EL TIPO DE DOCUMENTO QUE SE EMITIÓ-->
            <h1>{{$comprobante}}</h1>
            <p style="font-size: 12px;"><strong>Clave: </strong><span style="font-weight: normal;">{{$data->Clave}}</span></p>
            <p style="font-size: 12px;"><strong>Consecutivo: </strong><span style="font-weight: normal;">{{$data->NumeroConsecutivo}}</span></p>
        </td>
    </tr>

</table>
<table width="100%">
    <tr>
        <td style="width: 75%; color: #000000;font-size:12px;text-align:center">
            <strong style="font-size: 12px;">Razón Social: </strong><span style="color: #000000;font-size:12px;font-weight:bold;text-decoration: underline;"><?php echo $data->Emisor->Nombre;?></span>
            <br><br><strong style="font-size: 12px;">Nombre Comercial: </strong><span style="color: #000000;font-size:12px;font-weight:bold;text-decoration: underline;"><?php echo $nombre_comercial;?></span><br><br>
            <span style="font-size:12px;"><strong>Identificación: </strong><?php echo $data->Emisor->Identificacion->Numero?></span><br><br>
            <span style="font-size:12px;"><strong>Teléfono: </strong><?php if(isset($data->Emisor->Telefono->NumTelefono) AND $data->Emisor->Telefono->NumTelefono!=''){ echo $data->Emisor->Telefono->NumTelefono;}?></span><span style="font-size: 14px;"><strong> Correo: </strong><span><?php echo $data->Emisor->CorreoElectronico?></span></span><br><br>

            <span style="font-size:12px;"><strong>Dirección: </strong><?php echo $data->Emisor->Ubicacion->OtrasSenas?></span><br>

        </td>
    </tr>

</table>

<br/>
<table width="100%">
<tr>
    <td style=" color: #000000;font-size:12px;">
        <span style="font-size: 12px;"><strong>Cliente: </strong><span> <?php echo $data->Receptor->Nombre; ?></span></span>
    </td>

    <td style="width: 75%; color: #000000;font-size:14px;text-align:right;">
        <span style="font-size: 12px;text-align: right;"><strong>Fecha: </strong><span><?php $date = date('d/m/Y h:i:s A', strtotime($data->FechaEmision)); echo $date?> </span></span>
    </td>
</tr>

<tr>

    <td style="width: 75%; color: #000000;font-size:14px;text-align:left;line-height: 30px;">
        <span style="font-size: 12px;"><strong>Identificación: </strong><span><?php echo $data->Receptor->Identificacion->Numero;?> </span></span>
    </td>
    <td style="width: 75%; color: #000000;font-size:14px;text-align:right;line-height: 30px;">
        <span style="font-size: 12px;"><strong>Tipo de Factura: </strong><span><?php echo getCondicionVenta($data->CondicionVenta); ?> </span></span>
    </td>
</tr>

<tr style="margin-top: 0px;">

    <td style="width: 75%; color: #000000;font-size:14px;text-align:left;line-height: 20px;">
        <span style="font-size: 12px;"><strong>Correo Electrónico: </strong><span> <?php echo $data->Receptor->CorreoElectronico?></span></span>
    </td>
    <td style="width: 75%; color: #000000;font-size:14px;text-align:right;line-height: 20px;">
        <?php if(isset($data->PlazoCredito) AND $data->PlazoCredito!='0' AND $data->PlazoCredito!='')
        {?>
        <span style="font-size: 12px;"><strong>Fecha de Vencimiento:</strong><span> <?php echo getFechaVencimiento($data->PlazoCredito,$date);?></span></span><?php }
        else{
        ?>
        <span style="font-size: 12px;"><strong>Fecha de Vencimiento:</strong><span> No aplica</span></span> <?php }?>

    </td>
</tr>

<tr style="margin-top: 0px;">

    <td style="width: 75%; color: #000000;font-size:12px;text-align:left;line-height: 25px;">
        <span style="font-size: 12px;"><strong>Teléfono: </strong><span><?php if(isset($data->Receptor->Telefono->NumTelefono) AND $data->Receptor->Telefono->NumTelefono!=''){ echo $data->Receptor->Telefono->NumTelefono;} ?> </span></span>
    </td>
    <td style="width: 75%; color: #000000;font-size:14px;text-align:right;line-height: 25px;">
        <span style="font-size: 12px;"><strong>Forma de Pago:</strong><span> <?php echo getFormaPago($data->MedioPago);?></span></span>
    </td>
</tr>

<tr style="margin-top: 0px;">
    <td style="width: 75%; color: #000000;font-size:14px;text-align:left;line-height: 25px;">
        <span style="font-size: 12px;"><strong>Tipo de Cambio: </strong><span><?php if(empty($data->ResumenFactura->CodigoTipoMoneda->TipoCambio)){echo $tipo_cambio;}else{echo $data->ResumenFactura->CodigoTipoMoneda->TipoCambio;}?></span></span>
    </td>

    <td style="width: 50%; color: #000000;font-size:14px;text-align:right;line-height: 25px;">
        <span style="font-size: 12px;"><strong>Moneda: </strong><span><?php if(empty($data->ResumenFactura->CodigoTipoMoneda->TipoCambio)){echo $moneda;}else{echo $data->ResumenFactura->CodigoTipoMoneda->CodigoMoneda;} ?></span></span>
    </td>


</tr>
</table>
<table width="100%" style="margin-top: 15px;margin-bottom: 15px;border-collapse: collapse;border:0.5px solid #000" id="tabla_detalle">

    <thead style="background-color: #f1f1f1;">
    <tr>
        <th align="center" style="padding: 5px;">Artículo</th>
        <th align="center">Precio Unitario</th>
        <th align="center">Cantidad</th>
        <th align="center">Descuento</th>
        <th align="center">Impuesto</th>
        <th align="center">Total</th>
    </tr>
    </thead>
    <tbody>
    <!--<tr>

        <td>Medicina de Empresa y
            Ambulancia</td>
        <td align="right">3300.00000</td>
        <td align="right">1.000</td>
        <td align="right">0.00000</td>
        <td align="right">0.00000</td>
        <td align="right">3300.00000</td>
    </tr>-->
    <?php
    $xml_det=array();
    $xml_res=array();
    $xml_det=(array)$data->DetalleServicio->LineaDetalle;
    $xml_res=(array)$data->ResumenFactura;
    $montoImpuesto="0";
    $montoDescuento="0";
    $tamano=0;
    if(isset($xml_det['Cantidad']))
    {
        $tamano=sizeof((array)$data->DetalleServicio);
    }
    else{
        $tamano=sizeof((array)$data->DetalleServicio->LineaDetalle);
    }


    if($tamano==1)
    {

        if(isset($xml_det['Impuesto']->Monto) AND !empty($xml_det['Impuesto']->Monto)){
            $montoImpuesto=$xml_det['Impuesto']->Monto;}
        if(isset($xml_det['Descuento']->MontoDescuento) AND !empty($xml_det['Descuento']->MontoDescuento)){
            $montoDescuento=$xml_det['Descuento']->MontoDescuento;}
        echo '<tr style="border-bottom: 1px solid #000;"><td align="center" style="width: 25%;"><p>'.$xml_det['Detalle'].'</p></td>
            <td align="center" style="width: 15%;"><p>'.number_format($xml_det['PrecioUnitario'],2,'.',',').'</p></td>
            <td align="center" style="width: 10%;"><p>'.$xml_det['Cantidad'].'</p></td>
            <td align="center" style="width: 15%;"><p>'.number_format($montoDescuento,2,'.',',').'</p></td>
            <td align="center" style="width: 15%;"><p>'.number_format($montoImpuesto,2,'.',',').'</p></td>
            <td align="center" style="width: 20%;"><p>'.number_format($xml_det['MontoTotalLinea'],2,'.',',').'</p></td></tr>';
    }
    else{

        for($c=0;$c<$tamano;$c++){
            if(isset($xml_det[$c]->Descuento->MontoDescuento))
            {
                $montoDescuento=$xml_det[$c]->Descuento->MontoDescuento;
            }
            if(isset($xml_det[$c]->Impuesto->Monto))
            {
                $montoImpuesto=$xml_det[$c]->Impuesto->Monto;
            }
            elseif(isset($xml_det[$c]->Impuesto[0]->Monto))
            {
                for($i=0;$i<sizeof($xml_det[0]->Impuesto);$i++)
                {
                    $montoImpuesto+=$xml_det[$c]->Impuesto[$i]->Monto;
                }
            }

            echo '<tr style="background-color: #f2f2f2;"><td align="center" style="width: 25%;"><p>'.$xml_det[$c]->Detalle.'</p></td>
            <td align="center" style="width: 15%;"><p>'.number_format($xml_det[$c]->PrecioUnitario,2,'.',',').'</p></td>
            <td align="center" style="width: 10%;"><p>'.$xml_det[$c]->Cantidad.'</p></td>
            <td align="center" style="width: 15%;"><p>'.number_format($montoDescuento,2,'.',',').'</p></td>
            <td align="center" style="width: 15%;"><p>'.number_format($montoImpuesto,2,'.',',').'</p></td>
            <td align="center" style="width: 20%;"><p>'.number_format($xml_det[$c]->MontoTotalLinea,2,'.',',').'</p></td></tr>';
            $montoImpuesto="0";
            $montoDescuento="0";
        }


    }

    ?>

    </tbody>


</table>
<table width="50%" style="font-size: 12px;float: left;" id="tabla_observaciones">

                    <tr>
                        <th class="primeraTh">Observaciones</th>

                    </tr>
                    <tr>
                        <td class="primeraTh" style="text-align: left;"><?php if(!empty($data->Otros->OtroTexto)){ echo $data->Otros->OtroTexto;}?></td>
                    </tr>
                </table>

            <table  width="50%" style="font-size: 12px;float: right;" id="tabla_resumen">
                    <tr>
                        <th class="segundaTh">Total Servicios Gravados</th>
                        <th align="right" class="terceraTh"><?php if(!empty($xml_res['TotalServGravados'])){ echo number_format($xml_res['TotalServGravados'],2,'.',',');}else{ echo "0.00";}?></th>
                    </tr>
                    <tr>

                        <th  class="segundaTh">Total Servicios Exentos</th>
                        <th align="right"  class="terceraTh"><?php if(!empty($xml_res['TotalServExentos'])){echo number_format($xml_res['TotalServExentos'],2,'.',',');}else{ echo "0.00";}?></th>
                    </tr>
                <tr>
                    <th  class="segundaTh">Total Servicios Exonerados</th>
                    <th align="right"  class="terceraTh"><?php if(!empty($xml_res['TotalServExonerado'])){echo number_format($xml_res['TotalServExonerado'],2,'.',',');}else{ echo "0.00";}?></th>
                </tr>

                    <tr>
                        <th class="segundaTh">Total Mercancías Gravadas</th>
                        <th align="right" class="terceraTh"><?php if(!empty($xml_res['TotalMercanciasGravadas'])){echo number_format($xml_res['TotalMercanciasGravadas'],2,'.',',');}else{ echo "0.00";}?></th>
                    </tr>
                    <tr>

                        <th class="segundaTh">Total Mercancías Exentas</th>
                        <th align="right" class="terceraTh"><?php if(!empty($xml_res['TotalMercanciasExentas'])){ echo number_format($xml_res['TotalMercanciasExentas'],2,'.',',');}else{ echo "0.00";}?></th>
                    </tr>
                <tr>
                    <th class="segundaTh">Total Mercancías Exoneradas</th>
                    <th align="right" class="terceraTh"><?php if(!empty($xml_res['TotalMercExonerada'])){ echo number_format($xml_res['TotalMercExonerada'],2,'.',',');}else{ echo "0.00";}?></th>
                </tr>
                    <tr>

                        <th class="segundaTh">Total Gravado</th>
                        <th align="right" class="terceraTh"><?php if(!empty($xml_res['TotalGravado'])){echo number_format($xml_res['TotalGravado'],2,'.',',');}else{ echo "0.00";}?></th>
                    </tr>
                    <tr>

                        <th class="segundaTh">Total Exento</th>
                        <th align="right" class="terceraTh"><?php if(!empty($xml_res['TotalExento'])){ echo number_format($xml_res['TotalExento'],2,'.',',');}else{ echo "0.00";}?></th>
                    </tr>
                <tr>
                    <th class="segundaTh">Total Exonerado</th>
                    <th align="right" class="terceraTh"><?php if(!empty($xml_res['TotalExonerado'])){ echo number_format($xml_res['TotalExonerado'],2,'.',',');}else{ echo "0.00";}?></th>
                </tr>
                    <tr>

                        <th class="segundaTh">Total Venta</th>
                        <th align="right" class="terceraTh"><?php if(!empty($xml_res['TotalVenta'])){ echo number_format($xml_res['TotalVenta'],2,'.',',');}else{ echo "0.00";}?></th>
                    </tr>
                    <tr>

                        <th class="segundaTh">Total Descuento</th>
                        <th align="right" class="terceraTh"><?php if(!empty($xml_res['TotalDescuentos'])){ echo number_format($xml_res['TotalDescuentos'],2,'.',',');}else{ echo "0.00";}?></th>
                    </tr>
                    <tr>

                        <th class="segundaTh">Total Venta Neta</th>
                        <th align="right" class="terceraTh"><?php if(!empty($xml_res['TotalVentaNeta'])){echo number_format($xml_res['TotalVentaNeta'],2,'.',',');}else{ echo "0.00";}?></th>
                    </tr>
                    <tr>

                        <th class="segundaTh">Total Impuestos</th>
                        <th align="right" class="terceraTh"><?php if(!empty($xml_res['TotalImpuesto'])){ echo number_format($xml_res['TotalImpuesto'],2,'.',',');}else{ echo "0.00";}?></th>
                    </tr>
                <?php if(isset($xml_res['TotalIVADevuelto']) AND !empty($xml_res['TotalIVADevuelto']))
                    {
                        ?>
                <tr>
                    <th class="segundaTh">Total IVA Devuelto</th>
                    <th align="right" class="terceraTh"><?php if(!empty($xml_res['TotalIVADevuelto'])){ echo number_format($xml_res['TotalIVADevuelto'],2,'.',',');}else{ echo "0.00";}?></th>
                </tr>
                <?php }?>
                <tr>
                    <th class="segundaTh">Total Otros Cargos</th>
                    <th align="right" class="terceraTh"><?php if(!empty($xml_res['TotalIVADevuelto'])){ echo number_format($xml_res['TotalIVADevuelto'],2,'.',',');}else{ echo "0.00";}?></th>
                </tr>
                    <tr>

                        <th class="segundaTh" style="border-bottom: 1px solid #fff;">Total Comprobante</th>
                        <th align="right" class="terceraTh" style="border-bottom: 1px solid #fff;"><?php echo number_format($xml_res['TotalComprobante'],2,'.',',');?></th>
                    </tr>
                </table>
    <br><br><br>

    <div style="text-align: center;font-size: 10px;">
        <span style="font-weight: bold;">"Emitida conforme la resolución de facturación electrónica N° DGT-R-48-2016 del 07-10-2016 de la D.G.T.D."</span>
        <br><span class="ft0">Versión 4.3</span>
    </div>

</body>


</html>