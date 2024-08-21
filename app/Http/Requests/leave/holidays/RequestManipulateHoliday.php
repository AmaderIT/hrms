<?php

namespace App\Http\Requests\leave\holidays;

use App\Helpers\Common;
use App\Models\LeaveAllocation;
use App\Models\LeaveAllocationDetails;
use App\Models\User;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;

class RequestManipulateHoliday extends FormRequest
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
        $this->modifyHolidayManipulationRecords();
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
            "authorized_by" => "nullable|exists:users,id",
            "approved_by" => "nullable|exists:users,id",
            "status" => "nullable|integer|in:0,1,2,3"
        ];
    }

    /**
     * @return void
     */
    protected function modifyHolidayManipulationRecords(): void
    {
        $requestedApplication = request("requestedApplication");
        $halfDay = 0;
        $leave_start_time = null;
        $leave_end_time = null;
        $halfDaySlot = 0;

        if (!is_null($this->request->get("half_day_slot"))) {
            $halfDay = 1;
            $halfDaySlot = $this->request->get("half_day_slot");
            $halfDaySlots = Common::findOutWorkSlots($this->request->get("from_date"), $this->request->get('leave_request_type'),request("u_id"));
            $halfDaySlots = $halfDaySlots[(int)$this->request->get("half_day_slot")+6];

            $leave_start_time = date("h:i A",strtotime($halfDaySlots[0])) ?? null;
            $leave_end_time= date("h:i A",strtotime($halfDaySlots[1])) ?? null;
        }


        $this->request->add([
            "half_day" => $halfDay,
            "half_day_slot" => $halfDaySlot,
            "leave_start_time" => $leave_start_time,
            "leave_end_time" => $leave_end_time,
        ]);


        if ($requestedApplication) {
            if (auth()->user()->can("Authorize Leave Requests") and $requestedApplication->status === \App\Models\LeaveRequest::STATUS_PENDING) {
                $this->request->add([
                    "authorized_by" => auth()->user()->id
                ]);
            } elseif (auth()->user()->can("Approve Leave Requests") and $requestedApplication->status === \App\Models\LeaveRequest::STATUS_AUTHORIZED) {
                $this->request->add([
                    "approved_by" => auth()->user()->id
                ]);
            }

        }
    }
}
