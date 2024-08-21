<?php

namespace App\Http\Requests\leave\holidays;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;

class RequestWeeklyHoliday extends FormRequest
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
     * @return Validator
     */
    protected function getValidatorInstance()
    {
        $this->modifyWeeklyHolidayRecords();
        return parent::getValidatorInstance();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            "department_id" => "required|array",
            "days"          => "required|string",
            "effective_date"=> "required|date"
        ];
    }

    /**
     * @return void
     */
    protected function modifyWeeklyHolidayRecords(): void
    {
        $this->request->add([
            "days" => json_encode(request()->days)
        ]);
    }
}
