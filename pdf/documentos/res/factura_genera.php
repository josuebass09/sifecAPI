<?php
use App\Comprobante;
    $clave=Session::get('clave');
    $comprobante=DB::table('COMPROBANTES')->select('COMPROBANTES.xml_firmado')->where('clave','=',$clave)->first();
    $cargaXml = simplexml_load_string(base64_decode($comprobante->xml_firmado));
    $toJson=json_encode($cargaXml);
    $xml=json_decode($toJson);
    $nombre_comercial="";
    if(!empty((array)$xml->Emisor->NombreComercial))
    {
        $nombre_comercial=$xml->Emisor->NombreComercial;
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
function getFechaVencimiento($plazoCredito,$fechaActual)
{

    return date('Y-m-d', strtotime($fechaActual. ' + 1 days'));

}
    ?>
<style type="text/css">
<!--
table { vertical-align: top; position:relative;}
tr    { vertical-align: top; }
td    { vertical-align: top; }
.midnight-blue{
	background:#2c3e50;
	padding: 4px 4px 4px;
	color:white;
	font-weight:bold;
	font-size:12px;
}
.silver{
	background:white;
	padding: 3px 4px 3px;
}
.clouds{
	background:#ecf0f1;
	padding: 3px 4px 3px;
}
.border-top{
	border-top: solid 1px #bdc3c7;

}
.border-left{
	border-left: solid 1px #bdc3c7;
}
.border-right{
	border-right: solid 1px #bdc3c7;
}
.border-bottom{
	border-bottom: solid 1px #bdc3c7;
}
#tabla_resumen th{
    padding: 1px;

    border: 0.5px white;
}



table.page_footer {width: 100%; border: none; background-color: white; padding: 2mm;border-collapse:collapse; border: none;}


    .primeraTh{
        width: 50%;border-right: 1px solid #000000;border-bottom: 1px solid #000000
    }
    .segundaTh{
        width: 30%;border-right: 1px solid #000000;border-bottom: 1px solid #000000;height: 10px;
    }
    .terceraTh{
        width: 20%;text-align: right;border-bottom: 1px solid #000000
    }

div table {
    display: inline-block;

}

  #tabla_detalle tr th{
      padding-bottom:10px;

  }
</style>
<page backtop="10mm" backbottom="10mm" backleft="3mm" backright="3mm" style="font-size: 12pt; font-family: arial;padding:0;
    margin:0;" >

        <table align="right">
            <tr>
                <td style="width: 100%; text-align: right">
                    <?php
                    $comprobante="";
                    $tipoComprobante = substr($xml->Clave, 29, 2);
                    if ($tipoComprobante == "01") {
                        $comprobante="Factura Electrónica";
                    } elseif ($tipoComprobante == "02") {
                        $comprobante="Nota de Débito Electrónica";
                    } elseif ($tipoComprobante == "03") {
                        $comprobante="Nota de Crédito Electrónica";
                    } elseif ($tipoComprobante == "04") {
                        $comprobante="Tiquete Electrónico";
                    }
                    echo "<h2>$comprobante</h2>";?>
                </td>
            </tr>
            <tr>
                <td style="width: 100%; text-align: right">
                    <?php echo "<strong>Clave:</strong> $xml->Clave";?>
                </td>
            </tr>
            <tr>
                <td style="text-align: right;width: 100%">
                    <?php echo "<strong>Consecutivo:</strong> $xml->NumeroConsecutivo"?>
                </td>
            </tr>
        </table>

    <table cellspacing="75" style="width: 100%;" align="center">
        <tr>


            <br><br><br><br>
			<td style="width: 75%; color: #000000;font-size:12px;text-align:center">
                <strong style="font-size: 14px;">Razón Social: </strong><span style="color: #000000;font-size:14px;font-weight:bold;text-decoration: underline;"><?php echo $xml->Emisor->Nombre;?></span>
				<br><br><strong style="font-size: 14px;">Nombre Comercial: </strong><span style="color: #000000;font-size:14px;font-weight:bold;text-decoration: underline;"><?php echo $nombre_comercial;?></span><br><br>
                <span style="font-size:14px;"><strong>Identificación: </strong><?php echo $xml->Emisor->Identificacion->Numero?></span><br><br>
                <span style="font-size:14px;"><strong>Teléfono: </strong><?php echo $xml->Emisor->Telefono->NumTelefono?></span><span style="font-size: 14px;"><strong> Correo: </strong><span><?php echo $xml->Emisor->CorreoElectronico?></span></span><br><br>

			<span style="font-size:14px;"><strong>Dirección:</strong><?php echo $xml->Emisor->Ubicacion->OtrasSenas?></span><br>

            </td>


        </tr>


    </table>
<div>


    <table cellspacing="0" width="528">

        <tr>
            <td style=" color: #000000;font-size:12px;">
                <span style="font-size: 14px;"><strong>Cliente: </strong><span> <?php echo $xml->Receptor->Nombre; ?></span></span>
            </td>

            <td style="width: 75%; color: #000000;font-size:14px;text-align:right;line-height: 30px;">
                <span style="font-size: 14px;text-align: right;"><strong>Fecha: </strong><span><?php $date = date('d/m/Y', strtotime($xml->FechaEmision)); echo $date?> </span></span>
            </td>





        </tr>

        <tr style="margin-top: 0px;">

            <td style="width: 75%; color: #000000;font-size:14px;text-align:left;line-height: 30px;">
                <span style="font-size: 14px;"><strong>Identificación: </strong><span><?php echo $xml->Receptor->Identificacion->Numero;?> </span></span>
            </td>
            <td style="width: 75%; color: #000000;font-size:14px;text-align:right;line-height: 30px;">
                <span style="font-size: 14px;"><strong>Tipo de Factura: </strong><span><?php echo getCondicionVenta($xml->CondicionVenta); ?> </span></span>
            </td>
        </tr>

        <tr style="margin-top: 0px;">

            <td style="width: 75%; color: #000000;font-size:14px;text-align:left;line-height: 20px;">
                <span style="font-size: 14px;"><strong>Correo Electrónico: </strong><span> <?php echo $xml->Receptor->CorreoElectronico?></span></span>
            </td>
            <td style="width: 75%; color: #000000;font-size:14px;text-align:right;line-height: 20px;">
                <?php if(isset($xml->PlazoCredito) AND $xml->PlazoCredito!='0' AND $xml->PlazoCredito!='')
                {?>
                <span style="font-size: 14px;"><strong>Fecha de Vencimiento:</strong><span> <?php echo getFechaVencimiento($xml->PlazoCredito,$date);?></span></span><?php }
                else{
                ?>
                <span style="font-size: 14px;"><strong>Fecha de Vencimiento:</strong><span> No aplica</span></span> <?php }?>
            </td>
        </tr>

        <tr style="margin-top: 0px;">

            <td style="width: 75%; color: #000000;font-size:14px;text-align:left;line-height: 25px;">
                <span style="font-size: 14px;"><strong>Teléfono: </strong><span><?php echo $xml->Receptor->Telefono->NumTelefono; ?> </span></span>
            </td>
            <td style="width: 75%; color: #000000;font-size:14px;text-align:right;line-height: 25px;">
                <span style="font-size: 14px;"><strong>Forma de Pago:</strong><span> <?php echo getFormaPago($xml->MedioPago);?></span></span>
            </td>
        </tr>

        <tr style="margin-top: 0px;">
            <td style="width: 75%; color: #000000;font-size:14px;text-align:left;line-height: 25px;">
                <span style="font-size: 14px;"><strong>Tipo de Cambio: </strong><span><?php echo $xml->ResumenFactura->TipoCambio;?></span></span>
            </td>

            <td style="width: 50%; color: #000000;font-size:14px;text-align:right;line-height: 25px;">
                <span style="font-size: 14px;"><strong>Moneda: </strong><span><?php echo $xml->ResumenFactura->CodigoMoneda; ?></span></span>
            </td>
        </tr>

    </table>
</div>
    <br>



    <table style="text-align: center; font-size: 14px;border: 2px solid #fff;border: 2px;" width="791"  id="tabla_detalle" cellspacing="0">

            <tr style="background-color:#DCDCDC;">
                <th style="width: 25%;"><p>Artículo</p></th>
                <th style="width: 15%;"><p>Precio Unitario</p></th>
                <th style="width: 10%;"><p>Cantidad</p></th>
                <th style="width: 15%;"><p>Descuento</p></th>
                <th style="width: 15%;"><p>Impuesto</p></th>
                <th style="width: 20%;"><p>Total</p></th>

            </tr>



            <?php
            $xml_det=array();
            $xml_res=array();
            $xml_det=(array)$xml->DetalleServicio->LineaDetalle;
            $xml_res=(array)$xml->ResumenFactura;


            $montoImpuesto="0.00000";
            $montoDescuento="0.00000";
            $tamano=0;

            $tamano=sizeof((array)$xml->DetalleServicio);


            for($c=0;$c<$tamano;$c++){
                if($tamano==1)
                {

                    if(isset($xml_det->Impuesto->Monto) AND !empty($xml_det->Impuesto->Monto)){
                        $montoImpuesto=$xml_det->Impuesto->Monto;}
                    if(isset($xml_det['MontoDescuento']) AND !empty($xml_det['MontoDescuento'])){
                        $montoDescuento=$xml_det['MontoDescuento'];}
                }




                echo '<tr><td style="width: 25%;"><p>'.$xml_det['Detalle'].'</p></td>
            <td style="width: 15%;"><p>'.$xml_det['PrecioUnitario'].'</p></td>
            <td style="width: 10%;"><p>'.$xml_det['Cantidad'].'</p></td>
            <td style="width: 15%;"><p>'.$montoDescuento.'</p></td>
            <td style="width: 15%;"><p>'.$montoImpuesto.'</p></td>
            <td style="width: 20%;"><p>'.$xml_det['MontoTotalLinea'].'</p></td></tr>';

            }

            /*else{
                if(!isset($xml_det->DetalleServicio->LineaDetalle[$c]->Impuesto->Monto) OR empty($xml_det->DetalleServicio->LineaDetalle[$c]->Impuesto->Monto)){
                    $xml_det->DetalleServicio->LineaDetalle[$c]->Impuesto->Monto="0.00000";
                }
                echo '<tr><td style="width: 25%;"><p>'.$xml_det->DetalleServicio->LineaDetalle[$c]->Detalle.'</p></td>
            <td style="width: 15%;"><p>'.$xml_det->DetalleServicio->LineaDetalle[$c]->PrecioUnitario.'</p></td>
            <td style="width: 10%;"><p>'.$xml_det->DetalleServicio->LineaDetalle[$c]->Cantidad.'</p></td>
            <td style="width: 15%;"><p>'.$xml_det->DetalleServicio->LineaDetalle[$c]->MontoDescuento.'</p></td>
            <td style="width: 15%;"><p>'.$xml_det->DetalleServicio->LineaDetalle[$c]->Impuesto->Monto.'</p></td>
            <td style="width: 20%;"><p>'.$xml_det->DetalleServicio->LineaDetalle[$c]->MontoTotalLinea.'</p></td></tr>';
            }*/
            ?>



    </table>
    <br>
    <div style="border: 2px solid #000000;width: 100%;">
    <table>
        <tr>

        <td>
            <table  style="text-align: center; font-size: 14px;border: 2px solid #fff;float: right;width: 945px;" id="tabla_observaciones">

                <tr>
                    <th style="border-right: 0px solid #fff;text-align: left;" class="primeraTh">Observaciones(Otros)</th>

                </tr>
                <tr>
                    <td class="primeraTh" style="border-bottom: 1px solid #fff;border-right: 1px solid #fff;text-align: left;"><?php echo $xml->Otros->OtroTexto;?></td>
                </tr>
            </table>
        </td>
            <td><table  style="width: 600px; text-align: center; font-size: 14px;border: 2px solid #000000;border-bottom: 1px solid #fff;" id="tabla_resumen">
                    <tr>
                        <th class="segundaTh">Total servicios gravados</th>
                        <th class="terceraTh"><?php  echo $xml_res['TotalServGravados'];?></th>
                    </tr>
                    <tr>

                        <th class="segundaTh">Total servicios exentos</th>
                        <th class="terceraTh"><?php echo $xml_res['TotalServExentos'];?></th>
                    </tr>

                    <tr>

                        <th class="segundaTh">Total mercancías gravadas</th>
                        <th class="terceraTh"><?php echo $xml_res['TotalMercanciasGravadas'];?></th>
                    </tr>
                    <tr>

                        <th class="segundaTh">Total mercancías exentas</th>
                        <th class="terceraTh"><?php echo $xml_res['TotalMercanciasExentas'];?></th>
                    </tr>
                    <tr>

                        <th class="segundaTh">Total gravado</th>
                        <th class="terceraTh"><?php echo $xml_res['TotalGravado'];?></th>
                    </tr>
                    <tr>

                        <th class="segundaTh">Total exento</th>
                        <th class="terceraTh"><?php echo $xml_res['TotalExento'];?></th>
                    </tr>
                    <tr>

                        <th class="segundaTh">Total venta</th>
                        <th class="terceraTh"><?php echo $xml_res['TotalVenta'];?></th>
                    </tr>
                    <tr>

                        <th class="segundaTh">Total descuento</th>
                        <th class="terceraTh"><?php echo $xml_res['TotalDescuentos'];?></th>
                    </tr>
                    <tr>

                        <th class="segundaTh">Total venta neta</th>
                        <th class="terceraTh"><?php echo $xml_res['TotalVentaNeta'];?></th>
                    </tr>
                    <tr>

                        <th class="segundaTh">Total impuestos</th>
                        <th class="terceraTh"><?php echo $xml_res['TotalImpuesto'];?></th>
                    </tr>
                    <tr style="border-bottom: 1px solid #fff;">

                        <th class="segundaTh" style="border-bottom: 1px solid #fff;">Total comprobante</th>
                        <th class="terceraTh" style="border-bottom: 1px solid #fff;"><?php echo $xml_res['TotalComprobante'];?></th>
                    </tr>
                </table></td>
        </tr>
    </table>
    </div>
    <br>
</page>
<br>

<div style="width:55%;"><p style="text-align: left;"><strong>Firma:________________________________________________</strong></p><p style="margin-top: 0px;text-align: center;"><strong> <?php echo $xml->Receptor->Nombre;?></strong></p></div>
<br><br>
<div style="display: inline-block;width: 790px;"><strong><p style="text-align: center">"Autorizada mediante resolución N° DGT-R-48-2016 del 07-10-2016"</p></strong></div>


