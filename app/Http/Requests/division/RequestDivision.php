<?php

namespace App\Http\Requests\division;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class RequestDivision extends FormRequest
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
        $division = $this->route('division');

        if ($division) {
            $validation = "required|min:3|max:50|string|unique:divisions,name," . $this->route('division')->id;
        } else {
            $validation = "required|min:3|max:50|string|unique:divisions,name";
        }

        return ["name" => $validation];
    }
}
