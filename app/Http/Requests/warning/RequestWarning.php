<?php

namespace App\Http\Requests\warning;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class RequestWarning extends FormRequest
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
        $this->modifyWarningRecords();
        return parent::getValidatorInstance();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $warning = $this->route('warning');

        if ($warning) {
            $validation = "required|min:3|max:50|string|unique:warnings,memo_no," . $this->route('warning')->id;
        } else {
            $validation = "required|min:3|max:50|string|unique:warnings,memo_no";
        }

        return [
            "memo_no"       => $validation,
            "user_id"       => "required|exists:users,id",
            "level"         => "required|in:1,2,3,4,5",
            "subject"       => "required|max:100",
            "description"   => "required|max:300",
            "warned_by"     => "required|exists:users,id",
            "warning_date"  => "required",
            "updated_by"    => "required|exists:users,id"
        ];
    }

    /**
     * @return void
     */
    protected function modifyWarningRecords(): void
    {
        $this->request->add([
            "updated_by"   => auth()->user()->id,
        ]);
    }
}
