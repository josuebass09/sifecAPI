<?php
$msj=0;
?>
    <!DOCTYPE html>
<html lang="en">
@include('head')
<body>

<nav class="navbar navbar-default" style="background-color: #d9534f;">
    <div class="container">
        <div class="navbar-header">
            <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#navbar" aria-expanded="false" aria-controls="navbar">
                <span class="sr-only">Toggle navigation</span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
            </button>

        </div>
        <div id="navbar" class="collapse navbar-collapse">

        </div><!--/.nav-collapse -->
    </div>
</nav>

<section id="main">
    <div class="container container-login">
        <div class="row row-login">
            <div class="col-sm-4 col-sm-offset-4 div-login">
                <form method="POST" action="{{ route('login') }}">
                    <div class="form-group">
                        <label>Correo Electrónico</label>
                        <input id="email" type="email" class="form-control{{ $errors->has('email') ? ' is-invalid' : '' }}" name="email" value="{{ old('email') }}" required autofocus>
                    </div>
                    <div class="form-group">
                        <label>Contraseña</label>
                        <input id="password" type="password" class="form-control{{ $errors->has('password') ? ' is-invalid' : '' }}" name="contrasena" required>
                    </div>
                    <button type="submit" class="btn btn-block btn-danger"><strong>Entrar</strong></button>
                </form>
                @if (session('http_log')==401)
                    <div class="alert alert-danger">
                    <strong>{{session('mensaje_log')}}</strong>
                    </div>
                @endif
            </div>
        </div>
    </div>
</section>
@include('footer')
</body>
</html>

