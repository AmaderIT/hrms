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

        /* td, th {
            white-space: nowrap;
            border: 1px solid #ddd;
            padding: 5px;
        } */
    </style>
@endsection

@section('content')
    <div class="card card-custom" id="attendanceReportView">

        <div class="card-body">


            <div class="row">
                <div class="col-4"><h2>Leave Report - {{ $year }}</h2></div>
                <div class="col-8">
                    <div class="row">
                        <div class="col-6"></div>
                        <div class="col-2 text-right">
                            <a href="{{route('report.leaveReportYearly')}}"><button class="btn btn-sm btn-primary mb-5 mr-4">Back</button></a>
                        </div>
                        <div class="col-2 text-right">
                            <form action="{{route('report.generateLeaveReportYearly')}}" method="GET">
                                <input type="hidden" name="type" value="excel">
                                <input type="hidden" name="office_division_id" value="{{$filter['office_division_id']}}">
                                <input type="hidden" name="department_id" value="{{json_encode($filter['department_id'])}}">
                                <input type="hidden" name="user_id" value="{{json_encode($filter['user_id'])}}">
                                <input type="hidden" name="datepicker" value="{{$filter['datepicker']}}">
                                <input class="btn btn-sm btn-primary mb-5 mr-4" type="submit" value="Export Excel"/>
                            </form>
                        </div>
                        <div class="col-2 text-left">
                            <form action="{{ route('report.generateLeaveReportYearly') }}" method="GET" class="float-right">
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
                <div class="row">
                    <div class="col-lg-12 col-md-12">
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
        </div>
    </div>
@endsection


@section('footer-js')
    <script src="{{ asset('https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.12.13/xlsx.full.min.js') }}"></script>
    <script src="{{ asset('assets/js/export.js') }}"></script>
    <script type="text/javascript">
        $(".leaveReportExcel").click(function () {
            exportXcel("Leave Report", "reportTable", "Action")
        });
    </script>
@endsection



