<?php

namespace App\Http\Requests\action;

use Illuminate\Foundation\Http\FormRequest;

class RequestActionType extends FormRequest
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
        $actionType = $this->route('actionType');

        if ($actionType) {
            $name = "required|min:3|max:50|string|unique:action_reasons,name," . $actionType->id;
        } else {
            $name = "required|min:3|max:50|string|unique:action_reasons,name";
        }

        return [
            "parent_id" => "nullable|integer",
            "name"      => $name,
            "reason"    => "nullable"
        ];
    }
}
