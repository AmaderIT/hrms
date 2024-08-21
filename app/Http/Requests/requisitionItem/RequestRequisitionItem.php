<?php

namespace App\Http\Requests\requisitionItem;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;

class RequestRequisitionItem extends FormRequest
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
        $requisitionItem = $this->route('requisitionItem');

        if ($requisitionItem) {
            $name = "required|min:3|max:250|string|unique:requisition_items,name," . $requisitionItem->id;
            $code = "nullable|min:3|max:250|string";
        } else {
            $name = "required|min:3|max:250|string|unique:requisition_items,name";
            $code = "nullable|min:3|max:250|string";
        }

        return [
            "name"      => $name,
            "code"      => $code
        ];
    }
}
