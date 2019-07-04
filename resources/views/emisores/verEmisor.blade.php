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
            <?php $ruta_actualizar="/emisores/actualizar/".$emisor->id;
            $path_logo='/img/'.$emisor->logo;
            ?>
            <form action="<?php echo $ruta_actualizar?>" method="post" enctype="multipart/form-data" id="form-emisor" style="margin-top: -20px;">
                {{ method_field('PUT') }}
                <input name="_token" type="hidden" value="{{ csrf_token() }}"/>
                <div class="col-md-12">
                    <div class="checkbox checkbox-success col-md-1" style="float: left;">
                        @if($emisor->activo==1)
                            <input id="check_activo_emisor" name="check_activo_emisor" type="checkbox" checked>
                        @endif
                        @if($emisor->activo==0)
                                <input id="check_activo_emisor" name="check_activo_emisor" type="checkbox">
                            @endif
                        <label for="check_activo_emisor">
                            <span style="font-size: 1.2em;">Activo</span>
                        </label>
                    </div>
                    <!--<img src="{{$path_logo}}">-->
                    @if($emisor->logo!=NULL)
                        <div style="text-align: center;">
                            <img src="{{$path_logo}}">
                        </div>
                    @endif

                </div>
                <div class="form-group">
                    <h2 style="margin-left: 15px;">Datos Personales</h2>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        <label for="emi_tp_ide">Tipo de Identificación</label>

                        <select class="form-control" id="emi_tp_ide" name="emi_tp_ide" required>
                            <option value="01">Cédula Física</option>
                            <option value="02">Cédula Jurídica</option>
                            <option value="03">DIMEX</option>
                            <option value="04">NITE</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="emi_otr_sen">Otras Señas</label>
                        <input value="{{$emisor->otras_senas}}" type="text" class="form-control" id="emi_otr_sen" name="emi_otr_sen"  maxlength="160" required>
                        <!--<small id="emailHelp" class="form-text text-muted">Cédula Física, Cédula Jurídica, NITE o DIMEX</small>-->
                    </div>
                    <div class="form-group">
                        <label for="emi_ven_pla">Vencimiento del Plan</label>
                        <input value="{{$emisor->vencimiento_plan}}" type="date" class="form-control" id="emi_ven_pla" name="emi_ven_pla" maxlength="10" required>
                        <!--<small id="emailHelp" class="form-text text-muted">Cédula Física, Cédula Jurídica, NITE o DIMEX</small>-->
                    </div>
                    <div class="form-group">
                        <label for="emi_telefono">Teléfono</label>
                        <input type="number" class="form-control" value="{{$emisor->telefono}}" id="emi_telefono" name="emi_telefono" oninput="javascript: if (this.value.length > this.maxLength) this.value = this.value.slice(0, this.maxLength);" maxlength="20">
                        <!--<small id="emailHelp" class="form-text text-muted">Cédula Física, Cédula Jurídica, NITE o DIMEX</small>-->
                    </div>
                    <div class="form-group">
                        <h2>Datos ATV</h2>
                        <!--<small id="emailHelp" class="form-text text-muted">Cédula Física, Cédula Jurídica, NITE o DIMEX</small>-->
                    </div>


                        <!--<small id="emailHelp" class="form-text text-muted">Cédula Física, Cédula Jurídica, NITE o DIMEX</small>-->
                    <br>
                    <div class="form-group" style="margin-top: -20px;">
                        <label for="emi_usu_atv">Usuario ATV Producción</label>
                        <input value="{{$emisor->usuario_atv_prod}}" type="text" class="form-control" id="emi_usu_atv" name="emi_usu_atv"  maxlength="254" required>
                        <!--<small id="emailHelp" class="form-text text-muted">Cédula Física, Cédula Jurídica, NITE o DIMEX</small>-->
                    </div>
                    <div class="form-group">
                        <label class="form-check-label" for="myfile">Llave Criptográfica Producción</label>
                        <input class="form-control" type="file" id="myfile" name="myfile"/>
                    </div>

                    <div class="form-group">
                        <label for="emi_usu_atv_tes">Usuario ATV Test</label>
                        <input value="{{$emisor->usuario_atv_test}}" type="text" class="form-control" id="emi_usu_atv_tes" name="emi_usu_atv_tes"  maxlength="254">
                        <!--<small id="emailHelp" class="form-text text-muted">Cédula Física, Cédula Jurídica, NITE o DIMEX</small>-->
                    </div>
                    <div class="form-group">
                        <label class="form-check-label" for="myfile2">Llave Criptográfica Test</label>
                        <input class="form-control" type="file" id="myfile2" name="myfile2"/>
                    </div>

                    <div class="form-group">
                        <h2>Consecutivos</h2>
                        <!--<small id="emailHelp" class="form-text text-muted">Cédula Física, Cédula Jurídica, NITE o DIMEX</small>-->
                    </div>
                    <br>
                    <div class="form-group" style="margin-top: -20px;">
                        <label for="emi_con_fe_pro">consecutivoFE Producción</label>
                        <input type="number" class="form-control" id="emi_con_fe_pro" name="emi_con_fe_pro" value="{{$emisor->consecutivoFEprod}}" oninput="javascript: if (this.value.length > this.maxLength) this.value = this.value.slice(0, this.maxLength);" maxlength="10" required>
                        <!--<small id="emailHelp" class="form-text text-muted">Cédula Física, Cédula Jurídica, NITE o DIMEX</small>-->
                    </div>
                    <div class="form-group">
                        <label for="emi_con_ga_pro">consecutivoGA Producción</label>
                        <input type="number" class="form-control" id="emi_con_ga_pro" name="emi_con_ga_pro" value="{{$emisor->consecutivoGAprod}}" oninput="javascript: if (this.value.length > this.maxLength) this.value = this.value.slice(0, this.maxLength);" maxlength="10" required>
                        <!--<small id="emailHelp" class="form-text text-muted">Cédula Física, Cédula Jurídica, NITE o DIMEX</small>-->
                    </div>
                    <div class="form-group">
                        <label for="emi_con_fe_tes">consecutivoFE Test</label>
                        <input type="number" class="form-control" id="emi_con_fe_tes" name="emi_con_fe_tes" value="{{$emisor->consecutivoFEtest}}" oninput="javascript: if (this.value.length > this.maxLength) this.value = this.value.slice(0, this.maxLength);" maxlength="10">
                        <!--<small id="emailHelp" class="form-text text-muted">Cédula Física, Cédula Jurídica, NITE o DIMEX</small>-->
                    </div>
                    <div class="form-group">
                        <label for="emi_con_ga_tes">consecutivoGA Test</label>
                        <input type="number" class="form-control" id="emi_con_ga_tes" name="emi_con_ga_tes" value="{{$emisor->consecutivoGAtest}}" oninput="javascript: if (this.value.length > this.maxLength) this.value = this.value.slice(0, this.maxLength);" maxlength="10">
                        <!--<small id="emailHelp" class="form-text text-muted">Cédula Física, Cédula Jurídica, NITE o DIMEX</small>-->


                    </div>


                    <div class="form-group">
                        <h2>Ubicación</h2>
                        <!--<small id="emailHelp" class="form-text text-muted">Cédula Física, Cédula Jurídica, NITE o DIMEX</small>-->
                    </div>

                    <div class="form-group">
                        <label for="provincia_guardada">Provincia</label>
                        <input type="text" id="provincia_guardada" value="{{$emisor->provincia}}" class="form-control" readonly="readonly">
                    </div>

                    <div class="form-group" id="div_provincia">
                        <label for="provincia">Provincia</label>
                        <select class="form-control" id="provincia" name="provincia">
                            <option value="">Seleccione una Provincia</option>
                            <option value="1">San José</option>
                            <option value="2">Alajuela</option>
                            <option value="3">Cartago</option>
                            <option value="4">Heredia</option>
                            <option value="5">Guanacaste</option>
                            <option value="6">Puntarenas</option>
                            <option value="7">Limón</option>
                        </select>
                    </div>

                        <div class="custom-control custom-checkbox">
                            <button type="button" id="desbloquear_ubicacion" class="btn btn-default btn-warning"><i class="glyphicon glyphicon-lock margendivisor"></i>Editar Ubicación</button>
                        </div>
                    <br><br>
                    <div class="custom-control custom-checkbox">
                        @if($emisor->SMTP_OP==1)
                        <input id="desbloquear_smtp_opcional" name="desbloquear_smtp_opcional" class="checkbox-warning" type="checkbox" style="float: left;margin-right: 3px;" checked>
                        @endif
                        @if($emisor->SMTP_OP==0)
                                <input id="desbloquear_smtp_opcional" name="desbloquear_smtp_opcional" class="checkbox-warning" type="checkbox" style="float: left;margin-right: 3px;">
                            @endif
                        <label style="margin-top: 2px;">
                            <h2>SMTP Opcional</h2>
                        </label>
                    </div>
                    <br>
                    <div class="form-group" id="div_host">
                        <label for="emi_hos_smt_opc">Host SMTP Opcional</label>
                        <input type="text" value="{{$emisor->host_smtp_secundario}}" class="form-control" id="emi_hos_smt_opc" name="emi_hos_smt_opc" maxlength="50">
                        <!--<small id="emailHelp" class="form-text text-muted">Cédula Física, Cédula Jurídica, NITE o DIMEX</small>-->
                    </div>
                    <div class="form-group" id="div_puerto_smtp">
                        <label for="emi_pue_smt">Puerto SMTP Opcional</label>
                        <input type="number" value="{{$emisor->puerto_smtp_secundario}}" class="form-control" id="emi_pue_smt" name="emi_pue_smt" oninput="javascript: if (this.value.length > this.maxLength) this.value = this.value.slice(0, this.maxLength);" maxlength="5">
                        <!--<small id="emailHelp" class="form-text text-muted">Cédula Física, Cédula Jurídica, NITE o DIMEX</small>-->
                    </div>


                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        <label for="id">ID Fiscal</label>
                        <input value="{{$emisor->id}}" class="form-control quitarFlechas" id="id" name="id" oninput="javascript: if (this.value.length > this.maxLength) this.value = this.value.slice(0, this.maxLength);" type = "number" maxlength = "12" readonly>
                        <!--<small id="emailHelp" class="form-text text-muted">Cédula Física, Cédula Jurídica, NITE o DIMEX</small>-->
                    </div>

                    <div class="form-group">
                        <label for="emi_nom_com">Nombre Comercial</label>
                        <input type="text" value="{{$emisor->nombre_comercial}}" class="form-control" id="emi_nom_com" name="emi_nom_com" maxlength="80">
                        <!--<small id="emailHelp" class="form-text text-muted">Cédula Física, Cédula Jurídica, NITE o DIMEX</small>-->
                    </div>
                    <div class="form-group">
                        <label for="emi_logo">Logo</label><small id="emailHelp" class="form-text text-muted" style="margin-left: 5px;color: red;"><i class="glyphicon glyphicon-info-sign" title="La resolución debe ser IGUAL a 96x96"></i></small>
                        <input class="form-control" type="file" id="emi_logo" name="emi_logo"/>
                    </div>
                    <div class="form-group">
                        <label for="emi_fax">Fax</label>
                        <input type="number" class="form-control" value="{{$emisor->fax}}" id="emi_fax" name="emi_fax" oninput="javascript: if (this.value.length > this.maxLength) this.value = this.value.slice(0, this.maxLength);" maxlength="20">
                        <!--<small id="emailHelp" class="form-text text-muted">Cédula Física, Cédula Jurídica, NITE o DIMEX</small>-->
                    </div>
                    <div class="form-group" style="opacity: 0;">
                        <label for="emi_con_nc_test">SIN DEFINIR</label>
                        <input type="number" class="form-control" readonly>
                        <!--<small id="emailHelp" class="form-text text-muted">Cédula Física, Cédula Jurídica, NITE o DIMEX</small>-->
                    </div>

                    <div class="form-group" style="margin-top: -20px;">
                        <label for="emi_con_atv">Contraseña ATV Producción</label>
                        <input value="{{$emisor->contrasena_atv_prod}}" type="password" class="form-control" id="emi_con_atv" name="emi_con_atv" maxlength="254" required>
                    </div>

                    <div class="form-group" style="opacity: 0;">
                        <label for="emi_con_nc_test">SIN DEFINIR</label>
                        <input type="number" class="form-control" readonly>
                        <!--<small id="emailHelp" class="form-text text-muted">Cédula Física, Cédula Jurídica, NITE o DIMEX</small>-->
                    </div>

                    <div class="form-group">
                        <label for="emi_con_atv_test">Contraseña ATV Test</label>
                        <input type="password" value="{{$emisor->contrasena_atv_test}}" class="form-control" id="emi_con_atv_test" name="emi_con_atv_test" maxlength="254">
                    </div>
                    <div class="form-group" style="opacity: 0;">
                        <label for="emi_con_nc_test">SIN DEFINIR</label>
                        <input type="number" class="form-control" readonly>
                        <!--<small id="emailHelp" class="form-text text-muted">Cédula Física, Cédula Jurídica, NITE o DIMEX</small>-->
                    </div>
                    <div class="form-group" style="opacity: 0;">
                        <label for="emi_con_nc_test">SIN DEFINIR</label>
                        <input type="number" class="form-control" readonly>
                        <!--<small id="emailHelp" class="form-text text-muted">Cédula Física, Cédula Jurídica, NITE o DIMEX</small>-->
                    </div>
                    <div class="form-group" style="margin-top: -20px;">
                        <label for="emi_con_te_pro">consecutivoTE Producción</label>
                        <input type="number" class="form-control" id="emi_con_te_pro" name="emi_con_te_pro" value="{{$emisor->consecutivoTEprod}}" oninput="javascript: if (this.value.length > this.maxLength) this.value = this.value.slice(0, this.maxLength);" maxlength="10" required>
                        <!--<small id="emailHelp" class="form-text text-muted">Cédula Física, Cédula Jurídica, NITE o DIMEX</small>-->
                    </div>

                    <div class="form-group">
                        <label for="emi_con_fec_pro">consecutivoFEC Producción</label>
                        <input type="number" class="form-control" id="emi_con_fec_pro" name="emi_con_fec_pro" value="{{$emisor->consecutivoFECprod}}" oninput="javascript: if (this.value.length > this.maxLength) this.value = this.value.slice(0, this.maxLength);" maxlength="10">
                        <!--<small id="emailHelp" class="form-text text-muted">Cédula Física, Cédula Jurídica, NITE o DIMEX</small>-->
                    </div>
                    <div class="form-group">
                        <label for="emi_con_te_tes">consecutivoTE Test</label>
                        <input type="number" class="form-control" id="emi_con_te_tes" name="emi_con_te_tes" value="{{$emisor->consecutivoTEtest}}" oninput="javascript: if (this.value.length > this.maxLength) this.value = this.value.slice(0, this.maxLength);" maxlength="10">
                        <!--<small id="emailHelp" class="form-text text-muted">Cédula Física, Cédula Jurídica, NITE o DIMEX</small>-->
                    </div>


                    <div class="form-group">
                        <label for="emi_con_fec_test">consecutivoFEC Test</label>
                        <input type="number" class="form-control" id="emi_con_fec_test" name="emi_con_fec_test" value="{{$emisor->consecutivoFECtest}}" oninput="javascript: if (this.value.length > this.maxLength) this.value = this.value.slice(0, this.maxLength);" maxlength="10">
                        <!--<small id="emailHelp" class="form-text text-muted">Cédula Física, Cédula Jurídica, NITE o DIMEX</small>-->
                    </div>
                    <div class="form-group" style="opacity: 0;">
                        <label for="emi_con_nc_test">SIN DEFINIR</label>
                        <input type="number" class="form-control" readonly>
                        <!--<small id="emailHelp" class="form-text text-muted">Cédula Física, Cédula Jurídica, NITE o DIMEX</small>-->
                    </div>
                    <div class="form-group" style="margin-top: -20px;">
                        <label for="canton_guardado">Cantón</label>
                        <input type="text" id="canton_guardado" value="{{$emisor->canton}}" class="form-control" readonly="readonly">
                    </div>
                    <div class="form-group" id="div_canton">
                        <label for="canton">Cantón</label>
                        <select class="form-control" id="canton" name="canton"></select>
                    </div>
                    <div class="form-group" style="opacity: 0;">
                        <label for="emi_con_nc_test">SIN DEFINIR</label>
                        <input type="number" class="form-control" readonly>
                        <!--<small id="emailHelp" class="form-text text-muted">Cédula Física, Cédula Jurídica, NITE o DIMEX</small>-->
                    </div>
                    <div class="form-group" style="opacity: 0;">
                        <label for="emi_con_nc_test">SIN DEFINIR</label>
                        <input type="number" class="form-control" readonly>
                        <!--<small id="emailHelp" class="form-text text-muted">Cédula Física, Cédula Jurídica, NITE o DIMEX</small>-->
                    </div>
                    <br><br>

                    <div class="form-group" id="div_usuario_smtp" style="margin-top: -30px;">
                        <label for="emi_usu_smt_opc">Usuario SMTP Opcional</label>
                        <input type="text" class="form-control" id="emi_usu_smt_opc" name="emi_usu_smt_opc" value="{{$emisor->usuario_smtp_secundario}}" maxlength="50">
                        <!--<small id="emailHelp" class="form-text text-muted">Cédula Física, Cédula Jurídica, NITE o DIMEX</small>-->
                    </div>


                </div>
                <div class="col-md-3">

                    <div class="form-group">
                        <label for="emi_raz_soc">Razón Social</label>
                        <input value="{{$emisor->razon_social}}" type="text" class="form-control" id="emi_raz_soc" name="emi_raz_soc" maxlength="80" required>
                        <!--<small id="emailHelp" class="form-text text-muted">Cédula Física, Cédula Jurídica, NITE o DIMEX</small>-->
                    </div>
                    <div class="form-group">
                        <label for="emi_cre_usa">Créditos Usados</label>
                        <input  type="number" value="{{$emisor->creditos_usados}}" class="form-control"  id="emi_cre_usa" name="emi_cre_usa" oninput="javascript: if (this.value.length > this.maxLength) this.value = this.value.slice(0, this.maxLength);" maxlength="6" required>
                        <!--<small id="emailHelp" class="form-text text-muted">Cédula Física, Cédula Jurídica, NITE o DIMEX</small>-->
                    </div>

                    <div class="form-group">

                        <label for="emi_api_key" style="display: block;">API Key</label>
                        <input value="{{$emisor->api_key}}" style="width: 83%;display: inline-block;" type="text" class="form-control" id="emi_api_key" name="emi_api_key"  maxlength="80" readonly>
                        <a type="button" style="width: 15%;" id="btn_api_key" class="btn btn-success" title="Generar Nueva API Key"><i class="glyphicon glyphicon-refresh"></i></a>


                        <!--<small id="emailHelp" class="form-text text-muted">Cédula Física, Cédula Jurídica, NITE o DIMEX</small>-->
                    </div>
                    <div class="form-group" style="opacity: 0;">
                        <label for="emi_con_nc_test">SIN DEFINIR</label>
                        <input type="number" class="form-control" readonly>
                        <!--<small id="emailHelp" class="form-text text-muted">Cédula Física, Cédula Jurídica, NITE o DIMEX</small>-->
                    </div>
                    <div class="form-group" style="opacity: 0;">
                        <label for="emi_con_nc_test">SIN DEFINIR</label>
                        <input type="number" class="form-control" readonly>
                        <!--<small id="emailHelp" class="form-text text-muted">Cédula Física, Cédula Jurídica, NITE o DIMEX</small>-->
                    </div>

                    <div class="form-group" style="margin-top: -20px;">
                        <label for="emi_pin_atv">PIN ATV Producción</label>
                        <input value="{{$emisor->pin_atv_prod}}" type="number" class="form-control" id="emi_pin_atv" name="emi_pin_atv"  oninput="javascript: if (this.value.length > this.maxLength) this.value = this.value.slice(0, this.maxLength);" maxlength="4" required>
                        <!--<small id="emailHelp" class="form-text text-muted">Cédula Física, Cédula Jurídica, NITE o DIMEX</small>-->
                    </div>


                    <div class="form-group" style="opacity: 0;">
                        <label for="emi_con_nc_test">SIN DEFINIR</label>
                        <input type="number" class="form-control" readonly>
                        <!--<small id="emailHelp" class="form-text text-muted">Cédula Física, Cédula Jurídica, NITE o DIMEX</small>-->
                    </div>

                    <div class="form-group">
                        <label for="emi_pin_atv_test">PIN ATV Test</label>
                        <input type="number" value="{{$emisor->pin_atv_test}}" class="form-control" id="emi_pin_atv_test" name="emi_pin_atv_test"  oninput="javascript: if (this.value.length > this.maxLength) this.value = this.value.slice(0, this.maxLength);" maxlength="4">
                        <!--<small id="emailHelp" class="form-text text-muted">Cédula Física, Cédula Jurídica, NITE o DIMEX</small>-->
                    </div>
                    <div class="form-group" style="opacity: 0;">
                        <label for="emi_con_nc_test">SIN DEFINIR</label>
                        <input type="number" class="form-control" readonly>
                        <!--<small id="emailHelp" class="form-text text-muted">Cédula Física, Cédula Jurídica, NITE o DIMEX</small>-->
                    </div>
                    <div class="form-group" style="opacity: 0;">
                        <label for="emi_con_nc_test">SIN DEFINIR</label>
                        <input type="number" class="form-control" readonly>
                        <!--<small id="emailHelp" class="form-text text-muted">Cédula Física, Cédula Jurídica, NITE o DIMEX</small>-->
                    </div>
                    <div class="form-group" style="margin-top: -20px;">
                        <label for="emi_con_nc_pro">consecutivoNC Producción</label>
                        <input type="number" class="form-control" id="emi_con_nc_pro" name="emi_con_nc_pro" value="{{$emisor->consecutivoNCprod}}" oninput="javascript: if (this.value.length > this.maxLength) this.value = this.value.slice(0, this.maxLength);" maxlength="10" required>
                        <!--<small id="emailHelp" class="form-text text-muted">Cédula Física, Cédula Jurídica, NITE o DIMEX</small>-->
                    </div>
                    <div class="form-group">
                        <label for="emi_con_fee_pro">consecutivoFEE Producción</label>
                        <input type="number" class="form-control" id="emi_con_fee_pro" name="emi_con_fee_pro" value="{{$emisor->consecutivoFEEprod}}" oninput="javascript: if (this.value.length > this.maxLength) this.value = this.value.slice(0, this.maxLength);" maxlength="10">
                        <!--<small id="emailHelp" class="form-text text-muted">Cédula Física, Cédula Jurídica, NITE o DIMEX</small>-->
                    </div>
                    <div class="form-group">
                        <label for="emi_con_nc_test">consecutivoNC Test</label>
                        <input type="number" class="form-control" id="emi_con_nc_test" name="emi_con_nc_test" value="{{$emisor->consecutivoNCtest}}" oninput="javascript: if (this.value.length > this.maxLength) this.value = this.value.slice(0, this.maxLength);" maxlength="10">
                        <!--<small id="emailHelp" class="form-text text-muted">Cédula Física, Cédula Jurídica, NITE o DIMEX</small>-->
                    </div>
                    <div class="form-group">
                        <label for="emi_con_fee_test">consecutivoFEE Test</label>
                        <input type="number" class="form-control" id="emi_con_fee_test" name="emi_con_fee_test" value="{{$emisor->consecutivoFEEtest}}" oninput="javascript: if (this.value.length > this.maxLength) this.value = this.value.slice(0, this.maxLength);" maxlength="10">
                        <!--<small id="emailHelp" class="form-text text-muted">Cédula Física, Cédula Jurídica, NITE o DIMEX</small>-->
                    </div>
                    <div class="form-group" style="opacity: 0;">
                        <label for="emi_con_nc_test">SIN DEFINIR</label>
                        <input type="number" class="form-control" readonly>
                        <!--<small id="emailHelp" class="form-text text-muted">Cédula Física, Cédula Jurídica, NITE o DIMEX</small>-->
                    </div>


                    <!-- <div class="form-group" style="opacity: 0;">
                        <label for="emi_con_nc_test">SIN DEFINIR</label>
                        <input type="number" class="form-control" readonly>

                    </div> -->
                    <div class="form-group" style="margin-top: -20px;">
                        <label for="distrito_guardado">Distrito</label>
                        <input type="text" id="distrito_guardado" value="{{$emisor->distrito}}" class="form-control" readonly="readonly">
                    </div>

                    <div class="form-group" id="div_distrito">
                        <label for="distrito">Distrito</label>
                        <select class="form-control" id="distrito" name="distrito" disabled></select>
                    </div>
                    <div class="form-group" style="opacity: 0;">
                        <label for="emi_con_nc_test">SIN DEFINIR</label>
                        <input type="number" class="form-control" readonly>
                        <!--<small id="emailHelp" class="form-text text-muted">Cédula Física, Cédula Jurídica, NITE o DIMEX</small>-->
                    </div>
                    <div class="form-group" style="opacity: 0;">
                        <label for="emi_con_nc_test">SIN DEFINIR</label>
                        <input type="number" class="form-control" readonly>
                        <!--<small id="emailHelp" class="form-text text-muted">Cédula Física, Cédula Jurídica, NITE o DIMEX</small>-->
                    </div>
                    <br><br>
                    <div class="form-group" id="div_contrasena_smtp" style="margin-top: -30px;">
                        <label for="emi_con_stm_opc">Contraseña SMTP Opcional</label>
                        <input type="text" value="{{$emisor->contrasena_smtp_secundario}}" class="form-control" id="emi_con_stm_opc" name="emi_con_stm_opc" maxlength="50">
                        <!--<small id="emailHelp" class="form-text text-muted">Cédula Física, Cédula Jurídica, NITE o DIMEX</small>-->
                    </div>

                </div>
                <div class="col-md-3">

                    <div class="form-group">
                        <label for="correo">Correo Electrónico</label>
                        <input type="email" value="{{$emisor->correo}}" class="form-control" id="correo" name="correo" maxlength="40" required>
                    </div>
                    <div class="form-group">
                        <label for="emi_cre_dis">Créditos Disponibles</label>
                        <input type="number" class="form-control" value="{{$emisor->creditos_disponibles}}" id="emi_cre_dis" name="emi_cre_dis" oninput="javascript: if (this.value.length > this.maxLength) this.value = this.value.slice(0, this.maxLength);" maxlength="6" required>
                        <!--<small id="emailHelp" class="form-text text-muted">Cédula Física, Cédula Jurídica, NITE o DIMEX</small>-->
                    </div>
                    <div class="form-group" style="margin-top: 48px;">
                        @if($emisor->PDF==1)
                            <input id="emi_pdf" name="emi_pdf" class="checkbox-warning" type="checkbox" style="float: left;margin-right: 3px;" checked>
                        @endif
                        @if($emisor->PDF==0)
                            <input id="emi_pdf" name="emi_pdf" class="checkbox-warning" type="checkbox" style="float: left;margin-right: 3px;">
                        @endif
                        <label for="emi_pdf">Envío de PDF</label>
                    </div>
                    <div class="form-group" style="opacity: 0;">
                        <label for="emi_con_nc_test">SIN DEFINIR</label>
                        <input type="number" class="form-control" readonly>
                        <!--<small id="emailHelp" class="form-text text-muted">Cédula Física, Cédula Jurídica, NITE o DIMEX</small>-->
                    </div>
                    <div class="form-group" style="opacity: 0;">
                        <label for="emi_con_nc_test">SIN DEFINIR</label>
                        <input type="number" class="form-control" readonly>
                        <!--<small id="emailHelp" class="form-text text-muted">Cédula Física, Cédula Jurídica, NITE o DIMEX</small>-->
                    </div>

                    <div class="form-group" style="margin-top: -20px;">
                        <label for="emi_cer_atv">Certificado ATV Producción</label>
                        <input value="{{$emisor->certificado_atv_prod}}" type="text" class="form-control" id="emi_cer_atv" name="emi_cer_atv"  maxlength="80" required readonly>
                        <!--<small id="emailHelp" class="form-text text-muted">Cédula Física, Cédula Jurídica, NITE o DIMEX</small>-->
                    </div>

                    <div class="form-group" style="opacity: 0;">
                        <label for="emi_con_nc_test">SIN DEFINIR</label>
                        <input type="number" class="form-control" readonly>
                        <!--<small id="emailHelp" class="form-text text-muted">Cédula Física, Cédula Jurídica, NITE o DIMEX</small>-->
                    </div>


                    <div class="form-group">
                        <label for="emi_cer_atv_test">Certificado ATV Test</label>
                        <input type="text" value="{{$emisor->certificado_atv_test}}" class="form-control" id="emi_cer_atv_test" name="emi_cer_atv_test"  maxlength="80" disabled>
                        <!--<small id="emailHelp" class="form-text text-muted">Cédula Física, Cédula Jurídica, NITE o DIMEX</small>-->
                    </div>
                    <div class="form-group" style="opacity: 0;">
                        <label for="emi_con_nc_test">SIN DEFINIR</label>
                        <input type="number" class="form-control" readonly>
                        <!--<small id="emailHelp" class="form-text text-muted">Cédula Física, Cédula Jurídica, NITE o DIMEX</small>-->
                    </div>
                    <div class="form-group" style="opacity: 0;">
                        <label for="emi_con_nc_test">SIN DEFINIR</label>
                        <input type="number" class="form-control" readonly>
                        <!--<small id="emailHelp" class="form-text text-muted">Cédula Física, Cédula Jurídica, NITE o DIMEX</small>-->
                    </div>
                    <div class="form-group" style="margin-top: -20px;">
                        <label for="emi_con_nd_pro">consecutivoND Producción</label>
                        <input type="number" class="form-control" id="emi_con_nd_pro" name="emi_con_nd_pro" value="{{$emisor->consecutivoNDprod}}" oninput="javascript: if (this.value.length > this.maxLength) this.value = this.value.slice(0, this.maxLength);" maxlength="10" required>
                        <!--<small id="emailHelp" class="form-text text-muted">Cédula Física, Cédula Jurídica, NITE o DIMEX</small>-->
                    </div>
                    <div class="form-group" style="opacity: 0;">
                        <label for="emi_con_nc_test">SIN DEFINIR</label>
                        <input type="number" class="form-control" readonly>
                        <!--<small id="emailHelp" class="form-text text-muted">Cédula Física, Cédula Jurídica, NITE o DIMEX</small>-->
                    </div>
                    <div class="form-group">
                        <label for="emi_con_nd_tes">consecutivoND Test</label>
                        <input type="number" class="form-control" id="emi_con_nd_tes" name="emi_con_nd_tes" value="{{$emisor->consecutivoNDtest}}" oninput="javascript: if (this.value.length > this.maxLength) this.value = this.value.slice(0, this.maxLength);" maxlength="10">
                        <!--<small id="emailHelp" class="form-text text-muted">Cédula Física, Cédula Jurídica, NITE o DIMEX</small>-->
                    </div>
                    <div class="form-group" style="opacity: 0;">
                        <label for="emi_con_nc_test">SIN DEFINIR</label>
                        <input type="number" class="form-control" readonly>
                        <!--<small id="emailHelp" class="form-text text-muted">Cédula Física, Cédula Jurídica, NITE o DIMEX</small>-->
                    </div>

                    <div class="form-group" style="opacity: 0;">
                        <label for="emi_con_nc_test">SIN DEFINIR</label>
                        <input type="number" class="form-control" readonly>
                        <!--<small id="emailHelp" class="form-text text-muted">Cédula Física, Cédula Jurídica, NITE o DIMEX</small>-->
                    </div>


                    <!--<div class="form-group" style="opacity: 0;">
                        <label for="emi_con_nc_test">SIN DEFINIR</label>
                        <input type="number" class="form-control" readonly>

                    </div>-->

                    <div class="form-group" style="margin-top: -20px;">
                        <label for="barrio_guardado">Barrio</label>
                        <input type="text" id="barrio_guardado" value="{{$emisor->barrio}}" class="form-control" readonly="readonly">
                    </div>
                    <div class="form-group" id="div_barrio">
                        <label for="barrio">Barrio</label>
                        <select class="form-control" id="barrio" name="barrio" disabled></select>
                    </div>
                    <div class="form-group" style="opacity: 0;">
                        <label for="emi_con_nc_test">SIN DEFINIR</label>
                        <input type="number" class="form-control" readonly>
                        <!--<small id="emailHelp" class="form-text text-muted">Cédula Física, Cédula Jurídica, NITE o DIMEX</small>-->
                    </div>
                    <div class="form-group" style="opacity: 0;">
                        <label for="emi_con_nc_test">SIN DEFINIR</label>
                        <input type="number" class="form-control" readonly>
                        <!--<small id="emailHelp" class="form-text text-muted">Cédula Física, Cédula Jurídica, NITE o DIMEX</small>-->
                    </div>
                    <br><br>
                    <div class="form-group" id="div_metodo_smtp" style="margin-top: -30px;">
                        <label for="emi_met_smt_opc">Método SMTP Opcional</label>
                        <input  value="{{$emisor->metodo_smtp_secundario}}" type="text" class="form-control" id="emi_met_smt_opc" name="emi_met_smt_opc" maxlength="8">
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
<script>
    $(document).ready(function(){
        $("#emi_tp_ide").val('{{$emisor->id_tpidentificacion}}');
        esconderUbicaciones();
        esconderParametrosSmtpOpcionales();
    });
    $( "#btn_api_key" ).click(function(event) {
        $.ajax({
            type: "GET",
            url: "/getApiKey",
            beforeSend: function(objeto){
                //$("#resultados_ajax2").html("Mensaje: Cargando...");
            },
            success: function(datos){
                var response=JSON.parse(datos);
                $("#emi_api_key").val(response.key);


            }
        });
        event.preventDefault();
    });
    $("#desbloquear_ubicacion").click(function (e) {
        var provincia = document.getElementById("div_provincia");
        var canton = document.getElementById("div_canton");
        var distrito = document.getElementById("div_distrito");
        var barrio = document.getElementById("div_barrio");
        if (provincia.style.display === "none") {
            provincia.style.display = "block";
        } else {
            provincia.style.display = "none";
        }
        if (canton.style.display === "none") {
            canton.style.display = "block";
        } else {
            canton.style.display = "none";
        }
        if (distrito.style.display === "none") {
            distrito.style.display = "block";
        } else {
            distrito.style.display = "none";
        }
        if (barrio.style.display === "none") {
            barrio.style.display = "block";
        } else {
            barrio.style.display = "none";
        }

    });

    $("#desbloquear_smtp_opcional").click(function (e) {
        var host = document.getElementById("div_host");
        var usuario_smtp = document.getElementById("div_usuario_smtp");
        var contrasena_smtp = document.getElementById("div_contrasena_smtp");
        var metodo_smtp = document.getElementById("div_metodo_smtp");
        var puerto_smtp = document.getElementById("div_puerto_smtp");
        var espacio_arriba_canton = document.getElementById("div_arriba_canton");

        if (host.style.display === "none") {
            host.style.display = "block";
        } else {
            host.style.display = "none";
        }
        if (usuario_smtp.style.display === "none") {
            usuario_smtp.style.display = "block";
        } else {
            usuario_smtp.style.display = "none";
        }
        if (contrasena_smtp.style.display === "none") {
            contrasena_smtp.style.display = "block";
        } else {
            contrasena_smtp.style.display = "none";
        }
        if (metodo_smtp.style.display === "none") {
            metodo_smtp.style.display = "block";
        } else {
            metodo_smtp.style.display = "none";
        }
        if (puerto_smtp.style.display === "none") {
            puerto_smtp.style.display = "block";
        } else {
            puerto_smtp.style.display = "none";
        }
        if (espacio_arriba_canton.style.display === "none") {
            espacio_arriba_canton.style.display = "block";
        } else {
            espacio_arriba_canton.style.display = "none";
        }

    });


    function esconderUbicaciones()
    {
        $("#provincia").parent().hide();
        $("#canton").parent().hide();
        $("#distrito").parent().hide();
        $("#barrio").parent().hide();
    }
    function esconderParametrosSmtpOpcionales()
    {
        $("#emi_hos_smt_opc").parent().hide();
        $("#emi_usu_smt_opc").parent().hide();
        $("#emi_con_stm_opc").parent().hide();
        $("#emi_met_smt_opc").parent().hide();
        $("#emi_pue_smt").parent().hide();



    }
    $('#btn_resetear').click(function(){
        $('#form-emisor')[0].reset();
    });


</script>
<script src="/js/validaFormAgregCliente.js"></script>



