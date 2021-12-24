<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class BackupDownloadRequest extends FormRequest
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
        return [
            'type' => 'required|max:35',
            'filename' => 'required|max:127'
        ];
    }
}
