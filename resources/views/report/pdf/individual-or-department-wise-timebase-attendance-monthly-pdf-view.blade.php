<style>
    * {
        margin: 2px;
        font-size: 5px;
        width: 100%;
        text-align: center;
    }

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
        padding: 0;
    }

    td, th {
        white-space: nowrap;
        border: 1px solid #ddd;
        padding: 0px;
        vertical-align: middle;
        /*line-height: 28px;*/
    }
</style>
<div class="card card-custom" id="attendanceReportView">
    <div class="card-body">
        <div class="row" style="margin-top: 80px; margin-bottom: 5px;">
            <div class="col-lg-1">
            </div>
            <div class="col-lg-10 center-block text-center">
                <b>Individual OR Department wise Monthly Attendance Report</b><br/>
                <b>{{ $monthAndYear }}</b>
            </div>
        </div>
        <div>
            <table class="table table-responsive table-bordered text-center table-condensed" style="margin-bottom: 5px;">
                <thead>
                <tr>
                    <th>In Short Form</th>
                    <th>In elaborate</th>
                    <th>In Short Form</th>
                    <th>In elaborate</th>
                    <th>In Short Form</th>
                    <th>In elaborate</th>
                    <th>In Short Form</th>
                    <th>In elaborate</th>
                </tr>
                </thead>
                <tbody>
                <tr>
                    <td>P</td>
                    <td>Present</td>
                    <td>P - L</td>
                    <td>Present but late</td>
                    <td>P - Half</td>
                    <td>Present and half day leave</td>
                    <td>P - L - Half</td>
                    <td>Present but late and half day leave</td>
                </tr>
                <tr>
                    <td>P - L - W</td>
                    <td>Present but late and weekly holiday</td>
                    <td>P - L - H</td>
                    <td>Present but late and public holiday</td>
                    <td>P - W</td>
                    <td>Present but weekly holiday</td>
                    <td>P - H</td>
                    <td>Present but public holiday</td>
                </tr>
                <tr>
                    <td>A</td>
                    <td>Absent</td>
                    <td>A - Half</td>
                    <td>Absent but half</td>
                    <td>W</td>
                    <td>Weekly holiday</td>
                    <td>H</td>
                    <td>Public Holiday</td>
                </tr>
                <tr>
                    <td>L</td>
                    <td>Leave</td>
                    <td>R</td>
                    <td>Relax Day</td>
                    <td>H - Half</td>
                    <td>Public holiday but half</td>
                    <td>R - Half</td>
                    <td>Relax but half</td>
                </tr>
                </tbody>
            </table>
            <table class="table table-responsive table-bordered table-condensed" style="width: 100% !important; margin: 0 auto;">
                <thead>
                <tr style="background: antiquewhite;position: sticky;top: 0;vertical-align: top;">
                    <th style="" rowspan="2">Employee<br>Name</th>
                    <th style="" rowspan="2">Joining<br>date</th>
                    @foreach($all_dates as $key=>$date)
                        <td style="">{{substr($key, -2)}}</td>
                    @endforeach
                    <th style="" rowspan="2">Total<br>Days</th>
                    <th style="" rowspan="2">Total<br>Working<br>Days</th>
                    <th style="" rowspan="1" colspan="3">Total<br>Holidays</th>
                    <th style="" rowspan="2">Total<br>Leave</th>
                    <th style="" rowspan="1" colspan="3">Total<br>Attendance<br>(Days)</th>
                    <th style="" rowspan="2">Total<br>Present</th>
                    <th style="" rowspan="2">Total<br>Absent</th>
                    <th style="" rowspan="2">Total<br>Late<br>(days)</th>
                    <th style="" rowspan="2">Total<br>Late<br>(hours)</th>
                    <th style="" rowspan="2">Total<br>Working<br>hours</th>
                    <th style="" rowspan="2">Daily<br>Average<br>Working<br>Hours</th>
                    <th style="" rowspan="2">Total<br>Overtime<br>(hours)</th>
                </tr>
                <tr style="background: antiquewhite;position: sticky;top: 0;vertical-align: top;">
                    @foreach($all_dates as $date)
                        <td style="">{{$date}}</td>
                    @endforeach
                    <td style="">Weekend<br>Holiday</td>
                    <td style="">Official<br>Holiday</td>
                    <td style="">Relax<br>Day</td>
                    <td style="">Regular<br>Duty</td>
                    <td style="">Weekend<br>Holiday</td>
                    <td style="">Official<br>Holiday</td>
                </tr>
                </thead>
                <tbody>
                @foreach($employee_monthly_timebase_attendance_summary as $department_id=>$department_employees)
                    @php $colspan = count($all_dates)+18; @endphp
                    <tr style="font-weight: 600;background: lightgray;">
                        <td colspan="{{$colspan}}">{{$department_information[$department_id]['division_name'].', '.$department_information[$department_id]['department_name']}}</td>
                    </tr>
                    @foreach($department_employees as $user_id=>$summary_individual)
                        <tr>
                            <td>{{$employee_information[$user_id]->fingerprint_no.' - '.$employee_information[$user_id]->name}}</td>
                            <td>{{$employee_information[$user_id]->action_date}}</td>
                            @foreach($all_dates as $key=>$date)
                                @isset($summary_individual[$key]['time'])
                                    @php
                                        $summary_individual[$key]['time'] = str_replace('-','<br>',$summary_individual[$key]['time']);
                                    @endphp
                                @endisset
                                <td>{!! $summary_individual[$key]['time'] ?? $summary_individual[$key] !!}</td>
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
