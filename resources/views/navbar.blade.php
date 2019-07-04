<nav class="navbar navbar-default">
    <div class="container-fluid">
        <div class="navbar-header">
            <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#navbar" aria-expanded="false" aria-controls="navbar">
                <span class="sr-only">Toggle navigation</span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
            </button>

        </div>
        <div id="navbar" class="collapse navbar-collapse">
            <ul class="nav navbar-nav" id="menu">
                <li><a href="/home">Inicio</a></li>
                <li><a href="/emisores">Emisores</a></li>
                <li><a href="/api">API</a></li>
                <li><a href="/usuarios">Usuarios</a></li>
                <li><a href="/parametros">Parámetros</a></li>
            </ul>
            <ul class="nav navbar-nav navbar-right">
                <li><a href="#">¡Bienvenido <strong><?php echo Auth::user()->name?></strong>!</a></li>
                <li><a class="dropdown-item" href="{{ route('logout') }}"
                       onclick="event.preventDefault();
                                                     document.getElementById('logout-form').submit();">
                        {{ __('Cerrar Sesión') }}
                    </a>

                    <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
                        @csrf
                    </form>

            </ul>
        </div>
    </div>
</nav>
<header id="header">
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-12">
                    @if($modulo=='Emisores')
                        <h1><span class="glyphicon glyphicon-user" aria-hidden="true"></span> <?php echo "Emisores"?></h1>
                    @endif
                    @if($modulo=='Inicio')
                        <h1><span class="glyphicon glyphicon-th-large" aria-hidden="true"></span> <?php echo "Inicio"?></h1>
                    @endif
                        @if($modulo=='AgregarEmisor')
                            <h1><span class="glyphicon glyphicon-plus-sign" aria-hidden="true"></span> <?php echo "Agregar Emisor"?></h1>
                        @endif
                        @if($modulo=='VerEmisor')
                            <h1><span class="glyphicon glyphicon-edit" aria-hidden="true"></span> <?php echo "Información de Emisor"?></h1>
                        @endif
                        @if($modulo=='Usuarios')
                            <h1><span class="glyphicon glyphicon-user" aria-hidden="true"></span> <?php echo "Usuarios del sistema"?></h1>
                        @endif
                        @if($modulo=='AgregarUsuario')
                            <h1><span class="glyphicon glyphicon-plus-sign" aria-hidden="true"></span> <?php echo "Agregar Usuario"?></h1>
                        @endif
                        @if($modulo=='VerUsuario')
                            <h1><span class="glyphicon glyphicon-edit" aria-hidden="true"></span> <?php echo "Información de Usuario"?></h1>
                        @endif
                        @if($modulo=='Parametros')
                            <h1><span class="glyphicon glyphicon-wrench" aria-hidden="true"></span> <?php echo "Parámetros del Sistema"?></h1>
                        @endif
                        @if($modulo=='API')
                            <h1><span class="glyphicon glyphicon-cog" aria-hidden="true"></span> <?php echo "API"?></h1>
                        @endif
            </div>

        </div>
    </div>
</header>

