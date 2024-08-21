<?php

namespace App\Http\Controllers;

use App\Models\Department;
use App\Models\OfficeDivision;
use App\Models\Roster;
use App\Models\User;
use App\Models\WorkSlot;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

use Exception;
use Carbon\Carbon;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class RosterController extends Controller
{


    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
        // rgb(128, 193, 255). rgb(255, 128, 157), rgb(221, 128, 255), rgb(128, 142, 255), rgb(193, 128, 255), rgb(128, 138, 255), rgb(64 108 65), rgb(133 118 8)
    }

    /**
     * @return Application|Factory|View
     */
    public function index(Request $request)
    {
        $validator = Validator::make($request->only(['type','user_id','department_id', 'office_division_id']), [
            'type' => 'required|in:employee,department',
            'user_id' => 'nullable|integer',
            'department_id' => 'nullable|integer',
            'office_division_id' => 'nullable|integer',
            'datepicker' => 'nullable|date',
        ]);

        if($validator->fails() && !request()->ajax()) {
            abort(404);
        } else if($validator->fails()){
            return response()->json(['status' => false, 'message' => 'Something went wrong!'], 412);
        }

        $data = array(
            "officeDivisions" => OfficeDivision::whereIn('id',FilterController::getDivisionIds())->get(),
            'type' => $request->type
        );

        $type_key = "";
        $type_values = [];
        $current_date = Carbon::now()->toDateString();

        if($request->type === 'employee') {
            $type = Roster::EMPLOYEE_TYPE;
            $type_key = "user_id";
            $type_values = FilterController::getEmployeeIds();
            if ($request->division_id && $request->division_id > 0) {
                $type_values = User::whereIn('id', FilterController::getEmployeeIds(1, "division", $request->division_id))->pluck('id');
            }
            if($request->department_id && $request->department_id > 0) {
                $type_values = User::whereIn('id', FilterController::getEmployeeIds(1, "department", $request->department_id))->pluck('id');
            }
        } else {
            $type = Roster::DEPARTMENT_TYPE;
            $type_key = "department_id";
            $type_values = FilterController::getDepartmentIds();
            if ($request->division_id && $request->division_id > 0) {
                $type_values = Department::whereIn('id', FilterController::getDepartmentIds())->where('office_division_id', '=', $request->division_id)->pluck('id');
            }
            if($request->department_id && $request->department_id > 0) {
                $type_values = Department::whereIn('id', FilterController::getDepartmentIds())->where('id', '=', $request->department_id)->pluck('id');
            }
        }

        if (request()->ajax()) {

            if(auth()->user()->can('Roster Current Date Modification')) {
                $condition = sprintf("rosters.active_date >= '%s'", $current_date);
            } else {
                $condition = sprintf("rosters.active_date > '%s'", $current_date);
            }

            $items = Roster::with(["user","officeDivision", "department"])->select(
                'rosters.*',
                DB::raw("GROUP_CONCAT( DISTINCT CASE WHEN {$condition} THEN rosters.is_locked END ) as lock_status"),
                DB::raw("GROUP_CONCAT( DISTINCT CASE WHEN {$condition} THEN rosters.status END ) as approvel_status"),
                DB::raw("GROUP_CONCAT( CASE WHEN {$condition} AND rosters.status = 1 THEN rosters.status END ) as approve_count"),
                DB::raw("GROUP_CONCAT( CASE WHEN {$condition} AND rosters.status = 0 THEN rosters.status END ) as pending_count"),
                DB::raw("GROUP_CONCAT( CASE WHEN {$condition} AND rosters.status = 2 THEN rosters.status END ) as reject_count"),
                DB::raw("GROUP_CONCAT( CASE WHEN {$condition} AND rosters.is_locked = 1 THEN rosters.is_locked END ) as lock_count"),
                DB::raw("GROUP_CONCAT( CASE WHEN {$condition} AND rosters.is_locked = 0 THEN rosters.is_locked END ) as unlock_count"),
                DB::raw("DATE_FORMAT(rosters.active_date, '%Y-%m') as month_of_year"),
                DB::raw("DATE_FORMAT(rosters.active_date, '%M %Y') as month"),
                DB::raw("DATE_SUB(rosters.active_date, INTERVAL DAYOFMONTH(rosters.active_date)-1 DAY) as start"),
                DB::raw("LAST_DAY(rosters.active_date) as end"),
            );

            $items =  $items->leftJoin('users as uc', 'uc.id', '=', 'rosters.created_by');
            $items =  $items->leftJoin('users as ua', 'ua.id', '=', 'rosters.approved_by');
            $items =  $items->where('rosters.type', '=', $type);
            $items = $items->orderByRaw("IF(MONTH(rosters.active_date) < MONTH(NOW()), MONTH(rosters.active_date) + 12, MONTH(rosters.active_date)), MONTH(rosters.active_date)");

            $items->whereIn("rosters.{$type_key}",  $type_values);
            if ($request->user_id && $request->user_id > 0) $items->where('rosters.user_id',  $request->user_id);
            if ($request->datepicker && $request->datepicker > 0) $items->whereRaw("DATE_FORMAT(rosters.active_date, '%Y-%m')='{$request->datepicker}'");

            switch ($type) {
                case Roster::EMPLOYEE_TYPE: // employee
                    $items = $items->groupBy("rosters.type", 'rosters.user_id', DB::raw('MONTH(rosters.active_date)'));
                    break;
                case Roster::DEPARTMENT_TYPE: // department
                    $items = $items->groupBy("rosters.type", "rosters.department_id", DB::raw('MONTH(rosters.active_date)'));
                    break;
            }

            $dataTable = datatables($items);

            if($type == Roster::EMPLOYEE_TYPE) {
                $dataTable = $dataTable->editColumn('user.designation', function ($item) { return $item->user->currentPromotion->designation->title; });
                $dataTable = $dataTable->editColumn('user.name', function ($item) { return $item->user->name . ' -- ' . $item->user->fingerprint_no; });
            }
            $dataTable = $dataTable->editColumn('office_division', function ($item) {
                return $item->type == Roster::EMPLOYEE_TYPE ? $item->user->currentPromotion->officeDivision->name : $item->department->officeDivision->name;
            });
            $dataTable = $dataTable->editColumn('department', function ($item) {
                return $item->type == Roster::EMPLOYEE_TYPE ? $item->user->currentPromotion->department->name : $item->department->name;
            });
            $dataTable = $dataTable->addColumn('action', function ($item) {
                return '<div class="roster-list-actions-wrap">'
                    .$this->getActionInfoHtml($item)
                    .$this->getApprovalStatusHtml($item)
                    .$this->getLockStatusHtml($item)
                    .$this->getActionCalendarInfoHtml($item)
                .'</div>';
            })->setRowAttr([
                'data-employee' => function ($item) {return $item->type == 1 ? $item->user->name .'--'. $item->user->fingerprint_no: '';},
                'data-department' => function ($item) {
                    return $item->type == 1 ? $item->user->currentPromotion->department->name : $item->department->name;
                },
                'data-month' => function ($item) {return $item->month;},
            ]);
            $dataTable = $dataTable->make(true);
            return $dataTable;
        }

        return view('roster.index', compact('data'));
    }

    public function getRosterListGroupData(Request $request)
    {
        $validator = Validator::make($request->only(['type','user_id', 'department_id', 'start']), [
            'type' => 'required|in:employee,department',
            'start' => 'required|date',
            'user_id' => 'nullable|integer',
            'department_id' => 'nullable|integer',
        ]);
        if($validator->fails()) return response()->json(['status' => false, 'message' => 'Something went wrong!'], 412);

        $start = Carbon::parse($request->start)->startOfMonth()->startOfMonth()->toDateString();
        $end = Carbon::parse($request->start)->endOfMonth()->endOfMonth()->toDateString();

        $rosters = Roster::from("rosters as r")->select(
            DB::raw("MIN(r.active_date) as start"),
            DB::raw("MAX(r.active_date) as end"),
            DB::raw("IF(r.status = 1, 'Approved', IF(r.status = 2, 'Rejected', 'Pending')) AS approve_status"),
            DB::raw("IF(r.is_locked = 1, 'Locked', 'Unlocked') AS lock_status"),
            DB::raw("IF(r.created_by IS NULL, CONCAT(IF(r.created_at IS NULL, '', DATE_FORMAT(r.created_at, ' @%d %b %h:%i%p'))), CONCAT(uc.name, IF(r.created_at IS NULL, '', DATE_FORMAT(r.created_at, ' @%d %b %h:%i%p')) )) AS created_by"),
            DB::raw("IF(r.approved_by IS NULL, '', CONCAT( ua.name, IF(r.approved_date IS NULL, '', DATE_FORMAT(r.approved_date, ' @%d %b %h:%i%p'))) ) AS approved_by"),
        );
        $rosters =  $rosters->leftJoin('users as uc', 'uc.id', '=', 'r.created_by');
        $rosters =  $rosters->leftJoin('users as ua', 'ua.id', '=', 'r.approved_by');
        if($request->type === 'employee') {
            $rosters = $rosters->where('r.type', '=', Roster::EMPLOYEE_TYPE);
            $rosters = $rosters->where('r.user_id', '=', $request->user_id);
        } else {
            $rosters = $rosters->where('r.type', '=', Roster::DEPARTMENT_TYPE);
            $rosters = $rosters->where('r.department_id', '=', $request->department_id);
        }
        $rosters =  $rosters->whereBetween('r.active_date', [$start, $end]);
        $rosters = $rosters->groupBy("r.created_at")->orderBy('r.active_date')->get();
        return $rosters;
    }

    /**
     * @return Factory|View
     */

    public function create(Request $request)
    {
        $validator = Validator::make($request->only(['type','user_id','department_id']), [
            'type' => 'required|in:employee,department',
            'user_id' => 'nullable|integer',
            'department_id' => 'nullable|integer',
        ]);
        if($validator->fails()) {
            return redirect()->route("rosters.index", ['type' => $request->type])->withErrors('Something went wrong!');
        }

        $data = array(
            "officeDivisions" => OfficeDivision::whereIn('id', FilterController::getDivisionIds())->get(),
            "office_division_id" => 0,
            "department_id" => 0,
            "type" => $request->type,
        );
        return view("roster.create", compact('data'));
    }

    /**
     * @return Factory|View
     */
    public function createForm(Request $r)
    {
        if($r->method() == 'POST') {
            $request = $r;
        } else {
            try {
                $request = Crypt::decrypt($r->_id);
            } catch (Exception $th) {
                abort(404);
            }
            $request = new Request($request);
        }

        $validator = Validator::make($request->only(['type','user_id','department_id','start','end']), [
            'type' => 'required|in:employee,department',
            'user_id' => 'nullable|integer',
            'department_id' => 'nullable|integer',
            'start' => 'nullable|date',
            'end' => 'nullable|date',
        ]);
        if($validator->fails() || (!$request->department_id && !$request->user_id)) {
            return redirect()->route("rosters.index", ['type' => 'employee'])->withErrors('Something went wrong!');
        }

        $calenderSettings = [];

        if (auth()->user()->canany(['Roster Create', 'Roster Update'])) {
            $calenderSettings['selectable'] = 1;
            $calenderSettings['selectMirror'] = 0;
            $calenderSettings['selectOverlap'] = 1;
        } else {
            $calenderSettings['selectable'] = 0;
            $calenderSettings['selectMirror'] = 0;
            $calenderSettings['selectOverlap'] = 0;
        }
        if(auth()->user()->can('Roster Current Date Modification')) $calenderSettings['currentDateAccess'] = 1;
        else $calenderSettings['currentDateAccess'] = 0;


        $data['type'] = $request->type;
        if($request->type == 'employee' && $request->user_id ) {
            $data["user"] = User::find($request->user_id);
            if(!$data["user"]) {
                session()->flash('type', 'error');
                session()->flash('message', 'Something went wrong!');
                return redirect()->route("rosters.index", ['type' => 'employee']);
            }
        } else if($request->type == 'department' && $request->department_id ) {
            $data["department"] = Department::find($request->department_id);
            if(!$data["department"]) {
                session()->flash('type', 'error');
                session()->flash('message', 'Something went wrong!');
                return redirect()->route("rosters.index", ['type' => 'department']);
            }
        } else {

            return redirect()->route("rosters.index", ['type' => 'employee'])->withErrors('Something went wrong!');
        }

        $data["workSlots"] = WorkSlot::latest()->get();
        $data["calenderSettings"] = $calenderSettings;

        if($request->start && $request->end) {
            $data["start"] = $request->start;
            $data["end"] = $request->end;
        } else if($request->start) {
            $data["start"] = $request->start;
            $data["end"] = $request->start;
        } else if($request->end) {
            $data["start"] = $request->end;
            $data["end"] = $request->end;
        }
        return view('roster.create-calender', compact('data'));
    }

    public function getDaysStatus(Request $request)
    {
        $validator = Validator::make($request->only(['type','user_id','department_id', 'start', 'end']), [
            'type' => 'nullable|in:1,2',
            'user_id' => 'nullable|integer',
            'department_id' => 'nullable|integer',
            'start' => 'nullable|date',
            'end' => 'nullable|date',
        ]);
        if($validator->fails()) return response()->json(['status' => false, 'message' => 'Something went wrong!'], 412);

        $lock_context = 'follow_days_records'; // two type of lock context "followDaysRecords" and "dontFollowDaysRecords"
        $current_date_access_context = 0; // two type of current date access context 0 and 1

        if(auth()->user()->canany(['Roster Unlock', 'Roster Approve'])) $lock_context = 'dont_follow_days_records';

        $current_month_start = Carbon::now()->startOfMonth()->startOfDay()->toDateString();
        $current_month_end = Carbon::now()->endOfMonth()->endOfDay()->toDateString();

        $days = Roster::select('active_date', 'is_locked', 'is_weekly_holiday', 'status');
        $days = $days->where('type', '=', $request->type);

        if( $request->department_id && $request->type && $request->type == 2) {
            $days = $days->where('department_id', '=', $request->department_id);
        } else if($request->user_id && $request->type && $request->type == 1) {
            $days = $days->where('user_id', '=', $request->user_id);
        } else {
            return response()->json( ['data' => [], 'lockContext' => $lock_context, 'status' => true] );
        }

        if($request->start) {
            $days =  $days->whereBetween('active_date', [Carbon::parse($request->start)->startOfMonth()->startOfDay()->toDateString(), Carbon::parse($request->start)->endOfMonth()->endOfDay()->toDateString()]);
        } elseif($request->end){
            $days =  $days->whereBetween('active_date', [Carbon::parse($request->end)->startOfMonth()->startOfDay()->toDateString(), Carbon::parse($request->end)->endOfMonth()->endOfDay()->toDateString()]);
        } else {
            $days =  $days->whereBetween('active_date', [$current_month_start, $current_month_end]);
        }
        $days = $days->groupBy("active_date")->get();

        return response()->json( ['data' => $days, 'lockContext' => $lock_context, 'currentDateAccess' => $current_date_access_context, 'status' => true] );

    }

    public function getRosters(Request $request)
    {
        $validator = Validator::make($request->only(['type','user_id','department_id', 'start', 'end', 'ids']), [
            'type' => 'nullable|in:1,2',
            'ids' => 'nullable|string',
            'user_id' => 'nullable|integer',
            'department_id' => 'nullable|integer',
            'start' => 'nullable|date',
            'end' => 'nullable|date',
        ]);
        if($validator->fails()) return response()->json(['status' => false, 'message' => 'Something went wrong!'], 412);

        $current_month_start = Carbon::now()->startOfMonth()->startOfDay()->toDateString();
        $current_month_end = Carbon::now()->endOfMonth()->endOfDay()->toDateString();

        $rosters = Roster::from("rosters as r")->with(["user","officeDivision", "department", "workSlot"])->select('r.*');

        if($request->r_type && $request->r_type == 'events' && $request->department_id && $request->type && $request->type == 2) {
            $rosters = $rosters->addSelect(
                DB::raw("COUNT(id) as sub_roster_count"),
                DB::raw("IF( COUNT(id) > 1, CONCAT(GROUP_CONCAT(DISTINCT r.id SEPARATOR '|')), r.id) as id"),
                DB::raw("MIN(r.active_date) as start"),
                DB::raw("MAX(r.active_date) as end")
            );
            $rosters = $rosters->where('r.type', '=', $request->type);
            $rosters = $rosters->where('r.department_id', '=', $request->department_id);
            $rosters = $rosters->groupBy("r.created_at");
        } else if($request->r_type && $request->r_type == 'events' && $request->user_id && $request->type && $request->type == 1) {
            $rosters = $rosters->addSelect(
                DB::raw("COUNT(id) as sub_roster_count"),
                DB::raw("IF( COUNT(id) > 1, CONCAT(GROUP_CONCAT(DISTINCT r.id SEPARATOR '|')), r.id) as id"),
                DB::raw("MIN(r.active_date) as start"),
                DB::raw("MAX(r.active_date) as end")
            );
            $rosters = $rosters->where('r.type', '=', $request->type);
            $rosters = $rosters->where('r.user_id', '=', $request->user_id);
            $rosters = $rosters->groupBy("r.created_at");
        } else if ($request->r_type && $request->r_type == 'overlap_events' && $request->department_id && $request->type && $request->type == 2) {
            $rosters = $rosters->whereIn('active_date', function($query) use ($request){
                $query->select(DB::raw("min(active_date)"))->from(with(new Roster)->getTable())->whereBetween('active_date', [$request->start, $request->end]);
            });
            $rosters = $rosters->where('r.type', '=', $request->type);
            $rosters = $rosters->where('r.department_id', '=', $request->department_id);
            $rosters = $rosters->groupBy("r.department_id");
        } else if ($request->r_type && $request->r_type == 'overlap_events' && $request->user_id && $request->type && $request->type == 1) {
            $rosters = $rosters->whereIn('active_date', function($query) use ($request){
                $query->select(DB::raw("min(active_date)"))->from('rosters')->whereBetween('active_date', [$request->start, $request->end]);
            });
            $rosters = $rosters->where('r.type', '=', $request->type);
            $rosters = $rosters->where('r.user_id', '=', $request->user_id);
            $rosters = $rosters->groupBy("r.user_id");
        } else if ($request->ids) {
            $request->ids = json_decode($request->ids);
            $rosters = $rosters->whereIn('r.id', $request->ids);
        }

        if($request->start) {
            $rosters =  $rosters->whereBetween('r.active_date', [Carbon::parse($request->start)->startOfMonth()->startOfDay()->toDateString(), Carbon::parse($request->start)->endOfMonth()->endOfDay()->toDateString()]);
        } elseif($request->end){
            $rosters =  $rosters->whereBetween('r.active_date', [Carbon::parse($request->end)->startOfMonth()->startOfDay()->toDateString(), Carbon::parse($request->end)->endOfMonth()->endOfDay()->toDateString()]);
        } else {
            $rosters =  $rosters->whereBetween('r.active_date', [$current_month_start, $current_month_end]);
        }

        $rosters = $rosters->orderBy("r.active_date", "ASC");
        $rosters = $rosters->get();
        return response()->json( ['data' => $rosters, 'colors' => $this->getWorkSlotColors(), 'status' => true] );
    }

    public function getWorkSlotColors()
    {
        return [
            '1'=> 'rgb(128, 193, 255)',
            '3'=> 'rgb(255, 128, 157)',
            '8'=> 'rgb(153 113 109)',
            '9'=> 'rgb(128, 142, 255)',
            '13'=> 'rgb(193, 128, 255)',
            '14'=> 'rgb(16, 112, 116)',
            '15'=> 'rgb(133, 118, 8)',
            '17'=> 'rgba(87, 155, 177, 0.8)',
            '20'=> 'rgba(94, 153, 68, 0.8)',
        ];
    }


    /**
     * @param RequestRoaster $request
     * @return RedirectResponse
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->only(['type','user_id','department_id','work_slot_id','weekly_holidays','start','end', 'status', 'is_locked']), [
            'type' => 'required|in:1,2',
            'user_id' => 'nullable|integer',
            'department_id' => 'nullable|integer',
            'work_slot_id' => 'nullable|integer',
            'start' => 'required|date',
            'end' => 'required|date',
            'weekly_holidays' => 'nullable',
            'status' => 'nullable',
            'is_locked' => 'nullable'
        ]);
        if($validator->fails()) return response()->json(['status' => false, 'message' => 'Something went wrong!'], 412);

        $_id = [];
        if($request->type == Roster::EMPLOYEE_TYPE) {
            $_id['type'] ='employee';
            $_id['user_id'] = $request->user_id;
        } else {
            $_id['type'] ='department';
            $_id['department_id'] = $request->department_id;
        }
        $_id['start'] = $request->start;
        $_id['end'] = $request->end;

        $validator = Validator::make($request->only(['type','user_id','department_id','start','end']), [
            'type' => 'required|in:employee,department',
            'user_id' => 'nullable|integer',
            'department_id' => 'nullable|integer',
            'start' => 'nullable|date',
            'end' => 'nullable|date',
        ]);


        $request->start = Carbon::parse($request->start);
        $request->end = Carbon::parse($request->end);
        $current_at = Carbon::now();

        try {
            // delete
            $rostes = Roster::where('type', '=', $request->type);
            $rostes = $rostes->where([['active_date', '>=', $request->start->toDateString()],['active_date', '<', $request->end->toDateString()]]);
            if($request->type == 1) {
                $rostes = $rostes->where('user_id', '=', $request->user_id);
            } else {
                $rostes = $rostes->where('department_id', '=', $request->department_id);
            }
            $rostes = $rostes->delete();
            $approve_by = $request->status == 1 ? auth()->user()->id : null;
            $approve_at = $request->status == 1 ? $current_at->toDateTimeString() : null;

            // insert
            $data = [];
            while ($request->start->lt($request->end)) {
                $active_date = $request->start->toDateString();

                $data[$active_date]['uuid'] = Str::uuid();
                $data[$active_date]['type'] = $request->type;
                $data[$active_date]['company_id'] = 1;
                $data[$active_date]['work_slot_id'] = $request->work_slot_id;
                $data[$active_date]['is_weekly_holiday'] = (isset($request->weekly_holidays) && is_array($request->weekly_holidays) && in_array( strtolower($request->start->format('D') ) , $request->weekly_holidays ) ) ? 1 : 0;
                $data[$active_date]['active_date'] = $active_date;
                $data[$active_date]['status'] = $request->status ?? 0;
                $data[$active_date]['is_locked'] = $request->is_locked ?? 1;
                $data[$active_date]['created_by'] = auth()->user()->id;
                $data[$active_date]['approved_by'] = $approve_by;
                $data[$active_date]['approved_date'] = $approve_at;
                $data[$active_date]['created_at'] = $current_at->toDateTimeString();
                $data[$active_date]['updated_at'] = $current_at->toDateTimeString();
                if($request->type == 1) {
                    $data[$active_date]['user_id'] = $request->user_id;
                    $data[$active_date]['department_id'] = NULL;
                } else {
                    $data[$active_date]['user_id'] = NULL;
                    $data[$active_date]['department_id'] = $request->department_id;
                }

                $request->start->addDay();
            }
            Roster::insert($data);
        } catch (Exception $ex) {
            Log::info($ex->getMessage());
            return response()->json( ['status' => false, 'message' => 'Something went wrong!', '_id' => Crypt::encrypt($_id)], 412 );
        }
        return response()->json( ['status' => true, 'message' => 'Roster created successfully!', '_id' => Crypt::encrypt($_id)] );
    }

    public function update(Request $request)
    {
        $validator = Validator::make($request->only(['type','user_id','department_id','work_slot_id','weekly_holidays','start','end','status','is_locked','selected_days']), [
            'type' => 'required|in:1,2',
            'start' => 'required|date',
            'end' => 'required|date',
            'user_id' => 'nullable|integer',
            'department_id' => 'nullable|integer',
            'work_slot_id' => 'nullable|integer',
            'weekly_holidays' => 'nullable',
            'status' => 'nullable|in:0,1,2',
            'is_locked' => 'nullable|in:0,1',
            'selected_days' => 'nullable',
            'is_weekly' => 'nullable|in:0,1',
        ]);

        if($validator->fails()) return response()->json(['status' => false, 'message' => 'Something went wrong!'], 412);

        $current_at = Carbon::now();
        $data = $days = [];

        if(isset($request->status) && $request->status > -1) $data['status'] = $request->status;
        if(isset($request->is_locked) && $request->is_locked > -1) $data['is_locked'] = $request->is_locked;

        if(isset($request->status) && $request->status == 1) $data['approved_by'] = auth()->user()->id;
        if(isset($request->status) && $request->status == 1) $data['approved_date'] = $current_at->toDateTimeString();

        if(empty($data)) return response()->json(['status' => false, 'message' => 'Accepted data not given!'], 412);

        $data['updated_at'] = $current_at->toDateTimeString();
        $data['updated_by'] = auth()->user()->id;

        if($request->is_weekly && $request->is_weekly == 1) {
            $request->selected_days = json_decode($request->selected_days);
            if(!$request->selected_days || !is_array($request->selected_days) || empty($request->selected_days) ) {
                return response()->json(['status' => false, 'message' => 'Any week of this month was not selected or another month week was selected!'], 412);
            } else if ($request->selected_days && is_array($request->selected_days)) {
                $days = $request->selected_days;
            }
        }

        try {
            $rosters = Roster::whereBetween('active_date', [$request->start, $request->end])->where('type', '=', $request->type);

            if(auth()->user()->can('Roster Current Date Modification')) {
                $rosters =  $rosters->where('active_date', '>=', $current_at->toDateString());
            } else {
                $rosters =  $rosters->where('active_date', '>', $current_at->toDateString());
            }

            if(!empty($days)) {
                $rosters =  $rosters->whereIn('active_date', $days);
            }

            if($request->type == 1) {
                $rosters =  $rosters->where('user_id', '=', $request->user_id);
            } else {
                $rosters =  $rosters->where('department_id', '=', $request->department_id);
            }
            $rosters =  $rosters->update($data);
        } catch (Exception $e) {
            return response()->json( ['status' => false, 'message' => 'Something went wrong!'], 412 );
        }
        return response()->json( ['status' => true, 'message' => 'Roster updated successfully!'] );

    }

    protected function getApprovalStatusHtml(object $item)
    {
        if(!auth()->user()->can('Roster Approve')) return '';

        if($item->month_of_year < Carbon::now()->format('Y-m')) return "";

        $input = [];
        $input['approve'] = "<label class='btn btn-outline-secondary btn-bysl bysl-active' title='Approve'>
                <input type='radio' name='status' value='1' id='status-active' autocomplete='1'><i class='fa fa-check'></i>
                <span class='status-notify'>". ($item->approve_count != null ? count(explode(',', $item->approve_count)) : 0) ."</span>
            </label>";
        $input['pending'] = "<label class='btn btn-outline-secondary btn-bysl bysl-pending' title='Pending'>
            <input type='radio' name='status' value='0' id='status-pending' autocomplete='0'><i class='fas fa-dot-circle'></i>
            <span class='status-notify'>". ($item->pending_count != null ? count(explode(',', $item->pending_count)) : 0) ."</span>
        </label>";
        $input['reject'] = "<label class='btn btn-outline-secondary btn-bysl bysl-reject' title='Reject'>
            <input type='radio' name='status' value='2' id='status-cancle' autocomplete='2'><i class='fa fa-times'></i>
            <span class='status-notify'>". ($item->reject_count != null ? count(explode(',', $item->reject_count)) : 0) ."</span>
        </label>";

        $hidden_input = [];
        $hidden_input['type'] = "<input type='hidden' name='type' value='{$item->type}'/>";
        if($item->type == 1) {
            $hidden_input['user_id'] = "<input type='hidden' name='user_id' value='{$item->user_id}'/>";
        } else {
            $hidden_input['department_id'] = "<input type='hidden' name='department_id' value='{$item->department_id}'/>";
        }
        $hidden_input['status'] = "<input type='hidden' name='status' value=''/>";
        $hidden_input['prev_status'] = "<input type='hidden' name='prev_status' value=''/>";
        $hidden_input['start'] = "<input type='hidden' name='start' value='{$item->start}'/>";
        $hidden_input['end'] = "<input type='hidden' name='end' value='{$item->end}'/>";

        $item->approvel_status = $item->approvel_status !== null ? explode(',', $item->approvel_status) : [];

        if(count($item->approvel_status) == 1) {
            $hidden_input['prev_status'] = "<input type='hidden' name='prev_status' value='{$item->approvel_status[0]}'/>";
            $hidden_input['status'] = "<input type='hidden' name='status' value='{$item->approvel_status[0]}'/>";
            switch ($item->approvel_status[0]) {
                case 0:
                    $input['pending'] = "<label class='btn btn-outline-secondary btn-bysl bysl-pending active' title='Pending'>
                        <input type='radio' name='status' value='0' id='status-pending' autocomplete='0'><i class='fas fa-dot-circle'></i>
                        <span class='status-notify'>". ($item->pending_count != null ? count(explode(',', $item->pending_count)) : 0) ."</span>
                    </label>";
                    break;
                case 1:
                    $input['approve'] = "<label class='btn btn-outline-secondary btn-bysl bysl-active active' title='Approve'>
                        <input type='radio' name='status' value='1' id='status-active' autocomplete='1'><i class='fa fa-check'></i>
                        <span class='status-notify'>". ($item->approve_count != null ? count(explode(',', $item->approve_count)) : 0) ."</span>
                    </label>";
                    break;
                case 2:
                    $input['reject'] = "<label class='btn btn-outline-secondary btn-bysl bysl-reject active' title='Reject'>
                        <input type='radio' name='status' value='2' id='status-cancle' autocomplete='2'><i class='fa fa-times'></i>
                        <span class='status-notify'>". ($item->reject_count != null ? count(explode(',', $item->reject_count)) : 0) ."</span>
                    </label>";
                    break;

            }
        }

        $model_ontent = "<div class='modal-body'>
                <div class='modal-title' id='roster-list-approve-status-title'></div>
                <div class='btn-wrap'>
                    <button type='button' class='btn btn-secondary btn-roster-approve-close' data-dismiss='modal'>Close</button>
                    <button type='submit' class='btn btn-primary btn-roster-approve-submit'>Submit</button>
                </div>
            </div>";

        $model = "<div id='roster-list-approve-status-modal' class='modal fade roster-list-approve-status-modal' tabindex='-1' role='dialog' data-keyboard='false' data-backdrop='static' aria-labelledby='rosterModalCreate' aria-hidden='true'>
            <div class='modal-dialog modal-md' role='document'>
                <div class='modal-content'>
                    <form name='frm-roster-list-approve-status' id='frm-roster-list-approve-status' action='".route('rosters.update')."' method='POST'>"
                        .implode('', $hidden_input). $model_ontent
                    ."</form>
                </div>
            </div>
        </div>";

        return "<div class='btn-group btn-group-toggle btn-bysl-toggle btn-bysl-approve-group' data-toggle='buttons'>".implode('', $input). $model. "</div>";
    }

    protected function getLockStatusHtml(object $item)
    {
        if($item->month_of_year < Carbon::now()->format('Y-m')) return "";
        if(!auth()->user()->can('Roster Unlock')) return '';

        $input = [];
        $input['lock'] = "<label class='btn btn-outline-secondary btn-bysl bysl-active' title='Lock'>
            <input type='radio' name='is_locked' id='locked' value='1' autocomplete='1'><i class='fas fa-lock'></i>
            <span class='status-notify'>". ($item->lock_count != null ? count(explode(',', $item->lock_count)) : 0) ."</span>
        </label>";
        $input['unlock'] = "<label class='btn btn-outline-secondary btn-bysl bysl-reject' title='Unlock'>
            <input type='radio' name='is_locked' id='lock-free' value='0' autocomplete='0'><i class='fas fa-lock-open'></i>
            <span class='status-notify'>". ($item->unlock_count != null ? count(explode(',', $item->unlock_count)) : 0) ."</span>
        </label>";

        $hidden_input = [];
        $hidden_input['type'] = "<input type='hidden' name='type' value='{$item->type}'/>";
        if($item->type == 1) {
            $hidden_input['user_id'] = "<input type='hidden' name='user_id' value='{$item->user_id}'/>";
        } else {
            $hidden_input['department_id'] = "<input type='hidden' name='department_id' value='{$item->department_id}'/>";
        }
        $hidden_input['is_locked'] = "<input type='hidden' name='is_locked' value=''/>";
        $hidden_input['prev_is_locked'] = "<input type='hidden' name='prev_is_locked' value=''/>";
        $hidden_input['start'] = "<input type='hidden' name='start' value='{$item->start}'/>";
        $hidden_input['end'] = "<input type='hidden' name='end' value='{$item->end}'/>";

        $item->lock_status = $item->lock_status !== null ? explode(',', $item->lock_status) : [];
        if(count($item->lock_status) == 1) {
            $hidden_input['prev_is_locked'] = "<input type='hidden' name='prev_is_locked' value='{$item->lock_status[0]}'/>";
            $hidden_input['is_locked'] = "<input type='hidden' name='is_locked' value='{$item->lock_status[0]}'/>";
            switch ($item->lock_status[0]) {
                case 0:
                    $input['unlock'] = "<label class='btn btn-outline-secondary btn-bysl bysl-reject active' title='Unlock'>
                        <input type='radio' name='is_locked' id='lock-free' value='0' autocomplete='0'><i class='fas fa-lock-open'></i>
                        <span class='status-notify'>". ($item->unlock_count != null ? count(explode(',', $item->unlock_count)) : 0) ."</span>
                    </label>";
                    break;
                case 1:
                    $input['lock'] = "<label class='btn btn-outline-secondary btn-bysl bysl-active active' title='Lock'>
                        <input type='radio' name='is_locked' id='locked' value='1' autocomplete='1'><i class='fas fa-lock'></i>
                        <span class='status-notify'>". ($item->lock_count != null ? count(explode(',', $item->lock_count)) : 0) ."</span>
                    </label>";
                    break;

            }
        }

        $model_ontent = "<div class='modal-body'>
            <div class='modal-title' id='roster-list-lock-status-title'></div>
            <div class='btn-wrap'>
                <button type='button' class='btn btn-secondary btn-roster-lock-close' data-dismiss='modal'>Close</button>
                <button type='submit' class='btn btn-primary btn-roster-lock-submit'>Submit</button>
            </div>
        </div>";

        $model = "<div id='roster-list-lock-status-modal' class='modal fade roster-list-lock-status-modal' tabindex='-1' role='dialog' data-keyboard='false' data-backdrop='static' aria-labelledby='rosterModalCreate' aria-hidden='true'>
            <div class='modal-dialog modal-md' role='document'>
                <div class='modal-content'>
                    <form name='frm-roster-list-lock-status' id='frm-roster-list-lock-status' action='".route('rosters.update')."' method='POST'>"
                        .implode('', $hidden_input). $model_ontent
                    ."</form>
                </div>
            </div>
        </div>";

        return "<div class='btn-group btn-group-toggle btn-bysl-toggle btn-bysl-lock-group' data-toggle='buttons'>".implode('', $input). $model ."</div>";
    }

    protected function getActionInfoHtml(object $item)
    {
        $queryString = [];
        if($item->type == Roster::EMPLOYEE_TYPE) {
            $queryString['type'] ='employee';
            $queryString['user_id'] = $item->user_id;
        } else {
            $queryString['type'] ='department';
            $queryString['department_id'] = $item->department_id;
        }
        $queryString['start'] = $item->start;
        $queryString['end'] = $item->end;
        return sprintf('<a class="roster-list-info" data-rowid="%s" href="%s" title="Calendar Details"><i class="fa fa-calendar-alt" style="color:#014891d6;"></i></a>', $item->id, route('rosters.create-form', ['_id' => Crypt::encrypt($queryString)]));
    }

    protected function getActionCalendarInfoHtml(object $item)
    {
        $queryString = [];
        if($item->type == 1) {
            $queryString['type'] ='employee';
            $queryString['user_id'] = $item->user_id;
        } else {
            $queryString['type'] ='department';
            $queryString['department_id'] = $item->department_id;
        }
        $queryString['start'] = $item->start;
        return sprintf("<a class='roster-calendar-info' data-rowid='%s' href='%s'><i class='fa fa-info' aria-hidden='true'></i></a>", $item->id, route('rosters.info', $queryString));
    }



}
