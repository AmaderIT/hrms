<?php

namespace App\Http\Requests\roles;

use Illuminate\Foundation\Http\FormRequest;

class RequestUpdateEmployeeRole extends FormRequest
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
            "user_id"   => "required|exists:users,id",
            "role_id"   => "required|exists:roles,id"
        ];
    }
}
