<?php

namespace App\Http\Controllers;

use App\Clases\Parametro;
use Illuminate\Http\Request;

class ParametroController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }
    public function index()
    {
        return view('parametros',['modulo'=>'Parametros']);
    }

    public function save(Request $request)
    {
        try{
            $parametro=new Parametro($request->post('par_host_smtp'),$request->post('par_usuario_smtp'),$request->post('par_contrasena_smtp'),$request->post('par_puerto_smtp'),$request->post('par_metodo'),$request->post('par_easy_atv_server_prod'),$request->post('par_easy_atv_server_test'));
            $parametro->setValues();
            flash('¡Parámetros establecidos con éxito!')->success();
            return Redirect()->route('parametros');
        }
        catch (\Exception $exception)
        {
             flash('¡Ha ocurrido un error al establecer los parámetros!: '.$exception->getMessage())->error();
            return Redirect()->route('parametros');
        }

        exit;

    }
}
