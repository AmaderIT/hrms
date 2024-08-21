<?php

namespace App\Http\Controllers;

use Exception;
use Carbon\Carbon;
use App\Models\User;
use App\Models\AssignRelaxDay;
use App\Models\Department;
use App\Models\OfficeDivision;
use App\Models\RelaxDay;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AssignRelaxDayController extends Controller
{

    public function index(Request $request)
    {
        $data['items'] = [];
        $current_month = date('m');
        if (strlen($current_month) == 1) {
            $current_month = '0' . $current_month;
        }
        $current_date_time = strtotime(date('Y-m-d'));
        $status_approve = AssignRelaxDay::APPROVAL_CONFIRMED;
        $department_ids = FilterController::getDepartmentIds(!empty($request->division_id) ? $request->division_id : 0);
        if (request()->ajax()) {

            $items = RelaxDay::with('department')->select(
                'relax_day.*',
                DB::raw("COUNT(ard.id) as assignee"),
                DB::raw("GROUP_CONCAT( CASE WHEN ard.approval_status = '{$status_approve}' THEN ard.approval_status END ) as assignee_approved"),
            );
            $items = $items->leftJoin('assign_relax_day as ard', function($join){
                $join->on('relax_day.id', '=', 'ard.relax_day_id')->whereNull('ard.deleted_at');
            });
            // $items = $items->join('users as u', function($join){
            //     $join->on('u.id', '=', 'ard.user_id')->where('u.status', '=', User::STATUS_ACTIVE)->whereNull('ard.deleted_at');
            // });
            if(isset($request->department_id) && !empty($request->department_id)) $items = $items->where('relax_day.department_id', '=', $request->department_id);

            if($request->route()->getName() == 'assign-relax-day.archived') { // archived route
                $items = $items->where('relax_day.date', '<', date('Y-m-d'));
            } else { // default list route
                $items = $items->where('relax_day.date', '>=', date('Y-m-d'));
            }
            if(isset($request->datepicker)) $items = $items->where('relax_day.date', '=', $request->datepicker);
            $items = $items->groupBy('relax_day.id', 'relax_day.department_id');
            $items = $items->whereIn('relax_day.department_id', $department_ids);
            $items = self::custemColumnForOrderByDate($items, 'relax_day.date');
            $data['items'] = $items->orderBy('dateColForOrder');

            return datatables($data['items'])
                ->editColumn('date', function ($item) {
                    return date("jS(l) F, Y", strtotime($item->date));
                })->editColumn('department_name', function ($item) {
                    return isset($item->department) ? $item->department->name : '----';
                })->editColumn('assignee_approved', function ($item) {
                    return isset($item->assignee_approved) ? count(explode(",",$item->assignee_approved)) : 0;
                })->addColumn('type', function ($item) {
                    return $item->type == 1 ? 'Employee' : ($item->type == 2 ? 'Department' : '----');
                })->addColumn('action', function ($item) use ($current_date_time) {
                    $assignee_approved = isset($item->assignee_approved) ? count(explode(",",$item->assignee_approved)) : 0;
                    $_id = Crypt::encrypt([
                        'id' => $item->id,
                        'department_id' => $item->department_id,
                        'date' => $item->date,
                        'type' => $item->type,
                        'max' => isset($item->department) && isset($item->department->relaxDaySetting) ? $item->department->relaxDaySetting->max_count_per_month : 5,
                    ]);
                    $html = '';
                    if (auth()->user()->can("Approve Assigned Relax Days") && ($current_date_time <= strtotime($item->date)) && ($assignee_approved != $item->assignee) ) {
                        $html .= '<a  onclick="setListScroll(this)" title="Approve" class="p-2 approve_link" data-id="' . $item->id . '" data-href="' . route('assign-relax-day.approve') . '" href="#" ><i class="fa fa-check-circle" style="color: green"></i></a>';
                    }
                    if (auth()->user()->can("Relax Days Detail View") ) {
                        $html .= '<a  onclick="setListScroll(this)" title="Details List" class="p-2 detail_link" data-id="' . $item->id . '" data-href="' . route('assign-relax-day.details') . '" href="#" ><i class="fa fa-list" style="color: grey"></i></a>';
                    }
                    if (auth()->user()->can('Assign Relax Days') && ($current_date_time <= strtotime($item->date)) ) {
                        $html .= '<a  onclick="setListScroll(this)" title="Edit" class="p-2" href="' . route('assign-relax-day.create', ['_id' => $_id]) . '"><i class="fa fa-edit" style="color: lightskyblue"></i></a>';
                    }
                    // if (auth()->user()->can('Assign Relax Days') && ($current_date_time <= strtotime($item->date)) ) {
                    //     $html .= '<a  onclick="setListScroll(this)" title="Delete" class="p-2 delete_link" data-id="' . $item->id . '" data-href="' . route('assign-relax-day.delete') . '" href="#" ><i class="fa fa-trash" style="color: red"></i></a>';
                    // }
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

    public function details(Request $request)
    {
        $today = date('Y-m-d');
        $relax_date = RelaxDay::find($request->relax_day_id);
        $data['today'] = strtotime($today);
        $data['relax_day_id'] = $request->relax_day_id;
        $data['relax_date'] = strtotime($relax_date->date);
        $data['relax_date_label'] = date("jS F, Y", strtotime($relax_date->date));

        $users = AssignRelaxDay::select('u.id', 'u.name', 'u.email', 'u.fingerprint_no', 'assign_relax_day.approval_status');
        $users = $users->join('users as u', 'u.id', '=', 'assign_relax_day.user_id');
        $users = $users->where('assign_relax_day.relax_day_id', '=', $request->relax_day_id)->get();
        $data['employees'] = $users;
        $html = '';
        $html .= view('assign-relax-days.details.modal', $data);
        $data['html'] = $html;
        return response()->json($data);
    }

    public function create(Request $request)
    {
        try {
            $rData = (object)Crypt::decrypt($request->_id);
        } catch (\Exception $th) {
            abort(404);
        }

        $users = getEmployeesInformationByDepartmentIDs($rData->department_id);

        try {
            $relax_day_details = RelaxDay::find($rData->id);
            $data = array(
                "html" => $this->getEmployeeHtml($users, $request->_id),
                'relax_date' => date('dS F, Y',strtotime($relax_day_details->date)) ?? '',
                'division_name' => $users[0]->division_name ?? '',
                'department_name' => $users[0]->department_name ?? '',
                'type' => $rData->type == 1 ? 'Employee' : 'Department',
                'route' => route('assign-relax-day.store'),
            );

            return view("assign-relax-days.assign-from", $data);
        } catch (\Exception $ex) {
            Log::error($ex->getMessage());
            return redirect()->back()->withErrors($ex->getMessage());
        }
    }

    public function store(Request $request)
    {
        try {
            $inputs = (object)Crypt::decrypt($request->_id);
        } catch (\Exception $th) {
            abort(404);
        }

        DB::beginTransaction();
        try {
            $time = date('Y-m-d H:i:s');
            $assign_users = $request->assign_users ?? [];

            // if(empty($assign_users)) throw new Exception('Any employee are not selected');

            $valid_users = FilterController::getEmployeeIds(1, "department", $inputs->department_id);
            if(count(array_diff($assign_users, $valid_users->toArray())) > 0 ) throw new Exception('Selected employees, few of them is not in this department !!!');

            $assigned_users = AssignRelaxDay::where('relax_day_id', '=', $inputs->id)->pluck('user_id')->toArray();

            $combine_users = array_unique(array_merge($assigned_users, $assign_users));

            // delete users
            $deduct_assign_users = array_diff($combine_users, $assign_users);
            if(!empty($deduct_assign_users)){
                AssignRelaxDay::whereIn('user_id', $deduct_assign_users)->where('relax_day_id', '=', $inputs->id)->delete();
            }

            // assign new users
            $new_assign_users = array_diff($combine_users, $assigned_users);
            if(!empty($new_assign_users)){
                $insertData = [];
                foreach ($new_assign_users as $user_id) {
                    $insertData[] = [
                        'relax_day_id' => $inputs->id,
                        'user_id' => $user_id,
                        'created_by' => auth()->user()->id,
                        'created_at' => $time,
                    ];
                }
                AssignRelaxDay::insert($insertData);
            }

            DB::commit();
        } catch (\Exception $ex) {
            Log::info($ex->getMessage());
            DB::rollBack();
            return redirect()->back()->withErrors($ex->getMessage());
        }

        return redirect()->route('assign-relax-day.index')->with('message', 'Relax day assigned successfully!');
    }

    public function approve(Request $request)
    {
        try {
            $relax_day_id = $request->relax_day_id;
            $update['approval_status'] = AssignRelaxDay::APPROVAL_CONFIRMED;
            $update['approved_by'] = Auth::id();
            AssignRelaxDay::where('relax_day_id', '=', $relax_day_id)->where('deleted_at', '=', null)->update($update);
            $feedback['status'] = true;
        } catch (\Exception $ex) {
            Log::info($ex->getMessage());
            $feedback['status'] = false;
        }
        return $feedback;
    }

    public function delete(Request $request)
    {
        try {
            RelaxDay::where('id', '=', $request->id)->delete();
            AssignRelaxDay::where('relax_day_id', '=', $request->id)->delete();
            $feedback['status'] = true;
        } catch (\Exception $ex) {
            Log::info($ex->getMessage());
            $feedback['status'] = false;
        }
        return $feedback;
    }

    protected function getEmployeeHtml($users, $_id)
    {
        if(empty($users)) return '';

        $inputs = null;
        try {
            $inputs = (object)Crypt::decrypt($_id);
        } catch (\Exception $th) {
            abort(404);
        }

        $user_ids = array_column($users, 'id');
        $assigned_users = AssignRelaxDay::where('relax_day_id', '=', $inputs->id)->whereIn('user_id', $user_ids)->pluck('user_id')->all();

        $html = '';
        if($_id) $html = sprintf('<input type="hidden" name="_id" id="_id" value="%s">', $_id);
        $html .= '<div class="row">';
        foreach ($users as $user) {
            switch ($inputs->type) {
                case 1: // employee
                case 2: // department
                    if( !empty($assigned_users) && in_array($user->id, $assigned_users) ){
                        $html .= sprintf('<div class="col-4">
                            <input type="checkbox" name="assign_users[]" class="employee_checkbox" id="employee_%1$s" checked value="%1$s">
                            <label for="employee_%1$s"> %2$s - %3$s</label>
                        </div>', $user->id, $user->fingerprint_no, $user->name);
                    } else if( $this->isEmployeeEligible($user->id, $inputs) ) {
                        $html .= sprintf('<div class="col-4">
                            <input type="checkbox" name="assign_users[]" class="employee_checkbox" id="employee_%1$s"  value="%1$s">
                            <label for="employee_%1$s"> %2$s - %3$s</label>
                        </div>', $user->id, $user->fingerprint_no, $user->name);
                    } else {
                        // $this->isEmployeeEligible($user->id, $inputs);
                        $html .= sprintf('<div class="col-4">
                            <i class="fa fa-square" style="font-size:1.1rem;"></i>
                            <label for="employee_%1$s"> %2$s - %3$s</label>
                        </div>', $user->id, $user->fingerprint_no, $user->name);
                    }
                    break;

                // case 2: // department
                default: // previous data
                    if( !empty($assigned_users) && in_array($user->id, $assigned_users) ){
                        $html .= sprintf('<div class="col-4">
                            <input type="checkbox" name="assign_users[]" class="employee_checkbox" id="employee_%1$s" checked value="%1$s">
                            <label for="employee_%1$s"> %2$s - %3$s</label>
                        </div>', $user->id, $user->fingerprint_no, $user->name);
                    } else {
                        $html .= sprintf('<div class="col-4">
                            <input type="checkbox" name="assign_users[]" class="employee_checkbox" id="employee_%1$s"  value="%1$s">
                            <label for="employee_%1$s"> %2$s - %3$s</label>
                        </div>', $user->id, $user->fingerprint_no, $user->name);
                    }
                    break;
            }
        }

        $html .= '</div>';

        return $html;
    }

    protected function isEmployeeEligible($user_id, $inputs)
    {
        // $inputs['date'] = '2023-01-21';
        $start_week_date = Carbon::parse($inputs->date)->startOfWeek()->toDateString();
        $end_week_date = Carbon::parse($inputs->date)->endOfWeek()->toDateString();

        $start_month_date = Carbon::parse($inputs->date)->startOfMonth()->toDateString();
        $end_month_date = Carbon::parse($inputs->date)->endOfMonth()->toDateString();

        // weekly validation
        $weekly_count = DB::table('relax_day as rd')->whereNull('rd.deleted_at')->whereNull('ard.deleted_at');
        $weekly_count = $weekly_count->select('rd.id', 'ard.user_id','rd.department_id', 'rd.date');
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

        if($monthly_count >= $inputs->max) return false;

        return true;
    }

    public static function custemColumnForOrderByDate($sql, $col, $col_name='dateColForOrder')
    {
        return $sql->addSelect(
            DB::raw("CAST(
                CASE
                    WHEN MONTH({$col}) = MONTH(NOW()) AND YEAR({$col}) = year(NOW()) AND DATE({$col}) > DATE(NOW()) THEN
                    CASE
                        WHEN DAY({$col}) < 10 THEN CONCAT('1.00', DAY({$col}))
                        WHEN DAY({$col}) < 20 THEN CONCAT('1.0', DAY({$col}))
                        ELSE CONCAT('1.', DAY({$col}))
                    END
                    WHEN MONTH({$col}) = MONTH(NOW()) AND YEAR({$col}) = year(NOW()) AND DATE({$col}) < DATE(NOW()) THEN
                    CASE
                        WHEN DAY({$col}) < 10 THEN CONCAT('1.400', DAY({$col}))
                        WHEN DAY({$col}) < 20 THEN CONCAT('1.40', DAY({$col}))
                        ELSE CONCAT('1.4', DAY({$col}))
                    END
                    WHEN MONTH({$col}) >= MONTH(NOW()) AND YEAR({$col}) >= year(NOW()) THEN CONCAT('2.', DATE_FORMAT({$col}, '%y%m%d'))
                    WHEN MONTH({$col}) < MONTH(NOW()) AND YEAR({$col}) <= year(NOW()) THEN CONCAT('3.', DATE_FORMAT({$col}, '%y%m%d'))
                    WHEN YEAR({$col}) < year(NOW()) THEN CONCAT('4.', (YEAR(NOW()) - YEAR({$col})) * DATE_FORMAT({$col}, '%y'), (12 - MONTH({$col})), DATE_FORMAT({$col}, '%d'))
                END
            AS DECIMAL(10,8)) as {$col_name}")
        );
    }

    public static function removeEmployeeRelaxDay($data)
    {
        try {

            $relax_days = RelaxDay::where([['date', '>=', date('Y-m-d')],['date', '>=', $data->date]]);
            if($data->prev_department_id) $relax_days = $relax_days->where('department_id', '=', $data->prev_department_id);
            $relax_days = $relax_days->pluck('id');

            if($relax_days->isNotEmpty()){
                AssignRelaxDay::where('user_id', '=', $data->user_id)->whereIn('relax_day_id', $relax_days->toArray())->delete();
            }
        } catch (\Exception $ex) {
            Log::info($ex->getMessage());
        }
    }

    public static function removeDepartmentRelaxDay($department_id)
    {
        try {
            $relax_days = RelaxDay::where('date', '>=', date('Y-m-d'));
            $relax_days = $relax_days->where('department_id', '=', $department_id);
            $relax_days = $relax_days->pluck('id');

            if($relax_days->isNotEmpty()){
                RelaxDay::whereIn('id', $relax_days->toArray())->delete();
                AssignRelaxDay::whereIn('relax_day_id', $relax_days->toArray())->delete();
            }
        } catch (\Exception $ex) {
            Log::info($ex->getMessage());
        }
    }


}
