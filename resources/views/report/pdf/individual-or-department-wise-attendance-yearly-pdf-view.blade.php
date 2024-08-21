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
                <b>{{ $year }}</b>
            </div>
        </div>
        <div>

            <table class="table table-responsive table-bordered table-condensed" style="width: 100% !important; margin: 0 auto;">
                <thead>
                    <tr style="background: antiquewhite;position: sticky;top: 0;vertical-align: top;">
                        <th>Employee<br>ID</th>
                        <th>Employee<br>Name</th>
                        <th>Joining<br>date</th>
                        <th>Total<br>Days</th>
                        <th>Total<br>Working days</th>
                        <th>Total<br>Holiday</th>
                        <th>Total<br>Present</th>
                        <th>Total<br>Absent</th>
                        <th>Total<br>Leave</th>
                        <th>Total<br>Late (days)</th>
                        <th>Total<br>Late (hours)</th>
                        <th>Total<br>Working hours</th>
                        <th>Average<br>Working<br>Hours</th>
                        <th>Total<br>Overtime<br>(hours)</th>
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
