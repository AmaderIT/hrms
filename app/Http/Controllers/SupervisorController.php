<?php

namespace App\Http\Controllers;

use App\Http\Requests\supervisor\RequestSupervisor;
use App\Models\Department;
use App\Models\DepartmentSupervisor;
use App\Models\DivisionSupervisor;
use App\Models\OfficeDivision;
use App\Models\User;
use Exception;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Spatie\Permission\Models\Role;
use Yajra\DataTables\DataTables;
use function PHPUnit\Framework\returnArgument;

class SupervisorController extends Controller
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
    public function index()
    {
        return view("supervisor.index");
    }

    /**
     * @return Application|Factory|View
     */
    public function create()
    {
        $items = User::select("id", "name", "email", "fingerprint_no")->orderBy("name")->get();
        $data = array(
            "officeDivisions" => OfficeDivision::orderByDesc("id")->get(),
            "departments" => Department::orderByDesc("id")->get()
        );

        return view("supervisor.create", compact("items", "data"));
    }

    /**
     * @param RequestSupervisor $request
     * @return RedirectResponse
     */
    public function store(RequestSupervisor $request)
    {
        try {
            $request->validated();
            DB::transaction(function () use ($request) {
                if (!empty($request->department_id && count($request->department_id) <= 0)) {
                    throw new \Exception("Missing Department!!!");
                }
                foreach ($request->department_id as $getData) {
                    $departmentSupervisor = DepartmentSupervisor::where(['status' => '1', 'supervised_by' => $request->supervised_by, 'department_id' => $getData])->active()->first();
                    # Enable employee as a supervisor
                    if (!empty($departmentSupervisor)) {
                        continue;
                    }

                    $getOfficeDivisionID = Department::where(['id' => $getData])->select(['id', 'office_division_id', 'name'])->first();
                    if (empty($getOfficeDivisionID)) {
                        throw new \Exception("Missing Office Division!!!");
                    }
                    User::where("id", $request->supervised_by)->update(["is_supervisor" => 1]);
                    $dataArray = [
                        'office_division_id' => $getOfficeDivisionID->office_division_id,
                        'department_id' => $getData,
                        'supervised_by' => $request->supervised_by
                    ];
                    DepartmentSupervisor::create($dataArray);
                    $employee = User::find($request->supervised_by);
                    $role = Role::findByName(User::ROLE_SUPERVISOR)->id;
                    $employee->assignRole($role);
                    activity('supervisor-create')->by(auth()->user())->log('Supervisor has been changed');
                }
            });

            session()->flash("message", "Supervisor Changed Successfully");
            $redirect = redirect()->route("supervisor.index");
        } catch (Exception $exception) {
            session()->flash("type", "error");
            session()->flash("message", $exception->getMessage());
            $redirect = redirect()->back();
        }

        return $redirect;
    }

    /**
     * @param DepartmentSupervisor $departmentSupervisor
     * @return mixed
     */
    public function delete(DepartmentSupervisor $departmentSupervisor)
    {
        try {
            DB::transaction(function () use ($departmentSupervisor) {
                $totalAssignDept = DepartmentSupervisor::where(['status' => '1', 'supervised_by' => $departmentSupervisor->supervised_by])->get();
                $totalAssignDivision = DivisionSupervisor::where(['status' => '1', 'supervised_by' => $departmentSupervisor->supervised_by])->get();
                if ($totalAssignDept->count() > 1) {
                    $departmentSupervisor->update(["status" => DepartmentSupervisor::STATUS_DISABLE]);
                } else {
                    $departmentSupervisor->update(["status" => DepartmentSupervisor::STATUS_DISABLE]);
                    if ($totalAssignDept->count() == 1) {
                        $supervisor = DepartmentSupervisor::active()->whereSupervisedBy($departmentSupervisor->supervised_by)->first();
                        if (empty($totalAssignDivision->count()) && $totalAssignDivision->count() <= 0) {
                            if (!isset($supervisor)) User::whereId($departmentSupervisor->supervised_by)->update(["is_supervisor" => 0]);
                        }
                        $employee = User::where("id", $departmentSupervisor->supervised_by)->first();
                        $employee->removeRole(Role::findByName(User::ROLE_SUPERVISOR)->id);
                        $employee->assignRole(Role::findByName(User::ROLE_GENERAL_USER)->id);
                    }
                }
                activity('supervisor-delete')->by(auth()->user())->log('Supervisor Deleted');
            });

            $feedback["status"] = true;
        } catch (Exception $exception) {
            $feedback['message'] = $exception->getMessage();
            $feedback["status"] = false;
        }

        return $feedback;
    }

    /**
     * @param OfficeDivision $officeDivision
     * @return JsonResponse
     */
    public function getDepartmentByOfficeDivision(OfficeDivision $officeDivision)
    {
        return response()->json(["data" => $officeDivision->load("departments.supervisor.supervisedBy")]);
    }

    public function getEmployees(Request $request)
    {
        $search = $request->search;
        getEmployeeLists($search);
    }

    public function supervisorHistory(Request $request)
    {
        try {
            if (empty($request->input('employee_id'))) {
                throw new \Exception("Employee not found!!!");
            }
            $supervisorHistoryDatas = DepartmentSupervisor::with(["supervisedBy", "officeDivision", "department"])->where(['supervised_by' => $request->input('employee_id'), 'status' => '1'])
                ->orderBy('id', 'ASC')
                ->get();
            return view('supervisor.supervisor_history', compact('supervisorHistoryDatas'));
        } catch (Exception $exception) {
            $response = response()->json([
                "status" => 404,
                "result" => $exception->getMessage()
            ]);
        }
        return $response;
    }

    public function getDatatable(Request $request)
    {
        $authUser = auth()->user();
        $data = DepartmentSupervisor::with("supervisedBy", "officeDivision", "department")->groupBy('supervised_by')->active();
        return DataTables::eloquent($data)
            ->addColumn('all_departments', function (DepartmentSupervisor $obj) use ($authUser) {
                $str = "";
                $data = DB::table('department_supervisor')
                    ->join('departments', 'departments.id', 'department_id')
                    ->where(['supervised_by' => $obj->supervised_by, 'status' => '1'])
                    ->select(['departments.id', 'departments.name'])
                    ->get();
                foreach ($data as $d) {
                    $str .= '<a href="#" onclick = "showListsDepartmentWise(' . $d->id . ')" data-toggle="modal" data-target="#listingModal-' . $d->id . '"><span  class="badge badge-secondary role-btn">' . $d->name . '</span></a> ';
                }
                return $str;
            })
            ->addColumn('all_divisions', function (DepartmentSupervisor $obj) use ($authUser) {
                $str = "";
                $data = DB::table('department_supervisor')
                    ->join('office_divisions', 'office_divisions.id', 'office_division_id')
                    ->where(['supervised_by' => $obj->supervised_by, 'status' => '1'])
                    ->select(['office_divisions.id', 'office_divisions.name'])
                    ->groupBy('department_supervisor.office_division_id')
                    ->get();
                foreach ($data as $d) {
                    $str .= '<a href="#" onclick = "showListsOfficeDivisionWise(' . $d->id . ')" data-toggle="modal" data-target="#listingModal-' . $d->id . '"><span  class="badge badge-secondary role-btn">' . $d->name . '</span></a> ';
                }
                return $str;
            })
            ->addColumn('action', function (DepartmentSupervisor $obj) use ($authUser) {
                $str = "";

                if ($authUser->can('Edit Permission')) {
                    $str .= '<a href="' . route('supervisor.edit', ['departmentSupervisor' => $obj->id]) . '"><i class="fa fa-edit"style="color: green"></i></a>';
                }


                return $str;
            })
            ->rawColumns(['action', 'all_departments', 'all_divisions'])
            ->toJson();
    }

    /**
     * @param DepartmentSupervisor $departmentSupervisor
     * @return Application|Factory|View
     */
    public function edit(DepartmentSupervisor $departmentSupervisor)
    {
        $items = DepartmentSupervisor::where(['status' => '1', 'supervised_by' => $departmentSupervisor->supervised_by])->get();
        $departmentIDs = array_unique(array_column($items->toArray(), 'department_id'));
        $officeDivisionIDs = array_unique(array_column($items->toArray(), 'office_division_id'));
        $data = array(
            "officeDivisions" => OfficeDivision::orderByDesc("id")->get(),
            "departments" => Department::orderByDesc("id")->get()
        );

        $departmentSupervisorID = $departmentSupervisor->id;
        $getInfos = DB::table('department_supervisor')
            ->join('users', 'users.id', 'department_supervisor.supervised_by')
            ->join('departments', 'departments.id', 'department_id')
            ->where(['department_supervisor.supervised_by' => $departmentSupervisor->supervised_by, 'department_supervisor.status' => '1'])
            ->select(['departments.id', 'departments.name', 'users.name', 'users.fingerprint_no'])
            ->groupBy('department_supervisor.supervised_by')
            ->first();

        $supervisorHistoryDatas = DepartmentSupervisor::with(["supervisedBy", "officeDivision", "department"])->where(['supervised_by' => $departmentSupervisor->supervised_by, 'status' => '1'])
            ->orderBy('id', 'ASC')
            ->get();


        return view("supervisor.edit", compact("items", "data", 'departmentSupervisorID', 'departmentIDs', 'officeDivisionIDs', 'getInfos', 'supervisorHistoryDatas', 'departmentSupervisor'));
    }

    public function listsDepartmentWise(Request $request)
    {
        try {
            if (empty($request->department_id)) {
                throw new \Exception("Missing Department ID!!!");
            }
            $items = DepartmentSupervisor::with("supervisedBy", "officeDivision", "department")->active()->where(['department_id' => $request->department_id])->get();
            return view('supervisor.lists-department-wise', compact('items'));
        } catch (\Exception $ex) {
            session()->flash("type", "error");
            session()->flash("message", $ex->getMessage());
            return redirect()->back();
        }

    }

    public function listsOfficeDivisionWise(Request $request)
    {
        try {
            if (empty($request->office_division_id)) {
                throw new \Exception("Missing Office Division ID!!!");
            }
            $items = DepartmentSupervisor::with("supervisedBy", "officeDivision", "department")->active()->where(['office_division_id' => $request->office_division_id])->get();
            return view('supervisor.lists-office-division-wise', compact('items'));
        } catch (\Exception $ex) {
            session()->flash("type", "error");
            session()->flash("message", $ex->getMessage());
            return redirect()->back();
        }
    }

}
