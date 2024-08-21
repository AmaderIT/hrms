<?php

namespace App\Http\Requests\leave\holidays;

use Illuminate\Foundation\Http\FormRequest;

class RequestLeaveType extends FormRequest
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
        $leaveTypes = $this->route("leaveType");

        if ($leaveTypes) {
            $validation = "required|min:3|max:50|string|unique:leave_types,name," . $leaveTypes->id;
        } else {
            $validation = "required|min:3|max:50|string|unique:leave_types,name";
        }

        return [
            "name" => $validation,
            "is_paid" => "required|in:0,1,2",
            "priority" => "required|min:1|max:10|integer"
        ];
    }
}
