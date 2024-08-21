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
        {{--<div class="element-right">
            <div class="col-lg-6">
                <ul class="list-unstyled mb-0">
                    <li><p><b>Payslip no: </b>{{ $salary->id }}</p></li>
                </ul>
            </div>
        </div>--}}
    </div>
    {{-- Earnings --}}
    <div class="row" style="margin-top: 60px;">
        <div class="col-lg-12">
            <p><strong>Earnings</strong></p>
            <table class="table table-bordered">
                <tbody>

                @foreach($salary->cash_earnings as $cashEarning)
                    <tr>
                        <td>{{ $cashEarning->name }}</td>
                        <td>{{ number_format($cashEarning->amount, 2) }} /=</td>
                    </tr>
                @endforeach
                <tr>
                    <td>Total Cash Earnings</td>
                    <td>{{ number_format($salary->total_cash_earning, 2) }} /=</td>
                </tr>
                </tbody>
            </table>
        </div>
    </div>
    <br/>
    <br/>
    {{-- Summery --}}
    <div class="row mt-7">
        <div class="col-lg-12">
            <ul class="list-unstyled">
                <li>
                <span class="font-size-h4"><strong>Net Cash Payable Amount:</strong>
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
    <br/><br/>
    <span>...............................</span><br/>
    <span>Signature</span>
</body>
</html>
