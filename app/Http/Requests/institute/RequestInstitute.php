<?php

namespace App\Http\Requests\institute;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class RequestInstitute extends FormRequest
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
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $institute = $this->route('institute');

        if ($institute) {
            $validation = "required|min:3|max:100|string|unique:institutes,name," . $this->route('institute')->id;
        } else {
            $validation = "required|min:3|max:100|string|unique:institutes,name";
        }

        $rules = [
            "name" => $validation
        ];
        return $rules;
    }
    public function messages()
    {
        return [
            'name.required' => 'The institute name field is required',
            'name.unique' => 'The institute has already been taken'
        ];
    }
}
