<?php

namespace App\Http\Requests\LateDeduction;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
class LateDeductionRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return Auth::check();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
                "department_id"     => "required|integer|exists:departments,id",
                "total_days"        => "required|numeric|min:1",
                "type"              => "required|string|in:leave,salary",
                "deduction_day"     => "required|numeric|min:1",
        ];
    }
}
