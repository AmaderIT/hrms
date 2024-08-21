<?php

namespace App\Http\Requests\leave;

use Illuminate\Foundation\Http\FormRequest;

class RequestLeaveAllocation extends FormRequest
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
            "office_division_id"    => "required|array",
            "office_division_id.*"  => "required|integer|exists:office_divisions,id",
            "department_id"         => "required|array",
            "department_id.*"       => "required|integer|exists:departments,id",
            "leave_type_id"         => "required|array",
            "leave_type_id.*"       => "required|integer|integer|exists:leave_types,id",
            "year"                  => "required|integer",
            "days"                  => "required|array",
            "days.*"                => "required|integer"
        ];
    }
}
