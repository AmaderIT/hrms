<style>
    * {
        margin: 2px;
        font-size: 6.5px;
        width: 100%;
        text-align: center;
    }

    #attendanceReportView {
        display: flex;
        font-family: -apple-system;
        font-size: 14px;
        color: #333;
        justify-content: center;
    }

    table {
        border: 1px solid #ddd;
        border-collapse: collapse;
        padding: 0;
    }

    td, th {
        white-space: nowrap;
        border: 1px solid #ddd;
        padding: 4px 2px;
        vertical-align: middle;
        /*line-height: 28px;*/
    }
    .pdf-page {
        widows: 31cm;
        padding: 0 0.75cm;
        margin: 0 auto;
    }
    .prepared_by {
        margin-bottom: -10px;
    }

</style>

<div class="pdf-page card card-custom pl-6" id="attendanceReportView">
    <div class="card-body">
        <div class="row" style="margin-top: 80px; margin-bottom: 5px;">
            {{-- <div class="col-lg-1"><img src="{{ asset('assets/media/login/bysl.png') }}" style="width: 50px;" alt="BYSL"></div> --}}
            <div class="col-lg-10 center-block text-center">
                <b>{{ strtoupper($salaries->first()->officeDivision->name) }} DIVISION</b><br/>
                <b>Salary Sheet: {{ strtoupper($salaries->first()->department->name) }}</b>
            </div>
        </div>
        <div>
            <table class="table table-responsive table-bordered table-condensed" style="">
                <thead>
                    <tr>
                        <th colspan="7" style="background-color: #e3e3e3;">SALARY FOR THE MONTH OF:</th>
                        <th colspan="4" >{{ strtoupper(date('F', mktime(0, 0, 0, (int)$salaries->first()->month))) }} {{ $salaries->first()->year }}</th>
                        <th colspan="6" style="background-color: #e3e3e3;">NUMBER OF WORKING DAYS</th>
                        <th colspan="4">{{ $workingDay['workingDays'] . '/' . $workingDay['calendarDays'] }}</th>
                        <th colspan="7" style="background-color: #e3e3e3;">PREPARATION DATE:</th>
                        <th colspan="3">{{ date('M d, Y', strtotime($salaries->first()->created_at)) }}</th>
                    </tr>
                <tr style="position: sticky; top: 0; z-index: 1; vertical-align: middle;">
                    <th style="background-color: #e3e3e3; width: 2px;" class="align-middle" rowspan="2">ID</th>
                    <th style="background-color: #e3e3e3; text-align: left;" rowspan="2">Name</th>
                    <th style="background-color: #e3e3e3; text-align: left;" rowspan="2">Designation</th>
                    <th style="background-color: #e3e3e3; width: 5px;" class="align-middle" rowspan="2">Joining<br>Date</th>
                    <th style="background-color: #e3e3e3; width: 5px;" class="align-middle" rowspan="2">Basic</th>
                    <th style="background-color: #e3e3e3; width: 5px;" class="align-middle" rowspan="2">House<br>Rent</th>
                    <th style="background-color: #e3e3e3; width: 5px;" class="align-middle" rowspan="2">Medical<br>Allowance</th>
                    <th style="background-color: #e3e3e3; width: 5px;" class="align-middle" rowspan="2">Conveyance</th>
                    <th style="background-color: #e3e3e3; width: 5px;" class="align-middle" rowspan="2">Gross</th>
                    <th style="background-color: #e3e3e3;" class="align-middle" rowspan="1" colspan="4">Attendance<br>(Days)</th>
                    <th style="background-color: #e3e3e3; width: 5px;" class="align-middle" rowspan="2">Leave<br>(Days)</th>
                    <th style="background-color: #e3e3e3; width: 5px;" class="align-middle" rowspan="2">Absent<br>(Days)</th>
                    <th style="background-color: #e3e3e3; width: 5px;" class="align-middle" rowspan="2">Overtime<br>(Hours)</th>
                    <th style="background-color: #e3e3e3;" class="align-middle" rowspan="1" colspan="3">Holiday<br>(Days)</th>
                    <th style="background-color: #e3e3e3; width: 5px;" class="align-middle" rowspan="2">Holiday<br>Pay</th>
                    <th style="background-color: #e3e3e3; width: 5px;" class="align-middle" rowspan="2">Over<br>Time</th>
                    @if($hasCommission)
                    <th style="background-color: #e3e3e3; width: 5px;" class="align-middle" rowspan="2">Parcel<br>Charge</th>
                    <th style="background-color: #e3e3e3; width: 5px;" class="align-middle" rowspan="2">Delivery<br>Bonus</th>
                    <th style="background-color: #e3e3e3; width: 5px;" class="align-middle" rowspan="2">Distance<br>Bonus</th>
                    @endif
                    <th style="background-color: #e3e3e3;" class="align-middle" rowspan="2">Total<br>Payable</th>
                    <th style="background-color: #e3e3e3;" class="align-middle" rowspan="1" colspan="7">Adjustment<br>/Deduction</th>
                    <th style="background-color: #e3e3e3;" class="align-middle" rowspan="2">Net<br>Payable</th>
                    {{--<th style="background-color: #e3e3e3;" class="align-middle" rowspan="2">Hours<br>/Attendance</th>--}}
                    <th style="background-color: #e3e3e3;" class="align-middle" rowspan="2">Payment<br>Mode</th>
                    <th style="background-color: #e3e3e3;" class="align-middle" rowspan="2">Remarks</th>
                </tr>
                <tr style="position: sticky; top: 46px; vertical-align: top;">
                    <td style="background-color: #e3e3e3; width: 5px;">Regular<br>Duty</td>
                    <td style="background-color: #e3e3e3; width: 5px;">Weekend<br>Duty</td>
                    <td style="background-color: #e3e3e3; width: 5px;">Official<br>Duty</td>
                    <td style="background-color: #e3e3e3; width: 5px;">Late<br>Days</td>
                    <td style="background-color: #e3e3e3; width: 5px;">Weekend<br>Holiday</td>
                    <td style="background-color: #e3e3e3; width: 5px;">Official<br>Holiday</td>
                    <td style="background-color: #e3e3e3; width: 5px;">Relax<br>Day</td>
                    <td style="background-color: #e3e3e3; width: 5px;">Advance</td>
                    <td style="background-color: #e3e3e3; width: 5px;">Casual<br/>Leave<br/>(Days)</td>
                    <td style="background-color: #e3e3e3; width: 5px;">Ear<br/>Leave<br/>(Days)</td>
                    <td style="background-color: #e3e3e3; width: 5px;">Loan</td>
                    <td style="background-color: #e3e3e3; width: 5px;">Absent</td>
                    <td style="background-color: #e3e3e3; width: 5px;">Late</td>
                    <td style="background-color: #e3e3e3; width: 5px;">Income<br>Tax</td>
                </tr>
                </thead>
                @php
                    $total = [
                        "basic" => 0,
                        "house_rent" => 0,
                        "medical_allowance" => 0,
                        "conveyance" => 0,
                        "gross" => 0,
                        "holiday_amount" => 0,
                        "overtime_amount" => 0,
                        "parcel_charge" => 0,
                        "delivery_bonus" => 0,
                        "distance_bonus" => 0,
                        "payable_amount" => 0,
                        "advance" => 0,
                        "casual_leave" => 0,
                        "earn_leave" => 0,
                        "loan" => 0,
                        "absent_salary_deduction" => 0,
                        "late_salary_deduction" => 0,
                        "payable_tax_amount" => 0,
                        "net_payable_amount" => 0,
                        ];
                    $prepare = isset($salaryDepartment->preparedBy) ? sprintf(
                        "<tr style='border: none;'><th style='border: none;'><div class='prepared_by'>
                            <div class='item'>%s (ID-%s)</div>
                            <div class='item'>%s, %s</div>
                            <div class='item'>%s</div>
                        </div></th></tr>",
                        $salaryDepartment->preparedBy->name,
                        $salaryDepartment->preparedBy->fingerprint_no,
                        $salaryDepartment->preparedBy->currentPromotion->designation->title,
                        $salaryDepartment->preparedBy->currentPromotion->department->name,
                        date('jS M, h:i a', strtotime($salaryDepartment->prepared_date))
                    ) : "<tr style='border: none;'><th style='border: none;'><div class='prepared_by'>
                            <div class='item'>N/A</div>
                            <div class='item'></div>
                            <div class='item'></div>
                        </div></th></tr>";
                    $department = isset($salaryDepartment->departmentalApprovalBy) ? sprintf(
                        "<th style='border: none;'><div class='prepared_by'>
                            <div class='item'>%s (ID-%s)</div>
                            <div class='item'>%s, %s</div>
                            <div class='item'>%s</div>
                        </div></th>",
                        $salaryDepartment->departmentalApprovalBy->name,
                        $salaryDepartment->departmentalApprovalBy->fingerprint_no,
                        $salaryDepartment->departmentalApprovalBy->currentPromotion->designation->title,
                        $salaryDepartment->departmentalApprovalBy->currentPromotion->department->name,
                        date('jS M, h:i a', strtotime($salaryDepartment->departmental_approved_date))
                    ) : "<th style='border: none;'><div class='prepared_by'>
                            <div class='item'>N/A</div>
                            <div class='item'></div>
                            <div class='item'></div>
                        </div></th>";
                    $division = isset($salaryDepartment->divisionalApprovalBy) ? sprintf(
                        "<th style='border: none;'><div class='prepared_by'>
                            <div class='item'>%s (ID-%s)</div>
                            <div class='item'>%s, %s</div>
                            <div class='item'>%s</div>
                        </div></th>",
                        $salaryDepartment->divisionalApprovalBy->name,
                        $salaryDepartment->divisionalApprovalBy->fingerprint_no,
                        $salaryDepartment->divisionalApprovalBy->currentPromotion->designation->title,
                        $salaryDepartment->divisionalApprovalBy->currentPromotion->department->name,
                        date('jS M, h:i a', strtotime($salaryDepartment->divisional_approved_date))
                    ) : "<th style='border: none;'><div class='prepared_by'>
                            <div class='item'>N/A</div>
                            <div class='item'></div>
                            <div class='item'></div>
                        </div></th>";

                    $hr = isset($salaryDepartment->hrApprovalBy) ? sprintf(
                        "<th style='border: none;'><div class='prepared_by'>
                            <div class='item'>%s (ID-%s)</div>
                            <div class='item'>%s, %s</div>
                            <div class='item'>%s</div>
                        </div></th>",
                        $salaryDepartment->hrApprovalBy->name,
                        $salaryDepartment->hrApprovalBy->fingerprint_no,
                        $salaryDepartment->hrApprovalBy->currentPromotion->designation->title,
                        $salaryDepartment->hrApprovalBy->currentPromotion->department->name,
                        date('jS M, h:i a', strtotime($salaryDepartment->hr_approved_date))
                    ) : "<th style='border: none;'><div class='prepared_by'>
                            <div class='item'>N/A</div>
                            <div class='item'></div>
                            <div class='item'></div>
                        </div></th>";
                    $accounts = isset($salaryDepartment->accountsApprovalBy) ? sprintf(
                        "<th style='border: none;'><div class='prepared_by'>
                            <div class='item'>%s (ID-%s)</div>
                            <div class='item'>%s, %s</div>
                            <div class='item'>%s</div>
                        </div></th>",
                        $salaryDepartment->accountsApprovalBy->name,
                        $salaryDepartment->accountsApprovalBy->fingerprint_no,
                        $salaryDepartment->accountsApprovalBy->currentPromotion->designation->title,
                        $salaryDepartment->accountsApprovalBy->currentPromotion->department->name,
                        date('jS M, h:i a', strtotime($salaryDepartment->accounts_approved_date))
                    ) : "<th style='border: none;'><div class='prepared_by'>
                            <div class='item'>N/A</div>
                            <div class='item'></div>
                            <div class='item'></div>
                        </div></th>";
                    $managment = isset($salaryDepartment->managerialApprovalBy) ? sprintf(
                        "<th style='border: none;'><div class='prepared_by'>
                            <div class='item'>%s (ID-%s)</div>
                            <div class='item'>%s, %s</div>
                            <div class='item'>%s</div>
                        </div></th>",
                        $salaryDepartment->managerialApprovalBy->name,
                        $salaryDepartment->managerialApprovalBy->fingerprint_no,
                        $salaryDepartment->managerialApprovalBy->currentPromotion->designation->title,
                        $salaryDepartment->managerialApprovalBy->currentPromotion->department->name,
                        date('jS M, h:i a', strtotime($salaryDepartment->managerial_approved_date))
                    ) : "<th style='border: none;'><div class='prepared_by'>
                            <div class='item'>N/A</div>
                            <div class='item'></div>
                            <div class='item'></div>
                        </div></th>";
                @endphp
                <tbody>
                @foreach($salaries as $key => $salary)
                @php
                    $total["basic"] += $salary->basic;
                    $total["house_rent"] += collect($salary->earnings)->where("name", "House Rent")->first()->amount;
                    $total["medical_allowance"] += collect($salary->earnings)->where("name", "Medical Allowance")->first()->amount;
                    $total["conveyance"] += collect($salary->earnings)->where("name", "Conveyance")->first()->amount;
                    $total["gross"] += $salary->gross;
                    $total["holiday_amount"] += $salary->holiday_amount;
                    $total["overtime_amount"] += $salary->overtime_amount;
                    $total["parcel_charge"] += $salary->parcel_charge;
                    $total["delivery_bonus"] += $salary->delivery_bonus;
                    $total["distance_bonus"] += $salary->distance_bonus;
                    $total["payable_amount"] += $salary->payable_amount;
                    $total["advance"] += $salary->advance;
                    $total["casual_leave"] += $salary->casual_leave;
                    $total["earn_leave"] += $salary->earn_leave;
                    $total["loan"] += $salary->loan;
                    $total["absent_salary_deduction"] += $salary->absent_salary_deduction;
                    $total["late_salary_deduction"] += $salary->late_salary_deduction;
                    $total["payable_tax_amount"] += $salary->payable_tax_amount;
                    $total["net_payable_amount"] += $salary->net_payable_amount;
                @endphp
                <tr>
                    <td style="position: sticky; left: 0px;">{{ $salary->user->fingerprint_no }}</td>
                    <td style="position: sticky; left: 40px; text-align: left;">{{ $salary->user->name }}</td>
                    <td style="text-align: left;">{{ $salary->designation->title }}</td>
                    <td>{{ date('M d, y', strtotime($salary->user->employeeStatusJoining->action_date)) }}</td>
                    <td style="text-align: right">{{ \App\Http\Controllers\SalaryController::currencyFormat($salary->basic) }}</td>
                    <td style="text-align: right">{{ \App\Http\Controllers\SalaryController::currencyFormat(collect($salary->earnings)->where("name", "House Rent")->first()->amount) }}</td>
                    <td style="text-align: right">{{ \App\Http\Controllers\SalaryController::currencyFormat(collect($salary->earnings)->where("name", "Medical Allowance")->first()->amount) }}</td>
                    <td style="text-align: right">{{ \App\Http\Controllers\SalaryController::currencyFormat(collect($salary->earnings)->where("name", "Conveyance")->first()->amount) }}</td>
                    <td style="text-align: right">{{ \App\Http\Controllers\SalaryController::currencyFormat($salary->gross) }}</td>
                    <td>{{ \App\Http\Controllers\SalaryController::currencyFormat($salary->regular_duty) }}</td>
                    <td>{{ \App\Http\Controllers\SalaryController::currencyFormat($salary->weekend_holiday_duty) }}</td>
                    <td>{{ \App\Http\Controllers\SalaryController::currencyFormat($salary->official_holiday_duty) }}</td>
                    <td>{{ \App\Http\Controllers\SalaryController::currencyFormat($salary->late) }}</td>
                    <td>{{ \App\Http\Controllers\SalaryController::currencyFormat($salary->leave_days) }}</td>
                    <td>{{ \App\Http\Controllers\SalaryController::currencyFormat($salary->absent_days) }}</td>
                    <td>{{ \App\Http\Controllers\SalaryController::currencyFormat($salary->overtime_hours) }}</td>
                    <td>{{ \App\Http\Controllers\SalaryController::currencyFormat($salary->weekend_holiday_days) }}</td>
                    <td>{{ \App\Http\Controllers\SalaryController::currencyFormat($salary->official_holiday_days) }}</td>
                    <td>{{ \App\Http\Controllers\SalaryController::currencyFormat($salary->relax_day_days) }}</td>
                    <td style="text-align: right">{{ \App\Http\Controllers\SalaryController::currencyFormat($salary->holiday_amount) }}</td>
                    <td style="text-align: right">{{ \App\Http\Controllers\SalaryController::currencyFormat($salary->overtime_amount) }}</td>
                    @if($hasCommission)
                    <td style="text-align: right">{{ \App\Http\Controllers\SalaryController::currencyFormat($salary->parcel_charge) }}</td>
                    <td style="text-align: right">{{ \App\Http\Controllers\SalaryController::currencyFormat($salary->delivery_bonus) }}</td>
                    <td style="text-align: right">{{ \App\Http\Controllers\SalaryController::currencyFormat($salary->distance_bonus) }}</td>
                    @endif
                    <td style="text-align: right">{{ \App\Http\Controllers\SalaryController::currencyFormat($salary->payable_amount) }}</td>
                    <td style="text-align: right">{{ \App\Http\Controllers\SalaryController::currencyFormat($salary->advance) }}</td>
                    <td style="text-align: right">{{ \App\Http\Controllers\SalaryController::currencyFormat($salary->casual_leave) }}</td>
                    <td style="text-align: right">{{ \App\Http\Controllers\SalaryController::currencyFormat($salary->earn_leave) }}</td>
                    <td style="text-align: right">{{ \App\Http\Controllers\SalaryController::currencyFormat($salary->loan) }}</td>
                    <td style="text-align: right">{{ \App\Http\Controllers\SalaryController::currencyFormat($salary->absent_salary_deduction) }}</td>
                    <td style="text-align: right">{{ \App\Http\Controllers\SalaryController::currencyFormat($salary->late_salary_deduction) }}</td>
                    <td style="text-align: right">{{ \App\Http\Controllers\SalaryController::currencyFormat($salary->payable_tax_amount) }}</td>
                    <td style="text-align: right">{{ \App\Http\Controllers\SalaryController::currencyFormat($salary->net_payable_amount) }}</td>
                    {{--<td>{{ \App\Http\Controllers\SalaryController::currencyFormat($salary->attendance_hours) }}</td>--}}
                    <td>{{ $salary->payment_mode }}</td>
                    <td>{{ $salary->remarks }}</td>
                </tr>
                @endforeach
                <tr style="font-weight: bolder;">
                    <td colspan="4">TOTAL</td>
                    <td>{{ \App\Http\Controllers\SalaryController::currencyFormat($total["basic"]) }}</td>
                    <td>{{ \App\Http\Controllers\SalaryController::currencyFormat($total["house_rent"]) }}</td>
                    <td>{{ \App\Http\Controllers\SalaryController::currencyFormat($total["medical_allowance"]) }}</td>
                    <td>{{ \App\Http\Controllers\SalaryController::currencyFormat($total["conveyance"]) }}</td>
                    <td>{{ \App\Http\Controllers\SalaryController::currencyFormat($total["gross"]) }}</td>
                    <td colspan="10"></td>
                    <td style="text-align: right">{{ \App\Http\Controllers\SalaryController::currencyFormat($total["holiday_amount"]) }}</td>
                    <td style="text-align: right">{{ \App\Http\Controllers\SalaryController::currencyFormat($total["overtime_amount"]) }}</td>
                    @if($hasCommission)
                    <td style="text-align: right">{{ \App\Http\Controllers\SalaryController::currencyFormat($total["parcel_charge"]) }}</td>
                    <td style="text-align: right">{{ \App\Http\Controllers\SalaryController::currencyFormat($total["delivery_bonus"]) }}</td>
                    <td style="text-align: right">{{ \App\Http\Controllers\SalaryController::currencyFormat($total["distance_bonus"]) }}</td>
                    @endif
                    <td style="text-align: right">{{ \App\Http\Controllers\SalaryController::currencyFormat($total["payable_amount"]) }}</td>
                    <td style="text-align: right">{{ \App\Http\Controllers\SalaryController::currencyFormat($total["advance"]) }}</td>
                    <td style="text-align: right">{{ \App\Http\Controllers\SalaryController::currencyFormat($total["casual_leave"]) }}</td>
                    <td style="text-align: right">{{ \App\Http\Controllers\SalaryController::currencyFormat($total["earn_leave"]) }}</td>
                    <td style="text-align: right">{{ \App\Http\Controllers\SalaryController::currencyFormat($total["loan"]) }}</td>
                    <td style="text-align: right">{{ \App\Http\Controllers\SalaryController::currencyFormat($total["absent_salary_deduction"]) }}</td>
                    <td style="text-align: right">{{ \App\Http\Controllers\SalaryController::currencyFormat($total["late_salary_deduction"]) }}</td>
                    <td style="text-align: right">{{ \App\Http\Controllers\SalaryController::currencyFormat($total["payable_tax_amount"]) }}</td>
                    <td style="text-align: right">{{ \App\Http\Controllers\SalaryController::currencyFormat($total["net_payable_amount"]) }}</td>
                    <td colspan="2"></td>
                </tr>
                <tr style="font-weight: bolder;">
                    <td colspan="4">IN WORDS</td>
                    <td colspan="28" style="text-align: left">
                        {{ \App\Http\Controllers\SalaryController::getBangladeshCurrency($total["net_payable_amount"]) }}
                    </td>
                </tr>
                </tbody>
            </table>

            <table class="approval-table"style="margin-top: 20px; border: none; font-weight: bolder;">
                <thead style="border: none;">
                    {!! $prepare !!}
                    <tr style="border: none;">
                        <th style="border: none;">________________________________</th>
                        <th style="border: none;"></th>
                        <th style="border: none;"></th>
                        <th style="border: none;"></th>
                        <th style="border: none;"></th>
                    </tr>
                </thead>
                <tbody style="border: none;">
                    <tr style="border: none;">
                        <td style="border: none;">Prepared By</td>
                        <td style="border: none;"></td>
                        <td style="border: none;"></td>
                        <td style="border: none;"></td>
                        <td style="border: none;"></td>
                    </tr>
                </tbody>
            </table>

            <table style="margin-top: 20px; border: none; font-weight: bolder;">
                <thead style="border: none;">
                    <tr>
                        {!! $department !!}
                        {!! $division !!}
                        {!! $hr !!}
                        {!! $accounts !!}
                        {!! $managment !!}
                    </tr>
                <tr>
                    <th style="border: none;">________________________________</th>
                    <th style="border: none;">________________________________</th>
                    <th style="border: none;">________________________________</th>
                    <th style="border: none;">________________________________</th>
                    <th style="border: none;">________________________________</th>
                </tr>
                </thead>
                <tbody>
                <tr>
                    <td style="border: none;">Department Head</td>
                    <td style="border: none;">Division Head</td>
                    <td style="border: none;">Human Resources</td>
                    <td style="border: none;">Accounts</td>
                    <td style="border: none;">Management</td>
                </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>
