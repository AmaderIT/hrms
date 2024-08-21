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
                    <b>DIVISION: {{ strtoupper($departmentLeaveEncashment->officeDivision->name) }}</b><br/>
                    <b>Leave Encashment Sheet: {{ strtoupper($departmentLeaveEncashment->department->name) }}</b>
                </div>
                <div class="col-lg-7 text-right">
                    @can("Export Leave Encashment EXCEL")
                        <form class="form1" action="{{ route('leave-encashment.exportLeaveEncashment', ['departmentLeaveEncashment' => $departmentLeaveEncashment->uuid]) }}" method="POST">
                            @csrf
                            <input type="hidden" name="type" value="Export Excel"/>
                            <button title="Export Excel" class="btn btn-sm btn-primary ml-2 salary_btn mb-2" type="submit"><i class="fa fa-file-excel salary_btn_csv"></i> Excel</button>
                        </form>
                    @endcan
                    @can("Export Leave Encashment PDF")
                        <form class="form1" action="{{ route('leave-encashment.exportLeaveEncashment', ['departmentLeaveEncashment' => $departmentLeaveEncashment->uuid]) }}" method="POST">
                            @csrf
                            <input type="hidden" name="type" value="Export PDF"/>
                            <button title="Export PDF" class="btn btn-sm btn-primary ml-2 salary_btn mb-2" type="submit"><i class="fa fa-file-pdf salary_btn_pdf"></i> PDF</button>
                        </form>
                    @endcan
                    @can("Export Leave Encashment Bank Statement EXCEL")
                        <form class="form1" action="{{ route('leave-encashment.exportLeaveEncashment', ['departmentLeaveEncashment' => $departmentLeaveEncashment->uuid]) }}" method="POST">
                            @csrf
                            <input type="hidden" name="type" value="Bank Statement Excel"/>
                            <button title="Bank Statement Excel" class="btn btn-sm btn-primary ml-2 salary_btn mb-2" type="submit"><i class="fa fa-file-excel salary_btn_csv"></i> Bank Statement</button>
                        </form>
                    @endcan
                    @can("Export Leave Encashment Bank Statement PDF")
                        <form class="form1" action="{{ route('leave-encashment.exportLeaveEncashment', ['departmentLeaveEncashment' => $departmentLeaveEncashment->uuid]) }}" method="POST">
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
                        @php
                            $total_head = 11+count($earnings)+($leave_types->count()*4); // 11 = Fixed column count at 2nd&3rd row
                            $rest = $total_head-11; // 11 = first row's used column count
                            $earn_ar = [];
                            $total = 0;
                            $total_basic_salary_amount = 0;
                            $total_gross_salary_amount = 0;
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
                            <th colspan="2">{{ date('M d, Y', strtotime($departmentLeaveEncashment->prepared_date)) }}</th>
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
                                $total_basic_salary_amount += $data->basic_salary_amount;
                                $total_gross_salary_amount += $data->gross_salary_amount;
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
                                    <td>@isset($leave_details[$type->id]) {{currencyFormat($leave_details[$type->id]->payable_amount)}} @else {{0}} @endisset</td>
                                @endforeach
                                <td>{{ currencyFormat($data->tax_amount) }}</td>
                                <td>{{ currencyFormat($data->total_payable_amount) }}</td>
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
                            <td colspan="1">{{ currencyFormat($departmentLeaveEncashment->total_payable_amount) }}</td>
                        </tr>
                        <tr>
                            <td colspan="5">TOTAL IN WORDS</td>
                            <td colspan="{{$total_head-5}}" style="text-align: left;font-weight: 600;">
                                {{ getBangladeshCurrency($departmentLeaveEncashment->total_payable_amount) }}
                            </td>
                        </tr>
                    </tbody>
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
                            <td>{{ $departmentLeaveEncashment->preparedBy->fingerprint_no . ' - ' . $departmentLeaveEncashment->preparedBy->name }}</td>
                            <td>{{ date('j F, Y, g:i a', strtotime($departmentLeaveEncashment->prepared_date)) }}</td>
                            <td>Approved</td>
                            <td></td>
                            <td>N/A</td>
                        </tr>
                        <tr>
                            <td>Divisional Approval by:</td>
                            <td>
                                @if($departmentLeaveEncashment->divisional_approval_status === 0 && $departmentLeaveEncashment->pay_status == 0 && ($departmentLeaveEncashment->departmental_approval_status !== 2 && $departmentLeaveEncashment->hr_approval_status !== 2 && $departmentLeaveEncashment->accounts_approval_status !== 2 && $departmentLeaveEncashment->managerial_approval_status !== 2) && in_array($departmentLeaveEncashment->department_id, $departmentIds))
                                    @can('Leave Encashment Divisional Approval')
                                        <a href="#" data-toggle="modal" data-target="#approvalModal-divisional">Click to Approve</a>
                                    @endcan
                                @else
                                    {{ optional($departmentLeaveEncashment->divisionalApprovalBy)->fingerprint_no . ' - ' . optional($departmentLeaveEncashment->divisionalApprovalBy)->name }}
                                @endif
                            </td>
                            <td>
                                {!! (!empty($departmentLeaveEncashment->divisional_approved_date))? date('j F, Y, g:i a', strtotime($departmentLeaveEncashment->divisional_approved_date)): '-' !!}
                            </td>
                            <td>
                                @if($departmentLeaveEncashment->divisional_approval_status === 0) Pending
                                @elseif($departmentLeaveEncashment->divisional_approval_status === 1) Approved
                                @elseif($departmentLeaveEncashment->divisional_approval_status === 2) Rejected
                                @endif
                            </td>
                            <td>{{ $departmentLeaveEncashment->divisional_remarks }}</td>
                            <td>N/A</td>
                        </tr>
                        <tr>
                            <td>Departmental Approval by:</td>
                            <td>
                                @if($departmentLeaveEncashment->pay_status == 0 && ($departmentLeaveEncashment->divisional_approval_status !== 2 && $departmentLeaveEncashment->hr_approval_status !== 2 && $departmentLeaveEncashment->accounts_approval_status !== 2 && $departmentLeaveEncashment->managerial_approval_status !== 2) && $departmentLeaveEncashment->departmental_approval_status === 0 && in_array($departmentLeaveEncashment->department_id, $departmentIds))
                                    @can('Leave Encashment Departmental Approval')
                                        <a href="#" data-toggle="modal" data-target="#approvalModal-departmental">Click to Approve</a>
                                    @endcan
                                @else
                                    {{ optional($departmentLeaveEncashment->departmentalApprovalBy)->fingerprint_no . ' - ' . optional($departmentLeaveEncashment->departmentalApprovalBy)->name }}
                                @endif
                            </td>
                            <td>
                                {!! (!empty($departmentLeaveEncashment->departmental_approved_date))? date('j F, Y, g:i a', strtotime($departmentLeaveEncashment->departmental_approved_date)): '-' !!}
                            </td>
                            <td>
                                @if($departmentLeaveEncashment->departmental_approval_status === 0) Pending
                                @elseif($departmentLeaveEncashment->departmental_approval_status === 1) Approved
                                @elseif($departmentLeaveEncashment->departmental_approval_status === 2) Rejected
                                @endif
                            </td>
                            <td>{{ $departmentLeaveEncashment->departmental_remarks }}</td>
                            <td>N/A</td>
                        </tr>
                        <tr>
                            <td>HR Approval by:</td>
                            <td>
                                @if(($departmentLeaveEncashment->departmental_approval_status !== 2 && $departmentLeaveEncashment->divisional_approval_status !== 2 && $departmentLeaveEncashment->accounts_approval_status !== 2 && $departmentLeaveEncashment->managerial_approval_status !== 2) && $departmentLeaveEncashment->pay_status == 0 && $departmentLeaveEncashment->hr_approval_status === 0)
                                    @can('Leave Encashment HR Approval')
                                        <a href="#" data-toggle="modal" data-target="#approvalModal-hr">Click to Approve</a>
                                    @endcan
                                @else
                                    {{ optional($departmentLeaveEncashment->hrApprovalBy)->fingerprint_no . ' - ' . optional($departmentLeaveEncashment->hrApprovalBy)->name }}
                                @endif
                            </td>
                            <td>
                                {!! (!empty($departmentLeaveEncashment->hr_approved_date))? date('j F, Y, g:i a', strtotime($departmentLeaveEncashment->hr_approved_date)): '-' !!}
                            </td>
                            <td>
                                @if($departmentLeaveEncashment->hr_approval_status === 0) Pending
                                @elseif($departmentLeaveEncashment->hr_approval_status === 1) Approved
                                @elseif($departmentLeaveEncashment->hr_approval_status === 2) Rejected
                                @endif
                            </td>
                            <td>{{ $departmentLeaveEncashment->hr_remarks }}</td>
                            <td>N/A</td>
                        </tr>
                        <tr>
                            <td>Accounts Approval by:</td>
                            <td>
                                @if($departmentLeaveEncashment->hr_approval_status === 1 && $departmentLeaveEncashment->pay_status == 0 && $departmentLeaveEncashment->accounts_approval_status === 0 && ($departmentLeaveEncashment->departmental_approval_status !== 2 && $departmentLeaveEncashment->divisional_approval_status !== 2 && $departmentLeaveEncashment->hr_approval_status !== 2 && $departmentLeaveEncashment->managerial_approval_status !== 2))
                                    @can('Leave Encashment Accounts Approval')
                                        <a href="#" data-toggle="modal" data-target="#approvalModal-accounts">Click to Approve</a>
                                    @endcan
                                @else
                                    {{ optional($departmentLeaveEncashment->accountsApprovalBy)->fingerprint_no . ' - ' . optional($departmentLeaveEncashment->accountsApprovalBy)->name }}
                                @endif
                            </td>
                            <td>
                                {!! (!empty($departmentLeaveEncashment->accounts_approved_date))? date('j F, Y, g:i a', strtotime($departmentLeaveEncashment->accounts_approved_date)): '-' !!}
                            </td>
                            <td>
                                @if($departmentLeaveEncashment->accounts_approval_status === 0) Pending
                                @elseif($departmentLeaveEncashment->accounts_approval_status === 1) Approved
                                @elseif($departmentLeaveEncashment->accounts_approval_status === 2) Rejected
                                @endif
                            </td>
                            <td>{{ $departmentLeaveEncashment->accounts_remarks }}</td>
                            <td>N/A</td>
                        </tr>
                        <tr>
                            <td>Top Management Approval by:</td>
                            <td>
                                @if($departmentLeaveEncashment->hr_approval_status === 1 && ($departmentLeaveEncashment->departmental_approval_status !== 2 && $departmentLeaveEncashment->divisional_approval_status !== 2 && $departmentLeaveEncashment->hr_approval_status !== 2 && $departmentLeaveEncashment->accounts_approval_status !== 2) && $departmentLeaveEncashment->accounts_approval_status === 1 && $departmentLeaveEncashment->managerial_approval_status === 0 && $departmentLeaveEncashment->pay_status == 0)
                                    @can('Leave Encashment Managerial Approval')
                                        <a href="#" data-toggle="modal" data-target="#approvalModal-managerial">Click to Approve</a>
                                    @endcan
                                @else
                                    {{ optional($departmentLeaveEncashment->managerialApprovalBy)->fingerprint_no . ' - ' . optional($departmentLeaveEncashment->managerialApprovalBy)->name }}
                                @endif
                            </td>
                            <td>
                                {!! (!empty($departmentLeaveEncashment->managerial_approved_date))? date('j F, Y, g:i a', strtotime($departmentLeaveEncashment->managerial_approved_date)): '-' !!}
                            </td>
                            <td>
                                @if($departmentLeaveEncashment->managerial_approval_status === 0) Pending
                                @elseif($departmentLeaveEncashment->managerial_approval_status === 1) Approved
                                @elseif($departmentLeaveEncashment->managerial_approval_status === 2) Rejected
                                @endif
                            </td>
                            <td>{{ $departmentLeaveEncashment->managerial_remarks }}</td>
                            <td>N/A</td>
                        </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    {{-- Divisional Approval Modal --}}
    @can("Leave Encashment Divisional Approval")
        <div class="modal fade" id="approvalModal-divisional" tabindex="-1" role="dialog" aria-labelledby="exampleModalSizeXl" aria-hidden="true">
            <div class="modal-dialog modal-dialog-scrollable modal-md" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="exampleModalLabel">Divisional Approval</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <i aria-hidden="true" class="ki ki-close"></i>
                        </button>
                    </div>
                    <form class="form" action="{{ route('leave-encashment.approvalDivisional') }}" method="POST">
                        @csrf
                        <input type="hidden" name="uuid" value="{{ $departmentLeaveEncashment->uuid }}"/>
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
    @can("Leave Encashment Departmental Approval")
        <div class="modal fade" id="approvalModal-departmental" tabindex="-1" role="dialog" aria-labelledby="exampleModalSizeXl" aria-hidden="true">
            <div class="modal-dialog modal-dialog-scrollable modal-md" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="exampleModalLabel">Departmental Approval</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <i aria-hidden="true" class="ki ki-close"></i>
                        </button>
                    </div>
                    <form class="form" action="{{ route('leave-encashment.approvalDepartmental') }}" method="POST">
                        @csrf
                        <input type="hidden" name="uuid" value="{{ $departmentLeaveEncashment->uuid }}"/>
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
    @can("Leave Encashment HR Approval")
        <div class="modal fade" id="approvalModal-hr" tabindex="-1" role="dialog" aria-labelledby="exampleModalSizeXl" aria-hidden="true">
            <div class="modal-dialog modal-dialog-scrollable modal-md" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="exampleModalLabel">HR Approval</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <i aria-hidden="true" class="ki ki-close"></i>
                        </button>
                    </div>
                    <form class="form" action="{{ route('leave-encashment.approvalHr') }}" method="POST">
                        @csrf
                        <input type="hidden" name="uuid" value="{{ $departmentLeaveEncashment->uuid }}"/>
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
    @can("Leave Encashment Accounts Approval")
        <div class="modal fade" id="approvalModal-accounts" tabindex="-1" role="dialog" aria-labelledby="exampleModalSizeXl" aria-hidden="true">
            <div class="modal-dialog modal-dialog-scrollable modal-md" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="exampleModalLabel">Accounts Approval</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <i aria-hidden="true" class="ki ki-close"></i>
                        </button>
                    </div>
                    <form class="form" action="{{ route('leave-encashment.approvalAccounts') }}" method="POST">
                        @csrf
                        <input type="hidden" name="uuid" value="{{ $departmentLeaveEncashment->uuid }}"/>
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
    @can("Leave Encashment Managerial Approval")
        <div class="modal fade" id="approvalModal-managerial" tabindex="-1" role="dialog" aria-labelledby="exampleModalSizeXl" aria-hidden="true">
            <div class="modal-dialog modal-dialog-scrollable modal-md" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="exampleModalLabel">Managerial Approval</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <i aria-hidden="true" class="ki ki-close"></i>
                        </button>
                    </div>
                    <form class="form" action="{{ route('leave-encashment.approvalManagerial') }}" method="POST">
                        @csrf
                        <input type="hidden" name="uuid" value="{{ $departmentLeaveEncashment->uuid }}"/>
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
