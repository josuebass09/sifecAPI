<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Usuario;
use App\User;
use Illuminate\Support\Facades\DB;

class UsuarioController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function show($id_fiscal)
    {
        $usuario=DB::table('users')->join('ROLES','users.id_role','=','ROLES.id')->select('users.id','users.name','users.email','users.id_role','ROLES.descripcion as rolname')->where('users.id','=',$id_fiscal)->first();
        return view('usuarios/verUsuario',['modulo'=>'VerUsuario','usuario'=>$usuario]);
    }

    public function showAll()
    {

        return view('Usuario',['Usuario'=>DB::table('users')->join('ROLES','users.id_role','=','ROLES.id')->select('users.id','users.name','users.email','users.id_role','ROLES.descripcion as rolname')->get()]);
    }
    public function index()
    {
        return view('usuarios/usuarios',['modulo'=>'Usuarios']);
    }
    public function create()
    {
        return view('usuarios/agregarUsuario',['modulo'=>'AgregarUsuario']);
    }
    public function update(Request $request,$id)
    {
        try
        {
            $usuario=User::find($id);
            $usuario->name=$request->post('usu_name');
            $usuario->email=$request->post('usu_email');
            $usuario->update();
            flash('¡El Usuario '.$usuario->name.' fue actualizado satisfactoriamente!')->info();
            return Redirect()->route('usuarios');
        }
        catch (\Exception $e)
        {
            flash('¡Ha ocurrido un error al actualizar el registro: '.$e->getMessage())->error();
        }
    }
    public function getUsuariosById($query)
    {

        $emisores=null;
        $cadena='<tr><th>ID</th><th>Nombre</th><th>Correo Electrónico</th><th>Role</th><th>Acciones</th></tr>';
        if($query=='all')
        {
            $usuarios=DB::table('users')->join('ROLES','users.id_role','=','ROLES.id')->select('users.id','users.name','users.email','users.id_role','ROLES.descripcion as rolename')->get();

        }
        else{
            $usuarios=DB::table('users')->join('ROLES','users.id_role','=','ROLES.id')->select('users.id','users.name','users.email','users.id_role','ROLES.descripcion as rolename')->where('users.name','like','%'.$query.'%')->get();
        }

        foreach ($usuarios as $u)
        {
            $ruta="usuarios/".$u->id;
            $cadena=$cadena."<tr><td>".$u->id."</td><td>".$u->name."</td><td>".$u->email."</td><td>".$u->rolename."</td><td><a class='btn btn-warning margendivisor' href=$ruta><i class='glyphicon glyphicon-edit'></i></a></td></tr>";
        }
        return $cadena;
    }


}
