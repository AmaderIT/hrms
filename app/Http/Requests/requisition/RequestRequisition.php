<?php

namespace App\Http\Requests\requisition;

use Illuminate\Foundation\Http\FormRequest;

class RequestRequisition extends FormRequest
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
            "department_id"         => "required|exists:departments,id",
            "requisition_item_id.*" => "nullable",
            "priority"              => "required|numeric",
            "quantity"              => "required|min:1",
            "remarks"               => "nullable"
        ];
    }
}
