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
        @if (count($errors) > 0)
            <div class="alert alert-danger">
                {{$errors}}
            </div>
        @endif
    </div>
    <div class="row">
        <div class="col-md-12">
            <?php $ruta_actualizar="/usuarios/actualizar/".$usuario->id;?>
            <form action="<?php echo $ruta_actualizar?>" method="post" enctype="multipart/form-data" id="form-emisor">
                {{ method_field('PUT') }}
                <input name="_token" type="hidden" value="{{ csrf_token() }}"/>

                <div class="col-md-12">
                    <div class="col-md-6 col-md-offset-3">
                        <label for="id">ID</label>
                        <input value="{{$usuario->id}}" class="form-control quitarFlechas" id="id" name="id" oninput="javascript: if (this.value.length > this.maxLength) this.value = this.value.slice(0, this.maxLength);" type = "number" maxlength = "12" readonly>
                        <!--<small id="emailHelp" class="form-text text-muted">Cédula Física, Cédula Jurídica, NITE o DIMEX</small>-->
                    </div>
                    <div class="col-md-6 col-md-offset-3">
                        <label for="usu_name">Nombre</label>
                        <input value="{{$usuario->name}}" type="text" class="form-control" id="usu_name" name="usu_name" maxlength="80" required>
                        <!--<small id="emailHelp" class="form-text text-muted">Cédula Física, Cédula Jurídica, NITE o DIMEX</small>-->
                    </div>

                    <div class="col-md-6 col-md-offset-3">
                        <label for="usu_email">Correo Electrónico</label>
                        <input type="email" value="{{$usuario->email}}" class="form-control" id="usu_email" name="usu_email" maxlength="40" required>
                    </div>
                    <div class="col-md-6 col-md-offset-3">
                        <label for="usu_pass">Role</label>
                        <input type="text" value="{{$usuario->rolname}}" class="form-control" id="usu_pass" name="usu_pass" maxlength="40" readonly>
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

<script src="/js/validaFormAgregCliente.js"></script>



