<?php

namespace App\Http\Controllers;

use App\Http\Requests\divisionSupervisor\RequestDivisionSupervisor;
use App\Models\DepartmentSupervisor;
use App\Models\DivisionSupervisor;
use App\Models\OfficeDivision;
use App\Models\User;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Spatie\Permission\Models\Role;
use Exception;
use Yajra\DataTables\DataTables;

class DivisionSupervisorController extends Controller
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
        return view("division-supervisor.index");
    }

    /**
     * @return Application|Factory|View
     */
    public function create()
    {
        $data = [
            "officeDivisions" => OfficeDivision::orderByDesc("id")->get(),
        ];
        return view("division-supervisor.create", compact("data"));
    }

    /**
     * @param RequestDivisionSupervisor $request
     * @return mixed
     */
    public function store(RequestDivisionSupervisor $request)
    {
        DB::beginTransaction();
        try {
            $request->validated();
            if (!empty($request->office_division_id && count($request->office_division_id) <= 0)) {
                throw new \Exception("Missing Divisions!!!");
            }
            foreach ($request->office_division_id as $getData) {
                $divisionSupervisor = DivisionSupervisor::where(['status' => '1', 'supervised_by' => $request->supervised_by, 'office_division_id' => $getData])->active()->first();
                if (!empty($divisionSupervisor)) {
                    continue;
                }
                User::where("id", $request->supervised_by)->update(["is_supervisor" => User::SUPERVISOR_OFFICE_DIVISION]);
                $dataArray = [
                    'office_division_id' => $getData,
                    'supervised_by' => $request->supervised_by
                ];
                DivisionSupervisor::create($dataArray);
                $employee = User::find($request->supervised_by);
                $role = Role::findByName(User::ROLE_DIVISION_SUPERVISOR)->id;
                $employee->assignRole($role);
            }
            DB::commit();
            session()->flash("message", "Division Supervisor Added Successfully");
            $redirect = redirect()->route("division-supervisor.index");
        } catch (Exception $exception) {
            DB::rollBack();
            session()->flash("type", "error");
            session()->flash("message", $exception->getMessage());
            $redirect = redirect()->back();
        }
        return $redirect;
    }

    /**
     * @param DivisionSupervisor $divisionSupervisor
     * @return mixed
     */
    public function delete(DivisionSupervisor $divisionSupervisor)
    {
        try {
            DB::transaction(function () use ($divisionSupervisor) {
                $totalAssignDept = DepartmentSupervisor::where(['status' => '1', 'supervised_by' => $divisionSupervisor->supervised_by])->get();
                $totalAssignDivision = DivisionSupervisor::where(['status' => '1', 'supervised_by' => $divisionSupervisor->supervised_by])->get();
                if ($totalAssignDivision->count() > 1) {
                    $divisionSupervisor->update(["status" => DivisionSupervisor::STATUS_DISABLE]);
                } else {
                    $divisionSupervisor->update(["status" => DivisionSupervisor::STATUS_DISABLE]);
                    if ($totalAssignDivision->count() == 1) {
                        $supervisor = DivisionSupervisor::active()->whereSupervisedBy($divisionSupervisor->supervised_by)->first();
                        if(empty($totalAssignDept->count()) && $totalAssignDept->count()<=0){
                            if (!isset($supervisor)) User::whereId($divisionSupervisor->supervised_by)->update(["is_supervisor" => 0]);
                        }
                        $employee = User::where("id", $divisionSupervisor->supervised_by)->first();
                        $employee->removeRole(Role::findByName(User::ROLE_DIVISION_SUPERVISOR)->id);
                        $employee->assignRole(Role::findByName(User::ROLE_GENERAL_USER)->id);
                    }
                }
            });

            $feedback["status"] = true;
        } catch (Exception $exception) {
            $feedback['message'] = $exception->getMessage();
            $feedback["status"] = false;
        }

        return $feedback;
    }

    public function divisionSupervisorHistory(Request $request)
    {
        try {
            if (empty($request->input('employee_id'))) {
                throw new \Exception("Employee not found!!!");
            }
            $supervisorHistoryDatas = DivisionSupervisor::with(["supervisedBy", "officeDivision"])->where(['supervised_by' => $request->input('employee_id'), 'status' => '1'])
                ->orderBy('id', 'ASC')
                ->get();
            return view('division-supervisor.division_supervisor_history', compact('supervisorHistoryDatas'));
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
        $data = DivisionSupervisor::with("supervisedBy", "officeDivision")->active()->groupBy('supervised_by');
        return DataTables::eloquent($data)
            ->addColumn('all_divisions', function (DivisionSupervisor $obj) use ($authUser) {
                $str = "";
                $data = DB::table('division_supervisors')
                    ->join('office_divisions', 'office_divisions.id', 'office_division_id')
                    ->where(['supervised_by' => $obj->supervised_by, 'status' => '1'])
                    ->select(['office_divisions.id', 'office_divisions.name'])
                    ->groupBy('division_supervisors.office_division_id')
                    ->get();
                foreach ($data as $d) {
                    $str .= '<a href="#" onclick = "showListsOfficeDivisionWise(' . $d->id . ')" data-toggle="modal" data-target="#listingModal-' . $d->id . '"><span  class="badge badge-secondary role-btn">' . $d->name . '</span></a> ';
                }
                return $str;
            })
            ->addColumn('action', function (DivisionSupervisor $obj) use ($authUser) {
                $str = "";
                if ($authUser->can('Edit Permission')) {
                    $str .= '<a href="' . route('division-supervisor.edit', ['divisionSupervisor' => $obj->id]) . '"><i class="fa fa-edit"style="color: green"></i></a>';
                }
                return $str;
            })
            ->rawColumns(['action', 'all_divisions'])
            ->toJson();
    }

    public function listsOfficeDivisionWise(Request $request)
    {
        try {
            if (empty($request->office_division_id)) {
                throw new \Exception("Missing Office Division ID!!!");
            }
            $items = DivisionSupervisor::with("supervisedBy", "officeDivision")->active()->where(['office_division_id' => $request->office_division_id])->get();
            return view('division-supervisor.lists-office-division-wise', compact('items'));
        } catch (\Exception $ex) {
            session()->flash("type", "error");
            session()->flash("message", $ex->getMessage());
            return redirect()->back();
        }
    }

    /**
     * @param DivisionSupervisor $divisionSupervisor
     * @return Application|Factory|View
     */
    public function edit(DivisionSupervisor $divisionSupervisor)
    {
        $items = DivisionSupervisor::where(['status' => '1', 'supervised_by' => $divisionSupervisor->supervised_by])->get();
        $officeDivisionIDs = array_unique(array_column($items->toArray(), 'office_division_id'));
        $data = array(
            "officeDivisions" => OfficeDivision::orderByDesc("id")->get()
        );
        $divisionSupervisorID = $divisionSupervisor->id;
        $getInfos = DB::table('division_supervisors')
            ->join('users', 'users.id', 'division_supervisors.supervised_by')
            ->join('office_divisions', 'office_divisions.id', 'division_supervisors.office_division_id')
            ->where(['division_supervisors.supervised_by' => $divisionSupervisor->supervised_by, 'division_supervisors.status' => '1'])
            ->select(['office_divisions.id', 'office_divisions.name', 'users.name', 'users.fingerprint_no'])
            ->groupBy('division_supervisors.supervised_by')
            ->first();

        $divisionSupervisorHistoryDatas = DivisionSupervisor::with(["supervisedBy", "officeDivision"])->where(['supervised_by' => $divisionSupervisor->supervised_by, 'status' => '1'])
            ->orderBy('id', 'ASC')
            ->get();

        return view("division-supervisor.edit", compact("items", "data", 'divisionSupervisorID', 'officeDivisionIDs', 'getInfos', 'divisionSupervisorHistoryDatas', 'divisionSupervisor'));
    }


}
