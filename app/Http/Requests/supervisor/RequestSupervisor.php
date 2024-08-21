<?php

namespace App\Http\Requests\supervisor;

use Illuminate\Foundation\Http\FormRequest;

class RequestSupervisor extends FormRequest
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
            "office_division_id"    => "required|exists:office_divisions,id",
            "department_id"         => "required|exists:departments,id",
            "supervised_by"         => "required|exists:users,id",
            /*"override"              => "required"*/
        ];
    }

    /**
     * @return array
     */
    public function messages()
    {
        return [
            /*"override.required"     => "Please confirm by clicking checkbox"*/
        ];
    }
}
