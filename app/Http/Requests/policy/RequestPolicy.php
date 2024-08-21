<?php

namespace App\Http\Requests\policy;

use Illuminate\Foundation\Http\FormRequest;

class RequestPolicy extends FormRequest
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
        $policy = $this->route('policy');

        if ($policy) {
            $validation = "required|min:3|max:200|string|unique:policies,title," . $policy->id;
        } else {
            $validation = "required|min:3|max:200|string|unique:policies,title";
        }

        return [
            "title" => $validation,
            "order_no" => "nullable|integer",
            "attachment" => "nullable|mimes:jpg,jpeg,png,pdf|max:2048",
            "description" => "nullable",
        ];
    }

    public function messages()
    {
        return [
            'title.required' => 'The Policy Title field is required',
            'title.unique' => 'The Policy has already been taken'
        ];
    }
}
