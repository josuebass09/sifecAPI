<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\JsonResponse;

class EmisorRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
//protected $redirectRoute = 'emisores';

    public function authorize()
    {
        return true;
    }


    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        switch ($this->method())
        {
            case 'GET':{
                return [];
            }
            case 'PUT':{
                return [
                    'emi_raz_soc'=>'max:80|required',
                    //'emi_tp_ide'=>'required',
                    'emi_nom_com'=>'max:80',
                    'emi_usu_atv'=>'max:254|required',
                    'emi_con_atv'=>'max:254|required',
                    'emi_pin_atv'=>'max:4|required',
                    'emi_met_smt_opc'=>'max:8',
                    'emi_usu_atv_tes'=>'max:254',
                    'emi_con_atv_test'=>'max:254',
                    'emi_pin_atv_test'=>'max:4',
                    'emi_cre_usa'=>'max:6|required',
                    'emi_cre_dis'=>'max:6|required',
                    'emi_ven_pla'=>'max:10|required',
                    'emi_hos_smt_opc'=>'max:50',
                    'emi_pue_smt'=>'max:5',
                    'emi_otr_sen'=>'max:160|required',
                    'emi_con_fe_pro'=>'max:10|required',
                    'emi_con_te_pro'=>'max:10|required',
                    'emi_con_nc_pro'=>'max:10|required',
                    'emi_con_nd_pro'=>'max:10|required',
                    'emi_con_ga_pro'=>'max:10|required',
                    'emi_con_fe_tes'=>'max:10',
                    'emi_usu_smt_opc'=>'max:50',
                    'emi_con_te_tes'=>'max:10',
                    'emi_con_nc_test'=>'max:10',
                    'emi_con_nd_tes'=>'max:10',
                    'emi_con_ga_tes'=>'max:10',
                    'emi_con_fec_pro'=>'max:10|required',
                    'emi_con_fee_pro'=>'max:10|required',
                    'emi_con_fec_test'=>'max:10',
                    'emi_con_fee_test'=>'max:10',
                    'emi_con_stm_opc'=>'max:50',
                    //'myfile'=>'required',
                    'correo'=>'max:40|email',
                    'provincia'=>'max:1',
                    'canton'=>'max:2',
                    'distrito'=>'max:2',
                    'barrio'=>'max:2'
                ];
            }

            case 'DELETE':{
                return [];
            }
            case 'POST':{
                return [
                    'id'=>'max:12|required|unique:EMISORES',
                    'emi_raz_soc'=>'max:80|required',
                    'emi_tp_ide'=>'required',
                    'emi_nom_com'=>'max:80',
                    'emi_usu_atv'=>'max:254|required',
                    'emi_con_atv'=>'max:254|required',
                    'emi_pin_atv'=>'max:4|required',
                    'emi_met_smt_opc'=>'max:8',
                    'emi_usu_atv_tes'=>'max:254',
                    'emi_con_atv_test'=>'max:254',
                    'emi_pin_atv_test'=>'max:4',
                    'emi_cre_usa'=>'max:6|required',
                    'emi_cre_dis'=>'max:6|required',
                    'emi_ven_pla'=>'max:10|required',
                    'emi_hos_smt_opc'=>'max:50',
                    'emi_pue_smt'=>'max:5',
                    'emi_otr_sen'=>'max:160|required',
                    'emi_con_fe_pro'=>'max:10|required',
                    'emi_con_te_pro'=>'max:10|required',
                    'emi_con_nc_pro'=>'max:10|required',
                    'emi_con_nd_pro'=>'max:10|required',
                    'emi_con_ga_pro'=>'max:10|required',
                    'emi_con_fe_tes'=>'max:10',
                    'emi_usu_smt_opc'=>'max:50',
                    'emi_con_te_tes'=>'max:10',
                    'emi_con_nc_test'=>'max:10',
                    'emi_con_nd_tes'=>'max:10',
                    'emi_con_ga_tes'=>'max:10',
                    'emi_con_fec_pro'=>'max:10|required',
                    'emi_con_fee_pro'=>'max:10|required',
                    'emi_con_fec_test'=>'max:10',
                    'emi_con_fee_test'=>'max:10',
                    'emi_con_stm_opc'=>'max:50',
                    'myfile'=>'required',
                    'correo'=>'max:40|email|required',
                    'provincia'=>'required|min:1|max:1',
                    'canton'=>'required|max:2',
                    'distrito'=>'required|max:2',
                    'barrio'=>'required|max:2'

                ];
            }

        }

    }
    public function attributes()
    {
        return [
            'emi_id'=>'ID Fiscal',
            'emi_raz_soc'=>'Razón Social',
            'emi_tp_ide'=>'Tipo de Identificación',
            'emi_nom_com'=>'Nombre Comercial',
            'emi_usu_atv'=>'Usuario ATV',
            'emi_con_atv'=>'Contraseña ATV',
            'emi_pin_atv'=>'PIN ATV',
            'emi_met_smt_opc'=>'Método SMTP Opcional',
            'emi_usu_atv_tes'=>'Usuario ATV Test',
            'emi_con_atv_test'=>'Contraseña ATV Test',
            'emi_pin_atv_test'=>'PIN ATV Test',
            'emi_cre_usa'=>'Créditos Usados',
            'emi_cre_dis'=>'Créditos Disponibles',
            'emi_ven_pla'=>'Fecha de Vencimiento del Plan',
            'emi_hos_smt_opc'=>'Host SMTP Opcional',
            'emi_otr_sen'=>'Otras Señas',
            'emi_con_fe_pro'=>'Consecutivo FE Producción',
            'emi_con_te_pro'=>'Consecutivo TE Producción',
            'emi_con_nc_pro'=>'Consecutivo NC Producción',
            'emi_con_nd_pro'=>'Consecutivo ND Producción',
            'emi_con_ga_pro'=>'Consecutivo GA Producción',
            'emi_con_fe_tes'=>'Consecutivo FE Test',
            'emi_usu_smt_opc'=>'Usuario SMTP Opcional',
            'emi_con_te_tes'=>'Consecutivo TE Test',
            'emi_con_nc_test'=>'Consecutivo NC Test',
            'emi_con_nd_tes'=>'Consecutivo ND Test',
            'emi_con_ga_tes'=>'Consecutivo GA Test',
            'emi_con_stm_opc'=>'Contraseña SMTP Opcional',
            'myfile'=>'Llave Criptográfica',
            'correo'=>'Correo Electrónico',
            'provincia'=>'La Provincia',
            'canton'=>'El Cantón',
            'distrito'=>'El Distrito',
            'barrio'=>'El Barrio'



        ];
    }

    public function messages()
    {
        return [
            'emi_id.required' => '¡:attribute es obligatorio!',
            'emi_id.max' => '¡:attribute debe ser menor o igual a 12 caracteres!',
            'emi_raz_soc.required' => '¡:attribute es obligatoria!',
            'emi_raz_soc.max' => '¡:attribute debe ser menor o igual a 80 caracteres!',
            'emi_tp_ide.required' => '¡:attribute es obligatorio!',
            'emi_nom_com.max' => '¡:attribute debe ser menor o igual a 80 caracteres!',
            'emi_usu_atv.required' => '¡:attribute es obligatorio!',
            'emi_usu_atv.max' => '¡:attribute debe ser menor o igual a 254 caracteres!',
            'emi_con_atv.required' => '¡:attribute es obligatoria!',
            'emi_con_atv.max' => '¡:attribute debe ser menor o igual a 254 caracteres!',
            'emi_pin_atv.required' => '¡:attribute es obligatorio!',
            'emi_pin_atv.max' => '¡:attribute debe ser menor o igual a 4 caracteres!',
            'emi_met_smt_opc.max' => '¡:attribute debe ser menor o igual a 8 caracteres!',
            'emi_usu_atv_tes.max' => '¡:attribute debe ser menor o igual a 254 caracteres!',
            'emi_con_atv_test.max' => '¡:attribute debe ser menor o igual a 254 caracteres!',
            'emi_pin_atv_test.max' => '¡:attribute debe ser menor o igual a 4 caracteres!',
            'emi_cre_usa.required' => '¡:attribute es obligatorio!',
            'emi_cre_usa.max' => '¡:attribute debe ser menor o igual a 6 caracteres!',
            'emi_cre_dis.required' => '¡:attribute es obligatorio!',
            'emi_cre_dis.max' => '¡:attribute debe ser menor o igual a 6 caracteres!',
            'emi_ven_pla.required' => '¡:attribute es obligatorio!',
            'emi_ven_pla.max' => '¡:attribute debe ser menor o igual a 10 caracteres!',
            'emi_hos_smt_opc.max' => '¡:attribute debe ser menor o igual a 50 caracteres!',
            'emi_pue_smt.max' => '¡:attribute debe ser menor o igual a 5 caracteres!',
            'emi_otr_sen.required' => '¡:attribute es obligatorio!',
            'emi_otr_sen.max' => '¡:attribute debe ser menor o igual a 160 caracteres!',
            'emi_con_fe_pro.required' => '¡:attribute es obligatorio!',
            'emi_con_fe_pro.max' => '¡:attribute debe ser menor o igual a 10 caracteres!',
            'emi_con_te_pro.required' => '¡:attribute es obligatorio!',
            'emi_con_te_pro.max' => '¡:attribute debe ser menor o igual a 10 caracteres!',
            'emi_con_nc_pro.required' => '¡:attribute es obligatorio!',
            'emi_con_nc_pro.max' => '¡:attribute debe ser menor o igual a 10 caracteres!',
            'emi_con_nd_pro.required' => '¡:attribute es obligatorio!',
            'emi_con_nd_pro.max' => '¡:attribute debe ser menor o igual a 10 caracteres!',
            'emi_con_ga_pro.required' => '¡:attribute es obligatorio!',
            'emi_con_ga_pro.max' => '¡:attribute debe ser menor o igual a 10 caracteres!',
            'emi_con_fe_tes.max' => '¡:attribute debe ser menor o igual a 10 caracteres!',
            'emi_usu_smt_opc.max' => '¡:attribute debe ser menor o igual a 50 caracteres!',
            'emi_con_te_tes.max' => '¡:attribute debe ser menor o igual a 10 caracteres!',
            'emi_con_nc_test.max' => '¡:attribute debe ser menor o igual a 10 caracteres!',
            'emi_con_nd_tes.max' => '¡:attribute debe ser menor o igual a 10 caracteres!',
            'emi_con_ga_tes.max' => '¡:attribute debe ser menor o igual a 10 caracteres!',
            'emi_con_stm_opc.max' => '¡:attribute debe ser menor o igual a 50 caracteres!',
            'myfile.required' => '¡:attribute de producción es obligatoria',
            'correo.required'=>'¡:attribute es obligatorio',
            'correo.max'=>'!:attribute debe ser menor o igual a 40 caracteres',
            'correo.email'=>'!:attribute El formato no coincide con un Correo Electrónico',
            'provincia.required' => '¡:attribute es obligatoria!',
            'provincia.max' => '¡:attribute debe ser menor o igual a 2 caracteres!',
            'canton.required' => '¡:attribute es obligatorio!',
            'canton.max' => '¡:attribute debe ser menor o igual a 2 caracteres!',
            'distrito.required' => '¡:attribute es obligatorio!',
            'distrito.max' => '¡:attribute debe ser menor o igual a 2 caracteres!',
            'barrio.required' => '¡:attribute es obligatorio!',
            'barrio.max' => '¡:attribute debe ser menor o igual a 2 caracteres!',

        ];
    }

    public function response(array $errors)
    {
        $transformed = [];

        foreach ($errors as $field => $message) {
            $transformed[] = [
                'field' => $field,
                'message' => $message[0]
            ];
        }

        return response($errors, 422);

    }





}
