<?php

namespace App\Http\Controllers;

use App\Http\Requests\attendance\RequestAttendance;
use App\Models\HolidayAllowance;
use App\Models\LateDeduction;
use App\Models\LeaveRequest;
use App\Models\LeaveUnpaid;
use App\Models\Overtime;
use App\Models\Promotion;
use App\Models\PublicHoliday;
use App\Models\Roaster;
use App\Models\Roster;
use App\Models\User;
use App\Models\UserLate;
use App\Models\WeeklyHoliday;
use App\Models\WorkSlot;
use App\Models\ZKTeco\DailyAttendance;
use Carbon\Carbon;
use DateTime;
use Illuminate\Contracts\View\Factory;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Exception;

class AttendanceController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * @return Factory|View
     */
    public function dailyAttendance()
    {
        /*$employees = User::active()->get();*/
        $employees = User::get();
        return view("attendance.daily-attendance", compact("employees"));
    }

    /**
     * @param RequestAttendance $request
     * @return RedirectResponse
     */
    public function storeDailyAttendance(RequestAttendance $request)
    {
        $this->syncLate($request);
        DB::beginTransaction();
        try {
            $inserted = $this->insertDailyAttendance($request);
            if($inserted === true) {
//                $this->syncOverTime($request);
                $this->syncLate($request);
                $this->syncHolidayAllowance($request);
                $this->syncLeaveUnpaid($request);

                session()->flash("message", "Attendance Saved Successfully");
            } else {
                session()->flash("type", "error");
                session()->flash("message", "Sorry! Data already exists!!");
            }
            DB::commit();
        } catch (Exception $exception) {
            DB::rollBack();
            session()->flash("type", "error");
            session()->flash("message", "Sorry! Something went wrong!!");
        }

        return redirect()->back();
    }

    /**
     * @param $request
     *
     * @return bool
     */
    protected function insertDailyAttendance($request): bool
    {
        try{
            $date = date("Y-m-d", strtotime($request->input("time_in")));
            $endDate = date('Y-m-d', strtotime('+1 day', strtotime($date)));
            $sql_promotion = "SELECT users.id, users.fingerprint_no, promotions.office_division_id, promotions.department_id, promotions.designation_id, promotions.promoted_date, promotions.type, promotions.salary, promotions.workslot_id, promotions.pay_grade_id FROM users INNER JOIN promotions ON promotions.user_id = users.id AND promotions.id =( SELECT MAX( pm.id) FROM `promotions` AS pm WHERE pm.user_id = users.id AND pm.promoted_date <= '$date' ) WHERE /*users.`status` = 1 AND*/ users.`id` = $request->user_id";
            $promotion_record = DB::select($sql_promotion);
            $department_id = $promotion_record[0]->department_id;
            $is_public_holiday = false;
            $is_weekly_holiday = false;
            $sql_weekly_holiday = "SELECT * FROM `weekly_holidays` WHERE `effective_date` <= '$date' AND (`end_date` >= '$date' OR `end_date` IS NULL) AND department_id = $department_id";
            $results = DB::select($sql_weekly_holiday);
            $weekly_holidays = [];
            foreach ($results as $result) {
                $weekly_holidays[$result->department_id]['days'] = $result->days;
            }
            $text_represent_of_this_day = strtolower(date('D', strtotime($date)));
            $approved_value = Roster::STATUS_APPROVED;
            $sql_roster = "SELECT * FROM `rosters` WHERE `active_date` = '$date' AND `status` = $approved_value AND deleted_at IS NULL AND (`user_id` = $request->user_id OR `department_id` = $department_id)";
            $roster_records = DB::select($sql_roster);
            $roster_department = [];
            $roster_user = [];
            foreach($roster_records as $each_roster){
                if($each_roster->user_id){
                    $roster_user['is_weekly_holiday'] = $each_roster->is_weekly_holiday;
                    $roster_user['work_slot_id'] = $each_roster->work_slot_id;
                }else{
                    $roster_department['is_weekly_holiday'] = $each_roster->is_weekly_holiday;
                    $roster_department['work_slot_id'] = $each_roster->work_slot_id;
                }
            }
            $employee_weekend=[];
            if($roster_user){
                $work_slot_id = $roster_user['work_slot_id'];
                if($roster_user['is_weekly_holiday']){
                    $employee_weekend=[$text_represent_of_this_day];
                }
            }else{
                if($roster_department){
                    $work_slot_id = $roster_department['work_slot_id'];
                    if($roster_department['is_weekly_holiday']){
                        $employee_weekend=[$text_represent_of_this_day];
                    }
                }else{
                    $work_slot_id = $promotion_record[0]->workslot_id;
                    $weekend_days = $weekly_holidays[$promotion_record[0]->department_id]['days'] ?? '';
                    $weekend_days = substr($weekend_days, 2);
                    $weekend_days = substr($weekend_days, 0, -2);
                    $employee_weekend = explode('","',$weekend_days);
                }
            }
            $work_slot = WorkSlot::find($work_slot_id);
            $all_public_holiday_records = PublicHoliday::where("from_date", "<=", $date)
                ->where("to_date", ">=", $date)
                ->get();
            if ($all_public_holiday_records->count() > 0) {
                $is_public_holiday = true;
            }
            $holiday_count = $is_public_holiday;
            if (in_array($text_represent_of_this_day, $employee_weekend)) {
                $is_weekly_holiday = true;
                $holiday_count = true;
            }
            $late_in_min=0;
            $overtime_min = null;
            $is_late = false;
            $date_time=date_create($request->time_in);
            $entry_time_in_sec = strtotime(date_format($date_time,"Y-m-d H:i:s"));
            if($work_slot->is_flexible){
                $end_time_in_sec = $entry_time_in_sec + ($work_slot->total_work_hour*60*60);
                $late_time_in_sec = $entry_time_in_sec + 1;
                $start_time_in_sec = $entry_time_in_sec;
            }else{
                $start_time_in_sec = strtotime($date." ".$work_slot->start_time);
                $end_time_in_sec = strtotime($date." ".$work_slot->end_time);
                if($start_time_in_sec>=$end_time_in_sec){
                    $end_time_in_sec = strtotime($endDate." ".$work_slot->end_time);
                }
                $late_time_in_sec = strtotime($date." ".$work_slot->late_count_time);
                if($start_time_in_sec>$late_time_in_sec){
                    $late_time_in_sec = strtotime($endDate." ".$work_slot->late_count_time);
                }
            }
            if($late_time_in_sec<$entry_time_in_sec){
                $is_late=true;
                $late_in_min = round(abs($entry_time_in_sec - $late_time_in_sec) / 60,2);
            }
            $date_time_out=date_create($request->time_out);
            $exit_time_in_sec = strtotime(date_format($date_time_out,"Y-m-d H:i:s"));
            $working_min = round(abs($exit_time_in_sec - $entry_time_in_sec) / 60,2);
            if($work_slot->over_time == 'Yes'){
                $actual_work_start_time = $start_time_in_sec;
                if($work_slot->is_flexible){
                    $actual_work_end_time = $end_time_in_sec;
                }else{
                    $actual_work_end_time = strtotime("$date $work_slot->overtime_count");
                    if($end_time_in_sec>$actual_work_end_time){
                        $actual_work_end_time = strtotime($endDate." ".$work_slot->overtime_count);
                    }
                }
                $actual_work_time_in_sec = $actual_work_end_time - $actual_work_start_time;
                if($exit_time_in_sec>$actual_work_end_time){
                    $working_sec = $working_min*60;
                    if($working_sec>$actual_work_time_in_sec){
                        $extra_sec = $working_sec-$actual_work_time_in_sec;
                        if(!$work_slot->is_flexible){
                            if($actual_work_start_time>$entry_time_in_sec){
                                $extra_sec = $extra_sec - ($actual_work_start_time-$entry_time_in_sec);
                            }
                        }
                        if($extra_sec>0){
                            $overtime_min = round(abs($extra_sec) / 60,2);
//                            $ot_rule = (int) ($overtime_min / 30);
//                            $overtime_min = $ot_rule*30;
                        }
                    }
                }
            }
            $all_leave_records = LeaveRequest::where('status',LeaveRequest::STATUS_APPROVED)
                ->where("user_id", "=", $request->user_id)
                ->where("from_date", "<=", $date)
                ->where("to_date", ">=", $date)
                ->get();
            $employee_leave_record = [];
            foreach($all_leave_records as $record){
                $total_approved_days = $record->number_of_days - ($record->number_of_unpaid_days ?? 0);
                $total_unpaid_days = $record->number_of_days - $total_approved_days;
                $begin = strtotime($record->from_date);
                $end = strtotime($record->to_date);
                for ( $i = $begin; $i <= $end; $i = $i + 86400 ) {
                    $thisDate = date( 'Y-m-d', $i );
                    if($total_approved_days>=1){
                        if($thisDate == $date) {
                            $employee_leave_record['full']= $thisDate;
                        }
                        $total_approved_days--;
                    }else{
                        if($total_approved_days==0.5) {
                            if($thisDate == $date) {
                                $employee_leave_record['half'] = $thisDate;
                                $employee_leave_record['half_day_slot'] = $record->half_day_slot;
                                $employee_leave_record['half_leave_start'] = $record->leave_start_time;
                                $employee_leave_record['half_leave_end'] = $record->leave_end_time;
                            }
                            $total_approved_days=0;
                            if($total_unpaid_days > 0){
                                if($thisDate == $date) {
                                    $employee_leave_record['half_unpaid'] = true;
                                }
                                $total_unpaid_days = $total_unpaid_days - 0.5;
                            }
                        } else{
                            if($total_unpaid_days > 0){
                                if ($total_unpaid_days >= 1) {
                                    if($thisDate == $date) {
                                        $employee_leave_record['full_unpaid'] = true;
                                    }
                                    $total_unpaid_days--;
                                }else{
                                    if($thisDate == $date) {
                                        $employee_leave_record['half_unpaid'] = true;
                                    }
                                    $total_unpaid_days = 0;
                                }
                            }
                        }
                    }
                }
            }
            $leave_count = 0;
            $absent_count = 0;
            $present_count=1;
            if(isset($employee_leave_record['full']) && $employee_leave_record['full']==$date){
                $leave_count = 1;
                $present_count = 0;
                $is_late_final = false;
                $late_min_final = 0;
            }elseif(isset($employee_leave_record['half']) && $employee_leave_record['half']==$date){
                $leave_count = 0.5;
                if(isset($employee_leave_record['half_unpaid'])){
                    $absent_count = 0.5;
                    $present_count = 0;
                }else{
                    $present_count = 0.5;
                }
                if($is_late){
                    if(!is_null($employee_leave_record['half_leave_start'])){
                        if($employee_leave_record['half_day_slot']==1){
                            $half_leave_end = date("H:i", strtotime($employee_leave_record['half_leave_end']));
                            $half_leave_end_in_sec = strtotime($date.' '.$half_leave_end.':00');
                            $day_start_time_in_sec = strtotime($date." ".$work_slot->start_time);
                            $buffer = ($late_time_in_sec-$day_start_time_in_sec);
                            if($buffer>0){
                                $buffer = (int) $buffer/2;
                            }
                            if(($half_leave_end_in_sec+$buffer)<$entry_time_in_sec){
                                $is_late_final = true;
                                $late_mins = $entry_time_in_sec-($half_leave_end_in_sec+$buffer);
                                $late_min_final = round(abs($late_mins) / 60,2);
                            }else{
                                $is_late_final = false;
                                $late_min_final = 0;
                            }
                        }else{
                            $is_late_final = $is_late;
                            $late_min_final = $late_in_min;
                        }
                    }else{
                        $is_late_final = false;
                        $late_min_final = 0;
                    }
                }else{
                    $is_late_final = false;
                    $late_min_final = 0;
                }
            }else{
                if(isset($employee_leave_record['half_unpaid'])){
                    $absent_count = 0.5;
                    $present_count = 0.5;
                    $is_late_final = $is_late;
                    $late_min_final = $late_in_min;
                }
                elseif(isset($employee_leave_record['full_unpaid'])){
                    $absent_count = 1;
                    $present_count = 0;
                    $is_late_final = false;
                    $late_min_final = 0;
                }else{
                    $is_late_final = $is_late;
                    $late_min_final = $late_in_min;
                }
            }
            $attendance = DailyAttendance::where("user_id", $request->input("user_id"))
                ->where("date", '=', $date)
                ->first();
            $attendance_summary = [
                "time_in" => $request->time_in,
                "time_out"=> $request->time_out,
                "roaster_start_time" => $work_slot->start_time,
                "roaster_end_time" => $work_slot->end_time ?? '',
                "late_count_time" => $work_slot->late_count_time ?? '',
                "is_late_day" => $is_late,
                "late_in_min" => $late_in_min,
                "working_min" => $working_min,
                "is_ot_available" => ($work_slot->over_time == 'Yes') ? 1 : 0,
                "overtime_min" => $overtime_min,
                "present_count" => $present_count,
                "is_late_final" => $is_late_final,
                "late_min_final" => $late_min_final,
                'holiday_count' => $holiday_count,
                'absent_count' => $absent_count,
                'leave_count' => $leave_count,
                'is_public_holiday' => $is_public_holiday,
                'is_weekly_holiday' => $is_weekly_holiday,
                'is_relax_day' => false
            ];
            if(is_null($attendance)) {
                $attendance_summary["user_id"] = $promotion_record[0]->id;
                $attendance_summary["emp_code"] = $promotion_record[0]->fingerprint_no;
                $attendance_summary["date"] = $date;
                DailyAttendance::create($attendance_summary);
                $response = true;
            } else {
                DailyAttendance::where('user_id','=',$promotion_record[0]->id)->where('date','=',$date)->update($attendance_summary);
                $response = true;
            }
        }catch (Exception $exception){
            $response = false;
        }
        return $response;
    }

    /**
     * @param $request
     *
     * @return void
     */
    protected function syncOverTime($request): void
    {
        $date = date("Y-m-d", strtotime($request->input("time_in")));

        $timeIn = date("Y-m-d H:i", strtotime($request->input("time_in")));
        $timeOut = date("Y-m-d H:i", strtotime($request->input("time_out")));
        $inOffice   = (strtotime($timeOut) - strtotime($timeIn)) / (60 * 60);

        $workSlot = $this->getEmployeeWorkSlot($request->input("user_id"), $date);

        $startTime  = $workSlot->start_time;
        $endTime    = $workSlot->end_time;
        $totalOfficeTime = (strtotime($endTime) - strtotime($startTime)) / (60 * 60);

        Overtime::where("user_id", $request->input("user_id"))->where("overtime_date", $date)->delete();

        if (($inOffice - $totalOfficeTime) >= 1) {
            $eligibleOverTime = $inOffice - $totalOfficeTime;
            $eligibleOverTime = number_format($eligibleOverTime, 2);

            Overtime::create([
                "user_id"       => $request->input("user_id"),
                "overtime_date" => explode(" ", $request->input("in_time"))[0],
                "hours"         => $eligibleOverTime,
            ]);
        }
    }

    /**
     * @param $request
     *
     * @return void
     */
    protected function syncLate($request)
    {
        $date = date("Y-m-d", strtotime($request->input("time_in")));

        $workSlot = $this->getEmployeeWorkSlot($request->input("user_id"), $date);
        $lateCountTime = date("H:i:s", strtotime($workSlot->late_count_time));
        $timeIn = date("H:i:s", strtotime($request->input("time_in")));

        if($timeIn > $lateCountTime) {
            $userLate = UserLate::where("user_id", $request->input("user_id"))
                ->where("month", (int) date("m", strtotime($request->input("time_in"))))
                ->where("year", date("Y", strtotime($request->input("time_in"))))
                ->first();

            $user = User::with("currentPromotion")->where("id", $request->input("user_id"))->first();
            $currentPromotion = $user->currentPromotion;
            $lateDeduction = LateDeduction::where("department_id", $currentPromotion->department_id)->first();
            $totalDaysToDeduct = $lateDeduction->total_days ?? 0;

            if(!is_null($userLate)) {
                $totalLate =  $userLate->total_late + 1;
                $totalDeduction = $totalDaysToDeduct > 0 ? (int) floor($totalLate / $totalDaysToDeduct) : 0;

                UserLate::where("user_id", $request->input("user_id"))
                    ->where("month", (int) date("m", strtotime($request->input("time_in"))))
                    ->where("year", date("Y", strtotime($request->input("time_in"))))
                    ->update([
                        "total_late"        => $totalLate,
                        "total_deduction"   => $totalDeduction,
                    ]);
            } else {
                $totalLate = 1;
                $totalDeduction = $totalDaysToDeduct > 0 ? (int) floor($totalLate / $totalDaysToDeduct) : 0;

                UserLate::create([
                    "user_id"           => $request->input("user_id"),
                    "total_late"        => $totalLate,
                    "total_deduction"   => $totalDeduction,
                    "type"              => UserLate::TYPE_LEAVE,
                    "month"             => (int) date('m', strtotime($request->input("time_in"))),
                    "year"              => (int) date('Y', strtotime($request->input("time_in"))),
                ]);
            }
        }
    }

    /**
     * @param $request
     * @return void
     */
    protected function syncHolidayAllowance($request)
    {
        $date = date("Y-m-d", strtotime($request->input("time_in")));
        $holidayType = $this->getHolidayType($request->input("user_id"), $date);

        if(!is_null($holidayType)) {
            HolidayAllowance::create([
                "user_id"       => $request->input("user_id"),
                "holiday_date"  => $date,
                "type"          => $holidayType
            ]);
        }
    }

    /**
     * @param $request
     * @return void
     */
    protected function syncLeaveUnpaid($request)
    {
        $date = date("Y-m-d", strtotime($request->input("time_in")));
        LeaveUnpaid::where("user_id", $request->input("user_id"))->whereDate("leave_date", $date)->delete();
    }

    /**
     * @param $userId
     * @param $date
     * @return mixed
     */
    protected function getEmployeeWorkSlot($userId, $date)
    {
        $employee   = User::with("currentPromotion")->where("id", $userId)->first();
        $work_slot_id   = $employee->currentPromotion->workslot_id;
        $department_id   = $employee->currentPromotion->department_id;
        $approved_value = Roster::STATUS_APPROVED;
        $sql_roster = "SELECT * FROM `rosters` WHERE `active_date` = '$date' AND `status` = $approved_value AND deleted_at IS NULL AND (`user_id` = $userId OR `department_id` = $department_id)";
        $check_roaster = DB::select($sql_roster);
        $roster_department = [];
        $roster_user = [];
        foreach($check_roaster as $each_roster){
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
            }
        }
        $workSlot = WorkSlot::find($work_slot_id);
        return $workSlot;
    }

    /**
     * @param $userId
     * @param $date
     * @return string|null
     */
    protected function getHolidayType($userId, $date) {
        $user = User::with("currentPromotion")->where("id", $userId)->first();
        $currentPromotion = $user->currentPromotion;

        # Check Weekly Holiday
        $weeklyHoliday = WeeklyHoliday::where("department_id", $currentPromotion->department_id)->first();
        $weeklyHolidays = json_decode( $weeklyHoliday->days );

        $day = date("D", strtotime($date));

        # Check Public Holiday
        $publicHoliday = PublicHoliday::whereDate("from_date", "<=", $date)
            ->whereDate("to_date", ">=", $date)
            ->count();

        if(in_array(strtolower($day), $weeklyHolidays)) $type = HolidayAllowance::TYPE_WEEKLY;
        elseif ($publicHoliday > 0) $type = HolidayAllowance::TYPE_ORGANIZATIONAL;
        else $type = null;

        return $type;
    }
}
