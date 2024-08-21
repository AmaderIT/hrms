<?php

namespace App\Http\Requests\designation;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class RequestDesignation extends FormRequest
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
        $designation = $this->route('designation');

        if ($designation) {
            $validation = "required|min:3|max:100|string|unique:designations,title," . $this->route('designation')->id;
        } else {
            $validation = "required|min:3|max:100|string|unique:designations,title";
        }
        $rules=[
            "title" => $validation
        ];
        return $rules;
    }
    public function messages()
    {
        return [
            'title.required' => 'The designation name field is required',
            'title.unique' => 'The designation has already been taken'
        ];
    }
}
