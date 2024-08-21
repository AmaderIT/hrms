<?php

namespace App\Http\Requests\late_allow;

use App\Models\User;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class RequestLateAllow extends FormRequest
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
            "user_id" => "required|integer|exists:users,id",
            "office_division_id" => "required|integer|exists:office_divisions,id",
            "department_id" => "required|integer|exists:departments,id",
            "allow" => "required|integer|min:1|max:30"
        ];
    }

    public function messages($id = '')
    {
        return [
        ];
    }
    public function attributes()
    {
        return [
            'allow' => 'No Of Allowed Late'
        ];
    }
}
