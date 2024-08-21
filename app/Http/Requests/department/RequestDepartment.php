<?php

namespace App\Http\Requests\department;

use App\Models\Department;
use Illuminate\Foundation\Http\FormRequest;
use App\Models\LeaveType;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;

class RequestDepartment extends FormRequest
{
    protected $leaveTypes = null;

    public function __construct()
    {
        if(!$this->leaveTypes) $this->leaveTypes = LeaveType::select("id", "name")->orderByDesc("id")->get();
    }

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
        $rules = [
            "office_division_id"    => "required|exists:office_divisions,id",
            "name"                  => "required|min:3|max:255|string",
            "days"                  => "required",
            "days.*"                => 'in:fri,sat,sun,mon,tue,wed,thu',
            "year"                  => "required|integer",
            "deduction_day"         => "required|min:1|max:50|integer",
            'type'                  => 'required|in:leave,salary',
            'is_relax_day_setting'  => 'required_with:relax_day_type,max_count_per_month,weekly_days',
            'relax_day_type'        => 'required_with:is_relax_day_setting,max_count_per_month,weekly_days',
            'max_count_per_month'   => 'required_with:is_relax_day_setting,relax_day_type,weekly_days|max:5|min:0',
            'weekly_days'           => 'required_with:is_relax_day_setting,max_count_per_month,relax_day_type|array',
            'weekly_days.*'         => 'sometimes|required_with:is_relax_day_setting,max_count_per_month,relax_day_type|string|in:fri,sat,sun,mon,tue,wed,thu',
        ];
        if(isset($this->is_warehouse) && $this->is_warehouse==1){
            $rules['is_warehouse'] = 'required';
            $rules['warehouse_id'] = 'required';
        }

        $department = $this->route('department');
        if ($department) {
            if($this->leaveTypes->isNotEmpty()){
                foreach ($this->leaveTypes as $key => $item) {
                    $rules["leave_days.{$key}.value"] = "required|integer|min:0";
                }
            }
            $rules["total_days"] = "required|min:1|max:50|integer";
        } else {
            if($this->leaveTypes->isNotEmpty()){
                foreach ($this->leaveTypes as $key => $item) {
                    $rules["leave_days.{$key}"] = "required|integer|min:0";
                }
            }
            $rules["total_days"] = "nullable|min:1|max:50|integer";
        }



        return $rules;
    }
    public function messages()
    {
        $msg = [
            'office_division_id.required' => 'The division field is required',
            'name.required' => 'The department name field is required',
            'days.required' => 'The weekly holidays field is required',
            "total_days.required" => 'The days late field is required',
            "total_days.min" => 'The days late must be at least 1',
            "deduction_day.required" => 'The equivalent working day field is required',
            "deduction_day.min" => 'The equivalent working day must be at least 1',
            "type.required" => 'The deduction method type is invalid',
            "is_relax_day_setting.required_with" => 'Relax day must be enabled',
            "relax_day_type.required_with"      => 'Consumable type must be selected',
            "max_count_per_month.required_with" => 'Max consumable day(s) per month must be selected',
            "weekly_days.required_with"         => 'Consumable day(s) in week must be selected',
        ];

        $department = $this->route('department');
        if ($department) {
            if($this->leaveTypes->isNotEmpty()){
                foreach ($this->leaveTypes as $key => $item) {
                    $msg["leave_days.{$key}.value.required"] = "The {$item->name} leave is required";
                    $msg["leave_days.{$key}.value.min"] = "The {$item->name} leave must be at least 0";
                }
            }
        } else {
            if($this->leaveTypes->isNotEmpty()){
                foreach ($this->leaveTypes as $key => $item) {
                    $msg["leave_days.{$key}.required"] = "The {$item->name} leave is required";
                    $msg["leave_days.{$key}.min"] = "The {$item->name} leave must be at least 0";
                }
            }
        }
        return $msg;
    }
}
