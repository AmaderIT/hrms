<?php

namespace App\Http\Controllers;

use App\Models\AssignRelaxDay;
use App\Models\DailyCronLog;
use App\Models\LeaveRequest;
use App\Models\PublicHoliday;
use App\Models\Roaster;
use App\Models\Roster;
use App\Models\Setting;
use App\Models\User;
use App\Models\WorkSlot;
use App\Models\ZKTeco\Attendance;
use App\Models\ZKTeco\DailyAttendance;
use Carbon\Carbon;
use DateTime;
use Illuminate\Support\Facades\DB;
use Exception;
use Illuminate\Support\Facades\Log;

class DailyAttendanceController extends Controller
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
     * @return array
     */
    public function generate($date)
    {
        DB::beginTransaction();
        try {
            $attendanceCountStartHour = Setting::where("name", "attendance_count_start_hour")->select("id", "value")->first()->value;
            if($date){
                $startDate = $date;
                $endDate = date('Y-m-d', strtotime('+1 day', strtotime($startDate)));
            }else{
                $startDate = date('Y-m-d', strtotime('-1 day', strtotime(date('Y-m-d'))));
                $endDate = date('Y-m-d');
            }
            $text_represent_of_this_day = strtolower(date('D',strtotime($startDate)));
            Log::info('attendanceCountStartHour : '.$attendanceCountStartHour);
            Log::info('startDate : '.$startDate);
            Log::info('endDate : '.$endDate);
            $sql_promotion = "SELECT users.id, users.fingerprint_no, promotions.user_id, promotions.office_division_id, promotions.department_id, promotions.designation_id, promotions.promoted_date, promotions.type, promotions.workslot_id, promotions.pay_grade_id FROM users INNER JOIN promotions ON promotions.user_id = users.id AND promotions.id =( SELECT MAX( pm.id) FROM `promotions` AS pm WHERE pm.user_id = users.id AND pm.promoted_date <= '$startDate' ) WHERE users.`status` = 1";
            $employees = DB::select($sql_promotion);
            Log::info(count($employees));
            $all_leave_records = LeaveRequest::where('status',LeaveRequest::STATUS_APPROVED)
                ->where("from_date", "<=", $startDate)
                ->where("to_date", ">=", $startDate)
                ->get();
            Log::info('all_leave_records : '.json_encode($all_leave_records));



            $employee_leave_record = [];
            foreach($all_leave_records as $record){
                if(!isset($employee_leave_record[$record->user_id])){
                    $employee_leave_record[$record->user_id] = [];
                }
                $total_approved_days = $record->number_of_days - ($record->number_of_unpaid_days ?? 0);
                $total_unpaid_days = $record->number_of_days - $total_approved_days;
                $begin = strtotime($record->from_date);
                $end = strtotime($record->to_date);
                for ( $i = $begin; $i <= $end; $i = $i + 86400 ) {
                    $thisDate = date( 'Y-m-d', $i );
                    if($total_approved_days>=1){
                        if($thisDate == $startDate) {
                            $employee_leave_record[$record->user_id]['full'][] = $thisDate;
                        }
                        $total_approved_days--;
                    }else{
                        if($total_approved_days==0.5) {
                            if($thisDate == $startDate) {
                                $employee_leave_record[$record->user_id]['half'][] = $thisDate;
                                $employee_leave_record[$record->user_id]['half_day_slot'][$thisDate] = $record->half_day_slot;
                                $employee_leave_record[$record->user_id]['half_leave_start'][$thisDate] = $record->leave_start_time;
                                $employee_leave_record[$record->user_id]['half_leave_end'][$thisDate] = $record->leave_end_time;
                            }
                            $total_approved_days=0;
                            if($total_unpaid_days > 0){
                                if($thisDate == $startDate) {
                                    $employee_leave_record[$record->user_id]['half_unpaid'][$thisDate] = true;
                                }
                                $total_unpaid_days = $total_unpaid_days - 0.5;
                            }
                        }
                        else{
                            if($total_unpaid_days > 0){
                                if ($total_unpaid_days >= 1) {
                                    if($thisDate == $startDate) {
                                        $employee_leave_record[$record->user_id]['full_unpaid'][$thisDate] = true;
                                    }
                                    $total_unpaid_days--;
                                }else{
                                    if($thisDate == $startDate) {
                                        $employee_leave_record[$record->user_id]['half_unpaid'][$thisDate] = true;
                                    }
                                    $total_unpaid_days = 0;
                                }
                            }
                        }
                    }
                }
            }
            Log::info('employee_leave_record : '.json_encode($employee_leave_record));
            $is_public_holiday=false;
            $all_public_holiday_records = PublicHoliday::where("from_date", "<=", $startDate)
                ->where("to_date", ">=", $startDate)
                ->get();
            if($all_public_holiday_records->count()>0){
                $is_public_holiday = true;
            }
            $sql_weekly_holiday = "SELECT * FROM `weekly_holidays` WHERE `effective_date` <= '$startDate' AND (`end_date` >= '$startDate' OR `end_date` IS NULL)";
            $results = DB::select($sql_weekly_holiday);
            $weekly_holidays=[];
            foreach($results as $result){
                $weekly_holidays[$result->department_id]['days'] = $result->days;
            }
            $relax_approved_value = AssignRelaxDay::APPROVAL_CONFIRMED;
            $sql_relax_day = "SELECT assign_relax_day.user_id FROM assign_relax_day INNER JOIN relax_day ON relax_day.id=assign_relax_day.relax_day_id WHERE relax_day.deleted_at IS NULL AND relax_day.date='$startDate' AND assign_relax_day.deleted_at IS NULL AND assign_relax_day.approval_status = $relax_approved_value";
            $relax_users = DB::select($sql_relax_day);
            $relax_day_users=[];
            foreach($relax_users as $u_relax){
                $relax_day_users[]=$u_relax->user_id;
            }
            $approved_value = Roster::STATUS_APPROVED;
            $sql_roster = "SELECT * FROM `rosters` WHERE `active_date` = '$startDate' AND `status` = $approved_value AND deleted_at IS NULL ";
            $roster_records = DB::select($sql_roster);
            $roster_type_user = [];
            $roster_type_department = [];
            foreach($roster_records as $each_roster){
                if($each_roster->user_id){
                    $roster_type_user[$each_roster->user_id]['is_weekly_holiday'] = $each_roster->is_weekly_holiday;
                    $roster_type_user[$each_roster->user_id]['work_slot_id'] = $each_roster->work_slot_id;
                }else{
                    $roster_type_department[$each_roster->department_id]['is_weekly_holiday'] = $each_roster->is_weekly_holiday;
                    $roster_type_department[$each_roster->department_id]['work_slot_id'] = $each_roster->work_slot_id;
                }
            }
            $work_slot_arr = [];
            $work_slots = WorkSlot::all();
            foreach ($work_slots as $slot){
                $work_slot_arr[$slot->id]=$slot;
            }
            foreach ($employees as $employee) {
                Log::info('Each Employee : '.json_encode($employee));
                $log=[];
                $working_min=0;
                $overtime_min = null;
                $present_count = 0;
                $is_late = false;
                $late_in_min=0;
                $is_late_final = false;
                $late_min_final = 0;
                $is_weekly_holiday=false;
                $holiday_count=$is_public_holiday;
                $leave_count = 0;
                $employee_weekend=[];
                if(isset($roster_type_user[$employee->id])){
                    $work_slot_id = $roster_type_user[$employee->id]['work_slot_id'];
                    if($roster_type_user[$employee->id]['is_weekly_holiday']){
                        $employee_weekend=[$text_represent_of_this_day];
                    }
                }else{
                    if(isset($roster_type_department[$employee->department_id])){
                        $work_slot_id = $roster_type_department[$employee->department_id]['work_slot_id'];
                        if($roster_type_department[$employee->department_id]['is_weekly_holiday']){
                            $employee_weekend=[$text_represent_of_this_day];
                        }
                    }else{
                        $work_slot_id = $employee->workslot_id;
                        $weekend_days = $weekly_holidays[$employee->department_id]['days'] ?? '';
                        $weekend_days = substr($weekend_days, 2);
                        $weekend_days = substr($weekend_days, 0, -2);
                        $employee_weekend = explode('","',$weekend_days);
                    }
                }
                $attendanceCountStartHour_in_sec = $attendanceCountStartHour*60*60;
                $startDateTime_in_sec = strtotime($startDate." ".$work_slot_arr[$work_slot_id]->start_time);
                $startDateTime_in_sec = $startDateTime_in_sec-$attendanceCountStartHour_in_sec;
                $endDateTime_in_sec = $startDateTime_in_sec + 86399;
                $startDateTime=date('Y-m-d H:i:s',$startDateTime_in_sec);
                $endDateTime=date('Y-m-d H:i:s',$endDateTime_in_sec);
                Log::info('endDateTime : '.$endDateTime);
                Log::info('startDateTime : '.$startDateTime);
                $timeIn = Attendance::whereDate("punch_time", $startDate)
                    ->where("punch_time", '>=', $startDateTime)
                    ->orderBy("punch_time")
                    ->where("emp_code", $employee->fingerprint_no)
                    ->select("id", "emp_code", "punch_time")
                    ->first();
                if($timeIn){
                    $timeOut = Attendance::where("emp_code", $employee->fingerprint_no)
                        ->where("punch_time", '>=', $startDateTime)
                        ->where("punch_time", '<=', $endDateTime)
                        ->orderByDesc("punch_time")
                        ->select("id", "emp_code", "punch_time")
                        ->first();
                    if (!isset($timeOut->punch_time)) {
                        $timeOut = Attendance::whereDate("punch_time", $startDate)
                            ->orderByDesc("punch_time")
                            ->where("emp_code", $employee->fingerprint_no)
                            ->select("id", "emp_code", "punch_time")
                            ->first();
                    }
                }else{
                    $timeOut=null;
                }
                if(in_array($text_represent_of_this_day,$employee_weekend)){
                    $is_weekly_holiday = true;
                    $holiday_count = true;
                }
                $is_relax_day=false;
                if(in_array($employee->id,$relax_day_users)){
                    $is_relax_day=true;
                }
                if(isset($employee_leave_record[$employee->id]['full'])){
                    $leave_count=1;
                }else{
                    if(isset($employee_leave_record[$employee->id]['half'])){
                        $leave_count=0.5;
                    }
                }
                if(!is_null($timeIn)) {
                    $present_count = 1;
                    $absent_count = 0;
                    if($timeIn->punch_time === $timeOut->punch_time) $timeOut = null;
                    $date_time=date_create($timeIn->punch_time);
                    $entry_time_in_sec = strtotime(date_format($date_time,"Y-m-d H:i:s"));
                    if($work_slot_arr[$work_slot_id]->is_flexible){
                        $end_time_in_sec = $entry_time_in_sec + ($work_slot_arr[$work_slot_id]->total_work_hour*60*60);
                        $late_time_in_sec = $entry_time_in_sec + 1;
                        $start_time_in_sec = $entry_time_in_sec;
                    }else{
                        $start_time_in_sec = strtotime($startDate." ".$work_slot_arr[$work_slot_id]->start_time);
                        $end_time_in_sec = strtotime($startDate." ".$work_slot_arr[$work_slot_id]->end_time);
                        if($start_time_in_sec>=$end_time_in_sec){
                            $end_time_in_sec = strtotime($endDate." ".$work_slot_arr[$work_slot_id]->end_time);
                        }
                        $late_time_in_sec = strtotime($startDate." ".$work_slot_arr[$work_slot_id]->late_count_time);
                        if($start_time_in_sec>$late_time_in_sec){
                            $late_time_in_sec = strtotime($endDate." ".$work_slot_arr[$work_slot_id]->late_count_time);
                        }
                    }
                    $log[$employee->id]['user_id'] = $employee->id;
                    $log[$employee->id]['in_time'] = date_format($date_time,"Y-m-d H:i:s");
                    $log[$employee->id]['in_time_sec'] = $entry_time_in_sec;
                    $log[$employee->id]['late_time_sec'] = $late_time_in_sec;
                    if($late_time_in_sec<$entry_time_in_sec){
                        $is_late=true;
                        $late_in_min = round(abs($entry_time_in_sec - $late_time_in_sec) / 60,2);
                    }
                    if(isset($employee_leave_record[$employee->id]['full']) && in_array($startDate,$employee_leave_record[$employee->id]['full'])){
                        $present_count = 0;
                        $is_late_final = false;
                        $late_min_final = 0;
                    }
                    elseif(isset($employee_leave_record[$employee->id]['half']) && in_array($startDate,$employee_leave_record[$employee->id]['half'])){
                        if(isset($employee_leave_record[$employee->id]['half_unpaid'][$startDate])){
                            $absent_count = 0.5;
                            $present_count = 0;
                        }else{
                            $present_count = 0.5;
                        }
                        if($is_late){
                            if(!is_null($employee_leave_record[$employee->id]['half_leave_start'][$startDate])){
                                if($employee_leave_record[$employee->id]['half_day_slot'][$startDate]==1){
                                    $day_start_time_in_sec = strtotime($startDate." ".$work_slot_arr[$work_slot_id]->start_time);
                                    $half_leave_end = date("H:i", strtotime($employee_leave_record[$employee->id]['half_leave_end'][$startDate]));
                                    $half_leave_end_in_sec = strtotime($startDate.' '.$half_leave_end.':00');
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
                        if(isset($employee_leave_record[$employee->id]['half_unpaid'][$startDate])){
                            $absent_count = 0.5;
                            $present_count = 0.5;
                            $is_late_final = $is_late;
                            $late_min_final = $late_in_min;
                        }
                        elseif(isset($employee_leave_record[$employee->id]['full_unpaid'][$startDate])){
                            $absent_count = 1;
                            $present_count = 0;
                            $is_late_final = false;
                            $late_min_final = 0;
                        }else{
                            $is_late_final = $is_late;
                            $late_min_final = $late_in_min;
                        }
                    }
                    if(isset($timeOut) && isset($timeOut->punch_time)){
                        $date_time_out=date_create($timeOut->punch_time);
                        $exit_time_in_sec = strtotime(date_format($date_time_out,"Y-m-d H:i:s"));
                        $working_min = round(abs($exit_time_in_sec - $entry_time_in_sec) / 60,2);
                    }else{
                        $exit_time_in_sec=0;
                    }
                    if($work_slot_arr[$work_slot_id]->over_time == 'Yes' && $exit_time_in_sec>0){
                        $actual_work_start_time = $start_time_in_sec;
                        if($work_slot_arr[$work_slot_id]->is_flexible){
                            $actual_work_end_time = $end_time_in_sec;
                        }else{
                            $actual_work_end_time = strtotime($startDate." ".$work_slot_arr[$work_slot_id]->overtime_count);
                            if($end_time_in_sec>$actual_work_end_time){
                                $actual_work_end_time = strtotime($endDate." ".$work_slot_arr[$work_slot_id]->overtime_count);
                            }
                        }
                        $actual_work_time_in_sec = $actual_work_end_time - $actual_work_start_time;
                        if($exit_time_in_sec>$actual_work_end_time){
                            $working_sec = $working_min*60;
                            if($working_sec>$actual_work_time_in_sec){
                                $extra_sec = $working_sec-$actual_work_time_in_sec;
                                if(!$work_slot_arr[$work_slot_id]->is_flexible){
                                    if($actual_work_start_time>$entry_time_in_sec){
                                        $extra_sec = $extra_sec - ($actual_work_start_time-$entry_time_in_sec);
                                    }
                                }
                                if($extra_sec>0){
                                    $overtime_min = round(abs($extra_sec) / 60,2);
//                                    $ot_rule = (int) ($overtime_min / 30);
//                                    $overtime_min = $ot_rule*30;
                                }
                            }
                        }
                    }
                    $attendance_summary = [
                        "user_id" => $employee->id,
                        "emp_code"=> $employee->fingerprint_no,
                        "time_in" => $timeIn ? $timeIn->punch_time : null,
                        "date" => $startDate,
                        "time_out"=> $timeOut ? $timeOut->punch_time : null,
                        "roaster_start_time" => $work_slot_arr[$work_slot_id]->start_time,
                        "roaster_end_time" => $work_slot_arr[$work_slot_id]->end_time ?? '',
                        "late_count_time" => $work_slot_arr[$work_slot_id]->late_count_time ?? '',
                        "is_late_day" => $is_late,
                        "late_in_min" => $late_in_min,
                        "working_min" => $working_min,
                        "is_ot_available" => ($work_slot_arr[$work_slot_id]->over_time == 'Yes') ? 1 : 0,
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
                }else{
                    if($holiday_count){
                        $absent_count = 0;
                    }else{
                        if($is_relax_day){
                            $absent_count = 0;
                        }else{
                            $absent_count = 1 - $leave_count;
                        }
                    }
                    $attendance_summary = [
                        "user_id" => $employee->id,
                        "emp_code"=> $employee->fingerprint_no,
                        "time_in" => $timeIn ? $timeIn->punch_time : null,
                        "date" => $startDate,
                        "time_out"=> $timeOut ? $timeOut->punch_time : null,
                        "roaster_start_time" => $work_slot_arr[$work_slot_id]->start_time,
                        "roaster_end_time" => $work_slot_arr[$work_slot_id]->end_time ?? '',
                        "late_count_time" => $work_slot_arr[$work_slot_id]->late_count_time ?? '',
                        "is_late_day" => $is_late,
                        "late_in_min" => $late_in_min,
                        "working_min" => $working_min,
                        "is_ot_available" => ($work_slot_arr[$work_slot_id]->over_time == 'Yes') ? 1 : 0,
                        "overtime_min" => $overtime_min,
                        "present_count" => $present_count,
                        "is_late_final" => $is_late_final,
                        "late_min_final" => $late_min_final,
                        'holiday_count' => $holiday_count,
                        'absent_count' => $absent_count,
                        'leave_count' => $leave_count,
                        'is_public_holiday' => $is_public_holiday,
                        'is_weekly_holiday' => $is_weekly_holiday,
                        'is_relax_day' => $holiday_count ? false : $is_relax_day
                    ];
                }
                Log::info('Each Employee : '.json_encode($attendance_summary));
                $log["date"]=$startDate;
                $log["time_in"]=$timeIn ? $timeIn->punch_time : null;
                $log["time_out"]=$timeOut ? $timeOut->punch_time : null;
                $log["is_late_day"]=$is_late;
                $log["late_in_min"]=$late_in_min;
                $log["working_min"]=$working_min;
                $log["overtime_min"]=$overtime_min;
                $log["is_late_final"]=$is_late_final;
                $log["late_min_final"]=$late_min_final;
                DailyAttendance::firstOrCreate([
                    'user_id' => $employee->id,
                    'date' => $startDate,
                ], $attendance_summary);
                Log::channel('attendance')->info("$employee->id ::: ".json_encode($log));
            }
            DailyCronLog::insert(['date'=>$startDate,'created_at'=>now()]);
            DB::commit();
            $response = ["success" => true];
        } catch (Exception $exception) {
            Log::info($exception->getMessage());
            DB::rollBack();
            $response = [
                "success" => false,
                "message" => $exception->getMessage()
            ];
        }

        return $response;
    }

    /**
     * @return array
     */
    public function generateWithSubDays()
    {
        DB::beginTransaction();

        try {
            $employees = User::with("currentPromotion")->select("id", "fingerprint_no")->get();

            $attendanceCountStartTime = Setting::where("name", "attendance_count_start_time")->select("id", "value")->first()->value;
            $attendanceCountStartTime = date("H:i:s", strtotime($attendanceCountStartTime));

            $subDays = 26;

            while($subDays) {
                $startDate = Carbon::now()->subDays($subDays);
                $endDate = Carbon::now()->subDays($subDays);

                foreach ($employees as $employee) {
                    $timeIn = Attendance::whereDate("punch_time", $startDate)
                        ->whereTime("punch_time", ">=", $attendanceCountStartTime)
                        ->orderBy("punch_time")
                        ->where("emp_code", $employee->fingerprint_no)
                        ->select("id", "emp_code", "punch_time")
                        ->first();

                    $timeOut = Attendance::whereDate("punch_time", $endDate)
                        ->whereTime("punch_time", "<", $attendanceCountStartTime)
                        ->orderByDesc("punch_time")
                        ->where("emp_code", $employee->fingerprint_no)
                        ->select("id", "emp_code", "punch_time")
                        ->first();

                    if (!isset($timeOut)) {
                        $timeOut = Attendance::whereDate("punch_time", $startDate)
                            ->orderByDesc("punch_time")
                            ->where("emp_code", $employee->fingerprint_no)
                            ->select("id", "emp_code", "punch_time")
                            ->first();
                    }

                    if(!is_null($timeIn)) {
                        if($timeIn->punch_time === $timeOut->punch_time) $timeOut = null;

                        DailyAttendance::firstOrCreate([
                            "user_id" => $employee->id,
                            "emp_code"=> $employee->fingerprint_no,
                            "time_in" => $timeIn ? $timeIn->punch_time : null,
                            "time_out"=> $timeOut ? $timeOut->punch_time : null
                        ]);
                    }
                }

                $subDays--;
            }

            DB::commit();

            $response = ["success" => true];
        } catch (Exception $exception) {
            DB::rollBack();

            $response = [
                "success" => false,
                "message" => $exception->getMessage()
            ];
        }

        return $response;
    }
}
