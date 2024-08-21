<?php

namespace App\Http\Requests\report;

use Illuminate\Foundation\Http\FormRequest;

class RequestMonthlyAttendanceReport extends FormRequest
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
            "department_id"         => "required|array",
            "department_id.*"       => "required|integer|exists:departments,id",
            "user_id"               => "sometimes|exists:users,id",
            "datepicker"            => "required|string",
        ];
    }
}
