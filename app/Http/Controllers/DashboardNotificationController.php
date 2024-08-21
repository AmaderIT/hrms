<?php

namespace App\Http\Controllers;

use App\Models\LeaveRequest;
use App\Models\Promotion;
use App\Models\User;
use Carbon\Carbon;
use http\Env\Response;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\DataTables;
use function PHPUnit\Framework\throwException;

class DashboardNotificationController extends Controller
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

    protected $permissions = ["Provision Expiry Notification", "Total In Leave Yesterday", "View Leave Calendar"];


    private function callDynamicMethod($request)
    {

        $method_name = $request->card_key;
        return $this->$method_name($request);
    }

    public function getData(Request $request)
    {
        try {
            if (in_array($request->permission_key, $this->permissions) && auth()->user()->can($request->permission_key)) {
                $data = $request->card_key ? $this->callDynamicMethod($request) : [];
                if($request->card_key === 'leaveCalendar'){
                    $data[0]['request'] = $request->all();
                    return response()->json($data);
                }else {
                    return response()->json(['status' => 'success', 'message' => "data found", 'data' => $data], 200);
                }
            }
            return response()->json(['status' => 'error', "message" => "Unauthenticated."], 401);
        } catch (\Exception $exp) {
            return response()->json(['status' => 'error', 'message' => $exp->getMessage(), 'data' => null], 500);
        }
    }


    public function makeEmployeePermanent()
    {
        $response = ["success" => true, "message" => ""];
        try {

            $today = date("Y-m-d");
            $employees = User::where('status', User::STATUS_ACTIVE)
                ->where('provision_end_date', '<', $today)
                ->groupBy('users.id')
                ->select(['users.id', 'provision_end_date'])
                ->get();

            $c = 0;

            foreach ($employees as $emp) {

                $is_permanent_employee = Promotion::where('user_id', $emp->id)->where('type', Promotion::TYPE_PERMANENT)->count();


                if ($emp->currentPromotion && $is_permanent_employee == 0) {
                    $cp = $emp->currentPromotion;
                    if ($cp) {
                        $permanentData = $cp->replicate()->fill(
                            [
                                'type' => Promotion::TYPE_PERMANENT,
                                'employment_type' => Promotion::TYPE_PERMANENT,
                                'promoted_date' => date("Y-m-d", strtotime($emp->provision_end_date . "+1 DAY")),
                            ]
                        );
                        $permanentData->save();
                        $c++;
                    }
                }
            }
            $response = ["success" => true, "message" => "Total " . $c . " employee have been permanent."];
        } catch (\Exception $exception) {
            $response = [
                "success" => false,
                "message" => $exception->getMessage()
            ];
        }

        return $response;;
    }

    public function getEmployeeListProvision($room)
    {
        return view('dashboard-notification.employee-list-provision', ['card_title' => 'Provision Period Ending within 30 Days', 'room' => $room]);
    }

    private function provision($request)
    {
        $empIds = [];
        //checking the request from whether supervisor dashboard or admin dashboard
        if ($request->room == 'sp-room') {
            $empIds = FilterController::getEmployeeIdsSpDashboard();
        } else {
            $empIds = FilterController::getEmployeeIds();
        }
        $today = date("Y-m-d");
        $next_month = date("Y-m-d", strtotime($today . " +30 days"));
        $count = User::whereIn('id', $empIds)->whereBetween('provision_end_date', [$today, $next_month])->count('id');

        $url = route("dashboard-notification.get-employee-list-provision", ['room' => $request->room]);

        return ["count" => $count, "url" => $url];
    }


    public function getEmployeeListDataProvision(Request $request)
    {
        $authUser = auth()->user();
        $today = date("Y-m-d");
        $next_month = date("Y-m-d", strtotime($today . " +30 days"));
        $data = User::whereIn('id', FilterController::getEmployeeIds())->whereBetween('provision_end_date', [$today, $next_month])
            ->with(['currentPromotion', 'currentPromotion.officeDivision', 'currentPromotion.department', 'currentPromotion.designation'])
            ->orderBy("provision_end_date");

        return DataTables::eloquent($data)
            ->editColumn('photo', function ($item) {
                $path = "photo/" . $item->fingerprint_no . ".jpg";
                $imgSrc = file_exists($path) ? asset($path) : asset('assets/media/svg/avatars/001-boy.svg');
                return '<div class="symbol flex-shrink-0" style="width: 35px; height: auto"><img src=' . $imgSrc . '></div>';
            })
            ->addColumn('joining_date', function (User $obj) use ($authUser) {
                return $obj->joiningDate();
            })
            ->editColumn('provision_end_date', function (User $obj) use ($authUser) {
                return $obj->provisionEndDate();
            })
            ->editColumn('provision_remaining_day', function (User $obj) use ($authUser) {
                return $obj->provisionRemainingDay();
            })
            ->addColumn('service_year', function (User $obj) use ($authUser) {
                return $obj->servicePeriod();
            })
            ->addColumn('action', function (User $obj) use ($authUser) {
                $str = "";

                if ($authUser->can('Edit Employee Info')) {
                    $str .= '<a target="_BLANK" href="' . route('employee.edit', ['employee' => $obj->uuid]) . '"><i class="fa fa-edit"style="color: green"></i></a>';
                }

                return $str;
            })
            ->rawColumns(['action', 'photo'])
            ->toJson();
    }

    private function leaveInYesterday($request)
    {
        $empIds = [];
        //checking the request from whether supervisor dashboard or admin dashboard
        if ($request->room == 'sp-room') {
            $empIds = FilterController::getEmployeeIdsSpDashboard();
        } else {
            $empIds = FilterController::getEmployeeIds();
        }
        $today = date("Y-m-d");
        $yesterday = date("Y-m-d", strtotime($today . "-1 day"));

        $count = LeaveRequest::whereIn("user_id", $empIds)
            ->whereDate("from_date", "<=", $yesterday)
            ->whereDate("to_date", ">=", $yesterday)
            ->whereStatus(LeaveRequest::STATUS_APPROVED)
            ->distinct('user_id')
            ->count('user_id');

        $url = route("dashboard-notification.get-employee-list-leave-yesterday", ['room' => $request->room]);

        return ["count" => $count, "url" => $url];
    }

    public function getEmployeeListLeaveYesterday($room)
    {
        return view('dashboard-notification.employee-list-leave-yesterday', ['card_title' => 'Leave In Yesterday', 'room' => $room]);

    }

    public function getEmployeeListDataLeaveYesterday(Request $request)
    {
        $authUser = auth()->user();
        $empIds = [];
        //checking the request from whether supervisor dashboard or admin dashboard
        if ($request->room == 'sp-room') {
            $empIds = FilterController::getEmployeeIdsSpDashboard();
        } else {
            $empIds = FilterController::getEmployeeIds();
        }
        $today = date("Y-m-d");
        $yesterday = date("Y-m-d", strtotime($today . "-1 day"));

        $fIds = LeaveRequest::whereIn("user_id", $empIds)
            ->whereDate("from_date", "<=", $yesterday)
            ->whereDate("to_date", ">=", $yesterday)
            ->whereStatus(LeaveRequest::STATUS_APPROVED)
            ->distinct('user_id')
            ->pluck('user_id');
        $data = User::whereIn('id', $fIds)->with(['currentPromotion', 'currentPromotion.officeDivision', 'currentPromotion.department', 'currentPromotion.designation']);

        return DataTables::eloquent($data)
            ->editColumn('photo', function ($item) {
                $path = "photo/" . $item->fingerprint_no . ".jpg";
                $imgSrc = file_exists($path) ? asset($path) : asset('assets/media/svg/avatars/001-boy.svg');
                return '<div class="symbol flex-shrink-0" style="width: 35px; height: auto"><img src=' . $imgSrc . '></div>';
            })
            ->rawColumns(['photo'])
            ->toJson();
    }

    public function leaveCalendar(Request $request)
    {
        $empIds = [];
        if(!empty($request->input('empID'))){
            $empIds = [$request->input('empID')];
        }else{
            $empIds = FilterController::getEmployeeIds();
        }
        $first_date = date('Y-m-d',strtotime($request->input('start')));
        $last_date = date('Y-m-d',strtotime($request->input('end')));
        $last_date = date('Y-m-d', strtotime($last_date .' -1 day'));

        $events = [];
        $monthWiseData = [];
        for ($day = $first_date; $day <= $last_date; $day++) {
            $total_events = LeaveRequest::whereIn('user_id', $empIds)
                ->whereDate('from_date', '<=', $day)
                ->whereDate('to_date', '>=', $day)
                ->count('id');
            for ($j = 0; $j < $total_events; $j++) {
                $events[] = ["start" => $day, "end" => !empty($day) ? date('Y-m-d', strtotime('+1 day', strtotime($day))) : "" , "classNames" => 'leave-block', "title" => ''];
            }
            $monthWiseData[$day] = $total_events;
        }
        if(!empty($request->input('type')) && $request->input('type')=='chart'){
            return $monthWiseData;
        }
        return $events;
    }

    public function getSpecificDateLeaveLists(Request $request)
    {
        $empIds = [];
        $empIds = FilterController::getEmployeeIds();
        $events = LeaveRequest::with(["employee.currentPromotion" => function ($query) {
            $query->with("officeDivision", "department");
        }])->whereIn("user_id", $empIds)
            ->whereDate("from_date", "<=", $request->input('start'))
            ->whereDate("to_date", ">=", $request->input('start'))
            ->get();
        if (count($events) <= 0) {
            return json_encode(['status' => 'failed']);
        }
        $eventDate = $request->input('start');
        return view("dashboard-notification.employee-leave-lists-specific-date-wise", compact("events", 'eventDate'));
    }

}
