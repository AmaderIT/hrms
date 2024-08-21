<style>
    * {
        margin: 2px;
        font-size: 11px;
        width: 100%;
        text-align: center;
    }

    table {
        border: 1px solid #ddd;
        border-collapse: collapse;
        padding: 0;
    }

    td, th {
        border: 1px solid #ddd;
        padding: 0px;
        vertical-align: middle;
    }
</style>

<div class="card card-custom">
    <div class="card-header flex-wrap border-0 pt-6 pb-0">
        <div class="card-title">
            <h3 class="card-label">Leave History Reports</h3>
            <b>({{ $monthAndYear }})</b>
        </div>
    </div>
    <div class="card-body">
        <table class="table table-responsive table-bordered table-condensed" style="width: 100%; margin: 0 auto;">
            <thead>
            <tr style="background: antiquewhite;position: sticky;top: 0;vertical-align: top;">
                <th style="width: 15%;">Employee Name</th>
                <th style="width: 5%;">Leave Type</th>
                <th style="width: 10%;">Request Duration</th>
                <th style="width: 5%;">Applied Date</th>
                <th style="width: 2%;">No. of day</th>
                <th style="width: 15%;">Authorized By</th>
                <th style="width: 15%;">Approved By</th>
                <th style="width: 20%;">Reasons</th>
                <th style="width: 3%;">Status</th>
            </tr>
            </thead>
            <tbody>
            @if(count($employeeLeaveHistoryDeptWise)>0)
                @foreach($employeeLeaveHistoryDeptWise as $deptID => $items)
                    <tr style="font-weight: 600;background: lightgray;">
                        <td colspan="9">{{$departmentInformations[$deptID]['division_name'].', '.$departmentInformations[$deptID]['department_name']}}</td>
                    </tr>
                    @foreach($items as $item)
                        <tr>
                            <td style="width: 15%;">{{$item['employee_name'] .'('.$item['fingerprint_no'].')'}}</td>
                            <td style="width: 5%;">{{$item['leave_type']}}</td>
                            <td style="width: 10%;">{{ $item['request_duration'] }}</td>
                            <td style="width: 5%;">{{$item['applied_date']}}</td>
                            <td style="width: 2%;">{{$item['number_of_days']}}</td>
                            <td style="width: 15%;">{{$item['authorized_by']}}</td>
                            <td style="width: 15%;">{{$item['approved_by']}}</td>
                            <td style="width: 20%;">{{$item['purpose']}}</td>
                            @if ($item['status'] == \App\Models\LeaveRequest::STATUS_APPROVED)
                                <td style="width: 3%;">Approved</td>
                            @elseif ($item['status'] == \App\Models\LeaveRequest::STATUS_REJECTED)
                                <td style="width: 3%;">Cancelled</td>
                            @endif
                            @endforeach
                        </tr>
                    @endforeach
                    @else
                        <tr>
                            <td colspan="9">Data not found!!!</td>
                        </tr>
                    @endif
            </tbody>
        </table>
    </div>
</div>

