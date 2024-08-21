@extends('layouts.app')

@section('content')
    {{--  My Leave Status (Consumed by Leave) --}}
    <div class="row">
        <div class="col-xl-12">
            <div class="card card-custom card-stretch gutter-b">
                <div class="card-header border-0 pt-7">
                    <h3 class="card-title align-items-start flex-column">
                        <span class="card-label font-weight-bold font-size-h4 text-dark-75">Applied Leaves</span>
                    </h3>
                </div>
                <div class="card-body pt-0 pb-4">
                    <div class="card-body">
                        <table class="table table-responsive-lg" id="employeeLeaveTable">
                            <thead class="custom-thead">
                            <tr>
                                <th scope="col">Leave Type</th>
                                <th scope="col">Entitled</th>
                                <th scope="col">Utilized</th>
                                <th scope="col">Late Deductions</th>
                                <th scope="col">Actions</th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach($data["leaveReports"] as $value)
                                <tr>
                                    <td>{{ $value["name"] }}</td>
                                    <td>{{ $value["entitled"] }}</td>

                                    <td>{{ $value["utilized"] }}</td>
                                    <td>
                                        @if(!empty($data["lateLeaveDeductions"]['summery'][$value['leave_type_id']]))
                                            <button title="Details" type="button" style="    border: none;
    background: none;
    font-weight: bold;
    color: #3699ff;" data-toggle="modal" data-target="#late_leave_deduction">
                                                {{ $data["lateLeaveDeductions"]['summery'][$value['leave_type_id']] }}
                                            </button>
                                        @endif
                                    </td>
                                    <td>
                                        @if($value["utilized"] > 0)
                                            <button type="button" class="btn btn-outline-primary" data-toggle="modal" data-target="#exampleModal-{{ $value['leave_type_id'] }}">Details</button>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{--  My Leave Status (Late Deduction for Salary)  --}}
    @if(count($data["leaveReportsForLate"]) > 0)
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
                            @foreach($data["leaveReportsForLate"] as $value)
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
                                    <td>{{ $data["leaveReportBalance"]["casual_leave"] }}</td>
                                    <td>{{ $data["leaveReportBalance"]["earn_leave"] }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Leave Report to Supervisor --}}
    @if((auth()->user()->isSupervisor() == true AND auth()->user()->can("View Leave Status")))
        <div class="row">
            <div class="col-xl-12">
                <div class="card card-custom card-stretch gutter-b">
                    <div class="card-header border-0 pt-7">
                        <h3 class="card-title align-items-start flex-column">
                            <span class="card-label font-weight-bold font-size-h4 text-dark-75">Employee Leave Status</span>
                        </h3>
                    </div>
                    <div class="card-body pt-0 pb-4">
                        <div class="card-body">
                            <table class="table table-responsive-lg" id="employeeLeaveToSupervisorTable">
                                <thead class="custom-thead">
                                <tr>
                                    <th scope="col">Employee</th>
                                    <th scope="col">Office Division</th>
                                    <th scope="col">Department</th>
                                    <th scope="col">Total Entitled</th>
                                    <th scope="col">Total Utilized</th>
                                    <th scope="col">Actions</th>
                                </tr>
                                </thead>
                                <tbody>
                                @if($data["leaveReportToSupervisor"])
                                    @foreach($data["leaveReportToSupervisor"] as $leaveReportToSupervisor)
                                        <tr>
                                            <td>{{ $leaveReportToSupervisor["employee"]->name }}</td>
                                            <td>{{ $leaveReportToSupervisor["employee"]->currentPromotion->officeDivision->name }}</td>
                                            <td>{{ $leaveReportToSupervisor["employee"]->currentPromotion->department->name }}</td>
                                            <td>{{ $leaveReportToSupervisor["totalEntitled"] }}</td>
                                            <td>{{ $leaveReportToSupervisor["totalUtilized"] }}</td>
                                            <td>
                                                @if($leaveReportToSupervisor["totalUtilized"] > 0)
                                                    <a href="{{ route('leave-status.leaveToSupervisor', ['user' => $leaveReportToSupervisor["employee"]->uuid]) }}" class="btn btn-outline-primary">Details</a>
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
    @endif

    <!-- MODAL:: Leave Report to Employee -->
    @foreach($data["leaveReportDetails"] as $key => $item)
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

    @php $lateLeaveDeductions = $data['lateLeaveDeductions']['details'] @endphp

    @if(count($lateLeaveDeductions) > 0)
    <div class="modal fade" id="late_leave_deduction" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Leave deduction summery for late</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <i aria-hidden="true" class="ki ki-close"></i>
                    </button>
                </div>
                <div class="modal-body">
                    <table class="table">
                        <thead>
                        <tr>
                            <th scope="col">Month</th>
                            <th scope="col">Leave Type</th>
                            <th scope="col">Total Days</th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach($lateLeaveDeductions as $leaveDeduction)
                            @php $leaveDeductions = json_decode($leaveDeduction->late_leave_deduction, true) @endphp

                            @foreach($leaveDeductions as $typeWiseDeduction)
                                <tr>
                                    <td>{{ date('F', strtotime("$leaveDeduction->year-$leaveDeduction->month-01")) }}</td>
                                    <td>{{ !empty($data['leaveTypes'][$typeWiseDeduction['leave_type_id']])? $data['leaveTypes'][$typeWiseDeduction['leave_type_id']]: '' }}</td>
                                    <td>{{ !empty($typeWiseDeduction['to_be_deducted'])? $typeWiseDeduction['to_be_deducted']: '' }}</td>
                                </tr>
                            @endforeach
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
@endsection

@section('footer-js')
    <script>
        $(document).ready( function () {
            $('#employeeLeaveTable').DataTable({
                "order": []
            });
            $('#employeeLeaveToSupervisorTable').DataTable({
                "order": []
            });
        } );
    </script>
@endsection
