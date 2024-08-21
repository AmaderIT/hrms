<?php

namespace App\Http\Requests\transfer;

use App\Models\User;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class RequestTransfer extends FormRequest
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
            "workslot_id" => "required|integer|exists:work_slots,id",
            "promoted_date" => "required|date"
        ];
    }

    public function messages($id = '')
    {
        return [
            'promoted_date.required' => 'The transfer date field is required.',
            'promoted_date.date' => 'The transfer date field date format is invalid.',
            'promoted_date.key' => 'transfer date',
        ];
    }
}
