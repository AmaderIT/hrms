<?php

namespace App\Http\Controllers;

use App\Exports\Report\SalaryReportExport;
use App\Exports\Report\SalarySheetExport;
use App\Models\SalaryDepartment;
use Exception;
use App\Models\User;
use App\Models\Salary;
use App\Models\Department;
use App\Models\DepartmentSupervisor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class SalaryReportController extends Controller
{
    protected $allowedStr = '/[^A-Za-z0-9\-\.]/';

    public function generateSalaryReportView(Request $request)
    {
        if(!isset($request->filter_type) || empty($request->filter_type)) {
            return redirect()->back()->withErrors('Select atleast one checkbox in "Report type" field!!!')->withInput();
        }

        try {

            $office_division_id = $request->input('office_division_id');
            $month_year = $request->input('datepicker');
            $department_ids = $request->input('department_id');
            $user_ids = $request->input('user_id');
            $filter=[];
            $filter['office_division_id']=$office_division_id;
            $filter['department_id']=$department_ids;
            $filter['user_id']=$user_ids;
            $filter['datepicker']=$month_year;
            $month_year = explode("-", $month_year);
            $month = $month_year[0];
            $year = $month_year[1];
            $dateObj = \DateTime::createFromFormat('!m', $month);
            $monthName = $dateObj->format('F');
            $monthAndYear = $monthName . ", " . $year;
            $YearAndmonth = $year."-".$month;
            $date = $year . "-" . $month . "-" . "01";
            $firstDateOfMonth = $date;
            $date = new \DateTime($date);
            $date = $date->format('t');
            $lastDayOfMonth = (int)$date;
            $lastDateOfMonth = $year . "-" . $month . "-" . $lastDayOfMonth;
            if($office_division_id=='all') {
                $find_division=true;
            } else {
                $find_division=false;
            }
            if(in_array("all", $department_ids)) {
                $find_department=true;
            } else {
                $find_department=false;
            }
            if (in_array("all", $user_ids)) {
                $find_employee=true;
            } else {
                $find_employee=false;
            }

            /*if($find_employee){
                if($find_department){
                    if (auth()->user()->can('Show All Salary List')) {
                        if ($find_division) {
                            $sql = "SELECT users.id, users.`name`, users.email, users.fingerprint_no,employee_status.action_date, prm.department_id, prm.office_division_id, departments.`name` as department_name, office_divisions.`name` as division_name FROM `users` LEFT JOIN employee_status ON employee_status.user_id = users.id AND employee_status.id =( SELECT MAX( es.id) FROM `employee_status` AS es WHERE es.user_id = users.id AND es.action_reason_id = 2 ) INNER JOIN promotions as prm ON prm.user_id = users.id AND prm.id = (SELECT MAX( pm.id ) FROM `promotions` AS pm where pm.user_id = users.id) INNER JOIN departments ON departments.id = prm.department_id INNER JOIN office_divisions ON office_divisions.id = prm.office_division_id WHERE users.`status`=1";
                            $users = DB::select($sql);
                        } else {
                            $sql = "SELECT users.id, users.`name`, users.email, users.fingerprint_no,employee_status.action_date, prm.department_id, prm.office_division_id, departments.`name` as department_name, office_divisions.`name` as division_name FROM `users` LEFT JOIN employee_status ON employee_status.user_id = users.id AND employee_status.id =( SELECT MAX( es.id) FROM `employee_status` AS es WHERE es.user_id = users.id AND es.action_reason_id = 2 ) INNER JOIN promotions as prm ON prm.user_id = users.id AND prm.id = (SELECT MAX( pm.id ) FROM `promotions` AS pm where pm.user_id = users.id) INNER JOIN departments ON departments.id = prm.department_id INNER JOIN office_divisions ON office_divisions.id = prm.office_division_id WHERE `users`.`id` IN ( SELECT `promotions`.`user_id` FROM `promotions` WHERE `promotions`.`office_division_id` IN ( $office_division_id ) AND `promotions`.`id` IN ( SELECT MAX( `p`.`id` ) FROM `promotions` AS `p` GROUP BY `p`.user_id )) AND `users`.`status`=1";
                            $users = DB::select($sql);
                        }
                    }elseif(auth()->user()->hasRole([User::ROLE_SUPERVISOR])) {
                        if($find_division){
                            $permit_info = DepartmentSupervisor::where("supervised_by", auth()->user()->id)->active()->get();
                            $departmentIds=[];
                            foreach($permit_info as $info){
                                $departmentIds[]=$info->department_id;
                            }
                            $departmentIds_in_string = implode(',',$departmentIds);
                            $users = getEmployeesInformationByDepartmentIDs($departmentIds_in_string);
                        }else{
                            $permit_info = DepartmentSupervisor::where("supervised_by", auth()->user()->id)->where("office_division_id", $office_division_id)->active()->get();
                            $departmentIds=[];
                            foreach($permit_info as $info){
                                $departmentIds[]=$info->department_id;
                            }
                            $departmentIds_in_string = implode(',',$departmentIds);
                            $users = getEmployeesInformationByDepartmentIDs($departmentIds_in_string);
                        }
                    }elseif(auth()->user()->hasRole([User::ROLE_DIVISION_SUPERVISOR])) {
                        $departmentIds = $this->getDepartmentSupervisorIds();
                        if($find_division){
                            $departmentIds_in_string = implode(',',$departmentIds);
                            $users = getEmployeesInformationByDepartmentIDs($departmentIds_in_string);
                        }else{
                            $depts = Department::select("id", "name", "office_division_id")->whereIn('id',$departmentIds)->get();
                            $departmentIds=[];
                            foreach($depts as $info){
                                if($info->office_division_id == $office_division_id){
                                    $departmentIds[]=$info->id;
                                }
                            }
                            $departmentIds_in_string = implode(',',$departmentIds);
                            $users = getEmployeesInformationByDepartmentIDs($departmentIds_in_string);
                        }
                    }else{
                        if($find_division){
                            $sql = "SELECT users.id, users.`name`, users.email, users.fingerprint_no,employee_status.action_date, prm.department_id, prm.office_division_id, departments.`name` as department_name, office_divisions.`name` as division_name FROM `users` LEFT JOIN employee_status ON employee_status.user_id = users.id AND employee_status.id =( SELECT MAX( es.id) FROM `employee_status` AS es WHERE es.user_id = users.id AND es.action_reason_id = 2 ) INNER JOIN promotions as prm ON prm.user_id = users.id AND prm.id = (SELECT MAX( pm.id ) FROM `promotions` AS pm where pm.user_id = users.id) INNER JOIN departments ON departments.id = prm.department_id INNER JOIN office_divisions ON office_divisions.id = prm.office_division_id WHERE users.`status`=1";
                            $users = DB::select($sql);
                        }else{
                            $sql = "SELECT users.id, users.`name`, users.email, users.fingerprint_no,employee_status.action_date, prm.department_id, prm.office_division_id, departments.`name` as department_name, office_divisions.`name` as division_name FROM `users` LEFT JOIN employee_status ON employee_status.user_id = users.id AND employee_status.id =( SELECT MAX( es.id) FROM `employee_status` AS es WHERE es.user_id = users.id AND es.action_reason_id = 2 ) INNER JOIN promotions as prm ON prm.user_id = users.id AND prm.id = (SELECT MAX( pm.id ) FROM `promotions` AS pm where pm.user_id = users.id) INNER JOIN departments ON departments.id = prm.department_id INNER JOIN office_divisions ON office_divisions.id = prm.office_division_id WHERE `users`.`id` IN ( SELECT `promotions`.`user_id` FROM `promotions` WHERE `promotions`.`office_division_id` IN ( $office_division_id ) AND `promotions`.`id` IN ( SELECT MAX( `p`.`id` ) FROM `promotions` AS `p` GROUP BY `p`.user_id )) AND `users`.`status`=1";
                            $users = DB::select($sql);
                        }
                    }
                }else{
                    $departmentIds_in_string = implode(',',$department_ids);
                    $users = getEmployeesInformationByDepartmentIDs($departmentIds_in_string);
                }
            }else{
                $user_ids_in_string = implode(',',$user_ids);
                $sql = "SELECT users.id, users.`name`, users.email, users.fingerprint_no,employee_status.action_date, prm.department_id, prm.office_division_id, departments.`name` AS department_name, office_divisions.`name` AS division_name FROM `users` LEFT JOIN employee_status ON employee_status.user_id = users.id AND employee_status.id =( SELECT MAX( es.id) FROM `employee_status` AS es WHERE es.user_id = users.id AND es.action_reason_id = 2 ) INNER JOIN promotions AS prm ON prm.user_id = users.id AND prm.id = ( SELECT MAX( pm.id ) FROM `promotions` AS pm WHERE pm.user_id = users.id ) INNER JOIN departments ON departments.id = prm.department_id INNER JOIN office_divisions ON office_divisions.id = prm.office_division_id WHERE users.id IN ($user_ids_in_string) AND users.`status` = 1";
                $users = DB::select($sql);
            }

            if(!isset($user_ids_in_string)){
                $user_ids=[];
                foreach($users as $key=>$user){
                    if($key==0){
                        $user_ids_in_string=$user->id;
                    }else{
                        $user_ids_in_string.=','.$user->id;
                    }
                    $user_ids[]=$user->id;
                }
            }*/

            // get departments
            $departments = [];
            if(!empty($department_ids) && $department_ids[0] == 'all')  $departments = Department::get()->pluck('name', 'id' );
            else if(!empty($department_ids)) $departments = Department::whereIn('id', $department_ids)->get()->pluck('name', 'id');

            $salaries = Salary::where("month", (int)$month)
                ->where("year", (int)$year);

            if(!$find_division){
                $salaries->where('office_division_id', $office_division_id);
            }

            if(!$find_department){
                $salaries->whereIn('department_id', $department_ids);
            }

            if(!$find_employee){
                $salaries->whereIn('user_id', $user_ids);
            }

            $salaries = $salaries->orderBy("id")->orderBy("department_id")
                ->get();

            if($salaries->count() > 0) {
                if (in_array(1, $request->filter_type) && in_array(2, $request->filter_type)) {
                    $data = $this->salaryReportDataProcessWirhEmployeeAndDepartment(compact('salaries', 'departments'));
                } else if (in_array(1, $request->filter_type) ) {
                    $data = $this->salaryReportDataProcessWithEmployee(compact('salaries', 'departments'));
                } else if (in_array(2, $request->filter_type)) {
                    $data = $this->salaryReportDataProcessWithDepartment(compact('salaries', 'departments'));
                }
                $redirect = view('salary.report-view', compact('data'));
            }
            else {
                $redirect = redirect()->back()->withInput();
                session()->flash("type", "error");
                session()->flash("message", "Sorry! No Salary Data Found!!");
            }
        } catch (Exception $exception) {
            session()->flash("type", "error");
            session()->flash("message", "Something went wrong!!");
            $redirect = redirect()->back()->withInput();
        }

        return $redirect;
    }

    /**
     * @param Request $request
     * @param SalaryDepartment $salaryDepartment
     * @return BinaryFileResponse
     * @throws Exception
     */
    public function exportSalaryReport(Request $request, SalaryDepartment $salaryDepartment)
    {
        $data = $request->data;

        $salaryDepartment->load(
            "preparedBy", "divisionalApprovalBy", "departmentalApprovalBy",
            "hrApprovalBy", "accountsApprovalBy", "managerialApprovalBy"
        );

        $fileName = 'salary-report';
        if ($request->input("type") === "Export CSV" and auth()->user()->can("Export Salary CSV")) {
            $fileName .= '.xlsx';
            $fileName = preg_replace($this->allowedStr, '-', $fileName);
            return Excel::download(new SalaryReportExport($data), $fileName);
        }
    }

    protected function salaryReportDataProcessWirhEmployeeAndDepartment($data)
    {
        $departments = $data['departments'];
        $salaries = $data['salaries'];
        $dData = [];
        $dData['is_employee'] = true;
        $dData['is_department'] = true;

        $dData['total']['basic'] = 0;
        $dData['total']['house_rent'] = 0;
        $dData['total']['medical_allowance'] = 0;
        $dData['total']['conveyance'] = 0;
        $dData['total']['gross'] = 0;
        $dData['total']['holiday_amount'] = 0;
        $dData['total']['overtime_amount'] = 0;
        $dData['total']['payable_amount'] = 0;
        $dData['total']['advance_amount'] = 0;
        $dData['total']['loan_amount'] = 0;
        $dData['total']['payable_tax_amount'] = 0;
        $dData['total']['net_payable_amount'] = 0;
        foreach ($salaries as $salary) {
            if(!isset($dData['month'])) $dData['month'] = $salary->month;
            if(!isset($dData['year'])) $dData['year'] = $salary->year;
            $dData['departments'][$salary->department_id]['salaries'][] = $salary;

            $dData['departments'][$salary->department_id]['basic'][] = $salary->basic;
            $dData['departments'][$salary->department_id]['house_rent'][] = collect($salary->earnings)->where("name", "House Rent")->first()->amount;
            $dData['departments'][$salary->department_id]['medical_allowance'][] = collect($salary->earnings)->where("name", "Medical Allowance")->first()->amount;
            $dData['departments'][$salary->department_id]['conveyance'][] = collect($salary->earnings)->where("name", "Conveyance")->first()->amount;
            $dData['departments'][$salary->department_id]['gross'][] = $salary->gross;
            $dData['departments'][$salary->department_id]['holiday_amount'][] = $salary->holiday_amount;
            $dData['departments'][$salary->department_id]['overtime_amount'][] = $salary->overtime_amount;
            $dData['departments'][$salary->department_id]['payable_amount'][] = $salary->payable_amount;
            $dData['departments'][$salary->department_id]['advance_amount'][] = $salary->advance;
            $dData['departments'][$salary->department_id]['loan_amount'][] = $salary->loan;
            $dData['departments'][$salary->department_id]['payable_tax_amount'][] = $salary->payable_tax_amount;
            $dData['departments'][$salary->department_id]['net_payable_amount'][] = $salary->net_payable_amount;
            $dData['departments'][$salary->department_id]['name'] = $departments[$salary->department_id];

            $dData['total']['basic'] += $salary->basic;
            $dData['total']['house_rent'] += collect($salary->earnings)->where("name", "House Rent")->first()->amount;
            $dData['total']['medical_allowance'] += collect($salary->earnings)->where("name", "Medical Allowance")->first()->amount;
            $dData['total']['conveyance'] += collect($salary->earnings)->where("name", "Conveyance")->first()->amount;
            $dData['total']['gross'] += $salary->gross;
            $dData['total']['holiday_amount'] += $salary->holiday_amount;
            $dData['total']['overtime_amount'] += $salary->overtime_amount;
            $dData['total']['payable_amount'] += $salary->payable_amount;
            $dData['total']['advance_amount'] += $salary->advance;
            $dData['total']['loan_amount'] += $salary->loan;
            $dData['total']['payable_tax_amount'] += $salary->payable_tax_amount;
            $dData['total']['net_payable_amount'] += $salary->net_payable_amount;
        }
        return $dData;
    }

    protected function salaryReportDataProcessWithEmployee($data)
    {
        $salaries = $data['salaries'];
        $dData = [];
        $dData['is_employee'] = true;
        $dData['is_department'] = false;

        $dData['total']['basic'] = 0;
        $dData['total']['house_rent'] = 0;
        $dData['total']['medical_allowance'] = 0;
        $dData['total']['conveyance'] = 0;
        $dData['total']['gross'] = 0;
        $dData['total']['holiday_amount'] = 0;
        $dData['total']['overtime_amount'] = 0;
        $dData['total']['payable_amount'] = 0;
        $dData['total']['advance_amount'] = 0;
        $dData['total']['loan_amount'] = 0;
        $dData['total']['payable_tax_amount'] = 0;
        $dData['total']['net_payable_amount'] = 0;
        foreach ($salaries as $salary) {
            if(!isset($dData['month'])) $dData['month'] = $salary->month;
            if(!isset($dData['year'])) $dData['year'] = $salary->year;
            $dData['departments']['employee']['salaries'][] = $salary;

            $dData['total']['basic'] += $salary->basic;
            $dData['total']['house_rent'] += collect($salary->earnings)->where("name", "House Rent")->first()->amount;
            $dData['total']['medical_allowance'] += collect($salary->earnings)->where("name", "Medical Allowance")->first()->amount;
            $dData['total']['conveyance'] += collect($salary->earnings)->where("name", "Conveyance")->first()->amount;
            $dData['total']['gross'] += $salary->gross;
            $dData['total']['holiday_amount'] += $salary->holiday_amount;
            $dData['total']['overtime_amount'] += $salary->overtime_amount;
            $dData['total']['payable_amount'] += $salary->payable_amount;
            $dData['total']['advance_amount'] += $salary->advance;
            $dData['total']['loan_amount'] += $salary->loan;
            $dData['total']['payable_tax_amount'] += $salary->payable_tax_amount;
            $dData['total']['net_payable_amount'] += $salary->net_payable_amount;
        }
        return $dData;
    }

    protected function salaryReportDataProcessWithDepartment($data)
    {
        $departments = $data['departments'];
        $salaries = $data['salaries'];
        $dData = [];

        $dData['is_employee'] = false;
        $dData['is_department'] = true;

        $dData['total']['basic'] = 0;
        $dData['total']['house_rent'] = 0;
        $dData['total']['medical_allowance'] = 0;
        $dData['total']['conveyance'] = 0;
        $dData['total']['gross'] = 0;
        $dData['total']['holiday_amount'] = 0;
        $dData['total']['overtime_amount'] = 0;
        $dData['total']['payable_amount'] = 0;
        $dData['total']['advance_amount'] = 0;
        $dData['total']['loan_amount'] = 0;
        $dData['total']['payable_tax_amount'] = 0;
        $dData['total']['net_payable_amount'] = 0;
        foreach ($salaries as $salary) {
            if(!isset($dData['month'])) $dData['month'] = $salary->month;
            if(!isset($dData['year'])) $dData['year'] = $salary->year;

            $dData['departments'][$salary->department_id]['basic'][] = $salary->basic;
            $dData['departments'][$salary->department_id]['house_rent'][] = collect($salary->earnings)->where("name", "House Rent")->first()->amount;
            $dData['departments'][$salary->department_id]['medical_allowance'][] = collect($salary->earnings)->where("name", "Medical Allowance")->first()->amount;
            $dData['departments'][$salary->department_id]['conveyance'][] = collect($salary->earnings)->where("name", "Conveyance")->first()->amount;
            $dData['departments'][$salary->department_id]['gross'][] = $salary->gross;
            $dData['departments'][$salary->department_id]['holiday_amount'][] = $salary->holiday_amount;
            $dData['departments'][$salary->department_id]['overtime_amount'][] = $salary->overtime_amount;
            $dData['departments'][$salary->department_id]['payable_amount'][] = $salary->payable_amount;
            $dData['departments'][$salary->department_id]['advance_amount'][] = $salary->advance;
            $dData['departments'][$salary->department_id]['loan_amount'][] = $salary->loan;
            $dData['departments'][$salary->department_id]['payable_tax_amount'][] = $salary->payable_tax_amount;
            $dData['departments'][$salary->department_id]['net_payable_amount'][] = $salary->net_payable_amount;
            $dData['departments'][$salary->department_id]['name'] = $departments[$salary->department_id];

            $dData['total']['basic'] += $salary->basic;
            $dData['total']['house_rent'] += collect($salary->earnings)->where("name", "House Rent")->first()->amount;
            $dData['total']['medical_allowance'] += collect($salary->earnings)->where("name", "Medical Allowance")->first()->amount;
            $dData['total']['conveyance'] += collect($salary->earnings)->where("name", "Conveyance")->first()->amount;
            $dData['total']['gross'] += $salary->gross;
            $dData['total']['holiday_amount'] += $salary->holiday_amount;
            $dData['total']['overtime_amount'] += $salary->overtime_amount;
            $dData['total']['payable_amount'] += $salary->payable_amount;
            $dData['total']['advance_amount'] += $salary->advance;
            $dData['total']['loan_amount'] += $salary->loan;
            $dData['total']['payable_tax_amount'] += $salary->payable_tax_amount;
            $dData['total']['net_payable_amount'] += $salary->net_payable_amount;
        }
        return $dData;
    }


}
