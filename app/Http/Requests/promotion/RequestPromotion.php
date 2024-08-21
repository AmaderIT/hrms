<?php

namespace App\Http\Requests\promotion;

use App\Models\User;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class RequestPromotion extends FormRequest
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
            "user_id"               => "required|integer|exists:users,id",
            "office_division_id"    => "required|integer|exists:office_divisions,id",
            "department_id"         => "required|integer|exists:departments,id",
            "designation_id"        => "required|integer|exists:designations,id",
            "pay_grade_id"          => "required|integer|exists:pay_grades,id",
            "salary"                => "required|integer|min:0",
            "promoted_date"         => "required|date",
            "type"                  => "required|string|in:Internee,Provision,Permanent,Promoted,Transferred,Increment",
            "workslot_id"           => "required|integer|exists:work_slots,id"
        ];
    }
}
