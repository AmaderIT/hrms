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

        .table-wrapper {
            max-width: 1220px;
            overflow: scroll;
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

        th {
            background-color: #eee;
            position: sticky;
            top: -1px;
            z-index: 2;

            &:first-of-type {
                left: 0;
                z-index: 3;
            }
        }

        tbody tr td:first-of-type, td:nth-of-type(2) {
           background-color: #eee;
           position: sticky;
           left: -1px;
           z-index: 1;
        }
    </style>
@endsection

@section('content')
    <div class="card card-custom" id="attendanceReportView">
        <div class="card-header">
            <div class="card-title">
                <h2 class="card-label">Monthly Attendance Report</h2>
            </div>
        </div>

        @foreach($reports as $key => $report)
            <div class="card-body">
                <div class="row">
                    <div class="col-12">
                        <h2>{{ $report[0]["department"] . " " . $report[0]["monthAndYear"] }}
                            <div class="float-right">
                                <form action="{{ route('report.generateAttendanceReportPdf') }}" method="GET">
                                    <input type="hidden" name="office_division_id" value="{{ request()->input('office_division_id') }}"/>
                                    <input type="hidden" name="department_id[]" value="{{ $report[0]["employee"]->currentPromotion->department_id }}"/>

                                    @foreach($report as $value)
                                        <input type="hidden" name="user_id[]" value="{{ $value["employee"]["id"] }}"/>
                                    @endforeach

                                    <input type="hidden" name="datepicker" value="{{ request()->input('datepicker') }}"/>
                                    <input class="btn btn-sm btn-primary mb-5 mr-4" type="submit" value="Export PDF" style="margin-left: -90px !important;"/>
                                </form>

                                {{-- Export CSV --}}
                                {{--<form action="{{ route('report.generateAttendanceReportCsv') }}" method="GET">
                                    <input type="hidden" name="office_division_id" value="{{ request()->input('office_division_id') }}"/>
                                    <input type="hidden" name="department_id[]" value="{{ $report[0]["employee"]->currentPromotion->department_id }}"/>

                                    @foreach($report as $value)
                                        <input type="hidden" name="user_id[]" value="{{ $value["employee"]["id"] }}"/>
                                    @endforeach

                                    <input type="hidden" name="datepicker" value="{{ request()->input('datepicker') }}"/>
                                    <input class="btn btn-sm btn-primary mb-5 mr-4" type="submit" value="Export CSV" style="margin-top: -80px !important;"/>
                                </form>--}}
                            </div>
                        </h2>
                    </div>
                </div>

                @php
                    $headers = array("ID", "Employee Name");
                    for ($day = 1; $day <= $report[0]["lastDayOfMonth"]; $day++) {

                        $monthAndYear   = "{$day} " . $report[0]["monthAndYear"];
                        $dateObj        = DateTime::createFromFormat('d M, Y', $monthAndYear);
                        $result         = [
                            "day" => $dateObj->format("D"),
                            "date"=> (int) $dateObj->format("d")
                        ];

                        array_push($headers, $result);
                    }
                @endphp

                <div class="table-wrapper">
                    <table>
                        <thead>
                            <tr>
                                @foreach($headers as $key => $header)
                                    @if($key < 2)
                                        <th class="text-center">{{ $header }}</th>
                                    @elseif($key >= 2)
                                        <th class="text-center">
                                            <span style="font-size: 12px">{{ $header["day"] }}</span><br/>
                                            <span class="text-center">{{ $header["date"] }}</span>
                                        </th>
                                    @endif
                                @endforeach
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($report as $value)
                                <tr>
                                    <td>{{ $value["employee"]["fingerprint_no"] }}</td>
                                    <td>{{ $value["employee"]["name"] }}</td>

                                    @foreach($value["report"] as $day => $attendanceData)
                                        @php
                                            $attendance = reset($attendanceData);
                                        @endphp
                                        <td class="text-center">
                                            {{ $attendance["reason"] }} {{ $attendance["entry"] }} <br/> {{ $attendance["exit"] }}
                                        </td>
                                    @endforeach
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @endforeach
    </div>
@endsection
