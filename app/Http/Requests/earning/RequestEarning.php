<?php

namespace App\Http\Requests\earning;

use Illuminate\Foundation\Http\FormRequest;

class RequestEarning extends FormRequest
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
        $earning = $this->route("earning");

        if(isset($earning)) $name = "required|string|min:1|max:100|unique:earnings,name," . $earning->id;
        else $name = "required|string|min:1|max:100|unique:earnings,name";

        return [
            "name" => $name
        ];
    }
}
