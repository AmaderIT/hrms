<?php

namespace App\Http\Controllers;

use App\Http\Requests\department\RequestDepartment;
use App\Library\Filter;
use App\Models\Department;
use App\Models\DepartmentSupervisor;
use App\Models\LeaveAllocation;
use App\Models\LeaveAllocationDetails;
use App\Models\LeaveType;
use App\Models\OfficeDivision;
use App\Models\User;
use App\Models\Warehouse;
use App\Models\WeeklyHoliday;
use App\Models\LateDeduction;
use App\Models\RelaxDaySetting;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Http\Client\Response;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\View\View;
use Exception;
use Illuminate\Support\Facades\Log;
use Spatie\Permission\Models\Role;

class DepartmentController extends Controller
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
     * @param Request $request
     * @return Application|Factory|View
     */
    public function index(Request $request)
    {
        $filter = new Filter(Department::class, ["name"], $request->input("search"));
        $items = $filter->with("officeDivision")->select("id", "name", "office_division_id")->orderBy('name')->paginate(\Functions::getPaginate());
        return view('department.index', compact('items'));
    }

    /**
     * @return Factory|View
     */
    public function create()
    {
        $items = User::select("id", "name", "email", "fingerprint_no")->where(['status' => User::STATUS_ACTIVE])->orderBy("name")->get();
        $officeDivisions = OfficeDivision::select("id", "name")->get();
        $warehouses = Warehouse::select("id", "name")->get();
        $leaveTypes = LeaveType::select("id", "name")->orderByDesc("id")->get();
        return view("department.create", compact("officeDivisions", "warehouses", "items", "leaveTypes"));
    }

    /**
     * @param Department $department
     * @return Application|Factory|View
     */
    public function edit(Department $department)
    {
        $officeDivisions = OfficeDivision::select("id", "name")->get();
        $warehouses = Warehouse::select("id", "name")->get();
        $leaveTypes = LeaveType::select("id", "name")->orderByDesc("id")->get();
        if($department->relaxDaySetting) $department->relaxDaySetting->weekly_days = json_decode($department->relaxDaySetting->weekly_days) ?? [];
        return view("department.edit", compact("department", "officeDivisions", "warehouses", "leaveTypes"));
    }

    /**
     * @param RequestDepartment $request
     * @return RedirectResponse|string
     */
    public function store(RequestDepartment $request)
    {

        try {

            $checking_duplicate_name = Department::where('office_division_id', $request->office_division_id)
                ->where('name', $request->name)
                ->count();
            if ($checking_duplicate_name > 0) {
                return redirect()->back()->withInput()->withErrors(['name' => 'The name has already been taken under this division.']);
            }
            DB::transaction(function () use ($request) {

                # Save Department
                $department = Department::create($request->validated());

                // # Enable employee as a supervisor
                // User::where("id", $request->input("supervised_by"))->update(["is_supervisor" => 1]);

                // # Assign supervisor to the given department
                // DepartmentSupervisor::create([
                //     "office_division_id" => $request->input('office_division_id'),
                //     "department_id" => $department->id,
                //     "supervised_by" => $request->input('supervised_by'),
                // ]);

                // # Update Role to Supervisor
                // $employee = User::find($request->input("supervised_by"));
                // $employee->roles()->sync(Role::findByName(User::ROLE_SUPERVISOR)->id);

                // weekly holiday
                $weeklyHoliday =[
                    "department_id" => $department->id,
                    "days" => json_encode($request->input("days")),
                    "effective_date" => date('Y-m-d'),
                    "created_at" => now()
                ];
                WeeklyHoliday::insert($weeklyHoliday);

                # Leave Allocation
                $leaveAllocation = [];
                $leaveAllocation = LeaveAllocation::create([
                    "office_division_id" => $department->office_division_id,
                    "department_id" => $department->id,
                    "year" => $request->input("year"),
                ]);

                $data = array();
                foreach ($request->input("leave_days") as $key => $day) {
                    array_push($data, array(
                        "leave_allocation_id" => $leaveAllocation->id,
                        "leave_type_id" => $request->input("leave_type_id")[$key],
                        "total_days" => $request->input("leave_days")[$key],
                        "created_at" => now(),
                        "updated_at" => now(),
                    ));
                }

                # Add LateDeduction
                LateDeduction::create([
                    "department_id" => $department->id,
                    "total_days" => $request->input('total_days'),
                    "deduction_day" => $request->input('deduction_day'),
                    "type" => $request->input('type'),
                ]);

                # Leave Allocation Details
                $leaveAllocation->leaveAllocationDetails()->createMany($data);

                activity('department-create')->by(auth()->user())->log('Department has been changed');
                session()->flash("message", "Department Changed Successfully");

                # Sync Department to Attendance Server
                // $this->syncWithZKTeco($department);

                // Relax Day settings
                if($request->is_relax_day_setting == 1 && $request->relax_day_type) {
                    RelaxDaySetting::create([
                        "type" => $request->relax_day_type,
                        "department_id" => $department->id,
                        "max_count_per_month" => $request->max_count_per_month,
                        "weekly_days" => json_encode($request->weekly_days),
                        'created_by' => auth()->user()->id,
                    ]);
                }
            });

            session()->flash('message', 'Department Created Successfully');
            $redirect = redirect()->route("department.index");
        } catch (Exception $exception) {
            $redirect = redirect()->back()->withErrors($exception->getMessage());
        }

        return $redirect;
    }

    /**
     * @param RequestDepartment $request
     * @param Department $department
     * @return RedirectResponse
     */
    public function update(RequestDepartment $request, Department $department)
    {
        DB::beginTransaction();
        try {
            $checking_duplicate_name = Department::where('office_division_id', $request->office_division_id)
                ->where('name', $request->name)
                ->where('id','<>',$department->id)
                ->count();

            if ($checking_duplicate_name > 0) {

                return redirect()->back()->withInput()->withErrors(['name' => 'The name has already been taken under this division.']);
            }

            $update['office_division_id'] = $request->office_division_id;
            $update['name'] = $request->name;
            $update['is_warehouse'] = $request->is_warehouse ?? 0;
            $update['is_relax_day_setting'] = $request->is_relax_day_setting;
            if (isset($request->is_warehouse) && $request->is_warehouse == 1) {
                $update['warehouse_id'] = $request->warehouse_id;
            } else {
                $update['warehouse_id'] = null;
            }
            $department->update($update);

            // weekly holiday
            $department->weeklyHoliday->update(["end_date" => date('Y-m-d', strtotime(now() .' -1 day'))]);
            WeeklyHoliday::create([
                "department_id" => $department->id,
                "days" => json_encode($request->days),
                "effective_date" => date('Y-m-d'),
            ]);

            // leave allocation
            $insert_leave_allo_data = [];
            foreach ($request->leave_days as $item) {
                if(isset($item['id'])) {
                    LeaveAllocationDetails::where('id', '=', $item['id'])->update(['total_days' => $item['value']]);
                } else {
                    $insert_leave_allo_data[] = [
                        "leave_allocation_id" => $department->leaveAllocation->id,
                        "leave_type_id" => $item['leave_type_id'],
                        "total_days" => $item['value'],
                        "created_at" => now(),
                    ];
                }
            }
            if(!empty($insert_leave_allo_data)) LeaveAllocationDetails::insert($insert_leave_allo_data);

            // Late Deduction
            $department->lateDeduction->update([
                "total_days" => $request->total_days,
                "deduction_day" => $request->deduction_day,
                "type" => $request->type,
            ]);

            # relax_day settings
            if(isset($request->is_relax_day_setting) && $request->is_relax_day_setting == 1){
                if($department->relaxDaySetting) {
                    $department->relaxDaySetting->update([
                        "type" => $request->relax_day_type,
                        "max_count_per_month" => $request->max_count_per_month,
                        "weekly_days" => json_encode($request->weekly_days),
                        "updated_by" => auth()->user()->id,
                    ]);

                    // if any changes in relaxday settings then future date data will remove
                    if(array_key_exists('type', $department->relaxDaySetting->getChanges())
                        || array_key_exists('max_count_per_month', $department->relaxDaySetting->getChanges())
                        || array_key_exists('weekly_days', $department->relaxDaySetting->getChanges()) ) {
                        AssignRelaxDayController::removeDepartmentRelaxDay($department->relaxDaySetting->department_id);
                    }

                } else {
                    RelaxDaySetting::create([
                        "type" => $request->relax_day_type,
                        "department_id" => $department->id,
                        "max_count_per_month" => $request->max_count_per_month,
                        "weekly_days" => json_encode($request->weekly_days),
                        'created_by' => auth()->user()->id,
                    ]);
                }
            } else if($department->relaxDaySetting) {
                $department->relaxDaySetting->delete();
            }

            DB::commit();
            session()->flash('message', 'Department Updated Successfully');
            $redirect = redirect()->route("department.index");
        } catch (Exception $ex) {
            DB::rollBack();
            Log::info($ex->getMessage());
            $redirect = redirect()->back()->withErrors('Something went wrong');
        }

        return $redirect;
    }

    /**
     * @param Department $department
     * @return mixed
     * @throws Exception
     */
    public function delete(Department $department)
    {
        try {
            $feedback['status'] = $department->delete();
            // relax day settings and assign Relax day remove
            if($department->relaxDaySetting){
                $department->relaxDaySetting->delete();
                AssignRelaxDayController::removeDepartmentRelaxDay($department->relaxDaySetting->department_id);
            }
        } catch (Exception $exception) {
            $feedback['status'] = false;
        }

        return $feedback;
    }

    /**
     * @param $payload
     */
    protected function syncWithZKTeco($payload)
    {
        # Get JWT Token
        $jwtToken = $this->getToken();

        # Sync Department with Attendance Server
        $payload = array(
            "dept_code" => $payload->id,
            "dept_name" => $payload->name,
            "parent_dept" => null
        );
        $this->syncDepartmentWithAttendanceServer($payload, $jwtToken);
    }

    /**
     * Sync Department with Attendance Server
     *
     * @param $payload
     * @param $jwtToken
     * @return Response|mixed
     */
    protected function syncDepartmentWithAttendanceServer($payload, $jwtToken)
    {
        $http_response_header = array(
            "Content-Type" => "application/json",
            "Authorization" => "JWT " . $jwtToken
        );
        $url = env("ZKTECO_SERVER_PORT") . "/personnel/api/departments/";

        $response = Http::withHeaders($http_response_header)->post($url, $payload);
        $response = $response->json();

        return $response;
    }

    /**
     * @return Response|mixed|null
     */
    protected function getToken()
    {
        $http_response_header = array(
            "Content-Type" => "application/json"
        );

        $url = env("ZKTECO_SERVER_PORT") . "/jwt-api-token-auth/";
        $payLoad = array(
            "username" => env("ZKTECO_BIOTIME_USERNAME"),
            "password" => env("ZKTECO_BIOTIME_PASSWORD")
        );

        $response = Http::withHeaders($http_response_header)->post($url, $payLoad);
        $jwtToken = $response->json()["token"];

        return $jwtToken;
    }


    public function storeAjx(RequestDepartment $request)
    {
        $checking_duplicate_name = Department::where('office_division_id', $request->office_division_id)
            ->where('name', $request->name)
            ->count();
        if ($checking_duplicate_name > 0) {
            return \response()->json([
                'status' => 'already_taken',
                'message' => 'The name has already been taken under this division'
            ]);
        }
        $this->store($request);
         return \response()->json([
             'status' => 'success',
             'message' => 'Department Created Successfully'
         ]);
    }
}
