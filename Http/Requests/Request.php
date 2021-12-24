<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Validator;

abstract class Request extends FormRequest
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
    public function validator(){

        $v = Validator::make($this->input(), $this->rules(), $this->messages(), $this->attributes());

        if(method_exists($this, 'inputFieldValidation')){
            $this->inputFieldValidation($v);
        }

        return $v;
    }
}
