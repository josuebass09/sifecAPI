$(document).ready(function(){
   cargaEmisores('all','none');
    $('#tabla_emisores').DataTable();

});

$( "#busqueda_emi" ).keyup(function() {
    var query=$("#busqueda_emi").val();
    var filtro=$("#filtro_busqueda_emi").val();
    cargaEmisores(query,filtro);
    //alert(query+" ,"+filtro);
});

function cargaEmisores(query,filtro)
{
    $("#loader").hide();
    $.ajax({
        type: "GET",
        url: "/getEmisor/"+query+"/"+filtro,
        beforeSend: function(objeto){

            //$("#loader").html("<img src='/img/loader.gif'>");
        },
        success: function(datos){

            $("#tabla_emisores").html(datos);
            $("#loader").hide();
        }
    });
    event.preventDefault();


}

/*function abrir_modal(e) {
$("#ventana_eliminar_emisor").modal('show');
    $("#tabla_emisores").on('click','.btn-danger',function(){
        var currentRow=$(this).closest("tr");
        var col1=currentRow.find("td:eq(0)").text();
        var col2=currentRow.find("td:eq(1)").text();
        var ruta_eliminar="emisores/"+col1+"/"+col2;
        $("#razon_social_modal").text(col2);
        $('#btn_aceptar_eliminar').attr('href',ruta_eliminar);

    });
//$("#razon_social_modal").text('')
}*/
/*function elimina_emisor(identificador,nombre)
{
    if (confirm("¿Desea eliminar el emisor ?")){
        $.ajax({
            type: "GET",
            url: "./ajax/buscar_leches.php",
            beforeSend: function(objeto){

            },
            success: function(datos){
                cargaEmisores('all','none');
            }
        });


    }

}*/


