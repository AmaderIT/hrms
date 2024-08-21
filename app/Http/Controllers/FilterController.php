<?php

namespace App\Http\Controllers;


use App\Models\Department;
use App\Models\DepartmentSupervisor;
use App\Models\DivisionSupervisor;
use App\Models\EmployeeStatus;
use App\Models\LeaveAllocation;
use App\Models\OfficeDivision;
use App\Models\User;
use App\Models\UserLeave;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;
use Exception;

class FilterController extends Controller
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
     * @return Application|Factory|View
     */

    /**
     * @return Application|Factory|View
     */

    public function getOfficeDivision(Request $request)
    {

        try {

            $officeDivision = OfficeDivision::select(['id', 'name']);

            if (!auth()->user()->can('Show All Office Division')) {
                $departmentalSupervisorDivisions = DivisionSupervisor::where('status', DivisionSupervisor::STATUS_ACTIVE)
                    ->where('supervised_by', auth()->user()->id)
                    ->pluck('office_division_id');

                $divisionalSupervisorDivisions = DivisionSupervisor::where('status', DepartmentSupervisor::STATUS_ACTIVE)
                    ->where('supervised_by', auth()->user()->id)
                    ->pluck('office_division_id');

                $officeDivision->whereIn('id', $departmentalSupervisorDivisions);
                $officeDivision->OrwhereIn('id', $divisionalSupervisorDivisions);
            }


            $officeDivision = $officeDivision->get();

            $response = response()->json([
                "status" => 200,
                "data" => $officeDivision
            ]);
        } catch (Exception $exception) {
            $response = response()->json([
                "status" => 404,
                "result" => $exception->getMessage()
            ]);
        }


        return $response;

    }


    public function getDepartment(Request $request)
    {

        try {

            $result = Department::select(['id', 'name'])->where('office_division_id', $request->office_division_id);

            if (!auth()->user()->can('Show All Department')) {

                $divisionalSupervisorDivisions = DivisionSupervisor::where('status', DivisionSupervisor::STATUS_ACTIVE)
                    ->where('supervised_by', auth()->user()->id)
                    ->pluck('office_division_id');

                $departmentalSupervisorDepartments = DepartmentSupervisor::where('status', DepartmentSupervisor::STATUS_ACTIVE)
                    ->where('supervised_by', auth()->user()->id)
                    ->pluck('department_id');

                //Case 1:-Only Divisional
                if (count($divisionalSupervisorDivisions) > 0 && count($departmentalSupervisorDepartments) < 1) {

                    $result->whereIn('office_division_id', $divisionalSupervisorDivisions);
                } //Case 2:-Divisional+Departmental

                else if (count($divisionalSupervisorDivisions) > 0 && count($departmentalSupervisorDepartments) > 0) {
                    $dptIds = Department::whereIn('office_division_id', $divisionalSupervisorDivisions)->pluck('id');
                    $result->whereIn('id', $dptIds)
                        ->orWhereIn('id', $departmentalSupervisorDepartments)
                        ->where('office_division_id', $request->office_division_id);
                } //Case 3:-Departmental
                else if (count($departmentalSupervisorDepartments) > 0) {

                    $result->whereIn('id', $departmentalSupervisorDepartments);

                }


            }


            $result = $result->get();

            $response = response()->json([
                "status" => 200,
                "data" => $result
            ]);
        } catch (Exception $exception) {
            $response = response()->json([
                "status" => 404,
                "result" => $exception->getMessage()
            ]);
        }


        return $response;

    }


    public function getEmployee(Request $request)
    {

        try {

            $today = date("y-m-d");
            $result = User::select(['users.id', 'name', 'fingerprint_no'])
                ->join('promotions', function ($join) use ($today) {
                    $join->on('promotions.user_id', 'users.id');
                    $join->on('promotions.id', DB::raw("( select max(p.id) from promotions p where p.user_id= users.id and p.promoted_date <= '" . $today . "' limit 1)"));
                })
                ->where('status', 1)
                ->where('promotions.department_id', $request->department_id)
                ->get();

            $response = response()->json([
                "status" => 200,
                "data" => $result
            ]);
        } catch (Exception $exception) {
            $response = response()->json([
                "status" => 404,
                "result" => $exception->getMessage()
            ]);
        }


        return $response;

    }


    public function getDivisionIds($allDivisions = false, $forSalary = false)
    {
        $divisionIds = [];

        if (($forSalary && $allDivisions) || (!$forSalary && auth()->user()->can('Show All Office Division'))) {
            $divisionIds = OfficeDivision::pluck('id')->toArray();
        } else {

            $divisionalSupervisorDivisions = DivisionSupervisor::where('status', DivisionSupervisor::STATUS_ACTIVE)
                ->where('supervised_by', auth()->user()->id)
                ->pluck('office_division_id')->toArray();

            $departmentalSupervisorDivisions = DepartmentSupervisor::where('status', DepartmentSupervisor::STATUS_ACTIVE)
                ->where('supervised_by', auth()->user()->id)
                ->pluck('office_division_id')->toArray();

            $divisionIds = array_unique(array_merge($divisionalSupervisorDivisions, $departmentalSupervisorDivisions));
        }
        return $divisionIds;
    }


    public function getDepartmentIds($officeDivision = 0, $allDepartments = false, $forSalary = false)
    {
        $departmentIds = [];

        if (($forSalary && $allDepartments) || ((!$forSalary) && auth()->user()->can('Show All Department'))) {
            if ($officeDivision > 0) {
                $departmentIds = Department::where('office_division_id', $officeDivision)->pluck('id')->toArray();
            } else {
                $departmentIds = Department::pluck('id')->toArray();
            }
        } else {
            if ($officeDivision > 0) {

                $departmentalSupervisorDepartments = DepartmentSupervisor::where('status', DepartmentSupervisor::STATUS_ACTIVE)
                    ->where('supervised_by', auth()->user()->id)
                    ->where('office_division_id', $officeDivision)
                    ->pluck('department_id')->toArray();

                $divisionalSupervisorDivisions = DivisionSupervisor::where('status', DivisionSupervisor::STATUS_ACTIVE)
                    ->where('supervised_by', auth()->user()->id)
                    ->where('office_division_id', $officeDivision)
                    ->pluck('office_division_id')->toArray();

                $divisionalSupervisorDepartments = Department::whereIn('office_division_id', $divisionalSupervisorDivisions)->pluck('id')->toArray();

                $departmentIds = array_unique(array_merge($divisionalSupervisorDepartments, $departmentalSupervisorDepartments));

            } else {
                $departmentalSupervisorDepartments = DepartmentSupervisor::where('status', DepartmentSupervisor::STATUS_ACTIVE)
                    ->where('supervised_by', auth()->user()->id)
                    ->pluck('department_id')->toArray();

                $divisionalSupervisorDivisions = DivisionSupervisor::where('status', DivisionSupervisor::STATUS_ACTIVE)
                    ->where('supervised_by', auth()->user()->id)
                    ->pluck('office_division_id')->toArray();

                $divisionalSupervisorDepartments = Department::whereIn('office_division_id', $divisionalSupervisorDivisions)->pluck('id')->toArray();

                $departmentIds = array_unique(array_merge($divisionalSupervisorDepartments, $departmentalSupervisorDepartments));
            }
        }


        return $departmentIds;
    }

    public function getEmployeeIds($employeeStatus = 1, $key = "", $key_value = 0)
    {
        $departmentIds = [];

        if (auth()->user()->can('Show All Employee')) {

            if ($key != "" && $key_value > 0) {
                if ($key == "department" && $key_value > 0) {
                    $departmentIds = [$key_value];
                    if(is_array($key_value)) {
                        $departmentIds = $key_value;
                    }
                } else {

                    $departmentIds = self::getDepartmentIds($key_value);
                }
            } else {
                return User::where('status', $employeeStatus)->pluck('users.id');
            }
        } else {


            if ($key == "department" && $key_value > 0) {
                $departmentIds = [$key_value];
            } else {

                $departmentIds = self::getDepartmentIds($key_value);
            }

        }

        $today = date("Y-m-d");
        return User::join('promotions', function ($join) use ($today) {
            $join->on('promotions.user_id', 'users.id');
            $join->on('promotions.id', DB::raw("(select max(id) from promotions p where p.user_id = users.id and p.promoted_date <= '" . $today . "'  limit 1)"));
        })->where('status', $employeeStatus)->whereIn('department_id', $departmentIds)->groupBy('users.id')->pluck('users.id');
    }


    public function getDivisionIdsSpDashboard()
    {
        $divisionIds = [];


        $divisionalSupervisorDivisions = DivisionSupervisor::where('status', DivisionSupervisor::STATUS_ACTIVE)
            ->where('supervised_by', auth()->user()->id)
            ->pluck('office_division_id')->toArray();

        $departmentalSupervisorDivisions = DepartmentSupervisor::where('status', DepartmentSupervisor::STATUS_ACTIVE)
            ->where('supervised_by', auth()->user()->id)
            ->pluck('office_division_id')->toArray();

        $divisionIds = array_unique(array_merge($divisionalSupervisorDivisions, $departmentalSupervisorDivisions));

        return $divisionIds;
    }


    public function getDepartmentIdsSpDashboard($officeDivision = 0)
    {
        $departmentIds = [];


        if ($officeDivision > 0) {

            $departmentalSupervisorDepartments = DepartmentSupervisor::where('status', DepartmentSupervisor::STATUS_ACTIVE)
                ->where('supervised_by', auth()->user()->id)
                ->where('office_division_id', $officeDivision)
                ->pluck('department_id')->toArray();

            $divisionalSupervisorDivisions = DivisionSupervisor::where('status', DivisionSupervisor::STATUS_ACTIVE)
                ->where('supervised_by', auth()->user()->id)
                ->where('office_division_id', $officeDivision)
                ->pluck('office_division_id')->toArray();

            $divisionalSupervisorDepartments = Department::whereIn('office_division_id', $divisionalSupervisorDivisions)->pluck('id')->toArray();

            $departmentIds = array_unique(array_merge($divisionalSupervisorDepartments, $departmentalSupervisorDepartments));

        } else {
            $departmentalSupervisorDepartments = DepartmentSupervisor::where('status', DepartmentSupervisor::STATUS_ACTIVE)
                ->where('supervised_by', auth()->user()->id)
                ->pluck('department_id')->toArray();

            $divisionalSupervisorDivisions = DivisionSupervisor::where('status', DivisionSupervisor::STATUS_ACTIVE)
                ->where('supervised_by', auth()->user()->id)
                ->pluck('office_division_id')->toArray();

            $divisionalSupervisorDepartments = Department::whereIn('office_division_id', $divisionalSupervisorDivisions)->pluck('id')->toArray();

            $departmentIds = array_unique(array_merge($divisionalSupervisorDepartments, $departmentalSupervisorDepartments));
        }


        return $departmentIds;
    }

    public function getEmployeeIdsSpDashboard($employeeStatus = 1, $key = "", $key_value = 0)
    {
        $departmentIds = [];

        if ($key == "department" && $key_value > 0) {
            $departmentIds = [$key_value];
        } else {

            $departmentIds = self::getDepartmentIdsSpDashboard($key_value);
        }


        $today = date("Y-m-d");
        return User::join('promotions', function ($join) use ($today) {
            $join->on('promotions.user_id', 'users.id');
            $join->on('promotions.id', DB::raw("(select max(id) from promotions p where p.user_id = users.id and p.promoted_date <= '" . $today . "'  limit 1)"));
        })->where('status', $employeeStatus)->whereIn('department_id', $departmentIds)->groupBy('users.id')->pluck('users.id');
    }

    public function getUsedTableByColumn($columName = "")
    {
        $usedTbls = [];
        $tables = DB::select('SHOW TABLES');
        foreach ($tables as $table) {
            $key = "Tables_in_" . DB::connection()->getDatabaseName();
            $tblName = $table->$key;
            if (Schema::hasColumn($tblName, $columName)) {
                $usedTbls[] = $tblName;
            }
        }
        return $usedTbls;
    }

    public function getUsedTableById($usedTables = [], $columName = "", $val = null)
    {
        $ut = [];
        foreach ($usedTables as $tbl) {
            if (DB::table($tbl)->where($columName, $val)->count('id') > 0) {
                $ut [] = $tbl;
            }
        }
        return $ut;
    }
}
