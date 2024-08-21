<?php

namespace App\Http\Requests\bank;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class RequestBank extends FormRequest
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
        $bank = $this->route('bank');

        if ($bank) {
            $validation = "required|min:3|max:50|string|unique:banks,name," . $this->route('bank')->id;
        } else {
            $validation = "required|min:3|max:50|string|unique:banks,name";
        }

        return ["name" => $validation];
    }
}
