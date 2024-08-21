<!DOCTYPE html>
<head>
    <style>
        @page {
            margin: 20px 5px 0px 10px !important;
            padding: 0px 0px 0px 0px !important;
        }

        .reportTable {
            font-size: 8px;
        }

        table {
            border-left: 1px solid #000;
            border-right: 0;
            border-top: 1px solid #000;
            border-bottom: 0;
            border-collapse: collapse;
        }

        table th, table tr, table td {
            border-left: 0;
            border-right: 1px solid #000;
            border-top: 0;
            border-bottom: 1px solid #000;
        }
    </style>
</head>
<body>
    <div class="row">
        <div class="card-body">
            <div class="row">
                <div class="col-12">
                    <h2>{{ $result["department"] . " " . $result["monthAndYear"] }}</h2>
                </div>
            </div>

            @php
                $headers = array("ID", "Name");
                $totalDays = $result["lastDayOfMonth"];
                $monthYear = $result["monthAndYear"];
                for ($day = 1; $day <= $totalDays; $day++) {

                    $monthAndYear   = "{$day} " . $monthYear;
                    $dateObj        = DateTime::createFromFormat('d M, Y', $monthAndYear);
                    $header         = [
                        "day" => $dateObj->format("D"),
                        "date"=> (int) $dateObj->format("d")
                    ];

                    array_push($headers, $header);
                }
            @endphp

            <div class="table-wrapper reportTable">
                <table class="">
                    <thead>
                    <tr>
                        @foreach($headers as $key => $header)
                            @if($key < 2)
                                <th class="text-center">{{ $header }}</th>
                            @elseif($key >= 2)
                                <th class="text-center">
                                    <span>{{ $header["day"] }}</span><br/>
                                    <span class="text-center">{{ $header["date"] }}</span>
                                </th>
                            @endif
                        @endforeach
                    </tr>
                    </thead>
                    <tbody>
                    @php
                        $attendanceReport = $result["attendanceReport"];
                    @endphp
                    @foreach($attendanceReport as $value)
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
    </div>
</body>
</html>
