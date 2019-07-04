<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ComprobanteRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
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

                ];
            }

            case 'DELETE':{
                return [];
            }
            case 'POST':{
                return [
                    'api_key'=>'required',
                    'clave'=>'required|max:50'
                ];
            }

        }
    }
}
