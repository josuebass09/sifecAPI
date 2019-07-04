$(document).ready(function(){
    cargaUsuarios('all');

});

$( "#busqueda_usu" ).keyup(function() {
    var query=$("#busqueda_usu").val();
    cargaUsuarios(query);
    //alert(query+" ,"+filtro);
});

function cargaUsuarios(query)
{

    $.ajax({
        type: "GET",
        url: "/getUsuario/"+query,
        beforeSend: function(objeto){

            //$("#loader").html("<img src='/img/loader.gif'>");
        },
        success: function(datos){

            $("#tabla_usuarios").html(datos);

        }
    });
    event.preventDefault();


}
