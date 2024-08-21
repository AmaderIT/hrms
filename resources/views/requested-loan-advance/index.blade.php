@extends('layouts.app')

@section('top-css')
    <style>
        .view_icon {
            color: #fff;
            background: green;
            padding: 6px 6px;
            border-radius: 4px;
            font-size: 12px;
            vertical-align: middle;
            margin-bottom: 3px;
        }
    </style>
@endsection

@section('content')
    <div class="card mb-2">
        <div class="card-body">
            <form class="d-block" action="" method="get">
                <div class="row m-auto">
                    <div class="row col-12 justify-content-start mb-2">
                        @include('filter.loan-filter')
                        <div class="col-2">
                            <button class="btn btn-sm btn-default px-6 mt-7 btn-primary" type="submit" style="color: white">Search</button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div class="card card-custom">
        <div class="card-header flex-wrap border-0 pt-6 pb-0">
            <div class="card-title">
                <h3 class="card-label">Loan / Advance Applications</h3>
            </div>
            <div class="card-toolbar">
                @can("Add Another Employee Loan")
                    <a href="{{ route('requested-loan-advance.create') }}" class="btn btn-primary font-weight-bolder">
                    <span class="svg-icon svg-icon-default svg-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" width="24px"
                             height="24px" viewBox="0 0 24 24" version="1.1">
                            <g stroke="none" stroke-width="1" fill="none" fill-rule="evenodd">
                                <rect x="0" y="0" width="24" height="24"/>
                                <circle fill="#000000" opacity="0.3" cx="12" cy="12" r="10"/>
                                <path
                                    d="M11,11 L11,7 C11,6.44771525 11.4477153,6 12,6 C12.5522847,6 13,6.44771525 13,7 L13,11 L17,11 C17.5522847,11 18,11.4477153 18,12 C18,12.5522847 17.5522847,13 17,13 L13,13 L13,17 C13,17.5522847 12.5522847,18 12,18 C11.4477153,18 11,17.5522847 11,17 L11,13 L7,13 C6.44771525,13 6,12.5522847 6,12 C6,11.4477153 6.44771525,11 7,11 L11,11 Z"
                                    fill="#000000"/>
                            </g>
                        </svg>
                    </span>Add New Loan / Advance
                    </a>
                @endcan
            </div>
        </div>
        <div class="card-body">
            <table class="table" id="requestedLoanAdvanceTable">
                <thead class="custom-thead">
                <tr>
                    <th scope="col">Office ID</th>
                    <th scope="col">Employee Name</th>
                    <th scope="col">Office Division</th>
                    <th scope="col">Department</th>
                    <th scope="col">Designation</th>
                    <th scope="col">Type</th>
                    <th scope="col">Amount</th>
                    <th scope="col">Installments</th>
                    <th scope="col">Application Date</th>
                    <th scope="col">Amount Paid By</th>
                    <th scope="col">Status</th>
                    <th scope="col"></th>
                    <th width="10%" scope="col">Actions</th>
                </tr>
                </thead>
                <tbody>

                @foreach($items as $item)
                    <tr>
                        <td>{{ $item->user->fingerprint_no }}</td>
                        <td>{{ $item->user->name }}</td>
                        <td>{{ $item->user->currentPromotion->officeDivision->name }}</td>
                        <td>{{ $item->user->currentPromotion->department->name }}</td>
                        <td>{{ $item->user->currentPromotion->designation->title }}</td>
                        <td>{{ $item->type }}</td>
                        <td>{{ $item->loan_amount }}</td>
                        <td>{{ $item->loan_tenure }}</td>
                        <td>{{ date("d M, Y",strtotime($item->created_at)) }}</td>
                        <td>
                            @if(!empty($item->paidBy))
                                {{ $item->paidBy->fingerprint_no . ' - ' . $item->paidBy->name }}
                                <small>{{$item->loan_paid_date ? "@".date("d M, Y",strtotime($item->loan_paid_date)) : ""}}</small>
                            @endif
                        </td>
                        <td>{{ $item->status }}</td>
                        <td>
                            @if(
                                ($item->divisional_approval_status === 1 || $item->departmental_approval_status === 1) &&
                                ($item->divisional_approval_status !== 2 && $item->departmental_approval_status !== 2) &&
                                $item->hr_approval_status === 1 && $item->accounts_approval_status === 1 && $item->managerial_approval_status === 1 && $item->status == \App\Models\Loan::STATUS_PENDING &&
                                auth()->user()->can("Loan Amount Payment")
                            )
                                <a href="#" data-uuid="{{ $item->uuid }}" onclick="paymentEmployeeLoan(this)"
                                   class="btn btn-sm btn-primary">Pay</a>
                            @endif
                        </td>
                        <td>
                            @if($item->hr_approval_status === 1 && $item->accounts_approval_status === 1 && $item->managerial_approval_status === 1 && $item->status == \App\Models\Loan::STATUS_ACTIVE && auth()->user()->can("Loan Hold"))
                                <a href="#" title="Hold this Loan" data-uuid="{{ $item->uuid }}"
                                   onclick="holdEmployeeLoan(this)"
                                   class=""><i class="fa fa-stop view_icon" style="background: #ffa800"></i></a>
                            @endif
                            @if($item->hr_approval_status === 1 && $item->accounts_approval_status === 1 && $item->managerial_approval_status === 1 && $item->status == \App\Models\Loan::STATUS_HOLD && auth()->user()->can("Loan Hold"))
                                <a href="#" title="Resume this Loan" data-uuid="{{ $item->uuid }}"
                                   onclick="holdEmployeeLoan(this, true)"
                                   class=""><i class="fa fa-play view_icon" style="background: #5ee53c"></i></a>
                            @endif
                            @can("View Loan")
                                <a href="{{ route('loan.show', ['loan' => $item->uuid, 'requested' => 1]) }}" title="{{ auth()->user()->can("Pay Installment Amount") && $item->status == \App\Models\Loan::STATUS_ACTIVE? 'Approve / Custom Payment': 'Approve' }}"><i
                                        class="fa fa-check-square view_icon"></i></a>
                            @endcan
                            @if(auth()->user()->can("Edit Requested Loan / Advance Application") && ($item->status == \App\Models\Loan::STATUS_ACTIVE || $item->status == \App\Models\Loan::STATUS_PENDING))
                                <a href="{{ route('requested-loan-advance.edit', ['loan' => $item->uuid]) }}" title="Edit"><i class="fa fa-edit view_icon"></i></a>
                            @endcan
                            {{--@if(is_null($item->authorized_by) OR is_null($item->approved_by))
                                @can("Delete Requested Loan / Advance Application")
                                    || <a href="#"
                                          onclick="deleteAlert('{{ route('requested-loan-advance.delete', ['loan' => $item->uuid]) }}')"><i
                                            class="fa fa-trash" style="color: red"></i></a>
                                @endcan
                            @endif--}}
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
        @if($items->hasPages())
            <div class="card-footer">
                <div class="d-flex">
                    <div class="ml-auto">
                        {{ $items->links() }}
                    </div>
                </div>
            </div>
        @endif
    </div>

    <div class="modal fade" id="loan-payment-modal" tabindex="-1" role="dialog"
         aria-labelledby="exampleModalSizeXl" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-sm" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel">Are you sure to Pay this Loan?</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <i aria-hidden="true" class="ki ki-close"></i>
                    </button>
                </div>
                <form id="loan-payment-form" action="{{ route('loan.loanPayment') }}" method="POST">
                    @csrf
                    <input type="hidden" name="uuid" id="uuid">
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-primary mr-2">Pay Loan</button>
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="modal fade" id="loan-hold-modal" tabindex="-1" role="dialog"
         aria-labelledby="exampleModalSizeXl" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-md" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title hold-modal-title" id="exampleModalLabel">Are you sure to hold this Loan?</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <i aria-hidden="true" class="ki ki-close"></i>
                    </button>
                </div>
                <div class="modal-body">
                    <form id="loan-payment-form" action="{{ route('loan.loanHold') }}" method="POST">
                        @csrf
                        <input type="hidden" name="uuid" id="loan_uuid">
                        <input type="hidden" id="is_resume" name="is_resume">
                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group mb-1">
                                    <label for="reason" id="reason_label">Hold Reason</label>
                                    <textarea class="form-control" id="hold_remarks" rows="3" name="hold_remarks" required
                                              placeholder="Enter Reason"></textarea>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="submit" class="btn btn-primary mr-2">Submit</button>
                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('footer-js')
    <script>

        $(document).ready(function () {
            $('#requestedLoanAdvanceTable').DataTable({
                "order": [],
                "ordering": true,
                "paging": false,
                "bInfo": false,
                "bPaginate": false,
            });
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

        function holdEmployeeLoan(elm, isResume = false) {
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

            $('#loan_uuid').val(loanUuid);

            if(isResume){
                $('#is_resume').val('Y');
                $('.hold-modal-title').html('Are you sure to resume this Loan?');
                $('#reason_label').html('Resume Reason');
            }else{
                $('#is_resume').val('N');
                $('.hold-modal-title').html('Are you sure to hold this Loan?');
                $('#reason_label').html('Hole Reason');
            }

            $("#loan-hold-modal").modal('show');
        }
    </script>

    @stack('custom-scripts')
@endsection

