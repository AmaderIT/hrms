<?php

namespace App\Http\Controllers;

use App\Models\Overtime;
use App\Models\PublicHoliday;
use App\Models\Setting;
use App\Models\User;
use App\Models\WeeklyHoliday;
use App\Models\ZKTeco\Attendance as ZKTeco;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\DB;

class OverTimeController extends Controller
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
        $response = [ "success" => true ];

        try {
            DB::transaction(function () {
                $yesterday = date('Y-m-d', strtotime("-1 days"));
                $employees = User::with("currentPromotion.workSlot")->active()->get();

                foreach ($employees as $employee) {
                    $isHoliday = $this->isHoliday($employee, $yesterday);

                    if (!$isHoliday) {
                        $attendance = ZKTeco::with(["attendances" => function ($query) use ($yesterday, $employee) {
                            $query->where("punch_time", "LIKE", $yesterday . "%");
                        }])
                            ->whereEmpCode($employee->fingerprint_no)
                            ->select("id", "emp_code", "punch_time", "terminal_alias", "area_alias")
                            ->first();

                        $attendanceData = ["entry" => null, "exit" => null];
                        if (!empty($attendance->attendances)) {
                            $attendanceCountStartTime = Setting::where("name", "attendance_count_start_time")->select("id", "value")->first()->value;

                            # Find Next Day
                            $nextDay = new \DateTime($yesterday);
                            $nextDay = $nextDay->modify("+1 day");
                            $nextDay = $nextDay->format("Y-m-d");

                            $entry = $attendance->attendances->where("punch_time", ">=", Carbon::parse("${yesterday} ${attendanceCountStartTime}"))->first();

                            $exitBeforeNextDay = ZKTeco::with(["attendances" => function ($query) use ($nextDay, $employee) {
                                $query->where("punch_time", "LIKE", $nextDay . "%");
                            }])
                                ->whereEmpCode($employee->fingerprint_no)
                                ->select("id", "emp_code", "punch_time", "terminal_alias", "area_alias")
                                ->first();

                            $exitEntry = $exitBeforeNextDay->attendances->where("punch_time", "<", Carbon::parse("${nextDay} ${attendanceCountStartTime}"))->first();

                            $exit = null;
                            if(collect($exitEntry)->count() > 0) $exit = $exitEntry;
                            else $exit = $attendance->attendances->last();

                            $attendanceData = [
                                "entry" => $entry,
                                "exit"  => $exit
                            ];
                        }

                        $startTime = $employee->currentPromotion->workSlot->start_time;
                        $endTime = $employee->currentPromotion->workSlot->end_time;
                        $totalOfficeTime = (strtotime($endTime) - strtotime($startTime)) / (60 * 60);

                        if (!is_null($attendanceData["entry"]) AND !is_null($attendanceData["exit"])) {
                            $entryTime = $attendanceData["entry"]->punch_time;
                            $exitTime = $attendanceData["exit"]->punch_time;

                            $inOffice = (strtotime($exitTime) - strtotime($entryTime)) / (60 * 60);

                            if (($inOffice - $totalOfficeTime) >= 1) {
                                $eligibleOverTime = $inOffice - $totalOfficeTime;
                                $eligibleOverTime = number_format($eligibleOverTime, 2);

                                Overtime::firstOrCreate([
                                    "user_id"       => $employee->id,
                                    "overtime_date" => $yesterday
                                ], [
                                    "user_id"       => $employee->id,
                                    "overtime_date" => $yesterday,
                                    "hours"         => $eligibleOverTime,
                                ]);
                            }
                        }
                    }
                }
            });
        } catch (Exception $exception) {
            $response = [
                "success" => false,
                "message" => $exception->getMessage()
            ];
        }

        return $response;
    }

    /**
     * @param $employee
     * @param $yesterday
     * @return bool
     */
    protected function isHoliday($employee, $yesterday) {
        # Check Weekly Holiday
        $weeklyHoliday = WeeklyHoliday::where("department_id", $employee->currentPromotion->department_id)->first();
        $weeklyHolidays = json_decode( $weeklyHoliday->days );
        $day = date("D", strtotime($yesterday));

        if(in_array(strtolower($day), $weeklyHolidays)) return true;

        # Check Public Holiday
        $publicHoliday = PublicHoliday::where("from_date", ">=", $yesterday)
            ->where("to_date", "<=", $yesterday)
            ->count();

        if($publicHoliday > 0) return true;
    }
}
