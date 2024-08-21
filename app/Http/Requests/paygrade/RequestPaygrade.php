<?php

namespace App\Http\Requests\paygrade;

use App\Models\PayGrade;
use App\Models\PayGradeDeduction;
use App\Models\PayGradeEarning;
use App\Models\Tax;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;

class RequestPaygrade extends FormRequest
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
     * @return Validator
     */
    protected function getValidatorInstance()
    {
        $this->modifyPayGradeRecords();
        return parent::getValidatorInstance();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        if($this->get("tax_id") == Tax::STATUS_INACTIVE) $taxId = "nullable|boolean";
        else $taxId = "required|exists:taxes,id";

        return [
            "name"                              => "required|max:100",
            "range_start_from"                  => "required|integer|lt:range_end_to",
            "range_end_to"                      => "required|integer|gt:range_start_from",
            "percentage_of_basic"               => "required",
            "based_on"                          => "required|string|in:".implode(",", array(PayGrade::BASED_ON_BASIC, PayGrade::BASED_ON_GROSS)),
            "overtime_formula"                  => "nullable",
            "holiday_allowance_formula"         => "nullable",
            "weekend_allowance_formula"         => "nullable",
            "earning_id"                        => "sometimes",
            "deduction_id"                      => "sometimes",
            "tax_id"                            => $taxId,
            "earning_type"                      => "required|array",
            "earning_type.*"                    => "nullable|string|in:".implode(",", [PayGradeEarning::TYPE_PERCENTAGE, PayGradeEarning::TYPE_FIXED, PayGradeEarning::TYPE_REMAINING]),
            "earning_value"                     => "required|array",
            "earning_value.*"                   => "nullable|integer",
            "earning_tax_exempted"              => "required|array",
            "earning_tax_exempted.*"            => "nullable|integer",
            "earning_tax_exempted_percentage"   => "required|array",
            "earning_tax_exempted_percentage.*" => "nullable|integer",
            "earning_non_taxable"               => "nullable",
            "deduction_type"                    => "nullable|array",
            "deduction_type.*"                  => "nullable|string|in:".implode(",", [PayGradeDeduction::TYPE_PERCENTAGE, PayGradeDeduction::TYPE_FIXED]),
            "deduction_value"                   => "nullable|array",
            "deduction_value.*"                 => "nullable|integer",
        ];
    }

    /**
     * Modify Pay Grade Records
     */
    public function modifyPayGradeRecords(): void
    {
        if ($this->get("tax_id") == Tax::STATUS_ACTIVE) $this->request->add([ "tax_id" => Tax::active()->first()->id ]);
        else $this->request->add([ "tax_id" => 0 ]);
    }
}
