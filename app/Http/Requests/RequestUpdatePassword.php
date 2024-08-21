<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RequestUpdatePassword extends FormRequest
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
            "current"   => "required|min:6|max:20",
            "new"       => "required|min:6|max:20|regex:/(?=.*[a-z])(?=.*[A-Z])(?=.*\d)[a-zA-Z\d]{1,}/|different:current"
        ];
    }
    public function messages()
    {
        return [
            'current.required' => 'The current password is required.',
            'current.min' => 'The current password must be at least 6 characters.',
            'current.max' => 'The current password may not be greater than 20 characters.',
            'new.required' => 'The new password is required.',
            'new.min' => 'The new password must be at least 6 characters.',
            'new.max' => 'The new password may not be greater than 20 characters.',
            'new.different' => 'The new and current password should be different.',
            'new.regex' => 'The new password contains at least 1 lowercase, 1 uppercase,1 numeric number.'
        ];
    }
}
