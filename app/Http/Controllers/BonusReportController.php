<?php

namespace App\Http\Controllers;

use App\Exports\Report\BonusReportExport;
use App\Exports\Report\SalaryReportExport;
use App\Models\Bonus;
use App\Models\DepartmentSupervisor;
use App\Models\OfficeDivision;
use App\Models\BonusDepartment;
use App\Models\User;
use App\Models\UserBonus;
use Exception;
use App\Models\Department;
use Illuminate\Contracts\View\Factory;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class BonusReportController extends Controller
{
    protected $allowedStr = '/[^A-Za-z0-9\-\.]/';

    /**
     * @return Factory|\Illuminate\Contracts\View\View
     */
    public function bonusReportFilter()
    {
        $data = array(
            "officeDivisions" => [],
            "officeDepartments" => [],
            "employees" => [],
            "bonuses" => Bonus::where('status', 1)->orderBy('festival_name')->pluck('festival_name', 'id')->toArray()
        );

        if (auth()->user()->can('Show All Salary List')) {
            $data['officeDivisions'] = OfficeDivision::select("id", "name")->get();
            $data['officeDepartments'] = Department::select("id", "name")->get();
            $data['employees'] = User::select("id", "name", "fingerprint_no")->active()->get();

        } elseif (auth()->user()->hasRole([User::ROLE_SUPERVISOR])) {
            $permit_info = DepartmentSupervisor::where("supervised_by", auth()->user()->id)->active()->get();
            $divisions = [];
            $departmentIds = [];
            foreach ($permit_info as $info) {
                $divisions[] = $info->office_division_id;
                $departmentIds[] = $info->department_id;
            }
            if ($divisions) {
                $data['officeDivisions'] = OfficeDivision::select("id", "name")->whereIn('id', $divisions)->get();
                $data['officeDepartments'] = Department::select("id", "name")->whereIn('id', $departmentIds)->get();
                $departmentIds_in_string = implode(',', $departmentIds);
                $data['employees'] = getEmployeesByDepartmentIDs($departmentIds_in_string);
            }
        } elseif (auth()->user()->hasRole([User::ROLE_DIVISION_SUPERVISOR])) {
            $departmentIds = $this->getDepartmentSupervisorIds();
            $depts = Department::select("id", "name", "office_division_id")->whereIn('id', $departmentIds)->get();
            $divisions = [];
            foreach ($depts as $info) {
                $divisions[$info->office_division_id] = $info->office_division_id;
            }
            if ($divisions) {
                $data['officeDivisions'] = OfficeDivision::select("id", "name")->whereIn('id', $divisions)->get();
                $data['officeDepartments'] = $depts;
                $departmentIds_in_string = implode(',', $departmentIds);
                $data['employees'] = getEmployeesByDepartmentIDs($departmentIds_in_string);
            }
        } else {
            $data['officeDivisions'] = OfficeDivision::select("id", "name")->get();
            $data['officeDepartments'] = Department::select("id", "name")->get();
            $data['employees'] = User::select("id", "name", "fingerprint_no")->active()->get();
        }

        return view("user-bonus.report-filter", compact("data"));
    }

    public function generateBonusReportView(Request $request)
    {
        if (!isset($request->filter_type) || empty($request->filter_type)) {
            return redirect()->back()->withErrors('Select at least one checkbox in "Report type" field!!!')->withInput();
        }

        try {
            $bonusId = $request->input('bonus_id');
            $office_division_id = $request->input('office_division_id');
            $department_ids = $request->input('department_id');
            $user_ids = $request->input('user_id');
            $filter = [];
            $filter['bonus_id'] = $bonusId;
            $filter['office_division_id'] = $office_division_id;
            $filter['department_id'] = $department_ids;
            $filter['user_id'] = $user_ids;

            $festival = Bonus::select('festival_name', 'id')->where(['id' => $bonusId, 'status' => 1])->first();

            if ($bonusId == '') {
                $findBonusName = true;
            } else {
                $findBonusName = false;
            }

            if ($office_division_id == 'all') {
                $find_division = true;
            } else {
                $find_division = false;
            }
            if (in_array("all", $department_ids)) {
                $find_department = true;
            } else {
                $find_department = false;
            }
            if (in_array("all", $user_ids)) {
                $find_employee = true;
            } else {
                $find_employee = false;
            }

            // get departments
            $departments = [];
            if (!empty($department_ids) && $department_ids[0] == 'all') $departments = Department::get()->pluck('name', 'id');
            else if (!empty($department_ids)) $departments = Department::whereIn('id', $department_ids)->get()->pluck('name', 'id');

            $bonuses = UserBonus::select('*');

            if (!$findBonusName) {
                $bonuses->where('bonus_id', $bonusId);
            }

            if (!$find_division) {
                $bonuses->where('office_division_id', $office_division_id);
            }

            if (!$find_department) {
                $bonuses->whereIn('department_id', $department_ids);
            }

            if (!$find_employee) {
                $bonuses->whereIn('user_id', $user_ids);
            }

            $bonuses = $bonuses->orderBy("id")->orderBy("department_id")
                ->get();

            if ($bonuses->count() > 0) {
                if (in_array(1, $request->filter_type) && in_array(2, $request->filter_type)) {
                    $data = $this->bonusReportDataProcessWirhEmployeeAndDepartment(compact('bonuses', 'departments'));
                } else if (in_array(1, $request->filter_type)) {
                    $data = $this->bonusReportDataProcessWithEmployee(compact('bonuses', 'departments'));
                } else if (in_array(2, $request->filter_type)) {
                    $data = $this->bonusReportDataProcessWithDepartment(compact('bonuses', 'departments'));
                }
                $data['bonus_name'] = $festival->festival_name ?? '';
                $redirect = view('user-bonus.report-view', compact('data', 'festival'));
            } else {
                $redirect = redirect()->back()->withInput();
                session()->flash("type", "error");
                session()->flash("message", "Sorry! No Bonus Data Found!!");
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
     * @param BonusDepartment $bonusDepartment
     * @return BinaryFileResponse
     * @throws Exception
     */
    public function exportBonusReport(Request $request, BonusDepartment $bonusDepartment)
    {
        $data = $request->data;

        $bonusDepartment->load(
            "preparedBy", "divisionalApprovalBy", "departmentalApprovalBy",
            "hrApprovalBy", "accountsApprovalBy", "managerialApprovalBy"
        );

        $fileName = 'bonus-report';
        if ($request->input("type") === "Export CSV" and auth()->user()->can("Export Salary CSV")) {
            $fileName .= '.xlsx';
            $fileName = preg_replace($this->allowedStr, '-', $fileName);
            return Excel::download(new BonusReportExport($data), $fileName);
        }
    }

    protected function bonusReportDataProcessWirhEmployeeAndDepartment($data)
    {
        $departments = $data['departments'];
        $bonuses = $data['bonuses'];
        $dData = [];
        $dData['is_employee'] = true;
        $dData['is_department'] = true;

        $dData['total']['basic'] = 0;
        $dData['total']['house_rent'] = 0;
        $dData['total']['medical_allowance'] = 0;
        $dData['total']['conveyance'] = 0;
        $dData['total']['gross'] = 0;
        $dData['total']['payable_amount'] = 0;
        $dData['total']['payable_tax_amount'] = 0;
        $dData['total']['net_payable_amount'] = 0;
        foreach ($bonuses as $bonus) {
            if (!isset($dData['month'])) $dData['month'] = $bonus->month;
            if (!isset($dData['year'])) $dData['year'] = $bonus->year;
            $dData['departments'][$bonus->department_id]['bonuses'][] = $bonus;

            $dData['departments'][$bonus->department_id]['basic'][] = $bonus->basic;
            $dData['departments'][$bonus->department_id]['house_rent'][] = $bonus->house_rent;
            $dData['departments'][$bonus->department_id]['medical_allowance'][] = $bonus->medical_allowance;
            $dData['departments'][$bonus->department_id]['conveyance'][] = $bonus->conveyance;
            $dData['departments'][$bonus->department_id]['gross'][] = $bonus->gross;
            $dData['departments'][$bonus->department_id]['payable_amount'][] = $bonus->amount;
            $dData['departments'][$bonus->department_id]['payable_tax_amount'][] = $bonus->tax;
            $dData['departments'][$bonus->department_id]['net_payable_amount'][] = $bonus->net_payable_amount;
            $dData['departments'][$bonus->department_id]['name'] = $departments[$bonus->department_id];

            $dData['total']['basic'] += $bonus->basic;
            $dData['total']['house_rent'] += $bonus->house_rent;
            $dData['total']['medical_allowance'] += $bonus->medical_allowance;
            $dData['total']['conveyance'] += $bonus->conveyance;
            $dData['total']['gross'] += $bonus->gross;
            $dData['total']['payable_amount'] += $bonus->amount;
            $dData['total']['payable_tax_amount'] += $bonus->tax;
            $dData['total']['net_payable_amount'] += $bonus->net_payable_amount;
        }
        return $dData;
    }

    protected function bonusReportDataProcessWithEmployee($data)
    {
        $bonuses = $data['bonuses'];
        $dData = [];
        $dData['is_employee'] = true;
        $dData['is_department'] = false;

        $dData['total']['basic'] = 0;
        $dData['total']['house_rent'] = 0;
        $dData['total']['medical_allowance'] = 0;
        $dData['total']['conveyance'] = 0;
        $dData['total']['gross'] = 0;
        $dData['total']['payable_amount'] = 0;
        $dData['total']['payable_tax_amount'] = 0;
        $dData['total']['net_payable_amount'] = 0;
        foreach ($bonuses as $bonus) {
            if (!isset($dData['month'])) $dData['month'] = $bonus->month;
            if (!isset($dData['year'])) $dData['year'] = $bonus->year;
            $dData['departments']['employee']['bonuses'][] = $bonus;

            $dData['total']['basic'] += $bonus->basic;
            $dData['total']['house_rent'] += $bonus->house_rent;
            $dData['total']['medical_allowance'] += $bonus->medical_allowance;
            $dData['total']['conveyance'] += $bonus->conveyance;
            $dData['total']['gross'] += $bonus->gross;
            $dData['total']['payable_amount'] += $bonus->amount;
            $dData['total']['payable_tax_amount'] += $bonus->tax;
            $dData['total']['net_payable_amount'] += $bonus->net_payable_amount;
        }
        return $dData;
    }

    protected function bonusReportDataProcessWithDepartment($data)
    {
        $departments = $data['departments'];
        $bonuses = $data['bonuses'];
        $dData = [];

        $dData['is_employee'] = false;
        $dData['is_department'] = true;

        $dData['total']['basic'] = 0;
        $dData['total']['house_rent'] = 0;
        $dData['total']['medical_allowance'] = 0;
        $dData['total']['conveyance'] = 0;
        $dData['total']['gross'] = 0;
        $dData['total']['payable_amount'] = 0;
        $dData['total']['payable_tax_amount'] = 0;
        $dData['total']['net_payable_amount'] = 0;
        foreach ($bonuses as $bonus) {
            if (!isset($dData['month'])) $dData['month'] = $bonus->month;
            if (!isset($dData['year'])) $dData['year'] = $bonus->year;

            $dData['departments'][$bonus->department_id]['basic'][] = $bonus->basic;
            $dData['departments'][$bonus->department_id]['house_rent'][] = $bonus->house_rent;
            $dData['departments'][$bonus->department_id]['medical_allowance'][] = $bonus->medical_allowance;
            $dData['departments'][$bonus->department_id]['conveyance'][] = $bonus->conveyance;
            $dData['departments'][$bonus->department_id]['gross'][] = $bonus->gross;
            $dData['departments'][$bonus->department_id]['payable_amount'][] = $bonus->amount;
            $dData['departments'][$bonus->department_id]['payable_tax_amount'][] = $bonus->tax;
            $dData['departments'][$bonus->department_id]['net_payable_amount'][] = $bonus->net_payable_amount;
            $dData['departments'][$bonus->department_id]['name'] = $departments[$bonus->department_id];

            $dData['total']['basic'] += $bonus->basic;
            $dData['total']['house_rent'] += $bonus->house_rent;
            $dData['total']['medical_allowance'] += $bonus->medical_allowance;
            $dData['total']['conveyance'] += $bonus->conveyance;
            $dData['total']['gross'] += $bonus->gross;
            $dData['total']['payable_amount'] += $bonus->amount;
            $dData['total']['payable_tax_amount'] += $bonus->tax;
            $dData['total']['net_payable_amount'] += $bonus->net_payable_amount;
        }
        return $dData;
    }


}
