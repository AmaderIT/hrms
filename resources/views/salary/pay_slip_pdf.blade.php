<!DOCTYPE html>
<head>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css"
          integrity="sha384-BVYiiSIFeK1dGmJRAkycuHAHRg32OmUcww7on3RYdg4Va+PmSTsz/K68vbdEjh4u" crossorigin="anonymous">
    <style>
        .element-left {
            display: inline-block;
            width: 80%;
            height: 36px;
        }
        .element-right {
            display: inline-block;
            width: 20%;
            height: 36px;
        }
        .table tr > td {
            padding: 2px auto 2px 5px !important;
        }
    </style>
</head>
<body>
@php
    $basic = $salary->basic;

    // Earnings
    $houseRent = $salary->house_rent;
    $medicalAllowance = $salary->medical_allowance;
    $conveyance = $salary->conveyance;
    $overtimeAllowance = $salary->overtime_amount;
    $holidayAllowance = $salary->holiday_amount;
    $parcelCharge = $salary->parcel_charge;
    $deliveryBonus = $salary->delivery_bonus;

    // Deductions
    $unpaidLeave = $salary->leave_unpaid_amount;
    $tax = $salary->payable_tax_amount;
    $loan = $salary->loan;
    $absent = $salary->absent_salary_deduction;
    $advance = $salary->advance;
    $lateSalaryDeduction = $salary->late_salary_deduction;

    // Summary
    $totalEarnings = $salary->basic + $salary->house_rent + $salary->medical_allowance + $salary->conveyance +
                    $salary->overtime_amount + $salary->holiday_amount + $salary->parcel_charge + $salary->delivery_bonus;

    $totalDeductions = $salary->leave_unpaid_amount + $salary->payable_tax_amount + $salary->loan +
                    $salary->absent_salary_deduction + $salary->advance + $salary->late_salary_deduction;

    $payableAmount = $totalEarnings;
    $netPayableAmount = $totalEarnings - $totalDeductions;
@endphp
    <div class="row">
        <div class="col-lg-1">
            <img src="{{ asset('assets/media/logos/BYSL_Logo.png') }}" style="height: 70px;"/>
        </div>
        <div class="col-lg-11">
            <p style="margin-left: 240px; font-size: 14px;"><b><u>Payslip for the month of {{ date('F', mktime(0, 0, 0, $salary->month, 10)) . " " . $salary->year }}</u></b></p>
        </div>
    </div>
    <br/><br/>
    <div class="row">
        <div class="element-left">
            <div class="col-lg-6">
                <ul class="list-unstyled mb-0">
                    <li>{{ $salary->user->name }} - {{ $salary->user->fingerprint_no }}</li>
                    <li>{{ $salary->user->currentPromotion->designation->title }}, {{ $salary->user->currentPromotion->department->name }}</li>
                    <li>{{ $salary->officeDivision->name }}</li>
                </ul>
            </div>
        </div>
        <div class="element-right">
            <div class="col-lg-6">
                <ul class="list-unstyled float-right">
                    @if($salary->status === \App\Models\Salary::STATUS_PAID)
                        <button type="button" class="btn btn-sm btn-outline-success" style="background-color: lawngreen; margin-left: 60px !important;">PAID</button>
                    @elseif($salary->status === \App\Models\Salary::STATUS_UNPAID)
                        <button type="button" class="btn btn-sm btn-outline-danger" style="background-color: orangered; margin-left: 60px !important;">UNPAID</button>
                    @endif
                </ul>
            </div>
        </div>
    </div>
    {{-- Earnings --}}
    <div class="row" style="margin-top: 40px;">
        <div class="col-lg-12">
            <p><strong>Earnings</strong></p>
            <table class="table table-bordered">
                <tbody>
                <tr>
                    <td>Basic Salary</td>
                    <td>{{ number_format($basic, 2) }} /-</td>
                </tr>
                @foreach($salary->earnings as $earning)
                    <tr>
                        <td>{{ $earning->name }}</td>
                        <td>{{ number_format($earning->amount, 2) }} /=</td>
                    </tr>
                @endforeach
                <tr>
                    <td>Overtime Allowance</td>
                    <td>{{ number_format($overtimeAllowance, 2) }} /=</td>
                </tr>
                <tr>
                    <td>Holiday Allowance</td>
                    <td>{{ number_format($holidayAllowance, 2) }} /=</td>
                </tr>
                @if($parcelCharge > 0 || $deliveryBonus > 0)
                    <tr>
                        <td>Parcel Charge</td>
                        <td>{{ number_format($parcelCharge, 2) }} /=</td>
                    </tr>
                    <tr>
                        <td>Delivery Bonus</td>
                        <td>{{ number_format($deliveryBonus, 2) }} /=</td>
                    </tr>
                @endif
                <tr>
                    <td>Total Earnings</td>
                    <td>{{ \App\Http\Controllers\SalaryController::currencyFormat($totalEarnings) }} /=</td>
                </tr>
                </tbody>
            </table>
        </div>
    </div>
    <br/>
    {{-- Deductions --}}
    <div class="row">
        <div class="col-lg-12">
            <p><b>Deductions</b></p>
            <table class="table table-bordered">
                <tbody>
                @foreach($salary->deductions as $deduction)
                    <tr>
                        <td>{{ $deduction->name }}</td>
                        <td>{{ number_format($deduction->amount, 2) }} /=</td>
                    </tr>
                @endforeach
                {{-- TODO: Implement on next Sprint
                <tr>
                    <td>Unpaid Leave</td>
                    <td>{{ number_format($unpaidLeave, 2)  }} /=</td>
                </tr>
                --}}
                <tr>
                    <td>Tax</td>
                    <td>{{ number_format($tax, 2)  }} /=</td>
                </tr>
                <tr>
                    <td>Loan</td>
                    <td>{{ number_format($loan, 2)  }} /=</td>
                </tr>
                <tr>
                    <td>Absent</td>
                    <td>{{ number_format($absent, 2)  }} /=</td>
                </tr>
                <tr>
                    <td>Advance</td>
                    <td>{{ number_format($advance, 2)  }} /=</td>
                </tr>
                <tr>
                    <td>Late</td>
                    <td>{{ number_format($lateSalaryDeduction, 2)  }} /=</td>
                </tr>
                <tr>
                    <td>Total Deductions</td>
                    <td>{{ number_format($totalDeductions, 2)  }} /=</td>
                </tr>
                </tbody>
            </table>
        </div>
    </div>
    <div class="row mt-7">
        <div class="col-lg-12">
            <ul class="list-unstyled">
                <li>
                <span class="font-size-h4"><strong>Net Payable Amount:</strong>
                    ({{ \App\Http\Controllers\SalaryController::currencyFormat($totalEarnings) }}
                        -
                        {{ \App\Http\Controllers\SalaryController::currencyFormat($totalDeductions) }})
                    =
                    {{ \App\Http\Controllers\SalaryController::currencyFormat($netPayableAmount) }}/=
                </span>
                </li>
                <li>
                    <span class="font-size-h4"><strong>Amount In Words:</strong>
                    {{ \App\Http\Controllers\SalaryController::getBangladeshCurrency($netPayableAmount) }}.
                </span>
                </li>
            </ul>
        </div>
    </div>
    <br/><br/>
    <span>...............................</span><br/>
    <span>Signature</span>
</body>
</html>
