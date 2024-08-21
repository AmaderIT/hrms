<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css"
      integrity="sha384-BVYiiSIFeK1dGmJRAkycuHAHRg32OmUcww7on3RYdg4Va+PmSTsz/K68vbdEjh4u" crossorigin="anonymous">

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

<div class="row">
    <div class="col-lg-12">
        <h4 class="text-center text-uppercase"><u>Payslip for the month of Feb 2019</u></h4>
    </div>
</div>

<div class="row mt-30">
    <div class="col-lg-6">
        <ul class="list-unstyled mb-0">
            <li class="text-uppercase"><b>BYSL Technology</b></li>
            <li> Development Studio, Plot 39/2</li>
            <li>Dhaka 1212</li>
        </ul>
    </div>
    <div class="col-lg-6">
        <ul class="list-unstyled mb-0">
            <li><h4>Payslip #{{$data["employee"]->fingerprint_no +  50}}</h4></li>
            <li>Salary Month: <span>March, 2019</span></li>
        </ul>
    </div>
</div>

<div class="row mt-40">
    <div class="col-lg-12">
        <ul class="list-unstyled mb-0">
            <li class="text-uppercase"><b>{{ $data["employee"]->name }}</b></li>
            <li>{{ $latestPromotion->designation->title }}</li>
            <li>Employee ID: {{ $data["employee"]->fingerprint_no }}</li>
        </ul>
    </div>
</div>

{{-- Earnings --}}
<div class="row mt-30">
    <h4 class="ml-15"><strong>Earnings</strong></h4>
    <div class="col-lg-12">
        <table class="table table-bordered">
            <tbody>
                <tr>
                    <td><strong>Basic Salary </strong></td>
                    <td>{{ number_format($basicSalary, 2) }} /-</td>
                </tr>
                @foreach($data["employeeEarnings"] as $employeeEarning)
                    <tr>
                        <td>
                            <strong>{{ $employeeEarning->earning->name }}</strong>
                        </td>
                        <td>
                            @if($employeeEarning->earning->type === \App\Models\Earning::TYPE_PERCENTAGE)
                                {{ number_format(($basedOn * ($employeeEarning->earning->value / 100)), 2) }} /-
                            @else
                                {{ number_format($employeeEarning->earning->value, 2) }} /-
                            @endif
                        </td>
                    </tr>
                @endforeach
                <tr>
                    <td><strong>Total Earnings</strong></td>
                    <td><strong>{{ number_format($totalEarnings, 2) }} /-</strong></td>
                </tr>
            </tbody>
        </table>
    </div>
</div>

{{-- Deductions --}}
<div class="row mt-30">
    <h4 class="ml-15"><strong>Deductions</strong></h4>
    <div class="col-lg-12">
        <table class="table table-bordered">
            <tbody>
            @foreach($data["employeeDeductions"] as $employeeDeductions)
                <tr>
                    <td>
                        <strong>{{ $employeeDeductions->deduction->name }}</strong>
                    </td>
                    <td>
                        @if($employeeDeductions->deduction->type === \App\Models\Deduction::TYPE_PERCENTAGE)
                            {{ number_format(($basedOn * ($employeeDeductions->deduction->value / 100)) , 2) }}/-
                        @else
                            {{ number_format(($employeeDeductions->deduction->value), 2) }}/-
                        @endif
                    </td>
                </tr>
            @endforeach
                <tr>
                    <td>
                        <strong>Taxable Amount</strong>
                    </td>
                    <td>
                        <strong>{{ number_format($data["employeeTaxableAmount"], 2) }}/-</strong>
                    </td>
                </tr>
                <tr>
                    <td>
                        <strong>Total Deductions</strong>
                    </td>
                    <td>
                        <strong>{{ number_format($totalDeductions, 2) }}/-</strong>
                    </td>
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
