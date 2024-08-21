<?php

namespace App\Http\Requests\user;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class RequestDegreeUser extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return Auth::check();
    }

    /**
     * @return Validator
     */
    protected function getValidatorInstance()
    {
        $this->modifyDegreeUserRecords();
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
            "degree_id"     => "required",
            "passing_year"  => "required",
            "result"        => "required|string"
        ];
    }

    /**
     * @return void
     */
    protected function modifyDegreeUserRecords(): void
    {
        $degreeUser = $this->route("degreeUser");

        if($degreeUser) {
            $this->request->add([
                "user_id"      => $degreeUser->user_id,
            ]);
        }
        else {
            $this->request->add([
                "user_id"      => request()->user->id
            ]);
        }
    }
}
