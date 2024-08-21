<html lang="">
<head>
    <title>Mushak 6.5</title>
    <style>
        body {
            font-family: Arial, Helvetica, sans-serif;
        }
        .row {
            display: -ms-flexbox;
            display: flex;
            -ms-flex-wrap: wrap;
            flex-wrap: wrap;
            margin-right: -7.5px;
            margin-left: -7.5px;
        }
        /**, ::after, ::before {*/
        /*    box-sizing: border-box;*/
        /*}*/
        .mt-3, .my-3 {
            margin-top: 1rem!important;
        }
        .table-bordered {
            /*border: 1px solid #dee2e6;*/
            font-size: 14px !important;
        }
        .table {
            width: 100%;
            margin-bottom: 1rem;
            color: #212529;
            background-color: transparent;
            font-size: 14px !important;
            border-collapse: collapse;
        }
        .table:not(.table-dark) {
            color: inherit;
        }
        .offset-2 {
            margin-left: 16.666667%;
        }
        .col-8 {
            -ms-flex: 0 0 66.666667%;
            flex: 0 0 66.666667%;
            max-width: 66.666667%;
        }

        .col-2 {
            -ms-flex: 0 0 16.666667%;
            flex: 0 0 16.666667%;
            max-width: 16.666667%;
        }

        .col, .col-1, .col-10, .col-11, .col-12, .col-2, .col-3, .col-4, .col-5, .col-6, .col-7, .col-8, .col-9, .col-auto, .col-lg, .col-lg-1, .col-lg-10, .col-lg-11, .col-lg-12, .col-lg-2, .col-lg-3, .col-lg-4, .col-lg-5, .col-lg-6, .col-lg-7, .col-lg-8, .col-lg-9, .col-lg-auto, .col-md, .col-md-1, .col-md-10, .col-md-11, .col-md-12, .col-md-2, .col-md-3, .col-md-4, .col-md-5, .col-md-6, .col-md-7, .col-md-8, .col-md-9, .col-md-auto, .col-sm, .col-sm-1, .col-sm-10, .col-sm-11, .col-sm-12, .col-sm-2, .col-sm-3, .col-sm-4, .col-sm-5, .col-sm-6, .col-sm-7, .col-sm-8, .col-sm-9, .col-sm-auto, .col-xl, .col-xl-1, .col-xl-10, .col-xl-11, .col-xl-12, .col-xl-2, .col-xl-3, .col-xl-4, .col-xl-5, .col-xl-6, .col-xl-7, .col-xl-8, .col-xl-9, .col-xl-auto {
            position: relative;
            width: 100%;
            padding-right: 7.5px;
            padding-left: 7.5px;
        }
        .pl-1, .px-1 {
            padding-left: 0.25rem!important;
        }
        .pr-1, .px-1 {
            padding-right: 0.25rem!important;
        }
        .float-right {
            float: right!important;
        }
        .border-dark {
            border-color: #343a40!important;
        }
        .border {
            border: 1px solid #dee2e6!important;
        }

        .mb-0, .my-0 {
            margin-bottom: 0!important;
        }
        h5 {
            font-size: 14px !important;
        }
        .h1, .h2, .h3, .h4, .h5, .h6, h1, h2, h3, h4, h5, h6 {
            margin-bottom: 0.5rem;
            font-family: inherit;
            font-weight: 400;
            line-height: 1.2;
            color: inherit;
        }
        h1, h2, h3, h4, h5, h6 {
            margin-top: 0;
            margin-bottom: 0.5rem;
        }
        .col-12 {
            -ms-flex: 0 0 100%;
            flex: 0 0 100%;
            max-width: 100%;
        }
        .col, .col-1, .col-10, .col-11, .col-12, .col-2, .col-3, .col-4, .col-5, .col-6, .col-7, .col-8, .col-9, .col-auto, .col-lg, .col-lg-1, .col-lg-10, .col-lg-11, .col-lg-12, .col-lg-2, .col-lg-3, .col-lg-4, .col-lg-5, .col-lg-6, .col-lg-7, .col-lg-8, .col-lg-9, .col-lg-auto, .col-md, .col-md-1, .col-md-10, .col-md-11, .col-md-12, .col-md-2, .col-md-3, .col-md-4, .col-md-5, .col-md-6, .col-md-7, .col-md-8, .col-md-9, .col-md-auto, .col-sm, .col-sm-1, .col-sm-10, .col-sm-11, .col-sm-12, .col-sm-2, .col-sm-3, .col-sm-4, .col-sm-5, .col-sm-6, .col-sm-7, .col-sm-8, .col-sm-9, .col-sm-auto, .col-xl, .col-xl-1, .col-xl-10, .col-xl-11, .col-xl-12, .col-xl-2, .col-xl-3, .col-xl-4, .col-xl-5, .col-xl-6, .col-xl-7, .col-xl-8, .col-xl-9, .col-xl-auto {
            position: relative;
            width: 100%;
            padding-right: 7.5px;
            padding-left: 7.5px;
        }
        .table-bordered td, .table-bordered th {
            border: 1px solid #dee2e6;
            font-size: 14px !important;

        }
        .table tr{
            border: 1px solid #000;
        }
        .table td, .table th {
            padding: 0.75rem;
            vertical-align: top;
            /*border-top: 1px solid #dee2e6;*/
            font-size: 14px !important;
        }

        th {
            text-align: inherit;
            font-size: 14px !important;
        }
    </style>
</head>
<body>
<div class="container">

    <div class="row" style="position: relative;">
        <div class="col-8 offset-2">
            <h5 style="text-align: center;">Government of the People&apos;s Republic of Bangladesh</h5>
        </div>
        <div class="col-2" style="position: absolute; right: 0;">
            <div class="float-right border border-dark pr-1 pl-1">
                <h5 class="mb-0">Mushak 6.5</h5>
            </div>
        </div>
    </div>
    <div class="row" style="text-align: center;">
        <div class="col-12">
            <h5 style="text-align: center;">National Board of Revenue</h5>
        </div>
    </div>
    <div class="row" style="text-align: center;">
        <div class="col-12">
            <h5>Invoice for transfer Goods of Central registered organization</h5>
            <h5>BYSL Global Technology Group</h5>
            <h5>Challan No: <strong>{{ $item->challan }}</strong></h5>
        </div>
    </div>

    <table class="table table-bordered mt-3">
        <tr style="width: 100%">
            <td style="text-align: left;width: 50%">Source Type: <strong>@if(!empty($item->sourceDepartment)) {{'Department'}} @else {{'Warehouse'}} @endif</strong></td>
            <td style="text-align: left;width: 50%">Destination Type: <strong>@if(!empty($item->destinationDepartment)) {{'Department'}} @else {{'Warehouse'}} @endif</strong></td>
        </tr>
        <tr>
            <td style="text-align: left;width: 50%">Source Name: <strong>@if(!empty($item->sourceDepartment)) {{$item->sourceDepartment->name}} @else {{$item->sourceWarehouse->name}} @endif</strong></td>
            <td style="text-align: left;width: 50%">Destination Name: <strong>@if(!empty($item->destinationDepartment)) {{$item->destinationDepartment->name}} @else {{$item->destinationWarehouse->name}} @endif</strong></td>
        </tr>
        <tr>
            <td style="text-align: left;width: 50%">Date of Issue: <strong>{{ date('d/m/Y',strtotime($item->issue_at)) }}</strong></td>
            <td style="text-align: left;width: 50%">Time of Issue: <strong>{{ date('h:i:s A',strtotime($item->issue_at)) }}</strong></td>
        </tr>
    </table>
    <table class="table table-bordered" style="margin-top: 40px">
        <thead>
        <tr>
            <th style="border: 1px solid #000;">S/N</th>
            <th style="border: 1px solid #000;">Code</th>
            <th style="border: 1px solid #000;">Product</th>
            <th style="border: 1px solid #000;">Quantity</th>
            <th style="border: 1px solid #000;">UOM</th>
        </tr>
        </thead>
        <tbody style="">
        @foreach($challan_items as $index=>$c_item)
            <tr>
                <td style="border: 1px solid #000;">{{$index+1}}</td>
                <td style="border: 1px solid #000;">{{$c_item->code}}</td>
                <td style="border: 1px solid #000;">{{$c_item->item_name}}</td>
                <td style="border: 1px solid #000;">{{$c_item->qty}}</td>
                <td style="border: 1px solid #000;">{{$c_item->unit_name}}</td>
            </tr>
        @endforeach
        </tbody>
    </table>

    <table class="table table-bordered" style="margin-top: 40px">
        <thead>
        <tr>
            <th style="text-align: center; width: 20%;">Prepared By</th>
            <th style="text-align: center; width: 20%;">Authorized By</th>
            <th style="text-align: center; width: 20%;">Security Checked By</th>
            <th style="text-align: center; width: 20%;">Delivered By</th>
            <th style="text-align: center; width: 20%;">Received By</th>
        </tr>
        </thead>
        <tbody>
        <tr>
            <td style="text-align: center">@if(!empty($item->preparedBy)) {{$item->preparedBy->name}} @endif</td>
            <td style="text-align: center">@if(!empty($item->authorizedBy)) {{$item->authorizedBy->name}} @endif</td>
            <td style="text-align: center">@if(!empty($item->securityCheckedBy)) {{$item->securityCheckedBy->name}} @endif</td>
            <td style="text-align: center">@if(!empty($item->deliveredBy)) {{$item->deliveredBy->name}} @endif</td>
            <td style="text-align: center">@if(!empty($item->receivedBy)) {{$item->receivedBy->name}} @endif</td>
        </tr>
        </tbody>
    </table>
</div>
</body>
</html>
