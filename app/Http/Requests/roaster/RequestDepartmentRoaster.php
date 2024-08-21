<?php

namespace App\Http\Requests\roaster;

use Illuminate\Foundation\Http\FormRequest;

class RequestDepartmentRoaster extends FormRequest
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
        
        $rules = [
            "office_division_id"    => "required|integer|exists:office_divisions,id",
            "department_id"         => "required|integer|exists:departments,id",
            "work_slot_id"          => "required|integer|exists:work_slots,id",
            "active_from"           => "required|date",
            "end_date"              => "required|date",
            "weekly_holidays"       => "nullable|array",
            "weekly_holidays.*"     => "nullable|string|in:sat,sun,mon,tue,wed,thu,fri"
        ];

        return  $rules;
    }
}
