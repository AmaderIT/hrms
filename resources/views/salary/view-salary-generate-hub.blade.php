@extends('layouts.app')

@section("top-css")
    <style>
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
            padding: 0;
        }
        .salary_btn {
            /* font-size: 11px; */
            background: #fff;
            background-color: #fff !important;
            color: #000 !important;
        }
        .salary_btn_pdf{
            color: #ed0000 !important;
        }
        .salary_btn_csv{
            color: #089b08 !important;
        }
        .salary_btn:hover{
            background: #fff;
            background-color: #3699ff42 !important;
            color: #000 !important;
        }
        .form1{
            display: inline-block;
        }
    </style>
@endsection

@section('content')
    <div class="card card-custom" id="attendanceReportView">
        <div class="card-body">
            <div class="row mb-4">
                <div class="col-lg-1"><img src="{{ asset('assets/media/login/bysl.png') }}" width="70" alt="BYSL"></div>
                <div class="col-lg-4 center-block text-center">
                    <b>{{ strtoupper($salaries->first()->officeDivision->name) }} DIVISION</b><br/>
                    <b>Salary Sheet: {{ strtoupper($salaries->first()->department->name) }}</b>
                </div>
                <div class="col-lg-7 text-right">
                    @can("Export Salary CSV")
                        <form class="form1" action="{{ route('salary.salaryExport', ['salaryDepartment' => $uuid]) }}" method="POST">
                            @csrf
                            <input type="hidden" name="type" value="Export CSV"/>
                            <button title="Export CSV" class="btn btn-sm btn-primary ml-2 salary_btn mb-2" type="submit"><i class="fa fa-file-csv salary_btn_csv"></i> CSV</button>
                        </form>
                    @endcan
                    @can("Export Salary PDF")
                        <form class="form1" action="{{ route('salary.salaryExport', ['salaryDepartment' => $uuid]) }}" method="POST">
                            @csrf
                            <input type="hidden" name="type" value="Export PDF"/>
                            <button title="Export PDF" class="btn btn-sm btn-primary ml-2 salary_btn mb-2" type="submit"><i class="fa fa-file-pdf salary_btn_pdf"></i> PDF</button>
                        </form>
                    @endcan
                    @can("Export Salary Bank Statement CSV")
                        <form class="form1" action="{{ route('salary.salaryExport', ['salaryDepartment' => $uuid]) }}" method="POST">
                            @csrf
                            <input type="hidden" name="type" value="Bank Statement CSV"/>
                            <button title="Bank Statement CSV" class="btn btn-sm btn-primary ml-2 salary_btn mb-2" type="submit"><i class="fa fa-file-csv salary_btn_csv"></i> Bank Statement</button>
                        </form>
                    @endcan
                    @can("Export Salary Bank Statement PDF")
                        <form class="form1" action="{{ route('salary.salaryExport', ['salaryDepartment' => $uuid]) }}" method="POST">
                            @csrf
                            <input type="hidden" name="type" value="Bank Statement PDF"/>
                            <button title="Bank Statement PDF" class="btn btn-sm btn-primary ml-2 salary_btn mb-2" type="submit"><i class="fa fa-file-pdf salary_btn_pdf"></i> Bank Statement</button>
                        </form>
                    @endcan
                </div>
            </div>
            <div>
                <table class="table table-responsive table-bordered text-center table-condensed" style="max-height: 800px;overflow: auto;position: relative;">
                    <thead>
                    <tr>
                        <th colspan="3" style="background-color: #e3e3e3;">SALARY FOR THE MONTH OF:</th>
                        <th colspan="3">{{ strtoupper(date('F', mktime(0, 0, 0, (int)$salaries->first()->month))) }} {{ $salaries->first()->year }}</th>
                        <th colspan="6" style="background-color: #e3e3e3;">NUMBER OF WORKING DAYS</th>
                        <th colspan="7">{{ $workingDay['workingDays'] . '/' . $workingDay['calendarDays'] }}</th>
                        <th colspan="8" style="background-color: #e3e3e3;">PREPARATION DATE:</th>
                        <th colspan="3">{{ date('M d, Y', strtotime($salaries->first()->created_at)) }}</th>
                    </tr>
                    <tr style="position: sticky; top: 0;vertical-align: top; z-index: 1">
                        <th style="background-color: #e3e3e3;" class="align-middle" rowspan="2">Sl. No.</th>
                        <th style="background-color: #e3e3e3;" class="align-middle" rowspan="2">ID</th>
                        <th style="background-color: #e3e3e3;" class="align-middle" rowspan="2">Name</th>
                        <th style="background-color: #e3e3e3;" class="align-middle" rowspan="2">Designation</th>
                        <th style="background-color: #e3e3e3;" class="align-middle" rowspan="2">Joining Date</th>
                        <th style="background-color: #e3e3e3;" class="align-middle" rowspan="2">Basic (Tk.)</th>
                        <th style="background-color: #e3e3e3;" class="align-middle" rowspan="2">House Rent (Tk.)</th>
                        <th style="background-color: #e3e3e3;" class="align-middle" rowspan="2">Medical Allowance (Tk.)</th>
                        <th style="background-color: #e3e3e3;" class="align-middle" rowspan="2">Conveyance (Tk.)</th>
                        <th style="background-color: #e3e3e3;" class="align-middle" rowspan="2">Gross Salary (Tk.)</th>
                        <th style="background-color: #e3e3e3;" class="align-middle" rowspan="1" colspan="4">Attendance (Days)</th>
                        <th style="background-color: #e3e3e3;" class="align-middle" rowspan="2">Leave (Days)</th>
                        <th style="background-color: #e3e3e3;" class="align-middle" rowspan="2">Absent (Days)</th>
                        <th style="background-color: #e3e3e3;" class="align-middle" rowspan="2">Over Time (Hours)</th>
                        <th style="background-color: #e3e3e3;" class="align-middle" rowspan="1" colspan="3">Holiday (Days)</th>
                        <th style="background-color: #e3e3e3;" class="align-middle" rowspan="2">Holiday Pay (Tk.)</th>
                        <th style="background-color: #e3e3e3;" class="align-middle" rowspan="2">Over Time (Tk.)</th>
                        <th style="background-color: #e3e3e3;" class="align-middle" rowspan="2">Parcel Charge Total</th>
                        <th style="background-color: #e3e3e3;" class="align-middle" rowspan="2">Delivery Bonus (Tk.)</th>
                        <th style="background-color: #e3e3e3;" class="align-middle" rowspan="2">Distance Commission (Tk.)</th>
                        <th style="background-color: #e3e3e3;" class="align-middle" rowspan="2">Total Payable (Tk.)</th>
                        <th style="background-color: #e3e3e3;" class="align-middle" rowspan="1" colspan="7">Adjustment / Deduction</th>
                        <th style="background-color: #e3e3e3;" class="align-middle" rowspan="2">Net Payable (Tk.)</th>
                        {{--<th style="background-color: #e3e3e3;" class="align-middle" rowspan="2">Hours / Attendance</th>--}}
                        <th style="background-color: #e3e3e3;" class="align-middle" rowspan="2">Payment Mode</th>
                        <th style="background-color: #e3e3e3;" class="align-middle" rowspan="2">Remarks</th>
                    </tr>
                    <tr style="position: sticky; top: 46px;vertical-align: top; z-index: 1;">
                        <td style="background-color: #e3e3e3;">Regular Duty (Days)</td>
                        <td style="background-color: #e3e3e3;">Weekend Holiday Duty (Days)</td>
                        <td style="background-color: #e3e3e3;">Official Holiday Duty (Days)</td>
                        <td style="background-color: #e3e3e3;">Late (Days)</td>
                        <td style="background-color: #e3e3e3;">Weekend Holiday (Days)</td>
                        <td style="background-color: #e3e3e3;">Official Holiday (Days)</td>
                        <td style="background-color: #e3e3e3;">Relax Day (Days)</td>
                        <td style="background-color: #e3e3e3;">Advance (Tk.)</td>
                        <td style="background-color: #e3e3e3;">Casual Leave (Day)</td>
                        <td style="background-color: #e3e3e3;">Earn Leave (Day)</td>
                        <td style="background-color: #e3e3e3;">Loan (Tk.)</td>
                        <td style="background-color: #e3e3e3;">Absent (Tk.)</td>
                        <td style="background-color: #e3e3e3;">Late (Tk.)</td>
                        <td style="background-color: #e3e3e3;">Income Tax (Tk.)</td>
                    </tr>
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
                    @endphp
                    @foreach($salaries as $key => $data)
                        @php
                            $total["basic"] += $data->basic;
                            $total["house_rent"] += collect($data->earnings)->where("name", "House Rent")->first()->amount;
                            $total["medical_allowance"] += collect($data->earnings)->where("name", "Medical Allowance")->first()->amount;
                            $total["conveyance"] += collect($data->earnings)->where("name", "Conveyance")->first()->amount;
                            $total["gross"] += $data->gross;
                            $total["holiday_amount"] += $data->holiday_amount;
                            $total["overtime_amount"] += $data->overtime_amount;
                            $total["parcel_charge"] += $data->parcel_charge;
                            $total["delivery_bonus"] += $data->delivery_bonus;
                            $total["distance_bonus"] += $data->distance_bonus;
                            $total["payable_amount"] += $data->payable_amount;
                            $total["advance"] += $data->advance;
                            $total["casual_leave"] += $data->casual_leave;
                            $total["earn_leave"] += $data->earn_leave;
                            $total["loan"] += $data->loan;
                            $total["absent_salary_deduction"] += $data->absent_salary_deduction;
                            $total["late_salary_deduction"] += $data->late_salary_deduction;
                            $total["payable_tax_amount"] += $data->payable_tax_amount;
                            $total["net_payable_amount"] += $data->net_payable_amount;
                        @endphp
                        <tr>
                            <td style="background-color: #e3e3e3;">{{ $key+1 }}</td>
                            <td style="position: sticky; left: 0px; background-color: #e3e3e3;">{{ $data->user->fingerprint_no }}</td>
                            <td style="position: sticky; left: 40px; background-color: #e3e3e3;">{{ $data->user->name }}</td>
                            <td>{{ $data->designation->title }}</td>
                            <td>{{ date('M d, Y', strtotime($data->user->employeeStatusJoining->action_date)) }}</td>
                            <td>{{ \App\Http\Controllers\SalaryController::currencyFormat($data->basic) }}</td>
                            <td>{{ \App\Http\Controllers\SalaryController::currencyFormat(collect($data->earnings)->where("name", "House Rent")->first()->amount) }}</td>
                            <td>{{ \App\Http\Controllers\SalaryController::currencyFormat(collect($data->earnings)->where("name", "Medical Allowance")->first()->amount) }}</td>
                            <td>{{ \App\Http\Controllers\SalaryController::currencyFormat(collect($data->earnings)->where("name", "Conveyance")->first()->amount) }}</td>
                            <td>{{ \App\Http\Controllers\SalaryController::currencyFormat($data->gross) }}</td>
                            <td>{{ \App\Http\Controllers\SalaryController::currencyFormat($data->regular_duty) }}</td>
                            <td>{{ \App\Http\Controllers\SalaryController::currencyFormat($data->weekend_holiday_duty) }}</td>
                            <td>{{ \App\Http\Controllers\SalaryController::currencyFormat($data->official_holiday_duty) }}</td>
                            <td>{{ \App\Http\Controllers\SalaryController::currencyFormat($data->late) }}</td>
                            <td>{{ \App\Http\Controllers\SalaryController::currencyFormat($data->leave_days) }}</td>
                            <td>{{ \App\Http\Controllers\SalaryController::currencyFormat($data->absent_days) }}</td>
                            <td>
                                {{--{{ \App\Http\Controllers\SalaryController::currencyFormat($data->overtime_hours) }}--}}
                                {{--@php
                                    $floorOfOvertime = floor($data->overtime_hours);
                                    $minute = ($data->overtime_hours - $floorOfOvertime) == .5 ? 30: '00';
                                @endphp
                                {{ $floorOfOvertime . ':' . $minute }}--}}

                                @php
                                    $overTimeHourMinSec = convertMinToHrMinSec($data->overtime_hours * 60);
                                    $overTimeHourMinSecArr = explode(':', $overTimeHourMinSec);
                                    $overTimeHour = (int) $overTimeHourMinSecArr[0];
                                    $overTimeMin = (int) $overTimeHourMinSecArr[1];
                                    $overTimeSec = (int) $overTimeHourMinSecArr[2];
                                    $overTimeMin = ($overTimeSec > 0)? $overTimeMin + 1: $overTimeMin;
                                    $overTimeMin = ($overTimeMin < 10)? '0' . $overTimeMin: $overTimeMin;
                                @endphp
                                {{ $overTimeHourMinSecArr[0] . ':' . $overTimeMin }}
                            </td>
                            <td>{{ \App\Http\Controllers\SalaryController::currencyFormat($data->weekend_holiday_days) }}</td>
                            <td>{{ \App\Http\Controllers\SalaryController::currencyFormat($data->official_holiday_days) }}</td>
                            <td>{{ \App\Http\Controllers\SalaryController::currencyFormat($data->relax_day_days) }}</td>
                            <td>{{ \App\Http\Controllers\SalaryController::currencyFormat($data->holiday_amount) }}</td>
                            <td>{{ \App\Http\Controllers\SalaryController::currencyFormat($data->overtime_amount) }}</td>
                            <td>{{ \App\Http\Controllers\SalaryController::currencyFormat($data->parcel_charge) }}</td>
                            <td>{{ \App\Http\Controllers\SalaryController::currencyFormat($data->delivery_bonus) }}</td>
                            <td>{{ \App\Http\Controllers\SalaryController::currencyFormat($data->distance_bonus) }}</td>
                            <td>{{ \App\Http\Controllers\SalaryController::currencyFormat($data->payable_amount) }}</td>
                            <td>{{ \App\Http\Controllers\SalaryController::currencyFormat($data->advance) }}</td>
                            <td>{{ \App\Http\Controllers\SalaryController::currencyFormat($data->casual_leave) }}</td>
                            <td>{{ \App\Http\Controllers\SalaryController::currencyFormat($data->earn_leave) }}</td>
                            <td>{{ \App\Http\Controllers\SalaryController::currencyFormat($data->loan) }}</td>
                            <td>{{ \App\Http\Controllers\SalaryController::currencyFormat($data->absent_salary_deduction) }}</td>
                            <td>{{ \App\Http\Controllers\SalaryController::currencyFormat($data->late_salary_deduction) }}</td>
                            <td>
                                @if($data->taxable_amount != $data->payable_tax_amount)
                                    <s style="font-weight: bold;color:#cb5d68;">
                                        <s style="font-weight: bold;color:#cb5d68;">
                                            {{\App\Http\Controllers\SalaryController::currencyFormat($data->taxable_amount)}}
                                        </s>
                                    </s>
                                @else

                                @endif


                                @if(auth()->user()->can('Adjust Tax Amount') && $salaryDepartment->status != \App\Models\SalaryDepartment::STATUS_PAID )


                                    <input
                                        style="text-align: center;height: 20px;"
                                        type="text"
                                        class="form-control tax-amount-adjust"
                                        id="tax-amount-{{$data->uuid}}"
                                        value="{{$data->payable_tax_amount}}"
                                        data-uuid="{{$data->uuid}}"
                                        onpaste="return false;">

                                    <div style="display: none;" class="input-group-append">

                                        <button style="font-size: 14px;color:#f64e60;width: 48%;"
                                                data-uuid="{{$data->uuid}}"
                                                id="tax-amount-reset-btn-{{$data->uuid}}"
                                                class="btn btn-hover-light-danger btn-sm fa fa-window-close tax-amount-reset-btn"
                                                value="{{$data->payable_tax_amount}}"
                                                type="button">

                                        </button>

                                        <button style="font-size: 14px;color: #32b548;width: 48%;"
                                                data-uuid="{{$data->uuid}}"
                                                id="tax-amount-adjust-btn-{{$data->uuid}}"
                                                class="btn btn-hover-light-success  btn-sm  sm fa fa-check-circle tax-amount-adjust-btn"
                                                value="{{$data->payable_tax_amount}}"
                                                type="button">

                                        </button>


                                    </div>

                                @else
                                    <span>{{\App\Http\Controllers\SalaryController::currencyFormat($data->payable_tax_amount)}}</span>
                                @endif

                            </td>
                            <td>{{ \App\Http\Controllers\SalaryController::currencyFormat($data->net_payable_amount) }}</td>
                            {{--<td>
                                @php
                                    $floorOfAttendance = floor($data->attendance_hours);
                                    $attendanceMinute = round(($data->attendance_hours - $floorOfAttendance) * 60);
                                @endphp
                                {{ $floorOfAttendance . ':' . $attendanceMinute }}
                            </td>--}}
                            <td>{{ $data->payment_mode }}</td>
                            <td>{{ $data->remarks }}</td>
                        </tr>
                    @endforeach
                    <tr>
                        <td colspan="5">TOTAL</td>
                        <td>{{ \App\Http\Controllers\SalaryController::currencyFormat($total["basic"]) }}</td>
                        <td>{{ \App\Http\Controllers\SalaryController::currencyFormat($total["house_rent"]) }}</td>
                        <td>{{ \App\Http\Controllers\SalaryController::currencyFormat($total["medical_allowance"]) }}</td>
                        <td>{{ \App\Http\Controllers\SalaryController::currencyFormat($total["conveyance"]) }}</td>
                        <td>{{ \App\Http\Controllers\SalaryController::currencyFormat($total["gross"]) }}</td>
                        <td colspan="10"></td>
                        <td>{{ \App\Http\Controllers\SalaryController::currencyFormat($total["holiday_amount"]) }}</td>
                        <td>{{ \App\Http\Controllers\SalaryController::currencyFormat($total["overtime_amount"]) }}</td>
                        <td>{{ \App\Http\Controllers\SalaryController::currencyFormat($total["parcel_charge"]) }}</td>
                        <td>{{ \App\Http\Controllers\SalaryController::currencyFormat($total["delivery_bonus"]) }}</td>
                        <td>{{ \App\Http\Controllers\SalaryController::currencyFormat($total["distance_bonus"]) }}</td>
                        <td>{{ \App\Http\Controllers\SalaryController::currencyFormat($total["payable_amount"]) }}</td>
                        <td>{{ \App\Http\Controllers\SalaryController::currencyFormat($total["advance"]) }}</td>
                        <td>{{ \App\Http\Controllers\SalaryController::currencyFormat($total["casual_leave"]) }}</td>
                        <td>{{ \App\Http\Controllers\SalaryController::currencyFormat($total["earn_leave"]) }}</td>
                        <td>{{ \App\Http\Controllers\SalaryController::currencyFormat($total["loan"]) }}</td>
                        <td>{{ \App\Http\Controllers\SalaryController::currencyFormat($total["absent_salary_deduction"]) }}</td>
                        <td>{{ \App\Http\Controllers\SalaryController::currencyFormat($total["late_salary_deduction"]) }}</td>
                        <td>{{ \App\Http\Controllers\SalaryController::currencyFormat($total["payable_tax_amount"]) }}</td>
                        <td>{{ \App\Http\Controllers\SalaryController::currencyFormat($total["net_payable_amount"]) }}</td>
                        <td colspan="2"></td>
                    </tr>
                    <tr>
                        <td colspan="5">IN WORDS</td>
                        <td colspan="24" style="text-align: left">
                            {{ \App\Http\Controllers\SalaryController::getBangladeshCurrency($total["net_payable_amount"]) }}
                        </td>
                    </tr>
                    </thead>
                </table>
            </div>
        </div>
        <div class="card-footer">
            <div class="row">
                <div class="col-lg-12">
                    <table class="table table-bordered table-hover text-center">
                        <thead>
                        <tr>
                            <th width="10%">Activity</th>
                            <th width="25%">Action By</th>
                            <th width="10%">Action Date</th>
                            <th width="10%">Status</th>
                            <th width="30%">Remarks</th>
                            <th width="15%">Signature</th>
                        </tr>
                        </thead>
                        <tbody>
                        <tr>
                            <td>Prepared by:</td>
                            <td>{{ $salaryDepartment->preparedBy->fingerprint_no . ' - ' . $salaryDepartment->preparedBy->name }}</td>
                            <td>{{ date('j F, Y, g:i a', strtotime($salaryDepartment->prepared_date)) }}</td>
                            <td>Approved</td>
                            <td></td>
                            <td>N/A</td>
                        </tr>
                        <tr>
                            <td>Divisional Approval by:</td>
                            <td>
                                @if($salaryDepartment->divisional_approval_status === 0 && $salaryDepartment->status == 0 && $salaryDepartment->departmental_approval_status !== 2 && in_array($salaryDepartment->office_division_id, $divisionIds))
                                    @can('Salary Divisional Approval')
                                        <a href="#" data-toggle="modal" data-target="#approvalModal-divisional">Click to Approve</a>
                                    @endcan
                                @else
                                    {{ optional($salaryDepartment->divisionalApprovalBy)->fingerprint_no . ' - ' . optional($salaryDepartment->divisionalApprovalBy)->name }}
                                @endif
                            </td>
                            <td>
                                {!! (!empty($salaryDepartment->divisional_approved_date))? date('j F, Y, g:i a', strtotime($salaryDepartment->divisional_approved_date)): '-' !!}
                            </td>
                            <td>
                                @if($salaryDepartment->divisional_approval_status === 0) Pending
                                @elseif($salaryDepartment->divisional_approval_status === 1) Approved
                                @elseif($salaryDepartment->divisional_approval_status === 2) Rejected
                                @endif
                            </td>
                            <td>{{ $salaryDepartment->divisional_remarks }}</td>
                            <td>N/A</td>
                        </tr>
                        <tr>
                            <td>Departmental Approval by:</td>
                            <td>
                                @if($salaryDepartment->departmental_approval_status === 0 && $salaryDepartment->status == 0 && $salaryDepartment->divisional_approval_status !== 2 && in_array($salaryDepartment->department_id, $departmentIds))
                                    @can('Salary Departmental Approval')
                                        <a href="#" data-toggle="modal" data-target="#approvalModal-departmental">Click to Approve</a>
                                    @endcan
                                @else
                                    {{ optional($salaryDepartment->departmentalApprovalBy)->fingerprint_no . ' - ' . optional($salaryDepartment->departmentalApprovalBy)->name }}
                                @endif
                            </td>
                            <td>
                                {!! (!empty($salaryDepartment->departmental_approved_date))? date('j F, Y, g:i a', strtotime($salaryDepartment->departmental_approved_date)): '-' !!}
                            </td>
                            <td>
                                @if($salaryDepartment->departmental_approval_status === 0) Pending
                                @elseif($salaryDepartment->departmental_approval_status === 1) Approved
                                @elseif($salaryDepartment->departmental_approval_status === 2) Rejected
                                @endif
                            </td>
                            <td>{{ $salaryDepartment->departmental_remarks }}</td>
                            <td>N/A</td>
                        </tr>
                        <tr>
                            <td>HR Approval by:</td>
                            <td>
                                @php $hrRejectionCondition = ($salaryDepartment->divisional_approval_status !== 2 && $salaryDepartment->departmental_approval_status !== 2) @endphp
                                @if($salaryDepartment->hr_approval_status === 0 && $salaryDepartment->status == 0 && $hrRejectionCondition)
                                    @can('Salary HR Approval')
                                        <a href="#" data-toggle="modal" data-target="#approvalModal-hr">Click to Approve</a>
                                    @endcan
                                @else
                                    {{ optional($salaryDepartment->hrApprovalBy)->fingerprint_no . ' - ' . optional($salaryDepartment->hrApprovalBy)->name }}
                                @endif
                            </td>
                            <td>
                                {!! (!empty($salaryDepartment->hr_approved_date))? date('j F, Y, g:i a', strtotime($salaryDepartment->hr_approved_date)): '-' !!}
                            </td>
                            <td>
                                @if($salaryDepartment->hr_approval_status === 0) Pending
                                @elseif($salaryDepartment->hr_approval_status === 1) Approved
                                @elseif($salaryDepartment->hr_approval_status === 2) Rejected
                                @endif
                            </td>
                            <td>{{ $salaryDepartment->hr_remarks }}</td>
                            <td>N/A</td>
                        </tr>
                        <tr>
                            <td>Accounts Approval by:</td>
                            <td>
                                @if($salaryDepartment->accounts_approval_status === 0 && $salaryDepartment->status == 0 && $salaryDepartment->hr_approval_status == 1)
                                    @can('Salary Accounts Approval')
                                        <a href="#" data-toggle="modal" data-target="#approvalModal-accounts">Click to Approve</a>
                                    @endcan
                                @else
                                    {{ optional($salaryDepartment->accountsApprovalBy)->fingerprint_no . ' - ' . optional($salaryDepartment->accountsApprovalBy)->name }}
                                @endif
                            </td>
                            <td>
                                {!! (!empty($salaryDepartment->accounts_approved_date))? date('j F, Y, g:i a', strtotime($salaryDepartment->accounts_approved_date)): '-' !!}
                            </td>
                            <td>
                                @if($salaryDepartment->accounts_approval_status === 0) Pending
                                @elseif($salaryDepartment->accounts_approval_status === 1) Approved
                                @elseif($salaryDepartment->accounts_approval_status === 2) Rejected
                                @endif
                            </td>
                            <td>{{ $salaryDepartment->accounts_remarks }}</td>
                            <td>N/A</td>
                        </tr>
                        <tr>
                            <td>Top Management Approval by:</td>
                            <td>
                                @if($salaryDepartment->managerial_approval_status === 0 && $salaryDepartment->status == 0 && $salaryDepartment->accounts_approval_status == 1 && $salaryDepartment->hr_approval_status == 1)
                                    @can('Salary Managerial Approval')
                                        <a href="#" data-toggle="modal" data-target="#approvalModal-managerial">Click to Approve</a>
                                    @endcan
                                @else
                                    {{ optional($salaryDepartment->managerialApprovalBy)->fingerprint_no . ' - ' . optional($salaryDepartment->managerialApprovalBy)->name }}
                                @endif
                            </td>
                            <td>
                                {!! (!empty($salaryDepartment->managerial_approved_date))? date('j F, Y, g:i a', strtotime($salaryDepartment->managerial_approved_date)): '-' !!}
                            </td>
                            <td>
                                @if($salaryDepartment->managerial_approval_status === 0) Pending
                                @elseif($salaryDepartment->managerial_approval_status === 1) Approved
                                @elseif($salaryDepartment->managerial_approval_status === 2) Rejected
                                @endif
                            </td>
                            <td>{{ $salaryDepartment->managerial_remarks }}</td>
                            <td>N/A</td>
                        </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    {{-- Divisional Approval Modal --}}
    @can("Salary Divisional Approval")
    <div class="modal fade" id="approvalModal-divisional" tabindex="-1" role="dialog" aria-labelledby="exampleModalSizeXl" aria-hidden="true">
        <div class="modal-dialog modal-dialog-scrollable modal-md" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel">Divisional Approval</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <i aria-hidden="true" class="ki ki-close"></i>
                    </button>
                </div>
                <form class="form" action="{{ route('salary.approvalDivisional') }}" method="POST">
                    @csrf
                    <input type="hidden" name="uuid" value="{{ $salaryDepartment->uuid }}"/>
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-lg-12">
                                <div class="form-group">
                                    <label>Approval Status <span class="text-danger">*</span></label>
                                    <div class="radio-inline" id="approval-divisional">
                                        <label class="radio radio-success">
                                            <input type="radio" name="divisional_status" value="approved" checked>
                                            <span></span>Approve</label>
                                        <label class="radio radio-danger">
                                            <input type="radio" name="divisional_status" value="rejected">
                                            <span></span>Reject</label>
                                    </div>
                                </div>
                                <div class="form-group mb-1" id="reason-divisional">
                                    <label for="reason">Reject Reason</label>
                                    <textarea class="form-control" id="reason" rows="3" name="reject_reason" placeholder="Enter Reject Reason"></textarea>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-primary mr-2">Submit</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    @endcan

    {{-- Departmental Approval Modal --}}
    @can("Salary Departmental Approval")
    <div class="modal fade" id="approvalModal-departmental" tabindex="-1" role="dialog" aria-labelledby="exampleModalSizeXl" aria-hidden="true">
        <div class="modal-dialog modal-dialog-scrollable modal-md" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel">Departmental Approval</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <i aria-hidden="true" class="ki ki-close"></i>
                    </button>
                </div>
                <form class="form" action="{{ route('salary.approvalDepartmental') }}" method="POST">
                    @csrf
                    <input type="hidden" name="uuid" value="{{ $salaryDepartment->uuid }}"/>
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-lg-12">
                                <div class="form-group">
                                    <label>Approval Status <span class="text-danger">*</span></label>
                                    <div class="radio-inline" id="approval-departmental">
                                        <label class="radio radio-success">
                                            <input type="radio" name="departmental_status" value="approved" checked>
                                            <span></span>Approve</label>
                                        <label class="radio radio-danger">
                                            <input type="radio" name="departmental_status" value="rejected">
                                            <span></span>Reject</label>
                                    </div>
                                </div>
                                <div class="form-group mb-1" id="reason-departmental">
                                    <label>Reject Reason</label>
                                    <textarea class="form-control" rows="3" name="reject_reason" placeholder="Enter Reject Reason"></textarea>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-primary mr-2">Submit</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    @endcan

    {{-- HR Approval Modal --}}
    @can("Salary HR Approval")
    <div class="modal fade" id="approvalModal-hr" tabindex="-1" role="dialog" aria-labelledby="exampleModalSizeXl" aria-hidden="true">
        <div class="modal-dialog modal-dialog-scrollable modal-md" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel">HR Approval</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <i aria-hidden="true" class="ki ki-close"></i>
                    </button>
                </div>
                <form class="form" action="{{ route('salary.approvalHr') }}" method="POST">
                    @csrf
                    <input type="hidden" name="uuid" value="{{ $salaryDepartment->uuid }}"/>
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-lg-12">
                                <div class="form-group">
                                    <label>Approval Status <span class="text-danger">*</span></label>
                                    <div class="radio-inline" id="approval-hr">
                                        <label class="radio radio-success">
                                            <input type="radio" name="hr_status" value="approved" checked>
                                            <span></span>Approve</label>
                                        <label class="radio radio-danger">
                                            <input type="radio" name="hr_status" value="rejected">
                                            <span></span>Reject</label>
                                    </div>
                                </div>
                                <div class="form-group mb-1" id="reason-hr">
                                    <label>Reject Reason</label>
                                    <textarea class="form-control" rows="3" name="reject_reason" placeholder="Enter Reject Reason"></textarea>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-primary mr-2">Submit</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    @endcan

    {{-- Accounts Approval Modal --}}
    @can("Salary Accounts Approval")
    <div class="modal fade" id="approvalModal-accounts" tabindex="-1" role="dialog" aria-labelledby="exampleModalSizeXl" aria-hidden="true">
        <div class="modal-dialog modal-dialog-scrollable modal-md" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel">Accounts Approval</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <i aria-hidden="true" class="ki ki-close"></i>
                    </button>
                </div>
                <form class="form" action="{{ route('salary.approvalAccounts') }}" method="POST">
                    @csrf
                    <input type="hidden" name="uuid" value="{{ $salaryDepartment->uuid }}"/>
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-lg-12">
                                <div class="form-group">
                                    <label>Approval Status <span class="text-danger">*</span></label>
                                    <div class="radio-inline" id="approval-accounts">
                                        <label class="radio radio-success">
                                            <input type="radio" name="accounts_status" value="approved" checked>
                                            <span></span>Approve</label>
                                        <label class="radio radio-danger">
                                            <input type="radio" name="accounts_status" value="rejected">
                                            <span></span>Reject</label>
                                    </div>
                                </div>
                                <div class="form-group mb-1" id="reason-accounts">
                                    <label>Reject Reason</label>
                                    <textarea class="form-control" rows="3" name="reject_reason" placeholder="Enter Reject Reason"></textarea>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-primary mr-2">Submit</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    @endcan

    {{-- Managerial Approval Modal --}}
    @can("Salary Managerial Approval")
    <div class="modal fade" id="approvalModal-managerial" tabindex="-1" role="dialog" aria-labelledby="exampleModalSizeXl" aria-hidden="true">
        <div class="modal-dialog modal-dialog-scrollable modal-md" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel">Top Management Approval</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <i aria-hidden="true" class="ki ki-close"></i>
                    </button>
                </div>
                <form class="form" action="{{ route('salary.approvalManagerial') }}" method="POST">
                    @csrf
                    <input type="hidden" name="uuid" value="{{ $salaryDepartment->uuid }}"/>
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-lg-12">
                                <div class="form-group">
                                    <label>Approval Status <span class="text-danger">*</span></label>
                                    <div class="radio-inline" id="approval-managerial">
                                        <label class="radio radio-success">
                                            <input type="radio" name="managerial_status" value="approved" checked>
                                            <span></span>Approve</label>
                                        <label class="radio radio-danger">
                                            <input type="radio" name="managerial_status" value="rejected">
                                            <span></span>Reject</label>
                                    </div>
                                </div>
                                <div class="form-group mb-1" id="reason-managerial">
                                    <label>Reject Reason</label>
                                    <textarea class="form-control" rows="3" name="reject_reason" placeholder="Enter Reject Reason"></textarea>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-primary mr-2">Submit</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    @endcan
    <input type="hidden" id="uuid_dpt" value="{{$uuid}}">
@endsection

@section("footer-js")
    <script type="text/javascript">
        $(document).ready(function () {
            // Divisional Approval
            $("#reason-divisional").css("display", "none");
            $('#approval-divisional input:radio').on('click', function () {
                if ($(this).val() === 'approved') {
                    $("#reason-divisional").css("display", "none");
                    $("#reason-divisional textarea").removeAttr("required");
                    $("#reason-divisional textarea").val(null);
                } else if ($(this).val() === 'rejected') {
                    $("#reason-divisional").css("display", "");
                    $("#reason-divisional textarea").attr("required", "required");
                }
            });

            // Departmental Approval
            $("#reason-departmental").css("display", "none");
            $('#approval-departmental input:radio').on('click', function () {
                if ($(this).val() === 'approved') {
                    $("#reason-departmental").css("display", "none");
                    $("#reason-departmental textarea").removeAttr("required");
                    $("#reason-departmental textarea").val(null);
                } else if ($(this).val() === 'rejected') {
                    $("#reason-departmental").css("display", "");
                    $("#reason-departmental textarea").attr("required", "required");
                }
            });

            // HR Approval
            $("#reason-hr").css("display", "none");
            $('#approval-hr input:radio').on('click', function () {
                if ($(this).val() === 'approved') {
                    $("#reason-hr").css("display", "none");
                    $("#reason-hr textarea").removeAttr("required");
                    $("#reason-hr textarea").val(null);
                } else if ($(this).val() === 'rejected') {
                    $("#reason-hr").css("display", "");
                    $("#reason-hr textarea").attr("required", "required");
                }
            });

            // Accounts Approval
            $("#reason-accounts").css("display", "none");
            $('#approval-accounts input:radio').on('click', function () {
                if ($(this).val() === 'approved') {
                    $("#reason-accounts").css("display", "none");
                    $("#reason-accounts textarea").removeAttr("required");
                    $("#reason-accounts textarea").val(null);
                } else if ($(this).val() === 'rejected') {
                    $("#reason-accounts").css("display", "");
                    $("#reason-accounts textarea").attr("required", "required");
                }
            });

            // Accounts Approval
            $("#reason-managerial").css("display", "none");
            $('#approval-managerial input:radio').on('click', function () {
                if ($(this).val() === 'approved') {
                    $("#reason-managerial").css("display", "none");
                    $("#reason-managerial textarea").removeAttr("required");
                    $("#reason-managerial textarea").val(null);
                } else if ($(this).val() === 'rejected') {
                    $("#reason-managerial").css("display", "");
                    $("#reason-managerial textarea").attr("required", "required");
                }
            });
        });
        const taxAdjustmentRoute = '{{ route("salary.adjustTaxAmount") }}';
    </script>

    <script src="{{asset('js/tax-adjustment.js')}}"></script>
@endsection
