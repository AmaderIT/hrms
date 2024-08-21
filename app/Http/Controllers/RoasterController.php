<?php

namespace App\Http\Controllers;

use Auth;
use DateTime;
use App\Http\Requests\roaster\RequestRoaster;
use App\Http\Requests\roaster\RequestDepartmentRoaster;
use App\Models\Department;
use App\Models\DepartmentRoaster;
use App\Models\OfficeDivision;
use App\Models\DepartmentSupervisor;
use App\Models\DivisionSupervisor;
use App\Models\Roaster;
use App\Models\User;
use App\Models\WorkSlot;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Exception;
use Illuminate\Support\Facades\Input;

class RoasterController extends Controller
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
    public function index(Request $request)
    {
        $data = array(
            "officeDivisions" => OfficeDivision::whereIn('id',FilterController::getDivisionIds())->get(),
        );

        if (request()->ajax()) {

            $items = Roaster::with(["user","officeDivision", "department", "workSlot"])->select(['roasters.*']);

            $items->orderBy("roasters.id", "desc");
            $items->groupBy("roasters.department_roaster_id");

            $items = $items->whereIn('roasters.department_id', FilterController::getDepartmentIds());


            if ($request->division_id && $request->division_id > 0) {

                $items->where('roasters.office_division_id',  $request->division_id);
            }
            if ($request->department_id && $request->department_id > 0) {
                $items->where('roasters.department_id',  $request->department_id);
            }

            return datatables($items)

                ->addColumn('weekly_holidays', function ($item) {
                    $holidays = implode(",",$item->weekly_holidays);
                    if ($holidays) {
                        $html = $holidays;
                    } else {
                        $html = 'N/A';
                    }

                    return $html;
                })

                ->addColumn('roaster_unlock_btn', function ($item) use ($request) {
                    if (auth()->user()->can("Roaster Unlock Button")) {

                    $checkdVal = '';
                    if ($item->is_locked == 1) {
                        $checkdVal = '';
                        $sValue = 1;
                        $sBtn = '<i class="fas fa-lock icon_roaster_unlock_status'.$item->id.'" style="float: left; margin-top: 8px;"></i>';
                    } else {
                        $checkdVal = 'checked';
                        $sValue = 0;
                        $sBtn = '<i class="fas fa-lock-open icon_roaster_unlock_status'.$item->id.'" style="float: left; margin-top: 8px;"></i>';
                    }

                        return '<div class="lock_status_div'.$item->id.'">' .$sBtn.'<span class="switch switch-outline switch-icon switch-primary float-right">

                                <label>
                                    <input type="checkbox"'. $checkdVal .' class="roaster_unlock_status roaster_unlock_status'.$item->id.'" name="status" id="' . $item->id . '"  data="' . $item->id . '" value="'.$sValue.'"/>
                                    <span></span>
                                </label>
                            </span></div>';

                    }
                })

                ->editColumn('user.name', function ($item) {
                    if ($item->department_roaster_id>0) {
                        return "All";
                    } else {
                        return $item->user->name;
                    }
                })

                ->editColumn('work_slot.start_time', function ($item) {
                    return date("g:i a", strtotime($item->workSlot->start_time));
                })

                ->editColumn('work_slot.end_time', function ($item) {
                    return date("g:i a", strtotime($item->workSlot->end_time));
                })

                ->editColumn('work_slot.late_count_time', function ($item) {
                    return date("g:i a", strtotime($item->workSlot->late_count_time));
                })

                ->editColumn('active_from', function ($item) {
                    return date("M  d, "."Y", strtotime($item->active_from));
                })

                ->editColumn('end_date', function ($item) {
                    return date("M  d, "."Y", strtotime($item->end_date));
                })

                ->addColumn('approval_status', function ($item) use ($request) {
                    if (auth()->user()->can("Roaster Approval Permission")) {
                        $bgstyle = '';
                        $cstyle = '';
                        if ($item->approval_status==0) {
                            $bgstyle = "background: rgb(255, 204, 0);";
                            $cstyle = "color:white;";
                        } else if($item->approval_status==1){
                            $bgstyle = "background: #4ba774;";
                            $cstyle = "color:white;";
                        } else {
                            $bgstyle = "background: #f64e60;";
                            $cstyle = "color:white;";
                        }

                        $html = '';
                        $html .= '<div class="tri-state-toggle">
                            <button title="Rejected" status="2" previousStatus="'.$item->approval_status.'" class="tri-state-toggle-button toggle-button2'.$item->id. ' toggle-button'.$item->id.' ' . ($item->approval_status==2 ? ' active' : '') . '" id="toggle-button2'.$item->id.'" data="'.$item->id.'"  ' . ($item->approval_status==2 ? 'style="'.$bgstyle.' color:white;"' : "" ).' >
                            X
                            </button>
                            <button title="Pending" status="0" previousStatus="'.$item->approval_status.'" class="tri-state-toggle-button toggle-button0'.$item->id. ' toggle-button'.$item->id.' ' . ($item->approval_status==0 ? ' active' : '') . '" id="toggle-button0'.$item->id.'" data="'.$item->id.'"  ' . ($item->approval_status==0 ? 'style="'.$bgstyle.'"' : "" ).'>
                                <i class="fas fa-dot-circle" aria-hidden="true" ' . ($item->approval_status==0 ? 'style="'.$cstyle.'"' : "" ).'></i>
                            </button>
                            <button title="Approved" status="1" previousStatus="'.$item->approval_status.'" class="tri-state-toggle-button toggle-button1'.$item->id. ' toggle-button'.$item->id.' ' . ($item->approval_status==1 ? ' active' : '') . '" id="toggle-button1'.$item->id.'" data="'.$item->id.'"  ' . ($item->approval_status==1 ? 'style="'.$bgstyle.'"' : "" ).'>
                                <i class="fa fa-check" aria-hidden="true" ' . ($item->approval_status==1 ? 'style="'.$cstyle.'"' : "" ).'></i>
                            </button>
                        </div>';

                        return $html;
                    } else {
                        if ($item->approval_status==0) {
                            return "Pending";
                        } elseif ($item->approval_status==1) {
                            return "Approved";
                        } else {
                            return "Rejected";
                        }
                    }
                })

                ->addColumn('action', function ($item) use ($request) {
                    $html = '';

                    if (auth()->user()->can("Roaster Unlock Button")) {
                        if (auth()->user()->can('Edit Roasters')) {
                            $html .= '<a data-rowid="'.$item->id.'" href="' . route('roaster.edit', ['roaster' => $item->id]) . '"><i class="fa fa-edit" style="color: green"></i></a>';
                        }

                        if (auth()->user()->can('Edit Roasters') && auth()->user()->can('Delete Roasters')) {
                            $html .= '||';
                        }

                        if (auth()->user()->can('Delete Roasters')) {
                            $html .= '<a href="#" class="delete_link" data-id="' . $item->id . '" data-href="' . route('roaster.delete', ['roaster' => $item->id]) . '">
                                            <i class="fa fa-trash" style="color: red"></i>
                                        </a>';
                        }
                    } else {
                        if ($item->is_locked==0) {
                            if (auth()->user()->can('Edit Roasters')) {
                                $html .= '<a data-rowid="'.$item->id.'"  href="' . route('roaster.edit', ['roaster' => $item->id]) . '"><i class="fa fa-edit" style="color: green"></i></a>';
                            }

                            if (auth()->user()->can('Edit Roasters') && auth()->user()->can('Delete Roasters')) {
                                $html .= '||';
                            }

                            if (auth()->user()->can('Delete Roasters')) {
                                $html .= '<a href="#" class="delete_link" data-id="' . $item->id . '" data-href="' . route('roaster.delete', ['roaster' => $item->id]) . '">
                                                <i class="fa fa-trash" style="color: red"></i>
                                            </a>';
                            }

                        } else {
                            $html .= '<i class="fa fa-lock fa-sm" aria-hidden="true" style="padding:0 18px;"></i><span style="color:red;">Locked </span>';
                        }
                    }

                    return $html;
                })
                ->rawColumns(['roaster_unlock_btn', 'work_slot.start_time', 'work_slot.end_time', 'work_slot.late_count_time', 'approval_status', 'action'])
                ->make(true);
        }

        return view('roaster.index', compact('data'));
    }

    /**
     * @return Factory|View
     */

    public function create()
    {

        $data = array(
            "officeDivisions" => OfficeDivision::whereIn('id',FilterController::getDivisionIds())->get(),
            "office_division_id" => 0,
            "department_id" => 0,

            "employees" => []
        );
        return view("roaster.create", compact('data'));
    }

    /**
     * @return Factory|View
     */
    public function createForm()
    {
        $request = \request();
        if($request->get("department_id") > 0) {
        $data['param'] = $request->all();
        if($request->type=='emp'){
            $data["users"] = getEmployeesInformationByDepartmentIDs($data['param']['department_id']);
        }
        $data["workSlots"] = WorkSlot::latest()->get();
        $data["department"] = Department::find($request->get("department_id"));

        return view('roaster.create-form', compact('data'));
    }
    return redirect()->back()->withErrors('Inavalid Department ID!');
    }

    /**
     * @param Roaster $roaster
     * @return Factory|View
     */
    public function edit(Roaster $roaster)
    {
        $data["workSlots"]          = WorkSlot::latest()->get();
        $currectDate                = date('Y-m-d');
        $existRoaster               = Roaster::where('user_id', $roaster->user_id)->whereDate('end_date', '>', $currectDate)->whereNotIn('id', [$roaster->id])->orderByDesc('id')->first();
        $tomorrow                   = new DateTime(date('Y-m-d', strtotime("tomorrow")));
        $endDate                    = new DateTime($roaster->end_date);

        $data['formType']           = ($roaster->department_roaster_id>0)? 'dep':'emp';

        if ($data['formType']=='emp') {
            if ($existRoaster) {
                if ($roaster->active_from > $existRoaster->end_date) {
                    $endDate                        = new DateTime($existRoaster->end_date);
                    $endDayCount                    = $tomorrow->diff($endDate)->format('%a')+1;

                    $data['startAllowDateCount']    = $endDayCount;
                    $data['endAllowDateCount']      = ''; //Unlimited
                } else {
                    $endDate                        = new DateTime($existRoaster->active_from);
                    $endDayCount                    = $tomorrow->diff($endDate)->format('%a');

                    $data['startAllowDateCount']    = 1;
                    $data['endAllowDateCount']      = $endDayCount;
                }
            } else {
                $data['startAllowDateCount']    = 1;
                $data['endAllowDateCount']      = ''; //Unlimited
            }
        } else {
            $data['departmentRoaster'] = DepartmentRoaster::find($roaster->department_roaster_id);
        }

        $roaster = $roaster->load("user.currentPromotion");

        return view('roaster.edit', compact('data', 'roaster'));
    }

    /**
     * @param RequestRoaster $request
     * @return RedirectResponse
     */
    //STORE ROASTER
    public function store(RequestRoaster $request)
    {
        try {

            $currentDate            = date('Y-m-d');
            $weekly_arr             = $request->input("weekly_holidays");
            if($weekly_arr){
                foreach ($weekly_arr as $val){
                    if(!is_null($val)){
                        $week_value[]=$val;
                    }
                }
            }else{
                $week_value[] = '';
            }

            DB::beginTransaction();
            $roasters = [];
            if($request->type=='dept'){ //DEPARTMENT WISE ROASTER CREATE
                $userIds                = [];
                $users                  = getEmployeesInformationByDepartmentIDs($request->input("department_id"));
                $requestedActiveFrom    = date("Y-m-d", strtotime($request->input("active_from")));
                $requestedEndDate       = date("Y-m-d", strtotime($request->input("end_date")));
                $previousOneDay         = date('Y-m-d', strtotime('-1 day', strtotime($requestedActiveFrom)));
                $department_id          = $request->input("department_id");


                if ($requestedActiveFrom > $currentDate && $requestedEndDate > $currentDate) {
                    if ($requestedActiveFrom <= $requestedEndDate) {

                        $alreadyCreatedData = "SELECT * FROM department_roasters WHERE ((active_from <= '$requestedEndDate' AND end_date > '$requestedEndDate' AND (end_date >= '$requestedActiveFrom' )) OR (active_from < '$requestedEndDate' AND end_date <= '$requestedEndDate' AND ( end_date >= '$requestedActiveFrom'))) AND department_id = '$department_id'";
                        $existData = DB::select($alreadyCreatedData);


                        if (count($existData)>0) {
                            session()->flash('type', 'error');
                            session()->flash('message', "Department roaster has already been created for same date range.");

                            $redirect = redirect()->back()->withInput();
                        } else {
                            $departmentStoreData = [
                                "work_slot_id"          => $request->input("work_slot_id"),
                                "office_division_id"    => $request->input("office_division_id"),
                                "department_id"         => $request->input("department_id"),
                                "active_from"           => date("Y-m-d", strtotime($request->input("active_from"))),
                                "end_date"              => date("Y-m-d", strtotime($request->input("end_date"))),
                                "weekly_holidays"       => json_encode($week_value),
                                "created_at"            => now(),
                                "created_by"            => Auth::id()
                            ];

                            $departmentStore        = DepartmentRoaster::insert($departmentStoreData);
                            $departmentRoasterId    = DB::getPdo()->lastInsertId();

                            if ($departmentRoasterId) {
                                foreach ($users as $user) {

                                    $empAlreadyCreatedData = "SELECT * FROM roasters WHERE ((active_from <= '$requestedEndDate' AND end_date > '$requestedEndDate' AND (end_date >= '$requestedActiveFrom' )) OR (active_from < '$requestedEndDate' AND end_date <= '$requestedEndDate' AND ( end_date >= '$requestedActiveFrom'))) AND user_id = '$user->id'";
                                    $empExistData = DB::select($empAlreadyCreatedData);

                                    if (count($empExistData)>0) {
                                        foreach ($empExistData as $empExist) {
                                            if (($empExist->active_from >= $requestedActiveFrom) &&  ($empExist->end_date <= $requestedEndDate)) {
                                                Roaster::find($empExist->id)->delete();
                                            } else if(($empExist->active_from <= $requestedActiveFrom) &&  ($empExist->end_date <= $requestedEndDate)){
                                                Roaster::find($empExist->id)->update(["end_date"          => date('Y-m-d', strtotime('-1 day', strtotime($requestedActiveFrom))) ]);
                                            } else if(($empExist->active_from <= $requestedActiveFrom) &&  ($empExist->end_date >= $requestedEndDate)){
                                                Roaster::find($empExist->id)->update(["end_date"          => date('Y-m-d', strtotime('-1 day', strtotime($requestedActiveFrom))) ]);
                                            } else if(($empExist->active_from >= $requestedActiveFrom) &&  ($empExist->end_date >= $requestedEndDate)){
                                                Roaster::find($empExist->id)->update([
                                                    "active_from"       => date('Y-m-d', strtotime('1 day', strtotime($requestedEndDate)))
                                                ]);
                                            }
                                        }
                                    }

                                    $new_array = [
                                        "user_id"               => $user->id,
                                        "work_slot_id"          => $request->input("work_slot_id"),
                                        "office_division_id"    => $request->input("office_division_id"),
                                        "department_id"         => $request->input("department_id"),
                                        "active_from"           => date("Y-m-d", strtotime($request->input("active_from"))),
                                        "end_date"              => date("Y-m-d", strtotime($request->input("end_date"))),
                                        "weekly_holidays"       => json_encode($week_value),
                                        "created_by"            => Auth::id(),
                                        "created_at"            => now(),
                                        "department_roaster_id" => $departmentRoasterId
                                    ];
                                    $roasters[]=$new_array;
                                }
                            } else {
                                $roasters[]='';
                            }


                            if (!empty($roasters) && $departmentRoasterId>0) {
                                $storeResponse = Roaster::insert($roasters);
                                session()->flash('message', 'Roaster Created Successfully');

                                DB::commit();
                                $redirect = redirect()->route('roaster.index');
                            } else {
                                session()->flash('type', 'error');
                                session()->flash('message', 'Please check your required fields!!');

                                $redirect = redirect()->back()->withInput();
                            }
                        }
                    } else {
                        session()->flash('type', 'error');
                        session()->flash('message', 'The end date must be greater than or equal to the start date');

                        $redirect = redirect()->back()->withInput();
                    }
                } else {
                    session()->flash('type', 'error');
                    session()->flash('message', 'The start date & end date must be greater than current date');

                    $redirect = redirect()->back()->withInput();
                }
            } else { //EMPLOYEE WISE ROASTERS CREATE
                foreach ($request->input("user_id") as $index => $item) {
                    if(
                        !empty($request->input("user_id")[$index]) AND
                        !empty($request->input("office_division_id")[$index]) AND
                        !empty($request->input("department_id")[$index]) AND
                        !empty($request->input("work_slot_id")[$index]) AND
                        !empty($request->input("active_from")[$index]) AND
                        !empty($request->input("end_date")[$index])
                    ) {
                        $newActiveFromDate  = date("Y-m-d", strtotime($request->input("active_from")[$index]));
                        $newEndDate         = date("Y-m-d", strtotime($request->input("end_date")[$index]));

                        if ( ($newActiveFromDate > $currentDate && $newEndDate > $currentDate) && $newActiveFromDate <= $newEndDate) {
                            $userId = $request->input("user_id")[$index];
                            $alreadyCreatedData = "SELECT * FROM roasters WHERE ((active_from <= '$newEndDate' AND end_date > '$newEndDate' AND (end_date >= '$newActiveFromDate' )) OR (active_from < '$newEndDate' AND end_date <= '$newEndDate' AND ( end_date >= '$newActiveFromDate'))) AND user_id = '$userId'";
                            $existData = DB::select($alreadyCreatedData);

                            if (empty($existData)) {
                                array_push($roasters, [
                                    "user_id"                   => $request->input("user_id")[$index],
                                    "work_slot_id"              => $request->input("work_slot_id")[$index],
                                    "office_division_id"        => $request->input("office_division_id")[$index],
                                    "department_id"             => $request->input("department_id")[$index],
                                    "active_from"               => date("Y-m-d", strtotime($request->input("active_from")[$index])),
                                    "end_date"                  => date("Y-m-d", strtotime($request->input("end_date")[$index])),
                                    "weekly_holidays"           => json_encode(isset($weekly_arr) ? $weekly_arr[$index]: []),
                                    "department_roaster_id"     => '-'.(date("Ymdhis").$index.$request->input("user_id")[$index] ),
                                    "created_by"                => Auth::id(),
                                    "created_at"                => now()
                                ]);
                            }
                        }
                    }
                }

                if (!empty($roasters) ) {
                    $storeResponse = Roaster::insert($roasters);
                    session()->flash('message', 'Roaster Created Successfully');
                    DB::commit();
                    $redirect = redirect()->route('roaster.index');
                } else {
                    session()->flash('type', 'error');
                    session()->flash('message', 'Please check your required and valid date range fields!');

                    $redirect = redirect()->back()->withInput();
                }
            }

        } catch (Exception $exception) {
            DB::rollBack();
            session()->flash('type', 'error');
            session()->flash('message', 'Sorry! Something went wrong!!');

            $redirect = redirect()->back()->withInput();
        }

        return $redirect;
    }
    //END STORE ROASTER

    /**
     * @param RequestRoaster $request
     * @param Roaster $roaster
     * @return RedirectResponse
     */
    public function update(RequestRoaster $request, Roaster $roaster)
    {

        try {
            $currectDate                    = date('Y-m-d');
            $tomorrow                       = date('Y-m-d', strtotime("tomorrow"));

            //STORED DATA
            $roasterId                      = $roaster->id;
            $active_from                    = date("Y-m-d", strtotime($roaster->active_from));
            $end_date                       = date("Y-m-d", strtotime($roaster->end_date));

            $work_slot_id                   = $roaster->work_slot_id;
            $weekly_holidays                = $roaster->weekly_holidays;
            $activeFromDateBeforeOneDay     = date('Y-m-d', strtotime('-1 days', strtotime($active_from)));
            $endDateBeforeOneDay            = date('Y-m-d', strtotime('-1 days', strtotime($end_date)));

            //FORM DATA
            $new_active_from                = date("Y-m-d", strtotime($request->input("active_from")[0]));
            $new_end_date                   = date("Y-m-d", strtotime($request->input("end_date")[0]));
            $new_work_slot_id               = $request->input("work_slot_id")[0];
            $newActiveFromDateBeforeOneDay  = date('Y-m-d', strtotime('-1 days', strtotime($new_active_from)));
            $newEndDateBeforeOneDay         = date('Y-m-d', strtotime('-1 days', strtotime($new_end_date)));

            if ($new_active_from && $new_end_date) {

                $alreadyCreatedData = "SELECT * FROM roasters WHERE (( active_from <= '$new_end_date' AND end_date > '$new_end_date' AND (end_date >= '$end_date' OR  end_date >= '$new_active_from')) OR  ( active_from < '$new_end_date' AND end_date <= '$new_end_date' AND (end_date >= '$end_date' OR  end_date >= '$new_active_from'))) AND user_id = '$roaster->user_id' AND id != $roasterId";
                $existData = DB::select($alreadyCreatedData);

                if (count($existData)==0) {
                    if ($active_from <= $currectDate && $end_date <= $currectDate) {

                        session()->flash('type', 'error');
                        session()->flash('message', "You can't update this roaster!");
                        $redirect = redirect()->back();
                    } else {
                        if ($new_end_date <= $currectDate) {
                            session()->flash('type', 'error');
                            session()->flash('message', "Roaster start date & end date must be greater than current date!");
                            $redirect = redirect()->back();
                        } else {

                            if ($new_active_from <= $new_end_date) {
                                DB::beginTransaction();
                                //NEW WEEKLY HOKIDAYS
                                $new_weekly_arr = $request->input("weekly_holidays");
                                $existWeeklyHoliday = [];
                                $newWeeklyHoliday = [];
                                $existWeeklyHoliday2 = [];

                                foreach ((array)$new_weekly_arr as $new_weekly) {
                                    if ( in_array($new_weekly, $weekly_holidays)) {
                                        $existWeeklyHoliday[] = $new_weekly;
                                    } else {
                                        $newWeeklyHoliday[] = $new_weekly;
                                    }
                                }

                                foreach ((array)$weekly_holidays as $weekly_holiday) {
                                    if ( is_array($new_weekly_arr) && !in_array("$weekly_holiday", $new_weekly_arr)) {
                                        $existWeeklyHoliday2[] = $weekly_holiday;
                                    }
                                }

                                //END NEW WEEKLY HOKIDAY
                                if (count($newWeeklyHoliday)>0 || count($existWeeklyHoliday2)>0 || date("Y-m-d", strtotime($active_from)) !=$new_active_from ||  date("Y-m-d", strtotime($end_date))!=$new_end_date || $work_slot_id!=$new_work_slot_id) {
                                    if ($new_active_from <= $new_end_date) {
                                        // WORK SLOT CHECK
                                        if ($work_slot_id!=$new_work_slot_id) {
                                            if ($new_active_from==$active_from) { //EXISTING AND NEW END DATE SAME
                                                if (count($newWeeklyHoliday)>0 || count($existWeeklyHoliday2)>0) {
                                                    //EXSITING ROASTER UPDATE
                                                    $roaster->update([
                                                        "end_date"          => $new_end_date,
                                                        "work_slot_id"      => $new_work_slot_id,
                                                        "weekly_holidays"   => ($new_weekly_arr)? $new_weekly_arr:[],
                                                        "is_locked"         => 1, //1=Roaster Locked
                                                        "updated_at"        => now()
                                                    ]);
                                                } else {
                                                    $roaster->update([
                                                        "end_date"          => $new_end_date,
                                                        "work_slot_id"      => $new_work_slot_id,
                                                        "weekly_holidays"   => ($new_weekly_arr)? $new_weekly_arr:[],
                                                        "is_locked"         => 1, //1=Roaster Locked
                                                        "updated_at"        => now()
                                                    ]);
                                                }
                                            } else { //EXISTING AND NEW END DATE NOT SAME
                                                if ($active_from<=$currectDate) {
                                                    if ($new_active_from>=$active_from) {
                                                        session()->flash('type', 'error');
                                                        session()->flash('message', "Roaster previous start date can't be chenge!");
                                                        $redirect = redirect()->back();
                                                    } else {
                                                        //EXSITING ROASTER UPDATE
                                                        $roaster->update([
                                                            "end_date"          => $new_end_date,
                                                            "is_locked"         => 1, //1=Roaster Locked
                                                            "updated_at"        => now()
                                                        ]);

                                                        //NEW ROASTER CREATE
                                                        Roaster::insert([
                                                            "user_id"           => $roaster->user_id,
                                                            "work_slot_id"      => $new_work_slot_id,
                                                            "office_division_id"=> $roaster->office_division_id,
                                                            "department_id"     => $roaster->department_id,
                                                            'active_from'       => ($new_active_from>$currectDate)? $new_active_from:$active_from,
                                                            "end_date"          => $new_end_date,
                                                            "weekly_holidays"   => ($new_weekly_arr)? json_encode($new_weekly_arr):[],
                                                            "is_locked"         => 1, //1=Roaster Locked
                                                            "created_at"        => now()
                                                        ]);
                                                    }
                                                } else {
                                                    //EXSITING ROASTER UPDATE
                                                    $roaster->update([
                                                        "work_slot_id"      => $new_work_slot_id,
                                                        "end_date"          => $new_end_date,
                                                        "weekly_holidays"   => ($new_weekly_arr)? $new_weekly_arr:[],
                                                        "is_locked"         => 1, //1=Roaster Locked
                                                        "updated_at"        => now()
                                                    ]);
                                                }

                                            }
                                        } else {
                                            if ($new_active_from==$active_from) { //EXISTING AND NEW END DATE SAME

                                                if (count($newWeeklyHoliday)>0 || count($existWeeklyHoliday2)>0) {
                                                    if ($active_from<=$currectDate) {
                                                        //EXSITING ROASTER UPDATE
                                                        $roaster->update([
                                                            "end_date"          => $currectDate,
                                                            "is_locked"         => 1, //1=Roaster Locked
                                                            "updated_at"        => now()
                                                        ]);

                                                        //NEW ROASTER CREATE
                                                        Roaster::insert([
                                                            "user_id"           => $roaster->user_id,
                                                            "work_slot_id"      => $new_work_slot_id,
                                                            "office_division_id"=> $roaster->office_division_id,
                                                            "department_id"     => $roaster->department_id,
                                                            'active_from'       => $tomorrow,
                                                            "end_date"          => $new_end_date,
                                                            "weekly_holidays"   => ($new_weekly_arr)? json_encode($new_weekly_arr):[],
                                                            "is_locked"         => 1, //1=Roaster Locked
                                                            "created_at"        => now()
                                                        ]);
                                                    } else {
                                                        //EXSITING ROASTER UPDATE
                                                        $roaster->update([
                                                            "end_date"          => $new_end_date,
                                                            "weekly_holidays"   => ($new_weekly_arr)? $new_weekly_arr:[],
                                                            "is_locked"         => 1, //1=Roaster Locked
                                                            "updated_at"        => now()
                                                        ]);
                                                    }

                                                } else {
                                                    $roaster->update([
                                                        "end_date"          => $new_end_date,
                                                        "is_locked"         => 1, //1=Roaster Locked
                                                        "updated_at"        => now(),
                                                    ]);
                                                }
                                            } else { //EXISTING AND NEW END DATE NOT SAME
                                                if ($active_from<=$currectDate) {
                                                    if ($new_active_from>=$active_from) {
                                                        session()->flash('type', 'error');
                                                        session()->flash('message', "Roaster previous start date can't be chenge!");
                                                        $redirect = redirect()->back();
                                                    } else {
                                                        if (count($newWeeklyHoliday)>0 || count($existWeeklyHoliday2)>0) {
                                                            if ($new_end_date>$currectDate) {
                                                                //EXSITING ROASTER UPDATE
                                                                $roaster->update([
                                                                    "end_date"          => $currectDate,
                                                                    "is_locked"         => 1, //1=Roaster Locked
                                                                    "updated_at"        => now()
                                                                ]);

                                                                //NEW ROASTER CREATE
                                                                Roaster::insert([
                                                                    "user_id"           => $roaster->user_id,
                                                                    "work_slot_id"      => $new_work_slot_id,
                                                                    "office_division_id"=> $roaster->office_division_id,
                                                                    "department_id"     => $roaster->department_id,
                                                                    'active_from'       => $tomorrow,
                                                                    "end_date"          => $new_end_date,
                                                                    "weekly_holidays"   => ($new_weekly_arr)? json_encode($new_weekly_arr):[],
                                                                    "is_locked"         => 1, //1=Roaster Locked
                                                                    "created_at"        => now()
                                                                ]);
                                                            } else {
                                                                //EXSITING ROASTER UPDATE
                                                                $roaster->update([
                                                                    'active_from'       => ($new_active_from>$currectDate)? $new_active_from:$active_from,
                                                                    "end_date"          => $new_end_date,
                                                                    "weekly_holidays"   => ($new_weekly_arr)? $new_weekly_arr:[],
                                                                    "is_locked"         => 1, //1=Roaster Locked
                                                                    "updated_at"        => now()
                                                                ]);
                                                            }
                                                        } else {
                                                            //EXSITING ROASTER UPDATE
                                                            $roaster->update([
                                                                "end_date"          => $new_end_date,
                                                                "is_locked"         => 1, //1=Roaster Locked
                                                                "updated_at"        => now()
                                                            ]);
                                                        }
                                                    }
                                                } else {
                                                    $roaster->update([
                                                        'active_from'       => ($new_active_from>$currectDate)? $new_active_from:$active_from,
                                                        "end_date"          => $new_end_date,
                                                        "weekly_holidays"   => ($new_weekly_arr)? $new_weekly_arr:[],
                                                        "is_locked"         => 1, //1=Roaster Locked
                                                        "updated_at"        => now()
                                                    ]);
                                                }
                                            }
                                        }
                                        // END WORK SLOT CHECK

                                        DB::commit();
                                        session()->flash('message', 'Roaster Updated Successfully');
                                        $redirect = redirect()->route('roaster.index');
                                    } else {
                                        session()->flash('type', 'error');
                                        session()->flash('message', 'The end date must be greater than or equal to the start date');
                                        $redirect = redirect()->back();
                                    }
                                } else {
                                    $roaster->update([
                                        "is_locked"         => 1, //1=Roaster Locked
                                        "weekly_holidays"   => [],
                                    ]);
                                    DB::commit();
                                    session()->flash('message', 'Roaster Updated Successfully');
                                    $redirect = redirect()->route('roaster.index');
                                }
                            } else {
                                session()->flash('type', 'error');
                                session()->flash('message', "The end date must be greater than or equal to the start date!");
                                $redirect = redirect()->back();
                            }
                        }
                    }
                } else {
                    session()->flash('type', 'error');
                    session()->flash('message', "You can't update this roaster!");
                    $redirect = redirect()->back();
                }

            } else {
                session()->flash('type', 'error');
                session()->flash('message', 'Please check Active From Date & End Date!');
                $redirect = redirect()->back();
            }

        } catch (Exception $exception) {
            DB::rollBack();

            session()->flash('type', 'error');
            session()->flash('message', $exception->getMessage());
            $redirect = redirect()->back();
        }

        return $redirect;
    }


    /**
     * @param RequestDepartmentRoaster $request
     * @param DepartmentRoaster $roaster
     * @return RedirectResponse
     */
    //DEPARTMENT ROASTER UPDATE
    public function updateDepartmentRoaster(RequestDepartmentRoaster $request, DepartmentRoaster $roaster){

        try {
            $currectDate                    = date('Y-m-d');
            $tomorrow                       = date('Y-m-d', strtotime("tomorrow"));
            $weekly_arr             = $request->input("weekly_holidays");
            if($weekly_arr){
                foreach ($weekly_arr as $val){
                    if(!is_null($val)){
                        $week_value[]=$val;
                    }
                }
            }else{
                $week_value[] = '';
            }

            //STORED DATA
            $roasterId                      = $roaster->id;
            $active_from                    = date("Y-m-d", strtotime($roaster->active_from));
            $end_date                       = date("Y-m-d", strtotime($roaster->end_date));

            $work_slot_id                   = $roaster->work_slot_id;
            $weekly_holidays                = json_decode($roaster->weekly_holidays);
            $activeFromDateBeforeOneDay     = date('Y-m-d', strtotime('-1 days', strtotime($active_from)));
            $endDateBeforeOneDay            = date('Y-m-d', strtotime('-1 days', strtotime($end_date)));

            //FORM DATA
            $new_active_from                = date("Y-m-d", strtotime($request->active_from));
            $new_end_date                   = date("Y-m-d", strtotime($request->end_date));
            $new_work_slot_id               = $request->input("work_slot_id");
            $newActiveFromDateBeforeOneDay  = date('Y-m-d', strtotime('-1 days', strtotime($new_active_from)));
            $newEndDateBeforeOneDay         = date('Y-m-d', strtotime('-1 days', strtotime($new_end_date)));
            $users                          = getEmployeesInformationByDepartmentIDs($request->input("department_id"));

            if ($new_active_from && $new_end_date) {

                $alreadyCreatedData = "SELECT * FROM department_roasters WHERE (( active_from <= '$new_end_date' AND end_date > '$new_end_date' AND (end_date >= '$end_date' OR  end_date >= '$new_active_from')) OR  ( active_from < '$new_end_date' AND end_date <= '$new_end_date' AND (end_date >= '$end_date' OR  end_date >= '$new_active_from'))) AND department_id = '$roaster->department_id' AND id != $roasterId";
                $existData = DB::select($alreadyCreatedData);

                if (count($existData)==0) {
                    if ($active_from <= $currectDate && $end_date <= $currectDate) {

                        session()->flash('type', 'error');
                        session()->flash('message', "You can't update this roaster!");
                        $redirect = redirect()->back();
                    } else {
                        if ($new_end_date <= $currectDate) {
                            session()->flash('type', 'error');
                            session()->flash('message', "Roaster start date & end date must be greater than current date!");
                            $redirect = redirect()->back();
                        } else {

                            if ($new_active_from <= $new_end_date) {
                                DB::beginTransaction();
                                //NEW WEEKLY HOKIDAYS
                                $new_weekly_arr         = $request->input("weekly_holidays");
                                $existWeeklyHoliday     = [];
                                $newWeeklyHoliday       = [];
                                $existWeeklyHoliday2    = [];

                                foreach ($new_weekly_arr as $new_weekly) {
                                    if ( in_array($new_weekly, $weekly_holidays)) {
                                        $existWeeklyHoliday[] = $new_weekly;
                                    } else {
                                        $newWeeklyHoliday[] = $new_weekly;
                                    }
                                }

                                foreach ((array)$weekly_holidays as $weekly_holiday) {
                                    if ( is_array($new_weekly_arr) && !in_array("$weekly_holiday", $new_weekly_arr)) {
                                        $existWeeklyHoliday2[] = $weekly_holiday;
                                    }
                                }

                                //END NEW WEEKLY HOKIDAY
                                if (count($newWeeklyHoliday)>0 || count($existWeeklyHoliday2)>0 || date("Y-m-d", strtotime($active_from)) !=$new_active_from ||  date("Y-m-d", strtotime($end_date))!=$new_end_date || $work_slot_id!=$new_work_slot_id) {
                                    if ($new_active_from <= $new_end_date) {
                                        $departmentWiseRoasters = Roaster::select('id')->where('department_roaster_id', $roaster->id)->get();

                                        // WORK SLOT CHECK
                                        if ($work_slot_id!=$new_work_slot_id) {
                                            if ($new_active_from==$active_from) { //EXISTING AND NEW END DATE SAME

                                                if (count($newWeeklyHoliday)>0 || count($existWeeklyHoliday2)>0) {
                                                    if ($active_from<=$currectDate) {
                                                        if ($end_date>$currectDate) {
                                                            //EXSITING ROASTER UPDATE
                                                            $roaster->update([
                                                                "end_date"          => $currectDate,
                                                                "is_locked"         => 1, //1=Roaster Locked
                                                                "updated_at"        => now()
                                                            ]);

                                                            foreach ($departmentWiseRoasters as $key => $departmentWiseRoaster) {
                                                                $departmentWiseRoaster->update([
                                                                    "end_date"          => $currectDate,
                                                                    "is_locked"         => 1, //1=Roaster Locked
                                                                    "updated_at"        => now()
                                                                ]);
                                                            }

                                                            //NEW DEPARTMENT WISE ROASTER CREATE
                                                            $departmentStoreData = [
                                                                "work_slot_id"      => $request->input("work_slot_id"),
                                                                "department_id"     => $request->department_id,
                                                                "active_from"       => $tomorrow,
                                                                "end_date"          => date("Y-m-d", strtotime($request->input("end_date"))),
                                                                "weekly_holidays"   => json_encode($week_value),
                                                                "created_at"        => now(),
                                                                "created_by"        => Auth::id()
                                                            ];
                                                            //END DEPARTMENT WISE ROASTER CREATE

                                                            $departmentStore        = DepartmentRoaster::insert($departmentStoreData);
                                                            $departmentRoasterId    = DB::getPdo()->lastInsertId();

                                                            //NEW DEPARTMENT WISE USERS ROASTER CREATE
                                                            foreach ($users as $user) {
                                                                $new_array = [
                                                                    "user_id"               => $user->id,
                                                                    "work_slot_id"          => $request->input("work_slot_id"),
                                                                    "office_division_id"    => $request->office_division_id,
                                                                    "department_id"         => $request->department_id,
                                                                    "active_from"           => $tomorrow,
                                                                    "end_date"              => date("Y-m-d", strtotime($request->input("end_date"))),
                                                                    "weekly_holidays"       => json_encode($week_value),
                                                                    "created_by"            => Auth::id(),
                                                                    "created_at"            => now(),
                                                                    "department_roaster_id" => $departmentRoasterId
                                                                ];
                                                                $roasters[]=$new_array;
                                                            }

                                                            //NEW ROASTER CREATE
                                                            Roaster::insert($roasters);
                                                            //END DEPARTMENT WISE USERS ROASTER CREATE
                                                        } else {
                                                            $roaster->update([
                                                                "is_locked"         => 1, //1=Roaster Locked
                                                                "updated_at"        => now()
                                                            ]);

                                                            foreach ($departmentWiseRoasters as $key => $departmentWiseRoaster) {
                                                                $departmentWiseRoaster->update([
                                                                    "is_locked"         => 1, //1=Roaster Locked
                                                                    "updated_at"        => now()
                                                                ]);
                                                            }
                                                        }
                                                    } else {
                                                        $roaster->update([
                                                            "end_date"          => $new_end_date,
                                                            "work_slot_id"      => $new_work_slot_id,
                                                            "weekly_holidays"   => ($new_weekly_arr)? $new_weekly_arr:[],
                                                            "is_locked"         => 1, //1=Roaster Locked
                                                            "updated_at"        => now()
                                                        ]);

                                                        foreach ($departmentWiseRoasters as $key => $departmentWiseRoaster) {
                                                            $departmentWiseRoaster->update([
                                                                "end_date"          => $new_end_date,
                                                                "work_slot_id"      => $new_work_slot_id,
                                                                "weekly_holidays"   => ($new_weekly_arr)? $new_weekly_arr:[],
                                                                "is_locked"         => 1, //1=Roaster Locked
                                                                "updated_at"        => now()
                                                            ]);
                                                        }
                                                    }
                                                } else {
                                                    if ($active_from<=$currectDate) {
                                                        if ($end_date>$currectDate) {
                                                            // dd(333);
                                                            //EXSITING ROASTER UPDATE
                                                            $roaster->update([
                                                                "end_date"          => $currectDate,
                                                                "is_locked"         => 1, //1=Roaster Locked
                                                                "updated_at"        => now()
                                                            ]);

                                                            foreach ($departmentWiseRoasters as $key => $departmentWiseRoaster) {
                                                                $departmentWiseRoaster->update([
                                                                    "end_date"          => $currectDate,
                                                                    "is_locked"         => 1, //1=Roaster Locked
                                                                    "updated_at"        => now()
                                                                ]);
                                                            }

                                                            //NEW DEPARTMENT WISE ROASTER CREATE
                                                            $departmentStoreData = [
                                                                "work_slot_id"      => $request->input("work_slot_id"),
                                                                "department_id"     => $request->department_id,
                                                                "active_from"       => $tomorrow,
                                                                "end_date"          => date("Y-m-d", strtotime($request->input("end_date"))),
                                                                "weekly_holidays"   => json_encode($week_value),
                                                                "created_at"        => now(),
                                                                "created_by"        => Auth::id()
                                                            ];
                                                            //END DEPARTMENT WISE ROASTER CREATE

                                                            $departmentStore        = DepartmentRoaster::insert($departmentStoreData);
                                                            $departmentRoasterId    = DB::getPdo()->lastInsertId();

                                                            //NEW DEPARTMENT WISE USERS ROASTER CREATE
                                                            foreach ($users as $user) {
                                                                $new_array = [
                                                                    "user_id"               => $user->id,
                                                                    "work_slot_id"          => $request->input("work_slot_id"),
                                                                    "office_division_id"    => $request->office_division_id,
                                                                    "department_id"         => $request->department_id,
                                                                    "active_from"           => $tomorrow,
                                                                    "end_date"              => date("Y-m-d", strtotime($request->input("end_date"))),
                                                                    "weekly_holidays"       => json_encode($week_value),
                                                                    "created_by"            => Auth::id(),
                                                                    "created_at"            => now(),
                                                                    "department_roaster_id" => $departmentRoasterId
                                                                ];
                                                                $roasters[]=$new_array;
                                                            }

                                                            //NEW ROASTER CREATE
                                                            Roaster::insert($roasters);
                                                            //END DEPARTMENT WISE USERS ROASTER CREATE
                                                        } else {
                                                            session()->flash('type', 'error');
                                                            session()->flash('message', "You can't update this roaster!");
                                                            return redirect()->back();
                                                        }
                                                    } else {
                                                        $roaster->update([
                                                            "end_date"          => $new_end_date,
                                                            "work_slot_id"      => $new_work_slot_id,
                                                            "weekly_holidays"   => ($new_weekly_arr)? $new_weekly_arr:[],
                                                            "is_locked"         => 1, //1=Roaster Locked
                                                            "updated_at"        => now()
                                                        ]);

                                                        foreach ($departmentWiseRoasters as $key => $departmentWiseRoaster) {
                                                            $departmentWiseRoaster->update([
                                                                "end_date"          => $new_end_date,
                                                                "work_slot_id"      => $new_work_slot_id,
                                                                "weekly_holidays"   => ($new_weekly_arr)? $new_weekly_arr:[],
                                                                "is_locked"         => 1, //1=Roaster Locked
                                                                "updated_at"        => now()
                                                            ]);
                                                        }
                                                    }
                                                }
                                            } else { //EXISTING AND NEW END DATE NOT SAME
                                                if ($active_from<=$currectDate) {
                                                    if ($new_active_from>=$active_from) {
                                                        session()->flash('type', 'error');
                                                        session()->flash('message', "Roaster previous start date can't be chenge!");
                                                        return redirect()->back();
                                                    } else {
                                                        //EXSITING DEPARTMENT ROASTER UPDATE
                                                        $roaster->update([
                                                            "end_date"          => $currectDate,
                                                            "is_locked"         => 1, //1=Roaster Locked
                                                            "updated_at"        => now()
                                                        ]);

                                                        foreach ($departmentWiseRoasters as $key => $departmentWiseRoaster) {
                                                            $departmentWiseRoaster->update([
                                                                "end_date"          => $currectDate,
                                                                "is_locked"         => 1, //1=Roaster Locked
                                                                "updated_at"        => now()
                                                            ]);
                                                        }
                                                        //END EXSITING DEPARTMENT ROASTER UPDATE

                                                        //NEW DEPARTMENT WISE ROASTER CREATE
                                                        $departmentStoreData = [
                                                            "work_slot_id"      => $request->input("work_slot_id"),
                                                            "department_id"     => $request->department_id,
                                                            "active_from"       => $tomorrow,
                                                            "end_date"          => date("Y-m-d", strtotime($request->input("end_date"))),
                                                            "weekly_holidays"   => json_encode($week_value),
                                                            "created_at"        => now(),
                                                            "created_by"        => Auth::id()
                                                        ];
                                                        //END DEPARTMENT WISE ROASTER CREATE

                                                        $departmentStore        = DepartmentRoaster::insert($departmentStoreData);
                                                        $departmentRoasterId    = DB::getPdo()->lastInsertId();

                                                        //NEW DEPARTMENT WISE USERS ROASTER CREATE
                                                        foreach ($users as $user) {
                                                            $new_array = [
                                                                "user_id"               => $user->id,
                                                                "work_slot_id"          => $request->input("work_slot_id"),
                                                                "office_division_id"    => $request->office_division_id,
                                                                "department_id"         => $request->department_id,
                                                                "active_from"           => $tomorrow,
                                                                "end_date"              => date("Y-m-d", strtotime($request->input("end_date"))),
                                                                "weekly_holidays"       => json_encode($week_value),
                                                                "created_by"            => Auth::id(),
                                                                "created_at"            => now(),
                                                                "department_roaster_id" => $departmentRoasterId
                                                            ];
                                                            $roasters[]=$new_array;
                                                        }

                                                        //NEW ROASTER CREATE
                                                        Roaster::insert($roasters);
                                                        //END DEPARTMENT WISE USERS ROASTER CREATE
                                                    }
                                                } else {
                                                    //EXSITING ROASTER UPDATE
                                                    $roaster->update([
                                                        "work_slot_id"      => $new_work_slot_id,
                                                        "end_date"          => $new_end_date,
                                                        "weekly_holidays"   => ($new_weekly_arr)? $new_weekly_arr:[],
                                                        "is_locked"         => 1, //1=Roaster Locked
                                                        "updated_at"        => now()
                                                    ]);

                                                    foreach ($departmentWiseRoasters as $key => $departmentWiseRoaster) {
                                                        $departmentWiseRoaster->update([
                                                            "work_slot_id"      => $new_work_slot_id,
                                                            'active_from'       => $new_active_from,
                                                            "end_date"          => $new_end_date,
                                                            "weekly_holidays"   => ($new_weekly_arr)? $new_weekly_arr:[],
                                                            "is_locked"         => 1, //1=Roaster Locked
                                                            "updated_at"        => now()
                                                        ]);
                                                    }
                                                }
                                            }
                                        } else {
                                            if ($new_active_from==$active_from) { //EXISTING AND NEW END DATE SAME

                                                if (count($newWeeklyHoliday)>0 || count($existWeeklyHoliday2)>0) {
                                                    if ($active_from<=$currectDate) {
                                                        //EXSITING DEPARTMENT ROASTER UPDATE
                                                        $roaster->update([
                                                            "end_date"          => $currectDate,
                                                            "is_locked"         => 1, //1=Roaster Locked
                                                            "updated_at"        => now()
                                                        ]);

                                                        foreach ($departmentWiseRoasters as $key => $departmentWiseRoaster) {
                                                            $departmentWiseRoaster->update([
                                                                "end_date"          => $currectDate,
                                                                "is_locked"         => 1, //1=Roaster Locked
                                                                "updated_at"        => now()
                                                            ]);
                                                        }
                                                        //END EXSITING DEPARTMENT ROASTER UPDATE


                                                        //NEW DEPARTMENT WISE ROASTER CREATE
                                                        $departmentStoreData = [
                                                            "work_slot_id"      => $request->input("work_slot_id"),
                                                            "department_id"     => $request->department_id,
                                                            "active_from"       => $tomorrow,
                                                            "end_date"          => date("Y-m-d", strtotime($request->input("end_date"))),
                                                            "weekly_holidays"   => json_encode($week_value),
                                                            "created_at"        => now(),
                                                            "created_by"        => Auth::id()
                                                        ];
                                                        //END DEPARTMENT WISE ROASTER CREATE

                                                        $departmentStore        = DepartmentRoaster::insert($departmentStoreData);
                                                        $departmentRoasterId    = DB::getPdo()->lastInsertId();

                                                        //NEW DEPARTMENT WISE USERS ROASTER CREATE
                                                        foreach ($users as $user) {
                                                            $new_array = [
                                                                "user_id"               => $user->id,
                                                                "work_slot_id"          => $request->input("work_slot_id"),
                                                                "office_division_id"    => $request->office_division_id,
                                                                "department_id"         => $request->department_id,
                                                                "active_from"           => $tomorrow,
                                                                "end_date"              => date("Y-m-d", strtotime($request->input("end_date"))),
                                                                "weekly_holidays"       => json_encode($week_value),
                                                                "created_by"            => Auth::id(),
                                                                "created_at"            => now(),
                                                                "department_roaster_id" => $departmentRoasterId
                                                            ];
                                                            $roasters[]=$new_array;
                                                        }

                                                        //NEW ROASTER CREATE
                                                        Roaster::insert($roasters);
                                                        //END DEPARTMENT WISE USERS ROASTER CREATE

                                                    } else {
                                                        //DEPARTMENT ROASTER UPDATE
                                                        $roaster->update([
                                                            "end_date"          => $new_end_date,
                                                            "weekly_holidays"   => ($new_weekly_arr)? $new_weekly_arr:[],
                                                            "is_locked"         => 1, //1=Roaster Locked
                                                            "updated_at"        => now()
                                                        ]);

                                                        //EMPLOYEE WISE ROASTER UPDATE
                                                        foreach ($departmentWiseRoasters as $key => $departmentWiseRoaster) {
                                                            $departmentWiseRoaster->update([
                                                                "end_date"          => $new_end_date,
                                                                "weekly_holidays"   => ($new_weekly_arr)? $new_weekly_arr:[],
                                                                "is_locked"         => 1, //1=Roaster Locked
                                                                "updated_at"        => now()
                                                            ]);
                                                        }
                                                    }

                                                } else {
                                                    //DEPARTMENT ROASTER UPDATE
                                                    $roaster->update([
                                                        "end_date"          => $new_end_date,
                                                        "is_locked"         => 1, //1=Roaster Locked
                                                        "updated_at"        => now()
                                                    ]);

                                                    //EMPLOYEE WISE ROASTER UPDATE
                                                    foreach ($departmentWiseRoasters as $key => $departmentWiseRoaster) {
                                                        $departmentWiseRoaster->update([
                                                            "end_date"          => $new_end_date,
                                                            "is_locked"         => 1, //1=Roaster Locked
                                                            "updated_at"        => now()
                                                        ]);
                                                    }

                                                }
                                            } else { //EXISTING AND NEW END DATE NOT SAME
                                                if ($active_from<=$currectDate) {
                                                    if ($new_active_from>=$active_from) {
                                                        session()->flash('type', 'error');
                                                        session()->flash('message', "Roaster previous start date can't be chenge!");
                                                        $redirect = redirect()->back();
                                                    } else {
                                                        if (count($newWeeklyHoliday)>0 || count($existWeeklyHoliday2)>0) {
                                                            if ($new_end_date>$currectDate) {

                                                                //EXSITING DEPARTMENT ROASTER UPDATE
                                                                $roaster->update([
                                                                    "end_date"          => $currectDate,
                                                                    "is_locked"         => 1, //1=Roaster Locked
                                                                    "updated_at"        => now()
                                                                ]);

                                                                foreach ($departmentWiseRoasters as $key => $departmentWiseRoaster) {
                                                                    $departmentWiseRoaster->update([
                                                                        "end_date"          => $currectDate,
                                                                        "is_locked"         => 1, //1=Roaster Locked
                                                                        "updated_at"        => now()
                                                                    ]);
                                                                }
                                                                //END EXSITING DEPARTMENT ROASTER UPDATE


                                                                //NEW DEPARTMENT WISE ROASTER CREATE
                                                                $departmentStoreData = [
                                                                    "work_slot_id"      => $request->input("work_slot_id"),
                                                                    "department_id"     => $request->department_id,
                                                                    "active_from"       => $tomorrow,
                                                                    "end_date"          => date("Y-m-d", strtotime($request->input("end_date"))),
                                                                    "weekly_holidays"   => json_encode($week_value),
                                                                    "created_at"        => now(),
                                                                    "created_by"        => Auth::id()
                                                                ];
                                                                //END DEPARTMENT WISE ROASTER CREATE

                                                                $departmentStore        = DepartmentRoaster::insert($departmentStoreData);
                                                                $departmentRoasterId    = DB::getPdo()->lastInsertId();

                                                                //NEW DEPARTMENT WISE USERS ROASTER CREATE
                                                                foreach ($users as $user) {
                                                                    $new_array = [
                                                                        "user_id"               => $user->id,
                                                                        "work_slot_id"          => $request->input("work_slot_id"),
                                                                        "office_division_id"    => $request->office_division_id,
                                                                        "department_id"         => $request->department_id,
                                                                        "active_from"           => $tomorrow,
                                                                        "end_date"              => date("Y-m-d", strtotime($request->input("end_date"))),
                                                                        "weekly_holidays"       => json_encode($week_value),
                                                                        "created_by"            => Auth::id(),
                                                                        "created_at"            => now(),
                                                                        "department_roaster_id" => $departmentRoasterId
                                                                    ];
                                                                    $roasters[]=$new_array;
                                                                }

                                                                //NEW ROASTER CREATE
                                                                Roaster::insert($roasters);
                                                                //END DEPARTMENT WISE USERS ROASTER CREATE

                                                            } else {
                                                                //DEPARTMENT ROASTER UPDATE
                                                                $roaster->update([
                                                                    'active_from'       => ($new_active_from>$currectDate)? $new_active_from:$active_from,
                                                                    "end_date"          => $new_end_date,
                                                                    "weekly_holidays"   => ($new_weekly_arr)? $new_weekly_arr:[],
                                                                    "is_locked"         => 1, //1=Roaster Locked
                                                                    "updated_at"        => now()
                                                                ]);

                                                                //EMPLOYEE WISE ROASTER UPDATE
                                                                foreach ($departmentWiseRoasters as $key => $departmentWiseRoaster) {
                                                                    $departmentWiseRoaster->update([
                                                                        'active_from'       => ($new_active_from>$currectDate)? $new_active_from:$active_from,
                                                                        "end_date"          => $new_end_date,
                                                                        "weekly_holidays"   => ($new_weekly_arr)? $new_weekly_arr:[],
                                                                        "is_locked"         => 1, //1=Roaster Locked
                                                                        "updated_at"        => now()
                                                                    ]);
                                                                }

                                                            }
                                                        } else {
                                                            //DEPARTMENT ROASTER UPDATE
                                                            $roaster->update([
                                                                "end_date"          => $new_end_date,
                                                                "is_locked"         => 1, //1=Roaster Locked
                                                                "updated_at"        => now()
                                                            ]);

                                                            //EMPLOYEE WISE ROASTER UPDATE
                                                            foreach ($departmentWiseRoasters as $key => $departmentWiseRoaster) {
                                                                $departmentWiseRoaster->update([
                                                                    "end_date"          => $new_end_date,
                                                                    "is_locked"         => 1, //1=Roaster Locked
                                                                    "updated_at"        => now()
                                                                ]);
                                                            }

                                                        }
                                                    }
                                                } else {
                                                    //DEPARTMENT ROASTER UPDATE
                                                    $roaster->update([
                                                        'active_from'       => ($new_active_from>$currectDate)? $new_active_from:$active_from,
                                                        "end_date"          => $new_end_date,
                                                        "weekly_holidays"   => ($new_weekly_arr)? $new_weekly_arr:[],
                                                        "is_locked"         => 1, //1=Roaster Locked
                                                        "updated_at"        => now()
                                                    ]);

                                                    //EMPLOYEE WISE ROASTER UPDATE
                                                    foreach ($departmentWiseRoasters as $key => $departmentWiseRoaster) {
                                                        $departmentWiseRoaster->update([
                                                            'active_from'       => ($new_active_from>$currectDate)? $new_active_from:$active_from,
                                                            "end_date"          => $new_end_date,
                                                            "weekly_holidays"   => ($new_weekly_arr)? $new_weekly_arr:[],
                                                            "is_locked"         => 1, //1=Roaster Locked
                                                            "updated_at"        => now()
                                                        ]);
                                                    }
                                                }
                                            }
                                        }
                                        // END WORK SLOT CHECK

                                        DB::commit();
                                        session()->flash('message', 'Roaster Updated Successfully');
                                        $redirect = redirect()->route('roaster.index');
                                    } else {
                                        session()->flash('type', 'error');
                                        session()->flash('message', 'The end date must be greater than or equal to the start date');
                                        $redirect = redirect()->back();
                                    }
                                } else {
                                    //EXSITING DEPARTMENT ROASTER UPDATE
                                    $roaster->update([
                                        "is_locked"         => 1, //1=Roaster Locked
                                        "weekly_holidays"   => [],
                                    ]);

                                    foreach ($departmentWiseRoasters as $key => $departmentWiseRoaster) {
                                        $departmentWiseRoaster->update([
                                            "is_locked"         => 1, //1=Roaster Locked
                                            "weekly_holidays"   => [],
                                        ]);
                                    }
                                    //END EXSITING DEPARTMENT ROASTER UPDATE


                                    DB::commit();
                                    session()->flash('message', 'Roaster Updated Successfully');
                                    $redirect = redirect()->route('roaster.index');
                                }
                            } else {
                                session()->flash('type', 'error');
                                session()->flash('message', "The end date must be greater than or equal to the start date!");
                                $redirect = redirect()->back();
                            }
                        }
                    }
                } else {
                    session()->flash('type', 'error');
                    session()->flash('message', "You can't update this roaster!");
                    $redirect = redirect()->back();
                }

            } else {
                session()->flash('type', 'error');
                session()->flash('message', 'Please check Active From Date & End Date!');
                $redirect = redirect()->back();
            }

        } catch (Exception $exception) {
            DB::rollBack();

            session()->flash('type', 'error');
            session()->flash('message', $exception->getMessage());
            $redirect = redirect()->back();
        }

        return $redirect;
    }
    //END DEPARTMENT ROASTER UPDATE



    /**
     * @param Roaster $roaster
     * @return RedirectResponse
     */
    //DELETE ROASTER
    public function delete(Roaster $roaster)
    {
        try {
            DB::beginTransaction();
            if($roaster->department_roaster_id > 0){ //DEPARTMENT ROASTER
                $departmentRoaster = DepartmentRoaster::find($roaster->department_roaster_id);

                $roasters = Roaster::where('department_roaster_id', $roaster->department_roaster_id)->get();
                $feedback['status'] = $departmentRoaster->delete();
                foreach($roasters as $roaster){
                    $roaster->delete();
                }

            } else { //INDIVIDUAL ROASTER
                $feedback['status'] = $roaster->delete();
            }
            DB::commit();

        } catch (Exception $exception) {
            $feedback['status'] = false;
        }

        return $feedback;
    }
    //END DELETE ROASTER

    protected function getDepartmentSupervisorIds()
    {
        $divisionSupervisor = DivisionSupervisor::where("supervised_by", auth()->user()->id)->active()->orderByDesc("id")->pluck("office_division_id")->toArray();
        $departmentSupervisor = DepartmentSupervisor::where("supervised_by", auth()->user()->id)->active()->pluck("department_id")->toArray();

        if(count($divisionSupervisor) > 0) {
            $departmentIds = Department::whereIn("office_division_id", $divisionSupervisor)->pluck("id")->toArray();
        } elseif(count($departmentSupervisor) > 0) {
            $departmentIds = $departmentSupervisor;
        } else {
            $departmentIds = [];
        }

        return $departmentIds;
    }

    //START ROASTER LOCK STATUS UPDATE
    public function roasterLockStatusUpdate(Request $request){
        $roasterUnlockStatus        = $request->roaster_unlock_status;
        $roasterId                  = $request->roaster_id;
        $roasterInfo                = Roaster::find($roasterId);
        DB::beginTransaction();

        if($roasterInfo->department_roaster_id > 0){ //DEPARTMENT ROASTER
            $departmentRoaster = DepartmentRoaster::find($roasterInfo->department_roaster_id)->update(['is_locked' => $roasterUnlockStatus]);
            $roasters = Roaster::where('department_roaster_id', $roasterInfo->department_roaster_id)->get();
            foreach($roasters as $roaster){
                $roaster->update(['is_locked' => $roasterUnlockStatus]);
            }
            $data = [
                "status"        => 'success',
                "message"       => "Department Roaster Lock Status Updated Successfully!",
                "roStatus"      => $roasterUnlockStatus
            ];
        } else { //INDIVIDUAL ROASTER
            $roasterInfo->update(['is_locked' => $roasterUnlockStatus]);

            $data = [
                "status"        => 'success',
                "message"       => "Roaster Lock Status Updated Successfully!",
                "roStatus"      => $roasterUnlockStatus
            ];
        }
        DB::commit();
        return $data;

    }
    //END ROASTER LOCK STATUS UPDATE


    //CHECK EXISTING ROASTER DATE
    public function newRoasterDateCheck(Request $request){
        $user_id                    = $request->userId;
        $currectDate                = date('Y-m-d');
        $existRoaster               = Roaster::where('user_id', $user_id)->orderBy('end_date', 'desc')->first();

        $tomorrow                   = new DateTime(date('Y-m-d', strtotime($currectDate)));
        if ($existRoaster) {
            if ($existRoaster->end_date > $currectDate) {
                $endDate                    = new DateTime($existRoaster->end_date);
                $endDayCount                = $tomorrow->diff($endDate)->format('%a')+1;

                // $data['startAllowDateCount']    = $endDayCount;
                $data['startAllowDateCount']    = 1;
                $data['endAllowDateCount']      = ''; //Unlimited
            } else {

                $data['startAllowDateCount']    = 1;
                $data['endAllowDateCount']      = '';
            }
        } else {
            $data['startAllowDateCount']    = 1; //Unlimited
            $data['endAllowDateCount']      = ''; //Unlimited
        }


        return $data;
    }

    //ROASTER APPROVAL STATUS MODAL SHOW
    public function roasterApprovalStatus(Request $request){
        $data['roaster_id']         = $request->roasterId;
        $data['approval_status']    = $request->approvalStatus;
        $data['roasterInfo']        = $roasterInfo = Roaster::find($request->roasterId);
        $data['currentDate']        = $currectDate = date('Y-m-d');
        $data['active_from']        = $active_from = date("Y-m-d", strtotime($roasterInfo->active_from));

        if ($roasterInfo->approval_status==1 && $active_from<=$currectDate) {
            $data['message']            = "You can't update this roaster, Because already approved this roaster!";
        } else {
            $data['message']            = ($request->approvalStatus==0? 'Pending': ($request->approvalStatus==1? 'Approve':'Reject'));
        }

        return view('roaster.approval_status_update_modal', $data);
    }

    public function roasterApprovalStatusUpdate(Request $request){
        $roaster_id         = $request->roaster_id;
        $approval_status    = $request->approval_status;
        $roasterInfo        = Roaster::find($roaster_id);
        $currectDate        = date('Y-m-d');
        $active_from        = date("Y-m-d", strtotime($roasterInfo->active_from));

        if ($roasterInfo->approval_status==1 &&  $active_from<=$currectDate) {
            $data = [
                "status"        => 'error',
                "message"       => "You can't update this roaster, Because already approved this roaster!"
            ];
        } else {
            DB::beginTransaction();
            $inputData = [
                "approval_status"   => $approval_status,
                "approved_by"       => Auth::id(),
                "approved_date"     => now()
            ];
            ($approval_status==2)? $inputData['remarks'] = $request->remarks: '';

            if($roasterInfo->department_roaster_id > 0){ //DEPARTMENT ROASTER
                $departmentRoaster = DepartmentRoaster::find($roasterInfo->department_roaster_id)->update($inputData);
                $roasters = Roaster::where('department_roaster_id', $roasterInfo->department_roaster_id)->get();
                foreach($roasters as $roaster){
                    $roaster->update($inputData);
                }
            } else { //INDIVIDUAL ROASTER
                $roasterInfo->update($inputData);
            }

            $data = [
                "status"        => 'success',
                "message"       => "Roaster approval status updated successfully!"
            ];
            DB::commit();
        }

        return $data;


    }
}
