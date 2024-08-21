<?php

namespace App\Http\Controllers;

use App\Models\HolidayAllowance;
use App\Models\Overtime;
use App\Models\PublicHoliday;
use App\Models\Setting;
use App\Models\User;
use App\Models\WeeklyHoliday;
use App\Models\ZKTeco\Attendance as ZKTeco;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class HolidayAllowanceController extends Controller
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
    public function generate(): array
    {
        try {
            DB::transaction(function () {
                $yesterday = date('Y-m-d', strtotime("-1 days"));
                $employees = User::with("currentPromotion.workSlot", "currentPromotion.payGrade")->active()->get();

                foreach ($employees as $employee) {
                    $holidayType = $this->getHolidayType($employee, $yesterday);

                    if(!is_null($holidayType)) {
                        $attendance = ZKTeco::with(["attendances" => function ($query) use ($yesterday, $employee) {
                            $query->where("punch_time", "LIKE", $yesterday . "%");
                        }])
                            ->whereEmpCode($employee->fingerprint_no)
                            ->select("id", "emp_code", "punch_time", "terminal_alias", "area_alias")
                            ->first();

                        # Check the eligibility for holiday allowance
                        if (!empty($attendance->attendances) AND count($attendance->attendances) > 0) {
                            # Insert data to database
                            HolidayAllowance::firstOrCreate([
                                "user_id"       => $employee->id,
                                "holiday_date"  => $yesterday,
                                "type"          => $holidayType
                            ]);
                        }
                    }
                }
            });

            $response = [ "success" => true ];
        } catch (\Exception $exception) {
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
    protected function getHolidayType($employee, $yesterday) {
        # Check Weekly Holiday
        $weeklyHoliday = WeeklyHoliday::where("department_id", $employee->currentPromotion->department_id)->first();
        $weeklyHolidays = json_decode( $weeklyHoliday->days );
        $day = date("D", strtotime($yesterday));

        # Check Public Holiday
        $publicHoliday = PublicHoliday::where("from_date", ">=", $yesterday)
            ->where("to_date", "<=", $yesterday)
            ->count();

        if(in_array(strtolower($day), $weeklyHolidays)) $type = HolidayAllowance::TYPE_WEEKLY;
        elseif ($publicHoliday > 0) $type = HolidayAllowance::TYPE_ORGANIZATIONAL;
        else $type = null;

        return $type;
    }
}
