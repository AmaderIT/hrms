<?php

namespace App\Http\Requests\loan;

use App\Models\Loan;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class RequestLoan extends FormRequest
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
        $this->modifyLoanRecords();
        return parent::getValidatorInstance();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            "office_division_id"    => "required|integer|exists:office_divisions,id",
            "department_id"         => "required|integer|exists:departments,id",
            "user_id"               => "required|integer|exists:users,id",
            "type"                  => "required|in:".implode(",", [Loan::TYPE_LOAN, Loan::TYPE_ADVANCE]),
            "loan_amount"           => "required|integer",
            "loan_tenure"           => "required|integer",
            "installment_start_month"           => "required|string",
            "accept_policy"         => "required|in:".implode(",", array('Y')),
            "remarks"               => "nullable|string|min:3|max:255",
            "status"                => "required|in:".implode(",", array(Loan::STATUS_ACTIVE, Loan::STATUS_PAID, Loan::STATUS_PENDING)),
        ];
    }

    /**
     * @return void
     */
    protected function modifyLoanRecords(): void
    {
        $user = auth()->user()->load("currentPromotion");

        $this->request->add([
            "office_division_id"    => $user->currentPromotion->office_division_id,
            "department_id"         => $user->currentPromotion->department_id,
            "user_id"               => $user->id,
            "status"                => Loan::STATUS_PENDING,
            "created_by"            => Auth::id()
        ]);
    }
}
