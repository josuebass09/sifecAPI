$(document).ready(function(){
    cargaCantones($("#provincia").val());
});
$(':input[type=number]').on('mousewheel', function(e){
    $(this).blur();
});

function cargaCantones(cod_provincia)
{
    $.ajax({
        type: "GET",
        url: "/cantones/"+cod_provincia,
        beforeSend: function(objeto){
            //$("#resultados_ajax2").html("Mensaje: Cargando...");
        },
        success: function(datos){
            $("#canton").prop( "disabled", false );
            $("#canton").html(datos);
        }
    });
    event.preventDefault();
}
function cargaDistritos(cod_provincia,cod_canton)
{
    $.ajax({
        type: "GET",
        url: "/distritos/"+cod_provincia+"/"+cod_canton,
        beforeSend: function(objeto){
            //$("#resultados_ajax2").html("Mensaje: Cargando...");
        },
        success: function(datos){
            $("#distrito").prop( "disabled", false );
            $("#distrito").html(datos);

        }
    });
    event.preventDefault();
}
function cargaBarrios(cod_provincia,cod_canton,cod_distrito)
{
    $.ajax({
        type: "GET",
        url: "/barrios/"+cod_provincia+"/"+cod_canton+"/"+cod_distrito,
        beforeSend: function(objeto){
            //$("#resultados_ajax2").html("Mensaje: Cargando...");
        },
        success: function(datos){
            $("#barrio").prop( "disabled", false );
            $("#barrio").html(datos);

        }
    });
    event.preventDefault();
}

$("#provincia").change(function (e) {

        $("#canton").prop( "disabled", true);
        $("#canton").val('');
        $("#distrito").prop( "disabled", true);
        $("#distrito").val('');
        $("#barrio").prop( "disabled", true);
        $("#barrio").val('');
        cargaCantones($("#provincia").val());


});

$("#canton").change(function (e) {

    $("#distrito").prop( "disabled", true);
    $("#distrito").val('');
    $("#barrio").prop( "disabled", true);
    $("#barrio").val('');
    cargaDistritos($("#provincia").val(),$("#canton").val());
});

$("#distrito").change(function (e) {
    cargaBarrios($("#provincia").val(),$("#canton").val(),$("#distrito").val());
});
/*$( "#form-emisor" ).submit(function( event ) {

    event.preventDefault();
});*/
/*$("#canton").change(function (e) {

    var valor=$("#provincia").val();
    var valor2=$("#canton").val();
    var parametros = {'cod_provincia':valor,'cod_canton':valor2};

    $.ajax({
        type: "POST",
        url: "ajax/autocomplete/distritos.php",
        data: parametros,
        beforeSend: function(objeto){
            $("#resultados_ajax2").html("Mensaje: Cargando...");
        },
        success: function(datos){
            //var array_datos=JSON.parse(datos);

            $( "#distrito" ).prop( "disabled", false );
            $("#distrito").html(datos);
        }
    });
    event.preventDefault();

});

$("#distrito").change(function (e) {


    var valor=$("#provincia").val();
    var valor2=$("#canton").val();
    var valor3=$("#distrito").val();
    var parametros = {'cod_provincia':valor,'cod_canton':valor2,'cod_distrito':valor3};


    $.ajax({
        type: "POST",
        url: "ajax/autocomplete/barrios.php",
        data: parametros,
        beforeSend: function(objeto){
            $("#resultados_ajax2").html("Mensaje: Cargando...");
        },
        success: function(datos){
            //var array_datos=JSON.parse(datos);

            $("#barrio").prop( "disabled", false );
            $("#barrio").html(datos);
        }
    });
    event.preventDefault();

});*/
