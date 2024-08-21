@extends('layouts.app')

@section('top-css')
    <link href="{{ asset('assets/css/select2.min.css') }}" rel="stylesheet"/>
    <link href="{{ asset('assets/css/datepicker.min.css') }}" rel="stylesheet">
@endsection

@section('content')
    <!--begin::Card-->
    <div class="card card-custom" xmlns="http://www.w3.org/1999/html">
        <!--begin::Header-->
        <div class="card-header flex-wrap pt-3 pb-3">
            <div class="card-title">
                <h3 class="card-label">Employee Meal Status</h3>
            </div>
        </div>
        <!--end::Header-->
        <!--begin::Body-->
        <div class="card-body">
            <div class="d-flex">
                <div class="ml-auto">
                    <form action="{{ route('meal.index') }}" method="GET">
                        <div class="input-group mb-3">
                            <input type="text" class="form-control" name="search" role="search">
                            <div class="input-group-append">
                                <button class="btn btn-outline-secondary" type="submit">Search</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <div class="row">
                @foreach($items->chunk(50) as $item)
                    <div class="col-6">
                        <table class="table table-responsive-lg" id="employeeTable{{$loop->iteration}}">
                            <thead class="custom-thead">
                            <tr>
                                <th scope="col">Office ID</th>
                                <th scope="col">Name</th>
                                <th scope="col">Status</th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach($item as $half)
                                <tr>
                                    <td>{{ $half->fingerprint_no }}</td>
                                    <td>{{ $half->name }}</td>

                                    <td>
                                    <span class="switch switch-outline switch-icon switch-primary">
                                        <label>
                                            <input type="checkbox" {{ optional($half->meal)->status === \App\Models\Meal::STATUS_ACTIVE ? 'checked' : '' }}
                                            name="status" id="{{ $half->id }}" onclick="changeStatus({{ $half->id }}, {{ optional($half->meal)->status  }})"/>
                                            <span></span>
                                        </label>
                                    </span>
                                    </td>
                                </tr>
                            @endforeach

                            </tbody>
                        </table>
                    </div>
                @endforeach
            </div>
        </div>
        <!--end::Body-->
        @if($items->hasPages())
            <div class="card-footer">
                <div class="d-flex">
                    <div class="ml-auto">
                        {{ $items->links() }}
                    </div>
                </div>
            </div>
        @endif
    </div>
    <!--end::Card-->

@endsection

@section('footer-js')
    <script type="text/javascript" src="{{ asset('assets/js/pages/select2.min.js') }}"></script>
    <script src="{{ asset('assets/js/widget.js') }}"></script>
    <script type="text/javascript">

        // Change Status
        function changeStatus(user_id, status) {
            let url = "{{ route('meal.changeStatus') }}";
            let checkBox = document.getElementById(user_id);
            var status = 0;

            if(checkBox.checked) {
                status = 1;
            }

            $.post(url, {user_id: user_id, status: status}, function (response, status) {
                if(status === "success") {
                    swal.fire({
                        title: "Status updated successfully!!"
                    })
                }
            })
        }

        $(document).ready(function () {
            $('#employeeTable1').DataTable({
                "order": [],
                "ordering": true,
                "paging": false,
                "bInfo": false,
                "bPaginate": false,
                "searching": false,
                "sStripeEven": '',
                "sStripeOdd": ''

                // "bFilter": false,
            });

            $('#employeeTable2').DataTable({
                "order": [],
                "ordering": true,
                "paging": false,
                "bInfo": false,
                "bPaginate": false,
                "searching": false,
                "sStripeEven": '',
                "sStripeOdd": ''

                // "bFilter": false,
            });
        });

        // Enable Select2
        $("select").select2({
            theme: "classic",
        });
    </script>
@endsection
