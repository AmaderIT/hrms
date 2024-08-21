<?php

namespace App\Http\Requests\district;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class RequestDistrict extends FormRequest
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
        $institute = $this->route('district');

        if ($institute) {
            $validation = "required|min:3|max:50|string|unique:districts,name," . $this->route('district')->id;
        } else {
            $validation = "required|min:3|max:50|string|unique:districts,name";
        }

        return [
            "name" => $validation,
            "division_id" => "required|exists:divisions,id"
        ];
    }
}
