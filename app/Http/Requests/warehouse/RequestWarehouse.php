<?php

namespace App\Http\Requests\warehouse;

use Illuminate\Foundation\Http\FormRequest;

class RequestWarehouse extends FormRequest
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
            "name"          => "required",
            "company_name"  => "required",
            "bin"           => "required",
            "email"         => "required|email",
            "code"          => "required",
            "phone"         => "required",
            "city"          => "required",
            "area"          => "required",
            "address"       => "required"
        ];
    }
}
