<?php

namespace App\Http\Requests\taxRule;

use Illuminate\Foundation\Http\FormRequest;

class RequestTaxRule extends FormRequest
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
            # Male
            "slab_male"             => "required|array",
            "slab_male.*"           => "required|integer",
            "tax_rate_male"         => "required|array",
            "tax_rate_male.*"       => "required|integer",
            "remaining_rate_male"   => "required|integer",

            # Female
            "slab_female"           => "required|array",
            "slab_female.*"         => "required|integer",
            "tax_rate_female"       => "required|array",
            "tax_rate_female.*"     => "required|integer",
            "remaining_rate_female" => "required|integer",

            # Rebate
            "eligible_rebate"       => "nullable|integer|between:1,100",
//             "tax_rebate"            => "nullable|integer|between:1,100",
             "min_tax_amount"        => "required|integer",
            "slab_rebate"           => "required",
            "slab_rebate.*"         => "required|integer",
            "tax_rate_rebate"       => "required",
            "tax_rate_rebate.*"     => "required|integer",
            "remaining_rate_rebate" => "required|integer",
        ];
    }
}
