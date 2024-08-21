@extends('layouts.app')

@section("top-css")
    <style>
        #advance-policy {
            display: none;
        }

        .policy_ul {
            padding: 0;
        }

        .policy_ul li {
            list-style: none;
            margin-bottom: 5px;
            font-size: 14px;
        }

        .policy_ul li i {
            font-size: 13px;
            display: inline-block;
            color: #3F4254;
        }

        .color-red {
            color: red !important;
        }

        .color-green {
            color: #00d300 !important;
        }

        .policy_violation_p {
            margin-top: 5px;
            font-size: 15px;
            display: none;
        }

        .installment_table {
            position: relative;
        }

        .installment_table tr {
            position: relative;
        }

        .installment_table td {
            text-align: center;
        }

        .installment_table input {
            width: 100%;
            padding: 4px 5px;
        }

        .installment_table select {
            width: 100%;
            padding: 4px 0;
        }

        .installment_table .select2-container {
            width: 100% !important;
            text-align: left;
        }

        .add_new_icon {
            position: absolute;
            right: 13px;
            bottom: -20px;
            color: #fff;
            background: green;
            padding: 6px 8px;
            cursor: pointer;
        }

        .remove_icon {
            position: absolute;
            right: 10px;
            bottom: 17px;
            color: #fff;
            background: #d7041a;
            cursor: pointer;
            z-index: 9;
            border-radius: 2px;
            padding: 8px 6px;
        }
        .view_table td{
            font-weight: 400 !important;
            width: 25%;
        }
        .view_table th{
            width: 25%;
        }
        .view_table td, .view_table th {
            vertical-align: middle !important;
        }
        .table td{
            vertical-align: middle;
        }
    </style>
@endsection

@section('content')
    <div class="row">
        <div class="col-md-8">
            <div class="card card-custom gutter-b example example-compact">
                <div class="card-header">
                    <h3 class="card-title">Details of {{ $loan->type }}</h3>
                    <div class="card-toolbar">
                        <div class="example-tools justify-content-center">
                            <a href="{{ route(!empty($_GET['requested'])?'requested-loan-advance.index': 'loan.index') }}" class="btn btn-primary mr-2">Back</a>
                        </div>
                    </div>
                </div>
                <form action="{{ route('requested-loan-advance.update', ['loan' => $loan->uuid]) }}"
                      id="policy-form" method="POST">
                    @csrf
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-12">
                                <table class="table table-bordered view_table">
                                    <thead>
                                        <tr>
                                            <th width="20%">Name </th>
                                            <td width="30%">{{ $loan->user->name??'' }} ({{ $loan->user->fingerprint_no??'' }})</td>
                                            <th width="20%">Designation </th>
                                            <td width="30%">{{ $loan->user->currentPromotion->designation->title??'' }}</td>
                                        </tr>
                                        <tr>
                                            <th width="20%">Office Division </th>
                                            <td width="30%">{{ $loan->user->currentPromotion->officeDivision->name??'' }}</td>
                                            <th width="20%">Department </th>
                                            <td width="30%">{{ $loan->user->currentPromotion->department->name??'' }}</td>
                                        </tr>
                                        <tr>
                                            <th width="20%">Type </th>
                                            <td width="30%">{{ $loan->type }}</td>
                                            <th width="20%">Installment Start Month: </th>
                                            <td width="30%">{{ $loan->installment_start_month }}</td>
                                        </tr>
                                        <tr>
                                            <th width="20%">Loan Amount </th>
                                            <td width="30%">{{ $loan->loan_amount }}</td>
                                            <th width="20%">Total Installment(In Month) </th>
                                            <td width="30%">{{ $loan->loan_tenure }}</td>
                                        </tr>
                                        <tr>
                                            <th width="20%">Remarks </th>
                                            <td width="80%" colspan="3">{{ $loan->remarks }}</td>
                                        </tr>
                                        <tr>
                                            <th width="20%">Application Date </th>
                                            <td width="30%">{{ date("d M, Y",strtotime($loan->created_at)) }}</td>
                                            <th width="20%">Amount Paid By </th>
                                            <td width="30%">
                                                @if(!empty($loan->paidBy))
                                                    {{ $loan->paidBy->fingerprint_no . ' - ' . $loan->paidBy->name }}
                                                    <small>{{$loan->loan_paid_date ? "@".date("d M, Y",strtotime($loan->loan_paid_date)) : ""}}</small>
                                                @else
                                                    <span class="text-warning">Amount not paid yet</span>
                                                @endif
                                            </td>
                                        </tr>
                                        <tr>
                                            <th width="20%">Loan Status </th>
                                            <td width="80%">
                                                @php $color = '' @endphp
                                                @if($loan->status == \App\Models\Loan::STATUS_PAID)
                                                    @php $color = 'color-green' @endphp
                                                @elseif($loan->status == \App\Models\Loan::STATUS_REJECT)
                                                    @php $color = 'color-red' @endphp
                                                @endif
                                                <span class="{{ $color }}">{{ $loan->status }}</span>
                                            </td>
                                        </tr>
                                    </thead>
                                </table>
                            </div>
                            <div class="col-md-12">
                                <div class="form-group">
                                    <p class="text-danger policy_violation_p">Some of Policy could not meet with your
                                        application</p>
                                </div>
                            </div>
                            <div class="col-md-12" id="installment_table_main" style="position: relative">
                                <table class="installment_table table table-bordered">
                                    <thead>
                                    <tr class="text-center">
                                        <th width="10%">SL No.</th>
                                        <th width="25%">Month</th>
                                        <th width="25%">Installment Amount</th>
                                        <th width="30%">Remark</th>
                                        <th width="10%">Status</th>
                                        @if($loan->status == \App\Models\Loan::STATUS_ACTIVE && auth()->user()->can("Pay Installment Amount"))
                                            <th width="10%">Custom Payment</th>
                                        @endif
                                    </tr>
                                    </thead>

                                    <tbody>
                                    @php $sl = 1 @endphp
                                    @foreach($loan->userLoans as $instalment)
                                        @php
                                            $month = !empty($instalment->month)? date('M, Y', strtotime(date('d-m-Y', strtotime("01-$instalment->month-$instalment->year")))): null;
                                        @endphp
                                        <tr data-id="{{ $sl }}" class="installment_tr">
                                            <td>{{ $sl }}</td>
                                            <td>{{ $month }}</td>
                                            <td>{{ $instalment->amount_paid? round($instalment->amount_paid, 2): 0 }}</td>
                                            <td>{{ $instalment->remark }}</td>
                                            <td>{{ \App\Models\UserLoan::STATUS[$instalment->status]??'-' }}</td>
                                            @if($loan->status == \App\Models\Loan::STATUS_ACTIVE && auth()->user()->can("Pay Installment Amount"))
                                                <td>
                                                    @if($instalment->status == \App\Models\UserLoan::AMOUNT_APPROVED)
                                                        <a href="#" data-uuid="{{ $instalment->uuid }}" onclick="paymentEmployeeLoan(this)"
                                                           class="btn btn-sm btn-primary">Pay</a>
                                                    @endif
                                                </td>
                                            @endif
                                        </tr>
                                        @php $sl++ @endphp
                                    @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card card-custom gutter-b example example-compact">
                <div class="card-header">
                    <h3 class="card-title">Policy</h3>
                    <div class="card-toolbar">
                        <div class="example-tools justify-content-center"></div>
                    </div>
                </div>
                @php $violatedPolicies = json_decode($loan->policy_violations, true) @endphp
                @if($loan->type == \App\Models\Loan::TYPE_LOAN)
                    <div class="card-body">
                        <div class="col-md-12">
                            <h4>Loan Policy</h4>
                            <hr/>
                            <ul class="policy_ul">
                                @foreach(\App\Models\Loan::LOAN_POLICIES as $loanPolicyKey => $loanPolicy)
                                    @php $iconClass = 'fa-check'; $colorClass = 'color-green'; @endphp
                                    @if((!empty($violatedPolicies)) && count($violatedPolicies) > 0)
                                        @if(array_key_exists($loanPolicyKey, $violatedPolicies))
                                            @php $iconClass = 'fa-times'; $colorClass = 'color-red'; @endphp
                                        @endif
                                    @endif
                                    <li id="loan-policy-{{ $loanPolicyKey }}" class="{{ $colorClass }}">
                                        <i class="fa {{ $iconClass . ' '. $colorClass }}"></i>
                                        <span>{{ $loanPolicy }}</span>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                @endif
                @if($loan->type == \App\Models\Loan::TYPE_ADVANCE)
                    <div class="card-footer">
                        <div class="row">
                            <div class="col-lg-12">
                                <h4>Salary Advance Policy</h4>
                                <hr/>
                                <ul class="policy_ul">
                                    @foreach(\App\Models\Loan::ADVANCE_POLICIES as $advancePolicyKey => $advancePolicy)
                                        @php $iconClass = 'fa-check'; $colorClass = 'color-green'; @endphp
                                        @if(!empty($violatedPolicies) && count($violatedPolicies) > 0)
                                            @if(array_key_exists($advancePolicyKey, $violatedPolicies))
                                                @php $iconClass = 'fa-times'; $colorClass = 'color-red'; @endphp
                                            @endif
                                        @endif
                                        <li class="{{ $colorClass }}">
                                            <i class="fa {{ $iconClass . ' '. $colorClass }}"></i>
                                            <span>{{ $advancePolicy }}</span>
                                        </li>
                                    @endforeach
                                </ul>
                            </div>
                        </div>
                    </div>
                @endif
            </div>
        </div>
        <div class="col-md-12">
            <div class="card card-custom gutter-b example example-compact">
                <div class="card-footer">
                    <div class="row">
                        <div class="col-lg-12">
                            <h4>Approval Details</h4>
                            <br>
                            <table class="table table-bordered table-hover text-center">
                                <thead>
                                <tr>
                                    <th width="15%">Activity</th>
                                    <th width="20%">Action By</th>
                                    <th width="10%">Action Date</th>
                                    <th width="10%">Status</th>
                                    <th width="30%">Remarks</th>
                                    <th width="15%">Signature</th>
                                </tr>
                                </thead>
                                <tbody>
                                <tr>
                                    <td>Departmental Approval:</td>
                                    <td>
                                        @if($loan->departmental_approval_status === 0 && $loan->status == \App\Models\Loan::STATUS_PENDING && $loan->divisional_approval_status !== 2 && in_array($loan->department_id, $userDepartmentIds))
                                            @can('Loan Departmental Approval')
                                                <a href="#" data-toggle="modal" data-target="#approvalModal-{{ \App\Models\Loan::DEPARTMENTAL_APPROVAL }}">Click to Approve</a>
                                            @endcan
                                        @else
                                            {{ optional($loan->departmentalApprovalBy)->fingerprint_no . ' - ' . optional($loan->departmentalApprovalBy)->name }}
                                        @endif

                                    </td>
                                    <td>
                                        {!! (!empty($loan->departmental_approved_date))? date('j M, Y @g:i a', strtotime($loan->departmental_approved_date)): '-' !!}
                                    </td>
                                    <td>
                                        @if($loan->departmental_approval_status === 0) Pending
                                        @elseif($loan->departmental_approval_status === 1) <span class="color-green">Approved</span>
                                        @elseif($loan->departmental_approval_status === 2) <span class="color-red">Rejected</span>
                                        @endif
                                    </td>
                                    <td>{{ $loan->departmental_remarks }}</td>
                                    <td>N/A</td>
                                </tr>
                                <tr>
                                    <td>Divisional Approval:</td>
                                    <td>
                                        @if($loan->divisional_approval_status === 0 && $loan->status == \App\Models\Loan::STATUS_PENDING && $loan->departmental_approval_status !== 2 && in_array($loan->office_division_id, $divisionIds))
                                            @can('Loan Divisional Approval')
                                                <a href="#" data-toggle="modal" data-target="#approvalModal-{{ \App\Models\Loan::DIVISIONAL_APPROVAL }}">Click to Approve</a>
                                            @endcan
                                        @else
                                            {{ optional($loan->divisionalApprovalBy)->fingerprint_no . ' - ' . optional($loan->divisionalApprovalBy)->name }}
                                        @endif
                                    </td>
                                    <td>
                                        {!! (!empty($loan->divisional_approved_date))? date('j M, Y @g:i a', strtotime($loan->divisional_approved_date)): '-' !!}
                                    </td>
                                    <td>
                                        @if($loan->divisional_approval_status === 0) Pending
                                        @elseif($loan->divisional_approval_status === 1) <span class="color-green">Approved</span>
                                        @elseif($loan->divisional_approval_status === 2) <span class="color-red">Rejected</span>
                                        @endif
                                    </td>
                                    <td>{{ $loan->divisional_remarks }}</td>
                                    <td>N/A</td>
                                </tr>
                                <tr>
                                    <td>HR Approval:</td>
                                    <td>
                                        @php $hrRejectionCondition = ($loan->divisional_approval_status !== 2 && $loan->departmental_approval_status !== 2) @endphp
                                        @if($loan->hr_approval_status === 0 && $loan->status == \App\Models\Loan::STATUS_PENDING && $hrRejectionCondition)
                                            @can('Loan HR Approval')
                                                <a href="#" data-toggle="modal" data-target="#approvalModal-{{ \App\Models\Loan::HR_APPROVAL }}">Click to Approve</a>
                                            @endcan
                                        @else
                                            {{ optional($loan->hrApprovalBy)->fingerprint_no . ' - ' . optional($loan->hrApprovalBy)->name }}
                                        @endif
                                    </td>
                                    <td>
                                        {!! (!empty($loan->hr_approved_date))? date('j M, Y @g:i a', strtotime($loan->hr_approved_date)): '-' !!}
                                    </td>
                                    <td>
                                        @if($loan->hr_approval_status === 0) Pending
                                        @elseif($loan->hr_approval_status === 1) <span class="color-green">Approved</span>
                                        @elseif($loan->hr_approval_status === 2) <span class="color-red">Rejected</span>
                                        @endif
                                    </td>
                                    <td>{{ $loan->hr_remarks }}</td>
                                    <td>N/A</td>
                                </tr>
                                <tr>
                                    <td>Accounts Approval:</td>
                                    <td>
                                        @if($loan->accounts_approval_status === 0 && $loan->status == \App\Models\Loan::STATUS_PENDING && $loan->hr_approval_status == 1)
                                            @can('Loan Accounts Approval')
                                                <a href="#" data-toggle="modal" data-target="#approvalModal-{{ \App\Models\Loan::ACCOUNTS_APPROVAL }}">Click to Approve</a>
                                            @endcan
                                        @else
                                            {{ optional($loan->accountsApprovalBy)->fingerprint_no . ' - ' . optional($loan->accountsApprovalBy)->name }}
                                        @endif
                                    </td>
                                    <td>
                                        {!! (!empty($loan->accounts_approved_date))? date('j M, Y @g:i a', strtotime($loan->accounts_approved_date)): '-' !!}
                                    </td>
                                    <td>
                                        @if($loan->accounts_approval_status === 0) Pending
                                        @elseif($loan->accounts_approval_status === 1) <span class="color-green">Approved</span>
                                        @elseif($loan->accounts_approval_status === 2) <span class="color-red">Rejected</span>
                                        @endif
                                    </td>
                                    <td>{{ $loan->accounts_remarks }}</td>
                                    <td>N/A</td>
                                </tr>
                                <tr>
                                    <td>Top Management Approval:</td>
                                    <td>
                                        @if($loan->managerial_approval_status === 0 && $loan->status == \App\Models\Loan::STATUS_PENDING && $loan->accounts_approval_status == 1 && $loan->hr_approval_status == 1)
                                            @can('Loan Managerial Approval')
                                                <a href="#" data-toggle="modal" data-target="#approvalModal-{{ \App\Models\Loan::MANAGERIAL_APPROVAL }}">Click to Approve</a>
                                            @endcan
                                        @else
                                            {{ optional($loan->managerialApprovalBy)->fingerprint_no . ' - ' . optional($loan->managerialApprovalBy)->name }}
                                        @endif
                                    </td>
                                    <td>
                                        {!! (!empty($loan->managerial_approved_date))? date('j M, Y @g:i a', strtotime($loan->managerial_approved_date)): '-' !!}
                                    </td>
                                    <td>
                                        @if($loan->managerial_approval_status === 0) Pending
                                        @elseif($loan->managerial_approval_status === 1) <span class="color-green">Approved</span>
                                        @elseif($loan->managerial_approval_status === 2) <span class="color-red">Rejected</span>
                                        @endif
                                    </td>
                                    <td>{{ $loan->managerial_remarks }}</td>
                                    <td>N/A</td>
                                </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    @php
                        $approvals = \App\Models\Loan::APPROVAL_TYPE;
                    @endphp
                    @foreach($approvals as $key => $approval)
                        @if($approval == \App\Models\Loan::DEPARTMENTAL_APPROVAL)
                            @if(!auth()->user()->can('Loan Departmental Approval')) @continue @endif
                        @elseif($approval == \App\Models\Loan::DIVISIONAL_APPROVAL)
                            @if(!auth()->user()->can('Loan Divisional Approval')) @continue @endif
                        @elseif($approval == \App\Models\Loan::HR_APPROVAL)
                            @if(!auth()->user()->can('Loan HR Approval')) @continue @endif
                        @elseif($approval == \App\Models\Loan::ACCOUNTS_APPROVAL)
                            @if(!auth()->user()->can('Loan Accounts Approval')) @continue @endif
                        @elseif($approval == \App\Models\Loan::MANAGERIAL_APPROVAL)
                            @if(!auth()->user()->can('Loan Managerial Approval')) @continue @endif
                        @endif

                    @php $route = route('loan.loanApproval', $approval) @endphp
                    <div class="modal fade" id="approvalModal-{{ $approval }}" tabindex="-1" role="dialog" aria-labelledby="exampleModalSizeXl" aria-hidden="true">
                        <div class="modal-dialog modal-dialog-scrollable modal-md" role="document">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title" id="exampleModalLabel-{{ ucfirst($approval) }}">{{ $approval == 'hr'? strtoupper($approval) :ucfirst($approval) }} Approval</h5>
                                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                        <i aria-hidden="true" class="ki ki-close"></i>
                                    </button>
                                </div>
                                <form class="form" action="{{ $route }}" method="POST">
                                    @csrf
                                    <input type="hidden" name="uuid" value="{{ $loan->uuid }}"/>
                                    <div class="modal-body">
                                        <div class="row">
                                            <div class="col-lg-12">
                                                <div class="form-group">
                                                    <label>Approval Status <span class="text-danger">*</span></label>
                                                    <div class="radio-inline" id="approval-{{ $approval }}">
                                                        <label class="radio radio-success">
                                                            <input type="radio" name="{{ $approval }}_status" value="approved" checked>
                                                            <span></span>Approve</label>
                                                        <label class="radio radio-danger">
                                                            <input type="radio" name="{{ $approval }}_status" value="rejected">
                                                            <span></span>Reject</label>
                                                    </div>
                                                </div>
                                                <div class="form-group mb-1" id="reason-{{ $approval }}">
                                                    <label for="reason">Reject Reason</label>
                                                    <textarea class="form-control" id="reason-{{ $approval }}" rows="3" name="reject_reason" placeholder="Enter Reject Reason"></textarea>
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
                    @endforeach
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="loan-payment-modal" tabindex="-1" role="dialog"
         aria-labelledby="exampleModalSizeXl" aria-hidden="true">
        <div class="modal-dialog modal-dialog-scrollable modal-md" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel">Are you sure to Pay this Installment Amount?</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <i aria-hidden="true" class="ki ki-close"></i>
                    </button>
                </div>
                <form id="loan-payment-form" action="{{ route('user-loan.custom_payment') }}" method="POST">
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-lg-12">
                                @csrf
                                <div class="form-group mb-1">
                                    <label for="reason">Payment Reason</label>
                                    <textarea class="form-control" rows="3" name="remark"
                                              placeholder="Enter Payment Reason" required></textarea>
                                </div>
                                <input type="hidden" name="uuid" id="uuid">
                                <div class="modal-footer">
                                    <button type="submit" class="btn btn-primary mr-2">Pay Loan</button>
                                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@section('footer-js')
    <script type="text/javascript" src="{{ asset('assets/js/pages/select2.min.js') }}"></script>
    <script src="{{ asset('assets/js/bootstrap-datepicker.min.js') }}"></script>
    <script type="text/javascript">
        $("select").select2({
            theme: "classic",
        });

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

        function paymentEmployeeLoan(elm) {
            let loanUuid = $(elm).data('uuid');

            if (loanUuid == '') {
                swal.fire({
                    title: 'Something is Wrong!!',
                    text: "Some identity can't be matched!",
                    icon: 'warning',
                    buttonsStyling: false,
                    showCancelButton: false,
                    allowOutsideClick: false,
                    customClass: {
                        confirmButton: "btn btn-success"
                    }
                });
                setInterval(function () {
                    location.reload();
                }, 1000);
            }

            $('#uuid').val(loanUuid);
            $("#loan-payment-modal").modal('show');
        }
    </script>
@endsection
