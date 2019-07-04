<?php
/**
 * Created by PhpStorm.
 * User: josue
 * Date: 07/03/19
 * Time: 03:59 PM
 */
?>

    <!DOCTYPE html>
<html lang="en">
@include('head')


<body>

@include('navbar')
<section id="main">
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-12">
                <div class="panel panel-default">
                    <div class="panel-body">
                        <div class="col-md-6">
                            <span style="float: left;position:absolute;margin-left:-3px; margin-top: -4px; height: 1.7em;width: 1.7em;background-color: #dc3545!important;border-radius: 50%;display: inline-block;color: white;"><p style="font-size: 1.9vh;text-align: center;padding: 2px;margin-top: 2px;">{{$num_emisores}}</p></span>
                            <div class="well dash-box" style="background-color:#4285F4;color:white;" id="modulo_emisores">
                                <h2><span class="glyphicon glyphicon-user" aria-hidden="true"></span></h2>
                                <h4>Emisores</h4>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="well dash-box" style="color: white;background-color: #ffbb33;" id="modulo_api">
                                <h2><span class="glyphicon glyphicon-cog" aria-hidden="true"></span></h2>
                                <h4>API</h4>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="well dash-box bg-danger" style="color: white;background-color:#00C851;" id="modulo_usuarios">
                                <h2><span class="glyphicon glyphicon-user" aria-hidden="true"></span></h2>
                                <h4>Usuarios</h4>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="well dash-box bg-danger" style="color: white;background-color: #ff4444;" id="modulo_parametros">
                                <h2><span class="glyphicon glyphicon glyphicon-wrench" aria-hidden="true"></span></h2>
                                <h4>Parámetros</h4>
                            </div>
                        </div>
                        <!--<div class="col-md-3">
                            <div class="well dash-box">
                                <h2><span class="glyphicon glyphicon-pencil" aria-hidden="true"></span> 33</h2>
                                <h4>Posts</h4>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="well dash-box">
                                <h2><span class="glyphicon glyphicon-stats" aria-hidden="true"></span> 12,334</h2>
                                <h4>Visitors</h4>
                            </div>
                        </div>-->
                    </div>
                </div>



                </div>
            </div>
        </div>
    </div>
</section>

@include('footer')
<script>

    $("#modulo_emisores").click(function () {
        window.location.href="/emisores";
    });
    $("#modulo_usuarios").click(function () {
        window.location.href="/usuarios";
    });
    $("#modulo_parametros").click(function () {
        window.location.href="/parametros";
    });
    $("#modulo_api").click(function () {
        window.location.href="/api";
    });


</script>
</body>
</html>



