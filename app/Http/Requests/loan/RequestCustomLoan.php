<?php

namespace App\Http\Requests\loan;

use App\Models\Loan;
use App\Models\User;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\ValidationException;

class RequestCustomLoan extends FormRequest
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
     * @throws ValidationException
     */
    protected function getValidatorInstance()
    {
        $this->modifyLoanUserRecords();
        return parent::getValidatorInstance();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            "office_division_id"=> "required|exists:office_divisions,id",
            "department_id"     => "required|exists:departments,id",
            "user_id"           => "required|exists:users,id",
            "loan_id"           => "required|exists:loans,id",
            "amount_paid"       => "required|integer",
            "month"             => "required",
            "year"              => "required|string"
        ];
    }

    /**
     * @throws ValidationException
     */
    protected function modifyLoanUserRecords(): void
    {
        $monthAndYear = explode("-", request()->month);
        $month = (int) $monthAndYear[0];
        $year = $monthAndYear[1];

        /*$user_id = User::find($this->request->get("user_id"))->id;
        $loanId = Loan::where("user_id", $user_id)->orderByDesc("id")->first();

        if(!isset($loanId)) {
            $error = ValidationException::withMessages([
                'employee' => array("Employee don't belongs to loans")
            ]);
            throw $error;
        }*/

        $this->request->add([
            #"loan_id"   => $loanId->id,
            "month"     => $month,
            "year"      => $year
        ]);
    }
}
