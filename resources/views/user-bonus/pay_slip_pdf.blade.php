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
            <p style="margin-left: 240px; font-size: 14px;"><b><u>Payslip for the Bonus of {{ $userBonus->bonus->festival_name . " (" . date('F', mktime(0, 0, 0, $userBonus->month, 10)) . " " . $userBonus->year . ")" }}</u></b></p>
        </div>
    </div>
    <br/><br/>
    <div class="row">
        <div class="element-left">
            <div class="col-lg-6">
                <ul class="list-unstyled mb-0">
                    <li>{{ $userBonus->user->name }} - {{ $userBonus->user->fingerprint_no }}</li>
                    <li>{{ $userBonus->user->currentPromotion->designation->title }}, {{ $userBonus->user->currentPromotion->department->name }}</li>
                    <li>{{ $userBonus->officeDivision->name }}</li>
                </ul>
            </div>
        </div>
        <div class="element-right">
            <div class="col-lg-6">
                <ul class="list-unstyled float-right">
                    @if($userBonus->status === \App\Models\UserBonus::STATUS_PAID)
                        <button type="button" class="btn btn-sm btn-outline-success" style="background-color: lawngreen; margin-left: 60px !important;">PAID</button>
                    @else{{--if($userBonus->status === \App\Models\UserBonus::STATUS_UNPAID)--}}
                        <button type="button" class="btn btn-sm btn-outline-danger" style="background-color: orangered; margin-left: 60px !important;">UNPAID</button>
                    @endif
                </ul>
            </div>
        </div>
    </div>

    {{-- Bonus --}}
    <div class="row">
        <div class="col-lg-12">
            <p><strong>Bonus</strong></p>
            <table class="table table-bordered">
                <tbody>
                    <tr>
                        <td width="70%">Amount</td>
                        <td width="30%">{{ number_format($userBonus->amount, 2) }} /-</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
    <br/>

    {{-- Tax --}}
    <div class="row">
        <div class="col-lg-12">
            <p><b>Tax</b></p>
            <table class="table table-bordered">
                <tbody>
                    <tr>
                        <td width="70%">Taxable Amount</td>
                        <td width="30%">{{ number_format($userBonus->tax, 2)  }} /=</td>
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
                         {{ number_format( ($userBonus->amount - $userBonus->tax ), 2) }} /=
                    </span>
                </li>
                <li>
                    <span class="font-size-h4"><strong>Amount In Words:</strong>
                        {{ \App\Http\Controllers\SalaryController::convertToWord(($userBonus->amount - $userBonus->tax)) }} Taka Only
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
