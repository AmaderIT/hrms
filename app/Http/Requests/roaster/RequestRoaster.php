<?php

namespace App\Http\Requests\roaster;

use Illuminate\Foundation\Http\FormRequest;

class RequestRoaster extends FormRequest
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
        if($this->type=='emp'){
            $rules = [
                "user_id"               => "required|array",
                "user_id.*"             => "required|integer|exists:users,id",
                "office_division_id"    => "required|array",
                "office_division_id.*"  => "required|integer|exists:office_divisions,id",
                "department_id"         => "required|array",
                "department_id.*"       => "required|integer|exists:departments,id",
                "work_slot_id"          => "required|array",
                "work_slot_id.*"        => "nullable|integer|exists:work_slots,id",
                "active_from"           => "required|array",
                "active_from.*"         => "nullable|date",
                "end_date"              => "required|array",
                "end_date.*"            => "nullable|date",
            ];
        }else{
            $rules = [
                "office_division_id"    => "required|integer|exists:office_divisions,id",
                "department_id"         => "required|integer|exists:departments,id",
                "work_slot_id"          => "required|integer|exists:work_slots,id",
                "active_from"           => "required|date",
                "end_date"              => "required|date",
                "weekly_holidays"       => "nullable|array",
                "weekly_holidays.*"     => "nullable|string|in:sat,sun,mon,tue,wed,thu,fri"
            ];
        }
        return $rules;
    }
}
