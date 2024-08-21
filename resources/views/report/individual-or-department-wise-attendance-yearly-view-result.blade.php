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
        table {
            border: 1px solid #ddd;
            border-collapse: collapse;
        }
        td, th {
            white-space: nowrap;
            border: 1px solid #ddd;
            padding: 5px;
        }
    </style>
@endsection

@section('content')
    <div class="card card-custom" id="attendanceReportView">
        <div class="card-header">
            <div class="card-title">
                <h2 class="card-label">Individual OR Department wise Yearly Attendance Report</h2>
            </div>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-4"><h2>{{$year}}</h2></div>
                <div class="col-8">
                    <div class="row">
                        <div class="col-8"></div>
                        <div class="col-2 text-right">
                            <form action="{{route('report.generateYearlyAttendanceReportView')}}" method="GET">
                                <input type="hidden" name="type" value="excel">
                                <input type="hidden" name="office_division_id" value="{{$filter['office_division_id']}}">
                                <input type="hidden" name="department_id" value="{{json_encode($filter['department_id'])}}">
                                <input type="hidden" name="user_id" value="{{json_encode($filter['user_id'])}}">
                                <input type="hidden" name="datepicker" value="{{$filter['datepicker']}}">
                                <input class="btn btn-sm btn-primary mb-5 mr-4" type="submit" value="Export Excel"/>
                            </form>
                        </div>
                        <div class="col-2 text-left">
                            <form action="{{route('report.generateYearlyAttendanceReportView')}}" method="GET">
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
                            <th rowspan="2">Employee ID</th>
                            <th rowspan="2">Employee Name</th>
                            <th rowspan="2">Joining date</th>
                            <th rowspan="2">Total Days</th>
                            <th rowspan="2">Total Working days</th>
                            <th rowspan="2">Total Holiday</th>
                            <th rowspan="2">Total Present</th>
                            <th rowspan="2">Total Absent</th>
                            <th rowspan="2">Total Leave</th>
                            <th rowspan="2">Total Late (days)</th>
                            <th rowspan="2">Total Late (hours)</th>
                            <th rowspan="2">Total Working hours</th>
                            <th rowspan="2">Average Working Hours</th>
                            <th rowspan="2">Total Overtime (hours)</th>
                        </tr>
                    </thead>
                    <tbody>
                    @foreach($summary_data as $department_id=>$department_employees)
                        @php $colspan = 14; @endphp
                        <tr style="font-weight: 600;background: lightgray;">
                            <td colspan="{{$colspan}}">{{$department_information[$department_id]['division_name'].', '.$department_information[$department_id]['department_name']}}</td>
                        </tr>
                        @foreach($department_employees as $user_id=>$summary_individual)
                        <tr>
                            <td>{{$employee_information[$user_id]->fingerprint_no}}</td>
                            <td>{{$employee_information[$user_id]->name}}</td>
                            <td>{{$employee_information[$user_id]->action_date}}</td>
                            <td>{{$summary_individual['total_days']}}</td>
                            <td>{{$summary_individual['total_working_days']}}</td>
                            <td>{{$summary_individual['total_holidays']}}</td>
                            <td>{{$summary_individual['total_present']}}</td>
                            <td>{{$summary_individual['total_absent']}}</td>
                            <td>{{$summary_individual['total_leave']}}</td>
                            <td>{{$summary_individual['total_late_days']}}</td>
                            <td>{{$summary_individual['total_late_mins']}}</td>
                            <td>{{$summary_individual['total_working_mins']}}</td>
                            <td>{{$summary_individual['average_working_mins']}}</td>
                            <td>{{$summary_individual['total_overtime_mins']}}</td>
                        </tr>
                        @endforeach
                    @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection
