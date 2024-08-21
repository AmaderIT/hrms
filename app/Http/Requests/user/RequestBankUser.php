<?php

namespace App\Http\Requests\user;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class RequestBankUser extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return Auth::check();;
    }

    /**
     * @return Validator
     */
    protected function getValidatorInstance()
    {
        $this->modifyBankUserRecords();
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
            "user_id"       => "required|numeric|exists:users,id",
            "bank_id"       => "required|numeric|exists:banks,id",
            "account_name"  => "required|string|min:5|max:50",
            "account_no"    => "required|string|min:11|max:30"
        ];
    }

    /**
     * @return void
     */
    protected function modifyBankUserRecords(): void
    {
        $bankUser = $this->route("bankUser");

        if($bankUser) {
            $this->request->add([
                "user_id"   => $bankUser->user_id,
            ]);
        }
        else {
            $this->request->add([
                "user_id"   => request()->user->id
            ]);
        }
    }
}
