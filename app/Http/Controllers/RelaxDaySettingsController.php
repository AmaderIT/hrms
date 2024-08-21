<?php

namespace App\Http\Controllers;

use App\Models\Department;
use Exception;
use Carbon\Carbon;
use App\Models\RelaxDaySetting;
use Illuminate\Http\Request;
use App\Models\RelaxDay;
use App\Models\AssignRelaxDay;
use App\Models\OfficeDivision;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;

class RelaxDaySettingsController extends Controller
{
    protected $week_range = null;

    protected $month_range = null;

    protected $week_limit = null;

    protected $start_week = null;

    protected $end_week = null;

    public function __construct()
    {
        // how manay week will count
        $this->week_limit = RelaxDaySetting::FUTURE_WEEK_LIMIT;

        // start week count previous one week before
        $this->start_week = Carbon::now()->subWeek(1)->startOfWeek()->toDateString();

        // end week count future one week after
        $this->end_week = Carbon::parse($this->start_week)->addWeek($this->week_limit + 1)->endOfWeek()->toDateString();

        // generate week and month range;
        $start_week = $this->start_week;
        $week_limit = 1;
        do {
            $this->month_range[] = sprintf('%s|%s', Carbon::parse($start_week)->startOfMonth()->toDateString(), Carbon::parse($start_week)->endOfmonth()->toDateString());
            $this->week_range[] = sprintf('%s|%s', $start_week, Carbon::parse($start_week)->endOfWeek()->toDateString());
            $start_week = Carbon::parse($start_week)->addWeek(1)->startOfWeek()->toDateString();
            $week_limit++;
        } while ($week_limit <= $this->week_limit);
        $this->week_range = array_unique($this->week_range);
        $this->month_range = array_unique($this->month_range);

    }

    public function index(Request $request)
    {
        if (request()->ajax()) {
            $data = $this->generateList($request);

            return datatables($data)
                ->editColumn('date', function ($item) {
                    return date("jS(l) F, Y", strtotime($item->date));
                })->editColumn('type', function ($item) {
                    return $item->type == 1 ? 'Employee' : 'Department';
                })
                ->addColumn('action', function ($item) {
                    $id = Crypt::encrypt([
                        'department_id' => $item->department_id,
                        'date' => $item->date,
                        'type' => $item->type,
                        'max' => $item->max_count_per_month,
                    ]);

                    $html = '';
                    if (auth()->user()->can('Assign Relax Days')) {
                        $html .= '<a  onclick="setListScroll(this)" class="p-2" href="' . route('assign-relax-day.assign', ['id' => $id]) . '"><i class="fa fa-edit" style="color: lightskyblue"></i></a>';
                    }
                    return $html;
                })
                ->rawColumns(['action'])
                ->make(true);
        }
        $data = array(
            "officeDivisions" => OfficeDivision::select("id", "name")
                ->whereIn('id', FilterController::getDivisionIds())
                ->get(),
            "officeDepartments" => Department::select("id", "name")
                ->whereIn('id', FilterController::getDepartmentIds())
                ->get()
        );
        return view("assign-relax-days.index", $data);
    }

    public function assignFrom(Request $request)
    {
        try {
            $rData = Crypt::decrypt($request->id);
        } catch (Exception $th) {
            abort(404);
        }

        $users = getEmployeesInformationByDepartmentIDs($rData['department_id']);
        // if(empty($ids)) abort(404, 'No employee found!');

        try {
            $data = [
                "html" => $this->getEmployeeHtml($users, $request->id),
                'relax_date' => date('dS F, Y',strtotime($rData['date'])) ?? '',
                'division_name' => $users[0]->division_name ?? '',
                'department_name' => $users[0]->department_name ?? '',
                'type' => $rData['type'] == 1 ? 'Employee' : 'Department',
                'route' => route('assign-relax-day.assign-store'),
            ];
            return view("assign-relax-days.assign-from", $data);
        } catch (\Exception $ex) {
            Log::info($ex->getMessage());
            return redirect()->back()->withErrors($ex->getMessage());
        }
    }

    public function store(Request $request)
    {
        try {
            $rData = Crypt::decrypt($request->_id);
        } catch (Exception $th) {
            abort(404);
        }

        DB::beginTransaction();
        try {

            $valid_users = FilterController::getEmployeeIds(1, "department", $rData['department_id']);
            if(count(array_diff($request->assign_users, $valid_users->toArray())) > 0 ) throw new Exception('Selected employees, few of them is not in this department !!!');

            $relax_day = RelaxDay::where([['date', '=', $rData['date']],['department_id', '=', $rData['department_id']]])->first();

            if($relax_day) throw new Exception('Already assigned');
            $relaxDay = new RelaxDay();
            $relaxDay->department_id = $rData['department_id'];
            $relaxDay->type = $rData['type'];
            $relaxDay->date = $rData['date'];
            $relaxDay->created_by = auth()->user()->id;
            $relaxDay->save();

            if(empty($request->assign_users)) throw new Exception('Any employee are not selected');

            $time = date('Y-m-d H:i:s');
            $insertData = [];
            foreach ($request->assign_users as $user_id) {
                $insertData[] = [
                    'relax_day_id' => $relaxDay->id,
                    'user_id' => $user_id,
                    'created_by' => auth()->user()->id,
                    'created_at' => $time,
                ];
            }
            AssignRelaxDay::insert($insertData);

            DB::commit();
        } catch (Exception $ex) {
            Log::info($ex->getMessage());
            DB::rollBack();
            return redirect()->back()->withErrors($ex->getMessage());
        }
        return redirect()->route('assign-relax-day.index')->with('message', 'Relax day assigned successfully!');
    }

    protected function generateList($request)
    {
        $data = [];
        $c_at = Carbon::now();
        $changable_week = Carbon::parse($this->start_week);
        $start_week = $this->start_week;

        // department filtering
        $department_ids = (FilterController::getDepartmentIds(!empty($request->division_id) ? $request->division_id : 0));

        // department wise relax day settings
        $relax_day_settings = RelaxDaySetting::with('department')->whereIn('department_id', $department_ids);
        if(isset($request->department_id) && !empty($request->department_id)) {
            $relax_day_settings = $relax_day_settings->where('department_id', '=', $request->department_id);
        }
        $relax_day_settings = $relax_day_settings->get();

        // relax days
        $relax_days = RelaxDay::select('id','department_id','date')->whereBetween('date',[$this->start_week, $this->end_week]);
        $relax_days = $relax_days->whereIn('department_id', $department_ids);
        if(isset($request->department_id) && !empty($request->department_id)) {
            $relax_days = $relax_days->where('department_id', '=', $request->department_id);
        }
        $relax_days = $relax_days->get();

        // department wise date array generate
        $exist_relax_days = [];
        if($relax_days->isNotEmpty()) {
            foreach ($relax_days as $item) {
                $exist_relax_days[$item->department_id][] = $item->date;
            }
        }

        // generate list data
        $hash = [];
        $week_limit = 1;
        do {
            foreach ($relax_day_settings as $item) {
                if(!in_array($item->department_id, $department_ids)) continue;

                if(isset($request->department_id) && $request->department_id != $item->department_id) continue;

                $weekly_days = json_decode($item->weekly_days);

                $users = FilterController::getEmployeeIds(1, "department", $item->department_id);

                foreach ($weekly_days as $day) {
                    $date_string = $changable_week->next($day)->toDateString();
                    // dublicate entity avoid
                    if(in_array("{$item->department_id}|{$date_string}", $hash)) continue;

                    // already asign date avoid
                    if( !empty($exist_relax_days) && isset($exist_relax_days[$item->department_id]) && in_array($date_string, $exist_relax_days[$item->department_id]) ) continue;

                    // date picker filter
                    if(isset($request->datepicker) && $request->datepicker != $date_string) continue;

                    if($item->type == 2 && !$this->isDepartmentEligible($item->department_id, ['date' => $date_string, 'max' => $item->max_count_per_month])) continue;

                    if($c_at->lt(Carbon::parse($date_string)) ) { // past date filter
                        $hash[] = "{$item->department_id}|{$date_string}";
                        $data[] = (object)[
                            'id' => NULL,
                            'type' => $item->type,
                            'assignable' => $item->type == 2 ? $users->count() : $this->getAssignableEmployee($users, (object)['date'=>$date_string, 'max'=> $item->max_count_per_month]),
                            'department_id' => $item->department_id,
                            'department_name' => $item->department->name,
                            'date' => $date_string,
                            'max_count_per_month' => $item->max_count_per_month,
                            'note' => '---------------',
                            'created_by' => $item->created_by,
                            'created_at' => $item->created_at->toDateTimeString()
                        ];
                    }
                    $changable_week = Carbon::parse($start_week);
                }
                $changable_week = Carbon::parse($start_week);
            }
            $changable_week = Carbon::parse($start_week)->addWeek(1)->startOfWeek();
            $start_week = $changable_week->toDateString();
            $week_limit++;
        } while ($week_limit <= $this->week_limit);

        array_multisort(array_column($data, 'date'), SORT_ASC, $data);

        return $data;
    }

    protected function getEmployeeHtml($users, $id)
    {
        if(empty($users)) return '';

        $inputs = null;
        try {
            $inputs = Crypt::decrypt($id);
        } catch (Exception $th) {
            abort(404);
        }

        $html = '';
        if($id) $html = sprintf('<input type="hidden" name="_id" id="_id" value="%s">', $id);
        $html .= '<div class="row">';
        foreach ($users as $user) {
            if($inputs['type'] == 2 || $this->isEmployeeEligible($user->id, $inputs)) {
                $html .= sprintf('<div class="col-4">
                    <input type="checkbox" name="assign_users[%1$s]" class="employee_checkbox" id="employee_%1$s"  value="%1$s">
                    <label for="employee_%1$s"> %2$s - %3$s</label>
                </div>', $user->id, $user->fingerprint_no, $user->name);
            } else {
                $html .= sprintf('<div class="col-4">
                    <i class="fa fa-square" style="font-size:1.1rem;"></i>
                    <label for="employee_%1$s"> %2$s - %3$s</label>
                </div>', $user->id, $user->fingerprint_no, $user->name);
            }
        }
        $html .= '</div>';
        return $html;
    }

    protected function isEmployeeEligible($user_id, $inputs)
    {
        // $inputs['date'] = '2023-01-21';
        $start_week_date = Carbon::parse($inputs['date'])->startOfWeek()->toDateString();
        $end_week_date = Carbon::parse($inputs['date'])->endOfWeek()->toDateString();

        $start_month_date = Carbon::parse($inputs['date'])->startOfMonth()->toDateString();
        $end_month_date = Carbon::parse($inputs['date'])->endOfMonth()->toDateString();

        // weekly validation
        $weekly_count = DB::table('relax_day as rd')->whereNull('rd.deleted_at')->whereNull('ard.deleted_at');
        $weekly_count = $weekly_count->select('ard.user_id','rd.department_id', 'rd.date');
        $weekly_count = $weekly_count->whereBetween('date',[$start_week_date, $end_week_date])->where('ard.user_id', '=', $user_id);
        $weekly_count = $weekly_count->join('assign_relax_day as ard', 'ard.relax_day_id', '=', 'rd.id');
        $weekly_count = $weekly_count->groupBy('rd.id');
        $weekly_count = $weekly_count->get()->count();

        if($weekly_count >= 1) return false;

        // monthly malidation
        $monthly_count = DB::table('relax_day as rd')->whereNull('rd.deleted_at')->whereNull('ard.deleted_at');
        $monthly_count = $monthly_count->select('ard.user_id','rd.department_id', 'rd.date');
        $monthly_count = $monthly_count->whereBetween('date',[$start_month_date, $end_month_date])->where('ard.user_id', '=', $user_id);
        $monthly_count = $monthly_count->join('assign_relax_day as ard', 'ard.relax_day_id', '=', 'rd.id');
        $monthly_count = $monthly_count->groupBy('rd.id');
        $monthly_count = $monthly_count->get()->count();

        if($monthly_count >= $inputs['max']) return false;

        return true;
    }


    protected function isDepartmentEligible($department_id, $inputs)
    {
        $start_week_date = Carbon::parse($inputs['date'])->startOfWeek()->toDateString();
        $end_week_date = Carbon::parse($inputs['date'])->endOfWeek()->toDateString();

        $start_month_date = Carbon::parse($inputs['date'])->startOfMonth()->toDateString();
        $end_month_date = Carbon::parse($inputs['date'])->endOfMonth()->toDateString();

        // weekly validation
        $weekly_count = DB::table('relax_day')->whereNull('deleted_at');
        $weekly_count = $weekly_count->select('id','department_id', 'date');
        $weekly_count = $weekly_count->whereBetween('date',[$start_week_date, $end_week_date])->where('department_id', '=', $department_id);
        $weekly_count = $weekly_count->get()->count();

        if($weekly_count >= 1) return false;

        // monthly malidation
        $monthly_count = DB::table('relax_day')->whereNull('deleted_at');
        $monthly_count = $monthly_count->select('id','department_id', 'date');
        $monthly_count = $monthly_count->whereBetween('date',[$start_month_date, $end_month_date])->where('department_id', '=', $department_id);
        $monthly_count = $monthly_count->get()->count();

        if($monthly_count >= $inputs['max']) return false;

        return true;
    }

    protected function getAssignableEmployee($users, Object $args)
    {
        if($users->isEmpty()) return 0;

        $start_week_date = Carbon::parse($args->date)->startOfWeek()->toDateString();
        $end_week_date = Carbon::parse($args->date)->endOfWeek()->toDateString();

        $start_month_date = Carbon::parse($args->date)->startOfMonth()->toDateString();
        $end_month_date = Carbon::parse($args->date)->endOfMonth()->toDateString();

        $weekly_assigned_users = DB::table('relax_day as rd')->whereNull('rd.deleted_at')->whereNull('ard.deleted_at');
        $weekly_assigned_users = $weekly_assigned_users->select('ard.user_id','rd.department_id', 'rd.date');
        $weekly_assigned_users = $weekly_assigned_users->whereBetween('date',[$start_week_date, $end_week_date])->whereIn('ard.user_id', $users->toArray());
        $weekly_assigned_users = $weekly_assigned_users->join('assign_relax_day as ard', 'ard.relax_day_id', '=', 'rd.id');
        $weekly_assigned_users = $weekly_assigned_users->groupBy('ard.user_id');
        $weekly_assigned_users = $weekly_assigned_users->pluck('ard.user_id');
        $assignable_users = $users->diff($weekly_assigned_users);

        // monthly malidation
        $monthly_assigned_users = DB::table('relax_day as rd')->whereNull('rd.deleted_at')->whereNull('ard.deleted_at');
        $monthly_assigned_users = $monthly_assigned_users->select('ard.user_id','rd.department_id', 'rd.date');
        $monthly_assigned_users = $monthly_assigned_users->whereBetween('date',[$start_month_date, $end_month_date])->whereIn('ard.user_id', $users->toArray());
        $monthly_assigned_users = $monthly_assigned_users->join('assign_relax_day as ard', 'ard.relax_day_id', '=', 'rd.id');
        $monthly_assigned_users = $monthly_assigned_users->groupBy('ard.user_id')->havingRaw("COUNT(ard.id) >= {$args->max}");
        $monthly_assigned_users = $monthly_assigned_users->pluck('ard.user_id');
        $assignable_users = $assignable_users->diff($monthly_assigned_users);

        return $assignable_users->count();
    }

}
