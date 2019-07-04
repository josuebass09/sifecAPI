<?php

function makePdf($clave)
{

    session_start();
    $_SESSION['clave'] = $clave;

    require_once('/../html2pdf.class.php');
    include(dirname('__FILE__') . '/res/factura_genera.php');
    ob_start();



    $content = ob_get_clean();


    try {
        // init HTML2PDF
        $html2pdf = new HTML2PDF('P', 'LETTER', 'es', true, 'UTF-8', array(0, 0, 0, 0));
        // display the full page

        $html2pdf->pdf->SetDisplayMode('fullpage');


        // convert
        $html2pdf->writeHTML($content, $_GET['de']);

        $html2pdf->Output('Factura.pdf');
       // $html2pdf->Output('pdf/documentos/plantillas_generadas/ATV_Comprobante_Electrónico ' . $_SESSION['clave'] . '.pdf', 'F'); //PARA GUARDAR ARCHIVO EN EL SERVIDOR

    } catch (HTML2PDF_exception $e) {
        return $e->getMessage();
        exit;
    }
//Join entre comprobantes y clientes.

//print_r($fila);
}


//makePdf("50628031900020536006000100001010000000041105371107");
