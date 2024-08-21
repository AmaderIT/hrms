<?php

namespace App\Http\Requests\device;

use Illuminate\Foundation\Http\FormRequest;

class RequestDevice extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return auth()->check();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            "name"  => "required|min:3|max:20|string",
            "ip"    => "required|min:3|max:20|string",
            "port"  => "required|min:0|max:9999|integer",
            "serial"=> "required|min:3|max:50|string",
        ];
    }
}
