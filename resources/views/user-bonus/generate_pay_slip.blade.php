@extends('layouts.app')

@section("content")
    <div class="card card-custom">
        <div class="card-header">
            <h3 class="card-title">
                <img src="{{ asset('assets/media/logos/BYSL_Logo.png') }}" class="h-70px"/>
            </h3>
            @can("Download PDF")
                <div class="card-toolbar">
                    <div class="example-tools justify-content-center">
                        <a href="{{ route('user-bonus.pdfDownload', ['UserBonus' => $userBonus->uuid]) }}" class="btn btn-light-primary font-weight-bold">Download PDF</a>
                    </div>
                </div>
            @endcan
        </div>

        <div class="card-body">
            <h4 class="text-center text-uppercase"><u>Payslip for the Bonus of {{ $userBonus->bonus->festival_name . " (" . date('F', mktime(0, 0, 0, $userBonus->month, 10)) . " " . $userBonus->year . ")" }}</u></h4>
            {{--<div class="row">
                <div class="col-sm-6 m-b-20">
                    <img src="" alt="" class="inv-logo">
                    <ul class="list-unstyled mb-0">
                        <li class="text-uppercase"><b>{{ $userBonus->user->currentPromotion->department->name }}</b></li>
                        <li>39/2, Kalachandpur</li>
                        <li>Gulshan, Dhaka</li>
                    </ul>
                </div>
            </div>--}}

            <div class="row mt-5">
                <div class="col-lg-6">
                    <ul class="list-unstyled">
                        <li>
                            <h5><strong>{{ $userBonus->user->name }} - {{ $userBonus->user->fingerprint_no }}</strong></h5>
                        </li>
                        <li><span>{{ $userBonus->user->currentPromotion->designation->title }}, {{ $userBonus->department->name }}</span></li>
                    </ul>
                </div>

                <div class="col-lg-6">
                    <ul class="list-unstyled float-right">
                        @if($userBonus->status == \App\Models\UserBonus::STATUS_PAID)
                            <button type="button" class="btn btn-sm btn-outline-success">PAID</button>
                        @else{{--if($userBonus->status == \App\Models\UserBonus::STATUS_UNPAID)--}}
                            <button type="button" class="btn btn-sm btn-outline-danger">UNPAID</button>
                        @endif
                    </ul>
                </div>
            </div>

            <div class="row mt-12">
                <div class="col-lg-6">
                    <div>
                        <h4><strong>Bonus</strong></h4>
                        <table class="table table-bordered">
                            <tbody>
                                <tr>
                                    <td>
                                        <strong>Amount</strong>
                                        <span class="float-right">
                                            <strong>{{ number_format($userBonus->amount, 2) }} /=</strong>
                                        </span>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="col-lg-6">
                    <div>
                        <h4><strong>Tax</strong></h4>
                        <table class="table table-bordered">
                            <tbody>
                            <tr>
                                <td>
                                    <strong>Amount</strong>
                                    <span class="float-right">
                                        <strong>{{ number_format($userBonus->tax, 2) }} /=</strong>
                                    </span>
                                </td>
                            </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <div class="row mt-7">
                <div class="col-lg-12">
                    <ul class="list-unstyled">
                        <li>
                            <span class="font-size-h4"><strong>Net Payable Amount:</strong>
                                {{ number_format( ($userBonus->amount - $userBonus->tax ), 2) }} /=
                            </span>
                        </li>
                        <li>
                            <span class="font-size-h4"><strong>Amount In Words:</strong>
                                {{ \App\Http\Controllers\SalaryController::convertToWord(($userBonus->amount - $userBonus->tax)) }} Taka Only
                            </span>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
@endsection
