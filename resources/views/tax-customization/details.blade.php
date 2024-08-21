@extends('layouts.app')

@section('content')
    <!--begin::Card-->
    <div class="card card-custom">
        <!--begin::Header-->
        <div class="card-header flex-wrap border-0 pt-6 pb-0">
            <div class="card-title">
                <h3 class="card-label">Tax Customization Details</h3>
            </div>
        </div>
        <!--end::Header-->
        <!--begin::Body-->
        <div class="card-body">
            <table class="table">
                <thead class="custom-thead">
                <tr>
                    <th scope="col">Employee</th>
                    <th scope="col">Month & Year</th>
                    <th scope="col">Taxable Amount</th>
                    <th scope="col">Paid Amount</th>
                    <th scope="col">Due Amount</th>
                </tr>
                </thead>
                <tbody>

                @foreach($data["items"] as $item)
                    <tr>
                        <td>{{ $item->user->name }}</td>
                        <td>{{ date("F", mktime(0, 0, 0, $item->month, 10)) . ", " . $item->year }}</td>
                        <td>{{ number_format($item->taxable_amount, 2) }}/=</td>
                        <td>{{ number_format($item->payable_tax_amount, 2) }}/=</td>
                        <td>{{ number_format(($item->taxable_amount - $item->payable_tax_amount), 2) }}/=</td>
                    </tr>
                @endforeach
                <tr style="border-top: 2px solid black;">
                    <td></td>
                    <td>Total</td>
                    <td>{{ number_format($data["totalTaxableAmount"], 2) }}/=</td>
                    <td>{{ number_format($data["totalPayableAmount"], 2) }}/=</td>
                    <td>{{ number_format(($data["totalTaxableAmount"] - $data["totalPayableAmount"]), 2) }}/=</td>
                </tr>
                </tbody>
            </table>

            <div class="row mt-7">
                <div class="col-lg-12">
                    <ul class="list-unstyled">
                        <li>
                            <span class="font-size-h4"><strong>Total Taxable Amount:</strong> {{ number_format($data["totalTaxableAmount"], 2) }}/=</span>
                        </li>
                        <li>
                            <span class="font-size-h4"><strong>Total Paid Amount:</strong> {{ number_format($data["totalPayableAmount"], 2) }}/=</span>
                        </li>
                        <li>
                            <span class="font-size-h4"><strong>Total Due Amount:</strong>
                                {{ "(" . number_format($data["totalTaxableAmount"], 2) . " - " . number_format($data["totalPayableAmount"], 2) . ") = " . number_format($data["totalDueAmount"], 2) }}/=
                            </span>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
@endsection
