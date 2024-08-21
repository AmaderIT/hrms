@extends('layouts.app')

@section('content')
    <div class="row">
        <div class="col-xl-12">
            <div class="card card-custom card-stretch gutter-b">
                <!--begin::Header-->
                <div class="card-header border-0 pt-7">
                    <h3 class="card-title align-items-start flex-column">
                        <span class="card-label font-weight-bold font-size-h4 text-dark-75">{{ $report["employee"]->name }}</span>
                        <span class="text-muted mt-3 font-weight-bold font-size-sm">
                            <b class="text-dark-50">Division:</b> {{ $report["employee"]->currentPromotion->officeDivision->name }}
                        </span>
                        <span class="text-muted mt-1 font-weight-bold font-size-sm">
                            <b class="text-dark-50">Department:</b> {{ $report["employee"]->currentPromotion->department->name }}</span>
                    </h3>
                </div>
                <!--end::Header-->
                <!--begin::Body-->
                <div class="card-body pt-0 pb-4">
                    <div class="card-body">
                        <table class="table table-responsive-lg" id="employeeLeaveToSupervisorTable">
                            <thead class="custom-thead">
                            <tr>
                                <th scope="col">Leave Type</th>
                                <th scope="col">Entitled</th>
                                <th scope="col">Utilized</th>
                                <th scope="col">Balance</th>
                                <th scope="col">Actions</th>
                            </tr>
                            </thead>
                            <tbody>
                            @if(isset($report["data"]->leaveAllocationDetails))
                                @foreach($report["data"]->leaveAllocationDetails as $leaveAllocationDetails)
                                    <tr>
                                        <td>{{ $leaveAllocationDetails->leaveType->name }}</td>
                                        <td>{{ $leaveAllocationDetails->total_days }}</td>
                                        <td>{{ $leaveAllocationDetails->leave_requests_count ?? 0 }}</td>
                                        <td>{{ $leaveAllocationDetails->total_days - $leaveAllocationDetails->leave_requests_count }}</td>
                                        <td>
                                            @if(!is_null($leaveAllocationDetails->leave_requests_count))
                                                <button type="button" class="btn btn-outline-primary" data-toggle="modal" data-target="#exampleModal-{{ $report["employee"]->id }}-{{ $leaveAllocationDetails->leave_type_id }}">Details</button>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            @endif
                            </tbody>
                        </table>
                    </div>
                </div>
                <!--end::Body-->
            </div>
        </div>
    </div>

    {{-- MODAL:: Leave Report to Supervisor --}}
    @if(isset($report["data"]->leaveAllocationDetails))
        @foreach($report["data"]->leaveAllocationDetails as $leaveAllocationDetails)
            @if($leaveAllocationDetails->leave_type_id)
                <div class="modal fade" id="exampleModal-{{ $report["employee"]->id }}-{{ $leaveAllocationDetails->leave_type_id }}" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
                    <div class="modal-dialog" role="document">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title" id="exampleModalLabel-{{ $leaveAllocationDetails->leave_type_id }}">{{ $leaveAllocationDetails->leaveType->name }}</h5>
                                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                    <i aria-hidden="true" class="ki ki-close"></i>
                                </button>
                            </div>
                            <div class="modal-body">
                                <table class="table">
                                    <thead>
                                    <tr>
                                        <th scope="col">From Date</th>
                                        <th scope="col">To Date</th>
                                        <th scope="col">Total Days</th>
                                        <th scope="col">Remarks</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    @foreach($leaveAllocationDetails->leaveRequests as $leaveRequests)
                                        <tr>
                                            <td>{{ $leaveRequests->from_date->format('M d, Y') }}</td>
                                            <td>{{ $leaveRequests->from_date->format('M d, Y') != $leaveRequests->to_date->format('M d, Y') ? $leaveRequests->to_date->format('M d, Y') : "-" }}</td>
                                            <td>{{ $leaveRequests->number_of_days }}</td>
                                            <td>{{ $leaveRequests->purpose }}</td>
                                            </td>
                                        </tr>
                                    @endforeach
                                    </tbody>
                                </table>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-light-primary font-weight-bold" data-dismiss="modal">Close</button>
                            </div>
                        </div>
                    </div>
                </div>
            @endif
        @endforeach
    @endif
@endsection

@section('footer-js')
    <script type="text/javascript" src="{{asset('assets/js/widget.js')}}"></script>
    <script type="text/javascript">
        $(document).ready(function(e) {
            $("#employeeLeaveToSupervisorTable").DataTable();
        });
    </script>
@endsection
