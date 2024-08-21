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
                <b>Leave Report</b><br/>
                <b>{{ $year }}</b>
            </div>
        </div>
        <div>

            <table class="table table-responsive table-bordered" style="overflow: auto;position: relative;max-height: 600px;width: 100%;display: table;">
                <thead>
                <tr style="background: antiquewhite;position: sticky;top: 0;vertical-align: top;">
                    <th rowspan="2">Employee Id</th>
                    <th rowspan="2">Employee Name</th>
                    <th rowspan="2">Joining Date</th>
                    @foreach($leave_types as $type)
                        <th class="text-center" colspan="3">{{$type->name}}</th>
                    @endforeach
                </tr>
                <tr style="background: antiquewhite; top: 0; vertical-align: top;">
                    <th>Total Leave</th>
                    <th>Leave Conusme</th>
                    <th>Leave Balance</th>
                    <th>Total Leave</th>
                    <th>Leave Conusme</th>
                    <th>Leave Balance</th>
                </tr>
                </thead>
                <tbody>
                @foreach($summary_data as $department_id=>$department_employees)
                    @php $colspan = 9; @endphp
                    <tr style="font-weight: 600;background: lightgray;">
                        <td colspan="{{$colspan}}">{{$department_information[$department_id]['division_name'].', '.$department_information[$department_id]['department_name']}}</td>
                    </tr>
                    @foreach($department_employees as $user_id=>$summary_individual)
                        <tr>
                            <td>{{$employee_information[$user_id]->fingerprint_no}}</td>
                            <td>{{$employee_information[$user_id]->name}}</td>
                            <td>{{$employee_information[$user_id]->action_date}}</td>
                            @foreach($leave_types as $type)
                                <td>{{$summary_individual[$type->id]['total_leave']}}</td>
                                <td>{{$summary_individual[$type->id]['total_leave_consume']}}</td>
                                <td>{{$summary_individual[$type->id]['total_leave_balance']}}</td>
                            @endforeach
                        </tr>
                    @endforeach
                @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
