<?php

namespace App\Http\Controllers;

use App\Models\LateDeduction;
use App\Models\User as Employee;
use App\Models\ZKTeco\Attendance as ZKTeco;
use App\Models\UserLate;
use Illuminate\Contracts\View\Factory;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Exception;
use Illuminate\View\View;

class UserLateController extends Controller
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
    public function index()
    {
        $items = UserLate::where("user_id", auth()->user()->id)->where("year", date("Y"))->paginate(\Functions::getPaginate());
        $items = UserLate::where("user_id", 216)->where("year", date("Y"))->paginate(\Functions::getPaginate());

//        dd(auth()->user()->fingerprint_no);
//        dd(date("Y-m-d H:i:s", strtotime(auth()->user()->currentPromotion->workSlot->late_count_time)));

        $attendance = ZKTeco::where("emp_code", 302)
            ->whereMonth("punch_time", 11)
            ->whereYear("punch_time", 2021)
            ->select("id", "emp_code", "punch_time")
            ->groupBy(DB::raw("DAY(punch_time)"))
            ->get();

        $lateAttendances = $attendance->filter(function ($query) {
            $punchTime = date("H:i:s", strtotime($query->punch_time));
            $lateCountTime = date("H:i:s", strtotime(auth()->user()->currentPromotion->workSlot->late_count_time));

            if($punchTime > $lateCountTime) return $query;
        })->values();

        return view("user-late.index", compact("items"));
    }

    /**
     * @return JsonResponse
     */
    public function generate()
    {
        DB::beginTransaction();

        try {
            $activeEmployees = Employee::whereStatus(Employee::STATUS_ACTIVE)->select("id", "fingerprint_no")->get();
            $yesterday = date('Y-m-d', strtotime("yesterday"));

            foreach ($activeEmployees as $employee) {
                $currentPromotion = $employee->currentPromotion;
                $lateCountTime = date("H:i:s", strtotime($currentPromotion->workSlot->late_count_time));

                $attendance = ZKTeco::whereDate("punch_time", $yesterday)
                    ->whereEmpCode($employee->fingerprint_no)
                    ->orderBy("id", "asc")
                    ->select("id", "emp_code", "punch_time", "terminal_alias")
                    ->first();

                $lateDeduction = LateDeduction::where("department_id", $currentPromotion->department_id)->first();
                $totalDaysToDeduct = $lateDeduction->total_days ?? 0;

                if($totalDaysToDeduct == 0) $totalDaysToDeduct = 100;

                if(!is_null($attendance)) {
                    $punchTime = date("H:i:s", strtotime($attendance->punch_time));

                    if($punchTime > $lateCountTime) {
                        $userLate = UserLate::where("user_id", $employee->id)
                            ->where("month", (int) date('m', strtotime("yesterday")))
                            ->where("year", date('Y', strtotime("yesterday")))
                            ->first();

                        if(!is_null($userLate)) {
                            $totalLate =  $userLate->total_late + 1;
                            $totalDeduction = (int) floor($totalLate / $totalDaysToDeduct);

                            UserLate::where("user_id", $employee->id)
                                ->where("month", (int) date('m', strtotime("yesterday")))
                                ->where("year", date('Y', strtotime("yesterday")))
                                ->update([
                                    "total_late"        => $totalLate,
                                    "total_deduction"   => $totalDeduction,
                                ]);
                        } else {
                            $totalLate = 1;
                            $totalDeduction = (int) floor($totalLate / $totalDaysToDeduct);

                            UserLate::firstOrCreate([
                                "user_id"           => $employee->id,
                                "total_late"        => $totalLate,
                                "total_deduction"   => $totalDeduction,
                                "type"              => UserLate::TYPE_LEAVE,
                                "month"             => (int) date('m', strtotime("yesterday")),
                                "year"              => (int) date('Y', strtotime("yesterday")),
                            ]);
                        }
                    }
                }
            }

            $response = ["success" => true];

            DB::commit();
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
