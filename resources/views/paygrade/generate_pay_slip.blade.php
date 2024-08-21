@extends('layouts.app')

@section('content')
    @php
        $grossSalary = 0;
        $totalEarnings = 0;
        $totalDeductions = 0;

        $latestPromotion = $data["employee"]->latestPromotion();
        $grossSalary = $latestPromotion->salary;
        $basicSalary = $grossSalary * ($latestPromotion->payGrade->percentage_of_basic / 100);

        $latestPayGrade = $latestPromotion->payGrade->based_on;
        if($latestPayGrade == \App\Models\PayGrade::BASED_ON_BASIC) $basedOn = $basicSalary;
        elseif($latestPayGrade == \App\Models\PayGrade::BASED_ON_GROSS) $basedOn = $grossSalary;

        # Basic Salary
        $totalEarnings += $basicSalary;

        # Allowances
        foreach($data["employeeEarnings"] as $employeeEarnings)
        {
            if($employeeEarnings->earning->type === \App\Models\Earning::TYPE_PERCENTAGE) {
                $totalEarnings += $basedOn * ($employeeEarnings->earning->value / 100);
            } else {
                $totalEarnings += $employeeEarnings->earning->value;
            }
        }

        # Deductions
        foreach($data["employeeDeductions"] as $employeeDeductions)
        {
            if($employeeDeductions->deduction->type === \App\Models\Deduction::TYPE_PERCENTAGE) {
                $totalDeductions += $basedOn * ($employeeDeductions->deduction->value / 100);
            } else {
                $totalDeductions += $employeeDeductions->deduction->value;
            }
        }

        $totalDeductions += $data["employeeTaxableAmount"];

        $toBePaid = $totalEarnings - $totalDeductions;
    @endphp

    <!--begin::Card-->
    <div class="card card-custom">
        <!--begin::Header-->
        <div class="card-header">
            <h3 class="card-title">Employee Payslip</h3>
            <div class="card-toolbar">
                <div class="example-tools justify-content-center">
                    <a href="{{ route('paygrade.pdfDownload', ['user' => $data["employee"]->id]) }}" class="btn btn-light-primary font-weight-bold">Download PDF</a>
                </div>
            </div>
        </div>
        <!--end::Header-->
        <!--begin::Body-->
        <div class="card-body">
            <h4 class="text-center text-uppercase"><u>Payslip for the month of Feb 2019</u></h4>
            <div class="row">
                <div class="col-sm-6 m-b-20">
                    <img src="" alt="" class="inv-logo">
                    <ul class="list-unstyled mb-0">
                        <li class="text-uppercase"><b>BYSL Technology</b></li>
                        <li>Development Studio, Plot 39/2</li>
                        <li>Dhaka 1212</li>
                    </ul>
                </div>
                <div class="col-sm-6">
                    <div class="invoice-details">
                        <h3 class="text-uppercase text-right">Payslip #{{$data["employee"]->fingerprint_no +  50}}</h3>
                        <ul class="list-unstyled text-right">
                            <li>Month: <span>March, 2019</span></li>
                        </ul>
                    </div>
                </div>
            </div>
            <div class="row mt-5">
                <div class="col-lg-12">
                    <ul class="list-unstyled">
                        <li>
                            <h5><strong>{{ $data["employee"]->name}}</strong></h5>
                        </li>
                        <li><span>{{ $data["employee"]->latestPromotion()->designation->title }}</span></li>
                        <li>Employee ID: {{ $data["employee"]->fingerprint_no }}</li>
                    </ul>
                </div>
            </div>
            <div class="row mt-12">
                <div class="col-lg-6">
                    <div>
                        <h4><strong>Earnings</strong></h4>
                        <table class="table table-bordered">
                            <tbody>
                                <tr>
                                    <td><strong>Basic Salary </strong> <span class="float-right">{{ number_format($basicSalary, 2) }} /-</span></td>
                                </tr>
                                @foreach($data["employeeEarnings"] as $employeeEarnings)
                                    <tr>
                                        <td>
                                            <strong>{{ $employeeEarnings->earning->name }}</strong>
                                            <span class="float-right" id="allowances">
                                                @if($employeeEarnings->earning->type === \App\Models\Earning::TYPE_PERCENTAGE)
                                                    {{ number_format(($basedOn * ($employeeEarnings->earning->value / 100)), 2) }} /-
                                                @else
                                                    {{ number_format($employeeEarnings->earning->value, 2) }} /-
                                                @endif
                                            </span>
                                        </td>
                                    </tr>
                                @endforeach
                                <tr>
                                    <td>
                                        <strong>Total Earnings</strong>
                                        <span class="float-right"><strong> {{ number_format($totalEarnings, 2) }} /- </strong></span>
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
                            @foreach($data["employeeDeductions"] as $employeeDeductions)
                                <tr>
                                    <td>
                                        <strong>{{ $employeeDeductions->deduction->name }}</strong>
                                        <span class="float-right">
                                            @if($employeeDeductions->deduction->type === \App\Models\Deduction::TYPE_PERCENTAGE)
                                                {{ number_format(($basedOn * ($employeeDeductions->deduction->value / 100)), 2) }} /-
                                            @else
                                                {{ number_format($employeeDeductions->deduction->value, 2) }} /-
                                            @endif
                                        </span>
                                    </td>
                                </tr>
                            @endforeach

                            <tr>
                                <td>
                                    <strong>Tax</strong>
                                    <span class="float-right">
                                        {{ number_format($data["employeeTaxableAmount"], 2) }} /-
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
                                ({{ number_format($totalEarnings, 2) }} - {{ number_format($totalDeductions, 2) }}) = {{ number_format($toBePaid, 2) }} BDT
                            </span>
                        </li>
                        <li>
                            <span class="font-size-h4"><strong>Amount In Words:</strong>
                                {{ \App\Http\Controllers\PayGradeController::convertToWord($toBePaid) }} Taka Only
                            </span>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
        <!--end::Body-->
    </div>
    <!--end::Card-->
@endsection
