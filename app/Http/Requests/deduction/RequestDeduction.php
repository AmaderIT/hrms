<?php

namespace App\Http\Requests\deduction;

use App\Models\Deduction;
use Illuminate\Foundation\Http\FormRequest;

class RequestDeduction extends FormRequest
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
        $deduction = $this->route("deduction");

        if(isset($deduction)) $name = "required|string|min:1|max:100|unique:deductions,name," . $deduction->id;
        else $name = "required|string|min:1|max:100|unique:deductions,name";

        return [
            "name" => $name
        ];
    }
}
