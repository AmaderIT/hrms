<?php

namespace App\Http\Requests\degree;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class RequestDegree extends FormRequest
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
        $degree = $this->route('degree');

        if ($degree) {
            $validation = "required|min:3|max:50|string|unique:degrees,name," . $this->route('degree')->id;
        } else {
            $validation = "required|min:3|max:50|string|unique:degrees,name";
        }

        return ["name" => $validation];
    }
}
