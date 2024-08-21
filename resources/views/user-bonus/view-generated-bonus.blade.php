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
                    <b>{{ strtoupper($bonuses->first()->officeDivision->name) }} DIVISION</b><br/>
                    <b>Bonus Sheet: {{ strtoupper($bonuses->first()->department->name) }}</b>
                </div>
                <div class="col-lg-7 text-right">
                    @can("Export Salary CSV")
                    <form class="form1" action="{{ route('user-bonus.bonusExport', ['bonusDepartment' => $uuid]) }}" method="POST">
                        @csrf
                        <input type="hidden" name="type" value="Export CSV"/>
                        <button title="Export CSV" class="btn btn-sm btn-primary ml-2 salary_btn mb-2" type="submit"><i class="fa fa-file-csv salary_btn_csv"></i> CSV</button>
                    </form>
                    @endcan
                    @can("Export Salary PDF")
                    <form class="form1" action="{{ route('user-bonus.bonusExport', ['bonusDepartment' => $uuid]) }}" method="POST">
                        @csrf
                        <input type="hidden" name="type" value="Export PDF"/>
                        <button title="Export PDF" class="btn btn-sm btn-primary ml-2 salary_btn mb-2" type="submit"><i class="fa fa-file-pdf salary_btn_pdf"></i> PDF</button>
                    </form>
                    @endcan
                    @can("Export Salary Bank Statement CSV")
                    <form class="form1" action="{{ route('user-bonus.bonusExport', ['bonusDepartment' => $uuid]) }}" method="POST">
                        @csrf
                        <input type="hidden" name="type" value="Bank Statement CSV"/>
                        <button title="Bank Statement CSV" class="btn btn-sm btn-primary ml-2 salary_btn mb-2" type="submit"><i class="fa fa-file-csv salary_btn_csv"></i> Bank Statement</button>
                    </form>
                    @endcan
                    @can("Export Salary Bank Statement PDF")
                    <form class="form1" action="{{ route('user-bonus.bonusExport', ['bonusDepartment' => $uuid]) }}" method="POST">
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
                        <th colspan="3" style="background-color: #e3e3e3;">Bonus Name:</th>
                        <th colspan="4">{{ $bonuses->first()->bonus->festival_name }}</th>
                        <th colspan="3" style="background-color: #e3e3e3;">PREPARATION DATE:</th>
                        <th colspan="5">{{ date('M d, Y', strtotime($bonuses->first()->created_at)) }}</th>
                    </tr>
                    <tr>
                        <th style="background-color: #e3e3e3;" class="align-middle" rowspan="2">Sl. No.</th>
                        <th style="background-color: #e3e3e3;" class="align-middle" rowspan="2">ID</th>
                        <th style="background-color: #e3e3e3;" width="20%" class="align-middle" rowspan="2">Name</th>
                        <th style="background-color: #e3e3e3;" class="align-middle" rowspan="2">Designation</th>
                        <th style="background-color: #e3e3e3;" class="align-middle" rowspan="2">Joining Date</th>
                        <th style="background-color: #e3e3e3;" class="align-middle" rowspan="2">Basic (Tk.)</th>
                        <th style="background-color: #e3e3e3;" class="align-middle" rowspan="2">House Rent (Tk.)</th>
                        <th style="background-color: #e3e3e3;" class="align-middle" rowspan="2">Medical Allowance (Tk.)</th>
                        <th style="background-color: #e3e3e3;" class="align-middle" rowspan="2">Conveyance (Tk.)</th>
                        <th style="background-color: #e3e3e3;" class="align-middle" rowspan="2">Gross Salary (Tk.)</th>
                        <th style="background-color: #e3e3e3;" class="align-middle" rowspan="2">Total Payable (Tk.)</th>
                        <th style="background-color: #e3e3e3;" class="align-middle" rowspan="1">Income Tax (Tk.)</th>
                        <th style="background-color: #e3e3e3;" class="align-middle" rowspan="2">Net Payable (Tk.)</th>
                        <th style="background-color: #e3e3e3;" class="align-middle" rowspan="2">Payment Mode</th>
                        <th style="background-color: #e3e3e3;" width="10%" class="align-middle" rowspan="2">Remarks</th>
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
                    @endphp
                    <thead>
                    @foreach($bonuses as $key => $data)
                        @php
                            $total["basic"] += $data->basic;
                            $total["house_rent"] += $data->house_rent;
                            $total["medical_allowance"] += $data->medical_allowance;
                            $total["conveyance"] += $data->conveyance;
                            $total["gross"] += $data->gross;
                            $total["payable_amount"] += $data->amount;
                            $total["payable_tax_amount"] += $data->tax;
                            $total["net_payable_amount"] += $data->net_payable_amount;
                        @endphp

                        <tr>
                            <td style="background-color: #e3e3e3;">{{ $key+1 }}</td>
                            <td >{{ $data->user->fingerprint_no }}</td>
                            <td>{{ $data->user->name }}</td>
                            <td>{{ $data->designation->title }}</td>
                            <td>{{ date('M d, Y', strtotime($data->user->employeeStatusJoining->action_date)) }}</td>
                            <td>{{ \App\Http\Controllers\SalaryController::currencyFormat($data->basic) }}</td>
                            <td>{{ \App\Http\Controllers\SalaryController::currencyFormat($data->house_rent) }}</td>
                            <td>{{ \App\Http\Controllers\SalaryController::currencyFormat($data->medical_allowance) }}</td>
                            <td>{{ \App\Http\Controllers\SalaryController::currencyFormat($data->conveyance) }}</td>
                            <td>{{ \App\Http\Controllers\SalaryController::currencyFormat($data->gross) }}</td>
                            <td>{{ \App\Http\Controllers\SalaryController::currencyFormat($data->amount) }}</td>
                            <td>{{ \App\Http\Controllers\SalaryController::currencyFormat($data->tax) }}</td>
                            <td>{{ \App\Http\Controllers\SalaryController::currencyFormat($data->net_payable_amount) }}</td>
                            <td>{{ $data->payment_mode }}</td>
                            <td>{{ $data->remarks }}</td>
                        </tr>
                    @endforeach
                    </thead>
                    <thead>
                    <tr>
                        <td colspan="5">TOTAL</td>
                        <td>{{ \App\Http\Controllers\SalaryController::currencyFormat($total["basic"]) }}</td>
                        <td>{{ \App\Http\Controllers\SalaryController::currencyFormat($total["house_rent"]) }}</td>
                        <td>{{ \App\Http\Controllers\SalaryController::currencyFormat($total["medical_allowance"]) }}</td>
                        <td>{{ \App\Http\Controllers\SalaryController::currencyFormat($total["conveyance"]) }}</td>
                        <td>{{ \App\Http\Controllers\SalaryController::currencyFormat($total["gross"]) }}</td>
                        <td>{{ \App\Http\Controllers\SalaryController::currencyFormat($total["payable_amount"]) }}</td>
                        <td>{{ \App\Http\Controllers\SalaryController::currencyFormat($total["payable_tax_amount"]) }}</td>
                        <td>{{ \App\Http\Controllers\SalaryController::currencyFormat($total["net_payable_amount"]) }}</td>
                        <td colspan="2"></td>
                    </tr>
                    <tr>
                        <td colspan="5">IN WORDS</td>
                        <td colspan="10" style="text-align: left">
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
                            <td>{{ $bonusDepartment->preparedBy->fingerprint_no . ' - ' . $bonusDepartment->preparedBy->name }}</td>
                            <td>{{ date('j F, Y, g:i a', strtotime($bonusDepartment->prepared_date)) }}</td>
                            <td>Approved</td>
                            <td></td>
                            <td>N/A</td>
                        </tr>
                        <tr>
                            <td>Divisional Approval by:</td>
                            <td>
                                @if($bonusDepartment->divisional_approval_status === 0 && $bonusDepartment->status == 0 && $bonusDepartment->departmental_approval_status !== 2 && in_array($bonusDepartment->office_division_id, $divisionIds))
                                    @can('Salary Divisional Approval')
                                        <a href="#" data-toggle="modal" data-target="#approvalModal-divisional">Click to Approve</a>
                                    @endcan
                                @else
                                    {{ optional($bonusDepartment->divisionalApprovalBy)->fingerprint_no . ' - ' . optional($bonusDepartment->divisionalApprovalBy)->name }}
                                @endif
                            </td>
                            <td>
                                {!! (!empty($bonusDepartment->divisional_approved_date))? date('j F, Y, g:i a', strtotime($bonusDepartment->divisional_approved_date)): '-' !!}
                            </td>
                            <td>
                                @if($bonusDepartment->divisional_approval_status === 0) Pending
                                @elseif($bonusDepartment->divisional_approval_status === 1) Approved
                                @elseif($bonusDepartment->divisional_approval_status === 2) Rejected
                                @endif
                            </td>
                            <td>{{ $bonusDepartment->divisional_remarks }}</td>
                            <td>N/A</td>
                        </tr>
                        <tr>
                            <td>Departmental Approval by:</td>
                            <td>
                                @if($bonusDepartment->departmental_approval_status === 0 && $bonusDepartment->status == 0 && $bonusDepartment->divisional_approval_status !== 2 && in_array($bonusDepartment->department_id, $departmentIds))
                                    @can('Salary Departmental Approval')
                                        <a href="#" data-toggle="modal" data-target="#approvalModal-departmental">Click to Approve</a>
                                    @endcan
                                @else
                                    {{ optional($bonusDepartment->departmentalApprovalBy)->fingerprint_no . ' - ' . optional($bonusDepartment->departmentalApprovalBy)->name }}
                                @endif

                            </td>
                            <td>
                                {!! (!empty($bonusDepartment->departmental_approved_date))? date('j F, Y, g:i a', strtotime($bonusDepartment->departmental_approved_date)): '-' !!}
                            </td>
                            <td>
                                @if($bonusDepartment->departmental_approval_status === 0) Pending
                                @elseif($bonusDepartment->departmental_approval_status === 1) Approved
                                @elseif($bonusDepartment->departmental_approval_status === 2) Rejected
                                @endif
                            </td>
                            <td>{{ $bonusDepartment->departmental_remarks }}</td>
                            <td>N/A</td>
                        </tr>
                        <tr>
                            <td>HR Approval by:</td>
                            <td>
                                @php $hrRejectionCondition = ($bonusDepartment->divisional_approval_status !== 2 && $bonusDepartment->departmental_approval_status !== 2) @endphp
                                @if($bonusDepartment->hr_approval_status === 0 && $bonusDepartment->status == 0 && $hrRejectionCondition)
                                    @can('Salary HR Approval')
                                        <a href="#" data-toggle="modal" data-target="#approvalModal-hr">Click to Approve</a>
                                    @endcan
                                @else
                                    {{ optional($bonusDepartment->hrApprovalBy)->fingerprint_no . ' - ' . optional($bonusDepartment->hrApprovalBy)->name }}
                                @endif
                            </td>
                            <td>
                                {!! (!empty($bonusDepartment->hr_approved_date))? date('j F, Y, g:i a', strtotime($bonusDepartment->hr_approved_date)): '-' !!}
                            </td>
                            <td>
                                @if($bonusDepartment->hr_approval_status === 0) Pending
                                @elseif($bonusDepartment->hr_approval_status === 1) Approved
                                @elseif($bonusDepartment->hr_approval_status === 2) Rejected
                                @endif
                            </td>
                            <td>{{ $bonusDepartment->hr_remarks }}</td>
                            <td>N/A</td>
                        </tr>
                        <tr>
                            <td>Accounts Approval by:</td>
                            <td>
                                @if($bonusDepartment->accounts_approval_status === 0 && $bonusDepartment->status == 0 && $bonusDepartment->hr_approval_status == 1)
                                    @can('Salary Accounts Approval')
                                        <a href="#" data-toggle="modal" data-target="#approvalModal-accounts">Click to Approve</a>
                                    @endcan
                                @else
                                    {{ optional($bonusDepartment->accountsApprovalBy)->fingerprint_no . ' - ' . optional($bonusDepartment->accountsApprovalBy)->name }}
                                @endif
                            </td>
                            <td>
                                {!! (!empty($bonusDepartment->accounts_approved_date))? date('j F, Y, g:i a', strtotime($bonusDepartment->accounts_approved_date)): '-' !!}
                            </td>
                            <td>
                                @if($bonusDepartment->accounts_approval_status === 0) Pending
                                @elseif($bonusDepartment->accounts_approval_status === 1) Approved
                                @elseif($bonusDepartment->accounts_approval_status === 2) Rejected
                                @endif
                            </td>
                            <td>{{ $bonusDepartment->accounts_remarks }}</td>
                            <td>N/A</td>
                        </tr>
                        <tr>
                            <td>Top Management Approval by:</td>
                            <td>
                                @if($bonusDepartment->managerial_approval_status === 0 && $bonusDepartment->status == 0 && $bonusDepartment->accounts_approval_status == 1 && $bonusDepartment->hr_approval_status == 1)
                                    @can('Salary Managerial Approval')
                                        <a href="#" data-toggle="modal" data-target="#approvalModal-managerial">Click to Approve</a>
                                    @endcan
                                @else
                                    {{ optional($bonusDepartment->managerialApprovalBy)->fingerprint_no . ' - ' . optional($bonusDepartment->managerialApprovalBy)->name }}
                                @endif
                            </td>
                            <td>
                                {!! (!empty($bonusDepartment->managerial_approved_date))? date('j F, Y, g:i a', strtotime($bonusDepartment->managerial_approved_date)): '-' !!}
                            </td>
                            <td>
                                @if($bonusDepartment->managerial_approval_status === 0) Pending
                                @elseif($bonusDepartment->managerial_approval_status === 1) Approved
                                @elseif($bonusDepartment->managerial_approval_status === 2) Rejected
                                @endif
                            </td>
                            <td>{{ $bonusDepartment->managerial_remarks }}</td>
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
                <form class="form" action="{{ route('user-bonus.approvalDivisional') }}" method="POST">
                    @csrf
                    <input type="hidden" name="uuid" value="{{ $bonusDepartment->uuid }}"/>
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
                <form class="form" action="{{ route('user-bonus.approvalDepartmental') }}" method="POST">
                    @csrf
                    <input type="hidden" name="uuid" value="{{ $bonusDepartment->uuid }}"/>
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
                <form class="form" action="{{ route('user-bonus.approvalHr') }}" method="POST">
                    @csrf
                    <input type="hidden" name="uuid" value="{{ $bonusDepartment->uuid }}"/>
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
                <form class="form" action="{{ route('user-bonus.approvalAccounts') }}" method="POST">
                    @csrf
                    <input type="hidden" name="uuid" value="{{ $bonusDepartment->uuid }}"/>
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
                <form class="form" action="{{ route('user-bonus.approvalManagerial') }}" method="POST">
                    @csrf
                    <input type="hidden" name="uuid" value="{{ $bonusDepartment->uuid }}"/>
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
    </script>
@endsection
