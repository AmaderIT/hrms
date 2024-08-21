@extends('layouts.app')

@section('content')
    @can("Show Admin Dashboard")
        {{-- At a Glance --}}
        <div class="row">
            <div class="col-xl-4 dashboard-card">
                <!--begin::Stats Widget 1-->
                <div class="card card-custom card-stretch gutter-b">
                    <!--begin::Header-->
                    <div class="card-header border-0 pt-6">
                        <h3 class="card-title">
                            <span class="card-label text-dark-75" style="font-size: 15px">TOTAL EMPLOYEES</span>
                        </h3>
                    </div>
                    <!--end::Header-->
                    <!--begin::Body-->
                    <div class="card-body d-flex align-items-center justify-content-between pt-0 flex-wrap">
                        <div class="progress-vertical w-350px ml-25">
                            <div class="display2 py-0 pl-12 text-primary">
                                <a href="{{ route('employee.index') }}">{{ $data["reportToAdmin"]["totalEmployees"] }}</a>
                            </div>
                        </div>
                    </div>
                    <!--end::Body-->
                </div>
                <!--end::Stats Widget 1-->
            </div>
            <div class="col-xl-4 dashboard-card">
                <!--begin::Stats Widget 1-->
                <div class="card card-custom card-stretch gutter-b">
                    <!--begin::Header-->
                    <div class="card-header border-0 pt-6">
                        <h3 class="card-title">
                            <span class="card-label text-dark-75" style="font-size: 15px">IN LEAVE(TODAY)</span>
                        </h3>
                    </div>
                    <div class="card-body d-flex align-items-center justify-content-between pt-0 flex-wrap">
                        <div class="progress-vertical w-xl-350px ml-25">
                            <div class="display2 py-0 pl-22 text-primary">
                                <a href="{{ route('dashboard-admin.inLeaveToday') }}">
                                    {{ $data["reportToAdmin"]["inLeaveToday"] }}
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
                <!--end::Stats Widget 1-->
            </div>
            <div class="col-xl-4 dashboard-card">
                <!--begin::Stats Widget 1-->
                <div class="card card-custom card-stretch gutter-b">
                    <!--begin::Header-->
                    <div class="card-header border-0 pt-6">
                        <h3 class="card-title">
                            <span class="card-label text-dark-75">IN LEAVE(TOMORROW)</span>
                        </h3>
                    </div>
                    <!--end::Header-->
                    <!--begin::Body-->
                    <div class="card-body d-flex align-items-center justify-content-between pt-0 flex-wrap">
                        <div class="progress-vertical w-350px ml-25">
                            <div class="display2 py-0 pl-22 text-primary">
                                <a href="{{ route('dashboard-admin.inLeaveTomorrow') }}">
                                    {{ $data["reportToAdmin"]["inLeaveTomorrow"] }}
                                </a>
                            </div>
                        </div>
                        <!--end::Chart-->
                    </div>
                    <!--end::Body-->
                </div>
                <!--end::Stats Widget 1-->
            </div>
            <div class="col-xl-4 dashboard-card">
                <!--begin::Stats Widget 1-->
                <div class="card card-custom card-stretch gutter-b">
                    <!--begin::Header-->
                    <div class="card-header border-0 pt-6">
                        <h3 class="card-title">
                            <span class="card-label text-dark-75">TODAY'S PRESENT</span>
                        </h3>
                    </div>
                    <!--end::Header-->
                    <!--begin::Body-->
                    <div class="card-body d-flex align-items-center justify-content-between pt-0 flex-wrap">
                        <div class="progress-vertical w-350px ml-25">
                            <div class="display2 py-0 pl-18 text-primary">
                                <a href="{{ route('dashboard-admin.todayPresent') }}">
                                    {{ $data["reportToAdmin"]["presentToday"] }}
                                </a>
                            </div>
                        </div>
                        <!--end::Chart-->
                    </div>
                    <!--end::Body-->
                </div>
                <!--end::Stats Widget 1-->
            </div>
            <div class="col-xl-4 dashboard-card">
                <!--begin::Stats Widget 1-->
                <div class="card card-custom card-stretch gutter-b">
                    <!--begin::Header-->
                    <div class="card-header border-0 pt-6">
                        <h3 class="card-title">
                            <span class="card-label text-dark-75" style="font-size: 15px">TODAY'S ABSENT</span>
                        </h3>
                    </div>
                    <!--end::Header-->
                    <!--begin::Body-->
                    <div class="card-body d-flex align-items-center justify-content-between pt-0 flex-wrap">
                        <div class="progress-vertical w-350px ml-25">
                            <div class="display2 py-0 pl-15 text-primary">
                                <a href="{{ route('dashboard-admin.todayAbsent') }}">
                                    {{ $data["reportToAdmin"]["absentToday"] }}
                                </a>
                            </div>
                        </div>
                    </div>
                    <!--end::Body-->
                </div>
                <!--end::Stats Widget 1-->
            </div>
            <div class="col-xl-4 dashboard-card">
                <!--begin::Stats Widget 1-->
                <div class="card card-custom card-stretch gutter-b">
                    <!--begin::Header-->
                    <div class="card-header border-0 pt-6">
                        <h3 class="card-title">
                            <span class="card-label text-dark-75" style="font-size: 15px">TODAY'S LATE</span>
                        </h3>
                    </div>
                    <div class="card-body d-flex align-items-center justify-content-between pt-0 flex-wrap">
                        <div class="progress-vertical w-350px ml-25">
                            <div class="display2 py-0 pl-22 text-primary">
                                <a href="{{ route('dashboard-admin.todayLate') }}">
                                    {{ $data["reportToAdmin"]["lateToday"] }}
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
                <!--end::Stats Widget 1-->
            </div>
        </div>

        {{-- EMPLOYEE LEAVE REQUEST --}}
        <div class="row">
            <div class="col-xxl-12">
                <!--begin::List Widget 7-->
                <div class="card card-custom card-stretch gutter-b">
                    <!--begin::Header-->
                    <div class="card-header border-0 pt-7">
                        <h3 class="card-title align-items-start flex-column">
                            <span class="card-label font-weight-bold font-size-h4 text-dark-75">LEAVE REQUEST</span>
                            <span class="text-muted mt-3 font-weight-bold font-size-sm">EMPLOYEE LEAVE REQUEST</span>
                        </h3>
                    </div>
                    <!--end::Header-->
                    <!--begin::Body-->
                    <div class="card-body pt-0 pb-4">
                        <div class="card-body">
                            <table class="table table-responsive-lg" id="leaveRequestToAdmin">
                                <thead class="custom-thead">
                                <tr>
                                    <th scope="col">Employee Name</th>
                                    <th scope="col">Designation</th>
                                    <th scope="col">Office Division</th>
                                    <th scope="col">Department</th>
                                    <th scope="col">Requested Leave</th>
                                    <th scope="col">Status</th>
                                </tr>
                                </thead>
                                <tbody>
                                @if(isset($data["reportToAdmin"]))
                                    @foreach($data["reportToAdmin"]["leaveRequests"] as $leaveRequest)
                                        <tr>
                                            <td>{{ $leaveRequest->employee->name }}</td>
                                            <td>{{ $leaveRequest->employee->currentPromotion->designation->title }}</td>
                                            <td>{{ $leaveRequest->employee->currentPromotion->officeDivision->name }}</td>
                                            <td>{{ $leaveRequest->employee->currentPromotion->department->name }}</td>
                                            <td>{{ date("M d, Y", strtotime($leaveRequest->from_date)) }} - {{ date("M d, Y", strtotime($leaveRequest->to_date)) }}</td>
                                            <td>
                                                @if($leaveRequest->status == \App\Models\LeaveRequest::STATUS_PENDING)
                                                    <a href="#" class="btn btn-warning btn-sm font-weight-bold btn-pill">Pending</a>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                @endif
                                </tbody>
                            </table>
                        </div>
                        <!--end::Body-->
                    </div>
                    <!--end::List Widget 7-->
                </div>
            </div>
        </div>

        {{-- Employee Attendance --}}
        <div class="row">
            <div class="col-xxl-12">
                <!--begin::List Widget 7-->
                <div class="card card-custom card-stretch gutter-b">
                    <!--begin::Header-->
                    <div class="card-header border-0 pt-7">
                        <h3 class="card-title align-items-start flex-column">
                            <span class="card-label font-weight-bold font-size-h4 text-dark-75">ATTENDANCE</span>
                            <span class="text-muted mt-3 font-weight-bold font-size-sm">EMPLOYEE ATTENDANCE</span>
                        </h3>
                    </div>
                    <!--end::Header-->
                    <!--begin::Body-->
                    <div class="card-body pt-0 pb-4">
                        <div class="card-body">
                            <table class="table table-responsive-lg" id="attendanceToAdmin">
                                <thead class="custom-thead">
                                <tr>
                                    <th scope="col">Photo</th>
                                    <th scope="col">Office ID</th>
                                    <th scope="col">Name</th>
                                    <th scope="col">Office Division</th>
                                    <th scope="col">Department</th>
                                    <th scope="col">Time In</th>
                                    <th scope="col">Time Out</th>
                                    <th scope="col">Date</th>
                                </tr>
                                </thead>
                                <tbody>
                                @if(isset($data["reportToAdmin"]))
                                    @foreach($data["reportToAdmin"]["attendances"] as $attendance)
                                        <tr>
                                            <td scope="row">
                                                <div class="symbol flex-shrink-0" style="width: 35px; height: auto">
                                                    <img src="{{ $attendance->photo }}" alt="{{ $attendance->name }}"/>
                                                </div>
                                            </td>
                                            <td>{{ $attendance->fingerprint_no }}</td>
                                            <td>{{ $attendance->name }}</td>
                                            <td>{{ $attendance->currentPromotion->officeDivision->name }}</td>
                                            <td>{{ $attendance->currentPromotion->department->name }}</td>
                                            <td>{{ date('h:i:s a', strtotime($attendance->timeInToday->punch_time)) }}</td>
                                            <td>
                                                @if($attendance->timeInToday->punch_time != $attendance->timeOutToday->punch_time)
                                                    {{ date('h:i:s a', strtotime($attendance->timeOutToday->punch_time)) }}
                                                @else
                                                    ---
                                                @endif
                                            </td>
                                            <td>{{ date('M d, Y', strtotime($attendance->timeOutToday->punch_time)) }}</td>
                                        </tr>
                                    @endforeach
                                @endif
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <!--end::Body-->
                </div>
                <!--end::List Widget 7-->
            </div>
        </div>
    @endif
@endsection

@section('footer-js')
    <script type="text/javascript" src="{{ asset('assets/js/widget.js') }}"></script>
    <script type="text/javascript">
        $(document).ready(function () {
            $("#leaveRequestToAdmin").DataTable();
            $("#attendanceToAdmin").DataTable({
                "order": [5, "desc"]
            });
        });
    </script>
@endsection
