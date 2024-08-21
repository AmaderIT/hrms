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
            background: #fff;
            background-color: #fff !important;
            color: #000 !important;
        }
        .salary_btn_csv{
            color: #089b08 !important;
        }
        .salary_btn:hover{
            background: #fff;
            background-color: #3699ff42 !important;
            color: #000 !important;
        }
        .salary_report_download_csv_form{
            display: inline-flex;
            float: right;
        }
    </style>
@endsection

@section('content')
    <div class="card card-custom" id="attendanceReportView">
        <div class="card-body">
            <div class="row">
                <div class="col-lg-12 center-block text-center mb-2">
                    <b>SALARY FOR THE MONTH OF: </b>{{ strtoupper(date('F', mktime(0, 0, 0, (int)$data['month']))) }} {{ $data['year'] }}
                    @can("Generate Salary Report")
                        <form class="form1 salary_report_download_csv_form" action="{{ route('salary.exportSalaryReport') }}" method="POST">
                            @csrf
                            <input type="hidden" name="type" value="Export CSV"/>
                            <input type="hidden" name="data" value="{{ json_encode($data) }}"/>
                            <button title="Export Excel" class="btn btn-sm btn-primary ml-2 salary_btn mb-2" type="submit"><i class="fa fa-file-excel salary_btn_csv"></i> Download Excel</button>
                        </form>
                    @endcan
                </div>
            </div>
            <div>
                <table class="table table-responsive table-bordered text-center table-condensed" style="max-height: 800px;overflow: auto;position: relative;">
                    <thead>
                        <tr style="position: sticky; top: 0;vertical-align: top; z-index: 1">
                            <th style="background-color: #e3e3e3;" class="align-middle" rowspan="2">Sl. No.</th>
                            <th style="position: sticky; left: 0px; background-color: #e3e3e3;" class="align-middle" rowspan="2">ID</th>
                            <th style="position: sticky; left: 40px; background-color: #e3e3e3;" class="align-middle" rowspan="2">Name</th>
                            <th style="background-color: #e3e3e3;" class="align-middle" rowspan="2">Designation</th>
                            <th style="background-color: #e3e3e3;" class="align-middle" rowspan="2">Basic (Tk.)</th>
                            <th style="background-color: #e3e3e3;" class="align-middle" rowspan="2">House Rent (Tk.)</th>
                            <th style="background-color: #e3e3e3;" class="align-middle" rowspan="2">Medical Allowance (Tk.)</th>
                            <th style="background-color: #e3e3e3;" class="align-middle" rowspan="2">Conveyance (Tk.)</th>
                            <th style="background-color: #e3e3e3;" class="align-middle" rowspan="2">Gross Salary (Tk.)</th>
                            <th style="background-color: #e3e3e3;" class="align-middle" rowspan="2">Holiday Pay (Tk.)</th>
                            <th style="background-color: #e3e3e3;" class="align-middle" rowspan="2">Over Time (Tk.)</th>
                            <th style="background-color: #e3e3e3;" class="align-middle" rowspan="2">Total Payable (Tk.)</th>
                            <th style="background-color: #e3e3e3;" class="align-middle" rowspan="1" colspan="3">Adjustment / Deduction</th>
                            <th style="background-color: #e3e3e3;" class="align-middle" rowspan="2">Net Payable (Tk.)</th>
                            <th style="background-color: #e3e3e3;" class="align-middle" rowspan="2">Payment Mode</th>
                        </tr>
                        <tr style="position: sticky; top: 46px;vertical-align: top;">
                            <td style="background-color: #e3e3e3;">Advance (Tk.)</td>
                            <td style="background-color: #e3e3e3;">Loan (Tk.)</td>
                            <td style="background-color: #e3e3e3;">Income Tax (Tk.)</td>
                        </tr>

                        @php
                            $index = 1;
                        @endphp
                        @foreach($data['departments'] as $department)

                            @if ($data['is_employee'] == true)
                                @foreach ( $department['salaries'] as $salary )
                                    <tr>
                                        <td style="background-color: #e3e3e3;">{{ $index }}</td>
                                        <td style="position: sticky; left: 0px; background-color: #e3e3e3;">{{ $salary->user->fingerprint_no }}</td>
                                        <td style="position: sticky; left: 40px; background-color: #e3e3e3;">{{ $salary->user->name }}</td>
                                        <td>{{ $salary->designation->title }}</td>
                                        <td>{{ \App\Http\Controllers\SalaryController::currencyFormat($salary->basic) }}</td>
                                        <td>{{ \App\Http\Controllers\SalaryController::currencyFormat(collect($salary->earnings)->where("name", "House Rent")->first()->amount) }}</td>
                                        <td>{{ \App\Http\Controllers\SalaryController::currencyFormat(collect($salary->earnings)->where("name", "Medical Allowance")->first()->amount) }}</td>
                                        <td>{{ \App\Http\Controllers\SalaryController::currencyFormat(collect($salary->earnings)->where("name", "Conveyance")->first()->amount) }}</td>
                                        <td>{{ \App\Http\Controllers\SalaryController::currencyFormat($salary->gross) }}</td>
                                        <td>{{ \App\Http\Controllers\SalaryController::currencyFormat($salary->holiday_amount) }}</td>
                                        <td>{{ \App\Http\Controllers\SalaryController::currencyFormat($salary->overtime_amount) }}</td>
                                        <td>{{ \App\Http\Controllers\SalaryController::currencyFormat($salary->payable_amount) }}</td>
                                        <td>{{ \App\Http\Controllers\SalaryController::currencyFormat($salary->advance) }}</td>
                                        <td>{{ \App\Http\Controllers\SalaryController::currencyFormat($salary->loan) }}</td>
                                        <td>{{ \App\Http\Controllers\SalaryController::currencyFormat($salary->payable_tax_amount) }}</td>
                                        <td>{{ \App\Http\Controllers\SalaryController::currencyFormat($salary->net_payable_amount) }}</td>
                                        <td>{{ $salary->payment_mode }}</td>
                                    </tr>
                                @php
                                    $index++;
                                @endphp
                                @endforeach
                            @endif


                            @if ($data['is_department'] == true)
                                <tr style="background-color:#ffeac3;">
                                    <td>{{ $index }}</td>
                                    <td style="position: sticky; left: 0px; background-color: inherit;"> ### </td>
                                    <td style="position: sticky; left: 40px; background-color: inherit;">{{$department['name']}}</td>
                                    <td> -- </td>
                                    <td>{{ \App\Http\Controllers\SalaryController::currencyFormat(array_sum($department['basic'])) }}</td>
                                    <td>{{ \App\Http\Controllers\SalaryController::currencyFormat(array_sum($department['house_rent'])) }}</td>
                                    <td>{{ \App\Http\Controllers\SalaryController::currencyFormat(array_sum($department['medical_allowance'])) }}</td>
                                    <td>{{ \App\Http\Controllers\SalaryController::currencyFormat(array_sum($department['conveyance'])) }}</td>
                                    <td>{{ \App\Http\Controllers\SalaryController::currencyFormat(array_sum($department['gross'])) }}</td>
                                    <td>{{ \App\Http\Controllers\SalaryController::currencyFormat(array_sum($department['holiday_amount'])) }}</td>
                                    <td>{{ \App\Http\Controllers\SalaryController::currencyFormat(array_sum($department['overtime_amount'])) }}</td>
                                    <td>{{ \App\Http\Controllers\SalaryController::currencyFormat(array_sum($department['payable_amount'])) }}</td>
                                    <td>{{ \App\Http\Controllers\SalaryController::currencyFormat(array_sum($department['advance_amount'])) }}</td>
                                    <td>{{ \App\Http\Controllers\SalaryController::currencyFormat(array_sum($department['loan_amount'])) }}</td>
                                    <td>{{ \App\Http\Controllers\SalaryController::currencyFormat(array_sum($department['payable_tax_amount'])) }}</td>
                                    <td>{{ \App\Http\Controllers\SalaryController::currencyFormat(array_sum($department['net_payable_amount'])) }}</td>
                                    <td colspan="1"></td>
                                </tr>
                            @endif
                            @php
                                $index++;
                            @endphp
                        @endforeach

                    <tr>
                        <td colspan="4">TOTAL</td>
                        <td>{{ \App\Http\Controllers\SalaryController::currencyFormat($data['total']["basic"]) }}</td>
                        <td>{{ \App\Http\Controllers\SalaryController::currencyFormat($data['total']["house_rent"]) }}</td>
                        <td>{{ \App\Http\Controllers\SalaryController::currencyFormat($data['total']["medical_allowance"]) }}</td>
                        <td>{{ \App\Http\Controllers\SalaryController::currencyFormat($data['total']["conveyance"]) }}</td>
                        <td>{{ \App\Http\Controllers\SalaryController::currencyFormat($data['total']["gross"]) }}</td>
                        <td>{{ \App\Http\Controllers\SalaryController::currencyFormat($data['total']["holiday_amount"]) }}</td>
                        <td>{{ \App\Http\Controllers\SalaryController::currencyFormat($data['total']["overtime_amount"]) }}</td>
                        <td>{{ \App\Http\Controllers\SalaryController::currencyFormat($data['total']["payable_amount"]) }}</td>
                        <td>{{ \App\Http\Controllers\SalaryController::currencyFormat($data['total']["advance_amount"]) }}</td>
                        <td>{{ \App\Http\Controllers\SalaryController::currencyFormat($data['total']["loan_amount"]) }}</td>
                        <td>{{ \App\Http\Controllers\SalaryController::currencyFormat($data['total']["payable_tax_amount"]) }}</td>
                        <td>{{ \App\Http\Controllers\SalaryController::currencyFormat($data['total']["net_payable_amount"]) }}</td>
                        <td colspan="1"></td>
                    </tr>
                    <tr>
                        <td colspan="5">IN WORDS</td>
                        <td colspan="24" style="text-align: left">
                            {{ \App\Http\Controllers\SalaryController::getBangladeshCurrency($data['total']["net_payable_amount"]) }}
                        </td>
                    </tr>
                    </thead>
                </table>
            </div>
        </div>
    </div>
@endsection

@section("footer-js")
    <script type="text/javascript">
        $(document).ready(function () {
            //
        });
    </script>
@endsection
