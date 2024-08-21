<?php

namespace App\Http\Requests\employee;

use Illuminate\Foundation\Http\FormRequest;

class RequestEmployeeByOfficeDivisionDepartmentFilter extends FormRequest
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
            "office_division_id"    => "required|integer|exists:office_divisions,id",
            "department_id"         => "nullable|array",
            "department_id.*"       => "nullable|integer|exists:departments,id"
        ];
    }
}
