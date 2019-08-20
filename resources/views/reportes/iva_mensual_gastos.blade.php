<?php
$sumAcre=0;
$sumAplicable=0;
$sumImpuesto=0;
$sumTotal=0;
?>
        <!DOCTYPE html>
<html>
<head>
    <title>Reporte IVA Gastos</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.5/css/bootstrap.min.css" integrity="sha512-dTfge/zgoMYpP7QbHy4gWMEGsbsdZeCXz7irItjcC3sPUFtf0kuFbDz/ixG7ArTxmDjLXDmezHubeNikyKGVyQ==" crossorigin="anonymous">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>
<body style="width: 100%;margin-left: -30px;">
<div class="container">
    <!--<h2 style="background-color: #00C851;width: 100%;">Ventas</h2>-->
    <table class="table table-bordered table-striped table-hover" width="100">
        <tr bgcolor="#008b8b" style="font-size: 16px;"><th colspan="8">Gastos</th></tr>
        <tr bgcolor="silver">
            <th>Consecutivo</th>
            <!--<th width="100">Fecha Emisión</th>-->
            <th>Total Impuesto</th>
            <th>Total Venta</th>
            <th>Total IVA Acreditable</th>
            <th>Total Aplicable</th>
            <th>Vendedor</th>
            <th>Estado</th>
        </tr>
        @foreach($datos as $da)
            <tr>
                <td>{{$da->consecutivo_recepcion}}</td>
                <td>{{$da->total_impuestos}}</td>
                <td>{{$da->total_comprobante}}</td>
                <td>{{$da->total_acred}}</td>
                <td>{{$da->total_apli}}</td>
                <td>{{$da->identificacion_emisor}}</td>
                @if($da->estado=='aceptado')

                    <td bgcolor="#228b22">
                        {{$da->estado}}
                    </td>
                    {{ $sumAcre+=floatval($da->total_acred)}}
                    {{ $sumAplicable+=floatval($da->total_apli)}}
                    {{ $sumImpuesto+=floatval($da->total_impuestos)}}
                    {{$sumTotal+=floatval($da->total_comprobante)}}
                @endif
                @if($da->estado=='rechazado')

                    <td bgcolor="red">
                        {{$da->estado}}
                    </td>
                @endif
                @if($da->estado=='procesando')

                    <td bgcolor="#6495ed">
                        {{$da->estado}}
                    </td>
                @endif
            </tr>

        @endforeach

        <tfoot>
        <tr>
            <th colspan="3"></th>
            <th bgcolor="silver">Total IVA</th>
            <th bgcolor="silver">Total General Gastado</th>
            <th bgcolor="silver">Total IVA Acreditado</th>
            <th bgcolor="silver">Total Aplicable</th>
        </tr>
        <tr>
            <td colspan="3"></td>
            <td>
                {{number_format($sumImpuesto,5,'.',',')}}
            </td>
            <td>
                {{number_format($sumTotal,5,'.',',')}}
            </td>
            <td>
                {{number_format($sumAcre,5,'.',',')}}
            </td>
            <td>
                {{number_format($sumAplicable,5,'.',',')}}
            </td>
        </tr>

        </tfoot>
    </table>

</div><br /><br />

</body>
</html>


