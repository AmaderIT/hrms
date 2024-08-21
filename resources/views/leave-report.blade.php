@extends('layouts.app')

@section('content')
    <div class="row">
        <div class="col-xl-12">
            <div class="card card-custom card-stretch gutter-b">
                <div class="card-header border-0 pt-7">
                    <h3 class="card-title align-items-start flex-column">
                        <span class="card-label font-weight-bold font-size-h4 text-dark-75">Applied Leaves</span>
                        <span class="card-label font-weight-bold font-size-h4 text-dark-75">{{ $report["employee"]->name }}</span>
                        <span class="text-muted mt-3 font-weight-bold font-size-sm">
                            <b class="text-dark-50">Division:</b> {{ $report["employee"]->currentPromotion->officeDivision->name }}
                        </span>
                        <span class="text-muted mt-1 font-weight-bold font-size-sm">
                            <b class="text-dark-50">Department:</b> {{ $report["employee"]->currentPromotion->department->name }}</span>
                    </h3>
                </div>
                <div class="card-body pt-0 pb-4">
                    <div class="card-body">
                        <table class="table table-responsive-lg" id="employeeLeaveToSupervisorTable">
                            <thead class="custom-thead">
                            <tr>
                                <th scope="col">Leave Type</th>
                                <th scope="col">Entitled</th>
                                <th scope="col">Utilized</th>
                                <th scope="col">Actions</th>
                            </tr>
                            </thead>
                            <tbody>
                                @if(isset($report["data"]))
                                    @foreach($report["data"] as $leaveAllocationDetails)
                                        <tr>
                                            <td>{{ $leaveAllocationDetails["name"] }}</td>
                                            <td>{{ $leaveAllocationDetails["entitled"] }}</td>
                                            <td>{{ $leaveAllocationDetails["utilized"] }}</td>
                                            <td>
                                                @if($leaveAllocationDetails["utilized"] > 0)
                                                    <button type="button" class="btn btn-outline-primary" data-toggle="modal" data-target="#exampleModal-{{ $leaveAllocationDetails['leave_type_id'] }}">Details</button>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                @endif
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{--  My Leave Status (Late Deduction for Salary)  --}}
    @if(count($report["leaveReportsForLate"]) > 0)
        <div class="row">
            <div class="col-xl-12">
                <div class="card card-custom card-stretch gutter-b">
                    <div class="card-header border-0 pt-7">
                        <h3 class="card-title align-items-start flex-column">
                            <span class="card-label font-weight-bold font-size-h4 text-dark-75">Leave Deduction for Late</span>
                        </h3>
                    </div>
                    <div class="card-body pt-0 pb-4">
                        <div class="card-body">
                            <table class="table table-responsive-lg" id="employeeLeaveTable">
                                <thead class="custom-thead">
                                <tr>
                                    <th scope="col">Casual Leave</th>
                                    <th scope="col">Earn Leave</th>
                                    <th scope="col">Month & Year</th>
                                </tr>
                                </thead>
                                <tbody>
                                @foreach($report["leaveReportsForLate"] as $value)
                                    <tr>
                                        <td>{{ $value["casual_leave"] }}</td>
                                        <td>{{ $value["earn_leave"] }}</td>
                                        <td>{{ getMonthNameFromMonthNumber($value["month"]) . ", " . $value["year"] }}</td>
                                    </tr>
                                @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif

    {{--  Leave Balance  --}}
    <div class="row">
        <div class="col-xl-12">
            <div class="card card-custom card-stretch gutter-b">
                <div class="card-header border-0 pt-7">
                    <h3 class="card-title align-items-start flex-column">
                        <span class="card-label font-weight-bold font-size-h4 text-dark-75">Leave Balance</span>
                    </h3>
                </div>
                <div class="card-body pt-0 pb-4">
                    <div class="card-body">
                        <table class="table table-responsive-lg" id="employeeLeaveTable">
                            <thead class="custom-thead">
                            <tr>
                                <th scope="col">Casual Leave</th>
                                <th scope="col">Earn Leave</th>
                            </tr>
                            </thead>
                            <tbody>
                            <tr>
                                <td>{{ $report["leaveReportBalance"]["casual_leave"] }}</td>
                                <td>{{ $report["leaveReportBalance"]["earn_leave"] }}</td>
                            </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- MODAL:: Leave Report to Supervisor --}}
    @foreach($report["leaveReportDetails"] as $key => $item)
        <div class="modal fade" id="exampleModal-{{ $key }}" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="exampleModalLabel-{{ $key }}">{{ $item->first()->leaveType->name }}</h5>
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
                            @foreach($item as $index => $leaveRequest)
                                <tr>
                                    <td>{{ $leaveRequest->from_date->format('M d, Y') }}</td>
                                    <td>{{ $leaveRequest->from_date->format('M d, Y') != $leaveRequest->to_date->format('M d, Y') ? $leaveRequest->to_date->format('M d, Y') : "-" }}</td>
                                    <td>{{ $leaveRequest->number_of_days }}</td>
                                    <td>{{ $leaveRequest->purpose }}</td>
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
    @endforeach
@endsection

@section('footer-js')
    <script type="text/javascript" src="{{asset('assets/js/widget.js')}}"></script>
    <script type="text/javascript">
        $(document).ready(function(e) {
            $("#employeeLeaveToSupervisorTable").DataTable({
                "ordering": false
            });
        });
    </script>
@endsection
