<?php

namespace App\Http\Requests\taxCustomization;

use Illuminate\Foundation\Http\FormRequest;

class RequestTaxCustomization extends FormRequest
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
            "requested_amount"      => "required|numeric"
        ];
    }
}
