@extends('layouts.app')

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
    $totalEarnings = $salary->payable_amount;

    $totalDeductions = $salary->leave_unpaid_amount + $salary->payable_tax_amount + $salary->loan +
                    $salary->absent_salary_deduction + $salary->advance + $salary->late_salary_deduction;

    $payableAmount = $salary->payable_amount;
    $netPayableAmount = $salary->net_payable_amount;
@endphp

@section("content")
    <div class="card card-custom">
        <div class="card-header">
            <h3 class="card-title">
                <img src="{{ asset('assets/media/logos/BYSL_Logo.png') }}" class="h-70px"/>
            </h3>
            @can("Download PDF")
            <div class="card-toolbar">
                <div class="example-tools justify-content-center">
                    <a href="{{ route('salary.pdfDownload', ['salary' => $salary->uuid]) }}" class="btn btn-light-primary font-weight-bold">Download PDF</a>
                </div>
            </div>
            @endcan
        </div>

        <div class="card-body">
            <h4 class="text-center text-uppercase"><u>Payslip for the month of {{ date('F', mktime(0, 0, 0, $salary->month, 10)) . " " . $salary->year }}</u></h4>
            <div class="row mt-5">
                <div class="col-lg-6">
                    <ul class="list-unstyled">
                        <li>
                            <h5><strong>{{ $salary->user->name }} - {{ $salary->user->fingerprint_no }}</strong></h5>
                        </li>
                        <li><span>{{ $salary->user->currentPromotion->designation->title }}, {{ $salary->department->name }}</span></li>
                    </ul>
                </div>
                <div class="col-lg-6">
                    <ul class="list-unstyled float-right">
                        @if($salary->status === \App\Models\Salary::STATUS_PAID)
                        <button type="button" class="btn btn-sm btn-outline-success">PAID</button>
                        @elseif($salary->status === \App\Models\Salary::STATUS_UNPAID)
                        <button type="button" class="btn btn-sm btn-outline-danger">UNPAID</button>
                        @endif
                    </ul>
                </div>
            </div>
            <div class="row mt-8">
                <div class="col-lg-6">
                    <div>
                        <h4><strong>Earnings</strong></h4>
                        <table class="table table-bordered">
                            <tbody>
                                <tr>
                                    <td>
                                        <strong>Basic Salary </strong>
                                        <span class="float-right">
                                            {{ \App\Http\Controllers\SalaryController::currencyFormat($basic) }} /=
                                        </span>
                                    </td>
                                </tr>
                                @foreach($salary->earnings as $earning)
                                    <tr>
                                        <td>
                                            <strong>{{ $earning->name }}</strong>
                                            <span class="float-right" id="allowances">{{ number_format($earning->amount, 2) }} /=</span>
                                        </td>
                                    </tr>
                                @endforeach
                                <tr>
                                    <td>
                                        <strong>Overtime Allowance</strong>
                                        <span class="float-right">
                                            <span class="float-right">{{ number_format($overtimeAllowance, 2) }} /=</span>
                                        </span>
                                    </td>
                                </tr>
                                <tr>
                                    <td>
                                        <strong>Holiday Allowance</strong>
                                        <span class="float-right">
                                            <span class="float-right">{{ number_format($holidayAllowance, 2) }} /=</span>
                                        </span>
                                    </td>
                                </tr>

                                @if($parcelCharge > 0 || $deliveryBonus > 0)
                                <tr>
                                    <td>
                                        <strong>Parcel Charge</strong>
                                        <span class="float-right">
                                            <span class="float-right">{{ number_format($parcelCharge, 2) }} /=</span>
                                        </span>
                                    </td>
                                </tr>
                                <tr>
                                    <td>
                                        <strong>Delivery Bonus</strong>
                                        <span class="float-right">
                                            <span class="float-right">{{ number_format($deliveryBonus, 2) }} /=</span>
                                        </span>
                                    </td>
                                </tr>
                                @endif

                                <tr>
                                    <td>
                                        <strong>Total Earnings</strong>
                                        <span class="float-right">
                                            <strong>
                                                {{ \App\Http\Controllers\SalaryController::currencyFormat($totalEarnings) }} /=
                                            </strong>
                                        </span>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="col-lg-6">
                    <div>
                        <h4 class="m-b-10"><strong>Deductions</strong></h4>
                        <table class="table table-bordered">
                            <tbody>
                                @foreach($salary->deductions as $deduction)
                                    <tr>
                                        <td>
                                            <strong>{{ $deduction->name }}</strong>
                                            <span class="float-right">{{ number_format($deduction->amount, 2) }} /=</span>
                                        </td>
                                    </tr>
                                @endforeach
                                {{-- TODO: Implement on next Sprint
                                <tr>
                                    <td>
                                        <strong>Unpaid Leave</strong>
                                        <span class="float-right">
                                            {{ number_format($unpaidLeave, 2)  }} /=
                                        </span>
                                    </td>
                                </tr>
                                --}}
                                <tr>
                                    <td>
                                        <strong>Tax</strong>
                                        <span class="float-right">
                                            {{ number_format($tax, 2)  }} /=
                                        </span>
                                    </td>
                                </tr>
                                <tr>
                                    <td>
                                        <strong>Loan</strong>
                                        <span class="float-right">
                                            {{ number_format($loan, 2)  }} /=
                                        </span>
                                    </td>
                                </tr>
                                <tr>
                                    <td>
                                        <strong>Absent</strong>
                                        <span class="float-right">
                                            {{ number_format($absent, 2)  }} /=
                                        </span>
                                    </td>
                                </tr>
                                <tr>
                                    <td>
                                        <strong>Advance</strong>
                                        <span class="float-right">
                                            {{ number_format($advance, 2)  }} /=
                                        </span>
                                    </td>
                                </tr>
                                <tr>
                                    <td>
                                        <strong>Late</strong>
                                        <span class="float-right">
                                            {{ number_format($lateSalaryDeduction, 2)  }} /=
                                        </span>
                                    </td>
                                </tr>
                                <tr>
                                    <td>
                                        <strong>Total Deductions</strong>
                                        <span class="float-right">
                                            <strong>{{ number_format($totalDeductions, 2) }} /-</strong>
                                        </span>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
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
        </div>
    </div>

    @if($salary->total_cash_earning > 0)
    <div class="card card-custom mt-20">
        <div class="card-header">
            <h3 class="card-title">
                <img src="{{ asset('assets/media/logos/BYSL_Logo.png') }}" class="h-70px"/>
            </h3>
            @can("Download PDF")
                <div class="card-toolbar">
                    <div class="example-tools justify-content-center">
                        <a href="{{ route('salary.pdfCashDownload', ['salary' => $salary->uuid]) }}" class="btn btn-light-primary font-weight-bold">Download PDF</a>
                    </div>
                </div>
            @endcan
        </div>

        <div class="card-body">
            <h4 class="text-center text-uppercase"><u>Payslip for the month of {{ date('F', mktime(0, 0, 0, $salary->month, 10)) . " " . $salary->year }}</u></h4>
            <div class="row mt-5">
                <div class="col-lg-12">
                    <ul class="list-unstyled">
                        <li>
                            <h5><strong>{{ $salary->user->name }} - {{ $salary->user->fingerprint_no }}</strong></h5>
                        </li>
                        <li><span>{{ $salary->user->currentPromotion->designation->title }}, {{ $salary->department->name }}</span></li>
                    </ul>
                </div>
            </div>
            <div class="row mt-12">
                <div class="col-lg-6">
                    <div>
                        <h4><strong>Earnings</strong></h4>
                        <table class="table table-bordered">
                            <tbody>
                            @foreach($salary->cash_earnings as $cashEarning)
                                <tr>
                                    <td>
                                        <strong>{{ $cashEarning->name }}</strong>
                                        <span class="float-right" id="allowances">{{ number_format($cashEarning->amount, 2) }} /=</span>
                                    </td>
                                </tr>
                            @endforeach
                            <tr>
                                <td>
                                    <strong>Total Cash Earnings</strong>
                                    <span class="float-right">
                                        <strong>{{ number_format($salary->total_cash_earning, 2) }} /=</strong>
                                    </span>
                                </td>
                            </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <div class="row mt-7">
                <div class="col-lg-12">
                    <ul class="list-unstyled">
                        <li>
                            <span class="font-size-h4"><strong>Net Payable Amount:</strong>
                                {{ number_format($salary->total_cash_earning, 2) }} /=
                            </span>
                        </li>
                        <li>
                            <span class="font-size-h4"><strong>Amount In Words:</strong>
                                {{ \App\Http\Controllers\SalaryController::convertToWord($salary->total_cash_earning) }} Taka Only
                            </span>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
    @endif
@endsection
