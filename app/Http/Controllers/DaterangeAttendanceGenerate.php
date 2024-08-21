<?php

namespace App\Http\Controllers;

use App\Models\LeaveRequest;
use App\Models\PublicHoliday;
use App\Models\Roaster;
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

class DaterangeAttendanceGenerate extends Controller
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
    public function generate()
    {
        DB::beginTransaction();
        try {
            $employees = User::with("currentPromotion")->select("id", "fingerprint_no")->active()->get();
            $employee_join_date = [];
            $sql_join_date = "SELECT users.id, users.fingerprint_no, employee_status.action_date FROM `users` LEFT JOIN employee_status ON employee_status.user_id = users.id AND employee_status.id =( SELECT MAX( es.id) FROM `employee_status` AS es WHERE es.user_id = users.id AND es.action_reason_id = 2 ) INNER JOIN promotions AS prm ON prm.user_id = users.id AND prm.id = ( SELECT MAX( pm.id ) FROM `promotions` AS pm WHERE pm.user_id = users.id ) WHERE users.`status` = 1";
            $results = DB::select($sql_join_date);
            foreach ($results as $item){
                $employee_join_date[$item->id] = $item->action_date;
            }
            $attendanceCountStartTime = Setting::where("name", "attendance_count_start_time")->select("id", "value")->first()->value;
            $attendanceCountStartTime = date("H:i:s", strtotime($attendanceCountStartTime));
            $startDate = date('Y-m-d', strtotime('-1 day', strtotime(date('Y-m-d'))));
            $endDateTime = date('Y-m-d').' 05:59:59';
            $startDateTime = $startDate.' '.$attendanceCountStartTime;
            for($i = 1; $i <= 19; $i++) {
                $k=$i;
                $j=$i+1;
                $k = (strlen((string)$k)==1) ? str_pad($k,2,0,STR_PAD_LEFT ) : $k;
                $j = (strlen((string)$j)==1) ? str_pad($j,2,0,STR_PAD_LEFT ) : $j;
                $startDate = "2022-09-$k";
                $startDateTime = "2022-09-$k 06:00:00";
                $endDateTime = "2022-09-$j 05:59:59";
                $text_represent_of_this_day = strtolower(date('D', strtotime($startDate)));
                $all_leave_records = LeaveRequest::where('status', LeaveRequest::STATUS_APPROVED)
                    ->where("from_date", "<=", $startDate)
                    ->where("to_date", ">=", $startDate)
                    ->get();
                $employee_leave_record = [];
                foreach ($all_leave_records as $record) {
                    if (!isset($employee_leave_record[$record->user_id])) {
                        $employee_leave_record[$record->user_id] = [];
                    }
                    $begin = strtotime($record->from_date);
                    $end = strtotime($record->to_date);
                    $count = 0;
                    for ( $sec = $begin; $sec <= $end; $sec = $sec + 86400 ) {
                        $thisDate = date( 'Y-m-d', $sec );
                        $count++;
                        if ($thisDate == $startDate) {
                            if (!in_array($thisDate, $employee_leave_record[$record->user_id])) {
                                if (is_null($record->number_of_paid_days)) {
                                    if ($record->half_day) {
                                        $employee_leave_record[$record->user_id]['half'][] = $thisDate;
                                        $employee_leave_record[$record->user_id]['half_leave_start'][$thisDate] = $record->leave_start_time;
                                        $employee_leave_record[$record->user_id]['half_leave_end'][$thisDate] = $record->leave_end_time;
                                    } else {
                                        $employee_leave_record[$record->user_id]['full'][] = $thisDate;
                                    }
                                }
                                if (isset($record->number_of_paid_days) && $record->number_of_paid_days == 0) {
                                    $differ_days = $record->number_of_days - $record->number_of_unpaid_days;
                                    if ($differ_days > 0) {
                                        if ($count <= $differ_days) {
                                            $employee_leave_record[$record->user_id]['full'][] = $thisDate;
                                        }
                                    }
                                }
                                if (isset($record->number_of_paid_days) && $record->number_of_paid_days > 0) {
                                    if ($record->half_day) {
                                        $employee_leave_record[$record->user_id]['half'][] = $thisDate;
                                        $employee_leave_record[$record->user_id]['half_leave_start'][$thisDate] = $record->leave_start_time;
                                        $employee_leave_record[$record->user_id]['half_leave_end'][$thisDate] = $record->leave_end_time;
                                    } else {
                                        if ($count <= $record->number_of_paid_days) {
                                            $employee_leave_record[$record->user_id]['full'][] = $thisDate;
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
                $is_public_holiday = false;
                $all_public_holiday_records = PublicHoliday::where("from_date", "<=", $startDate)
                    ->where("to_date", ">=", $startDate)
                    ->get();
                if ($all_public_holiday_records->count() > 0) {
                    $is_public_holiday = true;
                }
                $sql_weekly_holiday = "SELECT * FROM `weekly_holidays` WHERE `effective_date` <= '$startDate' AND(`end_date` >= '$startDate' OR `end_date` IS NULL)";
                $results = DB::select($sql_weekly_holiday);
                $weekly_holidays = [];
                foreach ($results as $result) {
                    $weekly_holidays[$result->department_id]['days'] = $result->days;
                }
                $sql_roaster = "SELECT * FROM `roasters` WHERE `active_from` <= '$startDate' AND `end_date` >= '$startDate'";
                $roaster_records = DB::select($sql_roaster);
                $roaster_weekly_holidays = [];
                $roaster_work_slot = [];
                foreach ($roaster_records as $each_roaster) {
                    $roaster_weekly_holidays[$each_roaster->user_id]['days'] = $each_roaster->weekly_holidays;
                    $roaster_work_slot[$each_roaster->user_id]['work_slot_id'] = $each_roaster->work_slot_id;
                }
                $work_slot_arr = [];
                $work_slots = WorkSlot::all();
                foreach ($work_slots as $slot){
                    if($slot->end_time == '00:00:00'){
                        $slot->end_time = '23:59:59';
                    }
                    if($slot->overtime_count == '00:00:00'){
                        $slot->overtime_count = '23:59:59';
                    }
                    $work_slot_arr[$slot->id]=$slot;
                }
                foreach ($employees as $employee) {
                    if(isset($employee_join_date[$employee->id]) && $employee_join_date[$employee->id]<=$startDate){
                        $sql_promotion = "SELECT users.id, promotions.office_division_id, promotions.department_id, promotions.designation_id, promotions.promoted_date, promotions.type, promotions.salary, promotions.workslot_id, promotions.pay_grade_id FROM users INNER JOIN promotions ON promotions.user_id = users.id AND promotions.id =( SELECT MAX( pm.id) FROM `promotions` AS pm WHERE pm.user_id = users.id AND pm.promoted_date <= '$startDate' ) WHERE users.`status` = 1 AND users.`id` = $employee->id";
                        $promotion_record = DB::select($sql_promotion);
                        Log::info('emp => '.$employee->id.'----'.json_encode($promotion_record));
                        $log = [];
                        $working_min = 0;
                        $overtime_min = null;
                        $present_count = 0;
                        $is_late = false;
                        $late_in_min = 0;
                        $is_late_final = false;
                        $late_min_final = 0;
                        $is_weekly_holiday = false;
                        $holiday_count = $is_public_holiday;
                        $leave_count = 0;
                        $timeIn = Attendance::whereDate("punch_time", $startDate)
                            ->where("punch_time", '>=', $startDateTime)
                            ->orderBy("punch_time")
                            ->where("emp_code", $employee->fingerprint_no)
                            ->select("id", "emp_code", "punch_time")
                            ->first();
                        $timeOut = Attendance::where("emp_code", $employee->fingerprint_no)
                            ->where("punch_time", '>=', $startDateTime)
                            ->where("punch_time", '<=', $endDateTime)
                            ->orderByDesc("punch_time")
                            ->select("id", "emp_code", "punch_time")
                            ->first();
                        if (!isset($timeOut)) {
                            $timeOut = Attendance::whereDate("punch_time", $startDate)
                                ->orderByDesc("punch_time")
                                ->where("emp_code", $employee->fingerprint_no)
                                ->select("id", "emp_code", "punch_time")
                                ->first();
                        }
                        $work_slot_id = $roaster_work_slot[$employee->id]['work_slot_id'] ?? $promotion_record[0]->workslot_id;
                        if (isset($roaster_weekly_holidays[$employee->id])) {
                            $employee_weekend = $roaster_weekly_holidays[$employee->id]['days'];
                        } else {
                            $employee_weekend = $weekly_holidays[$promotion_record[0]->department_id]['days'];
                        }
                        $employee_weekend = substr($employee_weekend, 2);
                        $employee_weekend = substr($employee_weekend, 0, -2);
                        $employee_weekend = explode('","', $employee_weekend);
                        if (in_array($text_represent_of_this_day, $employee_weekend)) {
                            $is_weekly_holiday = true;
                            $holiday_count = true;
                        }
                        if (isset($employee_leave_record[$employee->id]['full'])) {
                            $leave_count = 1;
                        } else {
                            if (isset($employee_leave_record[$employee->id]['half'])) {
                                $leave_count = 0.5;
                            }
                        }
                        if (!is_null($timeIn)) {
                            $present_count = 1;
                            if ($timeIn->punch_time === $timeOut->punch_time) $timeOut = null;
                            $date_time = date_create($timeIn->punch_time);
                            $entry_time_in_sec = strtotime(date_format($date_time, "Y-m-d H:i:s"));
                            $late_time_in_sec = strtotime($startDate . " " . $work_slot_arr[$work_slot_id]->late_count_time);
                            $log[$employee->id]['user_id'] = $employee->id;
                            $log[$employee->id]['in_time'] = date_format($date_time, "Y-m-d H:i:s");
                            $log[$employee->id]['in_time_sec'] = $entry_time_in_sec;
                            $log[$employee->id]['late_time'] = $startDate . " " . $work_slot_arr[$work_slot_id]->late_count_time;
                            $log[$employee->id]['late_time_sec'] = $late_time_in_sec;
                            if ($late_time_in_sec <= $entry_time_in_sec) {
                                $is_late = true;
                                $late_in_min = round(abs($entry_time_in_sec - $late_time_in_sec) / 60, 2);
                            }
                            if (isset($employee_leave_record[$employee->id]['full']) && in_array($startDate, $employee_leave_record[$employee->id]['full'])) {
                                $present_count = 0;
                                $is_late_final = false;
                                $late_min_final = 0;
                            } elseif (isset($employee_leave_record[$employee->id]['half']) && in_array($startDate, $employee_leave_record[$employee->id]['half'])) {
                                $present_count = 0.5;
                                if ($is_late) {
                                    if (!is_null($employee_leave_record[$employee->id]['half_leave_start'][$startDate])) {
                                        $late_count_time = $work_slot_arr[$work_slot_id]->late_count_time;
                                        $late_count_time_in_sec = strtotime($startDate . ' ' . $late_count_time);
                                        $half_leave_start = $employee_leave_record[$employee->id]['half_leave_start'][$startDate];
                                        $half_leave_start_in_sec = strtotime($startDate . ' ' . $half_leave_start . ':00');
                                        if ($half_leave_start_in_sec > $late_count_time_in_sec) {
                                            $is_late_final = true;
                                            $late_min_final = $late_in_min;
                                        } else {
                                            $is_late_final = false;
                                            $late_min_final = 0;
                                        }
                                    } else {
                                        $is_late_final = false;
                                        $late_min_final = 0;
                                    }
                                } else {
                                    $is_late_final = false;
                                    $late_min_final = 0;
                                }
                            } else {
                                $is_late_final = $is_late;
                                $late_min_final = $late_in_min;
                            }
                            if (isset($timeOut) && isset($timeOut->punch_time)) {
                                $date_time_out = date_create($timeOut->punch_time);
                                $exit_time_in_sec = strtotime(date_format($date_time_out, "Y-m-d H:i:s"));
                                $working_min = round(abs($exit_time_in_sec - $entry_time_in_sec) / 60, 2);
                            }
                            if ($work_slot_arr[$work_slot_id]->over_time == 'Yes') {
                                $actual_work_start_time = strtotime($startDate . " " . $work_slot_arr[$work_slot_id]->late_count_time);
                                $actual_work_end_time = strtotime($startDate . " " . $work_slot_arr[$work_slot_id]->overtime_count);
                                $actual_work_time_in_sec = $actual_work_end_time - $actual_work_start_time;
                                $working_sec = $working_min * 60;
                                if ($working_sec > $actual_work_time_in_sec) {
                                    $extra_sec = $working_sec - $actual_work_time_in_sec;
                                    if ($entry_time_in_sec < $actual_work_start_time) {
                                        $extra_sec = $extra_sec - ($actual_work_start_time - $entry_time_in_sec);
                                        if ($extra_sec > 0) {
                                            $overtime_min = round(abs($extra_sec) / 60, 2);
                                        }
                                    }
                                }
                            }
                            $attendance_summary = [
                                "user_id" => $employee->id,
                                "emp_code" => $employee->fingerprint_no,
                                "time_in" => $timeIn ? $timeIn->punch_time : null,
                                "date" => $startDate,
                                "time_out" => $timeOut ? $timeOut->punch_time : null,
                                "roaster_start_time" => $work_slot_arr[$work_slot_id]->start_time,
                                "roaster_end_time" => $work_slot_arr[$work_slot_id]->end_time,
                                "late_count_time" => $work_slot_arr[$work_slot_id]->late_count_time,
                                "is_late_day" => $is_late,
                                "late_in_min" => $late_in_min,
                                "working_min" => $working_min,
                                "overtime_min" => $overtime_min,
                                "present_count" => $present_count,
                                "is_late_final" => $is_late_final,
                                "late_min_final" => $late_min_final,
                                'holiday_count' => $holiday_count,
                                'absent_count' => 0,
                                'leave_count' => $leave_count,
                                'is_public_holiday' => $is_public_holiday,
                                'is_weekly_holiday' => $is_weekly_holiday
                            ];
                        } else {
                            $attendance_summary = [
                                "user_id" => $employee->id,
                                "emp_code" => $employee->fingerprint_no,
                                "time_in" => $timeIn ? $timeIn->punch_time : null,
                                "date" => $startDate,
                                "time_out" => $timeOut ? $timeOut->punch_time : null,
                                "roaster_start_time" => $work_slot_arr[$work_slot_id]->start_time,
                                "roaster_end_time" => $work_slot_arr[$work_slot_id]->end_time,
                                "late_count_time" => $work_slot_arr[$work_slot_id]->late_count_time,
                                "is_late_day" => $is_late,
                                "late_in_min" => $late_in_min,
                                "working_min" => $working_min,
                                "overtime_min" => $overtime_min,
                                "present_count" => $present_count,
                                "is_late_final" => $is_late_final,
                                "late_min_final" => $late_min_final,
                                'holiday_count' => $holiday_count,
                                'absent_count' => $holiday_count ? 0 : 1 - $leave_count,
                                'leave_count' => $leave_count,
                                'is_public_holiday' => $is_public_holiday,
                                'is_weekly_holiday' => $is_weekly_holiday
                            ];
                        }
                        $log["date"] = $startDate;
                        $log["time_in"] = $timeIn ? $timeIn->punch_time : null;
                        $log["time_out"] = $timeOut ? $timeOut->punch_time : null;
                        $log["is_late_day"] = $is_late;
                        $log["late_in_min"] = $late_in_min;
                        $log["working_min"] = $working_min;
                        $log["overtime_min"] = $overtime_min;
                        $log["is_late_final"] = $is_late_final;
                        $log["late_min_final"] = $late_min_final;
                        DailyAttendance::firstOrCreate($attendance_summary);
                        Log::channel('attendance')->info("$employee->id ::: " . json_encode($log));
                    }
                }
            }
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
