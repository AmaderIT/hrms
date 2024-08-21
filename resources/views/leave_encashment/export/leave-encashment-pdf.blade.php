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
</style>

<div class="card card-custom" id="attendanceReportView">
    <div class="card-body">
        <div class="row" style="margin-top: 80px; margin-bottom: 5px;">
            <div class="col-lg-1"></div>
            <div class="col-lg-10 center-block text-center">
                <b>DIVISION: {{ strtoupper($departmentLeaveEncashment->officeDivision->name) }}</b><br/>
                <b>Leave Encashment Sheet: {{ strtoupper($departmentLeaveEncashment->department->name) }}</b>
            </div>
            <div class="col-lg-1"></div>
        </div>
        <div>
            <table class="table table-responsive table-bordered table-condensed" style="width: 95%; margin: 0 auto;">
                <thead>
                    @php
                        $total_head = 11+count($earnings)+($leave_types->count()*4);
                        $rest = $total_head-11;
                        $earn_ar = [];
                        $total = 0;
                        $total_basic_salary_amount = 0;
                        $total_gross_salary_amount = 0;
                        $total_net_payable = 0;
                        foreach($earnings as $earn){
                            $earn_total[$earn->id]=0;
                        }
                    @endphp
                    <tr>
                        <th colspan="3" style="background-color: #e3e3e3;">Leave Encashment for The Year of:</th>
                        <th>{{ $departmentLeaveEncashment->year }}</th>
                        <th colspan="2" style="background-color: #e3e3e3;">Eligible Month</th>
                        <th>{{ $departmentLeaveEncashment->eligible_month }}</th>
                        <th colspan="2" style="background-color: #e3e3e3;">Preparation Date:</th>
                        <th colspan="2">{{ date('M d, Y', strtotime($departmentLeaveEncashment->created_at)) }}</th>
                        @if($rest)
                            <th colspan="{{$rest}}" style="background-color: #e3e3e3;"></th>
                        @endif
                    </tr>
                    <tr style="position: sticky; top: 0;vertical-align: top; z-index: 1">
                        <th style="background-color: #e3e3e3;" class="align-middle" rowspan="2">Sl. No.</th>
                        <th style="background-color: #e3e3e3;" class="align-middle" rowspan="2">ID</th>
                        <th style="background-color: #e3e3e3;" class="align-middle" rowspan="2">Name</th>
                        <th style="background-color: #e3e3e3;" class="align-middle" rowspan="2">Designation</th>
                        <th style="background-color: #e3e3e3;" class="align-middle" rowspan="2">Joining Date</th>
                        <th style="background-color: #e3e3e3;" class="align-middle" rowspan="2">Basic (Tk.)</th>
                        @foreach($earnings as $earn)
                            @php
                                $earn_ar[$earn->id] = $earn->id;
                            @endphp
                            <th style="background-color: #e3e3e3;" class="align-middle" rowspan="2">{{$earn->name}} (Tk.)</th>
                        @endforeach
                        <th style="background-color: #e3e3e3;" class="align-middle" rowspan="2">Gross Salary (Tk.)</th>
                        <th style="background-color: #e3e3e3;" class="align-middle" rowspan="2">Salary (per day)</th>
                        @foreach($leave_types as $type)
                            <th style="background-color: #e3e3e3;" class="align-middle" rowspan="1" colspan="4">{{$type->name}}</th>
                        @endforeach
                        <th style="background-color: #e3e3e3;" class="align-middle" rowspan="2">Tax Amount (Tk.)</th>
                        <th style="background-color: #e3e3e3;" class="align-middle" rowspan="2">Net Payable (Tk.)</th>
                        <th style="background-color: #e3e3e3;" class="align-middle" rowspan="2">Payment Mode</th>
                    </tr>
                    <tr>
                        @foreach($leave_types as $type)
                            <td style="background-color: #e3e3e3;" class="align-middle">Total Leave</td>
                            <td style="background-color: #e3e3e3;" class="align-middle">Consume Leave</td>
                            <td style="background-color: #e3e3e3;" class="align-middle">Encashment Leave</td>
                            <td style="background-color: #e3e3e3;" class="align-middle">Payable (Tk.)</td>
                        @endforeach
                    </tr>
                </thead>
                <tbody>
                @foreach($departmentLeaveEncashment->employeeLeaveEncashment as $key => $data)
                    @php
                        $earning_amounts = (Array) json_decode($data->earning_amounts);
                        $leave_details = (Array) json_decode($data->leave_details);
                        $total += $data->total_payable_amount;
                        $total_basic_salary_amount += $data->basic_salary_amount;
                        $total_gross_salary_amount += $data->gross_salary_amount;
                        $total_net_payable += $data->total_payable_amount;
                    @endphp
                    <tr>
                        <td style="background-color: #e3e3e3;">{{ $key+1 }}</td>
                        <td style="position: sticky; left: 0px; background-color: #e3e3e3;">{{ $data->employeeInformation->fingerprint_no }}</td>
                        <td style="position: sticky; left: 40px; background-color: #e3e3e3;">{{ $data->employeeInformation->name }}</td>
                        <td>{{ $data->designation_name }}</td>
                        <td>{{ date('M d, Y', strtotime($data->employeeInformation->employeeStatusJoining->action_date)) }}</td>
                        <td>{{ currencyFormat($data->basic_salary_amount) }}</td>
                        @foreach($earnings as $earn)
                            <td>
                                @if(isset($earning_amounts[$earn->id]))
                                    @php($earn_total[$earn->id]+=$earning_amounts[$earn->id])
                                    {{currencyFormat($earning_amounts[$earn->id])}}
                                @else
                                    {{'N/A'}}
                                @endif
                            </td>
                        @endforeach
                        <td>{{ currencyFormat($data->gross_salary_amount) }}</td>
                        <td>{{ currencyFormat($data->per_day_salary_amount) }}</td>
                        @foreach($leave_types as $type)
                            <td>@isset($leave_details[$type->id]) {{$leave_details[$type->id]->total_leave_amount}} @else {{0}} @endisset</td>
                            <td>@isset($leave_details[$type->id]) {{$leave_details[$type->id]->consumed_leave_amount}} @else {{0}} @endisset</td>
                            <td>@isset($leave_details[$type->id]) {{$leave_details[$type->id]->leave_balance}} @else {{0}} @endisset</td>
                            <td>@isset($leave_details[$type->id]) {{$leave_details[$type->id]->payable_amount}} @else {{0}} @endisset</td>
                        @endforeach
                        <td>{{ $data->tax_amount }}</td>
                        <td>{{ $data->total_payable_amount }}</td>
                        <td>{{ $departmentLeaveEncashment->payment_mode }}</td>
                    </tr>
                @endforeach
                <tr>
                    <?php
                    $total_head_ag = 11+($leave_types->count()*4);
                    $rest1 = $total_head_ag-9;
                    ?>
                    <td colspan="5">TOTAL</td>
                    <td>{{ currencyFormat($total_basic_salary_amount) }}</td>
                    @foreach($earnings as $earn)
                        <td>{{currencyFormat($earn_total[$earn->id])}}</td>
                    @endforeach
                    <td>{{ currencyFormat($total_gross_salary_amount) }}</td>
                    <td colspan="{{$rest1}}">Total Net Payable</td>
                    <td colspan="1">{{ currencyFormat($total_net_payable) }}</td>
                    <td></td>
                </tr>
                <tr>
                    <td colspan="5">TOTAL IN WORDS</td>
                    <td colspan="{{$total_head-5}}" style="text-align: left;font-weight: 600;">
                        {{ getBangladeshCurrency($total) }}
                    </td>
                </tr>
                </tbody>
            </table>


            <?php
            $prepare = isset($departmentLeaveEncashment->preparedBy) ? sprintf(
                "<tr style='border: none;'><th style='border: none;'><div class='prepared_by'>
                        <div class='item'>%s (ID-%s)</div>
                        <div class='item'>%s, %s</div>
                        <div class='item'>%s</div>
                    </div></th></tr>",
                $departmentLeaveEncashment->preparedBy->name,
                $departmentLeaveEncashment->preparedBy->fingerprint_no,
                $departmentLeaveEncashment->preparedBy->currentPromotion->designation->title,
                $departmentLeaveEncashment->preparedBy->currentPromotion->department->name,
                date('jS M, h:i a', strtotime($departmentLeaveEncashment->prepared_date))
            ) : "<tr style='border: none;'><th style='border: none;'><div class='prepared_by'>
                        <div class='item'>N/A</div>
                        <div class='item'></div>
                        <div class='item'></div>
                    </div></th></tr>";
            $department = isset($departmentLeaveEncashment->departmentalApprovalBy) ? sprintf(
                "<th style='border: none;'><div class='prepared_by'>
                        <div class='item'>%s (ID-%s)</div>
                        <div class='item'>%s, %s</div>
                        <div class='item'>%s</div>
                    </div></th>",
                $departmentLeaveEncashment->departmentalApprovalBy->name,
                $departmentLeaveEncashment->departmentalApprovalBy->fingerprint_no,
                $departmentLeaveEncashment->departmentalApprovalBy->currentPromotion->designation->title,
                $departmentLeaveEncashment->departmentalApprovalBy->currentPromotion->department->name,
                date('jS M, h:i a', strtotime($departmentLeaveEncashment->departmental_approved_date))
            ) : "<th style='border: none;'><div class='prepared_by'>
                        <div class='item'>N/A</div>
                        <div class='item'></div>
                        <div class='item'></div>
                    </div></th>";
            $division = isset($departmentLeaveEncashment->divisionalApprovalBy) ? sprintf(
                "<th style='border: none;'><div class='prepared_by'>
                        <div class='item'>%s (ID-%s)</div>
                        <div class='item'>%s, %s</div>
                        <div class='item'>%s</div>
                    </div></th>",
                $departmentLeaveEncashment->divisionalApprovalBy->name,
                $departmentLeaveEncashment->divisionalApprovalBy->fingerprint_no,
                $departmentLeaveEncashment->divisionalApprovalBy->currentPromotion->designation->title,
                $departmentLeaveEncashment->divisionalApprovalBy->currentPromotion->department->name,
                date('jS M, h:i a', strtotime($departmentLeaveEncashment->divisional_approved_date))
            ) : "<th style='border: none;'><div class='prepared_by'>
                        <div class='item'>N/A</div>
                        <div class='item'></div>
                        <div class='item'></div>
                    </div></th>";

            $hr = isset($departmentLeaveEncashment->hrApprovalBy) ? sprintf(
                "<th style='border: none;'><div class='prepared_by'>
                        <div class='item'>%s (ID-%s)</div>
                        <div class='item'>%s, %s</div>
                        <div class='item'>%s</div>
                    </div></th>",
                $departmentLeaveEncashment->hrApprovalBy->name,
                $departmentLeaveEncashment->hrApprovalBy->fingerprint_no,
                $departmentLeaveEncashment->hrApprovalBy->currentPromotion->designation->title,
                $departmentLeaveEncashment->hrApprovalBy->currentPromotion->department->name,
                date('jS M, h:i a', strtotime($departmentLeaveEncashment->hr_approved_date))
            ) : "<th style='border: none;'><div class='prepared_by'>
                        <div class='item'>N/A</div>
                        <div class='item'></div>
                        <div class='item'></div>
                    </div></th>";
            $accounts = isset($departmentLeaveEncashment->accountsApprovalBy) ? sprintf(
                "<th style='border: none;'><div class='prepared_by'>
                        <div class='item'>%s (ID-%s)</div>
                        <div class='item'>%s, %s</div>
                        <div class='item'>%s</div>
                    </div></th>",
                $departmentLeaveEncashment->accountsApprovalBy->name,
                $departmentLeaveEncashment->accountsApprovalBy->fingerprint_no,
                $departmentLeaveEncashment->accountsApprovalBy->currentPromotion->designation->title,
                $departmentLeaveEncashment->accountsApprovalBy->currentPromotion->department->name,
                date('jS M, h:i a', strtotime($departmentLeaveEncashment->accounts_approved_date))
            ) : "<th style='border: none;'><div class='prepared_by'>
                        <div class='item'>N/A</div>
                        <div class='item'></div>
                        <div class='item'></div>
                    </div></th>";
            $managment = isset($departmentLeaveEncashment->managerialApprovalBy) ? sprintf(
                "<th style='border: none;'><div class='prepared_by'>
                        <div class='item'>%s (ID-%s)</div>
                        <div class='item'>%s, %s</div>
                        <div class='item'>%s</div>
                    </div></th>",
                $departmentLeaveEncashment->managerialApprovalBy->name,
                $departmentLeaveEncashment->managerialApprovalBy->fingerprint_no,
                $departmentLeaveEncashment->managerialApprovalBy->currentPromotion->designation->title,
                $departmentLeaveEncashment->managerialApprovalBy->currentPromotion->department->name,
                date('jS M, h:i a', strtotime($departmentLeaveEncashment->managerial_approved_date))
            ) : "<th style='border: none;'><div class='prepared_by'>
                        <div class='item'>N/A</div>
                        <div class='item'></div>
                        <div class='item'></div>
                    </div></th>";
            ?>




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
