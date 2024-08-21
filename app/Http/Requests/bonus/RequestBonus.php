<?php

namespace App\Http\Requests\bonus;

use App\Models\Bonus;
use Illuminate\Foundation\Http\FormRequest;

class RequestBonus extends FormRequest
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
            "festival_name"         => "required|string|min:3|max:200",
            "type"                  => "required|string|in:".implode(",", array(Bonus::TYPE_BASIC, Bonus::TYPE_GROSS)),
            "percentage_one"   => "required|regex:/^[0-9]+(\.[0-9][0-9]?)?$/",
            "percentage_two"   => "required|regex:/^[0-9]+(\.[0-9][0-9]?)?$/",
            "employment_period_one"   => "required|regex:/^[0-9]+(\.[0-9][0-9]?)?$/",
            "employment_period_two"   => "required|regex:/^[0-9]+(\.[0-9][0-9]?)?$/",
            "effective_date"   => "required",
        ];
    }
}
