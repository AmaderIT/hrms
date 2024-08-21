<?php

namespace App\Http\Controllers;

use App\Models\ActionReason;
use App\Models\Department;
use App\Models\DepartmentLeaveEncashment;
use App\Models\DepartmentSupervisor;
use App\Models\DivisionSupervisor;
use App\Models\Earning;
use App\Models\EmployeeLeaveEncashment;
use App\Models\LeaveType;
use App\Models\OfficeDivision;
use App\Models\PayGradeEarning;
use App\Models\User;
use Barryvdh\DomPDF\Facade as PDF;
use Exception;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;

class LeaveEncashmentController extends Controller
{
    public function leaveEncashment(){
        $data = array();
        $filter_obj = new FilterController();
        $divisionIds = $filter_obj->getDivisionIds();
        $data['officeDivisions'] = OfficeDivision::select("id", "name")->whereIn('id',$divisionIds)->get();
        if ((auth()->user()->can('Show All Office Division')) || (auth()->user()->can('Show All Department'))) {
            $data['officeDepartments'] = Department::select("id", "name")->whereIn('office_division_id',$divisionIds)->get();
        }else{
            $departmentalSupervisorDepartments = DepartmentSupervisor::where('status', DepartmentSupervisor::STATUS_ACTIVE)
                ->where('supervised_by', auth()->user()->id)
                ->whereIn('office_division_id', $divisionIds)
                ->pluck('department_id')->toArray();
            $divisionalSupervisorDivisions = DivisionSupervisor::where('status', DivisionSupervisor::STATUS_ACTIVE)
                ->where('supervised_by', auth()->user()->id)
                ->whereIn('office_division_id', $divisionIds)
                ->pluck('office_division_id')->toArray();
            $divisionalSupervisorDepartments = Department::whereIn('office_division_id', $divisionalSupervisorDivisions)->pluck('id')->toArray();
            $departmentIds = array_unique(array_merge($divisionalSupervisorDepartments, $departmentalSupervisorDepartments));
            $data['officeDepartments'] = Department::select("id", "name")->whereIn('id',$departmentIds)->get();
        }
        return view("leave_encashment.leave-encashment-report-view", compact("data"));
    }

    public function leaveEncashmentGenerate(Request $request){
        try{
            $terminate_action_reason_ids = implode(',',ActionReason::reasonForTermination()->pluck("id")->toArray());
            ini_set('max_execution_time', '300');
            $office_division_id = $request->office_division_id;
            $year = $request->datepicker;
            $max_year = $year+1;
            $eligible_month = ($request->eligible_month > 12) ? 12 : $request->eligible_month;
            $eligible_month = ($eligible_month < 6) ? 6 : $eligible_month;
            $valid_month = (12-$eligible_month)+1;
            $valid_month = (strlen($valid_month) == 1) ? '0'.$valid_month : $valid_month;
            $valid_date = $year.'-'.$valid_month.'-01';
            $current_year = date('Y');
            $last_date_of_requested_year = $year.'-12-31';
            if($year>$current_year){
                session()->flash("type", "error");
                session()->flash("message", "Please select current or previous year!!");
                return redirect()->back();
            }
            $department_ids = $request->department_id;
            if($office_division_id=='all'){
                $find_division=true;
            }else{
                $find_division=false;
            }
            if(in_array("all", $department_ids)) {
                $find_department=true;
            }else{
                $find_department=false;
            }
            if($find_department){
                if($find_division){
                    if (auth()->user()->can('Show All Office Division') && auth()->user()->can('Show All Department')) {
                        $sql = "SELECT A.* FROM( SELECT users.id, users.`name`, users.email, users.fingerprint_no, employee_status.action_date, es2.action_date as last_date, prm.department_id, prm.office_division_id, prm1.pay_grade_id, prm1.salary, departments.`name` AS department_name, office_divisions.`name` AS division_name, pay_grades.percentage_of_basic, pay_grades.based_on, designations.title, user_leaves.initial_leave, user_leaves.`leaves`, ( SELECT MAX( da.date ) FROM `daily_attendances` AS da WHERE da.user_id = users.id  ) as maxadate FROM `users` LEFT JOIN employee_status ON employee_status.user_id = users.id AND employee_status.id =( SELECT MAX( es.id) FROM `employee_status` AS es WHERE es.user_id = users.id AND es.action_reason_id = 2 ) LEFT JOIN employee_status AS es2 ON es2.user_id = users.id AND es2.id = ( SELECT MAX( es21.id ) FROM `employee_status` AS es21 WHERE es21.user_id = users.id AND es21.action_reason_id IN ($terminate_action_reason_ids) ) INNER JOIN promotions AS prm ON prm.user_id = users.id AND prm.id =( SELECT MAX( pm.id ) FROM `promotions` AS pm WHERE pm.user_id = users.id ) INNER JOIN promotions AS prm1 ON prm1.user_id = users.id AND prm1.id =( SELECT MAX( pm1.id ) FROM `promotions` AS pm1 WHERE pm1.user_id = users.id AND YEAR ( pm1.promoted_date ) < $max_year ) INNER JOIN pay_grades ON pay_grades.id = prm1.pay_grade_id INNER JOIN departments ON departments.id = prm.department_id INNER JOIN office_divisions ON office_divisions.id = prm.office_division_id INNER JOIN designations ON designations.id = prm.designation_id INNER JOIN user_leaves ON user_leaves.user_id = users.id AND user_leaves.`year` = $year ) AS A HAVING A.action_date <= '$valid_date' AND ( (A.last_date IS NULL AND maxadate>='$last_date_of_requested_year') OR A.last_date >= '$last_date_of_requested_year' )";
                        $users = DB::select($sql);
                    }else{
                        $filter_obj = new FilterController();
                        $divisionIds = $filter_obj->getDivisionIds();
                        $departmentalSupervisorDepartments = DepartmentSupervisor::where('status', DepartmentSupervisor::STATUS_ACTIVE)
                            ->where('supervised_by', auth()->user()->id)
                            ->whereIn("office_division_id", $divisionIds)
                            ->pluck('department_id')->toArray();
                        $divisionalSupervisorDivisions = DivisionSupervisor::where('status', DivisionSupervisor::STATUS_ACTIVE)
                            ->where('supervised_by', auth()->user()->id)
                            ->whereIn("office_division_id", $divisionIds)
                            ->pluck('office_division_id')->toArray();
                        $divisionalSupervisorDepartments = Department::whereIn('office_division_id', $divisionalSupervisorDivisions)->pluck('id')->toArray();
                        $departmentIds = array_unique(array_merge($divisionalSupervisorDepartments, $departmentalSupervisorDepartments));
                        $departmentIds_in_string = implode(',',$departmentIds);
                        $users = getEmployeesInformationAndOldSalaryByDepartmentIDs($departmentIds_in_string,$year,$valid_date,$last_date_of_requested_year,$terminate_action_reason_ids,$max_year);
                    }
                }else{
                    if (auth()->user()->can('Show All Office Division') && auth()->user()->can('Show All Department')) {
                        $departments = Department::where("office_division_id", '=', $request->office_division_id)->select("id", "name")->get();
                        $departmentIds = [];
                        foreach ($departments as $item){
                            $departmentIds[] = $item->id;
                        }
                        $departmentIds_in_string = implode(',',$departmentIds);
                        $users = getEmployeesInformationAndOldSalaryByDepartmentIDs($departmentIds_in_string,$year,$valid_date,$last_date_of_requested_year,$terminate_action_reason_ids,$max_year);
                    }else{
                        $departmentalSupervisorDepartments = DepartmentSupervisor::where('status', DepartmentSupervisor::STATUS_ACTIVE)
                            ->where('supervised_by', auth()->user()->id)
                            ->where("office_division_id", '=', $request->office_division_id)
                            ->pluck('department_id')->toArray();
                        $divisionalSupervisorDivisions = DivisionSupervisor::where('status', DivisionSupervisor::STATUS_ACTIVE)
                            ->where('supervised_by', auth()->user()->id)
                            ->where("office_division_id", '=', $request->office_division_id)
                            ->pluck('office_division_id')->toArray();
                        $divisionalSupervisorDepartments = Department::whereIn('office_division_id', $divisionalSupervisorDivisions)->pluck('id')->toArray();
                        $departmentIds = array_unique(array_merge($divisionalSupervisorDepartments, $departmentalSupervisorDepartments));
                        $departmentIds_in_string = implode(',',$departmentIds);
                        $users = getEmployeesInformationAndOldSalaryByDepartmentIDs($departmentIds_in_string,$year,$valid_date,$last_date_of_requested_year,$terminate_action_reason_ids,$max_year);
                    }
                }
            }else{
                $departmentIds = $department_ids;
                $departmentIds_in_string = implode(',',$department_ids);
                $users = getEmployeesInformationAndOldSalaryByDepartmentIDs($departmentIds_in_string,$year,$valid_date,$last_date_of_requested_year,$terminate_action_reason_ids,$max_year);
            }
            if(!isset($departmentIds)){
                $departments = Department::all();
                $departmentIds = [];
                foreach ($departments as $item){
                    $departmentIds[] = $item->id;
                }
            }
            $paid_ids = DepartmentLeaveEncashment::where('year','=',$year)->where('pay_status','=',1)->whereIn('department_id',$departmentIds)->get()->pluck('id')->toArray();
            if($paid_ids){
                if (request()->ajax()) {
                    $feedback["status"] = false;
                    $feedback["message"] = "Selected department already pay the leave encashment. Please select valid departments to go!";
                    return $feedback;
                }else{
                    session()->flash("type", "warning");
                    session()->flash("message", "Selected department already pay the leave encashment. Please select valid departments to go!");
                    return redirect()->back();
                }
            }
            $leave_types = LeaveType::where('is_paid','=',LeaveType::ENCASHMENT_PAID)->orderBy('priority')->pluck('id')->toArray();
            DB::beginTransaction();
            if($users){
                $earnings = Earning::all();
                $earning_arr = [];
                foreach($earnings as $earn){
                    $earning_arr[$earn->id] = $earn->name;
                }
                $paygrade_earnings = PayGradeEarning::all();
                $paygrade_earning_arr = [];
                foreach($paygrade_earnings as $paygrade_earn){
                    $paygrade_earning_arr[$paygrade_earn->pay_grade_id][$paygrade_earn->earning_id] = $paygrade_earn;
                }
                $employee_tax_rates = [];
                $tax_rates = DB::table('employee_tax_rates')->where('month','=',12)->where('year','=',$year)->get();
                foreach ($tax_rates as $rate){
                    $employee_tax_rates[$rate->user_id] = $rate->maximum_tax_rate;
                }
                $department_information = [];
                $employee_information = [];
                $earnings_data = [];
                foreach ($users as $usr){
                    $employee_details = [];
                    $department_information[$usr->department_id]['office_division_id'] = $usr->office_division_id;
                    $department_information[$usr->department_id]['department_id'] = $usr->department_id;
                    $department_information[$usr->department_id]['eligible_month'] = $eligible_month;
                    $department_information[$usr->department_id]['year'] = $year;
                    $department_information[$usr->department_id]['prepared_by'] = Auth::id();
                    $department_information[$usr->department_id]['prepared_date'] = date('Y-m-d H:i:s');
                    $department_information[$usr->department_id]['divisional_approval_status'] = 0;
                    $department_information[$usr->department_id]['divisional_approval_by'] = null;
                    $department_information[$usr->department_id]['divisional_approved_date'] = null;
                    $department_information[$usr->department_id]['divisional_remarks'] = null;
                    $department_information[$usr->department_id]['departmental_approval_status'] = 0;
                    $department_information[$usr->department_id]['departmental_approval_by'] = null;
                    $department_information[$usr->department_id]['departmental_approved_date'] = null;
                    $department_information[$usr->department_id]['departmental_remarks'] = null;
                    $department_information[$usr->department_id]['hr_approval_status'] = 0;
                    $department_information[$usr->department_id]['hr_approval_by'] = null;
                    $department_information[$usr->department_id]['hr_approved_date'] = null;
                    $department_information[$usr->department_id]['hr_remarks'] = null;
                    $department_information[$usr->department_id]['accounts_approval_status'] = 0;
                    $department_information[$usr->department_id]['accounts_approval_by'] = null;
                    $department_information[$usr->department_id]['accounts_approved_date'] = null;
                    $department_information[$usr->department_id]['accounts_remarks'] = null;
                    $department_information[$usr->department_id]['managerial_approval_status'] = 0;
                    $department_information[$usr->department_id]['managerial_approval_by'] = null;
                    $department_information[$usr->department_id]['managerial_approved_date'] = null;
                    $department_information[$usr->department_id]['managerial_remarks'] = null;
                    $department_information[$usr->department_id]['pay_status'] = 0;
                    $department_information[$usr->department_id]['total_payable_amount'] = $department_information[$usr->department_id]['total_payable_amount'] ?? 0;
                    $employee_information[$usr->department_id][$usr->id]['user_id'] = $usr->id;
                    $employee_information[$usr->department_id][$usr->id]['designation_name'] = $usr->title;
                    $remaining_id = 0;
                    $employee_information[$usr->department_id][$usr->id]['basic_salary_amount'] = ($usr->salary*$usr->percentage_of_basic)/100;
                    foreach ($earning_arr as $earning_id=>$earning_name){
                        if(isset($paygrade_earning_arr[$usr->pay_grade_id][$earning_id])){
                            if($paygrade_earning_arr[$usr->pay_grade_id][$earning_id]->type=='Percentage'){
                                $earnings_data[$usr->department_id][$usr->id]['earnings'][$earning_id] = ($usr->salary*$paygrade_earning_arr[$usr->pay_grade_id][$earning_id]->value)/100;
                            }elseif($paygrade_earning_arr[$usr->pay_grade_id][$earning_id]->type=='Fixed'){
                                $earnings_data[$usr->department_id][$usr->id]['earnings'][$earning_id] = $paygrade_earning_arr[$usr->pay_grade_id][$earning_id]->value;
                            }elseif ($paygrade_earning_arr[$usr->pay_grade_id][$earning_id]->type=='Remaining'){
                                $remaining_id = $earning_id;
                                $earnings_data[$usr->department_id][$usr->id]['earnings'][$earning_id] = $usr->salary - $employee_information[$usr->department_id][$usr->id]['basic_salary_amount'];
                            }
                        }
                    }
                    if($remaining_id){
                        foreach ($earnings_data[$usr->department_id][$usr->id]['earnings'] as $earning_key=>$earning_item_value){
                            if($earning_key!=$remaining_id){
                                $earnings_data[$usr->department_id][$usr->id]['earnings'][$remaining_id] = $earnings_data[$usr->department_id][$usr->id]['earnings'][$remaining_id] - $earning_item_value;
                            }
                        }
                    }
                    $employee_information[$usr->department_id][$usr->id]['earning_amounts'] = json_encode($earnings_data[$usr->department_id][$usr->id]['earnings']);
                    $employee_information[$usr->department_id][$usr->id]['gross_salary_amount'] = $usr->salary;
                    $employee_information[$usr->department_id][$usr->id]['per_day_salary_amount'] = $usr->salary/30;
                    $employee_information[$usr->department_id][$usr->id]['total_payable_amount'] = 0;
                    $initial_leaves = json_decode($usr->initial_leave);
                    foreach($initial_leaves as $item){
                        if(in_array($item->leave_type_id,$leave_types)){
                            $employee_details[$item->leave_type_id]['total_leave_amount'] = $item->total_days;
                        }
                    }
                    $consume_leaves = json_decode($usr->leaves);
                    foreach($consume_leaves as $item){
                        if(in_array($item->leave_type_id,$leave_types)){
                            $employee_details[$item->leave_type_id]['leave_balance'] = ($item->total_days < 0) ? 0 : $item->total_days;
                            $employee_details[$item->leave_type_id]['consumed_leave_amount'] = $employee_details[$item->leave_type_id]['total_leave_amount'] - $employee_details[$item->leave_type_id]['leave_balance'];
                            $employee_details[$item->leave_type_id]['payable_amount'] = $employee_information[$usr->department_id][$usr->id]['per_day_salary_amount'] * $employee_details[$item->leave_type_id]['leave_balance'];
                            $employee_information[$usr->department_id][$usr->id]['total_payable_amount'] += $employee_details[$item->leave_type_id]['payable_amount'];
                        }
                    }
                    $employee_information[$usr->department_id][$usr->id]['tax_amount'] = isset($employee_tax_rates[$usr->id]) ? ($employee_information[$usr->department_id][$usr->id]['total_payable_amount']*$employee_tax_rates[$usr->id])/100 : 0;
                    $employee_information[$usr->department_id][$usr->id]['total_payable_amount'] = $employee_information[$usr->department_id][$usr->id]['total_payable_amount'] - $employee_information[$usr->department_id][$usr->id]['tax_amount'];
                    $employee_information[$usr->department_id][$usr->id]['leave_details'] = json_encode($employee_details);
                    $department_information[$usr->department_id]['total_payable_amount'] += $employee_information[$usr->department_id][$usr->id]['total_payable_amount'];
                }
                $effected_department = [];
                foreach($department_information as $dept=>$information){
                    $effected_user = [];
                    $effected_department[]=$dept;
                    $dept_leave_encashment = DepartmentLeaveEncashment::updateOrCreate(
                        [
                            'department_id' => $dept,
                            'year' => $year,
                            'deleted_at' => null
                        ],
                        $information
                    );
                    foreach($employee_information[$dept] as $user=>&$user_information){
                        $effected_user[] = $user;
                        $user_information['department_leave_encashment_id'] = $dept_leave_encashment->id;
                        EmployeeLeaveEncashment::updateOrCreate(
                            [
                                'department_leave_encashment_id' => $dept_leave_encashment->id,
                                'user_id' => $user,
                                'deleted_at' => null
                            ],
                            $user_information
                        );
                    }
                    if($effected_user){
                        EmployeeLeaveEncashment::where('department_leave_encashment_id','=',$dept_leave_encashment->id)->whereNotIn('user_id',$effected_user)->delete();
                    }
                }
                $absent_dept = array_diff($departmentIds,$effected_department);
                $ids = DepartmentLeaveEncashment::where('year','=',$year)->whereIn('department_id',$absent_dept)->get()->pluck('id')->toArray();
                DepartmentLeaveEncashment::whereIn('id',$ids)->delete();
                EmployeeLeaveEncashment::whereIn('department_leave_encashment_id',$ids)->delete();
                DB::commit();
                if (request()->ajax()) {
                    $feedback["status"] = true;
                    $feedback["message"] = "Leave encashment regenerated successfully!";
                    return $feedback;
                }else{
                    session()->flash("type", "success");
                    session()->flash("message", "Leave encashment generated successfully!");
                    return redirect()->route("leave-encashment.leaveEncashmentList");
                }
            }else{
                DB::rollBack();
                if (request()->ajax()) {
                    $feedback["status"] = false;
                    $feedback["message"] = "Employee not found!";
                    return $feedback;
                }else{
                    session()->flash("type", "warning");
                    session()->flash("message", "Employee not found!");
                    return redirect()->back();
                }
            }
        }catch (Exception $exception) {
            DB::rollBack();
            if (request()->ajax()) {
                $feedback["status"] = false;
                $feedback["message"] = "Something went wrong!!";
                return $feedback;
            }else{
                Log::info($exception->getMessage());
                Log::info($exception->getLine());
                session()->flash("type", "error");
                session()->flash("message", "Something went wrong!!");
                return redirect()->back();
            }
        }
    }

    public function leaveEncashmentList(Request $request){
        $data = array();
        $filter_obj = new FilterController();
        if(auth()->user()->can('Show All Encashment List')){
            $divisionIds = $filter_obj->getDivisionIds(true,true);
        }else{
            $divisionIds = $filter_obj->getDivisionIds(false,true);
        }
        $data['officeDivisions'] = OfficeDivision::select("id", "name")->whereIn('id',$divisionIds)->get();
        if (request()->ajax()) {
            $year = $request->datepicker;
            $payment_status = $request->payment_status;
            $office_division_id = [];
            if(isset($request->office_division_id)){
                $office_division_id = [$request->office_division_id];
            }
            $departmentIds = [];
            if(isset($request->department_id)){
                $departmentIds = $request->department_id;
            }
            if(!$office_division_id){
                $filter_obj = new FilterController();
                if(auth()->user()->can('Show All Encashment List')){
                    $divisionIds = $filter_obj->getDivisionIds(true,true);
                }else{
                    $divisionIds = $filter_obj->getDivisionIds(false, true);
                }
            }else{
                $divisionIds = $office_division_id;
            }
            if(!$departmentIds){
                if(auth()->user()->can('Show All Encashment List')) {
                    $departmentIds = Department::whereIn('office_division_id',$divisionIds)->pluck('id')->toArray();
                }else {
                    $departmentalSupervisorDepartments = DepartmentSupervisor::where('status', DepartmentSupervisor::STATUS_ACTIVE)
                        ->where('supervised_by', auth()->user()->id)
                        ->whereIn('office_division_id', $divisionIds)
                        ->pluck('department_id')->toArray();
                    $divisionalSupervisorDivisions = DivisionSupervisor::where('status', DivisionSupervisor::STATUS_ACTIVE)
                        ->where('supervised_by', auth()->user()->id)
                        ->whereIn('office_division_id', $divisionIds)
                        ->pluck('office_division_id')->toArray();
                    $divisionalSupervisorDepartments = Department::whereIn('office_division_id', $divisionalSupervisorDivisions)->pluck('id')->toArray();
                    $departmentIds = array_unique(array_merge($divisionalSupervisorDepartments, $departmentalSupervisorDepartments));
                }
            }
            $departmentIds_in_string = implode(',',$departmentIds);
            $confirm_status = DepartmentLeaveEncashment::APPROVAL_CONFIRMED;
            $condition=" ";
            if(isset($payment_status) && $payment_status != 'all'){
                $condition = "AND `department_leave_encashment`.`pay_status` = $payment_status";
            }
            $isAdmin = auth()->user()->isAdminUser();
            if (auth()->user()->can('Salary Accounts Approval') && !$isAdmin) {
                $condition .= " AND `department_leave_encashment`.`hr_approval_status` = $confirm_status";
            }
            if (auth()->user()->can('Salary Managerial Approval') && !$isAdmin) {
                $condition .= " AND `department_leave_encashment`.`hr_approval_status` = $confirm_status AND `department_leave_encashment`.`accounts_approval_status` = $confirm_status";
            }
            $sql_leave_encashment = "SELECT `department_leave_encashment`.*, departments.`name` as department_name, office_divisions.`name` as office_division_name, users.`name` as prepared_user_name, users.fingerprint_no as prepared_user_fingerprint_no, u1.`name` as divisional_user_name, u1.fingerprint_no as divisional_user_fingerprint_no, u2.`name` as departmental_user_name, u2.fingerprint_no as departmental_user_fingerprint_no, u3.`name` as hr_user_name, u3.fingerprint_no as hr_user_fingerprint_no, u4.`name` as accounts_user_name, u4.fingerprint_no as accounts_user_fingerprint_no, u5.`name` as managerial_user_name, u5.fingerprint_no as managerial_user_fingerprint_no FROM `department_leave_encashment` LEFT JOIN office_divisions ON office_divisions.id = `department_leave_encashment`.office_division_id LEFT JOIN departments ON departments.id = `department_leave_encashment`.department_id LEFT JOIN users ON users.id = `department_leave_encashment`.prepared_by LEFT JOIN users as u1 ON u1.id = `department_leave_encashment`.divisional_approval_by LEFT JOIN users as u2 ON u2.id = `department_leave_encashment`.departmental_approval_by LEFT JOIN users as u3 ON u3.id = `department_leave_encashment`.hr_approval_by LEFT JOIN users as u4 ON u4.id = `department_leave_encashment`.accounts_approval_by LEFT JOIN users as u5 ON u5.id = `department_leave_encashment`.managerial_approval_by WHERE `year` = $year AND `department_id` IN ($departmentIds_in_string) $condition AND `department_leave_encashment`.`deleted_at` IS NULL";
            $items = DB::select($sql_leave_encashment);
            return datatables($items)
                ->addColumn('checkbox', function ($item) {
                    return '<th><input class="encashment-checkbox" type="checkbox" data-encashment-id="'.$item->id.'"></th>';
                })
                ->editColumn('division_name', function ($item) {
                    return $item->office_division_name;
                })
                ->editColumn('department_name', function ($item) {
                    return $item->department_name;
                })
                ->addColumn('payable_amount', function ($item) {
                    return currencyFormat($item->total_payable_amount);
                })
                ->editColumn('prepared_by', function ($item) {
                    $name = $item->prepared_user_fingerprint_no ?? "";
                    $name .= ' - ';
                    $name .= $item->prepared_user_name ?? "";
                    if(!empty($item->prepared_date)){
                        $name .= "<br><small>@".date("d M h:i A",strtotime($item->prepared_date))."</small>";
                    }
                    return $name;
                })
                ->editColumn('divisional_approved_by', function ($item) {
                    $name = $item->divisional_user_fingerprint_no ?? "";
                    $name .= ' - ';
                    $name .= $item->divisional_user_name ?? "";
                    if(!empty($item->divisional_approved_date)){
                        $name .= "<br><small>@".date("d M h:i A",strtotime($item->divisional_approved_date))."</small>";
                    }
                    return $name;
                })
                ->editColumn('departmental_approved_by', function ($item) {
                    $name = $item->departmental_user_fingerprint_no ?? "";
                    $name .= ' - ';
                    $name .= $item->departmental_user_name ?? "";
                    if(!empty($item->departmental_approved_date)){
                        $name .= "<br><small>@".date("d M h:i A",strtotime($item->departmental_approved_date))."</small>";
                    }
                    return $name;
                })
                ->editColumn('hr_approved_by', function ($item) {
                    $name = $item->hr_user_fingerprint_no ?? "";
                    $name .= ' - ';
                    $name .= $item->hr_user_name ?? "";
                    if(!empty($item->hr_approved_date)){
                        $name .= "<br><small>@".date("d M h:i A",strtotime($item->hr_approved_date))."</small>";
                    }
                    return $name;
                })
                ->editColumn('accounts_approved_by', function ($item) {
                    $name = $item->accounts_user_fingerprint_no ?? "";
                    $name .= ' - ';
                    $name .= $item->accounts_user_name ?? "";
                    if(!empty($item->accounts_approved_date)){
                        $name .= "<br><small>@".date("d M h:i A",strtotime($item->accounts_approved_date))."</small>";
                    }
                    return $name;
                })
                ->editColumn('management_approved_by', function ($item) {
                    $name = $item->managerial_user_fingerprint_no ?? "";
                    $name .= ' - ';
                    $name .= $item->managerial_user_name ?? "";
                    if(!empty($item->managerial_approved_date)){
                        $name .= "<br><small>@".date("d M h:i A",strtotime($item->managerial_approved_date))."</small>";
                    }
                    return $name;
                })
                ->editColumn('payment_status', function ($item) {
                    if ($item->pay_status == DepartmentLeaveEncashment::APPROVAL_CONFIRMED) {
                        return '<span class="badge badge-success">Paid</span>';
                    } else{
                        return '<span class="badge badge-danger">Unpaid</span>';
                    }
                })
                ->addColumn('action', function ($item) use ($request) {
                    $html = '';
                    if((auth()->user()->can("Regenerate Leave Encashment")) && ($item->pay_status == DepartmentLeaveEncashment::APPROVAL_PENDING)){
                        $html .= '<a href="#" data-href="'.route('leave-encashment.leaveEncashmentGenerate').'" data-office-division-id="'.$item->office_division_id.'" data-department-id="'.$item->department_id.'" data-eligible-month="'.$item->eligible_month.'" data-year="'.$item->year.'" class="btn btn-sm btn-info m-1 regenerate_leave_encashment">Regenerate</a>';
                    }
                    if((auth()->user()->can("Pay Leave Encashment")) && ($item->pay_status == DepartmentLeaveEncashment::APPROVAL_PENDING) && ($item->divisional_approval_status == DepartmentLeaveEncashment::APPROVAL_CONFIRMED || $item->departmental_approval_status == DepartmentLeaveEncashment::APPROVAL_CONFIRMED) && ($item->divisional_approval_status != DepartmentLeaveEncashment::APPROVAL_REJECTED && $item->departmental_approval_status != DepartmentLeaveEncashment::APPROVAL_REJECTED) && $item->hr_approval_status == DepartmentLeaveEncashment::APPROVAL_CONFIRMED && $item->accounts_approval_status == DepartmentLeaveEncashment::APPROVAL_CONFIRMED && $item->managerial_approval_status == DepartmentLeaveEncashment::APPROVAL_CONFIRMED){
                        $html .= '<a href="#" class="btn btn-sm btn-success m-1 pay-button" data-uuid="'.$item->uuid.'">Pay</a>';
                    }
                    if(auth()->user()->can("View Leave Encashment Details")){
                        $html .= '<a href="'.route('leave-encashment.details',[$item->uuid]).'" class="btn btn-sm btn-primary m-1">Details</a>';
                    }
                    return $html;
                })
                ->rawColumns(['checkbox','division_name', 'department_name', 'payable_amount', 'prepared_by', 'divisional_approved_by', 'departmental_approved_by', 'hr_approved_by', 'accounts_approved_by', 'management_approved_by', 'payment_status', 'action'])
                ->make(true);
        }
        return view("leave_encashment.leave-encashment-list", compact('data'));
    }

    public function details(DepartmentLeaveEncashment $departmentLeaveEncashment){
        try {
            $departmentLeaveEncashment = $departmentLeaveEncashment->load("officeDivision", "department", "employeeLeaveEncashment",
                "employeeLeaveEncashment.employeeInformation", "employeeLeaveEncashment.employeeInformation.employeeStatusJoining","preparedBy",
                "divisionalApprovalBy", "departmentalApprovalBy", "hrApprovalBy", "accountsApprovalBy", "managerialApprovalBy");
            $earnings = Earning::all();
            $leave_types = LeaveType::where('is_paid','=',LeaveType::ENCASHMENT_PAID)->orderBy('priority')->get();
            $filter_obj = new FilterController();
            $divisionIds = $filter_obj->getDivisionIds(false, true);
            $departmentalSupervisorDepartments = DepartmentSupervisor::where('status', DepartmentSupervisor::STATUS_ACTIVE)
                ->where('supervised_by', auth()->user()->id)
                ->whereIn('office_division_id', $divisionIds)
                ->pluck('department_id')->toArray();
            $divisionalSupervisorDivisions = DivisionSupervisor::where('status', DivisionSupervisor::STATUS_ACTIVE)
                ->where('supervised_by', auth()->user()->id)
                ->whereIn('office_division_id', $divisionIds)
                ->pluck('office_division_id')->toArray();
            $divisionalSupervisorDepartments = Department::whereIn('office_division_id', $divisionalSupervisorDivisions)->pluck('id')->toArray();
            $departmentIds = array_unique(array_merge($divisionalSupervisorDepartments, $departmentalSupervisorDepartments));
            return view("leave_encashment.encashment-details", compact('departmentLeaveEncashment', 'earnings','divisionIds','departmentIds','leave_types'));
        }catch (Exception $exception){
            Log::info($exception->getMessage());
            Log::info($exception->getLine());
            session()->flash("type", "error");
            session()->flash("message", "Something went wrong!!");
            return redirect()->back();
        }
    }

    /**
     * @param Request $request
     * @return RedirectResponse
     */
    public function approvalDivisional(Request $request)
    {
        try {
            $pay = DepartmentLeaveEncashment::where('uuid','=',$request->input("uuid"))->first();
            if($pay->pay_status){
                session()->flash('type', 'error');
                session()->flash('message', 'Sorry!! Leave encashment paid already!!');
                return redirect()->back();
            }
            if(
                $pay->departmental_approval_status == DepartmentLeaveEncashment::APPROVAL_REJECTED ||
                $pay->hr_approval_status == DepartmentLeaveEncashment::APPROVAL_REJECTED ||
                $pay->accounts_approval_status == DepartmentLeaveEncashment::APPROVAL_REJECTED ||
                $pay->managerial_approval_status == DepartmentLeaveEncashment::APPROVAL_REJECTED
            ){
                session()->flash('type', 'error');
                session()->flash('message', 'Invalid Try!!');
                return redirect()->back();
            }
            DB::beginTransaction();
            DepartmentLeaveEncashment::where('uuid','=',$request->input("uuid"))->update([
                "divisional_approval_status"=> $request->input("divisional_status") === "approved" ? DepartmentLeaveEncashment::APPROVAL_CONFIRMED: DepartmentLeaveEncashment::APPROVAL_REJECTED,
                "divisional_approval_by"    => auth()->user()->id,
                "divisional_approved_date"    => date('Y-m-d H:i:s'),
                "divisional_remarks"        => $request->input("reject_reason"),
            ]);
            DB::commit();
            session()->flash('message', 'Success!! Thank you for your feedback!!');
        } catch (Exception $exception) {
            DB::rollBack();
            Log::info($exception->getMessage());
            session()->flash('type', 'error');
            session()->flash('message', 'Sorry! Something went wrong!!');
        }
        return redirect()->back();
    }

    /**
     * @param Request $request
     * @return RedirectResponse
     */
    public function approvalDepartmental(Request $request)
    {
        try {
            $pay = DepartmentLeaveEncashment::where('uuid','=',$request->input("uuid"))->first();
            if($pay->pay_status){
                session()->flash('type', 'error');
                session()->flash('message', 'Sorry!! Leave encashment paid already!!');
                return redirect()->back();
            }
            if(
                $pay->divisional_approval_status == DepartmentLeaveEncashment::APPROVAL_REJECTED ||
                $pay->hr_approval_status == DepartmentLeaveEncashment::APPROVAL_REJECTED ||
                $pay->accounts_approval_status == DepartmentLeaveEncashment::APPROVAL_REJECTED ||
                $pay->managerial_approval_status == DepartmentLeaveEncashment::APPROVAL_REJECTED
            ){
                session()->flash('type', 'error');
                session()->flash('message', 'Invalid Try!!');
                return redirect()->back();
            }
            DB::beginTransaction();
            DepartmentLeaveEncashment::where('uuid','=',$request->input("uuid"))->update([
                "departmental_approval_status"=> $request->input("departmental_status") === "approved" ? DepartmentLeaveEncashment::APPROVAL_CONFIRMED: DepartmentLeaveEncashment::APPROVAL_REJECTED,
                "departmental_approval_by"    => auth()->user()->id,
                "departmental_approved_date"    => date('Y-m-d H:i:s'),
                "departmental_remarks"        => $request->input("reject_reason"),
            ]);

            DB::commit();
            session()->flash('message', 'Success!! Thank you for your feedback!!');
        } catch (Exception $exception) {
            DB::rollBack();

            session()->flash('type', 'error');
            session()->flash('message', 'Sorry! Something went wrong!!');
        }
        return redirect()->back();
    }

    /**
     * @param Request $request
     * @return RedirectResponse
     */
    public function approvalHr(Request $request)
    {
        try {
            $pay = DepartmentLeaveEncashment::where('uuid','=',$request->input("uuid"))->first();
            if($pay->pay_status){
                session()->flash('type', 'error');
                session()->flash('message', 'Sorry!! Leave encashment paid already!!');
                return redirect()->back();
            }
            if(
                $pay->divisional_approval_status == DepartmentLeaveEncashment::APPROVAL_REJECTED ||
                $pay->departmental_approval_status == DepartmentLeaveEncashment::APPROVAL_REJECTED ||
                $pay->accounts_approval_status == DepartmentLeaveEncashment::APPROVAL_REJECTED ||
                $pay->managerial_approval_status == DepartmentLeaveEncashment::APPROVAL_REJECTED
            ){
                session()->flash('type', 'error');
                session()->flash('message', 'Invalid Try!!');
                return redirect()->back();
            }
            DB::beginTransaction();
            DepartmentLeaveEncashment::where('uuid','=',$request->input("uuid"))->update([
                "hr_approval_status"=> $request->input("hr_status") === "approved" ? DepartmentLeaveEncashment::APPROVAL_CONFIRMED: DepartmentLeaveEncashment::APPROVAL_REJECTED,
                "hr_approval_by"    => auth()->user()->id,
                "hr_approved_date"    => date('Y-m-d H:i:s'),
                "hr_remarks"        => $request->input("reject_reason"),
            ]);
            DB::commit();
            session()->flash('message', 'Success!! Thank you for your feedback!!');
        } catch (Exception $exception) {
            DB::rollBack();

            session()->flash('type', 'error');
            session()->flash('message', 'Sorry! Something went wrong!!');
        }
        return redirect()->back();
    }

    /**
     * @param Request $request
     * @return RedirectResponse
     */
    public function approvalAccounts(Request $request)
    {
        try {
            $pay = DepartmentLeaveEncashment::where('uuid','=',$request->input("uuid"))->first();
            if($pay->pay_status){
                session()->flash('type', 'error');
                session()->flash('message', 'Sorry!! Leave encashment paid already!!');
                return redirect()->back();
            }
            if(
                $pay->divisional_approval_status == DepartmentLeaveEncashment::APPROVAL_REJECTED ||
                $pay->departmental_approval_status == DepartmentLeaveEncashment::APPROVAL_REJECTED ||
                $pay->hr_approval_status == DepartmentLeaveEncashment::APPROVAL_REJECTED ||
                $pay->managerial_approval_status == DepartmentLeaveEncashment::APPROVAL_REJECTED
            ){
                session()->flash('type', 'error');
                session()->flash('message', 'Invalid Try!!');
                return redirect()->back();
            }
            DB::beginTransaction();
            DepartmentLeaveEncashment::where('uuid','=',$request->input("uuid"))->update([
                "accounts_approval_status"=> $request->input("accounts_status") === "approved" ? DepartmentLeaveEncashment::APPROVAL_CONFIRMED: DepartmentLeaveEncashment::APPROVAL_REJECTED,
                "accounts_approval_by"    => auth()->user()->id,
                "accounts_approved_date"    => date('Y-m-d H:i:s'),
                "accounts_remarks"        => $request->input("reject_reason"),
            ]);
            DB::commit();
            session()->flash('message', 'Success!! Thank you for your feedback!!');
        } catch (Exception $exception) {
            DB::rollBack();
            session()->flash('type', 'error');
            session()->flash('message', 'Sorry! Something went wrong!!');
        }
        return redirect()->back();
    }

    /**
     * @param Request $request
     * @return RedirectResponse
     */
    public function approvalManagerial(Request $request)
    {
        try {
            $pay = DepartmentLeaveEncashment::where('uuid','=',$request->input("uuid"))->first();
            if($pay->pay_status){
                session()->flash('type', 'error');
                session()->flash('message', 'Sorry!! Leave encashment paid already!!');
                return redirect()->back();
            }
            if(
                $pay->divisional_approval_status == DepartmentLeaveEncashment::APPROVAL_REJECTED ||
                $pay->departmental_approval_status == DepartmentLeaveEncashment::APPROVAL_REJECTED ||
                $pay->hr_approval_status == DepartmentLeaveEncashment::APPROVAL_REJECTED ||
                $pay->accounts_approval_status == DepartmentLeaveEncashment::APPROVAL_REJECTED
            ){
                session()->flash('type', 'error');
                session()->flash('message', 'Invalid Try!!');
                return redirect()->back();
            }
            DB::beginTransaction();
            DepartmentLeaveEncashment::where('uuid','=',$request->input("uuid"))->update([
                "managerial_approval_status"=> $request->input("managerial_status") === "approved" ? DepartmentLeaveEncashment::APPROVAL_CONFIRMED: DepartmentLeaveEncashment::APPROVAL_REJECTED,
                "managerial_approval_by"    => auth()->user()->id,
                "managerial_approved_date"    => date('Y-m-d H:i:s'),
                "managerial_remarks"        => $request->input("reject_reason"),
            ]);
            DB::commit();
            session()->flash('message', 'Success!! Thank you for your feedback!!');
        } catch (Exception $exception) {
            DB::rollBack();

            session()->flash('type', 'error');
            session()->flash('message', 'Sorry! Something went wrong!!');
        }
        return redirect()->back();
    }

    /**
     * @param Request $request
     * @return RedirectResponse
     */
    public function payLeaveEncashmentToDepartment(Request $request)
    {
        try {
            DB::beginTransaction();
            $dept = $request->department_uuid;
            $encashmentDepartment = DepartmentLeaveEncashment::where('uuid','=',$dept)->first();
            if(($encashmentDepartment->divisional_approval_status == DepartmentLeaveEncashment::APPROVAL_CONFIRMED || $encashmentDepartment->departmental_approval_status == DepartmentLeaveEncashment::APPROVAL_CONFIRMED)
                && ($encashmentDepartment->divisional_approval_status != DepartmentLeaveEncashment::APPROVAL_REJECTED && $encashmentDepartment->departmental_approval_status != DepartmentLeaveEncashment::APPROVAL_REJECTED)
                && ($encashmentDepartment->hr_approval_status == DepartmentLeaveEncashment::APPROVAL_CONFIRMED)
                && ($encashmentDepartment->accounts_approval_status == DepartmentLeaveEncashment::APPROVAL_CONFIRMED)
                && ($encashmentDepartment->managerial_approval_status == DepartmentLeaveEncashment::APPROVAL_CONFIRMED)){
                if($encashmentDepartment->pay_status != DepartmentLeaveEncashment::APPROVAL_CONFIRMED){
                    $encashmentDepartment->update([
                        "pay_status" => DepartmentLeaveEncashment::APPROVAL_CONFIRMED,
                        "paid_at"    => now()
                    ]);
                    DB::commit();
                    session()->flash('message', 'Leave Encashment Paid Successfully');
                }else{
                    DB::rollBack();
                    session()->flash('type', 'warning');
                    session()->flash('message', 'Already Paid!');
                }
            }else{
                DB::rollBack();
                session()->flash('type', 'warning');
                session()->flash('message', 'Approval Pending!');
            }
        } catch (Exception $exception) {
            DB::rollBack();
            $message = "ERROR: Pay leave encashment to department " . $exception->getMessage() . " at line no " . $exception->getLine();
            Log::error($message);
            session()->flash('type', 'error');
            session()->flash('message', 'Sorry! Something went wrong!!');
        }
        return redirect()->back();
    }

    /**
     * @param Request $request
     * @param DepartmentLeaveEncashment $departmentLeaveEncashment
     * @return BinaryFileResponse
     * @throws Exception
     */
    public function exportLeaveEncashment(Request $request, DepartmentLeaveEncashment $departmentLeaveEncashment)
    {
        $departmentLeaveEncashment = $departmentLeaveEncashment->load("officeDivision", "department", "employeeLeaveEncashment",
            "employeeLeaveEncashment.employeeInformation", "employeeLeaveEncashment.employeeInformation.employeeStatusJoining","preparedBy", "employeeLeaveEncashment.employeeInformation.currentBank",
            "divisionalApprovalBy", "departmentalApprovalBy", "hrApprovalBy", "accountsApprovalBy", "managerialApprovalBy");
        $earnings = Earning::all();
        $leave_types = LeaveType::where('is_paid','=',LeaveType::ENCASHMENT_PAID)->orderBy('priority')->get();
        if($request->input("type") == "Export Excel" AND auth()->user()->can("Export Leave Encashment EXCEL")) {
            $total_head = 11+count($earnings)+($leave_types->count()*4);
            $spreadsheet = new Spreadsheet();
            $spreadsheet->getActiveSheet()
                ->mergeCells($this->cellsToMergeByColsRow(1,$total_head,1,1))
                ->setCellValueByColumnAndRow(1, 1, "BYSL Global Technologies Limited")
                ->getStyleByColumnAndRow(1, 1)
                ->getFill()
                ->setFillType(Fill::FILL_SOLID)
                ->getStartColor()
                ->setRGB('fff');
            $spreadsheet->getActiveSheet()
                ->mergeCells($this->cellsToMergeByColsRow(1,$total_head,2,2))
                ->setCellValueByColumnAndRow(1, 2, strtoupper($departmentLeaveEncashment->officeDivision->name))
                ->getStyleByColumnAndRow(1, 2)
                ->getFill()
                ->setFillType(Fill::FILL_SOLID)
                ->getStartColor()
                ->setRGB('D9D9D9');
            $spreadsheet->getActiveSheet()
                ->mergeCells($this->cellsToMergeByColsRow(1,$total_head,3,3))
                ->setCellValueByColumnAndRow(1, 3, 'Leave Encashment Sheet: '.strtoupper($departmentLeaveEncashment->department->name))
                ->getStyleByColumnAndRow(1, 3)
                ->getFill()
                ->setFillType(Fill::FILL_SOLID)
                ->getStartColor()
                ->setRGB('D9D9D9');
            $spreadsheet->getActiveSheet()
                ->mergeCells($this->cellsToMergeByColsRow(1,5,4,4))
                ->setCellValueByColumnAndRow(1, 4, 'Leave Encashment for The Year of:')
                ->getStyleByColumnAndRow(1, 4)
                ->getFill()
                ->setFillType(Fill::FILL_SOLID)
                ->getStartColor()
                ->setRGB('e3e3e3');
            $spreadsheet->getActiveSheet()
                ->mergeCells($this->cellsToMergeByColsRow(6,6,4,4))
                ->setCellValueByColumnAndRow(6, 4, $departmentLeaveEncashment->year)
                ->getStyleByColumnAndRow(6, 4)
                ->getFill()
                ->setFillType(Fill::FILL_SOLID)
                ->getStartColor()
                ->setRGB('fff');
            $spreadsheet->getActiveSheet()
                ->mergeCells($this->cellsToMergeByColsRow(7,8,4,4))
                ->setCellValueByColumnAndRow(7, 4, 'Eligible Month:')
                ->getStyleByColumnAndRow(7, 4)
                ->getFill()
                ->setFillType(Fill::FILL_SOLID)
                ->getStartColor()
                ->setRGB('e3e3e3');
            $spreadsheet->getActiveSheet()
                ->mergeCells($this->cellsToMergeByColsRow(9,9,4,4))
                ->setCellValueByColumnAndRow(9, 4, $departmentLeaveEncashment->eligible_month)
                ->getStyleByColumnAndRow(9, 4)
                ->getFill()
                ->setFillType(Fill::FILL_SOLID)
                ->getStartColor()
                ->setRGB('fff');
            $spreadsheet->getActiveSheet()
                ->mergeCells($this->cellsToMergeByColsRow(10,11,4,4))
                ->setCellValueByColumnAndRow(10, 4, 'Preparation Date:')
                ->getStyleByColumnAndRow(10, 4)
                ->getFill()
                ->setFillType(Fill::FILL_SOLID)
                ->getStartColor()
                ->setRGB('e3e3e3');
            $spreadsheet->getActiveSheet()
                ->mergeCells($this->cellsToMergeByColsRow(12,13,4,4))
                ->setCellValueByColumnAndRow(12, 4, date('M d, Y', strtotime($departmentLeaveEncashment->created_at)))
                ->getStyleByColumnAndRow(12, 4)
                ->getFill()
                ->setFillType(Fill::FILL_SOLID)
                ->getStartColor()
                ->setRGB('fff');
            $spreadsheet->getActiveSheet()
                ->mergeCells($this->cellsToMergeByColsRow(14,$total_head,4,4))
                ->setCellValueByColumnAndRow(14, 4, '')
                ->getStyleByColumnAndRow(14, 4)
                ->getFill()
                ->setFillType(Fill::FILL_SOLID)
                ->getStartColor()
                ->setRGB('e3e3e3');
            $spreadsheet->getActiveSheet()
                ->mergeCells($this->cellsToMergeByColsRow(1,$total_head,5,5))
                ->setCellValueByColumnAndRow(1, 5, '')
                ->getStyleByColumnAndRow(1, 5)
                ->getFill()
                ->setFillType(Fill::FILL_SOLID)
                ->getStartColor()
                ->setRGB('fff');
            $header = array(
                'SL. No.',
                'ID. No',
                'Name',
                'Designation',
                'Joining Date',
                'Basic (Tk.)'
            );

            foreach($earnings as $earn){
                $header[]=$earn->name;
            }
            $header1 = array(
                'Gross Salary (Tk.)',
                'Salary (per day)'
            );
            $header = array_merge($header,$header1);
            $col=1;
            $row=6;
            foreach ($header as $val) {
                $spreadsheet->getActiveSheet()
                    ->mergeCells($this->cellsToMergeByColsRow($col,$col,$row,$row+1))
                    ->setCellValueByColumnAndRow($col, $row, $val)
                    ->getStyleByColumnAndRow($col, $row)
                    ->getFill()
                    ->setFillType(Fill::FILL_SOLID)
                    ->getStartColor()
                    ->setRGB('e3e3e3');
                $col++;
            }
            foreach ($leave_types as $type_leave){
                $spreadsheet->getActiveSheet()
                    ->mergeCells($this->cellsToMergeByColsRow($col,$col+3,$row,$row))
                    ->setCellValueByColumnAndRow($col, $row, $type_leave->name)
                    ->getStyleByColumnAndRow($col, $row)
                    ->getFill()
                    ->setFillType(Fill::FILL_SOLID)
                    ->getStartColor()
                    ->setRGB('e3e3e3');

                $spreadsheet->getActiveSheet()
                    ->mergeCells($this->cellsToMergeByColsRow($col,$col,$row+1,$row+1))
                    ->setCellValueByColumnAndRow($col, $row+1, 'Total Leave')
                    ->getStyleByColumnAndRow($col, $row+1)
                    ->getFill()
                    ->setFillType(Fill::FILL_SOLID)
                    ->getStartColor()
                    ->setRGB('e3e3e3');
                $spreadsheet->getActiveSheet()
                    ->mergeCells($this->cellsToMergeByColsRow($col+1,$col+1,$row+1,$row+1))
                    ->setCellValueByColumnAndRow($col+1, $row+1, 'Consume Leave')
                    ->getStyleByColumnAndRow($col+1, $row+1)
                    ->getFill()
                    ->setFillType(Fill::FILL_SOLID)
                    ->getStartColor()
                    ->setRGB('e3e3e3');
                $spreadsheet->getActiveSheet()
                    ->mergeCells($this->cellsToMergeByColsRow($col+2,$col+2,$row+1,$row+1))
                    ->setCellValueByColumnAndRow($col+2, $row+1, 'Encashment Leave')
                    ->getStyleByColumnAndRow($col+2, $row+1)
                    ->getFill()
                    ->setFillType(Fill::FILL_SOLID)
                    ->getStartColor()
                    ->setRGB('e3e3e3');
                $spreadsheet->getActiveSheet()
                    ->mergeCells($this->cellsToMergeByColsRow($col+3,$col+3,$row+1,$row+1))
                    ->setCellValueByColumnAndRow($col+3, $row+1, 'Payable (Tk.)')
                    ->getStyleByColumnAndRow($col+3, $row+1)
                    ->getFill()
                    ->setFillType(Fill::FILL_SOLID)
                    ->getStartColor()
                    ->setRGB('e3e3e3');
                $col = $col+4;
            }
            $header2 = array(
                'Tax Amount (Tk.)',
                'Net Payable (Tk.)',
                'Payment Mode'
            );
            foreach ($header2 as $val) {
                $spreadsheet->getActiveSheet()
                    ->mergeCells($this->cellsToMergeByColsRow($col,$col,$row,$row+1))
                    ->setCellValueByColumnAndRow($col, $row, $val)
                    ->getStyleByColumnAndRow($col, $row)
                    ->getFill()
                    ->setFillType(Fill::FILL_SOLID)
                    ->getStartColor()
                    ->setRGB('e3e3e3');
                $col++;
            }
            $col=1;
            $row=8;
            $earn_ar = [];
            $earn_total = [];
            $total = 0;
            $total_basic_salary_amount = 0;
            $total_gross_salary_amount = 0;
            $total_net_payable = 0;
            foreach($earnings as $earn){
                $earn_total[$earn->id]=0;
                $earn_ar[$earn->id] = $earn->id;
            }
            foreach($departmentLeaveEncashment->employeeLeaveEncashment as $key => $data){
                $earning_amounts = (Array) json_decode($data->earning_amounts);
                $leave_details = (Array) json_decode($data->leave_details);
                $total += $data->total_payable_amount;
                $total_basic_salary_amount += $data->basic_salary_amount;
                $total_gross_salary_amount += $data->gross_salary_amount;
                $total_net_payable += $data->total_payable_amount;

                $spreadsheet->getActiveSheet()
                    ->setCellValueByColumnAndRow($col, $row, ($key + 1));
                $col++;
                $spreadsheet->getActiveSheet()
                    ->setCellValueByColumnAndRow($col, $row, $data->employeeInformation->fingerprint_no);
                $col++;
                $spreadsheet->getActiveSheet()
                    ->setCellValueByColumnAndRow($col, $row, $data->employeeInformation->name);
                $col++;
                $spreadsheet->getActiveSheet()
                    ->setCellValueByColumnAndRow($col, $row, $data->designation_name);
                $col++;
                $spreadsheet->getActiveSheet()
                    ->setCellValueByColumnAndRow($col, $row, date('M d, Y', strtotime($data->employeeInformation->employeeStatusJoining->action_date)));
                $col++;
                $spreadsheet->getActiveSheet()
                    ->setCellValueByColumnAndRow($col, $row, currencyFormat($data->basic_salary_amount));
                $col++;
                foreach($earnings as $earn){
                    if(isset($earning_amounts[$earn->id])){
                        $earn_total[$earn->id]+=$earning_amounts[$earn->id];
                        $spreadsheet->getActiveSheet()
                            ->setCellValueByColumnAndRow($col, $row, currencyFormat($earning_amounts[$earn->id]));
                        $col++;
                    }else{
                        $spreadsheet->getActiveSheet()
                            ->setCellValueByColumnAndRow($col, $row, 'N/A');
                        $col++;
                    }
                }
                $spreadsheet->getActiveSheet()
                    ->setCellValueByColumnAndRow($col, $row, currencyFormat($data->gross_salary_amount));
                $col++;
                $spreadsheet->getActiveSheet()
                    ->setCellValueByColumnAndRow($col, $row, currencyFormat($data->per_day_salary_amount));
                $col++;
                foreach($leave_types as $type){
                    if(isset($leave_details[$type->id])) {
                        $x = $leave_details[$type->id]->total_leave_amount;
                    }else{
                        $x = 0;
                    }
                    $spreadsheet->getActiveSheet()
                        ->setCellValueByColumnAndRow($col, $row, $x);
                    $col++;
                    if(isset($leave_details[$type->id])) {
                        $x = $leave_details[$type->id]->consumed_leave_amount;
                    }else{
                        $x = 0;
                    }
                    $spreadsheet->getActiveSheet()
                        ->setCellValueByColumnAndRow($col, $row, $x);
                    $col++;
                    if(isset($leave_details[$type->id])) {
                        $x = $leave_details[$type->id]->leave_balance;
                    }else{
                        $x = 0;
                    }
                    $spreadsheet->getActiveSheet()
                        ->setCellValueByColumnAndRow($col, $row, $x);
                    $col++;
                    if(isset($leave_details[$type->id])) {
                        $x = $leave_details[$type->id]->payable_amount;
                    }else{
                        $x = 0;
                    }
                    $spreadsheet->getActiveSheet()
                        ->setCellValueByColumnAndRow($col, $row, currencyFormat($x));
                    $col++;
                }
                $spreadsheet->getActiveSheet()
                    ->setCellValueByColumnAndRow($col, $row, currencyFormat($data->tax_amount));
                $col++;
                $spreadsheet->getActiveSheet()
                    ->setCellValueByColumnAndRow($col, $row, currencyFormat($data->total_payable_amount));
                $col++;
                $spreadsheet->getActiveSheet()
                    ->setCellValueByColumnAndRow($col, $row, $departmentLeaveEncashment->payment_mode);
                $row++;
                $col=1;
            }
            $spreadsheet->getActiveSheet()
                ->mergeCells($this->cellsToMergeByColsRow($col,$col+4,$row,$row))
                ->setCellValueByColumnAndRow($col, $row, 'TOTAL')
                ->getStyleByColumnAndRow($col, $row);
            $spreadsheet->getActiveSheet()
                ->mergeCells($this->cellsToMergeByColsRow($col+5,$col+5,$row,$row))
                ->setCellValueByColumnAndRow($col+5, $row, currencyFormat($total_basic_salary_amount))
                ->getStyleByColumnAndRow($col+5, $row);
            $col = $col+6;
            foreach($earnings as $earn){
                $spreadsheet->getActiveSheet()
                    ->setCellValueByColumnAndRow($col, $row, currencyFormat($earn_total[$earn->id]));
                $col++;
            }
            $spreadsheet->getActiveSheet()
                ->mergeCells($this->cellsToMergeByColsRow($col,$col,$row,$row))
                ->setCellValueByColumnAndRow($col, $row, currencyFormat($total_gross_salary_amount))
                ->getStyleByColumnAndRow($col, $row);
            $rest1 = $leave_types->count()*4;
            $spreadsheet->getActiveSheet()
                ->mergeCells($this->cellsToMergeByColsRow($col+1,$col+$rest1+1,$row,$row))
                ->setCellValueByColumnAndRow($col+1, $row, 'Total Net Payable')
                ->getStyleByColumnAndRow($col+1, $row);
            $col = $col+$rest1+3;
            $spreadsheet->getActiveSheet()
                ->mergeCells($this->cellsToMergeByColsRow($col,$col,$row,$row))
                ->setCellValueByColumnAndRow($col, $row, currencyFormat($total_net_payable))
                ->getStyleByColumnAndRow($col, $row);
            $row++;
            $col=1;
            $spreadsheet->getActiveSheet()
                ->mergeCells($this->cellsToMergeByColsRow($col,$col+4,$row,$row))
                ->setCellValueByColumnAndRow($col, $row, 'TOTAL IN WORDS')
                ->getStyleByColumnAndRow($col, $row)
                ->getFill()
                ->setFillType(Fill::FILL_SOLID)
                ->getStartColor()
                ->setRGB('e3e3e3');
            $spreadsheet->getActiveSheet()
                ->mergeCells($this->cellsToMergeByColsRow($col+5,$total_head,$row,$row))
                ->setCellValueByColumnAndRow($col+5, $row, getBangladeshCurrency($total))
                ->getStyleByColumnAndRow($col+5, $row)
                ->getFill()
                ->setFillType(Fill::FILL_SOLID)
                ->getStartColor()
                ->setRGB('e3e3e3');
            $spreadsheet->getActiveSheet()
                ->getStyle("A1:S$row")
                ->getAlignment()
                ->setVertical(Alignment::VERTICAL_CENTER)
                ->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $writer = new Xlsx($spreadsheet);
            $response =  new StreamedResponse(
                function () use ($writer) {
                    $writer->save('php://output');
                }
            );
            $fileName = 'leave-encashment-sheet-' . $departmentLeaveEncashment->department->name . '-' . $departmentLeaveEncashment->year;
            $response->headers->set('Content-Type', 'application/vnd.ms-excel');
            $response->headers->set('Content-Disposition', 'attachment;filename="'.$fileName.'.xlsx"');
            $response->headers->set('Cache-Control','max-age=0');
            return $response;
        }

        # Encashment PDF
        if($request->input("type") === "Export PDF" AND auth()->user()->can("Export Leave Encashment PDF")) {
            $fileName = 'leave-encashment-sheet-' . $departmentLeaveEncashment->department->name . '-' . $departmentLeaveEncashment->year;
            $fileName .= '.pdf';
            $page_name = "leave_encashment.export.leave-encashment-pdf";
            $pdf = PDF::loadView($page_name, compact("departmentLeaveEncashment","earnings", "leave_types"));
            return $pdf->setPaper('a2', 'landscape')->download($fileName);
        }

        # Bank Statement PDF
        if($request->input("type") === "Bank Statement PDF" AND auth()->user()->can("Export Leave Encashment Bank Statement PDF")) {
            $fileName = 'leave-encashment-bank-statement-sheet-' . $departmentLeaveEncashment->department->name . '-' . $departmentLeaveEncashment->year;
            $fileName .= '.pdf';
            $page_name = "leave_encashment.export.leave_encashment_export_bank_statement_pdf";
            $pdf = PDF::loadView($page_name, compact("departmentLeaveEncashment"));
            return $pdf->setPaper('a4', 'landscape')->download($fileName);
        }

        # Bank Statement Excel
        if($request->input("type") === "Bank Statement Excel" AND auth()->user()->can("Export Leave Encashment Bank Statement EXCEL")) {
            $spreadsheet = new Spreadsheet();
            $spreadsheet->getActiveSheet()
                ->mergeCells($this->cellsToMergeByColsRow(1,5,1,2))
                ->setCellValueByColumnAndRow(1, 1, "BYSL Global Technologies Limited")
                ->getStyleByColumnAndRow(1, 1)
                ->getFill()
                ->setFillType(Fill::FILL_SOLID)
                ->getStartColor()
                ->setRGB('d3d3d3');
            $col = 1;
            $row = 3;
            $header = array(
                'SL. No.',
                'ID. No',
                'Account Name',
                'Account No.',
                'Amount in Taka'
            );
            foreach ($header as $val) {
                $spreadsheet->getActiveSheet()
                    ->mergeCells($this->cellsToMergeByColsRow($col,$col,$row,$row+1))
                    ->setCellValueByColumnAndRow($col, $row, $val)
                    ->getStyleByColumnAndRow($col, $row)
                    ->getFill()
                    ->setFillType(Fill::FILL_SOLID)
                    ->getStartColor()
                    ->setRGB('D9D9D9');
                $col++;
            }

            $spreadsheet->getActiveSheet()
                ->getStyle("A1:E4")
                ->getAlignment()
                ->setVertical(Alignment::VERTICAL_CENTER)
                ->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $col=1;
            $row=5;
            $total = 0;
            foreach($departmentLeaveEncashment->employeeLeaveEncashment as $key => $data){
                $total += $data->total_payable_amount;
                $spreadsheet->getActiveSheet()
                    ->setCellValueByColumnAndRow($col, $row, ($key + 1));
                $col++;
                $spreadsheet->getActiveSheet()
                    ->setCellValueByColumnAndRow($col, $row, $data->employeeInformation->fingerprint_no);
                $col++;
                $spreadsheet->getActiveSheet()
                    ->setCellValueByColumnAndRow($col, $row, $data->employeeInformation->currentBank->account_name ?? "");
                $col++;
                $spreadsheet->getActiveSheet()
                    ->setCellValueByColumnAndRow($col, $row, $data->employeeInformation->currentBank->account_no ?? "");
                $col++;
                $spreadsheet->getActiveSheet()
                    ->setCellValueByColumnAndRow($col, $row, currencyFormat($data->total_payable_amount));
                $row++;
                $col=1;
            }
            $spreadsheet->getActiveSheet()
                ->mergeCells($this->cellsToMergeByColsRow($col,$col+3,$row,$row))
                ->setCellValueByColumnAndRow(1, $row, "TOTAL");
            $spreadsheet->getActiveSheet()
                ->setCellValueByColumnAndRow(5, $row, currencyFormat($total));
            $spreadsheet->getActiveSheet()
                ->getStyle("A$row:D$row")
                ->getAlignment()
                ->setVertical(Alignment::VERTICAL_CENTER)
                ->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $spreadsheet->getActiveSheet()
                ->getStyleByColumnAndRow($col, $row)
                ->getFill()
                ->setFillType(Fill::FILL_SOLID)
                ->getStartColor()
                ->setRGB('D9D9D9');
            $spreadsheet->getActiveSheet()
                ->getStyleByColumnAndRow(5, $row)
                ->getFill()
                ->setFillType(Fill::FILL_SOLID)
                ->getStartColor()
                ->setRGB('D9D9D9');
            $fileName = 'leave-encashment-bank-statement-sheet-' . $departmentLeaveEncashment->department->name . '-' . $departmentLeaveEncashment->year;
            $writer = new Xlsx($spreadsheet);
            $response =  new StreamedResponse(
                function () use ($writer) {
                    $writer->save('php://output');
                }
            );
            $response->headers->set('Content-Type', 'application/vnd.ms-excel');
            $response->headers->set('Content-Disposition', 'attachment;filename="'.$fileName.'.xlsx"');
            $response->headers->set('Cache-Control','max-age=0');
            return $response;
        }
    }

    function cellsToMergeByColsRow($start = -1, $end = -1, $row1 = -1,$row2 = -1){
        $merge = 'A1:A1';
        if($start>=0 && $end>=0 && $row1>=0 && $row2>=0){
            $start = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($start);
            $end = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($end);
            $merge = "$start{$row1}:$end{$row2}";
        }
        return $merge;
    }

    public function getDepartmentAndEmployeeByOfficeDivision(Request $request){
        $filter_obj = new FilterController();
        if (auth()->user()->can('Show All Encashment List')) {
            if($request->office_division_id=='all'){
                $departments = Department::select("id", "name")->get();
            }else{
                $departments = Department::where("office_division_id", '=', $request->office_division_id)->select("id", "name")->get();
            }
        }else{
            if($request->office_division_id=='all'){
                $divisionIds = $filter_obj->getDivisionIds();
                $departmentalSupervisorDepartments = DepartmentSupervisor::where('status', DepartmentSupervisor::STATUS_ACTIVE)
                    ->where('supervised_by', auth()->user()->id)
                    ->whereIn("office_division_id", $divisionIds)
                    ->pluck('department_id')->toArray();
                $divisionalSupervisorDivisions = DivisionSupervisor::where('status', DivisionSupervisor::STATUS_ACTIVE)
                    ->where('supervised_by', auth()->user()->id)
                    ->whereIn("office_division_id", $divisionIds)
                    ->pluck('office_division_id')->toArray();
                $divisionalSupervisorDepartments = Department::whereIn('office_division_id', $divisionalSupervisorDivisions)->pluck('id')->toArray();
                $departmentIds = array_unique(array_merge($divisionalSupervisorDepartments, $departmentalSupervisorDepartments));
                $departments = Department::select("id", "name")->whereIn('id',$departmentIds)->get();
            }else{
                $departmentalSupervisorDepartments = DepartmentSupervisor::where('status', DepartmentSupervisor::STATUS_ACTIVE)
                    ->where('supervised_by', auth()->user()->id)
                    ->where("office_division_id", '=', $request->office_division_id)
                    ->pluck('department_id')->toArray();
                $divisionalSupervisorDivisions = DivisionSupervisor::where('status', DivisionSupervisor::STATUS_ACTIVE)
                    ->where('supervised_by', auth()->user()->id)
                    ->where("office_division_id", '=', $request->office_division_id)
                    ->pluck('office_division_id')->toArray();
                $divisionalSupervisorDepartments = Department::whereIn('office_division_id', $divisionalSupervisorDivisions)->pluck('id')->toArray();
                $departmentIds = array_unique(array_merge($divisionalSupervisorDepartments, $departmentalSupervisorDepartments));
                $departments = Department::select("id", "name")->whereIn('id',$departmentIds)->get();
            }
        }
        return response()->json(["departments" => $departments]);
    }


    public function exportFileLeaveEncashment(Request $request)
    {
        $ids = explode(',',$request->ids);
        $departmentLeaveEncashment = DepartmentLeaveEncashment::whereIN('id',$ids)->with("officeDivision", "department", "employeeLeaveEncashment",
            "employeeLeaveEncashment.employeeInformation", "employeeLeaveEncashment.employeeInformation.employeeStatusJoining","preparedBy", "employeeLeaveEncashment.employeeInformation.currentBank",
            "divisionalApprovalBy", "departmentalApprovalBy", "hrApprovalBy", "accountsApprovalBy", "managerialApprovalBy")->get();

        # Bank Statement PDF
        if($request->input("export_file_type") === "PDF" AND auth()->user()->can("Export Leave Encashment Bank Statement PDF")) {
            $fileName = 'combined-leave-encashment-bank-statement-sheet';
            $fileName .= '.pdf';
            $page_name = "leave_encashment.export.multiple_leave_encashment_export_bank_statement_pdf";
            $pdf = PDF::loadView($page_name, compact("departmentLeaveEncashment"));
            return $pdf->setPaper('a4', 'landscape')->download($fileName);
        }
        # Bank Statement Excel
        if($request->input("export_file_type") === "Excel" AND auth()->user()->can("Export Leave Encashment Bank Statement EXCEL")) {
            $spreadsheet = new Spreadsheet();
            $spreadsheet->getActiveSheet()
                ->mergeCells($this->cellsToMergeByColsRow(1,6,1,2))
                ->setCellValueByColumnAndRow(1, 1, "BYSL Global Technologies Limited")
                ->getStyleByColumnAndRow(1, 1)
                ->getFill()
                ->setFillType(Fill::FILL_SOLID)
                ->getStartColor()
                ->setRGB('d3d3d3');
            $col = 1;
            $row = 3;
            $header = array(
                'SL. No.',
                'Fingerprint No',
                'Department',
                'Account Name',
                'Account No.',
                'Amount in Taka'
            );
            foreach ($header as $val) {
                $spreadsheet->getActiveSheet()
                    ->mergeCells($this->cellsToMergeByColsRow($col,$col,$row,$row+1))
                    ->setCellValueByColumnAndRow($col, $row, $val)
                    ->getStyleByColumnAndRow($col, $row)
                    ->getFill()
                    ->setFillType(Fill::FILL_SOLID)
                    ->getStartColor()
                    ->setRGB('D9D9D9');
                $col++;
            }

            $spreadsheet->getActiveSheet()
                ->getStyle("A1:E4")
                ->getAlignment()
                ->setVertical(Alignment::VERTICAL_CENTER)
                ->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $col=1;
            $row=5;
            $total = 0;

            $index=0;
            foreach($departmentLeaveEncashment as $dataM){
                foreach($dataM->employeeLeaveEncashment as $data){
                    $total += $data->total_payable_amount;
                    $spreadsheet->getActiveSheet()
                        ->setCellValueByColumnAndRow($col, $row, ++$index);
                    $col++;
                    $spreadsheet->getActiveSheet()
                        ->setCellValueByColumnAndRow($col, $row, $data->employeeInformation->fingerprint_no);
                    $col++;
                    $spreadsheet->getActiveSheet()
                        ->setCellValueByColumnAndRow($col, $row, $dataM->department->name);
                    $col++;
                    $spreadsheet->getActiveSheet()
                        ->setCellValueByColumnAndRow($col, $row, $data->employeeInformation->currentBank->account_name ?? "");
                    $col++;
                    $spreadsheet->getActiveSheet()
                        ->setCellValueByColumnAndRow($col, $row, $data->employeeInformation->currentBank->account_no ?? "");
                    $col++;
                    $spreadsheet->getActiveSheet()
                        ->setCellValueByColumnAndRow($col, $row, currencyFormat($data->total_payable_amount));
                    $row++;
                    $col=1;
                }
            }




            $spreadsheet->getActiveSheet()
                ->mergeCells($this->cellsToMergeByColsRow($col,$col+4,$row,$row))
                ->setCellValueByColumnAndRow(1, $row, "TOTAL");
            $spreadsheet->getActiveSheet()
                ->setCellValueByColumnAndRow(6, $row, currencyFormat($total));
            $spreadsheet->getActiveSheet()
                ->getStyle("A$row:D$row")
                ->getAlignment()
                ->setVertical(Alignment::VERTICAL_CENTER)
                ->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $spreadsheet->getActiveSheet()
                ->getStyleByColumnAndRow($col, $row)
                ->getFill()
                ->setFillType(Fill::FILL_SOLID)
                ->getStartColor()
                ->setRGB('D9D9D9');
            $spreadsheet->getActiveSheet()
                ->getStyleByColumnAndRow(5, $row)
                ->getFill()
                ->setFillType(Fill::FILL_SOLID)
                ->getStartColor()
                ->setRGB('D9D9D9');
            $fileName = 'combined-leave-encashment-bank-statement-sheet';
            $writer = new Xlsx($spreadsheet);
            $response =  new StreamedResponse(
                function () use ($writer) {
                    $writer->save('php://output');
                }
            );
            $response->headers->set('Content-Type', 'application/vnd.ms-excel');
            $response->headers->set('Content-Disposition', 'attachment;filename="'.$fileName.'.xlsx"');
            $response->headers->set('Cache-Control','max-age=0');
            return $response;
        }
    }

}
