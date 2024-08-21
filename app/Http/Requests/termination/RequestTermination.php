<?php

namespace App\Http\Requests\termination;

use Illuminate\Foundation\Http\FormRequest;

class RequestTermination extends FormRequest
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
            "user_id"           => "required|integer|exists:users,id",
            "action_reason_id"  => "required",
            "action_taken_by"   => "required|integer|exists:users,id",
            "action_date"       => "required|date",
            "remarks"       => "nullable"
        ];
    }
}
