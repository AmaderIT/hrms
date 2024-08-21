<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css"
      integrity="sha384-BVYiiSIFeK1dGmJRAkycuHAHRg32OmUcww7on3RYdg4Va+PmSTsz/K68vbdEjh4u" crossorigin="anonymous">

<style>
    .table-borderless > tbody > tr > td,
    .table-borderless > tbody > tr > th,
    .table-borderless > tfoot > tr > td,
    .table-borderless > tfoot > tr > th,
    .table-borderless > thead > tr > td,
    .table-borderless > thead > tr > th {
        border: none;
    }
</style>

<div class="row">
    <div class="col-lg-12">
{{--        <h4 class="text-center text-uppercase"><u><h1>BYSL</h1></u></h4>--}}
        <div class="col-lg-4 offset-4" style="text-align: center; vertical-align: middle;">
            <img src="{{ asset('assets/media/login/bysl.png') }}" width="100" height="50" alt="BYSL">
        </div>
    </div>
</div>

<div class="row mt-30">
    <div class="col-lg-12">
        <h4 style="text-align: center; vertical-align: middle;"><strong>Departmental Requisition Form for Office Supplies</strong></h4>
        <table class="table table-bordered">
            <tbody>
                <tr>
                    <td width="60%">Requisition Provider Name: {{ $data["requisition"]->appliedBy->name }}</td>
                    <td width="15%">Date</td>
                    <td width="25%">{{ $data["requisition"]->applied_date }}</td>
                </tr>
                <tr>
                    <td>Department's Name: {{ $data["requisition"]->department->name }}</td>
                    <td>Order No.</td>
                    <td>{{ $data["requisition"]->id }}</td>
                </tr>
            </tbody>
        </table>
    </div>
</div>

<div class="row mt-30">
    <div class="col-lg-12">
        <h4 style="text-align: center; vertical-align: middle;"><strong>Priority</strong></h4>
        <table class="table table-bordered text-center">
            <tbody>
                <tr>
                    <td width="25%">In Today</td>
                    <td width="25%">Within 3 days</td>
                    <td width="25%">Within 7 days</td>
                    <td width="25%">Within 10 days</td>
                </tr>
            </tbody>
        </table>
    </div>
</div>

<div class="row mt-30">
    <div class="col-lg-12">
        <h4 style="text-align: center; vertical-align: middle;"><strong>Details of Requisition</strong></h4>
        <table class="table table-bordered text-center">
            <tbody>
                <tr>
                    <td width="5%">SL No.</td>
                    <td width="45%">Description</td>
                    <td width="10%">Quantity (Pcs)</td>
                    <td width="20%">Unit Price (TK.)</td>
                    <td width="20%">Gross Price (TK.)</td>
                </tr>
                @foreach($data["requisition"]->details as $key => $details)
                    <tr>
                        <td width="5%">{{ $key + 1 }}</td>
                        <td width="45%">{{ $details->item->name }}</td>
                        <td width="10%">{{ $details->quantity }}</td>
                        <td width="20%">{{ $details->unit_price ?? "" }}</td>
                        <td width="20%">{{ $details->gross_price ?? "" }}</td>
                    </tr>
                @endforeach
                <tr>
                    <td width="5%" colspan="2" class="text-left"><strong>In Word:</strong></td>
                    <td width="60%"></td>
                    <td width="10%"></td>
                    <td width="25%"></td>
                </tr>
            </tbody>
        </table>
    </div>
</div>

<div class="row" style="margin-top: 30px">
    <div class="col-lg-12">
        <p><b>Remarks</b>: {{ $data["requisition"]->remarks }}</p>
    </div>
</div>

<div class="row" style="margin-top: 30px">
    <div class="col-lg-12">
        <table class="table table-borderless text-center">
            <tbody>
                <tr>
                    <td width="15%">________________</td>
                    <td width="15%">________________</td>
                    <td width="15%">________________</td>
                    <td width="15%">________________</td>
                    <td width="15%">________________</td>
                </tr>
                <tr>
                    <td width="15%">Provided by</td>
                    <td width="15%">Dept. Head</td>
                    <td width="15%">Division Head</td>
                    <td width="15%">Admin Head</td>
                    <td width="15%">Received by</td>
                </tr>
            </tbody>
        </table>
    </div>
</div>
