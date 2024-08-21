<!DOCTYPE html>
<head>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.0.0/dist/css/bootstrap.min.css"
          integrity="sha384-Gn5384xqQ1aoWXA+058RXPxPg6fy4IWvTNh0E263XmFcJlSAwiGgFAW/dAiS6JXm" crossorigin="anonymous"/>
    <style>
        /**
            Set the margins of the page to 0, so the footer and the header
            can be of the full height and width !
         **/
        @page {
            margin: 0cm 0cm;
        }

        /** Define now the real margins of every page in the PDF **/
        body {
            margin-top: 2cm;
            /*margin-left: 2cm;*/
            /*margin-right: 2cm;*/
            margin-bottom: 2cm;
        }

        /** Define the header rules **/
        header {
            width: 100%;
            position: fixed;
            top: 0cm;
            left: 0cm;
            right: 0cm;
            height: 2cm;

            /** Extra personal styles **/
            background-color: #f3f3f3;
            text-align: center;
            /*line-height: 0.5cm;*/
        }

        /** Define the footer rules **/
        footer {
            position: fixed;
            bottom: 0cm;
            left: 0cm;
            right: 0cm;
            height: 1cm;

            /** Extra personal styles **/
            background-color: #f3f3f3;
            padding: 2px auto;
            text-align: center;
            /*line-height: 1cm;*/
        }

        * {
            font-size: 10px;
        }

        .table tr > td {
            padding: 2px auto 2px 5px !important;
        }

        .table thead > tr > th {
            text-align: center;
        }
    </style>
</head>
<body>
<div class="container-fluid">
    <div class="row">
        <div class="card card-custom" id="attendanceReportView">
                <header>
                    <h2 style="margin-top: 25px !important;">BYSL Global Technologies Limited</h2>
                </header>
            @php
                $total = 0;
            @endphp
                <div class="col-10 offset-1">
                    <div class="card-body">
                        <table class="table table-bordered table-condensed text-center">
                            <thead>
                            <tr>
                                <th>SL. No.</th>
                                <th>ID. No</th>
                                <th>Account Name</th>
                                <th>Account No.</th>
                                <th>Amount in Taka</th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach($departmentLeaveEncashment->employeeLeaveEncashment as $key => $data)
                                @php
                                    $total += $data->total_payable_amount;
                                @endphp
                                <tr>
                                    <td>{{ $key + 1 }}</td>
                                    <td>{{ $data->employeeInformation->fingerprint_no }}</td>
                                    <td>{{ $data->employeeInformation->currentBank->account_name ?? "" }}</td>
                                    <td>{{ $data->employeeInformation->currentBank->account_no ?? "" }}</td>
                                    <td>{{ currencyFormat($data->total_payable_amount) }}/=</td>
                                </tr>
                            @endforeach
                            <tr>
                                <td style="float:right; margin-right: 10px" colspan="4">TOTAL</td>
                                <td>{{ currencyFormat($total) }}/=</td>
                            </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
                <footer>
                    <p style="margin-top: 14px;">Preparation Time: {{ date('M d, Y - h:i:s:A', strtotime(now())) }}</p>
                </footer>
            </div>
    </div>
</div>
</body>
