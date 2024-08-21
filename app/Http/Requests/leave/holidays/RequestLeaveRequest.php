<?php

namespace App\Http\Requests\leave\holidays;

use App\Helpers\Common;
use App\Models\DepartmentSupervisor;
use App\Models\LeaveRequest;
use App\Models\User;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Spatie\Permission\Models\Role;

class RequestLeaveRequest extends FormRequest
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
        $this->modifyLeaveRequestRecords();
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
            "leave_type_id" => "required|integer|exists:leave_types,id",
            "from_date" => "required|date",
            "to_date" => "required|date|after_or_equal:from_date",
            "leave_start_time" => "nullable|date_format:h:i A",
            "leave_end_time" => "nullable|date_format:h:i A|after_or_equal:leave_start_time",
            "number_of_days" => "required|numeric|between:0.5,30",
            "purpose" => "required|string|min:3|max:255",
        ];
    }

    /**
     * @return void
     */
    protected function modifyLeaveRequestRecords(): void
    {
        if (!is_null($this->request->get("half_day_slot"))) {
            $half_day = 1;
            $to_date = $this->request->get("from_date");

            $halfDaySlots = Common::findOutWorkSlots($this->request->get("from_date"), $this->request->get('leave_request_type'), auth()->user()->id);
            $halfDaySlots = $halfDaySlots[(int)$this->request->get("half_day_slot")+6];

            $leave_start_time = date("h:i A",strtotime($halfDaySlots[0])) ?? null;
            $leave_end_time= date("h:i A",strtotime($halfDaySlots[1])) ?? null;
            $number_of_days = 0.5;
        } else {
            $half_day = 0;
            $to_date = $this->request->get("to_date");

            $number_of_days = $this->request->get("number_of_days");
        }


        $departmentId = User::with("currentPromotion")->whereId(auth()->user()->id)->first()->currentPromotion->department_id;

        $appliedTo = DepartmentSupervisor::where("department_id", $departmentId)->first()->supervised_by ?? null;


        $this->request->add([
            "user_id" => auth()->user()->id,
            "office_division_id" => auth()->user()->currentPromotion->office_division_id,
            "department_id" => auth()->user()->currentPromotion->department_id,
            "applied_to" => $appliedTo,
            "authorized_by" => null,
            "approved_by" => null,
            "half_day" => $half_day,
            "to_date" => $to_date,
            "leave_start_time" => $leave_start_time ?? null,
            "leave_end_time" => $leave_end_time ?? null,
            "number_of_days" => $number_of_days,
            "applied_by" => auth()->user()->id,
            "status" => LeaveRequest::STATUS_PENDING
        ]);

    }
}
