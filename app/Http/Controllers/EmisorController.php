<?php
//Comentario
namespace App\Http\Controllers;

use App\Emisor;
use App\Http\Requests\EmisorRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redirect;
use Illuminate\View\View;


class EmisorController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }
    public function create()
    {
        return view('emisores/agregarEmisor',['modulo'=>'AgregarEmisor']);
    }
    public function index()
    {
        return view('emisores/emisores',['modulo'=>'Emisores']);

    }

    public function cantones($cod_provincia)
    {
        $cantones=DB::table('UBICACIONES')->select('cod_can','canton')->distinct()->where('cod_pro','=',$cod_provincia)->get();
        $cadena="<option value=''>Seleccione un Cantón</option>";
        foreach ($cantones as $c)
        {
            $cadena .= "<option value='" . $c->cod_can . "'>" . $c->canton . "</option>";
        }
        return $cadena;
    }
    public function distritos($cod_provincia,$cod_canton)
    {
        $distritos=DB::table('UBICACIONES')->select('cod_dis','distrito')->distinct()->where('cod_pro','=',$cod_provincia)->where('cod_can','=',$cod_canton)->get();
        $cadena="<option value=''>Seleccione un Distrito</option>";
        foreach ($distritos as $d)
        {
            $cadena .= "<option value='" . $d->cod_dis . "'>" . $d->distrito . "</option>";
        }
        return $cadena;
    }
    public function barrios($cod_provincia,$cod_canton,$cod_distrito)
    {
        $barrios=DB::table('UBICACIONES')->select('cod_barrio','barrio')->distinct()->where('cod_pro','=',$cod_provincia)->where('cod_can','=',$cod_canton)->where('cod_dis','=',$cod_distrito)->get();
        $cadena="<option value=''>Seleccione un Barrio</option>";
        foreach ($barrios as $b)
        {
            $cadena .= "<option value='" . $b->cod_barrio . "'>" . $b->barrio . "</option>";
        }
        return $cadena;

    }

    public function getIdUbicacion($cod_provincia,$cod_canton,$cod_distrito,$cod_barrio)
    {
        $ubicacion=DB::table('UBICACIONES')->select('id')->where('cod_pro','=',$cod_provincia)->where('cod_can','=',$cod_canton)->where('cod_dis','=',$cod_distrito)->where('cod_barrio','=',$cod_barrio)->first();
        return $ubicacion->id;
    }
    public function getEmisoresById($query,$filtro)
    {
        $emisores=null;
        $cadena='<tr><th>ID Fiscal</th><th>Razón Social</th><th>Créditos Disponibles</th><th>Correo Electrónico</th><th>Fecha de Vencimiento</th><th>Estado</th><th>Acciones</th></tr>';
        if($query=='all')
        {
            $emisores=DB::table('EMISORES')->join('TP_IDENTIFICACIONES', 'EMISORES.id_tpidentificacion', '=', 'TP_IDENTIFICACIONES.id') ->select('EMISORES.id','EMISORES.razon_social','EMISORES.activo','EMISORES.correo','EMISORES.vencimiento_plan','EMISORES.id_tpidentificacion','TP_IDENTIFICACIONES.descripcion as cedula','EMISORES.creditos_disponibles')->get();
        }
        else {
            if($filtro==='cedula')
            {
                $emisores=DB::table('EMISORES')->join('TP_IDENTIFICACIONES', 'EMISORES.id_tpidentificacion', '=', 'TP_IDENTIFICACIONES.id') ->select('EMISORES.id','EMISORES.razon_social','EMISORES.activo','EMISORES.correo','EMISORES.vencimiento_plan','EMISORES.id_tpidentificacion','TP_IDENTIFICACIONES.descripcion as cedula','EMISORES.creditos_disponibles')->where('EMISORES.id','like',$query.'%')->get();
            }

            elseif($filtro==='razonso')
            {
                $emisores=DB::table('EMISORES')->join('TP_IDENTIFICACIONES', 'EMISORES.id_tpidentificacion', '=', 'TP_IDENTIFICACIONES.id') ->select('EMISORES.id','EMISORES.razon_social','EMISORES.activo','EMISORES.correo','EMISORES.vencimiento_plan','EMISORES.id_tpidentificacion','TP_IDENTIFICACIONES.descripcion as cedula','EMISORES.creditos_disponibles')->where('razon_social','like','%'.$query.'%')->get();
            }
            elseif($filtro==='correo')
            {
                $emisores=DB::table('EMISORES')->join('TP_IDENTIFICACIONES', 'EMISORES.id_tpidentificacion', '=', 'TP_IDENTIFICACIONES.id') ->select('EMISORES.id','EMISORES.razon_social','EMISORES.activo','EMISORES.correo','EMISORES.vencimiento_plan','EMISORES.id_tpidentificacion','TP_IDENTIFICACIONES.descripcion as cedula','EMISORES.creditos_disponibles')->where('correo','like','%'.$query.'%')->get();
            }
        }

        foreach ($emisores as $e)
        {
            $ruta="emisores/".$e->id;

            if($e->activo)
            {
                $cadena=$cadena."<tr><td>".$e->id."</td><td>".$e->razon_social."</td><td>".$e->creditos_disponibles."</td><td>".$e->correo."</td><td>".$e->vencimiento_plan."</td><td><span class='col-md-10 bg-success badge' style='background-color:#5cb85c;'>Activo</span></td><td><a class='btn btn-warning margendivisor' href=$ruta><i class='glyphicon glyphicon-edit'></i></a></td></tr>";
            }
            else{

                $cadena=$cadena."<tr><td>".$e->id."</td><td>".$e->razon_social."</td><td>".$e->creditos_disponibles."</td><td>".$e->correo."</td><td>".$e->vencimiento_plan."</td><td><span class='col-md-10 bg-danger badge' style='background-color:#dc3545;'>Inactivo</span></td><td><a class='btn btn-warning margendivisor' href=$ruta><i class='glyphicon glyphicon-edit'></i></a></td></tr>";
            }
            //$cadena=$cadena."<tr><td>".$e->id."</td><td>".$e->razon_social."</td><td>".$e->creditos_disponibles."</td><td>".$e->correo."</td><td>".$e->vencimiento_plan."</td><td><a class='btn btn-warning margendivisor' href=$ruta><i class='glyphicon glyphicon-edit'></i></a><button class='btn btn-danger' onclick='abrir_modal();'><i class='glyphicon glyphicon-trash'></i></button></td></tr>";

        }
        return $cadena;
    }
    public function show($id_fiscal)
    {
        $emisor=DB::table('EMISORES')->join('UBICACIONES','EMISORES.id_ubicacion','=','UBICACIONES.id')->select('EMISORES.*','UBICACIONES.id as id_ubicaciones','UBICACIONES.cod_barrio','UBICACIONES.barrio','UBICACIONES.cod_dis','UBICACIONES.distrito','UBICACIONES.cod_can','UBICACIONES.canton','UBICACIONES.cod_pro','UBICACIONES.provincia')->where('EMISORES.id','=',$id_fiscal)->first();
        return view('emisores/verEmisor',['modulo'=>'VerEmisor','emisor'=>$emisor]);
    }
    public function update(EmisorRequest $request,$id)
    {
        try{
            $emisor=Emisor::find($id);
            $emisor->razon_social=$request->post('emi_raz_soc');
            $emisor->nombre_comercial=$request->post('emi_nom_com');
            $emisor->usuario_atv_prod=$request->post('emi_usu_atv');
            $emisor->contrasena_atv_prod=$request->post('emi_con_atv');
            //$emisor->certificado_atv_prod=$request->file('myfile')->getClientOriginalName();
            $emisor->pin_atv_prod=$request->post('emi_pin_atv');
            $emisor->puerto_smtp_nova=$request->post('emi_pue_smt_nov');
            $emisor->metodo_smtp_secundario=$request->post('emi_met_smt_opc');
            $emisor->usuario_atv_test=$request->post('emi_usu_atv_tes');
            $emisor->contrasena_atv_test=$request->post('emi_con_atv_test');
            $emisor->pin_atv_test=$request->post('emi_pin_atv_test');
            $emisor->creditos_usados=$request->post('emi_cre_usa');
            $emisor->creditos_disponibles=$request->post('emi_cre_dis');
            $emisor->vencimiento_plan=$request->post('emi_ven_pla');
            $emisor->host_smtp_secundario=$request->post('emi_hos_smt_opc');
            $emisor->puerto_smtp_secundario=$request->post('emi_pue_smt');
            //$emisor->id_ubicacion=$this->getIdUbicacion($request->post('provincia'),$request->post('canton'),$request->post('distrito'),$request->post('barrio'));
            $emisor->api_key=$request->post('emi_api_key');
            if($request->has('check_activo_emisor'))
            {
                $emisor->activo=1;
            }
            else{
                $emisor->activo=0;
            }
            $emisor->otras_senas=$request->post('emi_otr_sen');
            $emisor->consecutivoFEprod=$request->post('emi_con_fe_pro');
            $emisor->consecutivoTEprod=$request->post('emi_con_te_pro');
            $emisor->consecutivoNCprod=$request->post('emi_con_nc_pro');
            $emisor->consecutivoNDprod=$request->post('emi_con_nd_pro');
            $emisor->consecutivoGAprod=$request->post('emi_con_ga_pro');
            $emisor->consecutivoFEtest=$request->post('emi_con_fe_tes');
            $emisor->usuario_smtp_secundario=$request->post('emi_usu_smt_opc');
            $emisor->consecutivoTEtest=$request->post('emi_con_te_tes');
            $emisor->consecutivoNCtest=$request->post('emi_con_nc_test');
            $emisor->consecutivoNDtest=$request->post('emi_con_nd_tes');
            $emisor->consecutivoGAtest=$request->post('emi_con_ga_tes');
            $emisor->consecutivoFECtest=$request->post('emi_con_fec_test');
            $emisor->consecutivoFEEtest=$request->post('emi_con_fee_test');
            $emisor->consecutivoFECprod=$request->post('emi_con_fec_pro');
            $emisor->consecutivoFEEprod=$request->post('emi_con_fee_pro');
            $emisor->host_smtp_nova=$request->post('emi_hos_smt_nov');
            $emisor->usuario_smtp_nova=$request->post('emi_usu_smt_nov');
            $emisor->contrasena_smtp_nova=$request->post('emi_con_stm_nov');
            $emisor->metodo_smtp_nova=$request->post('emi_met_smt_nov');
            $emisor->contrasena_smtp_secundario=$request->post('emi_con_stm_opc');
            $emisor->correo=$request->post('correo');
            $emisor->telefono=$request->post('emi_telefono');
            $emisor->fax=$request->post('emi_fax');


            if($request->file('myfile'))
            {
                $uploadedFile = $request->file('myfile');
                $emisor->certificado_atv_prod=$request->file('myfile')->getClientOriginalName();
                $uploadedFile->move('../../EasyATV/bin/cer', $emisor->certificado_atv_prod);
            }
            if($request->file('myfile2'))
            {

                $uploadedFile2 = $request->file('myfile2');
                $emisor->certificado_atv_test =$request->file('myfile2')->getClientOriginalName();
                $uploadedFile2->move('../../EasyATV/bin/cer_sandbox', $emisor->certificado_atv_test);
            }
            if($request->file('emi_logo'))
            {
                try{
                    $uploadedFile3=$request->file('emi_logo');
                    $emisor->logo=$emisor->id.".png";
                    $uploadedFile3->move(public_path('img'),$emisor->logo);
                }
                catch(Exception $e)
                {
                    flash('¡Error al actualizar el logo:'.$e->getMessage())->danger();
                    return Redirect()->route('emisores');
                }


            }
            if(!is_null($request->post('canton')) AND !is_null($request->post('canton')) AND !is_null($request->post('distrito')) AND !is_null($request->post('barrio')))
            {
                $emisor->id_ubicacion=$this->getIdUbicacion($request->post('provincia'),$request->post('canton'),$request->post('distrito'),$request->post('barrio'));
            }
            if($request->post('desbloquear_smtp_opcional'))
            {
                $emisor->SMTP_OP=1;
            }
            else{
                $emisor->SMTP_OP=0;
            }
            if($request->post('emi_pdf'))
            {
                $emisor->PDF=1;
            }
            else{
                $emisor->PDF=0;
            }
            $emisor->update();
            flash('¡El emisor '.$emisor->razon_social.' fue actualizado satisfactoriamente!')->info();
            return Redirect()->route('emisores');

        }
        catch (\Exception $e)
        {
            flash('¡Ha ocurrido un error al actualizar el registro: '.$e->getMessage())->error();
        }
    }
    public function generarNuevaApiKey()
    {
        try {
            return json_encode(array("key"=>bin2hex(random_bytes(30))));
        } catch (\Exception $e) {
            return "Ha ocurrido un error al generar la nueva API Key:".$e->getMessage();
        }
    }

    public function store(EmisorRequest $request)
    {
        try{
            $emisor=new Emisor;
            $emisor->id=$request->post('id');
            $emisor->id_usuario=8;
            $emisor->razon_social=$request->post('emi_raz_soc');
            $emisor->id_tpidentificacion=$request->post('emi_tp_ide');
            $emisor->nombre_comercial=$request->post('emi_nom_com');
            $emisor->usuario_atv_prod=$request->post('emi_usu_atv');
            $emisor->contrasena_atv_prod=$request->post('emi_con_atv');
            $emisor->certificado_atv_prod=$request->file('myfile')->getClientOriginalName();
            $emisor->pin_atv_prod=$request->post('emi_pin_atv');
            //$emisor->puerto_smtp_nova=$request->post('emi_pue_smt_nov');
            $emisor->metodo_smtp_secundario=$request->post('emi_met_smt_opc');
            $emisor->usuario_atv_test=$request->post('emi_usu_atv_tes');
            $emisor->contrasena_atv_test=$request->post('emi_con_atv_test');
            $emisor->pin_atv_test=$request->post('emi_pin_atv_test');
            $emisor->creditos_usados=$request->post('emi_cre_usa');
            $emisor->creditos_disponibles=$request->post('emi_cre_dis');
            $emisor->vencimiento_plan=$request->post('emi_ven_pla');
            $emisor->host_smtp_secundario=$request->post('emi_hos_smt_opc');
            $emisor->puerto_smtp_secundario=$request->post('emi_pue_smt');
            $emisor->id_ubicacion=$this->getIdUbicacion($request->post('provincia'),$request->post('canton'),$request->post('distrito'),$request->post('barrio'));
            $emisor->api_key=bin2hex(random_bytes(30));
            $emisor->activo=1;
            $emisor->otras_senas=$request->post('emi_otr_sen');
            $emisor->consecutivoFEprod=$request->post('emi_con_fe_pro');
            $emisor->consecutivoTEprod=$request->post('emi_con_te_pro');
            $emisor->consecutivoNCprod=$request->post('emi_con_nc_pro');
            $emisor->consecutivoNDprod=$request->post('emi_con_nd_pro');
            $emisor->consecutivoGAprod=$request->post('emi_con_ga_pro');
            $emisor->consecutivoFEtest=$request->post('emi_con_fe_tes');
            $emisor->usuario_smtp_secundario=$request->post('emi_usu_smt_opc');
            $emisor->consecutivoTEtest=$request->post('emi_con_te_tes');
            $emisor->consecutivoNCtest=$request->post('emi_con_nc_test');
            $emisor->consecutivoNDtest=$request->post('emi_con_nd_tes');
            $emisor->consecutivoGAtest=$request->post('emi_con_ga_tes');
            $emisor->consecutivoFECtest=$request->post('emi_con_fec_test');
            $emisor->consecutivoFEEtest=$request->post('emi_con_fee_test');
            $emisor->consecutivoFECprod=$request->post('emi_con_fec_pro');
            $emisor->consecutivoFEEprod=$request->post('emi_con_fee_pro');
            $emisor->telefono=$request->post('emi_telefono');
            $emisor->fax=$request->post('emi_fax');

            //$emisor->host_smtp_nova=$request->post('emi_hos_smt_nov');
            //$emisor->usuario_smtp_nova=$request->post('emi_usu_smt_nov');
            //$emisor->contrasena_smtp_nova=$request->post('emi_con_stm_nov');
            //$emisor->metodo_smtp_nova=$request->post('emi_met_smt_nov');
            $emisor->contrasena_smtp_secundario=$request->post('emi_con_stm_opc');
            $emisor->correo=$request->post('correo');
            $uploadedFile = $request->file('myfile');
            $uploadedFile->move('../../EasyATV/bin/cer', $emisor->certificado_atv_prod);
            $uploadedFile2 = $request->file('myfile2');
            $uploadedFile3=$request->file('emi_logo');
            if($uploadedFile2)
            {
                $emisor->certificado_atv_test=$request->file('myfile2')->getClientOriginalName();
                $uploadedFile2->move('../../EasyATV/bin/cer_sandbox', $emisor->certificado_atv_test);
            }
            if($uploadedFile3)
            {
                $emisor->logo=$emisor->id.".png";
                $uploadedFile3->move(public_path('img'),$emisor->logo);

            }
            if($request->post('desbloquear_smtp_opcional'))
            {
                $emisor->SMTP_OP=1;
            }
            if(!$request->post('emi_pdf'))
            {
                $emisor->PDF=0;
            }
            $emisor->save();

            flash('¡El emisor '.$emisor->razon_social.' fue creado satisfactoriamente!')->success();
        }
        catch (\Exception $e)
        {
            flash('¡Ha ocurrido un error al crear el registro: '.$e->getMessage())->error();
        }
        return Redirect()->route('emisores');
    }
    public function destroy($id,$id2)
    {
        try
            {
                Emisor::where('id',$id)->delete();
                flash('¡El emisor '.$id2.' fue eliminado correctamente!')->success();
            }
            catch (\Exception $e)
            {
                flash('Ha ocurrido un error al eliminar el registro: '.$e->getMessage())->error();
            }

        return Redirect()->route('emisores');
    }
}
