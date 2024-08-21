<?php

namespace App\Http\Requests\tax;

use App\Models\Tax;
use Illuminate\Foundation\Http\FormRequest;

class RequestTax extends FormRequest
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
        $tax = $this->route("tax");

        if ($tax) {
            $validation = "required|min:3|max:50|string|unique:taxes,name," . $tax->id;
        } else {
            $validation = "required|min:3|max:50|string|unique:taxes,name";
        }

        return [
            "name"      => $validation
        ];
    }
}
