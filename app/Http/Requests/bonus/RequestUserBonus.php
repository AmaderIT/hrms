<?php

namespace App\Http\Requests\bonus;

use Illuminate\Foundation\Http\FormRequest;

class RequestUserBonus extends FormRequest
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
            "bonus_id"          => "required|exists:bonuses,id",
            "month_and_year"             => "required|string"
        ];
    }
}
