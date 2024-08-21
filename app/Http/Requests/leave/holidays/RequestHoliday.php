<?php

namespace App\Http\Requests\leave\holidays;

use Illuminate\Foundation\Http\FormRequest;

class RequestHoliday extends FormRequest
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
        $holiday = $this->route("holiday");

        if ($holiday) {
            $validation = "required|min:3|max:50|string|unique:holidays,name," . $holiday->id;
        } else {
            $validation = "required|min:3|max:50|string|unique:holidays,name";
        }

        return [
            "name" => $validation
        ];
    }
}
