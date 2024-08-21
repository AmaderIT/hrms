<?php

namespace App\Http\Requests\action;

use Illuminate\Foundation\Http\FormRequest;

class RequestActionReason extends FormRequest
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
            "parent_id" => "required|integer",
            "reason"    => "required|min:3|max:255"
        ];
    }
}
