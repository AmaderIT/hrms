@php
    $checkedInToday = null;
    $onlineAttendanceInfo = Functions::getOnlineAttendanceInfo();
    $checkInText = 'Online Check In';
    $checkOutText = 'Online Check Out';

    if($onlineAttendanceInfo['late_checkout']){
        $checkedInToday = \App\Models\OnlineAttendance::select('id', 'time_in', 'time_out', 'status')->where(['date' => date('Y-m-d', strtotime('-1 days')), 'user_id' => auth()->id(), ['status', '!=', \App\Models\OnlineAttendance::REJECTED]])->first();
    }else{
        $checkedInToday = \App\Models\OnlineAttendance::select('id', 'time_in', 'time_out', 'status')->where(['date' => date('Y-m-d'), 'user_id' => auth()->id(), ['status', '!=', \App\Models\OnlineAttendance::REJECTED]])->first();
    }
@endphp
@extends('layouts.app')
@section('top-css')
    <link href='{{ asset('assets/css/full_calendar_main.css') }}' rel='stylesheet'/>
    <style type="text/css">
        #calendar.fc .fc-toolbar.fc-header-toolbar {
            margin-bottom: 1em;
        }
        #calendar td.rosterEvent .bysl-more-link {
            display: none !important;
        }
        #calendar td.relaxDay.rosterEvent .bysl-more-link,
        #calendar td.rosterEvent.dayWithEvent .bysl-more-link {
            display: inherit !important;
        }
        #calendar td.dayWithEvent .bysl-weekend-day,
        #calendar td.relaxDay .bysl-relax-day {
            background-color: transparent !important;
            border-width: 0 !important;
        }
        #calendar .fc-toolbar-title {
            padding: 0 !important;
            margin: 0 !important;
            font-size: 15px !important;
            text-transform: uppercase;
            letter-spacing: 1.5;
        }
        #calendar.fc .fc-button {
            padding: 0.275em 0.6em;
            font-size: 1em;
            line-height: 1;
            text-transform: capitalize;
        }
        #calendar.fc .fc-button .fc-icon {
            font-size: 1.1em;
        }

        .fc-theme-standard tr th:last-child, .fc-theme-standard tr td:last-child {
            border-right: none;
        }
        .fc-daygrid-day-bottom {
            margin-top: -20px !important;
        }
        .event-info {
            display: flex;
            gap: 0.75em;
            padding: 0.2rem 2.25rem 1.5rem;
            font-size: 0.9em;
        }
        @media only screen and (max-width: 510px)  {
            .event-info {
                display: grid;
            }
        }
        .workslot-list {
            flex: 1 1 65%;
            display: flex;
            flex-direction: column;
            gap: 2px;
            line-height: 1.2;
        }
        .workslot-list .badge, .upcoming-holiday .badge {
            white-space: inherit;
        }
        .upcoming-holiday {
            display: flex;
            flex-direction: column;
            gap: 2px;
            flex: 0 1 35%;
        }
        .upcoming-holiday > div {
            display: flex;
            justify-content: space-between;
            vertical-align: middle;
            align-items: center;
            flex-flow: row wrap;
        }
        .upcoming-holiday > div > span {
            background-color: red;
            opacity: 0.7;
            color: #fff;
            padding: 0 0.3em 0 0.4em;
            line-height: 1.2;
            border-radius: 5px;
        }
        .fc .fc-button-primary {
            color: var(--fc-button-text-color, #fff) !important;
            background-color: #3699ff !important;
            border-color: #3699ff !important;
        }

        .fc .fc-button-primary:not(:disabled):active, .fc .fc-button-primary:not(:disabled).fc-button-active {
            color: var(--fc-button-text-color, #fff) !important;
            background-color: #3699ff !important;
            border-color: #3699ff !important;
        }

        a.fc-col-header-cell-cushion {
            color: #3f4254 !important;
        }

        .fc-daygrid-day-top a {
            color: #3f4254 !important;
        }

        .fc-daygrid-day-top a {
            color: #121314 !important;
        }

        .requested-leave-summary-card {
            border: 1px dashed #3699ff;
            padding-top: 15px;
        }

        .list-group-item.active {
            z-index: 0;
            color: #1c1a1a;
            background-color: #ffffff;
            border-color: #ffffff;
            font-weight: bold;
        }

        .list-group-item {
            position: relative;
            display: block;
            padding: 0.25rem 0.25rem !important;
            background-color: #ffffff;
            border: 1px solid #afb8c1;
            border-radius: 5px;
        }

        .requested-leave-summary-card-font-weight {
            font-weight: bold;
        }

        .fc-scroller.fc-scroller-liquid-absolute {
            overflow: visible !important;
        }

        .activeDay {
            background-color: #ff0000 !important;
        }

        .fc-daygrid-day-frame.fc-scrollgrid-sync-inner {
            /*cursor: pointer !important;*/
        }

        .fc-event-container {
            display: none;
        }

        .fc-day-top {
            border-color: solid green 3px;
        }

        .fc-today {
            background: #ffffa1 !important;
        }

        .fc-highlight {
            /*background: red !important;*/
            /*border: 1px solid red !important;*/
        }

        .fc-row .fc-content-skeleton td, .fc-row .fc-helper-skeleton td {
            border-color: inherit !important;
        }

        #calendar .dayWithEvent {
            background: rgba(255, 0, 0, 0.6) !important;
            cursor: pointer;
        }
        #calendar .dayWithEvent .bysl-weekend-day {
            background-color: transparent !important;
        }

        #calendar.fc .fc-bg-event {
            background-color: transparent;
        }
        #calendar .fc-daygrid-bg-harness .fc-bg-event {
            opacity: 1;
        }
        .fc-day-today.rosterEvent, .fc-day-today.dayWithEvent {
            outline-color: yellow !important;
            outline-offset: -3px;
            outline-style: double;
        }
        .relaxDay {
            background: rgb(143, 223, 130) !important;
            cursor: pointer;
        }

        .change-bg {
            background-color: darkgrey !important;
        }
        .fc .fc-button:focus {
            box-shadow: none !important;
        }

        .fc .fc-button-primary:focus {
            box-shadow: none !important;
        }

        .fc-popover {
            display: none !important;
        }

        @-moz-document url-prefix() {
            .fc .fc-scrollgrid-liquid {
                height: 87% !important;
            }
            .fc-scroller {
                overflow: unset !important;
            }

            .fc .fc-scrollgrid, .fc .fc-scrollgrid table {
                width: 100% !important;
                table-layout: fixed !important;
            }

            .fc-daygrid-body.fc-daygrid-body-balanced {
                width: initial !important;
            }

            .fc .fc-scrollgrid, .fc .fc-scrollgrid table {
                width: 100% !important;
                table-layout: fixed !important;
            }
            .fc-view-harness.fc-view-harness-active {
                height: 221.963px !important;
            }
        }

        .custom-upcoming-holiday {
            width: 275px;
            margin: 0 auto;
            height: auto;
        }

        .fc-theme-standard td, .fc-theme-standard th {
            border: 1px solid var(--fc-border-color, #ddd);
            /*border: none !important;*/
            /*border: 1px solid #f1f1f5 !important;*/
            border-radius: 1px !important;
        }

        table.fc-scrollgrid-sync-table {
            width: 100% !important;
        }

        .fc-scroller {
            overflow: unset !important;
            border: 1px solid #afb8c1;
            border-radius: 5px;
        }

        .fc-daygrid-body.fc-daygrid-body-balanced {
            width: 100% !important;
        }

        table.fc-col-header {
            width: 100% !important;
        }

        .fc-theme-standard .fc-scrollgrid {
            border: none !important;
        }

        .fc .fc-scrollgrid table {
            border-top-style: hidden !important;
            border-left-style: hidden !important;
            border-right-style: hidden !important;
            border-bottom-style: hidden !important;
        }

        .fc-daygrid-day-frame.fc-scrollgrid-sync-inner {
            border: none !important;
            /*border: 1px solid #e5e9ed;*/
            /*border: 1px solid #cbd0d5;*/
            /*border: 1px solid #e5e9ed;*/
            border-radius: 5px;
        }

        .fc-view-harness.fc-view-harness-active {
            height: 221.963px !important;
            border-radius: 5px;
        }

        /*.fc-scrollgrid-sync-inner {
            border: 1px solid #f7fbff00;
            border-radius: 1px;
        }*/
        .fc .fc-toolbar-title {
            padding: 2px !important;
        }

        .fc .fc-toolbar-title {
            font-size: 1.75em !important;
        }

        th.fc-col-header-cell.fc-day {
        }

        .fc-list.fc-list-sticky.fc-listYear-view.fc-view {
            overflow: hidden auto !important;
        }
        .swal2-icon.swal2-warning.swal2-icon-show {
            display: none !important;
        }
        .bysl-events-dots {
            position: absolute;
            bottom: 8px;
            left: 10px;
        }
        .bysl-events-dots i {
            font-size: .5em;
            color: #3699FF;
        }
        .fc-daygrid-event-harness {
            visibility: hidden !important;
            top: 0 !important;
            left: 0 !important;
            right: 0 !important;
        }
    </style>
@endsection
@section('content')
    @if(!isset($data["error"]))
        <ul class="nav nav-tabs nav-tabs-line">
            @if(isset($data["reportToEmployee"]) && auth()->user()->can("Show Employee Dashboard"))
                <li class="nav-item">
                    <a class="nav-link active" data-toggle="tab" href="#my_dashboard_for_employee">
                        <span class="nav-icon"><i class="flaticon2-dashboard"></i></span>
                        <span class="nav-text">My Dashboard</span>
                    </a>
                </li>
            @endif
            @if(isset($data["reportToSupervisor"]) && auth()->user()->can("Show Supervisor Dashboard"))
                <li class="nav-item">
                    <a class="nav-link" data-toggle="tab" href="#supervisor_dashboard_for_employee">
                        <span class="nav-icon"><i class="flaticon2-dashboard"></i></span>
                        <span class="nav-text">Supervisor Dashboard</span>
                    </a>
                </li>
            @endif
            @if(isset($data["reportToAdmin"]) && auth()->user()->can("Show Admin Dashboard"))
                <li class="nav-item">
                    <a class="nav-link" data-toggle="tab" href="#admin_dashboard_for_employee">
                        <span class="nav-icon"><i class="flaticon2-pie-chart-4"></i></span>
                        <span class="nav-text">Admin Dashboard</span>
                    </a>
                </li>
            @endif
        </ul>
        <div class="tab-content mt-5" id="myTabContentEmployee">
            @if(isset($data["reportToEmployee"]) && auth()->user()->can("Show Employee Dashboard"))
                <div class="tab-pane fade show active" id="my_dashboard_for_employee" role="tabpanel"
                     aria-labelledby="my_dashboard_for_employee">
                    <div class="row">
                        <div class="col-xxl-7">

                            <div class="row">

                                <div class="col-xxl-6 dashboard-card">
                                    <div class="card card-custom card-stretch gutter-b">
                                        <div class="card-header border-0 pt-6">
                                            <h2 class="card-title">
                                            <span class="card-label text-dark-75"
                                                  style="font-size: 15px">TODAY TIME IN</span>
                                                <span class="card-label text-dark-75" style="font-size: 20px">
                                                <div class="py-0 pl-4 text-primary">
                                                    @if(empty($data["reportToEmployee"]["today_emp_attendance"][date('Y-m-d')]['time_in'])) {{'-----'}} @else {{$data["reportToEmployee"]["today_emp_attendance"][date('Y-m-d')]['time_in']}} @endif
                                                </div>
                                            </span>
                                            </h2>
                                            <h3 class="card-title mt-0">
                                                <span class="card-label text-dark-75"
                                                      style="font-size: 15px">TODAY TIME OUT</span>
                                                <span class="card-label text-dark-75" style="font-size: 20px">
                                                    <div class="py-0 pl-4 text-primary">
                                                        @if(empty($data["reportToEmployee"]["today_emp_attendance"][date('Y-m-d')]['time_out'])) {{'-----'}} @else {{$data["reportToEmployee"]["today_emp_attendance"][date('Y-m-d')]['time_out']}} @endif
                                                    </div>
                                                </span>
                                            </h3>
                                        </div>
                                        @can("Online Attendance Feature")
                                            <div class="card-header border-0 pt-6">
                                                <form action="{{ route('attendance.online-attendance') }}" method="POST">
                                                    @csrf
                                                    @if($checkedInToday)
                                                        @if($checkedInToday->status == \App\Models\OnlineAttendance::PENDING)
                                                            <button type="submit" class="btn btn-sm btn-light-primary font-weight-bolder py-3 px-6">{{ $checkOutText }}</button>
                                                        @endif
                                                    @elseif($onlineAttendanceInfo['checkin_time'])
                                                        <button type="submit" class="btn btn-sm btn-light-primary font-weight-bolder py-3 px-6">{{ $checkInText }}</button>
                                                    @endif
                                                </form>
                                            </div>
                                        @endcan
                                    </div>
                                </div>

                                <div class="col-xxl-6 dashboard-card">
                                    <div class="card card-custom card-stretch gutter-b">
                                        <div class="card-header border-0 pt-6">
                                            <h3 class="card-title">
                                                <span class="card-label text-dark-75" style="font-size: 15px">TOTAL LATE OF THIS MONTH</span>
                                            </h3>
                                        </div>
                                        <div
                                            class="card-body d-flex align-items-center justify-content-between pt-0 flex-wrap">
                                            <div class="progress-vertical w-350px ml-25">
                                                <div class="display2 py-0 pl-25 text-primary">{{ $data["reportToEmployee"]["totalLateThisMonth"] }}</div>
                                            </div>
                                        </div>
                                    </div>
                                </div>


                            </div>

                            <div class="row">
                                <div class="col-xxl-6 dashboard-card">
                                    <div class="card card-custom card-stretch gutter-b">
                                        <div class="card-header border-0 pt-6">
                                            <h3 class="card-title">
                                                <span class="card-label text-dark-75"
                                                      style="font-size: 1.75rem">LEAVE</span>
                                            </h3>
                                            <div class="col-lg-12 pl-0">
                                                <h3 class="card-title">
                                            <span
                                                class="card-label text-dark-75">Total Leave - {{ $data["reportToEmployee"]["leaveTotal"] }}</span>
                                                </h3>
                                            </div>
                                            <div class="col-lg-12 pl-0">
                                                <h3 class="card-title">
                                            <span
                                                class="card-label text-dark-75">Consumed - {{ $data["reportToEmployee"]["leaveConsumed"] }}</span>
                                                </h3>
                                            </div>
                                            <div class="col-lg-12 pl-0">
                                                <h3 class="card-title">
                                            <span
                                                class="card-label text-dark-75">Left - {{ $data["reportToEmployee"]["leaveLeft"] }}</span>
                                                </h3>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-xxl-6 dashboard-card">
                                    <div class="card card-custom card-stretch gutter-b">
                                        <div class="row">
                                            <div class="col-lg-6">
                                                <div class="card-header border-0 pt-6">
                                                    <h3 class="card-title">
                                                <span class="card-label text-dark-75"
                                                      style="font-size: 1.4rem">SALARY</span>
                                                    </h3>
                                                    <div class="col-lg-12 pl-0" style="font-size: 1rem">
                                                        @foreach($data["reportToEmployee"]["salary"] as $salary)
                                                            <h2 class="card-title1"
                                                                style="font-size: 1rem; padding: 0px !important;">
                                                                <a onclick="viewPaySlipAlert('{{ route('salary.generatePaySlip', ['salary' => $salary->uuid]) }}')" href="#"
                                                                   class="card-label">{{ date('F', mktime(0, 0, 0, $salary->month, 10)) }} {{ $salary->year }}
                                                                </a>
                                                            </h2>
                                                        @endforeach

                                                        <div class="row" style="margin-top: -5px !important;">
                                                            <div class="col-lg-4"></div>
                                                            <div class="col-lg-8">
                                                                @if(count($data["reportToEmployee"]["salary"]) > 3)
                                                                    <a onclick="viewPaySlipAlert('{{ route('salary.paySlip') }}')" href="#"
                                                                       class="text-primary ml-10">More</a>
                                                                @endif
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="col-lg-6">
                                                <div class="card-header border-0 pt-6">
                                                    <h3 class="card-title">
                                                <span class="card-label text-dark-75"
                                                      style="font-size: 1.4rem">BONUS</span>
                                                    </h3>
                                                    <div class="col-lg-12 pl-0" style="font-size: 1rem">
                                                        @isset($data["reportToEmployee"]["bonus"])
                                                            @foreach($data["reportToEmployee"]["bonus"] as $bonus)
                                                                <h2 class="card-title1"
                                                                    style="font-size: 1rem; padding: 0px !important;">
                                                                    <a onclick="viewPaySlipAlert('{{ route('user-bonus.generatePaySlip', ['userBonus' => $bonus->uuid]) }}')" href="#"
                                                                       class="card-label">
                                                                        {{--{{ date('F', mktime(0, 0, 0, $bonus->month, 10)) }} {{ $bonus->year }}--}}
                                                                        {{ $bonus->bonus->festival_name?? '' }}
                                                                    </a>
                                                                </h2>
                                                            @endforeach
                                                        @endisset
                                                        <div class="row" style="margin-top: 5px !important;">
                                                            <div class="col-lg-4"></div>
                                                            <div class="col-lg-8">
                                                                @if(isset($data["reportToEmployee"]["bonus"]) && count($data["reportToEmployee"]["bonus"]) > 3)
                                                                    <a onclick="viewPaySlipAlert('{{ route('user-bonus.paySlip') }}')" href="#"
                                                                       class="text-primary ml-10">More</a>
                                                                @endif
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>


                            </div>

                        </div>

                        <div class="col-xxl-5">
                            <div class="card card-custom card-stretch gutter-b">
                                <div class="card-header border-0 pt-6">
                                    <div id='calendar' style="width:100%; min-height:250px"></div>
                                </div>
                                <div class="event-info">
                                    <div class="workslot-list"></div>
                                    <div class="upcoming-holiday">
                                        @if(!empty($data["reportToEmployee"]["upcomingHolidays"]) && count($data["reportToEmployee"]["upcomingHolidays"])>0)
                                            @foreach($data["reportToEmployee"]["upcomingHolidays"] as $upcomingHoliday)
                                                <div class="badge badge-secondary">
                                                    {{ $upcomingHoliday->holiday->name }}
                                                    <span>{{ \Carbon\Carbon::createFromDate($upcomingHoliday->from_date->toDateTimeString())->format('M jS') }}
                                                        @if($upcomingHoliday->from_date != $upcomingHoliday->to_date)
                                                            {{ \Carbon\Carbon::createFromDate($upcomingHoliday->to_date->toDateTimeString())->format('M jS') }}
                                                        @endif
                                                    </span>
                                                </div>
                                            @endforeach
                                        @endif
                                    </div>
                                </div>
                            </div>
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
                                            @php
                                                $leaveRequestAction = false;
                                            @endphp
                                            <table class="table table-responsive-lg" id="employeeLeaveRequest">
                                                <thead class="custom-thead">
                                                <tr>
                                                    <th scope="col">Requested Leave</th>
                                                    <th scope="col">From Date</th>
                                                    <th scope="col">To Date</th>
                                                    <th scope="col">Status</th>
                                                </tr>
                                                </thead>
                                                <tbody>
                                                @foreach($data["reportToEmployee"]["leaveRequests"] as $leaveRequest)
                                                    <tr>
                                                        <td>{{ $leaveRequest->purpose ?? "---" }}</td>
                                                        <td>{{ date("M d, Y", strtotime($leaveRequest->from_date)) }}</td>
                                                        <td>{{ date("M d, Y", strtotime($leaveRequest->to_date)) }}</td>
                                                        <td>
                                                            @if($leaveRequest->status == \App\Models\LeaveRequest::STATUS_PENDING)
                                                                Pending
                                                            @elseif($leaveRequest->status == \App\Models\LeaveRequest::STATUS_APPROVED)
                                                                Approved
                                                            @elseif($leaveRequest->status == \App\Models\LeaveRequest::STATUS_REJECTED)
                                                                Rejected
                                                            @elseif($leaveRequest->status == \App\Models\LeaveRequest::STATUS_AUTHORIZED)
                                                                Authorized
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
                                            <span class="card-label font-weight-bold font-size-h4 text-dark-75">My Un-applied Leave</span>
                                            <span class="text-muted mt-3 font-weight-bold font-size-sm">Employee Un-applied Leave</span>
                                        </h3>
                                    </div>

                                    <!--end::Header-->
                                    <!--begin::Body-->
                                    <div class="card-body pt-0 pb-4">
                                        <div class="card-body">
                                            <table class="table table-responsive-lg" id="employeeUnpaidLeave">
                                                <thead class="custom-thead">
                                                <tr>
                                                    <th scope="col" style="width: 50%;">Leave Date</th>
                                                    <th scope="col" style="width: 50%;text-align: right;">Action</th>
                                                </tr>
                                                </thead>
                                                <tbody>
                                                @foreach($data["reportToEmployee"]["unpaidLeaves"] as $unpaidLeave)
                                                    <tr>
                                                        <td>{{ date("M d, Y", strtotime($unpaidLeave->leave_date)) }}</td>
                                                        <td style="text-align: right;">
                                                            @if($unpaidLeave->is_half_day ==1)
                                                                <a href="{{ route('apply-for-leave.create', ['data' => $unpaidLeave->id,]) }}"
                                                                   class="btn btn-primary btn-sm">Apply for Half
                                                                    Leave</a>
                                                            @else
                                                                <a href="{{ route('apply-for-leave.create', ['data' => $unpaidLeave->id]) }}"
                                                                   class="btn btn-primary btn-sm">Apply for Leave</a>
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
                                            @php $attendances = array_reverse($data["reportToEmployee"]["today_emp_attendance"]) @endphp
                                            @foreach($attendances as $key => $attendance)
                                                <tr>
                                                    <td>{{$attendance['date']}}</td>
                                                    <td>@if(empty($attendance['time_in'])) {{'-----'}} @else {{$attendance['time_in']}} @endif</td>
                                                    <td>@if(empty($attendance['time_out'])) {{'-----'}} @else {{$attendance['time_out']}} @endif</td>
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

                </div>
            @endif

            @if(isset($data["reportToAdmin"]) && auth()->user()->can("Show Admin Dashboard"))
                <div class="tab-pane fade" id="admin_dashboard_for_employee" role="tabpanel"
                     aria-labelledby="admin_dashboard_for_employee">
                    {{-- Admin Dashboard --}}
                    @if(isset($data["reportToAdmin"]))
                        <div class="row">
                            <div class="col-xl-3 dashboard-card">
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
                                    <div
                                        class="card-body d-flex align-items-center justify-content-between pt-0 flex-wrap">
                                        <div class="progress-vertical w-350px ml-25">
                                            <div class="display2 py-0 pl-12 text-primary">
                                                <a href="{{ ($data["reportToAdmin"]["totalEmployees"] > 0) ? route('dashboard-admin.employees') : '#' }}">{{ $data["reportToAdmin"]["totalEmployees"] }}</a>
                                            </div>
                                        </div>
                                    </div>
                                    <!--end::Body-->
                                </div>
                                <!--end::Stats Widget 1-->
                            </div>
                            <!-- Start Employee Leave In Yesterday To Admin/HR -->
                        @can("Total In Leave Yesterday")
                            @include('dashboard-notification.card',
                                    [
                                        'card_width' => 3,
                                        'card_key' => 'leaveInYesterday',
                                        'permission_key'=> 'Total In Leave Yesterday',
                                        'card_title' => 'IN LEAVE(YESTERDAY)',
                                        'room' => 'admin'
                                    ])
                        @endcan
                        <!-- End Employee Leave In Yesterday To Admin/HR -->
                            <div class="col-xl-3 dashboard-card">
                                <!--begin::Stats Widget 1-->
                                <div class="card card-custom card-stretch gutter-b">
                                    <!--begin::Header-->
                                    <div class="card-header border-0 pt-6">
                                        <h3 class="card-title">
                                            <span class="card-label text-dark-75" style="font-size: 15px">IN LEAVE(TODAY)</span>
                                        </h3>
                                    </div>
                                    <div
                                        class="card-body d-flex align-items-center justify-content-between pt-0 flex-wrap">
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

                            <div class="col-xl-3 dashboard-card">
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
                                    <div
                                        class="card-body d-flex align-items-center justify-content-between pt-0 flex-wrap">
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
                            <div class="col-xl-3 dashboard-card">
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
                                    <div
                                        class="card-body d-flex align-items-center justify-content-between pt-0 flex-wrap">
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
                            <div class="col-xl-3 dashboard-card">
                                <!--begin::Stats Widget 1-->
                                <div class="card card-custom card-stretch gutter-b">
                                    <!--begin::Header-->
                                    <div class="card-header border-0 pt-6">
                                        <h3 class="card-title">
                                            <span class="card-label text-dark-75"
                                                  style="font-size: 15px">TODAY'S ABSENT</span>
                                        </h3>
                                    </div>
                                    <!--end::Header-->
                                    <!--begin::Body-->
                                    <div
                                        class="card-body d-flex align-items-center justify-content-between pt-0 flex-wrap">
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
                            <div class="col-xl-3 dashboard-card">
                                <!--begin::Stats Widget 1-->
                                <div class="card card-custom card-stretch gutter-b">
                                    <!--begin::Header-->
                                    <div class="card-header border-0 pt-6">
                                        <h3 class="card-title">
                                            <span class="card-label text-dark-75"
                                                  style="font-size: 15px">TODAY'S LATE</span>
                                        </h3>
                                    </div>
                                    <div
                                        class="card-body d-flex align-items-center justify-content-between pt-0 flex-wrap">
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


                            <!-- Start Employee Provisional Duration Notification To Admin/HR -->
                        @can("Provision Expiry Notification")
                            @include('dashboard-notification.card',
                                     [
                                    'card_width' => 3,
                                    'card_key' => 'provision',
                                    'permission_key'=> 'Provision Expiry Notification',
                                    'card_title' => 'PROVISION PERIOD ENDING WITHIN 30 DAYS',
                                    'room' => 'admin'
                                     ])
                        @endcan
                        <!-- End Employee Provisional Duration Notification To Admin/HR -->


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
                                                        <th scope="col">Office ID</th>
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
                                                                <td>{{ $leaveRequest->employee->fingerprint_no }}</td>
                                                                <td>{{ $leaveRequest->employee->name }}</td>
                                                                <td>{{ optional($leaveRequest->employee->currentPromotion->designation)->title }}</td>
                                                                <td>{{ $leaveRequest->employee->currentPromotion->officeDivision->name }}</td>
                                                                <td>{{ $leaveRequest->employee->currentPromotion->department->name }}</td>
                                                                <td>{{ date("M d, Y", strtotime($leaveRequest->from_date)) }}
                                                                    - {{ date("M d, Y", strtotime($leaveRequest->to_date)) }}</td>
                                                                <td>
                                                                    @if($leaveRequest->status == \App\Models\LeaveRequest::STATUS_PENDING)
                                                                        <a href="#"
                                                                           class="btn btn-warning btn-sm font-weight-bold btn-pill">Pending</a>
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
                                            <select class="form-control select w-100" name="department_id"
                                                    id="department_id" style="height: 30px;">
                                                @foreach($data["departments"] as $department)
                                                    <option
                                                        value="{{ $department->id }}">{{ $department->name }}</option>
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
                                                                <div class="symbol flex-shrink-0"
                                                                     style="width: 35px; height: auto">
                                                                    <img
                                                                        src='{{ asset("photo/".$attendance->fingerprint_no.".jpg") }}'
                                                                        onerror="this.onerror=null; this.src='{{ asset('assets/media/svg/avatars/001-boy.svg')}}';"
                                                                        width="110"/>
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
            @endif

            @if(isset($data["reportToSupervisor"]) && auth()->user()->can("Show Supervisor Dashboard"))
                <div class="tab-pane fade" id="supervisor_dashboard_for_employee" role="tabpanel"
                     aria-labelledby="supervisor_dashboard_for_employee">
                    {{-- At a Glance --}}
                    <div class="row">
                        @can('Supervisor Dashboard Total Employee')
                            <div class="col-xl-3 dashboard-card">
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
                                    <div
                                        class="card-body d-flex align-items-center justify-content-between pt-0 flex-wrap">
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
                        @endcan

                    <!-- Start Leave In Yesterday Supervisor Dashboard -->
                        @can("Total In Leave Yesterday")
                            @include('dashboard-notification.card',
                                     [
                                         'card_width' => 3,
                                         'card_key' => 'leaveInYesterday',
                                         'permission_key'=> 'Total In Leave Yesterday',
                                         'card_title' => 'IN LEAVE(YESTERDAY)',
                                         'room' => 'sp-room'
                                     ])
                        @endcan
                    <!-- End Leave In Yesterday Supervisor Dashboard -->

                        @can('Supervisor Dashboard Total In Leave Today')
                            <div class="col-xl-3 dashboard-card">
                                <!--begin::Stats Widget 1-->
                                <div class="card card-custom card-stretch gutter-b">
                                    <!--begin::Header-->
                                    <div class="card-header border-0 pt-6">
                                        <h3 class="card-title">
                                            <span class="card-label text-dark-75" style="font-size: 15px">IN LEAVE(TODAY)</span>
                                        </h3>
                                    </div>
                                    <div
                                        class="card-body d-flex align-items-center justify-content-between pt-0 flex-wrap">
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
                        @endcan
                        @can('Supervisor Dashboard Total In Leave Tomorrow')
                            <div class="col-xl-3 dashboard-card">
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
                                    <div
                                        class="card-body d-flex align-items-center justify-content-between pt-0 flex-wrap">
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
                        @endcan
                        @can('Supervisor Dashboard Today Present')
                            <div class="col-xl-3 dashboard-card">
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
                                    <div
                                        class="card-body d-flex align-items-center justify-content-between pt-0 flex-wrap">
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
                        @endcan
                        @can('Supervisor Dashboard Today Absent')
                            <div class="col-xl-3 dashboard-card">
                                <!--begin::Stats Widget 1-->
                                <div class="card card-custom card-stretch gutter-b">
                                    <!--begin::Header-->
                                    <div class="card-header border-0 pt-6">
                                        <h3 class="card-title">
                                            <span class="card-label text-dark-75"
                                                  style="font-size: 15px">TODAY'S ABSENT</span>
                                        </h3>
                                    </div>
                                    <!--end::Header-->
                                    <!--begin::Body-->
                                    <div
                                        class="card-body d-flex align-items-center justify-content-between pt-0 flex-wrap">
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
                        @endcan
                        @can('Supervisor Dashboard Today Late')
                            <div class="col-xl-3 dashboard-card">
                                <!--begin::Stats Widget 1-->
                                <div class="card card-custom card-stretch gutter-b">
                                    <!--begin::Header-->
                                    <div class="card-header border-0 pt-6">
                                        <h3 class="card-title">
                                            <span class="card-label text-dark-75"
                                                  style="font-size: 15px">TODAY'S LATE</span>
                                        </h3>
                                    </div>
                                    <div
                                        class="card-body d-flex align-items-center justify-content-between pt-0 flex-wrap">
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
                    @endcan


                    <!-- Start Employee Provisional Duration Notification To Supervisor Dashboard -->
                    @can("Provision Expiry Notification")
                        @include('dashboard-notification.card',
                                [
                                    'card_width' => 3,
                                    'card_key' => 'provision',
                                    'permission_key' => 'Provision Expiry Notification',
                                    'card_title' => 'PROVISION PERIOD ENDING WITHIN 30 DAYS',
                                    'room' => 'sp-room'
                                    ])
                    @endcan
                    <!-- End Employee Provisional Duration Notification To Supervisor Dashboard -->
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
                                                    @can('Edit Leave Application')
                                                        <th scope="col">Action</th>
                                                    @endcan
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
                                                            <td>{{ date("M d, Y", strtotime($leaveRequest->from_date)) }}
                                                                - {{ date("M d, Y", strtotime($leaveRequest->to_date)) }}</td>
                                                            <td>
                                                                @if($leaveRequest->status == \App\Models\LeaveRequest::STATUS_PENDING)
                                                                    <a href="#"
                                                                       class="btn btn-warning btn-sm font-weight-bold btn-pill">Pending</a>
                                                                @endif
                                                            </td>
                                                            @can('Edit Leave Application')
                                                                <td>
                                                                    @if(auth()->user()->can("Authorize Leave Requests") AND $leaveRequest->status === \App\Models\LeaveRequest::STATUS_PENDING)
                                                                        <a href="{{ route('requested-application.edit', ['requestedApplication' => $leaveRequest->uuid]) }}"><i
                                                                                class="fa fa-edit"
                                                                                style="color: green"></i></a>
                                                                    @elseif(auth()->user()->can("Approve Leave Requests") AND $leaveRequest->status === \App\Models\LeaveRequest::STATUS_AUTHORIZED)
                                                                        <a href="{{ route('requested-application.edit', ['requestedApplication' => $leaveRequest->uuid]) }}"><i
                                                                                class="fa fa-edit"
                                                                                style="color: green"></i></a>
                                                                    @endif
                                                                </td>
                                                            @endcan
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
                                                                <div class="symbol flex-shrink-0"
                                                                     style="width: 35px; height: auto">
                                                                    <img
                                                                        src='{{ asset("photo/".$attendance->fingerprint_no.".jpg") }}'
                                                                        onerror="this.onerror=null; this.src='{{ asset('assets/media/svg/avatars/001-boy.svg')}}';"
                                                                        width="110"/>
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
                </div>
            @endif
        </div>
    @else
        <div class="row">
            <div class="col-6 offset-3">
                <h1 class="align-content-center">{{ $data["error"] }}</h1>
            </div>
        </div>
    @endif
@endsection

<div id="calendarModal" class="modal fade" role="dialog">
    <div class="modal-dialog">
        <div class="modal-content">

        </div>
    </div>
</div>

@section('footer-js')
    <script src='{{ asset('assets/js/full_calendar_main.js') }}'></script>
    <script type="text/javascript" src="{{ asset('assets/js/widget.js') }}"></script>
    <script type="text/javascript">
        function viewPaySlipAlert(url) {
            let msg = 'Would you like to see the payslip?';
            swal.fire({
                title: '',
                text: msg,
                icon: 'warning',
                buttonsStyling: false,
                showCancelButton: true,
                allowOutsideClick: false,
                customClass: {
                    confirmButton: "btn btn-success",
                    cancelButton: "btn btn-danger"
                },
                cancelButtonText: "<i class='las la-times'></i> No, thanks.",
                confirmButtonText: "<i class='las la-thumbs-up'></i> Yeah, sure!",
            }).then(function(result) {
                if(result.isConfirmed) {
                    $.get(url, {}, function (data, status) {
                        window.location.href = url;
                    })
                }
            })
        }
        function changeStatus(user_id, status) {
            let url = "{{ route('meal.changeDailyMealStatus') }}";
            let checkBox = document.getElementById(user_id);
            var status = 0;

            if (checkBox.checked) {
                status = 1;
            }

            $.post(url, {user_id: user_id, status: status}, function (response, status) {
                console.log(response.message);
                if (status === "success") {
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

            if (checkBox.checked) {
                status = 1;
            }

            $.post(url, {user_id: user_id, status: status}, function (response, status) {
                if (status === "success") {
                    swal.fire({
                        title: "Status updated successfully!!"
                    })
                }
            })
        }

        $(document).ready(function () {
            // Admin Dashboard
            $("#leaveRequestToAdmin").DataTable({"order": false});
            $("#attendanceToAdmin").DataTable({
                "bInfo": false,
                "bPaginate": false,
                "order": [5, "desc"],
                paging: false,
                info: false,
                searching: false
            });

            // Supervisor Dashboard
            $("#leaveRequestToSupervisor").DataTable({"order": false});
            $("#attendanceToSupervisor").DataTable({
                "order": [5, "desc"]
            });
            $("#employeeUnpaidLeave").DataTable({
                "ordering": false
            });

            // Employee Dashboard
            $("#employeeAttendance").DataTable({
                "ordering": false
            });
            $("#employeeLeaveRequest").DataTable({"order": false});
            $("#employeeUnpaidLeave").DataTable();

            $("select").select2({
                theme: "classic",
            });
        });
    </script>
    <script>
        window.mobilecheck = function () {
            var check = false;
            (function (a) {
                if (/(android|bb\d+|meego).+mobile|avantgo|bada\/|blackberry|blazer|compal|elaine|fennec|hiptop|iemobile|ip(hone|od)|iris|kindle|lge |maemo|midp|mmp|mobile.+firefox|netfront|opera m(ob|in)i|palm( os)?|phone|p(ixi|re)\/|plucker|pocket|psp|series(4|6)0|symbian|treo|up\.(browser|link)|vodafone|wap|windows ce|xda|xiino/i.test(a) || /1207|6310|6590|3gso|4thp|50[1-6]i|770s|802s|a wa|abac|ac(er|oo|s\-)|ai(ko|rn)|al(av|ca|co)|amoi|an(ex|ny|yw)|aptu|ar(ch|go)|as(te|us)|attw|au(di|\-m|r |s )|avan|be(ck|ll|nq)|bi(lb|rd)|bl(ac|az)|br(e|v)w|bumb|bw\-(n|u)|c55\/|capi|ccwa|cdm\-|cell|chtm|cldc|cmd\-|co(mp|nd)|craw|da(it|ll|ng)|dbte|dc\-s|devi|dica|dmob|do(c|p)o|ds(12|\-d)|el(49|ai)|em(l2|ul)|er(ic|k0)|esl8|ez([4-7]0|os|wa|ze)|fetc|fly(\-|_)|g1 u|g560|gene|gf\-5|g\-mo|go(\.w|od)|gr(ad|un)|haie|hcit|hd\-(m|p|t)|hei\-|hi(pt|ta)|hp( i|ip)|hs\-c|ht(c(\-| |_|a|g|p|s|t)|tp)|hu(aw|tc)|i\-(20|go|ma)|i230|iac( |\-|\/)|ibro|idea|ig01|ikom|im1k|inno|ipaq|iris|ja(t|v)a|jbro|jemu|jigs|kddi|keji|kgt( |\/)|klon|kpt |kwc\-|kyo(c|k)|le(no|xi)|lg( g|\/(k|l|u)|50|54|\-[a-w])|libw|lynx|m1\-w|m3ga|m50\/|ma(te|ui|xo)|mc(01|21|ca)|m\-cr|me(rc|ri)|mi(o8|oa|ts)|mmef|mo(01|02|bi|de|do|t(\-| |o|v)|zz)|mt(50|p1|v )|mwbp|mywa|n10[0-2]|n20[2-3]|n30(0|2)|n50(0|2|5)|n7(0(0|1)|10)|ne((c|m)\-|on|tf|wf|wg|wt)|nok(6|i)|nzph|o2im|op(ti|wv)|oran|owg1|p800|pan(a|d|t)|pdxg|pg(13|\-([1-8]|c))|phil|pire|pl(ay|uc)|pn\-2|po(ck|rt|se)|prox|psio|pt\-g|qa\-a|qc(07|12|21|32|60|\-[2-7]|i\-)|qtek|r380|r600|raks|rim9|ro(ve|zo)|s55\/|sa(ge|ma|mm|ms|ny|va)|sc(01|h\-|oo|p\-)|sdk\/|se(c(\-|0|1)|47|mc|nd|ri)|sgh\-|shar|sie(\-|m)|sk\-0|sl(45|id)|sm(al|ar|b3|it|t5)|so(ft|ny)|sp(01|h\-|v\-|v )|sy(01|mb)|t2(18|50)|t6(00|10|18)|ta(gt|lk)|tcl\-|tdg\-|tel(i|m)|tim\-|t\-mo|to(pl|sh)|ts(70|m\-|m3|m5)|tx\-9|up(\.b|g1|si)|utst|v400|v750|veri|vi(rg|te)|vk(40|5[0-3]|\-v)|vm40|voda|vulc|vx(52|53|60|61|70|80|81|83|85|98)|w3c(\-| )|webc|whit|wi(g |nc|nw)|wmlb|wonu|x700|yas\-|your|zeto|zte\-/i.test(a.substr(0, 4))) check = true;
            })(navigator.userAgent || navigator.vendor || window.opera);
            return check;
        };
        setTimeout(function () {
            //$('.fc-popover.fc-more-popover').hide();
            $('.fc-more-link').removeClass('fc-more-link');
            $('.fc-daygrid-more-link').removeClass('fc-daygrid-more-link');
        }, 1000);
        $(document).ready(function () {
            $('.fc-next-button,.fc-prev-button').on('click', function () {
                setTimeout(function () {
                    //$('.fc-popover.fc-more-popover').hide();
                    $('.fc-more-link').removeClass('fc-more-link');
                    $('.fc-daygrid-more-link').removeClass('fc-daygrid-more-link');
                }, 1000);
            });
        });

        document.addEventListener('DOMContentLoaded', function () {
            var calendarDays = getDaysStatus(moment().format('YYYY-MM-DD'));
            var calendarEl = document.getElementById('calendar');
            var calendar = new FullCalendar.Calendar(calendarEl, {
                headerToolbar: {
                    left: 'title',
                    right: 'prev,next today'
                },
                customButtons: {
                    prev: {
                        click: function() {
                            calendar.prev();
                            calendarDays = getDaysStatus(moment(calendar.getDate()).format('YYYY-MM-DD'));
                            $(".bysl-events-dots").remove();
                        }
                    },
                    next: {
                        click: function() {
                            calendar.next();
                            calendarDays = getDaysStatus(moment(calendar.getDate()).format('YYYY-MM-DD'));
                            $(".bysl-events-dots").remove();
                        }
                    },
                    today: {
                        text: 'Today',
                        click: function() {
                            calendar.today();
                            calendarDays = getDaysStatus(moment(calendar.getDate()).format('YYYY-MM-DD'));
                            $(".bysl-events-dots").remove();
                        }
                    },
                },
                showNonCurrentDates: false,
                expandRows: false,
                timeZone: 'UTC',
                navLinks: false,
                selectable: false,
                selectMirror: true,
                editable: false,
                eventOverlap: false,
                handleWindowResize: true,
                dayMaxEvents: true,
                // dayCellDidMount: (args) => {
                //     const dayFrame = args.el.querySelector('.fc-daygrid-day-frame');
                //     const dotWrapEl = document.createElement('span');
                //     dotWrapEl.className = 'bysl-events-dots';


                //     setTimeout(function () {
                //         eventCount = calendarDays[moment(args.date).format('YYYY-MM-DD')];
                //         // console.log('jams');
                //         if(eventCount == undefined) return;
                //         for (let index = 1; index <= eventCount; index++) {
                //             const iEl = document.createElement('i');
                //                 iEl.className = 'fa fa-circle';
                //                 dotWrapEl.append(iEl);
                //         }
                //         dayFrame.append(dotWrapEl);
                //     }, 800);

                // },
                dateClick: function (info) {
                    setTimeout(function () {
                        $('.fc-popover.fc-more-popover').hide();
                    }, 500);
                    $.ajax({
                        url: '{{ route('getSpecificDateEvent') }}',
                        type: 'POST',
                        data: {'start': info.dateStr},
                        success: function (data) {
                            try {
                                $.parseJSON(data);
                                $('#calendarModal').modal('hide');
                            } catch (e) {
                                $('#calendarModal').modal('show');
                                $('#calendarModal').find('.modal-content').html(data);
                            }
                        },
                        error: function (xhr, desc, err) {
                            console.log("error");
                        }
                    });
                },
                events: {
                    url: '{{ route("calendar") }}',
                    success: function (data) {
                        let workSlots = data[1]['work_slots'];
                        let html = ''
                        if(workSlots) {
                            Object.entries(workSlots).forEach(([key, item]) => {
                                if(key == 'default') {
                                    html += '<div class="workslot-default-item badge badge-secondary">'+ item.title +' ('+ moment(item.start, 'hh:mm').format('hh:mm A') +'-'+ moment(item.end, 'hh:mm').format('hh:mm A') +')</div>';
                                } else {
                                    html += '<div class="workslot-item badge badge-secondary" style="background-color:'+ item.custom_color +';">'+ item.title +'<span>('+ moment(item.start, 'hh:mm').format('hh:mm A') +'-'+ moment(item.end, 'hh:mm').format('hh:mm A') +')</span></div>';
                                }
                                $('.workslot-list').html(html);
                            });
                        }
                    },
                    failure: function (er) {
                        console.log(er);
                    }
                },
                moreLinkContent: '',
                eventContent: function (arg) {
                    let event = arg.event;
                    let start = event.start, end = event.end, currentDate = new Date(start), eventTitle = event.title;
                    let fetchEventStartDate = '';
                    while (currentDate < end) {
                        fetchEventStartDate = new Date(currentDate);
                        if (eventTitle) {
                            $("td[data-date='" + moment(fetchEventStartDate).format('YYYY-MM-DD') + "']").addClass('dayWithEvent');
                        } else {
                            $("td[data-date='" + moment(fetchEventStartDate).format('YYYY-MM-DD') + "']").addClass('relaxDay');
                        }
                        currentDate.setDate(currentDate.getDate() + 1);
                    }
                },
                loading: function( isLoading ) {
                    if (isLoading == true) {
                    } else {
                        Object.entries(calendarDays).forEach((item) => {
                            const $wrap = $("td[data-date='" + moment(item[0]).format('YYYY-MM-DD') + "']");
                            const dayFrame = $wrap.find('.fc-daygrid-day-frame');
                            const dotWrapEl = document.createElement('span');
                            dotWrapEl.className = 'bysl-events-dots';
                            if(item[1] != undefined && item[1] > 0){
                                for (let index = 1; index <= item[1]; index++) {
                                    const iEl = document.createElement('i');
                                        iEl.className = 'fa fa-circle';
                                        dotWrapEl.append(iEl);
                                }
                            }
                            dayFrame.append(dotWrapEl);
                        });
                    }
                },
                eventDidMount: (arg) => {
                    let eventEx = arg.event.extendedProps;
                    if(eventEx.type != 'undefined') {
                        $("td[data-date='" + eventEx.active_date + "']").addClass('rosterEvent').css("background-color", eventEx.custom_color);
                        $("td[data-date='" + eventEx.active_date + "']").find('.fc-bg-event').css("background-color", eventEx.custom_color);
                        $("td[data-date='" + eventEx.active_date + "']").find('.fc-bg-event').css("opacity", 1);
                    }

                    let parentEl = arg.el.closest('td[role="gridcell"]');

                    if(parentEl == null || parentEl == 'undefined') return;

                    // weekend
                    if(!parentEl.classList.contains('rosterEvent')) {
                        let weekendEl = parentEl.querySelector('.bysl-weekend-day');
                        if (typeof(weekendEl) != 'undefined' && weekendEl != null){
                            weekendEl.style.backgroundColor = 'transparent';
                            parentEl.style.backgroundColor = 'rgba(255, 0, 0, 0.6)';
                        }
                    }

                },
            });
            calendar.render();

        });

        function getDaysStatus(start) {

            let data = [];
            let _data = {start: start}
            $.ajax({
                type: "GET",
                url: '{{ route("calendar-days-status") }}',
                data: _data,
                async: false,
                dataType: "json",
                success: function (res) {
                    data = res;
                },
            });
            return data;
        }

    </script>
@endsection
