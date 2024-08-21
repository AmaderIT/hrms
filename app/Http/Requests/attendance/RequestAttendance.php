<?php

namespace App\Http\Requests\attendance;

use App\Models\Roster;
use App\Models\Setting;
use App\Models\User;
use App\Models\WorkSlot;
use Carbon\Carbon;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;


class RequestAttendance extends FormRequest
{
    protected $garbageYear = 1970;

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
        $this->modifyAttendanceRequestRecords();

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
            "emp_code" => "required|integer|exists:users,fingerprint_no|bail",
            "user_id" => "required|integer|exists:users,id|bail",
            "time_in" => "required|date",
            "time_out" => "required|date|after_or_equal:time_in",
        ];
    }

    /**
     * @return void
     */
    protected function modifyAttendanceRequestRecords(): void
    {
        $userId = User::where("fingerprint_no", $this->get("emp_code"))->first();
        $timeIn = !is_null($this->get("time_in")) ? date("Y-m-d H:i", strtotime($this->get("time_in"))) : null;
        $timeOut = !is_null($this->get("time_out")) ? date("Y-m-d H:i", strtotime($this->get("time_out"))) : null;
        if (date("Y", strtotime($timeIn)) <= $this->garbageYear || date("Y", strtotime($timeOut)) <= $this->garbageYear) {
            throw ValidationException::withMessages(['time' => 'Invalid Date & Time Format.']);
        }
        $attendanceCountStartHour = Setting::where("name", "attendance_count_start_hour")->select("id", "value")->first()->value;
        $date = date("Y-m-d", strtotime($this->get("time_in")));
        $endDate = date('Y-m-d', strtotime('+1 day', strtotime($date)));
        $time_in_in_sec = strtotime($timeIn.':00');
        $time_out_in_sec = strtotime($timeOut.':00');
        $sql_promotion = "SELECT users.id, users.fingerprint_no, promotions.office_division_id, promotions.department_id, promotions.designation_id, promotions.promoted_date, promotions.type, promotions.salary, promotions.workslot_id, promotions.pay_grade_id FROM users INNER JOIN promotions ON promotions.user_id = users.id AND promotions.id =( SELECT MAX( pm.id) FROM `promotions` AS pm WHERE pm.user_id = users.id AND pm.promoted_date <= '$date' ) WHERE /*users.`status` = 1 AND*/ users.`id` = $userId->id";
        $promotion_record = DB::select($sql_promotion);
        $department_id = $promotion_record[0]->department_id;
        $approved_value = Roster::STATUS_APPROVED;
        $sql_roster = "SELECT * FROM `rosters` WHERE `active_date` = '$date' AND `status` = $approved_value AND deleted_at IS NULL AND (`user_id` = $userId->id OR `department_id` = $department_id)";
        $roster_records = DB::select($sql_roster);
        $roster_department = [];
        $roster_user = [];
        foreach($roster_records as $each_roster){
            if($each_roster->user_id){
                $roster_user['work_slot_id'] = $each_roster->work_slot_id;
            }else{
                $roster_department['work_slot_id'] = $each_roster->work_slot_id;
            }
        }
        if($roster_user){
            $work_slot_id = $roster_user['work_slot_id'];
        }else{
            if($roster_department){
                $work_slot_id = $roster_department['work_slot_id'];
            }else{
                $work_slot_id = $promotion_record[0]->workslot_id;
            }
        }
        $work_slot = WorkSlot::find($work_slot_id);
        $attendanceCountStartHour_in_sec = $attendanceCountStartHour*60*60;
        $start_time_in_sec = strtotime($date." ".$work_slot->start_time);
        $actual_start_time_in_sec = $start_time_in_sec;
        $actualStartDateTime=date('Y-m-d H:i:s',$actual_start_time_in_sec);
        $start_time_in_sec = $start_time_in_sec-$attendanceCountStartHour_in_sec;
        $end_time_in_sec = $start_time_in_sec + 86399;
        $startDateTime=date('Y-m-d H:i:s',$start_time_in_sec);
        $endDateTime=date('Y-m-d H:i:s',$end_time_in_sec);
        if($work_slot->is_flexible){
            if(isset($work_slot->total_work_hour)){
                $actual_end_time_in_sec = $actual_start_time_in_sec + ($work_slot->total_work_hour*60*60);
            }else{
                $actual_end_time_in_sec = strtotime($date.' 23:59:59');
            }
        }else{
            $actual_end_time_in_sec = strtotime($date." ".$work_slot->end_time);
            if($actual_start_time_in_sec>=$actual_end_time_in_sec){
                $actual_end_time_in_sec = strtotime($endDate." ".$work_slot->end_time);
            }
        }
        $actualEndDateTime=date('Y-m-d H:i:s',$actual_end_time_in_sec);
        if($time_in_in_sec<$start_time_in_sec){
            throw ValidationException::withMessages(['time' => 'Invalid Entry Datetime! Entry Datetime should be started from '.$startDateTime]);
        }
        if($time_in_in_sec>=$actual_end_time_in_sec){
            throw ValidationException::withMessages(['time' => 'Invalid Entry Datetime! Entry Datetime should be started before '.$actualEndDateTime]);
        }
        if($time_out_in_sec>$end_time_in_sec){
            throw ValidationException::withMessages(['time' => 'Invalid Exit Datetime! Exit Datetime should be started before '.$endDateTime]);
        }
        if($time_out_in_sec<=$actual_start_time_in_sec){
            throw ValidationException::withMessages(['time' => 'Invalid Exit Datetime! Exit Datetime should be started from '.$actualStartDateTime]);
        }
        $this->request->add([
            "user_id" => $userId->id,
            "time_in" => $timeIn,
            "time_out" => $timeOut,
        ]);
    }


    public function attributes()
    {
        return [
            "time_in" => "Entry Date & Time",
            "time_out" => "Exit Date & Time",
        ];
    }


}
