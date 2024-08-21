@extends('layouts.app')

@section('content')
    <!--begin::Card-->
    <div class="card card-custom">
        <!--begin::Header-->
        <div class="card-header flex-wrap border-0 pt-6 pb-0">
            <div class="card-title">
                <h3 class="card-label">Payment Info</h3>
            </div>
        </div>
        <!--end::Header-->
        <!--begin::Body-->
        <div class="card-body">
            <table class="table table-responsive-lg" id="promotionTable">
                <thead class="custom-thead">
                <tr>
                    <th scope="col">Office ID</th>
                    <th scope="col">Employee Name</th>
                    <th scope="col">Department</th>
                    <th scope="col">Designation</th>
                    <th scope="col">Pay Grade</th>
                    <th scope="col">Status</th>
                    <th scope="col">Action</th>
                </tr>
                </thead>
                <tbody>
                @foreach($items as $item)
                    @if(isset($item->user->fingerprint_no))
                    <tr>
                        <th scope="row">{{ $item->user->fingerprint_no }}</th>
                        <td>{{ $item->user->name }}</td>
                        <td>{{ $item->department->name }}</td>
                        <td>{{ $item->designation->title }}</td>
                        <td>{{ $item->payGrade->name }}</td>
                        <td><button class="btn btn-primary btn-sm font-weight-bold btn-pill">Paid</button></td>
                        <td>
                            <a href="{{ route('paygrade.generatePaySlip', ['user' => $item->user->id]) }}" target="_blank" class="btn btn-success btn-sm font-weight-bold btn-pill">Generate Payslip</a>
                        </td>
                    </tr>
                    @endif
                @endforeach
                </tbody>
            </table>
        </div>
        <!--end::Body-->
        <!--begin::Footer-->
        @if($items->hasPages())
            <div class="card-footer">
                <div class="d-flex">
                    <div class="ml-auto">
                        {{ $items->links() }}
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
            $('#promotionTable').DataTable({
                "order": [],
                "ordering": true,
                "paging": false,
                "bInfo": false,
                "bPaginate": false,
            });
        } );
    </script>
@endsection
