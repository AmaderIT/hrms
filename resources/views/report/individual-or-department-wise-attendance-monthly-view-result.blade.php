@extends('layouts.app')

@section("top-css")
    <style>
        #attendanceReportView {
            display: flex;
            font-family: -apple-system;
            font-size: 14px;
            color: #333;
            justify-content: center;
        }

        /*.table-wrapper {*/
        /*    max-width: 1220px;*/
        /*    overflow: scroll;*/
        /*}*/

        table {
            border: 1px solid #ddd;
            border-collapse: collapse;
        }

        td, th {
            white-space: nowrap;
            border: 1px solid #ddd;
            padding: 5px;
        }

        /*th {*/
        /*    background-color: #eee;*/
        /*    position: sticky;*/
        /*    top: -1px;*/
        /*    z-index: 2;*/

        /*&:first-of-type {*/
        /*     left: 0;*/
        /*     z-index: 3;*/
        /* }*/
        /*}*/

        /*tbody tr td:first-of-type, td:nth-of-type(2) {*/
        /*    background-color: #eee;*/
        /*    position: sticky;*/
        /*    left: -1px;*/
        /*    z-index: 1;*/
        /*}*/
    </style>
@endsection

@section('content')
    <div class="card card-custom" id="attendanceReportView">
        <div class="card-header">
            <div class="card-title">
                <h2 class="card-label">Individual OR Department wise Monthly Attendance Report</h2>
            </div>
        </div>

        <div class="card-body">
            <div class="row">
                <div class="col-3">
                    <table class="table">
                        <thead>
                        <tr>
                            <th>In Short Form</th>
                            <th>In elaborate</th>
                        </tr>
                        </thead>
                        <tbody>
                        <tr>
                            <td>P</td>
                            <td>Present</td>
                        </tr>
                        <tr>
                            <td>P - L</td>
                            <td>Present but late</td>
                        </tr>
                        <tr>
                            <td>P - Half</td>
                            <td>Present and half day leave</td>
                        </tr>
                        <tr>
                            <td>P - L - Half</td>
                            <td>Present but late and half day leave</td>
                        </tr>
                        </tbody>
                    </table>
                </div>
                <div class="col-3">
                    <table class="table">
                        <thead>
                        <tr>
                            <th>In Short Form</th>
                            <th>In elaborate</th>
                        </tr>
                        </thead>
                        <tbody>

                        <tr>
                            <td>P - L - W</td>
                            <td>Present but late and weekly holiday</td>
                        </tr>
                        <tr>
                            <td>P - L - H</td>
                            <td>Present but late and public holiday</td>
                        </tr>
                        <tr>
                            <td>P - W</td>
                            <td>Present but weekly holiday</td>
                        </tr>
                        <tr>
                            <td>P - H</td>
                            <td>Present but public holiday</td>
                        </tr>
                        </tbody>
                    </table>
                </div>
                <div class="col-3">
                    <table class="table">
                        <thead>
                        <tr>
                            <th>In Short Form</th>
                            <th>In elaborate</th>
                        </tr>
                        </thead>
                        <tbody>
                        <tr>
                            <td>A</td>
                            <td>Absent</td>
                        </tr>
                        <tr>
                            <td>A - Half</td>
                            <td>Absent but half</td>
                        </tr>
                        <tr>
                            <td>W</td>
                            <td>Weekly holiday</td>
                        </tr>
                        <tr>
                            <td>H</td>
                            <td>Public Holiday</td>
                        </tr>
                        </tbody>
                    </table>
                </div>
                <div class="col-3">
                    <table class="table">
                        <thead>
                        <tr>
                            <th>In Short Form</th>
                            <th>In elaborate</th>
                        </tr>
                        </thead>
                        <tbody>
                        <tr>
                            <td>L</td>
                            <td>Leave</td>
                        </tr>
                        <tr>
                            <td>R</td>
                            <td>Relax Day</td>
                        </tr>
                        <tr>
                            <td>H - Half</td>
                            <td>Public holiday but half</td>
                        </tr>
                        <tr>
                            <td>R - Half</td>
                            <td>Relax but half</td>
                        </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="card-body">
            <div class="row">
                    <div class="col-4"><h2>{{$monthAndYear}}</h2></div>
                    <div class="col-8">
                        <div class="row">
                            <div class="col-8"></div>
                            <div class="col-2 text-right">
                                <form action="{{route('report.generateMonthlyAttendanceReportView')}}" method="GET">
                                    <input type="hidden" name="type" value="excel">
                                    <input type="hidden" name="office_division_id" value="{{$filter['office_division_id']}}">
                                    <input type="hidden" name="department_id" value="{{json_encode($filter['department_id'])}}">
                                    <input type="hidden" name="user_id" value="{{json_encode($filter['user_id'])}}">
                                    <input type="hidden" name="datepicker" value="{{$filter['datepicker']}}">
                                    <input class="btn btn-sm btn-primary mb-5 mr-4" type="submit" value="Export Excel"/>
                                </form>
                            </div>
                            <div class="col-2 text-left">
                                <form action="{{route('report.generateMonthlyAttendanceReportView')}}" method="GET">
                                    <input type="hidden" name="type" value="pdf">
                                    <input type="hidden" name="office_division_id" value="{{$filter['office_division_id']}}">
                                    <input type="hidden" name="department_id" value="{{json_encode($filter['department_id'])}}">
                                    <input type="hidden" name="user_id" value="{{json_encode($filter['user_id'])}}">
                                    <input type="hidden" name="datepicker" value="{{$filter['datepicker']}}">
                                    <input class="btn btn-sm btn-primary mb-5 mr-4" type="submit" value="Export PDF"/>
                                </form>
                            </div>
                        </div>
                    </div>
            </div>
            <div>
                <table class="table table-responsive table-bordered" style="max-height: 600px;overflow: auto;position: relative;">
                    <thead>
                        <tr style="background: antiquewhite;position: sticky;top: 0;vertical-align: top;">
                            <th rowspan="2">Employee Name</th>
                            <th rowspan="2">Joining date</th>
                            @foreach($all_dates as $key=>$date)
                                <td style="position: sticky;top: 0;vertical-align: top;">{{substr($key, -2)}}</td>
                            @endforeach
                            <th rowspan="2">Total Days</th>
                            <th rowspan="2">Total Working Days</th>
                            <th rowspan="1" colspan="3">Total Holidays</th>
                            <th rowspan="2">Total Leave</th>
                            <th rowspan="1" colspan="3">Total Attendance (Days)</th>
                            <th rowspan="2">Total Present</th>
                            <th rowspan="2">Total Absent</th>
                            <th rowspan="2">Total Late (days)</th>
                            <th rowspan="2">Total Late (hours)</th>
                            <th rowspan="2">Total Working hours</th>
                            <th rowspan="2">Daily Average Working Hours</th>
                            <th rowspan="2">Total Overtime (hours)</th>
                        </tr>
                        <tr style="background: antiquewhite;position: sticky;top: 0;vertical-align: top;">
                            @foreach($all_dates as $date)
                                <td style="position: sticky;top: 0;vertical-align: top;">{{$date}}</td>
                            @endforeach
                            <td style="position: sticky;top: 0;vertical-align: top;">Weekend Holiday</td>
                            <td style="position: sticky;top: 0;vertical-align: top;">Official Holiday</td>
                            <td style="position: sticky;top: 0;vertical-align: top;">Relax Day</td>
                            <td style="position: sticky;top: 0;vertical-align: top;">Regualar Duty</td>
                            <td style="position: sticky;top: 0;vertical-align: top;">Weekend Holiday</td>
                            <td style="position: sticky;top: 0;vertical-align: top;">Official Holiday</td>
                        </tr>
                    </thead>
                    <tbody>
                    @foreach($employee_monthly_attendance_summary as $department_id=>$department_employees)
                        @php $colspan = count($all_dates)+18; @endphp
                        <tr style="font-weight: 600;background: lightgray;">
                            <td colspan="{{$colspan}}">{{$department_information[$department_id]['division_name'].', '.$department_information[$department_id]['department_name']}}</td>
                        </tr>
                        @foreach($department_employees as $user_id=>$summary_individual)
                        <tr>
                            <td>{{$employee_information[$user_id]->fingerprint_no.' - '.$employee_information[$user_id]->name}}</td>
                            <td>{{$employee_information[$user_id]->action_date}}</td>
                            @foreach($all_dates as $key=>$date)
                                <td>{{$summary_individual[$key]}}</td>
                            @endforeach
                            <td>{{$summary_data[$user_id]['total_days']}}</td>
                            <td>{{$summary_data[$user_id]['total_working_days']}}</td>
                            <td>{{$summary_data[$user_id]['total_weekly_holidays']}}</td>
                            <td>{{$summary_data[$user_id]['total_public_holidays']}}</td>
                            <td>{{$summary_data[$user_id]['total_relax_days']}}</td>
                            <td>{{$summary_data[$user_id]['total_leave']}}</td>
                            <td>{{$summary_data[$user_id]['total_regular_working_days']}}</td>
                            <td>{{$summary_data[$user_id]['total_weekend_working_days']}}</td>
                            <td>{{$summary_data[$user_id]['total_public_working_days']}}</td>
                            <td>{{$summary_data[$user_id]['total_present']}}</td>
                            <td>{{$summary_data[$user_id]['total_absent']}}</td>
                            <td>{{$summary_data[$user_id]['total_late_days']}}</td>
                            <td>{{convertMinToHrMinSec($summary_data[$user_id]['total_late_mins'])}}</td>
                            <td>{{convertMinToHrMinSec($summary_data[$user_id]['total_working_mins'])}}</td>
                            <td>{{convertMinToHrMinSec($summary_data[$user_id]['average_working_mins'])}}</td>
                            <td>{{convertMinToHrMinSec($summary_data[$user_id]['total_overtime_mins'])}}</td>
                        </tr>
                        @endforeach
                    @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection
