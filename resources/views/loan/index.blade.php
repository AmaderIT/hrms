@extends('layouts.app')

@section('top-css')
    <style>
        .view_icon {
            color: #fff;
            background: #3699ff;
            padding: 5px 5px;
            border-radius: 4px;
            font-size: 14px;
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

    <!--begin::Card-->
    <div class="card card-custom">
        <div class="card-header flex-wrap border-0 pt-6 pb-0">
            <div class="card-title">
                <h3 class="card-label">Loan / Advance Applications</h3>
            </div>
            <div class="card-toolbar">
                @can("Apply for Loans")
                    <a href="{{ route('loan.create') }}" class="btn btn-primary font-weight-bolder">
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
                    </span>Apply for Loan / Advance
                    </a>
                @endcan
            </div>
        </div>
        <div class="card-body">
            <table class="table" id="loanTable">
                <thead class="custom-thead">
                <tr>
                    <th scope="col">Office ID</th>
                    <th scope="col">Employee Name</th>
                    <th scope="col">Office Division</th>
                    <th scope="col">Department</th>
                    <th scope="col">Designation</th>
                    <th scope="col">Type</th>
                    <th scope="col">Amount</th>
                    <th scope="col">Instalments</th>
                    <th scope="col">Application Date</th>
                    <th scope="col">Amount Paid By</th>
                    <th scope="col">Status</th>
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
                            @can("View Loan")
                                <a href="{{ route('loan.show', ['loan' => $item->uuid]) }}" title="Show"><i
                                        class="fa fa-info-circle view_icon"></i></a>
                            @endcan
                            @if($item->status == \App\Models\Loan::STATUS_PENDING && $item->user_id == auth()->id() && $item->departmental_approval_status == 0 &&  $item->divisional_approval_status == 0 &&  $item->hr_approval_status == 0)
                                @can("Edit Loans")
                                    <a href="{{ route('loan.edit', ['loan' => $item->uuid]) }}"><i class="fa fa-edit view_icon" title="Edit"></i></a>
                                @endcan
                                {{--@can("Delete Loans")
                                    || <a href="#"
                                          onclick="deleteAlert('{{ route('loan.delete', ['loan' => $item->uuid]) }}')"><i
                                            class="fa fa-trash" title="Delete" style="color: red"></i></a>
                                @endcan--}}
                            @endif
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
@endsection

@section('footer-js')
    <script>
        $(document).ready(function () {
            $('#loanTable').DataTable({
                "order": [],
                "ordering": true,
                "paging": false,
                "bInfo": false,
                "bPaginate": false,
            });
        });
    </script>

    @stack('custom-scripts')
@endsection
