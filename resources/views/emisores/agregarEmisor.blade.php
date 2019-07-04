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
        @if ($errors->any())
            <h2 class="alert alert-danger col-md-12" style="display: inline-block;">Error al procesar el formulario</h2><br>
            <ul>{!! implode('', $errors->all('<li style="color:red">:message</li>')) !!}</ul>
        @endif
        </div>
        <div class="row">
                <div class="col-md-12" style="margin-top: 15px;">
                    <form action="{{route('crearEmisor') }}" method="POST" enctype="multipart/form-data" id="form-emisor" style="margin-top: -20px;">
                        <input name="_token" type="hidden" value="{{ csrf_token() }}"/>
                        <div class="form-group">
                            <h2 style="margin-left: 15px;">Datos Personales</h2>
                        </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="emi_tp_ide">Tipo de Identificación</label>
                            <select class="form-control" id="emi_tp_ide" name="emi_tp_ide" required>
                                <option value="01" selected>Cédula Física</option>
                                <option value="02">Cédula Jurídica</option>
                                <option value="03">DIMEX</option>
                                <option value="04">NITE</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="emi_otr_sen">Otras Señas</label>
                            <input type="text" class="form-control" id="emi_otr_sen" name="emi_otr_sen"  maxlength="250" required>
                            <!--<small id="emailHelp" class="form-text text-muted">Cédula Física, Cédula Jurídica, NITE o DIMEX</small>-->
                        </div>

                        <div class="form-group">
                            <label for="emi_ven_pla">Vencimiento del Plan</label>
                            <input value="2019-03-19" type="date" class="form-control" id="emi_ven_pla" name="emi_ven_pla" maxlength="10" required>
                            <!--<small id="emailHelp" class="form-text text-muted">Cédula Física, Cédula Jurídica, NITE o DIMEX</small>-->
                        </div>
                        <div class="form-group" style="margin-top: 48px;">
                            <input id="emi_pdf" name="emi_pdf" class="checkbox-warning" type="checkbox" style="float: left;margin-right: 3px;" checked>
                            <label for="emi_pdf">Envío de PDF
                            </label>
                        </div>

                        <div class="form-group">
                            <h2>Datos ATV</h2>
                            <!--<small id="emailHelp" class="form-text text-muted">Cédula Física, Cédula Jurídica, NITE o DIMEX</small>-->
                        </div>
                        <br>
                        <div class="form-group">
                            <label for="emi_usu_atv">Usuario ATV Producción</label>
                            <input type="text" class="form-control" id="emi_usu_atv" name="emi_usu_atv"  maxlength="254" required>
                            <!--<small id="emailHelp" class="form-text text-muted">Cédula Física, Cédula Jurídica, NITE o DIMEX</small>-->
                        </div>
                        <div class="form-group">
                            <label for="emi_usu_atv_tes">Usuario ATV Test</label>
                            <input type="text" class="form-control" id="emi_usu_atv_tes" name="emi_usu_atv_tes"  maxlength="254">
                            <!--<small id="emailHelp" class="form-text text-muted">Cédula Física, Cédula Jurídica, NITE o DIMEX</small>-->
                        </div>


                        <div class="form-group">
                            <h2>Consecutivos</h2>
                            <!--<small id="emailHelp" class="form-text text-muted">Cédula Física, Cédula Jurídica, NITE o DIMEX</small>-->
                        </div>
                        <br>
                        <div class="form-group">
                            <label for="emi_con_fe_pro">consecutivoFE Producción</label>
                            <input type="number" class="form-control" id="emi_con_fe_pro" name="emi_con_fe_pro" value="0" oninput="javascript: if (this.value.length > this.maxLength) this.value = this.value.slice(0, this.maxLength);" maxlength="10" required>
                            <!--<small id="emailHelp" class="form-text text-muted">Cédula Física, Cédula Jurídica, NITE o DIMEX</small>-->
                        </div>
                        <div class="form-group">
                            <label for="emi_con_ga_pro">consecutivoGA Producción</label>
                            <input type="number" class="form-control" id="emi_con_ga_pro" name="emi_con_ga_pro" value="0" oninput="javascript: if (this.value.length > this.maxLength) this.value = this.value.slice(0, this.maxLength);" maxlength="10" required>
                            <!--<small id="emailHelp" class="form-text text-muted">Cédula Física, Cédula Jurídica, NITE o DIMEX</small>-->
                        </div>
                        <div class="form-group">
                            <label for="emi_con_nd_tes">consecutivoND Test</label>
                            <input type="number" class="form-control" id="emi_con_nd_tes" name="emi_con_nd_tes" value="0" oninput="javascript: if (this.value.length > this.maxLength) this.value = this.value.slice(0, this.maxLength);" maxlength="10">
                            <!--<small id="emailHelp" class="form-text text-muted">Cédula Física, Cédula Jurídica, NITE o DIMEX</small>-->
                        </div>

                        <div class="form-group">
                            <h2>Ubicación</h2>
                            <!--<small id="emailHelp" class="form-text text-muted">Cédula Física, Cédula Jurídica, NITE o DIMEX</small>-->
                        </div>
                        <br>
                        <div class="form-group">
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
                            <input id="desbloquear_smtp_opcional" name="desbloquear_smtp_opcional" class="checkbox-warning" type="checkbox" style="float: left;margin-right: 3px;">
                            <label style="margin-top: 2px;">
                                <h2>SMTP Opcional</h2>
                            </label>
                        </div>
                        <br>
                        <div class="form-group" id="div_host">
                            <label for="emi_hos_smt_opc">Host SMTP Opcional</label>
                            <input type="text" class="form-control" id="emi_hos_smt_opc" name="emi_hos_smt_opc" maxlength="50">
                            <!--<small id="emailHelp" class="form-text text-muted">Cédula Física, Cédula Jurídica, NITE o DIMEX</small>-->
                        </div>
                        <div class="form-group" id="div_contrasena_smtp">
                            <label for="emi_con_stm_opc">Contraseña SMTP Opcional</label>
                            <input type="text" class="form-control" id="emi_con_stm_opc" name="emi_con_stm_opc" maxlength="50">
                            <!--<small id="emailHelp" class="form-text text-muted">Cédula Física, Cédula Jurídica, NITE o DIMEX</small>-->
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="id">ID Fiscal</label>
                            <input  class="form-control quitarFlechas" id="id" name="id" oninput="javascript: if (this.value.length > this.maxLength) this.value = this.value.slice(0, this.maxLength);" type = "number" maxlength = "12" required>

                        </div>
                        <div class="form-group">
                            <label for="emi_nom_com">Nombre Comercial</label>
                            <input type="text" class="form-control" id="emi_nom_com" name="emi_nom_com" maxlength="80">
                            <!--<small id="emailHelp" class="form-text text-muted">Cédula Física, Cédula Jurídica, NITE o DIMEX</small>-->
                        </div>
                        <div class="form-group">
                            <label for="emi_logo">Logo</label><small id="emailHelp" class="form-text text-muted" style="margin-left: 5px;color: red;"><i class="glyphicon glyphicon-info-sign" title="La resolución debe ser IGUAL a 96x96"></i></small>
                            <input class="form-control" type="file" id="emi_logo" name="emi_logo"/>
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
                        <div class="form-group">
                            <label for="emi_con_atv">Contraseña ATV Producción</label>
                            <input  type="text" class="form-control" id="emi_con_atv" name="emi_con_atv" maxlength="254" required>
                        </div>
                        <div class="form-group">
                            <label for="emi_con_atv_test">Contraseña ATV Test</label>
                            <input type="text" class="form-control" id="emi_con_atv_test" name="emi_con_atv_test" maxlength="254">
                        </div>




                        <div class="form-group" style="opacity: 0;">
                            <label for="emi_con_nc_test">SIN DEFINIR</label>
                            <input type="number" class="form-control" readonly>
                            <!--<small id="emailHelp" class="form-text text-muted">Cédula Física, Cédula Jurídica, NITE o DIMEX</small>-->
                        </div>
                        <div class="form-group">
                            <label for="emi_con_te_pro">consecutivoTE Producción</label>
                            <input type="number" class="form-control" id="emi_con_te_pro" name="emi_con_te_pro" value="0" oninput="javascript: if (this.value.length > this.maxLength) this.value = this.value.slice(0, this.maxLength);" maxlength="10" required>
                            <!--<small id="emailHelp" class="form-text text-muted">Cédula Física, Cédula Jurídica, NITE o DIMEX</small>-->
                        </div>
                        <div class="form-group">
                            <label for="emi_con_fe_tes">consecutivoFE Test</label>
                            <input type="number" class="form-control" id="emi_con_fe_tes" name="emi_con_fe_tes" value="0" oninput="javascript: if (this.value.length > this.maxLength) this.value = this.value.slice(0, this.maxLength);" maxlength="10">
                            <!--<small id="emailHelp" class="form-text text-muted">Cédula Física, Cédula Jurídica, NITE o DIMEX</small>-->
                        </div>
                        <div class="form-group">
                            <label for="emi_con_ga_tes">consecutivoGA Test</label>
                            <input type="number" class="form-control" id="emi_con_ga_tes" name="emi_con_ga_tes" value="0" oninput="javascript: if (this.value.length > this.maxLength) this.value = this.value.slice(0, this.maxLength);" maxlength="10">
                            <!--<small id="emailHelp" class="form-text text-muted">Cédula Física, Cédula Jurídica, NITE o DIMEX</small>-->
                        </div>


                        <div class="form-group" style="opacity: 0;">
                            <label for="emi_con_nc_test">SIN DEFINIR</label>
                            <input type="number" class="form-control" readonly>
                            <!--<small id="emailHelp" class="form-text text-muted">Cédula Física, Cédula Jurídica, NITE o DIMEX</small>-->
                        </div>

                        <div class="form-group">
                            <label for="canton">Cantón</label>
                            <select class="form-control" id="canton" name="canton" disabled></select>
                        </div>
                        <div class="form-group" style="opacity: 0;">
                            <label for="emi_con_nc_test">SIN DEFINIR</label>
                            <input type="number" class="form-control" readonly>
                            <!--<small id="emailHelp" class="form-text text-muted">Cédula Física, Cédula Jurídica, NITE o DIMEX</small>-->
                        </div>
                        <br>
                        <div class="form-group" style="margin-top: -7.5px;" id="div_usuario_smtp">
                            <label for="emi_usu_smt_opc">Usuario SMTP Opcional</label>
                            <input type="text" class="form-control" id="emi_usu_smt_opc" name="emi_usu_smt_opc" value="0" maxlength="50">
                            <!--<small id="emailHelp" class="form-text text-muted">Cédula Física, Cédula Jurídica, NITE o DIMEX</small>-->
                        </div>

                    </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="emi_raz_soc">Razón Social</label>
                                <input  type="text" class="form-control" id="emi_raz_soc" name="emi_raz_soc" maxlength="100" required>
                                <!--<small id="emailHelp" class="form-text text-muted">Cédula Física, Cédula Jurídica, NITE o DIMEX</small>-->
                            </div>
                            <div class="form-group">
                                <label for="emi_cre_usa">Créditos Usados</label>
                                <input  type="number" class="form-control" value="0" id="emi_cre_usa" name="emi_cre_usa" oninput="javascript: if (this.value.length > this.maxLength) this.value = this.value.slice(0, this.maxLength);" maxlength="6" required>
                                <!--<small id="emailHelp" class="form-text text-muted">Cédula Física, Cédula Jurídica, NITE o DIMEX</small>-->
                            </div>
                            <div class="form-group">
                                <label for="emi_telefono">Teléfono</label>
                                <input type="number" class="form-control" value="" id="emi_telefono" name="emi_telefono" oninput="javascript: if (this.value.length > this.maxLength) this.value = this.value.slice(0, this.maxLength);" maxlength="20">
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
                            <div class="form-group">
                                <label for="emi_pin_atv">PIN ATV Producción</label>
                                <input  type="number" class="form-control" id="emi_pin_atv" name="emi_pin_atv"  oninput="javascript: if (this.value.length > this.maxLength) this.value = this.value.slice(0, this.maxLength);" maxlength="4" required>
                                <!--<small id="emailHelp" class="form-text text-muted">Cédula Física, Cédula Jurídica, NITE o DIMEX</small>-->
                            </div>
                            <div class="form-group">
                                <label for="emi_pin_atv_test">PIN ATV Test</label>
                                <input type="number" class="form-control" id="emi_pin_atv_test" name="emi_pin_atv_test"  oninput="javascript: if (this.value.length > this.maxLength) this.value = this.value.slice(0, this.maxLength);" maxlength="4">
                                <!--<small id="emailHelp" class="form-text text-muted">Cédula Física, Cédula Jurídica, NITE o DIMEX</small>-->
                            </div>


                            <div class="form-group" style="opacity: 0;">
                                <label for="emi_con_nc_test">SIN DEFINIR</label>
                                <input type="number" class="form-control" readonly>
                                <!--<small id="emailHelp" class="form-text text-muted">Cédula Física, Cédula Jurídica, NITE o DIMEX</small>-->
                            </div>
                            <div class="form-group">
                                <label for="emi_con_nc_pro">consecutivoNC Producción</label>
                                <input type="number" class="form-control" id="emi_con_nc_pro" name="emi_con_nc_pro" value="0" oninput="javascript: if (this.value.length > this.maxLength) this.value = this.value.slice(0, this.maxLength);" maxlength="10" required>
                                <!--<small id="emailHelp" class="form-text text-muted">Cédula Física, Cédula Jurídica, NITE o DIMEX</small>-->
                            </div>
                            <div class="form-group">
                                <label for="emi_con_te_tes">consecutivoTE Test</label>
                                <input type="number" class="form-control" id="emi_con_te_tes" name="emi_con_te_tes" value="0" oninput="javascript: if (this.value.length > this.maxLength) this.value = this.value.slice(0, this.maxLength);" maxlength="10">
                                <!--<small id="emailHelp" class="form-text text-muted">Cédula Física, Cédula Jurídica, NITE o DIMEX</small>-->
                            </div>


                            <div class="form-group">
                                <label for="emi_con_fec_pro">consecutivoFEC Producción</label>
                                <input type="number" class="form-control" id="emi_con_fec_pro" name="emi_con_fec_pro" value="0" oninput="javascript: if (this.value.length > this.maxLength) this.value = this.value.slice(0, this.maxLength);" maxlength="10">
                                <!--<small id="emailHelp" class="form-text text-muted">Cédula Física, Cédula Jurídica, NITE o DIMEX</small>-->
                            </div>
                            <div class="form-group">
                                <label for="emi_con_fec_test">consecutivoFEC Test</label>
                                <input type="number" class="form-control" id="emi_con_fec_test" name="emi_con_fec_test" value="0" oninput="javascript: if (this.value.length > this.maxLength) this.value = this.value.slice(0, this.maxLength);" maxlength="10">
                                <!--<small id="emailHelp" class="form-text text-muted">Cédula Física, Cédula Jurídica, NITE o DIMEX</small>-->
                            </div>
                            <div class="form-group">
                                <label for="distrito">Distrito</label>
                                <select class="form-control" id="distrito" name="distrito" disabled></select>
                            </div>
                            <div class="form-group" style="opacity: 0;">
                                <label for="emi_con_nc_test">SIN DEFINIR</label>
                                <input type="number" class="form-control" readonly>
                                <!--<small id="emailHelp" class="form-text text-muted">Cédula Física, Cédula Jurídica, NITE o DIMEX</small>-->
                            </div>
                            <br>
                            <div class="form-group" style="margin-top: -7.5px;" id="div_metodo_smtp">
                                <label for="emi_met_smt_opc">Método SMTP Opcional</label>
                                <input  type="text" class="form-control" id="emi_met_smt_opc" name="emi_met_smt_opc" maxlength="8">
                                <!--<small id="emailHelp" class="form-text text-muted">Cédula Física, Cédula Jurídica, NITE o DIMEX</small>-->
                            </div>

                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="correo">Correo Electrónico</label>
                                <input type="email" class="form-control" id="correo" name="correo" maxlength="40" required>
                            </div>
                            <div class="form-group">
                                <label for="emi_cre_dis">Créditos Disponibles</label>
                                <input type="number" class="form-control" value="0" id="emi_cre_dis" name="emi_cre_dis" oninput="javascript: if (this.value.length > this.maxLength) this.value = this.value.slice(0, this.maxLength);" maxlength="6" required>
                                <!--<small id="emailHelp" class="form-text text-muted">Cédula Física, Cédula Jurídica, NITE o DIMEX</small>-->
                            </div>
                            <div class="form-group">
                                <label for="emi_fax">Fax</label>
                                <input type="number" class="form-control" value="" id="emi_fax" name="emi_fax" oninput="javascript: if (this.value.length > this.maxLength) this.value = this.value.slice(0, this.maxLength);" maxlength="20">
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
                            <div class="form-group">
                                <label class="form-check-label" for="myfile">Llave Criptográfica Producción</label>
                                <input class="form-control" type="file" id="myfile" name="myfile" required/>
                            </div>
                            <div class="form-group">
                                <label class="form-check-label" for="myfile2">Llave Criptográfica Test</label>
                                <input class="form-control" type="file" id="myfile2" name="myfile2"/>
                            </div>
                            <div class="form-group" style="opacity: 0;">
                                <label for="emi_con_nc_test">SIN DEFINIR</label>
                                <input type="number" class="form-control" readonly>
                                <!--<small id="emailHelp" class="form-text text-muted">Cédula Física, Cédula Jurídica, NITE o DIMEX</small>-->
                            </div>
                            <div class="form-group">
                                <label for="emi_con_nd_pro">consecutivoND Producción</label>
                                <input type="number" class="form-control" id="emi_con_nd_pro" name="emi_con_nd_pro" value="0" oninput="javascript: if (this.value.length > this.maxLength) this.value = this.value.slice(0, this.maxLength);" maxlength="10" required>
                                <!--<small id="emailHelp" class="form-text text-muted">Cédula Física, Cédula Jurídica, NITE o DIMEX</small>-->
                            </div>
                            <div class="form-group">
                                <label for="emi_con_nc_test">consecutivoNC Test</label>
                                <input type="number" class="form-control" id="emi_con_nc_test" name="emi_con_nc_test" value="0" oninput="javascript: if (this.value.length > this.maxLength) this.value = this.value.slice(0, this.maxLength);" maxlength="10">
                                <!--<small id="emailHelp" class="form-text text-muted">Cédula Física, Cédula Jurídica, NITE o DIMEX</small>-->
                            </div>


                            <div class="form-group">
                                <label for="emi_con_fee_pro">consecutivoFEE Producción</label>
                                <input type="number" class="form-control" id="emi_con_fee_pro" name="emi_con_fee_pro" value="0" oninput="javascript: if (this.value.length > this.maxLength) this.value = this.value.slice(0, this.maxLength);" maxlength="10">
                                <!--<small id="emailHelp" class="form-text text-muted">Cédula Física, Cédula Jurídica, NITE o DIMEX</small>-->
                            </div>
                            <div class="form-group">
                                <label for="emi_con_fee_test">consecutivoFEE Test</label>
                                <input type="number" class="form-control" id="emi_con_fee_test" name="emi_con_fee_test" value="0" oninput="javascript: if (this.value.length > this.maxLength) this.value = this.value.slice(0, this.maxLength);" maxlength="10">
                                <!--<small id="emailHelp" class="form-text text-muted">Cédula Física, Cédula Jurídica, NITE o DIMEX</small>-->
                            </div>
                            <div class="form-group">
                                <label for="barrio">Barrio</label>
                                <select class="form-control" id="barrio" name="barrio" disabled></select>
                            </div>
                            <div class="form-group" style="opacity: 0;">
                                <label for="emi_con_nc_test">SIN DEFINIR</label>
                                <input type="number" class="form-control" readonly>
                                <!--<small id="emailHelp" class="form-text text-muted">Cédula Física, Cédula Jurídica, NITE o DIMEX</small>-->
                            </div>
                            <br>
                            <div class="form-group" style="margin-top: -7.5px;" id="div_puerto_smtp">
                                <label for="emi_pue_smt">Puerto SMTP Opcional</label>
                                <input type="number" class="form-control" id="emi_pue_smt" name="emi_pue_smt" oninput="javascript: if (this.value.length > this.maxLength) this.value = this.value.slice(0, this.maxLength);" maxlength="5">
                                <!--<small id="emailHelp" class="form-text text-muted">Cédula Física, Cédula Jurídica, NITE o DIMEX</small>-->
                            </div>

                        </div>

                            <div class="row" style="display: block;">
                                <div class="col-md-12" style="margin-bottom: 75px;margin-top: 25px;text-align: center;">
                                    <button type="submit" class="btn btn-primary"><span class="glyphicon glyphicon-save"></span>Guardar</button>
                                    <a type="reset" class="btn btn-default"><span class="glyphicon glyphicon-remove-sign" style="margin-right: 9px;"></span>Resetear</a>
                                </div>
                            </div>
                    </form>
            </div>
        </div>

</section>

</body>
@include('footer')
<script src="../js/bootstrap.min.js"></script>

<script src="{{asset('../js/validaFormAgregCliente.js')}}" rel="javascript" type="text/javascript"></script>
<script>
    $(document).ready(function(){
        esconderParametrosSmtpOpcionales();
    });
    $("#desbloquear_smtp_opcional").click(function (e) {
        var host = document.getElementById("div_host");
        var usuario_smtp = document.getElementById("div_usuario_smtp");
        var contrasena_smtp = document.getElementById("div_contrasena_smtp");
        var metodo_smtp = document.getElementById("div_metodo_smtp");
        var puerto_smtp = document.getElementById("div_puerto_smtp");
        //var espacio_arriba_canton = document.getElementById("div_arriba_canton");

        if (host.style.display === "none") {
            host.style.display = "block";
            $("#emi_hos_smt_opc").prop('required',true);
        } else {
            host.style.display = "none";
            $("#emi_hos_smt_opc").prop('required',false);
        }
        if (usuario_smtp.style.display === "none") {
            usuario_smtp.style.display = "block";
            $("#emi_usu_smt_opc").prop('required',true);
        } else {
            usuario_smtp.style.display = "none";
            $("#emi_usu_smt_opc").prop('required',false);
        }
        if (contrasena_smtp.style.display === "none") {
            contrasena_smtp.style.display = "block";
            $("#emi_con_stm_opc").prop('required',true);
        } else {
            contrasena_smtp.style.display = "none";
            $("#emi_con_stm_opc").prop('required',false);
        }
        if (metodo_smtp.style.display === "none") {
            metodo_smtp.style.display = "block";
            $("#emi_met_smt_opc").prop('required',true);
        } else {
            metodo_smtp.style.display = "none";
            $("#emi_met_smt_opc").prop('required',false);
        }
        if (puerto_smtp.style.display === "none") {
            puerto_smtp.style.display = "block";
            $("#emi_pue_smt").prop('required',true);
        } else {
            puerto_smtp.style.display = "none";
            $("#emi_pue_smt").prop('required',false);
        }


    });
    function esconderParametrosSmtpOpcionales()
    {
        $("#emi_hos_smt_opc").parent().hide();
        $("#emi_usu_smt_opc").parent().hide();
        $("#emi_con_stm_opc").parent().hide();
        $("#emi_met_smt_opc").parent().hide();
        $("#emi_pue_smt").parent().hide();
    }
</script>



