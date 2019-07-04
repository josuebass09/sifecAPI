<?php
/**
 * Created by PhpStorm.
 * User: josue
 * Date: 18/03/19
 * Time: 04:01 PM
 */
?>
<?php
/**
 * Created by PhpStorm.
 * User: josue
 * Date: 08/03/19
 * Time: 11:48 PM
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
        @if (count($errors) > 0)
            <div class="alert alert-danger">
                {{$errors}}
            </div>
        @endif
    </div>
    <div class="row">
        <div class="col-md-12">
            <?php $ruta="setearParametros"?>
            <form action="<?php echo $ruta?>" method="post" enctype="multipart/form-data" id="form-parametros">
                <input name="_token" type="hidden" value="{{ csrf_token() }}"/>

                <div class="col-md-12">
                    <div class="col-md-3">
                        <label for="par_host_smtp">Host SMTP</label>
                        <input value="{{env('SMTP_HOST')}}" class="form-control quitarFlechas" id="par_host_smtp" name="par_host_smtp"  type = "text">
                        <!--<small id="emailHelp" class="form-text text-muted">Cédula Física, Cédula Jurídica, NITE o DIMEX</small>-->
                    </div>
                    <div class="col-md-3">
                        <label for="par_usuario_smtp">Usuario SMTP</label>
                        <input value="{{env('SMTP_USERNAME')}}" type="text" class="form-control" id="par_usuario_smtp" name="par_usuario_smtp">
                        <!--<small id="emailHelp" class="form-text text-muted">Cédula Física, Cédula Jurídica, NITE o DIMEX</small>-->
                    </div>

                    <div class="col-md-3">
                        <label for="par_contrasena_smtp">Contraseña SMTP</label>
                        <input value="{{env('SMTP_CONTRASENA')}}" type="text" class="form-control" id="par_contrasena_smtp" name="par_contrasena_smtp"  required>
                        <!--<small id="emailHelp" class="form-text text-muted">Cédula Física, Cédula Jurídica, NITE o DIMEX</small>-->
                    </div>
                    <div class="col-md-3">
                        <label for="par_puerto_smtp">Puerto SMTP</label>
                        <input value="{{env('SMTP_PUERTO')}}" class="form-control quitarFlechas" id="par_puerto_smtp" name="par_puerto_smtp" oninput="javascript: if (this.value.length > this.maxLength) this.value = this.value.slice(0, this.maxLength);" type = "number" maxlength = "5">
                        <!--<small id="emailHelp" class="form-text text-muted">Cédula Física, Cédula Jurídica, NITE o DIMEX</small>-->
                    </div>
                    <div class="col-md-3">
                        <label for="par_metodo">Método SMTP</label>
                        <input value="{{env('SMTP_METODO')}}" type="text" class="form-control" id="par_metodo" name="par_metodo" required>
                        <!--<small id="emailHelp" class="form-text text-muted">Cédula Física, Cédula Jurídica, NITE o DIMEX</small>-->
                    </div>
                    <div class="col-md-3">
                        <label for="par_easy_atv_server_prod">Servidor EasyATV Producción</label>
                        <input value="{{env('EASY_ATV_SERVER_PROD')}}" type="text" class="form-control" id="par_easy_atv_server_prod" name="par_easy_atv_server_prod" required>
                        <!--<small id="emailHelp" class="form-text text-muted">Cédula Física, Cédula Jurídica, NITE o DIMEX</small>-->
                    </div>
                    <div class="col-md-3">
                        <label for="par_easy_atv_server_test">Servidor EasyATV Test</label>
                        <input value="{{env('EASY_ATV_SERVER_TEST')}}" type="text" class="form-control" id="par_easy_atv_server_test" name="par_easy_atv_server_test" required>
                        <!--<small id="emailHelp" class="form-text text-muted">Cédula Física, Cédula Jurídica, NITE o DIMEX</small>-->
                    </div>
                </div>
                <div class="row" style="display: block;">
                    <div class="col-md-12" style="margin-bottom: 75px;margin-top: 25px;text-align: center;">
                        <button type="submit" class="btn btn-primary"><span class="glyphicon glyphicon-floppy-disk margendivisor"></span>Actualizar</button>
                        <a type="reset" class="btn btn-default" id="btn_resetear"><span class="glyphicon glyphicon-remove-sign" style="margin-right: 9px;"></span>Resetear</a>
                    </div>
                </div>
            </form>
        </div>
    </div>
    </div>
</section>

@include('footer')
<script src="/js/bootstrap.min.js"></script>






