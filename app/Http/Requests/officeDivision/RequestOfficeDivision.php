<?php

namespace App\Http\Requests\officeDivision;

use Illuminate\Foundation\Http\FormRequest;

class RequestOfficeDivision extends FormRequest
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
        $officeDivision = $this->route('officeDivision');

        if ($officeDivision) {
            $validation = "required|min:3|max:100|string|unique:office_divisions,name," . $officeDivision->id;
        } else {
            $validation = "required|min:3|max:100|string|unique:office_divisions,name";
        }

        return ["name" => $validation];
    }
    public function messages()
    {
        return [
            'name.required' => 'The Division name field is required',
            'name.unique' => 'The Division has already been taken'
        ];
    }
}
