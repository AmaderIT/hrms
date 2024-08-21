@extends('layouts.app')

@section('content')
    @php
        $priority = ["Today", "Within 3 days", "Within 7 days", "Within 10 days"];
        $status = ["New", "In Progress", "Delieverd", "Rejected", "Received"];
    @endphp

    <!--begin::Card-->
    <div class="card card-custom">
        <!--begin::Header-->
        <div class="card-header flex-wrap border-0 pt-6 pb-0">
            <div class="card-title">
                <h3 class="card-label">Requisition Listing</h3>
            </div>
            <div class="card-toolbar">
                <!--begin::Button-->
                @can("Create My Requisition")
                <a href="{{ route('apply-for-requisition.create') }}" class="btn btn-primary font-weight-bolder">
                    <span class="svg-icon svg-icon-default svg-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" width="24px" height="24px" viewBox="0 0 24 24" version="1.1">
                            <g stroke="none" stroke-width="1" fill="none" fill-rule="evenodd">
                                <rect x="0" y="0" width="24" height="24"/>
                                <circle fill="#000000" opacity="0.3" cx="12" cy="12" r="10"/>
                                <path d="M11,11 L11,7 C11,6.44771525 11.4477153,6 12,6 C12.5522847,6 13,6.44771525 13,7 L13,11 L17,11 C17.5522847,11 18,11.4477153 18,12 C18,12.5522847 17.5522847,13 17,13 L13,13 L13,17 C13,17.5522847 12.5522847,18 12,18 C11.4477153,18 11,17.5522847 11,17 L11,13 L7,13 C6.44771525,13 6,12.5522847 6,12 C6,11.4477153 6.44771525,11 7,11 L11,11 Z" fill="#000000"/>
                            </g>
                        </svg>
                    </span>Apply for Requisition
                </a>
                @endcan
                <!--end::Button-->
            </div>
        </div>
        <!--end::Header-->
        <!--begin::Body-->
        <div class="card-body">
            <table class="table" id="bankTable">
                <thead class="custom-thead">
                <tr>
                    <th scope="col">Order No.</th>
                    <th scope="col">Name</th>
                    <th scope="col">Department</th>
                    <th scope="col">Date</th>
                    <th scope="col">Status</th>
                    <th scope="col">Actions</th>
                </tr>
                </thead>
                <tbody>
                @foreach($items as $item)
                    <tr>
                        <td><a href="#" data-toggle="modal" data-target="#requisitionModal-{{ $item->id }}">{{ $item->id }}</a></td>
                        <td>{{ $item->appliedBy->name }}</td>
                        <td>{{ $item->department->name }}</td>
                        <td>{{ $item->applied_date }}</td>
                        <td>{{ $status[$item->status] }}</td>
                        <td>
                            @if($item->status === \App\Models\Requisition::STATUS_DELIVERED)
                            <a href="#" data-toggle="modal" data-target="#requisitionReceiveModal-{{ $item->id }}">
                                <i class="fa fa-file" style="color: green"></i>
                            </a> ||
                            @endif
                            @if($item->status == \App\Models\Requisition::STATUS_NEW AND auth()->user()->can("Edit My Requisition"))
                            <a href="{{ route('apply-for-requisition.edit', ['requisition' => $item->id]) }}"><i class="fa fa-edit" style="color: green"></i></a>
                            @endif
                            @can("Delete My Requisition")
                            || <a href="#" onclick="deleteAlert('{{ route('apply-for-requisition.delete', ['requisition' => $item->id]) }}')"><i class="fa fa-trash" style="color: red"></i></a>
                            @endcan
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
        <!--end::Body-->
        <!--begin::Footer-->
        @if($items->hasPages())
            <div class="card-footer">
                <div class="d-flex">
                    <div class="ml-auto">
                        {{ $items->links() }}
                    </div>
                </div>
            </div>
        @endif
        <!--end::Footer-->
    </div>
    <!--end::Card-->

    {{-- Requisition Modal --}}
    @foreach($items as $requisition)
        <div class="modal fade" id="requisitionModal-{{ $requisition->id }}" tabindex="-1" role="dialog" aria-labelledby="exampleModalSizeXl" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="exampleModalLabel">
                            Departmental Requisition Form for Office Supplies
                        </h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <i aria-hidden="true" class="ki ki-close"></i>
                        </button>
                    </div>
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-lg-12">
                                <table class="table table-borderless text-left">
                                    <tbody>
                                    <tr>
                                        <td width="50%"><strong>Order No: </strong>{{ $requisition->id }}</td>
                                        <td width="50%"><strong>Date: </strong> {{ $requisition->applied_date }}</td>
                                    </tr>
                                    <tr>
                                        <td><strong>Department: </strong>{{ $requisition->department->name }}</td>
                                        <td><strong>Status: </strong>{{ $status[$requisition->status] }}</td>
                                    </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <div class="row mt-0">
                            <div class="col-lg-12">
                                <table class="table table-borderless text-left">
                                    <tbody>
                                    <tr>
                                        <td width="55%"><strong>Priority: </strong>{{ $priority[$requisition->priority] }}</td>
                                        <td width="45%"></td>
                                    </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <div class="row mt-0">
                            <div class="col-lg-12">
                                <h4 style="text-align: center; vertical-align: middle;"><strong>Details of Requisition</strong></h4>
                                <table class="table table-bordered text-center">
                                    <tbody>
                                    <tr>
                                        <td width="10%">SL No.</td>
                                        <td width="40%">Item</td>
                                        <td width="20%">Requested Quantity (Pcs)</td>
                                        <td width="30%">Received Quantity (Pcs)</td>
                                    </tr>
                                    @foreach($requisition->details as $key => $details)
                                        <tr>
                                            <td width="5%">{{ $key + 1 }}</td>
                                            <td width="40%">{{ $details->item->name }}</td>
                                            <td width="20%">{{ $details->quantity }}</td>
                                            <td width="30%">{{ $details->received_quantity ?? 0 }}</td>
                                        </tr>
                                    @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <div class="row mt-0">
                            <div class="col-lg-12">
                                <table class="table table-borderless text-left">
                                    <tbody>
                                    <tr>
                                        <td width="100%"><strong>Remarks: </strong>{{ $requisition->remarks }}</td>
                                    </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endforeach

    {{-- Requisition Receive Modal --}}
    @foreach($items as $requisition)
        <div class="modal fade" id="requisitionReceiveModal-{{ $requisition->id }}" tabindex="-1" role="dialog" aria-labelledby="exampleModalSizeXl" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="exampleModalLabel">
                            Departmental Requisition Receive Form for Office Supplies
                        </h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <i aria-hidden="true" class="ki ki-close"></i>
                        </button>
                    </div>
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-lg-12">
                                <table class="table table-borderless text-left">
                                    <tbody>
                                    <tr>
                                        <td width="50%"><strong>Order No: </strong>{{ $requisition->id }}</td>
                                        <td width="50%"><strong>Date: </strong> {{ $requisition->applied_date }}</td>
                                    </tr>
                                    <tr>
                                        <td><strong>Department: </strong>{{ $requisition->department->name }}</td>
                                        <td><strong>Status: </strong>{{ $status[$requisition->status] }}</td>
                                    </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <div class="row mt-0">
                            <div class="col-lg-12">
                                <table class="table table-borderless text-left">
                                    <tbody>
                                    <tr>
                                        <td width="55%"><strong>Priority: </strong>{{ $priority[$requisition->priority] }}</td>
                                        <td width="45%"></td>
                                    </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <div class="row mt-0">
                            <div class="col-lg-12">
                                <h4 style="text-align: center; vertical-align: middle;"><strong>Details of Requisition</strong></h4>
                                <form method="POST" action="{{ route('apply-for-requisition.receive') }}">
                                    @csrf
                                    <table class="table table-bordered text-center">
                                        <tbody>
                                        <tr>
                                            <td width="10%">SL No.</td>
                                            <td width="40%">Item</td>
                                            <td width="20%">Requested Quantity (Pcs)</td>
                                            <td width="30%">Received Quantity (Pcs)</td>
                                        </tr>
                                        @foreach($requisition->details as $key => $details)
                                            <tr>
                                                <td width="5%">{{ $key + 1 }}</td>
                                                <td width="40%">{{ $details->item->name }}</td>
                                                <td width="20%">{{ $details->quantity }}</td>
                                                <td width="30%">
                                                    <input type="hidden" name="requisition_id" value="{{ $requisition->id }}"/>
                                                    <input type="hidden" name="id[]" value="{{ $details->id }}">
                                                    <input class="form-control" type="hidden" name="received_quantity[]"
                                                           value="{{ $details->received_quantity }}" placeholder="Received quantity" readonly/>
                                                    {{ $details->received_quantity }}
                                                </td>
                                            </tr>
                                        @endforeach
                                        </tbody>
                                    </table>
                                    <button type="submit" class="btn btn-primary mb-3 float-right">Receive</button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endforeach
@endsection

@section('footer-js')
    <script>
        $(document).ready( function () {
            $('#bankTable').DataTable({
                "order": [],
                "ordering": true,
                "paging": false,
                "bInfo": false,
                "bPaginate": false,
            });
        } );
    </script>
@endsection
