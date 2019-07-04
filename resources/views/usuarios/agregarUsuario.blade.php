<?php
/**
 * Created by PhpStorm.
 * User: josue
 * Date: 18/03/19
 * Time: 02:28 PM
 */
?>

    <!DOCTYPE html>
<html lang="en">
@include('head')

<body>
@include('navbar')

 <div class="container">
        <div class="row justify-content-center" style="margin-top: 5%;">
            <div class="col-md-12">
                        <form method="POST" action="{{ route('register') }}">
                            @csrf

                            <div class="col-md-6 col-md-offset-3">
                                <label for="name">{{ __('Nombre') }}</label>
                                <div class="form-group">
                                    @if ($errors->has('name'))
                                        <span class="invalid-feedback" role="alert">
                                        <strong>{{ $errors->first('name') }}</strong>
                                    </span>
                                    @endif
                                    <input id="name" type="text" class="form-control{{ $errors->has('name') ? ' is-invalid' : '' }}" name="name" value="{{ old('name') }}" maxlength="45" required autofocus>
                                </div>
                            </div>

                            <div class="col-md-6 col-md-offset-3">
                                <label for="email">{{ __('Correo Electrónico') }}</label>
                                <div class="form-group">
                                    @if ($errors->has('email'))
                                        <span class="invalid-feedback" role="alert">
                                        <strong>{{ $errors->first('email') }}</strong>
                                    </span>
                                    @endif
                                        <input id="email" type="email" class="form-control{{ $errors->has('email') ? ' is-invalid' : '' }}" name="email" value="{{ old('email') }}" maxlength="99" required>
                                </div>
                            </div>

                            <div class="col-md-6 col-md-offset-3">
                                <label for="password">{{ __('Contraseña') }}</label>
                                <div class="form-group">
                                    @if ($errors->has('password'))
                                        <span class="invalid-feedback" role="alert">
                                        <strong><?php if($errors->first('password')=='The password must be at least 6 characters.'){echo "La contraseña debe ser mínimo de 6 caracteres";}?></strong>
                                    </span>
                                    @endif
                                        <input id="password" type="password" class="form-control{{ $errors->has('password') ? ' is-invalid' : '' }}" name="password" maxlength="99" required>
                                </div>
                            </div>

                            <div class="col-md-6 col-md-offset-3">
                                <label for="password-confirm" >{{ __('Confirmar Contraseña') }}</label>
                                <div class="form-group">
                                    <input id="password-confirm" type="password" class="form-control" name="password_confirmation" maxlength="99" required>
                                </div>
                            </div>
                            <div class="col-md-6 col-md-offset-3">
                                <div class="form-group">
                                    <label for="rol">Tipo de Rol</label>
                                    <select class="form-control" id="rol" name="rol">
                                        <option value="1">Administrador</option>
                                        <option value="2">API</option>
                                    </select>
                                </div>
                            </div>

                            <div class="col-md-6 col-md-offset-3" style="margin-bottom: 90px;">
                                <div class="form-group">
                                    <button type="submit" class="btn btn-block btn-danger">
                                        {{ __('Registrar') }}
                                    </button>
                                </div>
                            </div>
                        </form>

            </div>
        </div>
    </div>



</body>
@include('footer')
<script src="../js/bootstrap.min.js"></script>

<script src="{{asset('../js/validaFormAgregCliente.js')}}" rel="javascript" type="text/javascript"></script>




