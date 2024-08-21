@extends('layouts.app')
@section('content')
    <div class="card card-custom">
        <div class="card-header flex-wrap border-0 pt-6 pb-0">
            <div class="card-title">
                <h3 class="card-label">Leave History Reports</h3>
                <b>({{ $monthAndYear }})</b>
            </div>

            <div class="row">
                <div class="col-4 text-right">
                    <a href="{{ route('report.viewLeaveHistory') }}" class="btn btn-sm btn-warning mb-5 mr-4">Back</a>
                </div>
                <div class="col-4 text-right">
                    <form action="{{route('report.generateLeaveHistory')}}" method="GET">
                        <input type="hidden" name="type" value="excel">
                        <input type="hidden" name="office_division_id" value="{{$filter['office_division_id']}}">
                        <input type="hidden" name="department_id" value="{{json_encode($filter['department_id'])}}">
                        <input type="hidden" name="user_id" value="{{json_encode($filter['user_id'])}}">
                        <input type="hidden" name="status" value="{{$filter['status']}}">
                        <input type="hidden" name="datepicker" value="{{$filter['datepicker']}}">
                        <input class="btn btn-sm btn-primary mb-5 mr-4"  type="submit" value="Export Excel"/>
                    </form>
                </div>
                <div class="col-4 text-left">
                    <form action="{{route('report.generateLeaveHistory')}}" method="GET">
                        <input type="hidden" name="type" value="pdf">
                        <input type="hidden" name="office_division_id" value="{{$filter['office_division_id']}}">
                        <input type="hidden" name="department_id" value="{{json_encode($filter['department_id'])}}">
                        <input type="hidden" name="user_id" value="{{json_encode($filter['user_id'])}}">
                        <input type="hidden" name="status" value="{{$filter['status']}}">
                        <input type="hidden" name="datepicker" value="{{$filter['datepicker']}}">
                        <input class="btn btn-sm btn-primary mb-5 mr-4"  type="submit" value="Export PDF"/>
                    </form>
                </div>
            </div>
        </div>
        <div class="card-body">
            <table class="table table-responsive table-bordered"
                   style="max-height: 600px;overflow: auto;position: relative;">
                <thead>
                <tr style="background: antiquewhite;position: sticky;top: 0;vertical-align: top;">
                    <th scope="col">Employee Name</th>
                    <th scope="col">Leave Type</th>
                    <th scope="col">Request Duration</th>
                    <th scope="col">Applied Date</th>
                    <th scope="col">No. of day</th>
                    <th scope="col">Authorized By</th>
                    <th scope="col">Approved By</th>
                    <th scope="col">Reasons</th>
                    <th scope="col">Status</th>
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
                                <td>{{$item['employee_name'] .'('.$item['fingerprint_no'].')'}}</td>
                                <td>{{$item['leave_type']}}</td>
                                <td>{{ $item['request_duration'] }}</td>
                                <td>{{$item['applied_date']}}</td>
                                <td>{{$item['number_of_days']}}</td>
                                <td>{{$item['authorized_by']}}</td>
                                <td>{{$item['approved_by']}}</td>
                                <td>{{$item['purpose']}}</td>
                                @if ($item['status'] == \App\Models\LeaveRequest::STATUS_APPROVED)
                                    <td><span class="badge badge-success">Approved</span></td>
                                @elseif ($item['status'] == \App\Models\LeaveRequest::STATUS_REJECTED)
                                    <td><span class="badge badge-danger">Cancelled</span></td>
                                @endif
                                @endforeach
                            </tr>
                        @endforeach
                        @else
                            <tr>
                                <td colspan="9" style="text-align: center;">Data not found!!!</td>
                            </tr>
                        @endif
                </tbody>
            </table>
        </div>
    </div>
@endsection

