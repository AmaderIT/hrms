<?php

namespace App\Http\Controllers;

use App\Models\LeaveRequest;
use App\Models\LeaveUnpaid;
use App\Models\PublicHoliday;
use App\Models\Setting;
use App\Models\WorkSlot;
use App\Models\User as Employee;
use App\Models\WeeklyHoliday;
use App\Models\ZKTeco\Attendance as ZKTeco;
use App\Models\ZKTeco\Employee as ZKTecoEmployee;
use DateTime;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Exception;
use DatePeriod;
use DateInterval;
use Illuminate\Support\Facades\Log;

class LeaveUnpaidController extends Controller
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
     * @return RedirectResponse
     */


    /**
     * @return array
     */

    public function generateLeaveUnpaidReportForYesterday($yesterdayDate)
    {

        $response               = [];
        $inputData              = [];
        $workSlotData           = [];
        $attendantEmp           = [];
        $attendantEmpCodes      = [];

        try {
            $year                   = date('Y', strtotime($yesterdayDate));
            $day                    = date('D', strtotime($yesterdayDate));
            $globalSetting          = Setting::where('name', 'half_day_leave_count_in_hr')->first()->value; //half_day_leave_count_in_hr
            $publicHoliday          = PublicHoliday::whereDate("from_date", "<=", $yesterdayDate)->whereDate("to_date", ">=", $yesterdayDate)->first();
            $workSlots              = WorkSlot::get();

            //WORK SLOT DATA
            foreach ($workSlots as $key => $workSlot) {
                $workSlotData[$workSlot->id] = $workSlot;
            }

            //ATTENDANCE DATA
            $attendantUsers         = ZKTeco::whereDate("punch_time", $yesterdayDate)->select('emp_code', 'punch_time')->groupBy('emp_code')->get();

            foreach ($attendantUsers as $key => $attendantUser) {
                $attendantEmp[$attendantUser->emp_code] = $attendantUser->punch_time;
                $attendantEmpCodes[] = $attendantUser->emp_code;
            }
            $attendantUsersData     = implode(',', $attendantEmpCodes);

            //END ATTENDANCE DATA

            //ATTENDANT EMPLOYEES
            if ($attendantUsersData) {
                $attendantEmployees     = "SELECT A.*,( SELECT GROUP_CONCAT( rosters.type) FROM rosters WHERE(( rosters.`user_id` = A.`id` AND rosters.`department_id` = A.`department_id` ) OR ( rosters.`department_id` = A.`department_id` AND rosters.`user_id` IS NULL ) ) AND rosters.`active_date` = '$yesterdayDate' AND rosters.`status` = 1 AND rosters.deleted_at IS NULL ) AS roster_types, ( SELECT GROUP_CONCAT( rosters.work_slot_id ) FROM rosters WHERE (( rosters.`user_id` = A.`id` AND rosters.`department_id` = A.`department_id` ) OR ( rosters.`department_id` = A.`department_id` AND rosters.`user_id` IS NULL ) ) AND rosters.`active_date` = '$yesterdayDate' AND rosters.`status` = 1 AND rosters.deleted_at IS NULL ) AS roster_work_slots, ( SELECT GROUP_CONCAT( rosters.is_weekly_holiday ) FROM rosters WHERE (( rosters.`user_id` = A.`id` AND rosters.`department_id` = A.`department_id` ) OR ( rosters.`department_id` = A.`department_id` AND rosters.`user_id` IS NULL ) ) AND rosters.`active_date` = '$yesterdayDate' AND rosters.`status` = 1 AND rosters.deleted_at IS NULL ) AS roster_weekly_holiday FROM ( SELECT users.id, users.fingerprint_no, promotions.user_id, promotions.office_division_id, promotions.department_id, promotions.designation_id, promotions.promoted_date, promotions.type, promotions.workslot_id, work_slots.start_time AS work_slot_start_time, work_slots.end_time AS work_slot_end_time, work_slots.late_count_time AS work_slot_late_count_time, weekly_holidays.days AS weekly_holidays, leave_allocations.half_day_count AS leave_al_half_day_count, leave_requests.half_day AS leave_requested_half_day, leave_requests.`status` AS leave_requested_status, assign_relax_day.relax_day_id FROM users INNER JOIN promotions ON promotions.user_id = users.id AND promotions.id =( SELECT MAX( pm.id ) FROM promotions AS pm WHERE pm.user_id = users.id AND pm.promoted_date <= '$yesterdayDate' ) INNER JOIN work_slots ON work_slots.id = promotions.workslot_id LEFT JOIN weekly_holidays ON weekly_holidays.department_id = promotions.department_id AND weekly_holidays.effective_date <= '$yesterdayDate' AND ( weekly_holidays.end_date >= '$yesterdayDate' OR weekly_holidays.end_date IS NULL ) LEFT JOIN leave_allocations ON leave_allocations.department_id = promotions.department_id AND leave_allocations.office_division_id = promotions.office_division_id AND leave_allocations.`year` = '$year' LEFT JOIN leave_requests ON leave_requests.user_id = users.id AND leave_requests.office_division_id = promotions.office_division_id AND leave_requests.department_id = promotions.department_id AND leave_requests.`status` IN ( 0, 1, 2, 3 ) AND leave_requests.from_date <= '$yesterdayDate' AND leave_requests.to_date >= '$yesterdayDate' LEFT JOIN relax_day ON relax_day.date = '$yesterdayDate' AND relax_day.deleted_at IS NULL AND relax_day.department_id = promotions.department_id LEFT JOIN assign_relax_day ON assign_relax_day.relax_day_id = relax_day.id AND assign_relax_day.user_id = users.id AND assign_relax_day.deleted_at IS NULL AND assign_relax_day.approval_status = 1 WHERE users.`status` = 1 AND users.deleted_at IS NULL AND users.fingerprint_no IN ( $attendantUsersData ) GROUP BY users.id ) AS A";

                $attendantEmployeesData = DB::select($attendantEmployees);


                foreach ($attendantEmployeesData as $key => $attendantEmployee) {
                    $inTime                     = date('H:i:s', strtotime(self::empInTime($attendantEmp, $attendantEmployee->fingerprint_no)));


                    //3=NOT APPLIED FOR LEAVE
                    if (is_null($attendantEmployee->leave_requested_half_day)) {
                        $existingLeaveStatus = 3;
                    } else {
                        $existingLeaveStatus = $attendantEmployee->leave_requested_half_day;
                    }


                    //WEEKLY HOLIDAY CHECK
                    $weeklyHoliday          = (isset($attendantEmployee->weekly_holidays)? in_array(strtolower($day), json_decode($attendantEmployee->weekly_holidays)): null);

                    if(is_null($attendantEmployee->roster_types)){
                        $roaster_weekly_holiday = false;
                        $attendantEmployee->roaster_work_slot_id = null;
                    }else{
                        $roster_types = explode(',',$attendantEmployee->roster_types);
                        $roster_work_slots = explode(',',$attendantEmployee->roster_work_slots);
                        $roster_weekly_holiday = explode(',',$attendantEmployee->roster_weekly_holiday);
                        if(in_array(1,$roster_types)){
                            $key = array_search(1, $roster_types);
                        }else{
                            $key = array_search(2, $roster_types);
                        }
                        $roaster_weekly_holiday = $roster_weekly_holiday[$key];
                        $attendantEmployee->roaster_work_slot_id = $roster_work_slots[$key];
                    }

                    //PUBLIC HOLIDAY CHECK
                    if (is_null($publicHoliday)) {
                        //CHECK ROASTER DUTY
                        if (is_null($attendantEmployee->roaster_work_slot_id)) {

                            if (!$weeklyHoliday) {
                                if (is_null($attendantEmployee->relax_day_id)) {
                                    //ROASTER DUTY CHECK
                                    $late_count_time_promotion  = self::workSlotDetails($workSlotData, $attendantEmployee->workslot_id);
                                    $empLateTime                = self::inOutRemainingTime($inTime, $late_count_time_promotion->late_count_time);

                                    //LEAVE HALF OR FULL DAY
                                    $is_half_day = self::halfFullDayDataCheck($empLateTime, $attendantEmployee->leave_al_half_day_count, $globalSetting, $existingLeaveStatus);
                                    Log::info('is half day'. $is_half_day);

                                    if ($is_half_day==0 || $is_half_day==1) {
                                        $inputData[]    = [
                                            'user_id'           => $attendantEmployee->id,
                                            'leave_date'        => $yesterdayDate,
                                            'status'            => 1, //NOT APPLIED FOR HALF DAY LEAVE
                                            'is_half_day'       => $is_half_day,
                                            'created_at'        => now()
                                        ];

                                        Log::info(json_encode($inputData));
                                    }
                                } else {
                                    //RELAX DAY
                                }
                            } else {
                                //WEEKLY HOLIDAY
                            }
                        } else {
                            if (!$roaster_weekly_holiday) {
                                if (is_null($attendantEmployee->relax_day_id)) {
                                    $late_count_time_roaster    = self::workSlotDetails($workSlotData, $attendantEmployee->roaster_work_slot_id);
                                    $empLateTime                = self::inOutRemainingTime($inTime, $late_count_time_roaster->late_count_time);

                                    //LEAVE HALF OR FULL DAY
                                    $is_half_day = self::halfFullDayDataCheck($empLateTime, $attendantEmployee->leave_al_half_day_count, $globalSetting, $existingLeaveStatus);
                                    Log::info('is half day'. $is_half_day);

                                    if ($is_half_day==0 || $is_half_day==1) {
                                        $inputData[]    = [
                                            'user_id'           => $attendantEmployee->id,
                                            'leave_date'        => $yesterdayDate,
                                            'status'            => 1, //NOT APPLIED FOR HALF DAY LEAVE
                                            'is_half_day'       => $is_half_day,
                                            'created_at'        => now()
                                        ];

                                        Log::info(json_encode($inputData));
                                    }
                                } else {
                                    //RELAX DAY
                                }
                            } else {
                                //ROASTER WEEKLY HOLIDAY
                            }
                        }
                    } else {
                        //PUBLIC HOLIDAY
                    }
                }
            }
            //END ATTENDANT EMPLOYEES



            //ABSENT EMPLOYEES
            if ($attendantUsersData) {
                $absentEmployees    = "SELECT A.*,( SELECT GROUP_CONCAT( rosters.type) FROM rosters WHERE(( rosters.`user_id` = A.`id` AND rosters.`department_id` = A.`department_id` ) OR ( rosters.`department_id` = A.`department_id` AND rosters.`user_id` IS NULL ) ) AND rosters.`active_date` = '$yesterdayDate' AND rosters.`status` = 1 AND rosters.deleted_at IS NULL ) AS roster_types, ( SELECT GROUP_CONCAT( rosters.work_slot_id ) FROM rosters WHERE (( rosters.`user_id` = A.`id` AND rosters.`department_id` = A.`department_id` ) OR ( rosters.`department_id` = A.`department_id` AND rosters.`user_id` IS NULL ) ) AND rosters.`active_date` = '$yesterdayDate' AND rosters.`status` = 1 AND rosters.deleted_at IS NULL ) AS roster_work_slots, ( SELECT GROUP_CONCAT( rosters.is_weekly_holiday ) FROM rosters WHERE (( rosters.`user_id` = A.`id` AND rosters.`department_id` = A.`department_id` ) OR ( rosters.`department_id` = A.`department_id` AND rosters.`user_id` IS NULL ) ) AND rosters.`active_date` = '$yesterdayDate' AND rosters.`status` = 1 AND rosters.deleted_at IS NULL ) AS roster_weekly_holiday FROM ( SELECT users.id, users.fingerprint_no, promotions.user_id, promotions.office_division_id, promotions.department_id, promotions.designation_id, promotions.promoted_date, promotions.type, promotions.workslot_id, work_slots.start_time AS work_slot_start_time, work_slots.end_time AS work_slot_end_time, work_slots.late_count_time AS work_slot_late_count_time, weekly_holidays.days AS weekly_holidays, leave_allocations.half_day_count AS leave_al_half_day_count, leave_requests.half_day AS leave_requested_half_day, leave_requests.`status` AS leave_requested_status, assign_relax_day.relax_day_id FROM users INNER JOIN promotions ON promotions.user_id = users.id AND promotions.id =( SELECT MAX( pm.id ) FROM promotions AS pm WHERE pm.user_id = users.id AND pm.promoted_date <= '$yesterdayDate' ) INNER JOIN work_slots ON work_slots.id = promotions.workslot_id LEFT JOIN weekly_holidays ON weekly_holidays.department_id = promotions.department_id AND weekly_holidays.effective_date <= '$yesterdayDate' AND ( weekly_holidays.end_date >= '$yesterdayDate' OR weekly_holidays.end_date IS NULL ) LEFT JOIN leave_allocations ON leave_allocations.department_id = promotions.department_id AND leave_allocations.office_division_id = promotions.office_division_id AND leave_allocations.`year` = '$year' LEFT JOIN leave_requests ON leave_requests.user_id = users.id AND leave_requests.office_division_id = promotions.office_division_id AND leave_requests.department_id = promotions.department_id AND leave_requests.`status` IN ( 0, 1, 2, 3 ) AND leave_requests.from_date <= '$yesterdayDate' AND leave_requests.to_date >= '$yesterdayDate' LEFT JOIN relax_day ON relax_day.date = '$yesterdayDate' AND relax_day.deleted_at IS NULL AND relax_day.department_id = promotions.department_id LEFT JOIN assign_relax_day ON assign_relax_day.relax_day_id = relax_day.id AND assign_relax_day.user_id = users.id AND assign_relax_day.deleted_at IS NULL AND assign_relax_day.approval_status = 1 WHERE users.`status` = 1 AND users.deleted_at IS NULL AND users.fingerprint_no NOT IN ( $attendantUsersData ) GROUP BY users.id ) AS A";

                    $absentEmployeesData    = DB::select($absentEmployees);


                    //DATA ARRAY GENERATE
                    foreach ($absentEmployeesData as $absentEmployee) {
                        $weeklyHoliday = (isset($absentEmployee->weekly_holidays)? in_array(strtolower($day), json_decode($absentEmployee->weekly_holidays)): null);


                        if(is_null($absentEmployee->roster_types)){
                            $roaster_weekly_holiday = false;
                            $absentEmployee->roaster_work_slot_id = null;
                        }else{
                            $roster_types = explode(',',$absentEmployee->roster_types);
                            $roster_work_slots = explode(',',$absentEmployee->roster_work_slots);
                            $roster_weekly_holiday = explode(',',$absentEmployee->roster_weekly_holiday);
                            if(in_array(1,$roster_types)){
                                $key = array_search(1, $roster_types);
                            }else{
                                $key = array_search(2, $roster_types);
                            }
                            $roaster_weekly_holiday = $roster_weekly_holiday[$key];
                            $absentEmployee->roaster_work_slot_id = $roster_work_slots[$key];
                        }

                        //PUBLIC HOLIDAY CHECK
                        if (is_null($publicHoliday)) {
                            //CHECK ROASTER DUTY
                            if (is_null($absentEmployee->roaster_work_slot_id)) {
                                if ($weeklyHoliday==false) {
                                    if (is_null($absentEmployee->relax_day_id)) {
                                        if (is_null($absentEmployee->leave_requested_half_day)) {
                                            $inputData[]    = [
                                                'user_id'           => $absentEmployee->id,
                                                'leave_date'        => $yesterdayDate,
                                                'status'            => 1, //NOT APPLIED FOR HALF DAY LEAVE
                                                'is_half_day'       => 0,
                                                'created_at'        => now()
                                            ];

                                            Log::info(json_encode($inputData));
                                        } else {
                                            if ($absentEmployee->leave_requested_half_day == 1) { //1=Half day
                                                $inputData[]    = [
                                                    'user_id'           => $absentEmployee->id,
                                                    'leave_date'        => $yesterdayDate,
                                                    'status'            => 1, //NOT APPLIED FOR HALF DAY LEAVE
                                                    'is_half_day'       => 0,
                                                    'created_at'        => now()
                                                ];

                                                Log::info(json_encode($inputData));
                                            }
                                        }
                                    } else {
                                        //RELAX DAY
                                    }
                                } else {
                                    //WEEKLY HOLIDAY
                                }
                            } else { //ROASTER WEEKLY HOLIDAY
                                if (!$roaster_weekly_holiday) {
                                    if (is_null($absentEmployee->relax_day_id)) {
                                        if (is_null($absentEmployee->leave_requested_half_day)) {
                                            $inputData[]    = [
                                                'user_id'           => $absentEmployee->id,
                                                'leave_date'        => $yesterdayDate,
                                                'status'            => 1, //NOT APPLIED FOR HALF DAY LEAVE
                                                'is_half_day'       => 0,
                                                'created_at'        => now()
                                            ];

                                            Log::info(json_encode($inputData));
                                        } else {
                                            if ($absentEmployee->leave_requested_half_day == 1) { //1=Half day
                                                $inputData[]    = [
                                                    'user_id'           => $absentEmployee->id,
                                                    'leave_date'        => $yesterdayDate,
                                                    'status'            => 1, //NOT APPLIED FOR HALF DAY LEAVE
                                                    'is_half_day'       => 0,
                                                    'created_at'        => now()
                                                ];

                                                Log::info(json_encode($inputData));
                                            }
                                        }
                                    } else {
                                        //RELAX DAY
                                    }
                                } else {
                                    //ROASTER HOLIDAY
                                }
                            }
                        } else {
                            //PUBLIC HOLIDAY
                        }
                    }

                    $response = [
                        "success"   => true,
                        "message"   => true,
                        "data"      => $inputData
                    ];
                } else {

                    $absentEmployees    = "SELECT A.*,( SELECT GROUP_CONCAT( rosters.type) FROM rosters WHERE(( rosters.`user_id` = A.`id` AND rosters.`department_id` = A.`department_id` ) OR ( rosters.`department_id` = A.`department_id` AND rosters.`user_id` IS NULL ) ) AND rosters.`active_date` = '$yesterdayDate' AND rosters.`status` = 1 AND rosters.deleted_at IS NULL ) AS roster_types, ( SELECT GROUP_CONCAT( rosters.work_slot_id ) FROM rosters WHERE (( rosters.`user_id` = A.`id` AND rosters.`department_id` = A.`department_id` ) OR ( rosters.`department_id` = A.`department_id` AND rosters.`user_id` IS NULL ) ) AND rosters.`active_date` = '$yesterdayDate' AND rosters.`status` = 1 AND rosters.deleted_at IS NULL ) AS roster_work_slots, ( SELECT GROUP_CONCAT( rosters.is_weekly_holiday ) FROM rosters WHERE (( rosters.`user_id` = A.`id` AND rosters.`department_id` = A.`department_id` ) OR ( rosters.`department_id` = A.`department_id` AND rosters.`user_id` IS NULL ) ) AND rosters.`active_date` = '$yesterdayDate' AND rosters.`status` = 1 AND rosters.deleted_at IS NULL ) AS roster_weekly_holiday FROM ( SELECT users.id, users.fingerprint_no, promotions.user_id, promotions.office_division_id, promotions.department_id, promotions.designation_id, promotions.promoted_date, promotions.type, promotions.workslot_id, work_slots.start_time AS work_slot_start_time, work_slots.end_time AS work_slot_end_time, work_slots.late_count_time AS work_slot_late_count_time, weekly_holidays.days AS weekly_holidays, leave_allocations.half_day_count AS leave_al_half_day_count, leave_requests.half_day AS leave_requested_half_day, leave_requests.`status` AS leave_requested_status, assign_relax_day.relax_day_id FROM users INNER JOIN promotions ON promotions.user_id = users.id AND promotions.id =( SELECT MAX( pm.id ) FROM promotions AS pm WHERE pm.user_id = users.id AND pm.promoted_date <= '$yesterdayDate' ) INNER JOIN work_slots ON work_slots.id = promotions.workslot_id LEFT JOIN weekly_holidays ON weekly_holidays.department_id = promotions.department_id AND weekly_holidays.effective_date <= '$yesterdayDate' AND ( weekly_holidays.end_date >= '$yesterdayDate' OR weekly_holidays.end_date IS NULL ) LEFT JOIN leave_allocations ON leave_allocations.department_id = promotions.department_id AND leave_allocations.office_division_id = promotions.office_division_id AND leave_allocations.`year` = '$year' LEFT JOIN leave_requests ON leave_requests.user_id = users.id AND leave_requests.office_division_id = promotions.office_division_id AND leave_requests.department_id = promotions.department_id AND leave_requests.`status` IN ( 0, 1, 2, 3 ) AND leave_requests.from_date <= '$yesterdayDate' AND leave_requests.to_date >= '$yesterdayDate' LEFT JOIN relax_day ON relax_day.date = '$yesterdayDate' AND relax_day.deleted_at IS NULL AND relax_day.department_id = promotions.department_id LEFT JOIN assign_relax_day ON assign_relax_day.relax_day_id = relax_day.id AND assign_relax_day.user_id = users.id AND assign_relax_day.deleted_at IS NULL AND assign_relax_day.approval_status = 1 WHERE users.`status` = 1 AND users.deleted_at IS NULL GROUP BY users.id ) AS A";

                    $absentEmployeesData    = DB::select($absentEmployees);

                    //DATA ARRAY GENERATE
                    foreach ($absentEmployeesData as $absentEmployee) {
                        $weeklyHoliday = (isset($absentEmployee->weekly_holidays)? in_array(strtolower($day), json_decode($absentEmployee->weekly_holidays)): null);

                        if(is_null($absentEmployee->roster_types)){
                            $roaster_weekly_holiday = false;
                            $absentEmployee->roaster_work_slot_id = null;
                        }else{
                            $roster_types = explode(',',$absentEmployee->roster_types);
                            $roster_work_slots = explode(',',$absentEmployee->roster_work_slots);
                            $roster_weekly_holiday = explode(',',$absentEmployee->roster_weekly_holiday);
                            if(in_array(1,$roster_types)){
                                $key = array_search(1, $roster_types);
                            }else{
                                $key = array_search(2, $roster_types);
                            }
                            $roaster_weekly_holiday = $roster_weekly_holiday[$key];
                            $absentEmployee->roaster_work_slot_id = $roster_work_slots[$key];
                        }



                        //PUBLIC HOLIDAY CHECK
                        if (is_null($publicHoliday)) {
                            //CHECK ROASTER DUTY
                            if (is_null($absentEmployee->roaster_work_slot_id)) {
                                if ($weeklyHoliday==false) {
                                    if (is_null($absentEmployee->relax_day_id)) {
                                        if (is_null($absentEmployee->leave_requested_half_day)) {
                                            $inputData[]    = [
                                                'user_id'           => $absentEmployee->id,
                                                'leave_date'        => $yesterdayDate,
                                                'status'            => 1, //NOT APPLIED FOR HALF DAY LEAVE
                                                'is_half_day'       => 0,
                                                'created_at'        => now()
                                            ];

                                            Log::info(json_encode($inputData));
                                        } else {
                                            if ($absentEmployee->leave_requested_half_day == 1) { //1=Half day
                                                $inputData[]    = [
                                                    'user_id'           => $absentEmployee->id,
                                                    'leave_date'        => $yesterdayDate,
                                                    'status'            => 1, //NOT APPLIED FOR HALF DAY LEAVE
                                                    'is_half_day'       => 0,
                                                    'created_at'        => now()
                                                ];

                                                Log::info(json_encode($inputData));
                                            }
                                        }
                                    } else {
                                        //RELAX DAY
                                    }
                                } else {
                                    //WEEKLY HOLIDAY
                                }
                            } else { //ROASTER WEEKLY HOLIDAY
                                if (!$roaster_weekly_holiday) {
                                    if (is_null($absentEmployee->relax_day_id)) {

                                        if (is_null($absentEmployee->leave_requested_half_day)) {
                                            $inputData[]    = [
                                                'user_id'           => $absentEmployee->id,
                                                'leave_date'        => $yesterdayDate,
                                                'status'            => 1, //NOT APPLIED FOR HALF DAY LEAVE
                                                'is_half_day'       => 0,
                                                'created_at'        => now()
                                            ];

                                            Log::info(json_encode($inputData));
                                        } else {
                                            if ($absentEmployee->leave_requested_half_day == 1) { //1=Half day
                                                $inputData[]    = [
                                                    'user_id'           => $absentEmployee->id,
                                                    'leave_date'        => $yesterdayDate,
                                                    'status'            => 1, //NOT APPLIED FOR HALF DAY LEAVE
                                                    'is_half_day'       => 0,
                                                    'created_at'        => now()
                                                ];

                                                Log::info(json_encode($inputData));
                                            }
                                        }
                                    } else {
                                        //RELAX DAY
                                    }
                                } else {
                                    //ROASTER HOLIDAY
                                }
                            }
                        } else {
                            //PUBLIC HOLIDAY
                        }
                    }

                    //MAKE DATA ARRAY
                    $response = [
                        "success"   => true,
                        "message"   => true,
                        "data"      => $inputData
                    ];
                }
            //END ABSENT EMPLOYEES

        } catch (Exception $exception) {
            $success = false;
            $message = $exception->getMessage();

            $response = [
                "success"   => false,
                "message"   => $exception->getMessage(),
                "data"      => []
            ];

            Log::info(json_encode($response));

        }

        return $response;
    }

    //EMPLOYEE HALF OR FULL DAY LEAVE DATA CHECK
    public static function halfFullDayDataCheck($empLateTime, $leaveAllocationHalfDayCount, $globalSetting, $existingLeaveStatus){
        //DEPARTMENT WISE SETING
        if ($leaveAllocationHalfDayCount != NULL || $leaveAllocationHalfDayCount != '') { //DEPARTMENT WISE SETTING

            $full_day_duration      = self::convertToHoursMins($leaveAllocationHalfDayCount);
            $half_late_duration     = self::floatTimeToSeconds(($leaveAllocationHalfDayCount*60)/100);


            if ($existingLeaveStatus==3) {
                if ($empLateTime>$full_day_duration) { //FULL DAY LEAVE
                    return $is_half_day = 0;

                } else { //HALF DAY LEAVE
                    if ($half_late_duration < $empLateTime) {
                        return $is_half_day = 1; //1=Half day
                    } else {
                        //NOT NEED HALF DAY APPLICATION
                        return $is_half_day = 3; //3=Not Effective Data as like null data
                    }
                }
            } else {
                if ($existingLeaveStatus==0) {
                    return $is_half_day = 3; //3=Not Effective Data as like null data
                } else {
                    if ($empLateTime>$full_day_duration) { //FULL DAY LEAVE
                        return $is_half_day = 0;
                    } else {
                        return $is_half_day = 3;
                    }
                }
            }

        } else { //GLOBAL SETING
            $full_day_duration      = self::convertToHoursMins($globalSetting);
            $half_late_duration     = self::floatTimeToSeconds(($globalSetting*60)/100);

            if ($existingLeaveStatus==3) {
                if ($empLateTime>$full_day_duration) { //FULL DAY LEAVE
                    return $is_half_day = 0;

                } else { //HALF DAY LEAVE
                    if ($half_late_duration < $empLateTime) {
                        return $is_half_day = 1; //1=Half day
                    } else {
                        //NOT NEED HALF DAY APPLICATION
                        return $is_half_day = 3; //3=Not Effective Data as like null data
                    }
                }
            } else {

                if ($existingLeaveStatus==0) {
                    return $is_half_day = 3; //3=Not Effective Data as like null data
                } else {
                    if ($empLateTime>$full_day_duration) { //FULL DAY LEAVE
                        return $is_half_day = 0;
                    } else {
                        return $is_half_day = 3;
                    }
                }
            }
        }
    }
    //END EMPLOYEE HALF OR FULL DAY LEAVE DATA CHECK

    public static function floatTimeToSeconds($time){
        $firstVal = (int) $time;
        $seconds = $firstVal * 60;
        $remainder = (($time - $firstVal) * 10) * 10;
        return ($remainder+$seconds)*60;
    }

    public static function convertToHoursMins($time) {
        $seconds = ($time*60)*60;

        return $seconds;
    }

    public static function inOutRemainingTime($inTime, $lateCountTime){

        $emp_in_time = date('H:i:s', strtotime($inTime));
        sscanf($emp_in_time, "%d:%d:%d", $hours, $minutes, $seconds);
        $emp_in_time_seconds = isset($seconds) ? $hours * 3600 + $minutes * 60 + $seconds : $hours * 60 + $minutes;

        $emp_late_count_time = date('H:i:s', strtotime($lateCountTime));
        sscanf($emp_late_count_time, "%d:%d:%d", $hours, $minutes, $seconds);
        $late_count_time_seconds = isset($seconds) ? $hours * 3600 + $minutes * 60 + $seconds : $hours * 60 + $minutes;

        return $emp_in_time_seconds-$late_count_time_seconds;
    }


    //WORK SLOT DATA
    public static function workSlotDetails($workSlotData, $work_slot_id){
        $keys = array_keys($workSlotData);
        $workSlotInfo = $workSlotData[$work_slot_id];
        return $workSlotInfo;
    }
    //END WORK SLOT DATA

    //EMPLOYEE INTIME GET
    public static function empInTime($attendantEmpArray, $emp_code){
        $keys = array_keys($attendantEmpArray);
        $attendantEmpPunchTime = $attendantEmpArray[$emp_code];
        return $attendantEmpPunchTime;
    }



    /**
     * @return Application|Factory|View
     */
    public function generateByMonth()
    {
        try {
            DB::transaction(function () {
                $items = LeaveUnpaid::orderByDesc("id")->get();
                $activeEmployees = Employee::active()->select("id", "fingerprint_no")->get();

                $month_ini = new DateTime("first day of last month");
                $month_end = new DateTime("last day of last month");

                # Loop through each day of the previous month
                $startDate  = (int) $month_ini->format("d");
                $endDate    = (int) $month_end->format("d");

                $days = [];
                // TODO: Fix the limit as $endDate rather the number "7". This need to be changed on the limit to the following loop
                for ($i = $startDate; $i <= 7; $i++) {
                    array_push($days,
                        date("Y-m", strtotime("-1 months")) . "-" . str_pad($i, 2, '0', STR_PAD_LEFT)
                    );
                }

                foreach ($activeEmployees as $employee) {
                    foreach($days as $day) {
                        $checkAttendance = ZKTeco::where("emp_code", $employee->fingerprint_no)->whereDate("punch_time", $day)->first();
                        if (!isset($checkAttendance)) {

                            # Check whether have any Requested Leave application
                            $leaveRequest = LeaveRequest::where("user_id", $employee->id)
                                ->whereDate("from_date", "<=", $day)
                                ->whereDate("to_date", ">=", $day)
                                ->whereStatus(LeaveRequest::STATUS_APPROVED)
                                ->first();

                            # Check any Public Holiday on the previous day
                            if (!isset($leaveRequest)) {
                                $publicHoliday = PublicHoliday::whereDate("from_date", "<=", $day)
                                    ->whereDate("to_date", ">=", $day)
                                    ->first();

                                # Check whether last day is Weekly Holiday
                                if (!isset($publicHoliday)) {
                                    $currentPromotion = $employee->load("currentPromotion");
                                    $departmentId = $currentPromotion->currentPromotion->department_id;

                                    $weeklyHoliday = WeeklyHoliday::whereDepartmentId($departmentId)->first();

                                    if (is_null($weeklyHoliday)) {
                                        $isHoliday = false;
                                    } else {
                                        $weeklyHolidays = json_decode($weeklyHoliday->days);
                                        $dayOnDate = strtolower(date('D', strtotime($day)));
                                        $isHoliday = in_array($dayOnDate, $weeklyHolidays);
                                    }

                                    if ($isHoliday == false) {
                                        $success = LeaveUnpaid::create(array(
                                            "user_id" => $employee->id,
                                            "leave_date" => $day
                                        ));

                                        session()->flash('message', 'UnPaid Leave Generated Successfully');
                                    }
                                }
                            }
                        }
                    }
                }
            });
            $redirect = redirect()->route("home");
        } catch (Exception $exception) {
            $redirect = redirect()->back();
        }

        return $redirect;
    }
}
