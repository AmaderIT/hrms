<?php

namespace App\Http\Requests\leave\holidays;

use Illuminate\Foundation\Http\FormRequest;

class RequestPublicHoliday extends FormRequest
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
        return [
            "holiday_id"=> "required|integer|exists:holidays,id",
            "from_date" => "required|date",
            "to_date"   => "required|date|after_or_equal:from_date",
            "remarks"   => "nullable|string|min:3|max:200"
        ];
    }
}
