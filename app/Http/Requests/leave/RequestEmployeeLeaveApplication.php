<?php

namespace App\Http\Requests\leave;

use App\Models\Department;
use App\Models\DepartmentSupervisor;
use App\Models\LeaveRequest;
use App\Models\User;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Spatie\Permission\Models\Role;

class RequestEmployeeLeaveApplication extends FormRequest
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
        $this->modifyEmployeeLeaveRequestRecords();
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
            "user_id"                       => "required|exists:users,id",
            "office_division_id"            => "required|exists:office_divisions,id",
            "department_id"                 => "required|exists:departments,id",
            "leave_allocation_details_id"   => "required|exists:leave_allocation_details,id",
            "leave_type_id"                 => "required|exists:leave_types,id",
            "half_day"                      => "required|boolean",
            "from_date"                     => "required|date",
            "to_date"                       => "required|date|after_or_equal:from_date",
            "number_of_days"                => "required|numeric|between:0.5,30",
            "applied_by"                    => "required|exists:users,id",
            "applied_to"                    => "required|exists:users,id",
            "approved_by"                   => "nullable|exists:users,id",
            "purpose"                       => "required|string|min:3|max:200",
            "status"                        => "required|integer|in:0,1,2"
        ];
    }

    /**
     * @return void
     */
    protected function modifyEmployeeLeaveRequestRecords(): void
    {
        if(!is_null($this->request->get("half_day"))) {
            $half_day       = true;
            $to_date        = $this->request->get("from_date");
            $number_of_days = 0.5;
        }
        else {
            $half_day       = false;
            $to_date        = $this->request->get("to_date");
            $number_of_days = $this->request->get("number_of_days");
        }

        $departmentId = User::with("currentPromotion")->whereId($this->request->get("user_id"))->first()->currentPromotion->department_id;
        $appliedTo = DepartmentSupervisor::where("department_id", $departmentId)->first()->supervised_by;

        $currentPromotion = User::with("currentPromotion")->where("id", $this->request->get("user_id"))->first()->currentPromotion;

        $this->request->add([
            "office_division_id"    => $currentPromotion->office_division_id,
            "department_id"         => $currentPromotion->department_id,
            "applied_by"            => auth()->user()->id,
            "applied_to"            => $appliedTo,
            "approved_by"           => null,
            "half_day"              => $half_day,
            "to_date"               => $to_date,
            "number_of_days"        => $number_of_days,
            "status"                => LeaveRequest::STATUS_PENDING
        ]);
    }
}
