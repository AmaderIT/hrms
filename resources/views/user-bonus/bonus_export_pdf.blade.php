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
                <b>{{ strtoupper($bonuses->first()->officeDivision->name) }} DIVISION</b><br/>
                <b>Bonus Sheet: {{ strtoupper($bonuses->first()->department->name) }}</b>
            </div>
        </div>
        <div>
            <table class="table table-responsive table-bordered table-condensed" style="">
                <thead>
                    <tr>
                        <th colspan="3" style="background-color: #e3e3e3;">BONUS NAME:</th>
                        <th colspan="4" >{{ $bonuses->first()->bonus->festival_name }}</th>
                        <th colspan="3" style="background-color: #e3e3e3;">PREPARATION DATE:</th>
                        <th colspan="4">{{ date('M d, Y', strtotime($bonuses->first()->created_at)) }}</th>
                    </tr>
                    <tr style="position: sticky; top: 0; z-index: 1; vertical-align: middle;">
                        <th style="background-color: #e3e3e3; width: 2px;" class="align-middle">ID</th>
                        <th style="background-color: #e3e3e3; text-align: left;">Name</th>
                        <th style="background-color: #e3e3e3; text-align: left;">Designation</th>
                        <th style="background-color: #e3e3e3; width: 5px;" class="align-middle">Joining<br>Date</th>
                        <th style="background-color: #e3e3e3; width: 5px;" class="align-middle">Basic</th>
                        <th style="background-color: #e3e3e3; width: 5px;" class="align-middle">House<br>Rent</th>
                        <th style="background-color: #e3e3e3; width: 5px;" class="align-middle">Medical<br>Allowance</th>
                        <th style="background-color: #e3e3e3; width: 5px;" class="align-middle">Conveyance</th>
                        <th style="background-color: #e3e3e3; width: 5px;" class="align-middle">Gross</th>
                        <th style="background-color: #e3e3e3;" class="align-middle">Total<br>Payable</th>
                        <th style="background-color: #e3e3e3;" class="align-middle">Income<br>Tax</th>
                        <th style="background-color: #e3e3e3;" class="align-middle">Net<br>Payable</th>
                        <th style="background-color: #e3e3e3;" class="align-middle">Payment<br>Mode</th>
                        <th style="background-color: #e3e3e3;" class="align-middle">Remarks</th>
                    </tr>
                </thead>
                @php
                    $total = [
                        "basic" => 0,
                        "house_rent" => 0,
                        "medical_allowance" => 0,
                        "conveyance" => 0,
                        "gross" => 0,
                        "payable_amount" => 0,
                        "payable_tax_amount" => 0,
                        "net_payable_amount" => 0,
                    ];
                    $prepare = isset($bonusDepartment->preparedBy) ? sprintf(
                        "<tr style='border: none;'><th style='border: none;'><div class='prepared_by'>
                            <div class='item'>%s (ID-%s)</div>
                            <div class='item'>%s, %s</div>
                            <div class='item'>%s</div>
                        </div></th></tr>",
                        $bonusDepartment->preparedBy->name,
                        $bonusDepartment->preparedBy->fingerprint_no,
                        $bonusDepartment->preparedBy->currentPromotion->designation->title,
                        $bonusDepartment->preparedBy->currentPromotion->department->name,
                        date('jS M, h:i a', strtotime($bonusDepartment->prepared_date))
                    ) : "<tr style='border: none;'><th style='border: none;'><div class='prepared_by'>
                            <div class='item'>N/A</div>
                            <div class='item'></div>
                            <div class='item'></div>
                        </div></th></tr>";
                    $department = isset($bonusDepartment->departmentalApprovalBy) ? sprintf(
                        "<th style='border: none;'><div class='prepared_by'>
                            <div class='item'>%s (ID-%s)</div>
                            <div class='item'>%s, %s</div>
                            <div class='item'>%s</div>
                        </div></th>",
                        $bonusDepartment->departmentalApprovalBy->name,
                        $bonusDepartment->departmentalApprovalBy->fingerprint_no,
                        $bonusDepartment->departmentalApprovalBy->currentPromotion->designation->title,
                        $bonusDepartment->departmentalApprovalBy->currentPromotion->department->name,
                        date('jS M, h:i a', strtotime($bonusDepartment->departmental_approved_date))
                    ) : "<th style='border: none;'><div class='prepared_by'>
                            <div class='item'>N/A</div>
                            <div class='item'></div>
                            <div class='item'></div>
                        </div></th>";
                    $division = isset($bonusDepartment->divisionalApprovalBy) ? sprintf(
                        "<th style='border: none;'><div class='prepared_by'>
                            <div class='item'>%s (ID-%s)</div>
                            <div class='item'>%s, %s</div>
                            <div class='item'>%s</div>
                        </div></th>",
                        $bonusDepartment->divisionalApprovalBy->name,
                        $bonusDepartment->divisionalApprovalBy->fingerprint_no,
                        $bonusDepartment->divisionalApprovalBy->currentPromotion->designation->title,
                        $bonusDepartment->divisionalApprovalBy->currentPromotion->department->name,
                        date('jS M, h:i a', strtotime($bonusDepartment->divisional_approved_date))
                    ) : "<th style='border: none;'><div class='prepared_by'>
                            <div class='item'>N/A</div>
                            <div class='item'></div>
                            <div class='item'></div>
                        </div></th>";

                    $hr = isset($bonusDepartment->hrApprovalBy) ? sprintf(
                        "<th style='border: none;'><div class='prepared_by'>
                            <div class='item'>%s (ID-%s)</div>
                            <div class='item'>%s, %s</div>
                            <div class='item'>%s</div>
                        </div></th>",
                        $bonusDepartment->hrApprovalBy->name,
                        $bonusDepartment->hrApprovalBy->fingerprint_no,
                        $bonusDepartment->hrApprovalBy->currentPromotion->designation->title,
                        $bonusDepartment->hrApprovalBy->currentPromotion->department->name,
                        date('jS M, h:i a', strtotime($bonusDepartment->hr_approved_date))
                    ) : "<th style='border: none;'><div class='prepared_by'>
                            <div class='item'>N/A</div>
                            <div class='item'></div>
                            <div class='item'></div>
                        </div></th>";
                    $accounts = isset($bonusDepartment->accountsApprovalBy) ? sprintf(
                        "<th style='border: none;'><div class='prepared_by'>
                            <div class='item'>%s (ID-%s)</div>
                            <div class='item'>%s, %s</div>
                            <div class='item'>%s</div>
                        </div></th>",
                        $bonusDepartment->accountsApprovalBy->name,
                        $bonusDepartment->accountsApprovalBy->fingerprint_no,
                        $bonusDepartment->accountsApprovalBy->currentPromotion->designation->title,
                        $bonusDepartment->accountsApprovalBy->currentPromotion->department->name,
                        date('jS M, h:i a', strtotime($bonusDepartment->accounts_approved_date))
                    ) : "<th style='border: none;'><div class='prepared_by'>
                            <div class='item'>N/A</div>
                            <div class='item'></div>
                            <div class='item'></div>
                        </div></th>";
                    $managment = isset($bonusDepartment->managerialApprovalBy) ? sprintf(
                        "<th style='border: none;'><div class='prepared_by'>
                            <div class='item'>%s (ID-%s)</div>
                            <div class='item'>%s, %s</div>
                            <div class='item'>%s</div>
                        </div></th>",
                        $bonusDepartment->managerialApprovalBy->name,
                        $bonusDepartment->managerialApprovalBy->fingerprint_no,
                        $bonusDepartment->managerialApprovalBy->currentPromotion->designation->title,
                        $bonusDepartment->managerialApprovalBy->currentPromotion->department->name,
                        date('jS M, h:i a', strtotime($bonusDepartment->managerial_approved_date))
                    ) : "<th style='border: none;'><div class='prepared_by'>
                            <div class='item'>N/A</div>
                            <div class='item'></div>
                            <div class='item'></div>
                        </div></th>";
                @endphp
                <tbody>
                @foreach($bonuses as $key => $bonus)
                @php
                    $total["basic"] += $bonus->basic;
                    $total["house_rent"] += $bonus->house_rent;
                    $total["medical_allowance"] += $bonus->medical_allowance;
                    $total["conveyance"] += $bonus->conveyance;
                    $total["gross"] += $bonus->gross;
                    $total["payable_amount"] += $bonus->amount;
                    $total["payable_tax_amount"] += $bonus->tax;
                    $total["net_payable_amount"] += $bonus->net_payable_amount;
                @endphp
                <tr>
                    <td style="position: sticky; left: 0px;">{{ $bonus->user->fingerprint_no }}</td>
                    <td style="position: sticky; left: 40px; text-align: left;">{{ $bonus->user->name }}</td>
                    <td style="text-align: left;">{{ $bonus->designation->title }}</td>
                    <td>{{ date('M d, y', strtotime($bonus->user->employeeStatusJoining->action_date)) }}</td>
                    <td style="text-align: right">{{ \App\Http\Controllers\SalaryController::currencyFormat($bonus->basic) }}</td>
                    <td style="text-align: right">{{ \App\Http\Controllers\SalaryController::currencyFormat($bonus->house_rent) }}</td>
                    <td style="text-align: right">{{ \App\Http\Controllers\SalaryController::currencyFormat($bonus->medical_allowance) }}</td>
                    <td style="text-align: right">{{ \App\Http\Controllers\SalaryController::currencyFormat($bonus->conveyance) }}</td>
                    <td style="text-align: right">{{ \App\Http\Controllers\SalaryController::currencyFormat($bonus->gross) }}</td>
                    <td style="text-align: right">{{ \App\Http\Controllers\SalaryController::currencyFormat($bonus->amount) }}</td>
                    <td style="text-align: right">{{ \App\Http\Controllers\SalaryController::currencyFormat($bonus->tax) }}</td>
                    <td style="text-align: right">{{ \App\Http\Controllers\SalaryController::currencyFormat($bonus->net_payable_amount) }}</td>
                    <td>{{ $bonus->payment_mode }}</td>
                    <td>{{ $bonus->remarks }}</td>
                </tr>
                @endforeach
                </tbody>
                <thead>
                    <tr style="font-weight: bolder;">
                        <td colspan="4">TOTAL</td>
                        <td>{{ \App\Http\Controllers\SalaryController::currencyFormat($total["basic"]) }}</td>
                        <td>{{ \App\Http\Controllers\SalaryController::currencyFormat($total["house_rent"]) }}</td>
                        <td>{{ \App\Http\Controllers\SalaryController::currencyFormat($total["medical_allowance"]) }}</td>
                        <td>{{ \App\Http\Controllers\SalaryController::currencyFormat($total["conveyance"]) }}</td>
                        <td>{{ \App\Http\Controllers\SalaryController::currencyFormat($total["gross"]) }}</td>
                        <td style="text-align: right">{{ \App\Http\Controllers\SalaryController::currencyFormat($total["payable_amount"]) }}</td>
                        <td style="text-align: right">{{ \App\Http\Controllers\SalaryController::currencyFormat($total["payable_tax_amount"]) }}</td>
                        <td style="text-align: right">{{ \App\Http\Controllers\SalaryController::currencyFormat($total["net_payable_amount"]) }}</td>
                        <td colspan="2"></td>
                    </tr>
                    <tr style="font-weight: bolder;">
                        <td colspan="4">IN WORDS</td>
                        <td colspan="10" style="text-align: left">
                            {{ \App\Http\Controllers\SalaryController::getBangladeshCurrency($total["net_payable_amount"]) }}
                        </td>
                    </tr>
                </thead>
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
