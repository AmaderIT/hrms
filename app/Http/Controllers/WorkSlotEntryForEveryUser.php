<?php

namespace App\Http\Controllers;

use App\Models\AssignRelaxDay;
use App\Models\DailyCronLog;
use App\Models\LeaveRequest;
use App\Models\PublicHoliday;
use App\Models\Roster;
use App\Models\Setting;
use App\Models\UsersDailyWorkSlot;
use App\Models\WorkSlot;
use App\Models\ZKTeco\Attendance;
use App\Models\ZKTeco\DailyAttendance;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Exception;

class WorkSlotEntryForEveryUser extends Controller
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
    public function insert($date)
    {
        DB::beginTransaction();
        try {
            $sql_promotion = "SELECT users.id, users.fingerprint_no, promotions.office_division_id, promotions.department_id, promotions.promoted_date, promotions.workslot_id FROM users INNER JOIN promotions ON promotions.user_id = users.id AND promotions.id =( SELECT MAX( pm.id) FROM `promotions` AS pm WHERE pm.user_id = users.id AND pm.promoted_date <= '$date' ) WHERE users.`status` = 1";
            $employees = DB::select($sql_promotion);
            Log::info(count($employees));
            $approved_value = Roster::STATUS_APPROVED;
            $sql_roster = "SELECT * FROM `rosters` WHERE `active_date` = '$date' AND `status` = $approved_value AND deleted_at IS NULL ";
            $roster_records = DB::select($sql_roster);
            $roster_type_user = [];
            $roster_type_department = [];
            foreach($roster_records as $each_roster){
                if($each_roster->user_id){
                    $roster_type_user[$each_roster->user_id]['work_slot_id'] = $each_roster->work_slot_id;
                }else{
                    $roster_type_department[$each_roster->department_id]['work_slot_id'] = $each_roster->work_slot_id;
                }
            }
            $work_slot_arr = [];
            $work_slots = WorkSlot::all();
            foreach ($work_slots as $slot){
                $work_slot_arr[$slot->id]=$slot;
            }
            foreach ($employees as $employee) {
                if(isset($roster_type_user[$employee->id])){
                    $work_slot_id = $roster_type_user[$employee->id]['work_slot_id'];
                }else{
                    if(isset($roster_type_department[$employee->department_id])){
                        $work_slot_id = $roster_type_department[$employee->department_id]['work_slot_id'];
                    }else{
                        $work_slot_id = $employee->workslot_id;
                    }
                }
                $daily_work_slot_log  = [];
                $daily_work_slot_log['user_id'] = $employee->id;
                $daily_work_slot_log['date'] = $date;
                $daily_work_slot_log['work_slot_id'] = $work_slot_id;
                $daily_work_slot_log['work_slot_title'] = $work_slot_arr[$work_slot_id]->title;
                $daily_work_slot_log['start_time'] = $work_slot_arr[$work_slot_id]->start_time;
                $daily_work_slot_log['end_time'] = $work_slot_arr[$work_slot_id]->end_time;
                $daily_work_slot_log['late_count_time'] = $work_slot_arr[$work_slot_id]->late_count_time;
                $daily_work_slot_log['is_flexible'] = $work_slot_arr[$work_slot_id]->is_flexible;
                $daily_work_slot_log['over_time'] = $work_slot_arr[$work_slot_id]->over_time;
                $daily_work_slot_log['overtime_count'] = $work_slot_arr[$work_slot_id]->overtime_count;
                $daily_work_slot_log['total_work_hour'] = $work_slot_arr[$work_slot_id]->total_work_hour;
                UsersDailyWorkSlot::create($daily_work_slot_log);
            }
            DailyCronLog::insert(['cron_key'=>'daily_work_slot','date'=>$date,'created_at'=>now()]);
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
}
