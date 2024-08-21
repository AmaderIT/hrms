@extends('layouts.app')

@section('content')
    <!--begin::Card-->
    <div class="card card-custom">
        <!--begin::Header-->
        <div class="card-header flex-wrap border-0 pt-6 pb-0">
            <div class="card-title">
                <h3 class="card-label">My Pay Slip Listing</h3>
            </div>
        </div>
        <!--end::Header-->
        <!--begin::Body-->
        <div class="card-body">
            <table class="table" id="bankTable">
                <thead class="custom-thead">
                <tr>
                    <th scope="col">Bonus</th>
                    <th scope="col">Month</th>
                    <th scope="col">Year</th>
                    <th scope="col">Actions</th>
                </tr>
                </thead>
                <tbody>
                @foreach($userBonuses as $userBonus)
                    <tr>
                        <td>{{ $userBonus->bonus->festival_name }}</td>
                        <td>{{ date('F', mktime(0, 0, 0, $userBonus->month, 10)) }}</td>
                        <td>{{ $userBonus->year }}</td>
                        <td>
                            <a href="{{ route('user-bonus.generatePaySlip', ['userBonus' => $userBonus->id]) }}">Download Payslip</a>
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
        <!--end::Body-->
        <!--begin::Footer-->
        @if($userBonuses->hasPages())
            <div class="card-footer">
                <div class="d-flex">
                    <div class="ml-auto">
                        {{ $userBonuses->links() }}
                    </div>
                </div>
            </div>
        @endif
        <!--end::Footer-->
    </div>
    <!--end::Card-->
@endsection

@section('footer-js')
    <script>
        $(document).ready( function () {
            $('#bankTable').DataTable({
                "order": [],
                "ordering": true,
                "paging": false,
                "bInfo": false,
                "bPaginate": false,
            });
        } );
    </script>
@endsection
