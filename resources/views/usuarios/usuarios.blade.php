<?php
/**
 * Created by PhpStorm.
 * User: josue
 * Date: 18/03/19
 * Time: 01:23 PM
 */
?>

    <!DOCTYPE html>
<html lang="en">
@include('head')

<body>
@include('navbar')
<section id="main">
    <div class="container-fluid">
        @include('flash::message')

        <div class="row">
            <div class="btn-group col-md-12" style="margin-bottom: 16px;">
                <!-- Search form -->
                <div class="form-group col-md-6" style="margin:0;padding:0;">
                    <div class="col-md-12">
                        <label for="busqueda_usu">Buscar</label>
                        <input class="form-control" id="busqueda_usu" style="float: left;" type="text" aria-label="Search">

                    </div>
                </div>

                <a href="{{route('agregarUsuario')}}" class="btn btn-success" style="float: right;"><span class="glyphicon glyphicon-plus-sign"></span> Agregar</a>
            </div>
        </div>
        <div class="row">

            <div class="panel panel-default">

                <div class="panel-body">
                    <table class="table table-striped table-hover" id="tabla_usuarios" style="width: 100%;text-align: left;">
                    </table>
                </div>
            </div>
        </div>
    </div>
    <!--<div class="modal fade" id="ventana_eliminar_emisor" tabindex="-1" role="dialog" aria-labelledby="ModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header" style="background-color:#e74c3c !important">
                    <button type="button" class="close" data-dismiss="modal">
                        <span aria-hidden="true">&times;</span>
                        <span class="sr-only">Cerrar</span>
                    </button>
                    <h4 class="modal-title" id="ModalLabel" style="color: white;font-size: 1.5em;">Eliminar Emisor</h4>
                </div>
                <div class="modal-body">
                    ¿Está seguro de eliminar al Emisor <strong id="razon_social_modal"></strong>?
                </div>
                <div class="modal-footer">
                    <form>
                        <a href="#" id="btn_aceptar_eliminar" class="btn btn-danger">Aceptar</a>
                        <button type="button" class="btn btn-default" data-dismiss="modal">Cancelar</button>
                    </form>
                </div>
            </div>
        </div>
    </div>-->

</section>

@include('footer')

<script src="{{asset('/js/usuarios.js')}}" rel="javascript" type="text/javascript"></script>
</body>
</html>



