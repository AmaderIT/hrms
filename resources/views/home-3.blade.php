@extends('layouts.app')

@section('content')
    @if(!isset($data["error"]))
        @if(auth()->user()->can("Show Admin Dashboard") AND !auth()->user()->hasRole(\App\Models\User::ROLE_HR_ADMIN_SUPERVISOR) AND !auth()->user()->hasRole(\App\Models\User::ROLE_GENERAL_USER))
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
                                    <a href="{{ ($data["reportToAdmin"]["totalEmployees"] > 0) ? route('employee.index') : '#' }}">{{ $data["reportToAdmin"]["totalEmployees"] }}</a>
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
                                    <a href="{{ $data["reportToAdmin"]["inLeaveToday"] > 0 ? route('dashboard-admin.inLeaveToday') : '#' }}">
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
                                    <a href="{{ $data["reportToAdmin"]["inLeaveTomorrow"] > 0 ? route('dashboard-admin.inLeaveTomorrow') : '#' }}">
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
                                    <a href="{{ $data["reportToAdmin"]["presentToday"] > 0 ? route('dashboard-admin.todayPresent') : '#' }}">
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
                                    <a href="{{ $data["reportToAdmin"]["absentToday"] > 0 ? route('dashboard-admin.todayAbsent') : '#' }}">
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
                                    <a href="{{ $data["reportToAdmin"]["lateToday"] > 0 ? route('dashboard-admin.todayLate') : '#' }}">
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
            @if(count($data["reportToAdmin"]["leaveRequests"]) > 0)
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
            @endif

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

                            <form action="">
                                <select class="form-control select w-100" name="department_id" id="department_id" style="height: 30px;">
                                    @foreach($data["departments"] as $department)
                                        <option value="{{ $department->id }}">{{ $department->name }}</option>
                                    @endforeach
                                </select>
                                <button class="btn btn-sm btn-primary">Filter</button>
                            </form>
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
                                                        <img src='{{ asset("photo/".$attendance->fingerprint_no.".jpg") }}' onerror="this.onerror=null; this.src='{{ asset('assets/media/svg/avatars/001-boy.svg')}}';" width="110" />
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
                                                <td>{{ date('M d, Y', strtotime($attendance->timeInToday->punch_time)) }}</td>
                                            </tr>
                                        @endforeach
                                    @endif
                                    </tbody>
                                </table>
                                <div class="d-flex">
                                    <div class="ml-auto">
                                        {{ $data["reportToAdmin"]["attendances"]->withQueryString()->links() }}
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!--end::Body-->
                    </div>
                    <!--end::List Widget 7-->
                </div>
            </div>

        @endif

        @if(!is_null($data["reportToSupervisor"]) AND auth()->user()->can("Show Supervisor Dashboard"))
            {{-- Tab View --}}
            <ul class="nav nav-tabs nav-tabs-line">
                <li class="nav-item">
                    <a class="nav-link active" data-toggle="tab" href="#admin_dashboard">
                        <span class="nav-icon"><i class="flaticon2-pie-chart-4"></i></span>
                        <span class="nav-text">Admin Dashboard</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" data-toggle="tab" href="#department_dashboard">
                        <span class="nav-icon"><i class="flaticon2-chat-1"></i></span>
                        <span class="nav-text">Department Dashboard</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" data-toggle="tab" href="#my_dashboard">
                        <span class="nav-icon"><i class="flaticon2-dashboard"></i></span>
                        <span class="nav-text">My Dashboard</span>
                    </a>
                </li>
            </ul>
            <div class="tab-content mt-5" id="myTabContent">
                <div class="tab-pane fade" id="department_dashboard" role="tabpanel" aria-labelledby="department_dashboard">
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
                                        <div class="display2 py-0 pl-18 text-primary">
                                            <a href="{{ $data["reportToSupervisor"]["totalEmployeesInDepartment"] > 0 ? route('dashboard-supervisor.employees') : '#' }}">
                                                {{ $data["reportToSupervisor"]["totalEmployeesInDepartment"] }}
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
                                        <span class="card-label text-dark-75" style="font-size: 15px">IN LEAVE(TODAY)</span>
                                    </h3>
                                </div>
                                <div class="card-body d-flex align-items-center justify-content-between pt-0 flex-wrap">
                                    <div class="progress-vertical w-xl-350px ml-25">
                                        <div class="display2 py-0 pl-22 text-primary">
                                            <a href="{{ $data["reportToSupervisor"]["inLeaveToday"] > 0 ? route('dashboard-supervisor.inLeaveToday') : '#' }}">
                                                {{ $data["reportToSupervisor"]["inLeaveToday"] }}
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
                                            <a href="{{ $data["reportToSupervisor"]["inLeaveTomorrow"] > 0 ? route('dashboard-supervisor.inLeaveTomorrow') : '#' }}">
                                                {{ $data["reportToSupervisor"]["inLeaveTomorrow"] }}
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
                                        <div class="display2 py-0 pl-25 text-primary">
                                            <a href="{{ $data["reportToSupervisor"]["presentToday"] > 0 ? route('dashboard-supervisor.todayPresent') : '#' }}">
                                                {{ $data["reportToSupervisor"]["presentToday"] }}
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
                                        <div class="display2 py-0 pl-25 text-primary">
                                            <a href="{{ $data["reportToSupervisor"]["absentToday"] > 0 ? route('dashboard-supervisor.todayAbsent') : '#' }}">
                                                {{ $data["reportToSupervisor"]["absentToday"] }}
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
                                        <div class="display2 py-0 pl-25 text-primary">
                                            <a href="{{ $data["reportToSupervisor"]["lateToday"] > 0 ? route('dashboard-supervisor.todayLate') : '#' }}">
                                                {{ $data["reportToSupervisor"]["lateToday"] }}
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <!--end::Stats Widget 1-->
                        </div>
                    </div>

                    {{-- EMPLOYEE LEAVE REQUEST --}}
                    @if(count($data["reportToSupervisor"]["leaveRequests"]) > 0)
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
                                            <table class="table table-responsive-lg" id="leaveRequestToSupervisor">
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
                                                @if(isset($data["reportToSupervisor"]))
                                                    @foreach($data["reportToSupervisor"]["leaveRequests"] as $leaveRequest)
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
                    @endif

                    {{-- Employee Attendance --}}
                    @if(count($data["reportToSupervisor"]["attendances"]) > 0)
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
                                            <table class="table table-responsive-lg" id="attendanceToSupervisor">
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
                                                @if(isset($data["reportToSupervisor"]))
                                                    @foreach($data["reportToSupervisor"]["attendances"] as $attendance)
                                                        <tr>
                                                            <td scope="row">
                                                                <div class="symbol flex-shrink-0" style="width: 35px; height: auto">
                                                                    <img src='{{ asset("photo/".$attendance->fingerprint_no.".jpg") }}' onerror="this.onerror=null; this.src='{{ asset('assets/media/svg/avatars/001-boy.svg')}}';" width="110" />
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

                    @if($data["reportToEmployee"]["unpaidLeaves"]->count() > 0)
                        <div class="row">
                            <div class="col-xxl-12">
                                <!--begin::List Widget 7-->
                                <div class="card card-custom card-stretch gutter-b">
                                    <!--begin::Header-->
                                    <div class="card-header border-0 pt-7">
                                        <h3 class="card-title align-items-start flex-column">
                                            <span class="card-label font-weight-bold font-size-h4 text-dark-75">My Unpaid Leave</span>
                                            <span class="text-muted mt-3 font-weight-bold font-size-sm">Employee Unpaid Leave</span>
                                        </h3>
                                    </div>

                                    <!--end::Header-->
                                    <!--begin::Body-->
                                    <div class="card-body pt-0 pb-4">
                                        <div class="card-body">
                                            <table class="table table-responsive-lg" id="employeeUnpaidLeave">
                                                <thead class="custom-thead">
                                                <tr>
                                                    <th scope="col">Leave Date</th>
                                                    <th scope="col">Action</th>
                                                </tr>
                                                </thead>
                                                <tbody>
                                                @foreach($data["reportToEmployee"]["unpaidLeaves"] as $unpaidLeave)
                                                    <tr>
                                                        <td>{{ date("M d, Y", strtotime($unpaidLeave->leave_date)) }}</td>
                                                        <td>
                                                            <a href="{{ route('apply-for-leave.create', ['date' => $unpaidLeave->leave_date]) }}" class="btn btn-primary btn-sm">Apply for Leave</a>
                                                        </td>
                                                    </tr>
                                                @endforeach
                                                </tbody>
                                            </table>
                                        </div>
                                        <!--end::Body-->
                                    </div>
                                    <!--end::List Widget 7-->
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
                <div class="tab-pane fade" id="my_dashboard" role="tabpanel" aria-labelledby="my_dashboard">
                    {{-- Employee Dashboard --}}
                    <div class="row">
                        <div class="col-xxl-4 dashboard-card">
                            <!--begin::Stats Widget 1-->
                            <div class="card card-custom card-stretch gutter-b">
                                <!--begin::Header-->
                                <div class="card-header border-0 pt-6">
                                    <h2 class="card-title">
                                        <span class="card-label text-dark-75" style="font-size: 15px">TODAY TIME IN</span>
                                        <span class="card-label text-dark-75" style="font-size: 20px">
                                        <div class="py-0 pl-4 text-primary">
                                            @if(!is_null($data["reportToEmployee"]["timeInToday"]))
                                                {{ \Carbon\Carbon::createFromDate($data["reportToEmployee"]["timeInToday"])->format('h:i:s A') }}
                                            @else
                                                ---
                                            @endif
                                        </div>
                                    </span>
                                    </h2>
                                    <h3 class="card-title mt-0">
                                        <span class="card-label text-dark-75" style="font-size: 15px">TODAY TIME OUT</span>
                                        <span class="card-label text-dark-75" style="font-size: 20px">
                                        <div class="py-0 pl-4 text-primary">
                                            @if($data["reportToEmployee"]["timeInToday"] != $data["reportToEmployee"]["timeOutToday"])
                                                {{ \Carbon\Carbon::createFromDate($data["reportToEmployee"]["timeOutToday"])->format('h:i:s A') }}
                                            @else
                                                ---
                                            @endif
                                        </div>
                                    </span>
                                    </h3>
                                </div>
                                <!--end::Header-->
                            </div>
                            <!--end::Stats Widget 1-->
                        </div>
                        <div class="col-xxl-4 dashboard-card">
                            <!--begin::Stats Widget 1-->
                            <div class="card card-custom card-stretch gutter-b">
                                <!--begin::Header-->
                                <div class="card-header border-0 pt-6">
                                    <h3 class="card-title">
                                        <span class="card-label text-dark-75" style="font-size: 15px">TOTAL LATE OF THIS MONTH</span>
                                    </h3>
                                </div>
                                <div class="card-body d-flex align-items-center justify-content-between pt-0 flex-wrap">
                                    <div class="progress-vertical w-350px ml-25">
                                        <div class="display2 py-0 pl-25 text-primary">{{ $data["reportToEmployee"]["totalLateThisMonth"] }}</div>
                                    </div>
                                </div>
                            </div>
                            <!--end::Stats Widget 1-->
                        </div>
                        <div class="col-xxl-4 dashboard-card">
                            <!--begin::Stats Widget 1-->
                            <div class="card card-custom card-stretch gutter-b">
                                <!--begin::Header-->
                                <div class="card-header border-0 pt-6">
                                    <h3 class="card-title">
                                        <span class="card-label text-dark-75" style="font-size: 1.75rem">UPCOMING HOLIDAYS</span>
                                    </h3>
                                    @foreach($data["reportToEmployee"]["upcomingHolidays"] as $upcomingHoliday)
                                        <h3 class="card-title mt-1">
                                        <span class="card-label text-dark-75" style="font-size: 1.1rem">
                                            {{ $upcomingHoliday->holiday->name }} -
                                            {{ \Carbon\Carbon::createFromDate($upcomingHoliday->from_date->toDateTimeString())->format('M jS') }}
                                            @if($upcomingHoliday->from_date != $upcomingHoliday->to_date)
                                                -
                                                {{ \Carbon\Carbon::createFromDate($upcomingHoliday->to_date->toDateTimeString())->format('M jS') }}
                                            @endif
                                        </span>
                                        </h3>
                                    @endforeach
                                </div>
                                <!--end::Header-->
                            </div>
                            <!--end::Stats Widget 1-->
                        </div>
                        <div class="col-xxl-4 dashboard-card">
                            <!--begin::Stats Widget 1-->
                            <div class="card card-custom card-stretch gutter-b">
                                <!--begin::Header-->
                                <div class="card-header border-0 pt-6">
                                    <h3 class="card-title">
                                        <span class="card-label text-dark-75" style="font-size: 1.75rem">LEAVE</span>
                                    </h3>
                                    <div class="col-lg-12 pl-0">
                                        <h3 class="card-title">
                                            <span class="card-label text-dark-75">Total Leave - {{ $data["reportToEmployee"]["leaveTotal"] }}</span>
                                        </h3>
                                    </div>
                                    <div class="col-lg-12 pl-0">
                                        <h3 class="card-title">
                                            <span class="card-label text-dark-75">Consumed - {{ $data["reportToEmployee"]["leaveConsumed"] }}</span>
                                        </h3>
                                    </div>
                                    <div class="col-lg-12 pl-0">
                                        <h3 class="card-title">
                                            <span class="card-label text-dark-75">Left - {{ $data["reportToEmployee"]["leaveLeft"] }}</span>
                                        </h3>
                                    </div>
                                </div>
                                <!--end::Header-->
                            </div>
                            <!--end::Stats Widget 1-->
                        </div>
                        <div class="col-xxl-4 dashboard-card">
                            <!--begin::Stats Widget 1-->
                            <div class="card card-custom card-stretch gutter-b">
                                <!--begin::Header-->
                                <div class="row">
                                    <div class="col-lg-6">
                                        <div class="card-header border-0 pt-6">
                                            <h3 class="card-title">
                                                <span class="card-label text-dark-75" style="font-size: 1.4rem">SALARY</span>
                                            </h3>
                                            <div class="col-lg-12 pl-0" style="font-size: 1rem">
                                                @foreach($data["reportToEmployee"]["salary"] as $salary)
                                                    <h2 class="card-title1" style="font-size: 1rem; padding: 0px !important;">
                                                        <a href="{{ route('salary.generatePaySlip', ['salary' => $salary->id]) }}"
                                                           class="card-label">{{ date('F', mktime(0, 0, 0, $salary->month, 10)) }} {{ $salary->year }}
                                                        </a>
                                                    </h2>
                                                @endforeach

                                                <div class="row" style="margin-top: -5px !important;">
                                                    <div class="col-lg-4"></div>
                                                    <div class="col-lg-8">
                                                        @if(count($data["reportToEmployee"]["salary"]) > 0)
                                                            <a href="{{ route('salary.paySlip') }}" class="text-primary ml-10">More</a>
                                                        @endif
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <!--end::Header-->
                            </div>
                            <!--end::Stats Widget 1-->
                        </div>
                        @if(time() < strtotime($data["mealRequestEndTime"]) OR strtotime(\Carbon\Carbon::parse($data["mealRequestEndTime"])->addHours(6)->format('H:i:s')) < time())
                            {{--<div class="col-xxl-4 dashboard-card">
                                <!--begin::Stats Widget 1-->
                                <div class="card card-custom card-stretch gutter-b">
                                    <!--begin::Header-->
                                    <div class="card-header border-0 pt-6">
                                        <h3 class="card-title">
                                            @if(optional(auth()->user()->meal)->status == 1)
                                                <span class="card-label text-dark-75" style="font-size: 1.75rem">Cancel Today's Meal</span>
                                            @else
                                                <span class="card-label text-dark-75" style="font-size: 1.75rem">Request Meal for Today</span>
                                            @endif
                                        </h3>
                                        <div class="col-lg-12 d-flex justify-content-center pl-0 mt-5 align-items-center">
                                            <span class="mr-2">No</span>
                                            <span class="switch switch-outline switch-icon switch-primary">
                                <label>
                                    <input type="checkbox"
                                           @if(optional(auth()->user()->meal)->status == 1)
                                           {{ (optional(auth()->user()->dailyMeal)->status == 0 AND auth()->user()->dailyMeal != NULL) ? 'checked' : '' }}
                                           @else
                                           {{ optional(auth()->user()->dailyMeal)->status == 1 ? 'checked' : '' }}
                                           @endif
                                           name="status" id="{{ auth()->user()->id }}" onclick="changeStatus({{ auth()->user()->id }}, {{ optional(auth()->user()->dailyMeal)->status  }})"/>
                                    <span></span>
                                </label>
                            </span>
                                            <span class="ml-2">Yes</span>
                                        </div>
                                    </div>
                                    <!--end::Header-->
                                </div>
                                <!--end::Stats Widget 1-->
                            </div>--}}


                            {{-- @elseif(\Carbon\Carbon::now()->format('H:i:s') > "17:00:00" AND \Carbon\Carbon::now()->format('H:i:s') < "23:59:59")
                                <div class="col-xxl-4 dashboard-card">
                                    <!--begin::Stats Widget 1-->
                                    <div class="card card-custom card-stretch gutter-b">
                                        <!--begin::Header-->
                                        <div class="card-header border-0 pt-6">
                                            <h3 class="card-title">
                                                <span class="card-label text-dark-75" style="font-size: 1.75rem">TOMORROW'S MEAL STATUS</span>
                                            </h3>
                                            <div class="col-lg-12 d-flex justify-content-center pl-0 mt-5">
                                            <span class="switch switch-outline switch-icon switch-primary">
                                                <label>

                                                    <input type="checkbox" {{ optional(auth()->user()->tomorrowMeal)->status === \App\Models\UserMeal::STATUS_ACTIVE ? 'checked' : '' }}
                                                    name="status" id="{{ auth()->user()->id }}" onclick="changeTomorrowStatus({{ auth()->user()->id }}, {{ optional(auth()->user()->dailyMeal)->status  }})"/>
                                                    <span></span>
                                                </label>
                                            </span>
                                            </div>
                                        </div>
                                        <!--end::Header-->
                                    </div>
                                    <!--end::Stats Widget 1-->
                                </div> --}}
                        @endif
                    </div>

                    {{-- Daily Attendance --}}
                    @if(!is_null($data["reportToEmployee"]["attendances"]))
                        <div class="row">
                            <div class="col-xxl-12">
                                <!--begin::List Widget 7-->
                                <div class="card card-custom card-stretch gutter-b">
                                    <!--begin::Header-->
                                    <div class="card-header border-0 pt-7">
                                        <h3 class="card-title align-items-start flex-column">
                                            <span class="card-label font-weight-bold font-size-h4 text-dark-75">DAILY ATTENDANCE</span>
                                            <span class="text-muted mt-3 font-weight-bold font-size-sm">EMPLOYEE DAILY ATTENDANCE</span>
                                        </h3>
                                    </div>
                                    <!--end::Header-->
                                    <!--begin::Body-->
                                    <div class="card-body pt-0 pb-4">
                                        <div class="card-body">
                                            <table class="table table-responsive-lg" id="employeeAttendance">
                                                <thead class="custom-thead">
                                                <tr>
                                                    <th scope="col">Month</th>
                                                    <th scope="col">Time In</th>
                                                    <th scope="col">Time Out</th>
                                                </tr>
                                                </thead>
                                                <tbody>
                                                @if(!is_null($data["reportToEmployee"]["attendances"]))
                                                    @foreach($data["reportToEmployee"]["attendances"]->timeInThisMonth as $key => $attendance)
                                                        <tr>
                                                            <td>{{ date("M jS, Y", strtotime($data["reportToEmployee"]["attendances"]->timeInThisMonth[$key]->punch_time)) }}</td>
                                                            <td>{{ date("h:i:s A", strtotime($data["reportToEmployee"]["attendances"]->timeInThisMonth[$key]->punch_time)) }}</td>
                                                            <td>
                                                                @if($data["reportToEmployee"]["attendances"]->timeInThisMonth[$key]->punch_time != $data["reportToEmployee"]["attendances"]->timeOutThisMonth[$key]->punch_time)
                                                                    {{ date("h:i:s A", strtotime($data["reportToEmployee"]["attendances"]->timeOutThisMonth[$key]->punch_time)) }}
                                                                @else
                                                                    -----
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
                    @endif

                    {{-- EMPLOYEE LEAVE REQUEST --}}
                    @if(count($data["reportToEmployee"]["leaveRequests"]) > 0)
                        <div class="row">
                            <div class="col-xxl-12">
                                <!--begin::List Widget 7-->
                                <div class="card card-custom card-stretch gutter-b">
                                    <!--begin::Header-->
                                    <div class="card-header border-0 pt-7">
                                        <h3 class="card-title align-items-start flex-column">
                                            <span class="card-label font-weight-bold font-size-h4 text-dark-75">MY LEAVE REQUEST</span>
                                            <span class="text-muted mt-3 font-weight-bold font-size-sm">EMPLOYEE LEAVE REQUEST</span>
                                        </h3>
                                    </div>

                                    <!--end::Header-->
                                    <!--begin::Body-->
                                    <div class="card-body pt-0 pb-4">
                                        <div class="card-body">
                                            <table class="table table-responsive-lg" id="employeeLeaveRequest">
                                                <thead class="custom-thead">
                                                <tr>
                                                    <th scope="col">Requested Leave</th>
                                                    <th scope="col">Status</th>
                                                    <th scope="col">Action</th>
                                                </tr>
                                                </thead>
                                                <tbody>
                                                @foreach($data["reportToEmployee"]["leaveRequests"] as $leaveRequest)
                                                    <tr>
                                                        <td>{{ date("M d, Y", strtotime($leaveRequest->leave_date)) }}</td>
                                                        <td>{{ date("M d, Y", strtotime($leaveRequest->leave_date)) }}</td>
                                                        <td>
                                                            @if($leaveRequest->status == \App\Models\LeaveRequest::STATUS_PENDING)
                                                                <a href="{{ route('apply-for-leave.edit', ['applyForLeave' => $leaveRequest->id]) }}">
                                                                    <i class="fa fa-edit" style="color: green"></i>
                                                                </a>
                                                            @endif
                                                        </td>
                                                    </tr>
                                                @endforeach
                                                </tbody>
                                            </table>
                                        </div>
                                        <!--end::Body-->
                                    </div>
                                    <!--end::List Widget 7-->
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
                <div class="tab-pane fade show active" id="admin_dashboard" role="tabpanel" aria-labelledby="admin_dashboard">
                    {{-- Admin Dashboard --}}
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
                                            <a href="{{ ($data["reportToAdmin"]["totalEmployees"] > 0) ? route('employee.index') : '#' }}">{{ $data["reportToAdmin"]["totalEmployees"] }}</a>
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
                                            <a href="{{ $data["reportToAdmin"]["inLeaveToday"] > 0 ? route('dashboard-admin.inLeaveToday') : '#' }}">
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
                                            <a href="{{ $data["reportToAdmin"]["inLeaveTomorrow"] > 0 ? route('dashboard-admin.inLeaveTomorrow') : '#' }}">
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
                                            <a href="{{ $data["reportToAdmin"]["presentToday"] > 0 ? route('dashboard-admin.todayPresent') : '#' }}">
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
                                            <a href="{{ $data["reportToAdmin"]["absentToday"] > 0 ? route('dashboard-admin.todayAbsent') : '#' }}">
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
                                            <a href="{{ $data["reportToAdmin"]["lateToday"] > 0 ? route('dashboard-admin.todayLate') : '#' }}">
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
                    @if(count($data["reportToAdmin"]["leaveRequests"]) > 0)
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
                    @endif

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

                                    <form action="">
                                        <select class="form-control select w-100" name="department_id" id="department_id" style="height: 30px;">
                                            @foreach($data["departments"] as $department)
                                                <option value="{{ $department->id }}">{{ $department->name }}</option>
                                            @endforeach
                                        </select>
                                        <button class="btn btn-sm btn-primary">Filter</button>
                                    </form>
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
                                                                <img src='{{ asset("photo/".$attendance->fingerprint_no.".jpg") }}' onerror="this.onerror=null; this.src='{{ asset('assets/media/svg/avatars/001-boy.svg')}}';" width="110" />
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
                                                        <td>{{ date('M d, Y', strtotime($attendance->timeInToday->punch_time)) }}</td>
                                                    </tr>
                                                @endforeach
                                            @endif
                                            </tbody>
                                        </table>
                                        <div class="d-flex">
                                            <div class="ml-auto">
                                                {{ $data["reportToAdmin"]["attendances"]->withQueryString()->links() }}
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <!--end::Body-->
                            </div>
                            <!--end::List Widget 7-->
                        </div>
                    </div>
                </div>
            </div>
        @endif

        {{-- Employee Dashboard --}}
        @if(!is_null($data["reportToEmployee"]) AND auth()->user()->can("Show Employee Dashboard") AND !auth()->user()->hasRole(\App\Models\User::ROLE_HR_ADMIN_SUPERVISOR))
            <ul class="nav nav-tabs nav-tabs-line">
                <li class="nav-item">
                    <a class="nav-link active" data-toggle="tab" href="#my_dashboard_for_employee">
                        <span class="nav-icon"><i class="flaticon2-dashboard"></i></span>
                        <span class="nav-text">My Dashboard</span>
                    </a>
                </li>
                @if(isset($data["reportToAdmin"]))
                    <li class="nav-item">
                        <a class="nav-link" data-toggle="tab" href="#admin_dashboard_for_employee">
                            <span class="nav-icon"><i class="flaticon2-pie-chart-4"></i></span>
                            <span class="nav-text">Admin Dashboard</span>
                        </a>
                    </li>
                @endif
            </ul>
            <div class="tab-content mt-5" id="myTabContentEmployee">
                <div class="tab-pane fade show active" id="my_dashboard_for_employee" role="tabpanel" aria-labelledby="my_dashboard_for_employee">
                    <div class="row">
                        <div class="col-xxl-4 dashboard-card">
                            <!--begin::Stats Widget 1-->
                            <div class="card card-custom card-stretch gutter-b">
                                <!--begin::Header-->
                                <div class="card-header border-0 pt-6">
                                    <h2 class="card-title">
                                        <span class="card-label text-dark-75" style="font-size: 15px">TODAY TIME IN</span>
                                        <span class="card-label text-dark-75" style="font-size: 20px">
                                <div class="py-0 pl-4 text-primary">
                                    @if(!is_null($data["reportToEmployee"]["timeInToday"]))
                                        {{ \Carbon\Carbon::createFromDate($data["reportToEmployee"]["timeInToday"])->format('h:i:s A') }}
                                    @else
                                        ---
                                    @endif
                                </div>
                            </span>
                                    </h2>
                                    <h3 class="card-title mt-0">
                                        <span class="card-label text-dark-75" style="font-size: 15px">TODAY TIME OUT</span>
                                        <span class="card-label text-dark-75" style="font-size: 20px">
                                <div class="py-0 pl-4 text-primary">
                                    @if($data["reportToEmployee"]["timeInToday"] != $data["reportToEmployee"]["timeOutToday"])
                                        {{ \Carbon\Carbon::createFromDate($data["reportToEmployee"]["timeOutToday"])->format('h:i:s A') }}
                                    @else
                                        ---
                                    @endif
                                </div>
                            </span>
                                    </h3>
                                </div>
                                <!--end::Header-->
                            </div>
                            <!--end::Stats Widget 1-->
                        </div>
                        <div class="col-xxl-4 dashboard-card">
                            <!--begin::Stats Widget 1-->
                            <div class="card card-custom card-stretch gutter-b">
                                <!--begin::Header-->
                                <div class="card-header border-0 pt-6">
                                    <h3 class="card-title">
                                        <span class="card-label text-dark-75" style="font-size: 15px">TOTAL LATE OF THIS MONTH</span>
                                    </h3>
                                </div>
                                <div class="card-body d-flex align-items-center justify-content-between pt-0 flex-wrap">
                                    <div class="progress-vertical w-350px ml-25">
                                        <div class="display2 py-0 pl-25 text-primary">{{ $data["reportToEmployee"]["totalLateThisMonth"] }}</div>
                                    </div>
                                </div>
                            </div>
                            <!--end::Stats Widget 1-->
                        </div>
                        <div class="col-xxl-4 dashboard-card">
                            <!--begin::Stats Widget 1-->
                            <div class="card card-custom card-stretch gutter-b">
                                <!--begin::Header-->
                                <div class="card-header border-0 pt-6">
                                    <h3 class="card-title">
                                        <span class="card-label text-dark-75" style="font-size: 1.75rem">UPCOMING HOLIDAYS</span>
                                    </h3>
                                    @foreach($data["reportToEmployee"]["upcomingHolidays"] as $upcomingHoliday)
                                        <h3 class="card-title mt-1">
                                <span class="card-label text-dark-75" style="font-size: 1.1rem">
                                    {{ $upcomingHoliday->holiday->name }} -
                                    {{ \Carbon\Carbon::createFromDate($upcomingHoliday->from_date->toDateTimeString())->format('M jS') }}
                                    @if($upcomingHoliday->from_date != $upcomingHoliday->to_date)
                                        -
                                        {{ \Carbon\Carbon::createFromDate($upcomingHoliday->to_date->toDateTimeString())->format('M jS') }}
                                    @endif
                                </span>
                                        </h3>
                                    @endforeach
                                </div>
                                <!--end::Header-->
                            </div>
                            <!--end::Stats Widget 1-->
                        </div>
                        <div class="col-xxl-4 dashboard-card">
                            <!--begin::Stats Widget 1-->
                            <div class="card card-custom card-stretch gutter-b">
                                <!--begin::Header-->
                                <div class="card-header border-0 pt-6">
                                    <h3 class="card-title">
                                        <span class="card-label text-dark-75" style="font-size: 1.75rem">LEAVE</span>
                                    </h3>
                                    <div class="col-lg-12 pl-0">
                                        <h3 class="card-title">
                                            <span class="card-label text-dark-75">Total Leave - {{ $data["reportToEmployee"]["leaveTotal"] }}</span>
                                        </h3>
                                    </div>
                                    <div class="col-lg-12 pl-0">
                                        <h3 class="card-title">
                                            <span class="card-label text-dark-75">Consumed - {{ $data["reportToEmployee"]["leaveConsumed"] }}</span>
                                        </h3>
                                    </div>
                                    <div class="col-lg-12 pl-0">
                                        <h3 class="card-title">
                                            <span class="card-label text-dark-75">Left - {{ $data["reportToEmployee"]["leaveLeft"] }}</span>
                                        </h3>
                                    </div>
                                </div>
                                <!--end::Header-->
                            </div>
                            <!--end::Stats Widget 1-->
                        </div>
                        <div class="col-xxl-4 dashboard-card">
                            <!--begin::Stats Widget 1-->
                            <div class="card card-custom card-stretch gutter-b">
                                <!--begin::Header-->
                                <div class="row">
                                    <div class="col-lg-6">
                                        <div class="card-header border-0 pt-6">
                                            <h3 class="card-title">
                                                <span class="card-label text-dark-75" style="font-size: 1.4rem">SALARY</span>
                                            </h3>
                                            <div class="col-lg-12 pl-0" style="font-size: 1rem">
                                                @foreach($data["reportToEmployee"]["salary"] as $salary)
                                                    <h2 class="card-title1" style="font-size: 1rem; padding: 0px !important;">
                                                        <a href="{{ route('salary.generatePaySlip', ['salary' => $salary->id]) }}"
                                                           class="card-label">{{ date('F', mktime(0, 0, 0, $salary->month, 10)) }} {{ $salary->year }}
                                                        </a>
                                                    </h2>
                                                @endforeach

                                                <div class="row" style="margin-top: -5px !important;">
                                                    <div class="col-lg-4"></div>
                                                    <div class="col-lg-8">
                                                        @if(count($data["reportToEmployee"]["salary"]) > 0)
                                                            <a href="{{ route('salary.paySlip') }}" class="text-primary ml-10">More</a>
                                                        @endif
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-lg-6">
                                        <div class="card-header border-0 pt-6">
                                            <h3 class="card-title">
                                                <span class="card-label text-dark-75" style="font-size: 1.4rem">BONUS</span>
                                            </h3>
                                            <div class="col-lg-12 pl-0" style="font-size: 1rem">
                                                @isset($data["reportToEmployee"]["bonus"])
                                                    @foreach($data["reportToEmployee"]["bonus"] as $bonus)
                                                        <h2 class="card-title1" style="font-size: 1rem; padding: 0px !important;">
                                                            <a href="{{ route('user-bonus.generatePaySlip', ['userBonus' => $bonus->id]) }}" class="card-label">
                                                                {{ date('F', mktime(0, 0, 0, $bonus->month, 10)) }} {{ $bonus->year }}
                                                            </a>
                                                        </h2>
                                                    @endforeach
                                                @endisset
                                                <div class="row" style="margin-top: 5px !important;">
                                                    <div class="col-lg-4"></div>
                                                    <div class="col-lg-8">
                                                        @if(count($data["reportToEmployee"]["salary"]) > 0)
                                                            <a href="{{ route('user-bonus.paySlip') }}" class="text-primary ml-10">More</a>
                                                        @endif
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <!--end::Header-->
                            </div>
                            <!--end::Stats Widget 1-->
                        </div>

                        @if(time() < strtotime($data["mealRequestEndTime"]) OR strtotime(\Carbon\Carbon::parse($data["mealRequestEndTime"])->addHours(6)->format('H:i:s')) < time())
                            {{--<div class="col-xxl-4 dashboard-card">
                                <!--begin::Stats Widget 1-->
                                <div class="card card-custom card-stretch gutter-b">
                                    <!--begin::Header-->
                                    <div class="card-header border-0 pt-6">
                                        <h3 class="card-title">
                                            @if(optional(auth()->user()->meal)->status == 1)
                                                <span class="card-label text-dark-75" style="font-size: 1.75rem">Cancel Today's Meal</span>
                                            @else
                                                <span class="card-label text-dark-75" style="font-size: 1.75rem">Request Meal for Today</span>
                                            @endif
                                        </h3>
                                        <div class="col-lg-12 d-flex justify-content-center pl-0 mt-5 align-items-center">
                                            <span class="mr-2">No</span>
                                            <span class="switch switch-outline switch-icon switch-primary">
                                <label>
                                    <input type="checkbox"
                                           @if(optional(auth()->user()->meal)->status == 1)
                                           {{ (optional(auth()->user()->dailyMeal)->status == 0 AND auth()->user()->dailyMeal != NULL) ? 'checked' : '' }}
                                           @else
                                           {{ optional(auth()->user()->dailyMeal)->status == 1 ? 'checked' : '' }}
                                           @endif
                                           name="status" id="{{ auth()->user()->id }}" onclick="changeStatus({{ auth()->user()->id }}, {{ optional(auth()->user()->dailyMeal)->status  }})"/>
                                    <span></span>
                                </label>
                            </span>
                                            <span class="ml-2">Yes</span>
                                        </div>
                                    </div>
                                    <!--end::Header-->
                                </div>
                                <!--end::Stats Widget 1-->
                            </div>--}}

                            {{-- @elseif(\Carbon\Carbon::now()->format('H:i:s') > "17:00:00" AND \Carbon\Carbon::now()->format('H:i:s') < "23:59:59")
                                <div class="col-xxl-4 dashboard-card">
                                    <!--begin::Stats Widget 1-->
                                    <div class="card card-custom card-stretch gutter-b">
                                        <!--begin::Header-->
                                        <div class="card-header border-0 pt-6">
                                            <h3 class="card-title">
                                                <span class="card-label text-dark-75" style="font-size: 1.75rem">TOMORROW'S MEAL STATUS</span>
                                            </h3>
                                            <div class="col-lg-12 d-flex justify-content-center pl-0 mt-5">
                                            <span class="switch switch-outline switch-icon switch-primary">
                                                <label>

                                                    <input type="checkbox" {{ optional(auth()->user()->tomorrowMeal)->status === \App\Models\UserMeal::STATUS_ACTIVE ? 'checked' : '' }}
                                                    name="status" id="{{ auth()->user()->id }}" onclick="changeTomorrowStatus({{ auth()->user()->id }}, {{ optional(auth()->user()->dailyMeal)->status  }})"/>
                                                    <span></span>
                                                </label>
                                            </span>
                                            </div>
                                        </div>
                                        <!--end::Header-->
                                    </div>
                                    <!--end::Stats Widget 1-->
                                </div> --}}
                        @endif
                    </div>
                    {{-- EMPLOYEE LEAVE REQUEST --}}
                    @if(count($data["reportToEmployee"]["leaveRequests"]) > 0)
                        <div class="row">
                            <div class="col-xxl-12">
                                <!--begin::List Widget 7-->
                                <div class="card card-custom card-stretch gutter-b">
                                    <!--begin::Header-->
                                    <div class="card-header border-0 pt-7">
                                        <h3 class="card-title align-items-start flex-column">
                                            <span class="card-label font-weight-bold font-size-h4 text-dark-75">MY LEAVE REQUEST</span>
                                            <span class="text-muted mt-3 font-weight-bold font-size-sm">EMPLOYEE LEAVE REQUEST</span>
                                        </h3>
                                    </div>

                                    <!--end::Header-->
                                    <!--begin::Body-->
                                    <div class="card-body pt-0 pb-4">
                                        <div class="card-body">
                                            <table class="table table-responsive-lg" id="employeeLeaveRequest">
                                                <thead class="custom-thead">
                                                <tr>
                                                    <th scope="col">Requested Leave</th>
                                                    <th scope="col">Status</th>
                                                    <th scope="col">Action</th>
                                                </tr>
                                                </thead>
                                                <tbody>
                                                @foreach($data["reportToEmployee"]["leaveRequests"] as $leaveRequest)
                                                    <tr>
                                                        <td>{{ date("M d, Y", strtotime($leaveRequest->from_date)) }}</td>
                                                        <td>{{ date("M d, Y", strtotime($leaveRequest->to_date)) }}</td>
                                                        <td>
                                                            @if($leaveRequest->status == \App\Models\LeaveRequest::STATUS_PENDING)
                                                                <a href="{{ route('apply-for-leave.edit', ['applyForLeave' => $leaveRequest->id]) }}">
                                                                    <i class="fa fa-edit" style="color: green"></i>
                                                                </a>
                                                            @endif
                                                        </td>
                                                    </tr>
                                                @endforeach
                                                </tbody>
                                            </table>
                                        </div>
                                        <!--end::Body-->
                                    </div>
                                    <!--end::List Widget 7-->
                                </div>
                            </div>
                        </div>
                    @endif

                    {{-- Employee Unpaid Leave --}}
                    @if($data["reportToEmployee"]["unpaidLeaves"]->count() > 0)
                        <div class="row">
                            <div class="col-xxl-12">
                                <!--begin::List Widget 7-->
                                <div class="card card-custom card-stretch gutter-b">
                                    <!--begin::Header-->
                                    <div class="card-header border-0 pt-7">
                                        <h3 class="card-title align-items-start flex-column">
                                            <span class="card-label font-weight-bold font-size-h4 text-dark-75">My Unpaid Leave</span>
                                            <span class="text-muted mt-3 font-weight-bold font-size-sm">Employee Unpaid Leave</span>
                                        </h3>
                                    </div>

                                    <!--end::Header-->
                                    <!--begin::Body-->
                                    <div class="card-body pt-0 pb-4">
                                        <div class="card-body">
                                            <table class="table table-responsive-lg" id="employeeUnpaidLeave">
                                                <thead class="custom-thead">
                                                <tr>
                                                    <th scope="col">Leave Date</th>
                                                    <th scope="col">Action</th>
                                                </tr>
                                                </thead>
                                                <tbody>
                                                @foreach($data["reportToEmployee"]["unpaidLeaves"] as $unpaidLeave)
                                                    <tr>
                                                        <td>{{ date("M d, Y", strtotime($unpaidLeave->leave_date)) }}</td>
                                                        <td>
                                                            <a href="{{ route('apply-for-leave.create', ['date' => $unpaidLeave->leave_date]) }}" class="btn btn-primary btn-sm">Apply for Leave</a>
                                                        </td>
                                                    </tr>
                                                @endforeach
                                                </tbody>
                                            </table>
                                        </div>
                                        <!--end::Body-->
                                    </div>
                                    <!--end::List Widget 7-->
                                </div>
                            </div>
                        </div>
                    @endif

                    {{-- Daily Attendance --}}
                    @if(!is_null($data["reportToEmployee"]["attendances"]))
                        @if(count($data["reportToEmployee"]["attendances"]->timeInThisMonth) > 0)
                            <div class="row">
                                <div class="col-xxl-12">
                                    <!--begin::List Widget 7-->
                                    <div class="card card-custom card-stretch gutter-b">
                                        <!--begin::Header-->
                                        <div class="card-header border-0 pt-7">
                                            <h3 class="card-title align-items-start flex-column">
                                                <span class="card-label font-weight-bold font-size-h4 text-dark-75">DAILY ATTENDANCE</span>
                                                <span class="text-muted mt-3 font-weight-bold font-size-sm">EMPLOYEE DAILY ATTENDANCE</span>
                                            </h3>
                                        </div>
                                        <!--end::Header-->
                                        <!--begin::Body-->
                                        <div class="card-body pt-0 pb-4">
                                            <div class="card-body">
                                                <table class="table table-responsive-lg" id="employeeAttendance">
                                                    <thead class="custom-thead">
                                                    <tr>
                                                        <th scope="col">Month</th>
                                                        <th scope="col">Time In</th>
                                                        <th scope="col">Time Out</th>
                                                    </tr>
                                                    </thead>
                                                    <tbody>
                                                    @if(!is_null($data["reportToEmployee"]["attendances"]))
                                                        @foreach($data["reportToEmployee"]["attendances"]->timeInThisMonth as $key => $attendance)
                                                            <tr>
                                                                <td>{{ date("M jS, Y", strtotime($data["reportToEmployee"]["attendances"]->timeInThisMonth[$key]->punch_time)) }}</td>
                                                                <td>{{ date("h:i:s A", strtotime($data["reportToEmployee"]["attendances"]->timeInThisMonth[$key]->punch_time)) }}</td>
                                                                <td>
                                                                    @if($data["reportToEmployee"]["attendances"]->timeInThisMonth[$key]->punch_time != $data["reportToEmployee"]["attendances"]->timeOutThisMonth[$key]->punch_time)
                                                                        {{ date("h:i:s A", strtotime($data["reportToEmployee"]["attendances"]->timeOutThisMonth[$key]->punch_time)) }}
                                                                    @else
                                                                        -----
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
                        @endif
                    @endif
                </div>
                <div class="tab-pane fade" id="admin_dashboard_for_employee" role="tabpanel" aria-labelledby="admin_dashboard_for_employee">
                    {{-- Admin Dashboard --}}
                    @if(isset($data["reportToAdmin"]))
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
                                            <a href="{{ ($data["reportToAdmin"]["totalEmployees"] > 0) ? route('employee.index') : '#' }}">{{ $data["reportToAdmin"]["totalEmployees"] }}</a>
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
                                            <a href="{{ $data["reportToAdmin"]["inLeaveToday"] > 0 ? route('dashboard-admin.inLeaveToday') : '#' }}">
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
                                            <a href="{{ $data["reportToAdmin"]["inLeaveTomorrow"] > 0 ? route('dashboard-admin.inLeaveTomorrow') : '#' }}">
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
                                            <a href="{{ $data["reportToAdmin"]["presentToday"] > 0 ? route('dashboard-admin.todayPresent') : '#' }}">
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
                                            <a href="{{ $data["reportToAdmin"]["absentToday"] > 0 ? route('dashboard-admin.todayAbsent') : '#' }}">
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
                                            <a href="{{ $data["reportToAdmin"]["lateToday"] > 0 ? route('dashboard-admin.todayLate') : '#' }}">
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
                    @if(count($data["reportToAdmin"]["leaveRequests"]) > 0)
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
                    @endif

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

                                    <form action="">
                                        <select class="form-control select w-100" name="department_id" id="department_id" style="height: 30px;">
                                            @foreach($data["departments"] as $department)
                                                <option value="{{ $department->id }}">{{ $department->name }}</option>
                                            @endforeach
                                        </select>
                                        <button class="btn btn-sm btn-primary">Filter</button>
                                    </form>
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
                                                                <img src='{{ asset("photo/".$attendance->fingerprint_no.".jpg") }}' onerror="this.onerror=null; this.src='{{ asset('assets/media/svg/avatars/001-boy.svg')}}';" width="110" />
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
                                                        <td>{{ date('M d, Y', strtotime($attendance->timeInToday->punch_time)) }}</td>
                                                    </tr>
                                                @endforeach
                                            @endif
                                            </tbody>
                                        </table>
                                        <div class="d-flex">
                                            <div class="ml-auto">
                                                {{ $data["reportToAdmin"]["attendances"]->withQueryString()->links() }}
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <!--end::Body-->
                            </div>
                            <!--end::List Widget 7-->
                        </div>
                    </div>
                    @endif
                </div>
            </div>
        @endif
    @else
        <div class="row">
            <div class="col-6 offset-3">
                <h1 class="align-content-center">{{ $data["error"] }}</h1>
            </div>
        </div>
    @endif
@endsection

@section('footer-js')
    <script type="text/javascript" src="{{ asset('assets/js/widget.js') }}"></script>
    <script type="text/javascript">
        function changeStatus(user_id, status) {
            let url = "{{ route('meal.changeDailyMealStatus') }}";
            let checkBox = document.getElementById(user_id);
            var status = 0;

            if(checkBox.checked) {
                status = 1;
            }

            $.post(url, {user_id: user_id, status: status}, function (response, status) {
                console.log(response.message);
                if(status === "success") {
                    swal.fire({
                        title: response.message
                    })
                }
            })
        }

        function changeTomorrowStatus(user_id, status) {
            let url = "{{ route('meal.changeDailyMealTomorrowStatus') }}";
            let checkBox = document.getElementById(user_id);
            var status = 0;

            if(checkBox.checked) {
                status = 1;
            }

            $.post(url, {user_id: user_id, status: status}, function (response, status) {
                if(status === "success") {
                    swal.fire({
                        title: "Status updated successfully!!"
                    })
                }
            })
        }

        $(document).ready(function () {
            // Admin Dashboard
            $("#leaveRequestToAdmin").DataTable();
            $("#attendanceToAdmin").DataTable({
                "bInfo" : false,
                "bPaginate" : false,
                "order": [5, "desc"],
                paging: false,
                info: false,
                searching: false
            });

            // Supervisor Dashboard
            $("#leaveRequestToSupervisor").DataTable();
            $("#attendanceToSupervisor").DataTable({
                "order": [5, "desc"]
            });
            $("#employeeUnpaidLeave").DataTable();

            // Employee Dashboard
            $("#employeeAttendance").DataTable({
                "ordering": false
            });
            $("#employeeLeaveRequest").DataTable();
            $("#employeeUnpaidLeave").DataTable();

            $("select").select2({
                theme: "classic",
            });
        });
    </script>
@endsection
